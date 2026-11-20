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
Context updated: 2026-11-20

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
Current repository head: `8090c9633f3309c1c4323da352942ccc44f64ef6` on `main`. This head and the preceding `b019831`, `33e1f1d`, and `cbd7142` changes are documentation-only; they do not change application code.
Latest full application-code verification baseline: `526bdf38dab6ff540c143b0e57dfcee468006dc3`. Evidence at that exact baseline: Laravel 607 passed / 1 skipped / 4,158 assertions; PostgreSQL migration, registration, backup/restore and private-file recovery passed; offline browser 51 desktop + 6 mobile-viewport + 1 phone-local Work/attachment journey passed. PHPMD, Psalm, Windows V1 package and populated upgrade/rollback passed on application-equivalent parent `30e70e929dc848c0119c8452395ad225a23b7c47`. These are historical exact-head results, not a fresh run on `8090c96`.
Latest completed repository workflows observed on documentation head `91f41ec2c0a389ce92324a6a3d3a7d0a01492e0d`: Rosscore Director Health, Zazu Quality, Director Contract Lint and Push-on-main passed. SonarCloud was skipped. Do not attribute those results to later `8090c96` without a new run.
Offline capability remains bounded: IndexedDB workspace and durable mutation queue; local customer and Work directories/inspection; dashboard quote detail/acceptance panel; phone-local Work/attachment journey. Cached Laravel HTML is a snapshot, not a general local route renderer. Do not claim app-wide offline parity.
Sync protections include business-scoped stable record identity, mutation replay checks, ordered delivery, atomic apply/publication, retryable infrastructure handling and contiguous pull/acknowledgement. The pending queue is ordered by creation time; this is not a confirmed defect.
**OPEN F1 data-integrity defect:** existing-record offline updates have no authoritative base revision. The server resolves the latest record and applies the offline payload without detecting that another device or online user changed it after bootstrap. The existing conflict path does not preserve a stale local payload alongside a remote snapshot or provide an explicit safe rebase. CASE-ZAZU-0033 / ENG-DATA-003 records the failure and required contract. The scope includes customer, Work/job, preparation, quote/draft version, inventory item, event cost, asset, supplier, service/capability and purchase order update-capable handlers; nested customer contacts and Work requirements are part of their parent aggregate.
**Active target:** implement server-enforced monotonic revisions across every relevant online and offline writer, same-device causal edit chaining, and loss-preserving stale conflict/rebase. Do not use timestamps, browser clocks, last-write-wins or mutation-ID reuse. Keep payment, receipt, stock movement and asset-allocation idempotency/invariants intact. Full sync-matrix acceptance remains separate and OPEN.
Other release gates remain OPEN: actual Android standalone offline cold launch/restart/reconnect; installed-PC DB-001 diagnosis and recovery drill; populated commercial reconciliation; POPIA operational sign-off; ZAZU name clearance. PC-to-phone pairing is CLOSED. V1 remains NOT READY; evidence maturity remains 73/100 with no uplift.
Current state: `.ai/engineering/STATE.md`. Sole readiness register: `.ai/engineering/READINESS_REGISTER.md`. Release acceptance: `docs/V1_RELEASE_CHECKLIST.md`.
Historical context is preserved in `docs/archive/engineering/2026-11-17-state-register-memory-history.md`.