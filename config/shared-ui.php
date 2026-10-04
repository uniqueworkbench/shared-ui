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
    | Feature API Key
    |--------------------------------------------------------------------------
    |
    | The key the account app sends (X-Api-Key) when its App Features pages
    | read this app's data — routes behind the `feature-api` middleware.
    | New App Setup writes it to each environment's .env and to the app's
    | Application (Admin → Applications → Feature API Key). Unset = refused.
    |
    */

    'feature_api_key' => env('FEATURE_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Google Maps (address lookup)
    |--------------------------------------------------------------------------
    |
    | A browser key for the Maps JavaScript API with the Places API (New)
    | enabled — restrict it to the apps' domains in Google Cloud. With it,
    | <x-address-lookup> / <x-address-fields> look addresses up in Google;
    | unset, they're plain fields.
    |
    */

    'google_maps_key' => env('GOOGLE_MAPS_API_KEY'),

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
    |              A group (label, icon, optional can, children: [items], no route)
    |              is a header that expands to its children; it starts open while
    |              a child is active and is hidden when no child is visible.
    | user_menu:   extra links in the user menu (label, route, icon), above
    |              Return to Workbench and Log Out.
    | portal_url:  where "Return to Workbench" (the top of the menu) goes — the
    |              Unique Workbench account app. null hides it (the account app
    |              is the portal).
    | app_header_logo / app_icon: the app's own brand files (paths under public/,
    |              e.g. 'images/app_header_logo.png' — wide, light on dark, name
    |              included — and 'images/app_icon.png', square), used when no header
    |              logo / icon was uploaded in the account app (Admin → Applications,
    |              sent at sign-in). Shown at the top left: the header logo, else the
    |              icon with the app name, else the Unique Workbench logo. The
    |              uploaded favicon likewise replaces app.favicon / favicon.ico.
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

    'app_header_logo' => null,

    'app_icon' => null,

    /*
    | The top bar and the bottom of the menu (all optional; null hides them):
    |
    | organization:  [Class::class, 'staticMethod'] returning null or
    |                ['name' => …, 'switch_url' => …, 'links' => [['label', 'url', 'icon'], …]]
    |                — the organization the user is working in, with Switch Organization.
    | notifications: ['count' => [Class::class, 'staticMethod'], 'route' => route name] — the bell.
    | help:          ['label' => 'Help & Support', 'route' => route name] (or 'url' => …), optional 'can' gate.
    */

    'organization' => null,

    'notifications' => null,

    'help' => null,

];
