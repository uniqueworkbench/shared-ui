{{--
    The Admin Mode bar in a portal: a Super Admin who opened it from the account app's Admin → Portals sees the portal as its owner,
    and is told so, with a way out (the account app ends the visit and goes back to Portals). Nothing for anyone else.
--}}
@if (function_exists('workbench') && workbench()->portalAdminMode())
    @php
        $adminOwner = workbench()->portalOwnerName();
        $adminExit = rtrim((string) config('shared-ui.portal_url'), '/') . '/admin/portals/' . (workbench()->portal()['id'] ?? 0) . '/exit';
    @endphp
    <div data-portal-admin-mode class="sticky top-0 z-20 bg-red-50 text-red-800 border-b border-red-100 px-4 py-2 text-sm flex flex-wrap justify-center items-center gap-x-4 gap-y-1 sm:grid sm:grid-cols-[1fr_auto_1fr]">
        <span class="hidden sm:block"></span>
        <span class="text-center"><strong>{{ __('Admin Mode') }}</strong> — {{ $adminOwner
            ? __('You\'re viewing this portal as :owner\'s owner, acting as a Super Admin', ['owner' => $adminOwner])
            : __('You\'re viewing this portal, which has no owner, as a Super Admin') }}</span>
        <a href="{{ $adminExit }}" class="underline font-semibold whitespace-nowrap sm:justify-self-end">{{ __('Exit Admin Mode') }}</a>
    </div>
@endif
