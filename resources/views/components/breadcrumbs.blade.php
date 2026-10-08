@php
    // The current page (last entry, no 'url') is always exactly one hop back
    // from its immediate parent link — route that one link through the
    // browser's own history instead of a fresh visit to its static href, so
    // returning to e.g. a filtered/paginated listing restores that state
    // instead of resetting it. Earlier ancestors may be more than one hop
    // back, so they stay plain links. Mirrors the same fix in measure's
    // AppHeader.jsx Breadcrumbs component.
    // Only when the previous page really is that link (the referrer's path):
    // after a form posted and redirected here, the page before is the form,
    // and going "back" would reopen it — then the link is followed instead.
    // (window.URL: in an inline handler, a bare URL is document.URL, a string.)
    $lastHasUrl = count($breadcrumbs) > 0 && isset($breadcrumbs[count($breadcrumbs) - 1]['url']);
    $immediateParentIndex = $lastHasUrl ? null : count($breadcrumbs) - 2;
    // The first crumb: config('shared-ui.breadcrumb_home') (['label' => …, 'url' => …]) when an app sets it
    // for a section of its own (e.g. a module's home page), else Home (the dashboard)
    $home = (array) config('shared-ui.breadcrumb_home', []);
    $homeUrl = $home['url'] ?? route('dashboard');
    $homeLabel = $home['label'] ?? 'Home';
    $backOnClick = "try { var r = document.referrer && new window.URL(document.referrer); if (r && r.origin === window.location.origin && r.pathname === new window.URL(this.href).pathname && window.history.length > 1) { event.preventDefault(); window.history.back(); } } catch (e) {}";
@endphp
@if(count($breadcrumbs) > 0)
<nav class="flex mb-4" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <li class="inline-flex items-center">
            <a href="{{ $homeUrl }}"
               @if($immediateParentIndex === -1) onclick="{{ $backOnClick }}" @endif
               class="text-sm font-medium text-zinc-600 hover:text-bt_primary-600">
                {{ $homeLabel }}
            </a>
        </li>
        @foreach($breadcrumbs as $index => $breadcrumb)
            <li>
                <div class="flex items-center">
                    <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    @if(isset($breadcrumb['url']))
                        <a href="{{ $breadcrumb['url'] }}"
                           @if($index === $immediateParentIndex) onclick="{{ $backOnClick }}" @endif
                           class="text-sm font-medium text-zinc-600 hover:text-bt_primary-600">
                            {{ $breadcrumb['label'] }}
                        </a>
                    @else
                        <span class="text-sm font-medium text-zinc-900">{{ $breadcrumb['label'] }}</span>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
</nav>
@endif