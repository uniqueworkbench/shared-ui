{{-- "Return to Workbench", at the very top of the menu — or, in an app that's one of a group's other apps, "Return to <the group's home app>". --}}
@php $return = workbench()->returnLink(); @endphp
@if ($return)
    <a href="{{ $return['url'] }}" data-nav-link @if ($return['group']) data-group-return @endif title="{{ $return['label'] }}"
       class="flex items-center gap-3 px-3 py-2 rounded-md text-[0.8rem] font-medium text-zinc-200 hover:bg-white/5 hover:text-white">
        <i class="fa-solid fa-arrow-left w-4 text-center text-[0.9rem]"></i><span data-nav-label>{{ $return['label'] }}</span>
    </a>
@endif
