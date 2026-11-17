# Zazu EMP — V1 Release Checklist

Last verified source baseline: Leano-Jordan/ZazuEMP / main / e5dc263aaa1fd509061c12abdb158c5b5765dfee (17 Nov 2026)
Disposition: NOT READY · Evidence maturity: 73/100 unchanged.

## Release acceptance gates
- [ ] Physical Android acceptance: secure-origin install, disconnected cold launch/restart, local records/attachments and reconnect sync on actual device.
- [ ] Installed-target recovery: controlled database + private-media backup/restore on intended Windows installation.
- [ ] Live commercial reconciliation: populated quote → invoice → deposit/final payment → purchasing/costs → completion → profit/margin.
- [ ] POPIA operational sign-off: privacy notice, retention/deletion, access controls and incident procedure.

## Engineering verification in progress — ff7f0bb
- [ ] Offline financial push contract: payment/expense enums, configured quote currency, calendar-date validation, all-zero receipt rejection and float-decoded purchase-order precision. Close only on exact-head Laravel and quality/security evidence.

## Closed acceptance
- [x] Windows fresh-install candidate package and PowerShell first-install smoke — built and verified at 00af9adda; not an updater or hosted deployment.
- [x] Seeded commercial workflow automation — verified; reopen only on reproducible defect.
- [x] Offline financial mutation precision/rejection — quote lines, payments, purchase orders/receipts, event and asset costs, expenses and inventory movement use strict cents/hundredths validation.
- [x] Desktop trusted-device consent — fresh browsers do not auto-register; owner explicitly trusts the current PC from Settings.
- [x] Destructive offline workspace cleanup revocation retry — only the installation UUID is retained after an unreachable host; the next authenticated owner session retries the owner-scoped revocation. Ordinary web logout remains non-destructive.
- [x] Password reset revokes active and pending devices across all owner businesses, clears token/pairing credentials and audits each business; staff-only memberships are excluded.
- [x] Serialized-asset date availability — online/offline tests pass for non-overlap, overlap, open-ended dates and independent returns at 00af9adda.

## Current release position
V1 remains NOT READY. Exact-head CI at e5dc263 passed: Laravel 523 passed / 1 skipped / 3,662 assertions; offline browser 45 desktop + 6 mobile + 1 phone-local attachment; PostgreSQL migration/recovery; Windows package/fresh install; populated upgrade/rollback; Zazu quality, Psalm, PHPMD, Director Health and Push-on-main. SonarCloud skipped. Evidence maturity remains 73/100; no score uplift.
