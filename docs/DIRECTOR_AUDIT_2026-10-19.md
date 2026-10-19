# Zazu EMP — Director Engineering Audit

**Date:** 2026-10-19  
**Repository:** `Leano-Jordan/ZazuEMP`  
**Control ref:** `main` only  
**Scope:** V1 correctness, engineering-control consistency, identified dead/stale source, service-worker privacy races, dependency hygiene, and scorecard evidence integrity.

## Executive disposition

Concrete defects were corrected directly on `main`. At the latest observation before the final documentation refresh, workflows for the audited source revision were still **QUEUED**. The scorecard has not been numerically increased. The prior 80/100 value remains a historical 2026-10-04 assessment, not a fresh current score.

## Findings and actions

### 1. Authenticated offline cache races — HIGH, source fix + behavioral coverage added

The service worker already used generation guards against stale cached identity reads and late authenticated HTML responses. The audit identified another asynchronous boundary: an identity-state cache write could be in flight when logout started deleting persisted identity. Without ordering, the write and cleanup could finish in the wrong order.

**Action:** `setUser()` identity-state writes and `clearActiveUser()` cleanup are serialized through a shared promise queue in `public/sw.js`.

**Regression coverage:** `tests/js/service-worker-auth-cache.test.js` loads the actual `public/sw.js` in a Node VM harness and deterministically covers:
- persisted identity read completing after logout;
- authenticated HTML response completing after logout;
- identity-state cache write already in flight when logout cleanup starts.

**CI:** `npm run test:service-worker` is registered in `package.json` and runs in `.github/workflows/quality.yml`. This is **IMPLEMENTED**, not yet TESTED/VERIFIED on the latest main SHA.

### 2. False-current health report — HIGH, corrected

`docs/health/latest.md` and `latest.json` described a 2026-10-12 Dependabot branch with queued tests, not current `main`. Their names incorrectly implied current release evidence.

**Action:** both snapshots were preserved under `docs/health/archive/`, root-level `latest.*` pointers were removed, and `docs/health/README.md` now defines authority. The current health workflow publishes a per-run artifact and Actions summary with repository contents read-only. The old privileged persistent publisher was not restored; its old implementation used a temporary publication branch, conflicting with the project's main-only control.

### 3. Stale V1 scorecard evidence — HIGH, corrected

`docs/V1_RELEASE_CHECKLIST.md` identified a 2026-10-07 SHA as current main and carried earlier CI PASS labels forward despite later commits. The most recent broad green CI evidence recorded in state is SHA `7940033b6fd4f691692eec5e9d7f6ad587486f61` dated 2026-10-15.

**Action:** the ledger no longer pins a moving SHA and distinguishes that historical green baseline from latest-main evidence. SonarCloud remains correctly labelled conditionally skipped, not passed.

### 4. Duplicate engineering control identifier — MEDIUM, corrected

`.ai/engineering/REGRESSION_LEDGER.md` assigned `REG-017` to both “Workspace-state visual language drift” and “Director visual recovery invariants”, despite the repository policy that identifiers are permanent.

**Action:** the original `REG-017` remains unchanged; Director visual recovery invariants is now `REG-022`. Post-write heading scan reports no duplicate REG identifiers.

### 5. Duplicate CSS declarations — LOW, corrected

A declaration scan over four active authored application stylesheets found two redundant `font-variant-numeric: tabular-nums` declarations in `resources/css/zazu-final-visual-sweep.css`.

**Action:** both duplicates were removed. Re-reading the file confirmed removal; re-scan found no remaining duplicate declarations in the four inspected stylesheets. No intended visual output changes.

### 6. Unused font dependency and stale licence inventory — MEDIUM, corrected at source

`@fontsource/anton` was declared and locked but no active JS/CSS/Vite source imported it or referenced its family. The active display stack uses the Space Grotesk/Manrope font faces declared in `resources/css/app.css`. The previous release licence audit also recorded an outdated npm package count.

**Action:** removed the unused dependency from `package.json` and `package-lock.json`; refreshed `docs/RELEASE_DEPENDENCY_LICENCE_AUDIT.md` and the top-level notice pointer from current locked manifests. Fresh `npm ci` and build evidence is still required before this is runtime-verified. Final legal distribution review remains a separate gate.

### 7. Health workflow contract — ACCEPTED, artifact-based

The current `.github/workflows/roscore-director-health.yml` grants `contents: read` and publishes a per-run artifact/summary. It does not persist `docs/health/latest.*`. Do not interpret absent persistent reports as a pass or reintroduce a privileged publisher without separate security review.

## CI evidence observed

At the last API read, workflows for SHA `03bfc665497f5ebd81cea1c7b1abbb7350c32639` were queued:
- Quality: [run 39284398441](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/39284398441)
- Laravel: [run 39284398779](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/39284398779)
- Browser smoke: [run 39284398186](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/39284398186)
- PHPMD: [run 39284398558](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/39284398558)
- Psalm Security Scan: [run 39284398271](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/39284398271)
- Populated upgrade/rollback: [run 39284398778](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/39284398778)

These were **QUEUED**, not passes, and refer to that exact SHA. Later source and documentation changes trigger newer runs; latest-main results remain **UNVERIFIED** until current runs complete.

## Remaining V1 gates

1. Reconcile current-main Quality (including the three Node regression cases), Laravel, browser, PHPMD, Psalm and populated upgrade/rollback runs against exact SHAs.
2. Perform the controlled customer-environment backup/restore exercise, including representative private media and record reconciliation.
3. Perform an actual deployment rollback exercise.
4. Verify installed-device offline launch/restart and multi-account logout/privacy behavior after service-worker changes; full disconnected parity across every desktop workflow remains unproven.
5. Complete legal/privacy operator controls and Zazu trademark clearance; neither can be closed by code changes.
6. Complete final distribution notice review and final V1 certification.
7. Continue a broader PHP/Blade/JS dead-code reachability analysis. The bounded CSS scan was clean after two removals; a search for TODO/FIXME/HACK/DEPRECATED returned no indexed matches, but neither fact proves the entire codebase is free of dead code.

## Score disposition

No numerical readiness uplift is claimed. The prior 80/100 assessment is historical. Source changes and stronger behavioral coverage improve the evidence path, but release readiness depends on current-main workflow results and the remaining operational/legal gates.
