# Zazu EMP — V1 Readiness Register

Current repository HEAD: Leano-Jordan/ZazuEMP / main / fd82f6a5fb59365687fac903f93e944394145427 / 17 Nov 2026
Last application-code test baseline: a5ead696429867bcd57342406bb37662ca1b2f2c
Release disposition: NOT READY
Evidence maturity: 73/100 unchanged; no uplift claimed.

## Existing release gates
| Gate | Disposition | Closure evidence |
|---|---|---|
| Physical offline device | OPEN | Actual Android installed-app cold launch, disconnected restart, local continuity and reconnect |
| Installation recovery | OPEN — CI supporting proof added | Windows V1 Package run 82 on fd82f6a passes Windows PowerShell first install and Windows-native SQLite + private-media backup/mutate/restore; still requires the controlled drill on the intended installed target, with representative data, media, authorization, audit and migrations |
| Commercial reconciliation | OPEN | Populated real-business quote, invoice, payment, cost and profit reconciliation |
| POPIA operations | OPEN | Owner-approved privacy/retention, incident and operational controls |
| Windows package | CLOSED for defined fresh-install candidate | Windows V1 Package run 82 at fd82f6a: production candidate build, PowerShell first-install smoke and Windows-native SQLite/private-media recovery pass; not an updater or hosted deployment |

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

## Verified application-code baseline — a5ead69

- Offline push validates payment type/method and expense status against the connected-workflow allowlists; quote currency must be configured.
- Offline payment, expense and invoice dates reject malformed and impossible calendar dates.
- All-zero offline purchase receipts are rejected before a receipt record or idempotency key is consumed.
- Regression coverage includes these cases and float-decoded purchase-order quantity/price precision.
- Laravel: PASS — 526 passed / 1 skipped / 3,681 assertions.
- Offline browser: PASS — 45 desktop Chromium, 6 mobile-viewport and 1 phone-local attachment journey.
- PostgreSQL migration/registration/backup/private-file recovery: PASS.

## Latest repository verification — fd82f6a

- The only change after a5ead69 is the Windows package workflow; application code is unchanged.
- Windows V1 Package run 82: PASS — production candidate, Windows PowerShell fresh install and Windows-native backup/restore of a disposable SQLite record plus private media.
- Director Health, Director Contract Lint and Push-on-main: PASS.
- Composer security audit and secret scan: PASS.
- Zazu Quality: FAIL at `npm audit --audit-level=high` due GHSA-fc5q-39xc-vjpf / CVE-2026-112366 in the locked picomatch build-tool dependency chain. GitHub's advisory currently lists affected versions through 4.0.7 and no patched version. The npm dependencies are development/build tools and are excluded from the Windows runtime candidate, but the quality workflow remains red. No force downgrade or advisory suppression applied.
- Windows CI recovery is supporting evidence, not a replacement for the owner-run recovery drill on the intended installation.
- No score uplift; release remains NOT READY.
## Documentation and commercial decisions
- Offline work is ACTIVE/RESUMED; stale pause notes are historical and archived.
- Tier conflict remains OWNER-DECISION: product-specification/operating-profile describe three tiers (Basic Free, Intermediate, Advanced), while commercial requirements specify four paid prices (R299, R499, R799, R1,299). No tier or price decision inferred.
- Root ROSCORE_PROJECT_MANIFEST.md is canonical; docs/ROSCOR_PROJECT_MANIFEST.md is a legacy pointer.
- Prior long-form state/register/memory, checklist and changelog are preserved in docs/archive/engineering/2026-11-17-state-register-memory-history.md.

## Earlier exact-head CI — e5dc263 (historical baseline)
- Laravel: PASS — 523 passed, 1 skipped, 3,662 assertions.
- Offline browser: PASS — 45 desktop, 6 mobile, 1 phone-local attachment.
- PostgreSQL migration/registration/backup/private-file recovery: PASS.
- Windows V1 package + PowerShell fresh install: PASS.
- Populated upgrade/rollback, Zazu quality, Psalm, PHPMD, Director Health and Push-on-main: PASS.
- SonarCloud: SKIPPED.
- Evidence maturity remains 73/100. No score uplift.

## Next
Close the physical Android device, intended-installation recovery, live financial reconciliation and POPIA operational gates. Resolve the unpatched frontend build-tool advisory when a verified upstream fix becomes available. Keep all owner-decision scope items unchanged until approved.
