# Zazu EMP — V1 Readiness Register

Last fully verified HEAD: Leano-Jordan/ZazuEMP / main / 807db36e6eb9aaded81e6bb45e1e2f0c9c18c5de
Current follow-up: open-ended rental overlap hardening; exact-head CI pending
Release disposition: NOT READY
Evidence maturity: 73/100 unchanged; no uplift claimed.

## Existing release gates
| Gate | Disposition | Closure evidence |
|---|---|---|
| Physical offline device | OPEN | Actual Android installed-app cold launch, disconnected restart, local continuity and reconnect |
| Installation recovery | OPEN | Controlled Windows installation restore including private media |
| Commercial reconciliation | OPEN | Populated real-business quote, invoice, payment, cost and profit reconciliation |
| POPIA operations | OPEN | Owner-approved privacy/retention, incident and operational controls |
| Windows package | CLOSED for defined fresh-install candidate | Windows package build + PowerShell fresh-install smoke at 807db36; not an updater or hosted deployment |

## Claude independent audit reconciliation — 2026-11-17
1. Owner web login auto-trusts browsers for 180 days vs 120-minute session; offline logout has no revoke retry — OWNER-DECISION; covered by existing session/device work.
2. Six-digit pairing code, per-IP throttle only, no failed-attempt lockout — DEFER; low-confidence hardening, no demonstrated V1 exploit.
3. Loopback-only proxy trust and no HTTPS forcing — ACCEPTED LIMITATION for local Windows/LAN V1; hosted reverse-proxy behavior is outside active deployment scope.
4. CurrentBusiness test-only branch, min-8 password policy, generated manifest drift, offline bootstrap permission filtering — MIXED: generated manifest tracking corrected; bootstrap endpoint permission gate covered; password-policy expansion and per-permission filtering deferred; test-only branch accepted.
5. Rental asset date allocation — REPRODUCED and CORRECTED for explicit date ranges at 807db36. Exact-head Laravel and offline tests passed: non-overlapping periods can be booked, overlapping periods are rejected, individual returns preserve allocated status until the final active booking is returned. Follow-up adds symmetric open-ended overlap protection; tests were split after PHPMD flagged method length, without weakening the rule. Exact-head CI pending. Manual physical return remains operator-confirmed; no automatic release on event state/date alone. Quantity-based rental stock remains outside the serialized-asset model.
6. Multi-day events, start/end/setup/collection times, clash detection and staff scheduling — OWNER-DECISION / SCOPE; no implementation without explicit V1 authorization.
7. Invoice numbering (online month-prefixed random suffix; offline UUID suffix) — OWNER-DECISION pending South African tax-adviser guidance; current format assertion passed, numbering unchanged.
8. Team invites/role assignment, email/PDF delivery, billing, app-wide licence enforcement, in-app legal terms/privacy, monitoring and scheduled jobs — OWNER-DECISION / SCOPE; no silent V1 expansion.

## Documentation and commercial decisions
- Offline work is ACTIVE/RESUMED; stale pause notes are historical and archived.
- Tier conflict remains OWNER-DECISION: product-specification/operating-profile describe three tiers (Basic Free, Intermediate, Advanced), while commercial requirements specify four paid prices (R299, R499, R799, R1,299). No tier or price decision inferred.
- Root ROSCORE_PROJECT_MANIFEST.md is canonical; docs/ROSCOR_PROJECT_MANIFEST.md is a legacy pointer.
- Prior long-form state/register/memory, checklist and changelog are preserved in docs/archive/engineering/2026-11-17-state-register-memory-history.md.

## Exact-head CI — 807db36
- Laravel run 44076956083: PASS — 521 passed, 1 skipped, 3,648 assertions.
- Offline browser acceptance: PASS — 45 desktop, 6 mobile and 1 phone-local attachment journey.
- PostgreSQL migration/registration/backup/private-file recovery: PASS.
- Windows V1 package build and PowerShell fresh-install verification: PASS.
- Populated upgrade/rollback, Zazu quality, Psalm, PHPMD, Director Health, Director Contract Lint and Push-on-main/CodeQL: PASS.
- SonarCloud: SKIPPED.
- No score uplift. Existing release gates remain open.

## Next
Run exact-head CI on the refactored open-ended overlap follow-up. Keep invoice numbering and all owner-decision scope items unchanged.
