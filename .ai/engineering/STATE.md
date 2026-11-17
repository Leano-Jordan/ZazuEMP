# Zazu EMP — Current Director State

As of: 2026-11-17
Repository: Leano-Jordan/ZazuEMP
Last verified source baseline: e5dc263aaa1fd509061c12abdb158c5b5765dfee
Canonical branch: main only; never create branches. Engineering changes are active on main; no branches created.
Owner mandate: ship a dependable first-business V1; preserve scope; no Render or AMPPS workflow.

## Current engineering delta — ff7f0bb (partial verification)
Queued financial mutation validation now matches connected-workflow rules for payment type/method, expense status, configured quote currency and calendar dates. All-zero offline purchase receipts are rejected before consuming their idempotency key. Focused regression coverage includes invalid enums/dates/currency, float-decoded purchase-order precision and all-zero receipts.
Exact code-head Laravel: PASS — 526 passed / 1 skipped / 3,680 assertions. PHPMD, Zazu Quality, Director Health, Composer security audit, secret scan, CodeQL and PostgreSQL migration/recovery: PASS. Offline browser acceptance remains in progress; no browser pass is claimed yet.

## Verified work
Rental asset date allocation was reproduced at c7be735: a second non-overlapping booking returned HTTP 422 before date-overlap evaluation. The online/offline correction now permits non-overlapping reservations, rejects overlapping and open-ended conflicts, supports per-allocation returns, and keeps asset status allocated until all active bookings are returned. Manual physical return remains operator-confirmed.

Online and offline regressions pass at current HEAD. The optional end date is normalized once and used consistently for validation, overlap checks, persistence and audit.

Trusted-device security corrections at e5dc263: fresh desktop browsers no longer receive long-lived sync credentials automatically; an owner must explicitly trust the current device in Settings. The installed-phone behaviour remains an accepted owner limitation. If destructive local workspace cleanup cannot reach the host to revoke a device, only its business-scoped installation UUID is retained and the next authenticated owner session retries revocation through an owner-only endpoint. Ordinary web logout remains non-destructive and preserves the trusted workspace by design.

Password recovery now revokes active and pending sync devices across every business where the resetting user has the owner role, clears token and pairing credentials, and records per-business audit events. Staff-only business devices are left untouched. The regression covers an owner whose currently selected business is a staff membership, protecting cross-business recovery from the normal active-workspace save guard.

Offline financial push validation uses integer-cent / hundredths parsing and rejects excess precision before posting. Regression coverage includes quote line quantity and price, event and asset costs, inventory movement, expenses, purchase-order quantity and price, purchase receipts, and payments.

## Exact-head CI — e5dc263
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
