<?php

namespace UniqueWorkbench\SharedUi\Workbench;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Another app's shared data (App Connections): what this app declares in config/workbench.php
 * `uses` ("timetracker.service-codes") and an admin approved in the account app.
 *
 *   app(Connections::class)->get('timetracker', 'service-codes')        the provider's JSON, or null
 *   app(Connections::class)->get('timetracker', 'service-codes', ['ids' => 3], $organizationId)
 *   app(Connections::class)->available('timetracker', 'service-codes')
 *
 * The account app is only the broker: this app asks it (client credentials, scope `connections`)
 * for a short-lived connection token for one provider, share and organization, then calls the
 * provider directly with it; the provider checks it with the account app's public key
 * (VerifyConnectionToken). No app holds another's key.
 *
 * Answers are cached config('workbench.connections_cache_seconds') (300) per provider, share,
 * organization and query, and the last good answer is kept longer: when the provider or the account
 * app can't be reached it's returned instead (with a warning in the log). Not approved (403) or
 * unknown (404) answers null, remembered for a minute so pages don't keep asking. Without SSO
 * configured (locally) it answers null without calling anything. In a signed-in request the
 * organization defaults to workbench()'s.
 */
class Connections
{
    /** How long a refusal from the account app (not approved, unknown share) is remembered */
    public const DENIED_SECONDS = 60;

    /** How long the last good answer is kept to fall back on */
    public const LAST_GOOD_SECONDS = 7 * 24 * 3600;

    public function get(string $provider, string $share, array $query = [], ?int $organizationId = null): ?array
    {
        $organizationId = $this->organization($organizationId);
        if (blank(config('sso.client_id'))) {
            return null;
        }

        $query = array_map(fn ($value) => is_array($value) ? implode(',', $value) : $value, $query);
        ksort($query);
        $key = $this->key('answer', $provider, $share, $organizationId) . '.' . md5(http_build_query($query));

        if (is_array($answer = Cache::get($key))) {
            return $answer;
        }

        try {
            $answer = $this->call($provider, $share, $query, $organizationId);
        } catch (RequestException|ConnectionException $e) {
            Log::warning("App connection {$provider}.{$share} (organization {$organizationId}) failed: {$e->getMessage()}");

            return Cache::get($key . '.last-good');
        }

        if ($answer === null) {
            return null;
        }

        Cache::put($key, $answer, (int) config('workbench.connections_cache_seconds', 300));
        Cache::put($key . '.last-good', $answer, self::LAST_GOOD_SECONDS);

        return $answer;
    }

    /** Whether this app may read the share for the organization now (approved, both apps there) */
    public function available(string $provider, string $share, ?int $organizationId = null): bool
    {
        $organizationId = $this->organization($organizationId);
        if (blank(config('sso.client_id'))) {
            return false;
        }

        try {
            return $this->token($provider, $share, $organizationId) !== null;
        } catch (RequestException|ConnectionException $e) {
            Log::warning("App connection {$provider}.{$share} (organization {$organizationId}): no token — {$e->getMessage()}");

            return false;
        }
    }

    /** Drop the cached connection token (e.g. after the provider refused it) */
    public function forgetToken(string $provider, string $share, int $organizationId): void
    {
        Cache::forget($this->key('token', $provider, $share, $organizationId));
        Cache::forget($this->key('denied', $provider, $share, $organizationId));
    }

    /** The provider's answer; null when the account app refuses the connection */
    private function call(string $provider, string $share, array $query, int $organizationId, bool $retry = true): ?array
    {
        if (! $connection = $this->token($provider, $share, $organizationId)) {
            return null;
        }

        try {
            return Http::withToken($connection['token'])->acceptJson()->timeout(10)
                ->get($connection['url'], $query + ['organization_id' => $organizationId])
                ->throw()->json();
        } catch (RequestException $e) {
            // The provider refused the token (e.g. the account app's key rotated): get a new one, once
            if ($retry && $e->response->status() === 401) {
                Cache::forget($this->key('token', $provider, $share, $organizationId));

                return $this->call($provider, $share, $query, $organizationId, false);
            }
            throw $e;
        }
    }

    /**
     * A connection token from the account app — ['token' => …, 'url' => the provider's endpoint] —
     * cached until shortly before it expires; null while the account app refuses it.
     */
    private function token(string $provider, string $share, int $organizationId, bool $retry = true): ?array
    {
        $key = $this->key('token', $provider, $share, $organizationId);
        if ($connection = Cache::get($key)) {
            return $connection;
        }
        if (Cache::has($this->key('denied', $provider, $share, $organizationId))) {
            return null;
        }

        $response = Http::withToken(ClientToken::get('connections'))->acceptJson()->timeout(10)
            ->post(config('sso.base_url') . '/api/connections/token', [
                'provider' => $provider,
                'share' => $share,
                'organization_id' => $organizationId,
            ]);

        if ($response->status() === 401 && $retry) {
            ClientToken::forget('connections');

            return $this->token($provider, $share, $organizationId, false);
        }
        if (in_array($response->status(), [403, 404], true)) {
            Log::warning("App connection {$provider}.{$share} (organization {$organizationId}) refused by the account app: " . ($response->json('message') ?? $response->status()));
            Cache::put($this->key('denied', $provider, $share, $organizationId), true, self::DENIED_SECONDS);

            return null;
        }
        $response->throw();

        $connection = ['token' => $response->json('token'), 'url' => $response->json('url')];
        Cache::put($key, $connection, max(10, (int) $response->json('expires_in', 600) - 60));

        return $connection;
    }

    private function organization(?int $organizationId): int
    {
        $organizationId ??= workbench()->organizationId();
        if (! $organizationId) {
            throw new \LogicException('App connections need an organization: there is no signed-in organization here, so pass one.');
        }

        return $organizationId;
    }

    private function key(string $what, string $provider, string $share, int $organizationId): string
    {
        return "workbench.connections.{$what}.{$provider}.{$share}.{$organizationId}";
    }
}
