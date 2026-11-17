# Zazu EMP — Current Director State

As of: 2026-11-17
Repository: Leano-Jordan/ZazuEMP
Last verified engineering source baseline: fd82f6a5fb59365687fac903f93e944394145427 (17 Nov 2026)
Last application-code test baseline: a5ead696429867bcd57342406bb37662ca1b2f2c
Canonical branch: main only; never create branches. Engineering changes are active on main; no branches created.
Owner mandate: ship a dependable first-business V1; preserve scope; no Render or AMPPS workflow.

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
