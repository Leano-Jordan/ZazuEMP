# Zazu EMP — Visual Excellence Execution Contract
Date: 2026-11-24
Authority: Rosscore Labs Director
Repository: Leano-Jordan/ZazuEMP
Source of truth: main

## Mission

Raise the complete Zazu experience from a visually capable but inconsistent application to a distinctive, premium event-business operating system. Use the clarity, calmness, responsiveness and finish of IObit Advanced SystemCare's light-mode experience as a quality reference only. Do not copy its product model, branding, layout or visual identity.

This is an implementation contract, not permission to expand product scope or rewrite working workflows.

## Evidence and baseline

- Reviewed all 46 PNGs in the 2026-11-24 Zazu App Screenshots collection.
- Screenshot-only overall visual assessment: 6.3/10. This is a design-review judgement, not a user-test metric.
- Strongest visible areas: public-facing presentation, event proposition, customer directory and basic activity views.
- Weakest visible patterns: long settings/configuration forms, quote line-item density, editing states, repeated card stacks, and inconsistent visual emphasis.
- The screenshots cannot establish responsive runtime behaviour, keyboard operation, actual interaction feedback, offline parity, accessibility conformance or performance. Those require separate runtime evidence.

## Current repository source map (verified on main)

The existing design system is more mature than a simple four-stylesheet map. Do not add another stylesheet or create another visual authority.

| File | Current responsibility |
|---|---|
| resources/css/app.css | Sole design-token authority and shared foundational styles |
| resources/css/zazu-responsive-theme.css | Shared responsive composition and responsive component behaviour |
| resources/css/zazu-mobile-refinement.css | Mobile shell, navigation and touch layout |
| resources/css/zazu-final-visual-sweep.css | Existing component-specific extensions; must not become a second generic authority |
| resources/css/zazu-dashboard-authority.css | Dashboard-only composition |
| resources/css/zazu-product-authority.css | Cross-module authenticated product components |

The authenticated shell loads these assets through resources/views/components/app-layout.blade.php. vite.config.js also declares them as build inputs. Keep both lists consistent if any asset entry changes.

Existing contracts that remain binding:
- .agents/skills/ui-recovery/SKILL.md
- .agents/skills/ui-recovery/references/05_UI_UX_IMPROVEMENT_ENGINE.md
- .ai/engineering/05_UI_UX_IMPROVEMENT_ENGINE.md
- docs/ZAZU_UI_UX_AUDIT.md
- docs/ZAZU_TYPOGRAPHY_STANDARD.md
- docs/DIRECTOR_VISUAL_COMMUNICATION_AUDIT_2026-11-23.md
- docs/DIRECTOR_CSS_AUTHORITY_AUDIT_2026-10-04.md (its original four-file map is historical; it predates the dashboard/product authority files)

## Design direction

### Product character

Zazu is a premium, practical event-business workbench. It should feel calm, precise, capable and welcoming—not like a marketing website pasted over a CRUD application and not like a generic white SaaS template.

### Light mode

- Move the perceived experience toward cleaner cloud-white work surfaces and a calmer, less visually heavy canvas while preserving Zazu's blue-slate identity.
- Keep deep blue for primary actions, navigation emphasis and meaningful focus—not as decoration on every surface.
- Keep turquoise/secondary accents restrained and purposeful.
- Use warm hospitality photography only where it strengthens brand or context: public pages, deliberate dashboard artwork, and media previews. Do not globally remove configured business wallpaper or dashboard artwork; fix only proven contrast/competition problems at their component boundary.
- Use surface contrast, typography, spacing and alignment—not stacks of cards—to establish hierarchy.

### Dark mode

Maintain a separately designed deep-water theme. Every shared component change must be checked in dark mode; do not derive dark values mechanically from light mode.

### Typography and iconography

- Preserve locally bundled Manrope and Space Grotesk; no remote font dependency.
- Use Manrope for operational UI and Space Grotesk for selective brand/display emphasis.
- Use the repository's curated local Tabler SVG component. Do not add a second icon library, emoji system or CDN.
- Keep numeric and financial values tabular and easy to scan.
- Do not use tiny all-caps metadata as the only label for important information.

### Interaction

- Short, purposeful transitions only; honour reduced motion.
- Buttons and fields must fit their purpose and content; do not globally force all controls full width or globally shrink mobile targets.
- Primary, secondary and destructive actions must be unmistakable.
- Empty, loading, success, warning, error, disabled, selected and unsaved states must communicate what happened and what the user can do next.

## Screenshot scorecard baseline

| Surface family | Baseline |
|---|---:|
| Public experience and access | 7.2/10 |
| Owner administration | 6.1/10 |
| Dashboard | 6.4/10 |
| Event operations | 6.2/10 |
| Quotes and commercial documents | 6.2/10 |
| Search, customers and calendar | 6.4/10 |
| Purchasing, suppliers, inventory and assets | 6.1/10 |
| Recovery, settings and compliance | 6.1/10 |
| Landing-page administration | 6.1/10 |
| Overall screenshot-based UI/UX | 6.3/10 |

Target: 9/10 visual maturity, subject to rendered desktop/mobile and light/dark acceptance. Do not report the target as achieved from source checks alone.

## Execution order

### Gate 0 — establish deterministic ownership (source audit)
- [x] Confirm current branch/source of truth is main.
- [x] Confirm the six active CSS authorities and shell/Vite loading relationship.
- [x] Read the existing UI recovery contract and current visual communication audit.
- [ ] Inventory the actual Blade component/page families and identify repeated markup or selectors before changing them.
- [ ] Reconcile stale documentation that still describes only four CSS authorities.

### Gate 1 — global application shell
Target: resources/views/components/app-layout.blade.php and the canonical shell owners only when a concrete defect is confirmed.
- Navigation hierarchy and active state.
- Topbar density and page context.
- Main content measure and consistent page-header rhythm.
- Wallpaper/image contrast without removing user-configured branding.
- Desktop, tablet and Android layout.
Acceptance: no clipped flyouts, no overlapping controls, no lost navigation, no horizontal page overflow, keyboard focus remains visible, light/dark both readable.

### Gate 2 — dashboard command centre
Target: resources/views/dashboard.blade.php and resources/css/zazu-dashboard-authority.css.
- Give the user one clear first scan path: business state, urgent work, commercial exposure, next actions.
- Keep operational metrics readable and actionable.
- Reduce redundant visual emphasis without deleting useful information.
- Retain business-owned dashboard image treatment where it remains legible.
Acceptance: first useful action and urgent business condition are identifiable immediately; no information hidden by visual simplification.

### Gate 3 — core commercial workflows
Priority order:
1. Work/event register and event editor.
2. Quotes register, quote editor, quote detail and customer-facing quote document.
3. Customers.
4. Calendar.
Acceptance: clear page title/context, one primary action, readable tables, compact and appropriately sized fields, aligned financial figures, clear save/error/empty states.

### Gate 4 — purchasing and operational resources
Suppliers, purchase orders, inventory, assets, costs and finance.
Acceptance: quantities, units, supplier identity, due dates, balances and status can be scanned without reading every field; edits remain usable on Android.

### Gate 5 — complex administration
Recovery/export/import, settings, compliance, activity, onboarding and landing-page administration.
Acceptance: long pages are grouped into task-oriented sections, not repetitive card stacks; destructive/recovery actions are clearly separated and explained.

### Gate 6 — public/customer-facing finish
Landing page, login, public quote and payment documents.
Acceptance: coherent Zazu identity, professional typography, strong document hierarchy, print/mobile readability.

## Implementation rules

1. Work only in Leano-Jordan/ZazuEMP, on main. Do not create branches.
2. Do not use Render or AMPPS for Zazu.
3. Do not introduce a new CSS file, new token root, global override stack or parallel component system.
4. Modify the canonical owner of a rule. Search for selector collisions before editing.
5. Preserve business rules, routes, permissions, business isolation, user data and all offline/local-first behaviour.
6. Do not remove dashboard artwork, business wallpaper or public photography as a blanket design shortcut.
7. Do not change icon libraries; use the existing local Tabler component and its licence notice.
8. Avoid speculative feature additions. This is presentation and interaction quality work within existing v1 scope.
9. Keep changes small enough to attribute regressions. Do not combine unrelated palette, shell, form, navigation and mobile rewrites in one pass.
10. Update the relevant audit/decision record with root cause, exact files, tests and verification boundary.

## Verification and acceptance

Source-level checks are necessary but not sufficient. For each meaningful visual slice:
- Run relevant existing PHP/Blade, UI and regression tests.
- Build Vite assets and confirm the manifest includes every loaded stylesheet.
- Inspect rendered desktop and Android-sized views.
- Inspect light and dark modes.
- Check keyboard focus, contrast, reduced motion, overflow, form validation, empty/error/success states.
- Confirm offline workflows and local persistence are unaffected.
- Compare before/after screenshots for the changed page family.

If the connected environment cannot render the real application, mark the work SOURCE-VERIFIED / VISUAL-UNVERIFIED. Never claim visual acceptance without rendered evidence.

## Immediate next execution slice

Do not begin with a global palette rewrite. First complete the Blade/component inventory and map each screenshot page to its actual view, shared components and CSS owner. Then choose one high-impact, low-blast-radius slice—preferably the dashboard or one core Work/Quote page family—and implement it through the existing authority. Capture the rendered result before proceeding to the next family.
