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


## Pre-launch landing page — 2026-10-12

- Added a standalone static marketing site under `prelaunch/`, separate from the Laravel application and PWA runtime.
- Added the GitHub Actions workflow `.github/workflows/deploy-prelaunch.yml` to publish only the `prelaunch/` directory to GitHub Pages from `main`.
- Page includes product positioning, conceptual (clearly labelled) workspace illustration, responsive feature sections, FAQ, early-access/demo enquiry form and a privacy notice.
- Form submission is configured through FormSubmit to the Rosscore Labs enquiry mailbox. Recipient activation and real delivery are **UNVERIFIED**; GitHub Pages does not store submissions.
- Static source integrity checks passed: expected files present, local asset references consistent, form consent/redirect/privacy links present, CSS/JS brace counts balanced, no remote image/font dependencies.
- GitHub Pages deployment, live browser rendering, mobile browser acceptance and end-to-end form delivery remain **UNVERIFIED** pending the Actions run, Pages settings and recipient activation.
- Expected project URL: `https://leano-jordan.github.io/ZazuEMP/`; confirm from the deployment output before public promotion.


## Pre-launch conversion copy and South African image refinement — 2026-10-13

- Revised public-facing early-access copy to lead with reduced chasing and a clearer view of the work behind each job, with a low-pressure choice between early access, a product demonstration, or both.
- Broadened copy to independent operators and growing teams across multiple South African event-related business types; avoided scarcity claims, invented social proof, and founder-support framing.
- Replaced six catering/venue-led photo references with responsive Wikimedia Commons image variants; added srcset/sizes, a public author/licence page, and privacy disclosure for the Wikimedia image host.
- Changed the pre-launch policy intentionally: image requests are remote but restricted by CSP and the deployment audit to HTTPS upload.wikimedia.org/wikipedia/commons/thumb/. This is not a self-hosted/offline image set.
- Verification remains pending: the changed deployment workflow must run on the new main HEAD, the deployed page must be checked in desktop and mobile browsers, and image loading/credits must be verified from the published artifact. FormSubmit delivery remains separately unverified.


## Landing-page reliability and first-impression refinement — 2026-10-13

- The SVG illustration substitution was unauthorised and has been fully removed from the landing page, media credits, privacy notice, source notes, and deployment workflow.
- Restored the six existing Wikimedia Commons photographic image references and responsive srcset variants, along with the required CSP host permission and original image-credit/privacy documentation.
- Removed all six newly added illustration assets from the repository. No AI-generated artwork is approved for this page.
- Kept the clearer hero headline and early-access/product-demo calls to action.
- Remaining acceptance: diagnose why the original photos did not display on the live GitHub Pages deployment, verify the current Actions deployment, then inspect image loading on desktop and mobile. Restoring source references does not prove live image delivery.

## Director cycle — 2026-10-15 current-main reconciliation

- Canonical branch: `main`; current assessed HEAD before this state-record update: `7940033b6fd4f691692eec5e9d7f6ad587486f61` (offline reliability/browser regression hardening).
- Current-main push workflows at that SHA: Laravel PASS (run 38789104896), Zazu quality PASS (38789104893), PHPMD PASS (38789105223), Psalm Security Scan PASS (38789105085), Zazu browser smoke PASS (38789104865), populated upgrade/rollback PASS (38789104911). SonarCloud was SKIPPED by workflow condition (38789105277); do not describe it as a pass.
- The Director health workflow completed successfully for the target SHA (run 38789753467). The committed `docs/health/latest.md` is stale and still describes an unrelated older branch/commit; generated workflow evidence and committed health report are not reconciled. Treat this as a control-plane reporting defect, not an application test failure.
- The backup/restore workflow provides CI evidence for a populated upgrade/rollback round trip, including representative business, event, invoice/payment, private-media and migration-state checks. It does **not** satisfy the release runbook's installed-customer-environment recovery drill.
- Remaining next hard gate: run the documented controlled Windows installation backup/restore drill and capture environment, archive ID, representative record/media/security checks. This requires access to the installed environment; do not mark PROVEN from CI alone.
- Branch policy remains main-only. GitHub currently exposes the stale `director/fix-offline-job-browser-selector` ref in addition to `main`; the connected GitHub action set exposes no branch-delete operation. Do not create another branch or misreport cleanup as complete.

Evidence: https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38789104896 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38789104893 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38789105223 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38789105085 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38789104865 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38789104911 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38789753467

Last updated: 2026-10-15 — reconciled current-main CI and recovery proof boundary.

## Director cycle — 2026-10-15 MySQL restore rollback hardening

- Canonical branch: `main`; implementation/test head: `fb0278949e8971d32a65d1d36e074b0561159cab`.
- `app/Console/Commands/ZazuRestoreCommand.php` now captures a pre-restore MySQL dump before private-storage mutation, uses a shared MySQL import path, attempts database rollback after a failed candidate import, and preserves the rollback snapshot with explicit operator warning if automatic rollback also fails.
- `tests/Feature/ZazuBackupRestoreTest.php` now exercises a simulated partial MySQL import failure and asserts the pre-restore SQL is imported and original private storage is restored. Test passed in Laravel workflow run 38793762832.
- At the time of this record, the following checks passed for this head: Laravel 38793762832, Zazu quality 38793762950, PHPMD 38793762939, Psalm Security Scan 38793762878, populated upgrade/rollback 38793762891, Push on main 38793762946, and Director health 38793901676. SonarCloud 38793762938 was skipped. Browser smoke 38793762835 was still running and is not marked passed here.
- Release limitation unchanged: no actual installed Windows/customer-environment backup/restore drill has been recorded. CI is not a substitute.
- Branch policy unchanged: no new branch or PR was created. The stale feature ref remains visible alongside `main`; the connected GitHub actions still expose no branch-delete operation.

Evidence: https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38793762832 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38793762950 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38793762939 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38793762878 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38793762891 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38793762946 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38793762835

Last updated: 2026-10-15 — MySQL rollback behavior and regression evidence recorded.
