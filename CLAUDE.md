# Shared UI Package

This package contains shared Blade layouts/components, plus cross-app backend
infrastructure (currently: hard-abend error tracking) used across all Unique Workbench apps.

## What lives here
- `<x-workbench-layout>` (`resources/views/components/workbench-layout.blade.php`
  + `resources/views/workbench/`) — the app shell every Unique Workbench app
  uses (the account app via its `layouts/app.blade.php` override, client apps
  directly). Two layouts, picked by `config('shared-ui.layout')`:
  - **desktop** — left sidebar (menu, Return to Portal, version at the very
    bottom) with a top bar (page title, user menu). Below `lg` it becomes a
    simple header with a hamburger; the sidebar turns into a drawer that also
    holds the user, their links and Log Out.
  - **phone** — app-style header (hamburger + centred title); the drawer holds
    the user, menu, their links, Log Out and the version. Content is `max-w-lg`.
  - Configured per app in its `config/shared-ui.php`: `navigation` (label,
    route, icon, optional `active`/`except` routeIs() patterns, `can` gate,
    `badge` `[Class::class, 'staticMethod']` count; an item with `children`
    and no route is a collapsible group header, open while a child is active;
    while collapsed it shows its children's badges added up, expanded each child shows its own),
    `user_menu` (extra user
    links), `portal_url` (null hides Return to Portal). No closures — apps
    cache config on deploy. Items with unknown routes are skipped.
  - Slots `header`, `breadcrumb`; props `title`, `padded` (false = pages bring
    their own spacing); `@push('banners')` renders under the header. The
    LOCAL/BUILD/BETA badge shows next to the app name outside production, and
    an empty `#main-nav-filler` sits in the header for native app wrappers
    whose JS sizes it to the status bar.
- `resources/views/layouts/` — app, guest, navigation, public layouts (the
  original top-nav shell; kept for apps that haven't moved to the workbench layout)
- `resources/views/components/` — all shared Blade components
- `resources/css/app.css` — base styles, FontAwesome, table utilities
- `tailwind.config.js` — shared color palette and fonts (no imports — apps handle those)
- `src/ErrorTracking/` — captures uncaught exceptions to an `error_logs` table,
  emails an alert, and renders formatted error pages (with a back button) for
  500/403/404. `HardAbendRecorder::register($exceptions)` is called from each
  app's `bootstrap/app.php` inside `withExceptions()` — nothing else to wire
  up per app. Config: `config/shared-ui.php` (`error_alert_email`,
  overridable per app via `ERROR_ALERT_EMAIL`). Migration auto-loads via
  `loadMigrationsFrom` — no publish step needed, just `php artisan migrate` in
  the consuming app.
  - **500** (genuine unhandled crash): recorded + emailed (as before), custom
    page shown — except when `APP_DEBUG=true`, where Laravel's normal debug
    trace still shows so local dev isn't hampered.
  - **403**: recorded + emailed (Laravel's default reportable() hook never
    sees 403/404/etc. — they're Symfony `HttpException` subclasses, which are
    in `Handler::$internalDontReport` — so this is recorded explicitly in the
    `render()` callback instead), custom page shown. Exception: an anonymous
    (no logged-in user) 403 from Laravel's storage-serving route
    (`storage.*` named route) is NOT recorded/emailed — that route 403s any
    unsigned request before checking file existence, so bots probing for
    exposed `.env`/`.git` files trigger it constantly, and only on
    non-production environments (prod gets a plain 404 there instead, per
    Laravel's own `ServeFile::isProduction` check — so this noise shows up on
    dev/stage specifically). An authenticated user hitting it is still a real
    signal and is recorded/emailed as usual.
  - **404**: custom page always shown, but only recorded + emailed if the
    request has a logged-in user. Anonymous 404 traffic (bots, scanners, dead
    links) is high-volume noise; a 404 hit by an authenticated user usually
    means a real broken internal link, so that's worth flagging.
  - JSON/API requests (`Accept: application/json` or an `api/*` path) are
    left alone — they keep whatever JSON error response the app already
    produces; only browser page loads get the formatted views.
  - Views: `resources/views/errors/_page.blade.php` (shared shell, no Vite
    dependency — self-contained inline CSS so it still renders even if the
    app's asset build is broken) plus thin `404.blade.php`, `403.blade.php`,
    `hard-abend.blade.php` wrappers.

## Rules
- No app-*specific* logic here — logic must be generic enough to apply to every
  consuming app (not "how account.uniqueworkbench.com handles X")
- No imports in tailwind.config.js (apps handle their own node_modules)
- Changes here affect ALL apps — test in account/ after any change
- After view/CSS changes, apps may need: php artisan view:clear
- After backend changes (migrations, config), apps need: composer update
  uniqueworkbench/shared-ui && php artisan migrate
- **Releasing:** apps require `"*"`, so they get the latest `vX.Y.Z` tag. Bump
  `"version"` in composer.json to match *before* tagging — Composer ignores a
  tag whose composer.json version differs. Each app's `push.sh` checks GitHub
  for the latest tag and updates its composer.lock when there's a newer one.