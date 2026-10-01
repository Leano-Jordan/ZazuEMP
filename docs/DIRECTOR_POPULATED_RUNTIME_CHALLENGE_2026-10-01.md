# Zazu EMP — Director Populated Runtime Challenge
## 2026-10-01

Branch: main

### Objective
Move the populated business walkthrough out of PHPUnit-only evidence and into a real browser/runtime execution path.

### Runtime boundary
The owner's local Laravel runtime was not reachable from this Director execution:
- no deployed Zazu URL is recorded in the repository;
- the available live-browser connector could not execute because its account has insufficient browser credits;
- no local repository/runtime is mounted in this execution environment.

Therefore no local rendered result is claimed.

### Director action
The existing GitHub browser workflow previously migrated a clean SQLite database but did not seed the populated demo. That meant the browser suite could pass while avoiding the populated business state.

Implemented:
- .github/workflows/zazu-browser.yml now seeds DemoScenarioSeeder after migration;
- e2e/populated-runtime.spec.js now exercises the seeded owner workflow and role boundaries.

### Populated challenge
The browser challenge follows:

Dashboard → Work → Job inspector → Requirements → Preparation → Purchasing → Inventory → Assets → Finance → Invoice/Payment history → Reports

It asserts live populated records including:
- Mokoena Family Celebration;
- ZAZU-DEMO-001;
- 80 guests;
- Buffet catering and preparation items;
- accepted commercial value ZAR 11,500.00;
- PO-ZAZU-DEMO-001 received and not receivable again;
- PO-ZAZU-DEMO-002 excluded from the event-scoped purchasing view;
- seeded inventory/assets;
- INV-ZAZU-DEMO-001 with payment history and zero balance;
- Reports rendering.

The test also records page-level JavaScript errors and any HTTP 5xx responses.

### Authorization challenge
The browser challenge additionally verifies:
- manager can enter operational surfaces;
- manager is denied owner-only Settings;
- manager is denied finance payment creation;
- finance staff can view Finance;
- finance staff is denied finance payment creation;
- finance staff is denied owner-only Settings.

The current source now contains an explicit manager permission mapping. The earlier AUTH-ROLE-001 source blocker is therefore closed; runtime certification is the remaining evidence gap.

### Current evidence
Implementation commits:
- 715a38a411606846c715fe38e3388276c613161 — populated runtime browser challenge
- 6b7c306349efd9852b60c5c14ccbf7f0aacbba34 — seed populated demo in browser workflow

Current main head observed after execution: 6b7c306349efd9852b60c5c14ccbf7f0aacbba34.

GitHub status inspection immediately after the commits returned no status entries and no workflow runs through the available connector paths. This is not interpreted as pass or fail.

### Director disposition
RUNTIME CHALLENGE IMPLEMENTED / EXECUTION RESULT UNOBSERVED

No release gate is marked green from this cycle until the populated browser run is actually observed.

Next target:
CONSUME THE POPULATED BROWSER RUN → classify every failure → fix only verified defects → rerun → then repeat on mobile/tablet and recovery/upgrade gates.