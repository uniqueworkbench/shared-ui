# Shared UI Package

This package contains shared Blade layouts/components, plus cross-app backend
infrastructure (hard-abend error tracking, the Feature API key check) used across all Unique Workbench apps.

## What lives here
- `<x-workbench-layout>` (`resources/views/components/workbench-layout.blade.php`
  + `resources/views/workbench/`) — the app shell every Unique Workbench app
  uses (the account app via its `layouts/app.blade.php` override, client apps
  directly). Two layouts, picked by `config('shared-ui.layout')`:
  - **desktop** — a dark top bar (logo, organization, bell, user menu) over a dark
    left sidebar (menu, Help & Support, Return to Portal, version at the very
    bottom); the page title (`header`) heads the content. Below `lg` the sidebar
    turns into a drawer (hamburger in the top bar) that also holds the user,
    their links and Log Out.
  - **phone** — dark app-style header (hamburger + centred title + bell); the
    drawer holds the user, organization, menu, their links, Log Out and the
    version. Content is `max-w-lg`.
  - Configured per app in its `config/shared-ui.php` (also `organization`, `notifications`,
    `help` — see the package config): `navigation` (label,
    route, icon, optional `active`/`except` routeIs() patterns, `can` gate,
    `badge` `[Class::class, 'staticMethod']` count; an item with `children`
    and no route is a collapsible group header, open while a child is active;
    while collapsed it shows its children's badges added up, expanded each child shows its own),
    `user_menu` (extra user
    links), `portal_url` (null hides Return to Portal). No closures — apps
    cache config on deploy. Items with unknown routes are skipped.
  - Slots `header`, `breadcrumb`; props `title`, `padded` (false = pages bring
    their own spacing); `@push('banners')` renders under the header. The
    LOCAL/BUILD/BETA badge shows next to the wordmark outside production, and
    an empty `#main-nav-filler` sits in the header for native app wrappers
    whose JS sizes it to the status bar.
- **The Unique Workbench look** (every app, set here — never restyle it per app):
  - Chrome: a dark (`zinc-900`) top bar across the page — logo + UNIQUE WORKBENCH wordmark,
    the organization the user is working in with Switch Organization (`organization`), the
    notification bell (`notifications`), the user's red initials avatar and menu — over a dark
    sidebar: menu items in white, the active one filled brand red; a client app's name heads its
    menu; Help & Support (`help`), Return to Portal and the version at the bottom;
    `@push('sidebar')` adds a note under the menu. Pages sit on a light `zinc-100` canvas; the
    `header` slot is the page title (3xl bold).
  - Colour: `bt_primary` is the brand red (`#D03A3A` at 600) — primary buttons, links, the active
    menu item, focus rings, pills. Neutrals are Tailwind `zinc`. Font: Inter.
  - Page patterns, as components (see `/ui-kit`, registered outside production, which rebuilds
    the reference design from them): `x-callout` (pale red page intro: icon circle, title, text,
    link), `x-page-header` (2xl bold section title, description, `actions` slot for a search or
    buttons), `x-card` (white, thin `zinc-200` border, `rounded-md`; optional title/description/
    `aside`; `:padded="false"` for edge-to-edge tables), `x-icon-circle` (tinted circle: gray, red,
    green, purple, orange, amber, teal, blue), `x-list-group` + `x-list-row` (bordered rows: icon,
    title, subtitle, chevron; `bordered` for a stack of item cards; `leading`/`actions` slots),
    `x-pill`, `x-info-box`, and buttons: `x-primary-button` (filled red), `x-outline-button` (red
    outline, `href` makes it a link), `x-secondary-button` (white, grey border), `x-danger-button`
    (dark red). Buttons are sentence case, `text-sm font-semibold`, never uppercase.
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
- `src/FeatureApi/VerifyFeatureApiKey.php` — route middleware alias
  `feature-api`, for an app's **Feature API**: the endpoints the account app's
  App Features pages read the app's data from, server to server. Requires
  `X-Api-Key` to equal `config('shared-ui.feature_api_key')` (`FEATURE_API_KEY`
  in the app's .env; New App Setup writes it, and stores the same value as the
  app's Feature API Key in the account app). No key set = every request gets
  a JSON 401. The key lives in the package's config, so apps that publish
  their own `config/shared-ui.php` still get it (Laravel merges top-level keys).

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