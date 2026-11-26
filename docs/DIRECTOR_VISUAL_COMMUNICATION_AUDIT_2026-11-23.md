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
- [x] Migrate primary navigation groups, Dashboard metric icons and quick actions to the shared icon system.
- [x] Start the status/action migration on Data export & recovery: local Tabler download/upload, lock and shield-check icons now replace the two Unicode archive arrows and clarify the protected actions. This is a first workflow slice, not a product-wide migration.
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


## Dashboard colour semantics

- The dashboard previously assigned KPI card fills and icon colours by `:nth-child` position. Optional modules change the order/count of cards, so a colour could describe a different metric in another business workspace.
- KPI surfaces are now neutral in both themes. Explicit metric classes drive icon accents: blue for work/customers/calendar, amber for outstanding quotes or invoices, green when those queues are clear and for revenue received.
- This preserves colour as a cue for meaning/state rather than decoration or card position.


## Recovery action icon migration — 2026-11-24

- Expanded the local curated Tabler subset with download, upload, lock, shield-check, alert, info and check symbols. All remain inline/local; no CDN, runtime package or network dependency was introduced.
- Replaced Unicode arrows on the business archive create/restore cards with consistent download/upload icons. Added lock and shield-check cues to the encryption/restore actions and download icons to CSV exports.
- Added a focused feature regression covering icon usage and removal of the two ambiguous arrow glyphs.
- Remaining: continue status and form-action migration in the highest-use workflows. This slice does not claim the full UI icon rollout is complete.


## Work, Quotes and Customers icon migration — 2026-11-24

- Added local Tabler state icons to Jobs and Quotes registers. Status text remains visible; icons reinforce confirmed/accepted, in-progress/sent, cancelled/declined/expired and neutral states rather than making colour carry the meaning alone.
- Added consistent action icons to the Work register (edit, inspect and remove) and Customer directory (import, new customer, start work and open).
- Extended the curated local icon component only; no icon package, remote asset, CDN, route, permission or workflow change.
- Added a focused feature regression covering the three view surfaces and required local icon definitions.
- This is a targeted visual communication slice. Finance and Purchasing remain in the next audit queue; full page-level spacing/width review remains separate.


## Finance and Purchasing status icon migration — 2026-11-24

- Added local semantic Tabler cues beside invoice, expense and purchase-order statuses while retaining their readable status labels and existing chip styling.
- Purchase-order icons follow the model's explicit lifecycle: draft/sent/ordered are pending, received is complete, and cancelled is stopped. Finance statuses use conservative label mappings with a neutral fallback for unknown states.
- No transaction logic, status values, permissions, routes or workflow behaviour changed. Icons remain inline/local and offline-safe.
- Added a focused feature regression for Finance and Purchasing status presentation.
- The remaining visual audit is page-level composition, spacing and responsive acceptance; these icon changes do not close those separate checks.


## Inventory and Finance form consistency — 2026-11-24

- Normalised the Inventory item, Finance expense and Finance payment forms to the shared Zazu field-label, input, select and textarea classes.
- Reintroduced visible required markers on mandatory fields, added input-mode hints for decimal amounts and a short reorder-level explanation. Kept the existing field names, option values, defaults, idempotency keys, offline entity markers, routes and submit/cancel actions intact.
- Preserved the existing responsive form grid and sticky action bar; no global width changes or business-rule changes.
- Added a focused feature regression to prevent these compact forms from drifting back to browser-default control styling.
- This is a consistency correction, not a full visual acceptance. Purchasing and the longer Job/Quote editors remain on the broader form audit.


## Compact form consistency correction — 2026-11-24

- Normalised Inventory item creation, Finance expense entry and Finance payment entry to the shared Zazu label, input, select and textarea styles. Previously these compact forms relied on browser defaults, unlike adjacent workflows.
- Restored visible required markers, decimal input modes and a concise reorder-level explanation. Existing form grids and sticky action bars remain contextual; no global width rule was added.
- Preserved routes, field names, option values, defaults, idempotency keys and offline entity markers.
- Added regression coverage alongside the existing operational presentation checks. The related assertions were consolidated into the existing presentation test method to remain within the PHPMD public-method limit.
- Remaining: rendered visual acceptance at desktop/mobile breakpoints, plus the rest of the Jobs/Quotes/Purchasing form sweep.


## Purchasing form label hierarchy — 2026-11-24

- The purchase-order creation form already used Zazu's shared control classes, responsive grid, sticky action bar and offline entity marker, but its field captions were plain spans. This made the longest, line-item-heavy form visually inconsistent with the Inventory and Finance forms just normalised.
- Applied the shared `zazu-label` treatment to supplier/job/currency/date, line description/quantity/unit/price/catalogue and notes captions, including the dynamically added line template. Required markers, helper text, line-total feedback, field names, JS hooks, idempotency key, route and offline marker are unchanged.
- Added focused regression assertions to the existing presentation test method; no new test class/method or business logic was introduced.
- This closes only the purchasing-create label inconsistency. Quote and Job editor page-level layout, responsive rendering and physical visual acceptance remain open.

- Verification note: the first exact-head quality run flagged one extra blank line at the end of the expanded presentation test. The test file was corrected to Pint's existing style; no production code or formatter rule was changed. The corrected commit is `717dea4e` and requires a fresh exact-head quality run.


## 2026-11-26 — Sidebar information-density refinement

The Founder screenshot showed three persistent context blocks competing with the navigation: a boxed business-mix card, a separate workspace identity card, and a signed-in account control. The result consumed too much vertical space and repeated workspace identity.

- Retained all five navigation groups, route permissions, active states, submenu destinations and mobile drawer behaviour.
- Kept the business mix as a quiet, read-only one-line context summary below navigation: active-offering count, compact focus chips and a subtle catalogue-management link. The secondary category paragraph is no longer persistently displayed in the sidebar; the underlying data and live-update hooks remain intact.
- Merged workspace identity into the existing account trigger. It now shows the active workspace as the primary label and the signed-in person/role as supporting context. The existing account popover continues to contain account details, business switching, experience preference, setup, settings and sign-out.
- Removed the standalone workspace block. Persistent lower-sidebar composition is now two elements instead of three; account details remain on demand.
- The same compact layout applies to desktop, tablet and phone. The mobile drawer retains its existing scroll ownership and safe-area footer spacing.
- No routes, permissions, account actions, workspace switching, profile data or offline behaviour were changed.


## 2026-11-26 — Navigation interaction model correction

- Treat the reference as a compact, anchored navigation system: expanded mode shows branded icon + label group triggers; each parent opens a contextual panel without navigating. Collapsed desktop mode becomes a 76px icon rail with the same panels anchored beside the selected icon.
- Added a persistent desktop collapse/expand control with local preference persistence. Tablet and phone retain the full-width drawer and tap-controlled accordion; desktop collapse state is not applied to touch layouts.
- Removed the vertical scrolling constraint from the desktop navigation list. Only a contextual panel may internally scroll when its content exceeds the available viewport.
- Finance's Search destination is explicitly named Advanced Search and opens the existing workspace search page with its record type, status and date filters.
- Retained Zazu brand lockup, current theme tokens, active-state treatment, role checks, route destinations and mobile interaction model.


## 2026-11-26 — Navigation CI root-cause correction

The Laravel workflow exposed three concrete regressions introduced by the navigation foundation:
- The group trigger's accessible name was changed to "Work navigation", breaking the existing Work-group browser locator. It now exposes the visible group name as its accessible name.
- The account control's "Signed in" context label was replaced with "WORKSPACE", breaking the account-shell contract. The label is restored while the active workspace remains the primary identity.
- A second base definition for .zazu-nav-area-title was appended instead of extending its canonical rule. Geometry now lives in the existing canonical selector; the duplicate rule is removed.

The desktop collapse initializer existed but was not called from the main UI initialization sequence. It is now registered before mobile and hierarchical navigation setup, so the control is live rather than decorative.


## Navigation presentation refinement — 2026-11-26

Expanded desktop now uses inline, indented subnavigation beneath its selected parent, matching the supplied reference rather than opening every submenu as an off-canvas flyout. Collapsed desktop keeps the 76px branded icon rail and moves the same submenu into a floating contextual panel beside its parent. The two modes share one semantic group structure and one interaction controller. Mobile/tablet retain the full drawer and inline accordion. Added browser acceptance for collapse, anchored panel visibility, no parent-route navigation, and destination navigation.
