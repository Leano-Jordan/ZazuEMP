# Zazu EMP — Current Director State

As of: 2026-11-17
Repository: Leano-Jordan/ZazuEMP
Canonical branch: main only; never create branches
Previous verified baseline: ac79543732f2c3c5345cd095dce6e55eb16ac319
Reproduction commit: c7be735021e24b88052511c91652d71607f71383
Correction candidate: pending commit SHA and exact-head CI
Owner mandate: ship a dependable first-business V1; preserve scope; no Render or AMPPS workflow.

## Current mission
Close reproducible defects in agreed V1 workflows and demonstrated security/privacy/data-integrity failures. Offline V1 work is RESUMED and authorized under AGENTS.md for bounded workflows that close V1 gaps.

## Reproduction evidence
- Rental date allocation reproduced in Laravel run 44075168970 at c7be735.
- The new test attempted two non-overlapping periods on one asset; the second request returned HTTP 422 before date-overlap evaluation.
- That run recorded 520 passed, 1 skipped, 3,638 assertions and one intentional new regression failure. PostgreSQL migration/registration/recovery passed. Zazu quality, Director Contract Lint, Director Health and Push-on-main passed; SonarCloud was skipped. Offline-browser acceptance was still running at the last observation.
- Online invoice-number assertion confirms the month-prefixed random suffix format; offline source uses an entity-UUID suffix. No numbering change is authorized before South African tax-adviser guidance.

## Correction candidate
A bounded correction permits additional allocations only when active allocations do not overlap; exposes allocation end dates; supports returning a specific allocation; preserves allocated status while any active booking remains; and keeps offline sync/optimistic state consistent. Physical return remains operator-confirmed; no automatic release on event completion/cancellation/date passage.

## Active V1 acceptance gates
1. Physical Android installed-app cold launch, offline restart and reconnect.
2. Installed-target recovery drill including private media.
3. Live populated-business quote → invoice/payment → costs → profit reconciliation.
4. POPIA operational/privacy sign-off.

## Release
V1: NOT READY. Evidence maturity: 73/100 unchanged. Historical state/register/memory snapshots are archived at docs/archive/engineering/2026-11-17-state-register-memory-history.md.
