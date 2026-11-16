{{--
    The signed-in user's links: config('shared-ui.user_menu') items (label, route, icon),
    Return to Workbench in the dropdown (unless portal_url is null — e.g. in the portal itself; the drawer
    has it at the top of the menu) and Log Out.
    $style: "dropdown" (desktop user menu) or "list" (drawer).
--}}
@php $style ??= 'list'; @endphp
@foreach (config('shared-ui.user_menu', []) as $link)
    @continue(! \Illuminate\Support\Facades\Route::has($link['route']))
    @if ($style === 'dropdown')
        <x-dropdown-link :href="route($link['route'])">
            <i class="{{ $link['icon'] ?? 'fa-solid fa-circle' }} w-4 mr-2 text-center"></i>{{ __($link['label']) }}
        </x-dropdown-link>
    @else
        <a href="{{ route($link['route']) }}" class="flex items-center gap-3 px-3 py-2 rounded-md text-[0.8rem] font-medium text-zinc-200 hover:bg-white/5 hover:text-white">
            <i class="{{ $link['icon'] ?? 'fa-solid fa-circle' }} w-4 text-center text-[0.9rem]"></i>{{ __($link['label']) }}
        </a>
    @endif
@endforeach

{{-- Admin Mode, for a Super Admin (the account app's is_admin): off at every sign-in, switched here like in the account app --}}
@if (function_exists('workbench') && workbench()->isAdminEligible() && \Illuminate\Support\Facades\Route::has('workbench.admin-mode'))
    @php $adminOn = workbench()->adminMode(); @endphp
    <form method="POST" action="{{ route('workbench.admin-mode') }}" data-admin-mode-toggle>
        @csrf
        <input type="hidden" name="on" value="{{ $adminOn ? 0 : 1 }}">
        @if ($style === 'dropdown')
            <x-dropdown-link :href="route('workbench.admin-mode')" onclick="event.preventDefault(); this.closest('form').requestSubmit();">
                <i class="fa-solid fa-user-shield w-4 mr-2 text-center"></i>{{ $adminOn ? __('Exit Admin Mode') : __('Enter Admin Mode') }}
            </x-dropdown-link>
        @else
            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-md text-[0.8rem] font-medium text-zinc-200 hover:bg-white/5 hover:text-white">
                <i class="fa-solid fa-user-shield w-4 text-center text-[0.9rem]"></i>{{ $adminOn ? __('Exit Admin Mode') : __('Enter Admin Mode') }}
            </button>
        @endif
    </form>
@endif

@if (config('shared-ui.portal_url') && ! config('shared-ui.portal') && $style === 'dropdown')
    <x-dropdown-link :href="config('shared-ui.portal_url')">
        <i class="fa-solid fa-arrow-left w-4 mr-2 text-center"></i>{{ __('Return to Workbench') }}
    </x-dropdown-link>
@endif

<form method="POST" action="{{ route('logout') }}">
    @csrf
    @if ($style === 'dropdown')
        <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').requestSubmit();">
            <i class="fa-solid fa-right-from-bracket w-4 mr-2 text-center"></i>{{ __('Log Out') }}
        </x-dropdown-link>
    @else
        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-md text-[0.8rem] font-medium text-zinc-200 hover:bg-white/5 hover:text-white">
            <i class="fa-solid fa-right-from-bracket w-4 text-center text-[0.9rem]"></i>{{ __('Log Out') }}
        </button>
    @endif
</form>
