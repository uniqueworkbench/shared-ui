<?php

namespace UniqueWorkbench\SharedUi\FeatureApi;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards an endpoint this app shares with other apps (config/workbench.php `provides`):
 * `->middleware('workbench.share:service-codes')`. It accepts either
 *  - this app's Feature API key (X-Api-Key — the account app's own App Features, as `feature-api`), or
 *  - a connection token (Authorization: Bearer) the account app issued to an approved subscriber:
 *    an RS256 JWT checked with the account app's public key, whose `iss` is this app's SSO_URL,
 *    `aud` its SSO client id, `share` the share guarded here and `org` the request's organization_id,
 *    and not expired.
 *
 * The caller's app key, the share, the organization and the token id are put on the request as
 * `workbench.connection`. The account app's public key (GET /api/connections/public-key) is cached
 * for a day and fetched again once when a signature doesn't check out, in case it was rotated.
 */
class VerifyConnectionToken
{
    public const PUBLIC_KEY_CACHE = 'workbench.connections.public-key';

    /** Seconds of clock difference allowed on exp / iat */
    public const LEEWAY = 30;

    public function handle(Request $request, Closure $next, string $share): Response
    {
        if ($request->hasHeader('X-Api-Key') && VerifyFeatureApiKey::valid($request)) {
            return $next($request);
        }

        $token = $request->bearerToken();
        if (! $token) {
            return $this->refuse('A connection token or API key is required.');
        }

        try {
            $claims = $this->decode($token);
        } catch (\Throwable) {
            return $this->refuse('Invalid connection token.');
        }

        $organizationId = $request->input('organization_id');
        if (($claims->iss ?? null) !== config('sso.base_url')
            || (string) ($claims->aud ?? '') === '' || (string) $claims->aud !== (string) config('sso.client_id')
            || ($claims->share ?? null) !== $share
            || ! is_numeric($organizationId) || (int) ($claims->org ?? 0) !== (int) $organizationId) {
            return $this->refuse('This connection token is not for this request.');
        }

        $request->attributes->set('workbench.connection', [
            'app' => $claims->sub ?? null,
            'share' => $share,
            'organization_id' => (int) $claims->org,
            'jti' => $claims->jti ?? null,
        ]);

        return $next($request);
    }

    private function decode(string $token): object
    {
        $leeway = JWT::$leeway;
        JWT::$leeway = self::LEEWAY;

        try {
            try {
                return JWT::decode($token, new Key($this->publicKey(), 'RS256'));
            } catch (SignatureInvalidException) {
                // The account app's key may have been rotated: fetch it again, once
                Cache::forget(self::PUBLIC_KEY_CACHE);

                return JWT::decode($token, new Key($this->publicKey(), 'RS256'));
            }
        } finally {
            JWT::$leeway = $leeway;
        }
    }

    private function publicKey(): string
    {
        return Cache::remember(self::PUBLIC_KEY_CACHE, 86400, function () {
            $key = Http::acceptJson()->timeout(10)->get(config('sso.base_url') . '/api/connections/public-key')->throw()->json('key');
            if (! is_string($key) || $key === '') {
                throw new \RuntimeException('The account app sent no public key.');
            }

            return $key;
        });
    }

    private function refuse(string $message): Response
    {
        return response()->json(['message' => $message], 401);
    }
}
