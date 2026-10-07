const storageKey = 'zazu-theme';

function safeStorageGet(key) {
    try {
        return window.localStorage.getItem(key);
    } catch {
        return null;
    }
}

function safeStorageSet(key, value) {
    try {
        window.localStorage.setItem(key, value);
    } catch {
        // Browser privacy/quota restrictions must not break Zazu UI behaviour.
    }
}

function preferredTheme() {
    const saved = safeStorageGet(storageKey);

    if (saved === 'light' || saved === 'dark') {
        return saved;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

function applyTheme(theme) {
    document.documentElement.dataset.theme = theme;

    const themeColor = document.querySelector('meta[name="theme-color"]');
    if (themeColor) themeColor.setAttribute('content', theme === 'dark' ? '#071B2C' : '#0B3A66');

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

            safeStorageSet(storageKey, next);
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

function setupZazuFormSubmissionGuards() {
    document.querySelectorAll('form').forEach((form) => {
        if (form.dataset.zazuSubmitGuardBound === '1' || form.dataset.zazuNoSubmitGuard === '1') return;

        form.dataset.zazuSubmitGuardBound = '1';

        form.addEventListener('submit', (event) => {
            if (event.defaultPrevented || form.dataset.zazuSubmitting === '1') return;

            const submitter = event.submitter instanceof HTMLElement
                ? event.submitter
                : form.querySelector('button[type="submit"], input[type="submit"]');

            if (!submitter) return;

            form.dataset.zazuSubmitting = '1';
            submitter.dataset.zazuSubmitLabel = submitter.innerText || submitter.value || '';
            submitter.disabled = true;
            submitter.setAttribute('aria-busy', 'true');
        });
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
            const maxBytes = 5 * 1024 * 1024;
            const error = document.querySelector('[data-branding-error="' + type + '"]');

            const clearError = () => {
                input.setCustomValidity('');
                input.removeAttribute('aria-invalid');
                if (error) {
                    error.hidden = true;
                    error.textContent = '';
                }
            };

            const showError = (message) => {
                input.setCustomValidity(message);
                input.setAttribute('aria-invalid', 'true');
                if (error) {
                    error.hidden = false;
                    error.textContent = message;
                }
            };

            clearError();

            if (!allowed) {
                input.value = '';
                preview.hidden = true;
                loading.hidden = true;
                if (placeholder) placeholder.hidden = false;
                container.setAttribute('aria-busy', 'false');
                if (filename) filename.textContent = 'Choose a JPG, PNG or WebP image.';
                showError('Choose a JPG, PNG or WebP image.');
                return;
            }

            if (file.size > maxBytes) {
                input.value = '';
                preview.hidden = true;
                loading.hidden = true;
                if (placeholder) placeholder.hidden = false;
                container.setAttribute('aria-busy', 'false');
                if (filename) filename.textContent = 'Image is larger than 5 MB.';
                showError('This image is larger than 5 MB. Choose a smaller image.');
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


function setupZazuCommandNavigation() {
    const shell = document.querySelector('[data-zazu-command-search]');
    const input = document.querySelector('[data-zazu-command-input]');
    const palette = document.querySelector('[data-zazu-command]');
    const results = document.querySelector('[data-zazu-command-results]');
    const empty = document.querySelector('[data-zazu-command-empty]');
    const count = document.querySelector('[data-zazu-command-count]');
    const jump = document.querySelector('[data-zazu-nav-jump]');

    if (!shell || !input || !palette || !results) return;

    const items = [...results.querySelectorAll('[data-command-item]')];
    let selected = 0;

    const visibleItems = () => items.filter((item) => !item.hidden);

    const select = (index) => {
        const visible = visibleItems();
        if (!visible.length) return;
        selected = (index + visible.length) % visible.length;
        visible.forEach((item, i) => {
            const active = i === selected;
            item.classList.toggle('is-command-active', active);
            item.setAttribute('aria-selected', active ? 'true' : 'false');
            if (active) item.scrollIntoView({ block: 'nearest' });
        });
    };

    const close = (restoreFocus = false) => {
        palette.hidden = true;
        shell.classList.remove('is-open');
        input.setAttribute('aria-expanded', 'false');
        if (restoreFocus) input.focus();
    };

    const open = (focusInput = true) => {
        palette.hidden = false;
        shell.classList.add('is-open');
        input.setAttribute('aria-expanded', 'true');
        if (focusInput) input.focus();
        select(0);
    };

    const filter = () => {
        const query = input.value.trim().toLowerCase();
        const visible = [];

        items.forEach((item) => {
            const haystack = [
                item.textContent || '',
                item.dataset.searchTerms || '',
            ].join(' ').toLowerCase();
            const match = !query || haystack.includes(query);
            item.hidden = !match;
            if (match) visible.push(item);
        });

        if (empty) empty.hidden = visible.length > 0;
        if (count) count.textContent = query
            ? `${visible.length} destination${visible.length === 1 ? '' : 's'}`
            : 'Workspace destinations';

        selected = 0;
        select(0);
    };

    input.addEventListener('focus', open);
    input.addEventListener('input', () => {
        open();
        filter();
    });

    input.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            event.preventDefault();
            close(true);
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            select(selected + 1);
            return;
        }

        if (event.key === 'ArrowUp') {
            event.preventDefault();
            select(selected - 1);
            return;
        }

        if (event.key === 'Enter') {
            const item = visibleItems()[selected];
            if (item) {
                event.preventDefault();
                item.click();
            }
        }
    });

    results.addEventListener('click', (event) => {
        const item = event.target.closest('[data-command-item]');
        if (!item) return;
        safeStorageSet('zazu-last-destination', item.href);
        close(false);
    });

    jump?.addEventListener('click', () => open());

    document.addEventListener('keydown', (event) => {
        const target = event.target;
        const typing = target instanceof HTMLInputElement
            || target instanceof HTMLTextAreaElement
            || target instanceof HTMLSelectElement
            || target?.isContentEditable;

        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k' && !typing) {
            event.preventDefault();
            if (palette.hidden) open();
            else close(true);
        }
    });

    document.addEventListener('click', (event) => {
        if (!shell.contains(event.target)) close(false);
    });

    filter();
}

const setupZazuHierarchicalNavigation = () => {
    const areas = [...document.querySelectorAll('[data-zazu-nav-area]')];
    if (!areas.length) return;

    const helper = document.querySelector('[data-zazu-helper]');
    const helperMoveAreaIds = new Set(['zazu-nav-workspace', 'zazu-nav-sales']);
    const desktop = () => window.matchMedia('(min-width: 851px)').matches;
    let helperNavArea = null;
    let helperMoveFrame = 0;

    const restoreHelperPosition = () => {
        cancelAnimationFrame(helperMoveFrame);
        helperNavArea = null;
        if (!helper) return;
        helper.classList.remove('is-nav-avoiding');
        helper.style.removeProperty('--zazu-helper-nav-right');
    };

    const moveHelperForNav = (area) => {
        if (!helper || !area || !desktop()) {
            restoreHelperPosition();
            return;
        }

        const panel = area.querySelector('[data-zazu-nav-panel]');
        if (!panel || !helperMoveAreaIds.has(panel.id)) {
            restoreHelperPosition();
            return;
        }

        helperNavArea = area;
        cancelAnimationFrame(helperMoveFrame);
        helperMoveFrame = requestAnimationFrame(() => {
            const panelRect = panel.getBoundingClientRect();
            const helperRect = helper.getBoundingClientRect();
            const viewportRight = window.innerWidth;
            const viewportPadding = 14;
            const gap = 16;
            const desiredLeft = Math.min(
                panelRect.right + gap,
                viewportRight - helperRect.width - viewportPadding
            );
            const desiredRight = Math.max(
                viewportPadding,
                viewportRight - desiredLeft - helperRect.width
            );

            helper.style.setProperty('--zazu-helper-nav-right', desiredRight + 'px');
            helper.classList.add('is-nav-avoiding');
        });
    };

    const syncHelperForAreaState = (area) => {
        if (!area || !helperMoveAreaIds.has(area.querySelector('[data-zazu-nav-panel]')?.id)) {
            restoreHelperPosition();
            return;
        }

        if (area.classList.contains('is-open')) moveHelperForNav(area);
        else restoreHelperPosition();
    };

    const closeOthers = (except) => {
        areas.forEach((area) => {
            if (area === except) return;
            area.classList.remove('is-open');
            area.querySelector('[data-zazu-nav-trigger]')?.setAttribute('aria-expanded', 'false');
        });

        if (!except || !helperMoveAreaIds.has(except.querySelector('[data-zazu-nav-panel]')?.id)) {
            restoreHelperPosition();
        }
    };

    const syncActiveAreaForViewport = () => {
        if (!window.matchMedia('(max-width: 850px)').matches) {
            restoreHelperPosition();
            return;
        }

        const activeArea = areas.find((area) => area.classList.contains('is-active'));
        if (!activeArea) return;

        closeOthers(activeArea);
        activeArea.classList.add('is-open');
        activeArea.querySelector('[data-zazu-nav-trigger]')?.setAttribute('aria-expanded', 'true');
    };

    areas.forEach((area) => {
        const trigger = area.querySelector('[data-zazu-nav-trigger]');
        const panel = area.querySelector('[data-zazu-nav-panel]');
        if (!trigger) return;

        const movesHelper = helperMoveAreaIds.has(panel?.id);

        trigger.addEventListener('click', () => {
            const open = area.classList.toggle('is-open');
            closeOthers(area);
            trigger.setAttribute('aria-expanded', open ? 'true' : 'false');

            if (movesHelper) {
                if (open) moveHelperForNav(area);
                else restoreHelperPosition();
            }
        });

        if (movesHelper) {
            area.addEventListener('mouseenter', () => moveHelperForNav(area));
            area.addEventListener('focusin', () => moveHelperForNav(area));
            area.addEventListener('mouseleave', () => {
                if (!area.classList.contains('is-open')) restoreHelperPosition();
            });
            area.addEventListener('focusout', (event) => {
                if (!area.contains(event.relatedTarget)) {
                    if (!area.classList.contains('is-open')) restoreHelperPosition();
                }
            });
        }
    });

    document.addEventListener('click', (event) => {
        if (!areas.some((area) => area.contains(event.target))) {
            closeOthers(null);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        closeOthers(null);
        document.querySelector('[data-zazu-nav-trigger][aria-expanded="true"]')?.focus();
    });

    window.addEventListener('resize', () => {
        if (helperNavArea?.classList.contains('is-open')) {
            moveHelperForNav(helperNavArea);
        } else if (!desktop()) {
            restoreHelperPosition();
        }
    }, { passive: true });

    syncActiveAreaForViewport();
    window.matchMedia('(max-width: 850px)').addEventListener?.('change', syncActiveAreaForViewport);
};

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


        // The guide stays quiet until the user asks for it. Attention is surfaced by the helper control itself.
    });
}


const initializeZazuUi = () => {
    setupZazuAuthExperience();
    setupZazuMobileNavigation();
    setupZazuHierarchicalNavigation();
    setupZazuCommandNavigation();
    setupZazuThemeToggle();
    setupZazuCatalogue();
    setupZazuUserMenus();
    setupZazuToasts();
    setupBrandingUploads();
    setupZazuHelper();
    setupZazuPrintButtons();
    setupZazuConfirmations();
    setupZazuFormSubmissionGuards();
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

    const media = window.matchMedia('(max-width: 850px)');
    const focusable = () => [...sidebar.querySelectorAll('a,button,input,select,textarea,[tabindex]:not([tabindex="-1"])')]
        .filter((element) => !element.hidden && !element.disabled && element.offsetParent !== null);

    let returnFocus = null;

    const syncActiveNavigationGroup = () => {
        if (!media.matches) return;

        const areas = [...sidebar.querySelectorAll('[data-zazu-nav-area]')];
        const activeArea = areas.find((area) => area.classList.contains('is-active'));
        if (!activeArea) return;

        areas.forEach((area) => {
            const isActive = area === activeArea;
            area.classList.toggle('is-open', isActive);
            area.querySelector('[data-zazu-nav-trigger]')?.setAttribute('aria-expanded', isActive ? 'true' : 'false');
        });
    };

    const sync = (open, moveFocus = false) => {
        const active = open && media.matches;
        document.body.classList.toggle('zazu-mobile-menu-open', active);
        toggle.setAttribute('aria-expanded', active ? 'true' : 'false');
        toggle.setAttribute('aria-label', active ? 'Close navigation' : 'Open navigation');
        sidebar.toggleAttribute('inert', !active && media.matches);

        if (active) syncActiveNavigationGroup();

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

function registerZazuServiceWorker() {
    if (!('serviceWorker' in navigator)) return;

    const primeInstalledPages = (registration) => {
        const routes = [...new Set(
            [...document.querySelectorAll('.zazu-sidebar a[href], .zazu-section-tabs a[href]')]
                .map((link) => {
                    try {
                        const url = new URL(link.href, window.location.href);
                        if (url.origin !== window.location.origin) return null;
                        if (url.search || url.hash) return null;
                        if (url.pathname === '/logout' || url.pathname.startsWith('/api/')) return null;
                        return url.pathname;
                    } catch {
                        return null;
                    }
                })
                .filter(Boolean)
        )];

        const target = navigator.serviceWorker.controller || registration.active;
        target?.postMessage({ type: 'prime-pages', routes });
    };

    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js', { scope: '/' })
            .then((registration) => {
                primeInstalledPages(registration);
                navigator.serviceWorker.ready.then((ready) => primeInstalledPages(ready));
            })
            .catch(() => {
                // Offline asset support is an enhancement; application behaviour must not depend on it.
            });
    });
}

registerZazuServiceWorker();
