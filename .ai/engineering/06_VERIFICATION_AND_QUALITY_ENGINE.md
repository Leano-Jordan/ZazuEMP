# ZAZU EMP — VERIFICATION & QUALITY CONTROL ENGINE

## Purpose

Verification is not one thing called "tests". Zazu has multiple evidence systems. A failure must be classified before Builder changes application code.

## Mandatory model

BASELINE → TARGET → IMPLEMENT → CHECK SOURCE → RUN RELEVANT EVIDENCE → CLASSIFY FAILURE → CORRECT RIGHT LAYER → RE-RUN → ADVERSARIAL CHECK → ACCEPT / BLOCK → RECORD

## Evidence layers

| Layer | Examples | Proves |
|---|---|---|
| Source integrity | PHP, routes, Blade, JS, CSS | Written repository is structurally valid |
| Unit | PHPUnit Unit | Deterministic rules |
| Feature/integration | PHPUnit Feature | Laravel workflow/database behaviour |
| Browser | Playwright | Rendered user journey and browser behaviour |
| UI/accessibility | PHP UI contracts + Playwright | Semantic, visual and accessibility contracts |
| Static quality | Pint, PHPMD, Psalm | Maintainability/static/security signals |
| CI/workflow | GitHub Actions YAML + actual runs | Automated environment and gate integrity |
| Dependency | composer.lock, package-lock, action versions | Reproducible tooling state |
| Runtime/manual | local Laravel/browser/database | Real environment behaviour |
| Recovery | backup/restore/upgrade | Recoverability and deployment safety |

No layer silently substitutes for another.

## Failure classification

### F1 — Application defect
Valid check; current product behaviour violates the intended contract.
Action: Builder fixes application code. Keep or strengthen the regression test.

### F2 — Test defect
Application contract is correct; test asserts stale, impossible or incorrect behaviour.
Action: fix the test and record why it was wrong. Never weaken application code to satisfy a bad test.

### F3 — Fixture/data defect
Test is valid but setup violates a current domain prerequisite.
Action: repair fixture/factory/seeder.

### F4 — Test-environment defect
Database, environment, browser setup, generated asset, PHP extension or local runtime prevents a valid test from running.
Action: repair environment or record evidence boundary. Do not alter application behaviour to hide it.

### F5 — CI/workflow defect
YAML, action configuration, permissions, trigger, cache, tool installation, version pin or command is wrong.
Action: repair .github/workflows or tool configuration.

### F6 — Tooling/static-analysis defect
Scanner is misconfigured, incompatible with the framework/version, or missing required configuration.
Action: repair tool configuration/version boundary. Scanner failure is not automatically an application vulnerability.

### F7 — Expected-contract drift
Implementation and test may both be internally consistent, but current Zazu authority changed.
Action: resolve against current Zazu authority, then update implementation/tests/docs together.

### F8 — Flaky/non-deterministic failure
A valid check changes outcome without meaningful code/environment change.
Action: Forensics. Do not rerun until green and call that a fix.

## Test correction rule

When a check fails:
1. Reproduce the narrowest failure.
2. Read the assertion.
3. Read fixture/setup.
4. Trace the production path.
5. Compare against current Zazu authority.
6. Classify F1–F8.
7. Correct only the responsible layer.
8. Re-run the narrow check.
9. Re-run its regression family.
10. Run the broader relevant suite before acceptance.

Critical rule: never change application code solely because a test failed.

## Code-review tests

Tests added by the owner or during code review are first-class evidence.

They must be:
- inventoried;
- mapped to the behaviour/invariant they protect;
- checked for valid assumptions;
- retained when they expose a real regression;
- corrected when they encode stale/incorrect behaviour;
- included in the relevant regression family.

An owner-added test is not "random" merely because it was created during review.

## GitHub Actions / YAML inspection

When verification or release evidence matters, inspect .github/workflows/*.yml and check:
- trigger scope;
- main-branch policy;
- pull-request behaviour;
- workflow permissions;
- action versions/pins;
- PHP/Node/tool versions;
- dependency installation;
- database setup/migrations;
- asset build;
- test commands;
- browser installation;
- artifacts/logs where useful;
- static-analysis configuration;
- secrets/variables;
- failure handling;
- whether the workflow actually ran for the target commit.

A workflow file existing is not evidence that its workflow passed.

## Current Zazu inventory

- Laravel/PHPUnit: .github/workflows/laravel.yml
- Browser: .github/workflows/zazu-browser.yml
- PHPMD: .github/workflows/phpmd.yml
- Psalm: .github/workflows/psalm.yml
- SonarCloud: .github/workflows/sonarcloud.yml
- Playwright: playwright.config.js
- PHPUnit: phpunit.xml
- Composer: composer.json
- Frontend: package.json

Current observations:
1. PHPUnit is an active application verification layer.
2. Playwright covers rendered browser behaviour and desktop/mobile/tablet projects.
3. PHPMD is a separate static-quality gate.
4. Psalm is a separate security/static-analysis gate and has a Laravel compatibility shim; its failure must not be misclassified as an application defect.
5. SonarCloud is conditional on repository variables and is not automatically active release evidence.
6. Pint is installed but is not currently an explicit CI quality gate.
7. Current connected GitHub inspection has not exposed usable workflow-run evidence for the latest main commits; this is UNVERIFIED until actual run evidence exists.

## Anti-loop

If the same failure survives two correction attempts:
STOP PATCHING → FORENSICS

Forensics must establish:
symptom → failing layer → execution path → root cause → correction → proof.

## Completion record

Every verification result records:
- target;
- checks executed;
- result;
- failure classification;
- correction layer;
- evidence;
- remaining uncertainty;
- regression disposition;
- next target.
