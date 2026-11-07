# Zazu EMP — V1 Release Gate Ledger

**Status:** ACTIVE / CANONICAL  
**Repository:** `Leano-Jordan/ZazuEMP`  
**Branch:** `main`  
**Main-ref rule:** query the live `main` ref; this ledger does not pin a moving HEAD.  
**Last Director reconciliation:** 2026-11-07  
**Latest application/test source SHA:** `45f3cfed9bee48153d939932ba4c495c85c0b26b` (latest observed `main` ref at this reconciliation: `d5608fd28256d72b9ddbcbce7e6826ade0c1ce55`).

## Evidence rule

**Implemented ≠ Automated ≠ Runtime-verified ≠ Physically accepted ≠ Release-certified.**

This ledger records the current release truth. Older Director audits remain historical evidence; they do not override this snapshot.


## Current V1 reconciliation — 2026-11-07

**Release position: NO-GO.** The 4 November scorecard remains a historical evidence baseline, not current certification.

| Area | Current source/test position | Evidence state |
|---|---|---|
| Active Work attachments | Phone-local queue, atomic batch save, idempotent reconnect upload, private attachment list/download cache and offline job-ID reconciliation are integrated into the active Work page | IMPLEMENTED; Laravel/Playwright execution pending |
| Trusted-device lifecycle | Browser regression covers unreachable logout preserving the signed-in session and queued business work, followed by same-account re-login; owner revocation regression covers bootstrap, pull, push and attachment-list APIs | TESTS ADDED; exact-head execution pending |
| Backup / restore | New format-v2 archives contain SHA-256 hashes for the database and private files; restore checks the exact payload set before extraction; valid v1 archives remain supported | IMPLEMENTED; generation, tamper, missing-manifest and v1 compatibility tests added; execution pending |
| Commercial workflow | Existing quote, deposit, invoice, payment, purchasing and receiving paths remain implemented; populated end-to-end reconciliation is still not evidenced on the current source head | IN PROGRESS |
| Security | Business isolation and device revocation foundations remain; CSP still permits inline script/style, sync-token expiry/rotation policy remains open | IN PROGRESS |
| Deployment / recovery | No current production HTTPS host or real operator recovery/rollback drill is recorded | OPEN / RELEASE BLOCKER |
| Legal / privacy / brand | Operational privacy sign-off, retention/incident ownership and official Zazu name clearance remain outstanding | OPEN / RELEASE BLOCKER |

**Current verification boundary:** GitHub combined-status lookup for the latest main head returned no status contexts. The available commit-workflow-run lookup did not expose push-triggered runs for that SHA. Do not reuse historical green Actions runs as current evidence. Laravel, Quality, Psalm, PHPMD, browser and populated upgrade/rollback checks remain UNVERIFIED for the latest application/test source.

**Physical acceptance boundary:** the owner previously confirmed installation on PC/mobile and screen rotation. That is not proof of disconnected installed-device launch, offline attachment recovery, session-transition behaviour or secure-origin operation.

No evidence-maturity score uplift is awarded for source changes or tests that have not executed. The 13-domain 60/100 score remains the last formally reconciled baseline until a fresh exact-head assessment is completed.

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
