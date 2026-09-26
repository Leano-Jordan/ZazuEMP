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
