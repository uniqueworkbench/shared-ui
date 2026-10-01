{{-- Logo and wordmark, on the dark top bar (and drawer); the environment outside production. --}}
<a href="{{ \Illuminate\Support\Facades\Route::has('dashboard') ? route('dashboard') : url('/') }}" class="flex items-center gap-3 min-w-0 text-white">
    @include('shared-ui::workbench.logo', ['class' => 'h-9 w-9 shrink-0'])
    <span class="leading-none">
        <span class="block text-lg font-bold tracking-[0.08em]">UNIQUE</span>
        <span class="block text-[0.8rem] font-medium tracking-[0.2em] text-zinc-200">WORKBENCH</span>
    </span>
    @if (app()->environment('local'))
        <span class="shrink-0 px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-400 text-amber-950">LOCAL</span>
    @elseif (app()->environment('development'))
        <span class="shrink-0 px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-orange-400 text-orange-950">BUILD</span>
    @elseif (app()->environment('staging'))
        <span class="shrink-0 px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-sky-400 text-sky-950">BETA</span>
    @endif
</a>
