{{--
    The other apps of the package this app is the home app of (workbench()->appGroupMembers()), as tiles — each signs in to
    its app. Renders nothing in an app that isn't a group's home, or when the person may use none of them.
    <x-package-apps /> — optional :title (default: the package's name).
--}}
@props(['title' => null])
@php
    $members = workbench()->isGroupHome() ? workbench()->appGroupMembers() : [];
    $title ??= workbench()->appGroup()['package']['name'] ?? null;
@endphp
@if ($members)
    <section {{ $attributes->merge(['class' => 'space-y-3']) }} data-package-apps>
        @if ($title)
            <h2 class="text-lg font-bold text-zinc-900">{{ $title }}</h2>
        @endif
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
            @foreach ($members as $app)
                <a href="{{ $app['url'] }}" data-package-app="{{ $app['key'] }}"
                   class="flex items-center gap-3 p-4 bg-white border border-zinc-200 rounded-md hover:bg-zinc-50 transition">
                    @if (! empty($app['icon_url']))
                        <img src="{{ $app['icon_url'] }}" alt="" class="h-9 w-9 rounded-md object-cover">
                    @else
                        <x-icon-circle icon="fa-solid fa-cube" color="gray" />
                    @endif
                    <span class="text-sm font-semibold text-zinc-900 truncate">{{ $app['name'] }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
