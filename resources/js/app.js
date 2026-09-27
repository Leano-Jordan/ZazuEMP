const storageKey = 'zazu-theme';

function preferredTheme() {
    const saved = localStorage.getItem(storageKey);

    if (saved === 'light' || saved === 'dark') {
        return saved;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

function applyTheme(theme) {
    document.documentElement.dataset.theme = theme;

    const themeColor = document.querySelector('meta[name="theme-color"]');
    if (themeColor) themeColor.setAttribute('content', theme === 'dark' ? '#0D1F3A' : '#F2F7FF');

document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        const isDark = theme === 'dark';
        const sun = button.querySelector('[data-theme-icon-sun]');
        const moon = button.querySelector('[data-theme-icon-moon]');

        if (sun) sun.hidden = isDark;
        if (moon) moon.hidden = !isDark;

        button.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        button.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
    });
}

applyTheme(preferredTheme());

function setupZazuThemeToggle() {
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        if (button.dataset.zazuThemeBound === '1') return;

        button.dataset.zazuThemeBound = '1';
        button.addEventListener('click', () => {
            const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';

            localStorage.setItem(storageKey, next);
            document.documentElement.classList.add('zazu-theme-transition');
            applyTheme(next);

            window.clearTimeout(window.__zazuThemeTransitionTimer);
            window.__zazuThemeTransitionTimer = window.setTimeout(() => {
                document.documentElement.classList.remove('zazu-theme-transition');
            }, 200);
        });
    });
}

function setupZazuUserMenus() {
    document.querySelectorAll('[data-user-menu]').forEach((menu) => {
        const trigger = menu.querySelector('[data-user-trigger]');
        const popover = menu.querySelector('[data-user-popover]');

        if (!trigger || !popover || menu.dataset.zazuUserBound === '1') return;

        menu.dataset.zazuUserBound = '1';

        const close = (restoreFocus = false) => {
            popover.hidden = true;
            trigger.setAttribute('aria-expanded', 'false');
            if (restoreFocus) trigger.focus();
        };

        trigger.addEventListener('click', (event) => {
            event.stopPropagation();
            const open = !popover.hidden;
            document.querySelectorAll('[data-user-popover]').forEach((item) => { item.hidden = true; });
            document.querySelectorAll('[data-user-trigger]').forEach((item) => { item.setAttribute('aria-expanded', 'false'); });

            popover.hidden = open;
            trigger.setAttribute('aria-expanded', open ? 'false' : 'true');
        });

        popover.addEventListener('click', (event) => event.stopPropagation());
        document.addEventListener('click', () => close());
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') close(true);
        });
    });
}

function setupZazuToasts() {
    document.querySelectorAll('[data-zazu-toast]').forEach((toast) => {
        if (toast.dataset.zazuToastBound === '1') return;

        toast.dataset.zazuToastBound = '1';
        const close = () => {
            const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            if (reducedMotion) {
                toast.remove();
                return;
            }

            toast.style.opacity = '0';
            toast.style.transform = 'translateY(8px)';
            toast.style.transition = 'opacity 140ms ease, transform 140ms ease';
            window.setTimeout(() => toast.remove(), 150);
        };

        toast.querySelector('[data-zazu-toast-close]')?.addEventListener('click', close);

        const duration = toast.classList.contains('zazu-toast-error') ? 9000 : 6000;
        window.setTimeout(() => {
            if (document.body.contains(toast)) close();
        }, duration);
    });
}

function setupZazuPrintButtons() {
document.querySelectorAll('[data-zazu-print]').forEach((button) => {
    button.addEventListener('click', () => window.print());
});
}

function setupZazuConfirmations() {
const forms = document.querySelectorAll('form[data-zazu-confirm]');
if (!forms.length) return;

let activeForm = null;
let returnFocus = null;

const dialog = document.createElement('dialog');
dialog.className = 'zazu-confirm-dialog';
dialog.setAttribute('aria-labelledby', 'zazu-confirm-title');
dialog.setAttribute('aria-describedby', 'zazu-confirm-message');
dialog.innerHTML = `
    <form method="dialog" class="zazu-confirm-panel">
        <div class="zazu-eyebrow">Confirmation</div>
        <h2 id="zazu-confirm-title" class="zazu-confirm-title"></h2>
        <p id="zazu-confirm-message" class="zazu-confirm-message"></p>
        <div class="zazu-confirm-actions">
            <button type="button" class="zazu-btn zazu-btn-secondary" data-zazu-confirm-cancel>Cancel</button>
            <button type="button" class="zazu-btn zazu-btn-danger" data-zazu-confirm-submit>Continue</button>
        </div>
    </form>
`;
document.body.appendChild(dialog);

const title = dialog.querySelector('#zazu-confirm-title');
const message = dialog.querySelector('#zazu-confirm-message');
const cancel = dialog.querySelector('[data-zazu-confirm-cancel]');
const submit = dialog.querySelector('[data-zazu-confirm-submit]');

const restoreFocus = () => {
    if (returnFocus && typeof returnFocus.focus === 'function') returnFocus.focus();
    returnFocus = null;
    activeForm = null;
};

forms.forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (form.dataset.zazuConfirmed === '1') {
            delete form.dataset.zazuConfirmed;
            return;
        }

        event.preventDefault();
        activeForm = form;
        returnFocus = document.activeElement instanceof HTMLElement ? document.activeElement : null;
        title.textContent = form.dataset.zazuConfirmTitle || 'Confirm action';
        message.textContent = form.dataset.zazuConfirm || 'Are you sure you want to continue?';
        submit.textContent = form.dataset.zazuConfirmAction || 'Continue';
        if (typeof dialog.showModal === 'function') {
            dialog.showModal();
        } else {
            const confirmed = window.confirm(message.textContent);
            if (confirmed) {
                form.dataset.zazuConfirmed = '1';
                form.requestSubmit();
            } else {
                restoreFocus();
            }
        }
    });
});

cancel?.addEventListener('click', () => dialog.close('cancel'));
submit?.addEventListener('click', () => {
    if (!activeForm) return;
    activeForm.dataset.zazuConfirmed = '1';
    const form = activeForm;
    dialog.close('confirm');
    form.requestSubmit();
});

dialog.addEventListener('close', () => {
    if (dialog.returnValue !== 'confirm') restoreFocus();
});
}





const brandingPreviewUrls = new WeakMap();

function setupBrandingUploads() {
    document.querySelectorAll('[data-branding-upload]').forEach((input) => {
        input.addEventListener('change', () => {
            const type = input.dataset.brandingUpload;
            const file = input.files?.[0];

            if (!type || !file) return;

            const container = document.querySelector(`[data-branding-preview-container="${type}"]`);
            const preview = document.querySelector(`[data-branding-preview="${type}"]`);
            const placeholder = document.querySelector(`[data-branding-placeholder="${type}"]`);
            const loading = document.querySelector(`[data-branding-loading="${type}"]`);
            const filename = document.querySelector(`[data-branding-file="${type}"]`);

            if (!container || !preview || !loading) return;

            const extension = file.name.split('.').pop()?.toLowerCase() ?? '';
            const allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            const allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
            const allowed = allowedExtensions.includes(extension)
                && (!file.type || allowedMimeTypes.includes(file.type));

            if (!allowed) {
                input.value = '';
                preview.hidden = true;
                loading.hidden = true;
                if (placeholder) placeholder.hidden = false;
                container.setAttribute('aria-busy', 'false');
                if (filename) filename.textContent = 'Choose a JPG, PNG or WebP image.';
                return;
            }

            if (filename) filename.textContent = file.name;

            container.setAttribute('aria-busy', 'true');
            loading.hidden = false;
            preview.hidden = true;
            if (placeholder) placeholder.hidden = true;

            const previousUrl = brandingPreviewUrls.get(input);
            if (previousUrl) URL.revokeObjectURL(previousUrl);

            const objectUrl = URL.createObjectURL(file);
            brandingPreviewUrls.set(input, objectUrl);

            const image = new Image();

            image.onload = () => {
                preview.src = objectUrl;
                preview.hidden = false;
                loading.hidden = true;
                container.setAttribute('aria-busy', 'false');
            };

            image.onerror = () => {
                loading.hidden = true;
                if (placeholder) placeholder.hidden = false;
                container.setAttribute('aria-busy', 'false');
                if (filename) filename.textContent = 'That image could not be previewed.';
                URL.revokeObjectURL(objectUrl);
                brandingPreviewUrls.delete(input);
            };

            image.src = objectUrl;
        });
    });

    document.querySelectorAll('[data-branding-form]').forEach((form) => {
        form.addEventListener('submit', () => {
            const button = form.querySelector('[data-branding-save]');
            const label = form.querySelector('[data-branding-save-label]');
            const spinner = form.querySelector('[data-branding-save-spinner]');

            if (!button) return;

            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            if (label) label.textContent = 'Saving…';
            if (spinner) spinner.hidden = false;
        });
    });
}

const initializeZazuUi = () => {
    setupZazuThemeToggle();
    setupZazuUserMenus();
    setupZazuToasts();
    setupBrandingUploads();
    setupZazuHelper();
    setupZazuBusinessSwitcher();
    setupZazuPrintButtons();
    setupZazuConfirmations();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeZazuUi, { once: true });
} else {
    initializeZazuUi();
}
