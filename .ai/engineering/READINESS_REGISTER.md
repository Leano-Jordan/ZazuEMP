# Zazu EMP — V1 Readiness Register

Last verified application-code baseline: ae3d49cae7973c2db708d5ff88c3a3eb51c35b54
Release disposition: NOT READY
Evidence maturity: 73/100 unchanged; no uplift claimed.

## Director source audit refresh — 2026-11-20

Repository HEAD `0bd468f222fc4dab87b725a99d913e2fb514b2f8` has passing Zazu Quality, Director Contract Lint and Push-on-main workflows. The exact-head quality workflow covers locked npm audit, service-worker auth/cache-race checks, Composer security audit and secret scanning; it is not the Laravel/offline acceptance suite. Latest full app test evidence remains on application-code baseline `526bdf3` (Laravel 607/1 skipped/4,158 assertions; PostgreSQL recovery; offline browser 51 desktop + 6 mobile + 1 phone-local attachment journey).

The source-level path map for all 17 offline entity types is now recorded in `docs/V1_RELEASE_CHECKLIST.md`. Static mapping is complete; runtime replay/reconciliation acceptance remains OPEN.

The stale-update finding is confirmed in the implementation: update-capable domain handlers resolve the latest record through the stable identity registry and update it without a server-enforced base version. The existing conflict route records validation/domain rejection and mutation identity mismatch, and exposes retry/discard; it does not detect stale snapshots or provide a safe rebase. The sync reliability gate remains OPEN and release remains NO-GO. Do not solve this with timestamps-only or last-write-wins.


## Owner-approved V1 scheduling addition — 2026-11-17
Event scheduling and connected rental logistics are explicitly authorised for V1: multi-day event dates, start/end times, setup/collection dates, schedule clash warnings, and rental allocation defaults using the event logistics window. Staff scheduling, quantity-based rental stock and wider workforce management remain deferred. Implementation is on `main` at code baseline `71c8fc5c7aeba392b95e26aee694945b286f2c8e`; Laravel 532/1 skipped/3,714 assertions and PHPMD pass. Windows package + PowerShell install/recovery pass on 2ef2a0b; PostgreSQL migration/recovery and offline-browser acceptance PASS at exact application-code baseline 71c8fc5 in run 44152431508.

## Owner-directed offline sync reliability gate — OPEN (2026-11-20)

The Founder has explicitly added end-to-end offline sync quality across the supported V1 workflow matrix as a release target. This is distinct from successful phone pairing and from the separate PC/server-independent phone launch gate. Pairing remains CLOSED by Founder confirmation.

**Required evidence:** trace every declared offline operation from UI through business-scoped IndexedDB, durable queue, mutation handler and stable identity registry, then through server acknowledgement/pull/bootstrap and final client reconciliation. Verify restart durability, transient and partial failure retries, duplicate/reordered delivery idempotency, parent/child references, pagination/cursors, business/device isolation, conflict preservation and resolution, attachment byte/metadata integrity, and financial/inventory/purchase/scheduling lifecycle parity. Verify representative journeys on the actual PC and phone after automated coverage passes.

**Additional integrity gap confirmed by source inspection (2026-11-20):** ordinary offline update mutations do not currently carry a base record version/precondition. Several handlers resolve the latest server record through `existing()` and then call `update()` without detecting that another trusted device changed it after the local snapshot. The conflict pipeline handles validation/domain rejections and changed-payload mutation IDs, but this is not equivalent to stale-record conflict detection. Treat concurrent same-record edits as OPEN until version-aware conflict behaviour is designed and tested, including safe rebasing of multiple sequential local edits from the same device. Do not patch this with an unreviewed last-write-wins rule.

**Latest exact-head evidence — `526bdf38dab6ff540c143b0e57dfcee468006dc3` (2026-11-20):** Laravel PASS — 607 passed / 1 skipped / 4,158 assertions; PostgreSQL migration/registration/backup/restore/private-file recovery PASS; offline browser PASS — 51 desktop Chromium tests, 6 mobile-viewport tests and 1 phone-local Work/attachment journey. The latest test-only commit adds a cold-launch check for the authenticated cached shell, durable local customer/Work records and pending queue after opening a fresh page. Zazu Quality and Push-on-main PASS; SonarCloud skipped. PHPMD, Psalm, Windows V1 package and populated upgrade/rollback PASS on application-equivalent parent `30e70e9`; `ae3d49c` and `526bdf3` add tests only, not application source.

**Verified sync changes:** failed mutations/open conflicts now block destructive bootstrap replacement; canonical IDs reconcile for `event_cost`, nested `payment`, `inventory_movement` and `asset_allocation`; disconnected stock receipts update local inventory balance/history; a final offline invoice payment marks the local invoice paid; retryable attachment failures are included in the existing backoff scheduler while preserving the idempotency key. Targeted browser regressions cover these paths.

**Acceptance remains OPEN:** these fixes do not establish sync parity across every supported operation or prove physical-device operation. Concurrent same-record edits still lack a base-version/precondition, so stale updates can overwrite newer server state. Physical Android standalone offline launch/restart/reconnect, DB-001, installed-target recovery, live commercial reconciliation, POPIA operations and ZAZU name clearance remain open. Pairing remains CLOSED by Founder confirmation. Release remains NOT READY; evidence maturity remains 73/100 with no uplift.

**Scope boundary:** no parallel offline engine, no cache-only workaround, no weakening of business isolation/idempotency/conflict tests, and no unsupported app-wide offline claim. Work within the existing offline client, mutation protocol, domain handlers, identity registry, attachment subsystem and documented offline workflow matrix.

## Open runtime blocker — repeated DB-001 (2026-11-17)

**Status:** OPEN — cause not yet proven  
**Owner-reported references:** `2bc77a48-5c6c-4095-afe2-fa079f60e4ba`, `aa9080f4-9580-41c9-a643-439b31928ffa`

The owner reported repeated database errors on the installed Windows application. The latest reference was supplied with the Account access error screen; its exact triggering action is not recorded.

**Source finding:** Work creation/editing now writes five scheduling columns introduced by migration `2026_11_17_000001_add_event_schedule_fields.php`. The previous shared Windows start script did not check or apply pending migrations. This makes local schema drift the leading hypothesis, but the reference number alone does not reveal the SQL exception.

**Correction on main:** Windows startup now checks migration status. If migrations are pending, it creates and verifies a full installation backup containing the database and private files before applying them, then checks that all migrations completed. Startup stops if backup, migration or final status verification fails.

**Closure evidence still required:** Exact-head Windows startup/upgrade CI; successful execution on the owner's intended installation; confirmation that all migrations are current; and successful completion of the job save that previously failed. No root cause or runtime fix is claimed yet.

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
