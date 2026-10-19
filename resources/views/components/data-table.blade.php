{{--
    A list table that never hides what it holds. Columns marked `hide` drop out on narrower screens, and on
    phones each row becomes a card (cells stacked, with their labels). When it's still wider than the page it
    scrolls sideways with a shadow on the cut-off edge; `scroll` caps its height at the screen's, so the
    header stays put and the sideways scrollbar is always in view; `sticky-first` keeps the first column
    (the name) in place while it scrolls sideways. Edge to edge in an `<x-card :padded="false">`.
    <x-data-table>
        <x-slot name="head">
            <x-data-table.th>Name</x-data-table.th>
            <x-data-table.th hide="lg">Contact</x-data-table.th>
            <x-data-table.th class="w-0"><span class="sr-only">Actions</span></x-data-table.th>
        </x-slot>
        @foreach ($people as $person)
            <x-data-table.row>
                <x-data-table.td primary>…</x-data-table.td>
                <x-data-table.td hide="lg" label="Contact">…</x-data-table.td>
                <x-data-table.td actions><x-data-table.actions>…</x-data-table.actions></x-data-table.td>
            </x-data-table.row>
        @endforeach
    </x-data-table>
--}}
@props(['scroll' => false, 'stickyFirst' => false])
<div {{ $attributes->merge(['class' => 'relative']) }}
     x-data="{
        left: false, right: false, inset: 0,
        measure() {
            const s = this.$refs.scroller;
            this.left = s.scrollLeft > 1;
            this.right = s.scrollLeft + s.clientWidth < s.scrollWidth - 1;
            this.inset = {{ $stickyFirst ? 'true' : 'false' }} ? (s.querySelector('tr > :first-child')?.offsetWidth ?? 0) : 0;
        },
        init() {
            this.measure();
            new ResizeObserver(() => this.measure()).observe(this.$refs.scroller);
        },
     }">
    <div x-ref="scroller" @scroll.passive="measure()"
         @class(['overflow-x-auto', 'sm:max-h-[calc(100vh-8rem)] sm:overflow-y-auto' => $scroll])>
        <table @class([
            'block sm:table min-w-full text-sm',
            'sm:[&_tr>*:first-child]:sticky sm:[&_tr>*:first-child]:left-0 sm:[&_tr>*:first-child]:bg-inherit sm:[&_tbody_tr>*:first-child]:z-[1] sm:[&_thead_tr>*:first-child]:z-[3]' => $stickyFirst,
        ])>
            <thead class="hidden sm:table-header-group">
                <tr class="bg-zinc-50">{{ $head }}</tr>
            </thead>
            <tbody class="block sm:table-row-group divide-y divide-zinc-200">
                {{ $slot }}
            </tbody>
        </table>
    </div>
    {{-- Shadows on an edge with more past it --}}
    <div x-show="left" x-cloak :style="`left: ${inset}px`"
         class="hidden sm:block pointer-events-none absolute inset-y-0 w-6 bg-gradient-to-r from-zinc-900/10 to-transparent"></div>
    <div x-show="right" x-cloak
         class="hidden sm:block pointer-events-none absolute inset-y-0 right-0 w-6 bg-gradient-to-l from-zinc-900/10 to-transparent"></div>
</div>
