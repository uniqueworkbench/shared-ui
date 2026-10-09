{{--
    Menu items from config('shared-ui.navigation'). Per item:
      label, route (route name) or url (any link — e.g. another app's), icon (Font Awesome classes)
      active  optional routeIs() pattern(s), string or array (default: the route; a url item only by this)
      except  optional routeIs() pattern(s) that never count as active
      can     optional gate ability — the item only shows when Gate::allows() it
      badge   optional [Class::class, 'staticMethod'] returning a count (shown when > 0)
      count   optional [Class::class, 'staticMethod'] returning how many things the page manages (shown muted
              after the label, 0 included — not an alert, unlike badge)
    Items whose route doesn't exist are skipped.

    A divider (['divider' => true]) is a thin line between sections. Dividers left at the start or
    end, or next to another, once hidden items are skipped, aren't shown.

    A heading (['heading' => 'Modules']) is a small label over the items after it, up to the next
    heading or divider. It isn't shown when none of those items are. With `sortable` (a URL), its items
    can be dragged into another order, each saved by PUT to that URL as JSON {order: [their sort_key]}
    (items under it need a `sort_key`; drag and drop needs a mouse — phones only follow the links).

    A group is an item with children (label, icon, optional can, children: items as above,
    no route). It shows as a header that expands to its children — open by default while
    one of them is active — and is hidden when none of its children are visible. While
    collapsed, the header shows its children's badges added up; expanded, each child shows its own.
--}}
@php
    $visible = fn (array $item) => ! (isset($item['can']) && \Illuminate\Support\Facades\Gate::denies($item['can']));
    $linkable = fn (array $item) => (isset($item['url']) || (isset($item['route']) && \Illuminate\Support\Facades\Route::has($item['route']))) && $visible($item);
    $isActive = fn (array $item) => ($item['active'] ?? $item['route'] ?? null) !== null
        && request()->routeIs(...(array) ($item['active'] ?? $item['route']))
        && ! (isset($item['except']) && request()->routeIs(...(array) $item['except']));
    $badgeOf = fn (array $item) => isset($item['badge']) && is_callable($item['badge']) ? (int) call_user_func($item['badge']) : 0;
    $pill = 'inline-flex items-center justify-center min-w-[1.25rem] px-1.5 py-0.5 rounded-full text-xs font-semibold leading-none bg-bt_primary-600 text-white';
    // What shows: links the user may follow, groups with such a child, and dividers between them
    $shown = [];
    foreach (config('shared-ui.navigation', []) as $item) {
        if (isset($item['heading'])) {
            $shown[] = $item;
            continue;
        }
        $isDivider = ! empty($item['divider']);
        if ($isDivider ? ($shown === [] || ! empty(end($shown)['divider']))
            : (isset($item['children']) ? ! $visible($item) || ! array_filter($item['children'], $linkable) : ! $linkable($item))) {
            continue;
        }
        $shown[] = $item;
    }
    // A heading only over something: drop those followed by another heading, a divider or the end
    $shown = array_values(array_filter($shown, function ($item, $i) use ($shown) {
        $next = $shown[$i + 1] ?? null;

        return ! isset($item['heading']) || ($next && ! isset($next['heading']) && empty($next['divider']));
    }, ARRAY_FILTER_USE_BOTH));
    if ($shown !== [] && ! empty(end($shown)['divider'])) {
        array_pop($shown);
    }
@endphp
@php $sorting = false; @endphp
@foreach ($shown as $item)
    @if ($sorting && (isset($item['heading']) || ! empty($item['divider'])))
        </div>
        @php $sorting = false; @endphp
    @endif
    @if (isset($item['heading']))
        <div role="heading" aria-level="2" data-nav-heading class="mt-4 mb-1 px-3 text-[0.68rem] font-semibold uppercase tracking-wider text-zinc-400">{{ __($item['heading']) }}</div>
        @if (! empty($item['sortable']))
            {{-- Its items drag into another order (a mouse; HTML drag and drop), saved at once --}}
            <div class="space-y-1" data-nav-sortable="{{ $item['sortable'] }}"
                 x-data="{
                     dragging: null,
                     over(event) {
                         const target = event.target.closest('[data-sort-key]');
                         if (! this.dragging || ! target || target === this.dragging || target.parentElement !== $el) return;
                         const box = target.getBoundingClientRect();
                         event.clientY > box.top + box.height / 2 ? target.after(this.dragging) : target.before(this.dragging);
                     },
                     drop() {
                         if (! this.dragging) return;
                         this.dragging.classList.remove('opacity-50');
                         this.dragging = null;
                         fetch($el.dataset.navSortable, {
                             method: 'PUT',
                             headers: { 'X-CSRF-TOKEN': @js(csrf_token()), 'Accept': 'application/json', 'Content-Type': 'application/json' },
                             body: JSON.stringify({ order: [...$el.querySelectorAll(':scope > [data-sort-key]')].map((row) => row.dataset.sortKey) }),
                         });
                     },
                 }"
                 @dragstart="dragging = $event.target.closest('[data-sort-key]'); dragging?.classList.add('opacity-50'); $event.dataTransfer.effectAllowed = 'move'"
                 @dragover.prevent="over($event)" @drop.prevent @dragend="drop()">
            @php $sorting = true; @endphp
        @endif
    @elseif (! empty($item['divider']))
        <div role="separator" class="my-2 mx-3 border-t border-white/10"></div>
    @elseif (isset($item['children']))
        @php
            $children = array_values(array_filter($item['children'], $linkable));
            $open = collect($children)->contains($isActive);
            $childBadges = array_map($badgeOf, $children);
            $groupBadge = array_sum($childBadges);
        @endphp
        @continue(empty($children))
        <div x-data="{ open: @js($open) }">
            <button type="button" @click="open = ! open" :aria-expanded="open.toString()"
                    @class([
                        'w-full flex items-center gap-3 px-3 py-2 rounded-md text-[0.8rem] font-medium transition hover:bg-white/5',
                        'text-white' => $open,
                        'text-zinc-200 hover:text-white' => ! $open,
                    ])>
                <i class="{{ $item['icon'] ?? 'fa-solid fa-folder' }} w-4 text-center text-[0.9rem]"></i>
                <span class="flex-1 truncate text-left">{{ __($item['label']) }}</span>
                @if ($groupBadge > 0)
                    {{-- Collapsed only: expanded, the children show their own --}}
                    <span x-show="! open" @if ($open) x-cloak @endif data-group-badge class="{{ $pill }}">{{ $groupBadge > 99 ? '99+' : $groupBadge }}</span>
                @endif
                <i class="fa-solid text-xs {{ $open ? 'fa-chevron-up' : 'fa-chevron-down' }}" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
            <div x-show="open" @unless ($open) x-cloak @endunless class="mt-1 space-y-1">
                @foreach ($children as $i => $childItem)
                    @include('shared-ui::workbench.nav-item', ['item' => $childItem, 'child' => true, 'badgeCount' => $childBadges[$i]])
                @endforeach
            </div>
        </div>
    @elseif ($sorting)
        <div draggable="true" data-sort-key="{{ $item['sort_key'] ?? '' }}" class="cursor-grab">
            @include('shared-ui::workbench.nav-item', ['item' => $item, 'child' => false, 'badgeCount' => null])
        </div>
    @else
        {{-- child passed explicitly: @include inherits this view's variables --}}
        @include('shared-ui::workbench.nav-item', ['item' => $item, 'child' => false, 'badgeCount' => null])
    @endif
@endforeach
@if ($sorting)
    </div>
@endif
