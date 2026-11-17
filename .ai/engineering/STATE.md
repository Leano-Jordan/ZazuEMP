# Zazu EMP — Current Director State

As of: 2026-11-17
Repository: Leano-Jordan/ZazuEMP
Last verified repository HEAD: 8bf76fe034f888138c0542dc308e3d4d752bd304 (17 Nov 2026)
Last application-code test baseline: 71c8fc5c7aeba392b95e26aee694945b286f2c8e
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
