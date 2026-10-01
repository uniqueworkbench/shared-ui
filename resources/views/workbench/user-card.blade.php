{{-- The signed-in user, at the top of the drawer. --}}
@auth
    <div class="flex items-center gap-3 px-5 py-4 border-b border-white/10">
        @include('shared-ui::workbench.avatar')
        <div class="min-w-0">
            <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
            @if (auth()->user()->email)
                <p class="text-xs text-zinc-400 truncate">{{ auth()->user()->email }}</p>
            @endif
        </div>
    </div>
@endauth
