{{--
    A row's ⋯ menu, holding <x-data-table.action> items. The menu opens on top of the page (not inside the
    table, so a scrolling table never cuts it off), below the button or above it near the bottom of the screen.
    label: what the button says to screen readers.
--}}
@props(['label' => 'Actions'])
<div class="inline-block"
     x-data="{
        open: false, top: 0, right: 0,
        toggle() {
            if (this.open) return this.open = false;
            const r = this.$refs.button.getBoundingClientRect();
            this.top = r.bottom + 4;
            this.right = window.innerWidth - r.right;
            this.open = true;
            this.$nextTick(() => {
                const h = this.$refs.menu.offsetHeight;
                if (this.top + h > window.innerHeight - 8 && r.top - 4 - h > 8) this.top = r.top - 4 - h;
            });
        },
     }"
     @keydown.escape.window="open = false" @scroll.window.capture="open = false" @resize.window="open = false">
    <button type="button" x-ref="button" @click="toggle()" :aria-expanded="open" aria-haspopup="menu" aria-label="{{ $label }}"
            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-bt_primary-500">
        <i class="fa-solid fa-ellipsis"></i>
    </button>
    <template x-teleport="body">
        <div x-ref="menu" x-show="open" x-cloak role="menu"
             @click.outside="if (! $refs.button.contains($event.target)) open = false"
             @click="if ($event.target.closest('a, button')) open = false"
             :style="`top: ${top}px; right: ${right}px`"
             class="fixed z-50 w-56 py-1 rounded-md bg-white text-left shadow-lg ring-1 ring-black/5">
            {{ $slot }}
        </div>
    </template>
</div>
