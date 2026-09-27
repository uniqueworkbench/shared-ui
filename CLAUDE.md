# Shared UI Package

This package contains shared Blade layouts/components, plus cross-app backend
infrastructure (currently: hard-abend error tracking) used across all Unique Workbench apps.

## What lives here
- `resources/views/layouts/` — app, guest, navigation, public layouts
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