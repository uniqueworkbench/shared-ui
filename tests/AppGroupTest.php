<?php

namespace UniqueWorkbench\SharedUi\Tests;

use Illuminate\Support\Facades\Route;
use UniqueWorkbench\SharedUi\Workbench\Workbench;

class AppGroupTest extends TestCase
{
    private function group(string $role, array $members = []): array
    {
        return [
            'package' => ['id' => 3, 'name' => 'Trigonon'],
            'role' => $role,
            'home' => ['id' => 1, 'key' => 'dashboard', 'name' => 'Trigonon Dashboard', 'url' => 'https://account.test/oauth/home', 'icon_url' => null],
            'members' => $members,
        ];
    }

    private function as(?array $group): void
    {
        workbench()->set(Workbench::fromSsoUser(['organization_id' => 5, 'organization_role' => 'owner', 'app_group' => $group]));
    }

    public function test_an_app_in_no_group_returns_to_the_workbench(): void
    {
        config(['shared-ui.portal_url' => 'https://account.test/dashboard', 'shared-ui.portal' => false]);
        $this->as(null);

        $this->assertNull(workbench()->appGroup());
        $this->assertFalse(workbench()->isGroupMember());
        $this->assertSame(['label' => 'Return to Workbench', 'url' => 'https://account.test/dashboard', 'group' => false], workbench()->returnLink());
        $this->assertSame([], workbench()->groupMenuItems());
    }

    public function test_a_member_app_returns_to_the_home_app_and_keeps_the_workbench_in_the_user_menu(): void
    {
        config(['shared-ui.portal_url' => 'https://account.test/dashboard', 'shared-ui.portal' => false]);
        $this->as($this->group('member'));

        $this->assertTrue(workbench()->isGroupMember());
        $this->assertSame(['label' => 'Return to Trigonon Dashboard', 'url' => 'https://account.test/oauth/home', 'group' => true], workbench()->returnLink());

        $link = view('shared-ui::workbench.portal-link')->render();
        $this->assertStringContainsString('Return to Trigonon Dashboard', $link);
        $this->assertStringContainsString('https://account.test/oauth/home', $link);
        $this->assertStringNotContainsString('Return to Workbench', $link);

        Route::post('/logout', fn () => '')->name('logout');
        $menu = view('shared-ui::workbench.user-links', ['style' => 'dropdown'])->render();
        $this->assertStringContainsString('Return to Trigonon Dashboard', $menu);
        $this->assertStringContainsString('Unique Workbench', $menu);
    }

    public function test_the_home_app_keeps_return_to_workbench_and_offers_its_members(): void
    {
        config(['shared-ui.portal_url' => 'https://account.test/dashboard', 'shared-ui.portal' => false]);
        $members = [['id' => 2, 'key' => 'measure', 'name' => 'Measure', 'url' => 'https://account.test/oauth/measure', 'icon_url' => null]];
        $this->as($this->group('home', $members));

        $this->assertTrue(workbench()->isGroupHome());
        $this->assertSame('Return to Workbench', workbench()->returnLink()['label']);
        $this->assertSame('Measure', workbench()->groupMenuItems()[1]['label']);
        $this->assertSame(['heading' => 'Trigonon'], workbench()->groupMenuItems()[0]);

        $html = view('shared-ui::components.package-apps')->render();
        $this->assertStringContainsString('data-package-app="measure"', $html);
        $this->assertStringContainsString('https://account.test/oauth/measure', $html);
    }

    public function test_the_tiles_render_nothing_outside_a_home_app(): void
    {
        $this->as($this->group('member', [['id' => 2, 'key' => 'measure', 'name' => 'Measure', 'url' => 'https://x.test', 'icon_url' => null]]));

        $this->assertSame('', trim(view('shared-ui::components.package-apps')->render()));
    }

    public function test_older_account_apps_send_no_group(): void
    {
        workbench()->set(Workbench::fromSsoUser(['organization_id' => 5, 'organization_role' => 'owner']));

        $this->assertNull(workbench()->appGroup());
        $this->assertFalse(workbench()->isGroupHome());
    }
}
