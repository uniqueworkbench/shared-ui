<?php

namespace UniqueWorkbench\SharedUi\Workbench;

/**
 * The organization in the top bar (config shared-ui.organization): the one
 * the user is working in, from the session. Switch Organization goes back
 * through the account app's chooser and returns signed in to the new one.
 */
class OrganizationMenu
{
    public static function current(): ?array
    {
        $workbench = workbench();
        if (! $workbench->organizationName()) {
            return null;
        }

        return [
            'name' => $workbench->organizationName(),
            'switch_url' => count($workbench->get('organizations', [])) > 1 ? route('sso.redirect', ['switch' => 1]) : null,
            'links' => [],
        ];
    }
}
