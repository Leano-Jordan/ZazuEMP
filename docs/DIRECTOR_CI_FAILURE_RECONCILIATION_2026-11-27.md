# Zazu Director — CI failure reconciliation

**Date:** 2026-11-27  
**Repository:** Leano-Jordan/ZazuEMP  
**Branch:** main only  
**Accountable role:** Zazu Engine Director (Jarvis)

## Outcome

This record separates historical failures that have been corrected from the current source candidate, for which exact-head CI evidence is still unavailable. No failed job is being silently relabelled as passed.

## Historical failures and their disposition

| Failure | Root cause / correction | Disposition |
|---|---|---|
| Laravel on f064dff: 783 passed, 2 failed, 1 skipped | Stale expectations: service-only business still exposed an empty rental tab; PWA test expected an obsolete one-line worker message. Corrected in f9a31c6, a164b77 and e896917. Later source-contract/Pint/browser-fixture corrections were committed as bb28ff1, 4adedd6, 3fd7cc7, c15d230 and 50f08ec. | CORRECTED in source/test history. Do not claim those old runs passed; require exact-head rerun for current code. |
| Offline browser: 60 passed / 20 failed on the earlier broad route-classifier implementation | Broad route-family detection hid native forms/content and misidentified route pages. Subsequent iterations still exposed service-worker fallback defects (78/2, then 79/1). The shared offline-workspace shell was ultimately rejected and removed; exact cached native routes now retain normal page identity, with a connection-required state only for a genuine uncached fallback. | ARCHITECTURAL CORRECTION IMPLEMENTED. Exact-head browser acceptance for be3457b3a0e7057010492ff8df700aeac90d001d remains UNVERIFIED. |
| Composer security audit: five Symfony advisories | Updated affected Symfony lock entries to 7.4.24 in 43e69e623aafb43e1159e8a0664d729764575844. | RESOLVED on recorded Zazu Quality runs 45787154093 and 45787226126; no advisory suppression was added. |
| Psalm / PHPMD SARIF publication jobs failed in an earlier quality run | The workflow's SARIF upload integration was aligned to pinned CodeQL action v4.39.3. Later recorded Psalm analysis and SARIF publication passed on candidate 43e69e6 (run 45787153999); PHPMD passed on run 45787154030. | CORRECTED on the later recorded candidate. This is not a current-head rerun claim. |

## Current verification boundary

- Latest offline source fix is `0f8447b`: align asset delivery target key with the bootstrap builder's `asset` collection key.
- Latest regression test commit is `b30de1b`: cover asset delivery target resolution and receiver bootstrap inclusion. Trial expiry reconnect/push/apply/pull/ack coverage is in `dc761af` and preceding test commit `54177fd`.
- Latest engineering state commit is `760097c`; latest capability matrix commit is `e6de4f6`.
- GitHub combined status for `b30de1b` and the connected workflow lookup returned no status entries / no PR-triggered runs. The lookup is limited to PR-triggered runs, so an empty response is not proof that Actions has no run or that CI passed.
- Exact-head Laravel, offline browser, quality/security, PHPMD/Psalm SARIF and Windows package results for the latest source/test state remain unconfirmed through the available repository status interface.
- SonarCloud is SKIPPED, not passed.

## Director action

1. Do not rerun historical failures whose fixes are already evidenced; do not waste cycles rerunning green predecessor jobs.
2. Obtain exact-head workflows for the current source/test state and inspect failed job logs before changing code. A retry is appropriate only for an identified infrastructure/transient failure; test or product failures require a code/test correction.
3. Treat the native-page offline architecture as implemented but not accepted until exact-head Laravel and browser runs pass. Physical PC/Android checks remain separate.
4. Continue the full reconnect regression and resolve the 500-record offline snapshot completeness boundary.
5. Keep Basic/Intermediate/Professional as capability labels only. No feature gate or numerical limit is authorised.

**Current verdict:** historical defects listed above have recorded corrective changes. The latest offline implementation is not CI-certified from the available evidence. Zazu V1 remains NOT READY.
## Workflow trigger clarification — 2026-11-27

- `.github/workflows/laravel.yml` runs on pushes to `main` when application/test paths change. It includes Laravel/PHPUnit, PostgreSQL migration/recovery and the offline-browser acceptance jobs.
- PHPMD and Psalm workflows also have `push` triggers for relevant app/test paths. The separate full `zazu-browser.yml` workflow is PR/manual-dispatch triggered; the focused offline browser suite is part of `laravel.yml`.
- The connected workflow-run fetch function filters to pull-request-triggered runs only. An empty result does not mean push-triggered workflows were not created. The combined-status lookup returned no status entries and this connector exposes no push-run job/log listing or workflow-dispatch operation.
- Therefore the current exact-head state is **UNVERIFIED through the available connector**, not evidence that the jobs did not run and not evidence that they passed. Read the `main` push run in GitHub Actions for the exact test commit before treating this change as accepted.
- Latest engineering state update: `57fe5d3`. Latest test commit: `b30de1b`. Latest source fix: `0f8447b`.