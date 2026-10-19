{{-- A column heading in <x-data-table>'s `head`. hide: md, lg, xl or 2xl — below that width the column drops out (give its cells the same) --}}
@props(['hide' => null, 'align' => 'left'])
@php
    $hidden = [
        'md' => 'hidden md:table-cell',
        'lg' => 'hidden lg:table-cell',
        'xl' => 'hidden xl:table-cell',
        '2xl' => 'hidden 2xl:table-cell',
    ];
@endphp
<th scope="col" {{ $attributes->class([
    'sticky top-0 z-[2] bg-zinc-50 border-b border-zinc-200 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-zinc-500 whitespace-nowrap',
    $hidden[$hide] ?? '',
    'text-right' => $align === 'right',
    'text-center' => $align === 'center',
    'text-left' => $align === 'left',
]) }}>{{ $slot }}</th>
