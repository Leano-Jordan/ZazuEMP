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
        const icon = button.querySelector('[data-theme-icon]');
        const label = button.querySelector('[data-theme-label]');

        if (icon) icon.textContent = isDark ? '☀' : '◐';
        if (label) label.textContent = isDark ? 'Light' : 'Dark';

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
