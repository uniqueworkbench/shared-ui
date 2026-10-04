{{--
    The top of the dark top bar (and drawer). A client app shows its own brand: its wide header logo
    (config('shared-ui.app_header_logo'), made for the dark bar), else its square icon (app_icon) on a small white
    tile with the app name; an app with neither (e.g. the portal) shows the Unique Workbench logo. Paths are under
    public/ and are skipped while the file isn't there. The environment outside production.
    $logoClass sizes the Unique Workbench logo.
--}}
@php
    $brandImage = fn (string $key) => ($path = config('shared-ui.' . $key)) && is_file(public_path($path)) ? asset($path) : null;
    $headerLogo = $brandImage('app_header_logo');
    $appIcon = $headerLogo ? null : $brandImage('app_icon');
@endphp
<a href="{{ \Illuminate\Support\Facades\Route::has('dashboard') ? route('dashboard') : url('/') }}" class="flex items-center gap-2 min-w-0 text-white">
    @if ($headerLogo)
        <img src="{{ $headerLogo }}" alt="{{ config('app.name') }}" class="h-9 w-auto min-w-0 max-w-full object-contain object-left">
    @elseif ($appIcon)
        <img src="{{ $appIcon }}" alt="" class="h-9 w-9 shrink-0 rounded-md bg-white object-contain p-0.5">
        <span class="min-w-0 truncate text-base font-semibold">{{ config('app.name') }}</span>
    @else
        <img src="{{ \UniqueWorkbench\SharedUi\Brand::logo('dark') }}" alt="Unique Workbench" class="{{ $logoClass ?? 'h-11' }} w-auto shrink-0">
    @endif
    @if (app()->environment('local'))
        <span class="shrink-0 px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-400 text-amber-950">LOCAL</span>
    @elseif (app()->environment('development'))
        <span class="shrink-0 px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-orange-400 text-orange-950">BUILD</span>
    @elseif (app()->environment('staging'))
        <span class="shrink-0 px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-sky-400 text-sky-950">BETA</span>
    @endif
</a>
