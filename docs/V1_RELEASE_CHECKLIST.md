# Zazu EMP — V1 Release Checklist

Last verified source baseline: Leano-Jordan/ZazuEMP / main / 00af9adda943ee4e232e7eedeaf894a809dcbe88 (17 Nov 2026)
Disposition: NOT READY · Evidence maturity: 73/100 unchanged.

## Release acceptance gates
- [ ] Physical Android acceptance: secure-origin install, disconnected cold launch/restart, local records/attachments and reconnect sync on actual device.
- [ ] Installed-target recovery: controlled database + private-media backup/restore on intended Windows installation.
- [ ] Live commercial reconciliation: populated quote → invoice → deposit/final payment → purchasing/costs → completion → profit/margin.
- [ ] POPIA operational sign-off: privacy notice, retention/deletion, access controls and incident procedure.

## Closed acceptance
- [x] Windows fresh-install candidate package and PowerShell first-install smoke — built and verified at 00af9adda; not an updater or hosted deployment.
- [x] Seeded commercial workflow automation — verified; reopen only on reproducible defect.
- [x] Offline purchase-order integer-cent precision/rejection — verified.
- [x] Password reset revokes the owner's trusted devices — independently verified in source.
- [x] Serialized-asset date availability — online/offline tests pass for non-overlap, overlap, open-ended dates and independent returns at 00af9adda.

## Current release position
V1 remains NOT READY. Exact-head CI passed: Laravel 523 passed / 1 skipped / 3,653 assertions; offline browser 45 desktop + 6 mobile + 1 phone-local attachment; PostgreSQL recovery; Windows package/fresh install; populated upgrade/rollback; quality, Psalm, PHPMD and CodeQL. SonarCloud skipped. No score uplift.
