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
