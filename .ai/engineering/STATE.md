# ZAZU EMP — ENGINEERING CONTROL STATE

## Identity
- Product: Zazu – Event Management Platform
- Repository: Leano-Jordan/ZazuEMP
- Canonical branch: main
- Master control: Morpheus / Director
- Owner-facing nickname: Jarvis

## Authority
1. Explicit owner instruction
2. Current repository state
3. Current living documentation / ledgers
4. Verified runtime / CI evidence
5. Verified external research
6. Historical records / chat memory

## Canonical control contract
`.ai/engineering/00_DIRECTOR_ENGINE.md` is the single canonical Director contract.
`.ai/engineering/00_ENGINE_ROUTER.md` is a compatibility pointer only.
Entry files must point to the canonical contract and must not define a competing read order.

## Control loop
IDENTITY → BASELINE → TARGET → ROUTE → INSPECT → CHANGE → VERIFY → BREAK → RECONCILE → ACCEPT/REPAIR → RECORD → NEXT

## Active target
**Product hardening: offline operational depth → reconciliation → workflow integrity → final certification later**

Verified gates:
- Previously verified browser, quality and populated upgrade/rollback suites remain green at their assessed heads.
- Populated commercial financial chain automated coverage remains green.
- Current main after the offline parity hardening cycle is **not yet runtime-verified**; GitHub Actions for the current head are queued.

Next hard gate: **reconcile current-head regression/CI evidence, then customer-environment backup/restore proof**.

Physical device acceptance is now owner-verified: Zazu installed on PC and mobile; screen rotation and presentation reported in order.

## Open / blocked cases

## Evidence rules
- IMPLEMENTED = repository change exists.
- TESTED = relevant automated check actually ran and passed.
- VERIFIED = intended behaviour has sufficient evidence.
- PROVEN = repeated realistic/production/recovery evidence exists.
- UNVERIFIED = required evidence unavailable.
- BLOCKED = safe progress materially prevented.
- A green narrow check never closes a broader behaviour gate.
- No case is CLOSED without its required evidence.

## Current CI boundary
The earlier browser run for `0a10d05` is not evidence for this final control-plane head. Current-head CI must be observed after this cycle. Record the exact run URL and SHA before closing any pending case.

## Application gates after browser convergence

1. Complete the populated commercial workflow traversal.
2. Execute the real backup/restore drill, including representative private media.
3. Execute the populated upgrade/rollback drill.
4. Complete physical phone/tablet acceptance.
5. Complete legal/privacy operational controls.
6. Complete dependency/licence notice closure.
7. Complete Zazu brand/trade-mark clearance.
8. Final Director re-audit against the selected release head.

**Current execution priority:** execute and reconcile the new offline inventory/cost/asset regression coverage; then close customer-environment recovery and rollback proof.

## Director cycle update — 2026-10-07

Offline operational depth expanded in current `main` through:
- inventory item + inventory movement handlers with business scoping, negative-stock protection and mutation idempotency;
- operational event-cost create/update handling with financial-state validation;
- asset create/update and allocation/return handling with explicit state-transition protection;
- direct and paired phone bootstrap coverage for inventory, assets and costs;
- phone-local UI actions for inventory, assets and costs;
- focused regression coverage for the new offline boundaries.

Current evidence status: **IMPLEMENTED; runtime TESTED/VERIFIED pending execution of the new regression set.** No release score uplift is claimed from source changes alone.

Last updated: 2026-10-07 — offline operational depth and parity hardened; current release gates remain current-head verification, recovery, rollback, legal/privacy and brand closure.

## PHPUnit / offline parity defect cycle — 2026-10-07

- Root cause of PHPUnit exit 255: malformed `catch (\\RuntimeException ...)` syntax in `tests/Feature/OfflineSyncFoundationTest.php`.
- Adjacent malformed test marker introduced during test-contract editing was also removed before validation.
- Offline local reconciliation was hardened for payments and purchase receipts so local invoice/payment and inventory state reflects accepted offline actions immediately.
- Targeted PWA regression assertions now cover those reconciliation boundaries.
- Current head CI is queued; runtime closure remains pending fresh Laravel/PHPMD/browser results.


## Director health-report hardening — 2026-10-08

- Post-commit Director health reporting is now persistent rather than artifact-only.
- The health workflow writes `docs/health/latest.json` and `docs/health/latest.md` for the latest source commit and retains the downloadable artifact.
- Report generation now includes current engineering-state signals alongside repository baseline signals.
- The workflow ignores `docs/health/**` push events to prevent report commits from recursively triggering the health workflow.
- Current implementation commit: `614f15ac4c0c7884e7af2194733a1ce0e6429657`.

## Current-head CI reconciliation automation — 2026-10-08

- Director health reporting now observes the actual Laravel and Zazu browser smoke workflow results for the target commit.
- The health workflow runs on completion of those required workflows as well as on source pushes/manual dispatch.
- Reports distinguish PASS, FAIL and PENDING current-head CI state and retain the workflow run URLs.
- Workflow-run-triggered reports serialize per target SHA to prevent competing report commits.
- Implementation commit: `af0f4158160488873bbaf072de5db75ccdc10192`.


## Director deep software audit — 2026-10-08

Jarvis completed a deep repository audit against docs/product-specification.md and the V1 capability map.

Confirmed defects corrected in this cycle:
- authenticated PWA page-cache lifecycle race;
- Director health report non-fast-forward publication race;
- concurrent offline pairing-code consumption race;
- concurrent offline mutation replay race;
- rejected offline mutation permanently blocking later sync pulls.

Regression coverage was added for authenticated offline navigation, pairing/conflict behavior and rejected-mutation recovery. A newly added pairing regression initially failed CI because of a test namespace typo; this was corrected immediately in de2413d.

Current audited application HEAD: de2413d78f1efbb8a511fadd78375f1ceba025ce.

Conservative assessment:
- V1 implementation maturity: approximately 87%.
- Commercial software readiness: approximately 72%.
- Current release is not certified.

Release-critical gates remaining:
1. current-head CI/browser/security convergence;
2. real offline installed-device proof and restart/reconciliation;
3. populated commercial workflow certification;
4. real backup/restore including representative private media;
5. populated upgrade/rollback drill;
6. final authorization/security challenge;
7. production hardening and operational runbooks;
8. offline licensing activation/signing/enforcement where required for the commercial model;
9. final Director release audit.

Explicit exclusion from these scores: logo, artwork, photography, branding, trademark and other human-controlled creative/legal tasks.

Audit record: docs/DIRECTOR_DEEP_AUDIT_2026-10-08.md.


## 2026-10-08 — Jarvis hardening cycle

- Latest verified main failure fingerprints were isolated to three owning layers: an offline pairing regression namespace typo, a browser test targeting a covered radio input, and the Director health publisher attempting to push directly to protected `main`.
- Corrected the regression namespace to fully-qualified `\\Illuminate\\Validation\\ValidationException`.
- Corrected the offline job journey to click the visible job-type card rather than the covered radio input, without hardcoding a specific job type.
- Moved persistent Director health snapshots to the dedicated `director-health` branch and serialized publication; the patched health workflow completed successfully and persisted `docs/health/latest.json` and `docs/health/latest.md`.
- CodeQL validation for PR #29 completed successfully.
- Evidence boundary: patched application Laravel / quality / browser runtime results have not yet been observed on the patched head, so no release-evidence uplift is claimed from these source/test fixes alone.
- Active PR: #29, Jarvis hardening.
- Next hard gate: obtain fresh Laravel + quality + browser evidence on the patched head, then reconcile release readiness and continue populated commercial/recovery certification.

## 2026-10-09 — Offline job submission initialization defect

- Latest browser smoke failed in all three Chromium profiles because the expected offline-save confirmation never appeared after submitting a new job while disconnected.
- Root cause identified in `resources/js/app.js`: `setupZazuOfflineForms()` was invoked immediately during script evaluation, before the DOM-ready UI initialization. If the script runs before the job form exists, the form receives no offline submit handler; the browser then follows the ordinary POST path while offline.
- Fixed by moving `setupZazuOfflineForms()` into `initializeZazuUi()`, which runs on `DOMContentLoaded` when required, and removing the premature standalone call.
- Fix commit: `621dd8c8b791c21870c0c3f5a07317449429038a`.
- Verification boundary: fresh CI for this fix is queued; offline persistence and desktop/mobile/tablet browser regression are not yet confirmed. Do not claim release readiness or score uplift until current-head checks settle.
