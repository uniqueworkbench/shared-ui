{{-- A small rounded label ("6 Apps", a status). color: red (default), gray, green, amber, blue --}}
@props(['color' => 'red'])
@php
    $colors = [
        'red' => 'bg-bt_primary-50 text-bt_primary-600 ring-bt_primary-100',
        'gray' => 'bg-zinc-100 text-zinc-700 ring-zinc-200',
        'green' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'amber' => 'bg-amber-50 text-amber-700 ring-amber-100',
        'blue' => 'bg-sky-50 text-sky-700 ring-sky-100',
    ];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold ring-1 ring-inset ' . ($colors[$color] ?? $colors['red'])]) }}>{{ $slot }}</span>
