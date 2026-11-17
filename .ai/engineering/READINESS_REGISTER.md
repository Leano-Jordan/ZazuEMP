# Zazu EMP — V1 Readiness Register

Baseline: Leano-Jordan/ZazuEMP / main / c7be735021e24b88052511c91652d71607f71383 / 17 Nov 2026
Current correction candidate: pending exact commit SHA and CI
Release disposition: NOT READY
Evidence maturity: 73/100 unchanged; no uplift claimed.

## Existing release gates
| Gate | Disposition | Closure evidence |
|---|---|---|
| Physical offline device | OPEN | Actual Android installed-app cold launch, disconnected restart, local continuity and reconnect |
| Installation recovery | OPEN | Controlled Windows installation restore including private media |
| Commercial reconciliation | OPEN | Populated real-business quote, invoice, payment, cost and profit reconciliation |
| POPIA operations | OPEN | Owner-approved privacy/retention, incident and operational controls |
| Windows package | CLOSED for defined fresh-install candidate | Existing source/run evidence; not an updater or hosted deployment |

## Claude independent audit reconciliation — 2026-11-17
1. Owner web login auto-trusts browsers for 180 days vs 120-minute session; offline logout has no revoke retry — OWNER-DECISION; covered by existing session/device work.
2. Six-digit pairing code, per-IP throttle only, no failed-attempt lockout — DEFER; low-confidence hardening, no demonstrated V1 exploit.
3. Loopback-only proxy trust and no HTTPS forcing — ACCEPTED LIMITATION for local Windows/LAN V1; hosted reverse-proxy behavior is outside active deployment scope.
4. CurrentBusiness test-only branch, min-8 password policy, generated manifest drift, offline bootstrap permission filtering — MIXED: generated manifest tracking corrected; bootstrap endpoint permission gate covered; password-policy expansion and per-permission filtering deferred; test-only branch accepted.
5. Rental asset date allocation — REPRODUCED. Run 44075168970 at c7be735: second non-overlapping booking returned HTTP 422 because global status blocked the request before overlap evaluation. Correction candidate now covers online/offline date booking, per-allocation returns and active-status consistency; exact-head CI pending. Manual physical return remains operator-confirmed; no automatic release on event state/date alone. Quantity-based rental stock remains outside the serialized-asset model.
6. Multi-day events, start/end/setup/collection times, clash detection and staff scheduling — OWNER-DECISION / SCOPE; no implementation without explicit V1 authorization.
7. Invoice numbering (online month-prefixed random suffix; offline UUID suffix) — OWNER-DECISION pending South African tax-adviser guidance; current format assertion added, numbering unchanged.
8. Team invites/role assignment, email/PDF delivery, billing, app-wide licence enforcement, in-app legal terms/privacy, monitoring and scheduled jobs — OWNER-DECISION / SCOPE; no silent V1 expansion.

## Documentation and commercial decisions
- Offline work is ACTIVE/RESUMED; stale pause notes are historical and archived.
- Tier conflict remains OWNER-DECISION: product-specification/operating-profile describe three tiers (Basic Free, Intermediate, Advanced), while commercial requirements specify four paid prices (R299, R499, R799, R1,299). No tier or price decision inferred.
- Root ROSCORE_PROJECT_MANIFEST.md is canonical; docs/ROSCOR_PROJECT_MANIFEST.md is a legacy pointer.
- Prior long-form state/register/memory, checklist and changelog are preserved in docs/archive/engineering/2026-11-17-state-register-memory-history.md.

## Exact reproduction evidence
- Laravel run 44075168970 at c7be735: 520 passed, 1 skipped, 3,638 assertions; one targeted rental reproduction failed with HTTP 422.
- On first correction attempt dbd6754, Laravel reported 510 passed, 1 skipped, 11 failed due to an extra closing brace in AssetController. OfflineOperationalDepthTest passed. PostgreSQL migration/registration/recovery passed; quality, PHPMD, Psalm, Director Health, Director Contract Lint, populated upgrade and Push-on-main passed. SonarCloud skipped; Windows package and offline-browser acceptance were still running at last observation.
- Current syntax correction and exact-head CI are pending.
- No score uplift. Existing release gates remain open.

## Next
Complete exact-head CI on the corrected candidate. Close only when online/offline rental regressions pass and overlap protection remains effective.
