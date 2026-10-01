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


## Director V2 — failure-case control

A repeated verification failure is governed by the Failure Case Engine.

Before another application correction:
1. identify or create the persistent failure case;
2. fingerprint the failure using stable observable facts;
3. load previous hypotheses, experiments and rejected approaches;
4. confirm whether materially new evidence exists;
5. classify the failure F1–F8;
6. choose the next diagnostic layer;
7. authorize correction only when evidence supports it.

### Attempt budget

- Maximum 2 correction attempts per hypothesis.
- Maximum 3 no-progress cycles per failure case.
- Exceeding either threshold triggers STOP PATCHING → FORENSICS / ESCALATION.

### Escalation ladder

1. reproduction
2. assertion/test contract
3. application path
4. fixture/data state
5. session/auth/cache/filesystem/runtime
6. browser/real workflow
7. instrumentation
8. architecture/design boundary
9. BLOCKED / owner decision

A green rerun without materially new evidence does not close a repeated or flaky case.

Persistent case history is stored in .ai/engineering/FAILURE_CASES.md.


## RENDERED HUMAN-VISUAL EVIDENCE

Human visual quality is a verification layer for UI-affecting work.

The UI/UX engine owns the human-eye critique. Verification owns the evidence discipline.

For meaningful rendered UI changes, verify:

- desktop composition;
- mobile composition;
- relevant intermediate responsive states;
- light theme;
- dark theme where supported;
- empty/populated/error states where affected;
- overflow/clipping;
- field sizing;
- table width and horizontal scanning burden;
- colour relationships and semantic state visibility;
- typography hierarchy;
- alignment and spacing;
- navigation and primary-action prominence.

A passing browser test proves behaviour in the exercised journey. It does not by itself prove that the resulting composition is clear, balanced or commercially polished.

### Visual finding classification

Use the UI/UX engine's finding classes:

- V1 — visual defect;
- V2 — UX friction;
- V3 — visual inconsistency;
- V4 — visual quality weakness;
- V5 — creative opportunity;
- V6 — intentional/acceptable.

V1–V4 require consideration as defects/risks. V5 is an improvement opportunity and is not automatically a release blocker. V6 is recorded only when useful and does not create work.

### Visual evidence vs test failure

A human-eye observation is not automatically F1–F8.

Classify the underlying engineering failure only when a verification check actually fails.

Examples:

- screenshot shows clipping caused by application CSS → F1;
- screenshot expectation is stale while application contract is correct → F2;
- abnormal fixture/data creates an unintended visual state → F3;
- browser environment cannot render the target → F4;
- CI visual/browser workflow is misconfigured → F5;
- visual scanner/tool configuration is defective → F6;
- current design authority changed → F7;
- inconsistent visual result without meaningful source/environment change → investigate F8.

### Width and eye-tracking verification

For tables and forms, verification should explicitly challenge avoidable horizontal scanning.

Do not use "fits on desktop" as the acceptance criterion.

Check whether:

- important columns remain visible;
- routine actions are close to the data they affect;
- long fields are bounded appropriately;
- secondary information can move to detail/inspector views;
- mobile does not simply become a compressed desktop;
- horizontal overflow is necessary rather than accidental.

### Colour verification

For meaningful theme or colour changes verify:

- semantic role consistency;
- foreground/background contrast;
- focus visibility;
- status distinction;
- selected/active states;
- disabled states;
- light/dark mapping;
- no accidental token drift.

The source token system must remain singular. Repeated root-token blocks or page-specific competing palettes are a verification concern.

### Synchronization requirement

Verification returns evidence to Director rather than maintaining a separate decision state.

A UI cycle is complete only when the Director has:

1. current source state;
2. implementation result;
3. automated verification result;
4. rendered visual evidence where required;
5. Guardian/regression disposition;
6. remaining uncertainty;
7. next target.

A green automated suite with missing required rendered evidence is:

**AUTOMATED PASS / VISUAL UNVERIFIED**

not a complete UI acceptance.


## RENDERED HUMAN-VISUAL EVIDENCE

Human visual quality is a verification layer for UI-affecting work.

UI/UX owns the human-eye critique. Verification owns evidence discipline.

For meaningful rendered changes, verify where applicable:
- desktop and mobile composition;
- relevant intermediate responsive states;
- light/dark themes;
- empty/populated/error states;
- overflow and clipping;
- field sizing;
- table width and horizontal scanning burden;
- colour relationships and semantic state visibility;
- typography hierarchy;
- alignment and spacing;
- navigation and primary-action prominence.

A passing browser journey proves exercised behaviour. It does not by itself prove clear, balanced or commercially polished composition.

### Visual finding classes

- V1 — visual defect
- V2 — UX friction
- V3 — visual inconsistency
- V4 — visual quality weakness
- V5 — creative opportunity
- V6 — intentional/acceptable

V5 is not automatically a release blocker.

### Width and eye-tracking verification

Do not use "fits on desktop" as the acceptance criterion.

Challenge whether:
- important table columns remain visible;
- routine actions stay near the data they affect;
- long fields are bounded appropriately;
- secondary detail can move to an inspector/detail surface;
- mobile avoids becoming a compressed desktop;
- horizontal overflow is necessary rather than accidental.

### Colour verification

For meaningful theme/colour changes verify:
- semantic role consistency;
- foreground/background contrast;
- focus visibility;
- status distinction;
- selected/active states;
- disabled states;
- light/dark mapping;
- absence of competing token authorities.

### Synchronization

Verification returns evidence to Director. It does not maintain a competing decision state.

For UI work, the acceptance state must distinguish:
**AUTOMATED PASS / VISUAL UNVERIFIED**
from
**VISUALLY VERIFIED**.

A green automated suite with missing required rendered evidence does not close the UI target.
