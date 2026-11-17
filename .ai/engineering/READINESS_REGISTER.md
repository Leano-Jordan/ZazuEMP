# Zazu EMP — V1 Readiness Register

Last verified source baseline: Leano-Jordan/ZazuEMP / main / e5dc263aaa1fd509061c12abdb158c5b5765dfee / 17 Nov 2026
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
1. Owner web login auto-trusts browsers for 180 days vs 120-minute session; offline cleanup has no revoke retry — DESKTOP AUTO-TRUST CLOSED: fresh browsers require explicit owner approval in Settings; destructive local cleanup now retries revocation on the next authenticated owner session using only the installation UUID. Installed-phone behaviour remains an accepted owner limitation; ordinary web logout remains non-destructive by design.
2. Six-digit pairing code, per-IP throttle only, no failed-attempt lockout — DEFER; low-confidence hardening, no demonstrated V1 exploit.
3. Loopback-only proxy trust and no HTTPS forcing — ACCEPTED LIMITATION for local Windows/LAN V1; hosted reverse-proxy behavior is outside active deployment scope.
4. CurrentBusiness test-only branch, min-8 password policy, generated manifest drift, offline bootstrap permission filtering — MIXED: generated manifest tracking corrected; bootstrap endpoint permission gate covered; password-policy expansion and per-permission filtering deferred; test-only branch accepted.
5. Rental asset date allocation — REPRODUCED and FIXED. Online/offline tests at 00af verify non-overlapping periods can be booked, overlapping periods are rejected, open-ended ranges conflict correctly, and individual returns preserve allocated status until the final active booking is returned. Manual physical return remains operator-confirmed; no automatic release on event state/date alone. Quantity-based rental stock remains outside the serialized-asset model and is an OWNER-DECISION / SCOPE item.
6. Multi-day events, start/end/setup/collection times, clash detection and staff scheduling — OWNER-DECISION / SCOPE; no implementation without explicit V1 authorization.
7. Invoice numbering (online month-prefixed random suffix; offline UUID suffix) — OWNER-DECISION pending South African tax-adviser guidance; current format assertion passed, numbering unchanged.
8. Team invites/role assignment, email/PDF delivery, billing, app-wide licence enforcement, in-app legal terms/privacy, monitoring and scheduled jobs — OWNER-DECISION / SCOPE; no silent V1 expansion.

## Verified engineering corrections — e5dc263
- Password reset revokes active and pending trusted devices across every business where the resetting user is an owner, clears bearer/pairing hashes and expiry, and writes audit events. Businesses where the user is staff only remain untouched.
- Offline financial mutation validation rejects precision beyond two decimal places before posting; quote, payment, purchase-order, receipt, cost, expense and inventory regressions are covered.
- CI verifies 523 Laravel tests passed / 1 skipped / 3,662 assertions; 45 desktop Chromium, 6 mobile-viewport and 1 phone-local attachment journey passed; PostgreSQL recovery, Windows fresh-install package, populated upgrade/rollback, quality, Psalm, PHPMD, Director Health and Push-on-main passed. SonarCloud skipped.

## Documentation and commercial decisions
- Offline work is ACTIVE/RESUMED; stale pause notes are historical and archived.
- Tier conflict remains OWNER-DECISION: product-specification/operating-profile describe three tiers (Basic Free, Intermediate, Advanced), while commercial requirements specify four paid prices (R299, R499, R799, R1,299). No tier or price decision inferred.
- Root ROSCORE_PROJECT_MANIFEST.md is canonical; docs/ROSCOR_PROJECT_MANIFEST.md is a legacy pointer.
- Prior long-form state/register/memory, checklist and changelog are preserved in docs/archive/engineering/2026-11-17-state-register-memory-history.md.

## Exact-head CI — e5dc263
- Laravel: PASS — 523 passed, 1 skipped, 3,662 assertions.
- Offline browser: PASS — 45 desktop, 6 mobile, 1 phone-local attachment.
- PostgreSQL migration/registration/backup/private-file recovery: PASS.
- Windows V1 package + PowerShell fresh install: PASS.
- Populated upgrade/rollback, Zazu quality, Psalm, PHPMD, Director Health and Push-on-main: PASS.
- SonarCloud: SKIPPED.
- Evidence maturity remains 73/100. No score uplift.

## Next
Close the remaining physical device, installed-target recovery, live financial reconciliation and POPIA operational gates. Keep all owner-decision scope items unchanged until approved.
