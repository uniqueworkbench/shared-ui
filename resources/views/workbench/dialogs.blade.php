{{--
    Custom dialogs — the apps never use the browser's own alert(), confirm() or prompt(). Included once by the layouts.

    Asking before a form is sent: put `data-confirm="Question?"` on the <form>, or on the submit <button> that needs it
    (so a form with two buttons asks only for that one), optionally `data-confirm-title="Delete project"` and
    `data-confirm-button="Delete"` (the red button's label, default Confirm):
        <form method="POST" action="…" data-confirm="Delete this project?" data-confirm-title="Delete project" data-confirm-button="Delete">

    From a script (each returns a promise):
        if (await window.uwConfirm('Move it?', { title: 'Move work order', button: 'Move' })) { … }
        await window.uwAlert('That change was not saved.', { title: 'Not saved' });
        const reason = await window.uwPrompt('Why?', { title: 'Reject time', placeholder: 'Reason', required: true, button: 'Reject' }); // text, or null when cancelled

    Alpine can still dispatch the window events directly: confirm-action ({ title, message, confirmLabel, callback, onCancel }),
    alert-action ({ title, message, onClose }), input-action ({ title, message, value, placeholder, required, button, callback, onCancel }).
--}}
<x-alert-modal/>
<x-input-modal/>
<script>
(() => {
    if (window.uwConfirm) return;

    const fire = (name, detail) => window.dispatchEvent(new CustomEvent(name, { detail }));

    window.uwConfirm = (message, options = {}) => new Promise((resolve) => fire('confirm-action', {
        title: options.title, message, confirmLabel: options.button, loading: false,
        callback: () => resolve(true), onCancel: () => resolve(false),
    }));
    window.uwAlert = (message, options = {}) => new Promise((resolve) => fire('alert-action', {
        title: options.title, message, onClose: () => resolve(),
    }));
    window.uwPrompt = (message, options = {}) => new Promise((resolve) => fire('input-action', {
        title: options.title, message, value: options.value, placeholder: options.placeholder, required: options.required, button: options.button,
        callback: (value) => resolve(value), onCancel: () => resolve(null),
    }));

    // data-confirm on a form, or on the submit button that sent it: ask first, then send it the same way (same button, so its name/value go too)
    const confirmed = new WeakSet();
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;
        const submitter = event.submitter;
        const asker = submitter && submitter.hasAttribute('data-confirm') ? submitter : (form.hasAttribute('data-confirm') ? form : null);
        if (!asker) return;
        if (confirmed.has(form)) { confirmed.delete(form); return; }

        event.preventDefault();
        fire('confirm-action', {
            title: asker.dataset.confirmTitle,
            message: asker.dataset.confirm,
            confirmLabel: asker.dataset.confirmButton,
            callback: () => {
                confirmed.add(form);
                form.requestSubmit(submitter && submitter.form === form ? submitter : undefined);
            },
        });
    }, true);
})();
</script>
