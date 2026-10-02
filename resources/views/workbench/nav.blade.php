{{--
    Menu items from config('shared-ui.navigation'). Per item:
      label, route (route name), icon (Font Awesome classes)
      active  optional routeIs() pattern(s), string or array (default: the route)
      except  optional routeIs() pattern(s) that never count as active
      can     optional gate ability — the item only shows when Gate::allows() it
      badge   optional [Class::class, 'staticMethod'] returning a count (shown when > 0)
    Items whose route doesn't exist are skipped.

    A group is an item with children (label, icon, optional can, children: items as above,
    no route). It shows as a header that expands to its children — open by default while
    one of them is active — and is hidden when none of its children are visible. While
    collapsed, the header shows its children's badges added up; expanded, each child shows its own.
--}}
@php
    $visible = fn (array $item) => ! (isset($item['can']) && \Illuminate\Support\Facades\Gate::denies($item['can']));
    $linkable = fn (array $item) => isset($item['route']) && \Illuminate\Support\Facades\Route::has($item['route']) && $visible($item);
    $isActive = fn (array $item) => request()->routeIs(...(array) ($item['active'] ?? $item['route']))
        && ! (isset($item['except']) && request()->routeIs(...(array) $item['except']));
    $badgeOf = fn (array $item) => isset($item['badge']) && is_callable($item['badge']) ? (int) call_user_func($item['badge']) : 0;
    $pill = 'inline-flex items-center justify-center min-w-[1.25rem] px-1.5 py-0.5 rounded-full text-xs font-semibold leading-none bg-bt_primary-600 text-white';
@endphp
@foreach (config('shared-ui.navigation', []) as $item)
    @if (isset($item['children']))
        @continue(! $visible($item))
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
    @else
        @continue(! $linkable($item))
        {{-- child passed explicitly: @include inherits this view's variables --}}
        @include('shared-ui::workbench.nav-item', ['item' => $item, 'child' => false, 'badgeCount' => null])
    @endif
@endforeach
