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
    one of them is active — and is hidden when none of its children are visible.
--}}
@php
    $visible = fn (array $item) => ! (isset($item['can']) && \Illuminate\Support\Facades\Gate::denies($item['can']));
    $linkable = fn (array $item) => isset($item['route']) && \Illuminate\Support\Facades\Route::has($item['route']) && $visible($item);
    $isActive = fn (array $item) => request()->routeIs(...(array) ($item['active'] ?? $item['route']))
        && ! (isset($item['except']) && request()->routeIs(...(array) $item['except']));
@endphp
@foreach (config('shared-ui.navigation', []) as $item)
    @if (isset($item['children']))
        @continue(! $visible($item))
        @php
            $children = array_values(array_filter($item['children'], $linkable));
            $open = collect($children)->contains($isActive);
        @endphp
        @continue(empty($children))
        <div x-data="{ open: @js($open) }">
            <button type="button" @click="open = ! open" :aria-expanded="open.toString()"
                    @class([
                        'w-full flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition',
                        'text-bt_primary-700' => $open,
                        'text-gray-600 hover:bg-gray-100 hover:text-gray-900' => ! $open,
                    ])>
                <i class="{{ $item['icon'] ?? 'fa-solid fa-folder' }} w-5 text-center"></i>
                <span class="flex-1 truncate text-left">{{ __($item['label']) }}</span>
                <i class="fa-solid fa-chevron-down text-xs transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div x-show="open" @unless ($open) x-cloak @endunless class="mt-1 space-y-1">
                @foreach ($children as $child)
                    @include('shared-ui::workbench.nav-item', ['item' => $child, 'child' => true])
                @endforeach
            </div>
        </div>
    @else
        @continue(! $linkable($item))
        @include('shared-ui::workbench.nav-item', ['item' => $item])
    @endif
@endforeach
