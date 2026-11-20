## Director disposition refresh — 2026-11-20

- **DB-001: DORMANT / MONITORING, not an active release blocker.** The Founder reports no recent recurrence. The historical root cause remains unproven; do not claim that the error was fixed. Resume investigation only on recurrence or new evidence. Keep historical references and diagnostics. The **installation recovery gate remains OPEN** as a separate V1 requirement.
- Revision-aware stale-write implementation is present on `main`; the old missing-base-revision implementation finding is superseded. Remaining sync acceptance is the full workflow matrix, command-side parent revision evidence, retries/replay/reconciliation and physical device checks.
- Bounded online-writer source review has inspected root/nested model hooks and customer/contact, Work, service/capability, supplier, quote, finance, purchase, inventory and asset write paths. No additional confirmed aggregate revision bypass was found in the inspected paths. This is not a claim that runtime acceptance is complete.
- Added `SyncRevisionAggregateTrackingTest::test_offline_commands_advance_every_related_aggregate_revision` on `main` at `98f5921`; exact-head CI is pending.

# Zazu EMP — V1 Readiness Register

Last verified application-code baseline: 526bdf38dab6ff540c143b0e57dfcee468006dc3
Release disposition: NOT READY
Evidence maturity: 73/100 unchanged; no uplift claimed.

## Director source audit refresh — 2026-11-20

During the 2026-11-20 source audit, documentation head `91f41ec` passed Rosscore Director Health, Zazu Quality, Director Contract Lint and Push-on-main workflows. The exact-head quality workflow covers locked npm audit, service-worker auth/cache-race checks, Composer security audit and secret scanning; it is not the Laravel/offline acceptance suite. Latest full app test evidence remains on application-code baseline `526bdf3` (Laravel 607/1 skipped/4,158 assertions; PostgreSQL recovery; offline browser 51 desktop + 6 mobile + 1 phone-local attachment journey).

The source-level path map for all 17 offline entity types is recorded in `docs/V1_RELEASE_CHECKLIST.md`. Static mapping is complete; runtime replay/reconciliation acceptance remains OPEN. The online/offline writer trace in `.ai/engineering/STATE.md` is PARTIAL: Eloquent aggregate and nested-model event hooks are present, but every indirect writer, bulk update and transaction boundary still needs exhaustive confirmation. One concrete bypass in customer-contact primary promotion was corrected by replacing a relationship mass update with locked per-model updates; a feature regression now asserts that the parent customer revision advances even when the selected primary itself is unchanged. Exact-head acceptance is pending.

**Revision-aware stale-write protection is now implemented on `main`.** Offline update/upsert mutations carry an expected aggregate revision or a same-device predecessor mutation ID. The server checks the revision under the existing business/device/mutation lock order, rejects stale writes as `stale_record_version` with the current remote snapshot, and supports keep-local rebasing through a new immutable mutation ID or use-server restoration. Parent revisions and related identities are advanced for command effects. This replaces the previous missing-base-version implementation finding; it does **not** close the sync gate until all online writers are audited and end-to-end acceptance passes.

**Latest evidence at this refresh:** Laravel on `5b56a7c` passed 621 tests / 1 skipped / 4,271 assertions; PostgreSQL migration/registration/backup/restore smoke passed. Offline-browser acceptance on that commit had 56 passes and one failure: after the last conflict was discarded, Settings hid the success message with the now-empty conflict list. The UI visibility correction is committed as `f50fcb1`; exact-head Laravel/browser and Windows package runs are still in progress. Zazu Quality, Director Contract Lint and Director Health passed on `f50fcb1`; SonarCloud was skipped. Do not infer exact-head browser acceptance from these results.


## Owner-approved V1 scheduling addition — 2026-11-17
Event scheduling and connected rental logistics are explicitly authorised for V1: multi-day event dates, start/end times, setup/collection dates, schedule clash warnings, and rental allocation defaults using the event logistics window. Staff scheduling, quantity-based rental stock and wider workforce management remain deferred. Implementation is on `main` at code baseline `71c8fc5c7aeba392b95e26aee694945b286f2c8e`; Laravel 532/1 skipped/3,714 assertions and PHPMD pass. Windows package + PowerShell install/recovery pass on 2ef2a0b; PostgreSQL migration/recovery and offline-browser acceptance PASS at exact application-code baseline 71c8fc5 in run 44152431508.

## Owner-directed offline sync reliability gate — OPEN (2026-11-20)

The Founder has explicitly added end-to-end offline sync quality across the supported V1 workflow matrix as a release target. This is distinct from successful phone pairing and from the separate PC/server-independent phone launch gate. Pairing remains CLOSED by Founder confirmation.

**Required evidence:** trace every declared offline operation from UI through business-scoped IndexedDB, durable queue, mutation handler and stable identity registry, then through server acknowledgement/pull/bootstrap and final client reconciliation. Verify restart durability, transient and partial failure retries, duplicate/reordered delivery idempotency, parent/child references, pagination/cursors, business/device isolation, conflict preservation and resolution, attachment byte/metadata integrity, and financial/inventory/purchase/scheduling lifecycle parity. Verify representative journeys on the actual PC and phone after automated coverage passes.

**Revision-aware integrity implementation (2026-11-20):** offline update/upsert mutations now carry a server-enforced expected aggregate revision or a predecessor mutation ID for same-device causal chains. Stale writes are rejected with a `stale_record_version` conflict and authoritative remote snapshot. Keep-local creates a new immutable mutation ID against the latest revision; use-server restores the authoritative record. Parent/related revisions are advanced for consequential command effects. `SyncRevisionConcurrencyTest` covers stale cross-device writes, sequential local chains, intervening online writes, rebasing and related aggregate effects. The remaining gap is exhaustive coverage of every online/indirect/bulk writer and end-to-end workflow acceptance; do not downgrade this to timestamps-only or last-write-wins.

**Latest full application evidence before the current UI correction:** Laravel PASS — 621 passed / 1 skipped / 4,271 assertions; PostgreSQL migration/registration/backup/restore/private-file recovery PASS on `5b56a7c`. Offline-browser acceptance on that commit: 56 passed, 1 failed at the final Settings conflict-success visibility assertion. The correction is on `f50fcb1`; its exact-head acceptance is pending. Do not carry forward the older 526bdf3 test counts as the latest evidence.

**Verified sync changes:** failed mutations/open conflicts now block destructive bootstrap replacement; canonical IDs reconcile for `event_cost`, nested `payment`, `inventory_movement` and `asset_allocation`; disconnected stock receipts update local inventory balance/history; a final offline invoice payment marks the local invoice paid; retryable attachment failures are included in the existing backoff scheduler while preserving the idempotency key. Targeted browser regressions cover these paths.

**Acceptance remains OPEN:** revision-aware stale-write protection is implemented and has server-side regression coverage, but broad sync parity, exhaustive writer/transaction audit and exact-head offline-browser acceptance remain open. Physical Android standalone offline launch/restart/reconnect, installed-target recovery, live commercial reconciliation, POPIA operations and ZAZU name clearance also remain open. Pairing remains CLOSED by Founder confirmation. Release remains NOT READY; evidence maturity remains 73/100 with no uplift.

**Scope boundary:** no parallel offline engine, no cache-only workaround, no weakening of business isolation/idempotency/conflict tests, and no unsupported app-wide offline claim. Work within the existing offline client, mutation protocol, domain handlers, identity registry, attachment subsystem and documented offline workflow matrix.

## Dormant incident record — DB-001 (first reported 2026-11-17; current status updated 2026-11-20)

**Status:** DORMANT / MONITORING — no recent recurrence reported by Founder; cause remains unproven.  
**Owner-reported references:** `2bc77a48-5c6c-4095-afe2-fa079f60e4ba`, `aa9080f4-9580-41c9-a643-439b31928ffa`

Historical record: the owner previously reported database errors on the installed Windows application. The exact triggering action for the earlier references was not recorded. The Founder now reports that DB-001 has not appeared recently, so active investigation is paused.

**Source finding:** Work creation/editing now writes five scheduling columns introduced by migration `2026_11_17_000001_add_event_schedule_fields.php`. The previous shared Windows start script did not check or apply pending migrations. This makes local schema drift the leading hypothesis, but the reference number alone does not reveal the SQL exception.

**Correction on main:** Windows startup now checks migration status. If migrations are pending, it creates and verifies a full installation backup containing the database and private files before applying them, then checks that all migrations completed. Startup stops if backup, migration or final status verification fails.

**Current handling:** no active reproduction chase. Reopen only if DB-001 recurs or new evidence materially changes the assessment. No root cause or runtime fix is claimed. The separate installation-recovery gate still requires a controlled drill on the intended installed target.

## Existing release gates
| Gate | Disposition | Closure evidence |
|---|---|---|
| Physical offline device | OPEN | Actual Android installed-app cold launch, disconnected restart, local continuity and reconnect |
| Installation recovery | OPEN — CI supporting proof added | Windows V1 Package run 82 on fd82f6a passes Windows PowerShell first install and Windows-native SQLite + private-media backup/mutate/restore; still requires the controlled drill on the intended installed target, with representative data, media, authorization, audit and migrations |
| Commercial reconciliation | OPEN | Populated real-business quote, invoice, payment, cost and profit reconciliation |
| POPIA operations | OPEN | Owner-approved privacy/retention, incident and operational controls |
| Event scheduling & rental logistics | CLOSED — engineering/CI | Online/offline fields, setup/collection calendar coverage, clash warnings and rental allocation defaults implemented; Laravel, PostgreSQL migration/recovery and desktop/mobile offline-browser acceptance passed at 71c8fc5 in run 44152431508; physical Android remains a separate release gate |
| Windows package | CLOSED for defined fresh-install candidate | Windows V1 Package run 82 at fd82f6a: production candidate build, PowerShell first-install smoke and Windows-native SQLite/private-media recovery pass; not an updater or hosted deployment |

## Claude independent audit reconciliation — 2026-11-17
1. Owner web login auto-trusts browsers for 180 days vs 120-minute session; offline cleanup has no revoke retry — DESKTOP AUTO-TRUST CLOSED: fresh browsers require explicit owner approval in Settings; destructive local cleanup now retries revocation on the next authenticated owner session using only the installation UUID. Installed-phone behaviour remains an accepted owner limitation; ordinary web logout remains non-destructive by design.
2. Six-digit pairing code, per-IP throttle only, no failed-attempt lockout — DEFER; low-confidence hardening, no demonstrated V1 exploit.
3. Loopback-only proxy trust and no HTTPS forcing — ACCEPTED LIMITATION for local Windows/LAN V1; hosted reverse-proxy behavior is outside active deployment scope.
4. CurrentBusiness test-only branch, min-8 password policy, generated manifest drift, offline bootstrap permission filtering — MIXED: generated manifest tracking corrected; bootstrap endpoint permission gate covered; password-policy expansion and per-permission filtering deferred; test-only branch accepted.
5. Rental asset date allocation — REPRODUCED and FIXED. Online/offline tests at 00af verify non-overlapping periods can be booked, overlapping periods are rejected, open-ended ranges conflict correctly, and individual returns preserve allocated status until the final active booking is returned. Manual physical return remains operator-confirmed; no automatic release on event state/date alone. Quantity-based rental stock remains outside the serialized-asset model and is an OWNER-DECISION / SCOPE item.
6. Multi-day events, start/end times, setup/collection dates and clash detection — OWNER-AUTHORIZED for focused V1 implementation on 2026-11-17. Staff scheduling and quantity-based rental stock remain deferred.
7. Invoice numbering — OWNER-AUTHORIZED for engineering implementation on 2026-11-18 following the owner's V1 workstream instruction. Implement a business/month-scoped sequence in the existing `INV-YYYYMM-` format across online issuance and offline sync; tax-adviser/legal review remains open before claiming tax-invoice compliance.
8. Team invites/role assignment, email/PDF delivery, billing, app-wide licence enforcement, in-app legal terms/privacy, monitoring and scheduled jobs — OWNER-DECISION / SCOPE; no silent V1 expansion.

## Verified engineering corrections — e5dc263
- Password reset revokes active and pending trusted devices across every business where the resetting user is an owner, clears bearer/pairing hashes and expiry, and writes audit events. Businesses where the user is staff only remain untouched.
- Offline financial mutation validation rejects precision beyond two decimal places before posting; quote, payment, purchase-order, receipt, cost, expense and inventory regressions are covered.
- CI verifies 523 Laravel tests passed / 1 skipped / 3,662 assertions; 45 desktop Chromium, 6 mobile-viewport and 1 phone-local attachment journey passed; PostgreSQL recovery, Windows fresh-install package, populated upgrade/rollback, quality, Psalm, PHPMD, Director Health and Push-on-main passed. SonarCloud skipped.

## Verified application-code baseline — a5ead69

- Offline push validates payment type/method and expense status against the connected-workflow allowlists; quote currency must be configured.
- Offline payment, expense and invoice dates reject malformed and impossible calendar dates.
- All-zero offline purchase receipts are rejected before a receipt record or idempotency key is consumed.
- Regression coverage includes these cases and float-decoded purchase-order quantity/price precision.
- Laravel: PASS — 526 passed / 1 skipped / 3,681 assertions.
- Offline browser: PASS — 45 desktop Chromium, 6 mobile-viewport and 1 phone-local attachment journey.
- PostgreSQL migration/registration/backup/private-file recovery: PASS.

## Latest repository verification — fd82f6a

- The only change after a5ead69 is the Windows package workflow; application code is unchanged.
- Windows V1 Package run 82: PASS — production candidate, Windows PowerShell fresh install and Windows-native backup/restore of a disposable SQLite record plus private media.
- Director Health, Director Contract Lint and Push-on-main: PASS.
- Composer security audit and secret scan: PASS.
- LAN asset delivery regression: PASS at application-code baseline a5ead69. Remote clients are served compiled same-origin assets even with a stale loopback Vite hot marker; Windows LAN startup removes stale hot state and validates the built manifest. This post-dates the supplied 13 Nov screenshot. Physical Android launch from the HTTPS origin remains part of the open device gate.
- Zazu Quality: FAIL at `npm audit --audit-level=high` due GHSA-fc5q-39xc-vjpf / CVE-2026-112366 in the locked picomatch build-tool dependency chain. GitHub's advisory currently lists affected versions through 4.0.7 and no patched version. The npm dependencies are development/build tools and are excluded from the Windows runtime candidate, but the quality workflow remains red. No force downgrade or advisory suppression applied.
- Windows CI recovery is supporting evidence, not a replacement for the owner-run recovery drill on the intended installation.
- No score uplift; release remains NOT READY.
## Documentation and commercial decisions
- Offline work is ACTIVE/RESUMED; stale pause notes are historical and archived.
- Tier conflict remains OWNER-DECISION: product-specification/operating-profile describe three tiers (Basic Free, Intermediate, Advanced), while commercial requirements specify four paid prices (R299, R499, R799, R1,299). No tier or price decision inferred.
- Root ROSCORE_PROJECT_MANIFEST.md is canonical; docs/ROSCOR_PROJECT_MANIFEST.md is a legacy pointer.
- Prior long-form state/register/memory, checklist and changelog are preserved in docs/archive/engineering/2026-11-17-state-register-memory-history.md.

## Earlier exact-head CI — e5dc263 (historical baseline)
- Laravel: PASS — 523 passed, 1 skipped, 3,662 assertions.
- Offline browser: PASS — 45 desktop, 6 mobile, 1 phone-local attachment.
- PostgreSQL migration/registration/backup/private-file recovery: PASS.
- Windows V1 package + PowerShell fresh install: PASS.
- Populated upgrade/rollback, Zazu quality, Psalm, PHPMD, Director Health and Push-on-main: PASS.
- SonarCloud: SKIPPED.
- Evidence maturity remains 73/100. No score uplift.

## Next
Close the physical Android device, intended-installation recovery, live financial reconciliation and POPIA operational gates. Resolve the unpatched frontend build-tool advisory when a verified upstream fix becomes available. Keep all owner-decision scope items unchanged until approved.


## Exact-head scheduling acceptance evidence — 2026-11-17
- GitHub Actions run `44152431508`: all three jobs completed successfully.
- Offline browser job `134284080089`: PASS — browser suite, mobile Work/customer acceptance and phone-local attachment journey.
- PostgreSQL job `134284080622`: PASS — migrations, account/workspace registration and backup/restore/private-file recovery.
- Laravel job `134284080768`: PASS — service-worker/session contracts, LAN asset-origin checks, Blade/manifest/config checks and PHPUnit/Pest.
- Application code is unchanged after tested source `71c8fc5`; latest `main` HEAD `1bb4936` is a documentation-only release-checklist commit.
- Scheduling engineering/CI subgate closed. No overall readiness uplift: physical Android, installed-target recovery, live commercial reconciliation, POPIA and dependency advisory remain open.


## DB-001 diagnostic contract correction — 2026-11-17

Source inspection confirms the authoritative incident logger is intentionally privacy-minimised. It records request ID, error classification, exception class, route/action, method/path, timestamp and source file/line; it does not record exception messages, SQL, bindings or stack traces. The request middleware also adds the request ID to log context and the response header.

The incident-framework documentation was corrected on `main` (documentation-only commit `f88af665ae09b6f87f3c8d73a52c3cc5e4854dbd`) to match the implementation. This is a documentation correction, not an incident fix. The actual owner-installation log entry matching `aa9080f4-9580-41c9-a643-439b31928ffa` is still required to inspect its exception class, source file/line and route. DB-001 remains OPEN; no readiness uplift.


## Backup-first startup verification hardening — 2026-11-17

The backup command now reopens its completed temporary ZIP, performs ZIP consistency checking, validates the Zazu manifest and database driver, checks that archive payload membership exactly matches the checksum manifest, and verifies every payload SHA-256 before publishing the backup. The Windows startup script's backup command therefore returns success only after this self-verification. A focused regression checks valid and tampered payload behavior.

**Evidence status:** committed on `main`; exact-head Laravel and Windows workflow results are not yet available. Owner-installation migration execution and the affected account workflow remain required. No DB-001 closure or readiness uplift.


## Zazu quality workflow — exact-head result — 2026-11-17

- GitHub Actions run `44179163841` passed all jobs on `76be1f234e7726352c2e15b511b37b9084d2ddfa`: application, Composer security audit and secret scan.
- Application: `npm ci`, scoped npm audit policy and service-worker authentication cache-race tests all passed.
- The exact GHSA-fc5q-39xc-vjpf / CVE-2026-112366 exception remains temporary because npm has no patched picomatch version. The audit script fails closed for every other high/critical advisory and for any dependency chain not exclusively attributable to this finding.
- This is a CI gate resolution, not vulnerability remediation. Keep the advisory tracked until upstream publishes a fix or withdraws it. No readiness score uplift; DB-001 and owner acceptance gates remain open.


## New-job form regression — CLOSED (2026-11-17)

The create screen had omitted the required Work / event name field although `WorkController` still validates and persists `name`. This caused three offline-browser journeys to time out at the absent form control. The production form was corrected on `main` at `ba94b39e0460e4e57c560e98541808c762f50e25`; the tests remain unchanged.

**Exact-head evidence:** Laravel run `44184183003` passed all three jobs: Laravel tests, PostgreSQL migration/registration/backup/restore/private-file recovery, and offline-browser acceptance (including the mobile customer-to-Work and phone-local attachment journeys). Zazu Quality `44184183085`, Windows V1 Package `44184182900` (including PowerShell fresh install), Director Health `44184182984` and Push-on-main `44184180721` also passed. SonarCloud was skipped, not passed.

This is a closed form/CI regression, not a readiness uplift. DB-001 and all four operator acceptance gates remain open; release remains NOT READY and evidence maturity remains 73/100.


## DB-001 recurrence — OPEN (2026-11-18)

New owner-reported reference: `895e2c23-bc6b-4919-a891-1e8e5c1e27e4`. The user reports another failure while opening/saving account/workspace information. The precise action and matching log record are not yet known, so no root cause is claimed.

A safe Windows capture utility is now on `main`: `ops/windows/Get-ZazuIncident.ps1`. Run it from the Zazu project folder with the reference ID; it returns only the correlated request ID, route/action, exception class, source file/line and bounded database diagnostics. It deliberately excludes SQL, bindings, messages and secrets. See `docs/DB001_INCIDENT_CAPTURE.md`.

**Closure still requires:** matching runtime record, a targeted correction, and successful repeat of the affected workflow on the intended installation. Do not alter the database while the cause is unknown. Release remains NOT READY; no score uplift.


## DB-001 read-only local SQLite inspection — OPEN (2026-11-18)

A new Windows diagnostic is committed on `main`: `ops/windows/Test-ZazuDatabaseSchema.ps1`, documented in `docs/DB001_SQLITE_SCHEMA_CHECK.md`. It inspects migration status, the event scheduling migration ledger/columns, the historical `business_user.primary_niche` column and SQLite integrity without changing the database. A Windows Ops workflow contract check now guards its required checks and non-mutating boundary.

**Next evidence required:** run the script against the owner's installed Zazu project and inspect the generated `Zazu-Database-Schema-Check.json`. Then select a targeted correction from the observed schema/migration state and reproduce the failing workflow. This source-side diagnostic does not establish the cause of request `895e2c23-bc6b-4919-a891-1e8e5c1e27e4`; do not claim the incident is resolved.
