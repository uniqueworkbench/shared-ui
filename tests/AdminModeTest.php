<?php

namespace UniqueWorkbench\SharedUi\Tests;

use UniqueWorkbench\SharedUi\Workbench\Workbench;

/**
 * A Super Admin (the account app's is_admin) is only eligible in a client app: Admin Mode is the session's own switch, off at every
 * sign-in, turned on and off from the user menu — as in the account app.
 */
class AdminModeTest extends TestCase
{
    private function workbench(bool $admin = true): Workbench
    {
        $workbench = new Workbench(app('session.store'));
        $workbench->set(Workbench::fromSsoUser(['organization_id' => 5, 'organization_role' => 'user', 'is_admin' => $admin, 'permissions' => []]));

        return $workbench;
    }

    public function test_being_a_super_admin_alone_does_not_make_them_an_admin_here(): void
    {
        $workbench = $this->workbench();

        $this->assertTrue($workbench->isAdminEligible());
        $this->assertFalse($workbench->adminMode());
        $this->assertFalse($workbench->isAdmin());
        // an ordinary member until they switch it on
        $this->assertFalse($workbench->isOwner());
        $this->assertFalse($workbench->can('anything.at.all'));
        $this->assertFalse($workbench->canEverywhere('projects.see_all'));
    }

    public function test_admin_mode_makes_them_an_admin_and_turning_it_off_takes_it_back(): void
    {
        $workbench = $this->workbench();

        $this->assertTrue($workbench->setAdminMode(true));
        $this->assertTrue($workbench->isAdmin());
        $this->assertTrue($workbench->isOwner());
        $this->assertTrue($workbench->can('anything.at.all'));
        $this->assertTrue($workbench->canEverywhere('projects.see_all'));

        $this->assertFalse($workbench->setAdminMode(false));
        $this->assertFalse($workbench->isAdmin());
        $this->assertFalse($workbench->isOwner());
    }

    public function test_someone_who_is_not_a_super_admin_cannot_turn_it_on(): void
    {
        $workbench = $this->workbench(admin: false);

        $this->assertFalse($workbench->setAdminMode(true));
        $this->assertFalse($workbench->adminMode());
        $this->assertFalse($workbench->isAdmin());
    }

    public function test_a_new_context_does_not_carry_it_and_forgetting_the_context_ends_it(): void
    {
        $workbench = $this->workbench();
        $workbench->setAdminMode(true);
        $workbench->forget();

        $this->assertFalse($workbench->adminMode());
    }

    public function test_the_user_menu_offers_the_switch_to_super_admins_only_and_shows_the_bar_when_on(): void
    {
        \Illuminate\Support\Facades\Route::post('/logout', fn () => '')->name('logout'); // the menu's Log Out
        workbench()->set(Workbench::fromSsoUser(['organization_id' => 5, 'organization_role' => 'user', 'is_admin' => true]));
        $menu = view('shared-ui::workbench.user-links', ['style' => 'dropdown'])->render();
        $this->assertStringContainsString('Enter Admin Mode', $menu);
        $this->assertStringNotContainsString('data-admin-mode-bar', view('shared-ui::workbench.mode-banners')->render());

        workbench()->setAdminMode(true);
        $this->assertStringContainsString('Exit Admin Mode', view('shared-ui::workbench.user-links', ['style' => 'dropdown'])->render());
        $this->assertStringContainsString('data-admin-mode-bar', view('shared-ui::workbench.mode-banners')->render());

        workbench()->set(Workbench::fromSsoUser(['organization_id' => 5, 'organization_role' => 'owner', 'is_admin' => false]));
        $this->assertStringNotContainsString('Admin Mode', view('shared-ui::workbench.user-links', ['style' => 'dropdown'])->render());
    }

    public function test_the_switch_is_a_route_apps_get(): void
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('workbench.admin-mode'));
    }
}
