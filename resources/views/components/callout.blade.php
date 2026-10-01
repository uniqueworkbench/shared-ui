{{--
    A highlighted introduction at the top of a page (the pale red band): icon circle, title, text, and an optional link on the right.
    <x-callout icon="fa-solid fa-toolbox" title="The Toolbox" href="…" link="View Your Toolbox">Select the apps…</x-callout>
--}}
@props(['icon' => null, 'title' => null, 'href' => null, 'link' => null])
<div {{ $attributes->merge(['class' => 'flex flex-col sm:flex-row sm:items-center gap-5 sm:gap-6 p-6 bg-bt_primary-50 border border-bt_primary-200 rounded-md']) }}>
    @if ($icon)
        <x-icon-circle :icon="$icon" color="red" size="lg" />
    @endif
    <div class="flex-1 min-w-0">
        @if ($title)
            <h2 class="text-2xl font-bold tracking-tight text-zinc-900">{{ $title }}</h2>
        @endif
        <div class="mt-1.5 text-base leading-relaxed text-zinc-700">{{ $slot }}</div>
    </div>
    @if ($href && $link)
        <a href="{{ $href }}" class="shrink-0 inline-flex items-center gap-2 text-sm font-semibold text-bt_primary-600 hover:text-bt_primary-800">
            {{ $link }} <i class="fa-solid fa-arrow-right text-xs"></i>
        </a>
    @endif
</div>
