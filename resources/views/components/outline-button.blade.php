{{-- A red-outlined action ("Configure & Add"): an <a> with href, a <button> without --}}
@props(['href' => null])
@php $class = 'inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white border border-bt_primary-600 rounded-md font-semibold text-sm text-bt_primary-600 hover:bg-bt_primary-50 focus:outline-none focus:ring-2 focus:ring-bt_primary-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150'; @endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $class]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'button', 'class' => $class]) }}>{{ $slot }}</button>
@endif
