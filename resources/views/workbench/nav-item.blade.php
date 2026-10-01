{{-- One menu link (see nav.blade.php). $item: label, route, icon, active, except, badge. $child: indented under a group.
     $badgeCount: the badge already counted (by the group), or null to count it here. --}}
@php
    $child = $child ?? false;
    $active = request()->routeIs(...(array) ($item['active'] ?? $item['route']))
        && ! (isset($item['except']) && request()->routeIs(...(array) $item['except']));
    $badge = $badgeCount ?? (isset($item['badge']) && is_callable($item['badge']) ? (int) call_user_func($item['badge']) : 0);
@endphp
<a href="{{ route($item['route']) }}"
   @if ($active) aria-current="page" @endif
   @class([
       'flex items-center gap-4 px-4 rounded-md font-medium transition',
       'py-3 text-base' => ! $child,
       'py-2.5 text-[0.95rem] pl-[3.25rem]' => $child,
       'bg-bt_primary-600 text-white' => $active,
       'text-zinc-200 hover:bg-white/5 hover:text-white' => ! $active,
   ])>
    @unless ($child && empty($item['icon']))
        <i class="{{ $item['icon'] ?? 'fa-solid fa-circle' }} w-5 text-center text-lg"></i>
    @endunless
    <span class="flex-1 truncate">{{ __($item['label']) }}</span>
    @if ($badge > 0)
        <span class="inline-flex items-center justify-center min-w-[1.25rem] px-1.5 py-0.5 rounded-full text-xs font-semibold leading-none {{ $active ? 'bg-white text-bt_primary-700' : 'bg-bt_primary-600 text-white' }}">{{ $badge > 99 ? '99+' : $badge }}</span>
    @endif
</a>
