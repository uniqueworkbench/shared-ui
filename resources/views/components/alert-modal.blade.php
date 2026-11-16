{{--
    A message with an OK button — the custom stand-in for the browser's alert(). Opened by the `alert-action` window event
    ({ title, message, onClose }) or, from a script, `await window.uwAlert('Message', { title })` (resolves when it's closed).
    Included once by the layouts, with <x-confirm-modal> and <x-input-modal>; never use the browser's own dialogs.
--}}
<div
    x-data="{
        open: false,
        title: '',
        message: '',
        onClose: null,
        show(event) {
            this.title = event.detail.title ?? 'Notice';
            this.message = event.detail.message ?? '';
            this.onClose = event.detail.onClose ?? null;
            this.open = true;
        },
        close() {
            this.open = false;
            this.onClose && this.onClose();
        }
    }"
    @alert-action.window="show($event)"
    @keydown.escape.window="open && close()"
>
    <div x-show="open" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4" role="alertdialog" aria-modal="true"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-gray-500/75" @click="close()"></div>
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md mx-auto">
            <div class="p-6">
                <div class="flex items-start gap-4">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-amber-100 shrink-0">
                        <i class="fa-solid fa-circle-info text-amber-600"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-base font-semibold text-gray-900" x-text="title"></h3>
                        <p class="mt-1 text-sm text-gray-500 whitespace-pre-line" x-text="message"></p>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 rounded-b-lg">
                <x-primary-button @click="close()">OK</x-primary-button>
            </div>
        </div>
    </div>
</div>
