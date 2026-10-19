{{--
    A cell of <x-data-table.row>. On phones the row is a card and each cell a line of it:
    `primary` is the card's title (no label), `label` names the others, `actions` (an <x-data-table.actions>)
    sits in the card's top right corner. hide: md, lg, xl or 2xl, as its column's heading — hidden between
    phone and that width (on phones the card still shows it).
--}}
@props(['hide' => null, 'label' => null, 'primary' => false, 'actions' => false, 'align' => 'left'])
@php
    $display = [
        'md' => 'block sm:hidden md:table-cell',
        'lg' => 'block sm:hidden lg:table-cell',
        'xl' => 'block sm:hidden xl:table-cell',
        '2xl' => 'block sm:hidden 2xl:table-cell',
    ][$hide] ?? 'block sm:table-cell';
@endphp
<td {{ $attributes->class([
    $display,
    'align-top',
    'px-4 py-1 sm:py-3' => ! $actions,
    'font-medium text-zinc-900 pe-14 sm:pe-4' => $primary,
    'text-zinc-600' => ! $primary,
    'absolute top-2 right-2 sm:static w-0 whitespace-nowrap sm:px-4 sm:py-2 text-right' => $actions,
    'sm:text-right' => $align === 'right' && ! $actions,
    'sm:text-center' => $align === 'center',
]) }}>
    @if ($label && ! $primary && ! $actions)
        <div class="sm:hidden text-xs font-medium uppercase tracking-wide text-zinc-400">{{ $label }}</div>
    @endif
    {{ $slot }}
</td>
