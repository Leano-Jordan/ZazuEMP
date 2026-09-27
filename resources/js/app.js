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

document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
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


      
document.querySelectorAll('[data-user-menu]').forEach((menu) => {
    const trigger = menu.querySelector('[data-user-trigger]');
    const popover = menu.querySelector('[data-user-popover]');

    if (!trigger || !popover) return;

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

    document.addEventListener('click', close);
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') close(true);
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

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupBrandingUploads);
} else {
    setupBrandingUploads();
}


function setupZazuHelper() {
    document.querySelectorAll('[data-zazu-helper]').forEach((helper) => {
        const route = helper.dataset.zazuGuideRoute || 'workspace';
        const storageKey = 'zazu-helper-enabled';
        const seenKey = 'zazu-helper-seen:' + route;
        const panel = helper.querySelector('[data-zazu-helper-panel]');
        const toggle = helper.querySelector('[data-zazu-helper-toggle]');
        const toggleLabel = helper.querySelector('[data-zazu-helper-toggle-label]');
        const closeButton = helper.querySelector('[data-zazu-helper-close]');
        const offButton = helper.querySelector('[data-zazu-helper-off]');
        const backButton = helper.querySelector('[data-zazu-helper-back]');
        const nextButton = helper.querySelector('[data-zazu-helper-next]');
        const title = helper.querySelector('[data-zazu-helper-title]');
        const copy = helper.querySelector('[data-zazu-helper-copy]');
        const stepLabel = helper.querySelector('[data-zazu-helper-step-label]');
        const count = helper.querySelector('[data-zazu-helper-count]');
        const guideLink = helper.querySelector('[data-zazu-helper-link]');
        const data = [...helper.querySelectorAll('[data-zazu-helper-step-data]')].map((item) => ({
            label: item.dataset.label || '',
            title: item.dataset.title || '',
            copy: item.dataset.copy || '',
            href: item.dataset.href || '',
            link: item.dataset.link || '',
        }));

        if (!panel || !toggle || !toggleLabel || !data.length) return;

        let index = 0;
        let enabled = localStorage.getItem(storageKey) !== 'off';
        let autoOpenTimer = null;

        const render = () => {
            const step = data[index];

            if (title) title.textContent = step.title;
            if (copy) copy.textContent = step.copy;
            if (stepLabel) stepLabel.textContent = step.label;
            if (count) count.textContent = (index + 1) + ' of ' + data.length;

            if (guideLink) {
                guideLink.hidden = !step.href;
                guideLink.href = step.href || '#';
                guideLink.textContent = step.link || '';
            }

            if (backButton) backButton.disabled = index === 0;
            if (nextButton) nextButton.textContent = index === data.length - 1 ? 'Done' : 'Next';
        };

        const syncEnabledState = () => {
            helper.dataset.zazuGuideEnabled = enabled ? 'on' : 'off';
            toggle.setAttribute('aria-pressed', enabled ? 'true' : 'false');
            toggleLabel.textContent = enabled ? 'Guide' : 'Guide off';
        };

        const close = () => {
            panel.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
        };

        const open = () => {
            if (!enabled) enabled = true;
            index = 0;
            localStorage.setItem(storageKey, 'on');
            localStorage.setItem(seenKey, '1');
            syncEnabledState();
            render();
            panel.hidden = false;
            toggle.setAttribute('aria-expanded', 'true');
        };

        const turnOff = () => {
            enabled = false;
            localStorage.setItem(storageKey, 'off');
            if (autoOpenTimer !== null) {
                window.clearTimeout(autoOpenTimer);
                autoOpenTimer = null;
            }
            syncEnabledState();
            close();
        };

        toggle.addEventListener('click', () => {
            if (panel.hidden) {
                open();
            } else {
                close();
            }
        });

        offButton?.addEventListener('click', turnOff);
        closeButton?.addEventListener('click', close);

        backButton?.addEventListener('click', () => {
            if (index === 0) return;
            index -= 1;
            render();
        });

        nextButton?.addEventListener('click', () => {
            if (index >= data.length - 1) {
                close();
                return;
            }

            index += 1;
            render();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !panel.hidden) close();
        });

        syncEnabledState();
        render();

        if (enabled && localStorage.getItem(seenKey) !== '1') {
            autoOpenTimer = window.setTimeout(() => {
                autoOpenTimer = null;

                if (enabled && localStorage.getItem(seenKey) !== '1') {
                    open();
                }
            }, 450);
        }
    });
}

function setupZazuBusinessSwitcher() {
    document.querySelectorAll('[data-business-switch]').forEach((select) => {
        select.addEventListener('change', () => select.form?.requestSubmit());
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupZazuHelper);
        setupZazuBusinessSwitcher();
} else {
    setupZazuHelper();
    setupZazuBusinessSwitcher();
}


if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        setupZazuPrintButtons();
        setupZazuConfirmations();
    });
} else {
    setupZazuPrintButtons();
    setupZazuConfirmations();
}
