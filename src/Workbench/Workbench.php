<?php

namespace UniqueWorkbench\SharedUi\Workbench;

use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Log;

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
 *   workbench()->hasPersona('Lifeguard')   personas drive what people can do
 *   workbench()->inUnit('North')         units drive what they can see
 *   workbench()->unitScopeIds() / canSeeUnit()   their units and the units below them
 *   workbench()->seesAllLocations() / locationIds()   which locations' data to show (ScopedToLocations does)
 *   workbench()->contactId()             the person (their contact) — key people by it, as Directory::people() does
 *   workbench()->contactType()           employee, contractor, customer, vendor, other, … (also relationship())
 *   workbench()->can('schedule.publish') this app's permissions (set per persona in the account app)
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
            // Pictures uploaded in the account app (absolute URLs; null = initials / building icon)
            'avatar_url' => $ssoUser['avatar_url'] ?? null,
            'organization_avatar_url' => $ssoUser['organization_avatar_url'] ?? null,
            // Their contact in the organization — the account app's record of them, what this app keys
            // people by (Directory::people() ids); null from account apps before contacts were people
            'contact_id' => isset($ssoUser['contact_id']) ? (int) $ssoUser['contact_id'] : null,
            // Their contact type (employee, contractor, customer, vendor, other, or an app's or the
            // organization's own); account apps from before relationships: everyone was an employee
            'relationship' => $ssoUser['contact_type'] ?? $ssoUser['relationship'] ?? 'employee',
            // Whether that type is the organization's workforce (null from older account apps: by WORKFORCE)
            'is_workforce' => isset($ssoUser['is_workforce']) ? (bool) $ssoUser['is_workforce'] : null,
            'customer_id' => $ssoUser['customer_id'] ?? null,
            'vendor_id' => $ssoUser['vendor_id'] ?? null,
            'customer_ids' => array_values(array_map('intval', $ssoUser['customer_ids'] ?? array_filter([$ssoUser['customer_id'] ?? null]))),
            'vendor_ids' => array_values(array_map('intval', $ssoUser['vendor_ids'] ?? array_filter([$ssoUser['vendor_id'] ?? null]))),
            'is_admin' => (bool) ($ssoUser['is_admin'] ?? false),
            'personas' => $ids($ssoUser['personas'] ?? []),
            'units' => $ids($ssoUser['units'] ?? []),
            // Their units plus every unit below them (the account app's User::unitScopeIn); null from older account apps
            'unit_scope_ids' => isset($ssoUser['unit_scope_ids']) ? array_values(array_map('intval', $ssoUser['unit_scope_ids'])) : null,
            // Before location scoping existed, nothing was limited
            'location_scope' => $ssoUser['location_scope'] ?? 'all',
            'location_ids' => array_map('intval', $ssoUser['location_ids'] ?? []),
            'assigned_locations' => $ssoUser['assigned_locations'] ?? [],
            'organizations' => $ssoUser['organizations'] ?? [],
            // What this app lets them do (the account app's app permissions for this app)
            'permissions' => array_values($ssoUser['permissions'] ?? []),
            'app_settings' => (array) ($ssoUser['app_settings'] ?? []),
            // This app's brand, uploaded in the account app (absolute URLs; null = this app's own files)
            'app_branding' => array_map(
                fn ($url) => is_string($url) && $url !== '' ? $url : null,
                array_intersect_key((array) ($ssoUser['app_branding'] ?? []), array_flip(['icon_url', 'header_logo_url', 'favicon_url'])),
            ),
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

    /** Whether the context has everything fromSsoUser() keeps now (one from an older shared-ui is renewed through SSO) */
    public function isCurrent(): bool
    {
        return array_key_exists('contact_id', $this->all());
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

    /** The user's picture from the account app, or null */
    public function avatarUrl(): ?string
    {
        return $this->get('avatar_url');
    }

    /** The organization's avatar from the account app, or null */
    public function organizationAvatarUrl(): ?string
    {
        return $this->get('organization_avatar_url');
    }

    /**
     * This app's brand from the account app (Admin → Applications), or null:
     * 'icon_url' (square), 'header_logo_url' (wide, for the dark top bar), 'favicon_url'.
     */
    public function brandingUrl(string $key): ?string
    {
        return $this->get('app_branding', [])[$key] ?? null;
    }

    /** owner or user (older account apps also sent manager or staff) */
    public function role(): ?string
    {
        return $this->get('role');
    }

    /** The role as people see it: Owner, or Member for `user` (so it isn't confused with Users) */
    public function roleLabel(): ?string
    {
        $role = $this->role();

        return $role === null ? null : (['owner' => 'Owner', 'user' => 'Member'][$role] ?? ucfirst($role));
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

    /**
     * The signed-in person's contact id — the account app's record of them in this organization.
     * Key people by it (it's what Directory::people() returns as id), not by the login's sso id:
     * contacts exist before they ever log in. Null from account apps before contacts were people.
     */
    public function contactId(): ?int
    {
        return $this->get('contact_id');
    }

    /** Their contact type: employee, contractor, customer, vendor, other, or an app's or the organization's own */
    public function contactType(): string
    {
        return $this->relationship();
    }

    /** Their contact type (the name it had before contacts were people) */
    public function relationship(): string
    {
        return $this->get('relationship', 'employee');
    }

    /** The organization's own people (a workforce contact type — employees and contractors by default), not customers or vendors */
    public function isWorkforce(): bool
    {
        return $this->get('is_workforce') ?? in_array($this->relationship(), self::WORKFORCE, true);
    }

    /** Every customer their contact is at (customerId() is the first, for a customer contact) */
    public function customerIds(): array
    {
        return $this->get('customer_ids', []);
    }

    public function vendorIds(): array
    {
        return $this->get('vendor_ids', []);
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
    public function personas(): array
    {
        return $this->get('personas', []);
    }

    /** @return array<int, array{id: int, name: string}> */
    public function units(): array
    {
        return $this->get('units', []);
    }

    /** Holds the persona (by name or id) — what people can do */
    public function hasPersona(string|int ...$personas): bool
    {
        return $this->inAny($this->personas(), $personas);
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

    /** The units whose data the user may see: their own and every unit below them */
    public function unitScopeIds(): array
    {
        return $this->get('unit_scope_ids') ?? array_column($this->units(), 'id');
    }

    /** Sees the unit's data: everyone who sees every location, else units in their scope */
    public function canSeeUnit(?int $unitId): bool
    {
        return $this->seesAllLocations() || in_array($unitId, $this->unitScopeIds(), true);
    }

    /** Where the user is assigned to work: [{id, name, customer_id, persona_id, starts_on, ends_on}] */
    public function assignedLocations(): array
    {
        return $this->get('assigned_locations', []);
    }

    /**
     * Whether the user has this app's permission $key: the app declares its
     * permissions, each with a default, in config/workbench.php `permissions`
     * (the account app syncs them from GET /api/features/permissions), owners
     * set them per persona, and owners and admins have them all. Also a gate
     * per declared key (@can, can: middleware).
     */
    public function can(string $key): bool
    {
        return $this->isOwner() || in_array($key, $this->get('permissions', []), true);
    }

    /**
     * The contact types the app declares in config/workbench.php `contact_types`, offered to the
     * organizations using it beside Workbench's (employee, contractor, customer, vendor, other):
     * key => label, or key => [label, description, workforce] (workforce: the organization's own
     * people — permission defaults, may be owners). Keys are lower-case letters, digits and _.
     *
     * @return array<int, array{key: string, label: string, description: ?string, workforce: bool}>
     */
    public static function declaredContactTypes(): array
    {
        $declared = [];
        foreach (config('workbench.contact_types', []) as $key => $definition) {
            $definition = is_array($definition) ? $definition : ['label' => $definition];
            $declared[] = [
                'key' => (string) $key,
                'label' => (string) ($definition['label'] ?? $key),
                'description' => $definition['description'] ?? null,
                'workforce' => (bool) ($definition['workforce'] ?? false),
            ];
        }

        return $declared;
    }

    /**
     * The permissions the app declares in config/workbench.php `permissions`:
     * key => label, or key => [label, description, default] (default: whether
     * employees and contractors have it unless a persona denies it; off =
     * owners and the personas allowed it).
     *
     * @return array<int, array{key: string, label: string, description: ?string, default: bool}>
     */
    public static function declaredPermissions(): array
    {
        $declared = [];
        foreach (config('workbench.permissions', []) as $key => $definition) {
            $definition = is_array($definition) ? $definition : ['label' => $definition];
            $declared[] = [
                'key' => (string) $key,
                'label' => (string) ($definition['label'] ?? $key),
                'description' => $definition['description'] ?? null,
                'default' => (bool) ($definition['default'] ?? false),
            ];
        }

        return $declared;
    }

    /**
     * What the app shares with other apps, declared in config/workbench.php `provides`:
     * key => [label, description, endpoint] — endpoint a path on this app under /api/features/,
     * guarded by the `workbench.share:<key>` middleware. The account app syncs them; admins
     * approve which apps may use each one. Keys are lower-case letters, digits and dashes;
     * an entry with a bad key or endpoint is skipped (and logged).
     *
     * @return array<int, array{key: string, label: string, description: ?string, endpoint: string}>
     */
    public static function declaredShares(): array
    {
        $declared = [];
        foreach (config('workbench.provides', []) as $key => $definition) {
            $definition = is_array($definition) ? $definition : ['label' => $definition];
            $endpoint = '/' . ltrim((string) ($definition['endpoint'] ?? ''), '/');
            if (! self::validShareKey((string) $key) || ! str_starts_with($endpoint, '/api/features/') || $endpoint === '/api/features/') {
                Log::warning("config/workbench.php provides: skipped \"{$key}\" — keys are lower-case letters, digits and dashes, and endpoints start with /api/features/.");

                continue;
            }
            $declared[] = [
                'key' => (string) $key,
                'label' => (string) ($definition['label'] ?? $key),
                'description' => $definition['description'] ?? null,
                'endpoint' => $endpoint,
            ];
        }

        return $declared;
    }

    /**
     * What the app reads from other apps, declared in config/workbench.php `uses`:
     * "<provider app key>.<share key>" => [reason] (or just the reason). The account app syncs
     * them as requests an admin approves; Connections::get() reads them once approved. An entry
     * with a bad key is skipped (and logged).
     *
     * @return array<int, array{provider: string, share: string, reason: ?string}>
     */
    public static function declaredUses(): array
    {
        $declared = [];
        foreach (config('workbench.uses', []) as $key => $definition) {
            $definition = is_array($definition) ? $definition : ['reason' => $definition];
            [$provider, $share] = array_pad(explode('.', (string) $key, 2), 2, '');
            if (! self::validShareKey($provider) || ! self::validShareKey($share)) {
                Log::warning("config/workbench.php uses: skipped \"{$key}\" — use \"<provider app key>.<share key>\".");

                continue;
            }
            $declared[] = [
                'provider' => $provider,
                'share' => $share,
                'reason' => $definition['reason'] ?? null,
            ];
        }

        return $declared;
    }

    /** An app key or share key: lower-case letters, digits and dashes, starting with a letter */
    public static function validShareKey(string $key): bool
    {
        return (bool) preg_match('/^[a-z][a-z0-9-]{0,63}$/', $key);
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
