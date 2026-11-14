<?php

namespace UniqueWorkbench\SharedUi\Tests;

/**
 * A portal's Customers and Branding menu items are added at boot. `config:cache` runs the providers first, so the
 * cached config already holds them: they must not be added again on every request after it.
 */
class PortalNavigationTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);
        $app['config']->set('shared-ui.portal', true);
        // As a cached config holds it: the app's items with the portal's already in
        $app['config']->set('shared-ui.navigation', [
            ['label' => 'Dashboard', 'route' => 'dashboard'],
            ['label' => 'Customers', 'route' => 'portal.customers', 'can' => 'portal-owner'],
            ['label' => 'Branding', 'route' => 'portal.branding', 'can' => 'portal-owner'],
        ]);
    }

    public function test_the_portal_items_are_in_the_menu_once_even_from_a_cached_config(): void
    {
        $routes = array_column(config('shared-ui.navigation'), 'route');

        $this->assertSame(['dashboard', 'portal.customers', 'portal.branding'], $routes);
    }
}
