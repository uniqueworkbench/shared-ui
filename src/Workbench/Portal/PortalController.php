<?php

namespace UniqueWorkbench\SharedUi\Workbench\Portal;

use Illuminate\Http\Client\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * A portal's built-in screens for its owners: customers (invite by email, resend, withdraw, remove) and
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
        $validated = $request->validate(['email' => ['required', 'email', 'max:255'], 'name' => ['nullable', 'string', 'max:255']]);

        return $this->done($this->send($request, 'post', '/api/portal/invites', $validated), __('Invitation sent to :email.', ['email' => $validated['email']]), 'email');
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
        $response = $this->send($request, 'put', '/api/portal/branding', $request->only(['primary_color', 'secondary_color', 'accent_color', 'icon', 'customer_icon']));

        // Their own session's copy of the look, so it shows now rather than at next sign-in
        if ($response->successful()) {
            $context = workbench()->all();
            $context['portal']['branding'] = $response->json('branding');
            workbench()->set($context);
        }

        return $this->done($response, __('Branding saved.'));
    }

    private function send(Request $request, string $method, string $path, array $data = []): Response
    {
        $token = $request->user()?->sso_access_token;
        abort_unless($token, 403, __('Sign in again to manage the portal.'));

        return Http::withToken($token)->acceptJson()->timeout(15)->{$method}(rtrim((string) config('sso.base_url'), '/') . $path, $data);
    }

    private function done(Response $response, string $success, string $errorField = 'portal'): RedirectResponse
    {
        if ($response->successful()) {
            return back()->with('success', $success);
        }

        return back()->withInput()->withErrors([$errorField => $response->json('message') ?: __('That did not work. Try again.')]);
    }
}
