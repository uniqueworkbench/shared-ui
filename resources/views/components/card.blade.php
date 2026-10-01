{{--
    The standard container: white, a thin border, small corners. Optional heading (title, description,
    a pill or actions on the right). padded=false for tables and lists that run edge to edge.
    <x-card title="Your Toolbox" description="…"><x-slot name="aside"><x-pill>6 Apps</x-pill></x-slot>…</x-card>
--}}
@props(['title' => null, 'description' => null, 'padded' => true])
<section {{ $attributes->merge(['class' => 'bg-white border border-zinc-200 rounded-md']) }}>
    @if ($title || isset($aside))
        <div class="flex items-start justify-between gap-4 px-6 pt-6 {{ $padded ? '' : 'pb-4' }}">
            <div class="min-w-0">
                @if ($title)
                    <h3 class="text-xl font-bold tracking-tight text-zinc-900">{{ $title }}</h3>
                @endif
                @if ($description)
                    <p class="mt-1.5 text-sm leading-relaxed text-zinc-600">{{ $description }}</p>
                @endif
            </div>
            @isset($aside)
                <div class="shrink-0">{{ $aside }}</div>
            @endisset
        </div>
    @endif
    <div @class(['p-6' => $padded, 'pt-4' => $padded && ($title || isset($aside))])>
        {{ $slot }}
    </div>
</section>
