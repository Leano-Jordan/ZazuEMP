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
    if (themeColor) themeColor.setAttribute('content', theme === 'dark' ? '#081A2A' : '#E3EBF3');

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
const panel = document.createElement('form');
panel.method = 'dialog';
panel.className = 'zazu-confirm-panel';

const eyebrow = document.createElement('div');
eyebrow.className = 'zazu-eyebrow';
eyebrow.textContent = 'Confirmation';

const titleElement = document.createElement('h2');
titleElement.id = 'zazu-confirm-title';
titleElement.className = 'zazu-confirm-title';

const messageElement = document.createElement('p');
messageElement.id = 'zazu-confirm-message';
messageElement.className = 'zazu-confirm-message';

const actions = document.createElement('div');
actions.className = 'zazu-confirm-actions';

const cancelButton = document.createElement('button');
cancelButton.type = 'button';
cancelButton.className = 'zazu-btn zazu-btn-secondary';
cancelButton.dataset.zazuConfirmCancel = '';
cancelButton.textContent = 'Cancel';

const submitButton = document.createElement('button');
submitButton.type = 'button';
submitButton.className = 'zazu-btn zazu-btn-danger';
submitButton.dataset.zazuConfirmSubmit = '';
submitButton.textContent = 'Continue';

actions.append(cancelButton, submitButton);
panel.append(eyebrow, titleElement, messageElement, actions);
dialog.appendChild(panel);
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

            if (typeof createImageBitmap !== 'function') {
                loading.hidden = true;
                if (placeholder) placeholder.hidden = false;
                container.setAttribute('aria-busy', 'false');
                if (filename) filename.textContent = 'Image preview is not supported in this browser.';
                return;
            }

            createImageBitmap(file)
                .then((bitmap) => {
                    const canvas = document.createElement('canvas');
                    canvas.width = bitmap.width;
                    canvas.height = bitmap.height;

                    const context = canvas.getContext('2d');
                    if (!context) {
                        bitmap.close();
                        throw new Error('Canvas preview context unavailable');
                    }

                    context.drawImage(bitmap, 0, 0);
                    bitmap.close();

                    canvas.toBlob((blob) => {
                        if (!blob) {
                            loading.hidden = true;
                            if (placeholder) placeholder.hidden = false;
                            container.setAttribute('aria-busy', 'false');
                            if (filename) filename.textContent = 'That image could not be previewed.';
                            return;
                        }

                        const objectUrl = URL.createObjectURL(blob);
                        brandingPreviewUrls.set(input, objectUrl);
                        preview.src = objectUrl;
                        preview.hidden = false;
                        loading.hidden = true;
                        container.setAttribute('aria-busy', 'false');
                    }, 'image/png');
                })
                .catch(() => {
                    loading.hidden = true;
                    if (placeholder) placeholder.hidden = false;
                    container.setAttribute('aria-busy', 'false');
                    if (filename) filename.textContent = 'That image could not be previewed.';
                });
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

function setupZazuCatalogue() {
    document.querySelectorAll('[data-zazu-catalogue]').forEach((catalogue) => {
        if (catalogue.dataset.zazuCatalogueBound === '1') return;
        catalogue.dataset.zazuCatalogueBound = '1';

        const tabs = catalogue.querySelectorAll('[data-catalogue-tab]');
        const panels = catalogue.querySelectorAll('[data-catalogue-panel]');
        const drawer = catalogue.querySelector('[data-catalogue-drawer]');
        const drawerOpeners = catalogue.querySelectorAll('[data-catalogue-drawer-open]');
        const drawerClosers = catalogue.querySelectorAll('[data-catalogue-drawer-close]');

        const selectTab = (name, moveFocus = false) => {
            tabs.forEach((tab) => {
                const active = tab.dataset.catalogueTab === name;
                tab.classList.toggle('is-active', active);
                tab.setAttribute('aria-selected', active ? 'true' : 'false');
                tab.tabIndex = active ? 0 : -1;
            });
            panels.forEach((panel) => {
                const active = panel.dataset.cataloguePanel === name;
                panel.hidden = !active;
                panel.classList.toggle('is-active', active);
            });
            if (moveFocus) catalogue.querySelector('[data-catalogue-tab="' + name + '"]')?.focus();
        };

        tabs.forEach((tab, index) => {
            tab.addEventListener('click', () => selectTab(tab.dataset.catalogueTab));
            tab.addEventListener('keydown', (event) => {
                if (!['ArrowRight', 'ArrowLeft', 'Home', 'End'].includes(event.key)) return;
                event.preventDefault();
                const nextIndex = event.key === 'Home' ? 0 : event.key === 'End' ? tabs.length - 1 :
                    (index + (event.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length;
                selectTab(tabs[nextIndex].dataset.catalogueTab, true);
            });
        });
        selectTab(tabs[0]?.dataset.catalogueTab || 'equipment');

        let drawerReturnFocus = null;
        const closeDrawer = () => {
            if (!drawer) return;
            drawer.hidden = true;
            drawer.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('zazu-drawer-open');
            drawerReturnFocus?.focus();
            drawerReturnFocus = null;
        };

        const openDrawer = (event) => {
            if (!drawer) return;
            drawerReturnFocus = event?.currentTarget || document.activeElement;
            drawer.hidden = false;
            drawer.setAttribute('aria-hidden', 'false');
            document.body.classList.add('zazu-drawer-open');
            drawer.querySelector('a,button,input,select,textarea')?.focus();
        };

        drawerOpeners.forEach((button) => button.addEventListener('click', openDrawer));
        drawerClosers.forEach((button) => button.addEventListener('click', closeDrawer));
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && drawer && !drawer.hidden) closeDrawer();
        });
    });
}

const initializeZazuUi = () => {
    setupZazuAuthExperience();
    setupZazuMobileNavigation();
    setupZazuThemeToggle();
    setupZazuCatalogue();
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


function setupZazuMobileNavigation() {
    const sidebar = document.querySelector('[data-mobile-sidebar]');
    const toggle = document.querySelector('[data-mobile-sidebar-toggle]');
    const closers = document.querySelectorAll('[data-mobile-sidebar-close]');
    if (!sidebar || !toggle) return;

    const media = window.matchMedia('(max-width: 820px)');
    const focusable = () => [...sidebar.querySelectorAll('a,button,input,select,textarea,[tabindex]:not([tabindex="-1"])')]
        .filter((element) => !element.hidden && !element.disabled && element.offsetParent !== null);

    let returnFocus = null;

    const sync = (open, moveFocus = false) => {
        const active = open && media.matches;
        document.body.classList.toggle('zazu-mobile-menu-open', active);
        toggle.setAttribute('aria-expanded', active ? 'true' : 'false');
        toggle.setAttribute('aria-label', active ? 'Close navigation' : 'Open navigation');
        sidebar.toggleAttribute('inert', !active && media.matches);

        if (moveFocus && active) {
            (sidebar.querySelector('[data-mobile-sidebar-close]') || focusable()[0])?.focus();
        }
    };

    const close = (restoreFocus = false) => {
        sync(false);
        if (restoreFocus) {
            (returnFocus || toggle)?.focus();
        }
        returnFocus = null;
    };

    toggle.addEventListener('click', () => {
        const opening = !document.body.classList.contains('zazu-mobile-menu-open');
        if (opening) returnFocus = document.activeElement instanceof HTMLElement ? document.activeElement : toggle;
        sync(opening, opening);
    });

    closers.forEach((button) => button.addEventListener('click', () => close(true)));
    sidebar.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => close(false)));

    document.addEventListener('keydown', (event) => {
        if (!document.body.classList.contains('zazu-mobile-menu-open')) return;

        if (event.key === 'Escape') {
            close(true);
            return;
        }

        if (event.key !== 'Tab') return;

        const items = focusable();
        if (!items.length) return;

        const first = items[0];
        const last = items[items.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
            return;
        }

        if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    media.addEventListener?.('change', () => {
        if (!media.matches) close(false);
        else sync(false);
    });

    sync(false);
}

function setupZazuAuthExperience() {
    document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
        if (toggle.dataset.zazuPasswordBound === '1') return;
        const id = toggle.dataset.passwordToggle || toggle.getAttribute('aria-controls');
        const password = id ? document.getElementById(id) : null;
        if (!password) return;
        toggle.dataset.zazuPasswordBound = '1';
        toggle.addEventListener('click', () => {
            const showing = password.type === 'text';
            password.type = showing ? 'password' : 'text';
            toggle.textContent = showing ? 'Show' : 'Hide';
            toggle.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
        });
    });

    document.querySelectorAll('[data-auth-switch]').forEach((link) => {
        if (link.dataset.zazuAuthBound === '1') return;
        link.dataset.zazuAuthBound = '1';
        link.addEventListener('click', async (event) => {
            if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
            event.preventDefault();
            const frame = document.querySelector('[data-auth-frame]');
            if (!frame) { window.location.href = link.href; return; }

            try {
                const response = await fetch(link.href, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
                });
                if (!response.ok) throw new Error('Auth navigation failed');
                const html = await response.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const next = doc.querySelector('[data-auth-frame]');
                if (!next) throw new Error('Auth surface missing');

                frame.classList.add('zazu-auth-switching');
                window.setTimeout(() => {
                    frame.replaceWith(next);
                    history.pushState({}, '', link.href);
                    document.title = doc.title;
                    next.classList.add('zazu-auth-switched');
                    setupZazuAuthExperience();
                    window.setTimeout(() => next.classList.remove('zazu-auth-switched'), 240);
                }, 120);
            } catch {
                window.location.href = link.href;
            }
        });
    });
}

async function restoreZazuAuthRoute() {
    if (!document.querySelector('[data-auth-frame]')) return;
    const frame = document.querySelector('[data-auth-frame]');
    try {
        const response = await fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } });
        if (!response.ok) throw new Error('Auth history navigation failed');
        const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
        const next = doc.querySelector('[data-auth-frame]');
        if (!next) throw new Error('Auth surface missing');
        frame.replaceWith(next);
        document.title = doc.title;
        setupZazuAuthExperience();
    } catch {
        window.location.reload();
    }
}

window.addEventListener('popstate', restoreZazuAuthRoute);
