# Zazu EMP — V1 Specification Scorecard

**Assessment date:** 2026-11-04  
**Repository:** `Leano-Jordan/ZazuEMP`  
**Branch:** `main`  
**Assessment HEAD:** `d1e2a4397b064d3fbfe7fc2cf1dc59236e00922f`  
**Evidence status:** exact source-head CI mostly passed; browser smoke still running; not release certification.

## Director formal re-score — 2026-11-09

**Current verified application/test HEAD:** `69040420caa49bf9c514699c97fb8f8c5148d03b`  
**Evidence:** exact-head Laravel, browser, quality, security, static-analysis, populated upgrade/rollback and Director checks passed. This is an evidence-maturity re-score, not a commercial release approval.

### Current 13-domain scorecard

| V1 specification area | Score | Evidence and remaining boundary |
|---|---:|---|
| Platform, accounts, onboarding | **4/5** | Laravel and browser regression suites pass on the current head; real customer onboarding acceptance remains. |
| Core lifecycle: client → event → work → completion | **4/5** | Populated lifecycle and commercial regression coverage passes in the current Laravel suite; realistic operator acceptance remains. |
| Commercial workflow: quote → acceptance → invoice → deposit → payment | **4/5** | Populated quote, invoice, payment and reconciliation coverage remains green; real customer financial reconciliation/concurrency drill remains. |
| Purchasing, receiving, inventory, costs | **4/5** | Purchasing, receiving, stock and event-cost regression coverage remains green; physical stock/GRN reconciliation remains. |
| Data integrity and auditability | **4/5** | Transaction, idempotency, ownership, audit and hostile-input tests pass; broader real concurrency proof remains. |
| Security and tenant isolation | **4/5** | Psalm, secret scan and Laravel security/tenant regression coverage pass; trusted-device physical acceptance and CSP style hardening remain. |
| Offline continuity | **3/5** | Bounded IndexedDB workspace, queue, conflict and reconnect paths are implemented and tested; secure-origin installed-device disconnect/restart/reconnect proof remains. |
| Documents and attachments | **4/5** | Active Work attachment queue, private retrieval, business scoping and reconnect/idempotency paths have automated Laravel/browser coverage on the current head; physical device persistence and real media restore remain. |
| Search, reporting and Zazu Helper | **3/5** | Permission-aware search, attachment metadata search and reporting exist; full customer-data usability acceptance remains incomplete. |
| UX, mobile, tablet and accessibility | **3/5** | Browser smoke passes across its configured viewport projects; physical device and complete accessibility acceptance remain. |
| Recovery and data portability | **2/5** | Operator backup/restore commands, checksums and automated archive tests exist; representative populated database/private-media restore remains unproven. |
| Deployment, upgrade and rollback | **4/5** | Populated upgrade/rollback automation passes on the current head; real target-environment deployment rollback remains. |
| Legal, privacy and brand clearance | **1/5** | Engineering controls and registers exist; operational POPIA/privacy/legal sign-off and official Zazu name clearance remain open. |

**Current evidence-maturity average: 3.38/5 — 67.7/100, reported as 68/100.**  
**Change from prior formal baseline: +8 points (60 → 68).**

The uplift is limited to four domains where the current exact-head automated evidence now meets the score-4 definition: platform/onboarding, core lifecycle, security/tenant controls and active documents/attachments. The other domains retain their prior scores because physical, customer, recovery or legal evidence is still missing. No domain receives a score of 5.

### Exact-head CI evidence

| Check | Result |
|---|---|
| Laravel | PASS — 412 tests, 2,904 assertions — [run 42698440540](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42698440540) |
| Browser smoke | PASS — 65 passed, 22 skipped, 0 failed/flaky — [run 42698440241](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42698440241) |
| Zazu Quality + secret scan | PASS — [run 42698440553](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42698440553) |
| Psalm Security | PASS — [run 42698440534](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42698440534) |
| PHPMD | PASS — [run 42698440505](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42698440505) |
| Populated upgrade/rollback | PASS — [run 42698440537](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42698440537) |
| Director Contract Lint | PASS — [run 42698440506](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42698440506) |
| Push on main | PASS — [run 42698440294](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42698440294) |
| SonarCloud | SKIPPED — not counted as a pass |

### What the score does not certify

V1 remains **NO-GO** until the installed-device offline journey is accepted on a stable secure origin; a representative database and private-media restore is demonstrated; a real deployment rollback is exercised; trusted-device/lost-device expectations are accepted; and privacy/legal plus brand clearance are closed. The local HTTP LAN launcher improves same-network access but does not satisfy the secure-origin PWA gate.

The previous 60/100 score remains the historical 2026-11-04 baseline below. This new score is the current formal evidence-maturity assessment.

---

## Historical reconciliation — 2026-11-08

The 2026-11-04 scorecard below is retained as the last formally calculated 13-domain baseline (**3.0/5, 60/100**); it has not been numerically recalculated. Several implementation statements in the original assessment are now superseded:

- **Phone-local Work attachments:** the active authenticated Work panel now integrates business-scoped IndexedDB persistence, offline file queueing, idempotent reconnect upload, private attachment caching and offline-created job identity reconciliation. This is IMPLEMENTED and the new Laravel/Playwright coverage passed on the verified application/test source.
- **Trusted-device transition:** unreachable-host logout preservation and owner revocation coverage have been added and passed automated verification.
- **Backup integrity:** version-2 checksum validation and version-1 compatibility coverage passed in the current Laravel suite. Checksums do not prove archive authenticity.
- **Exact-head CI:** application/test source `d44ec33a57a4958380f2cfe4624f040859cbcf7a` passed Laravel (407 / 2,758 assertions), Quality, browser smoke (65 passed / 22 skipped / 0 failed), Psalm, PHPMD and populated upgrade/rollback. The Quality workflow bootstrap correction is recorded in workflow-only commit `7946a87e78cf4dd2305d12fb20d3f77ed5653c87`. SonarCloud was skipped.
- **Still not proven:** physical phone/tablet disconnected launch and restart, full create-offline-job → sync → attach journey on real devices, representative operator restore, deployment rollback, legal/privacy operational closure and official brand clearance.

The original rows and blocker statements below describe the 2026-11-04 assessment date. Read this reconciliation and the current `docs/V1_RELEASE_CHECKLIST.md` first when determining today's status. No score uplift is claimed until a fresh full-domain evidence review is completed.

## Scoring method

Each area is scored from 0–5 for evidence maturity, not lines of code or visual completeness:

- **0** — absent
- **1** — specified / designed
- **2** — implemented in source
- **3** — automated tests exist
- **4** — relevant automated tests passed on a recent exact source head
- **5** — repeatedly proven in realistic customer/production/recovery use

The overall score is an unweighted average. A high average cannot override a Critical release blocker.

## Historical V1 scorecard baseline — 2026-11-04

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
