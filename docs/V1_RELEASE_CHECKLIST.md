# Zazu EMP — V1 Release Gate Ledger

**Status:** ACTIVE / CANONICAL  
**Repository:** `Leano-Jordan/ZazuEMP`  
**Branch:** `main`  
**Main-ref rule:** query the live `main` ref; this ledger does not pin a moving HEAD.  
**Last Director reconciliation:** 2026-11-01  
**Latest application evidence SHA:** `6b16b74a7b773ac2797bf7135f85d38b8af0de8d` (documentation reconciliation subsequently committed on `main` as `18b60aeb3bbcaecaa1ab5b08baa8ec3d1f1e4afe`).

## Evidence rule

**Implemented ≠ Automated ≠ Runtime-verified ≠ Physically accepted ≠ Release-certified.**

This ledger records the current release truth. Older Director audits remain historical evidence; they do not override this snapshot.

## Current-head CI evidence

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
Phone-local customer/job/quote/preparation and supporting inventory, operational cost, asset, supplier, purchasing and finance data are represented in the IndexedDB workspace and sync foundation. Offline form submissions use origin reachability rather than trusting `navigator.onLine`; local mutation, dirty marker and optimistic workspace copy are committed transactionally. Current-main Quality and Laravel workflows passed on application SHA `6b16b74a7b773ac2797bf7135f85d38b8af0de8d`, including the Node service-worker race suite. The browser smoke run was still in progress at reconciliation. This does not prove disconnected installed-device launch, full screen parity, attachment parity or real-world sync/conflict recovery.

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
