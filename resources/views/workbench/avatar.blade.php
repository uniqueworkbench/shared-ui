{{-- The signed-in user's initials in a red circle. --}}
@php
    $initials = collect(preg_split('/\s+/', trim((string) auth()->user()?->name)))
        ->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
@endphp
<span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-bt_primary-600 text-sm font-semibold text-white">
    {{ $initials ?: '?' }}
</span>
