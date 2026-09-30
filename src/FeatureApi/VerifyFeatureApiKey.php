<?php

namespace UniqueWorkbench\SharedUi\FeatureApi;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards an app's Feature API: the endpoints the account app's App Features
 * pages read this app's data from, server to server. The account app sends
 * the application's Feature API Key as X-Api-Key; it must match this app's
 * FEATURE_API_KEY (config('shared-ui.feature_api_key')). With no key set,
 * everything is refused.
 *
 * Route middleware alias: `feature-api`. The caller has already decided what
 * its user may see and says so in the request (e.g. an organization id), so
 * endpoints behind this trust those parameters.
 */
class VerifyFeatureApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('shared-ui.feature_api_key');

        if ($expected === '' || ! hash_equals($expected, (string) $request->header('X-Api-Key'))) {
            return response()->json(['message' => 'Invalid API key.'], 401);
        }

        return $next($request);
    }
}
