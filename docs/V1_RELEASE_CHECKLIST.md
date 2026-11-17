# Zazu EMP — V1 Release Checklist

Last verified engineering source baseline: Leano-Jordan/ZazuEMP / main / fd82f6a5fb59365687fac903f93e944394145427 (17 Nov 2026)
Last application-code test baseline: a5ead696429867bcd57342406bb37662ca1b2f2c
Disposition: NOT READY · Evidence maturity: 73/100 unchanged.

## Release acceptance gates
- [ ] Physical Android acceptance: secure-origin install, disconnected cold launch/restart, local records/attachments and reconnect sync on actual device.
- [ ] Installed-target recovery: controlled database + private-media backup/restore on intended Windows installation.
- [ ] Live commercial reconciliation: populated quote → invoice → deposit/final payment → purchasing/costs → completion → profit/margin.
- [ ] POPIA operational sign-off: privacy notice, retention/deletion, access controls and incident procedure.

## Latest CI verification

- Application-code baseline a5ead69: Laravel 526 passed / 1 skipped / 3,681 assertions; offline browser acceptance 45 desktop + 6 mobile-viewport + 1 phone-local attachment; PostgreSQL migration/registration/backup/private-file recovery passed.
- Repository head fd82f6a changes only the Windows package workflow after that application-code baseline.
- Windows V1 Package run 82: PASS — candidate package built; Windows PowerShell first install passed; Windows-native backup/restore recovered both a disposable SQLite record and private-media bytes.
- Director Health, Director Contract Lint and Push-on-main passed.
- LAN asset delivery regression passed at a5ead69: a remote client with a stale loopback Vite hot marker receives compiled same-origin CSS/JS. The supplied 13 Nov HTTP screenshot predates this correction; physical Android HTTPS launch remains required.
- Zazu Quality is RED at npm audit because GHSA-fc5q-39xc-vjpf / CVE-2026-112366 flags the locked picomatch build-tool dependency chain. GitHub currently lists no patched version. Composer security audit and secret scan passed. The advisory is not suppressed and no breaking Vite downgrade was applied.
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
V1 remains NOT READY. Application-code CI at a5ead69 passed: Laravel 526 passed / 1 skipped / 3,681 assertions; offline browser 45 desktop + 6 mobile + 1 phone-local attachment; PostgreSQL migration/recovery. Windows package run 82 at fd82f6a passed candidate build, Windows fresh install and the new Windows-native backup/private-media restore drill. Director Health, Contract Lint and Push-on-main passed. Zazu Quality is currently red due the unpatched picomatch advisory; Composer audit and secret scan passed. The four operator acceptance gates remain open. Evidence maturity remains 73/100; no score uplift.
