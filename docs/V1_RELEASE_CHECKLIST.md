# Zazu EMP — V1 Release Gate Ledger

**Status:** ACTIVE / CANONICAL  
**Repository:** `Leano-Jordan/ZazuEMP`  
**Branch:** `main`  
**Main-ref rule:** query the live `main` ref; this ledger does not pin a moving HEAD.  
**Last Director reconciliation:** 2026-10-19  
**Latest reviewed test/code sequence:** service-worker behavioral tests and serialized identity-cache mutation were added directly to `main`; current-head execution remains unverified.

## Evidence rule

**Implemented ≠ Automated ≠ Runtime-verified ≠ Physically accepted ≠ Release-certified.**

This ledger records the current release truth. Older Director audits remain historical evidence; they do not override this snapshot.

## Current-head CI evidence

| Check | Evidence status at latest audited main |
|---|---|
| Laravel tests | 🟡 NOT RECONCILED for the latest main SHA |
| Application/build | 🟡 NOT RECONCILED for the latest main SHA |
| Quality + service-worker behavioral race suite | 🟡 NEW CHECK; fresh run result not yet observed |
| PHPMD | 🟡 Latest-main result not observed |
| Psalm / PHP security | 🟡 Latest-main result not observed |
| Populated upgrade + rollback | 🟡 Latest-main result not observed |
| Browser smoke | 🟡 Latest-main result not observed |
| Director Contract Lint | 🟡 Latest-main result not observed |
| Push/main workflow | 🟡 Latest-main result not observed |
| SonarCloud | ⚪ Previously conditionally skipped; not a pass |

The last source-recorded broad green main run is commit `7940033b6fd4f691692eec5e9d7f6ad587486f61` (2026-10-15), including Laravel run 38789104896, Quality 38789104893, PHPMD 38789105223, Psalm Security Scan 38789105085, browser smoke 38789104865, and populated upgrade/rollback 38789104911. This is historical evidence for that SHA only. Do not promote it to current-head PASS. The current health workflow emits a per-run artifact and Actions summary; it does not publish a persistent latest report.

## V1 capability gates

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

### 1. Physical device acceptance — 🟢 VERIFIED
Owner verified on 2026-10-07 that Zazu was installed on PC and mobile, presentation was in order, and screen rotation worked. This remains physical acceptance for the reported devices only; it does not verify the post-2026-10-16 service-worker/logout changes or prove fully disconnected phone-local operation.

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
Phone-local inventory, operational cost and asset workflows are now represented in the local store, queued for synchronization, and protected by server-side domain handlers. The new Node behavioral race suite is now wired into the Quality workflow, but execution on the latest main and the resulting CI conclusion remain UNVERIFIED until the exact run is observed.

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

1. Execute/record real recovery drill.
2. Execute/record real rollback exercise.
3. Close dependency/licence release review.
4. Close legal/privacy operational controls.
5. Resolve Zazu brand clearance.
6. Run final Director V1 certification audit.

**Rule:** no broad feature expansion while a Critical release gate remains open.
