# Zazu EMP — V1 Release Checklist

Baseline: Leano-Jordan/ZazuEMP / main / ac79543732f2c3c5345cd095dce6e55eb16ac319 (17 Nov 2026)
Disposition: NOT READY · Evidence maturity: 73/100 unchanged.

## Release acceptance gates
- [ ] Physical Android acceptance: install/open from intended secure origin; disconnect PC/server/network; cold launch and restart; verify local records/attachments remain; reconnect and verify sync acknowledgement.
- [ ] Installed-target recovery: controlled database + private-media backup/restore on intended Windows installation.
- [ ] Live commercial reconciliation: populated quote → invoice → deposit/final payment → purchasing/costs → completion → profit/margin.
- [ ] POPIA operational sign-off: approve privacy notice, retention/deletion handling, access controls and incident procedure.

## Closed acceptance
- [x] Windows fresh-install candidate package and PowerShell first-install smoke — closed for defined fresh-install criteria at its recorded source/run; not an updater or hosted deployment.
- [x] Seeded commercial workflow automation — verified; reopen only on reproducible defect.
- [x] Offline purchase-order integer-cent precision/rejection — verified and covered by regression suite.
- [x] Password reset revokes the owner's trusted devices — independently verified in source.

## Independent audit reconciliation
All eight Claude findings are triaged in the existing readiness register; no second backlog or scorecard. Rental date-allocation has a focused regression reproduction awaiting exact-head CI; no application fix is included. Invoice numbering remains unchanged pending South African tax-adviser advice. Multi-day scheduling and team/communications/billing expansion require explicit owner approval. Persistent trusted-device scope and product tier count also require owner decisions.

Exact-head CI at ac795437: Laravel 520 passed / 1 skipped / 3,635 assertions; PostgreSQL migration/registration/recovery and offline browser acceptance jobs passed; Zazu quality and CodeQL passed; SonarCloud skipped. See the readiness register for run links and exact evidence.
