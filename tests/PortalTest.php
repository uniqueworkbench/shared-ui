<?php

namespace UniqueWorkbench\SharedUi\Tests;

use UniqueWorkbench\SharedUi\Workbench\Workbench;

class PortalTest extends TestCase
{
    public function test_the_portal_context_from_the_account_app_says_what_the_organization_is_to_it(): void
    {
        $context = Workbench::fromSsoUser([
            'organization_id' => 5, 'organization_role' => 'owner',
            'portal' => ['role' => 'owner', 'branding' => ['primary_color' => '#112233']],
        ]);
        $this->assertSame('owner', $context['portal']['role']);

        $workbench = new Workbench(app('session.store'));
        $workbench->set($context);
        $this->assertTrue($workbench->isPortalOwner());
        $this->assertFalse($workbench->isPortalCustomer());
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
}
