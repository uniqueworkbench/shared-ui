{{-- A portal's Branding screen ($brand: title, primary_color, secondary_color, accent_color, icon, customer_icon, icon_image_url, customer_icon_image_url) --}}
@php
    $icons = ['fa-building', 'fa-handshake', 'fa-store', 'fa-briefcase', 'fa-truck', 'fa-box', 'fa-wrench', 'fa-screwdriver-wrench', 'fa-hard-hat', 'fa-industry', 'fa-warehouse', 'fa-leaf', 'fa-heart', 'fa-star', 'fa-bolt', 'fa-shield-halved', 'fa-graduation-cap', 'fa-stethoscope', 'fa-utensils', 'fa-car', 'fa-house', 'fa-globe', 'fa-chart-line', 'fa-users'];
    $colors = ['primary_color' => __('Primary colour'), 'secondary_color' => __('Secondary colour'), 'accent_color' => __('Accent colour')];
    // Each icon, and the picture that can be uploaded in its place
    $iconFields = [
        'icon' => ['label' => __('Portal icon'), 'description' => __('Your own tile on the home page, and the top left corner inside the portal.'), 'image' => 'icon_image'],
        'customer_icon' => ['label' => __('Icon customers see on their Partner Portals tile'), 'description' => __('Shown to your customers on their Partner Portals tile.'), 'image' => 'customer_icon_image'],
    ];
@endphp
<x-workbench-layout :title="__('Branding')">
    <x-slot name="header">{{ __('Branding') }}</x-slot>

    <div class="space-y-6">
        @if (session('success'))<x-callout icon="fa-solid fa-check" :title="session('success')" />@endif
        @error('portal')<x-callout icon="fa-solid fa-triangle-exclamation" :title="$message" />@enderror

        <form method="POST" action="{{ route('portal.branding.update') }}" enctype="multipart/form-data" data-dirty-form class="space-y-6">
            @csrf @method('PUT')
            <x-card :title="__('Title')" :description="__('Your portal name on your own tile and in the top left corner inside the portal. Leave it empty to use the portal\'s own name.')">
                <x-text-input id="title" name="title" type="text" maxlength="40" class="block w-full sm:max-w-md" :value="old('title', $brand['title'] ?? '')" />
                <x-input-error :messages="$errors->get('title')" class="mt-2" />
            </x-card>

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

            @foreach ($iconFields as $key => $field)
                @php $imageKey = $field['image']; $current = $brand[$imageKey . '_url'] ?? null; @endphp
                <x-card :title="$field['label']" :description="$field['description'] . ' ' . __('Choose an icon, or upload your own picture — a picture is used instead of the icon.')">
                    <div class="grid grid-cols-6 sm:grid-cols-8 gap-2">
                        @foreach ($icons as $icon)
                            <label class="flex h-12 items-center justify-center rounded-md border border-zinc-200 cursor-pointer text-zinc-600 has-[:checked]:border-bt_primary-500 has-[:checked]:bg-bt_primary-50 has-[:checked]:text-bt_primary-700">
                                <input type="radio" name="{{ $key }}" value="{{ $icon }}" class="sr-only" @checked(old($key, $brand[$key] ?? null) === $icon)>
                                <i class="fa-solid {{ $icon }} text-lg"></i>
                            </label>
                        @endforeach
                    </div>

                    <div class="mt-5 flex flex-wrap items-center gap-4 border-t border-zinc-100 pt-4">
                        @if ($current)
                            <img src="{{ $current }}" alt="" class="h-16 w-16 rounded-full border border-zinc-200 object-cover" data-current-image="{{ $imageKey }}">
                        @endif
                        <div class="min-w-0">
                            <x-input-label :for="$imageKey" :value="$current ? __('Replace the picture') : __('Upload a picture')" />
                            <input id="{{ $imageKey }}" name="{{ $imageKey }}" type="file" accept="image/png,image/jpeg,image/gif,image/webp" class="mt-1 block text-sm text-zinc-600">
                            <p class="mt-1 text-xs text-zinc-500">{{ __('Square works best. PNG, JPG, GIF or WebP, up to 2 MB.') }}</p>
                            <x-input-error :messages="$errors->get($imageKey)" class="mt-1" />
                        </div>
                        @if ($current)
                            <label class="flex items-center gap-2 text-sm text-zinc-700">
                                <input type="checkbox" name="remove_{{ $imageKey }}" value="1" class="rounded border-zinc-300 text-bt_primary-600 focus:ring-bt_primary-500">
                                {{ __('Remove picture (use the icon)') }}
                            </label>
                        @endif
                    </div>
                </x-card>
            @endforeach

            <div><x-primary-button>{{ __('Save branding') }}</x-primary-button></div>
        </form>
    </div>
</x-workbench-layout>
