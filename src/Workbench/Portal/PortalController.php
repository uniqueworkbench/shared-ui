<?php

namespace UniqueWorkbench\SharedUi\Workbench\Portal;

use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * A portal's built-in screens for its owners: customers (the owner's Customers from the account app, each invited or not; add a new one, resend, withdraw, remove) and
 * branding (colours and icons). The data is the account app's; this calls its /api/portal with the
 * signed-in user's own token (sso_access_token on the app's users).
 */
class PortalController
{
    public function customers(Request $request)
    {
        $response = $this->send($request, 'get', '/api/portal/customers', array_filter(['q' => trim((string) $request->query('q')), 'page' => $request->query('page')], fn ($value) => filled($value)));
        if ($response->failed()) {
            Log::warning('Portal API failed', ['status' => $response->status()]);
            abort($response->status() === 403 ? 403 : 502, __('The portal could not be reached. Try again.'));
        }

        return view('shared-ui::portal.customers', [
            'customers' => $response->json('customers', []),
            'page' => (int) $response->json('page', 1),
            'lastPage' => (int) $response->json('last_page', 1),
            'total' => (int) $response->json('total', 0),
            'search' => trim((string) $request->query('q')),
        ]);
    }

    /** A new customer: added to the owner's Customers in the account app (with its first contact), and optionally invited */
    public function storeCustomer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'invite' => ['nullable', 'boolean'],
        ]);
        $response = $this->send($request, 'post', '/api/portal/customers', array_filter($validated, fn ($value) => filled($value)) + ['invite' => $request->boolean('invite')]);
        if ($response->successful() && $response->json('invite_error')) {
            return back()->with('success', __(':name was added to your Customers, but not invited: :why', ['name' => $validated['name'], 'why' => $response->json('invite_error')]));
        }

        return $this->done($response, $request->boolean('invite')
            ? __(':name was added to your Customers and invited.', ['name' => $validated['name']])
            : __(':name was added to your Customers.', ['name' => $validated['name']]), 'new_customer');
    }

    /** Invite one of the owner's Customers (customer_id) at one of its contacts' emails */
    public function invite(Request $request): RedirectResponse
    {
        $validated = $request->validate(['customer_id' => ['required', 'integer'], 'via' => ['nullable', 'string', 'max:300']]);
        // `via` is the channel picked: email:a@b.com or phone:5551234567 (none: the account app picks the primary contact's)
        [$kind, $value] = array_pad(explode(':', (string) ($validated['via'] ?? ''), 2), 2, null);
        $data = ['customer_id' => $validated['customer_id']] + (in_array($kind, ['email', 'phone'], true) && filled($value) ? [$kind => $value] : []);
        $response = $this->send($request, 'post', '/api/portal/invites', $data);

        return $this->done($response, __('Invitation sent to :to.', ['to' => $response->json('invite.sent_to')]));
    }

    public function resend(Request $request, int $invite): RedirectResponse
    {
        return $this->done($this->send($request, 'post', "/api/portal/invites/{$invite}/resend"), __('Invitation sent again.'));
    }

    public function revoke(Request $request, int $invite): RedirectResponse
    {
        return $this->done($this->send($request, 'delete', "/api/portal/invites/{$invite}"), __('Invitation withdrawn.'));
    }

    public function removeCustomer(Request $request, int $organization): RedirectResponse
    {
        return $this->done($this->send($request, 'delete', "/api/portal/customers/{$organization}"), __('Customer removed.'));
    }

    public function branding()
    {
        return view('shared-ui::portal.branding', ['brand' => workbench()->portalBrand()]);
    }

    public function saveBranding(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => ['nullable', 'string', 'max:40'],
            'icon_image' => ['nullable', 'file', 'image', 'mimes:png,jpg,jpeg,gif,webp', 'max:2048'],
            'customer_icon_image' => ['nullable', 'file', 'image', 'mimes:png,jpg,jpeg,gif,webp', 'max:2048'],
        ], ['*' => __('Upload a PNG, JPG, GIF or WebP picture under 2 MB.')]);

        // Pictures go along as a multipart POST (a PUT body isn't read as one)
        $fields = $request->only(['title', 'primary_color', 'secondary_color', 'accent_color', 'icon', 'customer_icon', 'remove_icon_image', 'remove_customer_icon_image']);
        $pending = Http::withToken($this->token($request))->acceptJson()->timeout(30);
        foreach (['icon_image', 'customer_icon_image'] as $key) {
            if ($file = $request->file($key)) {
                $pending = $pending->attach($key, file_get_contents($file->getRealPath()), $file->getClientOriginalName());
            }
        }
        $response = $pending->post(rtrim((string) config('sso.base_url'), '/') . '/api/portal/branding', $fields);

        // Their own session's copy of the look, so it shows now rather than at next sign-in
        if ($response->successful()) {
            $context = workbench()->all();
            $context['portal']['branding'] = $response->json('branding');
            workbench()->set($context);
        }

        return $this->done($response, __('Branding saved.'));
    }

    private function token(Request $request): string
    {
        $token = $request->user()?->sso_access_token;
        abort_unless($token, 403, __('Sign in again to manage the portal.'));

        return $token;
    }

    private function send(Request $request, string $method, string $path, array $data = []): Response
    {
        return Http::withToken($this->token($request))->acceptJson()->timeout(15)->{$method}(rtrim((string) config('sso.base_url'), '/') . $path, $data);
    }

    private function done(Response $response, string $success, string $errorField = 'portal'): RedirectResponse
    {
        if ($response->successful()) {
            return back()->with('success', $success);
        }

        return back()->withInput()->withErrors([$errorField => $response->json('message') ?: __('That did not work. Try again.')]);
    }
}
