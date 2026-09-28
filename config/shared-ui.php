<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Hard Abend Alert Recipient
    |--------------------------------------------------------------------------
    |
    | Uncaught exceptions ("hard abends") are recorded to the error_logs table
    | and an alert email is sent here. Override per-app via ERROR_ALERT_EMAIL.
    | Set to null to disable email alerts (rows are still recorded).
    |
    */

    'error_alert_email' => env('ERROR_ALERT_EMAIL', 'admin@uniqueworkbench.com'),

    /*
    |--------------------------------------------------------------------------
    | Client App Layout (<x-workbench-layout>)
    |--------------------------------------------------------------------------
    |
    | layout:      "desktop" — left sidebar navigation (a drawer on small screens)
    |              "phone"   — app-style header with a hamburger drawer only
    | navigation:  the app's menu, in order. Each item: label, route (a route
    |              name), icon (Font Awesome classes), and optionally active /
    |              except (routeIs() patterns), can (a gate ability) and badge
    |              ([Class::class, 'staticMethod'] returning a count). Items whose
    |              route doesn't exist are skipped. No closures — config is cached.
    | user_menu:   extra links in the user menu (label, route, icon), above
    |              Return to Portal and Log Out.
    | portal_url:  where "Return to Portal" goes — the Unique Workbench account
    |              app. null hides it (the account app is the portal).
    |
    | Apps override these in their own config/shared-ui.php.
    |
    */

    'layout' => env('UI_LAYOUT', 'desktop'),

    'navigation' => [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'fa-solid fa-house'],
    ],

    'user_menu' => [],

    'portal_url' => env('SSO_URL', 'https://account.uniqueworkbench.com'),

];
