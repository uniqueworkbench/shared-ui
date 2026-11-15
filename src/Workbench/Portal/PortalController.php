<?php

namespace UniqueWorkbench\SharedUi\Workbench\Portal;

use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * A portal's built-in screens for its owners: customers (pick one from the account app's Customers or invite by email, resend, withdraw, remove) and
 * branding (colours and icons). The data is the account app's; this calls its /api/portal with the
 * signed-in user's own token (sso_access_token on the app's users).
 */
class PortalController
{
    public function customers(Request $request)
    {
        $response = $this->send($request, 'get', '/api/portal');
        if ($response->failed()) {
            Log::warning('Portal API failed', ['status' => $response->status()]);
            abort($response->status() === 403 ? 403 : 502, __('The portal could not be reached. Try again.'));
        }

        return view('shared-ui::portal.customers', [
            'customers' => $response->json('customers', []),
            'invites' => $response->json('invites', []),
        ]);
    }

    public function invite(Request $request): RedirectResponse
    {
        // Either one of the owner's own customers from the account app's directory (customer_id; it's invited at the
        // chosen contact's email) or just an email
        $validated = $request->validate([
            'customer_id' => ['nullable', 'integer'],
            'email' => [$request->filled('customer_id') ? 'nullable' : 'required', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);
        $response = $this->send($request, 'post', '/api/portal/invites', array_filter($validated, fn ($value) => filled($value)));
        $sentTo = $response->json('invite.email') ?: ($validated['email'] ?? '');

        return $this->done($response, __('Invitation sent to :email.', ['email' => $sentTo]), $request->filled('customer_id') ? 'portal' : 'email');
    }

    /** The owner's own customers (the account app's directory) matching ?q=, for the picker on the Customers screen */
    public function searchCustomers(Request $request): JsonResponse
    {
        $response = $this->send($request, 'get', '/api/portal/customers/search', ['q' => (string) $request->query('q')]);

        return response()->json($response->successful() ? $response->json() : [], $response->successful() ? 200 : 502);
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
