# Zazu EMP — Hardening & Commercial Readiness Scorecard

## Purpose

Current working gate for development toward a commercially usable release.

This scorecard separates implementation from evidence maturity and separates **V1 release requirements** from **future scale programmes**.

## Evidence maturity

0 — not started  
1 — designed  
2 — implemented  
3 — automated evidence  
4 — runtime/CI verified  
5 — repeatedly proven through realistic/production/recovery evidence

## Current Director assessment — 2026-10-19

**Assessment boundary:** current main has advanced beyond the last broad green application run. The last recorded broad green baseline is `7940033b6fd4f691692eec5e9d7f6ad587486f61` (2026-10-15); that result does not certify later commits. Exact current-head CI for the new service-worker behavioral suite is **UNVERIFIED** until its run is observed.

| V1 domain | Current evidence position | Remaining release work |
|---|---|---|
| Correctness | Behavioral regression suite added for three logout/identity/cache races; fresh CI execution unobserved | Verify the exact current-main run and repair any genuine regression |
| Architecture | Identity cache mutation/clear operations are serialized; duplicate CSS declarations and one unreferenced font dependency were removed | Validate through CI/browser execution; broader PHP/Blade/JS reachability scan remains open |
| Data integrity | Existing transaction, idempotency and ownership controls remain in source; newest-head execution unverified | Current-head tests and populated commercial traversal |
| Security | Generation guards plus behavioral tests now target stale identity reads, late HTML writes and identity writes racing logout | Latest-run evidence and real multi-account installed-browser challenge |
| Workflow integrity | Connected commercial flow exists; previous populated proof is for an earlier SHA | Re-run current-head workflow and reconcile job → quote → invoice/payment → purchasing/costs |
| UX / accessibility | Earlier responsive/browser acceptance is historical; this audit introduces no UI redesign | Re-run browser checks after the current code changes and preserve owner-confirmed device acceptance boundary |
| Reliability / recovery | Automated restore/rollback coverage existed at the earlier verified baseline | Real deployment-environment backup/restore (including private media) and rollback drills remain open |
| Operability | Stale health snapshots are archived; current health workflow publishes per-run artifacts and summary only | Open the exact current-main workflow run; do not rely on a persistent `latest` file |
| Upgrade / deployment | Previous populated upgrade/rollback run was green at the recorded baseline | Confirm current-head results and perform real release rollback exercise |
| Documentation / compliance | V1 ledger reconciled; stale health claims removed; duplicate control ID and dependency inventory corrected | Legal/privacy operator controls, brand/trade-mark clearance and final dependency distribution review |
| Release evidence | Current exact-head results are not yet reconciled | Record SHA + run URLs + conclusions before promoting evidence maturity |

### Score disposition

The previously recorded **80/100 commercial-readiness figure is historical (2026-10-04)**, not a current live score. This audit does **not** award a numerical uplift: source changes and new tests are implementation evidence, not a passing runtime result. Re-score only after current-head CI is observed and the open V1 operational gates are reconciled.

## V1 gate domains

| Domain | What must be true |
|---|---|
| Correctness | Critical workflows produce the intended result |
| Architecture | Core structure is understandable, bounded and maintainable |
| Data integrity | Transactions, ownership, parent/child rules, precision and concurrency are protected |
| Security | Authentication, authorization, isolation and sensitive-media boundaries are enforced |
| Workflow integrity | Jobs can move through valid operational/commercial states |
| UX/accessibility | Critical workflows are understandable and usable across required contexts |
| Reliability/recovery | Failure paths are controlled and restore has been demonstrated |
| Operability | The owner can diagnose and respond to incidents |
| Upgrade/deployment safety | Clean installs and representative existing databases can be upgraded safely |
| Documentation/compliance | Required runbooks, dependency/IP/privacy evidence exist |
| Release evidence | Current CI/browser/runtime evidence supports the release claims |

## Release blocker definition

A blocker is release-critical when it can cause:
- data loss/corruption;
- unauthorized access;
- broken critical workflow;
- unrecoverable deployment/upgrade;
- inability to restore;
- materially incorrect financial/commercial state;
- inability to operate or diagnose the product.

## Future-scale gates

These are **not automatic V1 blockers** unless product scope explicitly requires them:
- SaaS billing/entitlements
- large-data performance proof
- production soak/long-term reliability evidence
- multi-location expansion
- advanced messaging/client portal ecosystem
- advanced AI automation

The architecture should preserve future options without allowing future-scale work to displace current release-risk closure.

## Operating rule

Readiness increases when meaningful engineering risk is removed **and evidence matures**.

It does not increase merely because:
- more files were created;
- more tests were added;
- another dashboard/report was written;
- an incomplete workflow was given a new UI.

Source of truth for live status: `.ai/engineering/READINESS_REGISTER.md`.
