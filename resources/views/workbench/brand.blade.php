{{-- The Unique Workbench logo on the dark top bar (and drawer); the environment outside production. $logoClass sizes it. --}}
<a href="{{ \Illuminate\Support\Facades\Route::has('dashboard') ? route('dashboard') : url('/') }}" class="flex items-center gap-2 min-w-0 text-white">
    <img src="{{ \UniqueWorkbench\SharedUi\Brand::logo('dark') }}" alt="Unique Workbench" class="{{ $logoClass ?? 'h-11' }} w-auto shrink-0">
    @if (app()->environment('local'))
        <span class="shrink-0 px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-400 text-amber-950">LOCAL</span>
    @elseif (app()->environment('development'))
        <span class="shrink-0 px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-orange-400 text-orange-950">BUILD</span>
    @elseif (app()->environment('staging'))
        <span class="shrink-0 px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-sky-400 text-sky-950">BETA</span>
    @endif
</a>
