{{-- A portal's Customers screen: the owner's Customers from the account app, each in the portal, invited or neither
     ($customers: id, name, account_number, contacts[name,email,phone,is_primary], portal_status customer|invited|null, organization_id,
     organization_name, since, invite{id,email,phone,sent_to,expires_at}; $page, $lastPage, $total, $search) --}}
<x-workbench-layout :title="__('Customers')">
    <x-slot name="header">{{ __('Customers') }}</x-slot>

    <div class="space-y-6">
        @if (session('success'))<x-callout icon="fa-solid fa-check" :title="session('success')" />@endif
        @error('portal')<x-callout icon="fa-solid fa-triangle-exclamation" :title="$message" />@enderror

        <x-card :title="__('Your customers')" :description="__('The same customers as in Unique Workbench. Invite the ones who should use this portal — we email or text their contact a link; they accept it for one of their organizations, or start a new one, and your portal appears under their Partner Portals.')">
            <form method="GET" action="{{ route('portal.customers') }}" class="flex gap-2">
                <x-text-input name="q" type="search" class="block w-full" :value="$search" :placeholder="__('Search by name, account number or contact')" />
                <x-primary-button>{{ __('Search') }}</x-primary-button>
            </form>

            <div class="mt-4">
                @forelse ($customers as $customer)
                    <div class="flex flex-wrap items-center justify-between gap-3 py-3 text-sm border-b border-zinc-100 last:border-0">
                        <div class="min-w-0">
                            <span class="font-medium">{{ $customer['name'] }}</span>
                            @if ($customer['account_number'])<span class="text-zinc-400">· {{ $customer['account_number'] }}</span>@endif
                            <div class="text-xs text-zinc-500 truncate">
                                @if ($customer['portal_status'] === 'customer')
                                    {{ __('In the portal as :name since :date', ['name' => $customer['organization_name'], 'date' => $customer['since']]) }}
                                @elseif ($customer['portal_status'] === 'invited')
                                    {{ __('Invited at :to — expires :date', ['to' => $customer['invite']['sent_to'], 'date' => $customer['invite']['expires_at']]) }}
                                @elseif (count($customer['contacts']))
                                    {{ collect($customer['contacts'])->flatMap(fn ($c) => [$c['email'], $c['phone'] ? preg_replace('/^(\d{3})(\d{3})(\d{4})$/', '($1) $2-$3', $c['phone']) : null])->filter()->unique()->join(', ') }}
                                @else
                                    {{ __('No contact with an email address or phone number — add one to this customer in Unique Workbench.') }}
                                @endif
                            </div>
                        </div>

                        @if ($customer['portal_status'] === 'customer')
                            <span class="flex items-center gap-3">
                                <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">{{ __('In the portal') }}</span>
                                <form method="POST" action="{{ route('portal.customers.destroy', $customer['organization_id']) }}" data-confirm="{{ __('Remove :name from the portal?', ['name' => $customer['name']]) }}" data-confirm-button="{{ __('Remove') }}">
                                    @csrf @method('DELETE')<button class="text-red-600 hover:text-red-800">{{ __('Remove') }}</button>
                                </form>
                            </span>
                        @elseif ($customer['portal_status'] === 'invited')
                            <span class="flex items-center gap-3">
                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">{{ __('Invited') }}</span>
                                <form method="POST" action="{{ route('portal.invites.resend', $customer['invite']['id']) }}">@csrf<button class="text-bt_primary-600 hover:text-bt_primary-800">{{ __('Resend') }}</button></form>
                                <form method="POST" action="{{ route('portal.invites.destroy', $customer['invite']['id']) }}">@csrf @method('DELETE')<button class="text-zinc-500 hover:text-zinc-800">{{ __('Withdraw') }}</button></form>
                            </span>
                        @elseif (count($customer['contacts']))
                            <form method="POST" action="{{ route('portal.invites.store') }}" class="flex items-center gap-2">
                                @csrf
                                <input type="hidden" name="customer_id" value="{{ $customer['id'] }}">
                                @php
                                    // Each contact's email (a link by email) and phone (a link by text) is a way to invite them
                                    $ways = collect($customer['contacts'])->flatMap(fn ($c) => array_filter([
                                        $c['email'] ? ['via' => 'email:' . $c['email'], 'label' => $c['name'] . ' · ' . $c['email'] . ' (' . __('email') . ')'] : null,
                                        $c['phone'] ? ['via' => 'phone:' . $c['phone'], 'label' => $c['name'] . ' · ' . preg_replace('/^(\d{3})(\d{3})(\d{4})$/', '($1) $2-$3', $c['phone']) . ' (' . __('text') . ')'] : null,
                                    ]))->values();
                                @endphp
                                @if ($ways->count() > 1)
                                    <select name="via" class="rounded-md border-zinc-300 text-sm py-1" aria-label="{{ __('Invite by') }}">
                                        @foreach ($ways as $way)<option value="{{ $way['via'] }}">{{ $way['label'] }}</option>@endforeach
                                    </select>
                                @else
                                    <input type="hidden" name="via" value="{{ $ways->first()['via'] }}">
                                @endif
                                <x-primary-button>{{ __('Invite') }}</x-primary-button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-zinc-500">{{ $search !== '' ? __('No customers match that. Add one below.') : __('You have no customers yet. Add one below.') }}</p>
                @endforelse
            </div>

            @if ($lastPage > 1)
                <div class="mt-4 flex items-center justify-between text-sm text-zinc-600">
                    <span>{{ __('Page :page of :last · :total customers', ['page' => $page, 'last' => $lastPage, 'total' => $total]) }}</span>
                    <span class="flex gap-4">
                        @if ($page > 1)<a class="text-bt_primary-600 hover:text-bt_primary-800" href="{{ route('portal.customers', array_filter(['q' => $search, 'page' => $page - 1])) }}">{{ __('Previous') }}</a>@endif
                        @if ($page < $lastPage)<a class="text-bt_primary-600 hover:text-bt_primary-800" href="{{ route('portal.customers', array_filter(['q' => $search, 'page' => $page + 1])) }}">{{ __('Next') }}</a>@endif
                    </span>
                </div>
            @endif
        </x-card>

        <x-card :title="__('Add a customer')" :description="__('Adds them to your Customers in Unique Workbench, with their first contact. You can invite them to this portal right away.')">
            @error('new_customer')<x-callout icon="fa-solid fa-triangle-exclamation" :title="$message" class="mb-4" />@enderror
            <form method="POST" action="{{ route('portal.customers.store') }}" class="grid gap-3 sm:grid-cols-2">
                @csrf
                <div class="sm:col-span-2">
                    <x-input-label for="new-name" :value="__('Customer name')" />
                    <x-text-input id="new-name" name="name" type="text" class="block mt-1 w-full" :value="old('name')" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="new-first" :value="__('Contact first name')" />
                    <x-text-input id="new-first" name="first_name" type="text" class="block mt-1 w-full" :value="old('first_name')" required />
                    <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="new-last" :value="__('Contact last name')" />
                    <x-text-input id="new-last" name="last_name" type="text" class="block mt-1 w-full" :value="old('last_name')" />
                </div>
                <div>
                    <x-input-label for="new-email" :value="__('Contact email')" />
                    <x-text-input id="new-email" name="email" type="email" class="block mt-1 w-full" :value="old('email')" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="new-phone" :value="__('Contact phone (optional)')" />
                    <x-text-input id="new-phone" name="phone" type="text" class="block mt-1 w-full" :value="old('phone')" />
                </div>
                <label class="sm:col-span-2 flex items-center gap-2 text-sm text-zinc-700">
                    <input type="checkbox" name="invite" value="1" class="rounded border-zinc-300 text-bt_primary-600 focus:ring-bt_primary-500" @checked(old('invite', true))>
                    {{ __('Invite them to this portal now (by email, or by text if only a phone is given)') }}
                </label>
                <div class="sm:col-span-2"><x-primary-button>{{ __('Add customer') }}</x-primary-button></div>
            </form>
        </x-card>
    </div>
</x-workbench-layout>
