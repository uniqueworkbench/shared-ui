{{--
    The bars a Super Admin sees under the header, nothing for anyone else:
      - Admin Mode (red): they turned it on from the user menu (workbench()->isAdmin(); off at every sign-in), with a way to turn it off.
      - Viewing as an owner (amber, like an impersonation — not Admin Mode): they opened a portal as its owner from the account app's
        Admin → Portals (workbench()->portalAsOwner()), with a link back that ends it.
--}}
@if (function_exists('workbench'))
    @if (workbench()->portalAsOwner())
        @php
            $asOwner = workbench()->portalOwnerName();
            $stopViewing = rtrim((string) config('shared-ui.portal_url'), '/') . '/admin/portals/' . (workbench()->portal()['id'] ?? 0) . '/exit';
        @endphp
        <div data-portal-as-owner class="sticky top-0 z-20 bg-amber-50 text-amber-900 border-b border-amber-200 px-4 py-2 text-sm flex flex-wrap justify-center items-center gap-x-4 gap-y-1 sm:grid sm:grid-cols-[1fr_auto_1fr]">
            <span class="hidden sm:block"></span>
            <span class="text-center">
                @if ($asOwner)
                    {{ __("You're viewing this portal as :owner's owner (acting as a Super Admin).", ['owner' => $asOwner]) }}
                @else
                    {{ __("You're viewing this portal, which has no owner, as a Super Admin.") }}
                @endif
            </span>
            <a href="{{ $stopViewing }}" class="underline font-semibold whitespace-nowrap sm:justify-self-end">{{ __('Stop viewing as owner') }}</a>
        </div>
    @endif

    @if (workbench()->isAdmin() && \Illuminate\Support\Facades\Route::has('workbench.admin-mode'))
        <div data-admin-mode-bar class="sticky top-0 z-20 bg-red-50 text-red-800 border-b border-red-100 px-4 py-2 text-sm flex flex-wrap justify-center items-center gap-x-4 gap-y-1 sm:grid sm:grid-cols-[1fr_auto_1fr]">
            <span class="hidden sm:block"></span>
            <span class="text-center"><strong>{{ __('Admin Mode') }}</strong> — {{ __("You're currently acting as a Super Admin") }}</span>
            <form method="POST" action="{{ route('workbench.admin-mode') }}" class="sm:justify-self-end">
                @csrf
                <input type="hidden" name="on" value="0">
                <button type="submit" class="underline font-semibold whitespace-nowrap">{{ __('Exit Admin Mode') }}</button>
            </form>
        </div>
    @endif
@endif
