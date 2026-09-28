<a href="{{ \Illuminate\Support\Facades\Route::has('dashboard') ? route('dashboard') : url('/') }}" class="flex items-center gap-2 min-w-0">
    <x-application-logo class="h-8 w-auto shrink-0" />
    <span class="font-semibold text-gray-800 truncate">{{ config('app.name') }}</span>
    @if (app()->environment('local'))
        <span class="shrink-0 px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 border border-amber-300">LOCAL</span>
    @elseif (app()->environment('development'))
        <span class="shrink-0 px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-orange-100 text-orange-800 border border-orange-300">BUILD</span>
    @elseif (app()->environment('staging'))
        <span class="shrink-0 px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-blue-100 text-blue-800 border border-blue-300">BETA</span>
    @endif
</a>
