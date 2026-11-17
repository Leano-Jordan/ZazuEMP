# Zazu EMP — V1 Release Checklist

Last verified application-code baseline: 71c8fc5c7aeba392b95e26aee694945b286f2c8e
Disposition: NOT READY · Evidence maturity: 73/100 unchanged.

## Open database incident — owner installation

- [ ] Resolve the repeated owner-reported DB-001 errors. References: `2bc77a48-5c6c-4095-afe2-fa079f60e4ba` and `aa9080f4-9580-41c9-a643-439b31928ffa`.
- [ ] Confirm the cause using the installed application's diagnostic record; local SQLite migration drift is currently only a hypothesis.
- [ ] Verify backup-first migration on the intended Windows installation, then repeat the affected job workflow successfully.
- Windows startup source now checks migration status, creates and verifies a full database/private-files backup before pending migrations, and checks the final migration state. Exact-head CI and owner-installation execution remain pending.

## Current code follow-up — 2026-11-17

- Job date/time relationships now use one validator for online creation/editing and offline sync.
- The job form uses shared labels, plain-English guidance and browser date/time limits.
- DB-001 and account error copy is simpler; the product-wide language rule is in `docs/ZAZU_PLAIN_LANGUAGE_STANDARD.md`.
- Source and regression tests are committed on `main`; exact-head CI is pending. No readiness uplift is claimed.

## Event scheduling and rental logistics — owner-authorised V1 addition (2026-11-17)
Implementation baseline: `71c8fc5c7aeba392b95e26aee694945b286f2c8e` on `main`.
- [x] Multi-day event date range, start/end time, setup and collection dates persist and validate online.
- [x] Schedule clash warnings identify overlapping active work without blocking an operator save.
- [x] Offline-created schedule fields survive sync and are included in the device workspace bootstrap.
- [x] Calendar displays multi-day events and includes setup/collection logistics days.
- [x] Asset booking defaults use the event setup/collection window; existing overlap and independent-return protections remain intact.
- [x] Exact-head PostgreSQL migration/recovery and desktop/mobile offline-browser regression pass (run 44152431508).

## Release acceptance gates
- [ ] Physical Android acceptance: secure-origin install, disconnected cold launch/restart, local records/attachments and reconnect sync on actual device.
- [ ] Installed-target recovery: controlled database + private-media backup/restore on intended Windows installation.
- [ ] Live commercial reconciliation: populated quote → invoice → deposit/final payment → purchasing/costs → completion → profit/margin.
- [ ] POPIA operational sign-off: privacy notice, retention/deletion, access controls and incident procedure.

## Event scheduling implementation evidence — 71c8fc5

- Laravel: PASS — 532 passed / 1 skipped / 3,714 assertions.
- PHPMD: PASS.
- Windows candidate build, fresh PowerShell install and SQLite/private-media recovery: PASS on 2ef2a0b, the same application-code implementation before the final regression-test-only commit.
- PostgreSQL migration/recovery and desktop/mobile offline-browser acceptance: PASS on exact source HEAD 71c8fc5 (run 44152431508).
- Zazu Quality: RED on GHSA-fc5q-39xc-vjpf / CVE-2026-112366. No patched version is published; no advisory suppression or breaking Vite downgrade applied.

## Latest CI verification

- Application-code baseline a5ead69: Laravel 526 passed / 1 skipped / 3,681 assertions; offline browser acceptance 45 desktop + 6 mobile-viewport + 1 phone-local attachment; PostgreSQL migration/registration/backup/private-file recovery passed.
- Repository head fd82f6a changes only the Windows package workflow after that application-code baseline.
- Windows V1 Package run 82: PASS — candidate package built; Windows PowerShell first install passed; Windows-native backup/restore recovered both a disposable SQLite record and private-media bytes.
- Director Health, Director Contract Lint and Push-on-main passed.
- LAN asset delivery regression passed at a5ead69: a remote client with a stale loopback Vite hot marker receives compiled same-origin CSS/JS. The supplied 13 Nov HTTP screenshot predates this correction; physical Android HTTPS launch remains required.
- Zazu Quality passes with a temporary, exact-advisory exception for GHSA-fc5q-39xc-vjpf / CVE-2026-112366 in the picomatch build-tool dependency chain. The underlying advisory remains open/unpatched; the audit script allows only this finding and its exclusive dependency chain. Composer security audit and secret scan passed. No breaking Vite downgrade was applied.
- The Windows CI recovery test is supporting evidence only; it does not close the controlled recovery drill on the intended installed target.
## Closed acceptance
- [x] Windows fresh-install candidate package, PowerShell first-install smoke and Windows-native SQLite/private-media backup/restore — Windows V1 Package run 82 passed at fd82f6a; not an updater or hosted deployment.
- [x] Seeded commercial workflow automation — verified; reopen only on reproducible defect.
- [x] Offline financial mutation precision/rejection — quote lines, payments, purchase orders/receipts, event and asset costs, expenses and inventory movement use strict cents/hundredths validation.
- [x] Desktop trusted-device consent — fresh browsers do not auto-register; owner explicitly trusts the current PC from Settings.
- [x] Destructive offline workspace cleanup revocation retry — only the installation UUID is retained after an unreachable host; the next authenticated owner session retries the owner-scoped revocation. Ordinary web logout remains non-destructive.
- [x] Password reset revokes active and pending devices across all owner businesses, clears token/pairing credentials and audits each business; staff-only memberships are excluded.
- [x] Serialized-asset date availability — online/offline tests pass for non-overlap, overlap, open-ended dates and independent returns at 00af9adda.

## Current release position
V1 remains NOT READY. Application-code CI at a5ead69 passed: Laravel 526 passed / 1 skipped / 3,681 assertions; offline browser 45 desktop + 6 mobile + 1 phone-local attachment; PostgreSQL migration/recovery. Windows package run 82 at fd82f6a passed candidate build, Windows fresh install and the new Windows-native backup/private-media restore drill. Director Health, Contract Lint and Push-on-main passed. Zazu Quality passes with a temporary exact-advisory exception for the unpatched picomatch finding; Composer audit and secret scan passed. The four operator acceptance gates remain open. Evidence maturity remains 73/100; no score uplift.


## Scheduling exact-head acceptance closure
- GitHub Actions run `44152431508`: Laravel, PostgreSQL migration/recovery and offline-browser jobs all succeeded.
- Offline browser job `134284080089`: desktop/browser, mobile-viewport Work/customer and phone-local attachment journeys passed.
- PostgreSQL job `134284080622`: migrations, account/workspace registration and backup/restore/private-file recovery passed.
- Laravel job `134284080768`: service-worker/session contracts, LAN asset-origin checks, Blade/manifest/config checks and PHPUnit/Pest passed.
- Scheduling engineering/CI subgate: CLOSED. Physical Android installation and disconnected cold-launch/reconnect remain a separate owner-operated gate.


### DB-001 diagnostic contract clarification — 2026-11-17

The incident logger records privacy-minimised metadata only (request ID, classification, exception class, route/action, method/path, timestamp and source file/line). It intentionally excludes exception messages, SQL, bindings and stack traces. The incident-framework documentation has been corrected to match this implementation.

The owner-installation entry matching `aa9080f4-9580-41c9-a643-439b31928ffa` remains required. Use it to identify the exception class, source file/line and route; if those bounded fields do not isolate the cause, reproduce locally under controlled diagnostics. This documentation correction does not close DB-001 or verify the startup migration against the intended installation.


### Backup-first verification hardening — 2026-11-17

- [x] Backup creation now reopens the completed ZIP and validates ZIP consistency, manifest/driver, exact payload membership and every SHA-256 checksum before publishing it.
- [x] Added a regression for valid archive acceptance and tampered private-payload rejection.
- [ ] Exact-head Laravel and Windows workflow verification.
- [ ] Run backup-first migration on the intended installation and confirm the previously failing workflow succeeds.


### Zazu quality gate — 2026-11-17

- [x] Exact-head quality workflow run `44179163841` passed on `76be1f234e7726352c2e15b511b37b9084d2ddfa`: application, Composer security audit and secret scan.
- [x] Application job passed dependency installation, the scoped npm audit policy and service-worker authentication cache-race tests.
- [ ] Replace the temporary GHSA-fc5q-39xc-vjpf exception when a verified picomatch fix is published or the advisory is withdrawn. This exception is not a dependency fix.
