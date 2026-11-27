# Zazu Offline Product Parity Standard

**Status:** Binding product requirement and engineering acceptance standard  
**Owner:** Founder  
**Accountable role:** Zazu Engine Director (Jarvis)  
**Applies to:** Windows/PC, Android and any future supported Zazu client  
**Effective:** 2026-11-27  
**Canonical branch:** `main`

## 1. Product decision

Zazu offline mode is the same Zazu application operating without a reachable host or internet connection. It is not a reduced offline dashboard, a separate mini-application, a fallback product or a collection of simplified substitutes.

**The user must not have to learn a second interface when connectivity disappears.**

The product must preserve, to the greatest extent technically possible, the same:

- Application shell, branding, theme, typography, spacing and visual hierarchy.
- Primary and secondary navigation, active states, collapse behaviour and page destinations.
- Page composition, headings, tabs, cards, tables, forms, detail panels, dialogs and action placement.
- Reusable components, field labels, validation language, empty states and feedback patterns.
- Business workflows and route semantics.
- Responsive behaviour and device-specific layout.
- Accessibility semantics, keyboard/focus behaviour and touch targets.

Offline status may add a small, consistent connectivity/sync indicator. It must not cause a wholesale visual redesign or hide ordinary application content.

## 2. Non-negotiable architecture principles

### 2.1 One application, not two UIs

- Reuse the normal application layout, route definitions, page templates, components, styles and interaction patterns.
- Do not build or expand a parallel `offline-workspace` as a substitute for ordinary application pages.
- Do not hide a normal page and replace it with a simplified collection view because a route matches a broad regular expression.
- Shared local-data adapters and offline state are acceptable; duplicated page implementations that drift visually or behaviourally are not the target.
- Offline mode is a runtime/data-source state, not a separate design system.

### 2.2 Route identity must be exact

- Preserve the requested route and its page identity through host loss, browser navigation, reload, cold launch and service-worker fallback.
- Exact cached pages must remain visible and interactive. A broad route-family match must never hide a cached create form, detail page, attachment workflow or settings page.
- Distinguish an exact cached response from a service-worker fallback through explicit response/document metadata that can be tested.
- Do not use referrer heuristics, empty-client-ID interpretation, arbitrary open-window enumeration or history-only simulation as the authority for route identity.
- When the requested page document is not cached, render its normal page composition from the shared application components if that route's offline capability is implemented. If it is not implemented, show a precise, in-context limitation without exposing unrelated pages or data.

### 2.3 One data contract, local-first execution

- Normal page components should consume a consistent domain/data contract. Online requests and offline local persistence/sync are implementations behind that contract, not separate page experiences.
- Locally available business records must render in their normal page locations and relationships.
- Supported create/edit actions must use the same field semantics, validation rules, defaults and workflow constraints as online.
- A local save must be durable before it is reported as saved. Label its state accurately (for example, saved on this device / waiting to sync).
- Queueing, retries, conflict handling and server reconciliation must not duplicate financial or inventory operations or silently discard user work.
- Local data, queues, attachments and sync credentials remain isolated by business and trusted device.

### 2.4 Honest, granular limitations

Some operations genuinely require a server or an external service: live collaboration, owner administration, new device approval, server-side invitations, external payment processing and other explicitly identified operations.

When unavailable:
- Keep the normal page and its usable local content visible.
- Disable or defer only the specific server-dependent control or operation.
- Explain why it is unavailable and what will happen after reconnecting.
- Preserve entered data and drafts.
- Never imply that an external action succeeded when it did not.
- Never present a whole module as unavailable merely because one action within it requires connectivity.

The existing action-by-action capability contract remains the factual implementation inventory. Its current bounded scope is a gap against this product requirement where normal page parity is not yet delivered; it is not permission to permanently ship a lesser interface.

## 3. Visual and interaction parity

Offline acceptance is not satisfied by the presence of text, a route response, or a successful local database write.

For each representative route, compare online and offline at the same viewport and state. The following must remain materially equivalent:

1. Page identity and title.
2. Application shell and navigation structure.
3. Content hierarchy, layout geometry and component placement.
4. Typography, spacing, colour, borders, surfaces, icons and responsive behaviour.
5. Form controls, labels, required indicators, validation and action placement.
6. Tables, cards, detail relationships, pagination/filter state where locally supported.
7. Loading, empty, saved-locally, pending-sync, conflict and error states.
8. Keyboard navigation, focus visibility, semantic labels and touch usability.

Connectivity and sync status may differ by definition. Data freshness may differ and must be disclosed. The rest of the interface must not degrade without a documented technical reason and explicit acceptance.

A visual regression suite must capture comparable online/offline screenshots for desktop and mobile. Assertions must check meaningful geometry and visible components, not merely DOM existence or a `hidden` attribute.

## 4. Required route and workflow coverage

Build an inventory from actual Laravel routes and navigation destinations, not a hand-picked list of only easy offline screens. At minimum, include:

- Dashboard and global navigation.
- Customers: list, create, edit, detail and attachments.
- Work/jobs: list, create, detail, edit, tabs, quotes, costs, travel and attachments.
- Quotes: list, detail, create/edit, line items, acceptance and conversion to invoice.
- Finance: invoices, invoice detail/create, payments, deposits, expenses and relevant reports.
- Suppliers, services/catalogue and their create/edit/detail pages.
- Inventory: items, movements and stock history.
- Purchasing: purchase orders, details, receiving and related expenses.
- Assets: list, detail, create/edit, allocation and return.
- Preparations and event costs.
- Settings: distinguish locally meaningful preferences from server-only account/business administration. Keep local settings usable offline where they do not require the server.
- Backup/export and recovery surfaces: disclose whether an operation is local or host-dependent and never claim completion prematurely.

For each route, record:
- Online route and page template/component.
- Offline document source (exact cache or app fallback).
- Local data dependencies and bootstrap requirements.
- Read, create, update and attachment capability.
- Server-dependent controls and their offline treatment.
- Permission/business isolation.
- Restart survival and reconnect/reconciliation evidence.

A module is not “offline supported” merely because its collection can be displayed in a shared offline list.

## 5. Defect record: route misclassification and hidden normal pages

The 2026-11-27 offline browser acceptance run failed with **20 failures out of 80** (60 passed). Failure clusters included hidden customer/job/attachment forms, incorrect create-page identity and local records not appearing in expected route views.

Source inspection identified a structural defect in `resources/js/app.js`:

- `applyOfflineRouteView()` classifies URLs through broad route-family regular expressions.
- It selects a shared offline workspace collection view.
- It hides normal sections under `.zazu-content`.
- As a result, routes such as `/customers/create`, `/work/create`, job detail routes and purchasing receipt routes can be classified as collection pages and their actual cached page/forms become inaccessible.

This is not just a selector bug. It is a mismatch between a reduced-shell architecture and the required product behaviour. Fixing individual regexes or adding route exceptions is not an acceptable final architecture.

**Required correction:** remove the shared-workspace takeover of normal page content. Keep exact cached pages intact. For uncached routes, render the same normal page composition using shared components and local data adapters. Make the service worker explicitly identify true fallback responses. Retain route-level capability checks and permissions.

The reported 20 failures are a known failing baseline, not proof that the root cause is fully resolved. Re-run the exact-head suite after implementation and record the new counts and logs.

## 6. Engineering execution sequence

### Phase A — establish route and component truth
- Enumerate Laravel routes, navigation links, page templates and current offline route mappings.
- Map each route to its canonical online page composition and local data requirements.
- Identify duplicated offline-only templates and all code that hides/replaces normal content.
- Mark each operation as local, deferred, online-only or not yet implemented.

### Phase B — correct application architecture
- Preserve the normal shell and route-specific page composition under host loss.
- Introduce/standardise a local-first domain data adapter for the existing page components.
- Ensure page components can render from local snapshots without requiring a successful network response.
- Replace broad route-family UI takeover with explicit route/document resolution.
- Mark service-worker fallback responses explicitly; exact cached documents must not be mistaken for fallback documents.
- Keep permission checks and business scoping authoritative in both online and offline paths.
- Preserve form drafts and attachment selectors when connectivity changes.

### Phase C — bring workflows to parity
- Implement missing local persistence and queue semantics per route/action.
- Reuse online validation and domain constraints; do not create a permissive offline-only ruleset.
- Support phone-local attachments with durable bytes, visible upload state, retry and duplicate-safe reconciliation.
- Keep online-only operations individually restricted and clearly explained.

### Phase D — prove parity
- Run route-by-route browser acceptance at desktop and mobile viewports.
- Add visual comparisons and functional tests for normal forms/details/navigation.
- Test exact cached page, uncached route fallback, direct URL, in-app navigation, back/forward, reload, cold launch and host-loss transitions.
- Test saved state, queued state, conflict, quota/storage failure, attachment retry and reconnect.
- Run Laravel, browser, PostgreSQL recovery, static analysis, dependency/security and Windows package checks on the exact same commit.
- Require physical owner-PC and Android tests for host stop, fully disconnected cold launch, force-close/restart, local work, attachment and reconnect.

## 7. Release gates — no partial credit

Offline parity is **NOT ACCEPTED** until all gates pass:

| Gate | Pass condition |
|---|---|
| Product architecture | No secondary reduced offline UI replacing normal application pages |
| Route coverage | Full route inventory classified; every claimed route renders its normal page composition |
| Navigation | Same destinations, hierarchy and active states online/offline |
| Visual parity | Desktop and mobile comparisons pass; no hidden forms, missing controls or layout regressions |
| Functional parity | Every claimed local workflow works with durable local persistence and the same validation semantics |
| Data safety | Business isolation, permissions, queue ordering, idempotency, conflict handling and attachment integrity pass |
| Restart | Cold launch and force-close/restart preserve the local workspace and pending work |
| Reconnect | Safe, duplicate-free reconciliation and clear conflict/error handling |
| Automated CI | Exact-head Laravel, browser, database recovery, quality and security checks pass |
| Physical acceptance | Owner-PC and Android host-loss/restart/reconnect checks pass |

A skipped check is not a pass. Browser emulation is not physical-device acceptance. Documentation, screenshots or a cached page alone are not functional proof.

## 8. Current status at standard adoption

At adoption, the repository's latest known offline browser acceptance result is **60 passed / 20 failed**. The failures include normal page/form visibility and route identity. Therefore:

- Offline product parity: **OPEN / NOT ACCEPTED**.
- Navigation parity: **OPEN / NOT ACCEPTED**.
- Full route/workflow parity: **NOT YET DEMONSTRATED**.
- Physical Android and owner-PC acceptance: **OPEN**.
- Existing supported local mutation contract: remains the factual implementation inventory; it does not constitute full parity.

Do not convert any of these statuses to PASS without exact-head evidence and the required physical acceptance.

## 9. Change control

This standard can only be weakened or narrowed by an explicit Founder decision. Engineering implementation details may evolve, but the product requirement—same Zazu offline—does not change through incidental code, test, documentation or release-note edits.


## 10. Implementation log — Iteration 1 (2026-11-27)

**Status: IN PROGRESS — automated validation pending; physical acceptance not started.**

Changes pushed to `main`:

- Exact cached route documents now bypass the legacy workspace takeover in `applyOfflineRouteView()`. The normal cached page, forms, controls and page title remain in place.
- The service worker returns exact cached pages unchanged. When the Dashboard is used because the requested route is not cached, it injects `<meta name="zazu-offline-fallback" content="1">` into the fallback HTML so the client can distinguish the two cases.
- The offline stylesheet no longer hides command search or section tabs.
- Cache version advanced to `zazu-static-v65` so installed clients receive the updated service worker.
- Added service-worker regression assertions for exact-page preservation and explicit fallback identity.
- Replaced the previous route-workspace-only browser test with a desktop/mobile scenario covering the cached customer-create and job-create pages, their titles, forms, navigation controls and layout geometry.

**Known transitional boundary:** the legacy reduced workspace remains available only when the service worker genuinely has to serve a fallback document, and the Dashboard still retains its existing offline overview. This is not full parity and does not close the requirement. Iteration 2 must move local records and supported actions into the ordinary route components and remove the reduced workspace as a user-facing substitute.

**Validation record:** an initial build caught an over-escaped route-normalization regex; it was corrected. The first service-worker test run then caught an invalid regex in a new test assertion; that assertion was corrected to a literal HTML inclusion check. The current exact-head CI run must pass the service-worker suite, Laravel tests, browser acceptance, PostgreSQL recovery, quality/security checks and Windows packaging before Iteration 1 can be accepted. Physical owner-PC and Android verification remain mandatory.


## 11. Iteration 2 — normal-page data integration inventory

The route-preservation change makes this gap explicit: an exact cached route no longer exposes the reduced workspace as a substitute. The normal page must therefore render the local working set itself.

### Existing normal-page adapters

- **Customers:** `renderCustomers()` reconciles local customer records into the normal customer directory, including local-only records and locally edited cached records.
- **Work:** `renderJobs()` reconciles local jobs into the normal Work register, including sync state and locally saved job details.
- **Work attachments:** the normal Work detail page includes the attachment form and list. The existing offline attachment handler queues file bytes locally for later upload.
- **Create flows:** customer and job create forms are normal Blade pages with offline mutation handlers.

### Existing local collection support that still targets the legacy workspace

The generic `renderOfflineCollections()` renderer currently targets `[data-zazu-offline-collection]` sections in `offline-workspace.blade.php`. The collections include:

- Suppliers
- Services/catalogue
- Inventory items and movements
- Assets and allocations
- Purchase orders and order lines
- Invoices and payments
- Expenses
- Work costs

Quotes have a separate local renderer, but its current `[data-zazu-local-quotes]` target is also in the legacy workspace.

### Iteration 2 implementation order

1. Move quote, supplier, catalogue, inventory, asset and purchasing local records into their existing normal register/list components.
2. Move invoice, payment, expense and event-cost records into the existing Finance and Work cost views.
3. Reconcile local edits against cached server rows by stable server identity; never duplicate a record or silently overwrite a newer local edit.
4. Reuse each page's existing row structure and action semantics. Add sync/conflict state as a compact inline status, not a replacement page.
5. Add route-level tests for both server-backed local edits and local-only records, including reload and reconnect.
6. Once all supported records are visible in their native pages, reduce the shared workspace to a compact sync/health surface and remove its collection-directory role.

### Explicitly read-only until separately implemented

Calendar, search, reports, audit/compliance and export/restore routes must retain their normal cached page identity, but their cached data is not proof of freshness and their server-dependent actions must not be simulated offline. Add a clear connection/freshness state where appropriate; do not silently present stale snapshots as live data.


## 12. Iteration 2 implementation log — 2026-11-27

**Status: IN PROGRESS — current-head automated validation pending.**

Native-page integration now extends beyond Customers and Work:

- `renderOfflineCollections()` can target either a native module register or the legacy workspace.
- Suppliers, Inventory, Assets, Purchasing, Finance invoices/expenses and Work costs now expose native register targets and stable server-row IDs.
- Local-only records are inserted into those normal registers. Locally edited server-backed records reconcile into their existing row and receive an inline sync indicator rather than a duplicate.
- The Quotes page now exposes its native register to the local quote renderer; local quote rows retain version, status and total columns.
- The browser acceptance scenario now primes these pages while connected, disconnects, and checks local records in the native registers. Catalogue remains an explicit fallback gap because its equipment and services tabs have distinct layouts.

A first browser run after Iteration 1 showed that some older tests still expected the reduced workspace to replace the native Dashboard and Work pages. Those assertions were rewritten to verify native page identity and local records in the normal directories. The same run exposed a real layout shift: the connectivity notice was pushing the page down. It is now a fixed overlay so the offline state does not change page geometry.

**Current release gate:** do not accept Iteration 1 or Iteration 2 until the latest `main` commit passes the full browser acceptance suite and all other CI checks. The latest test changes cover the responsive navigation baseline, customer/job create pages, Dashboard, Work detail attachments, normal module registers and quote register. Physical PC/Android verification is still required after CI.
