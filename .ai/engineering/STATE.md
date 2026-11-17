# Zazu EMP — Current Director State

As of: 2026-11-17
Repository: Leano-Jordan/ZazuEMP
Current main HEAD: 00af9adda943ee4e232e7eedeaf894a809dcbe88
Canonical branch: main only; never create branches
Owner mandate: ship a dependable first-business V1; preserve scope; no Render or AMPPS workflow.

## Verified work
Rental asset date allocation was reproduced at c7be735: a second non-overlapping booking returned HTTP 422 before date-overlap evaluation. The online/offline correction now permits non-overlapping reservations, rejects overlapping and open-ended conflicts, supports per-allocation returns, and keeps asset status allocated until all active bookings are returned. Manual physical return remains operator-confirmed.

Online and offline regressions pass at current HEAD. The optional end date is normalized once and used consistently for validation, overlap checks, persistence and audit.

## Exact-head CI — 00af9adda
- Laravel run 44079343772: PASS — 523 passed, 1 skipped, 3,653 assertions.
- Offline browser acceptance: PASS — 45 desktop, 6 mobile and 1 phone-local attachment journey.
- PostgreSQL migration/recovery: PASS — migration/registration and real backup/private-file restore.
- Windows V1 package run 44079343934: PASS — package built and Windows PowerShell fresh-install verification passed (private environment, app key, SQLite migration, route boot and local HTTP session cookie).
- Populated upgrade/rollback run 44079344032: PASS.
- Zazu quality run 44079343883: PASS.
- Psalm run 44079343941: PASS.
- PHPMD run 44079345552: PASS.
- Director Health run 44079343910: PASS.
- Director Contract Lint run 44079343776: PASS.
- Push-on-main / CodeQL run 44079343748: PASS.
- SonarCloud run 44079344053: SKIPPED, not counted as a pass.

No tests were weakened. Evidence maturity remains 73/100; no score uplift.

## Independent audit reconciliation
All eight Claude findings remain in the sole readiness register. Invoice numbering is unchanged pending South African tax-adviser guidance. Session auto-trust scope, commercial tiers and feature expansions remain owner decisions. Pairing-code hardening is deferred; hosted proxy behavior is an accepted limitation for local Windows/LAN V1.

## Active V1 acceptance gates
1. Physical Android installed-app cold launch, offline restart and reconnect.
2. Installed-target recovery drill including private media.
3. Live populated-business quote → invoice/payment → costs → profit reconciliation.
4. POPIA operational/privacy sign-off.

## Release
V1: NOT READY. Historical state/register/memory, checklist and changelog are preserved in docs/archive/engineering/2026-11-17-state-register-memory-history.md.
