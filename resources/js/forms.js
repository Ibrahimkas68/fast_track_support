document.addEventListener('submit', (event) => {
    const form = event.target.closest('[data-ticket-form]');
    if (!form) return;
    if (form.dataset.submitting === 'true') {
        event.preventDefault();
        return;
    }
    form.dataset.submitting = 'true';
    const button = form.querySelector('[data-submit-button]');
    if (button) button.disabled = true;
    const status = form.querySelector('[data-submit-status]');
    if (status) status.hidden = false;
});

window.addEventListener('pageshow', () => {
    document.querySelectorAll('[data-ticket-form]').forEach((form) => {
        delete form.dataset.submitting;
        const button = form.querySelector('[data-submit-button]');
        if (button) button.disabled = false;
        const status = form.querySelector('[data-submit-status]');
        if (status) status.hidden = true;
    });
    document.querySelector('[aria-invalid="true"]')?.focus();
});
