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

- Latest application/test source candidate recorded by the repository is be3457b3a0e7057010492ff8df700aeac90d001d.
- Documentation-only commits followed, with latest repository commit 8b7eb8ea6f573b5544cf70f8d57865a68a5542a5 at the time of this audit.
- GitHub combined status for 8b7eb8e returned an empty status list; the connected workflow-run lookup returned no runs. That lookup is limited to PR-triggered runs, so an empty response is not proof that Actions has no run or that CI passed.
- Exact-head Laravel, offline browser, quality/security, PHPMD/Psalm SARIF and Windows package results for be3457b3 remain unconfirmed through the available repository status interface.
- SonarCloud is SKIPPED, not passed.

## Director action

1. Do not rerun historical failures whose fixes are already evidenced; do not waste cycles rerunning green predecessor jobs.
2. Obtain or trigger exact-head workflows for be3457b3 and inspect failed job logs before changing code. A retry is appropriate only for an identified infrastructure/transient failure; test or product failures require a code/test correction.
3. Treat the native-page offline architecture as implemented but not accepted until exact-head Laravel and browser runs pass. Physical PC/Android checks remain separate.
4. Continue the tier source/test audit. The matrix now records the actual entitlement implementation boundary, queued-mutation regression gaps and the 500-record bootstrap technical bound. That bound is not an approved commercial quota.
5. Keep Basic/Intermediate/Professional as capability labels only. No feature gate or numerical limit is authorised.

**Current verdict:** historical defects listed above have recorded corrective changes. The latest offline implementation is not CI-certified from the available evidence. Zazu V1 remains NOT READY.