{{-- Phone layout: a dark app-style header (hamburger, centred title, notifications) with a dark drawer holding the user, the organization, Return to Workbench, menu, their links, Log Out and the version. --}}
<div x-data="{ drawer: false }" @keydown.escape.window="drawer = false" class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="sticky top-0 z-20 bg-zinc-900 text-white" style="padding-top: env(safe-area-inset-top)">
        {{-- Sized by native app wrappers whose status bar overlaps the page (see the app's JS) --}}
        <div id="main-nav-filler"></div>
        <div class="h-14 flex items-center gap-2 px-2">
            <button type="button" @click="drawer = true" class="p-3 text-zinc-200 hover:text-white" aria-label="{{ __('Open menu') }}">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
            <div class="flex-1 min-w-0 text-center font-semibold truncate">
                {{ $header ?? config('app.name') }}
            </div>
            <!-- Balances the hamburger so the title stays centred -->
            <div class="w-11 shrink-0 flex justify-center">@include('shared-ui::workbench.notifications')</div>
        </div>
    </header>

    <!-- Drawer -->
    <div x-show="drawer" x-cloak x-transition.opacity @click="drawer = false" class="fixed inset-0 z-40 bg-zinc-950/50"></div>
    <aside x-show="drawer" x-cloak
           x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
           x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
           class="fixed inset-y-0 left-0 z-50 w-72 max-w-[85%] bg-zinc-900 text-white shadow-xl flex flex-col"
           style="padding-top: env(safe-area-inset-top); padding-bottom: env(safe-area-inset-bottom)">
        <div class="flex items-center justify-between gap-3 px-5 h-16 shrink-0 border-b border-white/10">
            @include('shared-ui::workbench.brand', ['logoClass' => 'h-10'])
            <button type="button" @click="drawer = false" class="p-2 text-zinc-300 hover:text-white" aria-label="{{ __('Close menu') }}">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        @include('shared-ui::workbench.user-card')
        <div class="px-3 py-2 border-b border-white/10 empty:hidden">@include('shared-ui::workbench.organization')</div>

        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
            @if (config('shared-ui.portal_url'))
                <div class="pb-2 mb-2 border-b border-white/10">
                    @include('shared-ui::workbench.portal-link')
                </div>
            @endif
            @include('shared-ui::workbench.nav')
            @stack('sidebar')
        </nav>

        <div class="shrink-0 px-3 pt-3 pb-4 border-t border-white/10 space-y-1">
            @include('shared-ui::workbench.help-link')
            @auth
                @include('shared-ui::workbench.user-links', ['style' => 'list'])
            @endauth
            @include('shared-ui::workbench.version')
        </div>
    </aside>

    @stack('banners')

    @isset($breadcrumb)
        <div class="w-full max-w-lg mx-auto px-4 pt-4">{{ $breadcrumb }}</div>
    @endisset

    <main @class(['flex-1 w-full', 'max-w-lg mx-auto px-4 py-5' => $padded])>
        {{ $slot }}
    </main>

    @include('shared-ui::workbench.footer')
</div>
