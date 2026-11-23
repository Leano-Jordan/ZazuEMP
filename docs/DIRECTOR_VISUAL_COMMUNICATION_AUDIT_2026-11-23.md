# Director visual communication audit — 2026-11-23

## Immediate corrections implemented

- Moved the derived **Your business mix** panel below the primary navigation. It remains read-only, permission-aware and derived from existing business focus/catalogue data; no routes or access rules changed.
- Restored the dashboard image treatment. The layout already selected a business-uploaded dashboard image or the bundled `public/images/landing/stock/hero.jpg` fallback. The image was being washed out by an almost opaque surface overlay (94% / 82% in the later authority rule). The side panel now uses a materially lighter 76% / 54% overlay in light mode and a 78% / 56% dark overlay. The duplicate conditional image element was removed; the shared background variable is the single source of truth.
- Changed user-visible backup timestamps in Dashboard and Founder Control Centre to local `j M Y, H:i` presentation using the configured application timezone. Machine-readable timestamps remain unchanged in internal/API contracts.

## Icon library licensing review

The current `package.json` has no icon package dependency. The dashboard currently contains individually authored inline SVG markup, and other areas use a mixture of SVG, text glyphs and emoji. That makes icon weight, proportions and meaning inconsistent.

| Library | Licence | Commercial use | Assessment |
|---|---|---|---|
| [Tabler Icons](https://github.com/tabler/tabler-icons) | MIT | Permitted, including modification and redistribution, provided the copyright and licence notice is retained with copies/substantial portions | **Recommended Zazu standard.** Broad catalogue, outline and filled families, consistent 24px grid, and useful business/operations coverage. |
| [Lucide](https://github.com/lucide-icons/lucide) | ISC | Permitted for commercial use; retain the copyright/licence notice when redistributing the icon software/assets | Strong alternative with a restrained visual language, but a smaller domain vocabulary and no brand logos. |
| [Heroicons](https://github.com/tailwindlabs/heroicons) | MIT | Permitted with the MIT notice retained | High-quality, coherent set, but materially smaller catalogue than Tabler. |

**Decision:** standardise on official Tabler SVG assets for Zazu's interface. Do not mix icon libraries. Keep the upstream MIT licence in `THIRD_PARTY_NOTICES.md` and preserve the notice alongside any vendored icon assets. Do not load icons from a third-party CDN: Zazu must remain reliable offline. Use a deliberately selected local subset rather than shipping thousands of unused icons. Brand marks/logos remain separate from interface icons.

## Product-wide visual audit findings

1. **Meaning before decoration.** Important actions, statuses, navigation groups and empty states need a recognisable icon plus plain-language text. Never rely on an icon alone for a primary action or destructive operation.
2. **One icon grammar.** Use Tabler outline icons as the default; reserve filled variants for selected/active states or a clearly meaningful emphasis. Standardise 20px for compact controls and 24px for prominent section/action icons, consistent stroke and baseline alignment.
3. **Typography hierarchy.** Every page should have one clear page title, meaningful section headings, readable field labels, quiet helper text and legible values. Avoid tiny all-caps labels as the only way to identify content. Preserve the existing locally bundled Manrope and Space Grotesk fonts; no remote font dependency.
4. **Controls should fit their job.** Buttons and inputs should size to content or their grid column. Full-width controls belong only where the form layout or mobile viewport calls for them. Avoid oversized buttons and full-row fields on desktop.
5. **Colour must explain state.** Use the established blue/slate foundation; reserve green, amber and red for success, caution and destructive/error states. Do not use colour as the only status signal.
6. **Structure for scanning.** Group related information, align labels and values, use compact summaries with progressive disclosure, and avoid repeating the same card shape down a long page.
7. **Motion with purpose.** Use short transitions for feedback and state changes, never animation for decoration alone. Honour `prefers-reduced-motion`; avoid layout-shifting motion and infinite movement.
8. **Dates and times are for people.** Render local dates as readable calendar dates and times (for example, `23 Nov 2026, 22:53`), not raw ISO timestamps. Keep ISO-8601 for APIs, storage, sync and diagnostics only.

## Rollout sequence

- [x] Correct dashboard image visibility.
- [x] Move business mix below primary navigation.
- [x] Humanise backup timestamps in Dashboard and Founder Control Centre.
- [x] Select a commercially permissive, offline-safe icon library.
- [x] Add a local curated Tabler subset, retain the upstream MIT notice and create a shared Blade icon component. No runtime package or CDN is used.
- [x] Migrate primary navigation groups, Dashboard metric icons and quick actions to the shared icon system. Status indicators and form actions remain in the next migration slice.
- [ ] Audit and correct button/input widths and hierarchy on the highest-use workflows first: Jobs, Quotes, Customers, Purchasing, Inventory and Finance.
- [ ] Run a page-by-page visual communication sweep for labels, empty/loading/error states, spacing, responsive composition, contrast and reduced-motion behaviour.

**Scope guard:** this is a usability and visual-communication pass, not permission to rewrite workflows, change business rules, or replace Zazu's established identity. Preserve offline behaviour, permissions, business isolation and existing user data.


## Additional presentation root causes confirmed

- Business wallpaper and dashboard hero photography are separate settings. The business wallpaper was excluded from dashboard pages by the old `body.zazu-has-wallpaper:not(.zazu-dashboard-page)` selector. A dashboard-specific light/dark wallpaper layer now uses the existing `--zazu-wallpaper` variable; shell/content surfaces are transparent and mobile uses scroll attachment.
- The application retains UTC for internal/API timestamps. A separate `ZAZU_DISPLAY_TIMEZONE` controls human-facing dates and defaults to `Africa/Johannesburg`, overridable per installation. Backup and health timestamps in Dashboard, Founder Control Centre, Owner health and Local Recovery are now formatted as `j M Y, H:i` in the configured timezone. API/sync timestamps remain ISO-8601.


**Timezone boundary decision:** keep Laravel's application/system timezone at UTC so database, sync and machine-facing timestamps do not silently change offset. Use the separate `ZAZU_DISPLAY_TIMEZONE` (South African default) only when rendering human-facing dates and times.


## Control sizing and everyday readability

- The shared button base is already content-sized (`inline-flex`); full-width buttons are scoped to mobile stacks or individual cells in action grids. A global button-width reduction would damage legitimate mobile and card actions, so those exceptions remain contextual.
- The actual width issue is long form fields: `zazu-field-wide` spans the entire form grid and could become excessively wide on large screens. It is now capped at 760px while remaining full-width on mobile.
- Field helper text is raised to 12px. Inputs/selects/textareas use 14px on desktop and 16px on mobile for legibility and to avoid mobile browser auto-zoom. Existing 13.5px labels and 42px control height are retained.
- Existing reduced-motion handling is preserved. Zazu already has hover/focus/active transitions; the next motion pass should add feedback only where state changes need explaining, not animate every control.
