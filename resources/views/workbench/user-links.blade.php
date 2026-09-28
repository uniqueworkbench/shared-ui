{{--
    The signed-in user's links: config('shared-ui.user_menu') items (label, route, icon),
    Return to Portal (unless portal_url is null — e.g. in the portal itself) and Log Out.
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
        <a href="{{ route($link['route']) }}" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900">
            <i class="{{ $link['icon'] ?? 'fa-solid fa-circle' }} w-5 text-center"></i>{{ __($link['label']) }}
        </a>
    @endif
@endforeach

@if (config('shared-ui.portal_url'))
    @if ($style === 'dropdown')
        <x-dropdown-link :href="config('shared-ui.portal_url')">
            <i class="fa-solid fa-arrow-left w-4 mr-2 text-center"></i>{{ __('Return to Portal') }}
        </x-dropdown-link>
    @else
        @include('shared-ui::workbench.portal-link')
    @endif
@endif

<form method="POST" action="{{ route('logout') }}">
    @csrf
    @if ($style === 'dropdown')
        <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').requestSubmit();">
            <i class="fa-solid fa-right-from-bracket w-4 mr-2 text-center"></i>{{ __('Log Out') }}
        </x-dropdown-link>
    @else
        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900">
            <i class="fa-solid fa-right-from-bracket w-5 text-center"></i>{{ __('Log Out') }}
        </button>
    @endif
</form>
