{{-- A portal's Branding screen ($brand: title, palette, icon, customer_icon, icon_image_url, customer_icon_image_url, favicon_image_url) --}}
@php
    $icons = ['fa-building', 'fa-handshake', 'fa-store', 'fa-briefcase', 'fa-truck', 'fa-box', 'fa-wrench', 'fa-screwdriver-wrench', 'fa-hard-hat', 'fa-industry', 'fa-warehouse', 'fa-leaf', 'fa-heart', 'fa-star', 'fa-bolt', 'fa-shield-halved', 'fa-graduation-cap', 'fa-stethoscope', 'fa-utensils', 'fa-car', 'fa-house', 'fa-globe', 'fa-chart-line', 'fa-users'];
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

            <x-card :title="__('Colour palette')" :description="__('The top bar, the left menu and the buttons in your portal. Pick the one that best matches your company.')">
                @php $chosen = old('palette', $brand['palette'] ?? ($palettes[0]['id'] ?? null)); @endphp
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($palettes as $palette)
                        <label class="block cursor-pointer rounded-lg border border-zinc-200 p-2 has-[:checked]:border-bt_primary-500 has-[:checked]:ring-2 has-[:checked]:ring-bt_primary-500">
                            <input type="radio" name="palette" value="{{ $palette['id'] }}" class="sr-only" @checked($chosen === $palette['id'])>
                            {{-- A small picture of the portal in this palette: top bar, left menu, a button and a link --}}
                            <span class="block overflow-hidden rounded-md border border-zinc-200 bg-zinc-100" aria-hidden="true">
                                <span class="block h-3" style="background-color: {{ $palette['top'] }}"></span>
                                <span class="flex h-16">
                                    <span class="w-1/4 p-1 space-y-1" style="background-color: {{ $palette['side'] }}">
                                        <span class="block h-1.5 rounded-sm" style="background-color: {{ $palette['button'] }}"></span>
                                        <span class="block h-1.5 rounded-sm bg-white/25"></span>
                                        <span class="block h-1.5 rounded-sm bg-white/25"></span>
                                    </span>
                                    <span class="flex-1 p-2 space-y-1.5">
                                        <span class="block h-2 w-2/3 rounded-sm bg-zinc-300"></span>
                                        <span class="inline-block rounded px-2 py-0.5 text-[9px] font-semibold text-white" style="background-color: {{ $palette['button'] }}">{{ __('Button') }}</span>
                                    </span>
                                </span>
                            </span>
                            <span class="mt-2 block text-sm font-medium text-zinc-800">{{ $palette['name'] }}</span>
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('palette')" class="mt-2" />
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

            @php $favicon = $brand['favicon_image_url'] ?? null; @endphp
            <x-card :title="__('Favicon')" :description="__('The small picture on the browser tab while people are in your portal. Without one, the portal\'s own is used.')">
                <div class="flex flex-wrap items-center gap-4">
                    @if ($favicon)
                        <img src="{{ $favicon }}" alt="" class="h-10 w-10 rounded border border-zinc-200 object-contain bg-white p-1" data-current-image="favicon_image">
                    @endif
                    <div class="min-w-0">
                        <x-input-label for="favicon_image" :value="$favicon ? __('Replace the favicon') : __('Upload a favicon')" />
                        <input id="favicon_image" name="favicon_image" type="file" accept="image/png,image/jpeg,image/gif,image/webp" class="mt-1 block text-sm text-zinc-600">
                        <p class="mt-1 text-xs text-zinc-500">{{ __('Square, at least 64 × 64 pixels works best. PNG, JPG, GIF or WebP, up to 2 MB.') }}</p>
                        <x-input-error :messages="$errors->get('favicon_image')" class="mt-1" />
                    </div>
                    @if ($favicon)
                        <label class="flex items-center gap-2 text-sm text-zinc-700">
                            <input type="checkbox" name="remove_favicon_image" value="1" class="rounded border-zinc-300 text-bt_primary-600 focus:ring-bt_primary-500">
                            {{ __('Remove the favicon') }}
                        </label>
                    @endif
                </div>
            </x-card>

            <div><x-primary-button>{{ __('Save branding') }}</x-primary-button></div>
        </form>
    </div>
</x-workbench-layout>
