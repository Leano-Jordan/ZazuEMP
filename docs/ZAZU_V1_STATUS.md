# Zazu EMP — V1 Software / Commercial Status

**Assessment date:** 2026-11-07
**Repository:** `Leano-Jordan/ZazuEMP`  
**Validated application/test source commit:** `2c9a03e5de80e5c0aceb47724fff6b8d3a8439f1` on canonical `main` (399 Laravel tests / 2,621 assertions pass; browser smoke still running at 2026-11-07 reconciliation). Resolve live main before later work.

## Current position

Zazu EMP has a substantial connected operational foundation across:
- authentication/business context;
- customers and jobs;
- requirements/capabilities;
- quotes and revisions;
- travel and operational costs;
- preparation;
- finance;
- purchasing;
- inventory;
- physical assets;
- reporting;
- responsive/shared UI.

The product is **not yet release-certified**. The current main line contains a broad offline operational foundation, while release certification remains gated by real recovery/rollback evidence, legal/privacy closure and Zazu brand clearance.

## Exact-source CI snapshot — 2026-11-07

Source/test commit: `dd3e1d4c0e03c63c565d12fabf744b93b75dae52`.

- Laravel: **405 passed / 2,715 assertions**.
- Zazu quality + secret scan: PASS.
- Psalm security scan: PASS.
- PHPMD: PASS.
- Populated upgrade/rollback: PASS.
- Browser smoke: IN PROGRESS at reconciliation.
- SonarCloud: SKIPPED, not counted as a pass.
- Business export: 23 owner-only business-scoped CSV datasets; streamed, audit logged, Excel-compatible and formula-injection protected.
- Commercial profitability chain regression: PASS.
- The active Work attachment path, device expiry and backup-v2 integrity changes are included in the executed Laravel suite.

The complete run links and NO-GO gates are in `docs/V1_RELEASE_CHECKLIST.md`. PC/mobile installation and screen rotation do not establish disconnected installed-device acceptance. Real restore/rollback drills and legal/privacy/brand closure remain outstanding.

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
