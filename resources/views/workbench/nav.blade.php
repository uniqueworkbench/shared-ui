{{--
    Menu items from config('shared-ui.navigation'). Per item:
      label, route (route name), icon (Font Awesome classes)
      active  optional routeIs() pattern(s), string or array (default: the route)
      except  optional routeIs() pattern(s) that never count as active
      can     optional gate ability — the item only shows when Gate::allows() it
      badge   optional [Class::class, 'staticMethod'] returning a count (shown when > 0)
    Items whose route doesn't exist are skipped.
--}}
@foreach (config('shared-ui.navigation', []) as $item)
    @continue(! \Illuminate\Support\Facades\Route::has($item['route']))
    @continue(isset($item['can']) && \Illuminate\Support\Facades\Gate::denies($item['can']))
    @php
        $active = request()->routeIs(...(array) ($item['active'] ?? $item['route']))
            && ! (isset($item['except']) && request()->routeIs(...(array) $item['except']));
        $badge = isset($item['badge']) && is_callable($item['badge']) ? (int) call_user_func($item['badge']) : 0;
    @endphp
    <a href="{{ route($item['route']) }}"
       @if ($active) aria-current="page" @endif
       @class([
           'flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition',
           'bg-bt_primary-50 text-bt_primary-700' => $active,
           'text-gray-600 hover:bg-gray-100 hover:text-gray-900' => ! $active,
       ])>
        <i class="{{ $item['icon'] ?? 'fa-solid fa-circle' }} w-5 text-center"></i>
        <span class="flex-1 truncate">{{ __($item['label']) }}</span>
        @if ($badge > 0)
            <span class="inline-flex items-center justify-center min-w-[1.25rem] px-1.5 py-0.5 rounded-full text-xs font-semibold leading-none bg-blue-500 text-white">{{ $badge > 99 ? '99+' : $badge }}</span>
        @endif
    </a>
@endforeach
