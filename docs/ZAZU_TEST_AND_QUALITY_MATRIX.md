# Zazu EMP — Test & Quality Matrix

## Purpose

This is the current map of Zazu's verification ecosystem. It prevents tests, static analysis, CI workflows and browser checks from being treated as one undifferentiated test suite.

| Area | Current mechanism | Gate role |
|---|---|---|
| Unit | PHPUnit tests/Unit | correctness |
| Feature/integration | PHPUnit tests/Feature | workflow/data/security |
| Browser UX | Playwright e2e/ | rendered workflow |
| UI/theme | e2e/ui-theme.spec.ts + PHP UI tests | visual/semantic regression |
| Accessibility | UiAccessibilityTest + Playwright assertions | accessibility |
| Static quality | PHPMD | maintainability |
| Static security | Psalm | security/static analysis |
| External analysis | SonarCloud | conditional |
| Formatting | Laravel Pint dependency | currently not a CI gate |
| Dependency drift | Dependabot | maintenance |
| Build | Vite/npm | asset integrity |
| Database | migrations + PHPUnit/browser setup | schema/workflow integrity |

## Failure ownership

- PHPUnit application failure → Builder/Guardian.
- PHPUnit assertion/fixture failure → test correction after contract check.
- Playwright selector/rendering failure → UI/UX + Builder after application-path inspection.
- PHPMD failure → code-quality correction; not automatically a product defect.
- Psalm failure → static/security forensics; not automatically an application vulnerability.
- GitHub Actions YAML failure → CI/workflow correction.
- Missing workflow run/status → evidence gap, not a green result.
- Local runtime failure → environment/runtime evidence boundary until reproduced.

## Owner-added code-review tests

Owner-added tests are part of the quality system. If they catch a real regression, they become permanent regression protection. If they encode stale behaviour, correct them with an explicit reason rather than silently deleting them.

## Current gaps

1. No single repository-side verification authority classified failures before application changes.
2. Static-analysis and CI failures can be mistaken for application failures.
3. Pint is installed but not an explicit CI gate.
4. SonarCloud is conditional and must not be treated as active evidence unless configured and successfully run.
5. Psalm has a compatibility shim and needs its own toolchain-health acceptance rule.
6. Latest main commits currently lack usable workflow-run evidence through the connected inspection path.
7. Critical business workflows are not yet mapped in one concise evidence matrix.
8. The visual stylesheet had accumulated repeated token/sweep layers; the token declarations have now been consolidated into one light and one dark root authority. Fresh browser verification remains required.

## Critical workflow evidence

Customer/import: validation, matching, duplicates, authorization, transaction/rollback, preview UI, browser rendering.

Job/requirements: ownership, lifecycle, requirements, preparation, purchasing context, browser workflow.

Quote: calculation, revision, stale-state handling, acceptance, finance conversion, rendered totals.

Purchasing/inventory: authorization, quantities/costs, receiving, stock mutation, rollback, event attribution, populated browser flow.

Finance: invoice, payment, reconciliation, currency-safe formatting, authorization, populated data.

Shared UI: theme, typography, contrast, responsive layout, navigation, form controls, accessibility, browser rendering.

## Acceptance

No check is silently ignored. If a check is intentionally non-blocking, record the reason and residual risk in release evidence.


## Director verification efficiency policy — 2026-11-13

Verification must be risk-based and must not repeat an already-proven check without a changed dependency or new failure evidence.

- Every application change gets the narrowest relevant automated test first.
- Run the complete PHPUnit suite once per commit in the Laravel workflow. Do not run it again in a second quality workflow.
- Keep the focused service-worker race tests as a separate Node test; do not rebuild PHP assets or rerun PHPUnit to execute them.
- Keep the dedicated offline-browser acceptance test on main because it verifies the PWA runtime contract that PHP tests cannot prove.
- Run the full rendered-browser suite for browser-relevant pull-request changes or manually at release acceptance. Do not run it automatically on every main push.
- Do not rerun browser smoke just to confirm static pages, buttons or navigation already covered by established evidence. Trigger it when the changed surface can affect rendered browser behaviour, or when diagnosing a specific browser defect.
- Do not rerun a green suite after a documentation-only commit. Reuse exact-head evidence only when the application/test inputs are unchanged; record the source SHA and evidence SHA explicitly.
- A retry is not a pass. Inspect the first failure, classify it (application, test/fixture, environment, CI, tooling, contract or flake), and rerun only the affected test/job after correction.
- Keep release-level browser checks and real installed-device acceptance separate. Browser automation cannot certify the owner's actual phone installation or physical disconnected workflow.

### Known pipeline duplication removed

- The Zazu quality application job previously repeated the complete PHP install/build/config/migration/PHPUnit pipeline already performed by Laravel. It now runs only the focused service-worker Node regression and secret scan.
- Zazu browser smoke no longer runs on every push to main. It runs for browser-relevant pull requests or an explicit release/diagnostic dispatch.
- The Laravel workflow retains the full PHP suite, PostgreSQL migration/recovery checks, and focused offline-browser acceptance on main.

### Deliberate remaining cost

The Laravel and dedicated offline-browser jobs still build separately because they execute in isolated GitHub-hosted jobs and the browser job needs a fully seeded runtime. Removing that build would require artifact handoff or job restructuring; do not add that complexity unless measured CI duration justifies it.
