const panel = document.querySelector('#ticket-panel');
let trigger = null;
let controller = null;
let previousOverflow = '';

if (panel && typeof panel.showModal === 'function') {
    const content = panel.querySelector('[data-panel-content]');
    const heading = panel.querySelector('#ticket-panel-title');
    const closeButton = panel.querySelector('[data-panel-close]');

    document.addEventListener('click', async (event) => {
        const link = event.target.closest('a[data-panel-link]');
        if (!link || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || link.target || link.download) return;
        const url = new URL(link.href, window.location.href);
        if (url.origin !== window.location.origin) return;
        event.preventDefault();
        controller?.abort();
        controller = new AbortController();
        const activeController = controller;
        trigger = link;
        heading.textContent = url.pathname.endsWith('/create') ? 'Créer un ticket' : 'Détail du ticket';
        content.textContent = 'Chargement…';
        content.setAttribute('aria-busy', 'true');
        previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        panel.showModal();
        closeButton.focus();
        url.searchParams.set('panel', '1');

        try {
            const response = await fetch(url, { signal: activeController.signal, headers: { Accept: 'text/html' }, credentials: 'same-origin' });
            if (!response.ok || !response.headers.get('content-type')?.includes('text/html')) throw new Error('Unavailable fragment');
            const html = await response.text();
            if (!panel.open || activeController.signal.aborted) return;
            content.innerHTML = html;
            const focusTarget = content.querySelector('[aria-invalid="true"], input[name="title"], h1');
            if (focusTarget) {
                if (focusTarget.tagName === 'H1') focusTarget.tabIndex = -1;
                focusTarget.focus();
            }
        } catch (error) {
            if (error.name === 'AbortError') return;
            content.textContent = 'Impossible de charger ce panneau. ';
            const fallback = document.createElement('a');
            fallback.href = link.href;
            fallback.className = 'font-semibold text-brand underline';
            fallback.textContent = 'Ouvrir la page complète';
            content.append(fallback);
            fallback.focus();
        } finally {
            if (controller === activeController) content.removeAttribute('aria-busy');
        }
    });

    closeButton.addEventListener('click', () => panel.close());
    panel.addEventListener('close', () => {
        controller?.abort();
        document.body.style.overflow = previousOverflow;
        if (trigger?.isConnected) trigger.focus();
    });
}
