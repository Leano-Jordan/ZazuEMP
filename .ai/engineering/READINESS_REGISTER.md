## Current engineering reconciliation — 2026-11-28

**Live status authority:** [`.ai/engineering/STATE.md`](STATE.md). **Canonical acceptance ledger:** [`docs/V1_RELEASE_CHECKLIST.md`](../../docs/V1_RELEASE_CHECKLIST.md). This reconciliation supersedes the 2026-11-26 status below.

- **Latest implementation/test candidate:** [`94a69c4`](https://github.com/Leano-Jordan/ZazuEMP/commit/94a69c4af79bc3dc52aae388694e7eb65048023e). It adds the protected deep-link login acceptance case. The preceding implementation adds a fail-closed guard to installation-wide backup/restore when private storage is not local.
- **Exact-head Zazu Quality:** [run 46140230749](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/46140230749) PASS; formatting, application, secret scan and Composer security audit passed. Push-on-main [run 46140231363](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/46140231363) PASS.
- **Exact-head Laravel/browser:** [run 46140230781](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/46140230781) was still running at this reconciliation. Laravel feature/unit tests, coverage and PostgreSQL migration smoke passed; offline browser acceptance remained in progress. Do not claim the complete workflow green until the browser job finishes.
- **Windows package/fresh-install and encrypted/legacy archive verification:** [run 46140157017](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/46140157017) PASS on source-equivalent [`001b82a`](https://github.com/Leano-Jordan/ZazuEMP/commit/001b82a9045c45d45e008ec9f1ab75853cb474c5). Psalm security and PHPMD also PASS on that source head. SonarCloud is skipped, not passed.
- **Business archive restore:** CLOSED / OWNER-ACCEPTED. CASE-ZAZU-0054 records the Founder-confirmed successful restore of the original 2.20 MB encrypted Rosscore archive on 2026-11-26. This supersedes the old unresolved-restore status below.
- **Still open:** intended-PC installation-wide recovery and key custody; physical Android disconnected cold-launch/restart/reconnect; populated quote-to-profit reconciliation; DEC-019 shared-profile risk and independent adversarial security review; POPIA operations and ZAZU name clearance.
- **Formal evidence maturity:** 71/100, unchanged. V1 remains NOT READY. No score uplift is claimed.

---

**Canonical policy:** `docs/ZAZU_ENGINE_DIRECTOR_COMMERCIAL_V1_MANDATE.md` governs commercial positioning, V1 scope, formal scorecard, ordered release gates and the controlled-pilot target. This register is evidence/status, not a competing strategy document. Current-main evidence supersedes older dated checkpoints below.

## Current engineering reconciliation — 2026-11-26

**Live status authority:** [`.ai/engineering/STATE.md`](STATE.md). **Canonical acceptance ledger:** [`docs/V1_RELEASE_CHECKLIST.md`](../../docs/V1_RELEASE_CHECKLIST.md). This current reconciliation supersedes the historical dated checkpoints below.

- **Current application/test candidate:** [`8477f065`](https://github.com/Leano-Jordan/ZazuEMP/commit/8477f065d947375d0fc81c99b04efb313d0299c2) on `main`. It includes responsive offline launchpad layout, crash-atomic server acknowledgement, persistent-storage status/request, quota warning and fail-closed quota-write feedback, plus service-worker cache v62.
- **Latest exact-head Laravel run:** [`45787226043`](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45787226043) — **765 passed / 1 skipped / 5,471 assertions**; coverage PASS; PostgreSQL migration, rollback/reapplication, registration and recovery smoke PASS. Its desktop offline browser and remaining mobile jobs are still in progress at this reconciliation; do not call the complete workflow green until they finish.
- **Storage browser test correction:** predecessor `8cfd530` had 76 browser tests pass and one fixture failure because a mock of `navigator.storage` did not survive page navigation. Commit `8477f06` re-applies that mock on the Dashboard document. Current exact-head browser run remains pending.
- **Zazu Quality:** [`45787226126`](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45787226126) on current candidate — application, formatting, secret scan and Composer security audit PASS.
- **Symfony dependency security:** commit [`43e69e6`](https://github.com/Leano-Jordan/ZazuEMP/commit/43e69e623aafb43e1159e8a0664d729764575844) updates affected Symfony runtime lock entries to 7.4.24. The prior five-advisory Composer failure is resolved; Composer audit passed on both `43e69e6` and current `8477f06`. No audit suppression was added.
- **Supporting same-application-source evidence** (the later commits only change browser test fixtures and documentation): Windows package/fresh install and encrypted/legacy archive verification PASS; populated upgrade/rollback PASS; PHPMD PASS; Psalm security analysis and SARIF publication PASS; Push-on-main static analysis PASS. SonarCloud is skipped, not passed.
- **Offline acceptance still open:** exact-head browser run must finish; physical Android PWA review must verify the corrected mobile header, storage grant/quota panel, and v61 → v62 update preserving the authenticated business workspace. Force-close/restart and reconnect remain distinct physical checks.
- **Other release gates remain:** owner's encrypted business restore, intended-PC full installation recovery, populated quote-to-payment-to-profit reconciliation, trusted-device shared-profile risk, POPIA operations and ZAZU name clearance.

**Formal evidence maturity remains 71/100; no uplift is claimed. V1 remains NOT READY.**


---
## Latest Director engineering evidence — 2026-11-23

- CASE-ZAZU-0046 / ENG-JS-003 CLOSED: offline service metadata survives deferred image upload; image bytes are not queued.
- CASE-ZAZU-0047 / ENG-CONTRACT-008 CLOSED: Playwright service category selection no longer fails behind sticky chrome; production UI was not changed for the test failure.
- CASE-ZAZU-0048 / ENG-SEARCH-001 CLOSED: customer search includes active linked contact name, phone, email and label; deleted and cross-business contact regressions pass.
- Exact current-head Laravel: **745 passed / 1 skipped / 5,244 assertions**. Zazu Quality, PHPMD, PostgreSQL migration/recovery and Director Health PASS. Browser acceptance and Windows package checks pending at this checkpoint; SonarCloud skipped.
- Formal score remains **71/100**; no score uplift. V1 remains NOT READY.

---

## Latest Director verification — offline customer metadata/photo boundary — 2026-11-23

**Verified main head:** `c57bbd7233a325ed34deb9129f93fa0c9f7b6236`.

- CASE-ZAZU-0045 / ENG-JS-002 CLOSED: offline customer metadata is queued even when profile-photo upload/removal is deferred; no file bytes or removal intent are sent in the offline mutation.
- Laravel: **744 passed / 1 skipped / 5,230 assertions**.
- Offline browser: **64 desktop + quote single-unit + 7 mobile viewport + host-outage + phone-local attachment passed**.
- PostgreSQL migration/recovery and Zazu Quality PASS; Windows package/fresh install PASS on runtime-identical app code `51f809ce`. SonarCloud skipped.
- Formal 13-domain score remains **71/100**; no uplift.
- Release gates unchanged: installed encrypted business restore, full installation recovery, physical Android offline cold-launch/restart/reconnect, populated live-business quote-to-profit reconciliation, POPIA operational sign-off and ZAZU name clearance.

---
## Director audit reconciliation — 2026-11-23

**Current source head:** `620ab99b81b0f3237cae0a0fe053d35b2e83a1cf`.  
**Full audit:** [docs/DIRECTOR_WIDESPREAD_AUDIT_2026-11-23.md](../../docs/DIRECTOR_WIDESPREAD_AUDIT_2026-11-23.md).

- Owner-verified PASS: connected PC normal use; connected linked-phone normal use; shared workspace; populated fictional customer/quote/invoice/supplier/PO/inventory records; installed DB migration check; installation and business backup presence/download plus independent verification.
- Owner-tested but failed: encrypted business archive restore against disposable empty workspace. The initial failure stage is not conclusively identified; later SYS-002/429 is rate limiting, not the root cause.
- Corrected on main: Windows PHP host now explicitly sets 100M upload / 110M POST and 300-second execution/input limits; restore audit writes are now inside the restore transaction with rollback/file cleanup on audit failure.
- Maintainability: onboarding flow and archive encryption stream responsibilities split into focused classes; obsolete encryption helpers removed. PHPMD passed on `2d7b1146`; final route wiring is on `620ab99b`.
- Exact-head Laravel passed (744 passed, 1 skipped, 5,230 assertions); PostgreSQL migration/recovery, Zazu Quality, Psalm, Windows package/fresh install/archive, populated upgrade/rollback, Director Health and Push-on-main passed on `22300930`. Offline browser acceptance was still running at this checkpoint; SonarCloud skipped.
- Formal 13-domain evidence-maturity score remains **71/100 (46/65)**; 77/100 in older hardening notes is not the canonical recalculated score. V1 remains **NOT READY**.

Do not repeat connected startup, populated-record creation, or backup verification. Keep offline physical acceptance, successful business restore, full installation recovery, populated quote-to-profit reconciliation, POPIA and ZAZU clearance separate.

---

## POPIA documentation and data-minimisation checkpoint — 2026-11-23

**Documentation refreshed on main:**
- Updated the data-subject procedure to distinguish objections (Form 1), correction/deletion (Form 2), record access through PAIA/POPIA, and customer-managed records where Zazu is an operator. It uses the owner-approved provisional contact channel but does not claim a self-service portal.
- Updated the privacy notice draft with hosted/local role distinctions, a provisional contact disclosure, the lack of self-service deletion, and minimal handling of allergy/dietary information.
- Updated the incident runbook to remove the obsolete regulator-channel placeholder and retain the current eServices process, immediate operator escalation and no-low-risk-threshold handling.
- Updated PAIA and operator-control drafts so requests are routed by responsible-party role and operator templates are not represented as executed contracts.
- Added `docs/RELEASE_PRIVACY_OPERATIONS_REGISTER_TEMPLATE.md` for data-subject request, security-compromise and provider inventories. The working register must be stored outside GitHub; no personal information or completed register entries belong in the repository.
- Updated the privacy engineering baseline and legal register against the 2025 amended POPIA Regulations and the scoped 2026 health/sex-life information Regulations.
- Added minimal data-entry guidance on Job notes and requirement Special details: record only the operational instruction needed for the event; avoid diagnoses and medical history. `PrivacyDataMinimisationCopyTest` covers both surfaces.

**Release disposition:** these changes improve documentation and user guidance; they do not provide a DSAR/export/deletion portal, automated retention/deletion scheduler, executed operator agreements, registered Information Officer evidence or legal sign-off. The operational POPIA gate remains OPEN. Do not claim compliance certification.

## Founder installation and business backup verification — 2026-11-23

Founder reports the following live owner-PC checks while starting the Zazu server:

- Startup database check displayed: “The Zazu database is up to date.” Record this as **OWNER-VERIFIED database migration check PASS** for the installed environment at this observation.
- The latest installation-backup file is visible on the PC; the Zazu backup UI shows it as approximately **7 hours old** at the time of the observation.
- A business-level backup was downloaded.
- Founder independently verified the installation and business backup files.

**Recorded status:** installation backup present and independently verified — OWNER-VERIFIED PASS; business archive downloaded and independently verified — OWNER-VERIFIED PASS; installed database reports no pending migrations — OWNER-VERIFIED PASS.

**Still separate / NOT CLOSED:** a file existing and being independently verified does not prove that the intended installation can be fully restored. The controlled owner-PC restore drill, including database, configuration/key handling, private media and post-restore startup/workspace verification, remains OPEN until a restore is actually performed and checked. Preserve the existing installation and backup files; do not ask the Founder to repeat checks already completed.

## DB-001 disposition — Founder clarification (2026-11-22)

**DORMANT / HISTORICAL / NON-CRITICAL — not an active engineering task or V1 release blocker.** The Founder clarifies that the DB-001 occurrence under discussion happened while testing offline event creation and a calendar-related task. It has not recurred since the subsequent offline-architecture work. It is not a confirmed current database defect, and no root cause or fix is claimed.

Do not chase this historical anomaly, request diagnostic captures, or ask the Founder to run `Test-ZazuDatabaseSchema.ps1` or `Get-ZazuIncident.ps1`. Reopen only if the same failure is reproduced in a supported workflow on current `main`, or materially new diagnostic evidence appears. The separate installation backup/restore acceptance gate remains independent and must not be conflated with DB-001.

## Offline cache priming lifecycle — exact-head automated acceptance PASS — 2026-11-22

**Runtime implementation head:** [b9f9a14](https://github.com/Leano-Jordan/ZazuEMP/commit/b9f9a146c5a1310ef75e1f3edf5a341851be005b).  
**Latest source/test head:** [a0baea5](https://github.com/Leano-Jordan/ZazuEMP/commit/a0baea5901b139b1c7397a12d780af245841d3ce).

- Root cause: repeated page boots aborted an in-flight same-workspace cache-prime task; the next page boot restarted it and the client reported the interruption as a cache failure. Logout could also leave a stale task reference that a later same-workspace prime might join.
- Fix: same-context requests share the active prime; navigation no longer aborts it; fetched route HTML and its static/private assets are refreshed; clearing the active workspace aborts and releases the stale task reference. Service-worker cache is now `zazu-static-v61`.
- Laravel: **691 passed / 1 skipped / 4,794 assertions**; PostgreSQL migration/recovery smoke **PASS**.
- Offline browser exact-head [run 45052231006](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45052231006): **63 desktop Chromium + 7 mobile viewport + 1 mobile host-outage + 1 phone-local attachment/reconnect passed**; public/auth accessibility acceptance **1 passed**.
- Zazu Quality [45052230840](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45052230840), PHPMD [45052230971](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45052230971), Director Health [45052230859](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45052230859) and Push-on-main [45052231209](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45052231209): **PASS**. SonarCloud: **SKIPPED**, not counted as a pass.
- Windows V1 package/fresh-install [45052196760](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45052196760): **PASS** on the matching runtime implementation head.
- **Physical acceptance seed: PRESENT and runbook READY.** The Founder-confirmed fictional workspace already contains the required customer, quote, invoice, supplier, purchase-order and inventory records, with PC and Android linked to the same workspace. Use the prepared sequence in `docs/V1_RELEASE_CHECKLIST.md`; do not recreate or duplicate seed records.
- Physical Android cold-launch, host-stop, offline write, force-close/restart and reconnect remains **OPEN** until the Founder performs the actual-device run. Owner-PC off-disk recovery, live populated-business financial reconciliation, POPIA operational sign-off and ZAZU name clearance remain separate release gates.

**Evidence maturity:** remains **77/100**. No readiness uplift is claimed from automated results alone. V1 remains **NOT READY**.


## Closed security checkpoint — stale device credential writes — 2026-11-21

- Mutation application revalidates the original authenticated credential and mutation business/device ownership inside the same transaction as the domain write.
- Conflict recording now revalidates the same credential under the business-first/device-row guard; stale requests cannot create conflict state after device re-pair.
- Bootstrap, pull and acknowledgement now share the same guard, removing duplicated credential-check implementations.
- Adversarial regressions prove stale apply returns 401 without applying/delivering and stale conflict recording returns 401 without a conflict row.
- Exact-head [04db872](https://github.com/Leano-Jordan/ZazuEMP/commit/04db8722a66ff8c97d62fef855f7ba07073934c7): Laravel **675 passed / 1 skipped / 4,581 assertions**; PostgreSQL migration/recovery smoke and desktop/mobile browser acceptance **PASS** (63 desktop, 7 mobile, 1 LAN host-outage, 1 phone-local attachment/reconnect; no flaky retries); PHPMD, Psalm, Zazu Quality, Director Health, populated upgrade/rollback, Windows package/fresh install and Push-on-main **PASS**. SonarCloud skipped.
- Windows owner-PC backup automation now has runtime CI on [db5f25e](https://github.com/Leano-Jordan/ZazuEMP/commit/db5f25e1f6b54fb28909738508ef935792a419cb): the workflow registers and inspects a real temporary Task Scheduler task under Windows PowerShell 5.1 (S4U, Limited, daily 02:00, catch-up, wake, IgnoreNew), executes the backup runner, verifies local/secondary retention of 14, SHA-256 equality, and confirms a failed secondary destination preserves the previous successful timestamp. [Windows Ops workflow 44941196865](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44941196865) PASS. This is CI evidence, not the actual owner-PC observation/off-disk restore drill.
- CASE-ZAZU-0040 CLOSED at automated-test/source level. Readiness score unchanged; physical Android, owner-PC recovery, live-business commercial, POPIA and name-clearance gates remain open.


## Director execution checkpoint — offline host detection + recovery archive hardening — 2026-11-21

**Current source/test head:** [d716d5d](https://github.com/Leano-Jordan/ZazuEMP/commit/d716d5da615335c1afb3509867631c42b416ffbc) on main.

### Verified
- Laravel: **673 passed / 1 skipped / 4,575 assertions**. This includes shared backup/restore limit boundary tests and the empty-private-storage restore integration test. [Exact-head Laravel run 44937037910](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44937037910)
- PostgreSQL migration/recovery smoke: **PASS**.
- PHPMD, Zazu Quality, Composer security audit, secret scan and Director Health: **PASS**.
- Exact-head offline acceptance on [d716d5d](https://github.com/Leano-Jordan/ZazuEMP/commit/d716d5da615335c1afb3509867631c42b416ffbc): **63 desktop Chromium + 7 mobile viewport + 1 Pixel 7 host-outage + 1 phone-local attachment/reconnect passed**, no failures or flaky retries. [Workflow 44937037910](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44937037910).
- Windows V1 package/fresh-install plus Windows-native populated and empty private-storage restore passed on [workflow 44937732880](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44937732880), packaging the same runtime source head [d716d5d](https://github.com/Leano-Jordan/ZazuEMP/commit/d716d5da615335c1afb3509867631c42b416ffbc). Populated upgrade/rollback passed on earlier runtime code head [84dba32](https://github.com/Leano-Jordan/ZazuEMP/commit/84dba32ae96ececf6c1dd71034d79d0b93070b7e).
- CASE-ZAZU-0037 (LAN host outage UI), CASE-ZAZU-0038 (backup/restore archive limit drift) and CASE-ZAZU-0039 (empty private-storage restore retaining stale files) are closed at code/automated-test level.

### Remaining release gates — still NOT READY
- Physical Android installed-app cold launch, stop-host while Wi-Fi remains connected, disconnected work, force-close/restart and reconnect.
- Actual owner-PC off-disk backup and controlled full installation restore, including representative private media.
- Populated live-business quote-to-payment-to-profit reconciliation.
- POPIA operational sign-off and ZAZU name clearance.

**Evidence maturity remains 77/100. No score uplift is claimed from simulated mobile or code-only evidence.** SonarCloud is skipped and is not counted as a pass.

---

## Director execution checkpoint — event-cost offline bootstrap correction — 2026-11-21

**Application/test code head:** `86f03861cd640aaefa2a6b08f022d0a5b1f17e45` on `main`.  
**Implementation:** [4971ce1](https://github.com/Leano-Jordan/ZazuEMP/commit/4971ce12c7c968edf5fcdae4d269f4c0bb079aa2) adds the missing explicit device trust + workspace sync + server-backed Work wait to the actual failing event-cost test. [86f0386](https://github.com/Leano-Jordan/ZazuEMP/commit/86f03861cd640aaefa2a6b08f022d0a5b1f17e45) removes diagnostic logging that had been added to an unrelated test.

**Root cause:** the event-cost test deferred initial sync, waited only for local IndexedDB readiness, and then assumed a server-backed Work record had already been hydrated. The earlier attempted correction modified a different test, which is why the failure persisted. No production bootstrap defect has been established by this assertion.

**Verification:** previous exact run [44908353460](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44908353460) still failed at the event-cost test's missing-job assertion; Laravel and PostgreSQL jobs passed. No Actions run or commit status was available yet for the correction commit when this checkpoint was written. Exact-head offline browser verification is **PENDING**; the original event-cost acceptance assertions remain unchanged.

**Evidence maturity:** **77/100, unchanged**. No score uplift. V1 remains **NOT READY**.

---

## Director acceptance checkpoint — 2026-11-21

**Current source/test head:** `e45cab97cbcdbc3f989bf3843d751bb0f373c305` on `main`.  
**Laravel + browser + PostgreSQL:** [44904393629](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44904393629) · **Zazu Quality:** [44904393468](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44904393468) · **PHPMD:** [44904393461](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44904393461) · **Director Health:** [44904393452](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44904393452) · **Push-on-main:** [44904393426](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44904393426). SonarCloud [44904393462](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44904393462) was skipped.

- Laravel: **653 passed / 1 skipped / 4,495 assertions**.
- Offline browser: **61 desktop Chromium + 7 mobile viewport + 1 phone-local attachment journey passed; no flaky retries**.
- PostgreSQL migration/registration/backup/restore/private-file recovery smoke: **PASS**.
- Zazu Quality, Composer security audit, secret scan, PHPMD, Director Health and Push-on-main: **PASS**.
- Offline payment and expense reconciliation have server-side and browser acceptance. Expense acceptance preserves job/supplier/purchase-order identities, canonical server identity, audit entry and idempotent replay.
- Business archive restore rejects a workspace with existing trusted-device state before importing rows/files.
- A simulated mid-restore private-file write failure proves database rollback and cleanup of the file already written.
- Expired-session browser acceptance verifies the sign-in gate while preserving the trusted offline queue; it no longer assumes the service worker must change the URL during cached-shell fallback.
- Current service-worker static cache: `zazu-static-v60`.

**Evidence disposition:** evidence maturity remains **77/100**, unchanged. These test closures improve confidence but do not replace physical Android, intended-PC recovery, live populated-business commercial and POPIA operational acceptance. V1 remains **NOT READY**.

**Next engineering target:** adversarially verify business isolation for offline financial references, especially foreign supplier/event/purchase-order identities supplied in an expense mutation.

---

## Director acceptance checkpoint — 2026-11-21

**Current source/test head:** `b26df40e150fc3b81425db81fe9cb16040970547` on `main`.  
**Laravel + browser + PostgreSQL:** [44903681914](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44903681914) · **Zazu Quality:** [44903681940](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44903681940) · **Push-on-main:** [44903681684](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44903681684).  
**PHPMD / Director Health:** passed on immediately preceding PHP-identical source head `ed201e098892bce4a86b5383585616a31f26c589` ([PHPMD 44903058798](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44903058798), [Director Health 44903058803](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44903058803)). The b26 change after that head is browser-test-only.

- Laravel: **652 passed / 1 skipped / 4,487 assertions**.
- Offline browser: **61 desktop Chromium + 7 mobile viewport + 1 phone-local attachment journey passed; no flaky retries**.
- PostgreSQL migration/registration/backup/restore/private-file recovery smoke: **PASS**.
- Zazu Quality, Composer security audit, secret scan and Push-on-main: **PASS**. SonarCloud: **SKIPPED**.
- Existing offline payment and expense flows now have server and browser reconciliation coverage. Expense acceptance preserves job/supplier/purchase-order identities, canonical server identity, audit record and idempotent replay.
- Business archive restore now has a regression proving a target with trusted-device state is rejected before imported records or files are written.
- Expired-session browser acceptance now tests the actual authentication barrier (visible sign-in modal) while preserving the trusted offline queue; it no longer assumes the service worker must change the URL when its cached-shell fallback is used.
- Current service-worker static cache: `zazu-static-v60`.

**Evidence disposition:** evidence maturity remains **77/100**, unchanged. These closures improve verification confidence but do not replace the open physical Android, intended-PC recovery, live populated-business commercial and POPIA operational acceptance gates. V1 remains **NOT READY**.

**Next engineering target:** prove business archive restore rollback and file cleanup when a private-file write fails partway through restoration.

---

## Director operational reliability checkpoint — 2026-11-21

**Latest code/operations verification head:** 2d0269b00a3a94ecfb392f04c06438ece6eda603 on main.  
**Application/test source head:** 2d0269b00a3a94ecfb392f04c06438ece6eda603. **Windows host-operations source head:** 365609b4d30ca4978ba9480cf3571bb792ad442b.

- Laravel: **649 passed / 1 skipped / 4,456 assertions**. [Exact-head run 44892813216](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44892813216)
- PostgreSQL migration/recovery smoke: **PASS**; full Laravel suite remains SQLite-backed.
- Offline browser: **59 desktop Chromium + 7 mobile-viewport + 1 phone-local Work/attachment/reconnect passed** in the exact-head run.
- PHPMD: **PASS** [run 44892813263](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44892813263). Psalm: **PASS** [run 44892813233](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44892813233). Zazu Quality: **PASS** [run 44892813235](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44892813235). Populated upgrade/rollback: **PASS** [run 44892813238](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44892813238).
- Windows V1 package and fresh-install PowerShell acceptance: **PASS** [run 44892813237](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44892813237). Director Health: **PASS** [run 44892813214](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44892813214). SonarCloud was skipped.

### Implemented
- Windows host startup now installs/refreshes an idempotent daily 02:00 Task Scheduler backup task. It runs under S4U without storing the Windows password, catches up after missed runs, and writes zazu:backup archives to the protected storage/app/zazu-backups/daily directory.
- The runner retains the newest 14 successful archives and records bounded scheduler logs. Startup warns if no archive exists or the latest is more than 26 hours old; both the business-owner dashboard and Founder Control Centre display missing/stale backup warnings. An optional independent USB/synced-folder destination is copied and SHA-256 verified by the scheduled runner, with bounded retention and visible failure status.
- The restore runbook requires a separate USB/cloud-folder copy. Same-PC backups are not disk-failure protection.
- sync_devices.approved_by_user_id records the owner who approved a trusted device. Sync audit entries use that owner as actor, even if a staff web session is present; Settings shows the approver, offers explicit owner re-approval for active legacy devices with no inferred historical actor, and the audit screen labels trusted-device activity as approved by the recorded owner.
- The accepted limitation is explicit: a trusted device is a shared business-scoped workspace, not per-staff authentication. Normal staff permissions are not rechecked per offline mutation.
- A regression guard now rejects query-builder mass writes to revision-tracked aggregate tables. New registrations and password resets require 10 characters; no internet-dependent breached-password lookup is used. Daily log rotation is applied to existing installs at startup.
- The Windows launcher clears an existing Laravel config cache when daily logging is selected or HTTPS-only cookies are enabled, before the server starts. Windows Ops CI checks the cache-clear ordering so a stale cache cannot mask the `.env` logging policy.
- The backup-health refactor now keeps the owner-facing USB/cloud guidance and explicit unconfigured-destination warning while splitting health aggregation into PHPMD-compliant methods. Exact-head Laravel and PHPMD both pass.

### Still open — do not mark V1 ready
- Run the scheduled backup on the actual owner PC, copy an archive off-disk, and complete the controlled restore drill.
- Physical Android installed-app cold launch, restart, disconnected operation and reconnect acceptance.
- Live business-record quote-to-payment-to-profit reconciliation.
- POPIA operational sign-off and ZAZU name clearance.
- PostgreSQL commercial/sync feature testing is deferred while the V1 host remains the owner's SQLite PC. It becomes a pre-pilot gate if a PostgreSQL host is selected.

**Evidence maturity remains 77/100. No score uplift is claimed from code changes alone. V1 remains NOT READY.**

---

## Director closure checkpoint — 2026-11-21 (CASE-ZAZU-0033 stale-write protection)

**Verified source/test head:** `51a75eb1d06017211bd7808093d1863c930b6d71` on `main`. Laravel **634 passed / 1 skipped / 4,399 assertions**; PostgreSQL migration/recovery **PASS**; offline browser **59 desktop + 7 mobile + 1 phone-local Work/attachment/reconnect passed**; PHPMD, Zazu Quality, Composer audit, secret scan and application checks **PASS**. [Exact-head workflow](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44881431689).

CASE-ZAZU-0033 / ENG-DATA-003 is **CLOSED**. The stale-write contract now has paired-device rejection, same-device causal chains, conflict/rebase payload preservation, parent-revision propagation and business-isolation evidence. The final online writer review found and corrected the remaining primary-contact creation mass-update bypass; other identified bulk operations are paired with tracked parent updates or are restore/protocol writes outside existing domain identities.

**The full offline sync workflow matrix remains OPEN and separate.** Physical Android cold launch/restart/reconnect, intended-installation recovery, live-business commercial reconciliation and POPIA operational sign-off remain OPEN. Evidence maturity: **77/100 (+2)** for verified stale-write prevention across paired devices and the corrected online writer path. V1 remains NOT READY. V1 remains **NOT READY**.

---

## Director source-audit increment — 2026-11-21 (primary-contact creation)

- Confirmed and corrected a remaining online revision bypass in `CustomerContactController::store`: creating a new primary contact previously mass-updated the existing primary via a relationship query, bypassing nested Eloquent events.
- The controller now locks and updates existing primary contact models individually; a new feature regression asserts the parent Customer sync revision advances and the primary-contact invariant remains intact.
- Exact-head Laravel on `51a75eb` passed **634 / 1 skipped / 4,399 assertions**; PostgreSQL migration/recovery and PHPMD passed. Desktop/mobile offline-browser acceptance passed (59 desktop, 7 mobile and 1 phone-local attachment/reconnect). The new two-device stale-write regression is included in that Laravel pass. CASE-ZAZU-0033 / ENG-DATA-003 is CLOSED on exact-head evidence. The complete offline sync workflow matrix remains OPEN as a separate release gate. No score uplift; V1 remains NOT READY.

## Director disposition refresh — 2026-11-21 (receiver sync + bootstrap credential fence)

- **Application/test source head:** `559e66b87de8617f967b722b55b0fbfd9b116495`.
- **Laravel:** 632 passed / 1 skipped / 4,388 assertions. **PostgreSQL recovery smoke:** PASS.
- **PHPMD, Zazu Quality, Composer audit, secret scan, application, Director Health, Push-on-main and JavaScript/TypeScript/Python/Actions analysis:** PASS. SonarCloud skipped.
- **Offline browser:** 59 desktop Chromium passed; 7 mobile-viewport passed; 1 phone-local Work/attachment/reconnect journey passed (Laravel run 44880113367).
- **Receiver completeness correction:** targeted bootstrap sequence inclusion, revision-ranked normal windows, business/device scoping and explicit-pairing selection preservation are implemented. CASE-ZAZU-0034.
- **Bootstrap authentication correction:** stale device snapshots are rejected after revocation or bearer-token replacement under business-first locking. CASE-ZAZU-0035.
- **Disposition:** V1 NOT READY. Evidence maturity is **75/100 (+2)** for the verified receiver-completeness and bootstrap-auth controls; this does not close broader sync parity or owner acceptance.

---

## Director disposition refresh — 2026-11-21 (receiver sync completeness)

- **Application/test source head:** `472e301065a5301c80f0ffbb97f82b61c24575ba`.
- **Verification status:** exact-head Laravel, PostgreSQL recovery, offline-browser, PHPMD and quality workflows are pending; no pass is claimed for this source head until results finish.
- **Confirmed receiver defect:** the previous receiver flow could acknowledge delivery sequences after applying a bounded bootstrap that omitted a delivered older aggregate. A similar freshness risk existed for online updates because collection limits followed static display ordering.
- **Correction:** client sends exact delivery sequences with bootstrap; server resolves target primary/related aggregates using business + destination-device scoping and includes them beyond normal collection limits; recent aggregate revisions are prioritized in the ordinary bounded window; client only acknowledges after local snapshot persistence. Static offline client cache advanced to v58.
- **Regression coverage:** online revision promotion; explicit delivery inclusion outside 500; non-recipient device isolation; commercial second-device snapshot; receipt/inventory related aggregates; browser pull → bootstrap → ack ordering.
- **Failure register:** CASE-ZAZU-0034 / ENG-DATA-004. CASE-ZAZU-0033 remains open separately for full writer audit and stale-write matrix.
- **Disposition:** NOT READY. Evidence maturity stays **73/100** pending exact-head verification; physical Android, intended-installation recovery, live-business commercial reconciliation and POPIA owner acceptance remain open.

---

## Director disposition refresh — 2026-11-20

- **Verified code SHA:** `041314fd80147758dd2edb33c187b16328aea938`. Exact-head Laravel [44810699348](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44810699348): 623 passed / 1 skipped / 4,283 assertions; PostgreSQL migration/registration/backup/restore/private-file recovery smoke PASS; desktop offline browser 57 passed; mobile viewport 7 passed; dedicated phone-local Work attachment journey 1 passed. PHPMD [44810699344], Zazu Quality [44810699254], Director Health [44810699270] and Push-on-main [44810699529] PASS. SonarCloud skipped.
- **Offline session/device transition: AUTOMATED ACCEPTANCE PASS** on this exact SHA. Real-device and operational lost-device checks remain separate.
- **Active Work attachment flow: AUTOMATED ACCEPTANCE PASS** on this exact SHA, including the dedicated mobile-viewport Work-create/attachment/reconnect journey. Physical-phone force-close/restart and persistence remain OPEN.
- **Revision-aware stale-write protection: IMPLEMENTED.** The command-parent regression passes for quote acceptance → Work, payment → invoice, purchase receipt → purchase order/inventory, inventory movement → inventory item and asset allocation → asset. Full V1 sync matrix and complete runtime replay/reconciliation acceptance remain OPEN.
- **DB-001: DORMANT / MONITORING**, based on the Founder's report of no recent recurrence. Root cause remains unproven; do not claim a fix or use active engineering time unless it recurs/new evidence appears. **Installation recovery remains OPEN independently.**
- **Commercial reconciliation:** seeded automated traversal is covered; real-business record reconciliation remains OPEN. Tested values are R11,500 invoice incl. VAT; R3,450 deposit; R8,050 final payment; R10,000 net sales excl. VAT; R2,800 actual cost; R7,200 indicative contribution (72%).
- Bounded writer audit has inspected the relevant root/nested hooks and customer/contact, Work, service/capability, supplier, quote, finance, purchasing, inventory and asset write paths. No additional confirmed revision bypass found in those inspected paths; this is not exhaustive runtime proof.


# Zazu EMP — V1 Readiness Register

Last verified application-code baseline: 559e66b87de8617f967b722b55b0fbfd9b116495
Release disposition: NOT READY
Evidence maturity: 75/100 (+2) for exact-head receiver reconciliation and bootstrap authorization evidence; V1 remains NOT READY.

## Director source audit refresh — 2026-11-20

During the 2026-11-20 source audit, documentation head `91f41ec` passed Rosscore Director Health, Zazu Quality, Director Contract Lint and Push-on-main workflows. The exact-head quality workflow covers locked npm audit, service-worker auth/cache-race checks, Composer security audit and secret scanning; it is not the Laravel/offline acceptance suite. Latest full app test evidence remains on application-code baseline `526bdf3` (Laravel 607/1 skipped/4,158 assertions; PostgreSQL recovery; offline browser 51 desktop + 6 mobile + 1 phone-local attachment journey).

The source-level path map for all 17 offline entity types is recorded in `docs/V1_RELEASE_CHECKLIST.md`. Static mapping is complete; runtime replay/reconciliation acceptance remains OPEN. The online/offline writer trace in `.ai/engineering/STATE.md` is PARTIAL: Eloquent aggregate and nested-model event hooks are present, but every indirect writer, bulk update and transaction boundary still needs exhaustive confirmation. One concrete bypass in customer-contact primary promotion was corrected by replacing a relationship mass update with locked per-model updates; a feature regression now asserts that the parent customer revision advances even when the selected primary itself is unchanged. Exact-head acceptance is pending.

**Revision-aware stale-write protection is now implemented on `main`.** Offline update/upsert mutations carry an expected aggregate revision or a same-device predecessor mutation ID. The server checks the revision under the existing business/device/mutation lock order, rejects stale writes as `stale_record_version` with the current remote snapshot, and supports keep-local rebasing through a new immutable mutation ID or use-server restoration. Parent revisions and related identities are advanced for command effects. This replaces the previous missing-base-version implementation finding; it does **not** close the sync gate until all online writers are audited and end-to-end acceptance passes.

**Latest evidence at this refresh:** Laravel on `5b56a7c` passed 621 tests / 1 skipped / 4,271 assertions; PostgreSQL migration/registration/backup/restore smoke passed. Offline-browser acceptance on that commit had 56 passes and one failure: after the last conflict was discarded, Settings hid the success message with the now-empty conflict list. The UI visibility correction is committed as `f50fcb1`; exact-head Laravel/browser and Windows package runs are still in progress. Zazu Quality, Director Contract Lint and Director Health passed on `f50fcb1`; SonarCloud was skipped. Do not infer exact-head browser acceptance from these results.


## Owner-approved V1 scheduling addition — 2026-11-17
Event scheduling and connected rental logistics are explicitly authorised for V1: multi-day event dates, start/end times, setup/collection dates, schedule clash warnings, and rental allocation defaults using the event logistics window. Staff scheduling, quantity-based rental stock and wider workforce management remain deferred. Implementation is on `main` at code baseline `71c8fc5c7aeba392b95e26aee694945b286f2c8e`; Laravel 532/1 skipped/3,714 assertions and PHPMD pass. Windows package + PowerShell install/recovery pass on 2ef2a0b; PostgreSQL migration/recovery and offline-browser acceptance PASS at exact application-code baseline 71c8fc5 in run 44152431508.

## Owner-directed offline sync reliability gate — OPEN (2026-11-20)

The Founder has explicitly added end-to-end offline sync quality across the supported V1 workflow matrix as a release target. This is distinct from successful phone pairing and from the separate PC/server-independent phone launch gate. Pairing remains CLOSED by Founder confirmation.

**Required evidence:** trace every declared offline operation from UI through business-scoped IndexedDB, durable queue, mutation handler and stable identity registry, then through server acknowledgement/pull/bootstrap and final client reconciliation. Verify restart durability, transient and partial failure retries, duplicate/reordered delivery idempotency, parent/child references, pagination/cursors, business/device isolation, conflict preservation and resolution, attachment byte/metadata integrity, and financial/inventory/purchase/scheduling lifecycle parity. Verify representative journeys on the actual PC and phone after automated coverage passes.

**Revision-aware integrity implementation (2026-11-20):** offline update/upsert mutations now carry a server-enforced expected aggregate revision or a predecessor mutation ID for same-device causal chains. Stale writes are rejected with a `stale_record_version` conflict and authoritative remote snapshot. Keep-local creates a new immutable mutation ID against the latest revision; use-server restores the authoritative record. Parent/related revisions are advanced for consequential command effects. `SyncRevisionConcurrencyTest` covers stale cross-device writes, sequential local chains, intervening online writes, rebasing and related aggregate effects. The remaining gap is exhaustive coverage of every online/indirect/bulk writer and end-to-end workflow acceptance; do not downgrade this to timestamps-only or last-write-wins.

**Latest full application evidence before the current UI correction:** Laravel PASS — 621 passed / 1 skipped / 4,271 assertions; PostgreSQL migration/registration/backup/restore/private-file recovery PASS on `5b56a7c`. Offline-browser acceptance on that commit: 56 passed, 1 failed at the final Settings conflict-success visibility assertion. The correction is on `f50fcb1`; its exact-head acceptance is pending. Do not carry forward the older 526bdf3 test counts as the latest evidence.

**Verified sync changes:** failed mutations/open conflicts now block destructive bootstrap replacement; canonical IDs reconcile for `event_cost`, nested `payment`, `inventory_movement` and `asset_allocation`; disconnected stock receipts update local inventory balance/history; a final offline invoice payment marks the local invoice paid; retryable attachment failures are included in the existing backoff scheduler while preserving the idempotency key. Targeted browser regressions cover these paths.

**Acceptance remains OPEN:** revision-aware stale-write protection is implemented and has server-side regression coverage, but broad sync parity, exhaustive writer/transaction audit and exact-head offline-browser acceptance remain open. Physical Android standalone offline launch/restart/reconnect, installed-target recovery, live commercial reconciliation, POPIA operations and ZAZU name clearance also remain open. Pairing remains CLOSED by Founder confirmation. Release remains NOT READY; evidence maturity remains 73/100 with no uplift.

**Scope boundary:** no parallel offline engine, no cache-only workaround, no weakening of business isolation/idempotency/conflict tests, and no unsupported app-wide offline claim. Work within the existing offline client, mutation protocol, domain handlers, identity registry, attachment subsystem and documented offline workflow matrix.

## Dormant incident record — DB-001 (Founder disposition clarified 2026-11-22)

**Status:** DORMANT / MONITORING — no recent recurrence reported by Founder; cause remains unproven.  
**Owner-reported references:** `2bc77a48-5c6c-4095-afe2-fa079f60e4ba`, `aa9080f4-9580-41c9-a643-439b31928ffa`

Founder clarification: the DB-001 occurrence under discussion happened while testing offline event creation and a calendar-related task. It has not recurred since subsequent offline-architecture work. Earlier summaries that described it as a current account/workspace-save failure are superseded; do not use those summaries to infer a separate active defect.

**Historical source context only:** Work scheduling uses columns introduced by migration `2026_11_17_000001_add_event_schedule_fields.php`, and Windows startup migration safeguards have since been strengthened. This does not establish the cause of the historical test-time DB-001. Local schema drift was an unconfirmed hypothesis, not a verified root cause or current finding.

**Correction on main:** Windows startup now checks migration status. If migrations are pending, it creates and verifies a full installation backup containing the database and private files before applying them, then checks that all migrations completed. Startup stops if backup, migration or final status verification fails.

**Current handling:** no active investigation, no diagnostic script requested, and no owner action required for DB-001. Reopen only after reproducible recurrence on current `main` or materially new diagnostic evidence. No root cause or runtime fix is claimed. The separate installation-recovery gate still requires a controlled drill on the intended installed target.

## Existing release gates
| Gate | Disposition | Closure evidence |
|---|---|---|
| Offline session/device transition | AUTOMATED ACCEPTANCE PASS | Exact-head 041314f desktop session-transition and server revocation tests; actual phone/lost-device operational acceptance remains separate |
| Active Work attachments | AUTOMATED ACCEPTANCE PASS | Exact-head 041314f desktop/mobile-viewport queue, retry, reconnect, idempotency, private-file and directory-row journey; physical phone cold restart remains open |
| Offline sync reliability | IMPLEMENTED / FULL MATRIX OPEN | Exact-head command-parent revision regression passes; complete replay/reconciliation across every supported offline operation remains required |
| Physical offline device | OPEN | Actual Android installed-app cold launch, disconnected restart, local continuity and reconnect |
| Installation recovery | OPEN — CI supporting proof added | Windows V1 Package run 82 on fd82f6a passes Windows PowerShell first install and Windows-native SQLite + private-media backup/mutate/restore; still requires the controlled drill on the intended installed target, with representative data, media, authorization, audit and migrations |
| Commercial reconciliation | SEEDED AUTOMATED PASS / REAL-BUSINESS OPEN | Current test reconciles R11,500 invoice incl. VAT, R3,450 deposit, R8,050 final payment, R10,000 net sales excl. VAT, R2,800 actual cost, R7,200 indicative contribution (72%); live-business reconciliation still required |
| POPIA operations | OPEN | Owner-approved privacy/retention, incident and operational controls |
| Event scheduling & rental logistics | CLOSED — engineering/CI | Online/offline fields, setup/collection calendar coverage, clash warnings and rental allocation defaults implemented; Laravel, PostgreSQL migration/recovery and desktop/mobile offline-browser acceptance passed at 71c8fc5 in run 44152431508; physical Android remains a separate release gate |
| Windows package | CLOSED for defined fresh-install candidate | Windows V1 Package run 82 at fd82f6a: production candidate build, PowerShell first-install smoke and Windows-native SQLite/private-media recovery pass; not an updater or hosted deployment |

## Claude independent audit reconciliation — 2026-11-17
1. Owner web login auto-trusts browsers for 180 days vs 120-minute session; offline cleanup has no revoke retry — DESKTOP AUTO-TRUST CLOSED: fresh browsers require explicit owner approval in Settings; destructive local cleanup now retries revocation on the next authenticated owner session using only the installation UUID. Installed-phone behaviour remains an accepted owner limitation; ordinary web logout remains non-destructive by design.
2. Six-digit pairing code, per-IP throttle only, no failed-attempt lockout — DEFER; low-confidence hardening, no demonstrated V1 exploit.
3. Loopback-only proxy trust and no HTTPS forcing — ACCEPTED LIMITATION for local Windows/LAN V1; hosted reverse-proxy behavior is outside active deployment scope.
4. CurrentBusiness test-only branch, min-8 password policy, generated manifest drift, offline bootstrap permission filtering — MIXED: generated manifest tracking corrected; bootstrap endpoint permission gate covered; password-policy expansion and per-permission filtering deferred; test-only branch accepted.
5. Rental asset date allocation — REPRODUCED and FIXED. Online/offline tests at 00af verify non-overlapping periods can be booked, overlapping periods are rejected, open-ended ranges conflict correctly, and individual returns preserve allocated status until the final active booking is returned. Manual physical return remains operator-confirmed; no automatic release on event state/date alone. Quantity-based rental stock remains outside the serialized-asset model and is an OWNER-DECISION / SCOPE item.
6. Multi-day events, start/end times, setup/collection dates and clash detection — OWNER-AUTHORIZED for focused V1 implementation on 2026-11-17. Staff scheduling and quantity-based rental stock remain deferred.
7. Invoice numbering — OWNER-AUTHORIZED for engineering implementation on 2026-11-18 following the owner's V1 workstream instruction. Implement a business/month-scoped sequence in the existing `INV-YYYYMM-` format across online issuance and offline sync; tax-adviser/legal review remains open before claiming tax-invoice compliance.
8. Team invites/role assignment, email/PDF delivery, billing, app-wide licence enforcement, in-app legal terms/privacy, monitoring and scheduled jobs — OWNER-DECISION / SCOPE; no silent V1 expansion.

## Verified engineering corrections — e5dc263
- Password reset revokes active and pending trusted devices across every business where the resetting user is an owner, clears bearer/pairing hashes and expiry, and writes audit events. Businesses where the user is staff only remain untouched.
- Offline financial mutation validation rejects precision beyond two decimal places before posting; quote, payment, purchase-order, receipt, cost, expense and inventory regressions are covered.
- CI verifies 523 Laravel tests passed / 1 skipped / 3,662 assertions; 45 desktop Chromium, 6 mobile-viewport and 1 phone-local attachment journey passed; PostgreSQL recovery, Windows fresh-install package, populated upgrade/rollback, quality, Psalm, PHPMD, Director Health and Push-on-main passed. SonarCloud skipped.

## Verified application-code baseline — a5ead69

- Offline push validates payment type/method and expense status against the connected-workflow allowlists; quote currency must be configured.
- Offline payment, expense and invoice dates reject malformed and impossible calendar dates.
- All-zero offline purchase receipts are rejected before a receipt record or idempotency key is consumed.
- Regression coverage includes these cases and float-decoded purchase-order quantity/price precision.
- Laravel: PASS — 526 passed / 1 skipped / 3,681 assertions.
- Offline browser: PASS — 45 desktop Chromium, 6 mobile-viewport and 1 phone-local attachment journey.
- PostgreSQL migration/registration/backup/private-file recovery: PASS.

## Latest repository verification — fd82f6a

- The only change after a5ead69 is the Windows package workflow; application code is unchanged.
- Windows V1 Package run 82: PASS — production candidate, Windows PowerShell fresh install and Windows-native backup/restore of a disposable SQLite record plus private media.
- Director Health, Director Contract Lint and Push-on-main: PASS.
- Composer security audit and secret scan: PASS.
- LAN asset delivery regression: PASS at application-code baseline a5ead69. Remote clients are served compiled same-origin assets even with a stale loopback Vite hot marker; Windows LAN startup removes stale hot state and validates the built manifest. This post-dates the supplied 13 Nov screenshot. Physical Android launch from the HTTPS origin remains part of the open device gate.
- Zazu Quality: FAIL at `npm audit --audit-level=high` due GHSA-fc5q-39xc-vjpf / CVE-2026-112366 in the locked picomatch build-tool dependency chain. GitHub's advisory currently lists affected versions through 4.0.7 and no patched version. The npm dependencies are development/build tools and are excluded from the Windows runtime candidate, but the quality workflow remains red. No force downgrade or advisory suppression applied.
- Windows CI recovery is supporting evidence, not a replacement for the owner-run recovery drill on the intended installation.
- No score uplift; release remains NOT READY.
## Documentation and commercial decisions
- Offline work is ACTIVE/RESUMED; stale pause notes are historical and archived.
- Tier naming and capability direction are resolved by DEC-030: Basic, Intermediate and Professional. The four-tier price tables are historical hypotheses only. No prices or numerical capacity limits are approved. The capability design baseline is `docs/ZAZU_THREE_TIER_CAPABILITY_MATRIX.md`; enforcement remains out of scope until each boundary and downgrade behaviour is approved.
- Root ROSCORE_PROJECT_MANIFEST.md is canonical; docs/ROSCOR_PROJECT_MANIFEST.md is a legacy pointer.
- Prior long-form state/register/memory, checklist and changelog are preserved in docs/archive/engineering/2026-11-17-state-register-memory-history.md.

## Earlier exact-head CI — e5dc263 (historical baseline)
- Laravel: PASS — 523 passed, 1 skipped, 3,662 assertions.
- Offline browser: PASS — 45 desktop, 6 mobile, 1 phone-local attachment.
- PostgreSQL migration/registration/backup/private-file recovery: PASS.
- Windows V1 package + PowerShell fresh install: PASS.
- Populated upgrade/rollback, Zazu quality, Psalm, PHPMD, Director Health and Push-on-main: PASS.
- SonarCloud: SKIPPED.
- Evidence maturity remains 73/100. No score uplift.

## Next
Close the physical Android device, intended-installation recovery, live financial reconciliation and POPIA operational gates. Resolve the unpatched frontend build-tool advisory when a verified upstream fix becomes available. Keep all owner-decision scope items unchanged until approved.


## Exact-head scheduling acceptance evidence — 2026-11-17
- GitHub Actions run `44152431508`: all three jobs completed successfully.
- Offline browser job `134284080089`: PASS — browser suite, mobile Work/customer acceptance and phone-local attachment journey.
- PostgreSQL job `134284080622`: PASS — migrations, account/workspace registration and backup/restore/private-file recovery.
- Laravel job `134284080768`: PASS — service-worker/session contracts, LAN asset-origin checks, Blade/manifest/config checks and PHPUnit/Pest.
- Application code is unchanged after tested source `71c8fc5`; latest `main` HEAD `1bb4936` is a documentation-only release-checklist commit.
- Scheduling engineering/CI subgate closed. No overall readiness uplift: physical Android, installed-target recovery, live commercial reconciliation, POPIA and dependency advisory remain open.


## DB-001 diagnostic contract correction — 2026-11-17

Source inspection confirms the authoritative incident logger is intentionally privacy-minimised. It records request ID, error classification, exception class, route/action, method/path, timestamp and source file/line; it does not record exception messages, SQL, bindings or stack traces. The request middleware also adds the request ID to log context and the response header.

The incident-framework documentation was corrected on `main` (documentation-only commit `f88af665ae09b6f87f3c8d73a52c3cc5e4854dbd`) to match the implementation. This is a documentation correction, not an incident fix. At that historical checkpoint, the owner-installation log entry matching `aa9080f4-9580-41c9-a643-439b31928ffa` was requested. That request and the OPEN label are superseded by the 2026-11-22 Founder clarification at the top of this register; do not pursue it unless DB-001 recurs on current `main`.


## Backup-first startup verification hardening — 2026-11-17

The backup command now reopens its completed temporary ZIP, performs ZIP consistency checking, validates the Zazu manifest and database driver, checks that archive payload membership exactly matches the checksum manifest, and verifies every payload SHA-256 before publishing the backup. The Windows startup script's backup command therefore returns success only after this self-verification. A focused regression checks valid and tampered payload behavior.

**Evidence status:** committed on `main`; exact-head Laravel and Windows workflow results are not yet available. At that historical checkpoint, installation migration execution and account-workflow verification were still listed as required. That DB-001 follow-up is superseded by the 2026-11-22 Founder clarification; installation recovery remains its own separate acceptance gate.


## Zazu quality workflow — exact-head result — 2026-11-17

- GitHub Actions run `44179163841` passed all jobs on `76be1f234e7726352c2e15b511b37b9084d2ddfa`: application, Composer security audit and secret scan.
- Application: `npm ci`, scoped npm audit policy and service-worker authentication cache-race tests all passed.
- The exact GHSA-fc5q-39xc-vjpf / CVE-2026-112366 exception remains temporary because npm has no patched picomatch version. The audit script fails closed for every other high/critical advisory and for any dependency chain not exclusively attributable to this finding.
- This is a CI gate resolution, not vulnerability remediation. Keep the advisory tracked until upstream publishes a fix or withdraws it. No readiness score uplift at that historical checkpoint; the DB-001 open label is superseded by the 2026-11-22 Founder clarification. Separate owner acceptance gates remain open.


## New-job form regression — CLOSED (2026-11-17)

The create screen had omitted the required Work / event name field although `WorkController` still validates and persists `name`. This caused three offline-browser journeys to time out at the absent form control. The production form was corrected on `main` at `ba94b39e0460e4e57c560e98541808c762f50e25`; the tests remain unchanged.

**Exact-head evidence:** Laravel run `44184183003` passed all three jobs: Laravel tests, PostgreSQL migration/registration/backup/restore/private-file recovery, and offline-browser acceptance (including the mobile customer-to-Work and phone-local attachment journeys). Zazu Quality `44184183085`, Windows V1 Package `44184182900` (including PowerShell fresh install), Director Health `44184182984` and Push-on-main `44184180721` also passed. SonarCloud was skipped, not passed.

This is a closed form/CI regression, not a readiness uplift. The historical DB-001 open label is superseded by the 2026-11-22 Founder clarification. The separate operator acceptance gates remain open; release remained NOT READY and evidence maturity remained 73/100 at that historical checkpoint.


## Historical DB-001 recurrence record — 2026-11-18 (superseded; not active)

Historical reference retained for traceability: `895e2c23-bc6b-4919-a891-1e8e5c1e27e4`. Earlier summaries described an account/workspace-save failure, but the Founder clarified on 2026-11-22 that the occurrence under discussion happened during offline event-creation/calendar-related task testing and has not recurred since subsequent offline-architecture work. That clarification supersedes the earlier description.

A safe Windows capture utility was added to `main`: `ops/windows/Get-ZazuIncident.ps1`. It is retained for a genuinely new recurrence only; do not run it now or request another owner-side capture.

**Closure still requires:** matching runtime record, a targeted correction, and successful repeat of the affected workflow on the intended installation. Do not alter the database while the cause is unknown. Release remains NOT READY; no score uplift.


## Historical DB-001 read-only SQLite inspection — 2026-11-18 (superseded; tool retained only for a new recurrence)

The read-only Windows diagnostic `ops/windows/Test-ZazuDatabaseSchema.ps1` remains available for a genuinely new, reproducible recurrence. Its historical request for an owner-PC report is superseded by the 2026-11-22 Founder clarification. Do not run it now, request a report or select a speculative schema correction. No DB-001 root cause or fix is claimed.
