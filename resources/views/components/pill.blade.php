{{-- A small rounded label ("6 Apps", a status). color: red (default), gray, green, amber, blue, purple, orange, teal --}}
@props(['color' => 'red'])
@php
    $colors = [
        'red' => 'bg-bt_primary-50 text-bt_primary-600 ring-bt_primary-100',
        'gray' => 'bg-zinc-100 text-zinc-700 ring-zinc-200',
        'green' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'amber' => 'bg-amber-50 text-amber-700 ring-amber-100',
        'blue' => 'bg-sky-50 text-sky-700 ring-sky-100',
        'purple' => 'bg-violet-50 text-violet-700 ring-violet-100',
        'orange' => 'bg-orange-50 text-orange-700 ring-orange-100',
        'teal' => 'bg-teal-50 text-teal-700 ring-teal-100',
    ];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold ring-1 ring-inset ' . ($colors[$color] ?? $colors['red'])]) }}>{{ $slot }}</span>
