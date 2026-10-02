<?php

namespace UniqueWorkbench\SharedUi\Workbench;

use Illuminate\Contracts\Session\Session;

/**
 * Who the signed-in user is in the organization they're working in — the
 * account app's answer at sign-in (GET /api/user), kept in the session.
 *
 * Everything in this app is seen from one organization at a time, chosen in
 * the account app. A user in several organizations switches there (Return to
 * Portal → Switch Organization) and comes back through SSO, which replaces
 * this context. It's per session, never on the users table: the same person
 * can be in another organization in another browser.
 *
 * Use it through the `workbench()` helper or by injecting it:
 *   workbench()->organizationId()        every query on app data filters by it (BelongsToOrganization does)
 *   workbench()->isOwner()               the organization's owners (and admins) — roles are owner and user
 *   workbench()->hasPosition('Lifeguard')  positions drive what people can do
 *   workbench()->inUnit('North')         units drive what they can see
 *   workbench()->seesAllLocations() / locationIds()   which locations' data to show (ScopedToLocations does)
 *   workbench()->relationship()          employee, contractor, customer or vendor
 *   workbench()->can('schedule.publish') this app's permissions (granted to positions in the account app)
 *   workbench()->setting('key')          this app's settings (account app → App Settings)
 */
class Workbench
{
    public const SESSION_KEY = 'workbench';

    public const WORKFORCE = ['employee', 'contractor'];

    public function __construct(private Session $session)
    {
    }

    /**
     * The context to keep from the account app's /api/user response.
     */
    public static function fromSsoUser(array $ssoUser): array
    {
        $ids = fn (?array $items) => array_values(array_map(fn ($item) => ['id' => (int) $item['id'], 'name' => (string) $item['name']], $items ?? []));

        return [
            'organization_id' => isset($ssoUser['organization_id']) ? (int) $ssoUser['organization_id'] : (isset($ssoUser['account_id']) ? (int) $ssoUser['account_id'] : null),
            'organization_name' => $ssoUser['organization_name'] ?? $ssoUser['account_name'] ?? null,
            'role' => $ssoUser['organization_role'] ?? null,
            // Account apps from before relationships: everyone was an employee
            'relationship' => $ssoUser['relationship'] ?? 'employee',
            'customer_id' => $ssoUser['customer_id'] ?? null,
            'vendor_id' => $ssoUser['vendor_id'] ?? null,
            'is_admin' => (bool) ($ssoUser['is_admin'] ?? false),
            'positions' => $ids($ssoUser['positions'] ?? []),
            'units' => $ids($ssoUser['units'] ?? []),
            // Before location scoping existed, nothing was limited
            'location_scope' => $ssoUser['location_scope'] ?? 'all',
            'location_ids' => array_map('intval', $ssoUser['location_ids'] ?? []),
            'assigned_locations' => $ssoUser['assigned_locations'] ?? [],
            'organizations' => $ssoUser['organizations'] ?? [],
            // What this app lets them do (the account app's app permissions for this app)
            'permissions' => array_values($ssoUser['permissions'] ?? []),
            'app_settings' => (array) ($ssoUser['app_settings'] ?? []),
        ];
    }

    /** Local development without SSO: the owner of a local organization */
    public static function localDeveloper(): array
    {
        return self::fromSsoUser([
            'organization_id' => 1,
            'organization_name' => 'Local Organization',
            'organization_role' => 'owner',
            'is_admin' => true,
        ]);
    }

    public function set(array $context): void
    {
        $this->session->put(self::SESSION_KEY, $context);
    }

    public function forget(): void
    {
        $this->session->forget(self::SESSION_KEY);
    }

    /** Whether there's a context (a signed-in request that came through SSO) */
    public function has(): bool
    {
        return $this->organizationId() !== null;
    }

    public function all(): array
    {
        return (array) $this->session->get(self::SESSION_KEY, []);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function organizationId(): ?int
    {
        return $this->get('organization_id');
    }

    public function organizationName(): ?string
    {
        return $this->get('organization_name');
    }

    /** owner or user (older account apps also sent manager or staff) */
    public function role(): ?string
    {
        return $this->get('role');
    }

    public function isAdmin(): bool
    {
        return (bool) $this->get('is_admin', false);
    }

    public function isOwner(): bool
    {
        return $this->isAdmin() || $this->role() === 'owner';
    }

    /**
     * Owners (and admins), or a manager from an account app before roles
     * became owner and user.
     *
     * @deprecated Roles are owner and user: use isOwner() for running the
     *             organization and can('permission') for what people do in the app.
     */
    public function isManager(): bool
    {
        return $this->isOwner() || $this->role() === 'manager';
    }

    /** employee, contractor, customer or vendor */
    public function relationship(): string
    {
        return $this->get('relationship', 'employee');
    }

    /** Employees and contractors: the organization's own people, not customers or vendors */
    public function isWorkforce(): bool
    {
        return in_array($this->relationship(), self::WORKFORCE, true);
    }

    /** The customer a customer member signs in as */
    public function customerId(): ?int
    {
        return $this->get('customer_id');
    }

    public function vendorId(): ?int
    {
        return $this->get('vendor_id');
    }

    /** @return array<int, array{id: int, name: string}> */
    public function positions(): array
    {
        return $this->get('positions', []);
    }

    /** @return array<int, array{id: int, name: string}> */
    public function units(): array
    {
        return $this->get('units', []);
    }

    /** Holds the position (by name or id) — what people can do */
    public function hasPosition(string|int ...$positions): bool
    {
        return $this->inAny($this->positions(), $positions);
    }

    /** Is in the unit (by name or id) — what people can see */
    public function inUnit(string|int ...$units): bool
    {
        return $this->inAny($this->units(), $units);
    }

    /** Sees every location's data (owners and admins) */
    public function seesAllLocations(): bool
    {
        return $this->get('location_scope', 'all') === 'all';
    }

    /** The locations whose data the user may see, when not seesAllLocations() */
    public function locationIds(): array
    {
        return $this->get('location_ids', []);
    }

    public function canSeeLocation(?int $locationId): bool
    {
        return $this->seesAllLocations() || in_array($locationId, $this->locationIds(), true);
    }

    /** Where the user is assigned to work: [{id, name, customer_id, position_id, starts_on, ends_on}] */
    public function assignedLocations(): array
    {
        return $this->get('assigned_locations', []);
    }

    /**
     * Whether the user has this app's permission $key: admins define the app's
     * permissions in the account app (Applications → Permissions), owners grant
     * them to positions, and owners and admins have them all. Also a gate per
     * key listed in config/workbench.php `permissions` (@can, can: middleware).
     */
    public function can(string $key): bool
    {
        return $this->isOwner() || in_array($key, $this->get('permissions', []), true);
    }

    /** This app's setting for the user (account app → Applications → App Settings) */
    public function setting(string $key, mixed $default = null): mixed
    {
        return $this->get('app_settings', [])[$key] ?? $default;
    }

    private function inAny(array $items, array $wanted): bool
    {
        foreach ($items as $item) {
            foreach ($wanted as $want) {
                if (is_int($want) ? $item['id'] === $want : strcasecmp($item['name'], $want) === 0) {
                    return true;
                }
            }
        }

        return false;
    }
}
