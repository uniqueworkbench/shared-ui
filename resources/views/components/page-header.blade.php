{{--
    A page or section heading: bold title, optional description underneath, actions (search, buttons) on the right.
    <x-page-header title="Available Apps" description="Choose from…"><x-slot name="actions">…</x-slot></x-page-header>
--}}
@props(['title', 'description' => null, 'level' => 'h2'])
<div {{ $attributes->merge(['class' => 'flex flex-wrap items-start justify-between gap-4 mb-6']) }}>
    <div class="flex-1 min-w-[16rem] max-w-3xl">
        <{{ $level }} class="text-2xl font-bold tracking-tight text-zinc-900">{{ $title }}</{{ $level }}>
        @if ($description)
            <p class="mt-2 text-base leading-relaxed text-zinc-600">{{ $description }}</p>
        @endif
        {{ $slot }}
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-3 shrink-0">{{ $actions }}</div>
    @endisset
</div>
