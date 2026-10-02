{{--
    An address looked up in Google (Places API, US and Canada) rather than typed:
    pick a suggestion and street, city, state, ZIP and country are filled in, with
    the place id (verified) and, optionally, the map point. "Can't find it? Enter
    it manually" allows typing it — saved without a place id, shown as not verified.
    Apt / suite stays free text. Without config('shared-ui.google_maps_key') it's
    plain fields.

    Field names are $prefix + street, street2, city, state, zip, country; with
    :place-id="true" also {$prefix}place_id, with :coordinates="true" latitude and
    longitude. Two ways to use it:
    - in a form: <x-address-lookup prefix="address_" :values="[...]" place-id coordinates />
      — $values keyed by street, street2, …; renders the named (hidden) inputs
    - in an Alpine row: <x-address-lookup prefix="address_" target="location" place-id />
      — reads and writes that object's keys (address_street, …); renders no names,
      the row supplies its inputs
--}}
@props([
    'prefix' => '',
    'values' => [],
    'target' => null,
    'placeId' => false,
    'coordinates' => false,
    'required' => false,
    'label' => 'Address',
])
@php
    $google = filled(config('shared-ui.google_maps_key'));
    $names = collect(['street', 'street2', 'city', 'state', 'zip', 'country'])->mapWithKeys(fn ($f) => [$f => $prefix . $f])->all();
    if ($placeId) {
        $names['place_id'] = $prefix . 'place_id';
    }
    if ($coordinates) {
        $names += ['latitude' => 'latitude', 'longitude' => 'longitude'];
    }
    $initial = collect($names)->mapWithKeys(fn ($name, $field) => [$name => $values[$field] ?? ($field === 'country' ? 'US' : '')])->all();
    $input = 'block w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-bt_primary-500 focus:ring-bt_primary-500';
    $link = 'text-bt_primary-600 hover:text-bt_primary-800 underline-offset-2 hover:underline';
@endphp
<div x-data="uwAddressLookup(@js(['names' => $names, 'google' => $google, 'required' => (bool) $required]), {{ $target ?: 'null' }}, @js($initial))"
     {{ $attributes->merge(['class' => 'space-y-2']) }}>
    @if ($label)
        <x-input-label :value="__($label)" />
    @endif

    {{-- The chosen address --}}
    <div x-show="mode === 'display'" class="flex items-start justify-between gap-3 rounded-md border border-gray-300 bg-white px-3 py-2">
        <div class="min-w-0 text-sm text-gray-800">
            <template x-for="line in lines()"><div x-text="line"></div></template>
            <div x-show="google && names.place_id && verified()" class="mt-1 text-xs text-green-700"><i class="fa-solid fa-circle-check me-1"></i>Verified by Google</div>
            <div x-show="google && names.place_id && !verified()" class="mt-1 text-xs text-amber-700"><i class="fa-solid fa-triangle-exclamation me-1"></i>Entered manually — not verified</div>
        </div>
        <div class="flex shrink-0 gap-3 text-sm">
            <button type="button" @click="change()" class="{{ $link }}">Change</button>
            <button type="button" x-show="!required" @click="clear()" class="text-gray-500 hover:text-red-600">Remove</button>
        </div>
    </div>

    {{-- Looking it up --}}
    <div x-show="mode === 'search'" class="relative" @click.outside="suggestions = []">
        <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-2.5 text-sm text-gray-400"></i>
        <input type="text" x-ref="search" x-model="query" autocomplete="off" aria-label="{{ __('Search for an address') }}"
               placeholder="{{ __('Start typing an address…') }}" class="{{ $input }} ps-9"
               :required="required && mode === 'search' && !has()"
               @input.debounce.250ms="search()" @keydown.arrow-down.prevent="move(1)" @keydown.arrow-up.prevent="move(-1)"
               @keydown.enter.prevent="active >= 0 && choose(active)" @keydown.escape="suggestions = []">
        <ul x-show="suggestions.length" x-cloak class="absolute z-30 mt-1 max-h-72 w-full overflow-auto rounded-md border border-gray-200 bg-white py-1 shadow-lg" role="listbox">
            <template x-for="(s, i) in suggestions" :key="i">
                <li role="option" :aria-selected="active === i">
                    <button type="button" @click="choose(i)" @mouseenter="active = i"
                            class="w-full px-3 py-2 text-left text-sm" :class="active === i ? 'bg-gray-100' : ''">
                        <span class="font-medium text-gray-900" x-text="s.main"></span>
                        <span class="text-gray-500" x-text="s.secondary"></span>
                    </button>
                </li>
            </template>
            <li class="px-3 pt-1 text-right text-[11px] text-gray-400">Powered by Google</li>
        </ul>
        <div class="mt-1 flex flex-wrap items-center gap-x-3 text-xs">
            <span x-show="loading" class="text-gray-500"><i class="fa-solid fa-spinner fa-spin me-1"></i>Searching…</span>
            <span x-show="error" x-text="error" class="text-red-600"></span>
            <span class="ms-auto flex gap-3">
                <button type="button" x-show="has()" @click="mode = 'display'; suggestions = []" class="text-gray-500 hover:text-gray-800">Cancel</button>
                <button type="button" @click="manual()" class="{{ $link }}">Can't find it? Enter it manually</button>
            </span>
        </div>
    </div>

    {{-- Typed in (no key, or "enter it manually") --}}
    <div x-show="mode === 'manual'" class="space-y-2">
        <input type="text" x-model="addr[names.street]" maxlength="255" placeholder="{{ __('Street address') }}" aria-label="{{ __('Street address') }}"
               class="{{ $input }}" :required="required && mode === 'manual'" autocomplete="address-line1">
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
            <input type="text" x-model="addr[names.city]" maxlength="100" placeholder="{{ __('City') }}" aria-label="{{ __('City') }}"
                   class="{{ $input }} col-span-2" :required="required && mode === 'manual'" autocomplete="address-level2">
            <input type="text" x-model="addr[names.state]" maxlength="2" placeholder="{{ __('State') }}" aria-label="{{ __('State or province') }}"
                   class="{{ $input }} uppercase" :required="required && mode === 'manual'" autocomplete="address-level1"
                   @input="addr[names.state] = $event.target.value.toUpperCase()">
            <input type="text" x-model="addr[names.zip]" maxlength="10" placeholder="{{ __('ZIP') }}" aria-label="{{ __('ZIP or postal code') }}"
                   class="{{ $input }}" :required="required && mode === 'manual'" autocomplete="postal-code">
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <select x-model="addr[names.country]" aria-label="{{ __('Country') }}" class="{{ $input }} w-auto">
                <option value="US">United States</option>
                <option value="CA">Canada</option>
            </select>
            @if ($coordinates)
                <input type="number" step="any" x-model="addr.latitude" placeholder="{{ __('Latitude') }}" aria-label="{{ __('Latitude') }}" class="{{ $input }} w-32">
                <input type="number" step="any" x-model="addr.longitude" placeholder="{{ __('Longitude') }}" aria-label="{{ __('Longitude') }}" class="{{ $input }} w-32">
            @endif
        </div>
        <p x-show="google && names.place_id" class="text-xs text-amber-700">
            <i class="fa-solid fa-triangle-exclamation me-1"></i>Typed in, so it won't be verified.
            <button type="button" @click="change()" class="{{ $link }}">Search Google instead</button>
        </p>
    </div>

    {{-- Apt / suite: always free text --}}
    <input type="text" x-show="mode !== 'search' || has()" x-model="addr[names.street2]" maxlength="255"
           placeholder="{{ __('Apt / Suite / Unit (optional)') }}" aria-label="{{ __('Apt / Suite / Unit') }}" class="{{ $input }}"
           @unless ($target) name="{{ $names['street2'] }}" @endunless autocomplete="address-line2">

    @unless ($target)
        @foreach ($names as $field => $name)
            @if ($field !== 'street2')
                <input type="hidden" name="{{ $name }}" :value="addr['{{ $name }}']">
            @endif
        @endforeach
        <x-input-error :messages="collect($names)->flatMap(fn ($name) => $errors->get($name))->all()" class="mt-1" />
    @endunless
</div>

@once
    @push('scripts')
        <script>
            // Google's Places library, loaded on the first search
            window.uwPlaces = () => window.__uwPlaces ??= new Promise((resolve, reject) => {
                window.__uwGoogleMapsLoaded = () => google.maps.importLibrary('places').then(resolve, reject);
                const script = document.createElement('script');
                script.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(@js(config('shared-ui.google_maps_key') ?? ''))
                    + '&loading=async&callback=__uwGoogleMapsLoaded';
                script.async = true;
                script.onerror = () => { window.__uwPlaces = null; reject(new Error('Google Maps failed to load')); };
                document.head.appendChild(script);
            });

            window.uwAddressLookup = function (config, target, initial) {
                // Google objects stay out of Alpine's reactive state
                let predictions = [];
                let token = null;

                return {
                    names: config.names,
                    google: config.google,
                    required: config.required,
                    addr: target ?? initial,
                    mode: 'manual',
                    query: '',
                    suggestions: [],
                    active: -1,
                    loading: false,
                    error: '',

                    init() {
                        this.mode = this.has() ? 'display' : (this.google ? 'search' : 'manual');
                    },
                    v(field) {
                        return this.names[field] ? (this.addr[this.names[field]] ?? '') : '';
                    },
                    set(field, value) {
                        if (this.names[field]) this.addr[this.names[field]] = value;
                    },
                    has() {
                        return !!(this.v('street') || this.v('city'));
                    },
                    verified() {
                        return !!this.v('place_id');
                    },
                    lines() {
                        const place = [this.v('city'), [this.v('state'), this.v('zip')].filter(Boolean).join(' ')].filter(Boolean).join(', ');
                        return [this.v('street'), place, this.v('country') === 'CA' ? 'Canada' : ''].filter(Boolean);
                    },
                    async search() {
                        const query = this.query.trim();
                        this.error = '';
                        if (query.length < 3) { this.suggestions = []; return; }
                        this.loading = true;
                        try {
                            const { AutocompleteSuggestion, AutocompleteSessionToken } = await window.uwPlaces();
                            token ??= new AutocompleteSessionToken();
                            const { suggestions } = await AutocompleteSuggestion.fetchAutocompleteSuggestions({
                                input: query, sessionToken: token, includedRegionCodes: ['us', 'ca'],
                            });
                            if (query !== this.query.trim()) return; // a newer search is on its way
                            predictions = suggestions.map(s => s.placePrediction).filter(Boolean);
                            this.suggestions = predictions.map(p => ({
                                main: p.mainText?.text ?? p.text.text,
                                secondary: p.secondaryText?.text ?? '',
                            }));
                            this.active = this.suggestions.length ? 0 : -1;
                            if (!this.suggestions.length) this.error = 'No matches — keep typing, or enter it manually.';
                        } catch (e) {
                            console.error(e);
                            this.error = "Address search isn't available right now — enter it manually.";
                        } finally {
                            this.loading = false;
                        }
                    },
                    async choose(index) {
                        const prediction = predictions[index];
                        if (!prediction) return;
                        this.loading = true;
                        try {
                            const place = prediction.toPlace();
                            await place.fetchFields({ fields: ['addressComponents', 'location', 'formattedAddress'] });
                            token = null; // the session ends with the details request
                            const part = (type, short = false) => {
                                const c = (place.addressComponents || []).find(c => c.types.includes(type));
                                return c ? (short ? c.shortText : c.longText) : '';
                            };
                            const country = part('country', true);
                            if (!['US', 'CA'].includes(country)) {
                                this.error = 'Only US and Canadian addresses can be used.';
                                return;
                            }
                            this.set('street', [part('street_number'), part('route')].filter(Boolean).join(' ')
                                || (place.formattedAddress || '').split(',')[0]);
                            this.set('street2', part('subpremise') ? '#' + part('subpremise') : '');
                            this.set('city', part('locality') || part('postal_town') || part('sublocality_level_1')
                                || part('administrative_area_level_3') || part('neighborhood'));
                            this.set('state', part('administrative_area_level_1', true));
                            this.set('zip', part('postal_code'));
                            this.set('country', country);
                            this.set('place_id', place.id);
                            if (place.location) {
                                this.set('latitude', Number(place.location.lat().toFixed(7)));
                                this.set('longitude', Number(place.location.lng().toFixed(7)));
                            }
                            this.suggestions = [];
                            this.query = '';
                            this.error = '';
                            this.mode = 'display';
                        } catch (e) {
                            console.error(e);
                            this.error = "Couldn't get that address — try again, or enter it manually.";
                        } finally {
                            this.loading = false;
                        }
                    },
                    move(step) {
                        if (!this.suggestions.length) return;
                        this.active = (this.active + step + this.suggestions.length) % this.suggestions.length;
                    },
                    change() {
                        this.query = '';
                        this.suggestions = [];
                        this.error = '';
                        this.mode = this.google ? 'search' : 'manual';
                        this.$nextTick(() => this.$refs.search?.focus());
                    },
                    manual() {
                        this.set('place_id', '');
                        if (!this.has() && this.query) this.set('street', this.query.trim());
                        this.suggestions = [];
                        this.mode = 'manual';
                    },
                    clear() {
                        ['street', 'street2', 'city', 'state', 'zip', 'place_id', 'latitude', 'longitude'].forEach(f => this.set(f, ''));
                        this.change();
                    },
                };
            };
        </script>
    @endpush
@endonce
