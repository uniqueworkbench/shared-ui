<?php

namespace UniqueWorkbench\SharedUi\Workbench;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * The shared directory, read from the account app: an organization's
 * customers, vendors, locations, members (people), personas and units. This app never keeps
 * its own copies of these — it stores their ids (customer_id, location_id,
 * users.sso_id) and asks here for names and details, cached for
 * config('workbench.directory_cache_seconds').
 *
 * Server to server with a client-credentials token (this app's SSO client,
 * scope `directory`), so it also works in jobs and commands — pass the
 * organization id there; in a signed-in request it defaults to workbench()'s.
 *
 *   $directory->locations()                          active locations
 *   $directory->locations(['customer_ids' => 5])     a customer's sites
 *   $directory->locations(['visible_to_user_id' => $user->sso_id])
 *   $directory->members(['location_ids' => 12])      everyone at location 12 today
 *   $directory->members(['unit_ids' => 3, 'persona_ids' => 7])
 *   $directory->personas(), units()                  for pickers ("this shift needs a Lifeguard")
 *   $directory->location(12), customer(5), member($ssoId), persona(7)
 *
 * Filters are the account app's (its CLAUDE.md, "The directory API"). Each
 * row is an array. Failures throw Illuminate\Http\Client\RequestException.
 */
class Directory
{
    public function customers(array $filters = [], ?int $organizationId = null): Collection
    {
        return $this->fetch('customers', $filters, $organizationId);
    }

    public function vendors(array $filters = [], ?int $organizationId = null): Collection
    {
        return $this->fetch('vendors', $filters, $organizationId);
    }

    public function locations(array $filters = [], ?int $organizationId = null): Collection
    {
        return $this->fetch('locations', $filters, $organizationId);
    }

    /** People in the organization, narrowed to an audience (location_ids, unit_ids, persona_ids, relationships, …) */
    public function members(array $criteria = [], ?int $organizationId = null): Collection
    {
        return $this->fetch('members', $criteria, $organizationId);
    }

    /** The organization's personas (what people can do): [{id, name, description}] */
    public function personas(array $filters = [], ?int $organizationId = null): Collection
    {
        return $this->fetch('personas', $filters, $organizationId);
    }

    /** The organization's units (what people can see): [{id, name, description, parent_id}] */
    public function units(array $filters = [], ?int $organizationId = null): Collection
    {
        return $this->fetch('units', $filters, $organizationId);
    }

    public function persona(int $id, ?int $organizationId = null): ?array
    {
        return $this->personas(['ids' => $id], $organizationId)->firstWhere('id', $id);
    }

    public function customer(int $id, ?int $organizationId = null): ?array
    {
        return $this->customers(['ids' => $id, 'status' => 'all'], $organizationId)->first();
    }

    public function vendor(int $id, ?int $organizationId = null): ?array
    {
        return $this->vendors(['ids' => $id, 'status' => 'all'], $organizationId)->first();
    }

    public function location(int $id, ?int $organizationId = null): ?array
    {
        return $this->locations(['ids' => $id, 'status' => 'all'], $organizationId)->first();
    }

    /** A member by their account app user id (users.sso_id here) */
    public function member(int|string $ssoId, ?int $organizationId = null): ?array
    {
        return $this->members(['ids' => (int) $ssoId], $organizationId)->first();
    }

    /** Drop cached answers, e.g. after the account app says something changed */
    public function flush(): void
    {
        Cache::increment('workbench.directory.generation');
    }

    private function fetch(string $resource, array $filters, ?int $organizationId): Collection
    {
        $organizationId ??= workbench()->organizationId();
        if (! $organizationId) {
            throw new \LogicException("The directory needs an organization: there is no signed-in organization here, so pass one.");
        }

        // Locally without SSO there's no account app to ask
        if (blank(config('sso.client_id'))) {
            return collect();
        }

        $query = array_map(fn ($value) => is_array($value) ? implode(',', $value) : $value, $filters)
            + ['organization_id' => $organizationId];
        ksort($query);
        $key = 'workbench.directory.' . Cache::get('workbench.directory.generation', 0) . '.' . $resource . '.' . md5(http_build_query($query));

        return collect(Cache::remember($key, (int) config('workbench.directory_cache_seconds', 300), fn () => $this->request($resource, $query)));
    }

    private function request(string $resource, array $query, bool $retry = true): array
    {
        try {
            return Http::withToken($this->token())->acceptJson()->timeout(10)
                ->get(config('sso.base_url') . '/api/' . $resource, $query)
                ->throw()->json();
        } catch (RequestException $e) {
            if ($retry && $e->response->status() === 401) {
                Cache::forget('workbench.directory.token');

                return $this->request($resource, $query, false);
            }
            throw $e;
        }
    }

    /** A client-credentials token for this app's SSO client, cached until shortly before it expires */
    private function token(): string
    {
        if ($token = Cache::get('workbench.directory.token')) {
            return $token;
        }

        $response = Http::asForm()->acceptJson()->timeout(10)->post(config('sso.base_url') . '/oauth/token', array_filter([
            'grant_type' => 'client_credentials',
            'client_id' => config('sso.client_id'),
            'client_secret' => config('sso.client_secret'),
            // A client limited to certain scopes must ask for this one; an unrestricted client needs none
            'scope' => config('sso.scopes') ? 'directory' : null,
        ]))->throw()->json();

        Cache::put('workbench.directory.token', $response['access_token'], max(60, (int) ($response['expires_in'] ?? 3600) - 60));

        return $response['access_token'];
    }
}
