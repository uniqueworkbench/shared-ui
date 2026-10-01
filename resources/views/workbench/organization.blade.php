{{--
    The organization the user is working in, in the top bar, with a menu to switch (and the app's own links).
    config('shared-ui.organization'): [Class::class, 'staticMethod'] returning null or
    ['name' => string, 'switch_url' => ?string, 'links' => [['label' => …, 'url' => …, 'icon' => …], …]].
--}}
@php
    $resolver = config('shared-ui.organization');
    $organization = $resolver && is_callable($resolver) ? call_user_func($resolver) : null;
@endphp
@if ($organization && filled($organization['name'] ?? null))
    <div x-data="{ open: false }" @click.outside="open = false" class="relative min-w-0">
        <button type="button" @click="open = ! open" :aria-expanded="open.toString()"
                class="flex items-center gap-3 min-w-0 px-2 py-2 rounded-md text-white hover:bg-white/5" data-organization>
            <i class="fa-regular fa-building text-xl text-zinc-200"></i>
            <span class="truncate text-base font-semibold">{{ $organization['name'] }}</span>
            <i class="fa-solid fa-chevron-down text-xs text-zinc-300"></i>
        </button>
        <div x-show="open" x-cloak x-transition.opacity
             class="absolute left-0 z-50 mt-2 w-64 rounded-md bg-white py-1 shadow-lg ring-1 ring-black/5">
            <p class="px-4 pt-2 pb-1 text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Working in') }}</p>
            <p class="px-4 pb-2 text-sm font-semibold text-zinc-900 truncate">{{ $organization['name'] }}</p>
            @foreach ($organization['links'] ?? [] as $link)
                <x-dropdown-link :href="$link['url']"><i class="{{ $link['icon'] ?? 'fa-solid fa-circle' }} w-4 mr-2 text-center"></i>{{ __($link['label']) }}</x-dropdown-link>
            @endforeach
            @if (! empty($organization['switch_url']))
                <x-dropdown-link :href="$organization['switch_url']"><i class="fa-solid fa-right-left w-4 mr-2 text-center"></i>{{ __('Switch Organization') }}</x-dropdown-link>
            @endif
        </div>
    </div>
@endif
