{{--
    An item of <x-data-table.actions>: a link (href), or a button that submits a form (action, method —
    POST, PATCH, PUT, DELETE) and asks first when `confirm` is the question to ask (`confirm-title` its title).
    `disabled` (the reason why, shown on hover) greys it out; `danger` makes it red.
    <x-data-table.action icon="fa-regular fa-comment" :href="…">Message</x-data-table.action>
    <x-data-table.action icon="fa-solid fa-trash" :action="…" method="DELETE" confirm="Remove them?" danger>Remove</x-data-table.action>
--}}
@props(['href' => null, 'action' => null, 'method' => 'POST', 'icon' => null, 'confirm' => null, 'confirmTitle' => null, 'disabled' => null, 'danger' => false])
@php
    $item = 'flex w-full items-center gap-3 px-4 py-2 text-sm text-left';
    $enabled = $item . ($danger ? ' text-bt_primary-700 hover:bg-bt_primary-50' : ' text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900');
    $iconTag = $icon ? '<i class="' . e($icon) . ' w-4 text-center"></i>' : '';
@endphp
@if ($disabled)
    <span role="menuitem" aria-disabled="true" title="{{ $disabled }}" {{ $attributes->class($item . ' text-zinc-400 cursor-not-allowed') }}>{!! $iconTag !!}{{ $slot }}</span>
@elseif ($action)
    <form method="POST" action="{{ $action }}"
          @if ($confirm) x-data @submit.prevent="$dispatch('confirm-action', { title: {{ Js::from($confirmTitle ?? 'Are you sure?') }}, message: {{ Js::from($confirm) }}, callback: () => $el.submit() })" @endif>
        @csrf
        @if (strtoupper($method) !== 'POST')
            @method(strtoupper($method))
        @endif
        <button type="submit" role="menuitem" {{ $attributes->class($enabled) }}>{!! $iconTag !!}{{ $slot }}</button>
    </form>
@else
    <a href="{{ $href }}" role="menuitem" {{ $attributes->class($enabled) }}>{!! $iconTag !!}{{ $slot }}</a>
@endif
