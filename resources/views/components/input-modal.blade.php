{{--
    Asks for a line of text — the custom stand-in for the browser's prompt(). Opened by the `input-action` window event
    ({ title, message, value, placeholder, required, button, callback(value), onCancel }) or, from a script,
    `const reason = await window.uwPrompt('Why?', { title, value, placeholder, required: true, button: 'Reject' })`
    (the text, or null when cancelled). Included once by the layouts, with <x-confirm-modal> and <x-alert-modal>.
--}}
<div
    x-data="{
        open: false,
        title: '',
        message: '',
        value: '',
        placeholder: '',
        required: false,
        button: 'OK',
        error: false,
        callback: null,
        onCancel: null,
        show(event) {
            const d = event.detail;
            this.title = d.title ?? 'Enter a value';
            this.message = d.message ?? '';
            this.value = d.value ?? '';
            this.placeholder = d.placeholder ?? '';
            this.required = !!d.required;
            this.button = d.button ?? 'OK';
            this.error = false;
            this.callback = d.callback ?? null;
            this.onCancel = d.onCancel ?? null;
            this.open = true;
            this.$nextTick(() => this.$refs.field && this.$refs.field.focus());
        },
        confirm() {
            if (this.required && this.value.trim() === '') {
                this.error = true;
                return;
            }
            this.open = false;
            this.callback && this.callback(this.value);
        },
        cancel() {
            this.open = false;
            this.onCancel && this.onCancel();
        }
    }"
    @input-action.window="show($event)"
    @keydown.escape.window="open && cancel()"
>
    <div x-show="open" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4" role="dialog" aria-modal="true"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-gray-500/75" @click="cancel()"></div>
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md mx-auto">
            <div class="p-6">
                <h3 class="text-base font-semibold text-gray-900" x-text="title"></h3>
                <p class="mt-1 text-sm text-gray-500 whitespace-pre-line" x-show="message" x-text="message"></p>
                <input type="text" x-ref="field" x-model="value" :placeholder="placeholder" @keydown.enter.prevent="confirm()"
                       class="mt-4 block w-full border-zinc-300 focus:border-bt_primary-500 focus:ring-bt_primary-500 rounded-md shadow-sm text-sm">
                <p class="mt-2 text-sm text-red-600" x-show="error" x-cloak>This is required.</p>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 rounded-b-lg">
                <x-secondary-button @click="cancel()">Cancel</x-secondary-button>
                <x-primary-button @click="confirm()"><span x-text="button"></span></x-primary-button>
            </div>
        </div>
    </div>
</div>
