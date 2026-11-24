# Zazu EMP — Visual Page / Component Implementation Map
Date: 2026-11-24
Repository: Leano-Jordan/ZazuEMP
Branch: main
Parent contract: docs/DIRECTOR_VISUAL_EXCELLENCE_EXECUTION_2026-11-24.md

This map connects the 46-screen visual review to the real Blade/CSS implementation. It is a source-level implementation map, not proof of rendered behaviour.

## Shared foundations

| Surface | Source | Canonical styling owner | Implementation direction |
|---|---|---|---|
| Authenticated shell | resources/views/components/app-layout.blade.php | app.css + responsive-theme + mobile-refinement | Improve hierarchy only after the offline acceptance gate; preserve nav groups, permissions, mobile drawer, business profile, account menu and Helper. |
| Shared cards/panels/forms/tables/buttons | Shared Blade components and module views | app.css + zazu-product-authority.css | Reduce nested surfaces, align section headers, improve table scan paths and input widths by field role. Do not add generic override rules to final-visual-sweep. |
| Dashboard | resources/views/dashboard.blade.php | zazu-dashboard-authority.css | Prioritise operational signals and next actions; retain existing business artwork where readable. |
| Responsive layout | Shared Blade views | zazu-responsive-theme.css | Preserve intentional calendar/table scrolling; prevent page-level overflow and control collisions. |
| Mobile shell | app-layout.blade.php | zazu-mobile-refinement.css | Preserve 44px touch targets, drawer ownership, safe-area spacing and navigation state. |
| Component-specific details | Module Blade views | zazu-final-visual-sweep.css only when component-specific | Only add narrowly scoped component rules after selector ownership search. |
| Authenticated product consistency | Module Blade views | zazu-product-authority.css | Keep shared shell/surface language aligned without flattening distinct task layouts. |

## Page-family implementation map

| Screenshot group | Actual Blade source(s) | Main shared patterns | Design work required |
|---|---|---|---|
| Public landing and login | resources/views/landing.blade.php; resources/views/auth/login.blade.php | Public shell, auth frame, media slots | Preserve image-led public brand; refine content hierarchy, crop consistency, login focus and responsive composition. |
| Owner Admin | resources/views/owner/index.blade.php | Command band, metrics, admin panels | Establish a compact owner overview; distinguish platform/business health from routine workspace tasks. |
| Dashboard | resources/views/dashboard.blade.php | Dashboard hero, KPI metrics, quick actions, event timeline, operational panels | Reduce competing emphasis; make urgency, commercial exposure and next action immediately scannable. |
| Jobs / Event Operations | resources/views/work/index.blade.php; resources/views/work/create.blade.php; resources/views/work/edit.blade.php; resources/views/work/show.blade.php | Ops hero, workload pills, register rows, inspector, editor sections | Make register rows scan by reference/customer/date/status; clarify inspector summary and primary edit/open actions; maintain function-sheet access. |
| Quotes | resources/views/quotes/index.blade.php; resources/views/quotes/create.blade.php; resources/views/quotes/edit.blade.php; resources/views/quotes/show.blade.php; resources/views/quotes/public.blade.php | Command band, record list, quote-line editor, summary, document | Prioritise quote total, revision, status and customer; give line items more legible quantity/unit/rate/total alignment; separate internal editor from customer document. |
| Customers | resources/views/customers/index.blade.php; resources/views/customers/create.blade.php; resources/views/customers/edit.blade.php; resources/views/customers/show.blade.php | Register/list rows, editor, detail cards | Improve customer identity/contact hierarchy and record actions; avoid making secondary metadata compete with the name. |
| Calendar | resources/views/calendar/index.blade.php | Calendar shell, view switch, summary, month cells, agenda | Preserve multiple views and dense event data; refine date/status hierarchy, legend and mobile scroll affordance. |
| Purchasing | resources/views/purchasing/index.blade.php and related create/show views | Command band, register/list, order detail | Put supplier, order reference, expected date, total and status in a predictable scan path. |
| Suppliers | resources/views/suppliers/index.blade.php and related create/edit views | Register/list, editor | Distinguish supplier identity, contact and supply category; keep edit forms proportionate. |
| Inventory | resources/views/inventory/index.blade.php; resources/views/inventory/create.blade.php | Stock register, demand panel, form | Distinguish on-hand, unit, SKU and demand; use tabular numeric alignment and meaningful low/zero stock states. |
| Assets | resources/views/assets/* | Register, detail, editor | Make asset identity, condition, availability and allocation easy to scan; preserve traceability. |
| Finance and costs | resources/views/finance/*; resources/views/costs/* | Financial registers, summary totals, forms, documents | Align amounts and currencies; separate paid, due, overdue and forecast values semantically; protect calculation clarity. |
| Export/recovery | resources/views/exports/*; resources/views/sentinel/local-recovery.blade.php; resources/views/auth/workspace-recovery.blade.php | Recovery panels, archive actions, confirmation states | Use a guided recovery flow with clear scope, prerequisites, consequence and confirmation; avoid repeated same-weight cards. |
| Settings and compliance | resources/views/settings/index.blade.php; resources/views/settings/compliance.blade.php; resources/views/settings/compliance-pack.blade.php | Collapsible form sections, editor, context cards | Break long settings into task groups; preserve open-on-validation behaviour; communicate required/legal data and save state. |
| Activity audit | resources/views/audit/* and settings audit route/view | Timeline/table, actor, event, timestamp | Improve event grouping and timestamp readability; distinguish security-sensitive events without colour-only semantics. |
| Setup/onboarding | resources/views/onboarding/* | Stepper, setup cards, progress | Show progress, missing prerequisites and next action without inventing completion. |
| Landing-page administration | resources/views/admin/landing-settings.blade.php | Image slot grid, previews, library, upload controls | Improve preview/selection clarity and keep public media separate from business workspace branding. |

## Concrete component priorities

### P0 — protect and stabilise
- Preserve the existing CSS token owner in app.css.
- Preserve the hidden-state display contract; it is required by offline route isolation.
- Do not modify offline route rendering, service-worker caching, local data, or shared app shell while the offline acceptance gate is the active engineering priority.
- Do not remove the configured dashboard image or business wallpaper globally.

### P1 — first visual implementation after offline gate
1. Shared page-intro pattern: compact title, contextual description, one primary action, optional compact metadata. Avoid repeated oversized hero treatments on routine operational pages.
2. Shared register pattern: aligned column labels and values, readable row height, compact secondary metadata, clear row actions and useful empty state.
3. Shared editor pattern: sections based on user task, fields sized to data type, inline errors, visible save state, sticky action bar only where it improves completion.
4. Shared status language: use the existing semantic tokens and local Tabler icon component; text must accompany critical status icons.
5. Dashboard composition: distinguish urgent work, commercial state and next actions from passive counts.

### P2 — page-family refinements
- Work register/inspector and Work editor.
- Quote editor and quote document.
- Customer register/detail.
- Calendar month and agenda.
- Purchasing/supplier/inventory registers.
- Recovery, settings and compliance.
- Landing-page administration and public pages.

## Source-specific observations

- resources/views/work/index.blade.php already has a rich job inspector with scope, preparation, resources, allocations and commercial position. Improve its information hierarchy and panel dimensions; do not remove these capabilities.
- resources/views/quotes/edit.blade.php already separates commercial inputs from product/service lines and has explicit quantity guidance. The redesign must make this information easier to process, not weaken required quantity controls or alter quote arithmetic.
- resources/views/calendar/index.blade.php has month, multi-month, year and agenda representations. Preserve these representations and the holiday visibility contract.
- resources/views/settings/index.blade.php uses collapsible sections and validation-aware opening. Preserve that behaviour; redesign its long-form presentation rather than flattening every setting into one screen.
- resources/views/admin/landing-settings.blade.php already separates public landing media from workspace branding and supports previews. Preserve that distinction and the safe local-media workflow.
- The shell's navigation is permission-aware and contains five hierarchical groups. Visual simplification must not remove routes, access boundaries or the workspace/account controls.

## Execution state

- [x] Source-level page-family map created from current main.
- [x] Shared styling owners mapped.
- [ ] Offline acceptance gate closed by the Director.
- [ ] Rendered baseline captured at desktop and Android sizes, light and dark.
- [ ] First implementation slice selected and changed.
- [ ] Tests/build and rendered regression evidence recorded.

Until the offline gate is closed, this map is prepared work, not permission to interrupt the active offline/security acceptance priority. Once authorised by the Director's release sequence, execute one page family at a time on main and record exact source/test/render evidence.


## Founder direction — preserve and improve the existing interface (2026-11-24)

This map is a guide to improving the current Zazu screens, not a blueprint for replacing them. Keep the existing page concepts, useful layouts, established identity, imagery and functional components. Refine their execution in place: hierarchy, spacing, proportions, typography, contrast, field/table readability, icon/status clarity and responsive composition. Do not imitate Advanced SystemCare or another product, impose a new generic SaaS aesthetic, or remove useful functionality for minimalism. Recommend replacement only when a specific usability or technical defect makes refinement insufficient, and document the reason before changing it.


## Dashboard source inspection — implementation boundary (2026-11-24)

Source inspected at dashboard Blade blob bc988e5b53ca7848b8a6edf8d9d9858e286c4bb1 and dashboard CSS blob ac1d1c47041f63cce15a4ae02101fc1eede68b30.

The dashboard already has a clear product structure: setup/backup notices, greeting and business-capability hero, live metric links, quick actions, operational timeline, quote stages, priority actions and resource/health links. These should be refined in place, not replaced.

**Important implementation boundary:** the same Blade view also contains the offline workspace, offline overview, unavailable state and individual local collections. Any dashboard CSS change must explicitly target the online dashboard components only, unless it is intentionally an offline-workspace change with dedicated offline regression coverage. Do not use broad .zazu-dash-surface, .zazu-dash-grid or shared shell selectors as a shortcut.

The dedicated dashboard stylesheet is already substantial and contains accumulated late-file overrides and repeated !important declarations. Do not append another override patch. Before editing, identify the canonical existing selector block and consolidate the specific declarations there. Preserve the configured dashboard image and capability content, the current metric destinations, permission-aware conditional rendering, all offline hidden states and the offline route renderer contract.

**First refinement slice after the active offline gate permits visual changes:** improve metric-card scanability and spacing, hero/capability image balance, and operational-panel heading rhythm using existing classes and the dashboard stylesheet's existing authority. No Blade restructuring is needed unless a specific accessibility defect is demonstrated. Validate desktop and narrow mobile layouts, both themes, and offline workspace visibility before calling the slice accepted.
