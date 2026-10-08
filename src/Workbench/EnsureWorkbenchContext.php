<?php

namespace UniqueWorkbench\SharedUi\Workbench;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Every signed-in page needs the organization context (Workbench, workbench()).
 * A session without one — signed in by the remember-me cookie, or from before
 * the context existed or carried what it does now — goes back through SSO, which is silent while the
 * person is signed in to the account app. Locally without SSO, the local
 * developer gets a local organization.
 */
class EnsureWorkbenchContext
{
    public function __construct(private Workbench $workbench)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ($this->workbench->has() && $this->workbench->isCurrent()) || $request->routeIs('login', 'logout', 'sso.*', 'dev-login')) {
            return $next($request);
        }
        // A context from before it carried the person's contact (contactId()) is renewed the same way
        $this->workbench->forget();

        if (app()->environment('local') && blank(config('sso.client_id'))) {
            $this->workbench->set(Workbench::localDeveloper());

            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Sign in again.'], 401);
        }

        $request->session()->put('url.intended', $request->fullUrl());

        return redirect()->route('sso.redirect');
    }
}
