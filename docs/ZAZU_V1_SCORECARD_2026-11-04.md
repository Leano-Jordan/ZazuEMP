# Zazu EMP — V1 Specification Scorecard

**Assessment date:** 2026-11-04  
**Repository:** `Leano-Jordan/ZazuEMP`  
**Branch:** `main`  
**Assessment HEAD:** `d1e2a4397b064d3fbfe7fc2cf1dc59236e00922f`  
**Evidence status:** exact source-head CI mostly passed; browser smoke still running; not release certification.

## Scoring method

Each area is scored from 0–5 for evidence maturity, not lines of code or visual completeness:

- **0** — absent
- **1** — specified / designed
- **2** — implemented in source
- **3** — automated tests exist
- **4** — relevant automated tests passed on a recent exact source head
- **5** — repeatedly proven in realistic customer/production/recovery use

The overall score is an unweighted average. A high average cannot override a Critical release blocker.

## V1 scorecard

| V1 specification area | Score | Current assessment |
|---|---:|---|
| Platform, accounts, onboarding | **3/5** | Registration, authentication, business context and onboarding are implemented with browser/feature coverage. Latest-head browser verification is pending. |
| Core lifecycle: client → event → work → completion | **3/5** | Connected operational domain and populated demo scenario exist. Current exact-head test execution is pending. |
| Commercial workflow: quote → acceptance → invoice → deposit → payment | **4/5** | Versioned quotes, signed acceptance, invoice/payment flows and populated reconciliation tests are present; recent Laravel and quality runs passed on the immediately preceding audit head. |
| Purchasing, receiving, inventory, costs | **4/5** | Supplier, PO, receiving, stock movement and event-cost foundations are connected and covered by populated/feature tests. |
| Data integrity and auditability | **4/5** | Transactions, idempotency, business-parent constraints, audit records and adversarial tests are present. Broader real concurrency proof remains incomplete. |
| Security and tenant isolation | **3/5** | Server-side permissions, business scoping, private media controls and sync-token controls exist. Offline account/session boundary and CSP hardening remain open review items. |
| Offline continuity | **3/5** | Business-scoped IndexedDB workspace, durable mutation queue, reconnect sync and explicit conflict handling exist for a bounded set of workflows. Full disconnected parity is not promised or proven. |
| Documents and attachments | **2/5** | Online private attachments and active Work phone-local queue/upload/download/reconnect safeguards are implemented; document metadata search is business-scoped. Automated Laravel/browser coverage passes. Physical installed-device recovery and real data/media restore remain unproven, so the maturity score is held. |
| Search, reporting and Zazu Helper | **3/5** | Permission-aware search, essential reporting and contextual Helper foundation exist. Full end-to-end usability and acceptance evidence is incomplete. |
| UX, mobile, tablet and accessibility | **3/5** | Responsive foundations and browser coverage exist; physical acceptance was previously owner-reported. Latest-head critical-flow and accessibility sweep remain outstanding. |
| Recovery and data portability | **2/5** | Installation-level CLI backup/restore and automated round-trip/hostile-archive tests exist. Business self-service export/restore is not implemented; real operator recovery drill remains open. |
| Deployment, upgrade and rollback | **4/5** | Populated upgrade/rollback automation passed on the immediately preceding source head. Real deployment rollback and target-environment acceptance remain open. |
| Legal, privacy and brand clearance | **1/5** | Draft controls and registers exist. Operational POPIA/legal sign-off and Zazu trade-mark clearance remain open. |

**Unweighted evidence-maturity average: 3.0/5 — 60/100.**

**2026-11-08 reconciliation:** the automated application/test batch now passes on `d44ec33` (407 Laravel tests / 2,758 assertions; 65 browser tests passed, 22 skipped). The 60/100 baseline is deliberately unchanged until a full independent 13-domain re-audit and the physical/recovery/legal/brand gates close.

This is not “60% of the software built”. It is an unweighted summary of how far the V1 specification is supported by implementation and evidence. The product is not release-certified.

## Release blockers

1. **Recovery scope:** the former business settings routes exposed installation-wide backup/restore commands. Those routes and UI controls have now been removed. CLI recovery is operator-only. Tenant-scoped self-service export/restore remains unimplemented.
2. **Real recovery drill:** automated command tests do not prove a real backup → restore → data/media/security verification on the intended deployment.
3. **Offline acceptance:** bounded offline workflows need repeatable installed-device disconnect/restart/reconnect tests. Phone-local attachment sync remains unimplemented.
4. **Security review:** complete the offline account/session transition challenge and CSP nonce/hash migration plan.
5. **Current-head verification:** Laravel, quality, Psalm, PHPMD, populated upgrade/rollback and push-on-main passed for the assessed source SHA. Browser smoke is still running; SonarCloud was skipped and is not counted as a pass.
6. **Legal and brand:** complete operational privacy/terms controls and trade-mark clearance before commercial release.
7. **Deployment:** exercise upgrade and rollback on the selected paid persistent server when that environment is funded and available. The development PC is not the production target.

## V1 scope decisions

- Keep the primary commercial spine first: **Event → Quote → Deposit → Invoice → Payment → Purchasing → Receiving → Costs → Profit → Export**.
- Do not expand advanced team management, predictive finance, enterprise BI, multi-location or autonomous AI ahead of core reliability.
- Do not claim full offline parity. Support only the declared device-local workflows until broader parity is implemented and proven.
- Do not describe installation-wide backup/restore as a business-level capability.
- Do not use the 2027 hardening scorecard as a substitute for this V1 release scorecard.

## Next execution order

1. Observe and repair exact-head CI for `d1e2a4397b064d3fbfe7fc2cf1dc59236e00922f`.
2. Design a tenant-scoped export/import format or explicitly define operator-managed recovery as the V1 commercial boundary.
3. Close the highest-risk offline account/session boundary and phone-local attachment integration decision.
4. Run populated commercial reconciliation and real recovery/rollback acceptance.
5. Close legal/privacy and brand clearance.
6. Perform final V1 release certification.

**Director decision: V1 remains NO-GO for commercial release.** The core product is substantial, but recovery, offline acceptance, security review, legal/brand and deployment evidence are not yet closed.


## Exact source-head CI reconciliation — 2026-11-04

Application source SHA: `d1e2a4397b064d3fbfe7fc2cf1dc59236e00922f`.

| Check | Result |
|---|---|
| Laravel | PASS — [run 41727830781](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41727830781) |
| Zazu quality (build, tests and secret scan) | PASS — [run 41727830645](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41727830645) |
| Psalm security scan | PASS — [run 41727830698](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41727830698) |
| PHPMD | PASS — [run 41727830700](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41727830700) |
| Populated upgrade and rollback | PASS — [run 41727830659](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41727830659) |
| Push on main / CodeQL | PASS — [run 41727830881](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41727830881) |
| Browser smoke | IN PROGRESS at reconciliation — [run 41727830682](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41727830682) |
| SonarCloud | SKIPPED — [run 41727830643](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41727830643); not counted as a pass |

The documentation-only commits after `d1e2a439` do not change application source. Browser smoke remains the only observed pending required check for this source SHA. Do not mark the candidate fully CI-verified until it concludes.
