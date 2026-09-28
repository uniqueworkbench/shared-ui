{{--
    Desktop layout. lg and up: left sidebar (menu, portal link, version) and a top bar with the
    page title and user menu. Below lg: a simple header (hamburger + title); the sidebar becomes a
    drawer that also holds the user, their links and Log Out.
--}}
<div x-data="{ drawer: false }" @keydown.escape.window="drawer = false" class="min-h-screen lg:flex">
    <!-- Sidebar / drawer -->
    <aside :class="drawer ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-40 w-64 max-w-[85%] -translate-x-full bg-white border-r border-gray-200 flex flex-col transition-transform duration-200 ease-in-out lg:sticky lg:top-0 lg:h-screen lg:max-w-none lg:translate-x-0 lg:shrink-0"
           style="padding-top: env(safe-area-inset-top); padding-bottom: env(safe-area-inset-bottom)">
        <div class="h-16 shrink-0 flex items-center justify-between gap-3 px-4 border-b border-gray-200">
            @include('shared-ui::workbench.brand')
            <button type="button" @click="drawer = false" class="lg:hidden p-2 text-gray-400 hover:text-gray-600" aria-label="{{ __('Close menu') }}">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="lg:hidden">
            @include('shared-ui::workbench.user-card')
        </div>

        <nav class="flex-1 overflow-y-auto p-3 space-y-1">
            @include('shared-ui::workbench.nav')
        </nav>

        <div class="shrink-0 p-3 border-t border-gray-200 space-y-1">
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

    <!-- Drawer backdrop (small screens) -->
    <div x-show="drawer" x-cloak x-transition.opacity @click="drawer = false" class="fixed inset-0 z-30 bg-gray-900/40 lg:hidden"></div>

    <div class="flex-1 min-w-0 flex flex-col min-h-screen">
        <!-- Top bar -->
        <header class="sticky top-0 z-20 bg-white border-b border-gray-200" style="padding-top: env(safe-area-inset-top)">
            {{-- Sized by native app wrappers whose status bar overlaps the page (see the app's JS) --}}
            <div id="main-nav-filler"></div>
            <div class="h-16 flex items-center gap-3 px-4 sm:px-6">
                <button type="button" @click="drawer = true" class="lg:hidden -ml-2 p-2 text-gray-500 hover:text-gray-700" aria-label="{{ __('Open menu') }}">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>

                <div class="flex-1 min-w-0 font-semibold text-lg text-gray-800 truncate">
                    {{ $header ?? '' }}
                </div>

                @auth
                    <div class="hidden lg:block">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button type="button" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 rounded-md">
                                    @include('shared-ui::workbench.avatar')
                                    <span class="max-w-[12rem] truncate">{{ auth()->user()->name }}</span>
                                    <i class="fa-solid fa-chevron-down text-xs"></i>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                @include('shared-ui::workbench.user-links', ['style' => 'dropdown'])
                            </x-slot>
                        </x-dropdown>
                    </div>
                @endauth
            </div>
        </header>

        @stack('banners')

        @isset($breadcrumb)
            <div class="px-4 sm:px-6 lg:px-8 pt-3">{{ $breadcrumb }}</div>
        @endisset

        <main @class(['flex-1', 'px-4 py-6 sm:px-6 lg:px-8' => $padded])>
            {{ $slot }}
        </main>

        @include('shared-ui::workbench.footer')
    </div>
</div>
