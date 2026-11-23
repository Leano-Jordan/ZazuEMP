# ZAZU EMP — REGRESSION LEDGER

Known failure patterns and permanent controls.

## REG-001 — Automated PHP write corruption
Automated writing previously corrupted PHP namespace/import backslashes.

**Control:** re-fetch changed PHP files immediately and inspect namespace, imports, declarations and route/controller compatibility.

**Status:** CONTROL ACTIVE

**2026-10-01 recurrence:** a newly added UI regression test briefly received flattened model namespace references during automated repository writing. Director re-read the file, detected the corruption before closure and restored valid imports/usages. This remains a write-integrity control issue, not an application design issue.

## REG-002 — Repository migration vs existing database drift
A repository migration can be correct while an existing local database remains behind.

**Control:** populated-database upgrade verification is a release gate. Never assume source migrations equal local schema state.

**Status:** CONTROL ACTIVE

## REG-003 — Symptom patching
Repeated local fixes can leave the shared root cause intact.

**Control:** two failed corrections on one root cause → FORENSICS.

**Status:** CONTROL ACTIVE

## REG-004 — Historical documentation masquerading as current state
Dated records can contain old heads, statuses and sequences.

**Control:** STATE.md is current control state; historical records stay dated and are not treated as current instructions.

**Status:** CONTROL ACTIVE

## REG-005 — Cross-project context contamination
Generic terms can cause an agent to import concepts from an unrelated project.

**Control:** repository identity lock + context firewall + current-state source hierarchy.

**Status:** CONTROL ACTIVE

## REG-006 — Shared UI collateral damage
Shared tokens/layout/components can affect many screens at once.

**Control:** shared UI changes require blast-radius review, theme/responsive review and Guardian regression checks.

**Status:** CONTROL ACTIVE


## REG-007 — Verification-layer conflation
A failing test, static scanner, CI workflow, fixture, environment or application defect can present as the same red execution result.

**Control:** classify every failure F1–F8 before changing application code using 06_VERIFICATION_AND_QUALITY_ENGINE.md.

**Status:** CONTROL ACTIVE

## REG-008 — Append-only visual token drift
Zazu accumulated repeated visual sweep/root-token layers. Later declarations silently overrode earlier visual decisions, producing inconsistent colour relationships and theme behaviour.

**Control:** one shared light root + one shared dark root for design tokens; visual changes modify the shared authority rather than appending another sweep. Rendered desktop/mobile theme verification remains required.

**Status:** CONTROL ACTIVE

## REG-009 — Temporary branch residue

Ordinary Director execution created multiple non-main branches despite the owner's absolute main-only policy, increasing repository/sync clutter and fragmenting the working state.

**Control:** The owner mandates canonical `main` only. Never create, checkout, switch to, push to, or develop on a non-`main` branch. Every code/test/doc/audit change goes directly to `main`. If direct-main writing is blocked, stop and report the blocker rather than creating a branch.

**Historical inventory (2026-11-05; superseded):** GitHub then reported four branches: `main`, `director/offline-session-boundary`, `dependabot/composer/laravel/framework-13.39.0`, and `dependabot/composer/phpunit/phpunit-13.4.5`. Do not treat that inventory or its comparisons as current.

**Current inventory (2026-11-20, read-only GitHub check):** GitHub reports only `main` and `deploy/render-staging`. The latter points to `1d7af048bb8fc02051ce873fc6e9bc578e3c2f06` (2026-11-12). The comparison snapshot used `main` at `0f0d2f943545c535f41430096aebf1e979f11440`: the legacy ref was 0 commits ahead and 1,529 behind, and the compare endpoint returned no branch-unique files. Treat that ahead/behind count as an as-of snapshot; repeat a live comparison before any future branch action. It is therefore a stale legacy ref, not an engineering target. This session did not create, update, merge, rename, switch to or delete it. Its name alone is not evidence of a current deployment.

**Read-only comparison against current main:**
- `director/offline-session-boundary`: 8 commits ahead / 7 behind. It contains unique business-scoped service-worker cache partitioning and corresponding tests in `public/sw.js`, `resources/js/app.js`, `e2e/pwa-offline.spec.js`, `tests/Feature/PwaOfflineFoundationTest.php`, and `tests/js/service-worker-auth-cache.test.js`. This is not safe to discard without selectively reviewing and porting the business-context isolation work; the branch's older full-file snapshots must not replace newer main security/session work.
- Laravel Dependabot branch: 1 commit ahead / 282 behind; only `composer.lock` differs. Treat as stale dependency automation, not a candidate for wholesale merge.
- PHPUnit Dependabot branch: 1 commit ahead / 282 behind; `composer.json` and `composer.lock` differ. Treat as stale dependency automation, not a candidate for wholesale merge.

**Execution boundary:** The connected GitHub action surface available to this Director session exposes branch search and commit comparison but no remote branch-ref deletion operation. Do not claim the refs have been deleted. Preserve this inventory until the business-context service-worker changes have been selectively reconciled on `main` and the owner-authorized remote ref deletion can be performed through an available GitHub interface/API.

## REG-010 — Role-to-permission drift
A business role can be seeded or assigned in data without a corresponding permission definition, causing silent authorization denial across the application.

Observed 2026-10-01: the populated demo creates role manager, while config/zazu.php has no manager permission map. PermissionService therefore returns an empty permission set for that membership.

Control: every non-owner role referenced by seeders, membership fixtures or production-facing role administration must have an explicit permission matrix and adversarial route coverage. Missing role mapping is a release evidence blocker, not an implicit deny-by-default success.

Status: CONTROL ACTIVE

## REG-011 — AI failure-loop recurrence

A verification failure can recur across cycles while the Director repeatedly reinterprets it as a new task. This causes symptom patching, contaminated experiments and wasted verification cycles.

**Control:** persistent failure case ID + fingerprint + hypothesis ledger + 2-attempt hypothesis budget + 3-cycle case budget + mandatory escalation. Rejected approaches remain recorded.

**Status:** CONTROL ACTIVE

## REG-012 — Offline cache privacy/staleness leakage

A service worker that caches all same-origin images can accidentally retain authenticated/private media and stale business presentation assets on the device.

**Control:** service-worker caching is restricted to public static asset paths under /build/ and /images/. Old Zazu static cache generations are deleted during activation. Private /media paths are never cacheable by this policy.

**Status:** CONTROL ACTIVE

## REG-013 — MySQL restore partial-failure exposure

A restore can partially modify a MySQL database because the restore operation is not equivalent to an atomic SQLite file replacement.

**Control:** before a MySQL restore, stage a pre-restore dump. On failure after database replacement has begun, attempt restoration from that pre-restore dump before reporting the failure. Never activate private-storage replacement before the rollback snapshot exists.

**Status:** CONTROL ACTIVE

## REG-014 — E2E auth-state and responsive-navigation coverage gaps

Browser suites can silently skip authenticated UI coverage when they depend on an externally generated storage-state file; desktop-only locators can also miss controls collapsed into mobile navigation.

**Control:** Authenticate UI E2E tests through the seeded demo account by default, assert current semantic buttons, and explicitly open collapsed navigation before locating mobile actions. Keep only documented device-specific skips.

**Status:** CONTROL ACTIVE


## REG-015 — Resource register right-edge collapse / single-item layout regression

Asset and inventory registers previously regressed into a flex row where useful record content became squeezed while the action cluster consumed disproportionate right-edge space. A single record could therefore look like a large empty container with controls visually detached.

**Control:** asset/inventory rows use an explicit three-region desktop grid (record / state / actions), bounded action forms, left-aligned action groups, and deterministic 1100px/760px/520px/400px breakpoints. Do not restore generic right-justified flex treatment for these registers.

**Status:** CONTROL ACTIVE

## REG-016 — Foreground/surface theme contrast regression

Legacy landing and shared-banner declarations mixed dark-shell foreground tokens with light landing surfaces and dark page text with dark/image-backed surfaces.

**Control:** meaningful text uses an explicit foreground family matched to its surface. Image-backed banners are intentionally dark surfaces with light foregrounds. Light public landing surfaces use dark blue foregrounds. Contrast review covers default, hover, focus and state surfaces.

**Status:** CONTROL ACTIVE

## REG-017 — Workspace-state visual language drift

Page command banners previously used a separate pale-card treatment while the dashboard workspace-state area carried the stronger image-led operational language.

**Control:** shared .zazu-command-band is the page-banner authority and inherits the dashboard image variable, dark readability overlay, explicit light foregrounds and workspace-style state meta panel. Page-specific selectors must not replace this with unrelated light/dark text combinations.

**Status:** CONTROL ACTIVE

## REG-018 — Business logo presentation drift

Business logos are identity-critical. Treating the logo as a small decorative square or allowing arbitrary image sizing makes customer branding look unfinished and can create distorted or cropped marks.

**Control:** shared brand area reserves a 40px identity slot, keeps 72px shell height, uses object-fit: contain, centered positioning and a controlled shell surface. Preserve the supplied logo without distortion/cropping.

**Status:** CONTROL ACTIVE

## REG-019 — Landing navigation/composition geometry drift

Public navigation links, CTAs and later bento items can drift vertically or leave unused horizontal space when individual elements use unrelated sizing or implicit auto-placement.

**Control:** landing navigation uses a common 40px control rhythm; Plan and Delivery explicitly occupy the final 50/50 desktop row; responsive rules reset explicit placement below 920px. Do not reintroduce uneven CTA heights or unbounded final-row auto-placement.

**Status:** CONTROL ACTIVE

## REG-022 — Director visual recovery invariants

**ID correction — 2026-10-19:** This control previously duplicated `REG-017`, which remains assigned to “Workspace-state visual language drift”. The duplicate is corrected to the next unused permanent ID, `REG-022`. Historical references to this visual-recovery control should use `REG-022`.

The 2026-10-05 UI recovery exposed a cluster of regressions caused by later CSS/JS overrides defeating earlier visual decisions.

**Control:** treat these as protected shared contracts:
- `.zazu-command-band` remains an intentionally dark, image-led page-introduction surface with explicit light foregrounds. Late generic light-surface harmonization must never target it.
- The light application palette remains blue-slate and must not drift back to white/near-white as the default shell direction.
- The canonical card/panel primitive owns the restrained 10px/110% glass treatment; later visual sweeps must not remove or duplicate that authority.
- Global desktop search is bounded utility chrome and must not expand the application shell. Mobile search stays a full-width utility row.
- The Zazu Helper remains the established compact mascot size and stays docked to the far-right viewport safe area on desktop, tablet and mobile. It must not follow or move beside Work/Sales navigation popovers. When the mobile drawer is open, it remains non-interactive and slides outside the viewport until the drawer closes; do not reintroduce popover-positioning or generic collision engines.
- Customer quick-create must expose a permissioned path to the full customer editor after creation.
- Customer forms present identity and primary contact information before business, billing and tax details.
- Native customer dialogs must have an explicit viewport-centering contract.
- Any broad visual change touching these contracts requires source re-read, regression review and rendered desktop/mobile/light/dark acceptance before closure.

**Status:** CONTROL ACTIVE

## REG-020 — Calendar active-route drift / mobile navigation dead end

A destination can remain present and permission-gated while becoming operationally unreachable when its navigation-group ownership changes without updating the group's active-route predicate. Calendar was moved into Work, but Work did not recognize `calendar.*`; mobile active-group auto-expansion therefore had no target.

**Control:** whenever a destination is moved between primary navigation groups, update and verify all three contracts together: route ownership/permission, active-group predicate, and responsive auto-expansion. Add a direct-route browser regression that proves the destination remains reachable from its owning group. Do not solve ownership drift by duplicating destinations across groups.

**Status:** CONTROL ACTIVE

## REG-021 — Cross-theme foreground/surface mismatch

Light-mode and dark-mode tokens can be semantically reused in the wrong role, producing light text on light surfaces or dark text on dark surfaces. This is especially dangerous when action fills switch between themes.

**Control:** use semantic foreground tokens (`--zazu-primary-ink`, `--zazu-danger-ink`, `--zazu-success-ink`, etc.) rather than hard-coded white/dark text on theme-variable fills. Contrast-sensitive shared components require both-theme regression assertions. Standalone surfaces such as Offline must declare a matching `color-scheme` and keep foreground/background relationships internally coherent.

**Additional control — dashboard theme aliases:** dashboard-scoped `--dash-*` aliases must resolve to shared `--zazu-*` tokens in both light and dark modes. Browser assertions must check computed values under the theme-qualified selector, not only source order. Review selector specificity before assuming a later visual declaration wins.

**Status:** CONTROL ACTIVE


## REG-023 — Offline authenticated page cache missing business context

A service worker keyed authenticated HTML only by user, while the local IndexedDB workspace was keyed by business. Switching business under the same user could leave previous-business pages available offline.

**Control:** cache identity now includes user + business; rendered pages require both IDs; a successful business switch clears the previous cache before redirect; context generations reject late writes.

**Status:** FIXED ON MAIN — exact-head CI and physical PC/mobile acceptance pending.


## Identifier correction note

The offline authenticated-page cache/business-context control was previously assigned duplicate identifier REG-015. REG-015 remains reserved for the resource-register right-edge layout control. The offline cache control is corrected to REG-023, the next unused identifier after REG-022. No application behavior changed.


## REG-024 — Offline existing-record stale snapshot overwrite

An offline mutation may be based on an older business-record snapshot than the server currently holds. Stable entity identity, ordered mutation delivery and idempotent replay do not detect that the record itself changed after bootstrap.

**Control:** every existing-record offline update accepted by a server domain handler carries an authoritative server revision. Relevant online and offline writes advance that revision transactionally. The server checks the expected revision under lock and preserves stale local and current remote values in a conflict. Same-device queued edits chain revisions; an intervening external write breaks the chain. Conflict retry uses a new mutation ID and an explicitly reviewed latest base revision.

**Status:** CLOSED — exact-head Laravel/PostgreSQL/browser and paired-device stale-write regressions passed on 51a75eb. See CASE-ZAZU-0033 / ENG-DATA-003. The full offline sync workflow matrix remains a separate OPEN gate.

## REG-025 — Receiver bootstrap acknowledges an incomplete bounded snapshot

**Failure:** The receiving device pulls a contiguous delivery batch, fetches the ordinary bounded business bootstrap, persists it and acknowledges the batch. When a delivered aggregate falls outside a capped collection (or a recently changed aggregate is hidden by static display ordering), the receiver can acknowledge the change without ever refreshing that record.

**Control:**
- Include the exact receiver delivery sequences on the bootstrap request.
- Resolve delivery targets only within the authenticated device, business and stream.
- Add both primary and related aggregate snapshots for those sequences, including command-side parent effects.
- Keep normal working sets bounded, but promote recently revised aggregates into the next refresh window using sync-identity update time (fall back to record `updated_at`) and retain the existing display order.
- Persist the complete snapshot before acknowledging the contiguous delivery prefix. A failed or rejected bootstrap must leave the cursor untouched.
- Advance the precached offline client cache when this code changes.

**Regression controls:** 500-record supplier-window test, recently revised online supplier promotion, exact delivery inclusion and non-recipient isolation; cross-device commercial and receipt/inventory reconciliation; browser pull → targeted bootstrap → acknowledgement ordering.

**Status:** CLOSED — exact-head source `559e66b87de8617f967b722b55b0fbfd9b116495` passed Laravel (632 passed / 1 skipped / 4,388 assertions), PostgreSQL recovery, desktop Chromium (59), mobile viewport (7), phone-local attachment journey (1), PHPMD, Zazu Quality, Director Health, Push-on-main, Composer and secret checks. SonarCloud was skipped. The broader V1 sync matrix and physical-device acceptance remain open.

## REG-026 — Bootstrap authorization reuses a stale middleware device snapshot

**Failure:** The bootstrap endpoint relied on the SyncDevice model attached by authentication middleware. Revocation or bearer-token replacement between middleware lookup and bootstrap could leave the old request snapshot authorized to receive business data. The old last-seen write also occurred before the business lock.

**Control:** Bootstrap now takes the business lock first, re-reads the active destination device under row lock, checks the persisted bearer-token hash against the authenticated request snapshot, updates activity and builds the bootstrap in that locked transaction. A revoked/replaced device receives 401 before snapshot data is returned.

**Regression controls:** Direct stale-snapshot tests for revocation and credential replacement. Existing pull and acknowledgement stale-snapshot protections are retained.

**Status:** CLOSED — exact-head source `559e66b87de8617f967b722b55b0fbfd9b116495` passed Laravel (632 passed / 1 skipped / 4,388 assertions), PostgreSQL recovery, desktop Chromium (59), mobile viewport (7), phone-local attachment journey (1), PHPMD, Zazu Quality, Director Health, Push-on-main, Composer and secret checks. SonarCloud was skipped.


## REG-027 — Sync endpoints trust stale middleware device snapshots
A sync request can pass middleware authentication, then continue after the durable device has been revoked or its bearer credential has been replaced. Conflict listing/resolution and attachment endpoints previously did not all revalidate the device inside their operation transaction; a stale token could also target a replacement credential during self-revocation.

**Control:** sensitive sync reads and writes re-lock business → device inside their operation transaction, recheck active status, and compare the authenticated token hash with the persisted hash. Never treat the middleware's Eloquent model as current authorization state for a later transaction.

**Status:** CONTROL VERIFIED — exact-head Laravel (670 passed / 1 skipped / 4,552 assertions), PostgreSQL migration/recovery, PHPMD and Psalm passed on `7d4b097`; offline-browser and Windows package acceptance were still in progress at the checkpoint.

## REG-028 — Offline customer metadata must survive deferred photo changes

A customer create/update form may include supported text/contact metadata alongside an unsupported profile-photo upload or removal. Rejecting the entire mutation discards valid work and contradicts the operator-facing instruction to save details first.

**Control:** detect the unsupported photo operation without aborting the customer mutation. Queue only the supported business-scoped metadata, omit file bytes and removal intent, and show an explicit notice that the details were saved while the photo change remains pending an online update.

**Regression:** offline browser coverage selects a profile photo, submits the customer, verifies local state and pending mutation metadata, asserts no photo fields are queued, and checks the partial-save notice.

**Status:** CLOSED — exact-head Laravel (744 passed / 1 skipped / 5,230 assertions), desktop offline browser (64), mobile viewport (7), host-outage, phone-local attachment, PostgreSQL migration/recovery and Zazu Quality passed on `c57bbd72`. See CASE-ZAZU-0045 / ENG-JS-002. Customer photo upload/removal itself remains online-only.
