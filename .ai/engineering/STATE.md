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

## Current next-target rule

Select the highest-risk unresolved item that is:
1. actionable now;
2. materially reducing release risk;
3. supported by current evidence;
4. bounded enough to execute safely.

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

Last updated: 2026-10-01


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
