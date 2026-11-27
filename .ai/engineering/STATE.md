# Zazu EMP — Current Engineering State

**Last reconciled:** 2026-11-27
**Repository:** Leano-Jordan/ZazuEMP
**Canonical branch / write target:** `main` only
**Latest broad verified checkpoint:** `dbda546d677dd80c99bda83bfcdfaa4dc46e8239` remains the last broad all-workflow acceptance candidate; later changes must not inherit its PASS status.
## Director reconciliation — 2026-11-27 — tier source mapping and terminology

**Latest repository work:** capability matrix and first-pass source map are committed on `main`. Latest known documentation commit: `5325656524253979731e7f85c10743a674c76c49`. Latest application/UI/test commit in this batch: `1921eb94b108237f32747157ba1a829ba775e01f`.

- The matrix now records the intended capability progression and a source-evidence snapshot. Basic customer/job and finance workflows have existing source paths; offline support remains route/workflow-specific and acceptance-open.
- Intermediate supplier/purchasing/receiving, inventory/assets and finance/costing have existing online foundations. Offline parity and full commercial reconciliation remain separate acceptance gates. Team role foundations exist; assignment/workload/advanced controls are incomplete.
- Professional multi-location, advanced analytics/capacity planning, automation, integrations and API capabilities were not evidenced in the first-pass source search and remain deferred/not evidenced, not shipped claims.
- Entitlement code currently resolves the tier ceiling and clamps presentation selection; it does not implement module-level gates or customer/job/user/storage quotas.
- Customer-facing UI now says Professional. Internal `advanced` and legacy `solo` identifiers remain intact for signed licence and stored-preference compatibility.
- The previous four-tier price proposal is explicitly historical; the old customer terms draft is withdrawn. No prices, billing terms or numerical limits are approved.
- **Verification:** exact combined-status lookups returned zero status entries for the UI/test commits and latest documentation commit. No PASS is claimed. The Laravel suite, browser suite, quality/security and Windows package checks still need exact-head confirmation. Rendered UI and physical PC/Android acceptance are unverified.
- **Next target:** complete adversarial source/test mapping for each capability row, with particular attention to existing active work, downgrade, queued offline mutations, reconnect and permission boundaries. Then return any proposed numerical capacity boundaries for explicit Founder approval. No feature gate or quota implementation is authorised by this matrix.

## Director update — 2026-11-27 — Basic / Intermediate / Professional model

**Product decision:** DEC-030 records the Founder-approved customer-facing levels Basic, Intermediate and Professional. The canonical design matrix is `docs/ZAZU_THREE_TIER_CAPABILITY_MATRIX.md`.

- Basic is intended to support genuine small-business operation, not a crippled demo.
- Intermediate adds connected operational capacity across suppliers, purchasing, receiving, inventory/assets, costs, profitability, reporting and team collaboration.
- Professional adds advanced oversight for complex operations, including multiple locations, analytics, capacity management, advanced administration, automation and integrations where implemented.
- Reliability, supported offline workflows, security, data integrity, local backup/export and access to existing customer-owned records are shared foundations across all levels.
- No prices, billing terms or numerical quotas are approved. Capacity dimensions are design candidates only; no limits or feature gates have been enforced.
- Downgrade rules must preserve historical records, existing active work, exports and durable offline queue entries. Role/permission and business-isolation checks remain independent.
- The customer-facing third-tier label is now Professional in configuration, onboarding, preferences and Business Settings. The signed/stored internal identifier remains `advanced`; no token or database migration was attempted.
- The product specification and commercial requirements were reconciled; the four-tier pricing document is explicitly historical. The operating profile, offline licensing notes and readiness register now point to the new capability model.
- **Changed commits:** matrix `72d811d`; product specification `114db7f`; decision log `a9ba1e4`; historical commercial requirements marker `0d0906e`; UI label/config/tests `4c404e2` through `1921eb9`; operating profile/licensing/readiness reconciliation through `e405d5b`.
- **Verification:** GitHub combined status lookup returned no status entries for the UI/test commits or latest documentation commit. No exact-head PASS is claimed. Laravel, browser, quality/security and Windows package workflows must be checked after the push; rendered UI and physical device behaviour remain unverified.
- **Next target:** map every proposed tier capability to current source and tests as IMPLEMENTED / PARTIAL / MISSING / DEFERRED. Then define safe, non-numeric capacity enforcement candidates and downgrade behaviour. Do not implement feature gates or quotas until the Founder approves exact boundaries.

**Repository HEAD at this state snapshot:** `40692998d43575bb1383615e2f986da14d91ae2d` (a subsequent documentation-only parity-standard update followed). **Latest application/test source head:** `be3457b3a0e7057010492ff8df700aeac90d001d` on `main`. The latest implementation removes the reduced offline-workspace component, preserves exact cached native pages, adds explicit fallback identity and recursive route-graph priming, and integrates local catalogue records into the normal Services & Prices tabs. The latest service-worker route-completeness correction is `7661bbccfdbbda8fecf76215f45926b225bcf855`; latest application integration is `be3457b3a0e7057010492ff8df700aeac90d001d`. The user reported the pre-implementation baseline checks green; exact-head Laravel/browser, quality/security and Windows package workflows for this new batch are queued/pending and must not inherit that earlier PASS. SonarCloud remains skipped.

This is the concise live state register, not a session diary. Detailed incident evidence belongs in FAILURE_CASES.md, durable decisions in DECISION_LOG.md, recurring regressions in REGRESSION_LEDGER.md, and historical implementation evidence in dated audit/release records.

## Jarvis execution update — 2026-11-27 — preparation parity slice

**Latest application source commit:** `d832b3a03736e3c45e11e215abecc0a06809461d`.  
**Latest regression-test commit:** `5f332bd82d52aaaa9aba1516af35de9a366c6553`.  
**Latest repository documentation checkpoint before this state update:** `40692998d43575bb1383615e2f986da14d91ae2d`.

- Preparation is now wired into the native `[data-zazu-offline-collection]` register on its existing page. Server rows reconcile by stable identity; local-only rows render in the same list.
- Existing Open / Blocked / Ready controls are offline-aware. Existing records use update mutations and retain their local identity; server-backed rows are not recreated. Status chips reflect the local record state.
- Travel calculation creation has no local collection or offline mutation handler. Its form now explicitly discloses the live-connection requirement and blocks submission when the origin is unreachable, preserving the entered values on screen. Travel index remains a cached snapshot only.
- Laravel source-contract tests cover the Preparation native register/status queue and the Travel connection-dependent guard.
- **Validation:** exact-head CI results are not available in the current GitHub workflow lookup (no status checks/workflow runs were returned for the latest commit). Treat the code as implemented but unverified until the repository's actual build, Laravel, browser, PostgreSQL, quality/security and Windows package workflows report results. No PASS is inherited from earlier commits.
- **Physical acceptance:** desktop and Android preparation status change, reload/restart and reconnect remain open. Travel remains online-only until its own durable local contract is implemented.

## Current Director reconciliation — 2026-11-27 — native offline parity

**Authority:** this section supersedes earlier statements below that describe a shared offline-workspace UI. The term "workspace" in the storage/security records refers to the business-scoped local data store, not a separate user interface.

### Offline architecture now on main
- The broad route-family UI takeover has been removed. Exact cached pages keep their normal server-rendered route identity, page content, forms and controls.
- The reduced `resources/views/components/offline-workspace.blade.php` component has been deleted and its layout include, runtime overview renderer and workspace-specific CSS removed. There is no secondary collection interface in the authenticated DOM.
- The service worker returns exact cached documents unchanged. Only a true cached-Dashboard fallback receives `zazu-offline-fallback` metadata; the client displays a connection-required state inside the normal shell, preserves navigation and identifies the requested route.
- Offline page priming follows the signed-in user's same-origin navigation graph. It excludes APIs, public/session-changing endpoints, private media and static assets; it is capped at 100 routes and reports success only if every discovered route is cached and neither client nor worker truncated the graph.
- Local records are integrated into native Customers, Work, Quotes, Suppliers, Services & Prices, Inventory, Assets, Purchasing and Finance pages. Catalogue records now render in the native Hire & Rental / Services & Products tabs. Supplier and Inventory use their normal-page inline local editors.
- Browser acceptance has been rewritten to use native routes, not the deleted workspace. The uncached-route case now checks the honest connection-required state.

### Verification boundary
- The pre-implementation baseline was reported green by the Founder. The current source head `be3457b3` has new application, service-worker, Blade, CSS and E2E changes; its exact-head Laravel/browser, quality/security and Windows package workflows are queued/pending at reconciliation time.
- The earlier browser baseline on `ff38b873` was 60 passed / 20 failed. Those failures motivated the correction; do not carry that failure count forward as the current result, and do not claim the new suite passes until its exact-head run finishes.
- No physical owner-PC or Android verification has been performed for this new implementation batch. Exact cached route behaviour, disconnected cold launch, force-close/restart, local work, attachment opening and reconnect remain owner-device acceptance gates.
- Offline parity issue: [#80](https://github.com/Leano-Jordan/ZazuEMP/issues/80) remains OPEN.

### Next engineering sequence
1. Complete native local-data and form coverage for preparation, travel, job-linked operations and remaining route families.
2. Verify record editing/reconciliation, no duplicates, revision conflicts, idempotent queueing and restart persistence across each supported module.
3. Verify phone-local attachment bytes and retry/open behaviour on physical Android.
4. Add visual parity assertions beyond geometry checks for desktop and Android viewports.
5. Complete account/business transition, permissions, stale-data and sync-conflict acceptance.
6. Run the full Laravel/browser/PostgreSQL/quality/security/PHPMD/Windows suite on one exact commit; then perform PC and Android physical acceptance.

### Broader V1 disposition
- Formal evidence-maturity baseline remains **71/100**; no uplift is claimed from this implementation batch.
- V1 remains **NOT READY**. The remaining release gates include physical device acceptance, intended-PC installation-wide recovery, populated commercial quote-to-payment-to-profit reconciliation, POPIA operational sign-off, and ZAZU name clearance.
- Current owner/legal decisions remain unchanged: Founder personally owns current IP; Rosscore Labs is not registered; ZAZU name clearance is unresolved; Information Officer registration and privacy contact setup are incomplete; Privacy/Terms are review drafts; no production host/provider is provisioned.
- DEC-019 remains an accepted architectural constraint: a trusted browser profile shares the local business data and sync capability; IndexedDB is not application-encrypted and same-origin script execution can access it. Do not imply that deleting the secondary UI changes this storage threat model.

## Founder-directed trust-surface work — 2026-11-26

Accepted direction and execution record: [DIRECTOR_TRUST_SURFACE_EXECUTION_2026-11-26.md](../../docs/DIRECTOR_TRUST_SURFACE_EXECUTION_2026-11-26.md). Founder-provided public-credibility baseline is **22/40 (55%)**, judgement-based and separate from the formal **71/100 evidence-maturity score**. Work order: (1) accurate public trust surface, (2) source-derived workflow demonstration, (3) authorised non-commercial private pilot with synthetic data by default, then (4) bounded CSS polish after critical release gates. No ads, charging, public pricing, public beta, uptime/SLA promises or broad redesign.

Important identity/claim constraints: Zazu remains an event management platform, not a catering product. “From enquiry to profit” is an accepted workflow-story direction, not proof that every stage is production-accepted; preserve the current V1 mandate promise until formally changed. Do not imply native WhatsApp integration. Rosscore Labs is not yet registered/current owner; the Founder personally owns current IP. ZAZU name clearance is unresolved/high risk. Information Officer registration is incomplete; Outlook contact is provisional; no production hosting or third-party processor is provisioned. Privacy and Terms remain review drafts, not effective public legal notices. Status page is deferred until real monitoring exists.

Latest relevant application changes include the public trust surface, the 5-second navigation timeout/cache v64 correction, and exact-route offline fallback. Browser evidence rejected empty-clientId, live-window and referrer heuristics. The worker now always prefers the exact cached destination, including cold launch, and uses Dashboard only when that URL is not cached. The first exact-route browser run reached 79 passed / 1 failed: the correct cached Work detail route loaded but had no offline workspace shell because the markup existed only in Dashboard. That shared-shell approach was subsequently rejected. On 2026-11-27 the reduced component was deleted; exact cached native pages are now the only normal offline UI. The true fallback is a connection-required state inside the same application shell. Exact-head workflows are pending; no PASS is claimed. The cross-tab logout E2E accepts the intentional production confirmation dialog. Earlier CI findings (stale v63/1.8-second assertions, incorrect registration replay destination, and public accent contrast 3.79:1) were corrected; public teal now measures 5.81:1 against #f4f7f8. Public About, Security, Contact, What's New, Privacy review and Terms review pages are implemented as public GET routes in the existing Laravel app; the landing footer links to them. Privacy and Terms are explicitly non-effective review copies. Contact uses the provisional owner-approved Outlook address and states it is not a dedicated support/privacy desk. No public status page, uptime promise, pricing or commercial availability claim was added. Details: `docs/DIRECTOR_TRUST_SURFACE_EXECUTION_2026-11-26.md`.

Navigation defect: the 1.8-second timeout race was raised to 5 seconds (cache v64), and the over-broad Dashboard preference was removed. Empty-client, live-window and referrer heuristics were rejected. Current code always prefers the exact cached route and falls back to Dashboard only when that route is not cached. Cold Work-detail acceptance exposed the weakness of the Dashboard-only reduced workspace. The later correction removed that interface entirely and requires native cached route pages. Exact-head browser acceptance and Founder-device verification remain pending. Do not mark navigation closed. Detailed evidence: `docs/DIRECTOR_NAVIGATION_RECOVERY_2026-11-26.md`.

Active adversarial authentication audit remains OPEN. AuthenticationSessionSecurityTest passed on `32dac44`, including stale pre-authentication cookie replay after login/registration and the initial late database-session write interleaving regression. The test was strengthened on `2e385e7` so request A and request B use independent handler instances; that strengthened variant awaits exact-head CI. The handler-level test confirms a deleted database session is not recreated by a late save; it does not cancel an already-authorized domain mutation. Concurrent in-flight domain mutations and cross-device reset/logout races remain unproven. Continue the audit without displacing installation-wide recovery, physical offline and commercial acceptance gates. No branch creation; all work remains on `main`.


## Active security investigation — 2026-11-26

Founder-directed adversarial authentication audit remains OPEN. The implementation hardens phone pairing to 12-digit APP_KEY-derived indexed digests, a single-row provisioning lookup and a 10/minute IP cap; pairing creation and provisioning are rate-limited, and digests are invalidated on revocation/password reset. Login, registration and password reset now destructively rotate the prior session ID, with exact-feature regressions confirming the previous cookie-backed session row is deleted and flash state survives. Device revocation code is decomposed into auditable credential-cleanup and event-recording helpers. Settings now explicitly warns that a shared OS/browser profile shares locally stored business data and trusted sync capability; this is a mitigation/disclosure, not a fix for DEC-019. Exact-head CI is green, but the independent adversarial tracks remain open; audit remains OPEN. Scope and source-derived checkpoint: [DIRECTOR_ADVERSARIAL_AUTHENTICATION_AUDIT_2026-11-26.md](../../docs/DIRECTOR_ADVERSARIAL_AUTHENTICATION_AUDIT_2026-11-26.md). The six mandatory tracks are session fixation/concurrent account transitions; browser credential leakage/XSS/backups; pairing-code brute force/device takeover; cross-business token substitution/stale replay; password reset/owner removal/device revocation races; and offline logout/account switching/queued transaction preservation. Audit is OPEN; source inspection is not a penetration-test PASS. Preserve DEC-019 and the business-scoped offline queue; do not weaken controls or clear user data as a test. Continue on main only, no branches.

## Product identity and scope

Zazu is an **event management platform**, not catering software. The event is the centre of the operating model. Business capabilities describe services and resources an operator brings to an event; catering, equipment hire, sound/AV, décor, rentals, baking and photography are examples, not product boundaries. The strategic direction is to help plan, coordinate and carry out different event formats. V1 claims must remain limited to workflows actually implemented and accepted.

The current Laravel modular monolith, event/job-centred records, business isolation, PWA/service-worker shell, IndexedDB workspace, durable mutation queue, phone-local attachments, sync, Windows packaging and recovery architecture are to be preserved. No rewrite or speculative module expansion.

## Non-negotiable operating constraints

- main is the only write target. Never create branches or mutate other refs.
- Zazu does not use Render or AMPPS; do not reintroduce their deployment paths.
- Never reset, clear, replace or modify business data or verified backup files during engineering.
- Never perform an irreversible restore without explicit authority.
- Keep Dashboard free of phone pairing UI. Owner-controlled pairing remains in Settings → Phone sync.
- Automated browser/offline viewport checks do not count as physical Android acceptance.
- Tests are evidence, not a progress metric. Never weaken tests to obtain a pass.
- DB-001 is closed as an historical, unreproduced test-time anomaly. Do not reopen it without recurrence on current main or materially new evidence.

## Historical verified automated evidence — not current HEAD

- **Last fully verified offline architecture checkpoint:** `4a5bedfcbb8b59e09de5398694e5050cc7a61de7` — Laravel **765 passed / 1 skipped / 5,471 assertions**; coverage PASS; PostgreSQL migration/recovery PASS; desktop offline browser **75 passed**; mobile offline Work/visual **11 passed**; mobile stopped-host **1 passed**; phone-local attachment **1 passed**. The mobile header geometry and atomic acknowledgement transaction guards are included.
- **Current source candidate:** `8477f065d947375d0fc81c99b04efb313d0299c2`. The storage-protection slice adds explicit `StorageManager.persist()` request, persisted-state/quota reporting, a 90%-capacity warning, explicit `QuotaExceededError` “not saved” feedback, Settings and offline-workspace status panels, and service-worker cache v62 migration. The latest change after the first test run only re-applies the browser API mock in the new Dashboard document.
- **Latest current-head Laravel workflow:** `45787226043` — Laravel tests and coverage are PASS (**765 passed / 1 skipped / 5,471 assertions**); PostgreSQL migration/rollback/reapplication/registration/backup/restore smoke PASS. The offline browser job is still in progress and is the remaining gate for this candidate.
- **Storage browser test iteration:** predecessor `8cfd530` completed **76 passed / 1 failed**. The single failure was test-fixture-only: the test mocked `navigator.storage` on Settings, navigated to Dashboard (a new document), then expected the mock to persist. Commit `8477f06` re-applies the mock on Dashboard before asserting the offline panel. Do not count the storage E2E as accepted until run `45787226043` finishes green.
- **Current Zazu Quality:** run `45787226126` on exact candidate `8477f06` — application PASS, formatting PASS, secret scan PASS, **Composer security audit PASS**. This is the quality workflow that closes the previous Symfony advisory blocker.
- **Dependency security correction:** commit `43e69e623aafb43e1159e8a0664d729764575844` updates the affected Symfony lock entries to **7.4.24**. Zazu Quality run `45787154093` and current-head run `45787226126` both report Composer audit PASS. No advisory suppression was added.
- **Static/security/build evidence on the same application source (candidate `43e69e6`; later commits are test-only):** PHPMD `45787154030` PASS; Psalm security scan and SARIF publication `45787153999` PASS; Push-on-main CodeQL `45787226691` on current candidate PASS; Windows V1 package, encrypted/legacy archive verification and fresh install `45787153987` PASS; populated upgrade/rollback `45787154081` PASS; Director Health `45787154036` PASS.
- SonarCloud `45787226116` is skipped, not passed.
- None of these automated results constitutes physical Android visual, cold-launch, restart or reconnect acceptance.

## Previous evidence retained as historical

Earlier exact-head runs on commits `66f7392`, `f6a9b3c` and `a3ec152` remain useful historical checkpoints. Their counts and run IDs are not the current acceptance result. The 13 Nov / 21 Nov / 22 Nov full audits are preserved as dated snapshots and must not be read as current-head status.
 
## Confirmed open findings and release gates

### 1. Physical Android offline acceptance — OPEN

Automated offline navigation is now passing. The owner must still accept disconnected cold launch, route isolation, local writes, force-close/restart, reopening phone-local attachments and reconnect reconciliation on the actual Android device. Connected PC/phone operation previously passed; do not repeat that setup.

### 2. Encrypted business archive restore — CLOSED for the Rosscore archive; owner-accepted

On 2026-11-26 the Founder confirmed that the original **2.20 MB encrypted Rosscore business archive restored successfully through the live Zazu application** and that everything from the backup is present in the restored business workspace. This is the real-world owner acceptance that was previously missing; it supersedes the earlier “owner test failed / root stage UNKNOWN” status for this archive.

The correction addressed a confirmed Windows process-boundary defect: PHP upload limits configured on the Artisan parent process were not inherited by Laravel's child HTTP worker. The supported launcher now creates a project-local PHP INI scan file and passes `PHP_INI_SCAN_DIR` through Laravel's Windows server process allowlist, with a runtime signature that triggers restart of an older Zazu worker. The Founder retried after the correction was deployed and the server restarted, then confirmed the live restore and restored contents.

The Founder explicitly clarified that both workspaces were unpopulated. Target occupancy was not the cause of the failure. The empty, unpaired destination requirement remains a deliberate safety contract. The original request-validation incident reference was `3feb3a06-e743-49e7-9a7d-4dc5e77b68a7`; no separate server-log evidence was captured proving the precise historical PHP upload error code, so record the PHP worker configuration defect as the corrected implementation cause, not as a log-confirmed code for that exact request.

The encrypted path uses Sodium secretstream XChaCha20-Poly1305 with Argon2id, private temporary staging, strict ZIP/manifest allowlisting, business ownership validation and transactional restore. Read-only verification, automated encrypted/legacy archive checks and the live owner-confirmed restore now provide complementary evidence. Detailed operator guidance and the acceptance record are in [RELEASE_BACKUP_RESTORE_RUNBOOK.md](../../docs/RELEASE_BACKUP_RESTORE_RUNBOOK.md#founder-confirmed-business-archive-recovery--2026-11-26).

**Separate gate remains OPEN:** installation-wide backup/restore recovery on the intended owner PC is not the same workflow and is not closed by this business-level archive restore.

### 3. Installation-wide recovery — OPEN

Automated backup/restore, rollback, PostgreSQL recovery smoke, Windows packaging and fresh-install checks pass. A controlled full installation recovery drill on the intended owner PC remains mandatory and unaccepted.

### 4. Security, commercial and legal gates

- DEC-019 remains a documented **HIGH** trusted-device shared-profile risk: anyone with access to an owner-approved shared browser profile can inherit its approved offline workspace/mutation capability. Do not claim per-staff offline identity on that trust model.
- Populated quote-to-payment-to-actual-cost/profit reconciliation remains open.
- POPIA operational controls and ZAZU name clearance remain open.
- **Symfony dependency security — RESOLVED:** the five advisories previously reported against Symfony 7.4.23 / 8.0.15 were addressed by lockfile update commit `43e69e6` to Symfony **7.4.24**. Composer security audit passed in Zazu Quality runs `45787154093` and `45787226126`; no advisory exception or suppression was added.
- PHPMD passed on current verified head `e564ec24`. Psalm taint analysis and SARIF publication passed on earlier candidate `cb17492e`, but were not rerun on the current source head; do not report that as current-head evidence. SonarCloud is skipped.
- No independent penetration test has been completed; automated scanners and source review are not a penetration test.

## Cleanup completed on main

- Removed the retired Render/Docker deployment stack and its Docker-only Hadolint workflow.
- Removed an unused legacy offline-attachments script, two generated placeholder tests, a stale Claude handover file and an auto-downloading/self-committing vendor media workflow.
- Reduced STATE.md from approximately 331 KB / 2,500 lines to this concise evidence-led register. Historical audit/decision/failure records remain preserved and are explicitly identified as historical.
- Reconciled Zazu product identity across README, formal product specification, Composer metadata, manifest, app footer, Director mandate and chat bootstrap.
- Removed contradictory DB-001 active-blocker guidance and stale Morpheus role hierarchy from operating bootstraps.
- The existing deploy/render-staging ref is preserved as read-only historical state; no non-main ref was modified and no branch was created.

## Execution order — aligned to the binding V1 mandate

1. **Gate 1 — offline continuity and session/device boundaries.** Close current-head CI for the pairing UI and session-transition hardening; continue focused cross-tab, stale-token, logout/account-switch and queued-write checks. Physical Android cold launch, restart, attachments and reconnect remain separate owner acceptance.
2. **Gate 2 — recovery and installation.** The Rosscore business archive restore is owner-accepted and closed. Complete the separate intended-PC installation-wide recovery/key-custody drill; do not conflate it with business-level export/import.
3. **Gate 3 — commercial workflow integrity.** Reconcile the populated quote → acceptance → invoice → payments → purchasing/receiving → actual costs → profit path against records, documents and reports.
4. **Gate 4 — security, privacy and brand.** Continue adversarial tenant/session/device/file checks; resolve and verify security-result publication; keep the DEC-019 shared-profile risk explicit; obtain POPIA operational and ZAZU name-clearance decisions from the Founder.
5. **Gate 5 — usability, performance and pilot operations.** Complete human-eye responsive acceptance, supported-hardware baselines, onboarding and release operating instructions.

Physical-device, owner-PC recovery, populated-business and legal/brand acceptance remain separate Founder gates. Current code and automation evidence cannot substitute for those approvals. This ordering follows [the binding Commercial V1 Mandate](../../docs/ZAZU_ENGINE_DIRECTOR_COMMERCIAL_V1_MANDATE.md).

## Founder physical offline observation — 2026-11-26 (new evidence)

The Founder reports that with the Zazu PC host OFF, the Android phone successfully opens offline workspace pages and those pages render locally available business data. This is a material improvement over the previously observed frozen last-online Dashboard / all-sections-on-one-page failure. The offline-state banner is visible and communicates that the server is unavailable, locally saved records remain on the device, and only supported offline screens are available. Follow-up screenshots exposed a narrow-column mobile header defect. Source review also confirmed that Settings is intentionally excluded from the offline route allowlist because its current settings functions are server-dependent; do not present this as a missing supported offline route.

**Evidence classification:** OWNER-OBSERVED PARTIAL PASS for phone-side offline route/page rendering with local data while the PC host is off. This is not a full offline acceptance pass. The PC being off is explicitly recorded; do not ask the Founder to repeat that condition or basic connected PC/mobile setup.

**Remaining engineering and acceptance work:** the offline launchpad header has been corrected and browser-verified; unsupported Settings remains online-only. Continue with browser-managed storage-retention hardening, local persistence/restart, supported local mutations, attachment access and reconnect reconciliation. Preserve the existing offline-state banner and its honest capability limits.

**Owner interaction rule:** treat this observation as durable acceptance evidence. Do not ask the Founder again whether offline pages load, whether they contain local data, or whether the PC was on. Ask for another physical check only when engineering has reached a new, specific acceptance step that cannot be established through source inspection or automation; state exactly what new evidence is needed and why. Never repeat a previously completed setup or test.

**Gate status:** physical Android offline acceptance remains OPEN pending a post-fix phone visual check and the remaining distinct checks. The prior all-pages-collapse/frozen-dashboard symptom is no longer the current owner-reported behaviour. The mobile header correction is now automated-browser-verified; continue refining the existing architecture rather than restarting it.

## Founder mobile screenshot review and responsive-header regression — 2026-11-26

The Founder supplied two further Android screenshots (09:48 and 09:49) showing the current offline workspace. Visible local-state counters are **13 saved records, 0 waiting to sync and 0 needing attention**. The offline-state banner remains visible. The launchpad heading and description are compressed into a narrow left column while the eight module navigation actions occupy the right side of the same header, creating excessive vertical height and poor readability.

**Confirmed source mechanism:** the offline-navigation correction at `f6a9b3c5` made the permission-filtered module links visible again, but the offline header still inherited the shared flex-row composition. The generic `.zazu-action-group` becomes `width: 100%` at mobile breakpoints while its parent header remains a flex row, allowing the navigation to consume the row and shrink the heading. The earlier browser contract asserted navigation visibility but did not measure mobile heading/navigation geometry.

**Implemented correction on main:**
- `52152f2f6770a10377c59d28cdc376fa8baa76d3` — add an offline-only responsive grid to the existing dashboard authority stylesheet; phone layout stacks introduction, two-column module actions and status, with one-column actions on very narrow screens.
- `20e313ef1ade96071ceee45c9afa26e56bc20e3a` — add offline-only scope classes to the existing Blade shell.
- `66f7392aeb7f187c2b6bd3acb45b96dc6a56cce9` — extend the offline browser contract to assert the heading and navigation align full-width and stack correctly at 390×844.

This is **IMPLEMENTED IN SOURCE and AUTOMATED-BROWSER-VERIFIED** on exact checkpoint `4a5bedfc` (75 Chromium offline/browser journeys passed, including 390×844 header geometry and the atomic acknowledgement guard; the separate mobile suite passed 11 tests, plus mobile host-outage and phone-local attachment checks). No post-fix Android screenshot or physical visual acceptance is claimed.

**Settings navigation boundary:** source inspection confirms the offline route allowlist in `resources/js/app.js` includes only route families with an implemented local view. `/settings` is not mapped, so its unsupported sidebar destination and the otherwise-empty System group are hidden while disconnected. Pairing, owner administration and server-backed business settings remain online-only under the current contract. Do not restore a generic Settings link merely to make it visible; only expose a specific Settings function after its local data/behaviour is implemented and tested. The shell theme toggle remains independently available.

**Owner-test rule:** do not ask the Founder to repeat the already-established offline page/data/host condition. The only new physical confirmation to request is the corrected offline launchpad layout at phone width while the server remains unavailable. Persistence, airplane-mode cold launch, restart, attachments and reconnect remain separate open checks.

## Offline mutation acknowledgement crash-consistency — 2026-11-26

A source-level transaction audit found that server acknowledgement previously removed a queue row in one IndexedDB transaction, then reconciled the local server ID/revision, pending flags and `local_dirty` in a second state-only transaction. A browser termination in that gap could leave a locally pending record with no queue entry, causing the next bootstrap to remain blocked.

**Correction:** `public/zazu-offline.js` now reads the persisted workspace and queue and commits queue acknowledgement/removal, canonical identity/revision reconciliation, related-record reconciliation, conflict persistence, `pending_sync` flags and `local_dirty` in one `[QUEUE, STATE]` read/write transaction. If the transaction aborts, IndexedDB rolls back both stores; if it commits, the queue and workspace state move together. The server's idempotency key remains the safe retry boundary.

**Evidence on exact checkpoint `4a5bedfc`:**
- Laravel: **765 passed, 1 skipped, 5,471 assertions**.
- Offline browser acceptance: **75 passed** across the desktop offline suites, including the 390×844 layout assertion and acknowledgement failure-injection contract.
- Separate mobile offline Work/visual suite: **11 passed**; mobile stopped-host check: **1 passed**; phone-local attachment journey: **1 passed**.
- PostgreSQL migration/recovery smoke: **1 passed**.
- PHPMD, static analysis, Windows installation build, encrypted/legacy archive verification and fresh Windows installation: PASS.
- Zazu Quality application/formatting/secret-scan jobs: PASS; Composer security audit remains FAILED on five Symfony advisories (CVE-2026-133743, CVE-2026-133746, CVE-2026-133751, CVE-2026-133754, CVE-2026-133755). SonarCloud is skipped, not passed.
- The `4a5bedf` test-only commit arms the failure-injection boundary when the mocked push reaches the server boundary; exact-head Laravel and browser rerun PASS.

This is automated transaction-level evidence, not a simulated physical power-cut/restart test and not full reconnect acceptance. Physical local-write/restart/reconnect checks remain open.


- Exact current application/test checkpoint `4a5bedfcbb8b59e09de5398694e5050cc7a61de7` — Laravel run `45782481596`: **765 passed, 1 skipped, 5,471 assertions**; coverage PASS; PostgreSQL migration/recovery smoke PASS; offline browser acceptance **75 passed**; separate mobile Work/visual **11 passed**, stopped-host **1 passed**, phone-local attachment journey **1 passed**. Push-on-main static analysis PASS. Zazu Quality `45782481630`: application, formatting and secret-scan PASS; Composer security audit FAILED on the five Symfony advisories already listed. SonarCloud remains skipped. The test-only guard timing refinement is included in this exact-head passing result.

## Browser-managed local storage retention — IMPLEMENTED IN SOURCE; ACCEPTANCE OPEN

**Tracked as:** CASE-ZAZU-0052 / ENG-DATA-007. This is a platform-retention exposure identified by source audit, not a reported data-loss incident. The mitigation is implemented in source; automated and physical acceptance remain open.

The initial source audit found no `navigator.storage.persist()`, `persisted()` or `estimate()` handling. The current candidate now exposes `getStorageProtectionStatus()` and an explicit `requestStorageProtection()` action in both Settings → Data protection and the offline workspace. It reports persistence grant state and estimated usage/quota, warns at 90% of estimated quota, and normalizes quota write failures so failed local saves are not represented as successful. The request action calls `StorageManager.persist()` synchronously from the user's click handler; refusal or unsupported browsers do not block offline work. Users can still explicitly clear site data. See [MDN storage quotas and eviction criteria](https://developer.mozilla.org/en-US/docs/Web/API/Storage_API/Storage_quotas_and_eviction_criteria) and [MDN StorageManager](https://developer.mozilla.org/en-US/docs/Web/API/StorageManager).

**Current implementation:** `public/zazu-offline.js`, `resources/js/app.js`, `resources/views/settings/index.blade.php`, and `resources/views/dashboard.blade.php`; service-worker cache advanced to `zazu-static-v62` so existing PWA installs can precache the updated offline client. Browser tests cover granted, denied and unsupported outcomes, near-capacity warning and a forced `QuotaExceededError` that must reject the save and display the warning. Commits: API `dec3b00`, view `276c348`, renderer `1736db1`/`4d301d6`, quota handling `650ed74`/`2c8dc38`, worker cache `229183d`, settings `715d7fa`, final tests `3b6b404`. **Acceptance remains pending** on exact-head CI and physical Android validation. Never imply a persistence request guarantees against explicit user deletion. Business export/import and full recovery remain separate safeguards.


## Sidebar UX refinement — 2026-11-26

The persistent sidebar context has been simplified without changing navigation architecture: the derived business mix is a compact, low-emphasis summary; workspace identity is integrated into the existing account trigger; the standalone workspace card is removed. Account actions remain in the existing popover. All five permission-gated navigation groups and responsive interaction contracts remain unchanged. Current visual change requires exact-head Laravel, quality and responsive browser checks before acceptance.


## Navigation acceptance — 2026-11-26

Desktop navigation has two modes: expanded labelled sidebar and collapsed icon rail. Both expose all five permission-filtered groups through anchored contextual panels; parent activation never navigates. No desktop nav-list scrolling. Phone/tablet use the full drawer and tap accordion. Finance > Advanced Search opens the existing search page and its advanced filters. The new interaction model requires fresh CI and viewport E2E verification before acceptance.


## CI regression root causes — 2026-11-26

Three Laravel acceptance failures were traced to navigation integration: accessible-name drift on the Work trigger, removal of the account's Signed in context label, and a duplicated canonical .zazu-nav-area-title CSS rule. Corrected at source. The collapse initializer was also present but not invoked by the main initializer; it is now wired into startup. Re-run Laravel, coverage, and offline browser acceptance before declaring green.


## Navigation presentation refinement — 2026-11-26

Expanded desktop submenu presentation is inline/indented; collapsed desktop uses anchored contextual panels. Mobile/tablet retain inline drawer accordions. One semantic nav tree and shared parent trigger logic serve all viewports. Browser regression now covers collapsed desktop click-through and confirms parent activation does not navigate.

## Navigation hardening follow-through — 2026-11-26

The navigation state machine and stylesheet ownership have been tightened without changing destinations or permission gates:

- Removed the duplicate `setupZazuSidebarCollapse()` startup call; added a PHPUnit guard so the initializer remains registered exactly once.
- Removed the competing `:focus-within` visibility rule. A submenu is visible only while its parent has `is-open`, matching `aria-expanded`.
- Escape now captures the open parent trigger before closing the group, then restores focus to that trigger.
- Collapsed desktop flyouts are positioned relative to their own group; the final group opens upward. Flyouts have no max-height or nested scroll region.
- Desktop navigation has no internal scroll container. On touch layouts, the drawer is the single overflow container; the nav list and its inline submenu are not independently scrollable.
- Moved shared navigation geometry and typography out of `zazu-final-visual-sweep.css` and back to canonical `app.css` ownership. Regression coverage now guards against reintroducing generic navigation selectors in the visual extension layer.
- Browser acceptance covers collapsed-panel visibility, anchored positioning, no parent-route navigation, no nested scroll, Escape focus restoration, and mobile drawer scroll ownership.

### CI evidence and remaining independent failures

The original `e87bca6` run failed Laravel because `.zazu-nav-flyout-heading` had two base declarations where the canonical-selector test requires one. That duplicate has been removed. The same original run also reported independent PHPMD findings (two complexity-threshold methods and one test class over the method-count threshold) and offline-browser acceptance failures around device trust and the hidden sync-recovery alert. Those are not navigation regressions and remain separate work items. Exact-head CI for the current navigation changes must finish before acceptance is marked green.

### Browser CI follow-up — 2026-11-26

The first stylesheet-ownership cleanup run passed Laravel tests and coverage but caught a navigation icon regression in the browser acceptance step: the rendered nav icon measured 16px against the 22px minimum. Restored 23px icon sizing in canonical `app.css` (collapsed rail remains 22px). The Laravel workflow now explicitly runs the collapsed desktop rail and mobile drawer keyboard-focus tests alongside the expanded navigation/mobile calendar check. The exact-head rerun is in progress; do not mark browser acceptance green until it completes.


## Founder legal and brand decisions — 2026-11-26 (authoritative)

Current owner decisions are recorded in [FOUNDER_LEGAL_BRAND_DECISIONS_2026-11-26.md](../../docs/FOUNDER_LEGAL_BRAND_DECISIONS_2026-11-26.md). This record supersedes conflicting assumptions in older working drafts.

- **Ownership:** Founder personally owns Zazu EMP and its current IP until further notice. Rosscore Labs is not registered and is not the current owner/operator. A later transfer requires a separate written instrument after the company is properly established.
- **People:** Founder is the only person involved; no employees, co-founders or contractors.
- **Launch:** no commercial launch now. Product stability is the priority. A controlled non-commercial pilot with close trusted members is acceptable when specifically authorised. Prefer synthetic data; real personal information still triggers applicable duties.
- **Data scope:** business/workspace and staff account details; customer contact details; event addresses/dates/requirements/notes; quotes, invoices and payment records; documents, receipts, photographs and attachments. Identity numbers, bank-account details, children's information and medical/sensitive information are not intended V1 categories.
- **Providers/deployment:** no Zazu production hosting or third-party processing provider has been provisioned/selected. Hosted and local/offline remain intended product directions; actual first deployment and vendors are undecided. xneelo is a shortlist only.
- **Information Officer:** Founder has not registered an IO. Founder is proposed IO for the current individual operation, subject to confirming the correct registration route. No deputy is appointed. Never claim registration.
- **Privacy contact:** dedicated privacy mailbox approved in principle but not created; `rosscorelabs@outlook.com` remains provisional and must be monitored/tested before publication.
- **Retention:** Founder requested a proposal. `docs/RELEASE_RETENTION_DISPOSAL_SCHEDULE.md` is provisional, not approved and not technically enforced.
- **Brand:** Founder personally intends to own brand rights for now. ZAZU is not approved as the final product name. Descriptor and promise remain working positioning only. South African protection with international expansion intent; no domain/social/logo/trade-mark filing has been secured and no registration spend is authorised now. Keep a fallback name.
- **Open:** final product name; Rosscore incorporation and IP transfer; IO registration; privacy mailbox; deployment/provider selection; final retention approval; legal/accounting review; trade-mark classes/territories and any filing; commercial launch decision.

Product stability remains the immediate priority. Do not ask the Founder to repeat these decisions; consult the decision record first. Legal/brand work remains a pre-commercial-release gate and must not expand V1 scope.

## Business archive restore forensic hardening — 2026-11-26

**Scope:** tenant-scoped encrypted/legacy business archive export, verification, upload, restore, file staging, destination safety and owner-facing diagnostics. No installation-wide recovery or unrelated application work was changed.

**Founder evidence:** the 2.20 MB Rosscore business archive passed the read-only verification command with the supplied passphrase. This proves the encrypted stream can be decrypted and the archive structure, dataset and referenced payloads pass verification. The Founder has explicitly clarified that both restore accounts/workspaces were unpopulated; do not attribute the observed failure to seeded demo records or target occupancy without evidence.

**Destination safety contract:** the existing restore contract rejects populated workspaces and never merges or overwrites their data. The restore service checks destination eligibility before staging attachment payloads and repeats the check under the business-row lock before mutation. This is a safety rule, not an established explanation for the Founder's failed request.

**Confirmed security defect and correction:** untrusted file-manifest entries previously used left-biased PHP array union with server-generated staging metadata. An archive could provide a staged_path value that overrode the actual server temporary path; verification/restore cleanup could then unlink an arbitrary path accessible to PHP, and restore could read the attacker-selected path as a staged file. The manifest now requires a list, rejects unknown fields, validates and normalizes positive record IDs, and server-generated staging metadata takes precedence. A regression injects staged_path and payload_bytes and asserts that a sentinel file remains unchanged.

**Restore usability corrections:** the archive form now renders actual field errors in the global summary. Owners can explicitly create a separate business workspace under the same account for the archive; the current workspace remains untouched, the authenticated owner is attached to the new business, and the session switches only after a successful restore. If restore fails, imported rows/files are rolled back and the newly created empty workspace is removed. Existing-empty-workspace restore remains supported.

**New regression coverage:** hostile file-manifest metadata, occupied-target preflight before attachment staging, populated-target field error visibility, and restore into a new owner workspace while preserving the original demo workspace.

**Current verification:** code and documentation changes are on main. Exact-head GitHub checks for the latest main commit are pending; do not mark this module accepted until Laravel, coverage, formatting, PHPMD, Windows encrypted/legacy archive verification, PostgreSQL recovery smoke and relevant browser checks complete. The Founder’s original Rosscore archive has not yet been restored by the live application; successful read-only verification is not represented as a completed restore.


## Business archive upload pipeline defect — 2026-11-26

**Confirmed implementation defect:** the Windows launcher previously passed PHP `-d upload_max_filesize=100M` and `post_max_size=110M` options to `php artisan serve`. Laravel's Windows `ServeCommand` starts a separate PHP HTTP worker and filters inherited environment variables; command-line `-d` options on the Artisan parent are not inherited by that worker. The HTTP worker could therefore retain PHP's common 2 MiB upload / 8 MiB POST defaults even while the launcher appeared to configure a 100 MiB upload limit. A 2.20 MB archive can consequently be rejected before Laravel receives the file, surfacing as a generic required-field validation error.

**Correction committed to main:** the launcher now creates a project-local PHP INI scan file, passes `PHP_INI_SCAN_DIR` through Laravel's Windows child-process allowlist, and sets the parent environment ordering so the variable is available in `$_ENV`. The HTTPS-only `SESSION_SECURE_COOKIE` override is also explicitly passed through. A runtime signature is written only after HTTP readiness; an older verified Zazu worker without that signature is restarted to apply the new limits. Request validation now reports the active PHP upload/post limit when PHP rejects the body before archive processing. Regression coverage checks the child-process passthrough contract.

**Important verification boundary:** this is a strong code-level root cause for the 2.20 MB upload rejection, not yet proof that the Founder’s exact failed request had `UPLOAD_ERR_INI_SIZE` or exceeded `post_max_size`. The incident reference `3feb3a06-e743-49e7-9a7d-4dc5e77b68a7` belongs to the request-validation stage; its server log diagnostics identify the actual failing field and PHP upload settings. Do not claim the archive was restored until the updated launcher is deployed, the real restore succeeds, and exact-head CI passes.


## Founder-confirmed Rosscore business archive restore — 2026-11-26

<badge color="success">OWNER-ACCEPTED — RESTORE SUCCESSFUL</badge>

The Founder confirmed that the original 2.20 MB encrypted Rosscore business archive restored successfully through the live Zazu application and that everything from the backup is present in the restored workspace. The attempt succeeded after the PHP upload configuration correction was deployed and the supported Windows launcher restarted.

Both source and destination workspaces were unpopulated; target occupancy was not the cause. The earlier 2.20 MB upload rejection was consistent with a confirmed defect in which PHP upload limits set on the Artisan parent did not reach Laravel's child HTTP worker. The launcher now supplies a project-local INI scan directory through the child-process environment allowlist and restarts older workers that lack the runtime signature. The exact historical request log did not separately prove the PHP upload error code; preserve that distinction in future incident reporting.

**Closed:** this specific encrypted Rosscore business archive export/import and live restore acceptance.

**Still open:** installation-wide backup/restore recovery on the intended PC; this is a separate recovery scope. The overall release verdict remains NOT READY because other security, physical offline, commercial and legal gates remain open.

The runbook contains the owner acceptance evidence, incident resolution, operational instructions and explicit verification boundary: [Business archive recovery acceptance](../../docs/RELEASE_BACKUP_RESTORE_RUNBOOK.md#founder-confirmed-business-archive-recovery--2026-11-26).


## Director continuation — preparation offline acceptance (2026-11-27)

- Preparation status-update browser acceptance is now committed in `e2e/offline-work-creation.spec.js` at `f064dff847cd14b2b031d7ce32e3501c57d5ac16`.
- The test asserts the actual offline mutation envelope (`payload.local_id`, `payload.server_id`, nested `payload.record.status` and `event_local_id`), persisted local status after reload, and one queued mutation. It does not assert a revision field that the queue envelope does not expose at enqueue time.
- The test is an automated CI candidate, not yet a verified pass. Latest observed Laravel workflow is still running.
- Exact-head Composer security audit, PHP formatting, application checks and secret scan have passed on `f064dff`. Laravel browser acceptance, PHPUnit, coverage, PostgreSQL recovery and CodeQL are still in progress/pending.
- The parity standard was corrected to distinguish status-chip reconciliation on server-backed rows from selected status controls on local-only rows.
- Preparation remains open pending exact-head browser/Laravel results and Founder PC/Android disconnected acceptance. No release gate is accepted by this checkpoint.


## CI failure analysis and correction — 2026-11-27

The exact-head Laravel run on `f064dff` completed with 783 passed, 2 failed and 1 skipped (5,613 assertions). Both failures were stale expectations unrelated to the preparation mutation implementation:
- `BusinessCapabilityCatalogueTest` expected the equipment panel identifier to be absent, but the page still exposed an empty rental tab for a service-only business. The Blade view now omits that empty tab; the test asserts the actual tab control is absent.
- `PwaOfflineFoundationTest` expected an obsolete one-line service-worker message. The current app sends a structured message with a bounded route list and truncation flag; the test now asserts that contract.

Correction commits: `f9a31c6` (empty rental tab), `a164b77` (catalogue regression), `e896917` (PWA contract regression). All remain on `main`; no branches created.

At the first observed checkpoint for `e896917`: PHPMD passed, Director Health passed, Windows installation candidate build passed; PostgreSQL smoke and Windows archive/install verification were running; Laravel, Zazu Quality and Push-on-main were queued. Exact-head Composer audit/formatting/application/secret-scan results for the previous source checkpoint `f064dff` were all successful. The `e896917` rerun must be used for final acceptance; do not carry forward old green statuses as exact-head proof.


## Director continuation — offline acceptance test corrections (2026-11-27)

- `bb28ff1` corrected a stale Laravel source-contract assertion: authenticated route priming is link-discovered and bounded, not a hard-coded list of customer/job paths. Laravel tests passed on that checkpoint.
- `4adedd6` corrected the remaining Pint method-spacing issue in the same test file.
- `3fd7cc7` made the Preparation browser test explicitly trust the demo device and run sync before selecting its server-backed parent job; the previous test assumed the demo job was already present in the local collection.
- `c15d230` made the native catalogue browser test activate the Services & products tab before asserting a service row that correctly lives in that tab.
- `50f08ec` aligned the device-trust disclosure assertion with the current Settings copy.
- At this checkpoint, Zazu Quality on `50f08ec` passed, including formatting, Composer security audit, application checks and secret scan. The exact-head Laravel and offline-browser acceptance runs were still in progress; do not mark the browser suite green from earlier runs.
- Offline product parity remains open. The browser suite must prove same-page navigation, persistence, session transitions and reconnect reconciliation. Founder PC/Android disconnected checks remain outstanding; no release gate is accepted.
