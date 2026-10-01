{{--
    Desktop layout: a dark top bar across the page (logo, the organization, notifications, the user menu)
    over a dark left sidebar (menu, Help & Support, Return to Portal, version) and the page on a light
    grey canvas. Below lg the sidebar becomes a drawer (hamburger in the top bar) that also holds the
    user, their links and Log Out.
--}}
<div x-data="{ drawer: false }" @keydown.escape.window="drawer = false" class="min-h-screen flex flex-col">
    <!-- Top bar -->
    <header class="sticky top-0 z-30 bg-zinc-900 text-white" style="padding-top: env(safe-area-inset-top)">
        {{-- Sized by native app wrappers whose status bar overlaps the page (see the app's JS) --}}
        <div id="main-nav-filler"></div>
        <div class="h-[4.5rem] flex items-center">
            <div class="h-full flex items-center gap-2 px-3 sm:px-5 lg:w-72 lg:shrink-0 lg:px-6">
                <button type="button" @click="drawer = true" class="lg:hidden p-2 text-zinc-200 hover:text-white" aria-label="{{ __('Open menu') }}">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                @include('shared-ui::workbench.brand')
            </div>

            <div class="flex-1 min-w-0 h-full flex items-center gap-2 sm:gap-4 pr-3 sm:pr-5 lg:px-8">
                <div class="hidden sm:block min-w-0">
                    @include('shared-ui::workbench.organization')
                </div>
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
        <!-- Sidebar / drawer (the column keeps the dark background down long pages) -->
        <div class="lg:w-72 lg:shrink-0 lg:bg-zinc-900">
        <aside :class="drawer ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-40 w-72 max-w-[85%] -translate-x-full bg-zinc-900 text-white lg:border-t lg:border-white/10 flex flex-col transition-transform duration-200 ease-in-out lg:sticky lg:top-[4.5rem] lg:h-[calc(100vh-4.5rem)] lg:max-w-none lg:translate-x-0 lg:shrink-0"
               style="padding-top: env(safe-area-inset-top); padding-bottom: env(safe-area-inset-bottom)">
            <div class="lg:hidden flex items-center justify-between gap-3 px-5 h-16 shrink-0 border-b border-white/10">
                @include('shared-ui::workbench.brand')
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

            <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-1.5">
                @if (config('shared-ui.portal_url'))
                    {{-- A client app: its name heads its menu --}}
                    <p class="px-4 pb-2 text-xs font-semibold uppercase tracking-wider text-zinc-400">{{ config('app.name') }}</p>
                @endif
                @include('shared-ui::workbench.nav')
                @stack('sidebar')
            </nav>

            <div class="shrink-0 px-4 pt-3 pb-4 border-t border-white/10 space-y-1">
                @include('shared-ui::workbench.help-link')
                {{-- Desktop: the user menu is in the top bar, so just the portal link here --}}
                <div class="hidden lg:block">
                    @if (config('shared-ui.portal_url'))
                        @include('shared-ui::workbench.portal-link')
                    @endif
                </div>
                @auth
                    <div class="lg:hidden space-y-1">
                        @include('shared-ui::workbench.user-links', ['style' => 'list'])
                    </div>
                @endauth
                @include('shared-ui::workbench.version')
            </div>
        </aside>
        </div>

        <!-- Drawer backdrop (small screens) -->
        <div x-show="drawer" x-cloak x-transition.opacity @click="drawer = false" class="fixed inset-0 z-30 bg-zinc-950/50 lg:hidden"></div>

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
