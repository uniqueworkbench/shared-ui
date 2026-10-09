{{-- One menu link (see nav.blade.php). $item: label, route or url, icon or image, active, except, badge, count, highlight. $child: indented under a group.
     $badgeCount: the badge already counted (by the group), or null to count it here. --}}
@php
    $child = $child ?? false;
    $active = ($item['active'] ?? $item['route'] ?? null) !== null
        && request()->routeIs(...(array) ($item['active'] ?? $item['route']))
        && ! (isset($item['except']) && request()->routeIs(...(array) $item['except']));
    $badge = $badgeCount ?? (isset($item['badge']) && is_callable($item['badge']) ? (int) call_user_func($item['badge']) : 0);
    // How many things the page manages (not an alert, unlike the badge): shown muted, 0 included
    $count = isset($item['count']) && is_callable($item['count']) ? (int) call_user_func($item['count']) : null;
    // A section's own link (e.g. a module's name over its menu): always stands out, red while it's the page
    $highlight = ! empty($item['highlight']) && ! $child;
@endphp
<a href="{{ $item['url'] ?? route($item['route']) }}"
   @if ($active) aria-current="page" @endif
   draggable="false"
   @class([
       'flex items-center gap-3 px-3 rounded-md font-medium transition',
       'py-2 text-[0.8rem]' => ! $child,
       'py-1.5 text-[0.76rem] pl-[2.5rem]' => $child,
       'font-semibold' => $highlight,
       'bg-bt_primary-600 text-white' => $active,
       'bg-white/10 text-white ring-1 ring-inset ring-white/15 hover:bg-white/15' => $highlight && ! $active,
       'text-zinc-200 hover:bg-white/5 hover:text-white' => ! $active && ! $highlight,
   ])>
    @if (! empty($item['image']))
        {{-- A picture (e.g. an app's icon) in the icon's place --}}
        <span class="inline-flex h-5 w-5 -mx-0.5 shrink-0 items-center justify-center rounded bg-white overflow-hidden">
            <img src="{{ $item['image'] }}" alt="" class="h-4 w-4 object-contain" data-nav-image>
        </span>
    @elseif (! ($child && empty($item['icon'])))
        <i class="{{ $item['icon'] ?? 'fa-solid fa-circle' }} w-4 text-center text-[0.9rem]"></i>
    @endif
    <span class="flex-1 truncate">{{ __($item['label']) }}</span>
    @if ($count !== null)
        <span data-nav-count class="text-[0.7rem] tabular-nums {{ $active ? 'text-white/80' : 'text-zinc-400' }}">{{ number_format($count) }}</span>
    @endif
    @if ($badge > 0)
        <span class="inline-flex items-center justify-center min-w-[1.25rem] px-1.5 py-0.5 rounded-full text-xs font-semibold leading-none {{ $active ? 'bg-white text-bt_primary-700' : 'bg-bt_primary-600 text-white' }}">{{ $badge > 99 ? '99+' : $badge }}</span>
    @endif
</a>
