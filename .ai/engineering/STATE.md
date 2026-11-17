# Zazu EMP — Current Director State

As of: 2026-11-17
Repository: Leano-Jordan/ZazuEMP
Canonical branch: main only; never create branches
Last fully verified HEAD: 807db36e6eb9aaded81e6bb45e1e2f0c9c18c5de
Current follow-up: symmetric open-ended allocation-overlap protection; test namespace correction included in this update; exact-head CI pending.

## Verified correction
Rental asset date-allocation was reproduced at c7be735: a second non-overlapping booking returned HTTP 422 before date-overlap evaluation.
The bounded online/offline correction passed at 807db36:
- Laravel: 521 passed, 1 skipped, 3,648 assertions.
- Offline browser: 45 desktop tests, 6 mobile tests and 1 phone-local attachment journey passed.
- PostgreSQL migration/registration/backup/private-file recovery passed.
- Windows V1 package build and PowerShell fresh-install smoke passed.
- Populated upgrade/rollback, Zazu quality, Psalm, PHPMD, Director Health, Director Contract Lint and Push-on-main/CodeQL passed.
- SonarCloud skipped.

## Follow-up edge case
An allocation without an end date is open-ended. The overlap query must be symmetric: a new open-ended booking starting before an existing future booking must be rejected. This follow-up adds online/offline regression coverage and awaits exact-head CI.

## Commercial numbering
Online invoice-number format assertion passed: month-prefixed random suffix. Offline source uses an entity-UUID suffix. Numbering remains unchanged pending South African tax-adviser guidance.

## Active V1 acceptance gates
1. Physical Android installed-app cold launch, offline restart and reconnect.
2. Installed-target recovery drill including private media.
3. Live populated-business quote → invoice/payment → costs → profit reconciliation.
4. POPIA operational/privacy sign-off.

V1: NOT READY. Evidence maturity: 73/100 unchanged. Historical state/register/memory, checklist and changelog are preserved in docs/archive/engineering/2026-11-17-state-register-memory-history.md.
