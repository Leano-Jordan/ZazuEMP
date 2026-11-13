## Director reconciliation — 2026-11-13 (current exact-main evidence)

**Canonical branch:** `main` · **verified head:** `5888123f1941beb478b31870a6e782e0f8ef8053`.

- Laravel workflow [43491595380](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43491595380): **PASS** — 486 passed / 1 skipped / 3,475 assertions; PostgreSQL migration, registration, database/private-file recovery and dedicated offline-browser acceptance passed.
- PHPMD [43491595199](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43491595199), Psalm [43491595173](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43491595173), quality/secret scan/CodeQL [43491594809](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43491594809), and populated upgrade/rollback [43491595120](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43491595120): **PASS**.
- The archive restore round-trip and safety tests now execute successfully. Business-level archive restore is **AUTOMATED-VERIFIED**, including related-record remapping, tenant isolation and private/public file payloads. It is not a substitute for the separate installation-level operator recovery drill.
- Offline session transition and active Work attachment workflows are **AUTOMATED-VERIFIED**. Physical phone/tablet persistence and installed-app acceptance remain owner-verified gates.
- Quote → payment → profit is **SEEDED AUTOMATED-VERIFIED**; live business-record reconciliation remains a separate operator acceptance.
- Installation recovery is **CI-VERIFIED / OPERATOR DRILL OPEN**.
- POPIA operational controls, privacy notice review/publication, retention and incident procedures, and Zazu name/trade-mark clearance remain **OPEN**.
- **Release disposition: NOT READY.** CI is green; physical-device, installed-target recovery, legal/privacy and brand gates remain. Evidence score is unchanged; no score uplift is claimed.
- No production deployment, live database, customer data or non-main branch was changed.

---

# Exact-main gate reconciliation — 2026-11-13 (superseded by current exact-head evidence)

This historical snapshot was recorded against an earlier application head. Its implementation statements and run IDs are retained for audit history only. The current authoritative state is the Director reconciliation immediately above: business archive restore is now automated-verified, and exact-head CI on `5888123f1941beb478b31870a6e782e0f8ef8053` is green. Do not use the old “NOT IMPLEMENTED” archive disposition or old 71/100 score as a current release decision. Physical acceptance, the installed-target recovery drill, POPIA operations and Zazu name clearance remain open.

---

## Multi-customer sync identity acceptance — 2026-11-13 (a6cd9b87)

- Server-side regression test now pushes two offline customer creates in one batch, asserts each local UUID receives its own distinct persisted server ID, then replays the identical batch and verifies there are still exactly two customer rows and two customer identity rows.
- Browser-side registration recovery regression also covers two separate queued customers and asserts distinct server IDs after registration retry.
- Exact-main CI for `a6cd9b8776f5917c942a44f55daac7b2db793ada`: Laravel `43471194715`, browser smoke `43471194418`, quality `43471194594`, Psalm `43471194578`, PHPMD `43471194532`, populated upgrade/rollback `43471194613`, Director health `43471194860`, and SonarCloud `43471194797` were running at last inspection. Do not count these as passed until complete.
- Earlier exact-head `72dded07` Laravel/offline-browser/PostgreSQL and quality/security checks passed; its browser smoke `43470319049` remained abnormally long-running and is not counted as a pass.
- The user's two real queued customers remain a physical-device acceptance check; do not recreate them.

---

## Exact-main release evidence — 2026-11-13 (f0598324)

- Exact current `main` HEAD: `f05983240ba61cfba4b545c85369e4c4ccc0ad99`.
- Laravel [43452907338](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43452907338): **PASS**. The run's Laravel job passed frontend asset build, the new LAN Vite asset-origin configuration test, Blade compilation, built asset-manifest validation, configuration validation and PHPUnit/Pest. PostgreSQL migration/registration/backup-restore and dedicated offline-browser-acceptance jobs: **PASS**.
- Full browser smoke [43452907654](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43452907654): **PASS**.
- Zazu quality + secret scan [43452907356](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43452907356), Psalm [43452907402](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43452907402), PHPMD [43452907248](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43452907248), populated upgrade/rollback [43452907310](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43452907310), Director Contract Lint [43452907398](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43452907398), push-on-main [43452907244](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43452907244), and Director Health [43454290881](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43454290881): **PASS**.
- SonarCloud [43452907264](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43452907264): **SKIPPED**, not counted as a pass.
- This exact-head run verifies the Vite asset-origin config/build contract and the current automated regression suites. It does not establish rendered UI correctness on the owner's PC/phone or prove physical installed-device offline acceptance.
- Offline sync browser regressions are green, but the owner's two existing locally queued customers have not been observed on the server. Do not recreate them; their local IndexedDB/server reconciliation remains a device-side acceptance item.
- Evidence maturity remains **71/100**. Technical V1 remains **NO-GO** pending owner physical phone/tablet acceptance on the selected trusted HTTPS origin and final bounded release decision. Commercial subscription certification remains separate.
- No deployment, Render service, database or customer data was changed; `deploy/render-staging` remains untouched.

---

## Exact-head V1 regression reconciliation — 2026-11-13 (offline browser fixture closure)

- Verified application/test source SHA: `6303ff59effe95ad95d5df862ff823b01b49d891` on `main`. The only application/test change in this cycle is the Playwright setup correction in `e2e/pwa-offline.spec.js`; no production runtime code changed.
- Laravel [run 43426697007](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43426697007): **PASS — 476 passed / 3,383 assertions / 1 skipped**.
- Dedicated offline browser acceptance: **PASS — 22 passed** (same workflow).
- PostgreSQL 16 migration/registration/recovery: **PASS** — complete migrations, account/workspace registration (**1 test / 13 assertions**) and database/private-file backup/restore (**1 test / 5 assertions**).
- Full browser regression [run 43426696768](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43426696768): **PASS — 86 passed / 46 skipped / 0 failed / 0 flaky**.
- Zazu quality + secret scan [run 43426696823](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43426696823), Psalm [run 43426696750](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43426696750), PHPMD [run 43426696734](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43426696734), populated upgrade/rollback [run 43426696904](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43426696904), Director Contract Lint [run 43426696909](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43426696909), Director Health [run 43426696562](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43426696562) and CodeQL analysis [run 43426696578](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43426696578): **PASS**.
- SonarCloud [run 43426696691](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43426696691): **SKIPPED**, not counted as a pass.
- CASE-ZAZU-0029 / ENG-FIXTURE-004 is closed. No readiness score uplift; it remains 71/100.
- **Remaining technical V1 gate:** owner physical phone/tablet installed-app acceptance on the selected trusted HTTPS origin, followed by the final release decision. Browser tests do not substitute for real-device proof.
- Commercial subscription launch remains separate from technical V1. No deployment or Render changes; `deploy/render-staging` remains untouched.

---

## Exact-head CI reconciliation — 2026-11-13 (PWA offline navigation fixture)

- Exact current `main` HEAD: `282a4eb366956dfb49023cd5edc20999f5ead3cb`.
- Change scope: test-only correction in `tests/js/service-worker-auth-cache.test.js`. The offline navigation test now primes the authenticated dashboard while the host is available, then disables the host before requesting the cached page. This matches the real installed-app sequence and prevents a false test setup where the cache was never populated. No application runtime source or deployment configuration changed.
- Laravel workflow [run 43367980312](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43367980312): **PASS — 462 passed / 3,339 assertions / 1 skipped**. Inspected job log; PHPUnit/Pest completed successfully.
- PostgreSQL migration/recovery job: **PASS** — complete migration set, real account/workspace registration, PostgreSQL backup/restore and private-file recovery (same Laravel workflow).
- Offline browser acceptance: **PASS** — full dedicated job completed successfully (same Laravel workflow).
- Full browser regression [run 43367980364](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43367980364): **PASS — 83 passed / 40 skipped / 0 failed**. Inspected job log; no failed tests.
- Zazu quality + secret scan [run 43367980476](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43367980476): **PASS**.
- Psalm security [run 43367980426](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43367980426): **PASS**.
- PHPMD [run 43367980408](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43367980408): **PASS**.
- Populated upgrade/rollback [run 43367980313](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43367980313): **PASS**.
- Push-on-main [run 43367979917](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43367979917): **PASS**.
- Director Health [run 43368786394](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43368786394): **PASS**.
- SonarCloud [run 43367980435](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43367980435): **SKIPPED**, not counted as a pass.
- No deployment was performed; `deploy/render-staging` remains untouched.
- **Engineering conclusion:** exact-head automated verification is green. The remaining bounded owner acceptance is the real installed-device workflow on the intended secure HTTPS origin. Browser emulation is not physical-device proof. Do not add further offline edge-case scope without a reproducible V1 failure.

---

## Exact-head CI reconciliation — 2026-11-12 (offline runtime readiness regression)

- Exact current `main` HEAD: `918b011766931919485ad4b60d3f2b0494603b0a`.
- Runtime application source remains at `7193038a2b2d14ea71278050a0d7c26ce246bf86`; this latest commit changes only `e2e/pwa-offline.spec.js`, adding an explicit wait for the offline runtime API before device-pairing assertions. No application runtime code or deployment configuration changed.
- Laravel [run 43302174502](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43302174502): **PASS — 462 passed / 3,339 assertions / 1 skipped**. Inspected the job log: `CommercialReconciliationTest`, `QuoteCommercialHandoffTest`, `WorkCompletionGuardTest`, `PwaOfflineFoundationTest`, populated backup/restore, tenant isolation, and the broader feature suite passed.
- Offline browser acceptance: **PASS — 19 passed** (same Laravel workflow).
- PostgreSQL migration/recovery smoke: **PASS** (same workflow; PostgreSQL migration and backup/restore job succeeded).
- Full browser smoke [run 43302174393](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43302174393): **PASS — 82 passed / 40 skipped / 0 failed**. The 40 skips are the existing deliberate cross-project exclusions for stateful offline/session/registration journeys.
- Populated database upgrade/rollback [run 43302174523](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43302174523): **PASS**.
- Zazu quality + secret scan [run 43302174588](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43302174588): **PASS**.
- Psalm security [run 43302174606](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43302174606): **PASS**.
- PHPMD [run 43302174546](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43302174546): **PASS**.
- Push-on-main / CodeQL [run 43302175129](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43302175129): **PASS**.
- Director Health [run 43303097204](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43303097204): **PASS**.
- SonarCloud [run 43302174514](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43302174514): **SKIPPED**, not counted as a pass.
- No deployment was performed; `deploy/render-staging` remains untouched.
- **Engineering conclusion:** the test-readiness race is corrected and exact-head automated verification is green. No further code patch is justified by this evidence. Continue the bounded V1 finish sequence; physical installed-device offline acceptance remains a human-device task and must not be represented as passed by browser emulation.

---

## Exact-head CI update — 2026-11-12 (Work completion guards + offline customer-to-Work)

- Exact application/test-source HEAD: `7193038a2b2d14ea71278050a0d7c26ce246bf86` (merge commit; the later `main` descendants `453c80d` and `78fb793` change only these two evidence documents; includes the test-only commits `e3855998`, `b2e548be`, and offline acceptance correction `9bd1cc0d`).
- Laravel workflow [43298535995](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43298535995): **PASS — 462 passed / 3,339 assertions / 1 skipped**. The three focused Work completion-guard tests pass: completion is rejected while preparation is open or blocked, costs remain planned, or a linked purchase order is active. No application runtime change was needed; existing guards were locked with regressions.
- Offline browser acceptance: **PASS — 19 passed** (same Laravel workflow).
- PostgreSQL migration/recovery smoke: **PASS** (same workflow; both PostgreSQL-focused test steps passed).
- Full browser smoke [43298535961](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43298535961): **PASS — 83 passed / 40 skipped / 0 failed**. Inspected job log: 123 tests, no retry/failure lines. Offline customer-to-Work journey now explicitly disconnects the browser before local writes and checks the queued customer ID is the one linked by the Work record.
  - Skip audit: the offline/session/registration contracts marked `test.skip` for non-Chromium projects account for the 40 skips; those stateful contracts run once on Chromium, while the responsive suite still runs on desktop, Pixel 7 and tablet projects.
- Zazu quality/secret scan [43298535431](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43298535431): **PASS**.
- Psalm security [43298535526](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43298535526): **PASS**.
- PHPMD [43298536000](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43298536000): **PASS**.
- Populated upgrade/rollback [43298535460](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43298535460): **PASS**.
- Render Docker image build [43298535911](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43298535911): **PASS — build only; no deployment**.
- Push-on-main [43298535997](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43298535997): **PASS**.
- Director Health [43299843826](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43299843826): **PASS**.
- SonarCloud [43298536001](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43298536001): **SKIPPED**, not counted as a pass.
- Exact-head automated source verification is reconciled. No deployment or deployment-branch change occurred; `deploy/render-staging` remains untouched.
- Evidence-backed limitations are recorded in [`docs/V1_KNOWN_LIMITATIONS.md`](V1_KNOWN_LIMITATIONS.md): bounded offline scope, connection-required customer profile photos, non-autonomous Helper, deliberately deferred higher-tier capabilities, and the still-open physical-device acceptance boundary.
- Known limitations are now recorded in `docs/V1_KNOWN_LIMITATIONS.md` and linked from the release checklist. Bounded next acceptance: physical installed-device workflow on the owner's secure HTTPS origin, then issue the V1 decision. Do not expand scope or invent extra gates.

## Exact-head CI update — 2026-11-12 (offline customer-to-Work regression)

Exact source/test HEAD: `2b171f3fda4ae1ddeaf30700dc3f9c414866be92` on `main`.

- Browser smoke: **PASS — 83 passed / 40 skipped / 0 failed** ([run 43295881449](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43295881449)). Job log inspected; the 123-test run completed without reported retries. The customer-to-Work offline-save regression now intercepts the actual connectivity probe and verifies interception before the offline-save confirmation.
- Laravel, PostgreSQL migration/registration/recovery, and offline-browser-acceptance jobs: **PASS** ([run 43295881102](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43295881102)).
- Zazu quality/secret scan: **PASS** ([run 43295881101](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43295881101)).
- Psalm security: **PASS** ([run 43295881418](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43295881418)).
- PHPMD: **PASS** ([run 43295881090](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43295881090)).
- Populated database upgrade/rollback: **PASS** ([run 43295881378](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43295881378)).
- Push-on-main / CodeQL: **PASS** ([run 43295879914](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43295879914)).
- SonarCloud: **SKIPPED**, not counted as a pass.

This is automated source verification only. It does not close physical installed-device acceptance or the other operational release gates already recorded below. No deployment was performed; `deploy/render-staging` remains untouched.

---

## Director exact-head CI reconciliation — 2026-11-12 (resume-sync/cache v35)

- Exact application/test-source SHA: `5924fd6d294187ec482295193c4add865e64137b`.
- Laravel: **PASS — 459 passed / 3,331 assertions / 1 skipped** ([run 43290913503](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43290913503)).
- PostgreSQL migration, account/workspace registration, backup/restore and private-file recovery: **PASS** (Laravel workflow job `postgres-migration-smoke`).
- Offline browser acceptance: **PASS — 19 passed** (same Laravel workflow).
- Full browser smoke: **PASS — 82 passed / 40 skipped / 0 failed** ([run 43290913416](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43290913416)).
- Populated database upgrade/rollback: **PASS** ([run 43290913571](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43290913571)).
- Zazu quality + secret scan: **PASS** ([run 43290913644](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43290913644)).
- Psalm security: **PASS** ([run 43290913547](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43290913547)).
- PHPMD: **PASS** ([run 43290913940](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43290913940)).
- Director Health: **PASS** ([run 43290913608](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43290913608)).
- SonarCloud: **SKIPPED**, not counted as a pass.
- The Android installed-app resume/reconnect regression is included in the offline-browser suite and passed. This closes the previous exact-head CI uncertainty for this source/test SHA.
- Remaining V1 acceptance is the agreed physical installed-device offline check on the owner's secure HTTPS origin, followed by the release decision. Render production hosting/restore/rollback remain deferred under the owner-directed finish rule.

## Owner-directed V1 finish decision — 2026-11-12

This section supersedes older open-gate wording below where it conflicts with the owner's current scope direction.

- V1 scope is frozen by `.ai/engineering/V1_SCOPE_FREEZE.md`.
- Render is an interim remote-development workbench, not the final production destination. Production hosting, persistent production storage, production restore and production rollback are deferred until the actual production target is selected; they are not current Render-workbench gates.
- Do not treat speculative or future-facing items as V1 blockers. Only a reproducible defect in an agreed V1 workflow, a demonstrated security/privacy/data-integrity failure in that workflow, or an explicit owner-approved requirement can block V1.
- The remaining acceptance work is limited to the agreed customer workflows and concrete release obligations: final core workflow/offline-sync acceptance, one final regression pass, and recording known limitations. Physical installed-device checks are a bounded acceptance task, not an invitation to expand offline scope.
- Existing automated Laravel, PostgreSQL migration/registration/backup-restore CI and offline-browser results remain valid at the exact SHAs and run IDs recorded below. Do not claim later commits are covered without exact-head CI evidence.
- Stop once the agreed acceptance criteria pass; issue the V1 release decision rather than creating another checklist.

## Director exact-head CI reconciliation — 2026-11-12 (PostgreSQL recovery)

- Canonical engineering branch: `main`; exact application/test HEAD: `1cac0e51d72b506f7c58a1c06b9d204a521f81fa`.
- PostgreSQL backup/restore support is now included for the Render PostgreSQL deployment path. The Docker runtime installs PostgreSQL client tools; backup reads Laravel's resolved PostgreSQL connection including `DB_URL`; restore uses `psql` with stop-on-error and a single transaction, and captures a pre-restore PostgreSQL dump for rollback.
- Laravel: **PASS — 458 passed / 3,319 assertions / 1 skipped** (workflow run [43172547296](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43172547296)).
- PostgreSQL 16 migration + registration/workspace smoke: **PASS** (same workflow).
- Real PostgreSQL database + private-file backup/restore round-trip: **PASS — 1 test / 5 assertions** (same workflow; job `postgres-migration-smoke`). This verifies the test PostgreSQL service, not the live Render database.
- Offline browser acceptance: **PASS — 18 passed** (same workflow).
- Full browser smoke: **PASS** (run [43172547165](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43172547165)).
- PHPMD: **PASS** (run [43172547174](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43172547174)); Psalm security: **PASS** (run [43172547105](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43172547105)); Zazu quality + secret scan: **PASS** (run [43172547188](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43172547188)); populated upgrade/rollback: **PASS** (run [43172547074](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43172547074)); Render Docker image build: **PASS — build only, no deployment** (run [43172547119](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43172547119)); Director Health: **PASS** (run [43172584653](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43172584653)).
- SonarCloud: **SKIPPED**, not counted as a pass.
- **Release remains NO-GO.** The CI PostgreSQL round-trip does not prove recovery against the actual Render persistent database/disk. A controlled target-environment restore, data/private-media/access verification, and deployment rollback remain open, alongside physical installed-device offline acceptance and legal/brand/licensing closure. No Render service, database, disk, branch or deployment setting was changed. `deploy/render-staging` remains preserved.

## Director exact-head CI reconciliation — 2026-11-12

- Canonical engineering branch: `main`; exact HEAD: `9fd5dd20edfe8eaad5a80681a895ac9a3f459283`.
- The prior browser run on `0b14c00` was cancelled; it is superseded by the complete successful run on this newer exact HEAD.
- Laravel: **PASS — 456 tests / 3,305 assertions** (run `43170382971`).
- PostgreSQL 16 migration + registration/workspace acceptance job: **PASS** (same Laravel workflow).
- Offline browser acceptance: **PASS — 18 passed** (same Laravel workflow).
- Full browser smoke: **PASS — 82 passed / 38 skipped / 0 failed** (run `43170382911`).
- PHPMD: **PASS** (run `43170382965`); Psalm security: **PASS** (run `43170382864`); Zazu quality + secret scan: **PASS** (run `43170382928`); populated upgrade/rollback: **PASS** (run `43170382954`); Render Docker image build: **PASS — build only, no deployment** (run `43170382904`); Director Health: **PASS** (run `43171198770`).
- SonarCloud: **SKIPPED**, not counted as a pass.
- The 2026-11-12 manifest correction updates the app.css asset reference; this is not a UI/runtime redesign.
- **Release remains NO-GO.** CI does not prove live Render persistence, operator restore/rollback on the target, physical installed-device offline acceptance, or legal/brand/licensing closure. No Render service or deployment settings were changed in this cycle. `deploy/render-staging` remains preserved.

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



## CSP style migration — workspace background variables — 2026-11-10

- Moved the business-specific dashboard and wallpaper CSS custom properties off the authenticated document's `body style=` attribute and into a response-nonce-authorized `<style>` element in the document head.
- Preserved the existing media routes, cache-busting version, conditional wallpaper behaviour and CSS variable names; no visual or route behaviour was intentionally changed.
- Added a focused `SecurityHeadersTest` source regression asserting the nonce block and absence of a body inline-style attribute.
- Commits: `3a77c92` (layout), `c31347b` (initial regression), `8e17a61` (precise body-tag assertion), `ccb6239` (move style block into head).
- Verification boundary: changed source re-fetched and inspected. PHPUnit, Blade compilation, browser regression and exact-head CI have **not** been run for this change. Other inline style attributes remain; CSP unsafe-style allowances are intentionally unchanged. No score uplift; V1 remains **NO-GO**.


## Jarvis implementation update — offline Work attachments — 2026-11-10

**Application/test source has changed after the 90b3d1b verified baseline.** The 69/100 evidence score and the 90b3d1b CI results above remain historical baseline evidence only; they do not verify the following commits.

- `0a126881`: queue attachments against a validated local Work ID before server identity exists; preserve business scope, Blob and idempotency key.
- `48e991ba`: expose the attachment action on locally queued Work rows.
- `352f9d14`: add Laravel source-contract regression guards.
- `876f0828`: add a real Playwright journey for offline customer/job creation, attachment selection through the Work row, local Blob retrieval, no upload before identity resolution, then upload after the server ID is assigned.
- `0c202680` / `5a653874` / `d4c574e3`: show local attachment names/status and provide local download without rebuilding the Work row.
- `cd677995` / `22ea45fe`: assert visible file entry and download initiation in the browser regression.

All implementation changes target canonical `main`. Changed files were re-fetched and key source/test anchors inspected. GitHub combined-status lookups returned no status contexts for the checked commits. **Playwright, Laravel, Vite build and exact-head CI have not been executed for this batch.** The test simulates the job's server-ID assignment at the local-state boundary; a live Laravel sync round-trip and physical device test remain open.

**Disposition:** source implementation and regression coverage added; automated verification pending. No score uplift. V1 remains **NO-GO**.
# Zazu EMP — V1 Release Gate Ledger

## Director full audit and exact-head CI — 2026-11-10

**Verified application/test source:** `90b3d1b7ad1c888a69f728df0add75d834884bbf`.  
**Audit record:** [`docs/DIRECTOR_FULL_AUDIT_2026-11-10.md`](DIRECTOR_FULL_AUDIT_2026-11-10.md).  
**V1 evidence-maturity score:** **69/100 (3.46/5)** — retained from the last formally calculated 13-domain scorecard. No numerical uplift is awarded in this cycle.  
**Commercial offline licensing maturity:** **1/5 (additional, not included in the 13-domain score)**; activation/signing/enforcement are not integrated into the application.  
**Release decision: NO-GO.**

Exact-head verification for `90b3d1b`:
- Laravel: **PASS — 417 tests / 2,954 assertions** ([run 42792043006](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42792043006)).
- Browser smoke: **PASS — 65 passed / 22 skipped / 0 failed** ([run 42792043223](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42792043223)).
- Zazu Quality + secret scan: **PASS** ([run 42792042787](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42792042787)).
- Psalm Security: **PASS** ([run 42792043008](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42792043008)).
- PHPMD: **PASS** ([run 42792042981](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42792042981)).
- Populated upgrade/restore/rollback: **PASS** ([run 42792043233](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42792043233)).
- Director health: **PASS** ([run 42792042897](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42792042897)).
- Push on main / CodeQL: **PASS** ([run 42792044052](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42792044052)).
- SonarCloud: **SKIPPED**, not counted as a pass ([run 42792042834](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42792042834)).

The code change on this source baseline adds per-response CSP nonces to the three standalone inline style blocks and applies `style-src-elem` nonce enforcement. Inline style attributes and a legacy fallback remain; CSP hardening is improved, not complete. The updated source/test batch passed on the exact SHA above.

**Open release gates remain:** physical phone/tablet offline acceptance on secure HTTPS; selected persistent production hosting and deployment smoke; real operator backup/restore and deployment rollback; trusted-device/lost-device acceptance; operational privacy/legal and incident-response closure; Zazu trade-mark clearance; and a commercial decision/implementation for signed offline licensing where the paid entitlement model requires it.

This checklist update and the audit record are documentation-only descendants of the verified application/test SHA. They do not change the code assessment baseline.

## Director scorecard and exact-head evidence — 2026-11-09

**Current V1 evidence-maturity score: 69/100 (3.46/5), up from the historical 60/100 baseline.**  
**Verified application/test source:** `d55539ddfd52c7fa5140714288c5885dbcc99e3a`.  
**Release decision: NO-GO** — the score does not waive physical or operational release gates.

- Laravel: PASS — 414 tests / 2,938 assertions ([run 42702419420](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42702419420)).
- Browser smoke: PASS — 65 passed / 22 skipped / 0 failed or flaky ([run 42702419540](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42702419540)).
- Zazu Quality + secret scan: PASS ([run 42702419210](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42702419210)).
- Psalm Security: PASS ([run 42702419299](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42702419299)).
- PHPMD: PASS ([run 42702419074](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42702419074)).
- Populated upgrade/rollback: PASS ([run 42702419767](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42702419767)).
- Push on main: PASS ([run 42702420093](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42702420093)).
- SonarCloud: SKIPPED; not counted as a pass.
- Director Contract Lint: not triggered by this test-only commit; its previous control-plane run passed on the immediately preceding Director documentation commit.

The score increased to 69 because recovery/data portability moved from 2/5 to 3/5: CI now restores a populated seeded business database and its private media, then verifies representative values and 14 operational table counts. Platform/onboarding, core lifecycle, security/tenant controls and active documents/attachments retain their evidence-based improvements. Offline continuity remains 3; UX remains 3; legal/privacy/brand remains 1.

**Remaining release blockers:** secure-origin installed-device offline acceptance; representative restore on the actual target installation; real deployment rollback; trusted-device/lost-device acceptance; operational privacy/legal closure; official Zazu brand clearance.

The full 13-domain table and scoring method are in `docs/ZAZU_V1_SCORECARD_2026-11-04.md`.


## Director product audit — 2026-11-09

Current comprehensive audit: `docs/DIRECTOR_PRODUCT_AUDIT_2026-11-09.md`.

- V1 remains **NO-GO**. The last formal evidence-maturity score is 60/100; no new score is awarded by this CI cycle.
- Latest verified application/test source: `153c4d01bef4b5c3bd01a1db770902b0c7649bb0`. Laravel, Quality, PHPMD, Psalm, browser smoke, populated upgrade/rollback, Director Contract Lint and Director Health all passed on this source SHA. Browser smoke: 65 passed, 22 skipped, 0 failed. SonarCloud was skipped.
- Current `main` HEAD is `dff2b457e32096e4bb97cb46c3a5faa7ba67eda7`, a Director failure-registry documentation update; its CI batch is queued/in progress. No application/test source changed after the verified source SHA.
- The dark-theme regression case CASE-ZAZU-0014 is now verified on the exact application/test SHA above.
- PC/phone access still uses a temporary LAN address and plain HTTP. Establish a stable private HTTPS origin before physical PWA offline acceptance; preserve/reconcile origin-scoped IndexedDB queues before migration.
- Physical PC/mobile offline acceptance, real restore/rollback drills, trusted-device security, operational privacy/legal closure and official Zazu brand clearance remain open.

**Status:** ACTIVE / CANONICAL  
**Repository:** `Leano-Jordan/ZazuEMP`  
**Branch:** `main`  
**Main-ref rule:** query the live `main` ref; this ledger does not pin a moving HEAD.  
**Last Director reconciliation:** 2026-11-09  
**Latest verified application/test source SHA:** `153c4d01bef4b5c3bd01a1db770902b0c7649bb0`. Current `main` is a documentation-only descendant; the application/test source remains this exact verified SHA.

## Latest Director CI reconciliation — 2026-11-09

The application/test source `153c4d01bef4b5c3bd01a1db770902b0c7649bb0` passed the following exact-source checks:

| Check | Result |
|---|---|
| Laravel | PASS — [run 42694636423](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42694636423) |
| Zazu Quality + secret scan | PASS — [run 42694636578](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42694636578) |
| Psalm Security | PASS — [run 42694636966](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42694636966) |
| PHPMD | PASS — [run 42694636477](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42694636477) |
| Populated upgrade / rollback | PASS — [run 42694636774](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42694636774) |
| Browser smoke | PASS — 65 passed, 22 skipped, 0 failed — [run 42694636938](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42694636938) |
| Director Contract Lint | PASS — [run 42694637003](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42694637003) |
| Director Health Report | PASS — [run 42695508475](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42695508475) |
| Push on main | PASS — [run 42694636394](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42694636394) |
| SonarCloud | SKIPPED; not a pass |

The verified source was then followed by documentation-only commit `dff2b457e32096e4bb97cb46c3a5faa7ba67eda7` to close CASE-ZAZU-0014 and reconcile this ledger. A fresh workflow batch was triggered for that documentation commit; it is not yet complete at the time of this entry. The application/test source has not changed.

**Release position remains NO-GO.** CI verifies repository-level source and browser regressions. It does not certify real installed-device offline operation, operator restore/rollback, deployment readiness, legal/privacy operations or brand clearance.

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


## CSP inline-style attribute reduction — 2026-11-10

- Removed the six dynamic image `style=` attributes from the public landing page. Their image URLs now flow through response-nonce-authorized CSS custom properties and class selectors in the landing document head.
- Removed the dynamic inline `style=` attribute from the admin landing-image preview. Its per-field preview rule now uses a response-nonce-authorized style element and a stable field-derived element ID.
- Replaced the workspace-recovery sign-out form's inline `display:inline` declaration with the `zazu-auth-inline-form` class in the application stylesheet.
- Added SecurityHeadersTest source assertions that these active Blade views contain no inline style attributes and that the landing/admin dynamic CSS uses nonce-authorized style blocks.
- Commits: `4c80e28` landing page; `40711e5` admin preview; `b51f15a` recovery form; `e6735d0` stylesheet; `f8a236a` regression assertions.
- Guardian review: source-level inspection confirms the three changed views have zero `style=` attributes. The legacy `public/offline-attachments.js` contains inline attributes but is not imported by the active authenticated layout; it remains a dormant legacy asset and was not reactivated or modified. `resources/js/app.js` also uses CSSOM `style.setProperty/removeProperty` for the floating helper's measured nav-avoidance position, so the policy's `style-src-attr 'unsafe-inline'` allowance is deliberately retained until that runtime behaviour is redesigned and tested.
- Verification boundary: changed files re-fetched; no PHPUnit, Blade compilation, browser run or exact-head CI execution is available here. No CSP relaxation was made, no readiness score uplift is claimed, and V1 remains **NO-GO**.


## Signed offline licence verifier — current-head verification — 2026-11-10

- Refactored Ed25519 token verification into `SignedOfflineLicenseVerifier`; `OfflineLicenseService` now coordinates verification, anti-rollback and persistence. The service no longer exposes unsigned local entitlement issuance.
- Current regression coverage: valid import, expiry, tamper rejection without replacing an existing entitlement, cross-business rejection, signed revocation, missing public key fail-closed, and rejection of an older renewal token.
- PHPMD initially rejected the monolithic verifier for cyclomatic/NPath/method-length complexity. The verifier was split into bounded methods and a dedicated verifier class; the PHPMD workflow now passes.
- Full Laravel suite on commit `b9fc97d58e923200caf7e515926453b390aab0f3`: **422 passed, 2,985 assertions**. This run also caught two stale source-contract assertions in `PwaOfflineFoundationTest`; those were corrected to match the actual `dataset` API and local-event-ID method signature before this passing run.
- Current-head checks: PHPMD **PASS**; Psalm security scan **PASS**; Zazu quality workflow **PASS**; populated upgrade/rollback workflow **PASS**; Laravel **PASS**. Browser smoke is still running and is not yet accepted as passed.
- Relevant commits: `79f41fc` initial verifier; `f01371a` remove unsigned issuer; `39194b5` input bound/anti-rollback; `5544e07` verifier refactor; `632e148` dedicated verifier; `9e82b7c` DI; `5a54a21` attachment DOM regression correction; `b9fc97d` local attachment method-signature regression correction.
- Remaining licensing blockers: owner activation/status UI, production issuer and private-key custody, installation identity/reactivation, renewal UX, and enforcement at the correct business boundary. No readiness score uplift yet; V1 remains **NO-GO** pending browser smoke and remaining release gates.


## Director correction — offline licensing status (2026-11-10)

The earlier licensing note saying owner activation/status UI remained outstanding is superseded by current-main implementation:
- Owner Business Settings now displays the installed licence status, plan, start/expiry and reference.
- Owner can import/renew using a signed Ed25519 token while offline.
- The route is authenticated, business-context and owner protected, rate-limited, and audit logged.
- Feature tests cover activation, audit persistence, invalid-token handling, expiry, tampering, cross-business rejection and signed revocation.

**Still open:** production issuer and private-key custody; installation identity/reactivation; application-wide plan/feature enforcement; exact-current-HEAD CI confirmation. This is an activation/import flow, not yet a complete commercial licensing system.

The recorded 422 passing Laravel tests / 2,985 assertions are tied to commit `b9fc97d`; do not report them as current-HEAD results without a matching run. No physical-device, deployment, recovery, privacy or brand gate is closed by this correction.


## Jarvis implementation update — Docker preview context hardening — 2026-11-11

- Added `.dockerignore` to keep local environment files, developer state, SQLite files, runtime storage/private uploads, dependencies and test artifacts out of Docker build contexts.
- Added `.dockerignore` to the Render preview image workflow path filter so changes to build-context rules are validated by a fresh image build.
- Commits: `74196d7` and `934ada1`.
- Both changed files were re-fetched from `main` and checked.
- Exact-head CI and Docker build for the hardened context are pending. The previous Docker build was still running against `566ace3` and does not verify this change.
- Application source is unchanged. No evidence score uplift; V1 remains **NO-GO**.



## Jarvis execution — 2026-11-11 — Director CI noise reduction

- Finding: Director health reporting was triggered on every main push (except `docs/health/**`) and separately after completion of six CI workflows. One source commit could therefore queue many duplicate health-report runs.
- Changed `.github/workflows/roscore-director-health.yml`: push reporting is now limited to engineering/source/configuration/CI control paths; the completion trigger now listens only to the Zazu browser smoke workflow, which is the final/slow acceptance signal in the required batch.
- This preserves an early push snapshot and a post-browser final evidence snapshot while avoiding six near-identical post-check reports.
- Commit: `224ddadf3cb6a711fe8475e8f46d57d7fae54541`.
- Docker image build with the new `.dockerignore` passed on the prior preview-build run. It builds only; no deployment occurred.
- A fresh exact-head CI batch has been queued for the workflow change. Browser smoke on the previous documentation head was still running at last inspection; do not claim it passed.
- No application source changed and no score uplift is claimed. V1 remains **NO-GO**.

## Director reconciliation — 2026-11-11 — CSP and incident logging

**Current documentation/source head:** `6dc7e3c57a4ad770444ce337c128985d0074d02f` on `main`. This entry supersedes earlier CSP notes in this checklist and the stale ZAZU-SEC-05 description in the 3 November security audit.

### CSP source status

- `ApplySecurityHeaders` now sets `style-src 'self' 'nonce-{response nonce}'`, `style-src-elem` with the same nonce, and `style-src-attr 'none'`. No `unsafe-inline` remains in the emitted CSP.
- `SecurityHeadersTest` scans every active Blade view and rejects inline style attributes; every inline style element must carry a response nonce.
- The test also guards the shared layout, standalone views and app.js against the previously identified dynamic inline-style usage.
- This closes the source-level inline-style migration finding (ZAZU-SEC-05) on the current source. Browser smoke remains a separate runtime gate.

### Incident logging status

- ZAZU-SEC-07 redaction is implemented and regression-tested. The application incident recorder excludes exception messages, SQL, bindings and stack traces; Laravel's duplicate raw exception report path is suppressed.

### Exact-head automated evidence

For `6dc7e3c57a4ad770444ce337c128985d0074d02f`:
- Laravel: **PASS — 455 tests / 3,299 assertions**.
- Offline browser acceptance: **PASS**.
- Zazu quality + secret scan: **PASS**.
- Psalm security: **PASS**.
- PHPMD: **PASS**.
- Populated upgrade/rollback: **PASS**.
- Director Contract Lint: **PASS**.
- Zazu browser smoke: **IN PROGRESS / NOT YET VERIFIED** (run 43082852341). Do not treat this as a pass until the run concludes.

### Release decision

**V1 remains NO-GO.** Remaining proof is not another round of speculative code expansion: complete browser smoke; prove the real installation's workspace registration/creation path; run representative operator backup/restore and deployment rollback drills; complete trusted-device/lost-device acceptance; and close production privacy, provider-log, legal/brand and licence issuer/enforcement controls. No Render deployment was triggered by this cycle.

## Render preview registration incident — 2026-11-11

- Render request-correlated application logs confirm the previous registration failures were **SQLite read-only write failures**, not missing migration columns. The failing write occurred while inserting the new user row into `/tmp/zazu-preview.sqlite`.
- The current `docker/render-entrypoint.sh` now changes the SQLite file owner to `www-data` and applies mode `660` after migrations, before Apache starts. This is the intended runtime write-permission correction.
- Render service `zazu-emp-staging` is configured to deploy `main`, uses the preview-only Docker image, and has auto-deploy enabled. The deployment for `ac2df59` reached **LIVE** at 19:04:54Z; its startup logs show the fresh preview database migrations completing and Apache starting.
- **Not yet proven:** a fresh registration/workspace-creation request after that deployment. No successful registration is claimed.
- **Data warning:** the image deliberately uses `/tmp/zazu-preview.sqlite` and ephemeral container storage. It is a disposable preview, not a durable staging or production database. Never use it for customer data. A persistent database and durable private-media storage are required before any real pilot.
- Documentation commits in this cycle did trigger Render auto-deploys because the service is configured for auto-deploy. These were preview rebuilds only; no production service was deployed.

## Exact-head verification update — 2026-11-11 19:11Z

For commit `5719f5254c87cad43f6b74dc6be3af94a3adaf12`, all required automated checks below completed successfully:

- Laravel tests: **PASS**.
- Offline browser acceptance: **PASS**.
- Zazu browser smoke: **PASS** (run `43083900984`, browser job `130743341689`).
- Psalm security: **PASS**.
- Zazu quality and secret scan: **PASS**.
- Populated database upgrade: **PASS**.
- PHPMD: **PASS**.
- Director Contract Lint: **PASS**.
- Push workflow: **PASS**.

The Render preview deployment for this commit is **LIVE**. This is a deployment state, not a successful registration/workspace acceptance. The preview remains disposable and non-durable as documented above.

### Remaining release gates (not waived by CI)

- Confirm a real registration and workspace creation after the SQLite permission correction.
- Replace disposable preview persistence with a supported persistent database and durable private-media storage before any real pilot.
- Complete backup/restore and deployment rollback drills against that persistent target.
- Complete physical phone and tablet acceptance.
- Complete trusted-device/lost-device operational procedure and verify owner revocation on physical devices.
- Establish production licence issuer/private-key custody and approve the entitlement matrix before feature enforcement.
- Complete hosting privacy/log retention controls and legal/brand release review.

**Release decision: NO-GO.** Automated source verification is now green; the remaining blockers are deployment/data durability and human operational acceptance, not a reason to expand feature scope.


## Exact-head security and workbench verification — 2026-11-12

- Runtime/test source commit: `bed3941739cf02fc1a8152932292bc7d7ec68a21`.
- Exact-head Laravel workflow `43181669792`: **PASS** — Laravel tests, PostgreSQL migration/registration/backup-restore smoke, and offline browser acceptance jobs all succeeded.
- Exact-head browser smoke `43181669581`: **PASS**.
- Exact-head Zazu quality/secret scan `43181669747`, populated upgrade/rollback `43181669678`, Psalm security `43181669617`, and PHPMD `43181669720`: **PASS**.
- SonarCloud: **SKIPPED**, not counted as a pass. No Docker build result is attributed to `bed394`.
- Render workbench deployed documentation descendant `c450ed9` successfully; startup migrations completed and internal HEAD / plus GET / returned HTTP 200. This is preview-workbench verification only, not persistent production verification.
- Remaining bounded acceptance: physical installed-device offline workflow, then one final regression pass, known-limitations record and V1 release decision. No speculative gates are added.

## Exact-head CI reconciliation — 2026-11-13 (licensing continuity regression)

- Exact current `main` HEAD: `4086428e9baba002134645c7f13e1b215bb7266c`.
- Runtime/test change: added a licensing continuity regression proving that expiry and signed revocation preserve customer-record access; corrected the test's explicit Customer model import. No production runtime code or deployment configuration changed in this commit.
- Laravel workflow [run 43382234882](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43382234882): **PASS — 467 passed / 3,359 assertions / 1 skipped**.
- PostgreSQL 16 migration/registration and database/private-file backup-restore smoke: **PASS** (jobs `postgres-migration-smoke`; authentication smoke 1 passed / 13 assertions; PostgreSQL backup/restore 1 passed / 5 assertions).
- Offline browser acceptance [run 43382234882](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43382234882): **PASS — 19 passed**.
- Full browser smoke [run 43382234787](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43382234787): **PASS**.
- Populated database upgrade/rollback [run 43382234747](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43382234747): **PASS**.
- Psalm security [run 43382234744](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43382234744): **PASS**.
- PHPMD [run 43382234722](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43382234722): **PASS**.
- Zazu quality + secret scan [run 43382234666](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43382234666): **PASS**.
- Push-on-main [run 43382233637](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43382233637): **PASS**. SonarCloud [run 43382234690](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43382234690): **SKIPPED**, not counted as a pass.
- The continuity regression confirms expired/revoked licence state does not remove the customer's ability to view existing customer records. It does not establish the complete three-tier commercial matrix or premium-feature enforcement.
- No Render service, database, deployment branch or environment setting was changed. `deploy/render-staging` remains untouched.
- Remaining bounded work: owner approval of the three-tier entitlement matrix and final real-device acceptance. No global licence gate is to be added before the entitlement boundaries are defined and tested.

**Automated exact-head verification: GREEN. Commercial entitlement enforcement and physical installed-device acceptance remain OPEN.**
