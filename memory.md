# Zazu EMP — Living Project Context

Product: Zazu – Event Management Platform (Zazu EMP)
Repository: Leano-Jordan/ZazuEMP
Canonical branch: main only
Context updated: 2026-11-17

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
Last verified engineering source baseline: fd82f6a5fb59365687fac903f93e944394145427. Application-code test baseline: a5ead696429867bcd57342406bb37662ca1b2f2c (the only later change is the Windows package workflow).
Application-code CI: Laravel 526 passed, 1 skipped, 3,681 assertions; desktop/mobile offline browser acceptance and phone-local attachment journey passed; PostgreSQL migration and recovery passed.
Windows V1 Package run 82 at fd82f6a: candidate build, Windows PowerShell fresh install and Windows-native SQLite + private-media backup/restore passed. Director Health, Contract Lint and Push-on-main passed.
LAN asset delivery is covered by ViteAssetDeliveryTest at application baseline a5ead69: remote clients receive compiled same-origin assets despite stale loopback Vite hot markers. This fix post-dates the supplied 13 Nov HTTP screenshot; physical Android use must still be verified from the documented HTTPS origin.
Zazu Quality is currently red at npm audit because GHSA-fc5q-39xc-vjpf / CVE-2026-112366 flags the locked picomatch development/build dependency chain; GitHub currently lists no patched version. The finding is not suppressed or force-downgraded.
V1 remains NOT READY; evidence maturity remains 73/100 with no uplift. Current acceptance gates: physical Android offline/reconnect; intended installed-target recovery; live populated-business financial reconciliation; POPIA operational sign-off.
Current state: .ai/engineering/STATE.md. Sole readiness register: .ai/engineering/READINESS_REGISTER.md. Release acceptance: docs/V1_RELEASE_CHECKLIST.md.
Historical context is preserved in docs/archive/engineering/2026-11-17-state-register-memory-history.md.
Historical context is preserved in docs/archive/engineering/2026-11-17-state-register-memory-history.md.
