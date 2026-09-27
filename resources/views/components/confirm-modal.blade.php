<div
    x-data="{
        open: false,
        title: '',
        message: '',
        callback: null,
        show(event) {
            this.title   = event.detail.title   ?? 'Are you sure?';
            this.message = event.detail.message ?? '';
            this.callback = event.detail.callback;
            this.open = true;
        },
        confirm() {
            this.open = false;
            window.dispatchEvent(new CustomEvent('page-loading'));
            this.callback && this.callback();
        },
        cancel() {
            this.open = false;
        }
    }"
    @confirm-action.window="show($event)"
>
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
    >
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-gray-500/75" @click="cancel()"></div>

        {{-- Panel --}}
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md mx-auto"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
        >
            <div class="p-6">
                <div class="flex items-start gap-4">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-red-100 shrink-0">
                        <i class="fa-solid fa-triangle-exclamation text-red-600"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-base font-semibold text-gray-900" x-text="title"></h3>
                        <p class="mt-1 text-sm text-gray-500" x-text="message"></p>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 rounded-b-lg">
                <x-secondary-button @click="cancel()">Cancel</x-secondary-button>
                <x-danger-button @click="confirm()">Confirm</x-danger-button>
            </div>
        </div>
    </div>
</div>
