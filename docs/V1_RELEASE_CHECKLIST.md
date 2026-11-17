# Zazu EMP — V1 Release Checklist

Last fully verified HEAD: Leano-Jordan/ZazuEMP / main / 807db36e6eb9aaded81e6bb45e1e2f0c9c18c5de
Current follow-up candidate: open-ended rental overlap hardening; exact-head CI pending
Disposition: NOT READY · Evidence maturity: 73/100 unchanged.

## Release acceptance gates
- [ ] Physical Android acceptance: secure-origin install, disconnected cold launch/restart, local records/attachments and reconnect sync on actual device.
- [ ] Installed-target recovery: controlled database + private-media backup/restore on intended Windows installation.
- [ ] Live commercial reconciliation: populated quote → invoice → deposit/final payment → purchasing/costs → completion → profit/margin.
- [ ] POPIA operational sign-off: privacy notice, retention/deletion, access controls and incident procedure.

## Closed acceptance
- [x] Windows fresh-install candidate package and PowerShell first-install smoke — built and verified at 807db36; not an updater or hosted deployment.
- [x] Seeded commercial workflow automation — verified; reopen only on reproducible defect.
- [x] Offline purchase-order integer-cent precision/rejection — verified.
- [x] Password reset revokes the owner's trusted devices — independently verified in source.
- [x] Bounded asset date availability correction — Laravel and offline tests passed at 807db36; follow-up open-ended overlap case awaits CI.

## Current audit execution
Rental date-allocation defect was reproduced at c7be735 and corrected at 807db36. Exact-head CI passed, including Windows install, PostgreSQL recovery and desktop/mobile offline acceptance. A final open-ended interval edge-case test is in the current follow-up candidate. Invoice numbering remains unchanged pending SA tax-adviser advice. No score uplift or release gate closure.
