{{--
    A Font Awesome icon in a tinted circle, as on app tiles and callouts.
    icon: FA classes · color: gray, red, green, purple, orange, teal, blue, amber · size: sm (40px), md (48px), lg (64px)
--}}
@props(['icon' => 'fa-solid fa-circle', 'color' => 'gray', 'size' => 'md'])
@php
    $colors = [
        'gray' => 'bg-zinc-100 text-zinc-800',
        'red' => 'bg-bt_primary-100 text-bt_primary-600',
        'green' => 'bg-emerald-50 text-emerald-600',
        'purple' => 'bg-violet-50 text-violet-500',
        'orange' => 'bg-orange-50 text-orange-500',
        'amber' => 'bg-amber-50 text-amber-500',
        'teal' => 'bg-teal-50 text-teal-600',
        'blue' => 'bg-sky-50 text-sky-600',
    ];
    $sizes = ['sm' => 'h-10 w-10 text-base', 'md' => 'h-12 w-12 text-xl', 'lg' => 'h-16 w-16 text-2xl'];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center justify-center rounded-full ' . ($colors[$color] ?? $colors['gray']) . ' ' . ($sizes[$size] ?? $sizes['md'])]) }} aria-hidden="true">
    <i class="{{ $icon }}"></i>
</span>
