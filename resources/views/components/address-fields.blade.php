@props([
    'prefix'  => '',
    'street'  => '',
    'street2' => '',
    'city'    => '',
    'state'   => '',
    'zip'     => '',
    'country' => 'US',
    'required' => false,
])

<div x-data="{ country: '{{ $country }}' }">

    {{-- Street --}}
    <div class="mb-4">
        <x-input-label for="{{ $prefix }}street" :value="__('Street Address')" />
        <x-text-input id="{{ $prefix }}street" class="block mt-1 w-full" type="text"
            name="{{ $prefix }}street" :value="$street"
            :required="$required" autocomplete="address-line1" />
        <x-input-error :messages="$errors->get($prefix . 'street')" class="mt-2" />
    </div>

    {{-- Street 2 --}}
    <div class="mb-4">
        <x-input-label for="{{ $prefix }}street2" :value="__('Apt / Suite / Unit')" />
        <x-text-input id="{{ $prefix }}street2" class="block mt-1 w-full" type="text"
            name="{{ $prefix }}street2" :value="$street2"
            autocomplete="address-line2" />
        <x-input-error :messages="$errors->get($prefix . 'street2')" class="mt-2" />
    </div>

    {{-- Country --}}
    <div class="mb-4">
        <x-input-label for="{{ $prefix }}country" :value="__('Country')" />
        <select id="{{ $prefix }}country" name="{{ $prefix }}country" x-model="country"
            autocomplete="country" @if($required) required @endif
            class="block mt-1 w-full border-gray-300 focus:border-bt_primary-500 focus:ring-bt_primary-500 rounded-md shadow-sm">
            <option value="US" @selected($country === 'US')>United States</option>
            <option value="CA" @selected($country === 'CA')>Canada</option>
        </select>
        <x-input-error :messages="$errors->get($prefix . 'country')" class="mt-2" />
    </div>

    {{-- City --}}
    <div class="mb-4">
        <x-input-label for="{{ $prefix }}city" :value="__('City')" />
        <x-text-input id="{{ $prefix }}city" class="block mt-1 w-full" type="text"
            name="{{ $prefix }}city" :value="$city"
            :required="$required" autocomplete="address-level2" />
        <x-input-error :messages="$errors->get($prefix . 'city')" class="mt-2" />
    </div>

    {{-- State / Province + ZIP / Postal Code --}}
    <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="{{ $prefix }}state" x-text="country === 'CA' ? 'Province' : 'State'" />
            <select id="{{ $prefix }}state" name="{{ $prefix }}state"
                autocomplete="address-level1" @if($required) required @endif
                class="block mt-1 w-full border-gray-300 focus:border-bt_primary-500 focus:ring-bt_primary-500 rounded-md shadow-sm">
                <option value="">--</option>

                {{-- US States --}}
                <template x-if="country === 'US'">
                    <optgroup label="US States">
                        @foreach([
                            'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas',
                            'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware',
                            'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho',
                            'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas',
                            'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland',
                            'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi',
                            'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada',
                            'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York',
                            'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma',
                            'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
                            'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah',
                            'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia',
                            'WI' => 'Wisconsin', 'WY' => 'Wyoming', 'DC' => 'District of Columbia',
                        ] as $code => $stateName)
                            <option value="{{ $code }}" @selected($state === $code)>{{ $stateName }}</option>
                        @endforeach
                    </optgroup>
                </template>

                {{-- Canadian Provinces --}}
                <template x-if="country === 'CA'">
                    <optgroup label="Canadian Provinces">
                        @foreach([
                            'AB' => 'Alberta', 'BC' => 'British Columbia', 'MB' => 'Manitoba',
                            'NB' => 'New Brunswick', 'NL' => 'Newfoundland and Labrador',
                            'NS' => 'Nova Scotia', 'NT' => 'Northwest Territories',
                            'NU' => 'Nunavut', 'ON' => 'Ontario', 'PE' => 'Prince Edward Island',
                            'QC' => 'Quebec', 'SK' => 'Saskatchewan', 'YT' => 'Yukon',
                        ] as $code => $provName)
                            <option value="{{ $code }}" @selected($state === $code)>{{ $provName }}</option>
                        @endforeach
                    </optgroup>
                </template>
            </select>
            <x-input-error :messages="$errors->get($prefix . 'state')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="{{ $prefix }}zip" x-text="country === 'CA' ? 'Postal Code' : 'ZIP Code'" />
            <x-text-input id="{{ $prefix }}zip" class="block mt-1 w-full" type="text"
                name="{{ $prefix }}zip" :value="$zip"
                :required="$required"
                x-bind:placeholder="country === 'CA' ? 'A1A 1A1' : '12345'"
                autocomplete="postal-code" />
            <x-input-error :messages="$errors->get($prefix . 'zip')" class="mt-2" />
        </div>
    </div>

</div>
