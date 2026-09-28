{{-- Phone layout: app-style header with a hamburger drawer holding the user, menu, their links, Log Out and the version. --}}
<div x-data="{ drawer: false }" @keydown.escape.window="drawer = false" class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="sticky top-0 z-20 bg-white border-b border-gray-200" style="padding-top: env(safe-area-inset-top)">
        {{-- Sized by native app wrappers whose status bar overlaps the page (see the app's JS) --}}
        <div id="main-nav-filler"></div>
        <div class="h-14 flex items-center gap-2 px-2">
            <button type="button" @click="drawer = true" class="p-3 text-gray-600 hover:text-gray-800" aria-label="{{ __('Open menu') }}">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
            <div class="flex-1 min-w-0 text-center font-semibold text-gray-800 truncate">
                {{ $header ?? config('app.name') }}
            </div>
            <!-- Balances the hamburger so the title stays centred -->
            <span class="w-11 shrink-0"></span>
        </div>
    </header>

    <!-- Drawer -->
    <div x-show="drawer" x-cloak x-transition.opacity @click="drawer = false" class="fixed inset-0 z-40 bg-gray-900/40"></div>
    <aside x-show="drawer" x-cloak
           x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
           x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
           class="fixed inset-y-0 left-0 z-50 w-72 max-w-[85%] bg-white shadow-xl flex flex-col"
           style="padding-top: env(safe-area-inset-top); padding-bottom: env(safe-area-inset-bottom)">
        <div class="flex items-center justify-between gap-3 px-4 h-14 shrink-0 border-b border-gray-200">
            @include('shared-ui::workbench.brand')
            <button type="button" @click="drawer = false" class="p-2 text-gray-400 hover:text-gray-600" aria-label="{{ __('Close menu') }}">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        @include('shared-ui::workbench.user-card')

        <nav class="flex-1 overflow-y-auto p-3 space-y-1">
            @include('shared-ui::workbench.nav')
        </nav>

        <div class="shrink-0 p-3 border-t border-gray-200 space-y-1">
            @auth
                @include('shared-ui::workbench.user-links', ['style' => 'list'])
            @endauth
            @include('shared-ui::workbench.version')
        </div>
    </aside>

    @stack('banners')

    @isset($breadcrumb)
        <div class="w-full max-w-lg mx-auto px-4 pt-3">{{ $breadcrumb }}</div>
    @endisset

    <main @class(['flex-1 w-full', 'max-w-lg mx-auto px-4 py-4' => $padded])>
        {{ $slot }}
    </main>

    @include('shared-ui::workbench.footer')
</div>
