## Director reconciliation — 2026-11-13 (exact-head CI and archive gate closure)

**Canonical repository:** `Leano-Jordan/ZazuEMP` · **branch:** `main` · **verified head:** `5888123f1941beb478b31870a6e782e0f8ef8053`.

- Exact-head Laravel workflow [43491595380](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43491595380): **PASS** — Laravel suite 486 passed / 1 skipped / 3,475 assertions; PostgreSQL migration, registration and database/private-file recovery passed; dedicated offline-browser acceptance passed.
- Exact-head PHPMD [43491595199](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43491595199): **PASS**. The archive round-trip regression test was split into focused fixture/assertion helpers after PHPMD identified its 128-line test method; no warning suppression was added.
- Exact-head Psalm [43491595173](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43491595173): **PASS**.
- Exact-head Zazu quality, secret scan and CodeQL push workflow [43491594809](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43491594809): **PASS**.
- Populated upgrade/rollback [43491595120](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43491595120): **PASS**.
- Business archive restore is now **IMPLEMENTED / AUTOMATED-VERIFIED** on this head. The round-trip test covers linked tenant records, supplier → purchase order → purchase-order item remapping, tenant exclusion, private attachment bytes and public catalogue media. The passing test confirms the earlier overlapping simple/composite FK remapping correction. This does not replace an operator-run restore on the actual installed target.
- Offline account/session transition and active Work attachment flows are automated-verified by the passing Laravel and dedicated offline-browser jobs. Real phone/tablet persistence and secure-origin installation remain unverified.
- Quote → payment → profit remains **SEEDED AUTOMATED-VERIFIED**; this does not claim a manual reconciliation of real business records.
- Installation recovery remains **CI-VERIFIED / OPERATOR DRILL OPEN**. Business archive restore and installation-wide backup/restore are separate capabilities.
- Physical device acceptance remains open; the owner's current plain-HTTP LAN address does not satisfy the secure-origin installed-PWA precondition.
- POPIA operational controls, privacy notice publication/review, retention and incident procedures, and Zazu name/trade-mark clearance remain open. Do not claim legal clearance.
- No production deployment, live customer data, database or non-main branch was changed.

**Release decision: NOT YET READY.** Automated engineering gates are green; owner-controlled physical installation/recovery and legal/brand gates remain. No evidence score uplift is claimed by this reconciliation.

---

## Director reconciliation — 2026-11-13 (supersedes stale archive status below)

- Latest observed repository documentation HEAD: `7d95ee61da48d5fcf3ec4ed06ddbab0696df7b8d` on `main`.
- Business archive restore is now **IMPLEMENTED / EXECUTION UNVERIFIED**, not “not implemented”. The duplicate-FK remapping correction is in `app/Services/BusinessArchiveService.php` commit `51bba91ddfe4f438b4b979c14f5e67e6864ac048`; the focused round-trip regression fixture is in `tests/Feature/BusinessArchiveTest.php` commit `e4daa4d1b12378479b2990360877f964719d0a5a`. The fixture exercises supplier → purchase order → purchase-order item links and the overlapping simple/composite foreign-key case.
- The correction always resolves foreign-key values from the original archived row, avoiding a second constraint attempting to remap an already-remapped target ID.
- Acceptance remains **OPEN** until the focused archive round-trip and its safety tests execute and pass on a commit containing both changes. The latest combined-status lookup returned no contexts and the commit-workflow lookup exposed no runs; no pass is claimed.
- The archive test currently asserts 28 tables, 11 records and 2 files, plus restored tenant-scoped relationships and private/public attachment payloads.
- Director CI efficiency changes are present on `main`: duplicate PHP verification was removed from Quality, full browser regression is restricted to browser-relevant PRs or manual dispatch, and the unique service-worker regression plus secret scan remain. These workflow edits also require their own Actions evidence.
- Existing installation-level PostgreSQL/database+private-file CI evidence does not close the controlled installed-environment recovery drill. Physical phone/tablet acceptance, POPIA operations and Zazu name clearance remain open.
- No customer data, live database, deployment service or `deploy/render-staging` branch was changed.

---

# Zazu EMP — current V1 gate status (2026-11-13)

**Canonical repository:** `Leano-Jordan/ZazuEMP`  
**Canonical branch:** `main`  
**Verified application/test HEAD:** `245aa47f7ef74e6e133fff867670ecafabb603b8`.

## Current release position

The exact-head automated test batch now passes for offline session transitions, active Work attachments, tenant/device isolation, multi-customer sync identity reconciliation, commercial reconciliation, PostgreSQL migration/backup/restore, static quality and populated upgrade/rollback.

**Technical V1 is still NO-GO for owner acceptance**, not because these automated suites are failing, but because the installed-device and operational recovery checks have not been completed. The business-level restorable archive is also not implemented.

## Current V1 reconciliation

- **Offline account/session security — AUTOMATED PASS.** Chromium browser acceptance verifies interrupted logout preserves the signed-in session and queued work; switching from owner to manager in the same business preserves the trusted business workspace; expired web-session re-authentication preserves the pending offline mutation and device pairing. Business-switch installation-ID reuse is rejected with HTTP 409 without changing the original business's trusted device.
- **Offline Work attachments — AUTOMATED PASS.** The active Work attachment workflow passes browser acceptance for local queueing, reconnect upload, server-ID reconciliation, duplicate retry, offline file reuse, cross-tab re-pair and stale asynchronous response protection. Do not count the dormant `public/offline-attachments.js` as the active implementation.
- **Multi-customer sync — AUTOMATED PASS.** Two queued customer creates reconcile to distinct server IDs and replay without duplicates. The owner's actual existing IndexedDB customers remain unobserved by CI and must not be recreated.
- **Commercial reconciliation — AUTOMATED PASS.** A seeded scenario validates the complete accepted quote → invoice → deposit → final payment → actual cost → event profit path. Values: R11,500 invoiced; R3,450 deposit; R8,050 final payment; R2,800 actual cost; R8,700 profit; 75.65% margin. This is not a claim that live customer records have been manually checked.
- **Business data portability — PARTIAL.** Twenty-three business-scoped CSV datasets are exportable and customer CSV/XLSX import preview exists. These do not provide a relationship-complete business backup/import or restore uploaded files. A tenant-scoped archive/import remains open.
- **Installation recovery — CI VERIFIED, OPERATOR DRILL OPEN.** The CLI backup/restore supports SQLite, MySQL and PostgreSQL; PostgreSQL database and private-file round-trip passes in CI. The controlled installed-environment recovery drill remains required.
- **Physical acceptance — OWNER VERIFICATION REQUIRED.** Real secure-origin phone/tablet installation, disconnected workflow, local record and attachment persistence, force-close/reopen and reconnect sync remain unverified.
- **POPIA / legal / brand — OPEN.** Operational controls, retention/disposal ownership, incident handling, and professional Zazu name/trade-mark clearance remain to be completed.

## Exact-head CI evidence

- Laravel + PostgreSQL + dedicated offline browser acceptance: [43473670275](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43473670275) — **PASS**.
- Full Playwright browser regression: [43473670319](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43473670319) — **PASS**.
- Zazu quality: [43473670378](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43473670378) — **PASS**.
- Psalm: [43473670232](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43473670232) — **PASS**.
- PHPMD: [43473670420](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43473670420) — **PASS**.
- Populated upgrade/rollback: [43473670272](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43473670272) — **PASS**.
- Push-on-main: [43473671064](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43473671064) — **PASS**.
- Director Health: [43475031359](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43475031359) — **PASS**.
- SonarCloud: [43473670372](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43473670372) — **SKIPPED**, not counted as a pass.

**Evidence-maturity score remains 71/100** pending a formal re-score. Passing test corrections do not automatically justify a score increase.

---

# Zazu EMP — V1 Software / Commercial Status

**Assessment date:** 2026-11-09  
**Repository:** `Leano-Jordan/ZazuEMP`  
**Canonical branch:** `main`  
**Verified application/test source commit:** `d55539ddfd52c7fa5140714288c5885dbcc99e3a`.

## Current position

## Latest Director scorecard — 2026-11-09

**V1 evidence-maturity score: 69/100 (3.46/5), up from 60/100.**  
**Verified application/test source:** `d55539ddfd52c7fa5140714288c5885dbcc99e3a`.

- Laravel: 414 passed / 2,938 assertions.
- Browser smoke: 65 passed / 22 skipped / 0 failed or flaky.
- Zazu Quality + secret scan, Psalm, PHPMD, populated upgrade/rollback and push-on-main: all passed.
- Director Contract Lint passed on the preceding control-plane documentation commit; it was not triggered by this test-only source commit.
- SonarCloud: skipped, not a pass.

The uplift now also covers recovery/data portability: CI restores the seeded populated business database and private media, verifying key records and 14 operational table counts. This moves recovery from 2/5 to 3/5. The real installed-environment restore remains open.

**Release position: NO-GO.** Remaining gates: secure-origin installed-device offline acceptance, real installed-environment database/private-media restore, real deployment rollback, trusted-device acceptance, operational privacy/legal closure and official brand clearance. LAN launcher implementation is committed but has not yet been verified on the owner's PC.

See `docs/ZAZU_V1_SCORECARD_2026-11-04.md` for the full 13-domain scorecard and `docs/V1_RELEASE_CHECKLIST.md` for exact CI run links.

Zazu EMP has a substantial connected operational foundation across authentication/business context, customers and jobs, requirements/services, quotes and revisions, preparation, purchasing, inventory/assets, costs, finance, reporting, search, attachments and responsive UI.

Historical automated verification snapshot (superseded by the Director scorecard above):
- Laravel: **407 passed / 2,758 assertions**
- Zazu Quality + secret scan: **PASS**
- Psalm: **PASS**
- PHPMD: **PASS**
- Populated upgrade/rollback: **PASS**
- Browser smoke: **65 passed / 22 skipped / 0 failed**
- Director health report: **PASS**
- SonarCloud: **SKIPPED**, not a pass

Run IDs and release evidence are recorded in `docs/V1_RELEASE_CHECKLIST.md`.

**Release position: NO-GO.** The application/test source has a fully green automated verification batch, but this is not commercial release certification. Installed-device disconnected operation, representative customer data/private-media restore, deployment rollback, operational legal/privacy closure and official Zazu brand clearance remain outstanding.

## Current V1 capability reconciliation

- Global search now includes business-scoped metadata search of job attachments as well as the existing record domains.
- Phone-local Work attachment queue/upload/download and reconnect safeguards exist and pass automated coverage; physical installed-device acceptance remains open.
- Business export now covers 23 owner-only, tenant-scoped CSV datasets.
- Customer CSV/XLSX import preview/matching exists. Product/service and supplier import coverage remains an R499 commercial gap.
- The V1 system-feedback requirement is met by meaningful success/error toasts, status indicators and confirmation flows. Scheduled reminders are a separate R799 team-management gap.
- Staff assignment to work records is not implemented in the current data model. It is recorded as an R799 team-management gap, not falsely marked complete or treated as a R299/R499 release blocker.
- The dashboard remains owner-accepted as a design target; exact-head browser regression is green. No styling rollout was implied by that acceptance.

## Current release gates

1. Installed-device offline launch, local Work/attachment operation, force-close/reopen and reconnect acceptance on the intended secure origin.
2. Representative backup → restore → data/private-media verification on an isolated target deployment.
3. Real deployment rollback exercise.
4. POPIA/privacy/terms, retention/disposal ownership and incident escalation closure.
5. Official Zazu name/trade-mark clearance and professional review.
6. Final Director release audit after the above evidence is recorded.

## Historical-chat reconciliation

| Historical idea | Current repository state on 2026-10-03 |
|---|---|
| Event/business operational core | IMPLEMENTED — customer, job/event, requirements, capabilities, quotes, preparation, purchasing, inventory/assets, finance and reporting are connected around the operational record. |
| Experience modes | IMPLEMENTED foundation — Basic / Intermediate / Advanced are stored per business membership and already influence dashboard detail. |
| Primary niche focus | NEW FOUNDATION — stored per business membership with four presentation choices. Sound & DJ is the third niche and the concrete first iteration reference. |
| Progressive disclosure | PARTIAL / FOUNDATION — hierarchical navigation already exists; the workspace-focus screen now uses a native disclosure for secondary experience detail. Wider page-by-page adoption remains future iteration. |
| Command palette | IMPLEMENTED — global workspace search / quick access is present. |
| Niche-specific dashboard emphasis | PARTIAL — selected niche now appears in workspace context and dashboard copy; deeper niche-specific surfacing is not yet implemented. |
| Full offline business-data operation | PARTIAL / IN PROGRESS — IndexedDB local store + durable queue + reconnect sync cover the documented operational mutation domains. Phone-local Work attachment queue/upload/download is now IMPLEMENTED AT SOURCE with business-scoped persistence, batch idempotency, reconnect upload and attachment download caching; exact-head Laravel/Playwright execution and installed-device proof remain pending. Full disconnected parity remains open. |
| Online/offline parity | DIRECTION ACTIVE — the phone workspace is now a real local operational surface; it is not yet a complete disconnected replacement for every desktop workflow. |
| Local host / Wi-Fi clients | NOT IMPLEMENTED — remains an architecture target, not a current capability. |
| Historical infrastructure/capacity claims | EXCLUDED — not treated as requirements without repository or measured evidence. |

## Current-head verification boundary

The last proven application baseline was `92730f7cb660ae06d9df3c014eccf4f0d3e9188e`. The previously recorded candidate `aa1e3ca8921625abca16fe4bbc8546746b4bf135` is historical and must not be presented as the current application head. Subsequent source changes and the latest attachment retrieval correction require fresh exact-head Actions reconciliation. This status page is not a substitute for that run evidence.

## Release-critical work remaining

### Commercial completion
- Customer-facing quote delivery/presentation
- Quote acceptance/deposit lifecycle
- Complete invoice/document workflow
- Payment/reconciliation completion

### Integrity and control
- Complete authorization coverage
- Complete audit/activity continuity
- Parent/child and concurrency verification across critical mutations
- Representative existing-database migration/upgrade verification

### Recovery and operations
- Real backup/restore drill
- Restore of representative data and private media
- Rollback procedure
- Operational diagnostics/observability
- Final deployment/configuration review

### Previous verified release evidence

- Laravel: **202 passed / 1,174 assertions**
- Browser: **7 passed / 8 skipped**; the complete registration journey runs once on Chromium to avoid shared-IP registration throttling, while responsive entry coverage is separate
- Zazu Quality: **passed**
- PHPMD: **passed**
- Psalm: **passed** after removing the redundant init step and applying a narrowly scoped Laravel 13.34.0 compatibility shim for one unsupported `@phpstan-this-out` annotation
- Fresh SQLite migration, Blade compilation, asset build/manifest verification and application configuration validation: **passed**

### Runtime proof still required on the latest head
- Browser critical-path traversal
- Desktop/mobile verification
- Light/dark verification
- Accessibility verification
- Real local database/browser verification where CI cannot prove the owner's environment

## Engineering-system status

The repository now has a state-driven AI engineering control system:

- Morpheus/Jarvis control plane
- Discovery & Design
- Builder
- Guardian with Forensics / Break / Security / Data Integrity modes
- Release
- UI/UX Improvement
- persistent STATE / DECISION / REGRESSION / READINESS ledgers
- anti-loop and context-firewall controls

This documentation refactor is **engineering-control work**, not application feature completion.

## Evidence language

IMPLEMENTED, TESTED, VERIFIED and PROVEN are intentionally different states.

Historical CI/test figures in older records remain historical and are not restated as current evidence unless a current run is observed.

## Direction

The next Zazu engineering effort should close the highest-risk remaining release gate: populated commercial workflow → real recovery → representative upgrade → rollback → final Director certification.


## Jarvis update — 2026-10-07

Historical 2026-10-07 prototype note: a standalone phone-local attachment transfer layer was prototyped, but later inspection confirmed it is orphaned and not loaded by the authenticated application. It is not a working V1 feature. Server-side upload/download/list endpoints exist; the active Work panel remains online-only.

Remaining attachment gate: prove the browser/device flow against a real populated workspace, including a newly created offline job receiving its server identity before its queued attachment uploads.


### Offline attachment integration correction — 2026-11-01

The earlier phone-local attachment transfer note is not a verified active feature. Current-main inspection shows `/offline-attachments.js` is precached but is not loaded by the authenticated application layout and expects legacy globals absent from the current app shell. The Work screen supports online job attachments only. Phone-local attachment queueing/upload/download remains **NOT IMPLEMENTED** until the component is properly integrated and covered by browser/device tests. See `docs/ZAZU_OFFLINE_FIRST_ARCHITECTURE.md`.


### Current Director reconciliation — 2026-11-01

- The Work attachment panel now exposes the existing private download endpoint alongside Remove. The endpoint retains the `work.view` permission and controller business-isolation checks.
- A feature regression now checks that the Work page renders the download link and that an authorized user receives the private file download. A separate existing regression continues to reject foreign-business attachment downloads.
- These changes are committed to canonical `main`; their exact-head Laravel/quality/browser execution is **PENDING**. No current-head test pass is claimed here.
- The active phone PWA still does not load the legacy offline attachment script. Offline attachment queue/upload/download remains **NOT IMPLEMENTED** in the active app. Do not confuse online Work attachment retrieval with offline phone attachment parity.
- Stable HTTPS local-PC hosting, origin migration, disconnected installed-device launch, full offline parity, populated backup/restore, rollback and legal/privacy/brand closure remain open release gates.


## Director recovery-scope correction — 2026-11-04

A security review found that the former business settings backup/restore endpoints invoked installation-wide commands. The commands snapshot or replace the entire configured database and private storage, so exposing them to an individual business owner created cross-business disclosure and destructive-restore risk. The HTTP endpoints and UI controls have been removed. The settings page now states that installation recovery is operator-managed and business self-service export/restore is not available in this build.

The CLI backup/restore commands remain available for controlled installation-operator use. Automated command-level round-trip and hostile-archive tests remain supporting evidence, not proof of a real recovery drill. Tenant-scoped export/restore remains an explicit V1 gap.


## Offline transition and attachment hardening — 2026-11-04

- Phone-local attachment storage is now business-scoped; pending attachments are included in logout assessment; active-business attachment storage is cleared during local session cleanup.
- This is storage/lifecycle hardening only. Offline attachment upload/download remains **NOT IMPLEMENTED** in the active Work screen.
- DEC-019 retains the owner-approved business-trusted device model. Logout, expired sessions, same-business account changes, business switches, interrupted cleanup, late responses and remote device revocation still require acceptance testing.
- Exact-head Actions are pending. See `docs/ZAZU_OFFLINE_TRANSITION_AND_ATTACHMENT_HARDENING_2026-11-04.md`.

## V1 specification scorecard — 2026-11-04

The current evidence-maturity assessment is **3.0/5 (60/100)** across 13 V1 capability areas. This is an evidence score, not a percentage of features built. The detailed area-by-area score, method, blockers and execution order are recorded in `docs/ZAZU_V1_SCORECARD_2026-11-04.md`.

The product has a substantial connected commercial and operational core. It remains **NO-GO for commercial release** while real recovery/rollback, bounded offline acceptance, the offline account/session security challenge, tenant-scoped export/restore decision, legal/privacy operations and Zazu brand clearance remain open. Latest-head CI for `d1e2a4397b064d3fbfe7fc2cf1dc59236e00922f` was queued at assessment time.


## Exact source-head verification — 2026-11-04

Application source SHA `d1e2a4397b064d3fbfe7fc2cf1dc59236e00922f`: Laravel, Zazu quality, Psalm, PHPMD, populated upgrade/rollback and push-on-main checks passed. Browser smoke remains in progress; SonarCloud was skipped and is not counted as a pass. See run links in `docs/ZAZU_V1_SCORECARD_2026-11-04.md`. The documentation-only main commits after this source SHA do not alter application code. Do not close the browser gate until it passes.


## Phone-local Work attachment integration — 2026-11-07

**Source status: IMPLEMENTED. Runtime status: UNVERIFIED.**

The active authenticated Work attachment panel is now connected to the current business-scoped `ZazuOffline` client. The phone can atomically save a selection of up to 10 supported files (20 MB each) into its business-scoped IndexedDB attachment store, retain each UUID idempotency key and retry upload through the paired device API. The Work panel can read locally cached files while disconnected; paired devices also fetch and cache private server attachments for later offline access. The existing Laravel authenticated upload/download controls remain the online path.

The sync client keeps the local Blob until it receives a verifiable server attachment ID. Rejected or interrupted uploads remain on the phone with retry/error state. Logout assessment continues to include pending attachments; business-scoped local session cleanup continues to clear the attachment store. Uploads require a server-backed event ID. Pending records from the previous phone queue that retain `event_local_id` are now reconciled against `state.jobs` when the job receives its server ID, before upload is attempted.

Regression coverage now includes:
- Laravel source-contract assertions for the active offline attachment client and Work panel;
- Playwright scenarios exercising a real Work page, offline local file persistence, reconnect upload with the original idempotency key, remote attachment caching, and `event_local_id` reconciliation after offline Work identity resolution;
- existing server idempotency tests for duplicate retries and rejection of changed file content or event ownership;
- paired-device API tests for attachment-list metadata, private download success, foreign-business list denial and foreign-business private download denial (with the foreign file confirmed present).

The service-worker cache was advanced to `zazu-static-v30` because `/zazu-offline.js` is served cache-first. All source and test changes are on `main`; latest observed implementation/test SHA before the documentation update: `bd47ec672ea0f4b609614ce24c3baa9d1ac4bd0a`.

**Evidence boundary:** GitHub combined commit status returned no status contexts for the earlier `bc1de6224971803bdecee18dc8bcbed7781c5287` head; no Laravel, build or browser pass is claimed for the new source head. The added Playwright test and feature assertions remain unexecuted until exact-head CI is observed. Physical installed-device acceptance and disconnected Work-page availability remain unverified. The new browser regression covers attachment parent-ID reconciliation once the job has a server ID; the complete rendered create-offline-job → sync-job → attach-file user journey is still unverified.


## Trusted-device session transition coverage — 2026-11-07

The session-transition matrix now has additional source-level regression coverage:

- A Playwright scenario preserves a paired business workspace and queued mutation when the `/up` authentication-host probe is unreachable; after connectivity returns it completes logout, re-authenticates the same account and checks that the trusted business device and pending mutation remain available.
- Owner device revocation is tested against bootstrap, pull, push and attachment-list APIs using the formerly valid bearer token; cross-business owner revocation remains denied.

**Implementation/test status:** tests added on `main`; **execution status: UNVERIFIED**. The exact current-main Laravel and browser workflows must pass before the security/offline evidence score can rise. Physical-device testing, expired web-session acceptance, interrupted logout, stale in-flight response handling and the owner lost-device runbook remain outstanding.


## Recovery integrity hardening — 2026-11-07

New backups are now format version 2 and include SHA-256 checksums for the database snapshot and every private-storage payload file. Restore validates the exact payload/checksum file set and streams the payload hashes before extraction or installation mutation. Version-1 archives remain accepted for backward compatibility; new version-2 archives require the checksum manifest. Automated tests cover checksum generation and rejection of a modified private file without replacing the currently installed private probe.

**Implementation:** committed on `main`. **Verification:** exact-head Laravel/quality execution remains UNVERIFIED. Checksums protect against accidental corruption/content drift, not archive forgery; the manifest is not signed. The real operator backup → restore → record/media/access verification drill remains a release blocker, so recovery readiness stays at 2/5 and V1 remains NO-GO.


Backup compatibility coverage was extended after the initial integrity note: the test suite now verifies rejection of a version-2 archive with no checksum map and restores a valid version-1 archive without checksums. These cases are committed but remain unexecuted until exact-head CI is observed.


## CSP inline-style attribute reduction — 2026-11-10

- Removed the six dynamic image `style=` attributes from the public landing page. Their image URLs now flow through response-nonce-authorized CSS custom properties and class selectors in the landing document head.
- Removed the dynamic inline `style=` attribute from the admin landing-image preview. Its per-field preview rule now uses a response-nonce-authorized style element and a stable field-derived element ID.
- Replaced the workspace-recovery sign-out form's inline `display:inline` declaration with the `zazu-auth-inline-form` class in the application stylesheet.
- Added SecurityHeadersTest source assertions that these active Blade views contain no inline style attributes and that the landing/admin dynamic CSS uses nonce-authorized style blocks.
- Commits: `4c80e28` landing page; `40711e5` admin preview; `b51f15a` recovery form; `e6735d0` stylesheet; `f8a236a` regression assertions.
- Guardian review: source-level inspection confirms the three changed views have zero `style=` attributes. The legacy `public/offline-attachments.js` contains inline attributes but is not imported by the active authenticated layout; it remains a dormant legacy asset and was not reactivated or modified. `resources/js/app.js` also uses CSSOM `style.setProperty/removeProperty` for the floating helper's measured nav-avoidance position, so the policy's `style-src-attr 'unsafe-inline'` allowance is deliberately retained until that runtime behaviour is redesigned and tested.
- Verification boundary: changed files re-fetched; no PHPUnit, Blade compilation, browser run or exact-head CI execution is available here. No CSP relaxation was made, no readiness score uplift is claimed, and V1 remains **NO-GO**.
