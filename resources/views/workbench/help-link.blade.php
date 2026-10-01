{{-- "Help & Support" at the bottom of the menu. config('shared-ui.help'): ['label' => …, 'route' => … or 'url' => …, 'can' => optional gate]; null hides it. --}}
@php
    $help = config('shared-ui.help');
    if (isset($help['can']) && \Illuminate\Support\Facades\Gate::denies($help['can'])) {
        $help = null;
    }
    $href = isset($help['route']) && \Illuminate\Support\Facades\Route::has($help['route']) ? route($help['route']) : ($help['url'] ?? null);
@endphp
@if ($href)
    <a href="{{ $href }}" class="flex items-center gap-4 px-4 py-3 rounded-md text-base font-medium text-zinc-200 hover:bg-white/5 hover:text-white">
        <i class="fa-regular fa-circle-question w-5 text-center text-lg"></i>{{ __($help['label'] ?? 'Help & Support') }}
    </a>
@endif
