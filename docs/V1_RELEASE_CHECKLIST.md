# Zazu EMP — V1 Release Gate Ledger

## Director product audit — 2026-11-09

Current comprehensive audit: `docs/DIRECTOR_PRODUCT_AUDIT_2026-11-09.md`.

- V1 remains **NO-GO**. The last formal 60/100 baseline is historical and has not been re-scored.
- Current app/test candidate: `285fa18aa856539b0a1bbeef563aebd7138253f6`; exact-head Laravel, Quality, PHPMD, Psalm, browser and populated upgrade/rollback workflows are pending/in progress. Do not carry forward older green results as current.
- Current PC/phone development access uses a temporary LAN address and plain HTTP. Establish a stable private HTTPS origin before physical PWA offline acceptance; preserve/reconcile origin-scoped IndexedDB queues before migration.
- Physical PC/mobile offline acceptance, real restore/rollback drills, trusted-device security, operational privacy/legal closure and official Zazu brand clearance remain open.

**Status:** ACTIVE / CANONICAL  
**Repository:** `Leano-Jordan/ZazuEMP`  
**Branch:** `main`  
**Main-ref rule:** query the live `main` ref; this ledger does not pin a moving HEAD.  
**Last Director reconciliation:** 2026-11-08  
**Latest verified application/test source SHA:** `d44ec33a57a4958380f2cfe4624f040859cbcf7a`. A documentation-only reconciliation may follow; the application/test source remains this exact verified SHA.

## Evidence rule

**Implemented ≠ Automated ≠ Runtime-verified ≠ Physically accepted ≠ Release-certified.**

This ledger records the current release truth. Older Director audits remain historical evidence; they do not override this snapshot.

## Current V1 reconciliation — 2026-11-08

**Release position: NO-GO.** All required automated CI gates have passed on the same application/test source SHA. This closes the current automated source-verification batch, not the operational release gates. Installed-device offline acceptance, a real operator restore/rollback exercise, legal/privacy closure and official Zazu brand clearance remain open.

**Validated application/test source commit:** `d44ec33a57a4958380f2cfe4624f040859cbcf7a`  
**Canonical branch:** `main`.

| Area | Current source/test position | Evidence state |
|---|---|---|
| Core application and UI regression | Dark-theme authority restored; Finance header search is bounded where page actions exist; logout health probe uses the supported GET path; visible offline form controls and report/inspector assertions now match the user-facing workflow | IMPLEMENTED; Laravel and browser smoke PASS |
| Active Work attachments | Business-scoped local queue, atomic batch save, idempotent reconnect upload, private list/download caching and offline job-ID reconciliation are integrated into active Work | IMPLEMENTED; Laravel and browser smoke PASS; installed-device proof remains |
| Global document search | Existing job attachments are searchable by filename, description, MIME type and linked job metadata; search returns the job attachment section, is business-scoped and does not expose private storage paths | IMPLEMENTED; Laravel PASS |
| Trusted-device lifecycle | Owner revocation, 180-day rolling inactivity expiry, automatic extension on authenticated sync and expired-token re-pair enforcement | Laravel PASS; physical device acceptance remains |
| Backup / restore | Format-v2 SHA-256 manifest covers database and private files; restore validates payload set before installation mutation; legacy v1 remains supported | Automated Laravel coverage PASS; real operator restore drill OPEN |
| Commercial workflow | Quote acceptance, invoice, deposit/final payment, purchase receiving, actual event cost, completion and event profitability are covered by populated workflow regression | Laravel PASS; full operational/customer deployment proof remains |
| Event profitability | Reports reconcile issued/paid invoice value to actual event costs by event and currency; draft/void invoices and planned/cancelled costs are excluded | IMPLEMENTED; Laravel PASS |
| Business data export | 23 CSV datasets; owner-only, business-scoped, streamed, audited, formula-injection protected and Excel-compatible | IMPLEMENTED; Laravel and Quality PASS; records-only, not a restorable database backup |
| Security | Psalm security scan and secret scan pass; paired-device credentials have rolling idle expiry; CSP inline script/style and broader adversarial review remain | AUTOMATED SECURITY GATES PASS; additional hardening/acceptance open |
| Deployment / recovery | Populated existing-database upgrade and rollback workflow passes; no production deployment rollback drill or representative customer restore is recorded | AUTOMATED UPGRADE/ROLLBACK PASS; operational drills OPEN |
| Legal / privacy / brand | Retention/disposal ownership, incident escalation, customer-facing privacy/terms decisions and official Zazu name clearance remain outstanding | OPEN / RELEASE BLOCKER |

### Exact-source automated verification

All results below apply to application/test source commit `d44ec33a57a4958380f2cfe4624f040859cbcf7a`:

| Check | Result |
|---|---|
| Laravel feature suite | 🟢 PASS — 407 tests, 2,758 assertions — [run 42424637142](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42424637142) |
| Zazu quality (application/build + secret scan) | 🟢 PASS — [run 42424637115](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42424637115) |
| Psalm security scan | 🟢 PASS — [run 42424637178](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42424637178) |
| PHPMD | 🟢 PASS — [run 42424637119](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42424637119) |
| Populated upgrade + rollback | 🟢 PASS — [run 42424637129](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42424637129) |
| Browser smoke | 🟢 PASS — 65 passed, 22 skipped, 0 failed — [run 42424637118](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42424637118) |
| Director health report | 🟢 PASS — [run 42424637141](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42424637141) |
| SonarCloud | ⚪ SKIPPED; not a pass — [run 42424637162](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42424637162) |

**Verification boundary:** the Laravel, Quality, Psalm, PHPMD, browser smoke and populated upgrade/rollback results above all target the same application/test SHA. SonarCloud was skipped and is not counted as a pass. Automated migration/rollback and archive-integrity tests do not substitute for a real operator recovery drill.

**Physical acceptance boundary:** previous PC/mobile installation and screen-rotation confirmation is presentation evidence only. Disconnected installed-device launch/restart, offline Work and attachment recovery, session transitions and secure-origin behaviour still require the Rosscore offline-first physical acceptance sequence.

**Commercial scope boundary:** the customer CSV/XLSX import preview/matching path exists. Product/service and supplier import coverage remains incomplete for the R499 commercial bridge. Staff assignment and scheduled reminders are R799 team-management gaps; these are not being misrepresented as implemented or as blockers to the R299/R499 baseline. Core meaningful feedback/toasts are implemented; they are distinct from scheduled reminders.

**Export boundary:** CSV files provide portable structured business records. They do not contain uploaded binary attachments and are not installation-level backup archives or a database restore format. Exports are owner-only and export actions are recorded in business audit history.

No release certification or score uplift is awarded from CI alone. The 13-domain 60/100 evidence-maturity score remains the last formally reconciled baseline pending a fresh full-domain audit and closure of operational blockers.

## Latest CI workflow reconciliation — 2026-11-08

The application/test source remains `d44ec33a57a4958380f2cfe4624f040859cbcf7a`. Commit `7946a87e78cf4dd2305d12fb20d3f77ed5653c87` changes the Quality workflow bootstrap only; it does not change application source. The following Actions batch completed successfully against that workflow commit:

| Check | Result |
|---|---|
| Laravel | 🟢 PASS — 407 tests / 2,758 assertions — [run 42481998972](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481998972) |
| Zazu quality + secret scan | 🟢 PASS — [run 42481999066](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481999066) |
| Browser smoke | 🟢 PASS — 65 passed / 22 skipped / 0 failed — [run 42481998954](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481998954) |
| Psalm | 🟢 PASS — [run 42481998947](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481998947) |
| PHPMD | 🟢 PASS — [run 42481998949](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481998949) |
| Populated upgrade + rollback | 🟢 PASS — [run 42481998959](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481998959) |
| Director Contract Lint | 🟢 PASS — [run 42481998956](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481998956) |
| SonarCloud | ⚪ SKIPPED; not a pass — [run 42481998971](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481998971) |

The quality workflow bootstrap mismatch is corrected: it now prepares the same Laravel environment used by the dedicated Laravel workflow. This closes the automated CI reconciliation gap for the current application/test source. It does not close physical-device, real recovery, production rollback, privacy/legal or brand-clearance gates.

## Historical CI evidence — 2026-11-01 (not current)

| Check | Exact application SHA | Result |
|---|---|---|
| Laravel tests | `6b16b74a7b773ac2797bf7135f85d38b8af0de8d` | 🟢 PASS — [run 41290751650](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41290751650) |
| Application/build + service-worker behavioral race suite | `6b16b74a7b773ac2797bf7135f85d38b8af0de8d` | 🟢 PASS — [Quality run 41290751642](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41290751642) |
| PHPMD | `6b16b74a7b773ac2797bf7135f85d38b8af0de8d` | 🟢 PASS — [run 41290751665](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41290751665) |
| Psalm / PHP security | `6b16b74a7b773ac2797bf7135f85d38b8af0de8d` | 🟢 PASS — [run 41290751603](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41290751603) |
| Populated upgrade + rollback | `6b16b74a7b773ac2797bf7135f85d38b8af0de8d` | 🟢 PASS — [run 41290751645](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41290751645) |
| Browser smoke | `6b16b74a7b773ac2797bf7135f85d38b8af0de8d` | 🟡 IN PROGRESS at reconciliation — [run 41290751609](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41290751609) |
| SonarCloud | `6b16b74a7b773ac2797bf7135f85d38b8af0de8d` | ⚪ SKIPPED; not a pass |

These results apply only to the exact application SHA shown. The current `main` ref has since advanced through a documentation-only Director reconciliation commit. The current health workflow emits per-run artifacts and Actions summaries; it does not publish a persistent latest report.

## Historical capability snapshot — 2026-11-01 (superseded by the 2026-11-07 reconciliation above)

| Gate | State | Director position |
|---|---|---|
| Registration / authentication | 🟢 | Implemented + automated coverage |
| Business isolation / authorization | 🟢 | Two-business adversarial coverage exists; populated runtime challenge remains |
| Customer → event → work | 🟢 | Populated operational proof exists |
| Purchasing → receiving → inventory → cost | 🟢 | Populated reconciliation proof exists |
| Event completion lifecycle | 🟢 | Real `confirmed → in_progress → completed` path covered |
| Quote → acceptance → invoice → deposit → final payment | 🟢 | Populated reconciliation proof exists |
| Audit continuity | 🟢 | Commercial + inventory mutation evidence covered |
| Security headers / HSTS / CSP | 🟢 | Implemented with regression coverage |
| Public quote / sync throttling | 🟢 | Rate limits implemented |
| Demo seed safety | 🟢 | Local/testing guard + configurable demo credentials |
| Secret-history scanning | 🟡 | Existing gate exists; not re-used as current-head certification evidence in this cycle |
| Backup / restore code safety | 🟢 | Automated round-trip and hostile-archive tests |
| Populated upgrade | 🟢 | Current-head GitHub check passed |
| Rollback procedure | 🟢 AUTOMATED / 🟡 OPERATIONAL DRILL | Procedure and recovery path exist; real deployment exercise remains |
| Desktop/mobile/tablet browser coverage | 🟢 AUTOMATED | Physical-device acceptance separately verified |
| Offline-first foundation | 🟡 | Phone-local IndexedDB + queued sync covers the documented commercial/operational chain, including attachments and explicit conflict detection/resolution; full disconnected parity across every desktop workflow remains unproven and outside the current claim |
| Zazu Helper | 🟡 | Foundation exists; broader autonomous behaviour remains outside V1 |

## Remaining V1 certification gates

### 1. Physical device acceptance — 🟡 PARTIAL
Owner verified on 2026-10-07 that Zazu was installed on PC and mobile, presentation was in order, and screen rotation worked. This confirms installation/presentation only; disconnected app launch, offline Work, attachment recovery, session transitions and secure-origin operation remain unproven.

### 2. Legal / privacy operational closure — 🟡 OPEN
Still requires real operator/deployment details, final privacy/terms decisions, retention/disposal ownership, incident escalation and any applicable processing agreements.

### 3. Brand / trade-mark clearance — 🔴 OPEN / HIGH RISK
The repository records meaningful South African Zazu-name collisions. Official CIPC clearance and appropriate professional review have not been completed.

### 4. Operational recovery drill — 🟡 OPEN
Automated backup/restore and recovery-safety tests are present. Release certification still requires a real representative backup → restore → data/media verification exercise on the intended deployment environment.

### 5. Rollback exercise — 🟡 OPEN
The rollback runbook and recovery mechanisms exist. A real release/deployment rollback exercise remains required.

### 6. Dependency/licence engineering inventory — 🟢 VERIFIED
Exact locked Composer and npm manifests were reconciled on 2026-10-07. See `docs/RELEASE_DEPENDENCY_LICENCE_AUDIT.md`. Final legal/distribution review remains separate.

### 7. Offline operational depth — 🟡 IN PROGRESS
Phone-local customer/job/quote/preparation and supporting inventory, operational cost, asset, supplier, purchasing and finance data are represented in the IndexedDB workspace and sync foundation. The active Work attachment panel now has business-scoped local queueing, atomic multi-file save, idempotent reconnect upload, private-file caching and offline job-ID reconciliation. Trusted-device session-transition and revocation regressions have been added. Exact-head Laravel/Quality/Playwright execution remains unverified; installed-device disconnected launch, full screen parity and real-world sync/conflict recovery remain open.

### 8. Final Director certification — ⬜ NOT STARTED
Certification waits for the remaining operational/legal/brand gates; no rushed release claim.

## Deliberately outside V1

- Autonomous AI decisions/actions
- Predictive finance
- Advanced predictive BI
- Enterprise workflow designer
- Warehouse-scale inventory
- Enterprise SSO
- Broad collaboration suite
- Unlimited UI configurability
- Fully disconnected multi-device sync unless separately implemented and proven

## Next execution order

1. Obtain and reconcile exact-main Laravel, Quality, Psalm, PHPMD, browser and populated upgrade/rollback results; repair confirmed failures.
2. Complete a populated customer → event → Work → quote → acceptance → invoice → deposit/payment → purchasing → receiving → cost/profit traversal.
3. Execute/record a real operator backup → restore drill with representative database records and private media.
4. Execute/record a real deployment rollback exercise.
5. Complete adversarial security/session/device acceptance and close the CSP/token-lifecycle review.
6. Close operational privacy/POPIA controls, retention/incident ownership and dependency/licence release review.
7. Resolve official Zazu brand/trade-mark clearance.
8. Run final Director V1 certification audit.

**Rule:** no broad feature expansion while a Critical release gate remains open.


## Director source audit — latest verified candidate (2026-11-01)

Candidate: `c3370a317b016be8355df799f1eed37bc55c4488` on canonical `main`.

Automated gates:
- 🟢 Zazu quality — [41299518886](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299518886)
- 🟢 Laravel — [41299518930](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299518930)
- 🟢 Psalm — [41299518944](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299518944)
- 🟢 PHPMD — [41299518954](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299518954)
- 🟢 Populated upgrade/rollback — [41299518856](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299518856)
- 🟢 Push on main — [41299519274](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299519274)
- 🟡 Browser smoke — [41299519019](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299519019), still running at documentation time.
- ⚪ SonarCloud skipped; not counted.

Repairs in this audit cycle: live default pairing selection, offline asset duplicate-tag validation, visible/transactional conflict recovery, and business-scoped IndexedDB with guarded legacy migration. The worker cache is now v28.

**Release gate remains amber/open:** the new browser acceptance tests must pass, and source/CI verification still does not establish disconnected installed-device launch, full workflow parity, production hosting, durable persistence, or operational recovery drills.


## Director security correction — 2026-11-04

**Finding:** the previous business-settings backup/restore routes invoked installation-wide commands. The archive contains the complete configured database and private storage; restore replaces the installation-wide state. A business owner is not necessarily an installation operator. The UI copy incorrectly described this as a business-only backup/restore.

**Correction committed on main:** removed both business-owner HTTP routes and controller actions, removed the misleading settings controls, and replaced them with an explicit scope notice. The CLI commands remain for controlled installation-operator use. Added regression coverage that verifies no business-user HTTP endpoint exists and that settings no longer advertise tenant-scoped recovery.

**V1 disposition:**
- Installation-level CLI backup/restore implementation and automated round-trip/hostile-archive coverage: IMPLEMENTED / AUTOMATED.
- Business self-service export/restore: NOT IMPLEMENTED.
- Real customer-environment recovery drill: OPEN.
- Do not count installation-level restore as a tenant-scoped restore capability.
- Next: design a tenant-scoped export format with explicit dependency closure and safe import semantics, or retain operator-managed installation recovery and document the product boundary.


## V1 specification scorecard — 2026-11-04

Current scorecard: `docs/ZAZU_V1_SCORECARD_2026-11-04.md`.

- Evidence maturity: **3.0/5 (60/100)** across 13 V1 domains.
- This is not a feature-completion percentage and does not override release gates.
- Commercial release remains **NO-GO**.
- Exact-head CI for `d1e2a4397b064d3fbfe7fc2cf1dc59236e00922f` was queued at the time of assessment.
- Critical open gates: real recovery/rollback, offline device acceptance, offline account/session security review, tenant-scoped export/restore decision, legal/privacy operations and brand clearance.


## Current source CI evidence — 2026-11-04

Application source SHA `d1e2a4397b064d3fbfe7fc2cf1dc59236e00922f`:
- 🟢 Laravel, Zazu quality, Psalm, PHPMD, populated upgrade/rollback and push-on-main passed.
- 🟡 Browser smoke remains in progress.
- ⚪ SonarCloud skipped; not counted as pass.

Run links and exact IDs are recorded in `docs/ZAZU_V1_SCORECARD_2026-11-04.md`. Documentation-only commits after the source SHA do not change application code.
