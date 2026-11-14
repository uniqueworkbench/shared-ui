<?php

namespace UniqueWorkbench\SharedUi\Tests;

use UniqueWorkbench\SharedUi\Workbench\Workbench;

class PortalTest extends TestCase
{
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
