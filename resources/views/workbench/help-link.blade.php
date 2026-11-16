{{--
    "Help & Support" at the bottom of the menu. config('shared-ui.help'): ['label' => …, 'route' => … or 'url' => …, 'can' => optional gate]; null hides it.
    In a portal (config('shared-ui.portal')) it's "Unique Workbench" instead — a link back to the workbench (config('shared-ui.portal_url')),
    since a portal is its owner's own place and support is the workbench's.
--}}
@php
    if (config('shared-ui.portal')) {
        $help = ['label' => 'Unique Workbench'];
        $href = workbench()->workbenchUrl() ?: null; // always the workbench, wherever in the portal (never an app's repointed portal_url)
        $icon = 'fa-solid fa-arrow-left';
    } else {
        $help = config('shared-ui.help');
        if (isset($help['can']) && \Illuminate\Support\Facades\Gate::denies($help['can'])) {
            $help = null;
        }
        $href = isset($help['route']) && \Illuminate\Support\Facades\Route::has($help['route']) ? route($help['route']) : ($help['url'] ?? null);
        $icon = 'fa-regular fa-circle-question';
    }
@endphp
@if ($href)
    <a href="{{ $href }}" data-nav-link @if (config('shared-ui.portal')) data-workbench-link @endif title="{{ __($help['label'] ?? 'Help & Support') }}" class="flex items-center gap-3 px-3 py-2 rounded-md text-[0.8rem] font-medium text-zinc-200 hover:bg-white/5 hover:text-white">
        <i class="{{ $icon }} w-4 text-center text-[0.9rem]"></i><span data-nav-label>{{ __($help['label'] ?? 'Help & Support') }}</span>
    </a>
@endif
