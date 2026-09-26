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
    if (themeColor) themeColor.setAttribute('content', theme === 'dark' ? '#071812' : '#0d4f43');

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

document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';

        localStorage.setItem(storageKey, next);
        applyTheme(next);
    });
});


document.querySelectorAll('[data-user-menu]').forEach((menu) => {
    const trigger = menu.querySelector('[data-user-trigger]');
    const popover = menu.querySelector('[data-user-popover]');

    if (!trigger || !popover) return;

    const close = () => {
        popover.hidden = true;
        trigger.setAttribute('aria-expanded', 'false');
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

    document.addEventListener('click', close);
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') close();
    });
});


document.querySelectorAll('[data-zazu-toast]').forEach((toast) => {
    const close = () => {
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
            const allowed = file.type.startsWith('image/')
                || ['jpg', 'jpeg', 'png', 'webp'].includes(extension);

            if (!allowed) {
                input.value = '';
                if (filename) filename.textContent = 'Choose a JPG, PNG or WebP image.';
                return;
            }

            if (filename) filename.textContent = file.name;

            container.setAttribute('aria-busy', 'true');
            loading.hidden = false;
            preview.hidden = true;
            if (placeholder) placeholder.hidden = true;

            const objectUrl = URL.createObjectURL(file);
            const image = new Image();

            image.onload = () => {
                preview.src = objectUrl;
                preview.hidden = false;
                loading.hidden = true;
                container.setAttribute('aria-busy', 'false');
                window.setTimeout(() => URL.revokeObjectURL(objectUrl), 250);
            };

            image.onerror = () => {
                loading.hidden = true;
                if (placeholder) placeholder.hidden = false;
                container.setAttribute('aria-busy', 'false');
                if (filename) filename.textContent = 'That image could not be previewed.';
                URL.revokeObjectURL(objectUrl);
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

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupBrandingUploads);
} else {
    setupBrandingUploads();
}
