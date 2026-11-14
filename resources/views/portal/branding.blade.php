{{-- A portal's Branding screen ($brand: primary_color, secondary_color, accent_color, icon, customer_icon) --}}
@php
    $icons = ['fa-building', 'fa-handshake', 'fa-store', 'fa-briefcase', 'fa-truck', 'fa-box', 'fa-wrench', 'fa-screwdriver-wrench', 'fa-hard-hat', 'fa-industry', 'fa-warehouse', 'fa-leaf', 'fa-heart', 'fa-star', 'fa-bolt', 'fa-shield-halved', 'fa-graduation-cap', 'fa-stethoscope', 'fa-utensils', 'fa-car', 'fa-house', 'fa-globe', 'fa-chart-line', 'fa-users'];
    $colors = ['primary_color' => __('Primary colour'), 'secondary_color' => __('Secondary colour'), 'accent_color' => __('Accent colour')];
    $iconFields = ['icon' => __('Portal icon'), 'customer_icon' => __('Icon customers see on their Partner Portals tile')];
@endphp
<x-workbench-layout :title="__('Branding')">
    <x-slot name="header">{{ __('Branding') }}</x-slot>

    <div class="space-y-6">
        @if (session('success'))<x-callout icon="fa-solid fa-check" :title="session('success')" />@endif
        @error('portal')<x-callout icon="fa-solid fa-triangle-exclamation" :title="$message" />@enderror

        <form method="POST" action="{{ route('portal.branding.update') }}" data-dirty-form class="space-y-6">
            @csrf @method('PUT')
            <x-card :title="__('Colours')" :description="__('How your portal looks to you and your customers.')">
                <div class="grid gap-4 sm:grid-cols-3">
                    @foreach ($colors as $key => $label)
                        <div>
                            <x-input-label :for="$key" :value="$label" />
                            <input id="{{ $key }}" name="{{ $key }}" type="color" value="{{ old($key, $brand[$key] ?? '#2563eb') }}" class="mt-1 h-10 w-full rounded border border-zinc-300">
                        </div>
                    @endforeach
                </div>
            </x-card>

            @foreach ($iconFields as $key => $label)
                <x-card :title="$label">
                    <div class="grid grid-cols-6 sm:grid-cols-8 gap-2">
                        @foreach ($icons as $icon)
                            <label class="flex h-12 items-center justify-center rounded-md border border-zinc-200 cursor-pointer text-zinc-600 has-[:checked]:border-bt_primary-500 has-[:checked]:bg-bt_primary-50 has-[:checked]:text-bt_primary-700">
                                <input type="radio" name="{{ $key }}" value="{{ $icon }}" class="sr-only" @checked(old($key, $brand[$key] ?? null) === $icon)>
                                <i class="fa-solid {{ $icon }} text-lg"></i>
                            </label>
                        @endforeach
                    </div>
                </x-card>
            @endforeach

            <div><x-primary-button>{{ __('Save branding') }}</x-primary-button></div>
        </form>
    </div>
</x-workbench-layout>
