# ZAZU EMP — ENGINEERING CONTROL STATE

## Current identity

- Product: Zazu – Event Management Platform
- Repository: `Leano-Jordan/ZazuEMP`
- Canonical branch: `main`
- Master ENGINE: Morpheus
- Owner-facing nickname: Jarvis

## Current baseline

Control-system redesign began from main HEAD:
`ff28e74723e6de8c75805db325f8491fd78180c9`

The engineering-system commits that follow are documentation/control-plane changes. They do not by themselves constitute application-feature verification.

## Current application candidate

- Latest application-changing candidate for the landing/media cycle: `33ec396f69ddcf50b217f986611b5120e9a58d4d`.
- This candidate includes bundled real stock landing photography, tighter landing composition, public navigation refinement, service-worker image precache, and regression coverage.
- Later commits may update Director documentation without changing the application candidate.

## Current objective

Move Zazu toward commercial readiness through:
- architectural hardening;
- correctness;
- data integrity;
- security;
- workflow completion;
- UX/accessibility;
- reliability/recovery;
- operability;
- release evidence.

The objective is **risk reduction and software quality**, not test-count growth.

## Active control target

**ENG-SYS-001 — Replace conversation-driven agents with a state-driven delivery system.**

Status: IMPLEMENTED

The control system now uses:
`BASELINE → TARGET → INSPECT → DESIGN → CHANGE → VERIFY → BREAK → ACCEPT → RECORD → NEXT`

## Operating invariants

1. Zazu repository is the only implementation target.
2. Current repository evidence outranks historical chat.
3. Explicit owner instruction outranks old plans.
4. One primary target per meaningful cycle.
5. Every meaningful change has a baseline and expected delta.
6. Shared changes require blast-radius review.
7. Repeated failures trigger Forensics.
8. No-op cycles must advance, escalate or stop.
9. Tests are evidence, not the objective.
10. Runtime/CI states are never claimed without observed evidence.

## Current readiness domains

- Correctness
- Architecture
- Data Integrity
- Security
- Workflow Integrity
- UX / Accessibility
- Reliability / Recovery
- Operability
- Deployment / Upgrade Safety
- Documentation / Ownership / Compliance
- Release Evidence

See `READINESS_REGISTER.md` for live gate direction.

## Current release-candidate head

Main now contains the completed niche-focus, progressive-disclosure, offline-first architecture and commercial-isolation source cycle from 2026-10-03.

Latest verified remote main: `b6495964f5f39e2caea4c6512e5900e0784b9e4d` (current-head populated authorization/isolation challenge added and passed; prior d81c1bc... remains the CI-workflow correction).

### Current-head verification — 2026-10-04

- `composer test`: **242 passed, 1,381 assertions**.
- Full Playwright suite against a fresh isolated seeded SQLite database: **25 passed, 2 intentionally skipped**. The skips are the registration journey's explicit mobile/tablet exclusions to avoid repeated shared-IP registration throttling; responsive entry surfaces ran on all three projects.
- Desktop, Pixel 7 emulation and tablet projects exercised populated Work inspection, financial replay/overpayment, manager/staff authorization, onboarding, theme tokens and landing-to-workspace navigation.
- Theme/navigation tests now authenticate through the seeded demo owner instead of silently skipping when an external storage-state file is absent. A local E2E-only adjustment opens the collapsed public navigation on mobile/tablet before asserting its actions; the full passing run included that working-tree change.
- A four-worker local run produced two long-workflow timeouts; the same full suite passed serially. Classify those timeouts as runner-load-sensitive (F8), not application failures. CI is configured to use one worker.
- A responsive Work-inspector screenshot was rendered and reviewed. Closed inspector state is now explicitly hidden and non-interactive; opened content remains vertically readable on phone emulation.
- The PHP CLI still emits the missing `pdo_firebird` extension startup warning. It did not prevent the test suite from running. The earlier `Throwable` import warning did not recur in this run.
- GitHub Actions on current `main` `b6495964f5f39e2caea4c6512e5900e0784b9e4d` are **VERIFIED / PASSED**: Laravel, Quality, PHPMD, Psalm Security Scan, browser smoke, CodeQL and populated upgrade/rollback all completed successfully. SonarCloud remains intentionally skipped by workflow condition.
- Populated upgrade/rollback run `37202719119` exercised baseline seeding, backup creation, current migrations, populated-data preservation, backup restore, private-media recovery, migration reapplication and final migration-state verification; all passed.

The 2026-10-03 readiness scorecard remains historical. Current-head recovery, upgrade/rollback and populated authorization/isolation evidence are now verified; physical-device acceptance and legal/licence/brand closure remain open.

Previous green figures are not reused in place of this current-head evidence.

## Director release-evidence cycle — 2026-10-04

Target: close the highest-risk recovery/upgrade evidence gate and reconcile current-head CI evidence without changing product scope.

Completed:
- corrected the populated upgrade workflow's invalid job-level `runner.temp` context usage on `main`;
- verified Laravel, Quality, PHPMD, Psalm Security Scan, browser smoke and CodeQL success;
- verified populated upgrade + rollback recovery, including populated records, private media and migration reapplication;
- confirmed the apparent Work quote/requirements authorization gap was an extraction artefact; the current routes carry the intended permission middleware;
- advanced the Director evidence state for Recovery and Deployment/Upgrade Safety.

Release disposition:
- Recovery / backup / restore: VERIFIED at maturity 4/5; repeated production-style proof remains desirable.
- Deployment / upgrade / rollback: VERIFIED at maturity 4/5; representative populated drill passed on current `main`.
- Current-head CI evidence: VERIFIED / PASSED.
- Release is not yet certified. Remaining material gates are final populated authorization/isolation challenge, physical phone/tablet acceptance, and legal/privacy/licence/brand closure. Full disconnected phone-local operation remains outside the current implementation claim.

Next Director target:
FINAL AUTHORIZATION / ISOLATION CHALLENGE → LEGAL, LICENCE & BRAND CLOSURE → PHYSICAL-DEVICE ACCEPTANCE → FINAL RELEASE RE-AUDIT

Last updated: 2026-10-04


## Director populated authorization/isolation cycle — 2026-10-04

Target: prove populated multi-business read, search and mutation isolation on the current main head.

Completed:
- added PopulatedAuthorizationIsolationTest;
- seeded the complete Demo Scenario in Business A;
- created a separate Business B with its own owner and customer;
- verified Business B cannot view Business A's populated customer, Work/Event or invoice;
- verified Business B search cannot surface Business A's customer;
- verified Business B cannot create Work against Business A's customer;
- verified Business B's own customer remains available;
- current Laravel CI run 37203066802 passed the challenge as part of 242 tests / 1,381 assertions;
- current Psalm, PHPMD, Quality, CodeQL, browser and populated upgrade/rollback workflows also passed for the same head.

Security disposition:
- Final populated authorization/isolation challenge: VERIFIED / PROVEN at maturity 4/5.
- No cross-business data disclosure or mutation was evidenced by the challenge.
- This is application/runtime evidence, not a claim of formal security certification.

Next Director target:
LEGAL / LICENCE / BRAND CLOSURE → PHYSICAL-DEVICE ACCEPTANCE → FINAL DIRECTOR RE-AUDIT

Last updated: 2026-10-04


## Current product-direction note

- Primary niche focus is now a presentation preference stored per business membership, separate from experience level and permissions.
- Progressive disclosure is being extended from the existing hierarchical navigation into high-value workspace surfaces.
- Offline remains a core operating condition. The repository now has server-side source mutations, per-device delivery/cursors and a stable business-scoped local-record identity registry; it does not yet have a phone-local store, pairing/bootstrap flow, durable client queue or domain mutation handlers.

## Current next-target rule

Select the highest-risk unresolved item that is:
1. actionable now;
2. materially reducing release risk;
3. supported by current evidence;
4. bounded enough to execute safely.

Next release-evidence target: legal/licence/brand closure and physical-device acceptance before final Director re-audit.

## Evidence boundary

Repository inspection is available through GitHub-native access.

Local runtime, browser, database and environment claims require actual observation.

## Update rule

After every meaningful cycle:
- update this state;
- update REGRESSION_LEDGER when a new failure pattern appears;
- update DECISION_LOG when a durable rule changes;
- update READINESS_REGISTER when a gate changes;
- create a dated historical record only when the event is significant.

Last updated: 2026-10-04


## Latest completed cycle — 2026-09-28

Target: shared shell/UI refinement + dashboard usefulness + release-position check.

Completed:
- flattened primary navigation and removed nav icons/arrows;
- removed repeated "Active workspace" navigation block;
- strengthened vibrant light-mode blue treatment;
- expanded sidebar account/avatar surface;
- extended restrained cross-product motion with reduced-motion handling;
- added dashboard Priority Now attention surface;
- corrected the root landing-page regression test;
- added shared shell regression coverage;
- recorded current V1 release scorecard in `docs/DIRECTOR_RELEASE_STATUS_2026-09-28.md`.

Verification boundary:
- repository structure and source were inspected;
- current-HEAD CI status returned no observed status entries/runs at inspection time;
- local runtime/browser execution remains an environment-dependent verification boundary.

Next target:
**RELEASE VERIFICATION — runtime + populated data + recovery**


## Latest visual review cycle — 2026-09-28

Target: review the previous visual cycle for regressions and refine again.

Findings/corrections:
- detected that some previous light-theme assertions were global instead of theme-scoped;
- sealed dark mode with explicit dashboard, section-tab, form, button and status foreground/background pairings;
- separated sea-glass accent from indigo information status;
- tightened header search/action spacing and mobile positioning;
- retained flat navigation and stronger cobalt-iris identity;
- statically re-checked the final visual layer: no unscoped literal white/pale background + pale foreground pairing remains.

Verification boundary:
- source-level audit completed;
- rendered browser/device verification remains required before visual acceptance.


## Customer import cycle — 2026-09-30

Target: complete the bounded customer spreadsheet import path without weakening existing customer architecture.

Completed:
- preserved read-only spreadsheet reader and matcher boundaries;
- connected matching states to preview;
- added transactional customer import service;
- restricted writes to explicitly approved new rows;
- existing matches can only be skipped, never overwritten by import;
- duplicate and needs-review rows are blocked;
- serialized imports per business with a parent-business row lock;
- recorded per-customer import audit entries;
- added upload → preview → approval → import controller/view workflow;
- bound pending import data to the active business workspace;
- exposed import from the customer directory.

Verification boundary:
- repository source and diff inspected;
- current GitHub status returned no observed CI status entries;
- local Laravel/browser/database execution has not been observed in this cycle and remains required.

Remaining risk:
- duplicate/needs-review resolution UI is intentionally not implemented yet; those states remain hard blockers rather than silent guesses;
- runtime upload, preview, transaction, rollback and populated-data isolation still require execution evidence.



## Director revelation cycle — 2026-10-01

Target: persist the latest external design findings and establish the next UI/capability governance layer without changing application behaviour yet.

### New durable direction
- Progressive disclosure is now a formal Zazu UX rule: primary → expandable → advanced → specialist.
- Basic/Intermediate/Advanced remains an experience presentation level, not a requirement to show a literal mode switch on every screen.
- Accordions are preferred for grouped settings/forms; expandable rows for records; expandable cards/summaries for overview surfaces; sticky summaries for consequential workflows such as quotes.
- Event/job context should be the user's mental container for connected operational information.
- The public landing page is a brand/identity surface distinct from the operational dashboard.
- Development visual assets are allowed for design exploration. Release asset/IP/license clearance is a separate gate.
- Open-source libraries and APIs are enhancements unless explicitly promoted to core.

### New control documents
- docs/ZAZU_EXTERNAL_CAPABILITY_REGISTER.md
- docs/ZAZU_UI_DISCLOSURE_STANDARD.md

### Next Director target
**UI/UX RECONNAISSANCE — current landing + key operational screens against the new disclosure standard, followed by bounded implementation targets.**

No application code was changed by this documentation cycle.

Last updated: 2026-10-01


## Load-shedding mobile test cycle — 2026-10-01

Target: make Zazu practical to test from a phone when internet/GitHub/Wi-Fi are unavailable during load shedding.

Executed:
- added a web-app manifest for Zazu;
- added a conservative service worker that caches static assets only;
- deliberately excluded authenticated HTML and business data from offline caching;
- added a field-testing runbook for phone → hotspot → laptop → Laravel local-network testing;
- established that full offline business operation is a separate architecture decision and is not claimed by this change.

Verification boundary:
- repository source was inspected and the implementation was committed;
- actual phone/browser/local-network execution still requires owner-side runtime observation.

Next runtime target remains the populated end-to-end business workflow, with mobile field testing now available as a practical verification path.


## Director module-skeleton cycle — 2026-10-01

Target: stop polishing already-fit modules and identify the thin structural modules sitting inside the broader Zazu foundation.

Durable product rule added:
- Mobile is a first-class V1 usage surface. A business owner/staff member may have only a phone available; desktop is not a prerequisite for usable Zazu.
- Desktop remains important for dense setup, bulk operations, reporting and administration.
- True phone-only offline operation is a future architecture and is not claimed by the current PWA/static-cache work.
- Offline capability should be built incrementally: small foundation bricks with explicit evidence before each next layer.

Source-level module audit recorded in docs/DIRECTOR_MODULE_SKELETON_AUDIT_2026-10-01.md.

Primary skeleton target selected:
SUPPLIERS — coherent maintenance surface.

Reason: supplier records are a core dependency of purchasing, but the current module only exposes list/create/store and leaves normal supplier correction incomplete. This is a bounded structural completion, not feature expansion.

Other module decisions:
- Calendar: keep intentionally lean.
- Reports: keep as a reporting foundation until finance/commercial data closes.
- Compliance: already substantial; stop breadth expansion.
- Assets: existing foundation is sufficient for now; later address only the explicit condition/damage/loss requirement.
- Inventory: existing foundation is substantial; later reconcile with purchasing/receiving rather than expanding warehouse scope.
- Capabilities/catalogue: fit; deepen connections rather than add catalogue complexity.
- Work/Event: central authority; protect it from duplicate domain logic.
- Quotes/Finance: architecture is substantial, but commercial closure remains higher priority than internal feature accumulation.

Next execution target:
Complete the supplier maintenance skeleton, then re-audit its purchasing blast radius.



## Supplier skeleton execution — 2026-10-01

Executed the selected bounded skeleton correction.

Changed:
- added supplier edit/update routes using existing suppliers.update permission;
- added business-scoped supplier edit/update controller path;
- wrapped supplier updates in a transaction with row locking;
- added supplier create/update audit entries;
- reused the existing supplier form for create and edit;
- exposed Edit from the supplier register;
- removed the pre-existing duplicate Add supplier action in the supplier header;
- kept supplier history intact; no delete operation was introduced.

Source-level re-audit:
- purchasing already resolves suppliers by active business, so the maintenance surface remains within the existing supplier authority;
- supplier business ownership is checked before edit/update;
- no new supplier abstraction or parallel domain was introduced;
- no purchasing, inventory or finance logic was changed.

Verification boundary:
- current repository source was re-read after the changes;
- local Laravel/browser/database execution was not observed and is not claimed.

Next Director target:
**Re-audit the remaining lean resource modules, with purchasing ↔ inventory reconciliation as the next structural target unless a higher-risk commercial gap is verified first.**



## Navigation hardening cycle — 2026-10-01

Target: make navigation reliable enough that normal users can test and operate Zazu without hunting for modules.

Executed:
- kept primary navigation explicit: real business destinations are exposed by name rather than hidden behind category landing links;
- added regression coverage for the primary destination set, including Suppliers, Purchasing, Inventory, Assets, Quotes and core workspace destinations;
- hardened the mobile navigation region so a long destination list can scroll inside the drawer without clipping the drawer shell or pushing essential controls off-screen;
- preserved the same information architecture on desktop and mobile;
- retained contextual section tabs as secondary navigation rather than the sole route to a module.

Verification boundary:
- current GitHub source was re-read after the hardening changes;
- runtime/browser/mobile execution was not observed in this cycle and is not claimed.

Next Director target:
**CORE WORKFLOW INTEGRITY — audit Customer → Job/Event → Quote → acceptance/status → payment/deposit → preparation → purchasing/costs → invoice/completion, starting with the highest-risk broken or incomplete relationship found in source evidence.**


## Core workflow hardening cycle — 2026-10-01

Target: keep the Customer → Job/Event → Quote → acceptance/status chain internally coherent.

Finding:
- Quote acceptance changed the quote and quote version to accepted but did not advance a draft Job/Event into its confirmed operational state. That left the commercial acceptance and operational lifecycle disagreeing.

Executed:
- centralised the bounded rule in EventLifecycleService;
- public customer acceptance now confirms a draft job atomically with quote acceptance;
- authenticated staff acceptance follows the same rule;
- added regression coverage for both public and staff acceptance paths;
- existing confirmed/in-progress jobs are not rewritten by acceptance.

Re-audit:
- business/event/quote lock ordering remains intact;
- accepted quote and accepted version remain updated together;
- event confirmation occurs inside the same transaction;
- no new domain or parallel lifecycle logic introduced.

Verification boundary:
- repository source was re-read after the change;
- local PHPUnit/runtime execution is not observed in this cycle and is not claimed.

Next target:
**FINANCIAL CONVERSION INTEGRITY — re-audit accepted quote → invoice → payment/deposit relationships and identify the next concrete integrity gap before adding breadth.**


### Navigation hierarchy correction — 2026-10-01

The Quick Access implementation was rejected during owner UI testing because it duplicated destinations already present in the sidebar and did not solve the actual findability problem.

Executed:
- removed the duplicate Quick Access destination rail;
- replaced the long visible destination list with five compact top-level areas;
- desktop exposes child destinations through hover/focus flyouts;
- mobile exposes the same children through tap/focus expansion;
- System remains permanently visible, so Settings no longer depends on sidebar scrolling;
- active child/parent state is retained;
- permission-aware destination visibility remains intact.

Next Director target:
**Continue UI/UX reconnaissance and attack the highest-risk page/workflow defect found in source and owner testing, without deferring UI behind backend work.**


## Quote workflow UI/reliability cycle — 2026-10-01

Target: audit Customer → Job → Services → Quote → acceptance as one commercial workflow.

Homework:
- Current Dubsado guidance keeps proposals inside project context and can connect proposal, contract and invoice into one client flow; this supports Zazu's decision to keep quotes attached to the Work record rather than make them isolated documents.
- Tripleseat describes proposals and operational documents as deriving from the same event record, reinforcing the single-event-context direction.

Findings:
- Zazu's quote domain already has strong snapshot/version integrity, business scoping, status transition rules and stale-requirement detection.
- The quote register had a real UI correctness defect: every non-draft status was rendered as success, so declined/expired quotes could visually appear successful.
- A stale UI regression test still expected the previously rejected Quick Access navigation, conflicting with the current hierarchical navigation design.

Executed:
- corrected quote-register status semantics: accepted = success, declined/expired = danger, sent = info, draft/superseded = neutral;
- replaced the obsolete Quick Access regression test with current quote UI semantic coverage.

Verification boundary:
- repository source was re-read and both changed files were updated successfully;
- local Laravel/browser execution remains unobserved and is not claimed.

Next target:
**QUOTE → ACCEPTANCE → FINANCE CONVERSION** — verify the hand-off from an accepted commercial version into invoice/deposit/payment and identify the next concrete release-risk gap before adding quote breadth.


## Populated operator audit cycle — 2026-10-01

Target: use the advanced populated demo business as the end-to-end operator test fixture.

Journey:
Dashboard → Job → Requirements → Quote → Purchasing → Inventory / Assets → Preparation → Invoice → Payment → Reports

Executed:
- replaced the job-register function-sheet placeholders with live requirement, preparation, rental, asset-allocation, quote and invoice data;
- fixed the HTML template data-read bug by using template content;
- made guest headcount visible from recorded job requirements;
- preserved job context when moving from Work into Purchasing;
- added job preselection to new purchase orders;
- removed the invalid receipt path from received purchase orders and aligned the receiving service;
- added active-job attribution to inventory issue forms;
- added invoice payment history and direct invoice → payment hand-off;
- added direct Finance workspace entry actions;
- replaced stale Reports finance gating with live currency-safe invoice/payment/expense summaries;
- surfaced populated purchasing, stock, assets and receivables attention on the dashboard;
- corrected a populated demo PO/lifecycle contradiction by making the open replenishment PO general rather than event-linked;
- corrected demo invoice chronology;
- added populated operator regression coverage and receiving/inventory UI coverage;
- recorded the full audit in docs/DIRECTOR_OPERATOR_AUDIT_2026-10-01.md.

Re-audit:
- current repository head was re-read after the cycle;
- malformed report formatter reference was caught and corrected before completion;
- current combined GitHub status returned no status entries and the available workflow-run wrapper returned no runs for the latest main-branch commit;
- local runtime/browser execution remains unobserved and is not claimed.

Next target:
RUNTIME POPULATED WALKTHROUGH — execute the seeded business in the actual browser on desktop and mobile, focusing on rendered inspector, contextual purchasing, inventory attribution, invoice/payment hand-off, reports rendering and populated-content overflow.


## Long-sprint page-by-page hardening cycle — 2026-10-01

Target: audit the complete operator surface for solo → growing business usability while tightening reliability foundations without expanding architecture unnecessarily.

Executed:
- audited public landing/authentication, onboarding, dashboard, Work, requirements, customers/contacts, quotes, purchasing, inventory, assets, preparation, travel, costs, finance, reports, settings/compliance, search, calendar and shared shell;
- kept surfaces that already met the usability quota instead of adding duplicate cards, modules or navigation;
- exposed the existing multi-line purchase-order capability in the UI with add/remove lines and live totals;
- added live quote subtotal/tax/total/deposit checkpoints to quote create and revision surfaces, matching the documented financial-workflow UI rule;
- added shared duplicate-submit protection for ordinary forms and made browser storage optional/failure-tolerant;
- preserved job-scoped purchasing context and invoice-scoped payment return paths;
- extended regression contracts for the new foundations and corrected a test-source write defect found during re-audit;
- re-read changed files after writes and stopped further source-only mutation once no higher-value safe improvement remained without runtime evidence.

Re-audit:
- quote financial summary was verified as rendered in both create and revision views after the initial source gap was caught;
- application/test source contains no flattened AppModels/AppSupport/AppHttp/AppServices tokens;
- existing business isolation, composite parent/child constraints, lifecycle transactions, idempotency and error containment remain intact;
- no new navigation authority, permission model, or parallel domain source was introduced.

Verification boundary:
- repository source is current through main commit ddba8ce1d557eafc3d25bdadeaf2d5e7ed8414bf;
- GitHub connector still exposes no usable current workflow-run/status result for the latest main commits;
- runtime, browser/mobile, migration/upgrade and backup/restore execution remain unobserved because the development PC is off.

Next target:
**RUNTIME POPULATED WALKTHROUGH / RELEASE EVIDENCE.** Do not continue broad feature or cosmetic expansion until current-head execution exposes the next verified defect.


## Finance dashboard regression cycle — 2026-10-01

Target: close finance/workflow rendering and populated financial-display failures from the current test run.

Finding FIN-001:
- Finance dashboard rendering failed because the view checks action permissions against `$business`, but `FinanceController::index()` supplied only the active business ID.
- The work inspector exposed raw financial amounts without thousands grouping despite the existing `Money::formatCents()` display helper.
- The quote-editor UI test created an event without requirements; the quote route correctly redirected because quotes require at least one requirement.

Completed:
- passed the active business model to the finance view while retaining business-scoped queries;
- formatted inspector quote, deposit, paid and balance values with the shared money formatter;
- supplied an event requirement in the quote-editor UI test fixture.

Verification:
- targeted finance, skeleton-page and UI/accessibility suites: 37 passed;
- full automated suite: 202 passed, 1,174 assertions;
- `git diff --check`: passed.
- PHP emits a startup warning for the unavailable optional `pdo_firebird` extension; it did not prevent the test suite from completing.

Readiness impact: the observed source/test regressions are closed. Automated evidence is current; runtime/browser, populated-device, recovery and upgrade evidence remain unverified.

Next target:
**RUNTIME POPULATED WALKTHROUGH / RELEASE EVIDENCE.**


## Director control correction cycle — 2026-10-01

Target: eliminate repeated execution failures caused by weak verification orchestration, branch residue and competing UI token authorities.

Completed:
- added 06_VERIFICATION_AND_QUALITY_ENGINE.md;
- added docs/ZAZU_TEST_AND_QUALITY_MATRIX.md;
- formalized F1–F8 failure classification before application correction;
- made owner-added code-review tests first-class regression evidence;
- made GitHub workflow/YAML inspection part of relevant verification work;
- established main-only Director execution by default;
- consolidated the repeated root-token blocks in resources/css/zazu-final-visual-sweep.css into one light root and one dark root, preserving the latest declared value for each token;
- strengthened the UI/UX engine so visual changes must use one shared token authority and require rendered theme/responsive verification.

Observed repository branch state during audit:
- main;
- director/calendar-ux-refinement;
- director/ci-test-hardening;
- director/diagnostic-release-gates;
- director/psalm-ci-hardening;
- revert-12-hardening/process-cross-domain-2026-09-28.

The Director will not create additional temporary branches. Existing branch cleanup remains a repository-ref cleanup task because the connected GitHub action surface available to this execution does not expose branch deletion.

Verification boundary:
- repository source changes were written to main and re-read through GitHub operations;
- current workflow-run evidence remains unavailable through the connected workflow-run inspection path;
- local browser rendering remains required for visual acceptance;
- the consolidated CSS token layer is source-verified but not rendered-verified.

Next target:
**CURRENT-HEAD VERIFICATION RECONNAISSANCE** — inspect the exact current main workflows/tests and consume actual run evidence before further application mutation.

## Director populated workflow + authorization challenge — 2026-10-01

Target: execute the populated business walkthrough and challenge the role, permission and business-isolation boundaries using the current main source of truth.

Current main head observed: ebdebe9365181c1632dcbb804c51fd0905785a76.

Populated walkthrough result:
- The seeded Zazu Demo Catering fixture contains a connected Customer → Job/Event → Requirements → Quote → accepted version → Preparation → Purchasing → Inventory/Assets → Invoice → Deposit/Payment → Reports chain.
- The financial chain reconciles at source level: invoice total ZAR 11,500.00, deposit ZAR 3,450.00, balance payment ZAR 8,050.00, paid total ZAR 11,500.00 and balance ZAR 0.00.
- The received event PO is closed to further receiving; the second PO is deliberately general stock replenishment rather than attached to completed work.
- Demo seeding is idempotent and existing populated-surface regression coverage exercises dashboard, Work, purchasing, invoice/payment and reports relationships.

Authorization challenge result:
- Owner authorization path is explicitly supported by the current permission architecture.
- Staff owner-only boundaries are covered by existing tests for owner dashboard, settings, catalogue and finance mutation access.
- Active-business isolation and foreign-business route/model boundaries are structurally enforced.
- AUTH-ROLE-001 found: the populated demo seeds an Operations Manager membership with role manager, but config/zazu.php currently defines a permission set only for staff; PermissionService therefore resolves the manager to an empty permission set.
- This is an authorization-completeness / role-model consistency blocker for multi-role release certification. It does not by itself demonstrate privilege escalation.
- Safe release disposition: do not certify the manager role until the role is explicitly mapped to permissions or the seeded/demo role is changed to a supported role.

Verification boundary:
- GitHub source evidence was observed on the current main head.
- No live deployment URL and no local Laravel/browser runtime are available through this execution, so the literal rendered populated walkthrough and adversarial HTTP/browser responses are UNVERIFIED, not green.
- Historical/current-head test claims in repository documentation are not substituted for direct execution against this latest commit.

Next target:
ROLE POLICY CLOSURE → RUNTIME POPULATED WALKTHROUGH → FULL AUTHORIZATION/ISOLATION CHALLENGE.

## Director populated runtime challenge — 2026-10-01

Target: move the populated business workflow from source/PHPUnit evidence into real browser/runtime evidence and deliberately challenge the workflow rather than only its assertions.

Executed:
- inspected current main head before mutation;
- attempted direct local runtime/browser observation;
- confirmed no deployed runtime URL is recorded in the repository and the available live-browser connector could not execute because of insufficient browser credits;
- identified that .github/workflows/zazu-browser.yml migrated a clean database but did not seed the populated demo;
- added e2e/populated-runtime.spec.js covering the seeded owner chain: Dashboard → Work → Job inspector → Requirements → Preparation → Purchasing → Inventory → Assets → Finance → Invoice/Payment history → Reports;
- added adversarial role checks for manager and finance staff boundaries;
- changed zazu-browser.yml to seed DemoScenarioSeeder before Playwright execution;
- created docs/DIRECTOR_POPULATED_RUNTIME_CHALLENGE_2026-10-01.md with the evidence boundary and release disposition.

Important current-source correction:
- config/zazu.php now contains an explicit manager permission matrix. The earlier AUTH-ROLE-001 record describing a missing manager mapping is historical/stale; it is not a current source defect.

Verification boundary:
- GitHub source changes were committed and re-read;
- immediate commit-status/workflow inspection returned no usable status entries or workflow runs through the available connector;
- therefore populated browser execution is still UNOBSERVED, not green.

Current execution commits:
- 715a38a411606846c715fe38e3388276c613161 — populated runtime browser challenge;
- 6b7c306349efd9852b60c5c14ccbf7f0aacbba34 — seed populated demo in browser workflow;
- 0dcfd2e4db5f62f69972411a02c9bd71a2e89a25 — runtime challenge evidence record.

Next Director target:
**CONSUME POPULATED BROWSER EXECUTION → classify failures → correct verified defects only → rerun → then repeat responsive runtime and recovery/upgrade evidence.**


## Director V2 loop-breaking control — 2026-10-01

Implemented repository-side failure-loop controls:

- .ai/engineering/07_FAILURE_CASE_ENGINE.md — persistent failure-case operating contract;
- .ai/engineering/FAILURE_CASES.md — active/history registry and case template;
- Director and router now require failure identity, fingerprint review, hypothesis history and escalation before repeated correction;
- Verification engine now enforces the failure-case attempt budget;
- AGENTS.md and TASK_PACKET.md carry the V2 rules into future sessions;
- DEC-014 and REG-011 record the durable decision/control.

Hard limits:
- 2 correction attempts per hypothesis;
- 3 no-progress cycles per failure case;
- then STOP PATCHING → FORENSICS / ESCALATION.

This control-plane implementation is DOCUMENTATION/PROCESS infrastructure. It does not claim that application tests or runtime workflows are now passing.


## Director synchronization + human-eye UI doctrine — 2026-10-01

Decision:
- Director/Morpheus is the sole engineering entry point.
- Specialist engines are synchronized capabilities, not independent command hierarchies.
- Human-Eye / Creative Critique is consolidated into the existing UI/UX Improvement Engine.
- Verification owns rendered visual evidence; UI/UX owns visual critique; Guardian challenges regression; Director reconciles the combined state.

UI quality requirements:
- semantic colour systems with one shared token authority;
- light/dark theme relationship review;
- content-appropriate form widths;
- table width and horizontal eye-tracking burden review;
- predictable scan paths and grouping;
- typography hierarchy;
- responsive composition;
- technical correctness does not equal visual acceptance.

The owner is not expected to supply UI theory. UI/UX is responsible for applying established principles and surfacing concrete findings relevant to the current target.

No standalone Engine 08 is created.

## Director SaaS-readiness architecture cycle — 2026-10-02

Target: establish a controlled path from the current Zazu modular monolith to future hosted multi-business SaaS without introducing premature distributed infrastructure.

Executed:
- created `docs/ZAZU_SAAS_READINESS_ARCHITECTURE.md` as the living architecture contract;
- retained Director/Morpheus as the sole entry point and acceptance authority;
- formalized business ownership and parent/child invariant requirements for future hosted isolation;
- formalized idempotency, concurrency, state, offline dependency and data-migration rules;
- established evidence-driven progression from modular monolith → measurement → targeted optimisation → scaling → selective extraction;
- explicitly rejected speculative microservices, Kubernetes, sharding, distributed caching and cloud-only core dependencies;
- recorded DEC-015 in the durable decision log;
- added the SaaS-readiness architecture gate to the engine router.

Contradiction check:
- current Zazu V1 remains a coherent operational product;
- SaaS-readiness is treated as an architectural foundation, not as a claim that public multi-tenant SaaS certification is complete;
- local-first core operation remains protected;
- existing Director/engine authority remains unchanged;
- no application business rules were duplicated or replaced by a new SaaS layer.

Current evidence boundary:
- source architecture/control changes are committed to `main`;
- runtime populated-data isolation, adversarial authorization, recovery, deployment and upgrade evidence remain required before a hosted SaaS release can be certified.

Next Director target:
**COMMERCIAL SAAS ISOLATION PROOF — inspect the actual current business-owned models, queries, exports, attachments, search and route boundaries; then close only evidence-backed isolation gaps and verify them against populated multi-business fixtures.**

Last updated: 2026-10-03

## PHPUnit parse-blocker correction — 2026-10-03

Target: restore PHPUnit discovery for `UiAccessibilityTest`.

Completed:
- closed the test class, which had an unmatched opening brace at EOF;
- `php -l tests/Feature/UiAccessibilityTest.php` passed;
- `php artisan test tests/Feature/UiAccessibilityTest.php` passed: 11 tests, 91 assertions.

Full-suite evidence:
- `composer test` now runs 231 tests: 222 passed, 3 failed, 6 errored;
- failures/errors were subsequently fingerprinted and corrected in the incident cycle below;
- at that point, PHP warned that `pdo_firebird` could not be loaded and PHPUnit reported the ineffective global `Throwable` import in `bootstrap/app.php`; the repository warning was removed in the later cycle.

Disposition: the parse blocker was resolved; the remaining test failures were carried forward for root-cause correction in the incident cycle below.

## Dashboard database incident and offline/restore regressions — 2026-10-03

Target: restore local authenticated dashboard access and close the known offline and backup/restore regressions without weakening data or archive safeguards.

Baseline: `main` at `9e6ff7cccf1c341b99bd7be767f714154d1ece81`; worktree was clean before this cycle.

Completed:
- traced DB-001 to `GET /dashboard` selecting `business_user.primary_niche` from a local SQLite database missing that column;
- confirmed five additive repository migrations were pending, then applied them with `php artisan migrate --force --no-interaction`; no reset or data replacement;
- corrected offline license tests to pass the `Business` required by the service;
- aligned `SyncDevice` and `SyncMutation` model defaults with their database defaults so new Eloquent instances have valid active/pending state;
- corrected the sync acknowledgement test to retain sequence 2 after acknowledging sequence 1;
- normalized generated backup ZIP paths to `/`, preserving restore's rejection of ambiguous archive paths;
- removed the stale call to the undefined `setupZazuBusinessSwitcher()` initializer, which stopped later shared UI initialization;
- removed the ineffective global `Throwable` import warning from `bootstrap/app.php`.

Verification:
- all five pending migrations now report `Ran`; `business_user.primary_niche` is present;
- authenticated browser rendered `Dashboard · Zazu Demo Catering` using the newly built Vite JavaScript asset;
- focused offline, sync, backup/restore, accessibility and error handling tests: 33 passed, 190 assertions;
- `composer test`: 231 passed, 1,318 assertions;
- `npm run build`, changed PHP syntax checks and `git diff --check` passed.

Remaining environment warning: PHP still emits a startup warning because `pdo_firebird` is configured but its extension DLL is absent. This is outside repository code and was not changed.

Disposition: DB-001 and the reproduced offline/restore failures are closed locally. Deployment environments must apply pending migrations before serving the updated application. Persistent evidence: CASE-ZAZU-0001 through CASE-ZAZU-0003 in `FAILURE_CASES.md`.

## Restore command and stable sync identity cycle — 2026-10-03

Baseline: `main` at `28a80784995ec03565aae5d66358cac209508455`; the restore correction and identity foundation are now committed on `main` at `4c467c6b7a6ca0f6022284a0dd8912102ed8d0a1`. The evidence-count corrections in this entry remain in the local worktree.

Completed:
- repaired the missing closing parenthesis that prevented `ZazuRestoreCommand.php` from parsing and stopped Artisan from discovering `zazu:restore`;
- added `sync_entity_identities`, mapping business-owned persisted records and stable entity types to business-scoped UUIDs;
- added `SyncEntityIdentityRegistry::identify()` for repeatable local identity allocation and `register()` for idempotent imported identity registration;
- guarded record/type reassignment, duplicate identity reuse within one business, malformed UUID/entity type input, unsaved/non-business records, and stale in-memory business ownership;
- applied the additive identity migration locally; no database reset or existing data replacement;
- recorded owner-approved pairing and selected-bootstrap scope in DEC-019.

Verification:
- focused identity feature tests: 6 passed, 13 assertions;
- `composer test`: 236 passed, 1,331 assertions;
- `php artisan migrate:status`: all migrations report `Ran`, including the new identity migration;
- PHP syntax checks, Laravel Pint `--test`, and `git diff --check` passed;
- `php artisan help zazu:restore` confirms the command is registered and available.

Remaining evidence boundary:
- the configured `pdo_firebird` extension DLL is still missing and PHP emits a startup warning; it did not block the current checks;
- browser E2E, production recovery, real LAN sync, owner-approved pairing, selected-data serialization/bootstrap and phone-local queue remain unverified/not implemented.

Disposition: restore command discovery is closed as CASE-ZAZU-0004. Stable local entity identity is implemented and tested as a foundation only; no domain mutation handler is attached.

Next Director target:
**OWNER-APPROVED DEVICE PROVISIONING + SELECTED BOOTSTRAP — define the record-selection and transport contract, then implement and adversarially verify business scoping, pairing expiry/replay protection and selected-data completeness before activating a real domain handler.**

## Director commercial audit cycle — 2026-10-03

Target: assess Zazu against the product specification and commercial release requirements, with mobile-first acceptance and legal/recovery readiness treated as first-class release concerns.

Completed:
- audited current repository source and living product/release documents first;
- created docs/DIRECTOR_COMMERCIAL_AUDIT_2026-10-03.md;
- recorded overall commercial-readiness maturity at 67/100;
- reconciled Helper direction from a fixed bird identity to one character framework with selectable skins;
- established R0 Helper animation baseline as native SVG + CSS + browser WAAPI with no paid animation dependency;
- refreshed docs/ZAZU_V1_STATUS.md;
- refreshed .ai/engineering/READINESS_REGISTER.md;
- refreshed docs/ZAZU_COMMERCIAL_LEGAL_REGISTER.md;
- reconciled docs/V1_RELEASE_CHECKLIST.md;
- updated Director Product Definition to remove the stale fixed-bird wording.

Audit conclusion:
- core operational product foundation is substantial;
- commercial release is not yet certified;
- remaining risk is concentrated in current-head verification, populated business workflow proof, recovery, upgrade/rollback, mobile/tablet acceptance, legal/privacy operations and final brand/licence clearance;
- full phone-local offline operation remains unimplemented and must not be marketed as already available;
- Helper documentation is ahead of the current live Helper implementation.

Shared next target:
CURRENT-HEAD VERIFICATION → POPULATED COMMERCIAL WORKFLOW → RECOVERY → UPGRADE/ROLLBACK → LEGAL/LICENCE/BRAND CLOSURE → FINAL DIRECTOR CERTIFICATION

No feature expansion should displace these release-critical gates.

Last updated: 2026-10-03
 
## Director implementation hardening cycle — 2026-10-03

Target: close concrete recovery, stale-client and baseline security weaknesses without expanding product scope.

Implemented on main:
- Restore now validates staged SQLite databases with a real SQLite integrity check before activation.
- MySQL restore now stages a pre-restore database dump before replacing private storage and attempts database rollback if restore fails after mutation begins.
- Service-worker caching is restricted to public static assets under /build/ and /images/; authenticated/private /media responses are excluded.
- Previous Zazu static-cache generations are deleted during service-worker activation.
- Added regression coverage for corrupt SQLite restore rejection and offline cache scope.
- Added baseline web security response headers: X-Content-Type-Options, X-Frame-Options, Referrer-Policy and Permissions-Policy.
- Added regression coverage for the security headers.

Verification boundary:
- Source was re-read after every changed-file write.
- Tests were added but fresh CI/runtime execution was not available through the repository connector in this cycle.
- Therefore these changes are recorded as IMPLEMENTED, not VERIFIED/PROVEN.

No competing implementation was introduced. The existing backup/restore command remains the single restore authority, and the existing service worker remains the single offline asset-cache authority.

Next Director target:
CURRENT-HEAD CI/RUNTIME VERIFICATION → POPULATED COMMERCIAL WORKFLOW → RECOVERY DRILL → POPULATED UPGRADE/ROLLBACK

Last updated: 2026-10-03


## Director Decision Gate integration — 2026-10-04

The Senior Architect/Sprint Controller discipline is now integrated into Morpheus/Director rather than introduced as a separate engine.

Active controls:
- finding classification and weighted prioritisation;
- explicit scope authority and sprint freeze;
- GREEN/AMBER/RED churn detection;
- architectural escalation when local patching stops being rational;
- meaningful-progress criteria based on risk/evidence rather than commit or test count;
- persistent decision recording through existing Director state and ledgers.

This is a control-plane enhancement. It does not change Zazu's product scope or create a competing backlog/acceptance authority.


## Director continuation — 2026-10-04

### Current main
- Current head after release-documentation and UI refinement: `3f8c0862d0825dd14fef2bb88442862d2779bc75`.
- Release-documentation design set is present and implementation-audited.
- Brand clearance record is present and remains OPEN/HIGH.
- Physical phone/tablet acceptance record is present and remains OPEN pending human real-device testing.

### UI/UX refinement
Director reviewed Elizabeth Alli / DesignerUp's practical colour guidance and applied the lesson to the existing final visual authority rather than adding another stylesheet:
- consolidated the effective light UI around the existing cobalt-iris primary family;
- reinstated sea-glass as a restrained supporting accent;
- increased plane/card separation to reduce the white-on-white slab effect;
- reduced the content maximum from 1480px to 1320px;
- reduced shared surface radii to 10px/6px;
- constrained common form controls and filter controls;
- made desktop `.zazu-btn.w-full` actions intrinsic-width while preserving mobile full-width behaviour;
- kept the authentication primary intentionally full-width;
- tightened table cell padding and command-surface height.

A regression test now checks the key colour/proportion tokens.

### Verification state
The latest push has triggered the normal GitHub Actions suite. At the time of this state update the newest runs for `3f8c0862...` are not yet observed as completed, so no new CI green claim is made.

### Release gate interpretation
Technical evidence previously accepted remains valid. The next release gates are:
1. complete real-world privacy/legal details and review;
2. finish dependency/licence/notice evidence;
3. clear the Zazu brand;
4. complete physical phone/tablet acceptance;
5. final Director re-audit on the selected release commit.


## Director defect refinement — 2026-10-04

### Wallpaper visibility — ROOT CAUSE CONFIRMED / FIXED AT SOURCE

The business-settings save path correctly stores `wallpaper_path` in private `business-branding/` storage, the authenticated `business.media` route correctly serves it, and `app-layout.blade.php` correctly adds `zazu-has-wallpaper` plus `--zazu-wallpaper` to the body.

The actual defect was CSS cascade order: later global `body { background: ... }` rules in `resources/css/zazu-final-visual-sweep.css` replaced the earlier background image declaration, so the selected wallpaper was never allowed to remain visible.

Director fixed this by adding a final, more-specific `body.zazu-has-wallpaper` rule in the existing visual authority. No storage, route, database or working branding path was rewritten.

Regression coverage was added to protect the rule from later cascade regressions.

### New-job customer creation — UX GAP CONFIRMED / FIXED

The existing create-job flow was technically correct but operationally inconvenient:
- the job form required an existing customer;
- when no customers existed, the page replaced the job form with a separate customer-navigation state;
- even with existing customers, there was no in-context creation path.

Director added a small in-context Add customer dialog to the existing new-job surface. It:
- uses the existing customer-create permission boundary;
- creates the customer inside the active business only;
- creates a primary contact;
- returns the new customer to the page;
- immediately adds/selects it in the existing customer selector;
- leaves the existing job-store transaction and customer-selection logic intact.

Regression coverage verifies empty customer state, successful quick creation, duplicate-name rejection and active-business scoping.

### Release-doc owner data

Release documents now identify the real current solo business owner/developer as **Isaac Junior Lehlogonolo Maluleka** and the supplied operating address as **1068 Block JK, Soshanguve, Pretoria, South Africa**. The documents continue to avoid inventing a registered company or unsupported legal contact channel.

Last updated: 2026-10-04

## Director UI/UX + commercial audit — 2026-10-04

Target: full Zazu UI/UX refinement, dark-mode form correction, CSS authority cleanup, repository audit and commercial valuation.

Completed:
- made base form surfaces theme-aware instead of hard-coded white;
- deduplicated the final visual stylesheet from approximately 257 KB / 1,561 blocks to approximately 213 KB / 1,263 blocks;
- added dark-form Playwright regression coverage;
- retained dashboard-art command-band and calendar visual treatment;
- retained holiday dots in compact calendar views;
- completed repository hygiene scan for common debug residue.

Current scorecard: 80/100 commercial readiness, not certified.

Material remaining gates:
- final populated commercial traversal;
- physical phone/tablet acceptance;
- fully disconnected phone-local store/pairing/bootstrap implementation;
- legal/privacy/licence/brand closure;
- final physical-device UI regression after the latest CSS/build cycle.

Valuation view:
- estimated replacement cost: R450k–R900k;
- realistic current early software/product asset value: R150k–R400k;
- valuation should move materially only after recurring paid usage and retention are proven.

Evidence boundary:
- current engineering state records 242 Laravel tests / 1,381 assertions and 25 Playwright passes / 2 intentional skips, plus populated authorization/isolation and recovery/upgrade evidence;
- this cycle's UI changes are source-level until the refreshed local Vite build and physical phone are actually inspected.

Next Director target:
PHYSICAL PHONE UI ACCEPTANCE → DISCONNECTED PHONE-LOCAL ARCHITECTURE → FINAL RELEASE RE-AUDIT.

## Director Visual Recovery Pass — 2026-10-04

The previous visual-sweep accumulation was replaced with a single compact canonical visual layer.

Recovery authority:
- shared colour/dimension tokens: `resources/css/app.css`;
- final component visual authority: `resources/css/zazu-final-visual-sweep.css`;
- responsive structure remains in the existing responsive/mobile stylesheets.

Reference evidence:
- complete `reference ui ux images/` collection inspected;
- supplied mobile and desktop degraded screenshots treated as pre-recovery baseline evidence.

Foundation now targets:
- blue corporate page/shell foundation;
- visibly separated blue-tinted light surfaces and layered dark surfaces;
- readable 13.5px+ labels and 14px controls;
- 44px shared control rhythm;
- compact 1320px content measure;
- segmented section tabs;
- physical filled/outlined buttons;
- underlined ordinary links;
- dense readable lists/tables;
- forms treated as one work surface instead of floating field cards;
- mobile tabs retained as a horizontal scroll group;
- no pure-white application backgrounds.

The historical `zazu-final-visual-sweep.css` stack was reduced from approximately 213 KB / 1,263 CSS blocks to a compact canonical layer of approximately 27 KB / 1,100 lines.

Verification:
- CSS brace/source checks pass;
- no rendered-browser claim is made because the user's local `127.0.0.1` runtime is not accessible from this environment;
- screenshot golden baselines remain locked only after human approval of the recovered render.

Future UI agents must modify this canonical system rather than append another visual sweep.

## Director CSS authority stabilization — 2026-10-04

Target: make the existing styling system deterministic before further visual refinement.

Completed on main:
- app.css is now explicitly the canonical owner for design tokens and shared visual components.
- zazu-final-visual-sweep.css was reconstructed as a component-specific extension layer only; all root token systems and generic shared selectors were removed.
- The final sweep's competing light/dark token roots, including the late indigo/purple palette, are no longer active.
- Removed the obsolete hard-coded contrast/form refinement block from app.css (433 lines).
- Removed the obsolete compact navigation override block from app.css (37 lines), including legacy !important navigation shadow rules.
- Removed one stale <=520px page-title override from the mobile stylesheet.
- Added docs/DIRECTOR_CSS_AUTHORITY_AUDIT_2026-10-04.md with the ownership map, findings and verification boundary.

Current source-level stylesheet state:
- app.css: 7878 lines, 29 !important declarations.
- zazu-responsive-theme.css: 523 lines, 3 !important declarations.
- zazu-mobile-refinement.css: 735 lines, 7 !important declarations.
- zazu-final-visual-sweep.css: 3927 lines, 7 !important declarations and zero root token blocks.
- No exact selector duplication remains between the component extension stylesheet and the other three stylesheet files.
- Removed six selectors found only in the former extension CSS: zazu-asset-manifest, zazu-dashboard-telemetry, zazu-report-grid, zazu-supplier-network, zazu-travel-register and zazu-workspace-online.

Verification boundary:
- changed files were re-read from GitHub after writes;
- source-level authority/cascade checks pass;
- rendered browser/Vite runtime is not available through this connector, so no visual acceptance claim is made.

Next Director target:
REBUILD/REAL BROWSER RENDER → DESKTOP + MOBILE + LIGHT + DARK CASCADE CHECK → REFERENCE COMPARISON → ONLY THEN VISUAL REFINEMENT.


## Director UI/UX polish cycle — 2026-10-04

Objective: resolve rendered navigation/content hierarchy defects without another visual rewrite.

Changed:
- resources/css/zazu-responsive-theme.css — desktop nav overflow no longer clips nested flyouts; mobile keeps bounded scrolling.
- resources/css/app.css — hierarchical flyout is physically attached to its trigger, removes hover dead-zone, and keeps hidden panels non-interactive.
- resources/css/zazu-final-visual-sweep.css — removed obsolete 141-line hard-coded dashboard visual override layer and normalized remaining legacy component colours to semantic tokens.

Observed evidence:
- supplied desktop screenshots showed weak dashboard grouping/text-stream presentation and navigation/flyout layering problems.
- source audit confirmed overflow-x clipping and a 7px flyout gap as direct navigation causes.

Verification:
- changed files re-read from main after commits.
- source-level checks confirm desktop flyout is not clipped by nav overflow, mobile remains bounded, and no root token system was added.
- visual acceptance remains open because post-change local browser rendering is unavailable through the repository connector.

Current visual state: IMPLEMENTED / VISUAL UNVERIFIED.
Next target: actual browser render + human-eye comparison of Dashboard, Work, Forms and Calendar in desktop/mobile and light/dark before further broad visual changes.


## Director commercial UI harmonization — 2026-10-04

Objective: stop treating typography, palette, spacing and sizing as isolated fixes and establish one coherent Zazu application rhythm.

Completed on `main`:
- canonical application font/token scale established in `resources/css/app.css`;
- stale @theme blue values aligned to the canonical Zazu blue family;
- 14px body / 11px metadata / 13px nav / 24px page-title hierarchy introduced;
- 8px-based spacing rhythm plus controlled micro steps introduced;
- application shape scale normalised to 6/8/10px;
- 240px sidebar / 64px topbar / 1320px content measure alignment established;
- dashboard and calendar microtype raised out of sub-10px territory;
- dashboard KPI and operational values use UI type + tabular numerics; mono retained for references/identifiers;
- semantic state colours consolidated toward shared success/warning/danger/info tokens;
- mobile content, tabs and action targets refined for touch-safe cross-device use;
- reference collection explicitly treated as design evidence.

Research basis recorded:
- repository reference collection and prior visual baseline;
- Atlassian 8px spacing/token system;
- Carbon productive UI typography/spacing guidance;
- current commercial SaaS density/responsive patterns.

Verification:
- source files re-fetched from GitHub after writes;
- no <=9px font declarations remain in the four application stylesheets;
- no root token system exists in final visual sweep;
- visual state remains IMPLEMENTED / VISUAL UNVERIFIED pending a real browser render.


## Director current UI refinement cycle — 2026-10-04

User-reported defects addressed on the current main branch:

- Account popover was anchoring toward the left edge; it now mirrors the navigation flyout relationship on desktop and opens upward within the mobile shell.
- Zazu Helper now yields to navigation layering, dynamically avoids an intersecting desktop flyout, and becomes non-interactive while the mobile navigation drawer is open.
- Wallpaper visibility was re-hardened after a cascade regression: the application shell is transparent over the wallpaper layer and the page overlay is intentionally translucent rather than opaque.
- Page-introduction rectangles now use blue-tinted Zazu surfaces rather than near-white presentation, with the dashboard explicitly identified as the Dashboard command/title band.
- Calendar was strengthened as an operational surface. Month view remains dense and readable; 3 Months and Year expose holiday names instead of only dots/tooltips; Agenda exposes holiday rows, including holiday-only dates.
- Calendar view controls are now rectangular and visually related to the shell, and dense month layout remains horizontally usable on narrow screens.

Preservation rule:
- This cycle is refinement/hardening only. No working navigation section, workflow, route or UI capability was intentionally removed.
- Existing reference-led blue/nav system remains the governing visual direction.
- Avoid introducing another token root or competing visual stylesheet.

Verification boundary:
- GitHub source re-read after writes; CSS/JS/Blade structural balance checks passed.
- Rendered browser/device acceptance was not available through this environment; visual acceptance remains pending runtime/screenshot verification.


## Director typography / contrast refinement — 2026-10-04

Research basis:
- UXPeak's "Top 5 UX/UI Design Tips and Tricks" emphasizes differentiating information through size, weight, colour and visual cues, and prioritizing important information rather than giving every element equal emphasis.
- Carbon's current typography guidance uses calibrated type scales, weights and line heights to create hierarchy; productive UI uses a 14px base.
- Atlassian's current guidance recommends typography tokens, contrast-aware text colours, clear heading hierarchy and avoiding very small type except for fine print.

Zazu application of those principles:
- Kept the existing single UI font family; did not introduce another competing font.
- Raised normal metadata/label typography from 11px to 12px; 11px is no longer the normal hierarchy level.
- Established stronger title/heading/value separation using size + weight + colour rather than excessive colour changes.
- Kept monospace restricted to identifiers/codes/operational data.
- Strengthened KPI/metric values so the value leads while labels recede.
- Increased supporting text line-height and maintained the existing 14px productive body base.
- Reduced dependence on all-caps as a hierarchy mechanism; retained it only for compact navigational labels where it remains useful.
- Preserved the existing blue-tinted non-white surface system and dark shell.

Acceptance boundary:
- Source-level checks passed after the typography pass.
- Actual browser rendering/device comparison remains required before calling the visual pass fully accepted.


## Director UI recovery — 2026-10-04
- Re-read current `main` before editing; recovery started from `1607f23ccc9272535bf7b722b345fd55d6f5ab96` and preserved the existing application shell and dark-theme token set.
- Dashboard CSS was syntactically balanced but several selectors had drifted from live Blade markup; notably `.zazu-dash-resource-grid > div` targeted children that are actually `<a class="zazu-quick-link">`.
- Helper collision math was inverted: the previous calculation used the flyout left edge and `Math.max`, which could push the Helper into the open navigation popover. It now calculates safe space from the flyout right edge and animates a purposeful avoidance move.
- Added locally bundled Manrope Variable for UI copy and Space Grotesk Variable for display hierarchy. Light mode was shifted to a brighter blue-led summer palette; dark tokens were not changed.
- Strengthened global field spacing, selection contrast, dashboard hierarchy, and branding validation feedback.
- Branding image inputs now receive direct size/type errors; the shared error binding includes branding tiles.
- Acceptance boundary: source-level checks only. Full PHPUnit/Playwright and browser/device screenshot validation still require the Zazu runtime.


## Director UI/UX deep audit and acceptance pass — 2026-10-04

### Baseline
- Canonical repository: `Leano-Jordan/ZazuEMP`
- Canonical branch: `main`
- Starting HEAD for this pass: `724c81b80ee73778e650b49c05ba4060e815fa27`
- Current supplied dashboard renders were inspected before the correction pass.

### Regression risks recorded
- Asset and inventory register rows could collapse useful content into a right-edge action cluster.
- Shared page command banners did not inherit the dashboard workspace-state visual language.
- Business logo sizing lacked a strong identity-zone fit contract.
- Landing navigation controls could drift vertically because the CTA variants did not share one explicit geometry.
- Plan and Delivery lacked an explicit full-width 50/50 placement invariant.
- Landing CSS mixed legacy dark-shell colours into the later light public composition, causing the reported light-on-light and dark-on-dark failures.
- Page UI hierarchy needs readable productive type, deliberate spacing and explicit foreground/surface pairing rather than tiny labels and accidental colour inversion.

### Research basis
- W3C WCAG guidance: meaningful text contrast must meet the applicable minimum for normal/large text, and UI components/state indicators require at least 3:1 contrast against adjacent colours.
- Carbon current typography guidance: productive UI uses a 14px base and controlled sizes, weights and line heights to establish hierarchy.
- Carbon UI-shell guidance: navigation/header controls share a consistent shell structure and predictable sizing.
- Carbon content-switcher guidance: peer controls should use equal-sized containers, reinforcing common control geometry for landing navigation.
- Current SaaS landing references reviewed during this cycle consistently use clear navigation, strong headline hierarchy and a visual product/workspace story rather than mixing unrelated control sizes.

### Changes executed
- Asset/inventory registers now use explicit three-region desktop grids with bounded forms and left-aligned actions, then deliberately collapse at responsive thresholds.
- Shared `.zazu-command-band` now reuses `--zazu-dashboard-image`, adds a dark readability layer and presents its meta block as a workspace-state surface.
- Brand presentation now reserves a 40px identity slot inside a 72px shell row and contains the supplied logo without distortion/cropping.
- Landing navigation uses one 40px control rhythm.
- Plan and Delivery explicitly occupy the final 6-column + 6-column desktop row.
- Landing light surfaces and intentionally dark surfaces now have explicit, separate foreground palettes.

### Acceptance
- Modified source files were re-read from `main` after every write.
- Regression Feature checks now assert the register grid, banner visual contract, landing contrast pairs and Plan/Delivery geometry.
- Navigation/routes/capabilities were preserved.
- No new stylesheet or competing token-root system was introduced.
- Runtime browser/device acceptance is still gated by the repository's CI/browser workflows; this execution surface cannot honestly mark live pixel-level render acceptance complete without those results.


### Final acceptance checkpoint — 2026-10-04

- Latest application commit on `main`: `0e70159559477397dec706514438c8ed697409f6`.
- Post-change source re-read completed for application CSS, landing view, responsive CSS, regression tests and Director records.
- CSS brace/parenthesis balance checks passed for the modified stylesheets/views.
- GitHub Actions for the latest commit were observed at checkpoint: Zazu quality, Laravel, Psalm Security Scan, Zazu populated upgrade and rollback, PHPMD, Zazu browser smoke and Push on main were queued; SonarCloud was skipped.
- Therefore the Director state is **SOURCE-ACCEPTED / CI-PENDING**, not CI-GREEN. No browser workflow is being represented as passed until GitHub reports a completed conclusion.

### Director recovery checkpoint — 2026-10-04T23:47 SAST

- Target: authenticated mobile Services & Prices UI.
- Evidence: supplied Chrome screenshot showing wrapped/fragmented mobile header, detached page action, catalogue command text visually touching its boundary, and dark catalogue-tab text on a dark surface.
- Root cause confirmed in source history: commit 709dd6c4 introduced a large mobile-shell override stack; later refinements added overlapping header grids and absolute action positioning instead of one composition contract.
- Correction: consolidated resources/css/zazu-mobile-refinement.css into one explicit mobile header grid with title/search/theme, horizontally scrollable section navigation, and a dedicated page-action row.
- Correction: hardened resources/css/zazu-final-visual-sweep.css with explicit dark catalogue-tab foregrounds and catalogue command inner padding.
- Regression protection: added InterfaceRegressionTest coverage for the canonical mobile grid and dark catalogue readability contract.
- Source verification: passed; legacy mobile grid, 56px action offset and 112px reserved header height are absent; canonical header and readability contracts are present.
- Visual acceptance: PENDING. The supplied screenshot is pre-fix evidence; local browser rendering is not exposed to this execution surface.
- Next gate: rebuild Vite assets, hard refresh the local phone view, inspect header/tab/banner at the target viewport, then check desktop + mobile and both themes before another broad UI batch.


### Director commercial UI harmonisation recovery — 2026-10-05

- Benchmarked current UI direction against Carbon search guidance and Linear contextual command-menu patterns; retained Zazu's own blue operational language rather than copying either.
- Light mode is now deliberately blue-slate rather than white: richer page canvas, cool elevated surfaces, blue-tinted header and blue-toned dark rail.
- Existing card primitive owns the subtle glass treatment; later visual sweep no longer duplicates `.zazu-card` glass rules.
- Dashboard operational surfaces also receive the same restrained glass/depth treatment.
- Dashboard hero image overlay was reduced from the previous heavy darkening to a lighter 43% lower gradient while preserving white-on-image text.
- Primary actions have stronger CTA geometry/depth; dashboard job entry has explicit `.zazu-btn-cta` semantics.
- Mobile search is now a full-width focused search row with integrated shortcut affordance and a contextual command palette below it; the icon-only compressed treatment was removed.
- Regression tests were expanded for palette, glass, CTA and mobile-search contracts.
- Visual state: IMPLEMENTED / VISUAL UNVERIFIED until the local runtime is rendered at desktop/mobile in both themes.

### Director search/Helper regression recovery — 2026-10-05

- Current root cause: desktop search was allowed to participate in intrinsic header sizing; mobile contained a duplicate fixed-position command-palette authority; Helper JavaScript moved its fixed control horizontally when nav flyouts opened.
- Recovery: search is now width-contained and flex-shrinking inside desktop utility chrome; mobile palette is anchored to the search row only; Helper is a compact fixed dock with lower stacking and no collision-repositioning code.
- Mobile header order is now title row → search utility → section navigation → page actions.
- Current visual state: IMPLEMENTED / SOURCE-VERIFIED / VISUAL-UNVERIFIED.
- No CI status checks are attached to the latest code commit; actual browser render remains mandatory before visual acceptance.


### Director visual recovery / customer flow checkpoint — 2026-10-05

- Root causes confirmed: late generic page-surface CSS flattened the shared dark image banner; desktop search intrinsic width could redistribute the shell; mobile had stale palette positioning; Helper had no active Work/Sales yield state after generic collision logic was removed.
- Protected good improvements: blue-slate light mode, subtle canonical glass cards, bounded search, intentional CTA hierarchy, lighter dashboard hero treatment.
- Helper contract: mascot size remains unchanged; only Work and Sales desktop popovers trigger a right-of-popover movement using the measured active panel edge. Other navigation areas leave it alone.
- Customer contract: quick-created customer receives a permissioned edit URL; the current job surface exposes Complete customer profile after creation. Full editor remains the authoritative update surface.
- Form contract: customer identity → primary contact → business/billing/tax details.
- Dialog contract: customer quick-add is explicitly centered with viewport bounds.
- Visual acceptance: SOURCE-VERIFIED; rendered browser acceptance remains a separate gate.

## Director calendar navigation recovery — 2026-10-05

Target: restore direct Calendar reachability and protect the recent navigation/UI refinements.

Completed:
- confirmed the failure was not Calendar routing, data, or permission registration;
- traced the dead end to the Work navigation group's stale active-route predicate after Calendar was moved under Work;
- restored `calendar.*` to the Work active-state contract;
- added feature regression coverage proving Calendar marks Work active and the Calendar link is the current page;
- added browser regression coverage proving the active Work group exposes Calendar and auto-expands on mobile;
- recorded persistent failure case `CASE-ZAZU-0010` and regression control `REG-020`.

Preserved:
- Calendar remains intentionally under Work;
- existing calendar visual refinement, holiday presentation and dense information hierarchy are unchanged;
- existing blue-slate visual direction, bounded search, restrained glass surfaces, intentional CTA hierarchy and Helper Work/Sales yield contract are not replaced by another visual sweep.

Verification boundary:
- source changes and regression coverage are committed on `main`;
- rendered browser/device execution is not available through the current repository connector, so visual acceptance remains a separate runtime gate.

Next target:
CURRENT-HEAD BROWSER/DEVICE ACCEPTANCE → RE-AUDIT NAVIGATION + CALENDAR + RECENT UI RECOVERY CONTRACTS

## Director UI integrity recovery — 2026-10-05

Target: resolve the recurring Calendar lockout without another symptom patch, then harden the Zazu visual system against cross-page/cross-theme drift.

Completed in this cycle:
- Calendar remains owned by Work and Work now recognizes `calendar.*` as an active route.
- Mobile/tablet navigation breakpoint contracts are aligned at 850px across CSS and JavaScript.
- Active Work has a server-rendered flyout recovery path so Calendar remains reachable if JavaScript fails or stale assets load.
- Calendar's optional holiday-preference schema is now non-blocking; stale local databases without that optional column no longer have to take Calendar down.
- Staff/manager Calendar permission coverage is explicitly regression-tested.
- Calendar CSS was consolidated from competing canonical/late blocks into one visual authority.
- Helper CSS was consolidated from multiple competing root declarations into one visual authority while retaining the protected Work/Sales movement behavior.
- Dark destructive-action contrast was corrected and hard-coded white foregrounds on theme-variable Calendar actions were removed.
- Offline mobile surface contrast was corrected so its dark shell no longer contains light-on-light hero/notice content.
- Cross-theme UI regression coverage now protects the known light-on-light/dark-on-dark failure family.

Protected UI direction:
- blue-slate light mode;
- restrained glass/depth cards;
- bounded search;
- intentional CTA hierarchy;
- dark image-led command/banner surfaces with light foregrounds;
- Calendar information density and holiday visibility;
- Helper Work/Sales yield behavior;
- no competing token roots or generic late visual sweeps.

Failure control:
- `CASE-ZAZU-0010` records the first navigation-state recurrence.
- `CASE-ZAZU-0011` is the current owner-reported Calendar lockout recurrence and remains INVESTIGATING until authenticated runtime evidence reproduces and closes the failure.
- `REG-020` protects navigation ownership/active-state/responsive auto-expansion as one contract.
- `REG-021` protects cross-theme foreground/surface relationships.

Verification boundary:
- Changed source and control documents are committed to `main` and re-read.
- Current repository connector does not expose a usable CI result for the latest commits.
- Browser/device rendering remains the required closure gate for the owner's live lockout and final visual acceptance; no runtime success is being claimed.

Next Director target:
CURRENT-HEAD AUTHENTICATED CALENDAR RUNTIME REPRODUCTION → FULL DESKTOP/MOBILE/TABLET UI CONTRAST + NAVIGATION SWEEP → FINAL REGRESSION RE-AUDIT
