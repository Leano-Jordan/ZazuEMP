# Zazu EMP — Current Director State

As of: 2026-11-17
Repository: Leano-Jordan/ZazuEMP
Canonical branch: main only; never create branches
Last complete baseline: ac79543732f2c3c5345cd095dce6e55eb16ac319
Reproduction commit: c7be735021e24b88052511c91652d71607f71383
Previous correction attempt: dbd675448ccf62e344dbda1e0a2bc8898a8178b1 (blocked by an extra closing brace; corrected in the current candidate)
Current correction candidate: pending commit SHA and exact-head CI
Owner mandate: ship a dependable first-business V1; preserve scope; no Render or AMPPS workflow.

## Current mission
Close reproducible defects in agreed V1 workflows and demonstrated security/privacy/data-integrity failures. Offline V1 work is RESUMED and authorized under AGENTS.md for bounded workflows that close V1 gaps.

## Reproduction evidence
- Rental date allocation reproduced in Laravel run 44075168970 at c7be735.
- Two non-overlapping periods on one serialized asset: second booking returned HTTP 422 before date-overlap evaluation.
- Reproduction run: 520 passed, 1 skipped, 3,638 assertions, one intentional new regression failure. PostgreSQL migration/registration/recovery passed. Zazu quality, Director Contract Lint, Director Health and Push-on-main passed; SonarCloud skipped.
- The first correction candidate had a PHP ParseError from one extra closing brace in AssetController; the new candidate removes it. OfflineOperationalDepthTest passed in that candidate; full Laravel run had 510 passed, 1 skipped and 11 failures caused by that parse error.
- Online invoice-number format assertion passed: month-prefixed random suffix. Offline source uses an entity-UUID suffix. No numbering change is authorized before South African tax-adviser guidance.

## Current correction
Allow additional allocations only when active date ranges do not overlap; expose allocation end dates; return a specific allocation; preserve allocated status while any active booking remains; keep offline sync and optimistic state consistent. Physical return remains operator-confirmed; no automatic release on event completion/cancellation/date passage.

## Active V1 acceptance gates
1. Physical Android installed-app cold launch, offline restart and reconnect.
2. Installed-target recovery drill including private media.
3. Live populated-business quote → invoice/payment → costs → profit reconciliation.
4. POPIA operational/privacy sign-off.

## Release
V1: NOT READY. Evidence maturity: 73/100 unchanged. Historical state/register/memory, checklist and prior changelog are preserved in docs/archive/engineering/2026-11-17-state-register-memory-history.md.
