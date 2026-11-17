# Zazu EMP — V1 Release Checklist

Baseline: Leano-Jordan/ZazuEMP / main / c7be735021e24b88052511c91652d71607f71383 (17 Nov 2026)
Disposition: NOT READY · Evidence maturity: 73/100 unchanged.

## Release acceptance gates
- [ ] Physical Android acceptance: secure-origin install, disconnected cold launch/restart, local records/attachments and reconnect sync on actual device.
- [ ] Installed-target recovery: controlled database + private-media backup/restore on intended Windows installation.
- [ ] Live commercial reconciliation: populated quote → invoice → deposit/final payment → purchasing/costs → completion → profit/margin.
- [ ] POPIA operational sign-off: privacy notice, retention/deletion, access controls and incident procedure.

## Closed acceptance
- [x] Windows fresh-install candidate package and PowerShell first-install smoke — closed for defined criteria at its recorded source/run; not an updater or hosted deployment.
- [x] Seeded commercial workflow automation — verified; reopen only on reproducible defect.
- [x] Offline purchase-order integer-cent precision/rejection — verified.
- [x] Password reset revokes the owner's trusted devices — independently verified in source.

## Current audit execution
Rental date-allocation defect reproduced: run 44075168970, c7be735, 520 passed / 1 skipped / 3,638 assertions plus one targeted failure (non-overlapping booking received HTTP 422). A bounded online/offline correction is included in the current candidate; exact-head CI is pending. Invoice numbering remains unchanged pending SA tax-adviser advice. No score uplift or release gate closure.
