## 2026-11-14 — LAN built-asset startup contract final verification

- Latest implementation and workflow source: `8fc403f2609f5e8d96300ffd668c907809fa9220` on `main`.
- LAN/HTTPS launchers now select same-origin compiled assets, remove stale runtime `public/hot`, build only when the manifest is absent and dependencies are already installed, and validate each manifest `file`, `css`, and `assets` output before starting Laravel.
- Loopback development launcher remains unchanged and continues to support Vite/HMR.
- The initial Windows static assertion was corrected to match the actual nested `Join-Path` implementation; a subsequent exact-head run passes. No application code was modified to bypass a test.
- Exact-head checks: Windows Ops [43578557436](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43578557436), Zazu Quality [43578557394](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43578557394), Director Contract Lint [43578557360](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43578557360), Director Health [43578557375](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43578557375): **PASS**.
- Changed source/doc/workflow commits: `8d7dcb82`, `bff43d75`, `7e6a491c`, `50a4695c`, `e86a5db4`, `57753ff0`, `8fc403f2`.
- **Owner runtime acceptance remains open:** pull latest `main`, run `Start-Zazu-LAN.cmd`, then reload the current phone URL and confirm styling is restored. This cannot be proven by source inspection or Windows CI. Offline engineering remains paused.

---

## 2026-11-14 — LAN app styling / Vite asset delivery correction

- **Target:** stop the phone/browser LAN entrypoint from rendering the Laravel shell without CSS when `public/hot` points to a stopped or phone-unreachable Vite server.
- `ops/windows/Start-ZazuDev.ps1` now has a bounded built-assets preparation step for `-Lan` or `-BuiltAssetsOnly`: remove the runtime `public/hot` marker, require/produce `public/build/manifest.json`, build locally only when `node_modules` is already present, parse the manifest, and verify each referenced output file before Laravel starts.
- `Start-Zazu-LAN.cmd` reaches this automatically through its existing `-Lan` switch. `Start-ZazuLanHttps.ps1` explicitly passes `BuiltAssetsOnly = $true` so HTTPS uses same-origin compiled assets. `Start-Zazu-Dev.cmd` remains unchanged for normal loopback Vite/HMR work.
- Updated `docs/LOCAL_PC_HOST_DEPLOYMENT.md` to document automatic compiled-asset behaviour and reserve explicit Vite HMR settings for live LAN development.
- Added a Windows Actions contract check covering stale-marker removal, manifest/build validation, LAN and HTTPS launcher selection, and preservation of loopback development behaviour.
- Changed commits: `8d7dcb82e8d6d17f9a70b1df4e91d8eb33f792d1` (host launcher); `bff43d75edc7a2fd711ad3a98dd753312e018d23` (HTTPS launcher); `7e6a491cee69ca5dcc2c36c4f9e4a4db9f2d279a` (deployment guide); `50a4695cba7a2d5f7af0d37b2fcb41b6dfc5c8e3` (Windows validation); `e86a5db4f6e1b82393c07f8c1799268e00e593c5` (corrected validation assertion).
- Exact-head checks on `e86a5db4f6e1b82393c07f8c1799268e00e593c5`: Windows Ops [43578259181](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43578259181), Zazu Quality [43578259182](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43578259182), Director Contract Lint [43578259174](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43578259174), Director Health [43578259177](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43578259177), and Push on main [43578258703](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43578258703): **PASS**.
- First Windows contract run failed only because the new static assertion expected a literal combined path where the script correctly uses nested `Join-Path` calls. The assertion was corrected; the exact-head Windows Ops run then passed all steps, including PowerShell parsing and existing HTTPS cleanup checks. No product code was changed to appease a failing test.
- Verification boundary: source and Windows CI pass. The owner PC/phone has not yet run the updated LAN launcher, so rendered CSS on the actual phone remains **OWNER RUNTIME CHECK REQUIRED**. The concrete owner action is to pull latest `main`, run `Start-Zazu-LAN.cmd`, then reload the phone URL. Offline engineering remains paused.

---

## 2026-11-14 — Owner V1 shipping mandate and current operating state (AUTHORITATIVE)

This entry supersedes any lower historical/current-looking note that directs work back into offline engineering or keeps V1 gates open for speculative refinement.

- **Owner objective:** deliver a practical, dependable Zazu V1 that can be put in the hands of the first real businesses. Do not optimise for thousands of customers or theoretical enterprise perfection before first-customer usability.
- **Definition of done:** agree the acceptance criteria, verify them, award **5/5**, and CLOSE the gate. Distinguish DONE, ACCEPTED LIMITATION and BLOCKED. Reopen only for new reproducible evidence.
- **Execution discipline:** no repeated investigations, duplicate green tests, speculative hardening, scope creep, or documentation-only activity presented as product progress. Batch related verified defects and move to the next V1 gate.
- **Offline boundary:** offline engineering is PAUSED by owner direction. Do not modify the service worker, IndexedDB, attachment queue, offline sync or PWA installation path unless the owner explicitly resumes that work or a separate critical regression is demonstrated. The immediately following older entry's instruction to continue offline issues #34/#35 is superseded.
- **Repository instructions:** the owner mandate is pinned at the top of `memory.md`; `AGENTS.md` instructs every engineering session to read it first. Work directly on `main`; do not create feature/development branches; preserve `deploy/render-staging`.
- **Application/test source:** `62a551e97e48a2fa0045046f07742dfd9d817f43`. Exact-head Laravel workflow [43575869239](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43575869239): **PASS** — 496 passed, 1 skipped, 3,502 assertions; PostgreSQL migration/registration/database+private-file backup/restore: **PASS**; dedicated offline-browser acceptance: **29 passed**. These are automated results only, not physical-device proof.
- **Current documentation/control head at this entry:** `1b3571f2bc3fb1b3579bfa3533f4142a3e2c51f1`. Zazu quality [43577075706](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43577075706), Director Contract Lint [43577075740](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43577075740), Director Health [43577075750](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43577075750), and Push on main [43577075530](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43577075530): **PASS**. SonarCloud was skipped and is not counted.
- **No new offline work is authorised.** The remaining V1 items are bounded acceptance/owner decisions: physical device acceptance, installed-target recovery drill, live commercial-record reconciliation, and POPIA/privacy/brand closure. Do not turn these into invented engineering backlogs; complete any source-level work only when a verified V1 defect or explicit requirement exists.

---

## 2026-11-14 — Offline conflict acceptance deterministic; exact-head CI closure

- Canonical repository: `Leano-Jordan/ZazuEMP`; branch: `main`; application/test source: `aaa47895b334b3bbf3a68f89b66e4ff6a0738032`.
- Exact-head Laravel workflow [43567837212](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43567837212): **PASS**. Laravel suite: 496 passed, 1 skipped, 3,502 assertions. PostgreSQL migration, registration, backup/restore and private-file recovery: **PASS**. Dedicated offline-browser acceptance: **29 passed, 0 failed, 0 flaky**.
- The browser run includes the corrected rejected-sync/conflict-persistence case. The test now keeps the browser offline while installing mocked routes, reconnects after interception is ready, and polls the durable IndexedDB mutation status rather than assuming a coalesced sync promise represents the final pass. It passed on the first attempt in this run.
- Regression coverage also includes the single-backslash tenant file-path rejection test committed in parent `60dbfc71c556ce34ed4350560e8eb59b3ae4a4fc`.
- The run verifies browser automation and PostgreSQL recovery paths only. It does **not** prove physical Android install, force-close/cold-launch with the PC/server off, full local workflow parity, or operator recovery on the installed target.
- Current offline architectural blocker remains the server-rendered Laravel navigation/workspace dependency: service-worker page caching can preserve a previously rendered screen, but that is not equivalent to a phone-local application runtime. Continue from open Director issues #34 and #35; inspect and extend the existing IndexedDB/offline handlers rather than adding a second offline layer or cache-only workaround.
- Release remains **NO-GO** pending physical Android acceptance, business-level export/import and installed-target recovery drill, live quote-to-payment-to-profit reconciliation, and POPIA/privacy/retention/incident plus Zazu name clearance.
- No production deployment, live database, customer records, or non-main branch changed.

---

## 2026-11-13 — V1 release checklist exact-evidence correction

- Reconciled the leading current-status section of `docs/V1_RELEASE_CHECKLIST.md`. It incorrectly labelled application/test source `5888123f1941beb478b31870a6e782e0f8ef8053` as current, despite later verified application/test source `30e259d445c614df6d1b2495c5a5e505a6d36443`.
- Current checklist now distinguishes the verified application/test source from the later documentation/control-plane `main` head `6ca60e37bd71d6eebee0ff9d651c701ef2549ec8`, cites exact passing workflows [43504654649](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43504654649) and [43504020001](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43504020001), and preserves the NOT READY disposition and remaining operational obligations.
- Offline engineering remains paused. No offline application code was changed.
- Release-checklist commit: `444c423912009932ea0ca53860b18fcbd749db06`.
- No application source, tests, production data, database, deployment or non-main branch changed.

---

## 2026-11-13 — readiness register reconciliation and offline pause

- Owner direction: pause offline engineering. No service-worker, IndexedDB, attachment queue, offline sync or PWA installation changes are authorized in this work cycle.
- Reconciled `.ai/engineering/READINESS_REGISTER.md` with the current formal scorecard and exact-source CI evidence; its stale Nov 11 snapshot is now explicitly superseded by the Nov 13 reconciliation.
- The readiness register now records verified application/test source `30e259d445c614df6d1b2495c5a5e505a6d36443`, successful workflow [43504654649](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43504654649), successful service-worker regression workflow [43504020001](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43504020001), evidence maturity 71/100, and the remaining owner/operational release obligations.
- Seeded quote-to-payment-to-profit automation is already verified. Live business-record reconciliation remains an operator acceptance item; no speculative financial-concurrency gate is added.
- Readiness-register commit: `640b9ea97b70bcfa7d89f474899d85b24a6bacde`.
- No application source, tests, production data, database, deployment or non-main branch changed.

---

## 2026-11-13 — V1 scorecard re-score pushed

- Re-scored the 13 V1 domains against verified application/test source `30e259d445c614df6d1b2495c5a5e505a6d36443` and completed Laravel workflow [43504654649](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43504654649).
- **Evidence maturity: 71/100**, up from 69/100. Recovery/data portability advances 3/5 → 4/5 because tenant-scoped business archive round-trip/tenant isolation and PostgreSQL database/private-file recovery passed automation. Other domain scores held.
- Offline continuity remains 3/5. The disconnected attachment reload test passed in browser automation, but physical Android secure-origin install, cold-launch, local workflow and force-close/reopen persistence remain unverified. No claim of full offline parity.
- Release remains **NOT READY** pending bounded physical-device acceptance, installed-target operator recovery, live commercial record reconciliation, and POPIA/privacy/brand closure.
- Scorecard commit: `81d811df8c2edf1f0b0a48c2f8cb60c2748032b6`. No application code, production data, deployment or non-main branch changed.

---

## 2026-11-13 — Disconnected attachment persistence regression

- Extended `e2e/offline-attachment-sync.spec.js` so the active Work attachment test now reloads the job page while browser connectivity is disabled, then reopens the attachment queue through `window.ZazuOffline` and verifies the original file is still present before reconnect/upload.
- This closes a test-coverage gap: the prior scenario proved IndexedDB queueing and reconnect within one live document, but did not prove recovery after the document's JavaScript heap is discarded.
- Test commit: `30e259d445c614df6d1b2495c5a5e505a6d36443`. Exact-head Laravel workflow [43504654649](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43504654649) completed successfully: Laravel tests, PostgreSQL migration/backup/restore smoke, and offline-browser acceptance all passed, including the disconnected reload attachment test. This is browser automation evidence, not a physical Android force-stop claim.
- The preceding service-worker 5xx fallback and 401/403 non-fallback regressions passed in Laravel workflow [43504020001](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43504020001) on source commit `73734e7d5553eb9bb7b13cc519ac46f0b5266f35`; the workflow completed Laravel, PostgreSQL and offline-browser jobs successfully.
- No production data, deployment, local database or non-main branch changed.

---

## 2026-11-13 — Mobile navigation resilience during host restart

- Found a second mobile continuity gap in `public/sw.js`: when Laravel was reachable but returned an HTTP 5xx (for example, during a restart or temporary overload), navigation returned the server error directly instead of using the authenticated page already cached for that user/business. Network disconnects already fell back correctly; transient HTTP failures did not.
- Corrected on `main`: 5xx navigation responses now fall back to the active business-scoped cached page. 401/403 and other non-5xx responses are still returned unchanged, preventing stale cached authenticated HTML from masking an authorization/session failure.
- Advanced the service-worker cache generation to `zazu-static-v37`.
- Added focused regressions for 503 fallback and 401 non-fallback in `tests/js/service-worker-auth-cache.test.js`. The service-worker suite is now wired into the Laravel GitHub Actions workflow by commit `a8acd2f05f7c2467ed396e4964c1b186b7d8fafd`.
- Implementation commit: `118ebfa4af26bd909cf0d61f4d6f24039f2f4393`; regression commit: `73734e7d5553eb9bb7b13cc519ac46f0b5266f35`.
- Source was re-fetched from GitHub and reconciled. Automated execution on the resulting main head remains **PENDING / NOT CLAIMED**. This improves navigation continuity; it does not prove full mobile offline data/workflow parity or physical Android cold-start.
- No production data, deployment, local database or non-main branch changed.

---

## 2026-11-13 — Service-worker regression suite connected to CI

- Found a verification gap while tracing the new offline styled-shell regression: `package.json` defined `test:service-worker`, but `.github/workflows/laravel.yml` did not invoke it. The new regression and existing service-worker auth/cache tests would therefore not run in the main Laravel workflow.
- Corrected the workflow on `main` by adding a dedicated `Test service-worker offline cache and session contracts` step after frontend build. It runs `npm run test:service-worker` and is triggered by the existing public-source/workflow path filters.
- Workflow commit: `a8acd2f05f7c2467ed396e4964c1b186b7d8fafd`.
- This is a CI wiring correction, not a test pass. The workflow must complete on the resulting main head before the service-worker change is called verified.
- No production data, deployment, local database or non-main branch changed.

---

## 2026-11-13 — Offline styled-shell asset priming

- Root cause: authenticated page priming cached HTML and private media, but did not guarantee that Vite CSS/JavaScript and CSS-referenced fonts/images were present in the shared static cache. A disconnected dashboard could therefore reopen with missing presentation assets even when its HTML was cached.
- Implemented on `main`: service worker cache generation advanced to `zazu-static-v36`; page priming now extracts same-origin `/build/` and `/images/` references, caches them in the shared static cache, and follows CSS `url(...)` dependencies one level deep. Asset work is deduplicated and bounded to 150 URLs; private business media remains in its authenticated business cache.
- Added a focused Node regression in `tests/js/service-worker-auth-cache.test.js` covering cached HTML, Vite CSS/JS, CSS background images and fonts, followed by disconnected fetches.
- Commits: service-worker implementation `1cd8129cae6d46696d4415b011d02f54dbe4486f`; CSS URL parser correction `f51cbb942af228e56789e07c6080371b316fab03`; regression test `6ef56747d27c2989d3b7caba04367354d3824ae0`.
- Source re-fetched from GitHub and reconciled. Automated test execution and exact-head Actions result are **PENDING / NOT CLAIMED**. The Android plain-HTTP origin still cannot register a service worker; physical installed-device acceptance remains open.
- No production data, deployment, local database or non-main branch changed.

---

## 2026-11-13 — Android offline installation path and exact-head CI reconciliation

- Application/test source head: `805f210c96565899d8d7d5347b80d5461e401756`.
- Exact-head Laravel workflow [43499203157](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43499203157): **PASS**. Laravel tests, PostgreSQL migration/registration/backup-recovery and the dedicated offline-browser acceptance job all completed successfully.
- The offline-browser job executed the workflow step `Run offline browser acceptance suite` successfully after the workflow was updated to run both `e2e/pwa-offline.spec.js` and `e2e/offline-attachment-sync.spec.js`.
- Zazu quality [43499203207](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43499203207), CodeQL/push-on-main [43499203169](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43499203169), Director Contract Lint [43499203248](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43499203248), and Director Health [43499203355](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43499203355): **PASS**. SonarCloud was not triggered by the docs-only changes; no SonarCloud pass is claimed.
- Added `docs/ZAZU_ANDROID_OFFLINE_INSTALL_ACCEPTANCE.md` with the existing Windows Caddy HTTPS launcher flow, Android CA trust, install-from-HTTPS requirements, and a bounded physical disconnected/restart/reconnect acceptance sequence. Commit: `10c40e35ca174c7659bdd973126c8036eb714ca8`.
- Confirmed existing launcher scripts start Laravel on loopback behind Caddy's internal HTTPS CA; the old plain-HTTP LAN IP cannot register the service worker. HTTP and HTTPS are separate browser origins and do not share service-worker or PWA installation state.
- **Physical Android acceptance remains OPEN.** CI success does not prove the CA is installed/trusted on the actual phone, the app is installed from the HTTPS origin, or that it cold-launches and supports offline workflows after the PC server stops. Follow the new runbook; record the physical results before closing this gate.
- No production code, live database, customer data, deployment or non-main branch was changed in this Android setup documentation cycle.

---

## 2026-11-13 — exact-head CI closure and V1 gate reconciliation

- Verified current `main` head: `5888123f1941beb478b31870a6e782e0f8ef8053`.
- Exact-head Laravel workflow [43491595380](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43491595380) passed: 486 tests passed, 1 skipped, 3,475 assertions; PostgreSQL migration/registration/backup-restore and dedicated offline-browser acceptance all passed.
- PHPMD [43491595199](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43491595199), Psalm [43491595173](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43491595173), quality/secret scan/CodeQL [43491594809](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43491594809), and populated upgrade/rollback [43491595120](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43491595120) passed.
- PHPMD's 128-line archive round-trip test finding was resolved by extracting fixture setup and restore assertions into private helpers; no suppression or production behavior change.
- Business archive restore is automated-verified on this exact head. Installation-level recovery is CI-verified but the controlled installed-target operator drill remains open.
- Automated offline session transition, active Work attachment flow, and seeded commercial reconciliation are green. Real physical-device acceptance remains open.
- POPIA operational controls/privacy publication, retention and incident procedures, and Zazu name/trade-mark clearance remain open. No legal or brand clearance is claimed.
- No deployment, live database, customer data, or non-main branch was changed.

---

## 2026-11-13 — archive restore source review (follow-up)

- Re-read the complete current `BusinessArchiveService.php`, `BusinessArchiveTest.php`, tenant-parent migration and Laravel workflow from `main`.
- Structural review: archive allowlist order places suppliers/events before purchase orders and purchase orders before purchase-order items; restore remaps each FK from the original archived row, including columns covered by both simple and composite tenant constraints. File restore writes are tracked for cleanup if the DB transaction fails. Existing target-empty and sync-device guards run both before and inside the locked transaction.
- Regression fixture covers supplier → purchase order → purchase-order item remapping, purchase-order event relationship, cross-tenant export exclusion, private attachment bytes, public catalogue image bytes, non-empty-target rejection and paired-device rejection.
- The focused test asserts 28 allowlisted tables, 11 restored records and 2 file payloads.
- No new source defect was confirmed by this read-only review. This is **not** a runtime pass: the connected GitHub interface exposes no workflow-dispatch or repository-wide workflow-run listing, combined commit status has no contexts, and the commit-workflow wrapper only returns PR-triggered runs. Do not run the permanently loading browser smoke as a substitute.
- Next meaningful proof is the existing Laravel workflow's `php artisan test` job and PostgreSQL migration/backup recovery job on the current `main` head. The browser acceptance job is a separate relevant gate; routine browser smoke remains excluded unless a browser-facing change warrants it.
- No application files, databases, deployments, customer data or non-main branches were changed in this review.

---

## 2026-11-13 — V1 status-document reconciliation

- Reconciled `docs/ZAZU_V1_STATUS.md` and `docs/V1_RELEASE_CHECKLIST.md` with the live archive implementation. Their older sections incorrectly described business archive restore as not implemented; the new leading entries supersede that historical text.
- Current archive disposition: **IMPLEMENTED / EXECUTION UNVERIFIED**. Service correction: `51bba91ddfe4f438b4b979c14f5e67e6864ac048`; focused overlap-FK regression fixture: `e4daa4d1b12378479b2990360877f964719d0a5a`.
- Both documentation writes were made directly to `main`, then re-fetched and verified. Commits: `a9813fc5b4b73216239080e3a899842914dea486` and `79d5c1ba6d8e5345d9de8d06fdad1ae551930d1e`.
- The archive round-trip and safety tests remain unverified. The latest workflow efficiency changes also require Actions evidence; no CI pass is claimed.
- No application runtime, customer data, database, deployment configuration or deployment branch was changed.

---

## 2026-11-13 — Director verification efficiency audit and CI noise reduction

- Audited the live main workflows and test matrix after the owner identified repeated browser smoke as wasted effort.
- Confirmed the Zazu quality application job duplicated the Laravel workflow's PHP dependency install, frontend build, Blade/config checks, SQLite migration and complete PHPUnit run. Reduced Quality to the unique service-worker Node regression plus secret scanning; no unique test coverage was removed.
- Confirmed full Playwright browser regression was triggered on every main push even though the dedicated offline-browser acceptance job already runs on main. Changed full browser smoke to run only for browser-relevant pull requests or an explicit manual release/diagnostic dispatch.
- Kept the main-push Laravel suite, PostgreSQL migration/backup-recovery checks and focused PWA offline-browser acceptance.
- Verification policy: target the changed surface first; no repeat green suite without changed inputs; no browser smoke for routine backend/docs changes; classify the first failure before retry; never count a retry as a pass.
- Exact repository HEAD before these efficiency edits: 45361e3acd876550051078fb77df3ab9f9dedc9c. These workflow/documentation edits require their own GitHub Actions result; no CI pass is claimed here.
- No application runtime code, business data, database, deployment configuration or customer records were changed.

---

## 2026-11-13 — Business archive duplicate-FK remapping correction

- Current application/test commits on `main`: service fix `51bba91ddfe4f438b4b979c14f5e67e6864ac048`; focused regression test `e4daa4d1b12378479b2990360877f964719d0a5a`.
- Root cause identified by schema inspection: some tenant-owned columns participate in both a simple foreign key and a composite `(business_id, foreign_id)` tenant constraint. The restore loop previously read the already-mutated row value on each pass. After the first constraint remapped the source ID, a second constraint tried to look up the new target ID in the source ID map and raised a false unresolved-FK error.
- Correction: FK resolution now always reads the original archived `$sourceRow[$column]` value. Repeated constraints therefore resolve to the same target ID; explicit `business_id` rebinding remains separate and unchanged.
- Regression coverage: the archive round-trip fixture now includes a supplier, event-linked purchase order, and purchase-order item. It asserts target event, supplier and purchase-order references are correctly remapped, exercising the simple + composite purchase-order FK overlap. Expected restored record count is now 11.
- Source and test files were re-fetched from GitHub and the exact snippets verified. Combined status returned no contexts and the available commit-workflow lookup returned no runs. The Laravel workflow is configured for pushes to `main`, but this tool view does not expose a run for the new commit yet. PHPUnit execution remains **PENDING / UNVERIFIED**; do not mark archive restore accepted until the focused test and safety tests pass.
- No business records, live database, deployment configuration or customer data were changed.

---

## 2026-11-13 — Director source-lineage reconciliation

- Canonical branch: `main`; latest repository documentation commit observed: `193b4584f894bacd3107df926135049197bbcad1`.
- Latest application/test source checkpoint observed: `0eb03bdb206e0a36dffdc6ba8b00c38877f2c773`. The newer commits through the documentation tip are documentation-only; do not describe the old `35ad012` checkpoint as current source HEAD.
- Offline startup fixture CASE-ZAZU-0030: the correction from `b1da7595665a4f2d4b7ec8766d58c67440495f73` is present in current `e2e/pwa-offline.spec.js`; the test awaits `waitForOfflineRuntime(page)` before reading `window.ZazuOffline.ready`. This confirms source presence only. The available commit-workflow lookup returned no run for that correction, so exact-head browser/offline acceptance remains **UNVERIFIED** and the case stays open.
- Offline session transition and active Work attachment acceptance remain supported by the recorded exact-main run at `245aa47f7ef74e6e133fff867670ecafabb603b8`. Later source changes in the observed application/test lineage were archive-focused; do not extend the old run's claim to the new archive implementation.
- Business archive restore remains **OPEN / NO-GO**. The unresolved FK mapping still needs the exact focused-test exception from a run that includes `BusinessArchiveService.php` and `BusinessArchiveTest.php`. Historical green CI at `245aa47` predates those files and is not archive evidence.
- No application runtime code, business data, database, deployment setting, or customer record was changed by this reconciliation.

---

## 2026-11-13 — Current Director checkpoint: business archive restore mapping

- Canonical repository/ref: `Leano-Jordan/ZazuEMP` / `main`.
- Current observed source HEAD: `35ad01270a3ebd9de44bfcb37d28ae1e0f9096eb`.
- The owner-only business archive export/import path is present in current source. The implementation includes tenant-scoped table selection, business-ID rebinding, linked-record ID remapping, attachment/media payload restoration, bounded archive entry/size checks, opaque restored paths, and refusal to restore into a non-empty or already-paired workspace.
- The focused `BusinessArchiveTest::test_business_archive_restores_linked_records_and_private_attachments_into_an_empty_workspace` is the acceptance target. Its latest restore attempt exposed an unresolved foreign-key mapping. Commits `46e46a6` and `35ad012` respectively preserve explicit tenant-key rebinding and expose the exact unresolved table/column/target plus available ID maps in the exception.
- This is a real unresolved acceptance defect, not a verified pass. The current combined-status lookup for HEAD returned no status contexts; exact-HEAD Actions results are therefore **UNVERIFIED**. Do not infer a pass from earlier CI.
- Next bounded action: obtain the exact failing relationship from the focused test/CI output, correct the owning FK dependency/remapping rule, then rerun the archive round-trip and safety cases. Keep the target empty-workspace and unpaired-device guards intact.
- No physical customer data, database, deployment setting or `deploy/render-staging` branch was changed during this inspection.

---

## 2026-11-13 — Exact-main offline session, attachment and commercial acceptance

- Canonical application/test HEAD: `245aa47f7ef74e6e133fff867670ecafabb603b8`.
- Offline session/browser matrix: **PASS** at exact head. Full Playwright browser smoke [43473670319](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43473670319) completed successfully. Current `e2e/offline-session-transition.spec.js` covers unreachable logout host preservation, same-business account hand-off from owner to manager, and expired web-session re-authentication while preserving trusted device pairing and queued work.
- Business-switch trust boundary: **PASS**. `BusinessContextSecurityTest::test_switching_business_cannot_rebind_a_trusted_device_to_another_business` verifies reusing an installation ID in another business returns HTTP 409, leaves the original business device/token unchanged, and requires a separate registration for the second business. Laravel suite passed.
- Active Work attachment E2E suite: **PASS** as part of the full browser smoke. Existing tests cover offline queue, reconnect upload, server-id reconciliation, duplicate retry, private-file caching, stale-response fences after logout, and cross-tab re-pair. The active Work screen is the tested path; `public/offline-attachments.js` remains a dormant legacy file.
- Multi-customer sync identity hand-off: **PASS** in current Laravel/offline-browser CI. Two queued customer creates receive distinct server IDs, with idempotent replay creating no duplicate customers or identity rows.
- Laravel [43473670275](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43473670275), PostgreSQL migration/registration/database+private-file backup restore and dedicated offline-browser-acceptance jobs: **PASS**.
- Zazu quality [43473670378](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43473670378), Psalm [43473670232](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43473670232), PHPMD [43473670420](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43473670420), populated upgrade/rollback [43473670272](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43473670272), push-on-main [43473671064](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43473671064), and Director Health [43475031359](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43475031359): **PASS**.
- SonarCloud [43473670372](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43473670372): **SKIPPED**, not counted as a pass.
- Commercial reconciliation: **AUTOMATED PASS** through `CommercialReconciliationTest` on this Laravel run. The populated scenario accepts a ZAR 11,500 quote/invoice, reconciles a R3,450 deposit and R8,050 final payment, records R2,800 actual event cost, and asserts R8,700 profit with 75.65% margin. This is a seeded automated scenario, not proof against the owner's live business records.
- Recovery remains **PARTIAL**: installation-level CLI backup/restore and PostgreSQL database/private-file round-trip are implemented and CI-tested. Tenant-level business archive export/import is now implemented in source with owner-only controls, tenant-scoped datasets, ID remapping, file payload handling and empty/unpaired-target safeguards. Its linked-record + attachment round-trip acceptance is **NOT YET PASSING**; the current `main` diagnostic commit identifies an unresolved foreign-key mapping during restore. Do not count tenant archive recovery as accepted until the focused round-trip passes on exact HEAD. The controlled installed-environment restore drill remains **OWNER VERIFICATION REQUIRED**.
- Physical-device acceptance remains **UNVERIFIED**: real phone/tablet install on the selected secure origin, disconnected use, force-close/reopen, attachment persistence and reconnect reconciliation. Browser automation does not close this.
- Existing two customer records in the owner's IndexedDB remain a device-side reconciliation check; do not recreate them.
- POPIA operational controls and Zazu name/trade-mark clearance remain open. No numeric evidence score uplift is claimed; retain **71/100** pending formal re-score.
- No production/customer data was modified by this test cycle.

---

## 2026-11-13 — Multi-customer identity reconciliation guard (a6cd9b87)

- Main HEAD: `a6cd9b8776f5917c942a44f55daac7b2db793ada`.
- Extended the server-side push identity handoff feature test from one customer to two independently queued customer creates. It now asserts each local UUID maps to its own persisted customer/server ID, the IDs are distinct, and replaying the exact same mutation batch creates no duplicate customers or identity rows.
- This complements the browser registration-retry test added in `b72b28b07fc`, which checks that two local customer records reconcile independently after primary-device registration retry.
- New exact-HEAD CI is running: Laravel `43471194715`, browser smoke `43471194418`, quality `43471194594`, Psalm `43471194578`, PHPMD `43471194532`, populated upgrade/rollback `43471194613`, Director health `43471194860`, SonarCloud `43471194797` (in progress at last inspection).
- Earlier exact-HEAD `72dded07` Laravel, offline-browser-acceptance, PostgreSQL migration/recovery, quality, Psalm, PHPMD and Director checks passed. Its browser-smoke run `43470319049` remained in progress for an abnormally long period; this is not counted as a pass. The new HEAD has its own browser-smoke run.
- No production sync code or customer data was changed in this step; only the server-side regression test was strengthened. No deployment or Render action.
- Physical phone/PC acceptance and the two real IndexedDB customer reconciliation remain owner-device checks; CI cannot inspect the user's local browser database.

---

## 2026-11-13 — Exact-main automated verification reconciliation (f0598324)

- Canonical repository/ref: `Leano-Jordan/ZazuEMP` / `main`.
- Exact current main HEAD: `f05983240ba61cfba4b545c85369e4c4ccc0ad99`.
- Exact-HEAD Laravel workflow [43452907338](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43452907338): **PASS**. PostgreSQL migration/registration/backup-restore job and offline-browser-acceptance job also **PASS**. Laravel job explicitly passed frontend build, LAN Vite asset-origin configuration test, Blade compilation, built-manifest validation, configuration validation and PHPUnit/Pest.
- Full browser smoke [43452907654](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43452907654): **PASS**.
- Zazu quality + secret scan [43452907356](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43452907356), Psalm [43452907402](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43452907402), PHPMD [43452907248](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43452907248), populated upgrade/rollback [43452907310](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43452907310), Director Contract Lint [43452907398](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43452907398), push-on-main [43452907244](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43452907244), and Director Health [43454290881](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43454290881): **PASS**.
- SonarCloud [43452907264](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43452907264): **SKIPPED**, not counted as a pass.
- The LAN Vite origin configuration/build contract is now automated and passed at this exact main HEAD. This does not prove the owner's local PC/phone rendered presentation; the Vite server restart and rendered desktop/mobile check remain **OWNER DEVICE ACCEPTANCE / UNVERIFIED**.
- Offline browser and sync retry tests passing do not prove the owner's two existing IndexedDB customer records have reached the server. Their local queue and server records remain inaccessible to GitHub CI; do not recreate those customers.
- V1 evidence score remains **71/100**. Technical V1 remains **NO-GO** pending the bounded physical installed-device acceptance and final release decision. Commercial subscription/legal/brand certification remains separate.
- No deployment, Render service, database or customer data was changed. `deploy/render-staging` remains untouched.

---


## 2026-11-13 — LAN Vite asset-origin regression guard

- Added `tests/js/vite-config.test.js` to exercise the actual Vite config callback with a LAN HMR host, derived asset origin, and explicit asset-origin override. The test restores environment variables after execution.
- Added `npm run test:vite-config` and wired it into the Laravel CI workflow immediately after the frontend build, so the config behaviour is tested rather than merely syntax-checked by `vite build`.
- Commits: `6996004dc2f3b9c99b4ab8c5f1fd4eaab260e64d` (test), `285e49dde9ba3bc86c89036d5ab22a028df766de` (script), `f45fdde3e6e2aa61d28698fe4e49fe6ebd5339da` (CI).
- Source re-fetch/reconciliation completed. Combined status currently reports the available contexts; no pass is claimed until the workflow result is confirmed.

## 2026-11-13 — LAN Vite asset-origin correction

- Owner screenshot showed the authenticated Blade shell rendering without its CSS/JavaScript presentation. The failure is asset delivery, distinct from the shared CSS-token cascade correction above.
- Source inspection found that `vite.config.js` bound Vite to `0.0.0.0` and allowed `VITE_HMR_HOST` to change only the HMR websocket host. That did not guarantee generated CSS/JS asset URLs used the PC's LAN-reachable address.
- Corrected `vite.config.js`: `server.origin` now uses `VITE_DEV_SERVER_ORIGIN`, or derives `http://<VITE_HMR_HOST>:5173` when the existing LAN host override is set. HMR and asset origin therefore share the intended host.
- Documented the optional local `.env` values in `.env.example` and the exact LAN setup / built-asset fallback in `docs/LOCAL_PC_HOST_DEPLOYMENT.md`.
- Commits: `45969f6cd418f5ca35fd5d0aa185995707ed12f1` (Vite config), `f14631af2c977a087576d260e16442abd5a068cf` (`.env.example`), `8165b821ea01403271b717162c2eafe79758243e` (deployment notes). All writes targeted `main`; each changed file was re-fetched and reconciled.
- Earlier quality run `43437312175` failed before the namespace correction because `ApplySecurityHeaders.php` contained doubled namespace separators; commit `44b3aad31dcccfcb34471cbeb5d9d547562b6c78` corrected the PHP source and `fe4c21622a16621c1a9e2baae5395b135cf5007f` recorded APP-001. That failed run is not evidence against the later corrected source.
- Verification boundary: latest Vite config, environment example, deployment notes and middleware were re-fetched from `main`. Exact-head PHPUnit/quality/Vite build and rendered PC/phone acceptance after these changes remain **UNVERIFIED**. Do not claim the screenshot issue is closed until the Vite server is restarted with the LAN origin configured and the rendered page is checked.
- No customer data, database, Render service or `deploy/render-staging` was changed.

### Vite environment-loading correction

- Follow-up inspection caught that Vite config evaluation does not automatically populate `process.env` from the project's `.env` file. Updated `vite.config.js` to use Vite `loadEnv(mode, process.cwd(), 'VITE_')`, while retaining process-environment fallback, so the documented LAN origin settings are actually read.
- Commit: `942bdaab89ae745fd445b7dc7a316e78104c09fb`. Exact-head build and rendered verification remain pending.

## 2026-11-13 — Shared UI token cascade correction

- Owner-reported UI styling/structure failure routed through the UI recovery engine.
- Root cause confirmed in source: `resources/css/zazu-product-authority.css` contained competing light/dark `:root` token systems loaded after `app.css`. Those late declarations silently replaced the canonical palette, typography/surface relationships and navigation colours. This contradicted the Director CSS authority contract.
- Corrected on `main`: consolidated the effective product light/dark and semantic tone tokens into the existing canonical `:root` blocks in `resources/css/app.css`; removed all token-root blocks from `zazu-product-authority.css`. The product stylesheet now remains a component extension, not a second token authority.
- Updated `tests/Feature/UiVisualSystemRegressionTest.php` to assert that shared palette/semantic tokens live in `app.css` and that the product extension contains no light/dark root blocks.
- Source re-read after writes: `app.css` has exactly one light and one dark root; each contains unique custom-property declarations. `zazu-product-authority.css` has zero root blocks. CSS brace balance passed. Relevant source commits: `c5dc829`, `92056ff`, `4860b3e`, `887e970`, `82ce6ce9`, `df2cf7d`, `3f60ae9`.
- Latest test assertion correction: `e524ff352198fcd07c16afaf7a0906b7c4a37d94` moves all semantic-tone token checks to the canonical `app.css` assertion loop. Exact-head CI remains unavailable through the current status lookup.
- Verification boundary: GitHub combined status returned no status contexts for the latest test commit; PHPUnit/Vite build and rendered desktop/mobile + light/dark acceptance are **UNVERIFIED**. Do not claim visual acceptance until rendered evidence is inspected.
- No routes, business data, database, deployment settings, Render service or `deploy/render-staging` were changed.

## 2026-11-13 — Paid entitlement expiry continuity regression added

- Target: verify an expired Advanced paid entitlement resolves the effective presentation tier to Basic while preserving each member's saved Advanced preference.
- Change: added `test_expired_paid_entitlement_falls_back_to_basic_without_overwriting_member_preference` in `tests/Feature/ExperienceLevelTest.php`.
- Invariants covered: expired paid entitlement clamps available presentation to Basic; the saved membership preference remains unchanged; no customer records or permissions are mutated.
- Commit: `c41ea1cbb16f7eae4cc72459469f07d2e4a076e2`.
- Verification: source re-fetched and reconciled. Test execution and exact-head CI are **PENDING / UNVERIFIED**; do not count this regression as passed until Actions evidence is inspected.
- Runtime application code, licence records, customer data and deployment settings were not changed.
 
---

## 2026-11-13 — Exact-head V1 regression reconciliation (verified)

- Verified application/test source SHA: `6303ff59effe95ad95d5df862ff823b01b49d891` on `main`.
- Laravel [run 43426697007](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43426697007): **PASS — 476 passed / 3,383 assertions / 1 skipped**.
- Dedicated offline browser acceptance: **PASS — 22 passed** (same Laravel workflow).
- PostgreSQL 16 migration/registration/recovery: **PASS** — full migrations, registration (**1 test / 13 assertions**) and populated database/private-file backup-restore (**1 test / 5 assertions**) completed.
- Full browser regression [run 43426696768](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43426696768): **PASS — 86 passed / 46 skipped / 0 failed / 0 flaky**; 7.1 minutes.
- Zazu quality + secret scan [run 43426696823](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43426696823), Psalm [run 43426696750](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43426696750), PHPMD [run 43426696734](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43426696734), populated upgrade/rollback [run 43426696904](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43426696904), Director Contract Lint [run 43426696909](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43426696909), Director Health [run 43426696562](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43426696562) and CodeQL analysis [run 43426696578](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43426696578): **PASS**.
- SonarCloud [run 43426696691](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43426696691): **SKIPPED**, not counted as a pass.
- The only source/test change in this cycle is the two Playwright fixture preconditions in `e2e/pwa-offline.spec.js`; production runtime source and customer data were not changed. CASE-ZAZU-0029 is closed at this exact head.
- Evidence-maturity score remains **71/100**; no score uplift is awarded for test-fixture corrections.
- Technical V1 remains **NO-GO only for the bounded real installed-device acceptance and final release decision**: trusted HTTPS origin, physical phone/tablet install, force-close/relaunch offline, local save persistence and reconnect sync exactly once. Browser emulation is not physical-device proof.
- Commercial subscription certification remains separate: production issuer/key custody and installation identity/reactivation, approved module matrix, legal/privacy operations and brand clearance are not complete.
- No deployment, database mutation or Render change occurred; `deploy/render-staging` remains untouched.

---

## Director overdrive verification — 2026-11-13

- Exact test/application baseline: `785b544e3cf49a10f19c510b5e5e7ef8c5e4d9a6`.
- Regression additions: Helper attention business isolation, terminal-job filtering and role permission; report metric tenant isolation; search domain-permission filtering.
- Laravel suite: **PASS — 466 passed / 3,352 assertions / 1 skipped**. Dedicated offline browser acceptance: **PASS — 19 passed**. PostgreSQL migration/registration/recovery: **PASS**.
- Quality + secret scan, Psalm, PHPMD, populated upgrade/rollback, Director Health/Contract Lint and push-on-main: **PASS**. SonarCloud: **SKIPPED**.
- Full browser regression was still running when the audit report was revised; do not mark this head fully reconciled until its result is recorded.
- Evidence score updated from **69/100 to 71/100** (46/65): only the combined Search/Reporting/Helper domain moved from 3/5 to 4/5 after the new regression coverage passed. No runtime code changed in this cycle.
- Physical installed-device acceptance, recovery/operator drill and commercial/legal/brand tasks remain owner/external gates. No deployment was performed; `deploy/render-staging` remains untouched.

---

## Director full-scale V1 audit — 2026-11-13

- Audit report: [`docs/DIRECTOR_FULL_AUDIT_2026-11-13.md`](../../docs/DIRECTOR_FULL_AUDIT_2026-11-13.md).
- Audited application/test evidence baseline: `2d7672103ee4b51d28100383f3311111b493deea`; report-only commit: `10a3a5dace4c4100e5af7e397bb29706f8f5c273`.
- Evidence-maturity score reaffirmed at **69/100 (45/65 across 13 domains)**. No uplift claimed.
- Latest exact-head Laravel, offline browser, full browser, PostgreSQL migration/recovery, quality/secret scan, Psalm, PHPMD, populated upgrade/rollback, Director Contract Lint/Health and push checks passed; SonarCloud skipped. Run IDs are in the audit report.
- V1 remains **NO-GO for release certification** until owner physical installed-device acceptance on the selected trusted HTTPS origin and the final bounded regression/release decision.
- Commercial subscription launch remains distinct: production issuer/key custody and feature enforcement, final offer/tier matrix, privacy/legal operations and official Zazu brand clearance are not certified.
- Owner and engineering action split is recorded in the audit report. No runtime source, deployment setting, or `deploy/render-staging` branch changed during this audit.

---

## Director exact-head verification — 2026-11-13 (PWA offline navigation fixture)

- Canonical repository/branch: `Leano-Jordan/ZazuEMP` / `main`.
- Exact application/test HEAD: `282a4eb366956dfb49023cd5edc20999f5ead3cb`.
- Latest source change is test-only: `tests/js/service-worker-auth-cache.test.js` now primes the authenticated dashboard online before disabling the host and asserting offline navigation. No runtime application source or deployment configuration changed.
- Laravel [43367980312](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43367980312): **PASS — 462 passed / 3,339 assertions / 1 skipped**.
- PostgreSQL migration, account/workspace registration, backup/restore/private-file recovery: **PASS** (same workflow).
- Dedicated offline browser acceptance: **PASS**.
- Full browser regression [43367980364](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43367980364): **PASS — 83 passed / 40 skipped / 0 failed**.
- Zazu quality + secret scan [43367980476](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43367980476), Psalm [43367980426](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43367980426), PHPMD [43367980408](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43367980408), populated upgrade/rollback [43367980313](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43367980313), push-on-main [43367979917](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43367979917), and Director Health [43368786394](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43368786394): **PASS**.
- SonarCloud [43367980435](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43367980435): **SKIPPED**, not counted as a pass.
- The exact-head CI evidence is reconciled in `docs/V1_RELEASE_CHECKLIST.md`. No deployment was performed; `deploy/render-staging` remains untouched.
- Remaining bounded acceptance: real installed-device offline launch, local persistence across force-close/relaunch, reconnect sync exactly once, and phone/tablet results recorded against the selected secure HTTPS release origin. Browser emulation is not physical-device proof.
- Scope remains frozen by `.ai/engineering/V1_SCOPE_FREEZE.md`; do not invent new gates or broaden offline scope without a reproducible V1 failure.

---

## Director exact-head verification — 2026-11-12 (Work completion guards + offline customer-to-Work)

- Exact application/test-source HEAD: `7193038a2b2d14ea71278050a0d7c26ce246bf86` (merge commit; the later `main` descendants `453c80d` and `78fb793` change only these two evidence documents; includes the test-only commits `e3855998`, `b2e548be`, and offline acceptance correction `9bd1cc0d`).
- Laravel workflow [43298535995](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43298535995): **PASS — 462 passed / 3,339 assertions / 1 skipped**. The three focused Work completion-guard tests pass: completion is rejected while preparation is open or blocked, costs remain planned, or a linked purchase order is active. No application runtime change was needed; existing guards were locked with regressions.
- Offline browser acceptance: **PASS — 19 passed** (same Laravel workflow).
- PostgreSQL migration/recovery smoke: **PASS** (same workflow; both PostgreSQL-focused test steps passed).
- Full browser smoke [43298535961](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43298535961): **PASS — 83 passed / 40 skipped / 0 failed**. Inspected job log: 123 tests, no retry/failure lines. Offline customer-to-Work journey now explicitly disconnects the browser before local writes and checks the queued customer ID is the one linked by the Work record.
- Zazu quality/secret scan [43298535431](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43298535431): **PASS**.
- Psalm security [43298535526](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43298535526): **PASS**.
- PHPMD [43298536000](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43298536000): **PASS**.
- Populated upgrade/rollback [43298535460](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43298535460): **PASS**.
- Render Docker image build [43298535911](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43298535911): **PASS — build only; no deployment**.
- Push-on-main [43298535997](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43298535997): **PASS**.
- Director Health [43299843826](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43299843826): **PASS**.
- SonarCloud [43298536001](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43298536001): **SKIPPED**, not counted as a pass.
- Exact-head automated source verification is reconciled. No deployment or deployment-branch change occurred; `deploy/render-staging` remains untouched.
- Known limitations are now recorded in `docs/V1_KNOWN_LIMITATIONS.md` and linked from the release checklist. Bounded next acceptance: physical installed-device workflow on the owner's secure HTTPS origin, then issue the V1 decision. Do not expand scope or invent extra gates.

## Director exact-head CI reconciliation — 2026-11-12 (offline customer-to-Work regression)

- Exact application/test-source HEAD: `2b171f3fda4ae1ddeaf30700dc3f9c414866be92` on `main`.
- Browser smoke: **PASS — 83 passed / 40 skipped / 0 failed**, run [43295881449](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43295881449). Inspected job log confirms 123 tests, no retries reported, and successful completion. The offline customer-to-Work regression now blocks the actual `X-Zazu-Connectivity-Probe` HEAD request and asserts the probe was intercepted before accepting the local-save confirmation.
- Laravel workflow [43295881102](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43295881102): **PASS** — Laravel tests, PostgreSQL migration/registration/recovery and offline-browser-acceptance jobs all completed successfully.
- Zazu quality/secret scan [43295881101](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43295881101): **PASS**.
- Psalm security [43295881418](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43295881418): **PASS**.
- PHPMD [43295881090](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43295881090): **PASS**.
- Populated upgrade/rollback [43295881378](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43295881378): **PASS**.
- Push-on-main / CodeQL [43295879914](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43295879914): **PASS**.
- SonarCloud [43295881289](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43295881289): **SKIPPED**, not counted as a pass.
- No application runtime code changed in this regression correction; the change is confined to Playwright test determinism. No deployment branch or service was changed.
- Automated source verification for this exact HEAD is reconciled. Remaining bounded V1 acceptance is physical installed-device offline workflow on the owner's secure HTTPS origin, followed by final regression, limitations record and release decision. Do not expand scope or reopen unrelated gates without new evidence.

## Director regression correction — 2026-11-12

- Review of the exact-head browser job logs found one **flaky retry**, despite the suite ending with 82 passed / 40 skipped. The first attempt of `e2e/offline-session-transition.spec.js` failed because the test accessed `window.ZazuOffline.ready` immediately after the dashboard heading appeared, before the offline script had exposed its API. The retry passed.
- Corrected the test setup to explicitly poll for `window.ZazuOffline?.ready` before beginning the session-preservation journey. No application runtime code changed.
- Fix commit: `185628d9c7974cb25dbd81798303593a75f7c998`.
- Exact-head CI is running: Laravel `43294074147`; browser smoke `43294074265`; quality `43294074168`; Psalm `43294074314`; PHPMD `43294074198`; populated upgrade/rollback `43294074269`. Do not mark the correction verified until these finish, especially the browser smoke run.
- This is correction attempt 1 for the observed test race. Do not broaden the change unless the rerun supplies new evidence.

## Director exact-head CI reconciliation — 2026-11-12 21:45 SAST

- Exact application/test-source SHA: `5924fd6d294187ec482295193c4add865e64137b`.
- GitHub Actions query by exact `head_sha`: 11 workflow runs returned. All required workflows completed successfully:
  - Laravel: **459 passed / 3,331 assertions / 1 skipped** (run [43290913503](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43290913503)).
  - PostgreSQL migration, account/workspace registration and PostgreSQL backup/restore/private-file recovery job: **PASS** (same Laravel workflow).
  - Offline browser acceptance: **19 passed** (same Laravel workflow).
  - Full browser smoke: **82 passed / 40 skipped / 0 failed** (run [43290913416](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43290913416)).
  - Populated database upgrade and rollback: **PASS** (run [43290913571](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43290913571)).
  - Zazu quality + secret scan: **PASS** (run [43290913644](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43290913644)).
  - Psalm security: **PASS** (run [43290913547](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43290913547)).
  - PHPMD: **PASS** (run [43290913940](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43290913940)).
  - Director Health: **PASS** (run [43290913608](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43290913608)).
  - SonarCloud: **SKIPPED**, not counted as a pass.
- Job logs were inspected, not merely inferred from workflow summaries. The Laravel summary confirms 459 passed; offline browser job confirms 19 passed; full browser job confirms 82 passed / 40 skipped.
- The Android resume reconnect regression is included in the offline-browser acceptance suite and passed with the suite.
- The earlier CI uncertainty for this application/test SHA is closed. The documentation-only commits in this reconciliation cycle do not alter application or test source.
- Next bounded acceptance: inspect and record the agreed seeded core workflow coverage, then the remaining physical installed-device offline acceptance on the owner's secure HTTPS origin. Do not create additional speculative gates.
- No Render deployment or deployment-branch change was performed.

## Director exact-head reconciliation — 2026-11-12 21:38 SAST

- Canonical engineering repository/branch: `Leano-Jordan/ZazuEMP` / `main`. Latest application/test-source commit in this checkpoint: `5924fd6d294187ec482295193c4add865e64137b`. Subsequent commits in this reconciliation cycle are documentation-only and do not alter application or test source.
- The prior Android resume-sync checkpoint `fbd57dd` is no longer HEAD. Twelve commits follow it. Relevant changes include: resume/reconnect sync handling (`f864fb3`); BFCache-only `pageshow` resume handling and regression (`2af1298`, `71ad943`); primary-device registration session-race guard (`6aa68e8`); offline client cache refresh and service-worker cache generation v35 (`8e348d5`); and matching service-worker test fixtures (`ea1fbe3`, `5924fd6`). Render configuration and its diagnostic records were also added, but no Render deployment was performed by these commits.
- Current source inspection confirms: local IndexedDB readiness resolves before network work; reconnect/resume triggers share one in-flight attempt; initial-load `pageshow` is not treated as resume; the resume browser regression models a BFCache-restored page; primary-device registration checks the captured session generation and installation identity before accepting its response; private media remains in user/business-scoped cache generation v35.
- Latest application/test-source commit `5924fd6` is test-fixture-only and updates service-worker cache assertions from v34 to v35.
- Exact-HEAD CI evidence for `5924fd6` is **NOT RECONCILED**. The GitHub combined-status lookup returned no status contexts; this is not evidence of a pass or failure. Do not attribute earlier green runs to this HEAD. The new resume/registration/cache changes remain **SOURCE-INSPECTED / AUTOMATED EXECUTION UNVERIFIED** until exact-head Actions results are observed.
- V1 scope remains frozen under `.ai/engineering/V1_SCOPE_FREEZE.md`. Do not expand into additional offline edge cases. Next bounded target: reconcile exact-head CI for the already-implemented resume/registration/cache changes, then continue the agreed core-workflow acceptance and final regression sequence.
- `deploy/render-staging` remains the deployment-only branch. No deployment or service configuration change is authorised by this engineering checkpoint.

## Director engineering update — Android resume sync — 2026-11-12

- Latest canonical `main` HEAD: `fbd57dd3985521a0890eadd8e98ea76438d9f0b8`.
- Updated `public/zazu-offline.js`: added a shared reconnect/resume handler invoked by `online`, `pageshow`, `focus`, and visible `visibilitychange`. It waits for local readiness, attempts primary-device registration when no token exists, then drains queued sync when a token is available. Concurrent resume triggers share one in-flight attempt.
- Added Playwright regression test in `e2e/pwa-offline.spec.js`: queued customer mutation must push and clear when the installed app resumes after the browser missed the original online event.
- CI on exact HEAD `fbd57dd`: Laravel tests **PASS**; PHPMD **PASS**; Psalm **PASS**; Zazu quality/secret scan **PASS**; populated PostgreSQL upgrade/rollback **PASS**; Director Health **PASS**. PostgreSQL migration, registration/workspace, backup/restore/private-file steps completed successfully within the running workflow. Browser regression job is still **IN PROGRESS**; do not mark the new resume test passed until the job completes successfully.
- Render service remains on `deploy/render-staging`, not `main`. Do not promote this change until the database connection is configured and browser CI is green. No deployment was triggered by these commits.
- Free Render Postgres `zazuemp-preview-db` (`dpg-dbqupp95efls73fto6u0-a`) is available, PostgreSQL 18, Oregon, expires 2026-12-12. The Render integration's service update tool can merge environment variables but cannot retrieve the database secret. Link the database using the internal connection URL through Render Dashboard → database Connect → Internal URL, then service Environment → `DATABASE_URL`; save and deploy only when ready. The service and database share the Oregon region, so use the internal URL. Do not expose the credential or commit it.
- No additional scope expansion. This is a targeted reliability fix for the owner's reported offline reconnect behavior.

## Director runtime reconciliation — Render database provisioning — 2026-11-12

- Owner approved creation of a free PostgreSQL database for the Zazu EMP Render workbench.
- Created `zazuemp-preview-db`: PostgreSQL 18, free plan, Oregon, resource `dpg-dbqupp95efls73fto6u0-a`; Render reports status `available`. Free instance expiry is 2026-12-12.
- Render web service remains `ZazuEMP`, resource `srv-dbqpa1tbedkc73ebgeo0`, region Oregon, deployment branch `deploy/render-staging`. No service environment variables have been changed and no deployment has been triggered.
- Source check on canonical `main`: `docker/render-entrypoint.sh` accepts `DATABASE_URL` as an alias for `DB_URL` and selects PostgreSQL from a postgres URL when `DB_CONNECTION` is unset. Existing source is ready for this connection.
- The Render database query integration cannot connect because the database IP allowlist is empty and the hosted MCP connection has no allowlistable egress IP. This does not indicate a database failure. The correct application path is the private/internal database connection from the same-region Render service.
- Render integration responses expose database name/user/status but not the connection URL/password. Therefore the database is provisioned but **not yet linked to the web service**. Do not guess credentials or place secrets in source.
- Next action: use Render Dashboard's service Environment page to add `DATABASE_URL` from the `zazuemp-preview-db` resource (select its internal connection string / from-database reference), preserving all existing variables. Then deploy and verify migrations + registration. No app-side source change is required for this link.
- No persistent disk was created. Render remains an interim workbench; do not treat it as production hosting or use real customer data.
- The Render service is still using whichever DB configuration its current environment selects; effective active connection remains **UNKNOWN** until the service is linked and runtime evidence is checked.

 
## Owner-directed finish rule — 2026-11-12 (binding)

- Owner has explicitly directed that Zazu V1 work must stop expanding into endless edge cases. This instruction was repeated after the same scope discussion occurred on 2026-11-11; preserve it as a permanent engineering constraint.
- Binding policy: `.ai/engineering/V1_SCOPE_FREEZE.md`. Director and every specialist must read it before each cycle.
- Objective: finish a dependable, usable V1—not theoretical perfection. Only reproducible defects in agreed V1 workflows, demonstrated security/privacy/data-integrity failures, or explicit owner-approved additions may enter scope. Speculative risks, optional polish, new features and future infrastructure are deferred.
- Finish sequence: close the known checklist blockers → verify the agreed core workflows → one final regression pass → fix only material failures → document limitations → issue release decision. Do not reopen closed gates without new evidence.
- Render is an interim remote-development workbench for access away from the home PC. It is not the final production destination and production hosting/persistence must not be treated as a current development gate.
- Decision rule: if it is not necessary for a customer to use the agreed V1 workflows reliably, it does not block V1.
- Latest verified code change at this record: `bed3941739cf02fc1a8152932292bc7d7ec68a21` (sync responses marked no-store; regression assertions added). The CI result for that commit was not available in the inspected workflow response; do not claim it passed until checked.
## Director current checkpoint — 2026-11-12 (PostgreSQL recovery)

- Canonical repository/engineering branch: `Leano-Jordan/ZazuEMP` / `main`; deployment-only `deploy/render-staging` remains preserved.
- Latest verified application/test source: `1cac0e51d72b506f7c58a1c06b9d204a521f81fa`. Documentation reconciliation descendants: `c5a1f3f` and `59ab428`; no application runtime source changed in those commits.
- Laravel: **458 passed / 3,319 assertions / 1 skipped — PASS**. PostgreSQL 16 migrations and registration/workspace acceptance: **PASS**. Real PostgreSQL database + private-storage backup/restore round-trip: **PASS — 1 test / 5 assertions**. Offline browser acceptance: **18 passed**.
- PHPMD, Psalm, Zazu quality/secret scan, populated upgrade/rollback, Render Docker image build, Director Contract Lint and Director Health: **PASS**. SonarCloud: **SKIPPED**, not counted.
- Full browser smoke for the documentation-only descendant `59ab428` is still running at this checkpoint; do not claim that run passed until its job completes. The prior exact application/test source browser run `43172547165` passed.
- Added PostgreSQL operator instructions and reconciled exact CI evidence in `docs/RELEASE_BACKUP_RESTORE_RUNBOOK.md` and `docs/V1_RELEASE_CHECKLIST.md`.
- PostgreSQL CI proves the automated test service only. Live Render DB credentials/privileges, persistent disk, independent off-host backup retention, real operator restore and deployment rollback remain UNVERIFIED. No Render service, database, disk or deployment branch was changed.
- `deploy/render-staging` currently diverges from `main` (68 commits ahead on main and 3 commits unique to staging at inspection). It is deployment-only and remains untouched; do not merge or rewrite it as routine engineering cleanup.
- Next highest-risk gate: controlled backup → restore → representative record/private-media/authorization verification → deployment rollback on the selected persistent hosting target. Secure-origin installed-device offline acceptance, trusted-device operations, legal/privacy, brand clearance and production licensing controls remain open. V1 remains **NO-GO**.

## Director current checkpoint — 2026-11-12

- Canonical repository/branch: `Leano-Jordan/ZazuEMP` / `main`.
- Current exact HEAD: `9fd5dd20edfe8eaad5a80681a895ac9a3f459283`.
- Exact-head Laravel: **456 tests / 3,305 assertions — PASS**; PostgreSQL migrations plus registration/workspace acceptance: **PASS**; offline browser acceptance: **18 passed**.
- Full browser smoke: **82 passed / 38 skipped / 0 failed — PASS**.
- PHPMD, Psalm, Zazu quality/secret scan, populated upgrade/rollback, Docker image build and Director Health: **PASS**. SonarCloud skipped; not counted.
- Previous browser run `43169885907` on `0b14c00` was cancelled; later exact-head run `43170382911` passed and supersedes it.
- No live Render service settings or deployment were changed. The deployment-only `deploy/render-staging` branch remains preserved.
- Next hard gate: real database + private-media backup/restore and deployment rollback against the chosen persistent target. Installed secure-origin offline acceptance and operational/legal/brand/licensing closure remain open. V1 remains **NO-GO**.

## Director execution — registration on persistent database — 2026-11-11

- Canonical repository/branch: `Leano-Jordan/ZazuEMP` / `main`.
- Application/workflow source head verified by CI: `1519a3219572db48eedc2cc3bedd83a3bbab8b33`; subsequent commits in this record are documentation-only.
- Corrected Dockerfile build verified by GitHub Actions: Render Docker build run `43092418612` — **PASS** (image build only; no deployment).
- Added a PostgreSQL CI acceptance step that executes the existing end-to-end account registration test against PostgreSQL after the complete migration set. This exercises user creation, business creation, owner membership, authentication session and dashboard access on the persistent database engine.
- Workflow commit: `1519a3219572db48eedc2cc3bedd83a3bbab8b33`. Changed workflow re-read from `main`; source reconciliation PASS.
- Exact-head Laravel workflow run `43092828074`: **PASS** — 455 tests / 3,299 assertions; PostgreSQL 16 complete migration set: **PASS**; real account/workspace registration on PostgreSQL: **PASS** — 1 test / 13 assertions; offline browser acceptance: **PASS** — 18 passed. Exact-head Director Contract Lint, PHPMD, Zazu quality, populated upgrade/rollback, Psalm and Director Health also reported PASS. SonarCloud was SKIPPED and is not counted as a pass. Separate browser-smoke run `43092827911` was cancelled and is not counted as a pass.
- Render workspace discovery returned one workspace, `My Workspace`. It has not been selected; no Render service, environment, database, disk, branch or deployment was changed. Owner confirmation is required before using that workspace.
- The live Render workspace registration failure is not yet root-caused. `AuthController::storeRegistration` reports the underlying exception to the application log but returns a generic validation message. A real Render log and persistent-database registration result are still required before changing application behavior.
- `deploy/render-staging` remains preserved. No new branches were created.
- Release position remains **NO-GO** until PostgreSQL registration succeeds, persistent hosting is configured and verified, and backup/restore plus installed-device offline acceptance are completed.

## Director execution — persistent Render database support — 2026-11-11

- Current source/control head at implementation: `acad170dc80d5cc35bea974cd393be3520a320ea` on canonical `main`.
- **Target:** remove the verified container-level blocker preventing Render from using durable PostgreSQL and make the full migration set exercise PostgreSQL in CI.
- `docker/render-entrypoint.sh` now honours explicit `DB_CONNECTION`, `DB_URL` / `DB_HOST`, session, cache and queue settings. It retains the existing disposable SQLite preview defaults when no persistent database is configured and fails closed for a non-SQLite connection without a database endpoint.
- `Dockerfile` now installs PostgreSQL PDO support alongside SQLite and asserts both drivers are present during the image build.
- `.github/workflows/laravel.yml` now runs the complete migration set against a PostgreSQL 16 service as a separate CI job.
- `docs/RENDER_PREVIEW.md` now documents the persistent Render configuration, including a managed PostgreSQL database and durable `/var/www/html/storage` mount.
- Changed commits: `b7d6782` (entrypoint), `5cb8865` / `109d9e9` / `1d88214` (Docker image and driver assertions), `94136a0` (deployment documentation), `be4a639` / `acad170` (PostgreSQL migration CI).
- Changed files were re-fetched from `main`. Source reconciliation: PASS. GitHub combined status lookup returned no status contexts; the new Docker build and PostgreSQL migration job are therefore **PENDING / UNVERIFIED** until their Actions runs are inspected.
- Render account resources were not inspected or changed because no workspace was selected in this session. No database, persistent disk, deployment setting, or service branch was changed. `deploy/render-staging` remains preserved.
- This is source capability, not proof of persistent hosting or recovery. No evidence-maturity uplift. V1 remains **NO-GO** until the Docker build and PostgreSQL migration job pass, a persistent target is configured, registration/workspace creation succeeds there, and restore/rollback are exercised against it.

## Exact-head reconciliation — 2026-11-11 17:18 UTC

- Current canonical repository: `Leano-Jordan/ZazuEMP`, branch `main`.
- Current exact HEAD: `6c486389ff4c4a9d8ee6f5ecff241ce391b30a8c` — documentation-only Director rule update. No application runtime source changed in this commit.
- Owner's strict project-continuity rule is recorded and re-read in `.ai/engineering/00_DIRECTOR_ENGINE.md`; Director Contract Lint passed on this exact head.
- Exact-head Laravel workflow: **PASS — 454 tests / 3,296 assertions** ([run 43066228702](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43066228702)).
- Offline browser acceptance sub-job: **PASS — 18 passed** on the same exact head (same run). It covers the current PWA/local workspace browser acceptance suite.
- Zazu quality + secret scan: **PASS** ([run 43066228679](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43066228679)).
- Psalm Security: **PASS** ([run 43066228744](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43066228744)).
- PHPMD: **PASS** ([run 43066228711](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43066228711)).
- Populated upgrade/rollback: **PASS** ([run 43066228670](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43066228670)).
- Director Health: **PASS** ([run 43066228700](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43066228700)).
- Push-on-main: **PASS** ([run 43066228774](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43066228774)).
- Director Contract Lint: **PASS** ([run 43066228713](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43066228713)).
- SonarCloud: **SKIPPED**, not counted as a pass.
- Full Zazu browser smoke: **IN PROGRESS** at reconciliation ([run 43066228703](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43066228703)); do not count it as passed until completion.
- The offline phone/PC installed-app test remains **UNVERIFIED**. CI uses Chromium and cannot prove actual Android certificate trust, installed-app cold launch, airplane-mode relaunch, physical storage persistence, or same-device reconnect.
- The 69/100 evidence-maturity baseline remains unchanged; this documentation-only commit and CI do not close a physical or operational release gate. V1 remains **NO-GO**.
- `deploy/render-staging` remains a protected intentional deployment branch; no deployment was triggered.

## Exact-head reconciliation — 2026-11-11 12:20 UTC

- Current canonical engineering head: `7609049d8a520a4abb3b0d8d7d0cd2d8b140e49e` on `main`.
- This head includes the verified offline sync identity correction at `56bb804` and a test-fixture cleanup at `7609049`; no application runtime source changed after the verified sync correction.
- Exact-head GitHub Actions: Laravel PASS — 448 tests / 3,258 assertions; offline browser acceptance PASS — 18 passed; Zazu quality PASS; Psalm security PASS; PHPMD PASS; populated upgrade/rollback PASS; browser smoke PASS; Director health PASS; push workflow PASS. SonarCloud was SKIPPED and is not counted as a pass.
- Browser smoke run: `43019394866`; Laravel + offline browser acceptance: `43019394816`; quality: `43019394988`; Psalm: `43019394910`; PHPMD: `43019394850`; populated upgrade/rollback: `43019394959`.
- Render Docker workflow did not run for this test-only head. The latest verified Docker build is run `43015390179` on the preceding application source; it built an image only and did not deploy.
- No physical installed-device test, customer-environment recovery drill or production deployment is evidenced. V1 remains **NO-GO**. The intentional `deploy/render-staging` branch remains untouched.

# ZAZU EMP — ENGINEERING CONTROL STATE

## Identity
- Product: Zazu – Event Management Platform
- Repository: Leano-Jordan/ZazuEMP
- Canonical branch: main
- Master control: Morpheus / Director
- Owner-facing nickname: Jarvis

## Authority
1. Explicit owner instruction
2. Current repository state
3. Current living documentation / ledgers
4. Verified runtime / CI evidence
5. Verified external research
6. Historical records / chat memory

## Canonical control contract
`.ai/engineering/00_DIRECTOR_ENGINE.md` is the single canonical Director contract.
`.ai/engineering/00_ENGINE_ROUTER.md` is a compatibility pointer only.
Entry files must point to the canonical contract and must not define a competing read order.

## Control loop
IDENTITY → BASELINE → TARGET → ROUTE → INSPECT → CHANGE → VERIFY → BREAK → RECONCILE → ACCEPT/REPAIR → RECORD → NEXT

## Historical local-PC hosting investigation — superseded 2026-11-03

The 2026-11-01 local Windows-host proposal was exploratory and is no longer the active deployment target. The owner has clarified that this PC is a development workstation; future production hosting will be a paid server when funding and release readiness permit. The 2026-11-03 host diagnostic is recorded below. Do not configure this PC as production infrastructure.

## Active target
**Active target**: Owner-directed V1 convergence — latest exact-main automated verification is green on `9fd5dd20edfe8eaad5a80681a895ac9a3f459283`; next gate is representative operator backup/restore on the intended persistent hosting target. Live hosting selection and physical device acceptance remain operational gates.
**Owner-directed V1 convergence:** repair source defects, keep exact-main CI green, complete the remaining offline/device and commercial evidence, and remove every operational task that can be safely verified or automated from code. The owner-accepted dashboard remains closed. All engineering implementation stays on canonical `main`; `deploy/render-staging` remains deployment-only.
Next hard gate: Run a representative database + private-media backup/restore drill against the intended hosting target. Keep installed-device offline acceptance open until the secure-origin disconnect, full close/reopen, local-work recovery and reconnect sequence passes.

## Current verified source — 2026-11-11

- **Verified application/test head:** `56bb804b2b71f9d709b0846be8d7c76139b9db60` on canonical `main`.
- **Offline sync correction:** `public/zazu-offline.js` now preserves `server_id` as well as the legacy `id` alias when applying bootstrap records. This covers catalogue, suppliers, purchase orders and lines, invoices and payments, expenses, inventory and movements, assets and allocations, costs, and selected customers/jobs/quotes/preparations.
- **Root cause proved by the real browser journey:** the server had created the offline customer and job and returned their identities, but the subsequent bootstrap refresh replaced the local records without retaining `server_id`. The client then lost the stable server identity after reconnect.
- **Regression coverage:** the real offline Work-creation journey now verifies that customer/job IDs survive bootstrap refresh, that the IDs match, and that both records are available from their server-rendered pages. Each retry uses unique test records; browser-context values are passed explicitly into `page.evaluate`.
- **Flake correction:** the late-device-session browser test now waits for the offline runtime to load before using it.

Exact-head CI for `56bb804b2b71f9d709b0846be8d7c76139b9db60`:
- Laravel: **PASS — 448 tests / 3,257 assertions** ([run 43016394823](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43016394823)).
- Offline browser acceptance: **PASS — 18 passed** (same Laravel workflow).
- Browser smoke: **PASS — 82 passed / 38 skipped / 0 flaky** ([run 43016394826](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43016394826)).
- Zazu quality: **PASS** ([run 43016394710](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43016394710)).
- Psalm security: **PASS** ([run 43016394752](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43016394752)).
- PHPMD: **PASS** ([run 43016394775](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43016394775)).
- Populated upgrade/rollback: **PASS** ([run 43016394715](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43016394715)).
- Push on main: **PASS** ([run 43016394702](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43016394702)).
- Director health: **PASS** ([run 43017153387](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43017153387)).
- PHP security, secret scan, application build and static analysis checks: **PASS**.
- SonarCloud: **SKIPPED**, not counted as a pass.
- Render preview Docker image: **PASS — build only, no push/deploy** ([run 43015390179](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43015390179)); workflow checked out current `main` source. Subsequent commits were test-only.

**Release position remains NO-GO.** No score uplift is claimed; the last formally reconciled evidence-maturity score remains 69/100. Physical installed-device offline acceptance on secure HTTPS, representative operator backup/restore, deployment rollback, trusted-device/lost-device acceptance, operational privacy/legal closure, official Zazu brand clearance, and production licence issuer/key custody plus application-wide enforcement remain open. The `deploy/render-staging` branch is preserved; no deployment was triggered.

## Historical verified source — 2026-11-08

- Verified application/test source: `d44ec33a57a4958380f2cfe4624f040859cbcf7a` on canonical `main`; CI workflow correction commit: `7946a87e78cf4dd2305d12fb20d3f77ed5653c87` (workflow-only; no application source changes).
- Exact-head workflow evidence on that workflow commit: Laravel run [42481998972](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481998972) PASS; Quality + secret scan [42481999066](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481999066) PASS; browser smoke [42481998954](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481998954) PASS; populated upgrade/rollback [42481998959](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481998959) PASS; Director Contract Lint [42481998956](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481998956) PASS; Psalm [42481998947](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481998947) PASS; PHPMD [42481998949](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481998949) PASS. SonarCloud [42481998971](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481998971) SKIPPED, not a pass.
- The quality workflow now uses the same complete Laravel bootstrap as the dedicated Laravel workflow. The corrected run reports 407 passing tests / 2,758 assertions; browser smoke reports 65 passed / 22 skipped / 0 failed.
- Laravel: **407 tests / 2,758 assertions — PASS**.
- Zazu quality + secret scan: PASS.
- Psalm security scan: PASS.
- PHPMD: PASS.
- Populated upgrade/rollback: PASS.
- Browser smoke: **65 passed / 22 skipped / 0 failed — PASS**.
- Director health report: PASS.
- SonarCloud: skipped, not a pass.
- Business export: 23 owner-only, business-scoped CSV datasets; streaming, audit logged and formula-injection protected.
- Global document search: filename/description/MIME type and linked job metadata search; business-scoped, permission-aware, no private storage path disclosure.
- Offline Work attachments remain integrated into active Work; phone-local persistence/reconnect logic is automated-tested, but the installed-device disconnect/restart/reconnect sequence remains physically unverified.
- The current application/test source has a fully green automated verification batch. The release remains NO-GO pending installed-device offline acceptance, representative operator backup/restore and deployment rollback drills, operational privacy/legal closure and official Zazu brand clearance.
- Commercial follow-on gaps are explicitly tiered: product/service and supplier spreadsheet import for the R499 bridge; event/work staff assignment and scheduled reminders for the R799 team tier.
- Canonical release ledger: `docs/V1_RELEASE_CHECKLIST.md`. Never repeat a CI status from an older SHA as current evidence.

## Index-only dashboard refinement — 2026-11-06

Owner clarified that the authenticated index/dashboard must be refined alone until visual approval; no rollout to other application pages is authorized.

- Reworked the dashboard welcome hierarchy to match Designer (2): greeting, owner name, prominent lead line and secondary supporting line; removed the extra uppercase kicker.
- Separated quick actions from the enclosing panel so they render as individual dashboard actions rather than a boxed toolbar.
- Increased KPI label/supporting-text readability while retaining the four live, permission-aware business metrics.
- Inset the business-capability callout within the welcome banner and preserved the optional business-owned artwork hook.
- Kept the two-column run-sheet / quote-pipeline row and priority / operational-readiness row.
- Dashboard-only selectors remain prefixed `.zazu-dash-*`; shared shell, navigation, global tokens and other page views were not intentionally changed.
- Changed files: `resources/views/dashboard.blade.php`, `resources/css/zazu-final-visual-sweep.css`.
- Latest source commit at record time: `58da15307477eb49a8adb2655585a1102735acda` (main).
- Verification boundary: source re-read; current-head browser smoke and Laravel/quality workflows were still running at record time. Earlier quality runs exposed stale assertions for superseded shell palette/structure and a pre-existing PHPMD complexity threshold; those are not visual acceptance evidence.
- Rendered desktop/mobile visual acceptance remains **UNVERIFIED**. Owner review is the design acceptance gate. Do not propagate styling to other pages before explicit approval.


## Dashboard quick-action refinement — 2026-11-06

- Compared the current dashboard source with the supplied Designer (1) and Designer (2) references.
- Finding: quick actions were plain text links with trailing symbols; the references use compact icon-led actions with a clear title and one-line task description.
- Updated the four existing permission-gated actions (Create event, Create quote, Add customer, Open calendar) with inline SVG icons and concise supporting copy. Existing destinations and permission checks are unchanged.
- Updated the existing dashboard quick-action CSS owner in place: 40px icon tiles, consistent two-line copy, stronger primary action contrast, and 34px icon tiles at mobile widths. No new global tokens, shared-shell rules, routes, or page styles were added.
- Added a focused source regression test for action labels, supporting copy, icon treatment and mobile sizing.
- Changed files: `resources/views/dashboard.blade.php`, `resources/css/zazu-final-visual-sweep.css`, `tests/Feature/UiProportionFoundationTest.php`.
- Commits: `107377e` (view), `845bb39` (CSS), `7563404` (test).
- Re-read all three changed files from current `main`; source integrity checks passed. Automated test execution and rendered desktop/mobile acceptance remain **UNVERIFIED**. No styling rollout to other pages is authorized.


## Dashboard priority and workspace health panels — 2026-11-06

- Added the reference-style icon-led Priority Now header and a concise attention-focused subtitle.
- Reframed the lower-right panel as Workspace Health with an icon-led header and four permission-gated resource cards for open purchase orders, low stock, available assets and unpaid invoices.
- Kept each card tied to its real dashboard metric and existing destination. Status dots and supporting copy are derived from the actual metric values; no unverified claims about connectivity or system uptime were introduced.
- Added responsive card styling and regression coverage for truthful zero/non-zero states, metric bindings and route preservation.
- Changed files: `resources/views/dashboard.blade.php`, `resources/css/zazu-final-visual-sweep.css`, `tests/Feature/UiProportionFoundationTest.php`.
- Commits: `1a2bcbc` (view), `bdf077d` (CSS), `0c58b35` (test), `de600fb` (test literal correction).
- Re-read current-main source; markup, metrics, routes, responsive CSS and regression-test presence checks pass. Automated test execution and rendered desktop/mobile acceptance remain **UNVERIFIED**. Dashboard-only scope remains in force.



## Dashboard events and quote panels — 2026-11-06

- Reworked the Upcoming Events and Quote Pipeline panel headers to match the supplied reference hierarchy: compact icon tile, clear heading, supporting sentence and contextual action link.
- Replaced the event timeline's narrow date/time column and connector line with a dedicated day / month / year date tile, event identity block, status and trailing navigation cue. Existing event links and status values remain intact.
- Refined quote stage cards with clearer status labels, count emphasis and status-colour markers. Kept the actual persisted workflow semantics: draft, sent, accepted (presented as Approved), declined and expired (combined for the closed bucket). No unsupported "under review" state or count was fabricated.
- Added responsive panel-header, event-card and two-column quote-stage treatments down to narrow mobile widths.
- Added focused source regression coverage for the new panel hierarchy, date tile, status mapping, route preservation and responsive rules.
- Changed files: `resources/views/dashboard.blade.php`, `resources/css/zazu-final-visual-sweep.css`, `tests/Feature/UiProportionFoundationTest.php`.
- Commits: `86be043` (view), `de9f824` (CSS), `c7eb8fe` (test), `3dc8d11` (test literal correction).
- Re-read current-main source and checked required markup, status-count expressions, routes, responsive rules and regression-test presence. Source checks pass. Automated test execution and rendered desktop/mobile acceptance remain **UNVERIFIED**. Dashboard-only scope remains in force; no styling rollout to other pages.



## Shared shell topbar correction — 2026-11-06

- Source audit found a later `.zazu-topbar` rule overriding the earlier near-white workspace surface with 88% sidebar colour. This was a direct conflict with the intended light workspace / blue sidebar composition.
- Corrected the effective topbar rule to a near-white surface/page blend, with a restrained turquoise-tinted lower border. Sidebar colour remains owned by the sidebar only.
- Added a regression test against the effective final topbar rule to prevent sidebar colour bleed.
- Commit: 54391382052ac28e0da5fae834cf881c75a12b70.
- Source verification and automated test execution: pending; rendered review remains with owner.

## Coastal dashboard visual treatment — 2026-11-06

- Refined the existing dashboard hero into a visible sky-blue → sea-mist/turquoise → warm-sunlight wash using shared theme tokens.
- Gave the existing capability side panel its own subtle blue/turquoise surface, preserving the current content and responsive stacking.
- Dashboard operational cards now use a consistent coastal-tinted border and cleaner radius; section headers receive a soft blue-to-white wash.
- KPI tiles use more visible coastal semantic tints; the former green KPI tint now uses the existing turquoise accent family.
- Event hover uses a restrained blue wash. No markup, data, routes, permission checks or information architecture changed.
- Dark theme continues to derive these treatments from its own dark token values.
- Regression assertions added for the hero, capability panel and KPI surface treatment.
- Commit: 4751d58b89bbb1fc612afe94f6dabd3fc815140b.
- PHPUnit and rendered desktop/mobile visual acceptance remain UNVERIFIED.

## Bright Coastal Blue palette implementation — 2026-11-06

- Owner reaffirmed the sunny beach-day Coastal Blue direction; references remain inspiration, not literal templates.
- Replaced the muted blue-slate light palette at the canonical `:root` token source with a visibly brighter sky/ocean/turquoise system.
- Light canvas: cloud white `#F5FBFE`; sea mist `#DDF4FC`; sidebar sky blue `#8ED8F8`; ocean primary `#159DD2`; vivid ocean accent `#35B9E8`; turquoise `#49C7B8`.
- Shifted supporting text, borders, hover/active navigation and surface tokens to harmonise with the brighter palette. Existing dark theme token block was left untouched.
- All shared components inherit the new palette through canonical tokens; no new CSS override layer or structural changes.
- Updated canonical palette regression assertions.
- Commit: 500f1ba9d843044a78737c72be677b08bacc661c.
- Automated tests and rendered desktop/mobile visual acceptance remain UNVERIFIED.

## KPI scan-order refinement — 2026-11-06

- Ordered the four dashboard KPIs as Active Events → Draft Quotes → Customers → Next 14 Days, matching the primary reference's business-first scan path while retaining an honest scheduled-work metric instead of inventing revenue.
- Added a source regression assertion for the order.
- Runtime and rendered visual acceptance remain UNVERIFIED.

## Quote pipeline reference refinement — 2026-11-06

- Replaced the single draft-quote count presentation with a four-stage dashboard breakdown matching the supplied reference's compact pipeline treatment.
- Counts are queried from business-scoped Quote records grouped by their actual lifecycle status.
- Displayed stages: Draft, Sent, Accepted, and Closed (Declined + Expired). The lifecycle vocabulary comes from Quote::STATUS_TRANSITIONS; no unsupported "Under review" state was invented.
- Retained the draft queue warning/clear state and existing Quotes/Finance actions.
- Added responsive four-column desktop and two-column mobile styling plus source regression coverage.
- Changed files: app/Http/Controllers/DashboardController.php, resources/views/dashboard.blade.php, resources/css/zazu-final-visual-sweep.css, tests/Feature/UiProportionFoundationTest.php.
- Runtime query execution and rendered acceptance remain UNVERIFIED.

## Workspace health panel refinement — 2026-11-06

- Reconciled the lower-right panel against Designer 2: renamed Resource Readiness to Workspace Health with Operational Readiness as its heading.
- Kept the panel honest: its tiles remain business-scoped live counts (open purchase orders, low stock, available assets, unpaid invoices), not fabricated network, finance-connection or platform-health statuses.
- Changed the desktop panel to four compact tiles and mobile to two columns, collapsing to one column on very narrow screens.
- Preserved the existing permission-gated links and experience-level visibility.
- Added regression coverage for the reference-aligned heading, live-count framing and responsive tile layout.
- Changed files: resources/views/dashboard.blade.php, resources/css/zazu-final-visual-sweep.css, tests/Feature/UiProportionFoundationTest.php.
- Runtime and rendered acceptance remain UNVERIFIED; source assertions alone do not certify the visual result.

## Dashboard reference alignment — 2026-11-06

- Inspected both supplied dashboard references and current main source before changing the UI.
- Current Dashboard already had the KPI strip → quick actions → paired operational queues → paired lower panels grid foundation. The missing reference alignment was chiefly the welcome composition and KPI/action ordering, not a reason to rewrite the whole dashboard.
- Replaced the oversized generic operational hero with a user greeting and a business-capabilities callout. The optional business-owned dashboard image remains supported as restrained decorative artwork.
- Prioritised Draft Quotes in the KPI strip when the Quotes workspace is available; unpaid invoices remain the fallback when Quotes is unavailable and Finance is permitted.
- Reordered quick actions to Create Event → Create Quote → Add Customer → Open Calendar, each retaining existing permission checks and routes.
- Renamed the operational panels to Upcoming Events and Quote Pipeline. Existing Priority Now and Resource Readiness panels remain data-backed; no fabricated Workspace Health or revenue values were introduced.
- Preserved the existing two-column desktop composition and single-column responsive collapse.
- Changed files: resources/views/dashboard.blade.php, resources/css/zazu-final-visual-sweep.css, tests/Feature/UiProportionFoundationTest.php.
- Commits: 4ca48de (dashboard source), 42799ea (dashboard styling), 6c3a4aa and dbdf432 (regression coverage).
- Source re-read after writes; branch inventory confirms main only.
- PHPUnit, Vite build and browser-rendered desktop/tablet/mobile acceptance were not run in this environment. Rendered visual acceptance remains **UNVERIFIED**.


Current deployment boundary:
- The Windows PC is development-only. No permanent local-PC host, AMPPS production setup, Windows service, router/DNS or private-CA work is in scope.
- A paid production server is a future owner-approved decision. No provider, monthly spend, production DB engine or instance size is selected.
- The current production-readiness plan is `docs/LOCAL_PC_HOST_DEPLOYMENT.md` (renamed content; historical filename retained).

Current state:
- Canonical repository/ref: `Leano-Jordan/ZazuEMP` / `main` only. No branches are to be created or used.
- Broad green CI evidence recorded for `7940033b6fd4f691692eec5e9d7f6ad587486f61` (2026-10-15) is historical and does not certify later commits.
- Service-worker identity-state writes and logout cleanup are now serialized through a shared promise queue.
- Three Node behavioral regressions exercise the actual `public/sw.js`: delayed persisted identity read, delayed authenticated HTML response, and identity-cache write already in flight during logout.
- `npm run test:service-worker` is wired into `.github/workflows/quality.yml`. Repository files were re-read after writes; current-main runtime/CI result remains **UNVERIFIED** until the exact workflow run is observed.
- Stale health report snapshots from an October 12 Dependabot branch were archived and misleading root-level `docs/health/latest.*` files removed. The current health workflow is artifact/summary-only with `contents: read`; use exact Actions run evidence, not a persistent latest file.

Next hard gate: **obtain exact-main Laravel, quality, browser, Psalm/PHPMD and populated upgrade/rollback evidence for the current application head; then run the operator backup/restore drill against representative database and private media. Keep installed-device offline acceptance open until the secure-origin disconnect/restart/reconnect sequence passes.**

Physical device acceptance is now owner-verified: Zazu installed on PC and mobile; screen rotation and presentation reported in order.

## Open / blocked cases

## Evidence rules
- IMPLEMENTED = repository change exists.
- TESTED = relevant automated check actually ran and passed.
- VERIFIED = intended behaviour has sufficient evidence.
- PROVEN = repeated realistic/production/recovery evidence exists.
- UNVERIFIED = required evidence unavailable.
- BLOCKED = safe progress materially prevented.
- A green narrow check never closes a broader behaviour gate.
- No case is CLOSED without its required evidence.

## Current CI boundary
The earlier browser run for `0a10d05` is not evidence for this final control-plane head. Current-head CI must be observed after this cycle. Record the exact run URL and SHA before closing any pending case.

## Application gates after browser convergence

1. Complete the populated commercial workflow traversal.
2. Execute the real backup/restore drill, including representative private media.
3. Execute the populated upgrade/rollback drill.
4. Complete physical phone/tablet acceptance.
5. Complete legal/privacy operational controls.
6. Complete dependency/licence notice closure.
7. Complete Zazu brand/trade-mark clearance.
8. Final Director re-audit against the selected release head.

**Current execution priority:** reconcile the 2026-11-01 offline-continuity source/test batch on exact `main`; then establish the independent HTTPS install origin and run installed-device disconnected/reconnect drills.

## Director cycle update — 2026-10-07

Offline operational depth expanded in current `main` through:
- inventory item + inventory movement handlers with business scoping, negative-stock protection and mutation idempotency;
- operational event-cost create/update handling with financial-state validation;
- asset create/update and allocation/return handling with explicit state-transition protection;
- direct and paired phone bootstrap coverage for inventory, assets and costs;
- phone-local UI actions for inventory, assets and costs;
- focused regression coverage for the new offline boundaries.

Current evidence status: **IMPLEMENTED; runtime TESTED/VERIFIED pending execution of the new regression set.** No release score uplift is claimed from source changes alone.

Last updated: 2026-10-07 — offline operational depth and parity hardened; current release gates remain current-head verification, recovery, rollback, legal/privacy and brand closure.

## PHPUnit / offline parity defect cycle — 2026-10-07

- Root cause of PHPUnit exit 255: malformed `catch (\\RuntimeException ...)` syntax in `tests/Feature/OfflineSyncFoundationTest.php`.
- Adjacent malformed test marker introduced during test-contract editing was also removed before validation.
- Offline local reconciliation was hardened for payments and purchase receipts so local invoice/payment and inventory state reflects accepted offline actions immediately.
- Targeted PWA regression assertions now cover those reconciliation boundaries.
- Current head CI is queued; runtime closure remains pending fresh Laravel/PHPMD/browser results.


## Director health-report hardening — 2026-10-08 (historical; superseded)

- A prior implementation experimented with persistent `docs/health/latest.json` and `latest.md` files alongside downloadable artifacts.
- That persistence is not present in the current `.github/workflows/roscore-director-health.yml`, which has repository contents permission set to read-only and publishes the per-run artifact and Actions summary.
- The October 12 committed `latest.*` snapshots described a Dependabot branch with queued CI, not current `main`. They have now been retained under `docs/health/archive/` and the root-level misleading pointers removed.
- Do not restore a privileged persistent publisher without a separate least-privilege, main-only design and verified security controls. The workflow must never check out untrusted refs or create working branches.

## Current-head CI reconciliation automation — 2026-10-08

- Director health reporting now observes the actual Laravel and Zazu browser smoke workflow results for the target commit.
- The health workflow runs on completion of those required workflows as well as on source pushes/manual dispatch.
- Reports distinguish PASS, FAIL and PENDING current-head CI state and retain the workflow run URLs.
- Workflow-run-triggered reports serialize per target SHA to prevent competing report commits.
- Implementation commit: `af0f4158160488873bbaf072de5db75ccdc10192`.


## Director deep software audit — 2026-10-08

Jarvis completed a deep repository audit against docs/product-specification.md and the V1 capability map.

Confirmed defects corrected in this cycle:
- authenticated PWA page-cache lifecycle race;
- Director health report non-fast-forward publication race;
- concurrent offline pairing-code consumption race;
- concurrent offline mutation replay race;
- rejected offline mutation permanently blocking later sync pulls.

Regression coverage was added for authenticated offline navigation, pairing/conflict behavior and rejected-mutation recovery. A newly added pairing regression initially failed CI because of a test namespace typo; this was corrected immediately in de2413d.

Current audited application HEAD: de2413d78f1efbb8a511fadd78375f1ceba025ce.

Conservative assessment:
- V1 implementation maturity: approximately 87%.
- Commercial software readiness: approximately 72%.
- Current release is not certified.

Release-critical gates remaining:
1. current-head CI/browser/security convergence;
2. real offline installed-device proof and restart/reconciliation;
3. populated commercial workflow certification;
4. real backup/restore including representative private media;
5. populated upgrade/rollback drill;
6. final authorization/security challenge;
7. production hardening and operational runbooks;
8. offline licensing activation/signing/enforcement where required for the commercial model;
9. final Director release audit.

Explicit exclusion from these scores: logo, artwork, photography, branding, trademark and other human-controlled creative/legal tasks.

Audit record: docs/DIRECTOR_DEEP_AUDIT_2026-10-08.md.


## Pre-launch landing page — 2026-10-12

- Added a standalone static marketing site under `prelaunch/`, separate from the Laravel application and PWA runtime.
- Added the GitHub Actions workflow `.github/workflows/deploy-prelaunch.yml` to publish only the `prelaunch/` directory to GitHub Pages from `main`.
- Page includes product positioning, conceptual (clearly labelled) workspace illustration, responsive feature sections, FAQ, early-access/demo enquiry form and a privacy notice.
- Form submission is configured through FormSubmit to the Rosscore Labs enquiry mailbox. Recipient activation and real delivery are **UNVERIFIED**; GitHub Pages does not store submissions.
- Static source integrity checks passed: expected files present, local asset references consistent, form consent/redirect/privacy links present, CSS/JS brace counts balanced, no remote image/font dependencies.
- GitHub Pages deployment, live browser rendering, mobile browser acceptance and end-to-end form delivery remain **UNVERIFIED** pending the Actions run, Pages settings and recipient activation.
- Expected project URL: `https://leano-jordan.github.io/ZazuEMP/`; confirm from the deployment output before public promotion.


## Pre-launch conversion copy and South African image refinement — 2026-10-13

- Revised public-facing early-access copy to lead with reduced chasing and a clearer view of the work behind each job, with a low-pressure choice between early access, a product demonstration, or both.
- Broadened copy to independent operators and growing teams across multiple South African event-related business types; avoided scarcity claims, invented social proof, and founder-support framing.
- Replaced six catering/venue-led photo references with responsive Wikimedia Commons image variants; added srcset/sizes, a public author/licence page, and privacy disclosure for the Wikimedia image host.
- Changed the pre-launch policy intentionally: image requests are remote but restricted by CSP and the deployment audit to HTTPS upload.wikimedia.org/wikipedia/commons/thumb/. This is not a self-hosted/offline image set.
- Verification remains pending: the changed deployment workflow must run on the new main HEAD, the deployed page must be checked in desktop and mobile browsers, and image loading/credits must be verified from the published artifact. FormSubmit delivery remains separately unverified.


## Landing-page reliability and first-impression refinement — 2026-10-13

- The SVG illustration substitution was unauthorised and has been fully removed from the landing page, media credits, privacy notice, source notes, and deployment workflow.
- Restored the six existing Wikimedia Commons photographic image references and responsive srcset variants, along with the required CSP host permission and original image-credit/privacy documentation.
- Removed all six newly added illustration assets from the repository. No AI-generated artwork is approved for this page.
- Kept the clearer hero headline and early-access/product-demo calls to action.
- Remaining acceptance: diagnose why the original photos did not display on the live GitHub Pages deployment, verify the current Actions deployment, then inspect image loading on desktop and mobile. Restoring source references does not prove live image delivery.

## Director cycle — 2026-10-15 current-main reconciliation

- Canonical branch: `main`; current assessed HEAD before this state-record update: `7940033b6fd4f691692eec5e9d7f6ad587486f61` (offline reliability/browser regression hardening).
- Current-main push workflows at that SHA: Laravel PASS (run 38789104896), Zazu quality PASS (38789104893), PHPMD PASS (38789105223), Psalm Security Scan PASS (38789105085), Zazu browser smoke PASS (38789104865), populated upgrade/rollback PASS (38789104911). SonarCloud was SKIPPED by workflow condition (38789105277); do not describe it as a pass.
- The Director health workflow completed successfully for the target SHA (run 38789753467). The committed `docs/health/latest.md` is stale and still describes an unrelated older branch/commit; generated workflow evidence and committed health report are not reconciled. Treat this as a control-plane reporting defect, not an application test failure.
- The backup/restore workflow provides CI evidence for a populated upgrade/rollback round trip, including representative business, event, invoice/payment, private-media and migration-state checks. It does **not** satisfy the release runbook's installed-customer-environment recovery drill.
- Remaining next hard gate: run the documented controlled Windows installation backup/restore drill and capture environment, archive ID, representative record/media/security checks. This requires access to the installed environment; do not mark PROVEN from CI alone.
- Branch policy remains main-only. GitHub currently exposes the stale `director/fix-offline-job-browser-selector` ref in addition to `main`; the connected GitHub action set exposes no branch-delete operation. Do not create another branch or misreport cleanup as complete.

Evidence: https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38789104896 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38789104893 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38789105223 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38789105085 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38789104865 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38789104911 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38789753467

Last updated: 2026-10-15 — reconciled current-main CI and recovery proof boundary.

## Director cycle — 2026-10-15 MySQL restore rollback hardening

- Canonical branch: `main`; implementation/test head: `fb0278949e8971d32a65d1d36e074b0561159cab`.
- `app/Console/Commands/ZazuRestoreCommand.php` now captures a pre-restore MySQL dump before private-storage mutation, uses a shared MySQL import path, attempts database rollback after a failed candidate import, and preserves the rollback snapshot with explicit operator warning if automatic rollback also fails.
- `tests/Feature/ZazuBackupRestoreTest.php` now exercises a simulated partial MySQL import failure and asserts the pre-restore SQL is imported and original private storage is restored. Test passed in Laravel workflow run 38793762832.
- At the time of this record, the following checks passed for this head: Laravel 38793762832, Zazu quality 38793762950, PHPMD 38793762939, Psalm Security Scan 38793762878, populated upgrade/rollback 38793762891, Push on main 38793762946, and Director health 38793901676. SonarCloud 38793762938 was skipped. Browser smoke 38793762835 was still running and is not marked passed here.
- Release limitation unchanged: no actual installed Windows/customer-environment backup/restore drill has been recorded. CI is not a substitute.
- Branch policy unchanged: no new branch or PR was created. The stale feature ref remains visible alongside `main`; the connected GitHub actions still expose no branch-delete operation.

Evidence: https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38793762832 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38793762950 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38793762939 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38793762878 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38793762891 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38793762946 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38793762835

Last updated: 2026-10-15 — MySQL rollback behavior and regression evidence recorded.

## Director offline-launch defect cycle — 2026-10-16

- Root cause found in `resources/js/app.js`: service-worker registration sent `prime-pages` twice—once from the registration and again from `navigator.serviceWorker.ready`. The service worker aborts an active priming run when another `prime-pages` message arrives, so the duplicate call could cancel page caching during initial setup.
- Changed registration to await `navigator.serviceWorker.ready` and prime the installed pages once.
- Added regression assertions in `tests/Feature/PwaOfflineFoundationTest.php` to prevent the duplicate priming path returning.
- Status: source change committed to `main`; current-head CI and real installed-device offline relaunch are **PENDING**. This does not yet prove the user's offline launch issue is resolved.

## Director offline asset-cache hardening — 2026-10-16

- Further inspection found the service worker's static-asset handler started cache writes without awaiting them, then returned the network response. Browsers can suspend the worker before that write completes, leaving required JS/CSS unavailable offline.
- Reworked static-asset handling to return a cache hit immediately; on a cache miss, fetch and await the cache write before completing the event. Cache-storage failures do not break an otherwise successful online response.
- Bumped the static cache from v14 to v15 so installed clients activate a fresh cache namespace for the changed worker behavior.
- Added regression assertions for cache-first handling, awaited writes and the new cache version.
- Status: source and regression changes committed to `main`; current-head CI and actual installed-device offline launch remain **PENDING/UNVERIFIED**. The cache priming and asset-cache defects are evidence-backed contributors, not proof they explain every part of the reported failure.

## Director offline-origin correction — 2026-10-16

- Confirmed current source on canonical `main` at baseline `10f432bdd6a4d501bbd949c7973283e3ef8f7b66`.
- Current-head Laravel and Zazu quality workflows both failed because `tests/Feature/PwaOfflineFoundationTest.php` still asserted the removed `cache.put(request, copy)` implementation after the service-worker handler had been changed to await `cache.put(request, response.clone())`. This is test-contract drift (F2/F7), not evidence that the current asset-cache implementation failed.
- Corrected the stale assertion and added a regression contract for a user-facing warning when the app is opened in a non-secure context. The notice explains that a plain-HTTP LAN address depends on a reachable PC/server and cannot register the service worker.
- Updated the offline architecture contract to record the exact LAN-origin failure and the full disconnected-device acceptance gate.
- Verification on source/test commit `3f5d1f1877b281dc84a4bbce4bcabecc9fd12952`: Laravel passed ([run 38891784115](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38891784115)); Zazu quality passed ([run 38891784112](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38891784112)); Director Contract Lint, PHPMD, Psalm and populated upgrade/rollback also passed on that SHA.
- Browser smoke [run 38891784057](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38891784057) remains **in progress** and is not represented as passed. Actual installed-device offline relaunch remains **UNVERIFIED**.
- The product-wide offline parity gap remains open; no full-offline claim is made from this correction.



## Director audit — 2026-10-19

- Current control files now mark current-main CI as unverified rather than reusing green results from October 15 or the stale October 12 health artifact.
- Added three direct behavioral tests for service-worker identity/cache races and integrated them into the Quality workflow.
- Serialized `setUser` identity-state mutations with `clearActiveUser` cleanup in `public/sw.js` to prevent an in-flight identity write from surviving logout cleanup.
- Corrected V1 checklist/scorecard evidence language, archived and removed the misleading stale health pointers, and documented the current artifact-only health-report contract.
- Verification disposition: IMPLEMENTED at source; **latest-main execution and browser regression remain UNVERIFIED**. No numerical readiness increase is claimed.
- Next: reconcile the exact current-main Actions run, then perform installed-customer backup/restore including private media and an actual rollback exercise. Legal/privacy operations and trademark clearance remain external release gates.


## Dead/stale source and dependency cleanup — 2026-10-19

- A bounded declaration scan across `resources/css/app.css`, `zazu-responsive-theme.css`, `zazu-final-visual-sweep.css`, and `zazu-mobile-refinement.css` found exactly two duplicate `font-variant-numeric: tabular-nums` declarations; both were removed. Re-scan found no remaining repeated declarations in those four stylesheets.
- Removed unreferenced `@fontsource/anton`; package/lock are reconciled and the current npm licence inventory now records 153 package entries with declared metadata. Current-main `npm ci` is the required validation.
- Corrected the duplicate `REG-017` identifier to `REG-022`; the regression-ledger heading scan reports no duplicate REG IDs.
- Search returned no TODO/FIXME/HACK/DEPRECATED markers in the indexed results, but this is not proof that no dead code exists. Comprehensive PHP/Blade/JS reachability analysis remains open.
- Current-main install/build, service-worker behavioral tests, Laravel, browser, security and upgrade/rollback results remain UNVERIFIED until exact run conclusions are observed.


## Focused P0/P1 workflow audit — 2026-10-20

- P0 defect found: invoice creation displayed accepted quote, independent job, direct lines and tax treatment as concurrently editable despite quote invoices deriving job/lines/currency/tax from the accepted quote version.
- Source correction committed: selecting a quote binds the linked job, disables job/tax overrides, hides/disables direct invoice lines and explains the source. Clearing the quote restores direct mode and its prior manual job selection.
- Added a focused feature-test assertion for quote-bound invoice form hooks and event metadata.
- Existing server guards remain in place for quote/job mismatch, accepted latest version, duplicate invoice conversion and historical quote snapshotting.
- Existing populated reconciliation scenario covers R11,500 invoice, R3,450 deposit, R8,050 final payment and paid status. Asset overlap checks exist in online and offline paths.
- Current-head tests, CI and rendered browser behaviour are **UNVERIFIED** in this session. No release score/readiness uplift.
- Next: run targeted commercial and asset/offline tests, observe exact current-main CI, then browser-traverse quote select/switch/clear and one populated job end-to-end.

Last updated: 2026-10-20

## Director offline continuity cycle — 2026-10-21

- Re-read the canonical Director contract, current engineering state, offline architecture contract, service worker, local IndexedDB client, mobile bootstrap controller and PWA regression tests before making changes.
- Confirmed the existing offline-first implementation is established work, not a new proposal. It includes a user-scoped service-worker page cache, IndexedDB local workspace and mutation queue, pairing/sync endpoints, and explicit offline domain handlers. Full disconnected parity is still explicitly unproven.
- Confirmed the immediate origin blocker: `http://192.168.137.237:8000` is a PC-hosted plain-HTTP LAN origin. When its server process stops, server-dependent requests fail; because it is not a secure browser context, service-worker registration is unavailable there. The source already contains a user-facing diagnostic for this case.
- Added a direct behavioral regression in `tests/js/service-worker-auth-cache.test.js`: prime the authenticated dashboard, make the simulated host unavailable, and verify the service worker serves the saved dashboard from the user-scoped cache.
- Recorded the exact hosting and installed-device acceptance gate in `docs/ZAZU_OFFLINE_FIRST_ARCHITECTURE.md`.
- Changes were committed directly to canonical `main`; no branch was created.
- New test status: **IMPLEMENTED, execution pending**. A source write and a passing static assertion are not runtime proof.
- Deployment reconnaissance: hosting configuration was not part of this repository-only review. Production provider, URL, database and durable storage status must be established from Zazu-specific deployment evidence; do not infer them from another product.

### Active target

**P0 — finish VS Code-independent offline continuity:** observe current-main CI for the new offline-navigation regression; establish a durable HTTPS production origin separate from the VS Code server; install/update from that origin; then perform physical-device acceptance with the PC and internet unavailable, followed by reconnect/idempotency/conflict and logout/privacy checks.

**Acceptance rule:** no claim of "solid offline" until current-head CI and the realistic installed-device gate both pass. The host-independent launch gate and full offline-domain parity remain **OPEN**.

## Director offline continuity and V1 workflow hardening — 2026-11-01

**Source baseline for this cycle:** `main` at `ddf2d72bf79365c94f05a1a69784d6a404116cb7`; later same-day commits remain governed by the live `main` ref. All implementation and documentation writes target `main`; no working branch was created.

### Implemented in source

- Offline form submission probes the Zazu origin with a bounded same-origin request. It no longer assumes `navigator.onLine` means the Laravel host is reachable; supported forms can enter the local queue while Wi-Fi/network-interface status remains online.
- The offline mutation, the `local_dirty` marker and the staged optimistic workspace copy are committed in one IndexedDB transaction. The client adopts the new in-memory state only after transaction completion.
- The service-worker cache generation is `zazu-static-v25`, invalidating the older precached offline client after the persistence changes.
- Offline customer creation carries the primary contact, legal name, registration/tax/VAT details, billing address, notes and day/night contacts. Offline profile-photo upload is explicitly declined while disconnected; metadata is not presented as proof that a file was saved.
- Owner pairing with an empty selection now bootstraps a bounded recent working set: up to 25 customers, 25 jobs, 25 quotes and 25 preparation items, alongside the existing supporting catalogue, suppliers, purchasing, inventory, assets, costs, invoices and expenses. The settings screen explains the data copied to the paired device.
- Selected customer/job/quote/preparation records carry stable local identities and relationship references into the phone's IndexedDB store.
- Offline Work creation carries the selected customer's local identity, job type, date, address and services. The server resolves the customer identity within the same business, creates service requirements, and rejects a customer identity belonging to another business.
- The sync dependency sorter is covered with a reversed customer/job batch so the customer mutation must apply before its dependent Work record.

### Regression coverage added

- Browser coverage for an online browser with an unreachable Zazu origin, full offline customer details, queue persistence after reload, and a phone-local customer selected in offline Work creation.
- Laravel coverage for customer profile/contact persistence, bounded pairing bootstrap and parent identities, offline Work/customer linking, service requirement creation, reversed dependency ordering and cross-business customer rejection.
- Existing service-worker behavioral assertions now follow cache generation v25.

### Evidence disposition

- Source and tests: **IMPLEMENTED**.
- Exact-main Laravel, Quality, browser, PHPMD, Psalm and populated upgrade/rollback: **PENDING / UNVERIFIED** until the current `main` Actions runs finish and their exact SHA is recorded. Earlier successful runs do not certify these later changes.
- Real installed-device operation with the PC and internet unavailable: **UNVERIFIED**.
- Stable HTTPS Zazu origin with durable database/private-file storage and recovery: **NOT ESTABLISHED**.
- Full offline parity across every V1 screen, complete bidirectional reconciliation, attachment/file parity and real-world conflict recovery: **OPEN**.
- No readiness-score uplift is awarded for source/test additions alone.

### Next acceptance gate

1. Reconcile current-main Laravel, Quality, browser, PHPMD, Psalm and populated upgrade/rollback runs.
2. Resolve every confirmed regression on `main`; queued jobs are not passes.
3. Establish an independent HTTPS deployment target with durable persistence and tested backup/restore.
4. Install from that origin and complete disconnected/reconnect/logout/multi-user device acceptance.
5. Re-audit complete workflow parity and recovery before V1 release acceptance.


## Director current-main CI reconciliation — 2026-11-01

**Canonical target:** `Leano-Jordan/ZazuEMP` / `main`  
**Exact application SHA:** `6b16b74a7b773ac2797bf7135f85d38b8af0de8d`

### Observed current-head Actions evidence

The following workflows completed successfully against the exact SHA above:

- [Zazu quality](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41290751642)
- [Laravel](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41290751650)
- [Psalm Security Scan](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41290751603)
- [PHPMD](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41290751665)
- [Zazu populated upgrade and rollback](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41290751645)

The quality workflow includes frontend build, the Node behavioral service-worker authentication-cache race suite, and the Laravel application suite. The populated upgrade/rollback workflow is a CI result, not a substitute for a customer-environment drill.

**Browser smoke:** [run 41290751609](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41290751609) was still `in_progress` when reconciled. It is not counted as PASS.

### Acceptance disposition

- Current-main source/automated quality: **TESTED — PASS** for the workflows listed above.
- Browser smoke: **PENDING**.
- Offline launch with PC and internet unavailable on an installed customer device: **UNVERIFIED**.
- Independent HTTPS hosting with durable database and private-file storage: **NOT ESTABLISHED**.
- Real customer-environment backup/restore and rollback: **OPEN**.
- Full offline parity and multi-device conflict/recovery: **OPEN**.

No V1 readiness uplift is claimed from these CI passes alone. Continue the P0 offline-continuity gate, then execute the populated customer workflow and real recovery drills. All work remains on `main`; do not create branches.


## Director browser-regression repair cycle — 2026-11-01

**Target:** `main` only.  
**Failure baseline:** application SHA `6b16b74a7b773ac2797bf7135f85d38b8af0de8d`.  
**Failure evidence:** [Browser smoke run 41290751609](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41290751609), [job logs](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41290751609/job/124801314146).

### Diagnosis

The browser run had 46 passing tests and 6 failures (14 skipped). The failures were concentrated in offline form coverage:

- Job-create offline test selected a server-rendered customer that was not guaranteed to exist in the device-local workspace. The offline handler correctly rejected the unresolved relationship, but its early-return path could leave the generic form submit lock engaged.
- The customer-photo offline rejection path also exposed a submit-lock recovery gap: any unsuccessful offline submit must restore the button so the user can correct the form and retry.
- The customer-contact test attempted to fill Night contact inputs inside a collapsed `details` disclosure.
- The local-customer-to-Work test attempted to click a native radio obscured by its visible choice-card label.

### Changes committed directly to main

- `resources/js/app.js`: unsaved offline form attempts now release the generic submit lock, restore the original button label, and clear `aria-busy` in the finalizer. A rejected local mutation no longer strands the form disabled.
- `e2e/offline-attachments.spec.js`: seed a customer into IndexedDB/local mutation queue before creating a disconnected job; exercise a real local customer relationship and service selection.
- `e2e/pwa-offline.spec.js`: expand the Night contact disclosure and interact with the visible event-type choice card.

### Verification status

These changes are committed on `main` through application commit `df52f7c0a5b0d7c9a21a50db479cae6ee446246e`. Fresh Quality, Laravel, Psalm, PHPMD, populated upgrade/rollback and browser smoke workflows have been triggered for this SHA. Their results are pending; no repair is marked verified until those exact runs complete.

**Do not certify offline continuity from this repair alone.** Installed-device disconnected launch, data persistence after process restart, real sync/reconciliation and production recovery drills remain release gates.


## Director software audit and repair cycle — 2026-11-01

**Canonical target:** `Leano-Jordan/ZazuEMP` / `main`  
**Application/test candidate:** `c3370a317b016be8355df799f1eed37bc55c4488`  
**Working tree discipline:** main only; no branches; no database mutations.

### Verified source repairs

1. **Default phone working-set freshness**
   - Empty-selection pairing no longer freezes the first 25 customer/job/quote/preparation IDs at pairing time.
   - Bootstrap now recalculates the recent working set on refresh.
   - Explicit selections remain pinned; legacy devices with non-empty selections preserve their prior semantics.
   - Regression coverage: post-pairing records appear on refresh; explicit/legacy selections remain bounded and pinned.

2. **Offline asset update validation**
   - Replaced invalid Eloquent `whereKey('!=', $asset->id)` usage with `whereKeyNot($asset->id)`.
   - Duplicate asset tags now produce the intended validation response instead of falling through to the database unique constraint.
   - Regression test verifies both original asset tags remain unchanged.

3. **Sync conflict recovery**
   - Added an actionable Settings panel for per-device sync exceptions, with retry and discard actions.
   - Retry restores the failed IndexedDB mutation to the pending queue; discard removes it locally.
   - Server resolution is transactional and locks both conflict and mutation; repeat/concurrent requests return the authoritative mutation status.
   - Client now follows the server's final status instead of assuming its requested action won.
   - Playwright coverage exercises retry and discard; pending browser run below.

4. **Cross-business offline storage isolation**
   - Replaced the origin-wide IndexedDB workspace with a business-scoped database name.
   - Added the active business ID to the app shell.
   - Legacy storage is migrated only when the stored business and paired-device business IDs both match the active business.
   - A migration tombstone survives local logout, preventing already-cleared legacy queue data from being imported again.
   - Added browser tests for matching legacy migration, queue preservation, logout tombstone behaviour and foreign-business rejection.
   - Service-worker static cache generation advanced to `zazu-static-v28` so installed clients do not continue using the prior offline JS.

### Exact-main CI evidence

Against application candidate `c3370a317b016be8355df799f1eed37bc55c4488`:

- **PASS** — [Zazu quality](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299518886)
- **PASS** — [Laravel](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299518930)
- **PASS** — [Psalm Security Scan](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299518944)
- **PASS** — [PHPMD](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299518954)
- **PASS** — [Populated upgrade and rollback](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299518856)
- **PASS** — [Push on main](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299519274)
- **PENDING at reconciliation** — [Zazu browser smoke](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299519019)
- **SKIPPED** — SonarCloud analysis; not counted as a pass.
- Director health-report workflows succeeded; they are status/reporting workflows, not application-test substitutes.

### Release disposition

Source changes and automated server/security/quality checks are green. Browser smoke is still pending and must be reconciled before the new Playwright cases are accepted.

Still **OPEN / NOT CERTIFIED**:
- Installed-device launch after force-close with both PC/server and internet unavailable.
- Complete offline parity across every V1 workflow and reliable cross-device reconciliation beyond the bounded local working set.
- Independent stable HTTPS hosting with durable database and private-file storage.
- Real customer-environment backup/restore and deployment rollback drills.
- Final privacy/legal and Zazu brand clearance.

No release-readiness score uplift is claimed from these source fixes or CI passes alone.


## Director stable-origin migration audit — 2026-11-01

- Re-fetched current `main` source for the PWA client, service worker, session config, environment example, deployment plan, feature tests and Playwright offline tests.
- Confirmed service-worker registration uses root-relative `/sw.js`; service-worker request/cache logic and app logout checks consistently compare against the active origin. No hard-coded development LAN IP was found in the inspected runtime paths.
- Confirmed `config/session.php` defaults secure cookies to true in production when `SESSION_SECURE_COOKIE` is unset; `.env.example` now correctly leaves that variable unset.
- **Critical migration finding:** browser storage is origin-scoped. The current business workspace uses IndexedDB database `zazu-client-business-<business-id>`; legacy migration reads `zazu-client`; offline attachment blobs use a separate `zazu-phone-attachments` database. A scheme/host/port change creates a separate storage partition. Local queues, conflicts, attachment blobs, service-worker caches and cookies do not automatically transfer from the old HTTP IP origin to the new HTTPS hostname.
- Updated `docs/LOCAL_PC_HOST_DEPLOYMENT.md` with a controlled origin-cutover procedure: drain and verify old-origin mutations/conflicts/attachments; preserve old browser storage; deploy/restore the same authoritative business data; re-install and re-pair on HTTPS; verify record relationships and a one-time test sync; retire old origin only after all devices pass.
- Explicitly recorded that unsynchronized device-only data needs a tested export/import or migration mechanism; do not clear site data or uninstall the old PWA as part of cutover.
- Source changes in this cycle: documentation only. No runtime code, database schema or customer data changed. No tests were run in this remote GitHub-only cycle.
- **Next P0:** inspect the exact origin migration and attachment retry behaviour in the client, add automated guardrails for migration readiness where feasible, then reconcile the latest browser Actions run. Physical LAN HTTPS and disconnected-device tests remain pending until the PC is available.
- All work committed directly to canonical `main`; no branch created.


## Director offline attachment integration audit — 2026-11-01

- Inspected the current Work attachment partial/controller, authenticated app layout, service worker, standalone `public/offline-attachments.js`, sync attachment API and existing attachment feature tests.
- Found that the server-side upload/list/download endpoints and the online Work attachment panel exist, but the standalone phone transfer script is not imported by any active view. Its expected globals (`data`, `syncFetch`, `render`, `#content`, `.hero-actions`) are not provided by the current authenticated layout. The script is only referenced by service-worker caching rules, tests and docs.
- Corrected `docs/ZAZU_OFFLINE_FIRST_ARCHITECTURE.md` and `docs/ZAZU_V1_STATUS.md`: online job attachments are implemented; offline attachment queue/upload/download is **NOT IMPLEMENTED in the active app**. Do not include the orphaned script in the app layout without adapting its contracts and adding business-scope, logout, origin-migration and retry tests.
- This correction prevents the project from counting a precached but unreachable script as a shipped offline capability. No runtime app changes were made for this discovery.
- Separately tightened sync attachment idempotency: an existing key is now checked against the requested business-owned job, original file name, size, MIME type, description and SHA-256 content before returning duplicate success. A mismatched key reuse returns HTTP 409; inability to verify the existing stored file returns HTTP 503 and leaves the client upload queued.
- Added regression coverage for key reuse across a different job and for changed file contents. Exact-main execution is pending on the latest commit.
- No database schema, database data or branch changes. All commits remain direct to `main`.


## Director transaction integrity and attachment storage hardening — 2026-11-01

**Canonical target:** main only. No branch was created. No database schema or customer data was changed.

### Verified source corrections committed

- Attachment deletion now commits the business-scoped metadata deletion before removing the private file. A rolled-back DB transaction can no longer leave a live attachment row pointing at a file already deleted from storage.
- Added focused attachment tests for both rollback retention and successful row/file removal.
- Both normal Work uploads and device-sync attachment uploads now reject a failed private store() operation with HTTP 503 before attempting to create attachment metadata.
- Sync attachment retries validate the same business-owned job, file name, byte size, MIME type, description and SHA-256 file content. Reusing a key for a different payload returns 409; inability to verify the stored file returns 503. Concurrent same-key insert races are checked against the same payload before duplicate success.
- Finance payment retries now compare invoice, event, currency, type, amount, method, reference, paid date and notes. A changed payload using the same key returns 409.
- Finance expense retries compare business-bound event/purchase-order/supplier links, description, amount, currency, expense date, status, reference and notes. A changed payload returns 409.
- Inventory movement retries compare item, type, quantity, unit cost, date, event, reference and notes. A changed payload returns 409.
- Purchase-order creation retries compare supplier, event, currency, expected date, notes and all line details. A changed supplier commitment returns 409.
- Invoice creation retries compare event/quote identity, currency, totals, tax snapshot, submitted dates/notes and invoice lines. A changed invoice returns 409.
- Removed an early quote-version “already invoiced” guard that previously ran before invoice idempotency lookup. The transactional guard remains after same-key replay handling, allowing a legitimate retry of the accepted quote conversion.
- Purchase receipt replay now checks whether the key was already committed before rejecting a fully received or subsequently closed order/job. Regression coverage verifies replay after the order reaches received.

### Test coverage added or expanded

- Offline conflict fixture now reacquires the live workspace object after queue writes replace it.
- Offline logout tests capture the authenticated business DB name before redirecting to the public page; IndexedDB blocked conditions fail explicitly instead of hanging.
- Attachment idempotency rejects the same key when used for a different job or different file bytes.
- Attachment deletion rollback and success paths are covered.
- Payment, expense, inventory movement, purchase order and invoice tests now verify that changed payloads with the same key return HTTP 409 without duplicate posting.
- Commercial reconciliation now retries a quote-derived invoice after initial conversion and verifies the invoice count remains one.
- Purchase receipt test replays the final receipt key after the order becomes fully received.

### Known remaining integrity limitation

The purchase_order_receipts table stores only the business, purchase order and idempotency key. It does not persist the line quantities/payload applied by a receipt. Current code safely suppresses duplicate stock posting and permits retries after completion, but cannot prove that a same-key replay carries the same quantities. Full payload-mismatch rejection requires an additive persisted receipt-line fingerprint or receipt-line table. This is deliberately left open pending approval for a database migration; no schema change is made here.

### Current verification disposition

- Source changes are committed, but automated workflows must be reconciled against the exact current main SHA before any new tests are claimed as passed.
- Browser smoke on earlier candidates remained in progress; the latest run must complete before the offline fixture corrections are certified.
- Local Windows PC host installation, stable HTTPS, installed-device disconnected launch, and physical reconnect/restore drills remain unverified.
- Offline phone attachment upload/download is still NOT IMPLEMENTED in the active app. The standalone legacy script remains dormant and is not loaded into the authenticated layout.
- No release-readiness score uplift is claimed from source changes alone.


## Director local PC host inspection — 2026-11-01 22:24 +02:00

- Owner supplied read-only output from `ops/windows/Inspect-ZazuHost.ps1` for the actual Zazu PC.
- Windows 10 Pro build 19045; ESU/security-update enrollment and UBR remain unknown. Standard Windows 10 support ended 2025-10-14; do not certify this machine as a long-term host until its supported update state or OS migration is confirmed.
- PHP CLI 8.4.26 satisfies `^8.4`; common required extensions including PDO MySQL and OpenSSL are present. Apache's PHP runtime still needs independent verification.
- AMPPS exists under `C:\Program Files\Ampps`, but the inventory found no Apache/MySQL/MariaDB Windows services.
- Only detected listener: PHP on `0.0.0.0:8000` (PID 10268). It is consistent with a development server; full executable/command line was not captured. Do not use it as permanent customer endpoint.
- Current Wi-Fi address `192.168.137.237`, gateway/DNS `192.168.137.1`; require router DHCP reservation before stable hostname/certificate setup.
- Project dependencies/assets are present; C: free space approximately 127.8 GB.
- Deployment plan updated with these findings and the next read-only inspection checklist. No local services, firewall, router, DNS, certificates, database or files were changed.
- Latest observed main SHA at review: `2c984a71724fe8aebaf57cbb777fa029ca45ec12`. Exact-head workflows: Laravel PASS, Zazu quality PASS, PHPMD PASS, populated upgrade/rollback PASS, Psalm security PASS, browser smoke PASS, push checks PASS; SonarCloud SKIPPED.
- Remaining work: inspect exact PHP server process and AMPPS Apache/database layout, determine active DB driver/data paths and backup target, verify Windows security-update/ESU state, then design a safe deployment plan. No host configuration until these are known.


## Director attachment retrieval defect — 2026-11-01

- Expanded the audit beyond the offline host work into the active Work attachment workflow.
- **Defect found:** the Work attachment panel displayed uploaded filenames and a Remove action, but omitted the already-implemented authorized download route. Users could store files but had no download control in the normal job screen.
- Fixed `resources/views/work/attachments.blade.php` to expose a Download action using the existing `work.attachments.download` route. No public file URL was introduced; the route retains the `work.view` permission and controller business-boundary checks.
- Added a positive feature regression in `tests/Feature/BusinessIsolationTest.php` verifying an authorized user can download their own private job attachment. Existing foreign-business download rejection remains intact.
- Commits: `7a4ea84f214f3766bcecf4ea3a7f76a2a8f0c51f` (UI) and `f626522b5043549617e6a4689f695afb79c4f53d` (test), both directly on `main`.
- Verification status: source and test committed; PHPUnit/CI execution on this exact head is pending. Do not mark the defect runtime-verified until the Laravel workflow passes.
- No database/schema changes, no branch, and no attachment storage permissions were broadened.

- Follow-up refinement: added a rendered Work-screen assertion to the positive download regression, so it checks both that the download link is present and that the private file endpoint returns a download response. Tidied the Blade action markup. Follow-up commits: `56cf37bdf4f49c25f6cd06507af8a06b61a2626d` and `2ff49d6b0853f08cc9f5a539f49fe3583e660136`, direct to `main`.

- Reconciled `docs/ZAZU_V1_STATUS.md` on 2026-11-01: corrected stale assessment date/head, clarified that the old application candidate is historical, corrected the conflict-control status, and recorded the online attachment-download fix separately from still-unimplemented offline attachment transfer. The page now explicitly leaves exact-head CI pending and does not reuse historical green runs.


## Director deep host-forensics preparation — 2026-11-01

- Reconfirmed canonical repository `Leano-Jordan/ZazuEMP` and only visible branch `main`.
- Current observed main before this inspection-script commit: `a60d403c6305444f2f4a67ea72a398555a7261df` (V1 status reconciliation).
- Added `ops/windows/Inspect-ZazuHostDeep.ps1`, a read-only follow-up to the first host inventory.
- It captures the previously missing PHP listener executable/command line and parent PID; Windows build/UBR and recent hotfix indicators; AMPPS executable versions; PHP CLI and AMPPS PHP configuration/extensions; relevant Apache directives; non-secret Laravel DB connection/database/path metadata; SQLite file presence/size when applicable; database datadir config references; private-storage footprint; and disk space.
- It deliberately does not read database credentials, start/stop services, edit files, connect to the database, change firewall/router/certificate settings or migrate data.
- Script was committed directly to `main` as `f71fb703cf0565f8839bd7a197b18e9ce56d1f3e` and re-fetched from GitHub for content verification.
- Runtime result: **PENDING owner execution**. The remote GitHub session cannot inspect the local Windows process table, AMPPS installation, actual `.env`, database files or ESU state.
- Next gate: owner runs the deep inspection script from `C:\Projects\ZazuEMP` and returns its output. Use that evidence to choose the existing PHP/Apache/database runtime; do not configure services or certificates before the process and data paths are known.

- Branch-policy reconciliation during this cycle: GitHub branch search exposed `main` plus existing Dependabot refs `dependabot/composer/laravel/framework-13.39.0` and `dependabot/composer/phpunit/phpunit-13.4.5`. No branch was created or used by this Director cycle. Treat both Dependabot refs as existing policy drift; do not work on or delete them without owner authorization.


## Director takeover cycle — 2026-11-01 — current-main CI and host-plan correction

- Canonical target: `Leano-Jordan/ZazuEMP`, `main`; assessed source head: `8700fa6e1afdbbfd7a2fd75c1f07b26cdc438467`.
- Corrected the PHPMD failure observed on the preceding main head. The attachment download positive/negative tests were moved from `BusinessIsolationTest` into the focused `WorkAttachmentDownloadTest`; the former now has nine public test methods. This is test organisation only; no application behavior or database schema changed.
- Commits: `dcd03aba29c6c9356f4ae961059acb3da1639149` (new focused test class), `5fc6098328495cc585b997b5eec3cce0cd16c625` (isolation test refactor), and `8700fa6e1afdbbfd7a2fd75c1f07b26cdc438467` (deployment-plan correction).
- Corrected `docs/LOCAL_PC_HOST_DEPLOYMENT.md`: AMPPS is installed but was not used to run Zazu and is not the selected/default host. Current observed Zazu development runtime is standalone PHP 8.4 via `php artisan serve`. Host selection must be based on PHP 8.4 compatibility, HTTPS, persistent service recovery, database and private storage—not on AMPPS being present.
- Re-read the changed test files and deployment document from GitHub after writes.
- Exact-head Actions evidence for `8700fa6`: Laravel PASS (41355442767); Zazu quality PASS (41355442798); PHPMD PASS (41355442910); Psalm Security Scan PASS (41355442824); populated upgrade/rollback PASS (41355442795); Director Contract Lint PASS (41355442755); Push on main PASS (41355442522); Director Health Report PASS (41355483317). SonarCloud was SKIPPED (41355442974). Zazu browser smoke (41355442725) was still running at the time of this record and is not claimed as passed.
- Source-level offline audit: service-worker registration is deliberately skipped on insecure origins; the previous `http://192.168.137.237:8000` LAN origin cannot register the worker. The runtime uses same-origin/root-relative service-worker paths and production-secure session-cookie defaults. The offline client persists a device-local workspace and mutation queue, but the complete web application is not mirrored locally. Offline attachment transfer remains NOT IMPLEMENTED in the active app; the standalone script is not loaded by the authenticated layout. Origin changes create a separate browser-storage partition; queues and local-only data do not automatically migrate.
- No local PC services, Apache/PHP configuration, database, firewall, router, DNS, certificates or browser storage were changed. No feature branches were created or used.
- Next target: select and prove a persistent HTTPS host from the actual Windows/PHP/database/security evidence, then run the secure-origin installation and disconnected-device/reconnect acceptance. Do not request more owner terminal work until repository evidence is exhausted and the exact missing physical fact is identified.


## Director correction — development workstation vs future paid production server — 2026-11-03

- Owner clarified that the Windows PC is a development machine, not Zazu's permanent production host. Future production hosting will be a paid server when funding and release readiness allow.
- The previous local-PC production-host target is superseded. Do not select/configure AMPPS, Apache, Windows services, router DNS, LAN certificates or the owner's PC as a customer host. Do not recommend purchasing a cloud server merely to remove the VS Code terminal dependency.
- Owner supplied `Inspect-ZazuHostDeep.ps1` output. Observed development runtime: PHP 8.4.26 at `C:\\PHP84\\php.exe`; PHP built-in server on `0.0.0.0:8000` (PID 9664); active DB is SQLite at `database/database.sqlite`; private storage has 8 files / approximately 0.98 MB. No Windows web/database services were detected. AMPPS PHP is 7.4.33 and AMPPS was not hosting Zazu. Windows 10 Pro 22H2 build 19045, UBR 2251; listed hotfixes do not prove ESU enrollment.
- Replaced `docs/LOCAL_PC_HOST_DEPLOYMENT.md` with a production-readiness plan that explicitly separates temporary local development from a future paid server. It no longer treats the owner's PC as production infrastructure or assumes a provider/database.
- Future production baseline: HTTPS, PHP 8.4+, public-web-root isolation, persistent private files, tested database engine, queue/scheduler supervision where needed, off-server backup/restore, monitoring and safe release/rollback. Provider, server size, OS and database choice remain unselected until tested and owner-approved.
- Offline product capability remains independent of hosting choice. A cloud server does not provide offline operation; only declared device-local workflows can work during WAN loss. Offline attachment transfer and realistic reconnect/recovery proof remain open.
- Commit: `f638437097b94f5a87f37179efe492719dcf7ba6` on `main`. Deployment document was re-fetched and its updated content SHA verified.
- No local PC settings/services, database, firewall, router, certificates, browser storage or customer data were changed. No branch was created or used.
- Active next target: repository-only production portability audit. Remove verified environment-specific assumptions and establish repeatable release/deployment requirements; defer infrastructure purchase and owner-PC hosting changes.


## Local development runtime decoupling — 2026-11-03

- Added `Start-Zazu-Dev.cmd` and `Stop-Zazu-Dev.cmd` wrappers with `ops/windows/Start-ZazuDev.ps1`.
- Purpose: let the owner start/stop the temporary Laravel development server without leaving VS Code open. This is explicitly **not** a production host or Windows service.
- Launcher validates PHP 8.4+, project dependencies and `.env`; defaults to loopback; refuses to take over an occupied port; logs to timestamped files under `storage/logs`; and stops only a process chain it can identify as this Zazu project.
- A non-loopback bind can be supplied for trusted-LAN testing, but plain HTTP on a LAN IP is not a secure origin and cannot prove phone service-worker/PWA offline operation.
- Static source re-read completed. Owner-PC execution, process survival after VS Code closure and stop behaviour are **NOT YET VERIFIED**. No claim of runtime success.
- Commits: `412b979` launcher; `8323d97` start wrapper; `b28445f` stop wrapper; `dc11eb9` process verification; `bc1ea51` bind-address validation; `f821ea6` local test runbook; `4d7e4eb` deployment-readiness status.
- The future production target remains a paid server selected later; no local-PC hosting configuration or infrastructure purchase is authorized or planned in this cycle.


## Offline architecture alignment and evidence hygiene — 2026-11-03

- Updated `docs/ZAZU_OFFLINE_FIRST_ARCHITECTURE.md` to match the owner's clarified deployment direction: paid persistent production server in future; Windows PC development-only; device-local IndexedDB continuity during internet/server outages; server remains shared business-data authority when reachable.
- Explicitly ruled out an implied multi-device LAN sync mode while the cloud server is unreachable. That would require a separate approved architecture.
- Corrected a misleading browser-test filename: `e2e/offline-attachments.spec.js` contained only an offline job-creation test and did not test attachment transfer. Renamed it to `e2e/offline-work-creation.spec.js`; test content unchanged. Offline attachment integration remains NOT IMPLEMENTED in the active app.
- Current main after this batch: `a0637f15efbf05330c6d561bf4cfbcf1431bbdbe`. Exact-head CI has been triggered and is pending reconciliation.


## Director repository security audit — 2026-11-03

- Completed a main-branch source review of authentication/session handling, tenant boundaries, permissions, sync pairing/token handling, mutation idempotency, attachment/media storage, PWA cache/offline persistence, spreadsheet parsing, tests, workflows and current security documentation.
- Hardened CSV/XLSX parser resource boundaries, corrected the customer import controller's workbook response-shape mismatch, and applied request-ID/security-header middleware to the API group.
- Added regression coverage for parser budgets, customer import preview and API response headers. These are **implemented, not yet CI-verified** until the new exact-main SHA completes its test workflows.
- Recorded the complete findings and evidence limits in `docs/ZAZU_SECURITY_AUDIT_2026-11-03.md`.
- Security-audit correction: business-scoped IndexedDB and device tokens are intentional under DEC-019 owner-approved trusted-device pairing. The owner selects and discloses the bounded data copied to the paired device; normal staff web permissions are not a separate record-level security boundary inside that trusted offline device. Remaining controls are device inventory/revocation, physical device lock and a lost/reassigned-device response. Do not redesign as user-scoped storage without an owner-approved architecture change.
- GitHub reports `main` protected with no required status-check contexts. Owner must configure required checks; no setting was changed in this audit.
- No live Zazu black-box test was performed in this repository-only review because no Zazu runtime URL and test credentials were supplied. No security-alert clean bill is claimed.


## Director cycle — 2026-11-04 — installation recovery scope

A source audit found that business-owner settings exposed installation-wide backup and restore commands. The commands operate on the full database and private storage, not one tenant. This was a cross-business disclosure/destructive-restore boundary defect.

Corrective source changes on main:
- Removed business-owner backup/restore routes and controller actions.
- Removed misleading tenant-scoped backup/restore controls from settings.
- Added an explicit installation-operator / tenant self-service limitation notice.
- Updated regression tests and the recovery runbook.

Evidence: exact-head CI pending for this commit. Existing CLI command tests do not prove a real recovery drill. Business-scoped export/restore remains unimplemented and must not be claimed.

Next priority: finish V1 recovery design with explicit tenant-vs-installation scope, then verify remaining current-head CI and continue populated commercial workflow and offline device acceptance. Keep all work in this repository and on main.


## Director V1 scorecard — 2026-11-04

The V1 specification scorecard is recorded in `docs/ZAZU_V1_SCORECARD_2026-11-04.md`. Current unweighted evidence-maturity average: **3.0/5 (60/100) across 13 domains**. This is not a feature-completion percentage.

Assessment source head: `d1e2a4397b064d3fbfe7fc2cf1dc59236e00922f`; CI for that exact head was queued when assessed. Do not promote the score or readiness until the latest-head checks finish. Commercial release remains NO-GO.


## Exact source CI — 2026-11-04

For application SHA `d1e2a4397b064d3fbfe7fc2cf1dc59236e00922f`, Laravel, quality/secret scan, Psalm, PHPMD, populated upgrade/rollback and push-on-main passed. Browser smoke remains in progress; SonarCloud was skipped. Documentation-only commits followed; no application source changed after the assessed source SHA. Release certification remains blocked by browser acceptance and the operational/legal/brand gates.


## Director offline transition and attachment lifecycle hardening — 2026-11-04

- Preserved DEC-019's owner-approved business-trusted device architecture. Do not bind the offline business workspace to the current staff user or clear it on a same-business account transition without an approved architecture change; the paired device is explicitly trusted for the bounded business data selected by the owner.
- Added business-scoped local attachment storage using `zazu-phone-attachments-business-<business-id>`; new and downloaded records carry the business ID.
- Logout assessment now includes pending local attachments. The logout confirmation warns about pending attachment files, and local session cleanup clears the active business's attachment database.
- Cleanup fails closed if attachment inspection or clearing fails. The old unscoped `zazu-phone-attachments` database is deliberately not migrated/deleted because its records lack reliable business ownership metadata.
- Added PWA regression assertions for the attachment database boundary and logout cleanup.
- Source/test commits: `92bb9c0`, `8a14965`, `ba48760`, `cdea317`. Source/test head: `cdea317643dd5e226d685f84c32f51d875defc80`.
- CodeQL run `41882179271` completed successfully. This is not a substitute for Laravel, browser, or runtime acceptance; those checks were not present on the exact source SHA at the time of review.
- **Not complete:** account/session transition acceptance (logout, expiry, same-business account change, business switch, interrupted cleanup, late responses and revocation) and active Work-screen offline attachment integration. The legacy attachment client remains dormant.
- Evidence: `docs/ZAZU_OFFLINE_TRANSITION_AND_ATTACHMENT_HARDENING_2026-11-04.md`.


## Owner-directed active focus — 2026-11-05

The owner has explicitly directed the current execution focus to **UI/UX improvement**. This is the active session priority and supersedes the older offline-continuity sequencing until the owner redirects scope. Offline acceptance gates remain open; they are not being silently closed or reclassified.

Current UI/UX mission: continue the property-level consolidation of sidebar, brand and hierarchical navigation rules in `resources/css/app.css`, preserving the separate responsive/mobile ownership and all navigation interaction states. Keep the Designer 1 and Designer 2 references as design direction, not copy targets.

Latest source change: commit `29a38f55d3d3ff5979cf844ddbd624a9ea279515` consolidated the brand lockup/logo/fallback mark base styling and hierarchical navigation trigger/flyout base geometry into their canonical declarations. Source re-fetch passed. Rendered visual acceptance and Vite/runtime verification remain **UNVERIFIED**.


## Dashboard hero theme correction — 2026-11-05

- Active UI/UX focus continues.
- Corrected the final dashboard hero gradient so it uses semantic theme surface tokens instead of a fixed white background.
- Added a source regression assertion in `tests/Feature/UiProportionFoundationTest.php`.
- Commit evidence and source verification recorded in `docs/DIRECTOR_CSS_AUTHORITY_AUDIT_2026-10-04.md`.
- Browser-rendered acceptance and PHPUnit execution remain open; no visual/runtime pass is claimed.

## Dashboard hierarchy and visual proportion pass — 2026-11-05

- Continued the owner-directed UI/UX reference pass on the dashboard, using Designer 2 as a design direction rather than copying its content or visuals.
- Moved dashboard KPIs ahead of the work queues and separated create shortcuts into a dedicated Quick actions strip. Upcoming work and quote action now share the second row; Priority Now and Resource Readiness share the lower row.
- Refined final dashboard proportions: widened the hero's contextual side panel, compacted card headers and KPI scale, normalized dashboard surface radii, tightened event/priority spacing, and laid out resource links in two desktop columns with a single-column phone fallback.
- Consolidated the latest dashboard composition into one final CSS authority block instead of stacking another competing set of overrides.
- Added source regression coverage for hierarchy, quick-action placement, card proportions and responsive resource layout.
- Commits: 9fd69b9, 0046af8, dbb6a3a, 41bd22d, 03364ca, 99b25b4, 469c41e, a743263. Latest source/test head: a743263ef89ee0e664e6ef826d5327a53896fb1c.
- Re-fetched CSS and test files from main; verified expected layout declarations and balanced CSS braces. Only main was visible.
- Not verified: PHPUnit execution, Vite build, browser-rendered desktop/tablet/mobile acceptance, and light/dark rendered comparison. GitHub combined status was empty and the workflow-run wrapper returned no runs; neither is evidence of a pass.
- Next UI/UX source objective remains the documented property-level consolidation of sidebar, brand and hierarchical navigation rules in resources/css/app.css, preserving responsive/mobile ownership and all navigation state dependencies.

## Sidebar and hierarchical navigation cascade consolidation — 2026-11-05

- Continued the owner-directed UI/UX work in `resources/css/app.css`, consolidating repeated sidebar/navigation base rules, active/hover states, priority treatment, sidebar workspace identity, footer card/title/copy, and hierarchical desktop flyout geometry.
- Consolidated base selectors to one canonical rule: `.zazu-nav-stack`, `.zazu-nav-group`, `.zazu-nav-label`, `.zazu-nav-link`, `.zazu-nav-icon`, `.zazu-sidebar-workspace` and its label/name/meta/dot, `.zazu-nav-link:hover`, `.zazu-nav-link.active` and marker/icon states, `.zazu-nav-priority`, and sidebar footer card/title/copy.
- Consolidated the desktop hierarchical-navigation styles into a readable canonical block. Preserved the distinct open-state descendant and direct-child selectors, active-area styling, hover/focus access, light-theme active override, flyout active styling, 850px inline expansion and reduced-motion rules.
- Kept `resources/css/zazu-responsive-theme.css` and `resources/css/zazu-mobile-refinement.css` unchanged; their scrolling, off-canvas sidebar, mobile tap target and safe-area ownership remain intact.
- Added regression checks in `tests/Feature/UiProportionFoundationTest.php` for canonical rule counts and preservation of navigation states, theme override, responsive ownership and reduced motion. The chevron has a legitimate reduced-motion override and is validated separately rather than incorrectly treated as a single-rule selector.
- Commits: `faa5bd9`, `e2a4139`, `8649a6e`, `bea06b9`, `a8975f9`, `6c37f97`, `9461414`, `9bce61e`, `435454a`, `0a83eef`, `94479ce`. Current source/test verification to be reconciled at the latest commit below.
- Source re-fetch checks confirm the 25 canonical base/state selectors each have one definition (chevron intentionally excluded because its reduced-motion override is separate), CSS braces are balanced, and no literal newline escape remains. Only `main` was visible.
- Not verified: PHPUnit execution, Vite build, PHP/CSS toolchain lint, browser-rendered desktop/tablet/mobile acceptance, or light/dark rendered comparison. Empty status/workflow wrapper responses are not treated as passes.
- Next UI/UX step: review the remaining brand lockup, logo/fallback mark and topbar identity rules against the canonical shell, then arrange a rendered acceptance pass before broad cleanup of unrelated app.css history.

## Brand lockup and topbar cascade review — 2026-11-05

- Reviewed brand identity and topbar-related rules after sidebar/navigation consolidation.
- Brand link, logo image, lockup strong/span colour rules each have one canonical base declaration. The brand fallback mark intentionally shares geometry with the uploaded logo and has a separate appearance rule; these are complementary roles, not duplicate declarations.
- Added regression coverage for the unique brand link/logo/lockup rules and retained assertions for the shared logo/fallback geometry.
- Topbar, page title, eyebrow, workspace status and theme-toggle styles have multiple later declarations tied to sequential theme, wallpaper, mobile and shell treatments. They were deliberately not flattened: source inspection shows these are context-specific cascade overrides, not safe duplicate base rules. A follow-up should map their media/theme ownership before any migration.
- No additional brand or topbar CSS was changed in this review. Responsive/mobile stylesheets remain unchanged.
- Test update: `b06cc02e72fe7712671ab3058f1422d87907bd1d`.
- Source verification: brand base selector counts and the final navigation/sidebar state checks are recorded in `tests/Feature/UiProportionFoundationTest.php`. Runtime and rendered acceptance remain pending.
- Next objective: map the topbar/page-title/theme-toggle cascade by theme and viewport into explicit ownership, then consolidate only where computed property precedence is unchanged. Browser light/dark and mobile acceptance remains a release gate.

## Topbar, page title and theme-toggle cascade consolidation — 2026-11-05

- Confirmed the actual shared layout asset order: `app.css` → `zazu-responsive-theme.css` → `zazu-final-visual-sweep.css` → `zazu-mobile-refinement.css`. The header markup is in `resources/views/components/app-layout.blade.php`.
- Consolidated the desktop base rules in `app.css`: `.zazu-topbar`, `.zazu-topbar-inner`, `.zazu-page-title`, `.zazu-eyebrow`, and `.zazu-theme-toggle` now each have one canonical top-level definition.
- The topbar retains its theme-token sidebar surface, blur, sticky desktop positioning and current shadow. Its user-wallpaper translucency remains a higher-specificity state rule.
- The topbar inner container now has one desktop sizing rule. The <=1023px side padding and <=767px legacy layout declarations remain in their media scopes; the responsive layer retains the >=821px search/tab/action constraints.
- The page title keeps its existing effective desktop size/weight/line-height/letter-spacing and sidebar-aware foreground. The mobile layer still owns the 18px mobile title and 17px <=520px title.
- Moved shared `.zazu-eyebrow` typography out of `zazu-final-visual-sweep.css` and into the canonical `app.css` rule. The extension stylesheet no longer declares or groups the shared eyebrow selector. The effective 12px/700/1.35/.055em/non-uppercase typography and token-aware label colour are preserved; specific command-banner eyebrow contrast remains separate.
- Consolidated the theme toggle's effective 36px desktop geometry, token-aware surface/border/foreground, radius, focus and transition base. Hover/active/focus/reduced-motion states remain distinct. The mobile layer continues to size the toggle at 40px.
- Source property comparison against the pre-pass `b550514` tree found no effective declaration changes for topbar, page title or eyebrow. The topbar inner padding shorthand resolves to the same 11px vertical / 24px horizontal dimensions. The theme-toggle border shorthand carries the same token colour as the former border plus border-color cascade.
- Kept `zazu-responsive-theme.css` and `zazu-mobile-refinement.css` unchanged. Added `test_topbar_title_and_theme_toggle_have_one_canonical_base_with_theme_and_viewport_overrides` to `tests/Feature/UiProportionFoundationTest.php`.
- Commits: `dc42c84` (canonical desktop header bases), `7a40909` (shared eyebrow ownership), `cafea19` (regression coverage). Latest source/test head to be verified below.
- Not verified: PHPUnit execution, Vite build, browser-rendered desktop/tablet/mobile acceptance, light/dark rendered comparison, or physical touch/keyboard acceptance. Source checks do not replace those runtime gates.
- Next objective: obtain rendered browser acceptance of the dashboard/shared shell at desktop, tablet and mobile widths in both themes. Do not continue broad CSS consolidation until those visual results are reviewed.


## Dashboard density and duplicate-priority cleanup — 2026-11-06

- Removed two repeated entries from the Priority Now panel: workspace setup (already presented in the dedicated setup banner) and the next scheduled event (already listed in Upcoming Events).
- Priority Now now stays focused on distinct actions: quote review, open purchasing, low stock, unpaid invoices, and the setup banner remains separately visible to owners.
- Removed the upcoming-event dependency from the empty-priority condition so a scheduled event alone no longer suppresses the truthful "No immediate priority flagged" state.
- Added a source regression test to prevent setup and upcoming-event duplication inside the priority panel.
- Scope deliberately limited to dashboard content density; no unrelated shell, palette, route, permission, or data changes.
- Source re-fetch and structural checks required after commit. PHPUnit, Vite, and rendered desktop/tablet/mobile plus light/dark acceptance remain unverified until executed in an available runtime/browser.


## Dashboard run-sheet density refinement — 2026-11-06

- Limited the dashboard run-sheet to the five nearest upcoming, non-completed events; the existing All work link remains the route to the complete work list.
- Reduced dashboard event-row minimum height from 76px to 64px and tightened vertical padding. Existing mobile status placement and wrapping rules remain unchanged.
- Added regression coverage for the five-record limit, compact row sizing and continued access to the full work list.
- Source-level verification only; PHPUnit, build and rendered viewport/theme acceptance remain outstanding.


## Dashboard navigation duplication removal — 2026-11-06

- Removed the extra "Workspace surfaces" navigation strip beneath Quick actions. It repeated destinations already available through the primary navigation and the dashboard's own focused actions, adding a second navigation layer not present in the supplied reference.
- Removed its now-unused component CSS selectors and removed only the commandbar selector from shared grouped rules, preserving unrelated selectors and declarations.
- Added regression coverage confirming the duplicate strip and its styling stay absent while Quick actions remain.
- Dashboard layout is shorter and more focused; primary navigation remains the full route-discovery surface.
- Source re-fetch and selector/structure checks required after commit. PHPUnit/build/browser visual acceptance remains outstanding.


## Dashboard copy density pass — 2026-11-06

- Removed secondary explanatory sentences from the Quick actions, Upcoming events, Quote pipeline, Priority now and Workspace health headers. The section labels and titles already communicate their purpose.
- Removed repeated helper captions from the four Workspace health count tiles; each tile keeps its clear metric label and live count.
- Removed the dashboard-only finance disclaimer from Quote pipeline; finance navigation remains available and the finance workspace remains the source of financial detail.
- Kept action-specific priority explanations and the honest operational-readiness heading. No data, permissions, routes or calculations changed.
- Added regression coverage for the compact copy and retained section identity.
- Source re-fetch and structure checks required after commit; runtime and rendered acceptance remain outstanding.


## Dashboard KPI density refinement — 2026-11-06

- Removed four secondary KPI captions that repeated the meaning of each metric label or added no decision-making context.
- Kept all permission-gated KPI cards, live values, labels, routes and calculations unchanged.
- Removed the now-unused KPI-caption CSS rule.
- Added regression coverage for the four retained KPI labels and the absence of redundant captions/styling.
- Source-level change only; runtime, PHPUnit, Vite and rendered viewport/theme acceptance remain unverified.


## Theme default correction — 2026-11-06

- Root cause: both the layout's pre-paint bootstrap and the shared JavaScript theme initializer selected dark mode from the operating-system preference whenever no explicit Zazu theme had been saved. This made the dashboard open in dark mode on devices configured for dark appearance, despite the light-theme dashboard references being the active design target.
- Correction: default to the reference-aligned light theme when no explicit Zazu preference exists. A saved `light` or `dark` preference still takes precedence, and the manual theme toggle continues saving the user's selection.
- Kept the dark palette and all explicit dark-theme component rules intact; this changes only the no-preference fallback.
- Added a source regression test covering both initialization paths and persistence of an explicit toggle choice.
- Source verification required after commit. PHPUnit, Vite and rendered browser checks remain unverified in this environment.


## Light shell colour refinement — 2026-11-06

- Adapted the existing light-theme shell to the supplied visual direction rather than copying either reference literally.
- Shifted the light sidebar from a very pale cyan surface to a clearer mid-blue surface with coordinated navy-blue foreground, muted labels, hover and active tokens. Existing navigation, flyouts, footer and workspace panels inherit these shared tokens.
- Separated the desktop topbar from the sidebar palette: it now uses the existing near-white page surface and normal page foreground, matching the references' lighter header/content relationship.
- Updated page-title/eyebrow and theme-toggle colours to use page tokens instead of sidebar tokens. Dark-theme token values and explicit dark-mode overrides remain unchanged.
- Added source regression coverage for the light shell palette and separate topbar surface.
- Source-level only; rendered acceptance, PHPUnit and Vite build remain outstanding.


## Light sidebar navigation state refinement — 2026-11-06

- Tuned navigation hover and active states to stay within the new blue sidebar palette rather than borrowing the general content-surface colours.
- Active navigation now reads as a light selected surface with a restrained blue edge indicator; hover uses a soft blue tint. Navigation icons inherit the sidebar's coordinated muted blue.
- No navigation structure, routes, permissions, interaction logic or dark-theme rules changed.
- Added source regression coverage for the light navigation state declarations.
- Source-level only; rendered visual acceptance and automated runtime checks remain outstanding.


## Dashboard KPI surface treatment — 2026-11-06

- Added four restrained semantic surface tints to dashboard KPI tiles, using existing info, success, primary and warning theme tokens blended with the current card surface.
- Preserved the existing metric content, dimensions, links, hover behaviour and responsive grid. No positional business meaning is assigned to the tints; they are purely visual differentiation.
- Added source regression coverage for all four token-based treatments.
- The treatment uses theme tokens so the existing dark theme remains internally consistent; rendered contrast still requires browser acceptance.


## Shared panel header surface refinement — 2026-11-06

- Source review found shared panel headers remained flat while form-section headers already used the workspace surface token family, creating a small but repeated component-language inconsistency.
- Added a restrained surface-2 to surface gradient to the canonical shared `.zazu-panel-head` rule, retaining its existing dimensions, border, content, layout and responsive behaviour.
- The treatment is token-driven and therefore follows the existing light/deep-water theme definitions; no page-specific overrides or new palette values were introduced.
- Added a focused regression assertion against the effective final panel-header rule.
- Source-level verification only. PHPUnit, Vite build and rendered desktop/mobile review have not been run in this environment.

## Workspace search reference alignment — 2026-11-06

- The supplied dashboard reference uses a prominent, wide workspace search field; the live desktop header constrained it to a maximum of 330px, materially weakening the shared-shell match.
- Expanded the search field to a responsive 420px–760px range on wide desktop screens (1200px+), while retaining the existing compact sizing below that breakpoint and the existing full-width mobile rule.
- Updated the placeholder to name the actual searchable business areas: events, customers, quotes and inventory.
- Added a focused regression assertion covering the wide desktop sizing, mobile rule and search scope copy.
- Source re-read confirms all three changes are present on main. Automated tests, Vite build and rendered desktop/mobile/light/dark acceptance remain UNVERIFIED; the browser-rendered appearance still requires owner review.

## Dashboard KPI icon hierarchy — 2026-11-06

- Matched the supplied dashboard reference's icon-led KPI scan pattern using four lightweight inline SVG icons: active events, draft quotes, customers and the next 14 days.
- Reworked the KPI cards into a two-column icon/content layout on desktop and a narrower icon/content layout on phones. The existing KPI labels, live Blade values, route destinations, permission gates and metric calculations remain unchanged.
- Kept the icon treatment CSS-token-driven so the existing light and dark themes inherit their current semantic colours; no raster/generated assets were added.
- Added a focused regression test for all four icons, desktop/mobile card geometry and continued live metric rendering. The stylesheet is included in the shared app Vite bundle.
- Source re-fetch confirms the markup, CSS and regression assertion are present on main. PHPUnit, Vite build and rendered desktop/tablet/mobile light/dark acceptance remain UNVERIFIED; do not claim visual acceptance until rendered review is completed.

## Dashboard composition correction — 2026-11-06

- Owner rejected the previous refinement as insufficiently close to the references. Re-inspection identified the main structural mismatch: KPI cards and lower operational panels were not consistently arranged in the reference scan order.
- Corrected the existing canonical Director dashboard rule block rather than leaving a second appended override layer.
- Desktop composition now follows: welcome banner → four KPI cards in one row → quick-action strip → Upcoming Events / Quote Pipeline side-by-side → Priority Now / Operational Readiness side-by-side.
- Tablet and mobile breakpoints collapse the grid deliberately; KPI and quick-action cards use two columns on narrower screens and one column on very narrow screens.
- Resource readiness tiles now use a compact two-column layout on desktop, matching the paired lower panel rather than stretching across the full dashboard.
- Scope remains limited to dashboard CSS and its regression test. No shared shell, sidebar, navigation, other page views, data, routes or permission rules changed in this pass.
- Canonical CSS correction: `4f0d2176f34a643ef68b05eae1cb6a2b937761d9`. Regression assertion update: `74165ccce0c4008471989b290af797e76723a417`.
- CI evidence for the latest tested source: dashboard-specific assertions pass; Laravel reports 369 passed and 6 failures in legacy shell/onboarding expectations unrelated to this dashboard layout. PHPMD still flags the existing DashboardController `__invoke()` at 101 lines (threshold 100). Psalm and populated upgrade/rollback checks pass. Browser regression completed with 56 passed and 6 failures: Finance horizontal overflow, offline Work-record checkbox interaction, and browser theme assertions still expecting the superseded palette. No dashboard-specific rendered screenshot/acceptance was produced; rendered visual acceptance remains UNVERIFIED.
- Owner visual approval remains the acceptance gate. Do not propagate dashboard styling to other pages.



## Dashboard adaptive KPI alignment — 2026-11-06

- Corrected the canonical dashboard KPI grid from a fixed four-column layout to auto-fit columns with a 190px minimum card width. Permission-filtered workspaces now use the available row width instead of leaving a blank KPI slot for each hidden metric.
- Reduced KPI grid gap from 12px to 10px to keep the strip proportionate as cards expand.
- Updated the dashboard composition regression assertion to require the adaptive grid declaration.
- No metric data, permissions, routes, dashboard section order, shared shell or non-dashboard page changed.
- Source re-read confirms the canonical CSS rule and matching regression assertion on `main`. PHPUnit, Vite build and rendered desktop/tablet/mobile light/dark acceptance remain unverified. Browser connector was unavailable because no originating browser context was attached.
- Next: review the dashboard's desktop/tablet breakpoints and shortcut-row density; retain the supplied reference scan order and do not propagate styling beyond the dashboard.


## Dashboard desktop/tablet composition refinement — 2026-11-06

- Kept the paired dashboard work panels in a two-column layout through tablet widths; the full single-column collapse now begins at 760px rather than 900px.
- Preserved the 900px transition for the welcome hero and compact two-column KPI/quick-action rows, so the banner and dense controls adapt before the main work panels stack.
- Corrected the quote-pipeline regression assertion to verify the actual four-stage quote grid instead of accidentally asserting the KPI grid rule.
- Added an explicit regression assertion for the 760px dashboard stacking breakpoint.
- No shared shell, non-dashboard page, business logic, routes, permissions or metrics changed.
- Source-level verification only. PHPUnit, Vite build and rendered desktop/tablet/mobile light/dark acceptance remain unverified; browser access is unavailable in this session.

## Dashboard panel density and alignment refinement — 2026-11-06

- Tightened Upcoming Events row height and vertical spacing while retaining the date tile, event identity, reference and status hierarchy.
- Reduced Quote Pipeline stage-card height and internal spacing, and tightened the queue-state strip so the four lifecycle stages remain the main visual focus.
- Reduced Workspace Health tile height and padding to balance the paired lower panels without hiding labels or live values.
- Kept Priority Now content, business data, routes, permissions, responsive breakpoints and shared shell unchanged.
- Added source regression coverage for the compact panel dimensions. Source changes are committed; PHPUnit, Vite and rendered desktop/tablet/mobile light/dark acceptance remain unverified.

## Dashboard panel-header copy correction — 2026-11-06

- Source audit found four explanatory subtitles still present in the dashboard panel headers despite the earlier copy-density objective.
- Removed the leftover subtitles from Upcoming Events, Quote Pipeline, Priority Now and Workspace Health. Section titles, action links, quote-stage descriptions, live records and priority explanations remain.
- Updated the Priority/Workspace Health source assertions and added a regression test for all four concise panel headers.
- Dashboard-only Blade and test changes. No data, routes, permissions, panel structure, shared shell or theme changes.
- Source changes committed; PHPUnit, Vite and rendered browser acceptance remain unverified.

## Dashboard priority empty-state accuracy — 2026-11-06

- Corrected the Priority Now clear-state sentence: its condition checks setup, quotes and resource actions, but does not check the event schedule. The copy no longer claims schedule status is part of that condition.
- Added a regression assertion for the truthful empty-state wording.
- Dashboard-only copy and test change; no priority logic, data, routes or permissions changed.
- Source re-fetch verification required. PHPUnit and rendered acceptance remain unverified.



## Dashboard fallback KPI consistency — 2026-11-07

- Source scan found the invoice KPI fallback (shown when quotes are unavailable) still used the older text-only markup while the other KPI cards use the icon-led reference layout. This produced a different grid structure for users whose workspace exposes finance but not quotes.
- Added the same compact document icon treatment and explicit label span to the invoice fallback. Metric value, supporting copy, destination and permission/tool conditions remain unchanged.
- Added a focused regression assertion that the invoice fallback includes the icon wrapper.
- Dashboard Blade and its existing feature test only; no shared shell, non-dashboard pages, data, routes, metrics or permissions changed.
- Source re-fetch verification completed after the commits. PHPUnit, Vite build and rendered desktop/tablet/mobile light/dark acceptance remain unverified.


## Dashboard mobile event status visibility — 2026-11-07

- Responsive cascade review found an older max-620px rule hiding `.zazu-dash-status`, while the refined event row retained a status-and-arrow trailing group. On phones this removed useful event state from the dashboard list.
- Added a narrow final override to keep the status badge visible at compact size, constrain its width and ellipsize long labels. Desktop and tablet rules are unchanged.
- Added a focused source-level regression test for the mobile status rule.
- Dashboard visual CSS and its existing feature test only; no event data, status logic, routes, permissions, shared shell or non-dashboard pages changed.
- Source re-fetch verification pending. PHPUnit, Vite build and rendered acceptance remain unverified.


## Dashboard mobile event markup alignment — 2026-11-07

- Responsive audit compared the current Blade event row (date, event copy, trailing status/arrow) with an older mobile CSS block that still targeted removed time/connector elements and treated the status as a direct grid child.
- Removed the obsolete mobile layout block. The canonical mobile rules now own the actual three-child event row, with the status kept visible inside its trailing flex group and the existing narrow-screen two-row treatment preserved at 380px.
- Replaced the stale regression assertions with checks against the current Blade structure and the actual status wrapper; added guards against reintroducing the removed selectors and invalid grid placement.
- Dashboard CSS and the existing UI proportion feature test only. No business logic, data, routes, permissions, shared shell or non-dashboard pages changed.
- Source-level verification only. PHPUnit, Vite build and rendered desktop/tablet/mobile light/dark acceptance remain outstanding.


## Dashboard adaptive workspace-health panel — 2026-11-07

- Adaptive configuration audit found the Workspace Health section was gated only by experience level. In a non-basic workspace with purchasing, inventory, assets and finance all disabled, the dashboard could render an empty panel.
- Tightened the Blade visibility condition: Workspace Health now appears only for non-basic workspaces with at least one of those four resource tools enabled. Individual metric cards retain their existing per-tool gates.
- Added a regression test covering all four resource-tool gates and the placement of the section condition.
- Dashboard Blade, its existing UI proportion feature test and this engineering state record only. No metrics, data, permissions, routes, shared shell or other page changed.
- Source-level verification only. PHPUnit, Vite build and rendered desktop/tablet/mobile light/dark acceptance remain outstanding.


### Dashboard reference refinement — sparse workspace layouts

- Refined KPI layout for workspaces exposing only one or two metrics: a single KPI is capped and centered; two KPIs share a centered, bounded pair rather than stretching across the full dashboard.
- Quick Actions now use an adaptive minimum tile width so permission-filtered action sets naturally reflow instead of leaving oversized empty columns.
- Added source-level regression coverage in `UiProportionFoundationTest`.
- Commit sequence: `3173be5` (dashboard CSS), `e8f25b3` (regression test).
- Runtime PHPUnit, Vite build and browser visual review remain unverified in this environment.


## Dashboard visual identity refinement — 2026-11-07

- Refined the dashboard welcome banner with a clearer primary-content/capability split, stronger greeting hierarchy and a softer coastal surface treatment.
- Rebalanced KPI tiles around the icon, label and live value so the metric reads as one deliberate unit; retained sparse-workspace and mobile layout rules.
- Unified dashboard work panels with a restrained blue-tinted border, softer shadow and more compact panel headers.
- Added source regression coverage for the dashboard-only visual hierarchy.
- The sidebar was intentionally left unchanged: it belongs to the shared application shell, and changing it would extend this dashboard-only approval scope to every page. Its deep-navy treatment remains a separate owner-approved shell objective.
- GitHub source changes only. PHPUnit, Vite build and rendered desktop/tablet/mobile light/dark review remain unverified; visual approval is still pending.


## UI acceptance contract reconciliation — 2026-11-07

- Rechecked the current dashboard Blade, canonical app theme tokens and dashboard visual stylesheet on `main` before changing test expectations.
- Updated stale source-level assertions to the implemented interface: coastal light tokens (`#F5FBFE`, `#159DD2`), current sidebar/text colours, current event date labels, approved/declined quote-stage labels, Workspace health naming and View all events action.
- Updated the onboarding dashboard assertion to the current Quick actions / Open finance surface, and updated the Playwright theme test to the current coastal palette while retaining the existing dark-mode expectations.
- No application CSS, Blade, business logic, routes or permissions changed in this correction pass.
- Changes are committed on `main`; source re-fetch verification is being completed. GitHub Actions rerun and runtime PHPUnit/Playwright results are not yet available, so do not treat these expectation updates as a green CI result. Finance overflow and other non-dashboard browser failures remain outside this dashboard-only objective.

## Owner dashboard acceptance and V1 execution handoff — 2026-11-07

- Owner accepted the current dashboard as sufficient for now and directed development to continue against V1 requirements. The dashboard refinement mission is closed by owner acceptance; no further dashboard iteration is active.
- Active development focus: V1 implementation and release-evidence closure.
- Added `tests/Feature/SyncAttachmentIdempotencyTest.php` on `main` (commit `d7debb0d8378633174f74479f26258d469799ed6`, corrected namespace commit `b3f9eb360e6259868993814419c09e939a3d372d`). The tests cover exact-content retry idempotency and reject reuse of the same key for different file bytes with otherwise matching metadata.
- PHP syntax check passed locally under PHP 8.4.24. Laravel execution and exact-head CI remain UNVERIFIED; the available combined-status query returned no status contexts. Do not mark the tests passed until the Laravel/quality workflow reports success for the exact current main SHA.
- Next V1 implementation target: integrate phone-local attachment queue/upload/download into the active Work attachment flow using the current business-scoped `ZazuOffline` contract. The existing `public/offline-attachments.js` is legacy code and must not be loaded unchanged; it depends on globals absent from the active Laravel shell. Preserve private storage, business isolation, retry/idempotency, logout cleanup and device-recovery behavior.

## Attachment idempotency coverage extension — 2026-11-07

- Extended `SyncAttachmentIdempotencyTest` to cover three server contracts: identical retry returns the original attachment; same key with different bytes but matching metadata returns HTTP 409; same key cannot move an attachment to a different job in the same business.
- Latest test commit: `a1d16620996479d461574c5317dfccc82c48d35d` on `main`.
- Re-read current-main test source and ran PHP 8.4.24 syntax lint: PASS. Laravel feature execution remains UNVERIFIED pending exact-head CI evidence.


## Phone-local Work attachment integration — 2026-11-07

- Connected the authenticated Work attachment form to the active `ZazuOffline` client. The legacy `public/offline-attachments.js` remains unused.
- Added business-scoped IndexedDB file queue/read/write, idempotent multipart upload retry through `/api/sync/attachments`, remote attachment listing and private-file download caching. Pending local Blobs are retained until the server returns a verifiable attachment ID.
- Multi-file selection (1–10 files, each up to 20 MB) is validated first and saved in one IndexedDB read/write transaction. A failed batch transaction does not intentionally leave a partial queue.
- Work panel uses the online Laravel form path when the same-origin host is reachable; when unreachable it stores the selected files on the device. Unpaired devices are told to pair before upload can occur.
- Added Work attachment UI state/list, local file open action, online/reconnect refresh, and current-page duplicate avoidance using server attachment IDs.
- Advanced service-worker static cache to `zazu-static-v30`, with matching Laravel source assertion and Node service-worker cache test expectations.
- Added `e2e/offline-attachment-sync.spec.js` for disconnected local file persistence, bearer-authenticated reconnect upload using the original UUID idempotency key, and caching a remote attachment. Expanded `PwaOfflineFoundationTest` source assertions. Existing `SyncAttachmentIdempotencyTest` remains responsible for server duplicate/change/move-key contracts.
- Source write/re-read commits: `789820c`, `36acc45`, `c7cdd3d`, `3cf20a6`, `eedacf8`, `fd695f2`, `bc1de62`, `c94678a`, `6ceeec3`, `fd61e3d`, `65a88cc`, `c580099`, `1b06cce`, `1c05cb5`. Documentation follow-up is recorded in `docs/ZAZU_V1_STATUS.md` and `docs/ZAZU_OFFLINE_FIRST_ARCHITECTURE.md`.
- Current `main` source changes are **IMPLEMENTED**. GitHub combined status returned no status contexts for `bc1de6224971803bdecee18dc8bcbed7781c5287`; the commit-workflow-runs connector returned no PR-triggered runs for `1c05cb5359695e0a5588593c1de0fdf3407b3c2e`. Exact-head Laravel, Vite, Playwright and quality execution therefore remain **UNVERIFIED**. No readiness uplift is claimed.
- Physical installed-device acceptance remains open. The current upload endpoint requires a server-backed event ID; an offline-created job must first receive its server identity. Verify the cross-step flow after current source checks are available.


### Attachment identity reconciliation — 2026-11-07

- Added `reconcileAttachmentEventIds()` to the active sync client. Pending, business-scoped attachment records with `event_local_id` are matched against `state.jobs` and updated with the job's `server_id`/`id` in one IndexedDB transaction before upload.
- This preserves the older phone queue's stable local-parent reference across offline Work creation and later identity assignment. The online API request still carries only the resolved server event ID.
- Added a Playwright regression that seeds the previous queue record shape, assigns the synced job identity, and verifies the exact event ID and idempotency key sent by the client.
- Added source-contract assertions and updated the V1 status/architecture notes. Latest implementation/test commit: `e3e2c1ed1fc8c55f4d74ff0b281ffc044e7cb806`; status-doc follow-up: `c30fd588f8dfa29a9e3a52ab87d2b281968b8dfc`.
- Runtime test execution remains UNVERIFIED. The browser test covers the identity-remapping boundary, not a fully rendered offline-create → reconnect → attach journey.


### Attachment list/download API acceptance coverage — 2026-11-07

- Extended `SyncAttachmentIdempotencyTest` through the paired-device API: upload returns the idempotency key/event identity consumed by the client; list returns the expected attachment identity, original name and size; private file download returns the named file.
- Added foreign-business negative cases for event-filtered listing and private download. The fixture confirms the foreign private file actually exists, so the download denial exercises the business boundary rather than a missing-file response.
- Current implementation/test source is on `main`; latest test commit: `bd47ec672ea0f4b609614ce24c3baa9d1ac4bd0a`. Laravel/CI execution remains UNVERIFIED.


### Trusted-device session transition coverage — 2026-11-07

- Added `e2e/offline-session-transition.spec.js`: a paired business workspace with a pending customer mutation must remain signed in and retain its queue when the `/up` auth-host probe fails; after reachability returns, successful account logout and same-account re-login must preserve the paired device and pending mutation.
- Extended `SyncDeviceManagementTest`: owner revocation must reject the old bearer token at bootstrap, pull, push and attachment-list endpoints. Existing non-owner and foreign-business revocation denial cases remain.
- Exact source/test commits: `614c24131edc3023c05baad5e583614a8f4c7156` (browser test fixture), `4de9e8bf018404fe1b67e16a7d55b0ec09ef7109` (revocation matrix). Documentation updates: `0059793c55b8cd3abe13c6fea6bef636e9bcb9bc`, `252d0a4f79751280ea26113ad506ab5f846c6715`.
- Tests are ADDED, not executed. Physical installed-device acceptance, expired-session, interrupted logout and stale async completion cases remain open. No V1 evidence score uplift.


### Backup archive integrity hardening — 2026-11-07

- New `zazu:backup` archives use manifest format version 2 with SHA-256 checksums for the SQLite/MySQL database payload and every private-storage file.
- `zazu:restore` verifies the checksum map matches the exact payload file set and streams each file through SHA-256 before extraction or any target mutation. Missing/malformed/extra/mismatched checksums fail closed.
- Restore retains compatibility with format-version-1 archives without checksum maps; version 2 requires them.
- `ZazuBackupRestoreTest` asserts database/private-file checksum generation and rejects a tampered private payload while preserving the installed private probe.
- Source/test commits: `2c79de9`, `e49dfc7`, `e684203`, `6d361c8`, `6350760`, `1ac0a7e`, `3138d4f`. Docs: `f0e2410`, `e1be645`.
- Integrity hashes detect corruption/drift; they are not a signed authenticity mechanism. Real operator backup/restore verification remains open. Exact-head Laravel/quality execution not yet observed; no recovery score uplift.


- Backup test follow-up: `ZazuBackupRestoreTest` now also rejects a format-v2 archive missing its required checksum map and restores a valid legacy format-v1 archive without a checksum map. Latest backup test commit: `45f3cfed9bee48153d939932ba4c495c85c0b26b`; assertion formatting follow-up: `89daa318f310b5cf873b50711e31eff73db12b92`. Execution remains pending.


## Navigation contrast and geometry correction — 2026-11-09

- Reworked the shared hierarchical navigation authority in `resources/css/app.css`: explicit sidebar foreground/background pairing for idle, hover, open and active states; 4px control radius; tighter 40px area triggers and 38px destination rows; reduced padding; removed hover translation; retained a visible 2px keyboard-focus indicator.
- Reworked dashboard-specific navigation in `resources/css/zazu-dashboard-authority.css`: fixed dark-sidebar hover to a darker navy surface with white foreground (never white text on pale blue), removed permanent blue borders, used a restrained cyan active marker, compacted flyout geometry and retained keyboard focus.
- Removed two superseded dashboard navigation palette blocks that contradicted the current dark-sidebar palette and caused the stylesheet to carry dead, competing state definitions.
- Added `UiProportionFoundationTest::test_navigation_uses_compact_geometry_and_explicit_first_paint_contrast` to guard the intended contrast, radius, focus state and removal of the obsolete pale-blue/white-text declarations.
- Commits on `main`: `b623d33` (shared navigation), `d404b24` (dashboard navigation and obsolete rule removal), `7379a8b` (regression coverage).
- Source changes are committed and re-fetch verification is pending. Runtime PHPUnit, Vite build and rendered first-load/hover acceptance are not yet verified. No claim of visual sign-off.

## App-wide spacing refinement — 2026-11-09

Owner expanded the visual refinement scope beyond the dashboard: apply the established spacing, surface, border and shape preferences consistently across Zazu EMP, without changing business behaviour or page structure.

- Confirmed the dashboard quote-stage grid had only a 2px top inset below its panel header. Increased it to 12px while retaining the existing horizontal and bottom rhythm.
- Confirmed the upcoming-events list had only a 3px top inset. Increased it to 12px so the first event no longer crowds the header divider.
- Found the shared disclosure/accordion body used zero top padding beneath its divider on desktop and mobile. Increased the top inset to 12px desktop / 10px mobile.
- Shared form sections retain deliberate 20px desktop and 14–16px responsive padding; shared panels retain 18px padding. These were not globally overridden.
- Added source regression coverage for dashboard panel insets and disclosure spacing.
- Commits: `9c21375` (dashboard spacing), `95bda8f` (disclosure spacing), `71987f0` (dashboard regression test), `6e8a06b` (disclosure regression test).
- Scope is spacing-only. Existing status pills/chips remain intentionally rounded semantic labels; navigation and action controls retain the compact non-oval treatment already established.
- Source verification and CI/runtime test execution remain pending; no rendered browser acceptance is claimed.

## Product-wide visual authority — 2026-11-09

- Owner confirmed Zazu EMP must maintain one consistent quality level across all authenticated modules, not just the dashboard.
- Added `resources/css/zazu-product-authority.css` as the final shared visual layer, loaded after dashboard/module styles in the authenticated application shell and registered as a Vite entry.
- Shared authority aligns neutral border contrast in light/dark themes, compact 5/7/8px geometry, card/panel/form surfaces, header insets, form-section spacing, button and field geometry, table wrappers and responsive padding.
- Semantic status chips remain compact labels; module-specific layouts and workflows remain intact.
- Added `UiProportionFoundationTest::test_product_wide_visual_authority_is_loaded_after_module_styles_and_applies_shared_primitives`.
- Commits: `25dfe61` (shared stylesheet), `1c3381b` (authenticated layout load), `32008a6` (Vite entry), `67e8eb4` (regression coverage).
- Source is committed on `main`. PHPUnit, Vite build, full-page rendered comparison across modules/themes and mobile acceptance are not yet executed/verified. No claim of visual sign-off or CI pass.

## Shared work-surface refinement — 2026-11-09

- Continued product-wide visual pass using actual shared selectors found in the application CSS and Blade views.
- Standardized command-band and metric-card radius/border/depth; removed metric-card hover lift.
- Converted shared list rows from individually floating/translating cards to a coherent bordered list surface with consistent row separators and neutral hover state.
- Reduced empty-state vertical padding from 56px to 36px to avoid oversized blank areas across modules.
- Aligned document-table wrappers and table headers/cells with the shared surface and border tokens.
- Unified form-contained native controls and actionbar geometry.
- Added regression assertions for list, metric, document-table and empty-state styling.
- Commit: `3c004fc` (shared surface refinement), `6ef6bd0` (regression coverage).
- Runtime test/build and rendered cross-module light/dark/mobile review remain outstanding.

- Follow-up geometry scan found remaining pill-shaped interactive controls and repeated surfaces in workload filters and dashboard quick actions/KPIs. Their geometry now follows the shared compact 5/7px radius system; semantic status/count tags also use restrained corners. Circular progress markers and decorative dots remain circular by design.
- Regression coverage extended for workload filters and dashboard quick-action geometry.
- Commits: `40168a1` (compact control geometry), `f03a8b9` (regression coverage).


## Cross-module visual defect correction — 2026-11-09

- Owner reiterated that thick blue borders and dull, repetitive card surfaces remain unacceptable across the application.
- Replaced the prior weak colour treatment at the end of the canonical resources/css/zazu-product-authority.css rather than layering another competing stylesheet.
- Added coordinated semantic colour families for blue, green, amber, teal, violet and coral in both light and dark themes.
- KPI tiles now rotate through distinct colour surfaces; quote pipeline stages use separate workflow colours; Finance, Inventory/Purchasing, Suppliers/Assets, Customers/Quotes, Reports and Calendar receive route-specific card surface families and matching section headers.
- Normalised shared card, panel, dashboard hero, shell separator and compact editor header borders to 1px neutral separators. Removed oversized blue rails from shared attention/context surfaces while retaining semantic status fills.
- Kept helper dock at the screen edge, compact and hidden while mobile navigation is open.
- Added/updated UiVisualSystemRegressionTest assertions for route-specific card colour families, light/dark semantic colours and thin-border rules.
- Commits: 3b0b902 (colour authority), 4295ca8 (border normalisation), ee61e4f (module card colours), 048ba78 (regression assertions).
- Source re-read confirms current CSS and test changes are on main. Exact-head CI is still running; no green result is claimed yet.
- Rendered desktop/mobile visual acceptance remains UNVERIFIED because no connected browser session for the local Zazu instance was available during this pass. The owner should see updated CSS after the Vite dev server refreshes/rebuilds the imported stylesheet.
- Continue the UI defect audit across authenticated views; source-level border search identified remaining 2px dashboard shell seams and compact editor header declarations, now overridden by the final shared authority. Preserve functional/semantic borders such as focus rings, error summaries and loading indicators.


## Follow-up UI defect sweep — 2026-11-09

- Re-entered through the Director control plane and UI Recovery skill. Target remains Leano-Jordan/ZazuEMP on main; scope remains visual defects only, with routes, permissions, data and business behaviour protected.
- Confirmed the authenticated shell exposes `data-zazu-route` and the dashboard body class, so route-specific module selectors are grounded in actual markup.
- Corrected the dashboard selector mismatch: the rendered Blade panels use `.zazu-dash-surface-head`, not only `.zazu-dash-panel-head`. Added actual dashboard-specific header, event, priority, resource and quote-stage colour treatments with explicit specificity over older dashboard declarations.
- Found and removed the source-level 2px dashboard sidebar, topbar, hero, hero-side and panel-header borders in `zazu-dashboard-authority.css`; reduced the compact editor header divider from 2px to 1px in `zazu-final-visual-sweep.css`.
- Reduced remaining shared document/quote total separators, calendar holiday accents, inspector notes, offline-origin notice and nav flyout accent to thin separators. Added `.zazu-dash-context-note` to the shared thin-rail rule. Preserved intentional focus/error indicators, spinner rings and circular stage markers.
- Regression suite now has 10 public test methods (PHPMD class-method limit respected). The prior Laravel run had 412 passing tests and one visual assertion failure caused by an incorrect minified CSS expectation; that assertion was corrected. The prior PHPMD failure was only the added 11th public test method; assertions were folded into an existing test. Added checks for the source-level dashboard/editor border rules.
- Source re-read confirms actual dashboard markup selectors, route attribute, colour treatment and border declarations on `main`. Latest CI for current HEAD `c8f20d4` is queued/in progress; no pass is claimed yet.
- Browser connector could not read the user's local Edge session because this chat lacks the originating browser context. GitHub browser-smoke workflow is pending; rendered acceptance remains unverified until that workflow completes or a connected browser session is available.
- Continue the UI recovery pass across page-level form spacing, dense registers, dialogs, responsive overflow and module-specific header hierarchy. Do not infer visual acceptance from source or CSS tests alone.


- Additional page-level finding: the quote creation form placed `.zazu-quote-line-editor` inside a `.zazu-form-section` while also assigning it the full `.zazu-card` surface, creating an unnecessary card-within-card frame. Removed the nested card primitive and changed quote-line rows to a two-column description/price grid, stacking cleanly below 620px. Added source regression assertions for the flattened markup and responsive grid.
- Form-section title bands now have their own deliberate colour hierarchy. Finance uses green, Inventory/Purchasing amber, Quotes/Customers violet, and general Work teal; each route's form section gets a restrained matching surface. Quote routes are explicitly excluded from the general Work tint so the more specific commercial colour is retained.
- Latest source commits verified on main: `c9c1d54` dashboard source borders; `0c9c3df` compact editor divider; `b146d1a` regression coverage; `c8f20d4` dashboard context rail; `4df8a76` form-section colour bands; `0db39cb` route selector correction; `dbd4056` form regression assertions; `fde6d19` quote markup flattening; `8f8358d` quote-line layout; `38167e7` regression coverage.
- Latest CI for HEAD `38167e7` is queued/pending; rendered browser acceptance is still not available in the connected browser context. Continue to verify exact-head CI and browser smoke before calling this pass accepted.

- Continued into customer and Work detail views: customer contact/value cards now use the customer violet surface family rather than the default grey inset; the five-step Work progress strip now distinguishes current (blue), completed (green), and upcoming (teal-neutral) states instead of outlining current and completed steps identically. Regression assertions added without increasing the test class's public-method count.


## Owner-directed border, colour and navigation correction — 2026-11-09

- Target: remove heavy repeated control/card framing across authenticated pages; make semantic card colour unmistakable; soften the dark navigation hover without creating foreground/background contrast failures.
- Source findings:
  - The shared product authority assigned white hover text while using the shared sidebar-hover token. In the light shell palette that token is pale blue, creating the reported white-on-light hover defect.
  - The dashboard authority still contained multiple 1.5px card/control borders and late neutral important fills on event, focus, resource and quote-stage cards. Those later declarations overruled the earlier semantic colour treatments, which explains why the quote pipeline looked nearly monochrome.
  - Shared action controls could retain browser focus outlines in addition to their own borders; the final product layer now normalises action-control borders and keeps a deliberate 2px keyboard-only focus indicator.
- Changes on canonical main:
  - Strengthened all six light-theme semantic surface tokens (blue, green, amber, teal, violet, coral) while retaining coordinated dark-theme values.
  - Increased route-card colour coverage from weak 34–38% mixes to 68%, and assigned visible full-tint surfaces to the dashboard event, priority, resource and quote-stage cards.
  - Normalised shared action controls and common card/list/table surfaces to 1px neutral borders; removed default action-button shadow framing while preserving keyboard focus visibility.
  - Replaced the shared hover foreground with the sidebar foreground token so it remains readable on both light and dark sidebar surfaces.
  - Softened the dark navigation hover from #29445D to #34536D, with a matching light foreground #EAF2F9; active state remains distinct.
  - Reduced every 1.5px declaration in zazu-dashboard-authority.css to 1px and updated navigation regression expectations.
- Commits: 8d678d0 shared colour/control correction; 4a8ac27 dashboard border reduction; 3b67750 colour/control regression coverage; 210fb8a palette expectation correction; 931284d shared nav hover; 628aaac dashboard nav hover; fc34d6d navigation regression update.
- Re-fetch verification: changed product CSS, dashboard CSS and both affected visual regression tests were re-read from main; new palette tokens and hover colour are present; obsolete #29445D references are absent from the two authoritative CSS files.
- Verification boundary: source-only verification. PHPUnit, Vite build, exact-head CI and rendered desktop/mobile acceptance have not yet been observed for this batch. Do not mark the visual issue accepted until rendered review confirms the photographed Work action controls and dashboard quote pipeline.

## Gradient removal and navigation redesign — 2026-11-09
- Removed every gradient function from zazu-product-authority.css and zazu-dashboard-authority.css; semantic card, header, metric, form and dashboard hero surfaces now use solid fills.
- Replaced light-theme dark navy sidebar with light slate blue (#D5E0E8), dark readable text (#23394D), lighter hover (#C2D2DE) and distinct active (#B4C8D7).
- Dashboard navigation now follows the same light slate-blue hover/active/flyout palette with dark foreground text. No white-on-light hover.
- Dark theme sidebar changed from midnight blue to neutral charcoal-slate (#202B35) with coordinated muted foreground and restrained hover/active.
- Updated navigation regression expectations and visual-system palette expectations. Source and CI verification pending.

- Follow-up cleanup: removed all 34 remaining gradient functions from app.css, including card/command surfaces, wallpaper overlays, dashboard visual overlays, auth/login backdrops and decorative section fills. Replaced them with solid theme/semantic surfaces; wallpaper image assets remain supported. Skeleton shimmer is now a quiet solid placeholder rather than a moving gradient.
- Removed the last hard-coded dark-blue dashboard hover declaration from the product authority; dashboard hover now resolves to the light slate-blue palette.


## Card-only colour and navigation contrast correction — 2026-11-09

- Restricted semantic colour to actual card surfaces: route cards, KPI cards, dashboard event/focus/resource/quote-stage cards, customer detail cards and work-progress states.
- Removed category-specific card-header fills and route-specific form-section fills. All module and dashboard title bars now use one restrained neutral slate surface; dark mode uses a coordinated charcoal-slate surface.
- Fixed selected navigation contrast explicitly:
  - Light: #B4C8D7 selected surface with #23394D text.
  - Dark: #486174 selected surface with #F3F6F8 text.
  - Dark hover: #344653 with #E5EBF0 text.
- Added regression assertions for selected states and unified title bars.
- Automated tests, build and rendered desktop/mobile acceptance remain pending.


## Sidebar workspace footer contrast correction — 2026-11-09

- Found stale product-authority declarations left the footer workspace label and account kicker in pale blue while their actual .zazu-footer-workspace surface was transparent on the new light slate sidebar. The old deep-navy-rail rules were not scoped to the actual rendered footer container.
- Added explicit theme-paired surface/foreground rules for the Workspace identity card and Signed in/account metadata:
  - Light: #E7EEF3 surface, #23394D primary text, #536B7F secondary labels.
  - Dark: #293945 surface, #E5EBF0 primary text, #B8C7D2 secondary labels.
- Kept the account popover and navigation behavior unchanged. Added regression assertions for both themes.
- Current repo source baseline is being re-audited after the owner-directed V1 request; no offline implementation change is included in this footer correction.


## Director product-wide V1 audit — 2026-11-09

- Read the canonical Director contract, Director reconnaissance/release-readiness skills, current state, failure/readiness registers, product status, V1 checklist/scorecard, offline architecture and company-wide offline standard.
- Audited current application architecture/source and test contracts across account/business context, customer/event lifecycle, quotes, finance, purchasing/receiving, inventory/assets, reporting/search, attachments, offline workspace, security, recovery, deployment, UX and legal/brand gates.
- Added `docs/DIRECTOR_PRODUCT_AUDIT_2026-11-09.md` as the comprehensive current product audit. V1 remains NO-GO; the 60/100 score remains the last formal historical baseline and was not re-scored without current exact-head and operational evidence.
- Confirmed the current PC/phone address problem: local PC development origin is a temporary LAN IP + port 8000, served by Laravel's development server. It changes across networks and plain HTTP on a LAN IP is not a secure PWA origin. This is not a production topology.
- Recommended a private Tailscale MagicDNS + HTTPS Serve origin as the development bridge for cross-Wi-Fi access; it does not replace PC availability or device-local offline operation. Public production hosting remains deferred.
- CI reconciliation:
  - `59a5db5`: Laravel 409 pass / 3 stale UI assertion failures; PHPMD one method at 101 lines; Psalm and populated upgrade/rollback passed.
  - `3746d08`: Laravel 411 pass / 1 stale selector assertion; PHPMD passed; populated upgrade/rollback passed; Psalm passed; browser smoke was running; Quality failed on the same stale selector.
  - `285fa18`: obsolete selector assertions corrected; exact-head Laravel, Quality, PHPMD, Psalm, browser and populated upgrade/rollback workflows are now running. No current-head pass is claimed yet.
- Found branch-policy drift: `director/v1-sync-ci-repairs` is 124 commits behind main and 0 commits ahead. No unique work needs porting. Available GitHub tools do not expose branch deletion; do not create another branch or force-update it to work around this.
- Next active target: exact-head CI closure, then establish the private HTTPS development origin, then PC/mobile offline parity and physical acceptance. Do not expand features.


## Browser theme regression correction — 2026-11-09

- Exact-head `c1ed1a4` passed Laravel, Zazu Quality, PHPMD, Psalm, populated upgrade/rollback and push checks.
- Browser smoke completed but failed its theme-token assertion on all three viewports because it still queried removed legacy variables `--zazu-surface-canvas` and `--zazu-blue-primary`. The current product-authority tokens are `--zazu-surface` and `--zazu-primary`.
- Replaced the stale browser assertion with the current light/dark tokens and explicit contrast checks for selected navigation and the Workspace footer. Browser rerun pending.
- Offline review continues next: the service-worker HTML cache is user-scoped but not business-scoped; this is a confirmed cross-business stale-page risk and is the first offline boundary fix.


## Offline business-context cache boundary — 2026-11-09

- Confirmed the service-worker authenticated page cache was partitioned by user only, unlike the business-scoped IndexedDB workspace. Same-user business switching could leave previous-business HTML available offline.
- Rebuilt the page-cache identity as user + business; require both rendered body IDs; clear old context after a successful business switch; reject late old-context writes through the generation counter.
- Advanced service-worker cache to v31 and added JS/PHP regression coverage.
- PC and mobile offline parity remains the next active objective after exact-head CI. The stable development-origin decision is Tailscale MagicDNS + HTTPS Serve, pending owner device installation/approval.

## Editor and save-action rhythm — 2026-11-09

- Inspected shared editor layout and representative customer, job, quote and inventory forms before changing styles.
- Standardized editor column gap, form-section spacing/padding, section-header rhythm and form-grid gaps.
- Removed sticky side behaviour from form-aside guidance at tablet widths so it no longer competes with the form.
- Unified normal and sticky action bars: neutral border, restrained shadow, consistent radius/gap, safe-area-aware sticky offset and predictable mobile full-width actions.
- Added regression assertions for editor spacing, form sections, action bars, safe-area handling and single-column mobile form grids.
- Commits: `77d53b0` (editor/actionbar refinement), `e9855f6` (regression coverage).
- PHPUnit, Vite build and rendered desktop/tablet/mobile visual review remain unverified.

## LAN HTTPS launcher failure-cleanup hardening — 2026-11-10

- Active target: make the private LAN HTTPS launcher fail closed without leaving an orphaned Laravel development server.
- Finding: Caddy configuration validation occurred after Laravel startup; Caddy process launch, early exit, or listener timeout could leave the loopback Laravel process running after the HTTPS launcher failed.
- Change on canonical `main`: Caddy configuration is now validated before Laravel starts. If Caddy process launch/PID persistence fails, Caddy exits during startup, or the HTTPS listener does not bind within the existing timeout, the launcher stops the Zazu Laravel process and removes the saved Caddy PID where applicable.
- Added Windows workflow contract checks for validation-before-start ordering and cleanup on all three proxy failure paths. Existing PowerShell parser and wrapper checks remain.
- Commits: `02868ed` launcher cleanup; `2e7c95a` Windows workflow regression guard.
- Re-fetched both changed files from `main`; the expected ordering and cleanup calls are present. This is source reconciliation only. Windows PowerShell workflow execution and full exact-head Laravel/quality/browser/static CI have not yet been observed. No readiness-score uplift is awarded.
- Physical acceptance remains owner-PC/device work: install/verify Caddy, run the HTTPS launcher on a trusted Private Wi-Fi network, install only the public root certificate on the phone, then test same-network access and the installed offline/restart/reconnect journey. Do not bypass certificate warnings or migrate browser origin with pending queues.
- Next target: obtain exact-head Windows Ops workflow and full application CI evidence; repair only confirmed failures, then continue secure-origin PC/mobile offline acceptance.


## Windows Ops contract and offline documentation reconciliation — 2026-11-10

- Re-inspected the HTTPS launcher, development-server wrapper, stop wrapper, command files and Windows Ops workflow on canonical `main`.
- Strengthened `.github/workflows/zazu-windows-ops.yml` to assert Caddy config validation precedes Laravel startup; Laravel remains loopback-only behind Caddy; Caddy launch/PID-write failure stops any started Caddy process, removes partial PID state and attempts Laravel cleanup; early Caddy exit and HTTPS listener timeout each stop both processes and remove PID state.
- The first strengthened catch assertion did not match the launcher's guarded nested cleanup `try/catch`. Reconciled the contract with the actual implementation and re-evaluated every workflow regex against the current launcher source. All source-contract predicates now match. This is static source verification only; no PowerShell runtime or GitHub Actions pass is claimed.
- Reconciled stale offline-attachment statements in `docs/ZAZU_OFFLINE_FIRST_ARCHITECTURE.md` and `docs/LOAD_SHEDDING_MOBILE_TEST_RUNBOOK.md`: the active Work form is integrated with `ZazuOffline`; persisted-job attachment queueing, local retrieval, idempotent reconnect upload and private download caching exist in source with Laravel/Playwright coverage. The legacy `public/offline-attachments.js` remains unused. A complete rendered offline-created Work → attachment journey and physical installed-device acceptance remain open.
- Commits: `d699c56` architecture reconciliation; `fecac6e` mobile runbook reconciliation; `9adbbe5` Windows Ops contract expansion; `55b3b8b` PowerShell quoting correction; `863b693` nested-cleanup assertion correction.
- Current Windows Ops workflow and launcher source were re-fetched after the final correction. The GitHub combined-status endpoint returned no status contexts for the inspected baseline, and the available workflow-run lookup does not expose push-triggered runs. Windows-hosted execution therefore remains UNKNOWN / pending.
- Next: obtain actual Windows Ops Actions result; then run the exact-head application CI batch. Continue with secure HTTPS installation and the company-standard physical offline acceptance sequence (internet and host unavailable, installed-icon launch, declared workflows, local file persistence, restart, reconnect, idempotency and conflict recovery). Do not claim full offline parity.


## Mobile offline setup visibility — 2026-11-10

- Target: make a failed mobile PWA/service-worker installation visible instead of silently leaving the user dependent on the development server.
- Current-source finding: insecure LAN HTTP already displayed a warning, but secure-origin service-worker registration failures were swallowed and browsers without service-worker support received no explanation.
- Change on canonical `main`: added a distinct accessible warning for unsupported service workers and registration/activation failures. Normal online use remains available; the warning explains that offline setup is incomplete and that the device may still require the server. Added a focused PWA source-contract assertion.
- Commits: `8477f9f` application; `dfbad0f` regression test.
- Re-fetched both changed files from `main`; the new notice, registration failure paths and test assertions are present.
- Verification boundary: source reconciliation only. PHPUnit, Vite build, exact-head CI and real Android installation are not yet verified. This does not itself make the current HTTP LAN origin secure or remove the need for a reachable installation origin.
- Next operational gate: use the existing `Start-Zazu-LAN-HTTPS.cmd` launcher, install only Caddy's public `root.crt` on the test phone, install Zazu from the trusted HTTPS URL, allow the service worker to prime the bounded page set, then test installed-icon launch with the PC host and internet unavailable. Do not migrate the browser origin while pending local mutations or attachments remain unsynchronized.

## Director full audit, CSP hardening and exact-head verification — 2026-11-10

- Canonical full audit: `docs/DIRECTOR_FULL_AUDIT_2026-11-10.md`; canonical V1 release ledger reconciled in `docs/V1_RELEASE_CHECKLIST.md`.
- Verified application/test baseline: `90b3d1b7ad1c888a69f728df0add75d834884bbf`. Subsequent `aa3c753` and `3a0b018` commits are audit/ledger documentation only; they do not change the application/test baseline.
- Exact-head verification: Laravel 417 passed / 2,954 assertions; browser smoke 65 passed / 22 skipped / 0 failed; Zazu Quality + secret scan, Psalm, PHPMD, populated upgrade/restore/rollback, Director health, and Push on main / CodeQL all passed. SonarCloud was skipped and is not counted.
- CSP hardening: `style-src-elem` now uses the per-response nonce; nonce added to standalone inline style blocks in landing, purchasing create and error layout; regression coverage added. Residual `style-src-attr 'unsafe-inline'` and legacy fallback remain while dynamic attributes are migrated. Do not call CSP closure complete.
- The 13-domain V1 evidence score remains 69/100 (3.46/5); no numerical uplift awarded. Commercial offline licensing was independently assessed at 1/5 outside the historical rubric: the local entitlement service is a foundation, but signed activation, owner status/renewal and application enforcement are not integrated.
- V1 remains NO-GO. Outstanding: physical phone/tablet secure-origin offline acceptance; selected persistent production host and deployment smoke; actual operator restore and deployment rollback; trusted-device/lost-device acceptance; privacy/legal/incident process; Zazu brand clearance; paid offline licensing decision/implementation; remaining CSP attribute migration and log redaction/access policy.
- Next Director sequence: preserve the green exact source; complete physical acceptance on secure HTTPS; choose and validate the actual production deployment; run operator recovery/rollback; close legal/brand/licensing and security operations; then re-score.
- Evidence limits: no customer/staging URL, Windows runtime, physical phone/tablet or production database was accessible during this repository-only audit. Those gates remain OPEN/UNVERIFIED, not assumed passed.

## Offline Work attachment journey gap — 2026-11-10

- Exact current documentation/control-plane head `1852f955f9ee56b60a7bac232bd5a47665f1a6c0` triggered the full push CI batch. Laravel: 417 passed / 2,954 assertions; browser smoke: 65 passed / 22 skipped / 0 failed; Quality + secret scan, Psalm, PHPMD, populated upgrade/rollback, Director Contract Lint and Director Health passed. SonarCloud skipped.
- Source inspection found a concrete gap between the implemented offline attachment identity-reconciliation helper and the operator workflow:
  - `public/zazu-offline.js::queueAttachments()` rejects any job without a positive server ID.
  - `reconcileAttachmentEventIds()` can resolve `event_local_id`, but the normal queue path never creates a local-ID attachment record.
  - The E2E reconciliation test manually seeds an IndexedDB record with `event_local_id`, so it proves the reconciliation helper, not the real operator journey.
  - `resources/js/app.js::setupZazuOfflineDirectoryViews()` renders local Work rows as non-interactive articles without an attachment/detail action; the server-rendered Work detail/attachment form is unavailable until a server record exists.
- Classification: **V1 offline workflow gap / P1**. Do not claim a complete offline-created Work → attach file → reconnect journey.
- Next implementation target: add a safe, explicit local Work attachment path that accepts a local job identity, persists the Blob with business ID + `event_local_id` + idempotency UUID, presents the pending file in the local Work row/detail surface, and only uploads after the job mutation has been acknowledged with a verified server event ID. Preserve atomic writes, 10-file/20 MB limits, business isolation, retry state and no premature deletion. Add a real UI Playwright journey; do not rely on hand-seeded IndexedDB fixtures.
- Current application/test baseline remains `90b3d1b7ad1c888a69f728df0add75d834884bbf`; current control-plane documentation head is `1852f955f9ee56b60a7bac232bd5a47665f1a6c0`. No code change or readiness-score uplift was made in this investigation.

## Offline-created Work attachment implementation — 2026-11-10

- Target: close the P1 gap where a job created in the phone-local Work queue could not receive attachments until it had a server ID.
- Current source finding: queueAttachments() required a positive server event ID; the local Work directory rendered queued jobs without an attachment action; the existing identity-reconciliation helper therefore had no real operator entry point.
- Implemented directly on canonical main:
  - public/zazu-offline.js: attachment queue accepts either a validated server event ID or a validated local job ID; local records persist event_local_id, business ID, Blob and stable idempotency UUID. Local-ID retrieval is supported. Reconnect upload remains gated on successful reconciliation to a positive server event ID.
  - resources/js/app.js: pending local Work rows expose an Attach files action; files are stored locally through the shared offline client and the UI reports the local save result.
  - tests/Feature/PwaOfflineFoundationTest.php: added source-contract guards for the local identity queue/retrieval path and Work-row entry point.
- Commits: 0a12688 offline queue; 48e991b local Work UI; 352f9d1 regression guards.
- Re-fetched all three changed files from main; blob SHA reconciliation confirms the expected edits are present.
- Verification boundary: GitHub combined-status lookup for 352f9d1 returned no status contexts. No PHPUnit, Vite, Playwright or exact-head CI pass is claimed. The test currently guards the source contract; the existing hand-seeded E2E identity test still needs replacement/extension with a real local Work-row click → file selection → queued Blob → job sync → attachment upload journey.
- Readiness: no score uplift. Offline-created Work attachment path is IMPLEMENTED AT SOURCE / AUTOMATED VERIFICATION PENDING. V1 remains NO-GO.
- Next target: add the real Playwright journey and verify that attachment rows are visible/openable from the local Work row; then reconcile exact-head CI and Guardian regression review.

## Real offline Work attachment journey regression — 2026-11-10

- Extended e2e/offline-attachment-sync.spec.js with a real Chromium operator journey: create a customer using the offline customer form, create a job using the offline Work form, open the Work directory, select a file through the rendered Attach files control, and verify the Blob can be read back against the local job ID.
- The test then reconnects while the job still has no server identity and asserts the attachment remains pending with zero uploads. It assigns the simulated server identity through the synced local state, invokes the normal attachment reconciliation/upload path, and asserts the original file and idempotency key are uploaded against the resolved event ID.
- This removes the previous hand-seeded IndexedDB attachment fixture as the only evidence for the offline-created job path. Existing lower-level identity reconciliation test remains as a focused regression.
- Commit: 876f0828adba101221f89df10cbbced0890a2a89.
- Verification boundary: test source was re-fetched and inspected; Playwright has not been executed in this environment. The server ID transition is simulated at the local state boundary, not produced by a live Laravel mutation push/pull round-trip. Full sync integration and exact-head CI remain open. No readiness score uplift; V1 remains NO-GO.
## Local Work attachment visibility and retrieval — 2026-11-10

- The local Work directory now loads attachment metadata for each offline-created job and displays each filename with its pending/synced state.
- Operators can open/download a locally stored file directly from the Work row; retrieval uses the existing business-scoped getAttachmentFile() path and does not require a network request.
- Attachment-list refresh listens to zazu:offline-attachments-updated without rebuilding the Work row, preserving the active file-input action and its save feedback.
- The full browser regression now asserts the file appears in the Work row and that clicking it triggers a download with the original filename.
- Commits: 0c20268 directory list/open action; 5a65387 list refresh; d4c574e non-destructive refresh; cd67799 visible-file assertion; 22ea45f download assertion.
- Source files re-fetched and key integration anchors confirmed. No Playwright execution or exact-head CI evidence is available. V1 remains NO-GO; no score uplift.

## CSP style migration — workspace background variables — 2026-11-10

- Moved the business-specific dashboard and wallpaper CSS custom properties off the authenticated document's `body style=` attribute and into a response-nonce-authorized `<style>` element in the document head.
- Preserved the existing media routes, cache-busting version, conditional wallpaper behaviour and CSS variable names; no visual or route behaviour was intentionally changed.
- Added a focused `SecurityHeadersTest` source regression asserting the nonce block and absence of a body inline-style attribute.
- Commits: `3a77c92` (layout), `c31347b` (initial regression), `8e17a61` (precise body-tag assertion), `ccb6239` (move style block into head).
- Verification boundary: changed source re-fetched and inspected. PHPUnit, Blade compilation, browser regression and exact-head CI have **not** been run for this change. Other inline style attributes remain; CSP unsafe-style allowances are intentionally unchanged. No score uplift; V1 remains **NO-GO**.


## CSP inline-style attribute reduction — 2026-11-10

- Removed the six dynamic image `style=` attributes from the public landing page. Their image URLs now flow through response-nonce-authorized CSS custom properties and class selectors in the landing document head.
- Removed the dynamic inline `style=` attribute from the admin landing-image preview. Its per-field preview rule now uses a response-nonce-authorized style element and a stable field-derived element ID.
- Replaced the workspace-recovery sign-out form's inline `display:inline` declaration with the `zazu-auth-inline-form` class in the application stylesheet.
- Added SecurityHeadersTest source assertions that these active Blade views contain no inline style attributes and that the landing/admin dynamic CSS uses nonce-authorized style blocks.
- Commits: `4c80e28` landing page; `40711e5` admin preview; `b51f15a` recovery form; `e6735d0` stylesheet; `f8a236a` regression assertions.
- Guardian review: source-level inspection confirms the three changed views have zero `style=` attributes. The legacy `public/offline-attachments.js` contains inline attributes but is not imported by the active authenticated layout; it remains a dormant legacy asset and was not reactivated or modified. `resources/js/app.js` also uses CSSOM `style.setProperty/removeProperty` for the floating helper's measured nav-avoidance position, so the policy's `style-src-attr 'unsafe-inline'` allowance is deliberately retained until that runtime behaviour is redesigned and tested.
- Verification boundary: changed files re-fetched; no PHPUnit, Blade compilation, browser run or exact-head CI execution is available here. No CSP relaxation was made, no readiness score uplift is claimed, and V1 remains **NO-GO**.


## Signed offline licence verification foundation — 2026-11-10

- Added local Ed25519 detached-signature verification and import to `OfflineLicenseService`; the exact signed payload bytes are verified before JSON parsing and entitlement persistence.
- Added fail-closed checks for missing/invalid public-key configuration, malformed/tampered tokens, required payload fields, unsupported version/status, invalid UUID/plan, wrong business binding, malformed dates and expiry-before-start.
- Supports signed `active` and `revoked` states. A revoked entitlement imports inactive; all validity checks remain local and require no network request.
- Configures only `ZAZU_LICENSE_PUBLIC_KEY` (base64 public key). No signing private key is included or permitted in the app repository.
- Added feature-test source for valid import, tamper rejection without overwriting an existing entitlement, cross-business rejection and signed revocation.
- Commits: `79f41fc` verifier; `ca9aefa` config; `90f971e` environment example; `cd9edfd` tests; `a99308f` required-field hardening.
- Verification boundary: files re-fetched after writes; PHPUnit, sodium runtime compatibility, Blade/browser flows and exact-head CI have not been run. No owner activation UI, issuer, installation identity, renewal workflow or enforcement middleware exists yet. This is a service-level security foundation, not completed commercial licensing. No readiness score uplift; V1 remains NO-GO.


## Signed licence import hardening — 2026-11-10

- Removed the unsigned `issueLocalEntitlement` service method. Entitlement creation through the service now requires a valid Ed25519 signature; no application call sites depended on the removed method.
- Added a 16 KiB token size bound and signed `issued_at` requirement. The verifier stores the issue timestamp and rejects a different signed licence whose issue time is not newer than the currently installed signed entitlement, preventing a previously issued renewal token from rolling the local entitlement backwards.
- Added regression coverage for missing public-key configuration and stale renewal replay. Test fixtures generate ephemeral deterministic keys in the test process only; no private key is committed or configured for production.
- Follow-up commits: `f01371a` remove unsigned issuer; `742cb16` migrate tests; `6fa0299` import/config test hardening; `7b7d473` PHP namespace correction; `6dd4d58` test import correction; `39194b5` size and rollback guard; `27f2f6f` rollback guard namespace correction; `74a5c7f` rollback regression.
- Verification boundary: source re-fetch and structural inspection only. PHPUnit / PHP syntax checks and exact-head CI remain unrun. Activation UI, production issuer/key custody, installation identity, owner reactivation, renewal UX and enforcement remain open. No readiness score uplift; V1 remains NO-GO.


## Signed offline licence verifier — current-head verification — 2026-11-10

- Refactored Ed25519 token verification into `SignedOfflineLicenseVerifier`; `OfflineLicenseService` now coordinates verification, anti-rollback and persistence. The service no longer exposes unsigned local entitlement issuance.
- Current regression coverage: valid import, expiry, tamper rejection without replacing an existing entitlement, cross-business rejection, signed revocation, missing public key fail-closed, and rejection of an older renewal token.
- PHPMD initially rejected the monolithic verifier for cyclomatic/NPath/method-length complexity. The verifier was split into bounded methods and a dedicated verifier class; the PHPMD workflow now passes.
- Full Laravel suite on commit `b9fc97d58e923200caf7e515926453b390aab0f3`: **422 passed, 2,985 assertions**. This run also caught two stale source-contract assertions in `PwaOfflineFoundationTest`; those were corrected to match the actual `dataset` API and local-event-ID method signature before this passing run.
- Current-head checks: PHPMD **PASS**; Psalm security scan **PASS**; Zazu quality workflow **PASS**; populated upgrade/rollback workflow **PASS**; Laravel **PASS**. Browser smoke is still running and is not yet accepted as passed.
- Relevant commits: `79f41fc` initial verifier; `f01371a` remove unsigned issuer; `39194b5` input bound/anti-rollback; `5544e07` verifier refactor; `632e148` dedicated verifier; `9e82b7c` DI; `5a54a21` attachment DOM regression correction; `b9fc97d` local attachment method-signature regression correction.
- Remaining licensing blockers: owner activation/status UI, production issuer and private-key custody, installation identity/reactivation, renewal UX, and enforcement at the correct business boundary. No readiness score uplift yet; V1 remains **NO-GO** pending browser smoke and remaining release gates.

## Offline Work attachment sync regression strengthened — 2026-11-10

- Reworked the real operator journey in `e2e/offline-attachment-sync.spec.js` so it no longer assigns a server ID directly into IndexedDB-backed job state.
- The test now intercepts the normal `/api/sync/push` protocol, returns server mutation acknowledgements with customer/job identity records in the same shape as `SyncController::push`, and then lets the real client `sync()` sequence perform push → pull → bootstrap refresh → attachment upload.
- It asserts the attachment remains pending and is not uploaded while the local Work has no server identity; after the server acknowledges the job mutation, the client maps the returned identity to the local Work and uploads the original file with its original idempotency key against the returned server event ID.
- This closes a gap in the previous regression, which simulated the server identity transition by directly mutating client state and calling `syncAttachments()`.
- Commits: `918fb93` initial protocol-level regression; `fc715ba` removes the previous direct identity simulation.
- Verification boundary: updated E2E source was re-fetched and inspected. Playwright has not been executed in this environment; exact-head CI is not verified. No score uplift. V1 remains **NO-GO** pending CI and real installed-device acceptance.

## Attachment sync acknowledgement integrity — 2026-11-10

- Hardened `public/zazu-offline.js::syncAttachments()`: an HTTP-success response is no longer sufficient to mark a local attachment synced.
- The client now requires a positive safe-integer attachment ID, the exact expected server event ID, and the exact original idempotency key. Any mismatch leaves the original Blob and record pending, increments retry attempts, and records a clear error.
- Extended `e2e/offline-attachment-sync.spec.js` with a simulated successful HTTP response that deliberately returns the wrong event ID; the regression asserts that the local attachment remains pending instead of being marked synced.
- Commits: `9af8dc8` client guard; `0d82a12` response-mismatch regression; `fee725d` fixture key correction.
- Verification boundary: changed client and E2E files were re-fetched and inspected. Playwright/PHPUnit/build and exact-head CI have not been run here. No score uplift; V1 remains **NO-GO**.

## Remote attachment parent-identity validation — 2026-11-10

- Extended the attachment trust boundary in `loadAttachments()`: metadata returned for a job is ignored unless it contains a positive safe-integer attachment ID and its event ID exactly matches the requested Work record.
- This prevents a malformed or incorrectly scoped attachment-list response from causing the client to download and cache another job's file.
- Extended the browser regression with a remote attachment-list item deliberately assigned to a different event; the test asserts that no file download is attempted and no foreign attachment is added to the local list.
- Commits: `af99f69` client validation; `dbb2e3d` browser regression; `12600f4` Laravel source-contract assertions.
- Verification boundary: source, E2E and Laravel test changes re-fetched and inspected. Combined commit status for current main head `12600f4c0140f255d647f1f91760bcc97346d898` returned no status contexts; the commit-workflow-runs connector exposed no PR-triggered runs. Exact-head Laravel, Playwright, build and quality execution therefore remain unverified. No readiness score uplift. V1 remains **NO-GO**.

## Owner-side signed offline licence activation — 2026-11-10

- Added `OfflineLicenseController` to import a bounded signed token for the currently selected business. The endpoint is nested under authenticated session + business context + owner middleware, with a 10-per-minute throttle.
- Import delegates to the existing Ed25519 verifier/service; invalid tokens return a field error, and no raw token is flashed back into the session or written to audit metadata.
- Successful imports and renewals are wrapped with a business-scoped audit record containing only licence ID, plan, status and expiry.
- Business Settings now displays current plan/status/start/expiry and accepts a signed token for activation or offline renewal.
- Added feature coverage for owner activation, persisted entitlement + audit record, and rejection for non-owner users.
- Updated `docs/ZAZU_OFFLINE_LICENSING_SPEC.md` to distinguish the new owner import interface from the still-missing production issuer and enforcement boundary.
- Commits: `b7897d7` controller; `c77dc2c` namespace correction; `6715fada` route; `fe860b7` settings data; `a7b4f12` activation UI; `43115b8` route tests; `200fda4` test namespace correction; `5a13985` specification.
- Verification boundary: changed source files have been re-fetched and inspected. PHPUnit, Blade rendering, PHPMD, Psalm, browser flows and exact-head CI have not yet been executed. Production signing key custody/issuer, installation identity, renewal issuance and application enforcement remain open. No score uplift; V1 remains **NO-GO**.

## Licence activation UI state accuracy and secret-handling regression — 2026-11-10

- Corrected owner settings status rendering so an active-but-expired entitlement is shown as **Expired**, a future-start entitlement as **Not yet active**, and a signed revocation as **Revoked**. Previously, an expired record with database status `active` could be displayed misleadingly as Active.
- Added a feature regression for the expired-state label and a route regression proving rejected raw licence tokens are not flashed into session old input and do not create an entitlement.
- Commits: `25e3183` status rendering; `6e4261a` expired-state regression; `b3d6b59` token session-safety regression.
- Verification boundary: current `main` source was re-fetched and checked for route ownership, throttle, view state handling, token non-repopulation, owner/non-owner tests and audit assertions. Combined commit status and PR-run lookup exposed no checks for this push; PHPUnit/Blade/browser execution remains unverified. No readiness score uplift. V1 remains **NO-GO**.

## Licence status domain logic centralised — 2026-11-10

- Moved entitlement status presentation out of the Blade ternary into `BusinessLicense::statusLabel()`, keeping the rules alongside `isActive()`.
- The status helper distinguishes Active, Revoked, Not yet active and Expired using the supplied comparison time or the local clock. Settings now consumes this single domain method.
- Expanded feature assertions across all four states and retained the owner settings expired-label regression.
- Commits: `a4a4caf` initial helper; `b52bec6` type correction; `36f7c97` settings integration; `36a529a` state regression coverage.
- Verification boundary: model, view and feature test source were re-fetched and inspected. Current main head is `36a529ae77e1f1b15c275accded39711b30b02bb`; GitHub combined status returned no status contexts. PHP/Blade/CI execution is still unverified. No readiness score uplift; V1 remains **NO-GO**.

## Signed licence timestamp canonicalisation — 2026-11-10

- Hardened `SignedOfflineLicenseVerifier` to require full ISO-style timestamps with seconds and an explicit timezone for `starts_at`, `issued_at` and `expires_at`.
- Parsed timestamps are round-tripped against their canonical representation so impossible calendar dates that a date parser might otherwise normalize are rejected.
- Added feature regressions for timezone-less timestamps and normalized invalid calendar dates.
- Commits: `30eeb13` parser; `32d485a` exception namespace correction; `69f9756` test cases; `c07b417` test namespace correction.
- Verification boundary: parser and test source were re-fetched and inspected. PHP/PHPUnit, PHPMD, Psalm and exact-head CI remain unexecuted/unverified. No readiness score uplift; V1 remains **NO-GO**.

## Same-ID signed licence replay and concurrency hardening — 2026-11-10

- Found a rollback bypass in the earlier anti-replay rule: it rejected older tokens only when the incoming license_id differed. An issuer reusing one licence ID could therefore allow an older active token to replace a newer signed revocation or renewal.
- The verifier now returns a SHA-256 fingerprint of the exact payload bytes only after detached-signature verification succeeds.
- The service persists that fingerprint in signed entitlement metadata. A distinct payload must have a strictly newer issue timestamp regardless of licence ID; a byte-identical signed payload may be re-imported idempotently. Different payloads cannot reuse the same issue timestamp.
- Signed entitlement imports are serialized by locking the owning business row inside a database transaction, preventing concurrent import races. Signed records with incomplete issue metadata now fail closed rather than allowing replacement.
- Added regressions for same-ID older active-token replay after revocation, exact-token idempotency, same-timestamp payload substitution, and incomplete installed metadata.
- Updated docs/ZAZU_OFFLINE_LICENSING_SPEC.md with the stronger replay and concurrency rules.
- Commits: 620a644 verifier fingerprint; cca0e4b initial service guard; 7811316 replay tests; f590bac namespace correction; b449eb2 test namespace correction; eba7ec8 transaction/metadata guard; 8ed4323 transaction import and namespace correction; 32e7260 spec; 7636e4c incomplete-metadata regression.
- Verification boundary: source and tests were re-fetched and structurally inspected. PHPUnit, PHPMD, Psalm and exact-head CI have not been executed in this session. No score uplift; V1 remains **NO-GO**.


## Offline bootstrap / local-mutation race — 2026-11-10

- Confirmed a data-integrity race: an online bootstrap request could begin while the local queue was clean, then a user could save an offline mutation before the response returned. The old response path applied the remote snapshot and overwrote the newer local workspace state.
- Added an IndexedDB read/write reconciliation boundary across the workspace state and mutation queue. A bootstrap is applied only if the persisted device token still matches and the pending mutation queue is still empty inside that same transaction.
- Both incremental pull bootstrap and explicit bootstrap refresh use the guarded path. If local work arrived while the request was in flight, the snapshot is discarded and the pull cursor is not acknowledged; the next sync can safely retry.
- Added a Chromium Playwright regression that holds the bootstrap response, queues a real local mutation, releases the response, and asserts the local workspace and pending mutation survive.
- Implementation commits: `a75f6d6` source guard; `e63f97a` race regression.
- Source/test execution and exact-head CI remain UNVERIFIED until Actions runs on the resulting main SHA. No readiness score uplift; V1 remains **NO-GO**.


## Failed offline mutation / conflict atomicity — 2026-11-10

- Found a persistence gap in the rejection path: the failed mutation was written to IndexedDB in one transaction, while its operator-visible conflict was only added to in-memory state and persisted later. A tab close or subsequent storage failure between those writes could leave a failed queue item with no recoverable conflict UI.
- Changed the acknowledgement transaction to cover both the mutation queue and persisted workspace state. Failed mutation details and the corresponding open conflict are now committed together; the in-memory state is refreshed only after the transaction completes.
- Added a Chromium Playwright regression that queues a real customer mutation, returns the actual API contract shape for a rejected mutation (pending status plus error and conflict ID), runs sync, then inspects IndexedDB to assert the failed mutation and matching open conflict are both present and the workspace is no longer marked dirty.
- Implementation commits: `5b15209` atomic source persistence; `78917ad` regression; `d0377f8` route interception correction.
- Source and test were re-fetched and structurally checked. Playwright/Laravel execution and exact-head CI remain UNVERIFIED. No readiness uplift; V1 remains **NO-GO**.


## Retryable offline mutation failure handling — 2026-11-10

A confirmed failure-path gap was found in the offline push contract: unexpected non-validation server exceptions were returned with an error while leaving the server mutation pending, but the client treated every error as a terminal failed mutation. Because no conflict record existed for these exceptions, the client could strand the mutation outside the pending queue with no conflict-resolution path.

- Server now distinguishes deterministic validation conflicts from unexpected/retryable failures in each recorded mutation response.
- Client moves an error-bearing mutation to `failed` only when the server supplies an explicit conflict ID. Unexpected failures remain `pending`, retain the last error, and can retry on a later sync without creating a false domain conflict.
- Added a feature regression that forces a temporary persistence exception and asserts pending status, retryable disposition, retained error and absence of an orphan conflict.
- Commits: `3d22994` controller response contract; `8df076c` client queue handling; `3eba5cf` regression test.
- Changed files re-fetched and structurally inspected. PHPUnit, browser execution and exact-head CI remain **UNVERIFIED**. No readiness score uplift. V1 remains **NO-GO**.


## Idempotent offline conflict recording — 2026-11-10

- Found a duplicate-delivery race: repeated changed-payload replays could each observe no open conflict and create duplicate operator-visible conflict rows. Concurrent domain-rejection retries had the same check-then-create shape.
- Added `SyncConflictRecorder::recordOnce`, which serializes conflict creation on the owning device row, validates active device/business ownership, and reuses/refreshes the existing open conflict for the same device, mutation ID and conflict type.
- Both domain-rejection and mutation-identity-mismatch push paths now use the idempotent recorder.
- Added a feature regression that replays the same changed-payload mutation twice and asserts the same conflict ID and exactly one open conflict.
- Commits: `20b7724` recorder; `827188e` controller integration; `f886a98` regression.
- Source re-fetched and structurally inspected. PHP/PHPUnit, concurrent DB runtime behaviour, Playwright and exact-head CI remain **UNVERIFIED**. No readiness score uplift; V1 remains **NO-GO**.


## Expired/revoked sync credential recovery — 2026-11-10

- Found a silent recovery failure: push, pull, bootstrap and attachment endpoints could return HTTP 401/403, but the phone client treated those responses as ordinary no-op sync results. Local queued work remained durable, but the operator received no persisted indication that the device needed owner-authorised re-pairing.
- Added a shared client handler for 401/403 responses. It records an actionable re-pair message in the business-scoped local workspace, persists it, and dispatches the existing offline-state update event. It does not clear credentials, discard queue entries, or mutate local business records.
- Applied the handler to mutation push, business pull, bootstrap refresh and attachment upload rejection paths.
- Added a Chromium regression that returns 401 from push and asserts the recovery message is persisted while the original pending mutation and token remain intact.
- Commits: `9e50522` client recovery handling; `e546e21` regression.
- Verification boundary: current `main` source and E2E regression were re-fetched; structural checks confirm all four response paths use the handler and the test asserts queue preservation. Playwright, PHP, build and exact-head CI execution remain **UNVERIFIED**. No readiness score uplift; V1 remains **NO-GO**.


## Device re-pair recovery and operator visibility — 2026-11-10

- Added a persistent device re-pair warning to Settings. It is driven by an explicit local recovery flag and updates through the existing offline-state event; it tells operators not to clear browser storage because the phone may hold the only copy of queued work.
- Found and corrected a more serious re-pair defect: the previous `pair()` path replaced the complete local workspace with a blank state before applying the new bootstrap. The revised transition checks the returned device business ID against the business-scoped client, revokes a newly issued token if the scope is wrong, and preserves the current local workspace whenever mutation records or dirty local state exist.
- When local work exists, the new token is persisted without applying a destructive bootstrap. Normal sync then attempts to deliver the preserved queue and only later reconciles the remote snapshot. A successful clean re-pair clears the recovery flag. Pending attachment records remain in their separate business-scoped store.
- Added/extended Chromium regression coverage for the visible recovery warning, queue/token preservation on 401, re-pairing while the server push path is temporarily unavailable, and a cross-business pairing response being revoked without altering the original workspace.
- Commits: `f1cc5aa` recovery flag; `13c4ab3` Settings warning; `abebc63` regression update; `b329649` safe re-pair transition; `41af9a6` re-pair regression; `4355d22` fixture cleanup; `3c9b08b` visible-warning assertion.
- Verification boundary: source and test files re-fetched and structurally reviewed. Browser/PHP tests, production build, business-switch acceptance and exact-head CI remain **UNVERIFIED**. No readiness score uplift; V1 remains **NO-GO**.

- Cross-business token cleanup is fail-visible: the client confirms the revocation response before claiming success. If automatic revocation fails, it explicitly instructs the owner to revoke the newly paired device in Trusted phone access; local data remains untouched.


## Backup failure-path hardening — 2026-11-10

- Director inspected the current installation backup/restore implementation and found that `ZazuBackupCommand::backupPrivateStorage()` ignored the boolean result of `File::copyDirectory()`.
- A failed or partial private-storage copy could therefore proceed to checksum generation and publish an archive that omitted private attachments while reporting success.
- Backup now fails closed with an explicit operator error when the private-storage copy fails; no archive is published.
- Added `test_backup_fails_without_publishing_an_archive_when_private_storage_copy_fails`, which simulates the copy failure and asserts command failure plus zero published archives.
- Commits: `3e5b9c7` implementation; `cf332c2` regression.
- Source re-fetched and structural assertions confirmed. PHPUnit and exact-head CI are **UNVERIFIED**; GitHub combined status and workflow-run lookup returned no records for `cf332c246b001fcb5547e28b5b5d904088471368`.
- No recovery/readiness score uplift. The representative installation restore drill remains open.


## True disconnected customer acceptance — 2026-11-10

- Added a Chromium-only Playwright acceptance path that logs in while connected, warms and confirms service-worker control of the customer-create route, then disables the browser network.
- While genuinely offline, the test reloads the cached customer form, creates a customer through the normal UI, verifies the mutation is queued, reloads again while still disconnected, and verifies both the queued mutation and local customer remain.
- This complements the existing origin-unreachable/browser-online fallback test and the real offline dashboard-navigation test; it exercises a true browser-offline mutation rather than only request interception.
- Commits: `c88ace2` initial acceptance test; `236d4f6` payload/selector assertion alignment.
- Source and test structure re-fetched. Playwright execution and installed-device acceptance remain **UNVERIFIED**. This is added acceptance coverage, not a passing acceptance result; no readiness score uplift.
- Next: inspect whether the active offline customer/work journey is adequately covered by this true-disconnect test and then run exact-head browser CI when available.


## Offline browser acceptance CI — 2026-11-10

Added a dedicated GitHub Actions job to the existing Laravel workflow. It builds the frontend, creates a clean SQLite database, seeds the local-only demo scenario, installs Chromium, and runs `e2e/pwa-offline.spec.js` once in the Chromium project. This makes the bounded PWA/offline browser acceptance suite an automated push/PR gate rather than source-only evidence.

Workflow change: `.github/workflows/laravel.yml`, commit `3944745`. The new job has not yet executed on this commit; browser runtime and exact-head CI are **UNVERIFIED** until GitHub reports a completed run. No readiness score uplift; V1 remains NO-GO.


## Sync stream boundary hardening — 2026-11-10

Source review found that authenticated sync pull/acknowledgement accepted arbitrary non-empty stream names. The recorder currently emits only the `business` stream, but pull could create one cursor per arbitrary requested name and acknowledgement could also persist arbitrary cursor rows. This exposed an avoidable authenticated database-growth path and an undefined protocol surface.

- `SyncMutationProtocol` now accepts only its declared supported stream list (currently `business`) before any cursor lookup/creation.
- `OfflineSyncFoundationTest` adds regression coverage for both pull and acknowledgement rejecting unsupported stream names and verifies no cursor rows are created.
- Commits: `4f4f9f6` protocol guard; `a281567` regression test.

**Verification:** source re-fetched and structurally checked. PHPUnit and GitHub Actions execution remain **UNVERIFIED**. No readiness uplift; V1 remains NO-GO.


## Sync delivery and recovery hardening — 2026-11-10

- Repository: `Leano-Jordan/ZazuEMP`, canonical branch `main`.
- Source/test change series: `9334fc3` (producer/publisher stream enforcement, locked active-device recheck), `2670fee` (acknowledgement pending-state guard), `d138701` (three focused regressions).
- Mutation recording and publishing now reject any stream outside `SyncMutationProtocol::SUPPORTED_STREAMS`; unsupported streams cannot allocate mutation or sequence-counter rows and cannot be published.
- Delivery publication re-checks business ownership and active/revoked state in the same locked query used before assigning a destination sequence. This closes the stale-active-snapshot race when a device is revoked during publication.
- Acknowledgement now selects only pending deliveries in its requested contiguous range. The cursor and delivery-state update remain within one transaction; an invalid range rolls back without creating or advancing a cursor.
- Added focused regression source for unsupported producer streams, revocation between destination selection and locked re-check, and acknowledgement across a non-pending delivery.
- Re-fetched source and test files after the writes; the expected guards and regression methods are present.
- The Laravel workflow is push-triggered on `main`, includes `workflow_dispatch`, and has a dedicated Chromium job for `e2e/pwa-offline.spec.js`. The current-head run has not yet been observed; source presence is not runtime proof.
- The available commit-workflow lookup only returns pull-request runs, and no workflow-dispatch action is exposed by the connected GitHub integration. Use the push-triggered Actions run and exact commit status as the acceptance evidence.
- Runtime PHPUnit/Playwright execution remains **UNVERIFIED** until the new exact-head GitHub Actions run completes. No readiness uplift. V1 remains **NO-GO**.



## Sync delivery and offline acceptance — verified 2026-11-10

- Verified source head: `31fd732952a36ae99638ec8975706e95aa180c99` on `main`.
- The sync delivery hardening introduced earlier in this cycle is now covered by the passing full Laravel feature suite: unsupported producer streams are rejected before mutation/sequence allocation; revoked destinations are rechecked under lock before delivery publication; acknowledgements cannot advance across non-pending deliveries.
- Exact-head Laravel Actions run `42824939605`: Laravel suite passed **438 tests / 3,046 assertions**. Dedicated `offline-browser-acceptance` job `129859701759` passed **18 Chromium tests in 56.5 seconds**.
- The run also passed PHPMD (`42824939822`), Zazu quality including secret scan (`42824939746`), Psalm security analysis (`42824939755`), and populated upgrade/rollback (`42824939920`).
- E2E harness corrections only: the offline job now uses `CACHE_STORE=array` to isolate rate-limit state between test requests (matching the existing browser-smoke workflow; production login throttling is unchanged); the re-pair assertion matches the existing recovery message; the conflict-persistence fixture reads its business ID inside browser context and passes that value into the Node-side route handler.
- Reconciliation: source/test changes re-fetched from current `main`; the three sync regressions are included in the passing Laravel suite; the dedicated true-offline and recovery browser suite passed on exact source head. No application feature scope was added.
- Separate general browser-smoke run `42824939664` was still executing when this record was written; it is not substituted for the dedicated offline acceptance result.
- Readiness score unchanged. **V1 remains NO-GO**: the automated offline acceptance gate is now passed, but the real operator backup/restore drill and physical installed-device/business recovery acceptance remain open.



## Sync delivery and stream recovery hardening — 2026-11-10

- `SyncMutationRecorder` rejects unsupported streams before mutation or sequence allocation and before publication, using the protocol's declared stream allow-list.
- Delivery publication re-checks destination device business ownership, active status and revocation state after locking the device row, avoiding delivery creation for a device revoked after the initial destination scan.
- Acknowledgement selects only pending deliveries in the requested contiguous sequence window; cursor and delivery updates remain transactional.
- Regression source covers unsupported recorder streams and revocation between destination scan and row-lock recheck. Existing protocol tests cover contiguous acknowledgement and invalid sequence rejection.
- Source changes are committed on `main`; source inspection confirms the guards and regression source are present.
- Exact-head automated offline acceptance, PHPUnit and browser runtime results are not yet verified. No readiness uplift is claimed.


## Sync pull contiguous-delivery guard — 2026-11-10

- Source review found pull filtered out non-pending deliveries before selecting the batch. If an earlier delivery was failed while a later one remained pending, the client could receive the later mutation even though acknowledgement correctly refused to cross the failed sequence.
- SyncMutationProtocol::pull() now returns only a contiguous pending prefix beginning at the device cursor. A missing, failed or already-transitioned delivery stops the batch; later mutations are not exposed as an unacknowledgeable batch.
- Added a feature regression with delivery 1 failed and delivery 2 pending; expected pull result is empty and the cursor remains unchanged.
- Source commits: pending in current engineering cycle.
- PHPUnit and exact-head CI are not yet executed for this change. No readiness uplift; V1 remains **NO-GO**.


## Receiver-specific acknowledgement sequence — 2026-11-10

- Found a protocol mismatch: the phone acknowledged the business-wide mutation sequence, while the server validates the receiver-specific delivery sequence. A device's own mutations consume business sequence numbers but do not create deliveries back to that same device, so the two values legitimately diverge.
- Pull responses now include a transient `delivery_sequence` attribute on each returned mutation. The phone calculates its acknowledgement from that receiver-specific value.
- Added regression: destination's own mutation consumes business sequence 1; a remote mutation has business sequence 2 but destination delivery sequence 1; pull exposes both and acknowledgement succeeds at 1.
- Source/test changes committed separately; runtime verification remains pending until exact-head CI. No readiness uplift; V1 remains **NO-GO**.


## Lost acknowledgement response retry — 2026-11-10

- Reviewed the phone pull flow: it applies a clean bootstrap snapshot before acknowledging the receiver-specific contiguous delivery sequence.
- If the server commits the acknowledgement but the response is lost, the next acknowledgement retry is idempotent: `acknowledgeThrough()` returns the existing cursor when the requested sequence is already acknowledged.
- Added a feature regression that acknowledges the same delivery twice and verifies the cursor remains at sequence 1 and the delivery remains applied exactly once.
- This adds regression coverage; it does not change sync protocol behaviour.
- Source inspection only until exact-head Laravel CI completes. No readiness uplift; V1 remains **NO-GO**.

## Attachment upload/logout race hardening — 2026-11-10

- Identified that an in-flight attachment upload could receive a successful server response after logout had cleared local IndexedDB, then recreate a local attachment record.
- Added a session-generation guard captured per upload. Logout increments the generation before cleanup; stale upload responses cannot update or recreate local attachment records. The guard is also applied to retry/error persistence.
- Added a Chromium regression that deliberately delays the upload response until after logout and verifies the attachment store remains empty.
- Commits: `20dd2d7` application fix; `5fc5548` regression test; `51ece68` readiness record.
- Exact-head CI/runtime execution remains pending. No readiness uplift until the browser and full Laravel workflows pass.

## Private attachment cache session guard — 2026-11-10

- Applied the session-generation/token check to attachment metadata listing and private-file download caching as well as uploads.
- A list response, file response or Blob read that completes after logout/token replacement cannot repopulate the cleared local attachment store.
- Upload/logout has a dedicated delayed-response Chromium regression. Download/logout has source-level protection; its dedicated runtime regression is still outstanding.
- Exact-head CI has not yet been observed. Do not claim runtime verification or readiness uplift.

## Re-pair token-boundary hardening — 2026-11-10
- Source audit found that pairing could replace a device token while a prior sync pass was still active. The old request was not aborted at the credential boundary; its later phases could continue under the newly installed token. A sync request made during the old pass could also be silently dropped by the existing single-flight guard.
- Pairing now increments the session generation and aborts the active sync controller before installing the new token. Attachment operations captured under the previous generation are invalidated.
- The sync single-flight guard now records a pending rerun. If re-pair requests synchronization while the old pass unwinds, the new-token pass is automatically started after the old controller releases.
- Pairing now snapshots the local workspace after reading the mutation queue, avoiding a stale pre-await state snapshot.
- Added a Chromium regression that delays an old-token push, re-pairs, then verifies queued work is retried using the new token and is drained.
- Commits: `2b3828c` source; `7fbf4ed` regression; `d3e44ec` fixture abort handling.
- JavaScript and Playwright spec syntax parsing pass after re-fetching current `main`. Browser runtime, Laravel, quality and exact-head CI are **UNVERIFIED**. No readiness uplift; V1 remains **NO-GO**.

## Private attachment download/logout regression — 2026-11-10
- Added the missing Chromium runtime regression fixture for a private attachment download held in flight across local logout.
- The scenario verifies the local attachment database remains empty after logout completes and the held private-file response is released.
- Commit: `9c8dd61`.
- Test source syntax passes after re-fetch. Browser execution and exact-head CI remain **UNVERIFIED**; no readiness uplift; V1 remains **NO-GO**.

## Cross-tab device session replacement — 2026-11-10
- The session BroadcastChannel was already used for logout, but successful pairing did not notify other open tabs. A second tab could retain the previous token in memory and continue issuing sync requests after the shared IndexedDB state had been replaced.
- Pairing now broadcasts a credential-free `session-replaced` event only after the new session is durably written. Other tabs invalidate their session generation, abort old sync requests, reload the authoritative business-scoped state from IndexedDB and resume sync with the current token.
- Logout broadcasts now also invalidate the receiving tab's generation and abort its active sync before clearing local state.
- Added a Chromium multi-tab regression: tab B loads the old token, tab A pairs a new device, and tab B must adopt the new token and make its next sync request with that token.
- Commits: `a0ef99a` cross-tab source; `e5e9f5b` test; `192edbe` shared-auth fixture correction.
- JavaScript and Playwright spec syntax checks are pending final re-fetch. Browser runtime, Laravel and exact-head CI remain **UNVERIFIED**. No readiness uplift; V1 remains **NO-GO**.

## Cross-tab stale-write fencing — 2026-11-10
- Adversarial review found that broadcasting session replacement and reloading IndexedDB was not sufficient by itself: a stale tab could have a late state/queue transaction race with the replacement write and overwrite the new credential or enqueue work under the old session.
- Added persisted-token compare-before-write checks to general state persistence, offline mutation enqueue, sync push acknowledgement application and conflict resolution. A transaction aborts when its in-memory credential no longer matches the durable business workspace token.
- Pairing is the only normal state-write path explicitly allowed to replace the persisted token. The replacement is persisted before the cross-tab notification is sent.
- Extended the cross-tab Chromium regression to force a stale in-memory token and verify both state persistence and mutation enqueue are rejected while the durable replacement token remains intact.
- Commits: `74871c8` write fencing; `aea04e1` conflict-resolution fencing; `d6a8973` stale-write regression; `dea69a2` stale-queue regression.
- JavaScript and Playwright spec syntax checks pass after current-main re-fetch. Browser runtime and exact-head CI remain **UNVERIFIED**. No readiness uplift; V1 remains **NO-GO**.


## Owner-directed dashboard and Helper placement refinement — 2026-11-10

- Replaced Work/Sales popover-adjacent Helper displacement with a fixed far-right viewport dock. Desktop and tablet use a 16px safe-area-aware inset; compact mobile uses a 12px safe-area-aware inset. Opening the mobile navigation drawer continues to move the Helper out of its interaction area.
- Create Event now uses the shared Coastal Blue primary token with a dark, high-contrast foreground; hover uses the lighter primary token. Dark mode uses its theme-specific primary/ink mapping.
- Upcoming event cards now inherit the neutral surface token instead of the blue semantic card tint. Date and status elements remain the semantic visual cues.
- Updated Helper, navigation and dashboard regression assertions. The previous REG-019 popover-adjacent rule is superseded by the owner-directed far-right placement.
- Source files and tests were re-fetched after writes. Rendered desktop/tablet/mobile and light/dark visual acceptance, PHPUnit execution and exact-head CI remain **UNVERIFIED** from this session. No V1 readiness uplift; release remains **NO-GO**.
- Next active engineering target remains server-side sync-device lifecycle and revocation consistency; the UI refinement does not replace the V1 convergence mission.


## Server-side sync-device lifecycle and revocation hardening — 2026-11-10

- The sync middleware's initial token lookup could race with owner revocation. A stale device model could refresh its idle expiry after the database row had already been revoked, then continue through the middleware.
- Sync token activity refresh is now a conditional database write fenced by device ID, active status, null revocation timestamp, the exact SHA-256 token hash and a still-future token expiry. Middleware re-checks the persisted credential guard before dispatch and returns 401 if that boundary changed. The model is left clean after the write so later controller updates cannot accidentally re-save a stale expiry.
- Added an adversarial feature regression that revokes the row on Eloquent retrieval, between middleware lookup and activity refresh; the request must be rejected and the expiry remain null.
- Owner-initiated revocation now locks and rechecks the device/business row inside a transaction, clears token and pairing credentials, and records the audit transition only once. The owner regression repeats revocation and asserts one audit record.
- Device-initiated revocation now locks the device row in a transaction, is idempotent and clears sync-token plus pairing-code metadata while preserving unrelated metadata.
- Provisioning filters out expired/missing pairing credentials before taking row locks. Added an expired-pairing regression; expired pending rows no longer enter the provisioning lock set.
- Commits: 378b342 activity fence; ecad682 middleware rejection; bb7dd76 race regression; 072d0a1 owner revoke transaction; 9afeb98 one-audit regression; fe4ad7a self-revoke transaction; 5fdb467 pairing lock filtering; 16ab465/bcc3e17 revocation and expiry regressions; 504ee44 clean model state.
- Current source and test files were re-fetched and inspected. PHPUnit, PHP lint against a complete checkout, rendered browser/device acceptance and exact-head CI are UNVERIFIED. Combined commit status exposes no status contexts. No readiness uplift; V1 remains NO-GO.
- Continue with exact-main verification and the remaining server-side device lifecycle cases; do not treat source-only hardening as release acceptance.


## Revocation fencing at offline mutation write boundaries — 2026-11-10

- Adversarial follow-through on the middleware revocation race found a second boundary: a request can pass middleware, then lose its device authorization before the mutation recorder or applier reaches its transaction. Middleware-only checks do not protect these later writes.
- `SyncMutationRecorder` now follows the established business → device lock order, locks the durable device row, rechecks active/revoked state and compares the persisted token hash with the middleware snapshot before allocating a sequence or inserting a mutation. A stale/replaced credential receives HTTP 401.
- `SyncMutationApplier` now uses the same business → device → mutation lock order and rechecks the durable device row before invoking the domain handler. If revocation wins the device lock, the queued mutation remains pending and the domain handler is not invoked; if application wins, its transaction completes before revocation can commit.
- The push controller now lets this 401 escape its generic per-mutation error handler rather than converting a revoked-device request into a successful partial batch response.
- Added `OfflineOperationalDepthTest` regressions for stale authenticated snapshots at record time and revocation between record/apply. Existing tokenless internal fixtures remain supported only when both their snapshot and durable device have no token hash; real middleware-authenticated devices carry and compare their hash.
- Commits: `5cb9526` recorder transaction fence; `66094f9` applier transaction fence; `c6d6060` preserve 401; `8d7c4c1` fixture compatibility; `6e80ff0` record/apply regressions.
- Re-fetched source and test assertions are present on `main`. GitHub combined status has no status contexts; the workflow-run wrapper returned no runs for the commit. PHPUnit, PHP lint, browser/runtime and full CI remain **UNVERIFIED**. No readiness uplift; V1 remains **NO-GO**.


## PHPMD correction and test-boundary cleanup — 2026-11-10

- The first CI run after the mutation-boundary change exposed three PHPMD findings: `SyncController::push()` crossed the 100-line method limit; `OfflineSyncFoundationTest` exceeded the public-test count threshold; and `UiProportionFoundationTest` exceeded its public-method threshold.
- Corrected the controller finding by extracting the 401 rethrow into a private helper. The first extraction commit contained escaped namespace separators that PHPMD's parser rejected; the fully qualified PHP names have now been corrected and re-fetched.
- Moved three mutation-identity tests into `SyncMutationIdentityTest` (reducing `OfflineSyncFoundationTest` from 47 to 44 public tests) and moved the dashboard colour regression into `DashboardVisualRefinementTest` (reducing `UiProportionFoundationTest` from 11 to 10 methods). Assertions and coverage were preserved, not suppressed.
- Commits: `1f55289` helper extraction; `d3f54c4` original test split; `90bf475` mutation identity class; `13874ed` UI test split; `5255b40` dashboard test class; `82f0f85` and `177a368` PHP namespace correction.
- Exact-head PHPMD/Laravel/quality/Playwright/Psalm runs were queued at the time of this update. Do not treat the earlier PHPMD failure as resolved until a successful run is observed. V1 remains **NO-GO**.


## Offline session-fixture regression and exact-head CI — 2026-11-10

- GitHub's Chromium offline acceptance exposed eight failures in `e2e/pwa-offline.spec.js`, all at the new IndexedDB session compare-before-write guard. Root cause was test setup manually injecting a new sync token into in-memory state and then using ordinary `persist()`, which correctly refuses an unapproved session replacement.
- Updated the eight session-seeding/replacement fixture paths to call `persist({ allowSessionChange: true })`, matching the production pairing transition. The conflict retry/discard fixture now persists its session before it queues mutations, so queue writes use the established token. Ordinary persistence assertions and the production guard remain unchanged.
- Latest code/test commit under verification: `c021eb2`. Exact-head GitHub results observed: Laravel unit/feature suite **446 passed, 3,083 assertions**; offline browser acceptance **18 passed**; PHPMD **success**; Zazu quality and secret scan **success**; Psalm **success**; populated upgrade/rollback **success**; Director contract lint **success**. The separate Zazu browser smoke run remained in progress when recorded.
- The previous offline-browser failure is classified as a stale E2E fixture contract, not an application regression. The fixture correction is committed in `e03ee2b` and `c021eb2`.
- No release readiness uplift until the browser smoke run and exact-head post-documentation checks complete. V1 remains **NO-GO**.


## Commercial and legal operating baseline — 2026-11-10

- Owner-confirmed: Isaac Junior Lehlogonolo Maluleka operates Zazu independently as an individual/solo operator. Rosscore Labs remains a future business identity and must not be described as an incorporated contracting party until registration is complete.
- Owner-confirmed contact channels were supplied for future customer-facing documents. Personal contact details and the supplied residential street address are deliberately excluded from this public repository; only owner-approved public business contact fields may be inserted at publication.
- Initial commercial target: R99 per business/workspace/month. Proposed 14-day trial with advanced features during trial; trial/subscription expiry downgrades to Basic. No lockout or data deletion solely because a licence expires. Exact Basic/Advanced matrix, VAT treatment, storage quota and checkout disclosures remain to be confirmed.
- Both hosted and local/offline deployments are intended. Customer content remains customer-owned. Hosted customer data is expected to be processed under a written operator arrangement where POPIA applies; local deployment ordinarily leaves the customer in control of the local environment, with support access limited to authorised requests.
- Hosting shortlist: xneelo Cloud, Johannesburg. Indicative pilot estimate: 2 vCPU / 4 GB RAM (R198/month) + 10 GB premium boot volume (R18/month) + 50 GB premium NVMe data volume (R90/month), approximately R306/month at published prices. This is a self-managed single-region/single-availability-zone pilot, not an HA design. Provider not yet procured; offsite backups, patching, monitoring and recovery remain owner responsibilities.
- Retention policy now distinguishes SARS tax-record guidance (commonly five years, subject to exceptions) from Companies Act s24 company-record retention (generally seven years for covered records). General personal information is not assigned a blanket five/seven-year period. Proposed operational defaults: 30-day export window, deletion/de-identification within 90 days after account closure where lawful, 24-month security/support log baseline, and 30-day rolling backups. Legal/accounting review and technical enforcement remain open.
- Updated/created: docs/ZAZU_COMMERCIAL_OPERATING_PROFILE.md; RELEASE_CUSTOMER_TERMS_DRAFT.md; RELEASE_PRIVACY_NOTICE_DRAFT.md; RELEASE_RETENTION_DISPOSAL_SCHEDULE.md; RELEASE_DOCUMENTATION_INDEX.md.
- No legal or commercial launch clearance is claimed. V1 remains NO-GO until engineering acceptance, production deployment, actual provider procurement, final feature/pricing disclosures, operational privacy controls and legal review are complete.


## Commercial decisions — 2026-11-10 follow-up

- Owner confirmed a 30-day free trial. Trial expiry moves the workspace to Basic Free; no silent paid conversion, lockout or data deletion solely due to expiry.
- Director recommends Advanced at R99/workspace/month and a useful permanent Basic Free tier. Proposed (not yet verified) limits: Basic one owner, five active events, 250 MB; Advanced up to three users and 2 GB hosted storage, plus expanded implemented operational modules.
- Owner approved public service-area wording: Pretoria North, Gauteng, South Africa. This is not a registered-office claim; residential street address remains excluded from public repository.
- Current xneelo Cloud indicative resources total approximately R306/month including VAT (2 vCPU/4 GB, 10 GB boot, 50 GB NVMe). Budget R450–R500/month to allow provisionally for independent backups and small overhead. The extra allowance is not a vendor quote.
- PayFast official card pricing is 3.2% + R2 excl. VAT and payout R8.70 excl. VAT. Estimated card fee on R99 is ~R5.95 incl. VAT. Six R99 subscribers would cover the R450–R500 infrastructure planning budget on a rough cash basis after payment fees and one monthly payout, before tax and other operating costs.
- Commercial implementation gates: verify tier entitlements and downgrade boundaries in code; enforce storage quotas; select payment provider and recurring billing flow; procure independent offsite backups and complete restore drill; complete legal/accounting review.


## Director reconciliation — current-main licensing correction — 2026-11-10

- Corrected the 10 November full audit and V1 release checklist: the earlier licensing finding was stale. Current `main` already contains owner-only signed Ed25519 token import/renewal in Business Settings, status display, audit logging, business binding, anti-rollback and feature coverage.
- Remaining licensing work is production issuer/private-key custody, installation identity/reactivation and application-wide plan/feature enforcement. The import interface is not a complete commercial enforcement system.
- Evidence was re-fetched from current `main`: `OfflineLicenseController`, `OfflineLicenseService`, `SignedOfflineLicenseVerifier`, `resources/views/settings/index.blade.php`, `routes/web.php`, and `tests/Feature/OfflineLicenseTest.php`.
- Historical 422-test evidence belongs to `b9fc97d`; latest docs commit `8d3c1af` has no combined status contexts. No current-head test pass or release score uplift is claimed.
- Active next target: reconcile exact-main CI for the current source head, then close the production licensing issuer/enforcement boundary without weakening offline operation. P0 physical-device, deployment, operator recovery, privacy and brand gates remain external/operational.


## Offline browser fixture reconciliation — 2026-11-10

- Exact-head browser smoke on `f6104a1` failed eight Chromium cases across `offline-attachment-sync.spec.js` and `offline-session-transition.spec.js` with `The offline device session changed before local state could be saved.`
- Retrieved the failed GitHub Actions job log (job `130132374579`) and traced all failures to test setup replacing the seeded device token through ordinary `persist()`. The production compare-before-write guard correctly rejects an unapproved session replacement; the tests were not modelling the explicit pairing/session-replacement boundary.
- Classified **F2 — test/verification fixture contract drift**, not an application defect. Corrected only the fixture setup calls to `persist({ allowSessionChange: true })`; preserved the deliberate stale-token rejection assertion at `offline-attachment-sync.spec.js:875` using ordinary `persist()`.
- Commits: `2948dee` attachment fixture corrections; `4feb0e2` session-transition fixture correction. Both target canonical `main`.
- Re-fetched both changed test files and verified the explicit replacement option is present in seven setup points plus the session-transition setup; the stale-write negative assertion remains unchanged.
- Exact-head CI for `4feb0e2` is queued/in progress. No pass is claimed until the new browser run completes. V1 remains **NO-GO**; no score uplift.


## Licensing boundary reconciliation — 2026-11-10
- Re-read current-main OfflineLicenseService, SignedOfflineLicenseVerifier, OfflineLicenseController, BusinessLicense, config/zazu.php, routes/web.php, Business Settings view, licensing spec and feature tests.
- Correct current status: signed local import/renewal UI and owner-protected audited route are already implemented. The earlier note that activation UI remained open is stale.
- Confirmed verifier covers Ed25519 signature, business binding, bounded token size, explicit-timezone/calendar-valid dates, expiry/revocation, anti-rollback, idempotency and fail-closed missing-public-key handling.
- No application-wide plan/feature enforcement boundary was evidenced in the inspected route group. This is a genuine commercial gap, not a reason to add a blanket licence lockout.
- Basic Free and the 30-day trial → Basic Free fallback are owner-directed; exact feature/usage limits remain proposed, not approved. Do not invent enforcement rules or block access to customer data/core offline workflows.
- Production issuer/private-key custody and installation identity/reactivation remain external engineering/security gates. Never put a production private key in this repo.
- Updated docs/ZAZU_OFFLINE_LICENSING_SPEC.md with the authoritative implementation status, enforcement boundary and safe sequence.
- Exact-head 960b39e9 Laravel, quality, Psalm, PHPMD, populated upgrade/rollback, Director contract lint and push-on-main workflows are PASS. Zazu browser smoke run 42904398545 / job 130138486515 remains IN_PROGRESS at time of record. SonarCloud is SKIPPED, not a pass.
- No source-code change or V1 readiness uplift in this documentation-only reconciliation. V1 remains **NO-GO**.


## Browser smoke regression correction — 2026-11-10
- Exact-head `b12eac50` browser workflow failed: 81 passed, 1 failed, 38 skipped. Failure was isolated to `e2e/offline-attachment-sync.spec.js` test `late attachment upload response cannot recreate local files after logout`.
- Root cause: the test's multipart `idempotency_key` matcher contained doubled escape characters and therefore searched for literal backslash sequences instead of CRLF delimiters in the multipart body. The uploader correctly submits the field via `FormData.append('idempotency_key', ...)`; this failure was in the assertion parser, not evidence of a production upload defect.
- Corrected matcher to use actual CRLF/newline regex escapes while retaining the assertion that the request carries an idempotency key and the late response cannot recreate local files after logout.
- Commit: `9bb30a06357ae61e2f2fbdd0f2a6cc639e20acf8` (`test: correct multipart idempotency matcher`). Awaiting exact-head CI. No readiness uplift until browser suite passes.


## Director reconciliation — 2026-11-11

- Current repository: `Leano-Jordan/ZazuEMP`; canonical branch: `main`.
- Documentation baseline before this state reconciliation: `b749cd5bce9f3e0e12418e4f7105355f0b61d1a7`; this state-file update is a documentation-only descendant.
- Current application source has not changed in this cycle. The last application source/test assessment must remain tied to its previously recorded exact SHA; this cycle does not create a new application verification baseline.
- Active bounded target: harden and validate the disposable Render preview build path. Preview persistence is intentionally ephemeral and must not receive real customer data.
- Implemented: Docker build-context exclusions and workflow path coverage for `.dockerignore`; roadmap and canonical release checklist updated.
- Verification: changed files re-read. Exact-head CI and Docker build for the hardened context are pending.
- Release decision remains **NO-GO**. Do not award a score uplift from documentation or unverified build changes.
- Next control action: reconcile the exact-head CI/Docker build, then resume the highest-risk remaining operational release gate. Physical installed-device offline acceptance and operator recovery/legal/brand gates remain external acceptance work; do not imply they are closed by CI.


## Jarvis execution — 2026-11-11 — Director CI noise reduction

- Finding: Director health reporting was triggered on every main push (except `docs/health/**`) and separately after completion of six CI workflows. One source commit could therefore queue many duplicate health-report runs.
- Changed `.github/workflows/roscore-director-health.yml`: push reporting is now limited to engineering/source/configuration/CI control paths; the completion trigger now listens only to the Zazu browser smoke workflow, which is the final/slow acceptance signal in the required batch.
- This preserves an early push snapshot and a post-browser final evidence snapshot while avoiding six near-identical post-check reports.
- Commit: `224ddadf3cb6a711fe8475e8f46d57d7fae54541`.
- Docker image build with the new `.dockerignore` passed on the prior preview-build run. It builds only; no deployment occurred.
- A fresh exact-head CI batch has been queued for the workflow change. Browser smoke on the previous documentation head was still running at last inspection; do not claim it passed.
- No application source changed and no score uplift is claimed. V1 remains **NO-GO**.



## Jarvis execution — 2026-11-11 — private offline media race coverage

- Continued the offline uploaded-media correction on canonical `main`.
- Added a service-worker regression proving that an image request started under business 17 but completed after switching to business 29 is not written into the new workspace cache; the old business cache must also be absent.
- Commit: `f86b9432f8e7eea8e0ae1999ec17e000a36b4951`.
- Re-fetched the committed test and confirmed the new regression is present. The existing suite also covers successful offline image retrieval, private-cache isolation from the shared static cache, and no authenticated cache without a business context.
- GitHub's available commit-status and PR-workflow endpoints returned no status contexts or workflow runs for this push. Automated execution is therefore **UNVERIFIED**; do not report the new regression as passed until the quality workflow result is observed.
- No release readiness uplift. V1 remains **NO-GO**. The Render deployment branch was not modified, and no deployment was performed.


## Private uploaded-image offline cache correction — 2026-11-11

- Reconciled the active service-worker private-media path against the reported broken uploaded-image behaviour.
- Root cause: private media interception was gated on `Request.destination === 'image'`. That browser metadata is not reliable for every image-loading path, including CSS background-image requests, so an otherwise valid business-scoped cache could be bypassed and the image request would fail offline.
- Changed the private-media route matcher to use the authenticated `/media/profile/*` and `/media/business/*` URL boundary, independent of request destination. Private media remains isolated in the active user + business cache; it is not added to the shared static cache.
- Advanced the service-worker cache generation from `zazu-static-v32` to `zazu-static-v33` so installed clients replace the previous worker generation.
- Updated the service-worker regression to request a private business image with an empty destination, verify it is cached in the business cache, and verify it remains absent from the shared cache. Updated the Laravel source-contract assertion to v33.
- Commits: `ad81391` worker; `87cb501` browser-worker regression; `19ca15d` Laravel cache-generation assertion.
- Source re-fetch and commit integrity: pending final reconciliation. Node service-worker tests, Laravel suite, exact-head CI and installed-phone offline image acceptance have not been run in this environment. Do not claim the user-visible issue is fully verified until the exact-head workflow and physical device sequence pass. V1 remains **NO-GO**.


## Private uploaded-image offline cache completion — 2026-11-11

- Extended the private-media correction beyond request interception: installed-page priming now extracts same-origin `/media/profile/*` and `/media/business/*` references from authenticated HTML and fetches them into the active user/business cache before disconnected use.
- URL extraction covers normal image references and CSS `url(...)` references, deduplicates requests across the priming run, normalizes HTML-escaped query separators, rejects non-local origins and caps prefetch at 60 unique private images per run. Failed image fetches do not block page priming.
- Private-media fetch interception is route-based rather than dependent on `Request.destination`, so CSS backgrounds and other browser image-loading paths use the same private cache. No private media is placed in the shared static cache.
- Advanced service-worker generation to `zazu-static-v34` to retire the previous installed worker/cache generation.
- Added a service-worker regression proving that priming a dashboard caches referenced profile and CSS-background images in the correct business cache, excludes them from the shared cache, and serves both after the host becomes unavailable. Laravel source-contract assertion updated to v34.
- Commits: `ad81391`, `87cb501`, `19ca15d`, `799da2f`, `c9b347e`, `3b0ac8e`, `a041e07`, `a50e5f1`.
- Source re-fetch is complete. Node service-worker tests, Laravel suite, exact-head CI and physical installed-phone acceptance remain **UNVERIFIED** in this environment. The 60-image prefetch cap is a bounded safeguard, not proof that every image on every page is available offline. V1 remains **NO-GO**.


## Jarvis execution — MySQL backup payload correction — 2026-11-11

- Target: installation-level backup reliability, independent of the offline-media work.
- Finding: `ZazuBackupCommand::snapshotMysql()` assigned the return value of Symfony Process `run()` to the SQL payload. `run()` returns the process exit code; stdout is available through `getOutput()`. The command could therefore publish a format-v2 ZIP whose `database.sql` contained only the exit code rather than the database dump.
- Classification: **P0 RELEASE BLOCKER / DATA RECOVERY INTEGRITY** for MySQL installations. SQLite path is unaffected.
- Correction: execute the dump, check process success, read stdout using `getOutput()`, reject empty output, and verify the SQL file was actually written and is non-empty before continuing to private storage/checksum/archive creation.
- Regression: added a POSIX fake-`mysqldump` integration fixture that runs the real backup command under a MySQL config and inspects the generated ZIP, asserting that the exact multiline SQL stdout is stored in `database.sql` rather than the process exit code. Test skips on Windows because the fake executable uses a POSIX shell.
- Commits: `19de1d4` source fix; `f2598d9` regression; `7459d1c` fixture formatting correction.
- GitHub source/test files re-fetched after writes; structural assertions confirm stdout capture, empty-output/write failure guards, and exact archive payload assertion. PHPUnit, PHP syntax, full CI, a real MySQL dump/restore and operator recovery drill are **UNVERIFIED**. Do not count this as a runtime pass or raise recovery maturity. V1 remains **NO-GO**.

- Regression follow-up: the same MySQL backup integration test now also replaces the fake dump utility with a successful zero-output process and asserts the backup command fails without publishing an archive. Commit: `604475e`. Runtime execution remains unverified.


## Helper navigation clearance and restore rollback preservation — 2026-11-11

- Moved the floating Zazu Helper out of the authenticated content/main shell and placed it as a viewport-level sibling after the application shell. This prevents content-column positioning/stacking contexts from making the helper drift over navigation controls; its existing right-edge docking and mobile menu avoidance rules remain intact.
- Added InterfaceRegressionTest::test_helper_is_portaled_outside_the_application_shell to lock the DOM placement.
- Extended the fake-MySQL restore integration test to simulate both candidate-import failure and pre-restore rollback failure. It asserts the original private storage is restored and the only pre-restore SQL snapshot is retained and reported for operator recovery rather than deleted with the temporary workspace.
- Commits: d25f0a6 (helper placement), 627d7e5 (helper regression), a91a6ea (restore rollback preservation regression).
- GitHub source re-fetch and structural assertions: PASS. Exact-head PHPUnit, browser rendering, CSS/device acceptance and CI status: UNVERIFIED (no status checks reported for the latest test commit at reconciliation).
- The owner should only need to visually confirm helper placement on desktop and mobile after the next deployed build. No deployment was triggered; deploy/render-staging remains untouched.


## SQLite in-memory restore isolation — 2026-11-11

- Restore preflight temporarily redirects a SQLite `:memory:` connection to a file so the normal safe replacement path can run. The previous implementation left that temporary database path in Laravel config after the command and could leave its temporary database file behind.
- Captured the original SQLite database setting, restored it in `finally`, purged the temporary connection and removed its temporary database/WAL/SHM files. Rollback snapshots remain separately preserved when recovery fails.
- Added a regression test using a valid legacy archive and an in-memory SQLite configuration; it asserts the connection setting is restored and no temporary target remains.
- Commits: daa0a8a (config restoration), 5221165 (regression), 5fc4ae3 (temporary file cleanup), 6d6a361 (PDO namespace correction).
- GitHub source re-fetch: PASS. PHPUnit/runtime verification and CI: UNVERIFIED; no local execution environment or status contexts available. V1 remains NO-GO.


## Jarvis execution — SQLite WAL backup consistency — 2026-11-11

- Audited the SQLite backup path. It uses SQLite `VACUUM INTO` on the live connection, which is intended to create a consistent standalone snapshot and include committed WAL content.
- Added `test_sqlite_backup_includes_committed_wal_records` to prove the backup contains a committed row while the source database is in WAL mode and the committed WAL frames have not been checkpointed into the main database file. The test then restores the archive and asserts the row survives.
- Commits: `574e913` (test), `6957104` (correct PDO namespace in test).
- Test source re-fetched and reconciled. PHPUnit/runtime and exact-head CI are **UNVERIFIED**; no pass is claimed. No production backup implementation change was necessary because the existing snapshot uses SQLite's online snapshot mechanism rather than copying the live database file.
- Release remains **NO-GO**. No deployment; `deploy/render-staging` untouched.


## Jarvis execution — SQLite restore target preflight — 2026-11-11

- Finding: restore validated the archive and candidate SQLite database, but did not validate that the configured destination was a regular database-file path with an existing writable parent directory before mutation. A directory used as the configured SQLite filename could be mistaken for a usable destination during file activation.
- Change: reject directory/special-file targets and missing/non-writable target parent directories during preflight, before private storage or database replacement begins.
- Regression: added a valid SQLite backup fixture with the configured target deliberately set to a directory; asserts restore fails, the directory remains empty, and existing private data remains unchanged.
- Commits: `af84b59` (preflight), `968f5a6` (regression), `59e1e73` (PDO namespace correction).
- Source re-read: PASS. PHPUnit/runtime and exact-head CI: **UNVERIFIED**. No execution pass is claimed.
- No deployment. `deploy/render-staging` untouched. V1 remains **NO-GO**.


## Jarvis execution — reject symbolic-link SQLite restore targets — 2026-11-11

- Finding: the SQLite restore preflight rejected directories and special files but accepted a symbolic link. Moving/replacing a symlink path can produce ambiguous behaviour around the linked database and its WAL/SHM sidecars.
- Change: explicitly reject a symlink as the configured SQLite database target before any restore mutation.
- Regression: added POSIX-only coverage with a valid candidate archive and a configured target symlink to a protected SQLite database; asserts restore fails, the link remains, the protected record is unchanged, and private business data survives. Windows skips this test because the fixture requires POSIX symlink support.
- Commits: `3d848c0` (guard), `ac45f6f` (regression), `b11e779` (PDO namespace correction).
- Source re-read: PASS. PHPUnit/runtime and exact-head CI: **UNVERIFIED**; no test pass is claimed.
- No deployment. `deploy/render-staging` untouched. V1 remains **NO-GO**.


## Director verification — sync credential cache protection — 2026-11-12

- Application/test commit: `bed3941739cf02fc1a8152932292bc7d7ec68a21` (latest runtime change in the current main history).
- The sync and trusted-device no-store response change and its regression test are verified by exact-head GitHub Actions: Laravel workflow run `43181669792` — all jobs PASS, including Laravel tests, PostgreSQL migration/registration/backup-restore smoke, and offline browser acceptance; browser smoke `43181669581` PASS; Zazu quality/secret scan `43181669747` PASS; populated upgrade/rollback `43181669678` PASS; Psalm `43181669617` PASS; PHPMD `43181669720` PASS. SonarCloud was SKIPPED and is not counted.
- Render workbench: documentation descendant `c450ed90fe32a1abbe621e2d177d81590d20365c` deployed successfully as `dep-dbqns3bsuf0c7389gt6g` at 2026-11-12 08:52:58 UTC. Migration output reached the final migration successfully; internal HEAD / and GET / returned HTTP 200. No current-deployment application error was present in the inspected startup logs.
- Render remains a disposable SQLite preview workbench. This deployment evidence does not establish persistent customer data or production readiness and is not treated as such.
- Next bounded acceptance item: physical installed-device offline workflow on the owner's target phone. CI browser tests cannot certify the installed app's real device storage and reconnect behaviour. Do not invent additional gates while awaiting that acceptance.
- V1 release decision remains pending that agreed core workflow acceptance and the single final regression/release review. No new feature or speculative hardening scope is admitted.


## Render remote workbench reconciliation — 2026-11-12

- Confirmed the intended Render workspace and the ZazuEMP web service in that workspace. Service `srv-dbqpa1tbedkc73ebgeo0` is live at `https://zazuemp.onrender.com`, on the preserved `deploy/render-staging` branch, Free plan, with automatic deploys from commits.
- Confirmed a separate Render PostgreSQL 18 instance `zazuemp-preview-db` (`dpg-dbqupp95efls73fto6u0-a`) exists and is available on the Free plan. Its recorded expiry is 2026-12-12 16:44:21 UTC (18:44:21 South Africa time).
- Latest observed service deploy `dep-dbqvjuutlv9c73d963l0` is LIVE for commit `1d7af048bb8fc02051ce873fc6e9bc578e3c2f06`; startup migrations completed and HTTP health checks returned 200.
- **Database attachment is not yet verified.** The deployment entrypoint accepts `DATABASE_URL` / `DB_URL` and infers PostgreSQL, but defaults to disposable SQLite when neither is configured. Render's service API does not expose current environment-variable values through the available connector, and the hosted Render query connection is blocked because the database IP allowlist is empty. Do not claim that the running service uses this PostgreSQL instance or that existing accounts are stored there until the connection is verified from the service or an authorized database client.
- No database, service, deployment, environment variable or deployment-branch mutation was made during this inspection. Preserve the existing service and database; the owner will handle the expiry decision before the recorded date.


## Render login/database log diagnosis — 2026-11-12

- Inspected live Render application logs for service `srv-dbqpa1tbedkc73ebgeo0`.
- Confirmed repeated critical `DB-001` failures at 17:19–17:32 UTC. PostgreSQL connection attempts were sent to `127.0.0.1:5432`, not the managed Render database host. The logged database name was the literal unresolved reference `{{zazuemp-preview-db.DATABASE_URL}}`. This is direct evidence of a malformed Render environment-variable mapping: a database URL reference was placed in a database-name field (or otherwise not supplied as `DATABASE_URL` / `DB_URL`).
- The current entrypoint correctly maps `DATABASE_URL` to `DB_URL`; Laravel's PostgreSQL connection uses that URL. The required Render setting is a linked variable named exactly `DATABASE_URL`, sourced from the managed database's **Internal Database URL**. Keep `DB_CONNECTION=pgsql` if present. Do not put the URL/reference into `DB_DATABASE`.
- Later deploy at 17:40 UTC started successfully and reported `Nothing to migrate`, but this alone does not prove PostgreSQL is now active; the current runtime's selected database remains unverified. The service API connector does not expose environment-variable values, and the database query tool is blocked by the database's empty IP allowlist.
- No environment variables or database contents were changed. Next operator action: Render Dashboard → ZazuEMP service → Environment; add/link `DATABASE_URL` using the `zazuemp-preview-db` resource's Internal Database URL (Render's linked-variable picker, not a manually typed placeholder). Remove the malformed `DB_DATABASE` placeholder if present; preserve unrelated settings and the stable `APP_KEY`. Save, allow the service to redeploy, then verify startup logs show successful PostgreSQL migrations and no localhost connection attempts before testing registration/login.


## Three-tier licensing lifecycle — 2026-11-13

- Main implementation commits: `b67255b88a73421e3d141d96f92463291fc68a8d`, `50fe172a32371f5df4a33375538be0fb36f6bb46`, `49b65c46a9bd3381b5fa3a08292965d646933b6c`, `7612f7bd37795733d670f904cb3f5de48d5c2626`.
- Business-level `BusinessEntitlement` resolves the maximum tier from a current signed licence or the 30-day creation-date Advanced trial. Any installed expired/revoked/not-yet-active licence suppresses trial restart and falls back to Basic.
- `ExperienceLevel` clamps each member's saved presentation choice to the workspace entitlement. Onboarding and preference updates reject a choice above the current ceiling. The saved preference is not rewritten by expiry.
- `OfflineLicenseService` and `BusinessLicense` now use the same three-tier semantics. Legacy `solo` maps to Advanced for compatibility. Licence expiry is exclusive at the expiry timestamp.
- Experience preference screens show the current workspace tier, trial expiry and available experience levels.
- Regression coverage added for exact 30-day trial expiry, expired-trial preference preservation, Intermediate ceiling, legacy `solo` compatibility and unlicensed Basic Free continuity after the trial.
- No module-level feature gates, event/user/storage quotas, customer-data route middleware, data mutation/deletion, deployment or Render configuration changes were made.
- Commercial matrix: Basic Free; Intermediate paid (price/features unapproved); Advanced paid (R99/workspace/month remains a working target, not a published offer). Proposed 5-event, 3-user, 250 MB and 2 GB restrictions remain unapproved and unenforced.
- CI/runtime verification must be read against the latest commit before claiming acceptance. Render and deployment configuration remain untouched.

## Setup tier display consistency — 2026-11-13

- The Setup Centre previously displayed the member's raw saved experience preference. After trial expiry or licence downgrade, that could show Advanced even though the effective workspace experience had correctly been clamped to Basic.
- Updated the Setup Centre controller to display the effective entitlement-clamped level while using the stored preference only to determine whether the member has completed the choice. The saved preference remains unchanged and can be restored by a later valid upgrade.
- Added a regression test covering expired-trial Setup Centre display and preservation of the saved Advanced preference.
- Commits: `dbddbc9907be76ddf286c2685deea8deb604806d` (controller), `c9cc78850829a6392c2dd4f7323137763f46cdbc` (regression test).
- Updated the commercial operating profile to state explicitly that the earlier 250 MB / 2 GB storage figures and all tier quotas remain proposals, not approved policy. The complete Basic / Intermediate / Advanced matrix must be approved before restrictions are implemented or published (`9f6f627457009eb003f2aa4839232910d32ec164`).
- Latest CI for these commits: pending verification. Previous exact-head baseline CI passed Laravel tests, PostgreSQL migration/registration/backup-restore smoke, offline browser acceptance, browser smoke, quality, Psalm, PHPMD, populated upgrade/rollback and Director Contract Lint. No deployment or Render changes were made.


## 2026-11-13 — Reconnect sync retry defect

Owner clarified the observed failure: offline changes are saved locally, but remain "Waiting to sync" after the PC/server becomes reachable again. The expected behaviour is to consolidate queued changes with the server once the host is reachable.

Source inspection found a concrete retry gap in `public/zazu-offline.js`: reconnect/resume triggered one sync pass, but a failed HTTP push or a retryable server-side pending mutation could leave the queue unchanged. The sync loop then stopped. It depended on another browser online/focus/visibility event to try again; if the host became ready after the single reconnect attempt, no further event was guaranteed.

Implemented on `main`:
- Added bounded exponential retry for durable pending mutations (1s up to 30s) while online and paired.
- Retries stop while offline, after session clearing, or when device authorization requires owner recovery.
- Non-auth HTTP push failures now record a transient sync error; successful queue drain clears that stale error.
- Added Playwright regression coverage: first push receives HTTP 503, subsequent automatic retry succeeds and drains the queue.

Commits:
- `7368710e65eb724a4cdf7978d96c72fd0fee468d` — reconnect retry scheduler.
- `f913d3b8ab11a6abd42ec7651988c03af7aa6a55` — delayed-host reconnect regression.
- `8adbe3ec90f552264815837ac23f28af6301782a` — transient failure visibility and recovery cleanup.

Verification boundary: source was re-fetched from `main` and the retry implementation and regression test are present. GitHub combined status returned no checks for the latest commit; automated execution is therefore **UNVERIFIED**, not passed. No Render, deployment, database, or feature-scope changes.


## 2026-11-13 — Offline record sync-state visibility

- Target: make the existing local Customer and Work sync labels truthful and actionable while reconnect retries are running.
- Source finding: local directory rows derived sync state only from the presence of a server ID. A queued record therefore continued to display "Waiting to sync" even after a retryable server failure had been recorded in `last_sync_error`; the transient error was not shown in those rows.
- Changed `resources/js/app.js`: local Customer and Work rows now distinguish Synced, Needs attention, Re-pair required, Retrying sync, Waiting for connection and Waiting to sync. The status message is also exposed through the chip title and accessible label. Existing server identity and conflict handling remain unchanged.
- Added Playwright regression in `e2e/pwa-offline.spec.js`: simulate a 503 push response and assert the local Customer row changes to "Retrying sync" and exposes the HTTP failure message. Bootstrap and attachment endpoints are stubbed so this test isolates the status contract.
- Commits on canonical `main`:
  - `9a1543300d21bf9aeb206db90de716a4bbf73286` — status rendering.
  - `149cc09f8f49ab712718f027f9db4ef1bd10e8c2` — regression test.
  - `49e4bc703ce7c9a19d902979eb57f0d2c07dd83d` — isolate all sync endpoints in the test.
- Re-fetched and inspected the changed source and test from `main`. GitHub combined status returned no status contexts for the latest commit; Playwright execution and exact-head CI are **UNVERIFIED**. No local browser execution was available in this cycle.
- No Render, deployment branch, database or feature-scope changes.


## 2026-11-13 — Retry primary-device registration for queued offline work

- Owner reports two locally created customers still have not reached the server. This is a live acceptance failure; the earlier reconnect retry and status-label changes did not establish successful synchronization on the owner's installation.
- Further source inspection found a second concrete gap: the retry scheduler required an existing sync token. If initial `/offline/device/register` failed while the host was unavailable, queued changes had no token and no scheduled path to retry device registration. A browser can remain `navigator.onLine === true` while the local host is still starting, so no new browser `online` event is guaranteed.
- Changed `public/zazu-offline.js`:
  - Retry scheduler now covers both missing-token device registration and queued mutation synchronization.
  - Creating a durable local queue entry while the browser reports online starts the recovery scheduler.
  - Transient registration HTTP failures are persisted and surfaced to the UI.
  - HTTP 409 and cross-business registration mismatches are marked as blocked rather than retried indefinitely.
  - Successful registration clears the blocked state and continues normal queue reconciliation.
- Changed `resources/js/app.js`: a blocked cross-workspace registration displays **Needs attention**, not a misleading retry status.
- Added Playwright regression in `e2e/pwa-offline.spec.js`: queue a customer without a device token, simulate first registration HTTP 503, allow a later registration to succeed, then assert the queued customer is pushed and the queue drains.
- Commits on canonical `main`:
  - `1208a332941c322ae34baef50c0f2509549d80d9` — registration retry and queue-triggered recovery.
  - `5db2b7b002749fbe3acdcfee331aba37d9f9d264` — registration outage regression test.
  - `026de5835a8601918dfab1ef68eb69078df2a371` — deterministic test fixture.
  - `c9648e03ef820dd9cd7d356f4c0c364b27966be4` — stop retrying permanent HTTP 409 registration conflict.
  - `0bccdd1edfb2457b7e7dab4f7214136a3cb7510e` — surface blocked registration in local record status.
  - `55f1211a95b481644c957f798dc605fce6fe38de` — block cross-business registration mismatch.
- Source, UI and test were re-fetched from GitHub after writes. Workflow-run lookup for the test commit returned no runs; latest commit status contexts are unavailable. Playwright and local installation behaviour remain **UNVERIFIED**.
- The exact state of the owner's two queued customers cannot be read from GitHub; their IndexedDB queue, device token and server records have not been inspected. Do not claim these two records have synced. The owner should not recreate them.
- No Render, deployment, database or customer-data mutation was performed.


## 2026-11-13 — Offline sync recovery browser verification correction

- Current canonical repository/ref at start: `Leano-Jordan/ZazuEMP` / `main`; application/test candidate `55f1211a95b481644c957f798dc605fce6fe38de`.
- Exact-head Laravel job passed. The offline-browser-acceptance job failed: 20 passed, 2 failed. The first failure observed zero intercepted primary-device registration attempts because the test installed its route after dashboard boot could already initiate registration. The second status test's route setup occurred after queue creation, permitting scheduled retry timing to interfere with the assertion.
- Classified as test/fixture setup (F2/F3), not a confirmed production sync defect. Added `CASE-ZAZU-0029` / `ENG-FIXTURE-004`.
- Changed only `e2e/pwa-offline.spec.js`: defer automatic boot sync/registration in both affected tests; install status-test network routes before queuing the mutation; explicitly trigger sync after setup.
- Commit: `8ecd3894f30cf8c8218ce852aebbf3017d0eef4c`.
- Re-fetched changed test source from `main`; structural checks confirm both deferral guards and the revised status-test ordering.
- Exact-head CI for the correction is not yet visible through GitHub Actions. Browser acceptance remains **UNVERIFIED**; no application code, customer data, database, Render service, deployment branch or deployment setting was changed.
- Next: obtain exact-head offline browser acceptance. If green, reconcile the new registration-retry behaviour against the owner's two queued customer records without recreating or discarding them. If red, inspect the new job logs and follow CASE-ZAZU-0029's attempt budget.

## 2026-11-13 — Mobile UI unstyled / local Vite CSP correction

- Owner screenshot at `192.168.137.237:8000` shows the Blade shell rendering with browser-default typography, links and controls. This is an asset-delivery failure signature, not a confirmed palette/token defect; the earlier token-only diagnosis was insufficient.
- Re-inspected `app-layout.blade.php`, `vite.config.js`, `public/build/manifest.json` and `ApplySecurityHeaders.php`. The layout uses Laravel Vite. The security middleware's CSP allowed only `'self'` for styles/scripts/connections, which blocks Vite's separate development-server origin (including HMR websocket) even when the phone can reach that server.
- Corrected `ApplySecurityHeaders` to permit the origin from Laravel's runtime `public/hot` file only in `local` environment, and only when it is an HTTP(S) localhost/private-network origin with an explicit port. Added CSP regression tests for LAN Vite allowance and rejection of public origins.
- Updated Vite dev server binding to `0.0.0.0` and added optional `VITE_HMR_HOST` override for LAN testing.
- Commits: `edb02bcc` (CSP), `e95479cc` (tests), `c7d8baa7` (Vite LAN binding).
- Exact-head GitHub Actions quality workflow `43437312175` was queued at inspection time; test/build result is pending. No PHPUnit/Vite build result is claimed yet.
- Runtime verification boundary: actual phone access to the hot-file host and generated CSS HTTP responses still require device-side confirmation. If the hot file advertises localhost, set `VITE_HMR_HOST` to the PC's LAN IP and restart Vite; otherwise use a fresh `npm run build` with no stale hot file. Render, deployment, database and customer data remain untouched.

## 2026-11-13 — APP-001 regression correction

- Owner reported APP-001 immediately after the local Vite CSP middleware change.
- Root cause found in the committed PHP source: namespace/import separators had been written as doubled literal backslashes, making the middleware file syntactically invalid PHP and causing an application-level 500.
- Corrected the file's namespace/import separators and re-fetched the committed source to confirm valid single PHP namespace separators.
- Fix commit: `44b3aad31dcccfcb34471cbeb5d9d547562b6c78`.
- Exact-head CI for this correction was queued at inspection time; runtime request/visual acceptance is still pending. Do not claim APP-001 resolved on the user's machine until the local checkout is pulled and the page is retried.


## 2026-11-13 — Owner communication contract made persistent

- Owner directive: keep Director responses focused on the actual request; stop introducing unrequested topics, hypothetical alternatives, irrelevant infrastructure, invented scope or generic process narration.
- Added mandatory owner-facing communication rules to `.ai/engineering/00_DIRECTOR_ENGINE.md`.
- Added the durable reference `.ai/engineering/OWNER_COMMUNICATION_RULES.md` for all Director and specialist roles.
- Required reporting is outcome-first and proportional: material result, evidence, changed surface, genuine blocker and next in-scope action only.
- This instruction applies across future sessions and must not require the owner to repeat it.
- Commits: Director contract `5251da2c45a91912f805a71c880ca8c89b254d99`; communication reference `e8ed91954946ddcd2b4d86d06cf6a55d95c2fd26`.


## 2026-11-13 — V1 visual test contract reconciliation

- Active target: restore consistency between visual regression tests and the current canonical theme tokens in `resources/css/app.css`; application CSS and rendered UI were not changed.
- Corrected stale test expectations in:
  - `tests/Feature/InterfaceRegressionTest.php`
  - `tests/Feature/UiDashboardResponsiveTest.php`
  - `tests/Feature/UiShellThemeRegressionTest.php`
  - `tests/Feature/UiAccessibilityTest.php`
  - `tests/Feature/UiProportionFoundationTest.php`
- The assertions now match the actual light/dark palette and token ownership: `app.css` owns theme tokens; `zazu-product-authority.css` is a component extension and must not own root token blocks.
- Each changed test file was re-fetched from `main`; the updated representative assertions were confirmed present.
- Commits: `37f104a5a57b12e25dc8f8b301474cd4e022e8a6`, `025be00a81eabeb6f4e4a1395813a184f470f385`, `e4cd447a44aee7856bed92fc314b9314491a6b86`, `f72e9447766fbbe2646196f92b68c12db8921a59`, `79c86865c82ead240d09662022937a1e02b76d7b`.
- Verification boundary: GitHub combined status currently returns no status contexts for the latest commit. PHPUnit, PHPMD and exact-head workflow results are **PENDING / UNVERIFIED**. No application CSS, UI, business data or database was changed.
- Next active target: inspect the resulting exact-head Laravel and PHPMD output; address only failures still present after this test-contract correction.


## 2026-11-13 — V1 test maintainability findings addressed

- Split the three trial-lifecycle tests from `ExperienceLevelTest` into `ExperienceLevelTrialTest`; the original suite now has 8 public methods, below the PHPMD threshold.
- Split the long authenticated visual-authority regression into focused test methods; no rule suppression was added.
- Re-fetched both resulting test files and confirmed the new trial suite contains 3 tests and the original experience suite contains 8.
- Latest source commit: `f0b37a315940766c2f9d3f520493b3e6e7f1e061`.
- GitHub Actions quality run `43450011964` was created for that exact source commit. At the time of this record, both application and secret-scan jobs were queued; test outcome remains pending.
- Changed only test organisation. Application behaviour, CSS, UI and stored business data were not changed.
- Next: inspect the quality run's application result and act on any remaining concrete failures.


## 2026-11-13 — Deep audit and V1 gate reconciliation

- Audited the current main source and exact-head CI failures. Corrected stale radius/palette test contracts and the PHPMD Vite-origin complexity violation. No application CSS or visual design changes were made.
- Exact-head source checkpoint `f9eddc341f7bbca3902a5875b6359098dd183b11`: Laravel **480 passed / 1 skipped / 3,398 assertions**; PostgreSQL migration/registration/recovery PASS; PostgreSQL backup/restore round trip PASS; offline browser acceptance **22 passed**; Zazu quality PASS; PHPMD PASS; Psalm PASS; populated upgrade/rollback PASS.
- Full browser smoke on that checkpoint was cancelled by a subsequent documentation push and is not counted as passed.
- Reconciled two engineering-record defects: CASE-ZAZU-0029 / ENG-FIXTURE-004 now CLOSED in ERROR_INDEX, matching FAILURE_CASES; duplicate REG-015 was corrected by assigning the offline business-context cache control REG-023.
- Reviewed active business context, business-owned model writes, signed offline licensing/entitlement, finance transaction integrity and purchase-order receiving. No additional reproducible application defect was confirmed in those inspected paths.
- Audit report: `docs/DIRECTOR_DEEP_AUDIT_2026-11-13.md`.
- Latest branch checkpoint after audit documentation: `c750279c5ebc069cec15b921473e866ef62e8766`. Fresh exact-head workflows are queued/running; do not promote the prior source-checkpoint results to the new SHA until reruns complete.
- Remaining V1 acceptance: exact-head browser smoke and real installed phone/tablet acceptance against the release candidate. Scope remains frozen; no speculative gates were added.


## 2026-11-13 — Archive restore investigation continuation

- Latest `main` commit: `0eb03bdb206e0a36dffdc6ba8b00c38877f2c773`. This is a test-only strengthening: the archive fixture now explicitly asserts that its event attachment belongs to the source event before export. It does not change restore implementation or close the FK defect.
- Re-read the current `BusinessArchiveService.php` and the focused archive test. The restore loop remaps declared database foreign keys using ID maps built in definition order; the focused fixture has the expected parent order for customers → customer contacts → events → quotes → quote versions → quote items → attachments. This source inspection alone does not identify the runtime failing FK.
- Re-read the event/customer-contact/quote/event-requirement/attachment migrations. Confirmed event contact references point to `customer_contacts`; event requirements reference both events and business capabilities; attachment `event_id` references events and `uploaded_by` references users (which restore intentionally nulls).
- Exact commit combined status returned no status contexts, and the commit workflow-run lookup returned no runs. Focused test execution and exact-HEAD CI remain **UNVERIFIED**. No restore-code change is justified until the actual exception names the unresolved table/column.
- Next action: obtain the actual focused-test exception from a run or local test output; then fix only the proven missing dependency/remap and add the smallest regression. Keep the empty-target, unpaired-device, tenant-isolation and file-cleanup safeguards intact.


## 2026-11-13 — Archive verification lineage and FK dependency review

- Reconciled the historical green CI evidence against the archive feature's introduction. The recorded green run set on commit `245aa47f7ef74e6e133fff867670ecafabb603b8` passed Laravel, PostgreSQL recovery, offline-browser acceptance, browser smoke, quality and PHPMD. However, fetching `app/Services/BusinessArchiveService.php` and `tests/Feature/BusinessArchiveTest.php` at that SHA returned NOT_FOUND: those green runs predate the tenant business archive implementation and do **not** verify it.
- Reviewed the schema's single-column and composite tenant foreign keys against the archive restore definition order. The current order covers the inspected dependencies: customers → contacts; capabilities/tax rates → event/quote records; events → event children; suppliers → purchase orders → purchase-order items/receipts; inventory/assets → movements/allocations; invoices → payments; and all listed business-owned child tables. Composite tenant keys are handled by rebinding `business_id` separately and skipping that column during referenced-ID remapping (commit `46e46a6`).
- Current archive test fixture now explicitly asserts: attachment.event_id belongs to the source business event; the event is present in exported archive data; and the attachment's event ID matches that source event. These are useful preconditions, but they are test assertions, not a test execution result.
- No dependency-order or missing-ID-map correction is justified by static review alone. Exact focused-test exception and exact-head workflow execution for the archive commits remain **UNVERIFIED**. The business archive restore gate stays **OPEN / NO-GO**.
- Next action: obtain the focused PHPUnit output from a run that includes the current archive test, then correct only the named relationship. Do not use the older `245aa47` green run as evidence for archive recovery.

## 2026-11-13 — CI execution efficiency

- Reviewed the current `main` workflow triggers after documentation-only pushes repeatedly launched the full Laravel suite, PHPMD, Psalm and populated upgrade/rollback jobs.
- Added explicit path filters to `.github/workflows/laravel.yml`, `phpmd.yml`, `psalm.yml` and `zazu-populated-upgrade.yml`.
- Laravel remains triggered by application, migration, route, UI, browser-test, dependency, build/configuration and its own workflow changes. PHPMD and Psalm remain scoped to their relevant PHP/security source and dependency/configuration changes. Populated upgrade/rollback remains triggered by application/schema/dependency changes and its own workflow changes.
- Manual dispatch remains available for populated upgrade/rollback; PHPMD and Psalm scheduled scans remain unchanged.
- Documentation-only edits no longer trigger these four expensive workflows. Quality/secret scanning and Director contract lint retain their separate existing triggers.
- Commits: `c9c0761`, `42a715b`, `7762cd2`, `1433120`.
- Validation boundary: trigger definitions were re-fetched and the expected path filters verified. GitHub Actions execution for the workflow edits is pending; this is a CI scheduling change, not an application-code change.

## 2026-11-13 — SonarCloud trigger efficiency

- Scoped `.github/workflows/sonarcloud.yml` push and pull-request triggers to application, database, UI, route, test, dependency and analysis-configuration changes.
- Retained `workflow_dispatch` so a manual analysis can still be run when requested.
- This avoids queuing SonarCloud on documentation-only commits. SonarCloud remains skipped when its required project/organization variables are not configured; no skipped run is counted as a pass.
- Workflow commit: `3a22fc71af48a47cae25399b1756c7c57e39c6d6`.
- Validation boundary: workflow trigger content was re-fetched and the expected filters verified. Runtime application code was not changed.


## 2026-11-13 — security hardening and dependency remediation

- Owner priority: tighten security and reduce attack surface; offline engineering remains paused.
- Added Composer `composer audit --locked` and npm `npm audit --audit-level=high` to the Zazu quality workflow.
- The audit exposed vulnerable locked framework components. Updated and merged Laravel `v13.42.0`, Symfony HttpFoundation/HttpKernel/Routing `v8.1.11`, and Symfony Mime `v8.1.10` through [PR #54](https://github.com/Leano-Jordan/ZazuEMP/pull/54), merge commit `637561c53dedbe31d3de0e38e205200e3d5e3301`.
- Added a weekly/manual dependency-refresh workflow. It runs Composer audit, frontend build and the full Laravel suite before opening a dependency PR. Protected main correctly rejected direct workflow pushes; the process now creates PRs.
- Source audit found customer profile photos were business-scoped but did not enforce the existing `customers.view` permission. Fixed `ProfileMediaController` and added a regression for a member lacking that permission; expected 403 confirmed.
- Main regression evidence: [run 43508450343](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43508450343) — 487 passed, 3,476 assertions, 1 skipped.
- Dependency-upgrade PR evidence: [Laravel 487 passed](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43508848942), [Composer/npm audit and secret scan PASS](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43508848964), [Psalm PASS](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43508848909), [PHPMD PASS](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43508848991), [PostgreSQL migration/recovery PASS](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43508848942), [populated upgrade/rollback PASS](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43508848911).
- Psalm compatibility shim was verified against the upgraded Laravel source revision and its security scan passed.
- Offline browser acceptance was still running at checkpoint; it is not counted as passed and no offline application code was changed.
- Security score unchanged pending independent penetration testing and operational/privacy closure. No deployment or customer data was touched.


## 2026-11-13 — trusted-device privilege escalation closed

- Source review found `POST /offline/device/register` was available to any authenticated business member. It issued a business-wide sync bearer token; the device mutation handlers support invoices, payments and other financial entities. This bypassed the owner approval boundary and normal staff permission checks.
- Applied the existing `owner` middleware to primary-device registration. Non-owner staff now receive HTTP 403 and no sync-device row is created. Owner registration remains covered by the existing business-context regression.
- Added `test_staff_cannot_self_register_a_trusted_sync_device` in `BusinessContextSecurityTest`.
- Updated `public/zazu-offline.js` so HTTP 403 is treated as an owner-approval requirement, blocks automatic retries, and tells the user to ask the owner to pair the device.
- This is a bounded security correction. No sync protocol, mutation handler, IndexedDB queue, service worker or offline data model was changed.
- Exact main: `fd63ac5717a3afd656da7860530812d6a81a0016`. [Laravel run 43509943704](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43509943704): 488 passed, 3,478 assertions, 1 skipped; PostgreSQL migration/recovery PASS. [Quality + Composer/npm audit + secret scan](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43509943486), [Psalm PASS](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43509931780), [PHPMD PASS](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43509937057).
- Offline browser acceptance remained in progress and is not counted. Security remains 4/5; independent penetration testing and operational/privacy review remain outstanding.


## 2026-11-13 — Authorization boundary follow-up review

- Re-fetched current `main` source for `routes/web.php`, `routes/api.php`, `OfflineWorkspaceController`, `WorkspaceSearchService`, `PermissionService`, `ProfileMediaController`, `BusinessMediaController`, `ExperiencePreferenceController`, `BusinessContextController`, `CustomerContactController` and `CustomerImportController`.
- Checked the apparent absence of route middleware on `/offline/bootstrap`. The controller returns a broad offline workspace snapshot, but the current configured staff and manager roles have the matching read permissions for the included customer, work, quote, finance, supplier, purchasing, inventory, asset and capability datasets. The search service independently filters each result family by those same permissions. This is not recorded as a confirmed current privilege escalation. Offline implementation remains paused; no changes were made to bootstrap, sync, service worker, IndexedDB or device pairing.
- Confirmed customer profile media requires `customers.view` and business membership; user profile media requires active membership in the current business; business branding media is scoped to the resolved current business and served privately/no-store. Customer-contact endpoints combine route permission middleware with customer/business and contact/customer ownership checks. Customer import endpoints are permission-gated and bind the preview session to its originating business.
- Workspace switching delegates to `CurrentBusiness::switchTo`; search delegates authorization filtering to `WorkspaceSearchService`. Experience preferences are stored on the individual user/business membership rather than changing another member's preferences.
- No new confirmed authorization defect was established in this bounded source review. No application code or test was changed, and no runtime/CI pass is claimed. Existing security score remains 4/5 and evidence maturity remains 71/100; independent penetration testing and operational acceptance are still outstanding.


## 2026-11-13 — Authentication and privileged-route follow-up

- Reviewed the current login, registration, workspace recovery, logout and password-reset controllers; owner and platform-admin middleware; session configuration; named rate limiters; public signed-quote routes; and current-business switching.
- Confirmed login regenerates the session; logout invalidates the session and rotates the CSRF token; password reset rotates the remember token, regenerates the authenticated session, removes other database-backed sessions and invokes logout-other-devices; reset responses do not disclose whether an account exists.
- Confirmed owner routes resolve the active selected business and verify the user's owner membership. Platform administration is separately restricted by the configured platform-admin email allowlist. Business switching verifies active membership in the requested business before writing the session selection.
- Public quote viewing/acceptance requires valid signed URLs, permits only sent/accepted quote states as appropriate, locks the business/event/quote/version during acceptance, and rechecks the pending state before mutation.
- Login, registration, password-reset email, password-reset submission, public quote and sync API throttles are configured. No new confirmed defect was established in this source review. No application source or tests changed; no runtime/CI pass is claimed.
- Security remains 4/5 and evidence maturity 71/100. Independent penetration testing, physical acceptance and legal/privacy obligations remain open.


## 2026-11-13 — Main-only dependency security and commercial mutation review

- **Branch discipline:** all work remains on the existing `main` branch. No branch was created. Reconfirmed GitHub branch listing after owner cleanup: `main` plus the pre-existing `deploy/render-staging` ref.
- **Closed automation defect:** `.github/workflows/security-dependency-refresh.yml` previously ran `composer update`, pushed a run-specific `security/dependency-refresh-*` branch and opened a PR. Replaced that behaviour with a read-only scheduled/manual dependency audit: locked Composer audit, locked npm audit, frontend build and Laravel suite. Permissions are now `contents: read`; checkout credentials are not persisted; no branch/commit/PR creation commands remain. Commit: `7375cc198d6ef16930b764260c4a7e5fb0cb7aa2`.
- **Commercial mutation review:** re-read current-main `FinanceController`, `FinanceTransactionService`, `FinanceIdempotencyMatcher`, `PurchaseOrderController`, `PurchaseOrderReceivingService` and `InventoryController`. Confirmed route permission gates, active-business record scoping, business/event/order locks, idempotency checks, invoice balance/deposit guards, purchase-order line ownership, over-receipt prevention, negative-stock prevention and audit events in the reviewed paths. No new confirmed defect was established; no application mutation code or tests were changed.
- **Verification boundary:** the workflow change was source-verified after commit. No workflow run or CI pass is claimed for commit `7375cc1`; the combined status endpoint returned no status records at inspection time. The commercial review is source-level only, not runtime certification.
- Offline acceptance remains frozen. No browser smoke was repeated. No production deployment or customer data was touched.

## 2026-11-14 — Business archive restore complexity correction

- Current archive implementation was revalidated against the exact main head. The archive security test suite had passed on the preceding source checkpoint; the subsequent PHPMD run identified restoreRows() as exceeding cyclomatic and NPath complexity thresholds.
- Refactored the restore coordinator into focused table, row, foreign-key, file and business-profile helpers. No complexity suppression was added. Existing validation, tenant rebinding, source-row FK remapping, audit-subject detachment, file-write tracking and compensating file cleanup were preserved.
- Main commit: 41c473784da85e884db7b4978fd0b26c4884c0d5. Changed source re-fetched from GitHub and helper structure reconciled.
- Exact-head verification started: Laravel run 43520200922, PHPMD 43520200894, Psalm 43520200929, populated upgrade/rollback 43520200961, quality/audit 43520201036. Quality/audit completed successfully; the other runs were still in progress at this checkpoint.
- No release-readiness uplift until the exact-head Laravel, PHPMD, Psalm, PostgreSQL/offline and populated recovery results are reconciled. The real installation restore drill remains a separate operational acceptance gate.
