{{-- A quiet note with an info icon and an optional red link underneath --}}
@props(['href' => null, 'link' => null, 'icon' => 'fa-solid fa-circle-info'])
<div {{ $attributes->merge(['class' => 'flex gap-4 p-5 bg-zinc-50 border border-zinc-200 rounded-md']) }}>
    <i class="{{ $icon }} mt-0.5 text-lg text-bt_primary-600"></i>
    <div class="min-w-0 text-sm text-zinc-800">
        {{ $slot }}
        @if ($href && $link)
            <a href="{{ $href }}" class="mt-2 inline-flex items-center gap-2 font-semibold text-bt_primary-600 hover:text-bt_primary-800">
                {{ $link }} <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
        @endif
    </div>
</div>
