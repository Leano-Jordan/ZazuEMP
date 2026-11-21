# Zazu EMP — Living Project Context

## Rosscore Labs Founder and Sentinel authority — binding

- The sole Founder, owner and final authority is the user. Address him directly as **you** or **Rosco** when appropriate; never describe him as a hypothetical, replaceable, future or generic founder. The company identity is **Rosscore Labs**. Do not imply ownership or authority may transfer to another person.
- Your `/admin/sentinel` Founder Control Centre is your installation-wide oversight dashboard for monitoring every Zazu instance and platform health. It must provide useful fleet/instance visibility without exposing unnecessary customer or business-sensitive records.
- Sentinel Rescue (`/sentinel/rescue`) is to be merged into the existing business Owner Admin experience as its health, diagnostics and safe-recovery capability. Do not preserve it as a separate owner destination/page that fragments navigation.
- Keep scopes distinct: Rosscore Labs Founder Control Centre = cross-instance/platform oversight; business Owner Admin + integrated Sentinel Rescue = that authorised business's own health and diagnostics. Staff receive neither surface unless explicitly authorised in a future approved permission design.
- Use one coherent Sentinel monitoring/diagnostics capability with server-enforced scope boundaries. Never expose the installation-wide incident ledger or another business's data through Owner Admin.
- Navigation must make each authorised destination discoverable without requiring manual URL entry. Access denial remains server-enforced; hiding links is not a security control. Preserve a clear route back to the business workspace from Founder Control Centre and Owner Admin.
- These are product and continuity decisions, not permission to change application code. Implementation still follows the current owner-issued scope and main-only repository policy.


Product: Zazu – Event Management Platform (Zazu EMP)
Repository: Leano-Jordan/ZazuEMP
Canonical branch: main only
Context updated: 2026-11-21

## Owner V1 shipping mandate — binding
Primary objective: get a working Zazu V1 into the hands of the first real businesses. V1 is a practical first release, not an enterprise-scale perfection exercise.
- Build agreed V1 workflows; do not optimize for hypothetical scale before first-customer usability.
- When acceptance criteria pass and are evidenced, award 5/5 and close the gate.
- Only reproducible agreed-workflow defects, demonstrated security/privacy/data-integrity failures, or explicit owner-approved requirements may hold release.
- Do not reopen closed gates without new reproducible evidence or repeat unchanged investigations.
- Work in meaningful batches; verify once after the batch and move forward.
- Offline V1 engineering is RESUMED. Bounded service-worker, IndexedDB, local attachments, sync, PWA installation and recovery work is authorized where it closes agreed V1 gaps.
- Work directly on main. Never create branches. Do not introduce Render or AMPPS deployment workflows.
- Never claim unobserved runtime, CI, browser or release evidence.


## Founder commercial decision — 2026-11-21
Zazu EMP has exactly three commercial tiers: **Basic (permanent Free), Intermediate (paid), Advanced (paid)**. These are also the canonical experience/entitlement names. The Founder approved the tier structure only; exact paid monthly prices, feature matrix and quotas remain unapproved. The old Starter/Team/Business and R499/R899/R1,499 proposal is withdrawn and must not be represented as current pricing. See DEC-027 in .ai/engineering/DECISION_LOG.md and the reconciled product specification/valuation.

## Product direction
Zazu is a reusable commercial event-management platform for catering, event equipment hire, sound/DJ, baking, decor, rentals, photography/camera hire and related service businesses. Core flow: Business → Customer → Event/Job → Requirements → Quote → Confirmation/Deposit → Purchasing/Preparation → Execution → Payments/Completion.

## Engineering invariants
- Offline-first: agreed core workflows remain useful through ordinary internet loss.
- Business isolation and server-side authorization are mandatory.
- Preserve existing architecture and working data; prefer small evidence-backed corrections.
- Tests are evidence, not the progress metric; never weaken tests to get green.
- No deployment or production data changes without explicit authority.
- Primary currency is South African rand (ZAR / R); show rand first in commercial analysis.

## Current verified position

Latest code/operations verification head: 365609b4d30ca4978ba9480cf3571bb792ad442b on main. Latest application/test source head: d6c1d577b6ed0bd18bef5d1c869ccc00782b35cb; latest Windows host-operations source: 365609b4d30ca4978ba9480cf3571bb792ad442b.
Exact-head Laravel: **643 passed / 1 skipped / 4,424 assertions**. PostgreSQL migration/recovery smoke passed. Offline browser: **59 desktop Chromium + 7 mobile-viewport + 1 phone-local Work/attachment/reconnect passed**. PHPMD, Psalm, Zazu Quality and populated upgrade/rollback passed; SonarCloud was skipped. Windows package/fresh-install recovery passed on run 44888885245; Windows Ops PowerShell/contracts passed on run 44888885270; Zazu Quality passed on run 44888885301. See .ai/engineering/STATE.md and docs/V1_RELEASE_CHECKLIST.md for workflow evidence.

**Operational reliability work implemented:** daily 02:00 owner-PC backup task with 14-archive retention, startup and Founder Control Centre backup-age warnings, protected local backup directory, and a documented off-disk USB/cloud copy step. The actual PC task execution, off-disk copy and controlled restore drill remain OPEN.
**Device attribution implemented:** owner approver is recorded on newly paired/re-registered sync devices and used as actor in sync audit entries. This does not add per-staff authority to a shared trusted device; that limitation and warnings are explicit.
Also implemented: query-builder mass-write guard for revision-tracked tables, daily log rotation on existing PC installs with stale Laravel config-cache invalidation at startup, 10-character minimum for new/reset passwords, and explicit iOS offline limits.

Offline capability remains bounded: IndexedDB workspace and durable mutation queue; local customer and Work directories/inspection; dashboard quote detail/acceptance panel; phone-local Work/attachment journey. Cached Laravel HTML is a snapshot, not a general local route renderer. Do not claim app-wide offline parity.
Sync protections include business-scoped stable record identity, mutation replay checks, ordered delivery, atomic apply/publication, retryable infrastructure handling, contiguous pull/acknowledgement and revision-aware stale-write rejection.
**Remaining release gates:** owner-PC scheduled backup observation, off-disk copy and restore drill; physical Android cold launch/restart/reconnect; populated quote-to-payment-to-profit reconciliation; POPIA operational sign-off; ZAZU name clearance. PostgreSQL commercial/sync suite is conditional on selecting a PostgreSQL host; current owner-PC V1 uses SQLite. DB-001 remains dormant/monitoring by Founder report. Pairing remains closed.
Evidence maturity remains **77/100**. V1 remains **NOT READY**; do not award score uplift without owner-side operational evidence.
Current state: .ai/engineering/STATE.md. Sole readiness register: .ai/engineering/READINESS_REGISTER.md. Release acceptance: docs/V1_RELEASE_CHECKLIST.md.
