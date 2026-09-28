{{-- The signed-in user's initials. --}}
@php
    $initials = collect(preg_split('/\s+/', trim((string) auth()->user()?->name)))
        ->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
@endphp
<span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-bt_primary-100 text-xs font-semibold text-bt_primary-700">
    {{ $initials ?: '?' }}
</span>
