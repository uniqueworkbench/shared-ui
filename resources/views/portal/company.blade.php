{{-- A portal's Company Info screen ($company: name, street, street2, city, state, zip, country, phone, email, website, tax_id, footer) --}}
@php $v = fn (string $key) => old($key, $company[$key] ?? ''); @endphp
<x-workbench-layout :title="__('Company Info')">
    <x-slot name="header">{{ __('Company Info') }}</x-slot>

    <div class="space-y-6">
        @if (session('success'))<x-callout icon="fa-solid fa-check" :title="session('success')" />@endif
        @error('portal')<x-callout icon="fa-solid fa-triangle-exclamation" :title="$message" />@enderror

        <form method="POST" action="{{ route('portal.company.update') }}" data-dirty-form class="space-y-6">
            @csrf @method('PUT')

            <x-card :title="__('Company')" :description="__('Printed on quotes and the other documents your portal creates. Anything you leave empty is not shown.')">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <x-input-label for="name" :value="__('Company name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="$v('name')" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="tax_id" :value="__('Tax / registration number')" />
                        <x-text-input id="tax_id" name="tax_id" type="text" class="mt-1 block w-full" :value="$v('tax_id')" />
                        <x-input-error :messages="$errors->get('tax_id')" class="mt-2" />
                    </div>
                </div>
            </x-card>

            <x-card :title="__('Address')">
                <div class="grid gap-4 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <x-input-label for="street" :value="__('Street')" />
                        <x-text-input id="street" name="street" type="text" class="mt-1 block w-full" :value="$v('street')" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label for="street2" :value="__('Suite / unit')" />
                        <x-text-input id="street2" name="street2" type="text" class="mt-1 block w-full" :value="$v('street2')" />
                    </div>
                    <div class="sm:col-span-3">
                        <x-input-label for="city" :value="__('City')" />
                        <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="$v('city')" />
                    </div>
                    <div class="sm:col-span-1">
                        <x-input-label for="state" :value="__('State')" />
                        <x-text-input id="state" name="state" type="text" class="mt-1 block w-full" :value="$v('state')" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label for="zip" :value="__('ZIP')" />
                        <x-text-input id="zip" name="zip" type="text" class="mt-1 block w-full" :value="$v('zip')" />
                    </div>
                    <div class="sm:col-span-3">
                        <x-input-label for="country" :value="__('Country')" />
                        <x-text-input id="country" name="country" type="text" class="mt-1 block w-full" :value="$v('country')" />
                    </div>
                </div>
            </x-card>

            <x-card :title="__('Contact')">
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <x-input-label for="phone" :value="__('Phone')" />
                        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="$v('phone')" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="$v('email')" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="website" :value="__('Website')" />
                        <x-text-input id="website" name="website" type="url" class="mt-1 block w-full" placeholder="https://" :value="$v('website')" />
                        <x-input-error :messages="$errors->get('website')" class="mt-2" />
                    </div>
                </div>
            </x-card>

            <x-card :title="__('Document footer')" :description="__('A line or two at the bottom of your documents: terms, a thank-you, a licence number.')">
                <textarea id="footer" name="footer" rows="3" maxlength="500" class="block w-full rounded-md border-zinc-300 shadow-sm focus:border-bt_primary-500 focus:ring-bt_primary-500 text-sm">{{ $v('footer') }}</textarea>
                <x-input-error :messages="$errors->get('footer')" class="mt-2" />
            </x-card>

            <x-primary-button>{{ __('Save') }}</x-primary-button>
        </form>
    </div>
</x-workbench-layout>
