{{--
    Desktop layout: a dark top bar across the page (logo, the organization, notifications, the user menu)
    over a dark left sidebar (Return to Workbench, menu, Help & Support, version) and the page on a light
    grey canvas. Below lg the sidebar becomes a drawer (hamburger in the top bar) that also holds the
    user, their links and Log Out. With nav false there's no sidebar or drawer (the user menu stays in the top bar).

    From lg up the sidebar collapses to its icons (the toggle above the version). The choice is a cookie on the
    apps' shared parent domain (account.uniqueworkbench.com → uniqueworkbench.com), so every app opens the same
    way; it's applied to <html> (.uw-nav-collapsed) before the sidebar renders, so it never flashes open. The
    styles are inline here (apps keep their own app.css): elements opt in with data-nav-* attributes.
--}}
<script>
    window.uwNav = {
        cookie: 'uw_nav_collapsed',
        collapsed() {
            return new RegExp('(?:^|;\\s*)' + this.cookie + '=1(?:;|$)').test(document.cookie);
        },
        set(collapsed) {
            const value = this.cookie + '=' + (collapsed ? 1 : 0) + '; path=/; max-age=31536000; SameSite=Lax';
            const host = location.hostname;
            const parts = host.split('.');
            if (parts.length > 2 && ! /^[\d.]+$/.test(host)) {
                // Shared by every app: drop a host-only copy so it can't shadow the shared one
                document.cookie = this.cookie + '=; path=/; max-age=0';
                document.cookie = value + '; domain=.' + parts.slice(-2).join('.');
            }
            if (this.collapsed() !== collapsed) {
                document.cookie = value; // no parent domain (localhost, an IP) or the browser refused it
            }
            document.documentElement.classList.toggle('uw-nav-collapsed', collapsed);
        },
    };
    document.documentElement.classList.toggle('uw-nav-collapsed', uwNav.collapsed());
</script>
<style>
    @media (min-width: 1024px) {
        .uw-nav-collapsed [data-nav-column], .uw-nav-collapsed [data-nav-aside] { width: 4rem; }
        .uw-nav-collapsed [data-nav-pad] { padding-left: 0.5rem; padding-right: 0.5rem; }
        .uw-nav-collapsed [data-nav-label] { display: none; }
        .uw-nav-collapsed [data-nav-link] { position: relative; justify-content: center; padding-left: 0; padding-right: 0; }
        .uw-nav-collapsed [data-nav-dot] { display: inline-block; }
        .uw-nav-collapsed [data-nav-badge] {
            position: absolute; top: 0.125rem; right: 0.125rem; min-width: 1rem; padding: 0.125rem 0.25rem; font-size: 0.6rem;
        }
        .uw-nav-collapsed [data-nav-heading] { margin: 0.75rem 0.25rem 0.25rem; padding-top: 0.5rem; border-top: 1px solid rgb(255 255 255 / 0.1); }
        .uw-nav-collapsed [data-nav-heading] button { justify-content: center; padding: 0; }
        .uw-nav-collapsed [data-app-version] { padding-left: 0; padding-right: 0; text-align: center; font-size: 0.6rem; }
    }
</style>
<div x-data="{ drawer: false, navCollapsed: uwNav.collapsed() }" @keydown.escape.window="drawer = false" class="min-h-screen flex flex-col">
    <!-- Top bar -->
    <header class="sticky top-0 z-30 bg-zinc-900 text-white" style="padding-top: env(safe-area-inset-top)">
        {{-- Sized by native app wrappers whose status bar overlaps the page (see the app's JS) --}}
        <div id="main-nav-filler"></div>
        <div class="h-16 flex items-center">
            <div class="h-full flex items-center gap-2 px-3 sm:px-5 lg:w-60 lg:shrink-0 lg:px-6">
                @if ($nav)
                    <button type="button" @click="drawer = true" class="lg:hidden p-2 text-zinc-200 hover:text-white" aria-label="{{ __('Open menu') }}">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                @endif
                @include('shared-ui::workbench.brand', ['logoClass' => 'h-12'])
            </div>

            <div class="flex-1 min-w-0 h-full flex items-center gap-2 sm:gap-4 pr-3 sm:pr-5 lg:px-8">
                <div class="hidden sm:block min-w-0">
                    @include('shared-ui::workbench.organization')
                </div>
                {{-- The app's own top bar additions, after the organization (e.g. a search) — desktop layout only --}}
                @stack('topbar')
                <div class="flex-1"></div>
                @include('shared-ui::workbench.notifications')
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button type="button" class="flex items-center gap-3 p-1 sm:pr-2 rounded-md hover:bg-white/5">
                                @include('shared-ui::workbench.avatar')
                                <span class="hidden lg:block max-w-[12rem] truncate text-base font-semibold text-white">{{ auth()->user()->name }}</span>
                                <i class="hidden lg:block fa-solid fa-chevron-down text-xs text-zinc-300"></i>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            @include('shared-ui::workbench.user-links', ['style' => 'dropdown'])
                        </x-slot>
                    </x-dropdown>
                @endauth
            </div>
        </div>
    </header>

    <div class="flex-1 flex">
        @if ($nav)
        <!-- Sidebar / drawer (the column keeps the dark background down long pages) -->
        <div data-nav-column class="lg:w-60 lg:shrink-0 lg:bg-zinc-900">
        <aside data-nav-aside :class="drawer ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-40 w-64 max-w-[85%] -translate-x-full bg-zinc-900 text-white lg:border-t lg:border-white/10 flex flex-col transition-transform duration-200 ease-in-out lg:sticky lg:top-16 lg:h-[calc(100vh-4rem)] lg:max-w-none lg:translate-x-0 lg:shrink-0"
               style="padding-top: env(safe-area-inset-top); padding-bottom: env(safe-area-inset-bottom)">
            <div class="lg:hidden flex items-center justify-between gap-3 px-5 h-16 shrink-0 border-b border-white/10">
                @include('shared-ui::workbench.brand', ['logoClass' => 'h-10'])
                <button type="button" @click="drawer = false" class="p-2 text-zinc-300 hover:text-white" aria-label="{{ __('Close menu') }}">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="lg:hidden">
                @include('shared-ui::workbench.user-card')
                <div class="sm:hidden px-3 py-2 border-b border-white/10">
                    @include('shared-ui::workbench.organization')
                </div>
            </div>

            <nav data-nav-pad class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
                @if (config('shared-ui.portal_url'))
                    {{-- A client app: Return to Workbench heads its menu --}}
                    <div class="pb-2 mb-2 border-b border-white/10">
                        @include('shared-ui::workbench.portal-link')
                    </div>
                @endif
                @include('shared-ui::workbench.nav')
                <div data-nav-label>@stack('sidebar')</div>
            </nav>

            <div data-nav-pad class="shrink-0 px-3 pt-3 pb-4 border-t border-white/10 space-y-1">
                @include('shared-ui::workbench.help-link')
                {{-- Desktop: the user menu is in the top bar; the drawer holds it here --}}
                @auth
                    <div class="lg:hidden space-y-1">
                        @include('shared-ui::workbench.user-links', ['style' => 'list'])
                    </div>
                @endauth
                {{-- Collapse to icons / expand (desktop only: the drawer is always full) --}}
                <button type="button" data-nav-link data-nav-toggle
                        @click="navCollapsed = ! navCollapsed; uwNav.set(navCollapsed)"
                        :title="navCollapsed ? @js(__('Expand menu')) : @js(__('Collapse menu'))"
                        :aria-label="navCollapsed ? @js(__('Expand menu')) : @js(__('Collapse menu'))"
                        class="hidden lg:flex w-full items-center gap-3 px-3 py-2 rounded-md text-[0.8rem] font-medium text-zinc-400 hover:bg-white/5 hover:text-white">
                    <i class="fa-solid w-4 text-center text-[0.9rem]" :class="navCollapsed ? 'fa-angles-right' : 'fa-angles-left'"></i>
                    <span data-nav-label>{{ __('Collapse menu') }}</span>
                </button>
                @include('shared-ui::workbench.version')
            </div>
        </aside>
        </div>

        <!-- Drawer backdrop (small screens) -->
        <div x-show="drawer" x-cloak x-transition.opacity @click="drawer = false" class="fixed inset-0 z-30 bg-zinc-950/50 lg:hidden"></div>
        @endif

        <div class="flex-1 min-w-0 flex flex-col">
            @stack('banners')

            <main @class(['flex-1', 'px-4 py-6 sm:px-6 lg:px-8 lg:py-8' => $padded])>
                @isset($breadcrumb)
                    <div @class(['px-4 sm:px-6 lg:px-8 pt-6' => ! $padded])>{{ $breadcrumb }}</div>
                @endisset
                @if (isset($header) && trim((string) $header) !== '')
                    {{-- The page title; a heading the page puts in the slot gets the same style --}}
                    <div @class(['mb-6 text-3xl font-bold tracking-tight text-zinc-900 [&_h1]:text-3xl [&_h1]:font-bold [&_h1]:text-zinc-900 [&_h2]:text-3xl [&_h2]:font-bold [&_h2]:tracking-tight [&_h2]:text-zinc-900', 'px-4 sm:px-6 lg:px-8 pt-6' => ! $padded]) data-page-title>{{ $header }}</div>
                @endif
                {{ $slot }}
            </main>

            @include('shared-ui::workbench.footer')
        </div>
    </div>
</div>
