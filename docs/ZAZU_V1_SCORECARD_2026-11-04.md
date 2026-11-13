## Director formal re-score — 2026-11-13

**Verified application/test source:** `30e259d445c614df6d1b2495c5a5e505a6d36443` on `main`. Documentation-only reconciliation followed; no application/test source changed after this verified source head.

**Evidence:** Laravel workflow [43504654649](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43504654649) completed successfully. Laravel feature/unit suite, PostgreSQL migration plus database/private-file backup/restore smoke, and dedicated offline-browser acceptance all passed. The offline-browser run includes the new disconnected Work-attachment page-reload persistence assertion. The earlier service-worker navigation regression run [43504020001](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43504020001) also passed the 503 cached-page fallback and 401/403 non-fallback cases.

### Current 13-domain scorecard

| V1 specification area | Score | Evidence and remaining boundary |
|---|---:|---|
| Platform, accounts, onboarding | **4/5** | Laravel and browser regression suites pass; real customer onboarding acceptance remains. |
| Core lifecycle: client → event → work → completion | **4/5** | Populated lifecycle regression coverage passes; realistic operator acceptance remains. |
| Commercial workflow: quote → acceptance → invoice → deposit → payment | **4/5** | Seeded quote/payment/cost/profit reconciliation passes; real business-record reconciliation remains. |
| Purchasing, receiving, inventory, costs | **4/5** | Purchasing, receiving, stock and event-cost regression coverage passes; physical stock/GRN reconciliation remains. |
| Data integrity and auditability | **4/5** | Transaction, idempotency, ownership, archive relationship-remapping and tenant-isolation tests pass; real concurrency proof remains. |
| Security and tenant isolation | **4/5** | Psalm, secret scan and Laravel tenant/session regressions pass; physical trusted-device/lost-device acceptance remains. |
| Offline continuity | **3/5** | Business-scoped IndexedDB workspace, durable queue, reconnect, cached navigation and disconnected document-reload recovery are automated-verified; real Android cold-launch and supported workflow acceptance remain. This is not full offline parity. |
| Documents and attachments | **4/5** | Active Work attachment queue, IndexedDB file persistence across page reload, reconnect upload, idempotency and tenant/job scoping pass browser acceptance; physical force-close/reopen persistence remains. |
| Search, reporting and Zazu Helper | **3/5** | Permission-aware search and reporting foundations exist; complete customer-data usability acceptance remains. |
| UX, mobile, tablet and accessibility | **3/5** | Responsive browser coverage exists; physical device and complete accessibility acceptance remain. |
| Recovery and data portability | **4/5** | Tenant-scoped `.zazu` business archive round-trip, linked-record remapping, tenant exclusion and private/public file payloads pass CI; PostgreSQL installation backup/private-file restore passes CI. Actual installed-target operator recovery drill remains. |
| Deployment, upgrade and rollback | **4/5** | Populated upgrade/rollback automation passes; real target-environment deployment rollback remains. |
| Legal, privacy and brand clearance | **1/5** | Operational POPIA/privacy controls, retention and incident procedures, and official Zazu name clearance remain open. |

**Evidence-maturity average: 46/65 = 3.54/5 — 70.8/100, reported as 71/100.**

**Change from prior formal score: +2 points (69 → 71).** The sole numerical uplift is Recovery and data portability, from 3/5 to 4/5, based on passing tenant-scoped archive round-trip/tenant-isolation and installation backup/private-file recovery automation. Offline continuity remains 3/5: browser evidence improved, but the Android installed-device gap has not been physically verified.

### Release disposition

**V1 remains NOT READY.** Remaining owner/operational gates are secure-origin Android install and disconnected cold-launch/workflow acceptance; actual installed-target recovery drill; live commercial record reconciliation; and POPIA/privacy/brand closure. CI evidence does not substitute for those checks. No production deployment or live customer data was changed.

---

# Zazu EMP — V1 Specification Scorecard

**Original baseline assessment:** 2026-11-04  
**Current re-score:** 2026-11-09  
**Repository:** `Leano-Jordan/ZazuEMP`  
**Branch:** `main`  
**Current verified source/test HEAD:** `d55539ddfd52c7fa5140714288c5885dbcc99e3a`  
**Evidence status:** required current-head automated CI passed; physical acceptance remains open; not release certification.

## Historical formal re-score — 2026-11-09

**Current verified application/test HEAD:** `d55539ddfd52c7fa5140714288c5885dbcc99e3a`  
**Evidence:** exact-head Laravel, browser, quality, security, static-analysis and populated upgrade/rollback checks passed. Director Contract Lint passed on the preceding control-plane documentation commit and was not triggered by this test-only source commit. This is an evidence-maturity re-score, not a commercial release approval.

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
| Documents and attachments | **4/5** | Active Work attachment queue, private retrieval, business scoping, reconnect/idempotency and private-media backup/restore have automated coverage; physical device persistence remains unverified. |
| Search, reporting and Zazu Helper | **3/5** | Permission-aware search, attachment metadata search and reporting exist; full customer-data usability acceptance remains incomplete. |
| UX, mobile, tablet and accessibility | **3/5** | Browser smoke passes across its configured viewport projects; physical device and complete accessibility acceptance remain. |
| Recovery and data portability | **3/5** | A populated seeded installation restore now verifies core workflow record counts, business/event/invoice values and private media in CI; a real operator restore on the installed target remains open. |
| Deployment, upgrade and rollback | **4/5** | Populated upgrade/rollback automation passes on the current head; real target-environment deployment rollback remains. |
| Legal, privacy and brand clearance | **1/5** | Engineering controls and registers exist; operational POPIA/privacy/legal sign-off and official Zazu name clearance remain open. |

**Current evidence-maturity average: 3.46/5 — 69.2/100, reported as 69/100.**  
**Change from prior formal baseline: +9 points (60 → 69).**

Five domains have now improved against the historical baseline: platform/onboarding, core lifecycle, security/tenant controls and active documents/attachments have stronger exact-head regression evidence; recovery/data portability has moved from 2/5 to 3/5 after the populated database-and-private-media restore drill passed in CI. Real installed-environment restore and physical acceptance remain open. No domain receives a score of 5.

### Exact-head CI evidence

| Check | Result |
|---|---|
| Laravel | PASS — 414 tests, 2,938 assertions — [run 42702419420](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42702419420) |
| Browser smoke | PASS — 65 passed, 22 skipped, 0 failed/flaky — [run 42702419540](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42702419540) |
| Zazu Quality + secret scan | PASS — [run 42702419210](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42702419210) |
| Psalm Security | PASS — [run 42702419299](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42702419299) |
| PHPMD | PASS — [run 42702419074](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42702419074) |
| Populated upgrade/rollback | PASS — [run 42702419767](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42702419767) |
| Director Contract Lint | PASS on preceding control-plane docs commit; not triggered by this test-only commit — [run 42700325807](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42700325807) |
| Push on main | PASS — [run 42702420093](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42702420093) |
| SonarCloud | SKIPPED — not counted as a pass |

### What the score does not certify

V1 remains **NO-GO** until the installed-device offline journey is accepted on a stable secure origin; a representative database and private-media restore is demonstrated on the actual target installation; a real deployment rollback is exercised; trusted-device/lost-device expectations are accepted; and privacy/legal plus brand clearance are closed. The local HTTP LAN launcher improves same-network access but does not satisfy the secure-origin PWA gate.

The previous 60/100 score remains the historical 2026-11-04 baseline below. This new score is the current formal evidence-maturity assessment.

---

## Historical reconciliation — 2026-11-08

The 2026-11-04 scorecard below is retained as the last formally calculated 13-domain baseline (**3.0/5, 60/100**); it has not been numerically recalculated. Several implementation statements in the original assessment are now superseded:

- **Phone-local Work attachments:** the active authenticated Work panel now integrates business-scoped IndexedDB persistence, offline file queueing, idempotent reconnect upload, private attachment caching and offline-created job identity reconciliation. This is IMPLEMENTED and the new Laravel/Playwright coverage passed on the verified application/test source.
- **Trusted-device transition:** unreachable-host logout preservation and owner revocation coverage have been added and passed automated verification.
- **Backup integrity:** version-2 checksum validation and version-1 compatibility coverage passed in the current Laravel suite. Checksums do not prove archive authenticity.
- **Exact-head CI:** application/test source `d44ec33a57a4958380f2cfe4624f040859cbcf7a` passed Laravel (407 / 2,758 assertions), Quality, browser smoke (65 passed / 22 skipped / 0 failed), Psalm, PHPMD and populated upgrade/rollback. The Quality workflow bootstrap correction is recorded in workflow-only commit `7946a87e78cf4dd2305d12fb20d3f77ed5653c87`. SonarCloud was skipped.
- **Still not proven:** physical phone/tablet disconnected launch and restart, full create-offline-job → sync → attach journey on real devices, representative operator restore, deployment rollback, legal/privacy operational closure and official brand clearance.

The original rows and blocker statements below describe the 2026-11-04 assessment date. Read this reconciliation and the current `docs/V1_RELEASE_CHECKLIST.md` first when determining today's status. The current 2026-11-09 formal re-score above supersedes this historical note.

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


## Security hardening checkpoint — 2026-11-13

- **No score inflation:** security remains 4/5 and overall evidence maturity remains 71/100. These changes improve verified controls; they do not constitute an independent penetration test.
- Found and closed an authorization gap in customer profile media: the endpoint enforced business membership but did not also require the existing `customers.view` permission. `ProfileMediaController` now enforces that permission and `ProfileMediaSecurityTest` proves a business member without it receives 403. The regression passed on main: [Laravel run 43508450343](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43508450343) — 487 passed, 3,476 assertions, 1 skipped.
- Added locked dependency auditing to CI: Composer audit and npm audit. The old lockfile exposed vulnerable Laravel/Symfony versions. Updated to Laravel `v13.42.0`, Symfony HttpFoundation/HttpKernel/Routing `v8.1.11`, and Symfony Mime `v8.1.10`.
- Dependency remediation merged through [PR #54](https://github.com/Leano-Jordan/ZazuEMP/pull/54), merge commit `637561c53dedbe31d3de0e38e205200e3d5e3301`.
- Post-upgrade evidence on the PR head: [Laravel 487 passed / 3,476 assertions / 1 skipped](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43508848942), [Composer + npm quality/security audit PASS](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43508848964), [Psalm security PASS](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43508848909), [PHPMD PASS](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43508848991), [PostgreSQL migration and recovery PASS](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43508848942), and [populated upgrade/rollback PASS](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43508848911).
- The offline-browser job remained in progress at this checkpoint and is not counted as a security pass; offline engineering remains frozen by owner direction.
- Automated dependency refresh is available manually and scheduled weekly. Main remains protected; dependency updates are submitted through PRs, not direct pushes.
- Remaining security boundary: no independent external penetration test has been performed. Keep V1 security at 4/5 until operational review and privacy/legal controls are closed.
