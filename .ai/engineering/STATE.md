# Zazu EMP — Current Engineering State

**Last reconciled:** 2026-11-26
**Repository:** Leano-Jordan/ZazuEMP
**Canonical branch / write target:** `main` only
**Latest verified application/test checkpoint:** `4a5bedfcbb8b59e09de5398694e5050cc7a61de7` (atomic offline sync acknowledgement + mobile offline layout; exact-head Laravel, offline browser/mobile, PostgreSQL and code-analysis rerun passed).  
**Current application/test candidate:** `573a52dc04f481bc484f10da5d8e55353a49295e` (12-digit indexed HMAC phone pairing, single-row provisioning, revocation/reset invalidation, atomic password-reset/device-revocation transaction, stale-session business-switch regression, pairing abuse/expiry/one-use tests, hidden credential metadata and offline client v63; exact-head CI validation queued).
**Release verdict:** NOT READY
**Formal evidence-maturity score:** 71/100 (baseline retained; no uplift without a formal domain-by-domain reassessment)

This is the concise live state register, not a session diary. Detailed incident evidence belongs in FAILURE_CASES.md, durable decisions in DECISION_LOG.md, recurring regressions in REGRESSION_LEDGER.md, and historical implementation evidence in dated audit/release records.


## Active security investigation — 2026-11-26

Founder-directed adversarial authentication audit is the active security investigation. The current implementation slice hardens phone pairing to 12-digit APP_KEY-derived indexed digests, a single-row provisioning lookup and a 10/minute IP cap; pairing digests are invalidated on revocation/password reset and the offline client cache advances to v63. Exact-head CI and remaining adversarial tracks are still gates; audit remains OPEN. Scope and source-derived checkpoint: [DIRECTOR_ADVERSARIAL_AUTHENTICATION_AUDIT_2026-11-26.md](../../docs/DIRECTOR_ADVERSARIAL_AUTHENTICATION_AUDIT_2026-11-26.md). The six mandatory tracks are session fixation/concurrent account transitions; browser credential leakage/XSS/backups; pairing-code brute force/device takeover; cross-business token substitution/stale replay; password reset/owner removal/device revocation races; and offline logout/account switching/queued transaction preservation. Audit is OPEN; source inspection is not a penetration-test PASS. Preserve DEC-019 and the business-scoped offline queue; do not weaken controls or clear user data as a test. Continue on main only, no branches.

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

## Latest verified automated evidence

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

### 2. Encrypted business archive restore — owner test failed; root stage UNKNOWN

On 2026-11-23 the Founder verified an installation backup and a business archive. The encrypted business archive was attempted in a disposable empty workspace and failed with “archive failed to load”; a later SYS-002/429 was rate limiting, not the proven cause. The first failing stage remains unknown. Preserve the original archive, passphrase and disposable target. Never retry against the live business or weaken the three-attempt throttle. The endpoint is capped at 100 MB upload and the expanded archive at 250 MB; the user interface states both limits. No evidence currently proves either limit caused the failure. Investigate with the original incident-stage logs or safe reproducible fixtures.

The encrypted path uses Sodium secretstream XChaCha20-Poly1305 with Argon2id, private temporary staging, ZIP/manifest allowlisting, business ownership validation and transactional restore. Automated encrypted/legacy archive tests and Windows archive checks pass; these do not reproduce the owner's real archive.

### 3. Installation-wide recovery — OPEN

Automated backup/restore, rollback, PostgreSQL recovery smoke, Windows packaging and fresh-install checks pass. A controlled full installation recovery drill on the intended owner PC remains mandatory and unaccepted.

### 4. Security, commercial and legal gates

- DEC-019 remains a documented **HIGH** trusted-device shared-profile risk: anyone with access to an owner-approved shared browser profile can inherit its approved offline workspace/mutation capability. Do not claim per-staff offline identity on that trust model.
- Populated quote-to-payment-to-actual-cost/profit reconciliation remains open.
- POPIA operational controls and ZAZU name clearance remain open.
- **Symfony dependency security — RESOLVED:** the five advisories previously reported against Symfony 7.4.23 / 8.0.15 were addressed by lockfile update commit `43e69e6` to Symfony **7.4.24**. Composer security audit passed in Zazu Quality runs `45787154093` and `45787226126`; no advisory exception or suppression was added.
- PHPMD/Psalm SARIF publication still fails; analysis steps ran, but do not claim published results. SonarCloud is skipped.
- No independent penetration test has been completed; automated scanners and source review are not a penetration test.

## Cleanup completed on main

- Removed the retired Render/Docker deployment stack and its Docker-only Hadolint workflow.
- Removed an unused legacy offline-attachments script, two generated placeholder tests, a stale Claude handover file and an auto-downloading/self-committing vendor media workflow.
- Reduced STATE.md from approximately 331 KB / 2,500 lines to this concise evidence-led register. Historical audit/decision/failure records remain preserved and are explicitly identified as historical.
- Reconciled Zazu product identity across README, formal product specification, Composer metadata, manifest, app footer, Director mandate and chat bootstrap.
- Removed contradictory DB-001 active-blocker guidance and stale Morpheus role hierarchy from operating bootstraps.
- The existing deploy/render-staging ref is preserved as read-only historical state; no non-main ref was modified and no branch was created.

## Execution order

1. Forensically isolate the real encrypted business-archive restore failure without touching the live business or original verified backup.
2. Continue targeted adversarial tenant/session/device/file and business-rule tests; resolve SARIF publishing configuration so completed analysis reaches GitHub code scanning.
3. Complete installation recovery key-custody review and owner-PC drill when the Founder can accept it.
4. Reconcile the populated commercial workflow.
5. Prepare POPIA and ZAZU name-clearance decisions for Founder acceptance.

Physical-device, owner-PC recovery, populated-business and legal/brand acceptance remain separate Founder gates. Continue source, test, documentation and CI work that does not require those actions.

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
