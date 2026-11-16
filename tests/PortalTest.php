<?php

namespace UniqueWorkbench\SharedUi\Tests;

use UniqueWorkbench\SharedUi\Workbench\Workbench;

class PortalTest extends TestCase
{
    public function test_a_super_admins_view_as_the_owner_is_like_an_impersonation_not_admin_mode(): void
    {
        $workbench = new Workbench(app('session.store'));
        $workbench->set(Workbench::fromSsoUser([
            'organization_id' => 5, 'organization_role' => 'owner', 'is_admin' => true,
            'portal' => ['id' => 9, 'role' => 'owner', 'as_owner' => true, 'owner' => ['id' => 5, 'name' => 'Acme']],
        ]));
        $this->assertTrue($workbench->portalAsOwner());
        $this->assertTrue($workbench->isPortalOwner());
        // It isn't Admin Mode: that's the separate switch, still off
        $this->assertFalse($workbench->isAdmin());

        $workbench->set(Workbench::fromSsoUser(['organization_id' => 5, 'organization_role' => 'owner', 'portal' => ['role' => 'owner', 'as_owner' => false]]));
        $this->assertFalse($workbench->portalAsOwner());
        $workbench->set(Workbench::fromSsoUser(['organization_id' => 5, 'organization_role' => 'owner']));
        $this->assertFalse($workbench->portalAsOwner());
    }

    public function test_the_viewing_as_owner_bar_shows_only_then_with_a_way_out(): void
    {
        config(['shared-ui.portal_url' => 'https://account.example.test']);
        $context = fn (array $portal) => Workbench::fromSsoUser(['organization_id' => 5, 'organization_role' => 'owner', 'portal' => $portal]);

        workbench()->set($context(['id' => 9, 'role' => 'owner', 'as_owner' => true, 'owner' => ['id' => 5, 'name' => 'Acme']]));
        $html = view('shared-ui::workbench.mode-banners')->render();
        $this->assertStringContainsString('data-portal-as-owner', $html);
        $this->assertStringContainsString("Acme's owner", html_entity_decode($html, ENT_QUOTES));
        $this->assertStringContainsString('https://account.example.test/admin/portals/9/exit', $html);
        $this->assertStringNotContainsString('data-admin-mode-bar', $html); // not Admin Mode

        workbench()->set($context(['id' => 9, 'role' => 'owner', 'as_owner' => false, 'owner' => ['id' => 5, 'name' => 'Acme']]));
        $this->assertSame('', trim(view('shared-ui::workbench.mode-banners')->render()));
    }

    public function test_a_portals_title_is_shown_in_full_not_truncated(): void
    {
        config(['shared-ui.portal' => true]);
        $this->actingAs(new class extends \Illuminate\Foundation\Auth\User {
            public $id = 1;
            public $name = 'Pat';
        });
        $title = 'Test Pool Cover/Liner Manufacturer Portal Company';
        workbench()->set(Workbench::fromSsoUser([
            'organization_id' => 5, 'organization_role' => 'owner',
            'portal' => ['role' => 'owner', 'owner' => ['id' => 5, 'name' => 'Acme'], 'branding' => ['title' => $title, 'primary_color' => '#112233']],
        ]));

        $html = view('shared-ui::workbench.brand')->render();

        $this->assertStringContainsString($title, $html);
        $this->assertMatchesRegularExpression('/<span[^>]*data-portal-title[^>]*>/', $html);
        $this->assertDoesNotMatchRegularExpression('/<span[^>]*truncate[^>]*data-portal-title|<span[^>]*data-portal-title[^>]*truncate/', $html);

        // In the desktop top bar its box grows with the title (min the sidebar's width) instead of being fixed at it
        $bar = file_get_contents(__DIR__ . '/../resources/views/workbench/desktop.blade.php');
        $this->assertStringContainsString("'lg:w-60' => ! config('shared-ui.portal')", $bar);
        $this->assertStringContainsString("'lg:min-w-[15rem] lg:whitespace-nowrap' => config('shared-ui.portal')", $bar);
    }

    public function test_the_portal_context_from_the_account_app_says_what_the_organization_is_to_it(): void
    {
        $context = Workbench::fromSsoUser([
            'organization_id' => 5, 'organization_role' => 'owner',
            'portal' => ['role' => 'owner', 'owner' => ['id' => 5, 'name' => 'Acme'], 'branding' => ['primary_color' => '#112233']],
        ]);
        $this->assertSame('owner', $context['portal']['role']);

        $workbench = new Workbench(app('session.store'));
        $workbench->set($context);
        $this->assertTrue($workbench->isPortalOwner());
        $this->assertFalse($workbench->isPortalCustomer());
        $this->assertSame([5, 'Acme'], [$workbench->portalOwnerId(), $workbench->portalOwnerName()]);
        $this->assertSame('#112233', $workbench->portalBrand()['primary_color']);

        // A member (not an owner) of an owning organization sees the portal but doesn't manage it
        $workbench->set(Workbench::fromSsoUser(['organization_id' => 5, 'organization_role' => 'user', 'portal' => ['role' => 'owner']]));
        $this->assertTrue($workbench->isPortalOwnerOrg());
        $this->assertFalse($workbench->isPortalOwner());

        $workbench->set(Workbench::fromSsoUser(['organization_id' => 6, 'organization_role' => 'owner', 'portal' => ['role' => 'customer']]));
        $this->assertTrue($workbench->isPortalCustomer());
        $this->assertFalse($workbench->isPortalOwner());

        // An ordinary app, or an older account app
        $workbench->set(Workbench::fromSsoUser(['organization_id' => 6]));
        $this->assertNull($workbench->portal());
        $this->assertSame([], $workbench->portalBrand());
    }

    public function test_managing_branding_and_customers_are_each_a_permission_of_an_owner_organization(): void
    {
        $workbench = new Workbench(app('session.store'));
        $context = fn (string $portalRole, array $permissions, string $role = 'user') => Workbench::fromSsoUser([
            'organization_id' => 5, 'organization_role' => $role, 'permissions' => $permissions, 'portal' => ['role' => $portalRole],
        ]);

        $workbench->set($context('owner', ['portal.manage_branding']));
        $this->assertTrue($workbench->canManagePortalBranding());
        $this->assertFalse($workbench->canManagePortalCustomers());

        $workbench->set($context('owner', ['portal.manage_customers']));
        $this->assertFalse($workbench->canManagePortalBranding());
        $this->assertTrue($workbench->canManagePortalCustomers());

        // A customer organization never does, whatever it holds
        $workbench->set($context('customer', ['portal.manage_branding', 'portal.manage_customers'], 'owner'));
        $this->assertFalse($workbench->canManagePortalBranding());
        $this->assertFalse($workbench->canManagePortalCustomers());
    }

    public function test_a_portal_declares_its_two_permissions_with_the_apps_own(): void
    {
        config(['workbench.permissions' => ['orders.view' => 'View orders']]);
        $this->assertSame(['orders.view'], array_column(Workbench::declaredPermissions(), 'key'));

        config(['shared-ui.portal' => true]);
        $declared = collect(Workbench::declaredPermissions())->keyBy('key');
        $this->assertSame(['orders.view', 'portal.manage_branding', 'portal.manage_customers'], $declared->keys()->all());
        $this->assertFalse($declared['portal.manage_branding']['default']);
    }
}
