{{-- A portal's Customers screen ($customers: organization_id, name, since; $invites: id, email, name, expires_at, customer?) --}}
<x-workbench-layout :title="__('Customers')">
    <x-slot name="header">{{ __('Customers') }}</x-slot>

    <div class="space-y-6">
        @if (session('success'))<x-callout icon="fa-solid fa-check" :title="session('success')" />@endif
        @error('portal')<x-callout icon="fa-solid fa-triangle-exclamation" :title="$message" />@enderror

        <x-card :title="__('Add one of your customers')" :description="__('Pick from your Customers in Unique Workbench. We email their contact a link; once they accept, they show on the customer\'s page there too.')">
            <div x-data="{
                    q: '', results: [], busy: false, loaded: false, timer: null,
                    search() {
                        clearTimeout(this.timer);
                        this.timer = setTimeout(async () => {
                            this.busy = true;
                            try {
                                const r = await fetch(@js(route('portal.customers.search')) + '?q=' + encodeURIComponent(this.q), { headers: { Accept: 'application/json' } });
                                this.results = r.ok ? await r.json() : [];
                            } catch (e) { this.results = []; }
                            this.busy = false; this.loaded = true;
                        }, 250);
                    }
                }" x-init="search()">
                <x-input-label for="customer-search" :value="__('Search by name, account number or contact')" />
                <x-text-input id="customer-search" type="search" class="block mt-1 w-full" x-model="q" @input="search()" autocomplete="off" />
                <p x-show="loaded && ! busy && results.length === 0" x-cloak class="mt-3 text-sm text-zinc-500">{{ __('No matching customers. Add them under Customers in Unique Workbench, or invite by email below.') }}</p>
                <ul class="mt-3 divide-y divide-zinc-100" x-show="results.length" x-cloak>
                    <template x-for="c in results" :key="c.id">
                        <li class="flex flex-wrap items-center justify-between gap-3 py-2 text-sm">
                            <span>
                                <span class="font-medium" x-text="c.name"></span>
                                <span class="text-zinc-400" x-show="c.account_number" x-text="'· ' + c.account_number"></span>
                            </span>
                            <span x-show="c.portal_status === 'customer'" class="text-zinc-500">{{ __('Already a customer') }}<span x-show="c.portal_organization" x-text="' (' + c.portal_organization + ')'"></span></span>
                            <span x-show="c.portal_status === 'invited'" class="text-zinc-500">{{ __('Invited — waiting for a reply') }}</span>
                            <span x-show="! c.portal_status && c.contacts.length === 0" class="text-zinc-500">{{ __('No contact with an email address') }}</span>
                            <form x-show="! c.portal_status && c.contacts.length" method="POST" action="{{ route('portal.invites.store') }}" class="flex items-center gap-2">
                                @csrf
                                <input type="hidden" name="customer_id" :value="c.id">
                                <select name="email" class="rounded-md border-zinc-300 text-sm py-1">
                                    <template x-for="contact in c.contacts" :key="contact.email">
                                        <option :value="contact.email" x-text="contact.name + ' <' + contact.email + '>'"></option>
                                    </template>
                                </select>
                                <x-primary-button>{{ __('Invite') }}</x-primary-button>
                            </form>
                        </li>
                    </template>
                </ul>
            </div>
        </x-card>

        <x-card :title="__('Or invite by email')" :description="__('We email them a link. They accept it for one of their organizations — or start a new one — and your portal appears under their Partner Portals.')">
            <form method="POST" action="{{ route('portal.invites.store') }}" class="grid gap-3 sm:grid-cols-[1fr_1fr_auto] items-start">
                @csrf
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="old('email')" required />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="name" :value="__('Name (optional)')" />
                    <x-text-input id="name" name="name" type="text" class="block mt-1 w-full" :value="old('name')" />
                </div>
                <x-primary-button class="sm:mt-6">{{ __('Send invitation') }}</x-primary-button>
            </form>
        </x-card>

        @if (count($invites))
            <x-card :title="__('Waiting for a reply')">
                <ul class="divide-y divide-zinc-100">
                    @foreach ($invites as $invite)
                        <li class="flex flex-wrap items-center justify-between gap-3 py-2 text-sm">
                            <span>{{ $invite['name'] ? $invite['name'] . ' · ' : '' }}{{ $invite['email'] }}@if (! empty($invite['customer'])) <span class="text-zinc-400">· {{ $invite['customer']['name'] }}</span>@endif <span class="text-zinc-400">· {{ __('expires :date', ['date' => $invite['expires_at']]) }}</span></span>
                            <span class="flex gap-3">
                                <form method="POST" action="{{ route('portal.invites.resend', $invite['id']) }}">@csrf<button class="text-bt_primary-600 hover:text-bt_primary-800">{{ __('Resend') }}</button></form>
                                <form method="POST" action="{{ route('portal.invites.destroy', $invite['id']) }}">@csrf @method('DELETE')<button class="text-zinc-500 hover:text-zinc-800">{{ __('Withdraw') }}</button></form>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </x-card>
        @endif

        <x-card :title="__('Your customers')">
            @forelse ($customers as $customer)
                <div class="flex items-center justify-between gap-3 py-2 text-sm border-b border-zinc-100 last:border-0">
                    <span>{{ $customer['name'] }}@if (! empty($customer['customer']) && $customer['customer']['name'] !== $customer['name']) <span class="text-zinc-400">({{ $customer['customer']['name'] }})</span>@endif <span class="text-zinc-400">· {{ __('since :date', ['date' => $customer['since']]) }}</span></span>
                    <form method="POST" action="{{ route('portal.customers.destroy', $customer['organization_id']) }}" onsubmit="return confirm(@js(__('Remove :name as a customer?', ['name' => $customer['name']])))">
                        @csrf @method('DELETE')<button class="text-red-600 hover:text-red-800">{{ __('Remove') }}</button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-zinc-500">{{ __('No customers yet.') }}</p>
            @endforelse
        </x-card>
    </div>
</x-workbench-layout>
