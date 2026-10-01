{{--
    The bell in the top bar, with an unread count.
    config('shared-ui.notifications'): ['count' => [Class::class, 'staticMethod'], 'route' => route name]; null hides it.
--}}
@php
    $notifications = config('shared-ui.notifications');
    $route = $notifications['route'] ?? null;
    $count = isset($notifications['count']) && is_callable($notifications['count']) ? (int) call_user_func($notifications['count']) : 0;
@endphp
@if ($route && \Illuminate\Support\Facades\Route::has($route))
    <a href="{{ route($route) }}" class="relative p-2 text-zinc-200 hover:text-white" aria-label="{{ __('Notifications') }}" data-notifications>
        <i class="fa-regular fa-bell text-xl"></i>
        @if ($count > 0)
            <span class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full bg-bt_primary-600 text-[11px] font-semibold text-white">{{ $count > 99 ? '99+' : $count }}</span>
        @endif
    </a>
@endif
