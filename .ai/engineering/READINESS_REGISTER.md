# Zazu EMP — V1 Readiness Register

Current baseline: Leano-Jordan/ZazuEMP / main / 00af9adda943ee4e232e7eedeaf894a809dcbe88 / 17 Nov 2026
Release disposition: NOT READY
Evidence maturity: 73/100 unchanged; no uplift claimed.

## Existing release gates
| Gate | Disposition | Closure evidence |
|---|---|---|
| Physical offline device | OPEN | Actual Android installed-app cold launch, disconnected restart, local continuity and reconnect |
| Installation recovery | OPEN | Controlled Windows installation restore including private media |
| Commercial reconciliation | OPEN | Populated real-business quote, invoice, payment, cost and profit reconciliation |
| POPIA operations | OPEN | Owner-approved privacy/retention, incident and operational controls |
| Windows package | CLOSED for defined fresh-install candidate | Windows package build + PowerShell fresh-install smoke at 00af9adda; not an updater or hosted deployment |

## Claude independent audit reconciliation — 2026-11-17
1. Owner web login auto-trusts browsers for 180 days vs 120-minute session; offline logout has no revoke retry — OWNER-DECISION; covered by existing session/device work.
2. Six-digit pairing code, per-IP throttle only, no failed-attempt lockout — DEFER; low-confidence hardening, no demonstrated V1 exploit.
3. Loopback-only proxy trust and no HTTPS forcing — ACCEPTED LIMITATION for local Windows/LAN V1; hosted reverse-proxy behavior is outside active deployment scope.
4. CurrentBusiness test-only branch, min-8 password policy, generated manifest drift, offline bootstrap permission filtering — MIXED: generated manifest tracking corrected; bootstrap endpoint permission gate covered; password-policy expansion and per-permission filtering deferred; test-only branch accepted.
5. Rental asset date allocation — REPRODUCED and FIXED. Online/offline tests at 00af verify non-overlapping periods can be booked, overlapping periods are rejected, open-ended ranges conflict correctly, and individual returns preserve allocated status until the final active booking is returned. Manual physical return remains operator-confirmed; no automatic release on event state/date alone. Quantity-based rental stock remains outside the serialized-asset model and is an OWNER-DECISION / SCOPE item.
6. Multi-day events, start/end/setup/collection times, clash detection and staff scheduling — OWNER-DECISION / SCOPE; no implementation without explicit V1 authorization.
7. Invoice numbering (online month-prefixed random suffix; offline UUID suffix) — OWNER-DECISION pending South African tax-adviser guidance; current format assertion passed, numbering unchanged.
8. Team invites/role assignment, email/PDF delivery, billing, app-wide licence enforcement, in-app legal terms/privacy, monitoring and scheduled jobs — OWNER-DECISION / SCOPE; no silent V1 expansion.

## Documentation and commercial decisions
- Offline work is ACTIVE/RESUMED; stale pause notes are historical and archived.
- Tier conflict remains OWNER-DECISION: product-specification/operating-profile describe three tiers (Basic Free, Intermediate, Advanced), while commercial requirements specify four paid prices (R299, R499, R799, R1,299). No tier or price decision inferred.
- Root ROSCORE_PROJECT_MANIFEST.md is canonical; docs/ROSCOR_PROJECT_MANIFEST.md is a legacy pointer.
- Prior long-form state/register/memory, checklist and changelog are preserved in docs/archive/engineering/2026-11-17-state-register-memory-history.md.

## Exact-head CI — 00af9adda
- Laravel: PASS — 523 passed, 1 skipped, 3,653 assertions.
- Offline browser: PASS — 45 desktop, 6 mobile, 1 phone-local attachment.
- PostgreSQL migration/registration/backup/private-file recovery: PASS.
- Windows V1 package + PowerShell fresh install: PASS.
- Populated upgrade/rollback, Zazu quality, Psalm, PHPMD, Director Health, Contract Lint and Push-on-main/CodeQL: PASS.
- SonarCloud: SKIPPED.
- No score uplift. Remaining V1 gates are listed above.

## Next
Close the remaining physical device, installed-target recovery, live financial reconciliation and POPIA operational gates. Keep all owner-decision scope items unchanged until approved.
