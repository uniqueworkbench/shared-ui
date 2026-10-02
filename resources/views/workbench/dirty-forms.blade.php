{{--
    Dirty forms: <form data-dirty-form> starts with its Save (submit) buttons disabled; changing any field
    enables them, along with a Cancel that puts every field back to how the page loaded (changing it back
    by hand disables them again). Included once by <x-workbench-layout>; opt in per form.

    - data-dirty-form="reload" — Cancel reloads the page instead: for forms whose fields come and go
      (rows added or removed by Alpine), which putting values back can't undo.
    - A button with data-dirty-cancel in the form is used as its Cancel; otherwise one is added before
      the first Save. A form with no Save (e.g. shown read-only) is left alone and its Cancel hidden. Submit buttons with data-dirty-ignore stay as they are, and buttons outside the
      form with form="<its id>" count as its Save buttons.
    - Fields an Alpine component keeps in its own state can put themselves back on Cancel:
      @dirty-reset.window="$event.detail.form.contains($el) && (value = initial)".
    - After a failed submit (validation errors on the page) forms start enabled, so it can be sent again.
--}}
@php $startDirty = isset($errors) && $errors->any(); @endphp
<script>
(() => {
    if (window.uwDirtyForms) return;

    const IGNORED = ['_token', '_method'];
    const START_DIRTY = @js($startDirty);
    const CANCEL_CLASS = 'inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white border border-zinc-300 rounded-md font-semibold text-sm text-zinc-700 hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-bt_primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150';

    const fields = (form) => [...form.elements].filter((el) => el.name && ! IGNORED.includes(el.name)
        && ! ['submit', 'button', 'reset', 'image'].includes(el.type) && ! el.matches(':disabled'));

    const valueOf = (el) => {
        if (el.type === 'checkbox' || el.type === 'radio') return el.checked ? el.value : null;
        if (el.type === 'file') return [...el.files].map((f) => f.name + ':' + f.size).join(',');
        if (el.tagName === 'SELECT' && el.multiple) return [...el.selectedOptions].map((o) => o.value).join(',');
        return el.value;
    };

    const snapshot = (form) => JSON.stringify(fields(form).map((el) => [el.name, valueOf(el)]));

    const saveButtons = (form) => [
        ...form.querySelectorAll('button[type=submit], button:not([type]), input[type=submit]'),
        ...(form.id ? document.querySelectorAll(`button[form="${CSS.escape(form.id)}"], input[type=submit][form="${CSS.escape(form.id)}"]`) : []),
    // Only buttons that submit this form (a button inside it can belong to another, via form="…")
    ].filter((button) => button.form === form && ! button.hasAttribute('data-dirty-ignore'));

    function init(form) {
        if (form.uwDirty) return;
        const saves = saveButtons(form);
        if (! saves.length) {
            // Nothing to save (e.g. shown read-only): no Cancel either
            form.querySelectorAll('[data-dirty-cancel]').forEach((button) => { button.hidden = true; });
            return;
        }

        let cancel = form.querySelector('[data-dirty-cancel]');
        if (! cancel) {
            cancel = document.createElement('button');
            cancel.type = 'button';
            cancel.dataset.dirtyCancel = '';
            cancel.className = CANCEL_CLASS;
            cancel.textContent = 'Cancel';
            const first = saves.find((button) => form.contains(button)) || saves[0];
            first.insertAdjacentElement('beforebegin', cancel);
            // Keep it apart from the Save it sits next to, whatever the container's spacing
            if (! first.parentElement.matches('.flex, .inline-flex, .grid')) cancel.style.marginRight = '0.75rem';
        }

        // Each field's starting value, to put back on Cancel
        const initial = fields(form).map((el) => ({
            el, value: el.value, checked: el.checked,
            selected: el.tagName === 'SELECT' ? [...el.options].map((o) => o.selected) : null,
        }));
        const original = snapshot(form);
        const state = form.uwDirty = { dirty: null, forced: START_DIRTY };

        const set = (dirty) => {
            if (state.dirty === dirty) return;
            state.dirty = dirty;
            // The classes make plain <button type="submit"> Saves look disabled too
            saves.forEach((button) => { button.disabled = ! dirty; button.classList.add('disabled:opacity-50', 'disabled:cursor-not-allowed'); });
            cancel.disabled = ! dirty;
            form.toggleAttribute('data-dirty', dirty);
        };
        const check = () => set(state.forced || snapshot(form) !== original);

        form.addEventListener('input', check);
        form.addEventListener('change', check);
        // Alpine-driven fields (hidden inputs, rows added or removed) change without input events
        form.addEventListener('click', () => setTimeout(check, 0));

        cancel.addEventListener('click', () => {
            if (form.dataset.dirtyForm === 'reload') {
                window.location.reload();
                return;
            }
            initial.forEach(({ el, value, checked, selected }) => {
                if (el.type === 'file') el.value = '';
                else if (el.type === 'checkbox' || el.type === 'radio') el.checked = checked;
                else if (selected) [...el.options].forEach((o, i) => { o.selected = selected[i]; });
                else el.value = value;
                // So x-model and other listeners follow
                el.dispatchEvent(new Event('input', { bubbles: true }));
                el.dispatchEvent(new Event('change', { bubbles: true }));
            });
            window.dispatchEvent(new CustomEvent('dirty-reset', { detail: { form } }));
            state.forced = false;
            setTimeout(check, 0);
        });

        set(state.forced);
    }

    const initAll = () => document.querySelectorAll('form[data-dirty-form]').forEach(init);
    window.uwDirtyForms = { init, initAll };

    // After Alpine has set its fields' first values (x-model, :value)
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => requestAnimationFrame(initAll));
    else requestAnimationFrame(initAll);
})();
</script>
