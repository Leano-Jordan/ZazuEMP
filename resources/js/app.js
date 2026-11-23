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

    return 'light';
}

function applyTheme(theme) {
    document.documentElement.dataset.theme = theme;

    const themeColor = document.querySelector('meta[name="theme-color"]');
    if (themeColor) themeColor.setAttribute('content', theme === 'dark' ? '#071B2C' : '#DDF4FA');

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

function setupZazuBusinessSwitcher() {
    document.querySelectorAll('[data-business-switch]').forEach((select) => {
        if (select.dataset.zazuBusinessSwitchBound === '1') return;

        const form = select.closest('form');
        if (!(form instanceof HTMLFormElement)) return;

        select.dataset.zazuBusinessSwitchBound = '1';
        select.addEventListener('change', () => {
            const businessId = Number(select.value);
            if (!Number.isSafeInteger(businessId) || businessId < 1) return;
            if (form.dataset.zazuBusinessSwitchSubmitting === '1') return;

            form.dataset.zazuBusinessSwitchSubmitting = '1';
            select.setAttribute('aria-busy', 'true');
            // Keep the select enabled so its business_id remains in the POST body.
            form.requestSubmit();
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

            toast.classList.add('is-closing');
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
        // Offline forms own their async IndexedDB save lifecycle. The generic
        // guard disables submitters synchronously before that handler can decide
        // whether to queue, reject an attachment, or recover from a storage error.
        if (form.hasAttribute('data-zazu-offline-entity') || form.dataset.zazuSubmitGuardBound === '1' || form.dataset.zazuNoSubmitGuard === '1') return;

        form.dataset.zazuSubmitGuardBound = '1';

        form.addEventListener('submit', (event) => {
            if (event.defaultPrevented || form.dataset.zazuSubmitting === '1') return;

            const submitter = event.submitter instanceof HTMLElement
                ? event.submitter
                : form.querySelector('button[type="submit"], button:not([type]), input[type="submit"]');

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

    const closeOthers = (except) => {
        areas.forEach((area) => {
            if (area === except) return;
            area.classList.remove('is-open');
            area.querySelector('[data-zazu-nav-trigger]')?.setAttribute('aria-expanded', 'false');
        });
    };

    areas.forEach((area) => {
        const trigger = area.querySelector('[data-zazu-nav-trigger]');
        if (!trigger) return;

        trigger.addEventListener('click', () => {
            const open = area.classList.toggle('is-open');
            closeOthers(area);
            trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    });

    document.addEventListener('click', (event) => {
        const target = event.target;
        if (target instanceof Element
            && (target.closest('[data-mobile-sidebar]') || target.closest('[data-mobile-sidebar-toggle]'))) {
            return;
        }

        if (!areas.some((area) => area.contains(target))) {
            closeOthers(null);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        closeOthers(null);
        document.querySelector('[data-zazu-nav-trigger][aria-expanded="true"]')?.focus();
    });

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
        const recommendation = helper.querySelector('[data-zazu-helper-recommendation]');
        const recommendationTitle = helper.querySelector('[data-zazu-helper-recommendation-title]');
        const recommendationCopy = helper.querySelector('[data-zazu-helper-recommendation-copy]');
        const recommendationLink = helper.querySelector('[data-zazu-helper-recommendation-link]');
        const connectivity = helper.querySelector('[data-zazu-helper-connectivity]');
        const data = [...helper.querySelectorAll('[data-zazu-helper-step-data]')].map((item) => ({
            label: item.dataset.label || '',
            title: item.dataset.title || '',
            copy: item.dataset.copy || '',
            href: item.dataset.href || '',
            link: item.dataset.link || '',
            target: item.dataset.target || '',
        }));

        if (!panel || !toggle || !toggleLabel || !data.length) return;

        let index = 0;
        let enabled = safeStorageGet(storageKey) !== 'off';
        let targetTimer = null;
        let originAvailable = null;

        const clearTargetHighlight = () => {
            window.clearTimeout(targetTimer);
            document.querySelectorAll('.zazu-helper-target-highlight').forEach((element) => {
                element.classList.remove('zazu-helper-target-highlight');
            });
        };

        const resolveVisibleTarget = (key) => [...document.querySelectorAll('[data-zazu-anchor]')]
            .find((element) => {
                if (element.dataset.zazuAnchor !== key || !element.isConnected) return false;
                if (element.closest('[hidden], [aria-hidden="true"]')) return false;
                const style = window.getComputedStyle(element);

                return element.getClientRects().length > 0
                    && style.display !== 'none'
                    && style.visibility !== 'hidden';
            });

        const render = (guideTarget = false) => {
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

            if (guideTarget) {
                clearTargetHighlight();

                if (step.target) {
                    const target = resolveVisibleTarget(step.target);

                    if (target) {
                        target.classList.add('zazu-helper-target-highlight');

                        try {
                            target.scrollIntoView({
                                behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
                                block: 'center',
                            });
                        } catch {
                            target.scrollIntoView();
                        }

                        targetTimer = window.setTimeout(() => {
                            if (target.isConnected) target.classList.remove('zazu-helper-target-highlight');
                        }, 2400);
                    }
                }
            }

            if (backButton) backButton.disabled = index === 0;
            if (nextButton) nextButton.textContent = index === data.length - 1 ? 'Done' : 'Next';
        };

        const syncRecommendation = () => {
            if (!recommendation || !recommendationTitle || !recommendationCopy || !recommendationLink) return;

            // Prefer the job's state-derived next action. On other workspaces,
            // promote the first permission-filtered attention item from the server.
            const source = document.querySelector('[data-zazu-anchor="work.next-action"]');
            const attentionSource = document.querySelector('[data-zazu-helper-attention-item]');
            const titleText = source?.querySelector('.zazu-next-action-title')?.textContent?.trim()
                || attentionSource?.querySelector('[data-zazu-helper-attention-title]')?.textContent?.trim()
                || '';
            const copyText = source?.querySelector('.zazu-next-action-copy')?.textContent?.trim()
                || attentionSource?.querySelector('[data-zazu-helper-attention-copy]')?.textContent?.trim()
                || '';
            const sourceLink = source?.querySelector('a[href]')
                || attentionSource?.querySelector('[data-zazu-helper-attention-link][href]');
            let safeHref = '';

            if (sourceLink) {
                try {
                    const url = new URL(sourceLink.getAttribute('href'), window.location.href);
                    if (url.origin === window.location.origin) safeHref = url.href;
                } catch {
                    safeHref = '';
                }
            }

            const available = originAvailable === true;
            recommendation.hidden = !available || !titleText || !copyText || !safeHref;
            if (!recommendation.hidden) {
                recommendationTitle.textContent = titleText;
                recommendationCopy.textContent = copyText;
                recommendationLink.href = safeHref;
                recommendationLink.textContent = sourceLink.textContent.trim() || 'Open next action';
            }
        };

        const syncConnectivity = (available) => {
            originAvailable = available === true;

            if (connectivity) {
                connectivity.hidden = originAvailable;
                connectivity.textContent = originAvailable
                    ? ''
                    : 'Connection to Zazu is unavailable. Job recommendations are paused until the server responds. Use only the supported offline workspace meanwhile.';
            }

            syncRecommendation();
        };

        window.addEventListener('zazu:origin-availability', (event) => {
            syncConnectivity(event.detail?.available === true);
        });

        const syncEnabledState = () => {
            helper.dataset.zazuGuideEnabled = enabled ? 'on' : 'off';
            toggleLabel.textContent = enabled ? 'Guide' : 'Guide off';
        };

        const close = (restoreFocus = true) => {
            clearTargetHighlight();
            panel.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
            toggle.setAttribute('aria-label', 'Open Zazu guide');

            if (restoreFocus) toggle.focus();
        };

        const open = () => {
            if (!enabled) enabled = true;
            index = 0;
            safeStorageSet(storageKey, 'on');
            safeStorageSet(seenKey, '1');
            syncEnabledState();
            render(true);
            syncRecommendation();
            panel.hidden = false;
            toggle.setAttribute('aria-expanded', 'true');
            toggle.setAttribute('aria-label', 'Close Zazu guide');
            closeButton?.focus();
        };

        const turnOff = () => {
            enabled = false;
            safeStorageSet(storageKey, 'off');
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
            render(true);
        });

        nextButton?.addEventListener('click', () => {
            if (index >= data.length - 1) {
                close();
                return;
            }

            index += 1;
            render(true);
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
    setupZazuBusinessSwitcher();
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

async function canReachZazuOrigin() {
    const controller = new AbortController();
    const timeout = window.setTimeout(() => controller.abort(), 3000);
    const healthUrl = new URL('/up', window.location.origin);

    try {
        // Probe Laravel's lightweight health route instead of re-rendering the
        // current page. A slow Blade/database-backed page must not be mistaken
        // for an unreachable host and cause an online save to queue locally.
        const response = await fetch(healthUrl.href, {
            method: 'HEAD',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { 'X-Zazu-Connectivity-Probe': '1' },
            signal: controller.signal,
        });

        return response.status < 500;
    } catch {
        // navigator.onLine only describes the browser's network interface. The
        // Zazu origin itself can still be unavailable while Wi-Fi remains up.
        return false;
    } finally {
        window.clearTimeout(timeout);
    }
}

function setupZazuOfflineDirectoryViews() {
    if (!window.ZazuOffline) return;

    const text = (tag, className, value) => {
        const node = document.createElement(tag);
        if (className) node.className = className;
        node.textContent = value == null || value === '' ? '—' : String(value);
        return node;
    };

    const localSyncStatus = (state, entityType, localId, serverId, pendingSync = false) => {
        const conflict = (state?.conflicts || []).find(item =>
            item.status === 'open'
            && item.entity_type === entityType
            && String(item.entity_id || '') === String(localId || '')
        );
        if (conflict) return {
            label: 'Needs attention',
            title: conflict.message || 'This saved change needs review before it can sync.',
        };
        if (state?.sync_authorization_required) return {
            label: 'Re-pair required',
            title: state.last_sync_error || 'This device must be paired again before queued changes can sync.',
        };
        if (state?.sync_registration_blocked) return {
            label: 'Needs attention',
            title: state.last_sync_error || 'The owner must resolve this device registration before queued changes can sync.',
        };
        if (pendingSync && state?.last_sync_error) return {
            label: 'Retrying sync',
            title: state.last_sync_error,
        };
        if (pendingSync) return {
            label: 'Waiting to sync',
            title: 'This device has saved changes for this record. They will sync when Zazu reconnects.',
        };
        if (serverId) return { label: 'Synced', title: 'This record has a server identity.' };
        if (state?.last_sync_error) return {
            label: 'Retrying sync',
            title: state.last_sync_error,
        };
        if (!navigator.onLine) return {
            label: 'Waiting for connection',
            title: 'Saved on this device. It will sync when Zazu reconnects.',
        };
        return {
            label: 'Waiting to sync',
            title: 'Saved on this device and queued for synchronisation.',
        };
    };

    const addCustomerOfflineDetails = (row, customer) => {
        if (row.dataset.zazuCustomerDetailsBound === '1') return;
        const actions = row.querySelector('.zazu-action-group, .zazu-list-side') || row;
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'zazu-btn zazu-btn-ghost';
        button.textContent = 'View offline';
        button.title = 'Read this customer and linked work from this device';
        button.setAttribute('aria-expanded', 'false');
        button.addEventListener('click', () => {
            const currentState = window.ZazuOffline?.getState();
            if (!currentState) return;
            const customerServerId = Number(customer.server_id ?? customer.id ?? 0);
            const currentCustomer = (currentState.customers || []).find(item =>
                (customer.local_id && item.local_id === customer.local_id)
                || (customerServerId > 0 && Number(item.server_id ?? item.id ?? 0) === customerServerId)
            ) || customer;
            let panel = row.nextElementSibling;
            if (!panel?.matches('[data-zazu-customer-offline-detail]')) {
                panel = document.createElement('section');
                panel.className = 'zazu-panel mt-3';
                panel.dataset.zazuCustomerOfflineDetail = '1';
                panel.setAttribute('role', 'region');
                panel.setAttribute('aria-label', 'Offline customer details');
                row.insertAdjacentElement('afterend', panel);
            }
            const opening = panel.hidden || panel.childElementCount === 0;
            panel.hidden = !opening;
            button.setAttribute('aria-expanded', opening ? 'true' : 'false');
            if (!opening) return;

            panel.replaceChildren();
            const heading = document.createElement('div');
            heading.className = 'zazu-panel-head';
            const title = document.createElement('div');
            title.className = 'zazu-panel-title';
            title.textContent = currentCustomer.name || 'Customer details';
            const close = document.createElement('button');
            close.type = 'button';
            close.className = 'zazu-btn zazu-btn-ghost';
            close.textContent = 'Close';
            close.addEventListener('click', () => {
                panel.hidden = true;
                button.setAttribute('aria-expanded', 'false');
                button.focus();
            });
            const editButton = document.createElement('button');
            editButton.type = 'button';
            editButton.className = 'zazu-btn zazu-btn-secondary';
            editButton.textContent = 'Edit offline';
            editButton.setAttribute('aria-expanded', 'false');
            heading.append(title, editButton, close);
            panel.append(heading);

            const editForm = document.createElement('form');
            editForm.className = 'mt-4 grid gap-3';
            editForm.dataset.zazuCustomerOfflineEdit = '1';
            editForm.hidden = true;
            const editFields = document.createElement('div');
            editFields.className = 'grid gap-3 sm:grid-cols-2';
            const addEditField = (labelText, name, value, type = 'text', multiline = false) => {
                const label = document.createElement('label');
                label.className = 'zazu-field';
                const caption = document.createElement('span');
                caption.className = 'zazu-label';
                caption.textContent = labelText;
                const input = document.createElement(multiline ? 'textarea' : 'input');
                input.className = multiline ? 'zazu-textarea' : 'zazu-input';
                input.name = name;
                input.value = value == null ? '' : String(value);
                if (multiline) input.rows = 3;
                else input.type = type;
                if (name === 'name') input.required = true;
                label.append(caption, input);
                editFields.append(label);
            };
            addEditField('Customer name', 'name', currentCustomer.name);
            addEditField('Primary contact', 'contact_name', currentCustomer.contact_name);
            addEditField('Phone', 'phone', currentCustomer.phone, 'tel');
            addEditField('Email', 'email', currentCustomer.email, 'email');
            addEditField('Legal / billing name', 'legal_name', currentCustomer.legal_name);
            addEditField('Registration number', 'registration_number', currentCustomer.registration_number);
            addEditField('Tax reference', 'tax_number', currentCustomer.tax_number);
            addEditField('VAT number', 'vat_number', currentCustomer.vat_number);
            addEditField('Billing address', 'billing_address', currentCustomer.billing_address, 'text', true);
            addEditField('Notes', 'notes', currentCustomer.notes, 'text', true);
            const editStatus = document.createElement('p');
            editStatus.className = 'zazu-panel-copy';
            editStatus.setAttribute('role', 'status');
            editStatus.setAttribute('aria-live', 'polite');
            const editActions = document.createElement('div');
            editActions.className = 'flex flex-wrap gap-2';
            const saveEdit = document.createElement('button');
            saveEdit.type = 'submit';
            saveEdit.className = 'zazu-btn zazu-btn-primary';
            saveEdit.textContent = 'Save offline changes';
            const cancelEdit = document.createElement('button');
            cancelEdit.type = 'button';
            cancelEdit.className = 'zazu-btn zazu-btn-ghost';
            cancelEdit.textContent = 'Cancel';
            cancelEdit.addEventListener('click', () => {
                editForm.hidden = true;
                editButton.setAttribute('aria-expanded', 'false');
            });
            editActions.append(saveEdit, cancelEdit);
            editForm.append(editFields, editStatus, editActions);
            panel.append(editForm);

            editButton.addEventListener('click', () => {
                editForm.hidden = !editForm.hidden;
                editButton.setAttribute('aria-expanded', editForm.hidden ? 'false' : 'true');
                if (!editForm.hidden) editForm.querySelector('[name="name"]')?.focus();
            });

            editForm.addEventListener('submit', async event => {
                event.preventDefault();
                if (saveEdit.disabled) return;
                const formData = new FormData(editForm);
                const payload = {
                    local_id: currentCustomer.local_id,
                    server_id: currentCustomer.server_id ?? currentCustomer.id ?? null,
                    name: String(formData.get('name') || '').trim(),
                    contact_name: String(formData.get('contact_name') || '').trim() || null,
                    phone: String(formData.get('phone') || '').trim() || null,
                    email: String(formData.get('email') || '').trim() || null,
                    legal_name: String(formData.get('legal_name') || '').trim() || null,
                    registration_number: String(formData.get('registration_number') || '').trim() || null,
                    tax_number: String(formData.get('tax_number') || '').trim() || null,
                    vat_number: String(formData.get('vat_number') || '').trim() || null,
                    billing_address: String(formData.get('billing_address') || '').trim() || null,
                    notes: String(formData.get('notes') || '').trim() || null,
                };
                if (!payload.local_id || !payload.name) {
                    editStatus.textContent = 'A customer name and local workspace identity are required.';
                    return;
                }

                saveEdit.disabled = true;
                saveEdit.textContent = 'Saving…';
                editStatus.textContent = '';
                try {
                    const nextState = JSON.parse(JSON.stringify(window.ZazuOffline.getState()));
                    const customerIndex = nextState.customers.findIndex(item =>
                        item.local_id === payload.local_id
                    );
                    if (customerIndex < 0) throw new Error('This customer is no longer available in the local workspace.');
                    nextState.customers[customerIndex] = {
                        ...nextState.customers[customerIndex],
                        ...payload,
                        pending_sync: true,
                    };
                    await window.ZazuOffline.queueMutation('customer', 'update', payload, nextState);
                    editStatus.textContent = 'Saved on this device. It will sync when Zazu reconnects.';
                    editForm.hidden = true;
                    editButton.setAttribute('aria-expanded', 'false');
                    panel.hidden = true;
                    button.setAttribute('aria-expanded', 'false');
                    window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
                } catch (error) {
                    editStatus.textContent = error.message || 'The customer change could not be saved locally.';
                } finally {
                    saveEdit.disabled = false;
                    saveEdit.textContent = 'Save offline changes';
                }
            });

            const fields = [
                ['Primary contact', currentCustomer.contact_name],
                ['Phone', currentCustomer.phone],
                ['Email', currentCustomer.email],
                ['Legal / billing name', currentCustomer.legal_name],
                ['Registration number', currentCustomer.registration_number],
                ['VAT number', currentCustomer.vat_number],
                ['Billing address', currentCustomer.billing_address],
                ['Notes', currentCustomer.notes],
            ].filter(([, value]) => value !== null && value !== undefined && String(value).trim() !== '');
            const details = document.createElement('div');
            details.className = 'zazu-detail-rows mt-3';
            for (const [label, value] of fields) {
                const item = document.createElement('div');
                item.className = 'zazu-detail-row';
                const name = document.createElement('div');
                name.className = 'zazu-detail-label';
                name.textContent = label;
                const detail = document.createElement('div');
                detail.className = 'zazu-detail-value';
                detail.textContent = String(value);
                item.append(name, detail);
                details.append(item);
            }
            if (fields.length) panel.append(details);

            const jobs = (currentState.jobs || []).filter(job =>
                (currentCustomer.local_id && job.customer_local_id === currentCustomer.local_id)
                || (customerServerId > 0 && Number(job.customer_id || 0) === customerServerId)
            );
            const workHeading = document.createElement('div');
            workHeading.className = 'zazu-panel-title mt-4';
            workHeading.textContent = 'Work on this device (' + jobs.length + ')';
            panel.append(workHeading);
            const workList = document.createElement('div');
            workList.className = 'zazu-list mt-2';
            for (const job of jobs) {
                const item = document.createElement('div');
                item.className = 'zazu-list-item';
                const main = document.createElement('div');
                main.className = 'zazu-list-main min-w-0 flex-1';
                const jobTitle = document.createElement('div');
                jobTitle.className = 'zazu-list-title';
                jobTitle.textContent = job.name || 'Untitled work';
                const meta = document.createElement('div');
                meta.className = 'zazu-list-meta';
                meta.textContent = [job.reference || 'Local job', job.event_date || 'Date not set', String(job.status || 'draft').replace(/_/g, ' ')].join(' · ');
                main.append(jobTitle, meta);
                item.append(main);
                workList.append(item);
            }
            if (!jobs.length) {
                const empty = document.createElement('p');
                empty.className = 'zazu-panel-copy mt-2';
                empty.textContent = 'No linked work records are stored on this device.';
                workList.append(empty);
            }
            panel.append(workList);

            const jobLocalIds = new Set(jobs.map(job => job.local_id).filter(Boolean));
            const jobServerIds = new Set(jobs.map(job => Number(job.server_id ?? job.id)).filter(id => id > 0));
            const quotes = (currentState.quotes || []).filter(quote =>
                (quote.event_local_id && jobLocalIds.has(quote.event_local_id))
                || (quote.event_id && jobServerIds.has(Number(quote.event_id)))
            );
            const quoteHeading = document.createElement('div');
            quoteHeading.className = 'zazu-panel-title mt-4';
            quoteHeading.textContent = 'Quotes on this device (' + quotes.length + ')';
            panel.append(quoteHeading);
            const quoteList = document.createElement('div');
            quoteList.className = 'zazu-list mt-2';
            for (const quote of quotes) {
                const item = document.createElement('div');
                item.className = 'zazu-list-item';
                const main = document.createElement('div');
                main.className = 'zazu-list-main min-w-0 flex-1';
                const quoteTitle = document.createElement('div');
                quoteTitle.className = 'zazu-list-title';
                quoteTitle.textContent = quote.reference || 'Draft quote';
                const meta = document.createElement('div');
                meta.className = 'zazu-list-meta';
                meta.textContent = String(quote.status || 'draft').replace(/_/g, ' ');
                main.append(quoteTitle, meta);
                item.append(main);
                quoteList.append(item);
            }
            if (!quotes.length) {
                const empty = document.createElement('p');
                empty.className = 'zazu-panel-copy mt-2';
                empty.textContent = 'No linked quotes are stored on this device.';
                quoteList.append(empty);
            }
            panel.append(quoteList);
            const note = document.createElement('p');
            note.className = 'zazu-panel-copy mt-4';
            note.textContent = 'Offline view uses this device’s last synchronised and locally saved records. Changes made on another device will appear after reconnection.';
            panel.append(note);
        });
        actions.append(button);
        row.dataset.zazuCustomerDetailsBound = '1';
    };

    const renderCustomers = (state) => {
        const directory = document.querySelector('.zazu-customer-directory');
        if (!directory) return;

        directory.querySelectorAll('[data-zazu-local-record="customer"]').forEach(node => {
            const detail = node.nextElementSibling;
            if (detail?.matches('[data-zazu-customer-offline-detail]')) detail.remove();
            node.remove();
        });
        const customers = [];
        for (const customer of state?.customers || []) {
            if (!customer?.local_id) continue;

            const serverId = customer.server_id ?? customer.id;
            const serverRow = serverId
                ? directory.querySelector('[data-zazu-server-customer-id="' + Number(serverId) + '"]')
                : null;

            if (serverRow) {
                // A locally edited server record must override the cached HTML
                // snapshot while disconnected; otherwise the directory shows
                // stale identity/contact details until the next server response.
                const title = serverRow.querySelector('.zazu-list-title');
                if (title) title.textContent = customer.name || 'Saved customer';

                const meta = serverRow.querySelector('.zazu-list-meta');
                if (meta) {
                    meta.textContent = [customer.phone, customer.email].filter(Boolean).join(' · ') || 'No phone';
                }
                const syncStatus = localSyncStatus(state, 'customer', customer.local_id, serverId, customer.pending_sync === true);
                const syncChip = serverRow.querySelector('.zazu-chip');
                if (syncChip) {
                    syncChip.textContent = syncStatus.label;
                    syncChip.title = syncStatus.title;
                    syncChip.setAttribute('aria-label', syncStatus.label + '. ' + syncStatus.title);
                }
                addCustomerOfflineDetails(serverRow, customer);
                continue;
            }

            customers.push(customer);
        }
        const pendingCustomers = customers.filter(customer => customer.server_id == null && customer.id == null);
        const empty = directory.querySelector('.zazu-empty');
        const header = directory.querySelector('.zazu-record-header');

        if (customers.length && !header) {
            const recordHeader = document.createElement('div');
            recordHeader.className = 'zazu-record-header';
            recordHeader.dataset.recordCols = '2';
            recordHeader.append(
                text('div', 'zazu-record-header-note', 'Customer'),
                text('div', 'zazu-record-header-cell', 'Work'),
                text('div', 'zazu-record-header-cell', 'Sync')
            );
            directory.insertBefore(recordHeader, empty || null);
        }

        for (const customer of customers) {
            const row = document.createElement('div');
            row.className = 'zazu-list-item';
            row.dataset.zazuLocalRecord = 'customer';

            const identity = document.createElement('div');
            identity.className = 'zazu-row-with-avatar min-w-0 flex-1';
            const initials = text('span', 'zazu-avatar', String(customer.name || 'C').trim().charAt(0).toUpperCase());
            initials.setAttribute('aria-hidden', 'true');
            const main = document.createElement('div');
            main.className = 'zazu-list-main';
            main.append(
                text('div', 'zazu-list-title', customer.name || 'Saved customer'),
                text('div', 'zazu-list-meta', [customer.contact_name, customer.phone, customer.email].filter(Boolean).join(' · ') || 'Contact not recorded')
            );
            identity.append(initials, main);

            const work = document.createElement('div');
            work.className = 'zazu-list-side';
            work.append(text('div', 'zazu-side-primary', 'Available offline'));

            const sync = document.createElement('div');
            sync.className = 'zazu-action-group';
            const syncStatus = localSyncStatus(state, 'customer', customer.local_id, customer.server_id ?? customer.id, customer.pending_sync === true);
            const syncChip = text('span', 'zazu-chip zazu-chip-neutral', syncStatus.label);
            syncChip.title = syncStatus.title;
            syncChip.setAttribute('aria-label', syncStatus.label + '. ' + syncStatus.title);
            sync.append(syncChip);

            row.append(identity, work, sync);
            addCustomerOfflineDetails(row, customer);
            if (empty?.parentNode === directory) directory.insertBefore(row, empty);
            else directory.append(row);
        }

        if (empty) empty.hidden = customers.length > 0;
        const count = directory.closest('main, [data-page-content]')?.querySelector('.zazu-command-meta-value');
        if (count && pendingCustomers.length) {
            const serverCount = Number(count.dataset.serverCount ?? count.textContent.match(/[0-9]+/)?.[0] ?? 0);
            count.dataset.serverCount = String(Number.isFinite(serverCount) ? serverCount : 0);
            count.textContent = String(Number(count.dataset.serverCount) + pendingCustomers.length);
        }
    };

    const addJobOfflineDetails = (row, job) => {
        if (row.dataset.zazuJobDetailsBound === '1') return;
        const actions = row.querySelector('.zazu-ops-actions') || row;
        const serverEventId = Number(job.server_id ?? job.id ?? 0);

        // Synced jobs also need a device-local attachment path. Previously the
        // attachment control existed only on pending local-job rows, leaving
        // existing jobs unable to accept files while disconnected.
        if (serverEventId > 0 && !actions.querySelector('[data-zazu-server-job-attachments]')) {
            const attachmentInput = document.createElement('input');
            attachmentInput.type = 'file';
            attachmentInput.multiple = true;
            attachmentInput.accept = '.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.jpg,.jpeg,.png,.webp';
            attachmentInput.hidden = true;
            attachmentInput.dataset.zazuServerJobAttachments = String(serverEventId);

            const attachButton = document.createElement('button');
            attachButton.type = 'button';
            attachButton.className = 'zazu-btn zazu-btn-secondary';
            attachButton.textContent = 'Attach files';
            attachButton.addEventListener('click', () => attachmentInput.click());

            const attachmentStatus = document.createElement('span');
            attachmentStatus.className = 'zazu-chip zazu-chip-neutral';
            attachmentStatus.setAttribute('role', 'status');
            attachmentStatus.setAttribute('aria-live', 'polite');

            attachmentInput.addEventListener('change', async () => {
                const files = [...(attachmentInput.files || [])];
                if (!files.length) return;

                attachButton.disabled = true;
                attachmentStatus.textContent = 'Saving files…';
                try {
                    await window.ZazuOffline.queueAttachments(serverEventId, files);
                    attachmentInput.value = '';
                    attachmentStatus.textContent = navigator.onLine
                        ? 'Files queued'
                        : 'Saved offline';
                } catch (error) {
                    attachmentStatus.textContent = 'Not saved';
                    attachmentStatus.title = error.message || 'Files could not be saved locally.';
                } finally {
                    attachButton.disabled = false;
                }
            });

            actions.append(attachmentInput, attachButton, attachmentStatus);
        }

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'zazu-btn zazu-btn-ghost';
        button.textContent = 'View offline';
        button.title = 'Inspect this job using records stored on this device';
        button.setAttribute('aria-expanded', 'false');
        button.addEventListener('click', () => {
            const currentState = window.ZazuOffline?.getState();
            if (!currentState) return;
            const serverId = Number(job.server_id ?? job.id ?? 0);
            const currentJob = (currentState.jobs || []).find(item =>
                (job.local_id && item.local_id === job.local_id)
                || (serverId > 0 && Number(item.server_id ?? item.id ?? 0) === serverId)
            ) || job;
            const customer = (currentState.customers || []).find(item =>
                (currentJob.customer_local_id && item.local_id === currentJob.customer_local_id)
                || (currentJob.customer_id && Number(item.id ?? item.server_id) === Number(currentJob.customer_id))
            );
            let panel = row.nextElementSibling;
            if (!panel?.matches('[data-zazu-job-offline-detail]')) {
                panel = document.createElement('section');
                panel.className = 'zazu-panel mt-3';
                panel.dataset.zazuJobOfflineDetail = '1';
                panel.setAttribute('role', 'region');
                panel.setAttribute('aria-label', 'Offline job details');
                row.insertAdjacentElement('afterend', panel);
            }
            const opening = panel.hidden || panel.childElementCount === 0;
            panel.hidden = !opening;
            button.setAttribute('aria-expanded', opening ? 'true' : 'false');
            if (!opening) return;

            panel.replaceChildren();
            const heading = document.createElement('div');
            heading.className = 'zazu-panel-head';
            const title = document.createElement('div');
            title.className = 'zazu-panel-title';
            title.textContent = currentJob.name || 'Job details';
            const close = document.createElement('button');
            close.type = 'button';
            close.className = 'zazu-btn zazu-btn-ghost';
            close.textContent = 'Close';
            close.addEventListener('click', () => {
                panel.hidden = true;
                button.setAttribute('aria-expanded', 'false');
                button.focus();
            });
            const editButton = document.createElement('button');
            editButton.type = 'button';
            editButton.className = 'zazu-btn zazu-btn-secondary';
            editButton.textContent = 'Edit offline';
            editButton.setAttribute('aria-expanded', 'false');
            heading.append(title, editButton, close);
            panel.append(heading);

            const editForm = document.createElement('form');
            editForm.className = 'mt-4 grid gap-3';
            editForm.dataset.zazuJobOfflineEdit = '1';
            editForm.hidden = true;
            const editFields = document.createElement('div');
            editFields.className = 'grid gap-3 sm:grid-cols-2';
            const addEditField = (labelText, name, value, type = 'text', multiline = false, maxLength = null) => {
                const label = document.createElement('label');
                label.className = 'zazu-field';
                const caption = document.createElement('span');
                caption.className = 'zazu-label';
                caption.textContent = labelText;
                const input = document.createElement(multiline ? 'textarea' : 'input');
                input.className = multiline ? 'zazu-textarea' : 'zazu-input';
                input.name = name;
                input.value = value == null ? '' : String(value);
                if (multiline) input.rows = 3;
                else input.type = type;
                if (name === 'name') input.required = true;
                if (maxLength) input.maxLength = maxLength;
                label.append(caption, input);
                editFields.append(label);
            };
            const addEditSelect = (labelText, name, value, options) => {
                const label = document.createElement('label');
                label.className = 'zazu-field';
                const caption = document.createElement('span');
                caption.className = 'zazu-label';
                caption.textContent = labelText;
                const select = document.createElement('select');
                select.className = 'zazu-input';
                select.name = name;
                for (const option of options) {
                    const item = document.createElement('option');
                    item.value = option.value;
                    item.textContent = option.label;
                    select.append(item);
                }
                select.value = value;
                label.append(caption, select);
                editFields.append(label);
            };
            const statusOptions = {
                draft: [{ value: 'draft', label: 'Draft' }, { value: 'confirmed', label: 'Confirmed' }, { value: 'cancelled', label: 'Cancelled' }],
                confirmed: [{ value: 'confirmed', label: 'Confirmed' }, { value: 'in_progress', label: 'In progress' }, { value: 'cancelled', label: 'Cancelled' }],
                in_progress: [{ value: 'in_progress', label: 'In progress' }, { value: 'completed', label: 'Completed' }, { value: 'cancelled', label: 'Cancelled' }],
                completed: [{ value: 'completed', label: 'Completed' }],
                cancelled: [{ value: 'cancelled', label: 'Cancelled' }],
            };
            const availableStatuses = statusOptions[currentJob.status || 'draft'] || statusOptions.draft;
            addEditSelect('Work status', 'status', currentJob.status || 'draft', availableStatuses);
            const currentCustomerLocalId = currentJob.customer_local_id || customer?.local_id || '';
            const customerOptions = (currentState.customers || [])
                .filter(item => item && item.local_id)
                .map(item => ({ value: item.local_id, label: item.name || 'Unnamed customer' }));
            if (currentCustomerLocalId && !customerOptions.some(item => item.value === currentCustomerLocalId)) {
                customerOptions.unshift({ value: currentCustomerLocalId, label: customer?.name || currentJob.customer_name || 'Current customer' });
            }
            if (customerOptions.length) {
                addEditSelect('Customer', 'customer_local_id', currentCustomerLocalId, customerOptions);
            }
            addEditField('Job name', 'name', currentJob.name, 'text', false, 255);
            addEditField('Event date', 'event_date', currentJob.event_date ? String(currentJob.event_date).slice(0, 10) : '', 'date');
            addEditField('Venue / address', 'event_address', currentJob.event_address, 'text', false, 255);
            addEditField('Operational notes', 'notes', currentJob.notes, 'text', true, 5000);
            const currentRequirements = (currentJob.requirements || [])
                .map(item => item.record || item)
                .filter(item => item && (item.description || item.name));
            const currentServices = currentRequirements.length
                ? currentRequirements.map(item => item.description || item.name)
                : (currentJob.services || []);
            addEditField('Services / requirements (one per line)', 'services', currentServices.join('\n'), 'text', true, 3000);
            const editStatus = document.createElement('p');
            editStatus.className = 'zazu-panel-copy';
            editStatus.setAttribute('role', 'status');
            editStatus.setAttribute('aria-live', 'polite');
            const editActions = document.createElement('div');
            editActions.className = 'flex flex-wrap gap-2';
            const saveEdit = document.createElement('button');
            saveEdit.type = 'submit';
            saveEdit.className = 'zazu-btn zazu-btn-primary';
            saveEdit.textContent = 'Save offline changes';
            const cancelEdit = document.createElement('button');
            cancelEdit.type = 'button';
            cancelEdit.className = 'zazu-btn zazu-btn-ghost';
            cancelEdit.textContent = 'Cancel';
            cancelEdit.addEventListener('click', () => {
                editForm.hidden = true;
                editButton.setAttribute('aria-expanded', 'false');
            });
            editActions.append(saveEdit, cancelEdit);
            editForm.append(editFields, editStatus, editActions);
            panel.append(editForm);

            editButton.addEventListener('click', () => {
                editForm.hidden = !editForm.hidden;
                editButton.setAttribute('aria-expanded', editForm.hidden ? 'false' : 'true');
                if (!editForm.hidden) editForm.querySelector('[name="name"]')?.focus();
            });

            editForm.addEventListener('submit', async event => {
                event.preventDefault();
                if (saveEdit.disabled) return;
                const formData = new FormData(editForm);
                const selectedCustomerLocalId = String(formData.get('customer_local_id') || currentCustomerLocalId || '');
                const selectedCustomer = (window.ZazuOffline.getState().customers || [])
                    .find(item => item.local_id === selectedCustomerLocalId);
                const payload = {
                    local_id: currentJob.local_id,
                    server_id: currentJob.server_id ?? currentJob.id ?? null,
                    name: String(formData.get('name') || '').trim(),
                    status: String(formData.get('status') || currentJob.status || 'draft'),
                    event_type: currentJob.event_type || 'Other',
                    event_date: String(formData.get('event_date') || '').trim() || null,
                    event_address: String(formData.get('event_address') || '').trim() || null,
                    notes: String(formData.get('notes') || '').trim() || null,
                    services: [...new Set(String(formData.get('services') || '')
                        .split(/[\n,]+/)
                        .map(value => value.trim())
                        .filter(Boolean))],
                    customer_local_id: String(formData.get('customer_local_id') || currentCustomerLocalId || '') || null,
                    customer_name: selectedCustomer?.name || currentJob.customer_name || null,
                    event_day_contact_id: selectedCustomerLocalId === currentCustomerLocalId ? (currentJob.event_day_contact_id ?? null) : null,
                    event_night_contact_id: selectedCustomerLocalId === currentCustomerLocalId ? (currentJob.event_night_contact_id ?? null) : null,
                };
                if (!payload.local_id || !payload.name) {
                    editStatus.textContent = 'A job name and local workspace identity are required.';
                    return;
                }

                saveEdit.disabled = true;
                saveEdit.textContent = 'Saving…';
                editStatus.textContent = '';
                try {
                    const nextState = JSON.parse(JSON.stringify(window.ZazuOffline.getState()));
                    const jobIndex = nextState.jobs.findIndex(item => item.local_id === payload.local_id);
                    if (jobIndex < 0) throw new Error('This job is no longer available in the local workspace.');
                    nextState.jobs[jobIndex] = {
                        ...nextState.jobs[jobIndex],
                        ...payload,
                        customer_id: selectedCustomer?.server_id ?? selectedCustomer?.id
                            ?? (selectedCustomerLocalId === currentCustomerLocalId ? currentJob.customer_id : null),
                        requirements: payload.services.map(description => {
                            const existing = currentRequirements.find(item => (item.description || item.name) === description);
                            return existing
                                ? { ...existing, description }
                                : { description, category: 'Other', quantity: 1, unit: 'service', status: 'open' };
                        }),
                        pending_sync: true,
                    };
                    await window.ZazuOffline.queueMutation('job', 'update', payload, nextState);
                    editStatus.textContent = 'Saved on this device. It will sync when Zazu reconnects.';
                    editForm.hidden = true;
                    editButton.setAttribute('aria-expanded', 'false');
                    panel.hidden = true;
                    button.setAttribute('aria-expanded', 'false');
                    window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
                } catch (error) {
                    editStatus.textContent = error.message || 'The job change could not be saved locally.';
                } finally {
                    saveEdit.disabled = false;
                    saveEdit.textContent = 'Save offline changes';
                }
            });

            const fields = [
                ['Reference', currentJob.reference || (serverId > 0 ? '#' + serverId : 'Local job')],
                ['Customer', customer?.name || currentJob.customer_name],
                ['Contact', customer?.contact_name || customer?.phone || customer?.email],
                ['Function type', currentJob.event_type],
                ['Date', currentJob.event_date],
                ['Venue', currentJob.event_address],
                ['Status', String(currentJob.status || 'draft').replace(/_/g, ' ')],
                ['Notes', currentJob.notes],
            ].filter(([, value]) => value !== null && value !== undefined && String(value).trim() !== '');
            const details = document.createElement('div');
            details.className = 'zazu-detail-rows mt-3';
            for (const [label, value] of fields) {
                const item = document.createElement('div');
                item.className = 'zazu-detail-row';
                const name = document.createElement('div');
                name.className = 'zazu-detail-label';
                name.textContent = label;
                const detail = document.createElement('div');
                detail.className = 'zazu-detail-value';
                detail.textContent = String(value);
                item.append(name, detail);
                details.append(item);
            }
            if (fields.length) panel.append(details);

            const addListSection = (sectionTitle, records, formatter, emptyLabel) => {
                const section = document.createElement('section');
                section.className = 'mt-4';
                const sectionHeading = document.createElement('div');
                sectionHeading.className = 'zazu-panel-title';
                sectionHeading.textContent = sectionTitle + ' (' + records.length + ')';
                section.append(sectionHeading);
                const list = document.createElement('div');
                list.className = 'zazu-list mt-2';
                if (!records.length) {
                    const empty = document.createElement('p');
                    empty.className = 'zazu-panel-copy';
                    empty.textContent = emptyLabel;
                    list.append(empty);
                } else {
                    for (const record of records) {
                        const item = document.createElement('div');
                        item.className = 'zazu-list-item';
                        const main = document.createElement('div');
                        main.className = 'zazu-list-main min-w-0 flex-1';
                        const line = formatter(record);
                        const recordTitle = document.createElement('div');
                        recordTitle.className = 'zazu-list-title';
                        recordTitle.textContent = line.title;
                        const meta = document.createElement('div');
                        meta.className = 'zazu-list-meta';
                        meta.textContent = line.meta || '';
                        main.append(recordTitle);
                        if (line.meta) main.append(meta);
                        item.append(main);
                        list.append(item);
                    }
                }
                section.append(list);
                panel.append(section);
            };

            const requirementRecords = (currentJob.requirements || [])
                .map(item => item.record || item)
                .filter(item => item && (item.description || item.name));
            const serviceRecords = requirementRecords.length
                ? requirementRecords
                : (currentJob.services || []).map(description => ({ description }));
            addListSection('Services', serviceRecords, item => ({
                title: item.description || item.name || 'Service',
                meta: [item.quantity ? String(item.quantity) + ' ' + (item.unit || '') : '', item.category, item.status].filter(Boolean).join(' · '),
            }), 'No services are stored on this device for this job.');

            const relatedQuotes = (currentState.quotes || []).filter(quote =>
                (quote.event_local_id && quote.event_local_id === currentJob.local_id)
                || (quote.event_id && serverId > 0 && Number(quote.event_id) === serverId)
            );
            addListSection('Quotes', relatedQuotes, quote => ({
                title: quote.reference || 'Draft quote',
                meta: [
                    String(quote.status || 'draft').replace(/_/g, ' '),
                    quote.latest_version?.status ? 'Version ' + quote.latest_version.status : '',
                    quote.currency || currentState.business?.currency || 'ZAR',
                    quote.latest_version?.total ? String(quote.latest_version.total) : '',
                ].filter(Boolean).join(' · '),
            }), 'No linked quotes are stored on this device.');

            const relatedPreparation = (currentState.preparations || []).filter(item =>
                (item.event_local_id && item.event_local_id === currentJob.local_id)
                || (item.event_id && serverId > 0 && Number(item.event_id) === serverId)
            );
            addListSection('Preparation', relatedPreparation, item => ({
                title: item.title || 'Preparation item',
                meta: [String(item.status || 'open').replace(/_/g, ' '), item.due_date ? 'Due ' + item.due_date : ''].filter(Boolean).join(' · '),
            }), 'No preparation items are stored on this device for this job.');

            const relatedCosts = (currentState.costs || []).filter(item =>
                (item.event_local_id && item.event_local_id === currentJob.local_id)
                || (item.event_id && serverId > 0 && Number(item.event_id) === serverId)
            );
            addListSection('Costs', relatedCosts, item => ({
                title: item.description || item.name || item.category || 'Recorded cost',
                meta: [item.amount ? String(item.currency || currentState.business?.currency || 'ZAR') + ' ' + String(item.amount) : '', item.status].filter(Boolean).join(' · '),
            }), 'No linked costs are stored on this device.');

            const relatedOrders = (currentState.purchase_orders || []).filter(item =>
                (item.event_local_id && item.event_local_id === currentJob.local_id)
                || (item.event_id && serverId > 0 && Number(item.event_id) === serverId)
            );
            addListSection('Purchasing', relatedOrders, item => ({
                title: item.reference || 'Purchase order',
                meta: [String(item.status || 'draft').replace(/_/g, ' '), (item.items || []).length + ' lines'].join(' · '),
            }), 'No linked purchase orders are stored on this device.');

            const relatedInvoices = (currentState.invoices || []).filter(item =>
                (item.event_local_id && item.event_local_id === currentJob.local_id)
                || (item.event_id && serverId > 0 && Number(item.event_id) === serverId)
            );
            addListSection('Invoices', relatedInvoices, item => ({
                title: item.number || 'Invoice',
                meta: [
                    String(item.status || 'draft').replace(/_/g, ' '),
                    item.paid_amount !== undefined ? 'Paid ' + String(item.currency || 'ZAR') + ' ' + String(item.paid_amount) : '',
                    item.balance !== undefined ? 'Balance ' + String(item.currency || 'ZAR') + ' ' + String(item.balance) : '',
                ].filter(Boolean).join(' · '),
            }), 'No linked invoices are stored on this device.');

            const note = document.createElement('p');
            note.className = 'zazu-panel-copy mt-4';
            note.textContent = 'This inspection uses the last synchronised and locally saved records on this device. It does not fetch live server data while disconnected.';
            panel.append(note);
        });
        actions.append(button);
        row.dataset.zazuJobDetailsBound = '1';
    };

    const renderJobs = (state) => {
        const register = document.querySelector('.zazu-ops-register');
        if (!register) return;

        register.querySelectorAll('[data-zazu-local-record="job"]').forEach(node => {
            const detail = node.nextElementSibling;
            if (detail?.matches('[data-zazu-job-offline-detail]')) detail.remove();
            node.remove();
        });
        const filter = new URLSearchParams(window.location.search).get('filter') || '';
        const allJobs = (state?.jobs || []).filter(job => job?.local_id);
        for (const job of allJobs) {
            const serverId = job.server_id ?? job.id;
            const serverRow = serverId
                ? register.querySelector('[data-zazu-server-job-id="' + Number(serverId) + '"]')
                : null;
            if (serverRow) {
                const syncStatus = localSyncStatus(state, 'job', job.local_id, serverId, job.pending_sync === true);
                const actions = serverRow.querySelector('.zazu-ops-actions');
                let syncChip = actions?.querySelector('[data-zazu-local-sync-chip]');
                if (actions && !syncChip) {
                    syncChip = document.createElement('span');
                    syncChip.className = 'zazu-chip zazu-chip-neutral';
                    syncChip.dataset.zazuLocalSyncChip = '1';
                    actions.prepend(syncChip);
                }
                if (syncChip) {
                    syncChip.textContent = syncStatus.label;
                    syncChip.title = syncStatus.title;
                    syncChip.setAttribute('aria-label', syncStatus.label + '. ' + syncStatus.title);
                }
                addJobOfflineDetails(serverRow, job);
            }
        }
        const jobs = allJobs.filter(job => {
            if (filter && !['all', 'draft'].includes(filter)) return false;
            const serverId = job.server_id ?? job.id;
            return !serverId || !register.querySelector('[data-zazu-server-job-id="' + Number(serverId) + '"]');
        });
        const pendingJobs = jobs.filter(job => job.server_id == null && job.id == null);
        const empty = register.querySelector('.zazu-empty');
        const header = register.querySelector('.zazu-ops-table-head');

        if (jobs.length && !header) {
            const tableHeader = document.createElement('div');
            tableHeader.className = 'zazu-ops-table-head';
            tableHeader.setAttribute('aria-hidden', 'true');
            ['Job reference', 'Client', 'Function', 'Execution', 'Status', 'Sync'].forEach(label => {
                tableHeader.append(text('span', '', label));
            });
            register.insertBefore(tableHeader, register.querySelector('[data-zazu-job-row]') || empty || null);
        }

        for (const job of jobs) {
            const customer = (state?.customers || []).find(item => item.local_id === job.customer_local_id);
            const row = document.createElement('article');
            row.className = 'zazu-ops-row';
            row.dataset.zazuLocalRecord = 'job';
            row.dataset.zazuLocalId = job.local_id;

            const main = document.createElement('div');
            main.className = 'zazu-ops-row-main';
            main.append(
                (() => {
                    const ref = document.createElement('span');
                    ref.className = 'zazu-ops-ref';
                    const serverId = job.server_id ?? job.id;
                    ref.append(
                        text('span', 'zazu-ops-ref-label', serverId ? 'Job reference' : 'Local reference'),
                        text('strong', '', serverId ? (job.reference || '#' + serverId) : 'Pending sync')
                    );
                    return ref;
                })(),
                (() => {
                    const client = document.createElement('span');
                    client.className = 'zazu-ops-client';
                    client.append(text('strong', '', job.customer_name || customer?.name || 'Customer not set'), text('small', '', customer?.phone || customer?.email || 'Saved on this device'));
                    return client;
                })(),
                (() => {
                    const type = document.createElement('span');
                    type.className = 'zazu-ops-function';
                    type.append(text('strong', '', job.event_type || 'Event'), text('small', '', (Array.isArray(job.services) ? job.services : []).join(', ') || 'Services not recorded'));
                    return type;
                })(),
                (() => {
                    const date = document.createElement('span');
                    date.className = 'zazu-ops-date';
                    date.append(text('strong', '', job.event_date || 'Date not set'), text('small', '', job.name || 'Untitled job'));
                    return date;
                })(),
                (() => {
                    const status = document.createElement('span');
                    const statusValue = String(job.status || 'draft');
                    const statusText = statusValue.replace(/_/g, ' ');
                    const statusLabel = statusText.charAt(0).toUpperCase() + statusText.slice(1);
                    const statusClass = {
                        confirmed: 'zazu-chip-success',
                        in_progress: 'zazu-chip-info',
                        completed: 'zazu-chip-accent',
                        cancelled: 'zazu-chip-danger',
                    }[statusValue] || 'zazu-chip-neutral';
                    const chip = text('span', 'zazu-chip ' + statusClass, statusLabel);
                    chip.title = 'Current job lifecycle status stored on this device.';
                    chip.setAttribute('aria-label', 'Job status: ' + statusLabel);
                    status.append(chip);
                    return status;
                })()
            );

            const actions = document.createElement('div');
            actions.className = 'zazu-ops-actions';
            const syncStatus = localSyncStatus(state, 'job', job.local_id, job.server_id ?? job.id, job.pending_sync === true);
            const syncChip = text('span', 'zazu-chip zazu-chip-neutral', syncStatus.label);
            syncChip.title = syncStatus.title;
            syncChip.setAttribute('aria-label', syncStatus.label + '. ' + syncStatus.title);
            actions.append(syncChip);

            if (!(job.server_id ?? job.id)) {
                const attachmentInput = document.createElement('input');
                attachmentInput.type = 'file';
                attachmentInput.multiple = true;
                attachmentInput.accept = '.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.jpg,.jpeg,.png,.webp';
                attachmentInput.hidden = true;
                attachmentInput.dataset.zazuLocalJobAttachments = job.local_id;

                const attach = document.createElement('button');
                attach.type = 'button';
                attach.className = 'zazu-btn zazu-btn-secondary';
                attach.textContent = 'Attach files';
                attach.addEventListener('click', () => attachmentInput.click());
                attachmentInput.addEventListener('change', async () => {
                    const files = [...(attachmentInput.files || [])];
                    if (!files.length) return;
                    attach.disabled = true;
                    const originalLabel = attach.textContent;
                    attach.textContent = 'Saving…';
                    try {
                        await window.ZazuOffline.queueAttachments(null, files, '', job.local_id);
                        attachmentInput.value = '';
                        attach.textContent = 'Files saved';
                        attach.setAttribute('aria-label', files.length + ' files saved locally for ' + (job.name || 'this job'));
                    } catch (error) {
                        attach.textContent = 'Try again';
                        attach.title = error.message || 'Files could not be saved locally.';
                    } finally {
                        attach.disabled = false;
                        if (attach.textContent === 'Saving…') attach.textContent = originalLabel;
                    }
                });
                actions.append(attachmentInput, attach);

                const attachmentList = document.createElement('div');
                attachmentList.className = 'zazu-local-attachment-list';
                attachmentList.setAttribute('aria-live', 'polite');
                actions.append(attachmentList);


            }

            row.append(main, actions);
            addJobOfflineDetails(row, job);

            if (empty?.parentNode === register) register.insertBefore(row, empty);
            else register.append(row);
        }

        if (empty) empty.hidden = jobs.length > 0;
        const total = document.querySelector('.zazu-ops-total');
        if (total) {
            const serverCount = Number(total.dataset.serverCount ?? total.textContent.match(/[0-9]+/)?.[0] ?? 0);
            total.dataset.serverCount = String(Number.isFinite(serverCount) ? serverCount : 0);
            total.textContent = (Number(total.dataset.serverCount) + pendingJobs.length) + ' records';
        }

        const allValue = [...document.querySelectorAll('.zazu-workload-pill')].find(item => item.textContent.includes('All Work'));
        const draftValue = [...document.querySelectorAll('.zazu-workload-pill')].find(item => item.textContent.includes('Drafts'));
        for (const pill of [allValue, draftValue]) {
            const value = pill?.querySelector('strong');
            if (!value) continue;
            const serverCount = Number(value.dataset.serverCount ?? value.textContent.trim());
            value.dataset.serverCount = String(Number.isFinite(serverCount) ? serverCount : 0);
            value.textContent = String(Number(value.dataset.serverCount) + pendingJobs.length);
        }
    };

    const refreshLocalAttachmentLists = () => {
        document.querySelectorAll('[data-zazu-local-record="job"][data-zazu-local-id]').forEach(row => {
            const list = row.querySelector('.zazu-local-attachment-list');
            const localId = row.dataset.zazuLocalId;
            if (!list || !localId) return;
            window.ZazuOffline.loadAttachments(null, localId).then(records => {
                if (!row.isConnected) return;
                list.replaceChildren();
                for (const record of records) {
                    const item = document.createElement('div');
                    item.className = 'zazu-local-attachment-item';
                    const open = document.createElement('button');
                    open.type = 'button';
                    open.className = 'zazu-btn zazu-btn-ghost';
                    open.textContent = record.original_name || 'Attached file';
                    open.addEventListener('click', async () => {
                        open.disabled = true;
                        try {
                            const file = await window.ZazuOffline.getAttachmentFile(record.idempotency_key);
                            const url = URL.createObjectURL(file);
                            const link = document.createElement('a');
                            link.href = url;
                            link.download = record.original_name || 'zazu-attachment';
                            document.body.append(link);
                            link.click();
                            link.remove();
                            window.setTimeout(() => URL.revokeObjectURL(url), 1000);
                        } catch (error) {
                            open.title = error.message || 'This file is not available on this device.';
                        } finally {
                            open.disabled = false;
                        }
                    });
                    const status = document.createElement('span');
                    status.className = 'zazu-chip zazu-chip-neutral';
                    status.textContent = record.status === 'synced' ? 'Synced' : 'Saved offline';
                    item.append(open, status);
                    list.append(item);
                }
            }).catch(() => {
                if (row.isConnected) list.textContent = 'Attachments could not be loaded from this device.';
            });
        });
    };

    const addQuoteOfflineDetails = (row, quote) => {
        if (row.dataset.zazuQuoteDetailsBound === '1') return;
        const actions = row.querySelector('.zazu-action-group') || row;
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'zazu-btn zazu-btn-ghost';
        button.textContent = 'View offline';
        button.title = 'Inspect this quote and its invoice records from this device';
        button.setAttribute('aria-expanded', 'false');
        button.addEventListener('click', () => {
            const currentState = window.ZazuOffline?.getState();
            if (!currentState) return;
            const serverId = Number(quote.server_id ?? quote.id ?? 0);
            const currentQuote = (currentState.quotes || []).find(item =>
                (quote.local_id && item.local_id === quote.local_id)
                || (serverId > 0 && Number(item.server_id ?? item.id ?? 0) === serverId)
            ) || quote;
            let panel = row.nextElementSibling;
            if (!panel?.matches('[data-zazu-quote-offline-detail]')) {
                panel = document.createElement('section');
                panel.className = 'zazu-panel mt-3';
                panel.dataset.zazuQuoteOfflineDetail = '1';
                panel.setAttribute('role', 'region');
                panel.setAttribute('aria-label', 'Offline quote details');
                row.insertAdjacentElement('afterend', panel);
            }
            const opening = panel.hidden || panel.childElementCount === 0;
            panel.hidden = !opening;
            button.setAttribute('aria-expanded', opening ? 'true' : 'false');
            if (!opening) return;

            panel.replaceChildren();
            const heading = document.createElement('div');
            heading.className = 'zazu-panel-head';
            const title = document.createElement('div');
            title.className = 'zazu-panel-title';
            title.textContent = currentQuote.reference || 'Quote details';
            const close = document.createElement('button');
            close.type = 'button';
            close.className = 'zazu-btn zazu-btn-ghost';
            close.textContent = 'Close';
            close.addEventListener('click', () => {
                panel.hidden = true;
                button.setAttribute('aria-expanded', 'false');
                button.focus();
            });
            heading.append(title, close);
            panel.append(heading);

            const job = (currentState.jobs || []).find(item =>
                (currentQuote.event_local_id && item.local_id === currentQuote.event_local_id)
                || (currentQuote.event_id && Number(item.id ?? item.server_id) === Number(currentQuote.event_id))
            );
            const customer = (currentState.customers || []).find(item =>
                (job?.customer_local_id && item.local_id === job.customer_local_id)
                || (job?.customer_id && Number(item.id ?? item.server_id) === Number(job.customer_id))
            );
            const currentQuoteServerId = Number(currentQuote.server_id ?? currentQuote.id ?? serverId ?? 0);
            const version = currentQuote.latest_version || currentQuote.latestVersion || {};

            // Expose the existing quote-acceptance queue from the local quote
            // view, so acceptance does not depend on a cached Laravel detail page.
            if (currentQuote.status === 'sent' && version.status === 'sent') {
                const acceptance = document.createElement('div');
                acceptance.className = 'zazu-work-actions mt-3';
                const acceptButton = document.createElement('button');
                acceptButton.type = 'button';
                acceptButton.className = 'zazu-btn zazu-btn-primary';
                acceptButton.textContent = 'Accept quote offline';
                const acceptanceStatus = document.createElement('p');
                acceptanceStatus.className = 'zazu-panel-copy';
                acceptanceStatus.setAttribute('role', 'status');
                acceptButton.addEventListener('click', async () => {
                    const latestState = window.ZazuOffline?.getState();
                    const latestQuote = latestState?.quotes?.find(item =>
                        item.local_id === currentQuote.local_id
                    );
                    const latestVersion = latestQuote?.latest_version || latestQuote?.latestVersion;
                    if (!latestQuote?.local_id || latestQuote.status !== 'sent' || latestVersion?.status !== 'sent') {
                        acceptanceStatus.textContent = 'This quote is no longer awaiting acceptance in the local workspace. Refresh after reconnecting.';
                        acceptButton.disabled = true;
                        return;
                    }

                    const nextState = JSON.parse(JSON.stringify(latestState));
                    const nextQuote = nextState.quotes.find(item => item.local_id === latestQuote.local_id);
                    nextQuote.status = 'accepted';
                    if (nextQuote.latest_version) nextQuote.latest_version.status = 'accepted';
                    if (nextQuote.latestVersion) nextQuote.latestVersion.status = 'accepted';
                    const linkedWork = nextState.jobs?.find(item => item.local_id === nextQuote.event_local_id);
                    if (linkedWork?.status === 'draft') linkedWork.status = 'confirmed';

                    acceptButton.disabled = true;
                    acceptButton.textContent = 'Saving acceptance…';
                    try {
                        await window.ZazuOffline.queueMutation('quote_acceptance', 'create', {
                            local_id: crypto.randomUUID(),
                            server_id: null,
                            quote_local_id: nextQuote.local_id,
                            customer_name: customer?.name || job?.customer_name || 'Customer',
                        }, nextState);
                        acceptanceStatus.textContent = 'Acceptance saved on this device and queued for server validation.';
                        window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
                    } catch (error) {
                        acceptButton.disabled = false;
                        acceptButton.textContent = 'Accept quote offline';
                        acceptanceStatus.textContent = error?.message || 'Acceptance could not be saved on this device.';
                    }
                });
                acceptance.append(acceptButton, acceptanceStatus);
                panel.append(acceptance);
            }

            const fields = [
                ['Status', String(currentQuote.status || version.status || 'draft').replace(/_/g, ' ')],
                ['Version', version.version ? String(version.version) : null],
                ['Job', currentQuote.event_name || job?.name],
                ['Customer', customer?.name || job?.customer_name],
                ['Currency', currentQuote.currency || currentState.business?.currency || 'ZAR'],
                ['Subtotal', version.subtotal],
                ['Tax', version.tax_total],
                ['Total', version.total ?? currentQuote.total],
                ['Deposit', version.deposit_percent !== undefined ? String(version.deposit_percent) + '%' : null],
                ['Deposit amount', version.deposit_amount],
                ['Notes', version.notes ?? currentQuote.notes],
            ].filter(([, value]) => value !== null && value !== undefined && String(value).trim() !== '');
            const details = document.createElement('div');
            details.className = 'zazu-detail-rows mt-3';
            for (const [label, value] of fields) {
                const item = document.createElement('div');
                item.className = 'zazu-detail-row';
                const name = document.createElement('div');
                name.className = 'zazu-detail-label';
                name.textContent = label;
                const detail = document.createElement('div');
                detail.className = 'zazu-detail-value';
                detail.textContent = String(value);
                item.append(name, detail);
                details.append(item);
            }
            if (fields.length) panel.append(details);

            const items = version.items || currentQuote.items || [];
            const itemSection = document.createElement('section');
            itemSection.className = 'mt-4';
            const itemHeading = document.createElement('div');
            itemHeading.className = 'zazu-panel-title';
            itemHeading.textContent = 'Quote lines (' + items.length + ')';
            itemSection.append(itemHeading);
            const itemList = document.createElement('div');
            itemList.className = 'zazu-list mt-2';
            if (!items.length) {
                const empty = document.createElement('p');
                empty.className = 'zazu-panel-copy';
                empty.textContent = 'No quote lines are stored on this device.';
                itemList.append(empty);
            } else {
                for (const line of items) {
                    const item = document.createElement('div');
                    item.className = 'zazu-list-item';
                    const main = document.createElement('div');
                    main.className = 'zazu-list-main min-w-0 flex-1';
                    const lineTitle = document.createElement('div');
                    lineTitle.className = 'zazu-list-title';
                    lineTitle.textContent = line.description || 'Quote line';
                    const meta = document.createElement('div');
                    meta.className = 'zazu-list-meta';
                    meta.textContent = [
                        line.quantity !== undefined ? String(line.quantity) + (line.unit ? ' ' + line.unit : '') : '',
                        line.unit_price !== undefined ? String(currentQuote.currency || 'ZAR') + ' ' + String(line.unit_price) : '',
                        line.line_total !== undefined ? 'Line total ' + String(line.line_total) : '',
                    ].filter(Boolean).join(' · ');
                    main.append(lineTitle);
                    if (meta.textContent) main.append(meta);
                    item.append(main);
                    itemList.append(item);
                }
            }
            itemSection.append(itemList);
            panel.append(itemSection);

            const invoice = (currentState.invoices || []).find(item =>
                (item.quote_id && currentQuoteServerId > 0 && Number(item.quote_id) === currentQuoteServerId)
                || (item.quote_local_id && item.quote_local_id === currentQuote.local_id)
            );
            const invoices = invoice ? [invoice] : [];
            const invoiceSection = document.createElement('section');
            invoiceSection.className = 'mt-4';
            const invoiceHeading = document.createElement('div');
            invoiceHeading.className = 'zazu-panel-title';
            invoiceHeading.textContent = 'Invoice and payments (' + invoices.length + ')';
            invoiceSection.append(invoiceHeading);
            const invoiceList = document.createElement('div');
            invoiceList.className = 'zazu-list mt-2';
            if (!invoice) {
                const empty = document.createElement('p');
                empty.className = 'zazu-panel-copy';
                empty.textContent = 'No linked invoice is stored on this device.';
                invoiceList.append(empty);
            } else {
                const invoiceItem = document.createElement('div');
                invoiceItem.className = 'zazu-list-item';
                const invoiceMain = document.createElement('div');
                invoiceMain.className = 'zazu-list-main min-w-0 flex-1';
                const invoiceTitle = document.createElement('div');
                invoiceTitle.className = 'zazu-list-title';
                invoiceTitle.textContent = invoice.number || 'Invoice';
                const invoiceMeta = document.createElement('div');
                invoiceMeta.className = 'zazu-list-meta';
                invoiceMeta.textContent = [
                    String(invoice.status || 'draft').replace(/_/g, ' '),
                    invoice.paid_amount !== undefined ? 'Paid ' + String(invoice.currency || 'ZAR') + ' ' + String(invoice.paid_amount) : '',
                    invoice.balance !== undefined ? 'Balance ' + String(invoice.currency || 'ZAR') + ' ' + String(invoice.balance) : '',
                ].filter(Boolean).join(' · ');
                invoiceMain.append(invoiceTitle, invoiceMeta);
                invoiceItem.append(invoiceMain);
                invoiceList.append(invoiceItem);

                for (const payment of invoice.payments || []) {
                    const paymentItem = document.createElement('div');
                    paymentItem.className = 'zazu-list-item';
                    const paymentMain = document.createElement('div');
                    paymentMain.className = 'zazu-list-main min-w-0 flex-1';
                    const paymentTitle = document.createElement('div');
                    paymentTitle.className = 'zazu-list-title';
                    paymentTitle.textContent = payment.reference || payment.method || 'Payment';
                    const paymentMeta = document.createElement('div');
                    paymentMeta.className = 'zazu-list-meta';
                    paymentMeta.textContent = [
                        payment.amount !== undefined ? String(invoice.currency || 'ZAR') + ' ' + String(payment.amount) : '',
                        payment.paid_at || payment.received_at || '',
                        payment.status || '',
                    ].filter(Boolean).join(' · ');
                    paymentMain.append(paymentTitle);
                    if (paymentMeta.textContent) paymentMain.append(paymentMeta);
                    paymentItem.append(paymentMain);
                    invoiceList.append(paymentItem);
                }
            }
            invoiceSection.append(invoiceList);
            panel.append(invoiceSection);

            const note = document.createElement('p');
            note.className = 'zazu-panel-copy mt-4';
            note.textContent = 'This quote view uses the last synchronised and locally saved version. It does not fetch live pricing, tax or acceptance status while disconnected.';
            panel.append(note);
        });
        actions.append(button);
        row.dataset.zazuQuoteDetailsBound = '1';
    };

    const renderQuotes = (state) => {
        const directory = document.querySelector('[data-zazu-local-quotes]');
        if (!directory) return;

        directory.querySelectorAll('[data-zazu-local-record="quote"]').forEach(node => {
            const detail = node.nextElementSibling;
            if (detail?.matches('[data-zazu-quote-offline-detail]')) detail.remove();
            node.remove();
        });
        const quotes = state?.quotes || [];
        const empty = directory.querySelector('.zazu-empty');

        for (const quote of quotes) {
            if (!quote?.local_id) continue;

            const serverId = quote.server_id ?? quote.id;
            const job = (state?.jobs || []).find(item =>
                (quote.event_local_id && item.local_id === quote.event_local_id)
                || (quote.event_id && Number(item.id ?? item.server_id) === Number(quote.event_id))
            );
            const row = document.createElement('div');
            row.className = 'zazu-list-item';
            row.dataset.zazuLocalRecord = 'quote';

            const identity = document.createElement('div');
            identity.className = 'zazu-row-with-avatar min-w-0 flex-1';
            const marker = text('span', 'zazu-avatar', 'Q');
            marker.setAttribute('aria-hidden', 'true');

            const main = document.createElement('div');
            main.className = 'zazu-list-main';
            main.append(
                text('div', 'zazu-list-title', quote.reference || (serverId ? 'Quote #' + serverId : 'Draft quote')),
                text('div', 'zazu-list-meta', [
                    quote.event_name || job?.name || 'Work not recorded on this device',
                    String(quote.status || 'draft').replace(/_/g, ' '),
                    quote.currency || state?.business?.currency || 'ZAR',
                ].join(' · '))
            );
            identity.append(marker, main);

            const sync = document.createElement('div');
            sync.className = 'zazu-action-group';
            const syncStatus = localSyncStatus(state, 'quote', quote.local_id, serverId);
            const chip = text('span', 'zazu-chip zazu-chip-neutral', syncStatus.label);
            chip.title = syncStatus.title;
            chip.setAttribute('aria-label', syncStatus.label + '. ' + syncStatus.title);
            sync.append(chip);

            row.append(identity, sync);
            addQuoteOfflineDetails(row, quote);
            if (empty?.parentNode === directory) directory.insertBefore(row, empty);
            else directory.append(row);
        }

        if (empty) empty.hidden = quotes.length > 0;
    };

    const offlineCollectionDefinitions = {
        suppliers: {
            entity: 'supplier',
            title: record => record.name || 'Unnamed supplier',
            meta: record => [record.phone, record.email].filter(Boolean).join(' · ') || 'Contact details not recorded',
            fields: record => [
                ['Contact', record.contact_name], ['Phone', record.phone], ['Email', record.email],
                ['Address', record.address], ['Notes', record.notes],
            ],
        },
        catalogue: {
            entity: 'service',
            title: record => record.name || 'Unnamed service',
            meta: record => [record.capability_type, (record.is_active ?? record.active) === false ? 'Inactive' : 'Active'].filter(Boolean).join(' · '),
            fields: record => [
                ['Type', record.capability_type], ['Description', record.description],
                ['Availability', (record.is_active ?? record.active) === false ? 'Inactive' : 'Active'],
            ],
        },
        inventory_items: {
            entity: 'inventory_item',
            title: record => record.name || 'Inventory item',
            meta: record => [record.sku ? 'SKU '+record.sku : '', record.on_hand !== undefined ? String(record.on_hand)+' '+(record.unit || 'units')+' on hand' : ''].filter(Boolean).join(' · '),
            fields: record => [
                ['SKU', record.sku], ['On hand', record.on_hand !== undefined ? String(record.on_hand)+' '+(record.unit || 'units') : null],
                ['Reorder level', record.reorder_level], ['Movements', Array.isArray(record.movements) ? record.movements.length : null],
            ],
            children: record => [{
                label: 'Recent stock movements',
                records: (record.movements || []).slice(-8).reverse(),
                title: item => item.type || 'Stock movement',
                meta: item => [item.quantity, item.movement_date, item.reference].filter(Boolean).join(' · '),
            }],
        },
        assets: {
            entity: 'asset',
            title: record => record.name || record.asset_tag || 'Asset',
            meta: record => [record.asset_tag, record.status, record.condition].filter(Boolean).join(' · '),
            fields: record => [
                ['Asset tag', record.asset_tag], ['Status', record.status], ['Condition', record.condition],
                ['Location', record.location], ['Acquired', record.acquired_at],
                ['Purchase cost', record.purchase_cost !== undefined ? String(record.currency || 'ZAR')+' '+String(record.purchase_cost) : null],
            ],
            children: record => [{
                label: 'Current allocations',
                records: record.allocations || [],
                title: item => item.event_name || (item.event_id ? 'Work #'+item.event_id : 'Asset allocation'),
                meta: item => [item.allocated_from, item.allocated_until, item.status].filter(Boolean).join(' · '),
            }],
        },
        purchase_orders: {
            entity: 'purchase_order',
            title: record => record.reference || 'Purchase order',
            meta: record => [record.supplier_name, record.status, record.total_amount !== undefined ? String(record.currency || 'ZAR')+' '+String(record.total_amount) : ''].filter(Boolean).join(' · '),
            fields: record => [
                ['Supplier', record.supplier_name || (record.supplier_id ? 'Supplier #'+record.supplier_id : null)],
                ['Status', record.status], ['Expected', record.expected_at], ['Total', record.total_amount],
                ['Lines', Array.isArray(record.items) ? record.items.length : null], ['Notes', record.notes],
            ],
            children: record => [{
                label: 'Order lines',
                records: record.items || [],
                title: item => item.description || 'Order line',
                meta: item => [item.quantity ? String(item.quantity)+' '+(item.unit || '') : '', item.received_quantity !== undefined ? 'Received '+String(item.received_quantity) : '', item.line_total !== undefined ? String(record.currency || 'ZAR')+' '+String(item.line_total) : ''].filter(Boolean).join(' · '),
            }],
        },
        invoices: {
            entity: 'invoice',
            title: record => record.number || 'Invoice',
            meta: record => [record.status, record.total !== undefined ? String(record.currency || 'ZAR')+' '+String(record.total) : '', record.balance !== undefined ? 'Balance '+String(record.currency || 'ZAR')+' '+String(record.balance) : ''].filter(Boolean).join(' · '),
            fields: record => [
                ['Status', record.status], ['Issued', record.issued_at], ['Due', record.due_at],
                ['Total', record.total !== undefined ? String(record.currency || 'ZAR')+' '+String(record.total) : null],
                ['Paid', record.paid_amount !== undefined ? String(record.currency || 'ZAR')+' '+String(record.paid_amount) : null],
                ['Balance', record.balance !== undefined ? String(record.currency || 'ZAR')+' '+String(record.balance) : null],
            ],
            children: record => [
                { label: 'Invoice lines', records: record.items || [], title: item => item.description || 'Invoice line', meta: item => [item.quantity ? String(item.quantity)+' '+(item.unit || '') : '', item.line_total !== undefined ? String(record.currency || 'ZAR')+' '+String(item.line_total) : ''].filter(Boolean).join(' · ') },
                { label: 'Payments', records: record.payments || [], title: item => item.reference || item.method || 'Payment', meta: item => [item.amount !== undefined ? String(item.currency || record.currency || 'ZAR')+' '+String(item.amount) : '', item.paid_at].filter(Boolean).join(' · ') },
            ],
        },
        expenses: {
            entity: 'expense',
            title: record => record.description || record.reference || 'Expense',
            meta: record => [record.amount !== undefined ? String(record.currency || 'ZAR')+' '+String(record.amount) : '', record.expense_date, record.status].filter(Boolean).join(' · '),
            fields: record => [
                ['Reference', record.reference], ['Date', record.expense_date], ['Status', record.status],
                ['Amount', record.amount !== undefined ? String(record.currency || 'ZAR')+' '+String(record.amount) : null],
                ['Notes', record.notes],
            ],
        },
        costs: {
            entity: 'event_cost',
            title: record => record.description || record.category || 'Event cost',
            meta: record => [record.event_name, record.category, record.status].filter(Boolean).join(' · '),
            fields: record => [
                ['Work', record.event_name || (record.event_id ? 'Work #'+record.event_id : null)],
                ['Category', record.category], ['Status', record.status],
                ['Projected', record.projected_amount !== undefined ? String(record.currency || 'ZAR')+' '+String(record.projected_amount) : null],
                ['Actual', record.actual_amount !== undefined ? String(record.currency || 'ZAR')+' '+String(record.actual_amount) : null],
                ['Notes', record.notes],
            ],
        },
    };

    const renderOfflineCollections = (state) => {
        document.querySelectorAll('[data-zazu-offline-collection]').forEach(section => {
            const collection = section.dataset.zazuOfflineCollection;
            const definition = offlineCollectionDefinitions[collection];
            const directory = section.querySelector('[data-zazu-offline-collection-list]');
            if (!definition || !directory) return;

            directory.querySelectorAll('[data-zazu-local-record]').forEach(node => {
                const detail = node.nextElementSibling;
                if (detail?.matches('[data-zazu-offline-record-detail]')) detail.remove();
                node.remove();
            });

            const records = (state?.[collection] || []).filter(record => record?.local_id);
            const empty = directory.querySelector('.zazu-empty');
            for (const record of records) {
                const row = document.createElement('article');
                row.className = 'zazu-list-item';
                row.dataset.zazuLocalRecord = collection;
                row.dataset.zazuLocalId = record.local_id;

                const main = document.createElement('div');
                main.className = 'zazu-list-main min-w-0 flex-1';
                main.append(text('div', 'zazu-list-title', definition.title(record)));
                const meta = definition.meta(record);
                if (meta) main.append(text('div', 'zazu-list-meta', meta));

                const actions = document.createElement('div');
                actions.className = 'zazu-action-group';
                const syncStatus = localSyncStatus(state, definition.entity, record.local_id, record.server_id ?? record.id, record.pending_sync === true);
                const chip = text('span', 'zazu-chip zazu-chip-neutral', syncStatus.label);
                chip.title = syncStatus.title;
                chip.setAttribute('aria-label', syncStatus.label+'. '+syncStatus.title);
                actions.append(chip);

                const detailButton = document.createElement('button');
                detailButton.type = 'button';
                detailButton.className = 'zazu-btn zazu-btn-ghost';
                detailButton.textContent = 'View saved details';
                detailButton.setAttribute('aria-expanded', 'false');
                detailButton.addEventListener('click', () => {
                    let panel = row.nextElementSibling;
                    if (!panel?.matches('[data-zazu-offline-record-detail]')) {
                        panel = document.createElement('section');
                        panel.className = 'zazu-panel mt-2';
                        panel.dataset.zazuOfflineRecordDetail = '1';
                        panel.setAttribute('role', 'region');
                        panel.setAttribute('aria-label', definition.title(record)+' saved details');
                        row.insertAdjacentElement('afterend', panel);
                    }
                    const opening = panel.hidden || panel.childElementCount === 0;
                    panel.hidden = !opening;
                    detailButton.setAttribute('aria-expanded', opening ? 'true' : 'false');
                    if (!opening) return;

                    panel.replaceChildren();
                    const current = (window.ZazuOffline?.getState()?.[collection] || [])
                        .find(item => item.local_id === record.local_id) || record;
                    const fields = (definition.fields?.(current) || [])
                        .filter(([, value]) => value !== null && value !== undefined && String(value).trim() !== '');
                    if (fields.length) {
                        const details = document.createElement('div');
                        details.className = 'zazu-detail-rows';
                        for (const [label, value] of fields) {
                            const item = document.createElement('div');
                            item.className = 'zazu-detail-row';
                            item.append(text('div', 'zazu-detail-label', label), text('div', 'zazu-detail-value', value));
                            details.append(item);
                        }
                        panel.append(details);
                    }

                    for (const child of definition.children?.(current) || []) {
                        const group = document.createElement('section');
                        group.className = 'mt-3';
                        group.append(text('div', 'zazu-panel-title', child.label+' ('+child.records.length+')'));
                        const list = document.createElement('div');
                        list.className = 'zazu-list mt-2';
                        for (const item of child.records) {
                            const childRow = document.createElement('div');
                            childRow.className = 'zazu-list-item';
                            childRow.append(
                                text('div', 'zazu-list-title', child.title(item)),
                                text('div', 'zazu-list-meta', child.meta(item)),
                            );
                            list.append(childRow);
                        }
                        if (!child.records.length) list.append(text('p', 'zazu-panel-copy', 'No '+child.label.toLowerCase()+' stored on this device.'));
                        group.append(list);
                        panel.append(group);
                    }

                    panel.append(text('p', 'zazu-panel-copy mt-3', 'This is the last synchronised and locally saved version on this device. Reconnect to refresh it.'));
                });
                actions.append(detailButton);
                row.append(main, actions);
                if (empty?.parentNode === directory) directory.insertBefore(row, empty);
                else directory.append(row);
            }

            if (empty) empty.hidden = records.length > 0;
        });
    };

    const render = () => {
        const state = window.ZazuOffline.getState();
        renderCustomers(state);
        renderJobs(state);
        renderQuotes(state);
        renderOfflineCollections(state);
        refreshOfflineWorkspaceVisibility(state);
        refreshLocalAttachmentLists();
    };

    // One shared origin state prevents overlapping health probes from
    // racing and hiding the local workspace after a newer offline transition.
    let lastOriginAvailable = navigator.onLine;
    const refreshOfflineWorkspaceVisibility = (state) => {
        const workspace = document.querySelector('[data-zazu-offline-workspace]');
        if (!workspace?.isConnected) return lastOriginAvailable;

        const hasPendingRecords = ['customers', 'jobs', 'quotes'].some(collection =>
            (state?.[collection] || []).some(record =>
                record?.local_id && !(record.server_id ?? record.id)
            )
        );

        workspace.hidden = !hasPendingRecords && lastOriginAvailable;
        applyOfflineRouteView();

        return lastOriginAvailable;
    };

    const offlineRouteViews = [
        [/^\/customers(?:\/|$)/, 'customers'],
        [/^\/work\/[^/]+\/(?:quotes)(?:\/|$)/, 'quotes'],
        [/^\/work\/[^/]+\/(?:costs|travel)(?:\/|$)/, 'costs'],
        [/^\/work(?:\/|$)/, 'jobs'],
        [/^\/quotes(?:\/|$)/, 'quotes'],
        [/^\/suppliers(?:\/|$)/, 'suppliers'],
        [/^\/inventory(?:\/|$)/, 'inventory_items'],
        [/^\/assets(?:\/|$)/, 'assets'],
        [/^\/purchasing(?:\/|$)/, 'purchase_orders'],
        [/^\/finance\/expenses(?:\/|$)/, 'expenses'],
        [/^\/finance(?:\/|$)/, 'invoices'],
    ];

    const applyOfflineRouteView = () => {
        const workspace = document.querySelector('[data-zazu-offline-workspace]');
        if (!workspace) return;
        const route = offlineRouteViews.find(([pattern]) => pattern.test(window.location.pathname));
        const activeView = route?.[1] || null;
        workspace.querySelectorAll('[data-zazu-offline-view]').forEach(section => {
            section.hidden = Boolean(activeView && section.dataset.zazuOfflineView !== activeView);
        });
        document.querySelectorAll('main#main-content > .zazu-dash-hero, main#main-content > .zazu-dash-overview, main#main-content > .zazu-dash-grid')
            .forEach(section => { section.hidden = Boolean(activeView); });
        workspace.dataset.zazuOfflineActiveView = activeView || 'overview';
    };

    const setupOriginAvailabilityMonitor = () => {
        const main = document.querySelector('main#main-content');
        if (!main || main.querySelector('[data-zazu-origin-offline-notice]')) return;

        const notice = document.createElement('aside');
        notice.className = 'zazu-origin-offline-notice';
        notice.dataset.zazuOriginOfflineNotice = '1';
        notice.hidden = true;
        notice.setAttribute('role', 'status');
        notice.setAttribute('aria-live', 'polite');

        const message = document.createElement('div');
        message.append(
            text('strong', '', 'Zazu server is unavailable'),
            text('p', '', 'Your last saved records remain on this device. Only supported offline screens can be used until the server reconnects.')
        );
        const action = document.createElement('a');
        action.className = 'zazu-origin-offline-action';
        action.href = '/dashboard';
        action.textContent = 'Open offline workspace';
        notice.append(message, action);

        const topbar = main.querySelector(':scope > .zazu-topbar');
        if (topbar) topbar.insertAdjacentElement('afterend', notice);
        else main.prepend(notice);

        let checking = false;
        let refreshQueued = false;
        const refreshOriginAvailability = async () => {
            if (checking) {
                // Do not drop an offline/online transition that arrives while
                // the previous health request is still in flight.
                refreshQueued = true;
                return;
            }

            checking = true;
            try {
                lastOriginAvailable = navigator.onLine && await canReachZazuOrigin();
                notice.hidden = lastOriginAvailable;
                window.dispatchEvent(new CustomEvent('zazu:origin-availability', {
                    detail: { available: lastOriginAvailable },
                }));
                refreshOfflineWorkspaceVisibility(window.ZazuOffline.getState());
            } finally {
                checking = false;
                if (refreshQueued) {
                    refreshQueued = false;
                    refreshOriginAvailability();
                }
            }
        };

        window.addEventListener('online', refreshOriginAvailability);
        window.addEventListener('offline', () => {
            lastOriginAvailable = false;
            notice.hidden = false;
            window.dispatchEvent(new CustomEvent('zazu:origin-availability', {
                detail: { available: false },
            }));
            refreshOfflineWorkspaceVisibility(window.ZazuOffline.getState());
            refreshOriginAvailability();
        });
        window.addEventListener('focus', refreshOriginAvailability);
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') refreshOriginAvailability();
        });
        const monitor = window.setInterval(refreshOriginAvailability, 10000);
        window.addEventListener('pagehide', () => window.clearInterval(monitor), { once: true });
        refreshOriginAvailability();
    };

    setupOriginAvailabilityMonitor();
    window.ZazuOffline.ready.then(() => { applyOfflineRouteView(); render(); }).catch(() => {});
    window.addEventListener('zazu:offline-ready', render);
    window.addEventListener('popstate', applyOfflineRouteView);
    window.addEventListener('zazu:offline-state-updated', render);
    window.addEventListener('zazu:offline-attachments-updated', refreshLocalAttachmentLists);
    window.addEventListener('online', render);
    window.addEventListener('offline', render);
    window.addEventListener('focus', render);
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') render();
    });
}

function setupZazuOfflineCustomerOptions() {
    const select = document.querySelector('select[name="customer_id"]');
    if (!select || !window.ZazuOffline) return;

    window.ZazuOffline.ready.then(() => {
        const customers = window.ZazuOffline.getState()?.customers || [];

        for (const customer of customers) {
            const localId = customer.local_id;
            if (!localId) continue;

            const serverId = customer.server_id ?? customer.id;
            const value = serverId !== null && serverId !== undefined
                ? String(serverId)
                : 'local:' + localId;
            let option = [...select.options].find(item =>
                item.value === value || item.dataset.zazuLocalId === localId
            );

            if (!option) {
                option = new Option(customer.name || 'Saved offline customer', value);
                select.add(option);
            }

            option.dataset.zazuLocalId = localId;
            option.dataset.zazuOfflineCustomer = '1';
        }
    }).catch(() => {
        // A customer selector enhancement must never prevent ordinary Work entry.
    });
}

function setupZazuOfflineForms() {
    document.querySelectorAll('form[data-zazu-offline-entity]').forEach((form) => {
        if (form.dataset.zazuOfflineBound === '1') return;
        form.dataset.zazuOfflineBound = '1';

        const offlineFormSignature = () => JSON.stringify(
            [...new FormData(form).entries()].map(([name, value]) => [
                name,
                typeof File !== 'undefined' && value instanceof File
                    ? ['file', value.name, value.size, value.lastModified, value.type]
                    : String(value),
            ]),
        );

        const unlockChangedOfflineDraft = () => {
            if (form.dataset.zazuOfflineSaved !== '1') return;
            if (form.dataset.zazuOfflineSavedSignature === offlineFormSignature()) return;

            // Keep duplicate-submit protection until the user actually changes
            // the saved form. A changed draft may be queued as a new mutation.
            delete form.dataset.zazuOfflineSaved;
            delete form.dataset.zazuOfflineSavedSignature;
            delete form.dataset.zazuOfflineSubmitting;

            const submit = form.querySelector('button[type="submit"], button:not([type]), input[type="submit"]');
            if (!submit) return;

            submit.disabled = false;
            submit.removeAttribute('aria-busy');
            if (submit.dataset.zazuSubmitLabel) {
                submit.textContent = submit.dataset.zazuSubmitLabel;
                delete submit.dataset.zazuSubmitLabel;
            }
        };

        form.addEventListener('input', unlockChangedOfflineDraft);
        form.addEventListener('change', unlockChangedOfflineDraft);

        form.addEventListener('submit', async (event) => {
            if (!window.ZazuOffline) return;

            event.preventDefault();
            if (form.dataset.zazuOfflineSubmitting === '1') return;
            form.dataset.zazuOfflineSubmitting = '1';

            try {
                await window.ZazuOffline.ready;

                const selectedCustomerValue = form.querySelector('select[name="customer_id"]')?.value || '';
                const hasUnresolvedLocalCustomer = selectedCustomerValue.startsWith('local:');

                if (navigator.onLine && !hasUnresolvedLocalCustomer && await canReachZazuOrigin()) {
                    delete form.dataset.zazuOfflineSubmitting;
                    // Preserve the browser's normal Laravel form/redirect flow
                    // after the origin has been confirmed reachable. A phone-local
                    // customer must stay queued until its server identity is known.
                    HTMLFormElement.prototype.submit.call(form);
                    return;
                }

            const entity = form.dataset.zazuOfflineEntity;
            const formData = new FormData(form);
            const get = (name) => String(formData.get(name) || '').trim();
            const localId = crypto.randomUUID();
            const getInputValue = (input) => String(input?.value || '').trim();
            const releaseSubmitLock = () => {
                const submit = form.querySelector('button[type="submit"], button:not([type]), input[type="submit"]');
                if (submit) {
                    submit.disabled = false;
                    submit.removeAttribute('aria-busy');
                    if (submit.dataset.zazuSubmitLabel) {
                        submit.textContent = submit.dataset.zazuSubmitLabel;
                        delete submit.dataset.zazuSubmitLabel;
                    }
                }
                delete form.dataset.zazuSubmitting;
            };

            let payload;
            let scheduleConflictNames = [];
            if (entity === 'customer') {
                const offlineState = window.ZazuOffline.getState();
                const serverId = form.dataset.zazuOfflineServerId;
                const existing = serverId
                    ? offlineState?.customers?.find(item => Number(item.id ?? item.server_id) === Number(serverId))
                    : null;

                if (serverId && !existing?.local_id) {
                    releaseSubmitLock();
                    window.alert('This customer is not available in the local Zazu copy yet. Reconnect and refresh the customer record before editing it offline.');
                    return;
                }

                const profilePhoto = formData.get('profile_photo');
                if ((profilePhoto instanceof File && profilePhoto.size > 0) || get('remove_profile_photo') === '1') {
                    releaseSubmitLock();
                    window.alert('Customer profile photo changes need a connection. Save the customer details first, then update the photo when Zazu is online.');
                    return;
                }

                for (const period of ['day', 'night']) {
                    const contactName = get(period + '_contact_name');
                    const contactPhone = get(period + '_contact_phone');
                    const contactEmail = get(period + '_contact_email');
                    if ((contactPhone || contactEmail) && !contactName) {
                        releaseSubmitLock();
                        window.alert('Enter a ' + period + ' contact name before saving its phone number or email.');
                        return;
                    }
                }

                payload = {
                    local_id: existing?.local_id || localId,
                    server_id: existing?.server_id ?? (serverId ? Number(serverId) : null),
                    name: get('name'),
                    contact_name: get('primary_contact_name') || get('name'),
                    legal_name: get('legal_name') || null,
                    registration_number: get('registration_number') || null,
                    tax_number: get('tax_number') || null,
                    vat_number: get('vat_number') || null,
                    billing_address: get('billing_address') || null,
                    notes: get('notes') || null,
                    phone: get('primary_contact_phone') || get('phone') || null,
                    email: get('primary_contact_email') || get('email') || null,
                    day_contact_name: get('day_contact_name') || null,
                    day_contact_phone: get('day_contact_phone') || null,
                    day_contact_email: get('day_contact_email') || null,
                    night_contact_name: get('night_contact_name') || null,
                    night_contact_phone: get('night_contact_phone') || null,
                    night_contact_email: get('night_contact_email') || null,
                };
                payload.__operation = existing?.local_id ? 'update' : 'create';
            } else if (entity === 'job') {
                const customerOption = form.querySelector('[name="customer_id"]')?.selectedOptions?.[0];
                const customerValue = customerOption?.value || '';
                const offlineState = window.ZazuOffline.getState();
                const customer = customerValue
                    ? offlineState?.customers?.find(item =>
                        item.local_id === customerOption?.dataset.zazuLocalId
                        || String(item.id ?? item.server_id ?? '') === customerValue
                        || ('local:' + item.local_id) === customerValue
                    )
                    : null;

                if (customerValue && !customer?.local_id) {
                    window.alert('This customer is not available in the local Zazu copy yet. Reconnect and refresh the customer list before creating this job.');
                    return;
                }

                payload = {
                    local_id: localId,
                    server_id: null,
                    name: get('name'),
                    customer_local_id: customer?.local_id || null,
                    customer_name: customer?.name || (customerOption && customerValue ? customerOption.textContent.trim() : (get('customer_name') || null)),
                    event_type: get('event_type'),
                    event_date: get('event_date') || null,
                    event_end_date: get('event_end_date') || null,
                    event_start_time: get('event_start_time') || null,
                    event_end_time: get('event_end_time') || null,
                    setup_date: get('setup_date') || null,
                    collection_date: get('collection_date') || null,
                    event_address: get('event_address') || null,
                    event_day_contact_id: get('event_day_contact_id') || null,
                    event_night_contact_id: get('event_night_contact_id') || null,
                    services: formData.getAll('services[]').map(value => String(value).trim()).filter(Boolean),
                    other_service: get('other_service') || null,
                    notes: get('notes') || null,
                };

                const scheduleWindow = (record) => {
                    const startDate = record.setup_date || record.event_date;
                    const endDate = record.collection_date || record.event_end_date || record.event_date;
                    if (!startDate || !endDate) return null;
                    const hasTimeRange = Boolean(record.event_start_time && record.event_end_time);
                    const startTime = record.setup_date || !hasTimeRange ? '00:00' : String(record.event_start_time).slice(0, 5);
                    const endTime = record.collection_date || !hasTimeRange ? '23:59' : String(record.event_end_time).slice(0, 5);
                    const start = new Date(startDate + 'T' + startTime + ':00').getTime();
                    const end = new Date(endDate + 'T' + endTime + ':00').getTime();
                    return Number.isFinite(start) && Number.isFinite(end) ? { start, end } : null;
                };
                const newWindow = scheduleWindow(payload);
                if (newWindow) {
                    scheduleConflictNames = (offlineState?.jobs || [])
                        .filter(job => !['completed', 'cancelled'].includes(job.status))
                        .filter(job => {
                            const existingWindow = scheduleWindow(job);
                            return existingWindow
                                && newWindow.start < existingWindow.end
                                && existingWindow.start < newWindow.end;
                        })
                        .map(job => job.name)
                        .filter(Boolean);
                }
            } else if (entity === 'quote') {
                const state = window.ZazuOffline.getState();
                const serverJobId = form.dataset.zazuOfflineJobId;
                const job = state?.jobs?.find(item => Number(item.id) === Number(serverJobId));
                if (!job?.local_id) {
                    window.alert('This job is not available in this device copy yet.');
                    return;
                }

                const items = [...form.querySelectorAll('[name^="unit_price["]')].map((input) => {
                    const row = input.closest('.zazu-list-item');
                    const description = row?.querySelector('.zazu-list-title')?.textContent?.trim() || 'Quote line';
                    const meta = row?.querySelector('.zazu-list-meta')?.textContent?.trim() || '';
                    const quantity = Number.parseFloat(meta.match(/^[0-9]+(?:\\.[0-9]+)?/)?.[0] || '1');

                    return {
                        description,
                        quantity: Number.isFinite(quantity) ? quantity : 1,
                        unit: null,
                        unit_price: getInputValue(input),
                    };
                }).filter(item => item.unit_price !== '');

                payload = {
                    local_id: localId,
                    server_id: null,
                    event_local_id: job.local_id,
                    reference: null,
                    status: 'draft',
                    currency: get('currency') || state.business?.currency || 'ZAR',
                    deposit_percent: get('deposit_percent') || '0',
                    notes: get('notes') || null,
                    items,
                };
            } else if (entity === 'quote_acceptance') {
                const state = window.ZazuOffline.getState();
                const serverQuoteId = form.dataset.zazuOfflineQuoteId;
                const quote = state?.quotes?.find(item => Number(item.id ?? item.server_id) === Number(serverQuoteId));
                if (!quote?.local_id || quote.status !== 'sent') {
                    window.alert('This quote is not available as a sent quote in this device copy yet.');
                    return;
                }
                payload = {
                    local_id: localId,
                    server_id: null,
                    quote_local_id: quote.local_id,
                    customer_name: form.dataset.zazuOfflineCustomerName || 'Customer',
                };
            } else if (entity === 'supplier') {
                const state = window.ZazuOffline.getState();
                const serverId = form.dataset.zazuOfflineServerId;
                const existing = serverId
                    ? state?.suppliers?.find(item => Number(item.id ?? item.server_id) === Number(serverId))
                    : null;
                payload = {
                    local_id: existing?.local_id || localId,
                    server_id: existing?.server_id ?? (serverId ? Number(serverId) : null),
                    name: get('name'),
                    contact_name: get('contact_name') || null,
                    email: get('email') || null,
                    phone: get('phone') || null,
                    notes: get('notes') || null,
                };
                payload.__operation = existing?.local_id ? 'update' : 'create';
            } else if (entity === 'inventory_item') {
                const state = window.ZazuOffline.getState();
                const capabilityId = get('capability_id');
                const capability = capabilityId
                    ? state?.catalogue?.find(item => Number(item.id ?? item.server_id) === Number(capabilityId))
                    : null;
                payload = {
                    local_id: localId,
                    server_id: null,
                    name: get('name'),
                    sku: get('sku') || null,
                    unit: get('unit') || 'unit',
                    reorder_level: get('reorder_level') || '0',
                    capability_local_id: capability?.local_id || null,
                };
            } else if (entity === 'asset') {
                const state = window.ZazuOffline.getState();
                const serverId = form.dataset.zazuOfflineServerId;
                const existing = serverId
                    ? state?.assets?.find(item => Number(item.id ?? item.server_id) === Number(serverId))
                    : null;
                const capabilityId = get('capability_id');
                const capability = capabilityId
                    ? state?.catalogue?.find(item => Number(item.id ?? item.server_id) === Number(capabilityId))
                    : null;
                payload = {
                    local_id: existing?.local_id || localId,
                    server_id: existing?.server_id ?? (serverId ? Number(serverId) : null),
                    asset_tag: get('asset_tag'),
                    name: get('name'),
                    condition: get('condition') || 'good',
                    location: get('location') || null,
                    acquired_at: get('acquired_at') || null,
                    purchase_cost: get('purchase_cost') || '0',
                    capability_local_id: capability?.local_id || null,
                    notes: get('notes') || null,
                };
                payload.__operation = existing?.local_id ? 'update' : 'create';
            } else if (entity === 'expense') {
                const state = window.ZazuOffline.getState();
                const supplierId = get('supplier_id');
                const supplier = supplierId ? state?.suppliers?.find(item => Number(item.id ?? item.server_id) === Number(supplierId)) : null;
                const eventId = get('event_id');
                const event = eventId ? state?.jobs?.find(item => Number(item.id ?? item.server_id) === Number(eventId)) : null;
                const purchaseOrderId = get('purchase_order_id');
                const purchaseOrder = purchaseOrderId ? state?.purchase_orders?.find(item => Number(item.id ?? item.server_id) === Number(purchaseOrderId)) : null;
                if (supplierId && !supplier?.local_id) {
                    window.alert('This supplier is not available in this device copy yet.');
                    return;
                }
                if (eventId && !event?.local_id) {
                    window.alert('This job is not available in this device copy yet.');
                    return;
                }
                if (purchaseOrderId && !purchaseOrder?.local_id) {
                    window.alert('This purchase order is not available in this device copy yet.');
                    return;
                }
                payload = {
                    local_id: localId,
                    server_id: null,
                    description: get('description'),
                    amount: get('amount'),
                    status: get('status') || 'unpaid',
                    reference: get('reference') || null,
                    expense_date: get('expense_date') || null,
                    notes: get('notes') || null,
                    supplier_local_id: supplier?.local_id || null,
                    event_local_id: event?.local_id || null,
                    purchase_order_local_id: purchaseOrder?.local_id || null,
                };
            } else if (entity === 'payment') {
                const state = window.ZazuOffline.getState();
                const invoiceId = get('invoice_id');
                const invoice = invoiceId ? state?.invoices?.find(item => Number(item.id ?? item.server_id) === Number(invoiceId)) : null;
                if (!invoice?.local_id) {
                    window.alert('This invoice is not available in this device copy yet.');
                    return;
                }
                if (invoice.status !== 'issued') {
                    window.alert('Only issued invoices can accept payments.');
                    return;
                }

                const toCents = value => {
                    const amount = Number.parseFloat(String(value ?? ''));
                    return Number.isFinite(amount) ? Math.round(amount * 100) : NaN;
                };
                const amountCents = toCents(get('amount'));
                const totalCents = toCents(invoice.total);
                const paidCents = (invoice.payments || []).reduce(
                    (sum, payment) => sum + (Number.isFinite(toCents(payment.amount)) ? toCents(payment.amount) : 0),
                    0,
                );
                if (!Number.isSafeInteger(amountCents) || amountCents <= 0 || !Number.isSafeInteger(totalCents)) {
                    window.alert('Enter a valid payment amount.');
                    return;
                }
                if (amountCents > Math.max(0, totalCents - paidCents)) {
                    window.alert('Payment cannot exceed the outstanding invoice balance.');
                    return;
                }

                const paymentType = get('type') || 'payment';
                if (paymentType === 'deposit') {
                    const requiredDepositCents = toCents(invoice.deposit_required);
                    const paidDepositCents = (invoice.payments || [])
                        .filter(payment => payment.type === 'deposit')
                        .reduce((sum, payment) => sum + (Number.isFinite(toCents(payment.amount)) ? toCents(payment.amount) : 0), 0);
                    if (!Number.isSafeInteger(requiredDepositCents) || requiredDepositCents <= 0) {
                        window.alert('This invoice has no deposit requirement.');
                        return;
                    }
                    if (paidDepositCents + amountCents > requiredDepositCents) {
                        window.alert('This deposit would exceed the required deposit amount.');
                        return;
                    }
                }

                payload = {
                    local_id: localId,
                    server_id: null,
                    invoice_local_id: invoice.local_id,
                    type: paymentType,
                    amount: (amountCents / 100).toFixed(2),
                    method: get('method') || null,
                    reference: get('reference') || null,
                    paid_at: get('paid_at') || null,
                    notes: get('notes') || null,
                };
            } else if (entity === 'preparation') {
                const state = window.ZazuOffline.getState();
                const serverJobId = form.dataset.zazuOfflineJobId;
                const job = state?.jobs?.find(item => Number(item.id ?? item.server_id) === Number(serverJobId));
                if (!job?.local_id) {
                    window.alert('This job is not available in this device copy yet.');
                    return;
                }
                payload = {
                    local_id: localId,
                    server_id: null,
                    event_local_id: job.local_id,
                    title: get('title'),
                    category: get('category') || null,
                    quantity: get('quantity') || null,
                    unit: get('unit') || null,
                    due_date: get('due_date') || null,
                    status: get('status') || 'open',
                    notes: get('notes') || null,
                };
            } else if (entity === 'event_cost') {
                const state = window.ZazuOffline.getState();
                const serverJobId = form.dataset.zazuOfflineJobId;
                const job = state?.jobs?.find(item => Number(item.id ?? item.server_id) === Number(serverJobId));
                if (!job?.local_id) {
                    window.alert('This job is not available in this device copy yet.');
                    return;
                }
                payload = {
                    local_id: localId,
                    server_id: null,
                    event_local_id: job.local_id,
                    category: get('category'),
                    description: get('description'),
                    currency: get('currency') || state.business?.currency || 'ZAR',
                    projected_amount: get('projected_amount'),
                    actual_amount: get('actual_amount') || null,
                    status: get('status') || 'planned',
                    notes: get('notes') || null,
                    pending_sync: true,
                };
            } else if (entity === 'service') {
                const state = window.ZazuOffline.getState();
                const image = formData.get('image');
                if (image instanceof File && image.size > 0) {
                    window.alert('Service images need a connection. Save the service details first, then add the image while Zazu is online.');
                    return;
                }
                const serverId = form.dataset.zazuOfflineServerId;
                const existing = serverId
                    ? state?.catalogue?.find(item => Number(item.id ?? item.server_id) === Number(serverId))
                    : null;
                payload = {
                    local_id: existing?.local_id || localId,
                    server_id: existing?.server_id ?? (serverId ? Number(serverId) : null),
                    name: get('name'),
                    capability_type: get('capability_type') || existing?.capability_type || 'service',
                    pricing_basis: get('pricing_basis') || existing?.pricing_basis || 'custom',
                    default_price: get('default_price') || existing?.default_price || null,
                    currency: get('currency') || existing?.currency || state.business?.currency || 'ZAR',
                    default_unit: get('default_unit') || existing?.default_unit || null,
                    description: get('description') || existing?.description || null,
                    category: form.querySelector('input[name="category"]:checked')?.value || get('category') || existing?.category || null,
                    active: form.querySelector('input[name="is_active"]')?.checked !== false,
                };
                payload.__operation = existing?.local_id ? 'update' : 'create';
            } else if (entity === 'inventory_movement') {
                const state = window.ZazuOffline.getState();
                const itemServerId = form.dataset.zazuOfflineItemId;
                const item = state?.inventory_items?.find(record => Number(record.id ?? record.server_id) === Number(itemServerId));
                if (!item?.local_id) {
                    window.alert('This inventory item is not available in this device copy yet.');
                    return;
                }
                const eventServerId = get('event_id');
                const event = eventServerId ? state?.jobs?.find(record => Number(record.id ?? record.server_id) === Number(eventServerId)) : null;
                if (eventServerId && !event?.local_id) {
                    window.alert('This job is not available in this device copy yet.');
                    return;
                }
                payload = {
                    local_id: localId,
                    server_id: null,
                    inventory_item_local_id: item.local_id,
                    type: get('type'),
                    quantity: get('quantity'),
                    unit_cost: get('unit_cost'),
                    movement_date: get('movement_date'),
                    reference: get('reference') || null,
                    notes: get('notes') || null,
                    event_local_id: event?.local_id || null,
                };
            } else if (entity === 'purchase_receipt') {
                const state = window.ZazuOffline.getState();
                const serverOrderId = form.dataset.zazuOfflinePurchaseOrderId;
                const order = state?.purchase_orders?.find(item => Number(item.id ?? item.server_id) === Number(serverOrderId));
                if (!order?.local_id) {
                    window.alert('This purchase order is not available in this device copy yet.');
                    return;
                }
                if (order.status !== 'ordered') {
                    window.alert('This purchase order is not open for receiving in this device copy.');
                    return;
                }

                const toHundredths = (value) => {
                    const amount = Number.parseFloat(String(value ?? 0));
                    return Number.isFinite(amount) ? Math.round(amount * 100) : 0;
                };
                const receivedQuantities = {};
                for (const input of form.querySelectorAll('[name^="received_quantity["]')) {
                    const quantity = Number.parseFloat(String(input.value || '').trim());
                    if (!Number.isFinite(quantity) || quantity <= 0) continue;
                    const match = input.name.match(/^received_quantity\[(\d+)\]$/);
                    const item = match ? order.items?.find(line => Number(line.id ?? line.server_id) === Number(match[1])) : null;
                    if (!item?.local_id) {
                        window.alert('A purchase order line is not available in this device copy yet.');
                        return;
                    }

                    const remaining = toHundredths(item.quantity) - toHundredths(item.received_quantity);
                    const incoming = toHundredths(quantity);
                    if (incoming > remaining) {
                        window.alert('This receipt exceeds the remaining ordered quantity for ' + (item.description || 'this line') + '.');
                        return;
                    }
                    receivedQuantities[item.local_id] = (incoming / 100).toFixed(2);
                }
                if (!Object.keys(receivedQuantities).length) {
                    window.alert('Enter at least one received quantity before saving.');
                    return;
                }

                payload = {
                    local_id: localId,
                    server_id: null,
                    purchase_order_local_id: order.local_id,
                    received_quantities: receivedQuantities,
                };
            } else if (entity === 'invoice') {
                const state = window.ZazuOffline.getState();
                const quoteId = get('quote_id');
                const quote = quoteId
                    ? state?.quotes?.find(item => Number(item.id ?? item.server_id) === Number(quoteId))
                    : null;
                if (!quote?.local_id || quote.status !== 'accepted') {
                    window.alert('Offline invoices require an accepted quote available in this device copy.');
                    return;
                }
                payload = {
                    local_id: localId,
                    server_id: null,
                    quote_local_id: quote.local_id,
                    issued_at: get('issued_at') || null,
                    due_at: get('due_at') || null,
                };
            } else if (entity === 'asset_allocation') {
                const state = window.ZazuOffline.getState();
                const serverAssetId = form.dataset.zazuOfflineAssetId;
                const asset = state?.assets?.find(item => Number(item.id ?? item.server_id) === Number(serverAssetId));
                if (!asset?.local_id) {
                    window.alert('This asset is not available in this device copy yet.');
                    return;
                }

                const allocationLocalId = form.dataset.zazuOfflineAllocationId
                    ? asset.allocations?.find(item => Number(item.id ?? item.server_id) === Number(form.dataset.zazuOfflineAllocationId))?.local_id
                    : null;

                if (form.dataset.zazuOfflineAllocationId && !allocationLocalId) {
                    window.alert('This asset allocation is not available in this device copy yet.');
                    return;
                }

                if (allocationLocalId) {
                    payload = {
                        local_id: allocationLocalId,
                        server_id: Number(form.dataset.zazuOfflineAllocationId),
                        asset_local_id: asset.local_id,
                        status: 'returned',
                    };
                    payload.__operation = 'update';
                } else {
                    const eventId = get('event_id');
                    const event = eventId
                        ? state?.jobs?.find(item => Number(item.id ?? item.server_id) === Number(eventId))
                        : null;
                    if (!event?.local_id) {
                        window.alert('This job is not available in this device copy yet.');
                        return;
                    }
                    payload = {
                        local_id: localId,
                        server_id: null,
                        asset_local_id: asset.local_id,
                        event_local_id: event.local_id,
                        allocated_from: get('allocated_from') || null,
                        allocated_until: get('allocated_until') || null,
                        notes: get('notes') || null,
                    };
                }
            } else if (entity === 'purchase_order') {
                const state = window.ZazuOffline.getState();
                const supplierId = get('supplier_id');
                const supplier = state?.suppliers?.find(item => Number(item.id ?? item.server_id) === Number(supplierId));
                if (!supplier?.local_id) {
                    window.alert('This supplier is not available in this device copy yet.');
                    return;
                }
                const eventId = get('event_id');
                const event = eventId
                    ? state?.jobs?.find(item => Number(item.id ?? item.server_id) === Number(eventId))
                    : null;
                if (eventId && !event?.local_id) {
                    window.alert('This job is not available in this device copy yet.');
                    return;
                }
                const descriptions = formData.getAll('description[]').map(value => String(value).trim());
                const quantities = formData.getAll('quantity[]');
                const units = formData.getAll('unit[]');
                const prices = formData.getAll('unit_price[]');
                const capabilities = formData.getAll('capability_id[]');
                const lines = descriptions.map((description, index) => {
                    const capability = state?.catalogue?.find(item => Number(item.id ?? item.server_id) === Number(capabilities[index] || 0));
                    return {
                        local_id: crypto.randomUUID(),
                        description,
                        quantity: String(quantities[index] ?? '').trim(),
                        unit: String(units[index] ?? 'unit').trim() || 'unit',
                        unit_price: String(prices[index] ?? '').trim(),
                        capability_id: capability?.server_id ?? (capabilities[index] ? Number(capabilities[index]) : null),
                    };
                }).filter(line => line.description && line.quantity && line.unit_price !== '');
                if (!lines.length) {
                    window.alert('Add at least one purchase order line before saving.');
                    return;
                }
                payload = {
                    local_id: localId,
                    server_id: null,
                    supplier_local_id: supplier.local_id,
                    event_local_id: event?.local_id || null,
                    currency: get('currency') || state.business?.currency || 'ZAR',
                    expected_at: get('expected_at') || null,
                    notes: get('notes') || null,
                    status: 'draft',
                    lines,
                };
            } else {
                return;
            }

            if (entity === 'quote' && !payload.items.length) {
                window.alert('Add at least one quote line before saving.');
                return;
            }

            const entitiesRequiringName = ['customer', 'job', 'supplier', 'inventory_item', 'asset', 'service'];
            if (entitiesRequiringName.includes(entity) && !payload.name) return;

            const operation = payload.__operation || 'create';
            delete payload.__operation;

            // Stage the optimistic working copy separately, then commit it in
            // the same IndexedDB transaction as its queued mutation.
            const state = JSON.parse(JSON.stringify(window.ZazuOffline.getState()));

            if (entity === 'quote_acceptance') {
                const quote = state.quotes?.find(item => item.local_id === payload.quote_local_id);
                if (quote) {
                    quote.status = 'accepted';
                    if (quote.latest_version) quote.latest_version.status = 'accepted';
                    if (quote.latestVersion) quote.latestVersion.status = 'accepted';
                    const linkedWork = state.jobs?.find(item => item.local_id === quote.event_local_id);
                    if (linkedWork?.status === 'draft') linkedWork.status = 'confirmed';
                }
            }

            if (entity === 'payment') {
                const invoice = state.invoices?.find(item => item.local_id === payload.invoice_local_id);
                if (invoice) {
                    invoice.payments = invoice.payments || [];
                    invoice.payments.push({
                        local_id: payload.local_id,
                        server_id: null,
                        id: null,
                        invoice_id: invoice.id,
                        type: payload.type,
                        amount: payload.amount,
                        currency: invoice.currency,
                        method: payload.method,
                        reference: payload.reference,
                        paid_at: payload.paid_at,
                        notes: payload.notes || null,
                        pending_sync: true,
                    });

                    const toCents = value => {
                        const amount = Number.parseFloat(String(value ?? ''));
                        return Number.isFinite(amount) ? Math.round(amount * 100) : 0;
                    };
                    const paidCents = invoice.payments.reduce(
                        (sum, payment) => sum + toCents(payment.amount),
                        0,
                    );
                    const totalCents = toCents(invoice.total);
                    invoice.paid_amount = (paidCents / 100).toFixed(2);
                    const nextBalanceCents = Math.max(0, totalCents - paidCents);
                    invoice.balance = (nextBalanceCents / 100).toFixed(2);
                    if (nextBalanceCents === 0) invoice.status = 'paid';

                    if (payload.type === 'deposit') {
                        const depositPaidCents = invoice.payments
                            .filter(payment => payment.type === 'deposit')
                            .reduce((sum, payment) => sum + toCents(payment.amount), 0);
                        const requiredDepositCents = toCents(invoice.deposit_required);
                        invoice.deposit_paid_amount = (depositPaidCents / 100).toFixed(2);
                        invoice.deposit_balance = (Math.max(0, requiredDepositCents - depositPaidCents) / 100).toFixed(2);
                    }
                }
            }

            if (entity === 'inventory_movement') {
                const inventoryItem = state.inventory_items?.find(item =>
                    item.local_id === payload.inventory_item_local_id
                );

                if (inventoryItem) {
                    const quantity = Number.parseFloat(String(payload.quantity ?? ''));
                    const currentOnHand = Number.parseFloat(String(inventoryItem.on_hand ?? 0));
                    if (Number.isFinite(quantity) && quantity > 0 && Number.isFinite(currentOnHand)) {
                        const increasesStock = ['receipt', 'return', 'adjustment_in'].includes(payload.type);
                        const nextOnHand = currentOnHand + (increasesStock ? quantity : -quantity);
                        inventoryItem.on_hand = Number(nextOnHand.toFixed(2));
                    }

                    const event = state.jobs?.find(item => item.local_id === payload.event_local_id);
                    inventoryItem.movements = inventoryItem.movements || [];
                    inventoryItem.movements.push({
                        local_id: payload.local_id,
                        server_id: null,
                        id: null,
                        inventory_item_id: inventoryItem.server_id ?? inventoryItem.id ?? null,
                        event_id: event?.server_id ?? event?.id ?? null,
                        type: payload.type,
                        quantity: payload.quantity,
                        unit_cost: payload.unit_cost,
                        movement_date: payload.movement_date,
                        reference: payload.reference || null,
                        notes: payload.notes || null,
                        pending_sync: true,
                    });
                }
            }

            if (entity === 'purchase_receipt') {
                const order = state.purchase_orders?.find(item => item.local_id === payload.purchase_order_local_id);
                const toHundredths = (value) => {
                    const amount = Number.parseFloat(String(value ?? 0));
                    return Number.isFinite(amount) ? Math.round(amount * 100) : 0;
                };
                if (order?.items) {
                    for (const [lineLocalId, quantity] of Object.entries(payload.received_quantities)) {
                        const line = order.items.find(item => item.local_id === lineLocalId);
                        if (!line) continue;
                        const receivedHundredths = toHundredths(line.received_quantity)
                            + toHundredths(quantity);
                        line.received_quantity = (receivedHundredths / 100).toFixed(2);
                        line.pending_sync = true;
                    }
                    const complete = order.items.every(item =>
                        toHundredths(item.received_quantity) >= toHundredths(item.quantity)
                    );
                    if (complete) order.status = 'received';
                    order.pending_sync = true;

                    for (const [lineLocalId, quantityValue] of Object.entries(payload.received_quantities)) {
                        const line = order.items.find(item => item.local_id === lineLocalId);
                        if (!line) continue;

                        const quantityHundredths = toHundredths(quantityValue);
                        if (quantityHundredths <= 0) continue;

                        const description = String(line.description || '').trim().toLowerCase();
                        const capabilityId = line.capability_id == null ? null : Number(line.capability_id);
                        const inventoryMatchesServer = (item) => {
                            const sameCapability = capabilityId === null
                                || Number(item.capability_id ?? 0) === capabilityId;
                            const sameDescription = String(item.name || '').trim().toLowerCase() === description
                                || String(item.sku || '').trim().toLowerCase() === description;
                            return sameCapability && sameDescription;
                        };
                        let inventoryItem = state.inventory_items?.find(inventoryMatchesServer);

                        if (!inventoryItem) {
                            inventoryItem = {
                                local_id: crypto.randomUUID(),
                                server_id: null,
                                id: null,
                                capability_id: line.capability_id ?? null,
                                name: line.description,
                                unit: line.unit || 'unit',
                                reorder_level: '0',
                                on_hand: 0,
                                pending_sync: true,
                                movements: [],
                            };
                            state.inventory_items = state.inventory_items || [];
                            state.inventory_items.push(inventoryItem);
                        }

                        const onHandHundredths = toHundredths(inventoryItem.on_hand);
                        inventoryItem.on_hand = Number(((onHandHundredths + quantityHundredths) / 100).toFixed(2));
                        inventoryItem.pending_sync = true;
                        inventoryItem.movements = inventoryItem.movements || [];
                        inventoryItem.movements.push({
                            local_id: crypto.randomUUID(),
                            server_id: null,
                            id: null,
                            inventory_item_id: inventoryItem.id,
                            purchase_order_id: order.id,
                            purchase_order_item_id: line.id,
                            type: 'receipt',
                            quantity: (quantityHundredths / 100).toFixed(2),
                            unit_cost: line.unit_price,
                            movement_date: new Date().toISOString().slice(0, 10),
                            reference: order.reference,
                            notes: 'Receipt from purchase order.',
                            pending_sync: true,
                        });
                    }
                }
            }

            if (entity === 'asset_allocation') {
                const asset = state.assets?.find(item => Number(item.id ?? item.server_id) === Number(payload.server_id || form.dataset.zazuOfflineAssetId));
                if (asset) {
                    if (operation === 'update') {
                        const allocation = asset.allocations?.find(item => item.local_id === payload.local_id);
                        if (allocation) allocation.status = 'returned';
                        asset.status = asset.allocations?.some(item => item.status === 'allocated')
                            ? 'allocated'
                            : 'available';
                    } else {
                        const event = state.jobs?.find(item => item.local_id === payload.event_local_id);
                        asset.status = 'allocated';
                        asset.allocations = asset.allocations || [];
                        asset.allocations.push({
                            local_id: payload.local_id,
                            server_id: null,
                            asset_id: asset.id,
                            event_id: event?.id ?? null,
                            allocated_from: payload.allocated_from,
                            allocated_until: payload.allocated_until,
                            status: 'allocated',
                            notes: payload.notes || null,
                        });
                    }
                }
            }
            const collections = {
                customer: state.customers,
                job: state.jobs,
                quote: state.quotes,
                supplier: state.suppliers,
                inventory_item: state.inventory_items,
                purchase_order: state.purchase_orders,
                asset: state.assets,
                invoice: state.invoices,
                expense: state.expenses,
                preparation: state.preparations,
                event_cost: state.costs,
                service: state.catalogue,
            };
            const collection = collections[entity];
            if (collection) {
                const existingIndex = collection.findIndex(item => payload.local_id && item.local_id === payload.local_id);
                if (existingIndex >= 0) collection[existingIndex] = { ...collection[existingIndex], ...payload };
                else collection.push(payload);
            }
            state.local_dirty = true;
            await window.ZazuOffline.queueMutation(entity, operation, payload, state);
            window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
            form.dataset.zazuOfflineSavedSignature = offlineFormSignature();
            form.dataset.zazuOfflineSaved = '1';

            const submit = form.querySelector('button[type="submit"], button:not([type]), input[type="submit"]');
            if (submit) {
                if (!submit.dataset.zazuSubmitLabel) {
                    submit.dataset.zazuSubmitLabel = submit.textContent || submit.value || '';
                }
                submit.disabled = true;
                submit.textContent = 'Saved locally';
            }

            let notice = form.querySelector('[data-zazu-offline-save-notice]');
            if (!notice) {
                notice = document.createElement('div');
                notice.className = 'zazu-notice';
                notice.setAttribute('role', 'status');
                notice.dataset.zazuOfflineSaveNotice = '1';
                form.prepend(notice);
            }
            notice.textContent = scheduleConflictNames.length
                ? 'Saved on this device. Schedule overlap with: ' + scheduleConflictNames.join(', ') + '. Review the calendar before confirming this job. It will sync when Zazu reconnects.'
                : 'Saved on this device. It will sync automatically when Zazu reconnects.';
            } catch (error) {
                // IndexedDB quota, blocked upgrades, and transaction failures
                // must not leave the submit button stuck or silently lose the user's work.
                const submit = form.querySelector('button[type="submit"], button:not([type]), input[type="submit"]');
                if (submit) {
                    submit.disabled = false;
                    submit.removeAttribute('aria-busy');
                    delete form.dataset.zazuSubmitting;
                    if (submit.dataset.zazuSubmitLabel) {
                        submit.textContent = submit.dataset.zazuSubmitLabel;
                        delete submit.dataset.zazuSubmitLabel;
                    }
                }

                const existingNotice = form.querySelector('[data-zazu-offline-save-error]');
                if (existingNotice) existingNotice.remove();
                const notice = document.createElement('div');
                notice.className = 'zazu-notice';
                notice.setAttribute('role', 'alert');
                notice.dataset.zazuOfflineSaveError = '1';
                notice.textContent = 'Zazu could not confirm that this change was saved locally. Check the offline queue before trying again.';
                form.prepend(notice);
            } finally {
                if (form.dataset.zazuOfflineSaved !== '1') {
                    delete form.dataset.zazuOfflineSubmitting;

                    // The global submit guard may already have disabled the
                    // submit control before this async offline handler finished.
                    // Any rejected/unsupported offline mutation must release it,
                    // otherwise the user cannot correct the form and retry.
                    const submit = form.querySelector('button[type="submit"], button:not([type]), input[type="submit"]');
                    if (submit) {
                        submit.disabled = false;
                        submit.removeAttribute('aria-busy');
                        if (submit.dataset.zazuSubmitLabel) {
                            submit.textContent = submit.dataset.zazuSubmitLabel;
                            delete submit.dataset.zazuSubmitLabel;
                        }
                    }
                    delete form.dataset.zazuSubmitting;
                }
            }
        });
    });
}

function handleZazuPhonePairing() {
    const panel = document.querySelector('[data-zazu-phone-pairing]');
    const form = panel?.querySelector('[data-zazu-phone-pairing-form]');
    if (!panel || !form || !window.ZazuOffline || form.dataset.bound === '1') return;

    const isPhoneLayout = window.matchMedia('(max-width: 900px)').matches
        || /Android|iPhone|iPod/i.test(navigator.userAgent);
    if (!isPhoneLayout) return;

    const offline = window.ZazuOffline;
    const input = form.querySelector('[data-zazu-phone-pairing-code]');
    const submit = form.querySelector('[data-zazu-phone-pairing-submit]');
    const status = form.querySelector('[data-zazu-phone-pairing-status]');
    if (!input || !submit || !status) return;

    form.dataset.bound = '1';
    const refreshVisibility = async () => {
        await offline.ready;
        panel.hidden = offline.isPaired();
        if (!panel.hidden) {
            submit.disabled = input.value.replace(/\D/g, '').length !== 8;
        }
    };

    input.addEventListener('input', () => {
        const digits = input.value.replace(/\D/g, '').slice(0, 8);
        if (input.value !== digits) input.value = digits;
        input.setCustomValidity(digits.length === 8 ? '' : 'Enter all 8 digits from the owner’s phone pairing code.');
        submit.disabled = digits.length !== 8;
        status.textContent = '';
    });

    form.addEventListener('submit', async event => {
        event.preventDefault();
        const code = input.value.replace(/\D/g, '').slice(0, 8);
        if (code.length !== 8) {
            input.setCustomValidity('Enter all 8 digits from the owner’s phone pairing code.');
            input.reportValidity();
            return;
        }

        submit.disabled = true;
        input.disabled = true;
        status.textContent = 'Pairing this phone…';
        try {
            await offline.pair(code);
            status.textContent = 'Phone paired. Loading your business workspace…';
            window.location.reload();
        } catch (error) {
            status.textContent = error.message || 'Zazu could not pair this phone. Check the code and try again.';
            input.disabled = false;
            submit.disabled = false;
            input.focus();
            input.select();
        }
    });

    refreshVisibility().catch(() => {
        status.textContent = 'Zazu could not check this phone’s pairing status. Reload and try again.';
    });
}

window.addEventListener('zazu:offline-ready', handleZazuPhonePairing);
if (window.ZazuOffline) handleZazuPhonePairing();

setupZazuOfflineDirectoryViews();
setupZazuOfflineForms();
setupZazuOfflineCustomerOptions();
setupZazuOfflineAttachments();


function setupZazuOfflineAttachments() {
    const form = document.querySelector('[data-zazu-offline-attachment-form]');
    if (!form) return;

    if (!window.ZazuOffline) {
        window.addEventListener('zazu:offline-ready', setupZazuOfflineAttachments, { once: true });
        return;
    }
    if (form.dataset.zazuOfflineAttachmentBound === '1') return;

    const client = window.ZazuOffline;
    const eventId = Number(form.dataset.zazuEventId || 0);
    const serverAttachmentIds = new Set(String(form.dataset.zazuServerAttachmentIds || '')
        .split(',')
        .map(value => Number(value))
        .filter(value => Number.isSafeInteger(value) && value > 0));
    const input = form.querySelector('input[type="file"][name="files[]"]');
    const submit = form.querySelector('button[type="submit"]');
    const status = form.querySelector('[data-zazu-offline-attachment-status]');
    const list = form.parentElement?.querySelector('[data-zazu-offline-attachment-list]');

    if (!eventId || !input || !submit || !status || !list) return;
    form.dataset.zazuOfflineAttachmentBound = '1';

    const setStatus = (message, error = false) => {
        status.textContent = message;
        status.dataset.state = error ? 'error' : 'info';
    };

    const renderLocalFiles = async () => {
        const records = await client.loadAttachments(eventId);
        const visibleRecords = records.filter(record =>
            record.status === 'pending'
            || !navigator.onLine
            || !serverAttachmentIds.has(Number(record.attachment?.id || 0))
        );
        list.replaceChildren();
        list.hidden = visibleRecords.length === 0;

        for (const record of visibleRecords) {
            const row = document.createElement('div');
            row.className = 'zazu-list-item';

            const details = document.createElement('div');
            details.className = 'zazu-list-main';

            const name = document.createElement('div');
            name.className = 'zazu-list-title';
            name.textContent = record.original_name || 'Attachment';

            const meta = document.createElement('div');
            meta.className = 'zazu-list-meta';
            meta.textContent = record.status === 'pending'
                ? 'Saved on this phone · waiting to upload'
                : 'Available on this phone';

            if (record.last_error) {
                const warning = document.createElement('div');
                warning.className = 'zazu-list-meta';
                warning.textContent = record.last_error;
                details.append(name, meta, warning);
            } else {
                details.append(name, meta);
            }

            const open = document.createElement('button');
            open.type = 'button';
            open.className = 'zazu-btn zazu-btn-secondary';
            open.textContent = 'Open';
            open.addEventListener('click', async () => {
                try {
                    const file = await client.getAttachmentFile(record.idempotency_key);
                    const url = URL.createObjectURL(file);
                    const link = document.createElement('a');
                    link.href = url;
                    link.target = '_blank';
                    link.rel = 'noopener';
                    link.download = record.original_name || 'attachment';
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    window.setTimeout(() => URL.revokeObjectURL(url), 60000);
                } catch (error) {
                    setStatus(error.message || 'This file is not available on the phone.', true);
                }
            });

            row.append(details, open);
            list.append(row);
        }
    };

    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (form.dataset.zazuOfflineAttachmentSubmitting === '1') return;

        const files = [...(input.files || [])];
        if (!files.length) {
            setStatus('Choose at least one file.', true);
            return;
        }
        if (files.length > 10) {
            setStatus('Add up to 10 files per upload.', true);
            return;
        }

        form.dataset.zazuOfflineAttachmentSubmitting = '1';
        submit.disabled = true;
        const originalLabel = submit.textContent;
        submit.textContent = 'Saving…';

        try {
            await client.ready;

            // Keep Laravel's existing authenticated upload path whenever the
            // application origin is reachable. Queue only when the server is down.
            if (navigator.onLine && await canReachZazuOrigin()) {
                HTMLFormElement.prototype.submit.call(form);
                return;
            }

            await client.queueAttachments(eventId, files, '');

            input.value = '';
            const uploadGuidance = client.isPaired()
                ? ' They will upload when Zazu reconnects.'
                : ' Pair this device with Zazu before they can upload.';
            setStatus((files.length === 1
                ? 'File saved on this phone.'
                : files.length + ' files saved on this phone.') + uploadGuidance);

            if (client.isPaired()) {
                await client.syncAttachments();
            }
            await renderLocalFiles();
        } catch (error) {
            setStatus(error.message || 'The files could not be saved on this phone.', true);
        } finally {
            delete form.dataset.zazuOfflineAttachmentSubmitting;
            submit.disabled = false;
            submit.textContent = originalLabel;
        }
    });

    const refresh = () => renderLocalFiles().catch(() => {
        setStatus('The phone’s local attachment store could not be read.', true);
    });

    window.addEventListener('online', refresh);
    window.addEventListener('zazu:offline-attachments-updated', event => {
        if (!event.detail?.event_id || Number(event.detail.event_id) === eventId) refresh();
    });

    client.ready.then(refresh).catch(() => {
        setStatus('The phone’s local attachment store is unavailable.', true);
    });
}

function showZazuOfflineOriginNotice() {
    if (document.querySelector('[data-zazu-offline-origin-notice]')) return;

    const notice = document.createElement('aside');
    notice.dataset.zazuOfflineOriginNotice = '1';
    notice.className = 'zazu-offline-origin-notice';
    notice.setAttribute('role', 'status');
    notice.setAttribute('aria-live', 'polite');

    const title = document.createElement('strong');
    title.textContent = 'Offline installation unavailable from this address.';

    const description = document.createElement('p');
    description.textContent = 'This plain-HTTP network address depends on a reachable PC/server and cannot register Zazu’s service worker. Use a secure HTTPS install address or a self-contained app build before relying on independent offline launch.';

    notice.append(title, description);
    document.body.prepend(notice);
}

function showZazuOfflineRegistrationNotice() {
    if (document.querySelector('[data-zazu-offline-registration-notice]')) return;

    const notice = document.createElement('aside');
    notice.dataset.zazuOfflineRegistrationNotice = '1';
    notice.className = 'zazu-offline-origin-notice';
    notice.setAttribute('role', 'status');
    notice.setAttribute('aria-live', 'polite');

    const title = document.createElement('strong');
    title.textContent = 'Zazu could not enable offline app support.';

    const description = document.createElement('p');
    description.textContent = 'This browser could not register Zazu’s service worker. Confirm that the address uses trusted HTTPS, then reload Zazu. Until offline setup succeeds, this device may still need the server to open the app.';

    notice.append(title, description);
    document.body.prepend(notice);
}

function registerZazuServiceWorker() {
    if (window.isSecureContext === false) {
        showZazuOfflineOriginNotice();
        return;
    }

    if (!('serviceWorker' in navigator)) {
        showZazuOfflineRegistrationNotice();
        return;
    }

    const primeInstalledPages = (registration) => {
        const userId = document.body?.dataset.zazuUserId;
        const businessId = document.body?.dataset.zazuBusinessId;
        if (!userId || !businessId) return;

        // These are the two device-local creation routes currently supported
        // by the offline mutation queue. Prime them even when responsive navigation
        // or role-specific shortcuts do not expose links on the dashboard.
        const routes = [...new Set([
            '/dashboard',
            '/customers',
            '/work',
            '/customers/create',
            '/work/create',
            window.location.pathname,
        ])];

        const target = navigator.serviceWorker.controller || registration.active;
        if (!target) return;

        const channel = new MessageChannel();
        channel.port1.onmessage = (event) => {
            if (event.data?.type !== 'prime-pages-complete') return;
            document.documentElement.dataset.zazuOfflinePagesPrimed = event.data.success ? 'true' : 'false';
            window.dispatchEvent(new CustomEvent('zazu:offline-pages-primed', {
                detail: { success: event.data.success === true },
            }));
            channel.port1.close();
        };
        target.postMessage({ type: 'prime-pages', userId, businessId, routes }, [channel.port2]);
    };


    window.addEventListener('zazu:offline-pages-primed', event => {
        const existing = document.querySelector('[data-zazu-offline-pages-notice]');
        if (event.detail?.success) {
            existing?.remove();
            return;
        }

        if (existing) return;
        const notice = document.createElement('aside');
        notice.dataset.zazuOfflinePagesNotice = '1';
        notice.className = 'zazu-offline-origin-notice';
        notice.setAttribute('role', 'status');
        notice.setAttribute('aria-live', 'polite');

        const title = document.createElement('strong');
        title.textContent = 'Offline workspace is not fully prepared.';
        const description = document.createElement('p');
        description.textContent = 'Zazu could not cache the Customers and Work pages. Stay connected and reload before relying on this device offline.';
        notice.append(title, description);
        document.body.prepend(notice);
    });

    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js', {
            scope: '/',
            updateViaCache: 'none',
        })
            .then(async (registration) => {
                // Check the origin for a newer worker on every launch, including installed PWA launches.
                try {
                    await registration.update();
                } catch {
                    // A temporary network failure must not block the cached app shell.
                }

                return navigator.serviceWorker.ready;
            })
            .then((ready) => {
                // Prime once only: prime-pages aborts any earlier priming run, so
                // sending the same route set twice can cancel the dashboard cache fill.
                primeInstalledPages(ready);
            })
            .catch(() => {
                // Keep normal online use available, but make failed offline setup visible.
                showZazuOfflineRegistrationNotice();
            });
    });
}

registerZazuServiceWorker();

let zazuLogoutInProgress = false;

async function handleZazuLogout(form) {
    const businessId = Number(document.body?.dataset.zazuBusinessId || 0);
    const message = businessId > 0
        ? 'Signing out ends this web session. This browser’s business-scoped offline workspace and any queued changes or attachments will remain on this device. To remove this device from the business, an owner must revoke it in Business Settings. Its sync credential remains on this browser after sign-out, and offline changes are attributed to the approving owner—not necessarily the person using it. Revoke it if this device is shared, lost or reassigned. Continue?'
        : 'Signing out ends this account’s web session. No active business workspace is attached to this account. Continue?';

    if (!window.confirm(message)) return;

    const submit = form.querySelector('button[type="submit"]');
    if (submit) {
        submit.disabled = true;
        submit.setAttribute('aria-busy', 'true');
    }

    try {
        // Submit the real Laravel logout action directly. A separate /up probe
        // creates a needless race and can block logout even when POST /logout works.
        const response = await fetch(form.action || '/logout', {
            method: 'POST',
            credentials: 'same-origin',
            cache: 'no-store',
            redirect: 'follow',
            headers: { 'Accept': 'text/html' },
            body: new FormData(form),
        });

        if (!response.ok || !response.redirected) {
            const message = response.status === 419
                ? 'Your security token has expired. Refresh Zazu, then try signing out again.'
                : 'Zazu could not confirm that your session ended (HTTP ' + response.status + '). You are still signed in; please retry.';
            throw new Error(message);
        }

        // Clear only the authenticated page cache after the server confirms
        // that the session-ending POST succeeded. Business data and its queue
        // remain intact for the next authorized sign-in.
        if ('serviceWorker' in navigator) {
            try {
                const registration = await navigator.serviceWorker.getRegistration();
                const target = navigator.serviceWorker.controller || registration?.active;
                target?.postMessage({ type: 'clear-user' });
            } catch {
                // Cache cleanup is best-effort; the server session is already ended.
            }
        }

        window.location.assign(response.url || '/');
    } catch (error) {
        window.alert(error instanceof TypeError
            ? 'Zazu could not reach the server to end your session. You are still signed in. Your offline workspace and queued work are safe; reconnect and try again.'
            : (error.message || 'Zazu could not complete sign out. Your session and offline work have been left unchanged; please retry.'));
    } finally {
        if (submit) {
            submit.disabled = false;
            submit.removeAttribute('aria-busy');
        }
    }
}

document.addEventListener('submit', async (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;

    let action;
    try {
        action = new URL(form.action || window.location.href, window.location.href);
    } catch {
        return;
    }
    if (action.origin !== window.location.origin || action.pathname !== '/logout') return;

    event.preventDefault();
    if (zazuLogoutInProgress) return;
    zazuLogoutInProgress = true;

    try {
        await handleZazuLogout(form);
    } catch {
        window.alert('Zazu could not complete safe local-session cleanup. Your data was left in place; retry after reloading.');
    } finally {
        zazuLogoutInProgress = false;
    }
});
