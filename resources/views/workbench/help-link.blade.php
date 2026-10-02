{{-- "Help & Support" at the bottom of the menu. config('shared-ui.help'): ['label' => …, 'route' => … or 'url' => …, 'can' => optional gate]; null hides it. --}}
@php
    $help = config('shared-ui.help');
    if (isset($help['can']) && \Illuminate\Support\Facades\Gate::denies($help['can'])) {
        $help = null;
    }
    $href = isset($help['route']) && \Illuminate\Support\Facades\Route::has($help['route']) ? route($help['route']) : ($help['url'] ?? null);
@endphp
@if ($href)
    <a href="{{ $href }}" class="flex items-center gap-3 px-3 py-2 rounded-md text-[0.8rem] font-medium text-zinc-200 hover:bg-white/5 hover:text-white">
        <i class="fa-regular fa-circle-question w-4 text-center text-[0.9rem]"></i>{{ __($help['label'] ?? 'Help & Support') }}
    </a>
@endif
