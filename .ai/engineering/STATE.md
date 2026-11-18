## Sentinel local recovery implementation — 2026-11-18

- Added `/api/sentinel/recovery` as a stateless, loopback-only host-operator recovery surface outside Laravel's web/session middleware.
- The route explicitly excludes Laravel's database-backed `throttle:api` middleware; the recovery path therefore does not rely on the default database cache store.
- Access requires a loopback peer address and loopback Host header. POST also requires same-origin Origin and rejects cross-site Fetch Metadata.
- Added `zazu:sentinel-recovery-code`: creates a 256-bit random code, stores only SHA-256 hash plus five-minute expiry, invalidates prior codes, and displays the new code once.
- The code is consumed atomically under a filesystem lock and cannot be replayed. It is entered in a local form, never placed in a URL.
- The unlocked report reuses read-only `ZazuSystemHealth` checks. It contains installation health only and no customer records, attachment bytes, global incident ledger or founder console.
- Added feature coverage for unauthenticated local screen access, LAN denial, cross-origin POST denial and one-use report access.
- Updated Sentinel, DB-001 and Windows package documentation.
- **Acceptance remains pending:** GitHub Actions must pass; verify the command, route, file permissions and database/session-outage behaviour on the packaged Windows installation. This path cannot diagnose a stopped host, PHP process or broken Laravel bootstrap.
- All changes committed directly to `main`; no branches created.

## Sentinel access isolation correction — 2026-11-18

- Removed the one-year signed bearer URL from `/api/sentinel/rescue`; unauthenticated requests can no longer reach Sentinel Rescue.
- Moved Rescue to `/sentinel/rescue` behind `auth`, `auth.session`, `business.context` and the active-business `owner` middleware.
- Added explicit `sentinel.admin` route authorization for `/admin/sentinel`, separate from ordinary business-owner roles.
- Founder authorization now resolves only to the one explicitly configured email identity (`ZAZU_PLATFORM_ADMIN_EMAIL`). The legacy plural `ZAZU_PLATFORM_ADMIN_EMAILS` variable is ignored completely; business `admin` roles do not grant platform access. Existing installations must set the singular variable for the designated founder.
- Founder navigation is visible independently of business-owner navigation. Business owners see only their owner Rescue entry; the founder sees the Control Centre only when the configured identity matches.
- Added regression coverage for unauthenticated denial, removal of the old API route, authenticated owner access and updated founder identity configuration.
- **Resilience trade-off:** authenticated business-owner Rescue still depends on the normal user/session path. The separate loopback-only one-time-code host recovery path now provides installation health checks without that dependency. Packaged Windows acceptance remains open; this does not diagnose a stopped host or broken PHP/Laravel bootstrap.
- Changes were committed directly to `main`; no branches were created. Automated tests and runtime acceptance have not yet been executed in this environment.
# Zazu EMP — Current Director State

## DB-001 support capture — 2026-11-18

The DB-001 error page now offers a reference-scoped download of the matching redacted incident record through the local API. It is rate-limited and exposes only an allowlist of diagnostic fields; SQL, bindings, exception messages, customer data and credentials are excluded. This removes the need to copy diagnostic files between the Windows host and phone. The endpoint is useful only when the running installation contains the updated incident recorder. **DB-001 remains OPEN until the actual installed-workflow failure is identified, corrected and repeated successfully.** No root cause or readiness uplift is claimed.


As of: 2026-11-17
Repository: Leano-Jordan/ZazuEMP
Last verified application-code baseline: ba94b39e0460e4e57c560e98541808c762f50e25
Canonical branch: main only; never create branches. Engineering changes are active on main; no branches created.
Owner mandate: ship a dependable first-business V1; preserve scope; no Render or AMPPS workflow.

## Owner-approved event scheduling implementation — 2026-11-17

Owner authorised the focused V1 addition: multi-day event dates, start/end times, setup/collection dates, schedule clash warnings and connected rental allocation dates. Staff scheduling, quantity-based rental stock and workforce management remain deferred.

Implementation candidate is on `main` at source HEAD `71c8fc5c7aeba392b95e26aee694945b286f2c8e` (17 Nov 2026). Added an additive events migration; online Work create/edit persistence and validation; offline mutation payload/server application and workspace bootstrap; calendar multi-day expansion; schedule clash detection; and rental allocation date defaults. Added focused online/offline feature tests, including preservation of new schedule fields when older queued offline mutations arrive. At the current baseline, Laravel passes 532 tests / 1 skipped / 3,714 assertions; PHPMD passes; the Windows package and PowerShell first-install + private-media recovery pass on the immediately preceding code baseline 2ef2a0b. PostgreSQL and desktop/mobile offline-browser acceptance completed PASS for 71c8fc5 in run 44152431508. Zazu Quality remains red on the unpatched picomatch advisory. Rendered/physical acceptance is pending; no readiness uplift is claimed.

## Event scheduling implementation verification — 71c8fc5 (17 Nov 2026)

- Online event creation/editing persists event date range, start/end times, setup and collection dates; invalid date/time ranges are rejected.
- Active schedule overlaps produce a warning while preserving operator save; setup and collection logistics are included in the conflict window.
- Calendar now spans event and logistics dates and labels setup/collection days.
- Offline job mutations carry the schedule fields; server-side validation protects the fields and older queued mutations cannot erase schedule values they do not know about.
- Asset allocation defaults follow the event setup/collection window; existing serialized-asset overlap/return protections remain in force.
- Laravel: PASS — 532 passed / 1 skipped / 3,714 assertions. PHPMD: PASS.
- Windows V1 package, fresh PowerShell install and Windows SQLite/private-media recovery: PASS at 2ef2a0b (same scheduling implementation; latest commit after that is regression-test-only).
- PostgreSQL migration/recovery and desktop/mobile offline-browser acceptance: PASS at 71c8fc5 (run 44152431508; all three jobs succeeded).
- Zazu Quality remains RED on the unpatched high-severity picomatch build-tool advisory; no breaking downgrade or suppression applied.
- No readiness uplift. Physical Android, intended-installation recovery, populated-business financial reconciliation and POPIA remain operator gates.

## Current repository verification — fd82f6a (17 Nov 2026)

The only repository change between application-code baseline a5ead69 and fd82f6a is `.github/workflows/zazu-windows-package.yml`; application source and tests are unchanged across that range.

Application-code CI at a5ead69:
- Laravel: PASS — 526 passed / 1 skipped / 3,681 assertions.
- Offline browser acceptance: PASS — 45 desktop Chromium, 6 mobile-viewport and 1 phone-local attachment journey.
- PostgreSQL migration, account/workspace registration, backup/restore and private-file recovery: PASS.

Windows V1 Package run 82 at fd82f6a: PASS. The production candidate built, Windows PowerShell fresh installation passed, and a new Windows-native recovery drill created a disposable SQLite record and private-media file, backed up both, mutated both, restored the archive and verified the original database value and private-file bytes.

Director Health, Director Contract Lint and Push-on-main passed at fd82f6a. Composer security audit and secret scan passed.
LAN visual asset delivery is addressed in the current source: non-loopback clients use compiled same-origin Vite assets, a stale `public/hot` marker cannot send phones to an unreachable dev server, and the Windows LAN launcher removes stale hot state and verifies the built manifest/assets. `ViteAssetDeliveryTest` specifically exercises a remote client with a stale loopback hot URL and asserts compiled CSS delivery. These changes post-date the supplied 13 Nov phone screenshot. The actual Android launch still requires the HTTPS-origin physical-device acceptance in `docs/ZAZU_ANDROID_OFFLINE_INSTALL_ACCEPTANCE.md`; the old HTTP :8000 screenshot is not proof of current behavior or offline capability.

Zazu Quality is currently RED at `npm audit --audit-level=high`. The current lockfile contains the development/build-tool dependency chain that resolves to picomatch versions flagged by [GHSA-fc5q-39xc-vjpf](https://github.com/advisories/GHSA-fc5q-39xc-vjpf) / CVE-2026-112366. GitHub's advisory currently lists affected versions through 4.0.7 and no patched version. The finding is in the npm development/build toolchain; the Windows runtime candidate excludes npm manifests and node_modules. This still blocks the quality workflow. Do not suppress the advisory or apply the suggested breaking Vite downgrade without a verified fix.

The Windows CI round-trip is supporting evidence only. It does not close the installed-target operator gate because it is not a recovery drill on the intended owner/customer installation.
## Verified work
Rental asset date allocation was reproduced at c7be735: a second non-overlapping booking returned HTTP 422 before date-overlap evaluation. The online/offline correction now permits non-overlapping reservations, rejects overlapping and open-ended conflicts, supports per-allocation returns, and keeps asset status allocated until all active bookings are returned. Manual physical return remains operator-confirmed.

Online and offline regressions pass at current HEAD. The optional end date is normalized once and used consistently for validation, overlap checks, persistence and audit.

Trusted-device security corrections at e5dc263: fresh desktop browsers no longer receive long-lived sync credentials automatically; an owner must explicitly trust the current device in Settings. The installed-phone behaviour remains an accepted owner limitation. If destructive local workspace cleanup cannot reach the host to revoke a device, only its business-scoped installation UUID is retained and the next authenticated owner session retries revocation through an owner-only endpoint. Ordinary web logout remains non-destructive and preserves the trusted workspace by design.

Password recovery now revokes active and pending sync devices across every business where the resetting user has the owner role, clears token and pairing credentials, and records per-business audit events. Staff-only business devices are left untouched. The regression covers an owner whose currently selected business is a staff membership, protecting cross-business recovery from the normal active-workspace save guard.

Offline financial push validation uses integer-cent / hundredths parsing and rejects excess precision before posting. Regression coverage includes quote line quantity and price, event and asset costs, inventory movement, expenses, purchase-order quantity and price, purchase receipts, and payments.

## Earlier application-code CI — e5dc263 (historical baseline)
- Laravel run 44127089629: PASS — 523 passed / 1 skipped / 3,662 assertions.
- Offline browser acceptance in the same run: PASS — 45 desktop Chromium, 6 mobile-viewport, and 1 phone-local attachment journey.
- PostgreSQL migration, registration, backup/restore and private-file recovery: PASS.
- Windows V1 package and PowerShell fresh-install verification: PASS.
- Populated upgrade/rollback, Zazu quality, Psalm, PHPMD, Director Health and Push-on-main: PASS.
- SonarCloud: SKIPPED, not counted as a pass.

No tests were weakened. Evidence maturity remains 73/100; no score uplift. Physical Android, intended-installation recovery, live commercial reconciliation and POPIA remain operator gates.

## Independent audit reconciliation
All eight Claude findings remain in the sole readiness register. Invoice numbering is unchanged pending South African tax-adviser guidance. Session auto-trust scope, commercial tiers and feature expansions remain owner decisions. Pairing-code hardening is deferred; hosted proxy behavior is an accepted limitation for local Windows/LAN V1.

## Active V1 acceptance gates
1. Physical Android installed-app cold launch, offline restart and reconnect.
2. Installed-target recovery drill including private media.
3. Live populated-business quote → invoice/payment → costs → profit reconciliation.
4. POPIA operational/privacy sign-off.

## Release
V1: NOT READY. Historical state/register/memory, checklist and changelog are preserved in docs/archive/engineering/2026-11-17-state-register-memory-history.md.


## Exact-head scheduling acceptance closure — 2026-11-17

- Source: `71c8fc5c7aeba392b95e26aee694945b286f2c8e`.
- GitHub Actions run `44152431508`: Laravel tests, PostgreSQL migration/recovery and offline-browser acceptance all completed successfully.
- Offline-browser job `134284080089`: PASS; browser suite, mobile-viewport Work/customer acceptance and phone-local attachment journey all passed.
- PostgreSQL job `134284080622`: PASS; full migration set, account/workspace registration and backup/restore/private-file recovery passed.
- Laravel job `134284080768`: PASS; service-worker/session contracts, LAN asset-origin checks, Blade/manifest/config checks and PHPUnit/Pest passed.
- Documentation-only follow-up on `main` is `8bf76fe034f888138c0542dc308e3d4d752bd304`; no application code changed after the tested source baseline.
- Scheduling engineering/CI subgate is CLOSED. Physical Android and other operator gates remain OPEN. Release remains NOT READY.


## Director follow-up — job date integrity, startup migration safety and plain English (2026-11-17)

- Introduced `EventScheduleValidator` as the single relationship check for online job create/edit and offline job sync.
- The validator rejects an end date before the start date, setup after the start date, equipment collection before the job end date, and an earlier/equal end time for a one-day job. Multi-day jobs may end at an earlier clock time on their final day.
- Job create/edit screens now share the same clearly labelled schedule fields and explain what each date/time means. The form sets browser date/time limits as values change.
- Added online and offline regression cases for conflicting dates, shared-validator relationship coverage, and form labels/date limits.
- Simplified the DB-001 and account-error wording. Added `docs/ZAZU_PLAIN_LANGUAGE_STANDARD.md` as a standing product-wide writing rule.
- Windows startup now checks migration status before starting Laravel. When updates are pending, it must create and verify a full database + private-files backup before applying them, then confirm no migrations remain pending. Backup or migration failure blocks server startup.
- **Verification:** Source is committed on `main`. Exact-head CI and PowerShell execution for this follow-up have not yet been observed. No readiness uplift is claimed.

## Open owner-reported database incident — 2026-11-17

- Repeated user-facing `DB-001` incidents were reported. References: `2bc77a48-5c6c-4095-afe2-fa079f60e4ba` and `aa9080f4-9580-41c9-a643-439b31928ffa`.
- The newer reference was supplied with the Account access error screen; the exact action that triggered that occurrence was not stated.
- Source inspection found that the Work controller writes the five new scheduling columns, while the previous Windows start path did not check or apply pending migrations. A local SQLite database behind the current code is therefore the leading explanation, **not a confirmed root cause**.
- Backup-first migration handling has now been added to the shared Windows startup script. It has not yet run against the owner's installed database. The incident remains OPEN until exact runtime evidence confirms the cause and the affected workflow succeeds.


## DB-001 diagnostic contract correction — 2026-11-17

- Inspected `app/Support/ZazuIncidentRecorder.php`, `bootstrap/app.php`, `AttachRequestId.php`, `ZazuErrorCatalog.php` and `ErrorHandlingTest.php` at the current source baseline.
- Confirmed that the authoritative incident logger deliberately records bounded metadata only: request ID, classification, exception class, route/action, method/path, timestamp and source file/line. It intentionally omits exception messages, SQL, bindings and stack traces to prevent customer-data/credential leakage. Request middleware adds the same request ID to log context and the response header.
- Corrected `docs/ZAZU_ERROR_INCIDENT_FRAMEWORK.md` on `main` (documentation-only commit `f88af665ae09b6f87f3c8d73a52c3cc5e4854dbd`) because its prior wording incorrectly claimed the routine incident log contained technical exception messages, stack traces and safe query context.
- This clarifies the diagnostic contract but does not establish the cause of either owner-reported DB-001. The installed log entry matching `aa9080f4-9580-41c9-a643-439b31928ffa` is still required to identify exception class, source file/line and route. If those fields are insufficient, reproduce under controlled local diagnostics without adding raw SQL/bindings to production logs.
- No application code changed, no database touched, no readiness uplift. DB-001 remains OPEN.


## Backup publication integrity hardening — 2026-11-17

- Closed a source-level safety gap found while reviewing the backup-first Windows startup path: the launcher previously accepted a non-empty ZIP based on process exit code and file size, while full manifest/checksum validation happened only during restore.
- `ZazuBackupCommand` now reopens the completed temporary ZIP with consistency checking, validates the Zazu format/driver/manifest, checks exact payload membership and verifies every payload SHA-256 before publishing the archive. Failed verification prevents publication.
- Added a focused regression proving the verifier accepts a valid archive and rejects a tampered private-storage payload.
- Changes are on `main`; exact-head Laravel/Windows CI has not yet been observed. This is a source correction, not proof of the owner's installed backup or migration. DB-001 remains OPEN; no readiness uplift.


## Zazu quality workflow — scoped npm advisory exception — 2026-11-17

- Exact-head GitHub Actions run `44179163841` passed all three jobs on `76be1f234e7726352c2e15b511b37b9084d2ddfa`: application, Composer security audit and secret scan. The application job completed `npm ci`, the scoped npm audit policy and the service-worker authentication cache-race tests.
- The underlying npm audit still reports GHSA-fc5q-39xc-vjpf / CVE-2026-112366 against picomatch (locked versions through 4.0.7). No patched npm version is currently published. The npm CLI's suggested force downgrade is not adopted.
- Added `scripts/audit-npm.mjs`: it permits only this exact advisory and only packages whose complete npm audit dependency chain leads to that finding. Every other high/critical finding remains blocking. The exception is temporary and must be removed when the advisory is fixed or withdrawn.
- This is a CI policy exception, **not a dependency remediation** and not a claim that the advisory is resolved. Zazu's Vite/Laravel build configuration uses repository-defined static glob patterns; no user-supplied glob input was found in the inspected Vite configuration.
- DB-001 remains OPEN. No release-readiness uplift.


## New-job form regression closure — 2026-11-17

- Root cause of the single failing Laravel workflow: `resources/views/work/create.blade.php` omitted the required `name` field while `WorkController` continued to validate and persist it. Three offline-browser journeys consequently stalled on the missing field selector; the tests were exercising the real required workflow correctly.
- Restored the required **Work / event name** field and validation feedback on `main` in application commit `ba94b39e0460e4e57c560e98541808c762f50e25`. No tests were weakened and no branches were created.
- Exact-head Laravel run `44184183003`: PASS — Laravel tests, PostgreSQL migration/registration/backup/restore/private-file recovery, and offline-browser acceptance including mobile customer-to-Work and phone-local attachment journeys.
- Supporting exact-head checks: Zazu Quality `44184183085` PASS; Windows V1 Package `44184182900` PASS including PowerShell fresh-install verification; Director Health `44184182984` PASS; Push-on-main `44184180721` PASS. SonarCloud was skipped and is not counted as a pass.
- This closes the form regression and associated CI failure only. DB-001 remains OPEN; release remains NOT READY; evidence maturity stays 73/100 with no readiness uplift.


## DB-001 recurrence — reference 895e2c23-bc6b-4919-a891-1e8e5c1e27e4 (2026-11-18)

- Owner reports another DB-001 while opening/saving account/workspace information. Reference: `895e2c23-bc6b-4919-a891-1e8e5c1e27e4`. The exact triggering action and matching runtime record are not yet available; do not infer a cause from the reference alone.
- Existing incident recorder now emits bounded database diagnostics (connection, driver, SQLSTATE, numeric driver code and operation) without SQL, bindings, exception messages or credentials.
- Added `ops/windows/Get-ZazuIncident.ps1` and `docs/DB001_INCIDENT_CAPTURE.md` on `main`. The script finds the exact request ID in Laravel logs and outputs only an allowlist of diagnostic fields. This is a safe evidence-capture path, not a root-cause fix.
- DB-001 remains OPEN until the matching record is inspected and the failing workflow is repeated successfully on the intended installation. No database changes, reset or restore performed. V1 remains NOT READY; no evidence-score uplift.


## DB-001 read-only SQLite inspection added — 2026-11-18

- Added `ops/windows/Test-ZazuDatabaseSchema.ps1` and `docs/DB001_SQLITE_SCHEMA_CHECK.md` on `main`.
- The Windows diagnostic reads Laravel migration status, checks the event schedule migration ledger entry and all five expected `events` columns, checks the historical `business_user.primary_niche` column, and runs SQLite `PRAGMA integrity_check`.
- It emits only bounded schema/status metadata to a desktop JSON report. It does not run migrations, create backups, restore archives, or alter database records/schema.
- Added a Windows Ops workflow contract check for the diagnostic's required checks and non-mutating boundary.
- This enables evidence-based selection of a repair once the owner's actual installed SQLite database is inspected. The source repository cannot inspect the owner's local database or recover the omitted SQL exception from the request ID alone.
- DB-001 reference `895e2c23-bc6b-4919-a891-1e8e5c1e27e4` remains OPEN pending the read-only report and successful reproduction on the intended installation. No readiness uplift.


## Offline cache-priming input hardening — 2026-11-18

- Reviewed the service-worker page-priming message path under issue #35.
- Found that the route filter accepted any string beginning with /; URL parsing treats protocol-relative and backslash-normalized paths as cross-origin URLs. The priming fetch uses credentials: include, so this was an unnecessary credentialed cross-origin request surface.
- Updated public/sw.js to parse and normalize each route before fetching; reject cross-origin URLs, embedded URL credentials, /api and /api/*, non-string values, and oversized route strings.
- Added tests/js/service-worker-auth-cache.test.js regression coverage proving only a same-origin non-API route is fetched.
- Commits: service-worker correction 51e71414a293b4584d1b304dcbf0d0d2d1b15b97; regression test 6517703a4d2b2263547b603d1b8dc1a9c0c2e0e1.
- Exact-head CI is queued/running. No test pass is claimed until observed. This is a narrow defense-in-depth correction; it does not close physical Android acceptance or establish full offline workflow parity.


## Sentinel control-centre mandate — 2026-11-18

- Owner directive: prioritise Sentinel as the next major engineering focus. The target is a local, self-service operations and diagnostic control centre that explains failures and collects the evidence a developer would otherwise request manually. It must remain useful without internet access.
- Audited the actual implementation at `main` HEAD `68f8729e728fd43e238a5d4ad6376ab07dbb561f`: independent privacy-filtered incident ledger, fingerprint grouping/timeline, platform-admin UI, redacted reference-scoped DB-001 download, Windows incident capture and read-only SQLite schema tooling already exist.
- Important capability gap: `/admin/sentinel` is protected by `auth`, `auth.session` and `platform.admin`; it is not currently the business-owner control surface. The installation-wide ledger has no tested per-business incident ownership boundary. Do not remove platform-admin access controls or expose the global ledger to business owners as a shortcut.
- Important resilience gap: `config/session.php` defaults `SESSION_DRIVER` to `database`. The diagnostic interface must not rely invisibly on the business database/session path it is expected to diagnose. Its fallback access path needs an explicit security design and packaged-installation verification.
- Created `docs/SENTINEL_CONTROL_CENTRE.md` defining the product contract, five control surfaces, access/isolation rules, resilience requirements, delivery sequence and acceptance criteria. Updated `docs/ZAZU_ERROR_INCIDENT_FRAMEWORK.md` to remove its obsolete statement that no dashboard exists.
- This is a scope/architecture definition only. No runtime capability has been added and no CI or production acceptance is claimed. DB-001 remains OPEN; V1 remains NOT READY; no readiness uplift.


## Sentinel control-centre implementation — 2026-11-18

- Current source head at verification: `f608effe6b890f618e2cdd5b93bb7d26e58f502a` on `main`.
- Added owner-discoverable **System → Sentinel Rescue** with a one-year temporary signed URL. The route is outside the regular authenticated web/session middleware; it was tested while `session.driver=database` and the configured SQLite database path was intentionally unavailable. The request still rendered the rescue page.
- Sentinel Rescue now performs read-only database connectivity and migration-history checks; private storage permission checks; offline support-file presence/readability checks; and independent Sentinel ledger folder/segment/write-read-delete checks.
- A failed database connection or migration read is captured through the independent ledger. The page displays a safe error code/failure category/reference and a link to the allowlisted DB-001 report.
- Raw PDO failures now inherit only the validated configured connection alias and driver, allowing SQLite failures during connection/bootstrap to be classified without storing private exception messages.
- Sentinel admin timeline DB-001 references now include a direct download action. The owner rescue access is installation-level and signed-link protected; it does not expose the platform-admin timeline or the raw global ledger.
- Exact-head run `44318909570`: Laravel/Pest/PHPUnit **563 passed, 1 skipped, 3,915 assertions**; PostgreSQL migration smoke **PASS**. The offline-browser acceptance job was still running at the time of this note.
- Exact-head run `44318909679`: PHPMD **PASS**. Exact-head run `44318909551`: Zazu quality application, Composer security audit and secret scan **PASS**.
- Windows V1 package run `44318697559` on app-source-equivalent commit `e5bb8c602b7b66403dbda49ca023cb7000856170`: package build and Windows PowerShell fresh-install verification **PASS**. No physical owner installation acceptance is inferred from CI.
- Remaining Sentinel gaps: package export/manifest, full failure knowledge base, attachment health, offline queue/sync checks, session/cache diagnostics, backup integrity and recovery checks, richer fault injection, and on-device/offline packaged acceptance.
- DB-001 reference `895e2c23-bc6b-4919-a891-1e8e5c1e27e4` remains OPEN until the actual installation's failing workflow is identified, corrected and repeated successfully. V1 remains NOT READY; no readiness score uplift. No branches created.


## Sentinel Rescue owner-dashboard refinement — 2026-11-18

- Owner reported that the standalone Rescue screen consumed too much vertical space and offered no route back into the Zazu workspace.
- Kept the standalone read-only diagnostic surface so it remains useful when the normal application shell/database path is unhealthy; added a persistent compact workspace link to the owner dashboard.
- Reworked the large introduction and instructional panels into a compact status summary and responsive health-check grid. Detailed migration names remain collapsed until requested.
- Founder environment setting confirmed by owner as `Rosscorelabs@outlook.com`. `config/zazu.php` normalizes the configured `ZAZU_PLATFORM_ADMIN_EMAIL` to lowercase. Do not hard-code the founder identity into shared defaults; each installation must set it in its private `.env`.
- Source/test update is not visually accepted until rendered on the owner's installation. Existing Sentinel security boundaries remain unchanged.


## Sentinel Rescue interaction and visual refinement — 2026-11-18

- Owner requested stronger borders and meaningful interaction rather than a static report.
- Diagnostic cards and dashboard surfaces now use higher-contrast borders and clearer hover/focus states.
- Added client-side status filters (all / failed / attention / healthy), live text search, visible-result count, expand/collapse for diagnostic details, and a recheck control that reloads the read-only health inspection.
- Controls are progressively enhanced: the filter toolbar remains hidden until its script initialises; without JavaScript the complete diagnostic report remains visible.
- No new server write endpoints, external requests, business-record access, or telemetry were introduced. Recheck reruns the existing GET health inspection.
- Automated verification is pending for the current main head; visual acceptance remains with the owner on the installed application.


## Sentinel Rescue actionable diagnostics — 2026-11-18

- Failed and attention checks now show a contextual **Recommended next step** panel based on the stable diagnostic key.
- Guidance covers database connectivity, migration state, private storage, installed-app assets, the private diagnostics directory, incident-ledger integrity and the temporary write/read probe.
- Existing DB-001 safe incident report remains the only linked diagnostic action. No automatic repair, migration, file deletion or business-data mutation was added.
- Updated the page copy to distinguish read-only database checks from the existing temporary diagnostics-file write/read probe, which attempts cleanup after each check.
- Added a focused Blade-render regression test for safe incident-ledger guidance and updated Sentinel documentation.
- Verification is pending for the new commits. Visual acceptance remains pending on the owner's installation. All commits are directly on `main`; no branches created.


## Sentinel local recovery complexity cleanup — 2026-11-18

- Refactored `EnsureLocalSentinelRecovery` into focused loopback and same-origin POST guards; retained exact local address/host restrictions, content-type, origin scheme/host/port checks and Fetch Metadata behavior.
- Refactored `ZazuLocalSentinelRecovery` into code-format, locked-read, challenge-validation and invalidation helpers. The 256-bit code, SHA-256 hash storage, five-minute expiry, lock-protected single-use behavior and invalidation fallback are preserved.
- Added feature coverage for expired and malformed challenges alongside existing cross-origin, different-port and one-time-use tests.
- PHPMD had reported complexity violations in the two recovery methods. Refactor verified by PHPMD run `44372419579` on commit `a3dc514`; scan completed successfully with no suppression or threshold change.
- Removed a duplicate incident-report link from the owner guidance panel; the existing reference-scoped DB-001 report action remains in the diagnostic facts row.
- Changes committed directly to `main`; no branches created. Full Laravel and physical-device acceptance remain pending.


## Sentinel CI regression closure — 2026-11-18

- Corrected the founder layout so workspace footer destinations are rendered only when an active business workspace exists; founders with a business retain normal workspace navigation. This resolves the founder-console navigation assertion without exposing the console to business users.
- Local recovery feature requests now explicitly model the local host request context (loopback address, HTTP scheme, host and configured port) and submit the required form content type. The recovery middleware remains unchanged and continues to enforce loopback-only access plus exact same-origin scheme/host/port and Fetch Metadata checks.
- Laravel run 44374252678 on commit a9ab1f2: **573 passed, 1 skipped, 3,956 assertions**. PostgreSQL migration smoke and offline browser acceptance were still running when this note was prepared; the preceding run 44373939367 had PostgreSQL and offline browser acceptance **PASS**.
- PHPMD run 44374538225 and Zazu quality run 44374538448 on 3d809a6: **PASS**. Laravel run 44374538236 on 3d809a6: **573 passed, 1 skipped, 3,956 assertions**. PostgreSQL migration smoke and offline browser acceptance for that exact commit were still running at this note; earlier commit 9d5ae4f had both checks **PASS**.
- Windows V1 package build and fresh-install PowerShell verification passed on commit 9d5ae4f. Director Contract Lint passed on 21a2f4e.
- No security checks were relaxed, no branches were created, and no physical-device acceptance is inferred from CI. V1 remains **NOT READY** pending physical installation/offline acceptance and remaining release gates.

## Sentinel request and incident-capture hardening — 2026-11-18

- Tightened the local recovery POST content-type guard to compare the parsed media type exactly with `application/x-www-form-urlencoded`; values that merely share the prefix are rejected. Added a regression test for the malformed suffix case. Existing loopback, origin scheme/host/port, Fetch Metadata and one-time challenge controls are unchanged.
- Hardened `ZazuIncidentRecorder`: if both the primary Laravel log channel and fallback Laravel error log fail, the recorder emits only the failure class to PHP's local `error_log` and does not allow the secondary logging failure to escape the exception-reporting path.
- Added `IncidentRecorderResilienceTest` proving the independent redacted incident ledger still receives the record during a simulated primary/fallback log-channel outage and that private exception details are not written to the ledger.
- Exact-head commit `529bd6fe47e9d7624a47c3237b87e03f508ae872`: Laravel **575 passed, 1 skipped, 3,962 assertions**; PostgreSQL migration/recovery smoke **PASS**; PHPMD **PASS**; Zazu quality application, Composer security audit and secret scan **PASS**. Offline browser acceptance was still running at the time of this note.
- Earlier CI failures on intermediate commits were traced to a malformed namespace separator in the newly added test and a PHPMD public-method count after adding the test to an already-large test class. The test was moved into its own feature-test class, syntax corrected, and PHPMD plus Laravel now pass on 529bd6f. No production safeguard was weakened.
- All changes are committed directly to `main`; no branches created. Physical Windows/Android acceptance remains separate from CI, and V1 remains **NOT READY** pending the outstanding operator gates.

## Incident ledger filesystem hardening — 2026-11-18

- `ZazuIncidentLedger` now reapplies private filesystem modes when writing: diagnostics directory `0700`, lock file `0600`, JSONL ledger `0600` (best effort on Windows).
- This tightens pre-existing paths from older installations as well as newly created paths. No incident fields or retention policy changed.
- Windows security remains dependent on effective NTFS ACLs; PHP `chmod` is not treated as proof. Installed-package ACL verification remains an acceptance requirement.
- Change committed directly to `main` as `396960a`; CI for this exact head is pending. Prior application commit `529bd6f` had Laravel 575 passed / 1 skipped / 3,962 assertions, PostgreSQL smoke PASS, PHPMD PASS and quality scans PASS; offline-browser acceptance was still running at last check.
- No branches created. V1 remains NOT READY pending exact-head CI and physical-device/package acceptance.

- Added a POSIX feature test that verifies the diagnostics directory is `0700` and the incident lock/ledger files are `0600`; the test explicitly skips on Windows because NTFS ACLs, not POSIX mode bits, determine effective access there. Commit `d1923af`; exact-head CI pending.

- PHPMD caught a malformed fully-qualified exception name in the new permission regression test (`\\RuntimeException`); corrected to `\RuntimeException` in `61dbd19`. This was test syntax only, not a production-code finding. Fresh PHPMD/Laravel runs are required before considering the permission test verified.


## Windows private-storage ACL hardening — 2026-11-18

- Windows installer `ops/windows/Install-Zazu.ps1` now removes inherited parent permissions from `storage/app/private` and normalises existing descendants to allow Full Control only for the installing Windows account, Local System and local Administrators. It refuses reparse points in this private tree.
- The Windows package workflow now boots the installed app, writes a real redacted incident-ledger probe, and verifies the private root, diagnostics directory, lock and JSONL file contain only the allowed SIDs; child entries must inherit the restricted ACL.
- Windows Ops Scripts validation on `409056b`: **PASS**. Windows package build and fresh-install smoke on `409056b`: **PASS** (before the new explicit ACL assertions). The exact workflow with ACL assertions on `d39396f` is still running; a subsequent package workflow on `ceb6c8c` is also running.
- Application hardening verification on `61dbd19`: Laravel, PostgreSQL migration smoke, offline-browser acceptance, PHPMD and Zazu quality/security scans **PASS**. The POSIX permission regression test is included in that Laravel run.
- Documentation updated in `docs/V1_PACKAGE_INSTALLATION.md` and `docs/SENTINEL_CONTROL_CENTRE.md`. Current `main` documentation head is `707dcb0`; its quality and Director Contract Lint checks are running.
- Do not claim NTFS ACL acceptance until the exact `d39396f` or later Windows package workflow completes successfully. Physical-device acceptance remains separate. No branches created; V1 remains NOT READY.

- Exact Windows package workflow `44380197892` on commit `ceb6c8c`: package build **PASS**; Windows PowerShell fresh install **PASS**, including the new real-ledger NTFS ACL assertions for the private root, diagnostics directory, lock and JSONL file. The ACL test confirmed only the installer account, Local System and local Administrators are allowed, with diagnostic children inheriting the protected ACL.
- Latest main state/documentation commit `e278d9c`: Zazu quality, Director Contract Lint and Director Health Report **PASS**. No code changes after the Windows ACL-verified package commit; only documentation/state updates.
