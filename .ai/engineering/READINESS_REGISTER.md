# Zazu EMP — V1 Readiness Register

Baseline: Leano-Jordan/ZazuEMP / main / ac79543732f2c3c5345cd095dce6e55eb16ac319 / 17 Nov 2026
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
Baseline: live main HEAD ac79543732f2c3c5345cd095dce6e55eb16ac319; Claude baseline 325dc67 is older. Reconciled here; no second backlog:
1. Owner web login auto-trusts browsers for 180 days vs 120-minute session; offline logout has no revoke retry — OWNER-DECISION on intended all-browser persistent trust; already covered by session/device work.
2. Six-digit pairing code, per-IP throttle only, no failed-attempt lockout — DEFER; low-confidence/low-priority hardening, no demonstrated V1 exploit.
3. Loopback-only proxy trust and no HTTPS forcing — ACCEPTED LIMITATION for current local Windows/LAN V1; hosted reverse-proxy behavior is outside active deployment scope.
4. CurrentBusiness test-only branch, min-8 password policy, generated manifest drift, offline bootstrap permission filtering — MIXED: generated Vite manifest tracking already corrected; bootstrap endpoint permission gate already covered; password-policy expansion and per-permission filtering deferred absent a demonstrated V1 failure; test-only branch accepted as test isolation.
5. Rental asset date allocation — NEW / REPRODUCTION REQUIRED. Source shows global status != available rejection before date-overlap evaluation, blocking a later non-overlapping booking while prior allocation remains active. Add a focused regression reproduction before any application fix.
6. Multi-day events, start/end/setup/collection times, clash detection and staff scheduling — OWNER-DECISION / SCOPE; no implementation without explicit V1 authorization.
7. Invoice numbering (random INV-YYYYMM suffix; offline UUID suffix) vs serial expectation — OWNER-DECISION pending South African tax-adviser guidance; preserve current numbering until confirmed.
8. Team invites/role assignment, email/PDF delivery, billing, app-wide licence enforcement, in-app legal terms/privacy, monitoring and scheduled jobs — OWNER-DECISION / SCOPE; no silent V1 expansion.

Documentation reconciliation: live HEAD and exact CI recorded; offline work ACTIVE/RESUMED; tier conflict remains OWNER-DECISION; root ROSCORE_PROJECT_MANIFEST.md is canonical and docs/ROSCOR_PROJECT_MANIFEST.md is a legacy pointer.

## Exact-head CI — ac795437
- Laravel run 44073375399: PASS — 520 passed, 1 skipped, 3,635 assertions; PostgreSQL migration/registration/backup/private-file recovery job passed; dedicated offline-browser job passed.
- Zazu quality run 44073375388: PASS — secret scan, npm audit/service-worker tests, Composer security audit.
- Push on main / CodeQL run 44073375925: PASS.
- SonarCloud run 44073375462: SKIPPED, not counted.
- No separate combined status contexts were reported. No Windows package workflow was triggered at this head; package evidence remains tied to its recorded source commit.

Evidence maturity remains 73/100 with no uplift. Release remains NOT READY pending physical Android offline/reconnect, installed-target recovery including private media, live populated-business financial reconciliation and POPIA operational sign-off.

## Next engineering target
Reproduce rental date-allocation behavior in a focused regression test. Preserve current behavior until the test proves the failure and correction is safely scoped. Invoice numbering remains unchanged pending tax-adviser advice.
