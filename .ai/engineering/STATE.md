# ZAZU EMP — ENGINEERING CONTROL STATE

## Identity
- Product: Zazu – Event Management Platform
- Repository: Leano-Jordan/ZazuEMP
- Canonical branch: main
- Master control: Morpheus / Director
- Owner-facing nickname: Jarvis

## Authority
1. Explicit owner instruction
2. Current repository state
3. Current living documentation / ledgers
4. Verified runtime / CI evidence
5. Verified external research
6. Historical records / chat memory

## Canonical control contract
`.ai/engineering/00_DIRECTOR_ENGINE.md` is the single canonical Director contract.
`.ai/engineering/00_ENGINE_ROUTER.md` is a compatibility pointer only.
Entry files must point to the canonical contract and must not define a competing read order.

## Control loop
IDENTITY → BASELINE → TARGET → ROUTE → INSPECT → CHANGE → VERIFY → BREAK → RECONCILE → ACCEPT/REPAIR → RECORD → NEXT

## Local PC host deployment cycle — 2026-11-01

- Active deployment target: stable local HTTPS hostname → persistent Windows Apache/PHP host → local authoritative database/private storage → phone/tablet PWA clients.
- No feature branches; all changes land directly on `main`.
- Added `docs/LOCAL_PC_HOST_DEPLOYMENT.md` with the target topology, secure-origin design, Windows service requirements, network boundaries, backup plan, phases and acceptance gates.
- Added `ops/windows/Inspect-ZazuHost.ps1`, a read-only inventory script. It must be run on the actual owner PC before touching AMPPS, Apache, database services, firewall, certificates or customer data.
- Corrected `.env.example`: removed the explicit `SESSION_SECURE_COOKIE=false` override. `config/session.php` already defaults the cookie to secure when `APP_ENV=production`; the explicit false value would have defeated that production default in copied environments.
- Rewrote `docs/LOAD_SHEDDING_MOBILE_TEST_RUNBOOK.md` to distinguish temporary Artisan/HTTP LAN testing from customer hosting and to reflect the current IndexedDB/offline mutation implementation rather than the obsolete static-cache-only description.
- Source files were re-read after the writes. No PHP/Node test suite or Windows host inspection has been executed in this remote repository-only cycle.
- Next remote gate: audit all origin-dependent code, service-worker registration/scope, generated URLs, cookie/CSRF behaviour and host availability/reconnect assumptions; add automated regression coverage where source evidence supports it.
- Next physical gate: run the read-only host inspection, then select the existing Windows stack and configure it only after backup and compatibility checks.

## Active target
**P0 active mission: finish VS Code-independent offline continuity; reconcile current-main offline/workflow CI; establish a durable HTTPS install origin; prove disconnected-device operation and recovery.**

Current state:
- Canonical repository/ref: `Leano-Jordan/ZazuEMP` / `main` only. No branches are to be created or used.
- Broad green CI evidence recorded for `7940033b6fd4f691692eec5e9d7f6ad587486f61` (2026-10-15) is historical and does not certify later commits.
- Service-worker identity-state writes and logout cleanup are now serialized through a shared promise queue.
- Three Node behavioral regressions exercise the actual `public/sw.js`: delayed persisted identity read, delayed authenticated HTML response, and identity-cache write already in flight during logout.
- `npm run test:service-worker` is wired into `.github/workflows/quality.yml`. Repository files were re-read after writes; current-main runtime/CI result remains **UNVERIFIED** until the exact workflow run is observed.
- Stale health report snapshots from an October 12 Dependabot branch were archived and misleading root-level `docs/health/latest.*` files removed. The current health workflow is artifact/summary-only with `contents: read`; use exact Actions run evidence, not a persistent latest file.

Next hard gate: **observe current-main Quality/Laravel/browser checks and the three behavioral regressions; then execute the documented customer-environment backup/restore and rollback drills**.

Physical device acceptance is now owner-verified: Zazu installed on PC and mobile; screen rotation and presentation reported in order.

## Open / blocked cases

## Evidence rules
- IMPLEMENTED = repository change exists.
- TESTED = relevant automated check actually ran and passed.
- VERIFIED = intended behaviour has sufficient evidence.
- PROVEN = repeated realistic/production/recovery evidence exists.
- UNVERIFIED = required evidence unavailable.
- BLOCKED = safe progress materially prevented.
- A green narrow check never closes a broader behaviour gate.
- No case is CLOSED without its required evidence.

## Current CI boundary
The earlier browser run for `0a10d05` is not evidence for this final control-plane head. Current-head CI must be observed after this cycle. Record the exact run URL and SHA before closing any pending case.

## Application gates after browser convergence

1. Complete the populated commercial workflow traversal.
2. Execute the real backup/restore drill, including representative private media.
3. Execute the populated upgrade/rollback drill.
4. Complete physical phone/tablet acceptance.
5. Complete legal/privacy operational controls.
6. Complete dependency/licence notice closure.
7. Complete Zazu brand/trade-mark clearance.
8. Final Director re-audit against the selected release head.

**Current execution priority:** reconcile the 2026-11-01 offline-continuity source/test batch on exact `main`; then establish the independent HTTPS install origin and run installed-device disconnected/reconnect drills.

## Director cycle update — 2026-10-07

Offline operational depth expanded in current `main` through:
- inventory item + inventory movement handlers with business scoping, negative-stock protection and mutation idempotency;
- operational event-cost create/update handling with financial-state validation;
- asset create/update and allocation/return handling with explicit state-transition protection;
- direct and paired phone bootstrap coverage for inventory, assets and costs;
- phone-local UI actions for inventory, assets and costs;
- focused regression coverage for the new offline boundaries.

Current evidence status: **IMPLEMENTED; runtime TESTED/VERIFIED pending execution of the new regression set.** No release score uplift is claimed from source changes alone.

Last updated: 2026-10-07 — offline operational depth and parity hardened; current release gates remain current-head verification, recovery, rollback, legal/privacy and brand closure.

## PHPUnit / offline parity defect cycle — 2026-10-07

- Root cause of PHPUnit exit 255: malformed `catch (\\RuntimeException ...)` syntax in `tests/Feature/OfflineSyncFoundationTest.php`.
- Adjacent malformed test marker introduced during test-contract editing was also removed before validation.
- Offline local reconciliation was hardened for payments and purchase receipts so local invoice/payment and inventory state reflects accepted offline actions immediately.
- Targeted PWA regression assertions now cover those reconciliation boundaries.
- Current head CI is queued; runtime closure remains pending fresh Laravel/PHPMD/browser results.


## Director health-report hardening — 2026-10-08 (historical; superseded)

- A prior implementation experimented with persistent `docs/health/latest.json` and `latest.md` files alongside downloadable artifacts.
- That persistence is not present in the current `.github/workflows/roscore-director-health.yml`, which has repository contents permission set to read-only and publishes the per-run artifact and Actions summary.
- The October 12 committed `latest.*` snapshots described a Dependabot branch with queued CI, not current `main`. They have now been retained under `docs/health/archive/` and the root-level misleading pointers removed.
- Do not restore a privileged persistent publisher without a separate least-privilege, main-only design and verified security controls. The workflow must never check out untrusted refs or create working branches.

## Current-head CI reconciliation automation — 2026-10-08

- Director health reporting now observes the actual Laravel and Zazu browser smoke workflow results for the target commit.
- The health workflow runs on completion of those required workflows as well as on source pushes/manual dispatch.
- Reports distinguish PASS, FAIL and PENDING current-head CI state and retain the workflow run URLs.
- Workflow-run-triggered reports serialize per target SHA to prevent competing report commits.
- Implementation commit: `af0f4158160488873bbaf072de5db75ccdc10192`.


## Director deep software audit — 2026-10-08

Jarvis completed a deep repository audit against docs/product-specification.md and the V1 capability map.

Confirmed defects corrected in this cycle:
- authenticated PWA page-cache lifecycle race;
- Director health report non-fast-forward publication race;
- concurrent offline pairing-code consumption race;
- concurrent offline mutation replay race;
- rejected offline mutation permanently blocking later sync pulls.

Regression coverage was added for authenticated offline navigation, pairing/conflict behavior and rejected-mutation recovery. A newly added pairing regression initially failed CI because of a test namespace typo; this was corrected immediately in de2413d.

Current audited application HEAD: de2413d78f1efbb8a511fadd78375f1ceba025ce.

Conservative assessment:
- V1 implementation maturity: approximately 87%.
- Commercial software readiness: approximately 72%.
- Current release is not certified.

Release-critical gates remaining:
1. current-head CI/browser/security convergence;
2. real offline installed-device proof and restart/reconciliation;
3. populated commercial workflow certification;
4. real backup/restore including representative private media;
5. populated upgrade/rollback drill;
6. final authorization/security challenge;
7. production hardening and operational runbooks;
8. offline licensing activation/signing/enforcement where required for the commercial model;
9. final Director release audit.

Explicit exclusion from these scores: logo, artwork, photography, branding, trademark and other human-controlled creative/legal tasks.

Audit record: docs/DIRECTOR_DEEP_AUDIT_2026-10-08.md.


## Pre-launch landing page — 2026-10-12

- Added a standalone static marketing site under `prelaunch/`, separate from the Laravel application and PWA runtime.
- Added the GitHub Actions workflow `.github/workflows/deploy-prelaunch.yml` to publish only the `prelaunch/` directory to GitHub Pages from `main`.
- Page includes product positioning, conceptual (clearly labelled) workspace illustration, responsive feature sections, FAQ, early-access/demo enquiry form and a privacy notice.
- Form submission is configured through FormSubmit to the Rosscore Labs enquiry mailbox. Recipient activation and real delivery are **UNVERIFIED**; GitHub Pages does not store submissions.
- Static source integrity checks passed: expected files present, local asset references consistent, form consent/redirect/privacy links present, CSS/JS brace counts balanced, no remote image/font dependencies.
- GitHub Pages deployment, live browser rendering, mobile browser acceptance and end-to-end form delivery remain **UNVERIFIED** pending the Actions run, Pages settings and recipient activation.
- Expected project URL: `https://leano-jordan.github.io/ZazuEMP/`; confirm from the deployment output before public promotion.


## Pre-launch conversion copy and South African image refinement — 2026-10-13

- Revised public-facing early-access copy to lead with reduced chasing and a clearer view of the work behind each job, with a low-pressure choice between early access, a product demonstration, or both.
- Broadened copy to independent operators and growing teams across multiple South African event-related business types; avoided scarcity claims, invented social proof, and founder-support framing.
- Replaced six catering/venue-led photo references with responsive Wikimedia Commons image variants; added srcset/sizes, a public author/licence page, and privacy disclosure for the Wikimedia image host.
- Changed the pre-launch policy intentionally: image requests are remote but restricted by CSP and the deployment audit to HTTPS upload.wikimedia.org/wikipedia/commons/thumb/. This is not a self-hosted/offline image set.
- Verification remains pending: the changed deployment workflow must run on the new main HEAD, the deployed page must be checked in desktop and mobile browsers, and image loading/credits must be verified from the published artifact. FormSubmit delivery remains separately unverified.


## Landing-page reliability and first-impression refinement — 2026-10-13

- The SVG illustration substitution was unauthorised and has been fully removed from the landing page, media credits, privacy notice, source notes, and deployment workflow.
- Restored the six existing Wikimedia Commons photographic image references and responsive srcset variants, along with the required CSP host permission and original image-credit/privacy documentation.
- Removed all six newly added illustration assets from the repository. No AI-generated artwork is approved for this page.
- Kept the clearer hero headline and early-access/product-demo calls to action.
- Remaining acceptance: diagnose why the original photos did not display on the live GitHub Pages deployment, verify the current Actions deployment, then inspect image loading on desktop and mobile. Restoring source references does not prove live image delivery.

## Director cycle — 2026-10-15 current-main reconciliation

- Canonical branch: `main`; current assessed HEAD before this state-record update: `7940033b6fd4f691692eec5e9d7f6ad587486f61` (offline reliability/browser regression hardening).
- Current-main push workflows at that SHA: Laravel PASS (run 38789104896), Zazu quality PASS (38789104893), PHPMD PASS (38789105223), Psalm Security Scan PASS (38789105085), Zazu browser smoke PASS (38789104865), populated upgrade/rollback PASS (38789104911). SonarCloud was SKIPPED by workflow condition (38789105277); do not describe it as a pass.
- The Director health workflow completed successfully for the target SHA (run 38789753467). The committed `docs/health/latest.md` is stale and still describes an unrelated older branch/commit; generated workflow evidence and committed health report are not reconciled. Treat this as a control-plane reporting defect, not an application test failure.
- The backup/restore workflow provides CI evidence for a populated upgrade/rollback round trip, including representative business, event, invoice/payment, private-media and migration-state checks. It does **not** satisfy the release runbook's installed-customer-environment recovery drill.
- Remaining next hard gate: run the documented controlled Windows installation backup/restore drill and capture environment, archive ID, representative record/media/security checks. This requires access to the installed environment; do not mark PROVEN from CI alone.
- Branch policy remains main-only. GitHub currently exposes the stale `director/fix-offline-job-browser-selector` ref in addition to `main`; the connected GitHub action set exposes no branch-delete operation. Do not create another branch or misreport cleanup as complete.

Evidence: https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38789104896 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38789104893 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38789105223 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38789105085 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38789104865 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38789104911 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38789753467

Last updated: 2026-10-15 — reconciled current-main CI and recovery proof boundary.

## Director cycle — 2026-10-15 MySQL restore rollback hardening

- Canonical branch: `main`; implementation/test head: `fb0278949e8971d32a65d1d36e074b0561159cab`.
- `app/Console/Commands/ZazuRestoreCommand.php` now captures a pre-restore MySQL dump before private-storage mutation, uses a shared MySQL import path, attempts database rollback after a failed candidate import, and preserves the rollback snapshot with explicit operator warning if automatic rollback also fails.
- `tests/Feature/ZazuBackupRestoreTest.php` now exercises a simulated partial MySQL import failure and asserts the pre-restore SQL is imported and original private storage is restored. Test passed in Laravel workflow run 38793762832.
- At the time of this record, the following checks passed for this head: Laravel 38793762832, Zazu quality 38793762950, PHPMD 38793762939, Psalm Security Scan 38793762878, populated upgrade/rollback 38793762891, Push on main 38793762946, and Director health 38793901676. SonarCloud 38793762938 was skipped. Browser smoke 38793762835 was still running and is not marked passed here.
- Release limitation unchanged: no actual installed Windows/customer-environment backup/restore drill has been recorded. CI is not a substitute.
- Branch policy unchanged: no new branch or PR was created. The stale feature ref remains visible alongside `main`; the connected GitHub actions still expose no branch-delete operation.

Evidence: https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38793762832 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38793762950 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38793762939 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38793762878 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38793762891 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38793762946 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38793762835

Last updated: 2026-10-15 — MySQL rollback behavior and regression evidence recorded.

## Director offline-launch defect cycle — 2026-10-16

- Root cause found in `resources/js/app.js`: service-worker registration sent `prime-pages` twice—once from the registration and again from `navigator.serviceWorker.ready`. The service worker aborts an active priming run when another `prime-pages` message arrives, so the duplicate call could cancel page caching during initial setup.
- Changed registration to await `navigator.serviceWorker.ready` and prime the installed pages once.
- Added regression assertions in `tests/Feature/PwaOfflineFoundationTest.php` to prevent the duplicate priming path returning.
- Status: source change committed to `main`; current-head CI and real installed-device offline relaunch are **PENDING**. This does not yet prove the user's offline launch issue is resolved.

## Director offline asset-cache hardening — 2026-10-16

- Further inspection found the service worker's static-asset handler started cache writes without awaiting them, then returned the network response. Browsers can suspend the worker before that write completes, leaving required JS/CSS unavailable offline.
- Reworked static-asset handling to return a cache hit immediately; on a cache miss, fetch and await the cache write before completing the event. Cache-storage failures do not break an otherwise successful online response.
- Bumped the static cache from v14 to v15 so installed clients activate a fresh cache namespace for the changed worker behavior.
- Added regression assertions for cache-first handling, awaited writes and the new cache version.
- Status: source and regression changes committed to `main`; current-head CI and actual installed-device offline launch remain **PENDING/UNVERIFIED**. The cache priming and asset-cache defects are evidence-backed contributors, not proof they explain every part of the reported failure.

## Director offline-origin correction — 2026-10-16

- Confirmed current source on canonical `main` at baseline `10f432bdd6a4d501bbd949c7973283e3ef8f7b66`.
- Current-head Laravel and Zazu quality workflows both failed because `tests/Feature/PwaOfflineFoundationTest.php` still asserted the removed `cache.put(request, copy)` implementation after the service-worker handler had been changed to await `cache.put(request, response.clone())`. This is test-contract drift (F2/F7), not evidence that the current asset-cache implementation failed.
- Corrected the stale assertion and added a regression contract for a user-facing warning when the app is opened in a non-secure context. The notice explains that a plain-HTTP LAN address depends on a reachable PC/server and cannot register the service worker.
- Updated the offline architecture contract to record the exact LAN-origin failure and the full disconnected-device acceptance gate.
- Verification on source/test commit `3f5d1f1877b281dc84a4bbce4bcabecc9fd12952`: Laravel passed ([run 38891784115](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38891784115)); Zazu quality passed ([run 38891784112](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38891784112)); Director Contract Lint, PHPMD, Psalm and populated upgrade/rollback also passed on that SHA.
- Browser smoke [run 38891784057](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38891784057) remains **in progress** and is not represented as passed. Actual installed-device offline relaunch remains **UNVERIFIED**.
- The product-wide offline parity gap remains open; no full-offline claim is made from this correction.



## Director audit — 2026-10-19

- Current control files now mark current-main CI as unverified rather than reusing green results from October 15 or the stale October 12 health artifact.
- Added three direct behavioral tests for service-worker identity/cache races and integrated them into the Quality workflow.
- Serialized `setUser` identity-state mutations with `clearActiveUser` cleanup in `public/sw.js` to prevent an in-flight identity write from surviving logout cleanup.
- Corrected V1 checklist/scorecard evidence language, archived and removed the misleading stale health pointers, and documented the current artifact-only health-report contract.
- Verification disposition: IMPLEMENTED at source; **latest-main execution and browser regression remain UNVERIFIED**. No numerical readiness increase is claimed.
- Next: reconcile the exact current-main Actions run, then perform installed-customer backup/restore including private media and an actual rollback exercise. Legal/privacy operations and trademark clearance remain external release gates.


## Dead/stale source and dependency cleanup — 2026-10-19

- A bounded declaration scan across `resources/css/app.css`, `zazu-responsive-theme.css`, `zazu-final-visual-sweep.css`, and `zazu-mobile-refinement.css` found exactly two duplicate `font-variant-numeric: tabular-nums` declarations; both were removed. Re-scan found no remaining repeated declarations in those four stylesheets.
- Removed unreferenced `@fontsource/anton`; package/lock are reconciled and the current npm licence inventory now records 153 package entries with declared metadata. Current-main `npm ci` is the required validation.
- Corrected the duplicate `REG-017` identifier to `REG-022`; the regression-ledger heading scan reports no duplicate REG IDs.
- Search returned no TODO/FIXME/HACK/DEPRECATED markers in the indexed results, but this is not proof that no dead code exists. Comprehensive PHP/Blade/JS reachability analysis remains open.
- Current-main install/build, service-worker behavioral tests, Laravel, browser, security and upgrade/rollback results remain UNVERIFIED until exact run conclusions are observed.


## Focused P0/P1 workflow audit — 2026-10-20

- P0 defect found: invoice creation displayed accepted quote, independent job, direct lines and tax treatment as concurrently editable despite quote invoices deriving job/lines/currency/tax from the accepted quote version.
- Source correction committed: selecting a quote binds the linked job, disables job/tax overrides, hides/disables direct invoice lines and explains the source. Clearing the quote restores direct mode and its prior manual job selection.
- Added a focused feature-test assertion for quote-bound invoice form hooks and event metadata.
- Existing server guards remain in place for quote/job mismatch, accepted latest version, duplicate invoice conversion and historical quote snapshotting.
- Existing populated reconciliation scenario covers R11,500 invoice, R3,450 deposit, R8,050 final payment and paid status. Asset overlap checks exist in online and offline paths.
- Current-head tests, CI and rendered browser behaviour are **UNVERIFIED** in this session. No release score/readiness uplift.
- Next: run targeted commercial and asset/offline tests, observe exact current-main CI, then browser-traverse quote select/switch/clear and one populated job end-to-end.

Last updated: 2026-10-20

## Director offline continuity cycle — 2026-10-21

- Re-read the canonical Director contract, current engineering state, offline architecture contract, service worker, local IndexedDB client, mobile bootstrap controller and PWA regression tests before making changes.
- Confirmed the existing offline-first implementation is established work, not a new proposal. It includes a user-scoped service-worker page cache, IndexedDB local workspace and mutation queue, pairing/sync endpoints, and explicit offline domain handlers. Full disconnected parity is still explicitly unproven.
- Confirmed the immediate origin blocker: `http://192.168.137.237:8000` is a PC-hosted plain-HTTP LAN origin. When its server process stops, server-dependent requests fail; because it is not a secure browser context, service-worker registration is unavailable there. The source already contains a user-facing diagnostic for this case.
- Added a direct behavioral regression in `tests/js/service-worker-auth-cache.test.js`: prime the authenticated dashboard, make the simulated host unavailable, and verify the service worker serves the saved dashboard from the user-scoped cache.
- Recorded the exact hosting and installed-device acceptance gate in `docs/ZAZU_OFFLINE_FIRST_ARCHITECTURE.md`.
- Changes were committed directly to canonical `main`; no branch was created.
- New test status: **IMPLEMENTED, execution pending**. A source write and a passing static assertion are not runtime proof.
- Deployment reconnaissance: the connected Render workspace currently lists no Zazu EMP service; its listed web service is for ZIA Inventory. No verified stable HTTPS production origin is configured through that workspace. Do not deploy Zazu to ephemeral storage or create a potentially billable production service without durable database/private-file storage and recovery configuration.

### Active target

**P0 — finish VS Code-independent offline continuity:** observe current-main CI for the new offline-navigation regression; establish a durable HTTPS production origin separate from the VS Code server; install/update from that origin; then perform physical-device acceptance with the PC and internet unavailable, followed by reconnect/idempotency/conflict and logout/privacy checks.

**Acceptance rule:** no claim of "solid offline" until current-head CI and the realistic installed-device gate both pass. The host-independent launch gate and full offline-domain parity remain **OPEN**.

## Director offline continuity and V1 workflow hardening — 2026-11-01

**Source baseline for this cycle:** `main` at `ddf2d72bf79365c94f05a1a69784d6a404116cb7`; later same-day commits remain governed by the live `main` ref. All implementation and documentation writes target `main`; no working branch was created.

### Implemented in source

- Offline form submission probes the Zazu origin with a bounded same-origin request. It no longer assumes `navigator.onLine` means the Laravel host is reachable; supported forms can enter the local queue while Wi-Fi/network-interface status remains online.
- The offline mutation, the `local_dirty` marker and the staged optimistic workspace copy are committed in one IndexedDB transaction. The client adopts the new in-memory state only after transaction completion.
- The service-worker cache generation is `zazu-static-v25`, invalidating the older precached offline client after the persistence changes.
- Offline customer creation carries the primary contact, legal name, registration/tax/VAT details, billing address, notes and day/night contacts. Offline profile-photo upload is explicitly declined while disconnected; metadata is not presented as proof that a file was saved.
- Owner pairing with an empty selection now bootstraps a bounded recent working set: up to 25 customers, 25 jobs, 25 quotes and 25 preparation items, alongside the existing supporting catalogue, suppliers, purchasing, inventory, assets, costs, invoices and expenses. The settings screen explains the data copied to the paired device.
- Selected customer/job/quote/preparation records carry stable local identities and relationship references into the phone's IndexedDB store.
- Offline Work creation carries the selected customer's local identity, job type, date, address and services. The server resolves the customer identity within the same business, creates service requirements, and rejects a customer identity belonging to another business.
- The sync dependency sorter is covered with a reversed customer/job batch so the customer mutation must apply before its dependent Work record.

### Regression coverage added

- Browser coverage for an online browser with an unreachable Zazu origin, full offline customer details, queue persistence after reload, and a phone-local customer selected in offline Work creation.
- Laravel coverage for customer profile/contact persistence, bounded pairing bootstrap and parent identities, offline Work/customer linking, service requirement creation, reversed dependency ordering and cross-business customer rejection.
- Existing service-worker behavioral assertions now follow cache generation v25.

### Evidence disposition

- Source and tests: **IMPLEMENTED**.
- Exact-main Laravel, Quality, browser, PHPMD, Psalm and populated upgrade/rollback: **PENDING / UNVERIFIED** until the current `main` Actions runs finish and their exact SHA is recorded. Earlier successful runs do not certify these later changes.
- Real installed-device operation with the PC and internet unavailable: **UNVERIFIED**.
- Stable HTTPS Zazu origin with durable database/private-file storage and recovery: **NOT ESTABLISHED**.
- Full offline parity across every V1 screen, complete bidirectional reconciliation, attachment/file parity and real-world conflict recovery: **OPEN**.
- No readiness-score uplift is awarded for source/test additions alone.

### Next acceptance gate

1. Reconcile current-main Laravel, Quality, browser, PHPMD, Psalm and populated upgrade/rollback runs.
2. Resolve every confirmed regression on `main`; queued jobs are not passes.
3. Establish an independent HTTPS deployment target with durable persistence and tested backup/restore.
4. Install from that origin and complete disconnected/reconnect/logout/multi-user device acceptance.
5. Re-audit complete workflow parity and recovery before V1 release acceptance.


## Director current-main CI reconciliation — 2026-11-01

**Canonical target:** `Leano-Jordan/ZazuEMP` / `main`  
**Exact application SHA:** `6b16b74a7b773ac2797bf7135f85d38b8af0de8d`

### Observed current-head Actions evidence

The following workflows completed successfully against the exact SHA above:

- [Zazu quality](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41290751642)
- [Laravel](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41290751650)
- [Psalm Security Scan](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41290751603)
- [PHPMD](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41290751665)
- [Zazu populated upgrade and rollback](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41290751645)

The quality workflow includes frontend build, the Node behavioral service-worker authentication-cache race suite, and the Laravel application suite. The populated upgrade/rollback workflow is a CI result, not a substitute for a customer-environment drill.

**Browser smoke:** [run 41290751609](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41290751609) was still `in_progress` when reconciled. It is not counted as PASS.

### Acceptance disposition

- Current-main source/automated quality: **TESTED — PASS** for the workflows listed above.
- Browser smoke: **PENDING**.
- Offline launch with PC and internet unavailable on an installed customer device: **UNVERIFIED**.
- Independent HTTPS hosting with durable database and private-file storage: **NOT ESTABLISHED**.
- Real customer-environment backup/restore and rollback: **OPEN**.
- Full offline parity and multi-device conflict/recovery: **OPEN**.

No V1 readiness uplift is claimed from these CI passes alone. Continue the P0 offline-continuity gate, then execute the populated customer workflow and real recovery drills. All work remains on `main`; do not create branches.


## Director browser-regression repair cycle — 2026-11-01

**Target:** `main` only.  
**Failure baseline:** application SHA `6b16b74a7b773ac2797bf7135f85d38b8af0de8d`.  
**Failure evidence:** [Browser smoke run 41290751609](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41290751609), [job logs](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41290751609/job/124801314146).

### Diagnosis

The browser run had 46 passing tests and 6 failures (14 skipped). The failures were concentrated in offline form coverage:

- Job-create offline test selected a server-rendered customer that was not guaranteed to exist in the device-local workspace. The offline handler correctly rejected the unresolved relationship, but its early-return path could leave the generic form submit lock engaged.
- The customer-photo offline rejection path also exposed a submit-lock recovery gap: any unsuccessful offline submit must restore the button so the user can correct the form and retry.
- The customer-contact test attempted to fill Night contact inputs inside a collapsed `details` disclosure.
- The local-customer-to-Work test attempted to click a native radio obscured by its visible choice-card label.

### Changes committed directly to main

- `resources/js/app.js`: unsaved offline form attempts now release the generic submit lock, restore the original button label, and clear `aria-busy` in the finalizer. A rejected local mutation no longer strands the form disabled.
- `e2e/offline-attachments.spec.js`: seed a customer into IndexedDB/local mutation queue before creating a disconnected job; exercise a real local customer relationship and service selection.
- `e2e/pwa-offline.spec.js`: expand the Night contact disclosure and interact with the visible event-type choice card.

### Verification status

These changes are committed on `main` through application commit `df52f7c0a5b0d7c9a21a50db479cae6ee446246e`. Fresh Quality, Laravel, Psalm, PHPMD, populated upgrade/rollback and browser smoke workflows have been triggered for this SHA. Their results are pending; no repair is marked verified until those exact runs complete.

**Do not certify offline continuity from this repair alone.** Installed-device disconnected launch, data persistence after process restart, real sync/reconciliation and production recovery drills remain release gates.


## Director software audit and repair cycle — 2026-11-01

**Canonical target:** `Leano-Jordan/ZazuEMP` / `main`  
**Application/test candidate:** `c3370a317b016be8355df799f1eed37bc55c4488`  
**Working tree discipline:** main only; no branches; no database mutations.

### Verified source repairs

1. **Default phone working-set freshness**
   - Empty-selection pairing no longer freezes the first 25 customer/job/quote/preparation IDs at pairing time.
   - Bootstrap now recalculates the recent working set on refresh.
   - Explicit selections remain pinned; legacy devices with non-empty selections preserve their prior semantics.
   - Regression coverage: post-pairing records appear on refresh; explicit/legacy selections remain bounded and pinned.

2. **Offline asset update validation**
   - Replaced invalid Eloquent `whereKey('!=', $asset->id)` usage with `whereKeyNot($asset->id)`.
   - Duplicate asset tags now produce the intended validation response instead of falling through to the database unique constraint.
   - Regression test verifies both original asset tags remain unchanged.

3. **Sync conflict recovery**
   - Added an actionable Settings panel for per-device sync exceptions, with retry and discard actions.
   - Retry restores the failed IndexedDB mutation to the pending queue; discard removes it locally.
   - Server resolution is transactional and locks both conflict and mutation; repeat/concurrent requests return the authoritative mutation status.
   - Client now follows the server's final status instead of assuming its requested action won.
   - Playwright coverage exercises retry and discard; pending browser run below.

4. **Cross-business offline storage isolation**
   - Replaced the origin-wide IndexedDB workspace with a business-scoped database name.
   - Added the active business ID to the app shell.
   - Legacy storage is migrated only when the stored business and paired-device business IDs both match the active business.
   - A migration tombstone survives local logout, preventing already-cleared legacy queue data from being imported again.
   - Added browser tests for matching legacy migration, queue preservation, logout tombstone behaviour and foreign-business rejection.
   - Service-worker static cache generation advanced to `zazu-static-v28` so installed clients do not continue using the prior offline JS.

### Exact-main CI evidence

Against application candidate `c3370a317b016be8355df799f1eed37bc55c4488`:

- **PASS** — [Zazu quality](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299518886)
- **PASS** — [Laravel](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299518930)
- **PASS** — [Psalm Security Scan](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299518944)
- **PASS** — [PHPMD](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299518954)
- **PASS** — [Populated upgrade and rollback](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299518856)
- **PASS** — [Push on main](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299519274)
- **PENDING at reconciliation** — [Zazu browser smoke](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299519019)
- **SKIPPED** — SonarCloud analysis; not counted as a pass.
- Director health-report workflows succeeded; they are status/reporting workflows, not application-test substitutes.

### Release disposition

Source changes and automated server/security/quality checks are green. Browser smoke is still pending and must be reconciled before the new Playwright cases are accepted.

Still **OPEN / NOT CERTIFIED**:
- Installed-device launch after force-close with both PC/server and internet unavailable.
- Complete offline parity across every V1 workflow and reliable cross-device reconciliation beyond the bounded local working set.
- Independent stable HTTPS hosting with durable database and private-file storage.
- Real customer-environment backup/restore and deployment rollback drills.
- Final privacy/legal and Zazu brand clearance.

No release-readiness score uplift is claimed from these source fixes or CI passes alone.


## Director stable-origin migration audit — 2026-11-01

- Re-fetched current `main` source for the PWA client, service worker, session config, environment example, deployment plan, feature tests and Playwright offline tests.
- Confirmed service-worker registration uses root-relative `/sw.js`; service-worker request/cache logic and app logout checks consistently compare against the active origin. No hard-coded development LAN IP was found in the inspected runtime paths.
- Confirmed `config/session.php` defaults secure cookies to true in production when `SESSION_SECURE_COOKIE` is unset; `.env.example` now correctly leaves that variable unset.
- **Critical migration finding:** browser storage is origin-scoped. The current business workspace uses IndexedDB database `zazu-client-business-<business-id>`; legacy migration reads `zazu-client`; offline attachment blobs use a separate `zazu-phone-attachments` database. A scheme/host/port change creates a separate storage partition. Local queues, conflicts, attachment blobs, service-worker caches and cookies do not automatically transfer from the old HTTP IP origin to the new HTTPS hostname.
- Updated `docs/LOCAL_PC_HOST_DEPLOYMENT.md` with a controlled origin-cutover procedure: drain and verify old-origin mutations/conflicts/attachments; preserve old browser storage; deploy/restore the same authoritative business data; re-install and re-pair on HTTPS; verify record relationships and a one-time test sync; retire old origin only after all devices pass.
- Explicitly recorded that unsynchronized device-only data needs a tested export/import or migration mechanism; do not clear site data or uninstall the old PWA as part of cutover.
- Source changes in this cycle: documentation only. No runtime code, database schema or customer data changed. No tests were run in this remote GitHub-only cycle.
- **Next P0:** inspect the exact origin migration and attachment retry behaviour in the client, add automated guardrails for migration readiness where feasible, then reconcile the latest browser Actions run. Physical LAN HTTPS and disconnected-device tests remain pending until the PC is available.
- All work committed directly to canonical `main`; no branch created.
