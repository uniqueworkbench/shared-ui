{{-- The signed-in user, for the top of the drawer. --}}
@auth
    <div class="flex items-center gap-3 px-4 py-4 border-b border-gray-200">
        @include('shared-ui::workbench.avatar')
        <div class="min-w-0">
            <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name }}</p>
            @if (auth()->user()->email)
                <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
            @endif
        </div>
    </div>
@endauth
