function bindPublicDocumentPrintButtons() {
    document.querySelectorAll('[data-zazu-print]').forEach((button) => {
        if (button.dataset.zazuPrintBound === '1') return;

        button.dataset.zazuPrintBound = '1';
        button.addEventListener('click', () => window.print());
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindPublicDocumentPrintButtons, { once: true });
} else {
    bindPublicDocumentPrintButtons();
}
