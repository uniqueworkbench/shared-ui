{{-- "Return to Workbench" (the portal), at the very top of the menu. --}}
<a href="{{ config('shared-ui.portal_url') }}" data-nav-link title="{{ __('Return to Workbench') }}"
   class="flex items-center gap-3 px-3 py-2 rounded-md text-[0.8rem] font-medium text-zinc-200 hover:bg-white/5 hover:text-white">
    <i class="fa-solid fa-arrow-left w-4 text-center text-[0.9rem]"></i><span data-nav-label>{{ __('Return to Workbench') }}</span>
</a>
