{{-- A portal's Customers screen ($customers: organization_id, name, since; $invites: id, email, name, expires_at) --}}
<x-workbench-layout :title="__('Customers')">
    <x-slot name="header">{{ __('Customers') }}</x-slot>

    <div class="space-y-6">
        @if (session('success'))<x-callout icon="fa-solid fa-check" :title="session('success')" />@endif
        @error('portal')<x-callout icon="fa-solid fa-triangle-exclamation" :title="$message" />@enderror

        <x-card :title="__('Invite a customer')" :description="__('We email them a link. They accept it for one of their organizations — or start a new one — and your portal appears under their Partner Portals.')">
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
                            <span>{{ $invite['name'] ? $invite['name'] . ' · ' : '' }}{{ $invite['email'] }} <span class="text-zinc-400">· {{ __('expires :date', ['date' => $invite['expires_at']]) }}</span></span>
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
                    <span>{{ $customer['name'] }} <span class="text-zinc-400">· {{ __('since :date', ['date' => $customer['since']]) }}</span></span>
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
