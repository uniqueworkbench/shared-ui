{{-- The signed-in user's picture (the user model's avatar_url, else the one the account app sent at sign-in — Workbench), else their initials in a red circle. --}}
@php
    $avatarUrl = auth()->user()?->avatar_url ?: (auth()->check() ? workbench()->avatarUrl() : null);
    $initials = collect(preg_split('/\s+/', trim((string) auth()->user()?->name)))
        ->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
@endphp
@if ($avatarUrl)
    <img src="{{ $avatarUrl }}" alt="" class="h-10 w-10 shrink-0 rounded-full object-cover bg-white">
@else
    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-bt_primary-600 text-sm font-semibold text-white">
        {{ $initials ?: '?' }}
    </span>
@endif
