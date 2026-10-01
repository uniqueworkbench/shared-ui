{{--
    A row that leads somewhere: icon, title, smaller lines underneath, a chevron. Inside <x-list-group>,
    or standalone (bordered=true) for a stack of item cards.
    <x-list-row href="…" icon="fa-regular fa-user" title="Users" subtitle="Select users" />
    Lead with an <x-icon-circle> instead of a plain icon with the `leading` slot; put buttons in `actions`.
--}}
@props(['href' => null, 'icon' => null, 'title', 'subtitle' => null, 'bordered' => false])
@php $tag = $href ? 'a' : 'div'; @endphp
<{{ $tag }} @if ($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'flex items-center gap-4 px-4 py-3.5 bg-white' . ($href ? ' hover:bg-zinc-50 transition' : '') . ($bordered ? ' border border-zinc-200 rounded-md' : '')]) }}>
    @isset($leading)
        {{ $leading }}
    @elseif ($icon)
        <i class="{{ $icon }} w-5 text-center text-zinc-700"></i>
    @endisset
    <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold text-zinc-900 truncate">{{ $title }}</p>
        @if ($subtitle)
            <p class="text-xs text-zinc-500 truncate">{{ $subtitle }}</p>
        @endif
        {{ $slot }}
    </div>
    @isset($actions)
        <div class="flex items-center gap-3 shrink-0">{{ $actions }}</div>
    @endisset
    @if ($href)
        <i class="fa-solid fa-chevron-right text-xs text-zinc-500"></i>
    @endif
</{{ $tag }}>
