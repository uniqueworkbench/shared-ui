{{-- Bordered rows stacked with dividers — wrap <x-list-row>s in it --}}
<div {{ $attributes->merge(['class' => 'border border-zinc-200 rounded-md divide-y divide-zinc-200 overflow-hidden bg-white']) }}>
    {{ $slot }}
</div>
