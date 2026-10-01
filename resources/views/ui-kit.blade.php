{{--
    The Unique Workbench look, built from the shared components — the reference every page follows.
    Served at /ui-kit outside production (SharedUiServiceProvider). Copy its patterns; don't restyle them per app.
--}}
@php
    $apps = [
        ['Schedule Creator', 'Create and manage schedules for your team.', 'fa-regular fa-calendar-plus', 'gray', 'Employees'],
        ['Schedule Viewer', 'View schedules and make changes (according to permissions).', 'fa-regular fa-calendar-check', 'green', 'Employees'],
        ['Time Card', 'Clock in, clock out and view time cards.', 'fa-regular fa-file-lines', 'purple', 'Employees'],
        ['Clock In & Out', 'Track employee clock in and out times.', 'fa-regular fa-clock', 'orange', 'Employees'],
        ['Time Card Approvals', 'View, edit and approve time cards.', 'fa-regular fa-square-check', 'teal', 'Managers, Admins'],
        ['Payroll', 'Process payroll and manage payroll data.', 'fa-solid fa-file-invoice-dollar', 'red', 'Admins'],
    ];
@endphp
<x-workbench-layout title="UI kit">
    @push('sidebar')
        {{-- A tip at the bottom of the menu --}}
        <div class="mt-8 p-4 border border-white/15 rounded-md flex gap-3 text-sm leading-relaxed text-zinc-200">
            <i class="fa-regular fa-lightbulb mt-0.5 text-lg"></i>
            <p><strong class="text-white">The Toolbox</strong> gives you control over which apps, users and connections can access your tools.</p>
        </div>
    @endpush

    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_22rem] 2xl:grid-cols-[minmax(0,1fr)_26rem] gap-8 items-start">
        <div class="space-y-8 min-w-0">
            {{-- Page introduction --}}
            <x-callout icon="fa-solid fa-toolbox" title="The Toolbox" href="#" link="View Your Toolbox">
                Select the apps you want to use, and set up who can access them and which connections they can work with.
            </x-callout>

            {{-- Section heading with a search on the right --}}
            <div>
                <x-page-header title="Available Apps" description="Choose from the available tools below. Each app can be configured with specific users and connections before adding it to your toolbox.">
                    <x-slot name="actions">
                        <label class="relative block w-72 max-w-full">
                            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-zinc-400"></i>
                            <x-text-input type="search" placeholder="Search apps…" class="w-full pl-10" />
                        </label>
                    </x-slot>
                </x-page-header>

                {{-- A grid of item cards --}}
                <div class="grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-3 gap-6">
                    @foreach ($apps as [$name, $description, $icon, $color])
                        <div class="relative flex flex-col p-6 bg-white border border-zinc-200 rounded-md">
                            <input type="checkbox" class="absolute top-5 right-5 h-5 w-5 rounded border-zinc-300 text-bt_primary-600 focus:ring-bt_primary-500" aria-label="Select {{ $name }}">
                            <x-icon-circle :icon="$icon" :color="$color" size="lg" />
                            <h3 class="mt-6 text-xl font-bold tracking-tight text-zinc-900">{{ $name }}</h3>
                            <p class="mt-2 text-base leading-relaxed text-zinc-600">{{ $description }}</p>
                            <x-list-group class="mt-6">
                                <x-list-row href="#" icon="fa-regular fa-user" title="Users" subtitle="Select users" />
                                <x-list-row href="#" icon="fa-solid fa-diagram-project" title="Connections" subtitle="Select connections" />
                            </x-list-group>
                            <x-outline-button class="mt-5 w-full">Configure &amp; Add</x-outline-button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- A side panel: a card with a pill, item rows, the main action and a note --}}
        <x-card title="Your Toolbox" description="These are the apps currently available in your organization's toolbox.">
            <x-slot name="aside"><x-pill>{{ count($apps) }} Apps</x-pill></x-slot>
            <div class="space-y-3">
                @foreach ($apps as [$name, , $icon, $color, $users])
                    <x-list-row bordered :title="$name" class="py-4">
                        <x-slot name="leading"><x-icon-circle :icon="$icon" :color="$color" /></x-slot>
                        <p class="mt-1 text-xs text-zinc-600">Users: {{ $users }}</p>
                        <p class="text-xs text-zinc-600">Connections: Lifeguard Management</p>
                        <x-slot name="actions">
                            <a href="#" class="text-zinc-700 hover:text-bt_primary-600" aria-label="Settings"><i class="fa-solid fa-gear"></i></a>
                            <a href="#" class="text-zinc-700 hover:text-bt_primary-600" aria-label="Open"><i class="fa-solid fa-chevron-right text-xs"></i></a>
                        </x-slot>
                    </x-list-row>
                @endforeach
            </div>
            <x-primary-button class="mt-6 w-full py-3.5 text-base"><i class="fa-solid fa-gear"></i> Manage Toolbox Settings</x-primary-button>
            <x-info-box class="mt-6" href="#" link="Go to Toolbox Settings">Need to remove an app or change its permissions?</x-info-box>
        </x-card>
    </div>

    {{-- Forms and tables in the same style --}}
    <div class="mt-10 grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
        <x-card title="A form" description="Labels above fields; the main action on the right.">
            <div class="space-y-4">
                <div>
                    <x-input-label for="kit-name" value="Name" />
                    <x-text-input id="kit-name" class="block mt-1 w-full" value="Lakeside Pool" />
                </div>
                <div class="flex justify-end gap-3">
                    <x-secondary-button>Cancel</x-secondary-button>
                    <x-primary-button>Save</x-primary-button>
                </div>
            </div>
        </x-card>
        <x-card title="A table" :padded="false">
            <table class="min-w-full divide-y divide-zinc-200 text-sm">
                <thead class="bg-zinc-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                    <tr><td class="px-6 py-4 font-medium text-zinc-900">Lakeside Pool</td><td class="px-6 py-4"><x-pill color="green">Active</x-pill></td></tr>
                    <tr><td class="px-6 py-4 font-medium text-zinc-900">Hilltop Club</td><td class="px-6 py-4"><x-pill color="gray">Inactive</x-pill></td></tr>
                </tbody>
            </table>
        </x-card>
    </div>
</x-workbench-layout>
