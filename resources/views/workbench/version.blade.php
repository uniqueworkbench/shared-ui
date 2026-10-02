{{-- The very bottom of the nav: the app's version (from package.json) and, outside production, the environment. --}}
<p class="px-3 pt-3 text-xs text-zinc-500" data-app-version>
    @if (config('app.env') !== 'production'){{ config('app.env') }} &middot; @endif v{{ config('app.version', '1.0.0') }}
</p>
