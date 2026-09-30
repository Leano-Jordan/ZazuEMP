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

Last updated: 2026-09-28


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
