## Current Director checkpoint — 2026-11-16 (Windows V1 package)

**Canonical repository:** `Leano-Jordan/ZazuEMP`, branch `main`  
**Verified Windows package source:** `9a1fc33004c52d7ee4b5a16d94dc7d94b578e5f0`  
**Package run:** [43966804827](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43966804827) — package assembly and Windows PowerShell first-install smoke **PASS**.

### Verified package and installation evidence

- Production-only Composer dependencies installed; Vite frontend built; manifest and referenced assets verified; clean Windows ZIP assembled with SHA-256.
- Windows PowerShell 5.1 fresh-install smoke with PHP 8.4/SQLite created the private `.env`, generated APP_KEY, ran migrations, booted Laravel routes, and fetched `/login` over loopback HTTP. The returned session cookie was checked to be browser-usable for the local HTTP profile.
- Artifact `ZazuEMP-v1-candidate-9a1fc33.zip`; standalone SHA-256: `33a60db7fb40a6031634c3d97fc1d3600b46e6994c2276f48fc08ca448c980a7`.
- Package includes production PHP dependencies, compiled frontend assets, Windows launchers/installer and customer/operator runbooks. It excludes `.env`, local DB, private business files, tests, Node modules and internal engineering controls.
- The default package uses `SESSION_SECURE_COOKIE=false` for the local `http://127.0.0.1:8000` address. HTTPS-only installations must set it to `true`; the HTTP server must never be public.
- The package is a **fresh local install candidate**, not an in-place updater or hosted SaaS deployment.
- Zazu Quality [43966805237](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43966805237), Director Contract Lint [43966804706](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43966804706), Director Health [43966804574](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43966804574), and Push on main [43966803035](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43966803035): **PASS**.

### Score and release disposition

**Evidence maturity: 73/100 (up from 71/100).** The +2 reflects verified Windows packaging and first-install evidence only; it does not certify actual owner-device use or complete commercial release readiness.

**Windows package/first-install gate: CLOSED for the defined fresh-install candidate.** The actual owner PC/browser and phone remain separate acceptance targets.

**Commercial V1 remains NOT READY** pending:
1. Owner-PC/browser rendered acceptance and physical phone acceptance for the intended phone/PWA workflows.
2. Installed-target operator backup/restore drill, including private media.
3. Live populated-business reconciliation through quote → invoice/payment → incurred costs → event contribution.
4. POPIA operational/privacy sign-off.

Brand/name clearance remains deferred by owner direction. Do not reopen already-passing seeded commercial tests without reproducible new evidence. No production deployment or live business-data change occurred.

---
# Zazu EMP — Hardening & Commercial Readiness Scorecard

## Prior Director checkpoint — 2026-11-16 (dashboard/theme stage)

**Canonical repository:** `Leano-Jordan/ZazuEMP`, branch `main`  
**Current documentation/control head:** `19bc949891d02afd1226cf0843df259b4b35e682`  
**Application/test head:** `8cdadcd1563bd30c88b15153ca91181a2d5abb23`; generated-asset manifest descendant `dfc01749af5718011332a9e86d303b9622fe7ff4`.

### Verified engineering progress

- Dashboard light/dark palette is now sourced from shared Zazu theme tokens. The dark-mode specificity defect is fixed, and Playwright asserts the computed shared-token relationship while retaining the layered hero/business-image treatment.
- Exact source/test run [43952349496](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43952349496): Laravel **500 passed / 1 skipped / 3,549 assertions**; offline browser **37 desktop passed**; mobile-viewport acceptance **6 passed**; phone-local attachment journey **1 passed**; PostgreSQL migration and database/private-file recovery smoke **PASS**.
- Manifest descendant run [43952861421](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43952861421): Laravel, offline-browser and PostgreSQL jobs **PASS**. Zazu Quality [43952861428](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43952861428), Director Health [43952861384](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43952861384), and Push on main [43952861462](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43952861462) **PASS**. SonarCloud was skipped.
- Current documentation-only reconciliation commit [19bc949](https://github.com/Leano-Jordan/ZazuEMP/commit/19bc949891d02afd1226cf0843df259b4b35e682): Zazu Quality **PASS** (application, Composer security audit, secret scan); Director Contract Lint **PASS**. Push-on-main was still running at the time of this checkpoint.
- Offline session/attachment scope is documented against current automated coverage: unreachable logout, expired web-session recovery, owner/manager account transition, business-scoped queue preservation, stale response/logout races, cross-business pairing rejection, owner revocation and active Work attachment persistence/reconnect/idempotency/private-file checks. These remain automated evidence, not physical-device proof.

### Score and release disposition

**Evidence maturity: 73/100 (up from 71/100).** The +2 reflects verified Windows packaging and first-install evidence only; it does not certify actual owner-device use or complete commercial release readiness.

The remaining release gates are deliberately bounded:
1. Physical Android installed-app acceptance: secure-origin launch, disconnected cold launch/restart/force-close, local file picker/storage and reconnect on the actual phone.
2. Operator recovery drill on the intended installation, including database and private media.
3. Live populated business-record reconciliation through quote → invoice/payment → costs/profit.
4. POPIA operational/privacy sign-off.

Brand/name clearance remains deferred by owner direction. Do not reopen already-passing seeded commercial tests without a reproducible defect. Do not claim PC visual acceptance or physical Android acceptance from CI viewport tests.

**Execution control:** stay on `main`; no branches, no unrelated feature expansion, no production deployment or customer-data changes. Next engineering focus remains the highest-risk reproducible defect in V1 security/offline continuity, not future-scale work.

---

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

## Current Director assessment — 2026-11-03

The 19 October assessment below is retained as historical evidence. The 3 November source audit is recorded in `docs/ZAZU_SECURITY_AUDIT_2026-11-03.md`.

- Parser resource bounds, API security headers/request IDs and the customer-import reader/controller contract have been hardened in source; their new regression tests await exact-head CI.
- The offline workspace is intentionally business-scoped under owner-approved trusted-device pairing (DEC-019). The remaining control is trusted-device lifecycle: owner-visible device inventory/revocation, physical device lock and lost-device procedure; per-staff offline isolation is not part of the current V1 architecture.
- Main is protected but has no required status-check contexts. The owner must require current quality/security workflows.
- No live black-box verification was performed in this repository-only review because a Zazu runtime URL and test credentials were not supplied.
- No numerical readiness uplift is awarded until exact-head CI and the open security gate are addressed.

## Previous Director assessment — 2026-10-19 (historical)

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
