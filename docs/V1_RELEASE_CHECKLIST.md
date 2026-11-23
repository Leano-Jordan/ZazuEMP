## Director consolidation checkpoint — 2026-11-23

**Repository:** `Leano-Jordan/ZazuEMP` · canonical branch: `main`  
**Capability regression source/test commit:** `3ca0b72e4a370f94b3e9c12f42fb8840ce20d12d`  
**Engineering state checkpoint:** `053182a1b5c37529a170af994d88210cb4674f81`

### Dependency and CI consolidation

- [x] Confirmed direct Composer requirements match the locked Composer packages and declared constraints.
- [x] Confirmed direct npm development/optional dependencies match the locked npm packages and declared ranges.
- [x] Recalculated licence metadata from the lockfiles: 77 Composer runtime packages, 34 Composer development packages and 147 npm package entries; every entry has declared licence metadata.
- [x] Refreshed `docs/RELEASE_DEPENDENCY_LICENCE_AUDIT.md` and `THIRD_PARTY_NOTICES.md` with current lockfile counts and direct dependency versions.
- [x] Pinned the remaining floating checkout actions and aligned PHPMD SARIF upload to pinned CodeQL v4.39.3.
- [x] Removed the obsolete GHSA-fc5q-39xc-vjpf / CVE-2026-112366 npm audit exception. GitHub marks the advisory withdrawn on 2026-11-18; the audit wrapper now applies the normal high-severity threshold without a package-specific bypass.
- [ ] Exact-head CI for the consolidation commits: not yet confirmed. Do not infer a pass from earlier green runs.

### Recovery confidentiality and staging hardening

- [x] Restricted POSIX installation backup staging and published ZIP permissions to owner-only access.
- [x] Restricted POSIX restore staging, extracted private media and rollback snapshots before activation.
- [x] Moved Windows backup/restore staging and rollback artifacts under `storage/app/zazu-backups/`, which the current installer already restricts to the installation owner, SYSTEM and local Administrators.
- [x] Added regression coverage for private staging and restored private-media permissions.
- [x] New business V2 `.zazu` exports use streaming Sodium secretstream XChaCha20-Poly1305 with Argon2id-derived keys and authenticated chunk/final-tag verification.
- [x] Export requires a confirmed 12–128 character passphrase; restore requires the matching passphrase for V2 archives.
- [x] Passphrase and confirmation are excluded from validation flash/session old input; export and restore use independent owner-scoped rate limits (3 attempts per 10 minutes).
- [x] Existing plaintext V1 business archives remain importable for compatibility; owners are warned to re-export or securely protect old copies.
- [x] Installation-wide scheduled backups remain unattended plain ZIP files; off-disk copies require encrypted removable media or an encrypted, access-controlled vault.
- [ ] Verify the configured secondary destination is BitLocker-protected USB or an encrypted, access-controlled cloud vault. The runner verifies SHA-256 only; it does not enforce or attest destination encryption.
- [x] Windows automated archive acceptance passed on test head `93c6e2a50b755a9aeb44715a120df1ff2cf2df21`, run [45149443876](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45149443876): **7 passed / 31 assertions**. Covers multi-chunk Sodium encryption/decryption, wrong-passphrase rejection, ciphertext tamper rejection, owner export, encrypted restore and legacy plaintext V1 restore.
- [ ] Run the encrypted export/restore drill on the Founder’s actual installed Windows PC; CI is not a substitute for the installed environment and the live business workspace.
- [ ] Confirm the configured secondary installation-backup destination is BitLocker-protected USB or an encrypted, access-controlled cloud vault.
- [ ] Confirm all exact-head CI checks for the final documentation/source head are green.

### Capability-to-Job integration coverage

- [x] Added a mixed Job fixture proving saved Decor, Photography & video and Transport requirements drive the matching Job-specific Helper guidance.
- [x] Added a deactivation regression through the real owner update route: the offering leaves business-wide active focus and staff catalogue visibility.
- [x] Verified the owner retains the hidden catalogue item and the existing Job requirement/category and Job-specific guidance remain intact.
- [ ] Exact-head Laravel, PHPMD, formatting and browser acceptance for the new regression tests.

### Current release disposition

V1 remains **NOT READY**. Automated coverage and the dependency refresh do not close the owner-operated physical Android offline/restart/reconnect test, controlled restore on the intended Windows installation, populated live-business quote-to-payment-to-cost/profit reconciliation, or POPIA operational and Zazu name-clearance gates. Do not ask the Founder to recreate populated records or repeat backup checks already confirmed.

Historical dependency notes later in this checklist that describe GHSA-fc5q-39xc-vjpf as active/unpatched are superseded by this checkpoint and the 2026-11-18 GitHub advisory withdrawal.

---

## POPIA operational documentation update — 2026-11-23

- [x] Refresh the manual data-subject request procedure for objection, correction/deletion, access and role routing; self-service remains unavailable.
- [x] Refresh privacy notice draft for hosted/local processing roles, provisional contact, dietary/allergy data minimisation and incident escalation.
- [x] Correct regulator eServices reporting reference in the security incident runbook.
- [x] Clarify PAIA routing and the operator agreement template boundary.
- [x] Add privacy request, incident and provider inventory template. Working records must be kept outside GitHub.
- [x] Add data-minimisation guidance to Job notes and requirement special details, with feature coverage.
- [ ] Complete Information Officer appointment/registration and PAIA applicability/manual obligations for the actual responsible party.
- [ ] Test/monitor the approved provisional support/privacy mailbox and confirm final public provider/contact details.
- [ ] Inventory actual sub-operators and execute written operator terms where required.
- [ ] Approve the retention/disposal policy, verify private-media disposal and backup expiry, and complete legal review/tabletop exercise.

**Disposition:** documentation/UI refinement is implemented. POPIA operational sign-off remains OPEN; these edits are not legal approval or a compliance claim.

## Windows installation candidate verification — 2026-11-23

- Runtime/source head: `6933f41621fd8ac2e3ff7b311d3eb81123594511` (includes the Helper current-state implementation, including rental asset allocations).
- GitHub Actions Windows V1 Package run `45133569162`: **Build Windows installation candidate — PASS**; **Verify fresh install on Windows PowerShell — PASS**.
- The optional GitHub prerelease publication job was **SKIPPED**; no prerelease publication is claimed.
- This verifies package construction and a clean PowerShell installation workflow in CI. It does not substitute for restoring the Founder’s intended installed PC from the independently verified backup or for physical Android offline acceptance.

# Owner-verified physical operation and populated workspace — 2026-11-22

**Founder evidence update; current repository baseline:** [`cd7762a`](https://github.com/Leano-Jordan/ZazuEMP/commit/cd7762a18976eeae7cef6d2c4fb132f69b32b0f5).

The Founder confirms physical operation of Zazu EMP on the PC and linked mobile phone, with both using the same workspace. The Founder reports that fictional customer/client, quote, invoice, supplier, purchase-order and inventory records have already been created and that these areas work as intended. This supersedes the earlier assumption that the owner still needed to create the initial customer/job and populate the commercial modules.

- **PC normal-use acceptance:** OWNER-VERIFIED PASS.
- **Linked mobile normal-use acceptance:** OWNER-VERIFIED PASS.
- **Shared workspace operation:** OWNER-VERIFIED PASS.
- **Populated business modules:** OWNER-REPORTED FUNCTIONAL; record population confirmed by Founder.
- **Screenshots reviewed:** dashboard shows Zazu Demo Catering, two customers, one active event and connected workspace-health indicators. Screenshots do not show transaction detail for quotes, invoices, POs or inventory.
- **Data classification:** Founder confirms all test/business records supplied for this exercise are fictional.

Do not ask the Founder to recreate records or repeat basic connected PC/mobile startup checks. Do not infer that connected operation proves disconnected cold-start, offline write/restart/reconnect, attachment sync, full installation restore, or transaction-level quote-to-profit reconciliation. Keep those distinct evidence gates open until directly demonstrated. The owner-PC off-disk backup/restore and POPIA/name-clearance gates are also unaffected.
## Founder installation and business backup verification — 2026-11-23

Founder reports the following live owner-PC checks while starting the Zazu server:

- Startup database check displayed: “The Zazu database is up to date.” Record this as **OWNER-VERIFIED database migration check PASS** for the installed environment at this observation.
- The latest installation-backup file is visible on the PC; the Zazu backup UI shows it as approximately **7 hours old** at the time of the observation.
- A business-level backup was downloaded.
- Founder independently verified the installation and business backup files.

**Recorded status:** installation backup present and independently verified — OWNER-VERIFIED PASS; business archive downloaded and independently verified — OWNER-VERIFIED PASS; installed database reports no pending migrations — OWNER-VERIFIED PASS.

**Still separate / NOT CLOSED:** a file existing and being independently verified does not prove that the intended installation can be fully restored. The controlled owner-PC restore drill, including database, configuration/key handling, private media and post-restore startup/workspace verification, remains OPEN until a restore is actually performed and checked. Preserve the existing installation and backup files; do not ask the Founder to repeat checks already completed.

## Physical Android acceptance — REQUIRED / PENDING

**Acceptance status: NOT YET PERFORMED.** The Founder-confirmed fictional workspace already contains customers, quotes, invoices, suppliers, purchase orders and inventory. The PC and linked Android phone already use that same workspace. Do not create duplicate setup records, reseed the workspace or re-pair devices. The physical acceptance run itself is still required.

**Physical Android acceptance sequence:**
1. Record the tested main commit and installed build. Confirm the phone is using the installed Zazu PWA over its supported HTTPS origin and that the local mutation/attachment queues are clear before starting.
2. Keep phone Wi-Fi enabled. Stop the Zazu host on the PC; do not use airplane mode. Confirm the phone detects the unavailable host and offers the offline workspace.
3. Open the cached dashboard, Customers and Work screens. Create one clearly named offline acceptance job against an existing fictional customer and attach one small file from the phone.
4. Force-close the installed PWA/browser completely while the host remains stopped. Relaunch it and verify the job, its details and attachment remain available, with the mutation still pending locally.
5. Restart the PC host. Wait for the queue to reconcile. Verify on both devices that exactly one job exists, its attachment is present and opens, and no pending mutation or attachment remains.
6. Using the existing populated fictional commercial records, trace an accepted quote version through invoice, recorded payments, actual costs and the resulting profit/margin. Record the actual values; do not substitute automated test example amounts.

**Stop conditions:** if the queue does not drain, the attachment is missing, a duplicate appears, or the app asks to clear/re-pair the device, stop and preserve local data. Do not clear browser storage, uninstall the PWA or repeat the operation until the cause is captured.

**Evidence to record:** exact commit/build, Android model and browser/PWA version, host-offline and restart observations, queue state before/after, the single reconciled job, attachment open result, and commercial totals. Browser emulation is supporting evidence only; it does not close this physical gate.

**Automated gate:** PASS on source/test head `a0baea5901b139b1c7397a12d780af245841d3ce`: Laravel 691 passed / 1 skipped / 4,794 assertions; PostgreSQL migration/recovery; 63 desktop Chromium, 7 mobile viewport, 1 mobile host-outage and 1 phone-local attachment/reconnect; public/auth accessibility 1 passed; Zazu Quality, PHPMD, Director Health and Push-on-main passed. Windows V1 package/fresh-install passed on runtime implementation head `b9f9a146c5a1310ef75e1f3edf5a341851be005b`. SonarCloud was skipped. Physical acceptance is **REQUIRED / PENDING**; it remains OPEN until the Founder performs and records the actual-device run. No physical acceptance result is claimed.


## DB-001 disposition — Founder clarification (2026-11-22)

**DORMANT / HISTORICAL / NON-CRITICAL — not an active engineering task or V1 release blocker.** The Founder clarifies that the DB-001 occurrence under discussion happened while testing offline event creation and a calendar-related task. It has not recurred since the subsequent offline-architecture work. It is not a confirmed current database defect, and no root cause or fix is claimed.

Do not chase this historical anomaly, request diagnostic captures, or ask the Founder to run `Test-ZazuDatabaseSchema.ps1` or `Get-ZazuIncident.ps1`. Reopen only if the same failure is reproduced in a supported workflow on current `main`, or materially new diagnostic evidence appears. The separate installation backup/restore acceptance gate remains independent and must not be conflated with DB-001.

# Secondary backup health integrity — 2026-11-22

**Implementation head:** [c992b98](https://github.com/Leano-Jordan/ZazuEMP/commit/c992b986497ca1c233437212d55b3e93ad29c455).

- Fixed a false healthy state: a recent successful-copy timestamp alone no longer proves the secondary archive remains available.
- Windows runner records the secondary archive path, SHA-256, byte size and last-write timestamp after copy verification. The PHP health check requires the archive to remain present as a regular non-symlink file with matching size and timestamp; missing/unavailable/changed metadata triggers **ATTENTION**.
- SHA-256 is verified at copy time, not recomputed on every dashboard request. The health check does not prove a physically separate disk or completed cloud upload; the owner must verify the configured destination is genuinely independent.
- Laravel: **679 passed / 1 skipped / 4,671 assertions**; the new missing/changed-secondary-copy regression passed. Zazu Quality, PHPMD, Psalm, populated upgrade/rollback, PostgreSQL migration/recovery and Windows fresh-install acceptance: **PASS**. SonarCloud: **SKIPPED**.
- Windows PowerShell 5.1 runner metadata/failure-preservation test: [44988789562](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44988789562) **PASS**.
- Exact-head offline-browser acceptance [44989474410](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44989474410): **63 desktop + 7 mobile viewport + 1 host-outage + 1 phone-local attachment/reconnect passed**; public/auth accessibility acceptance passed.
- Evidence maturity remains **77/100**. V1 remains **NOT READY** until the owner-PC scheduled backup, independent off-disk copy and controlled full restore are demonstrated.

**DB-001 disposition:** dormant, historical and non-critical; not an active engineering task or V1 release blocker. The Founder confirms it occurred during offline event-creation/calendar-related task testing and has not recurred after subsequent offline-architecture work. Do not request or run DB-001 diagnostics unless it recurs on current `main`. Commercial, physical-device, recovery and legal/brand gates remain assessed separately.

---

# Offline session and trusted-device security review — 2026-11-22

**Reviewed source/test head:** [8803746](https://github.com/Leano-Jordan/ZazuEMP/commit/88037465ddacbc4b60a81efe343e4210b6b1dc02). Exact-head Laravel/browser/PostgreSQL [44987764883](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44987764883) **PASS**.

- Reviewed login/session expiry, logout and interrupted logout, business switching, device revocation/expiry, stale bootstrap, re-pair token replacement, in-flight sync and attachment responses.
- Existing automated coverage passed: expired-session sign-in barrier with queue preservation; owner/manager transition; business queue/cache isolation; stale bootstrap rejection; old-token push cancellation/retry after re-pair; late attachment responses blocked after logout; server-side owner revocation and bearer expiry.
- The approved DEC-019 model intentionally keeps the business-trusted device credential independent of an individual web login. Ordinary account logout preserves the business-scoped offline workspace and paired device. Device revocation is an explicit owner action. No new confirmed security defect was found; no architectural change is warranted.
- Laravel: **679 passed / 1 skipped / 4,665 assertions**. Offline browser: **63 desktop + 7 mobile viewport + 1 host-outage + 1 phone-local attachment/reconnect passed**. PostgreSQL migration/recovery, Zazu Quality, PHPMD, Director Health and Push-on-main: **PASS**. SonarCloud: **SKIPPED**.
- Automated acceptance does not prove physical Android cold-start/restart or owner-operated lost-device handling. Evidence maturity remains **77/100**; V1 remains **NOT READY**.

**Next engineering target:** recovery integrity — business archive portability versus installation-wide backup/restore, private-media integrity, trusted-device safeguards and intended-PC recovery evidence.

---

# Offline expense identity isolation — 2026-11-22

**Verified source/test head:** [8803746](https://github.com/Leano-Jordan/ZazuEMP/commit/88037465ddacbc4b60a81efe343e4210b6b1dc02).

- Added a fail-closed regression for corrupted business-local sync identity rows pointing to foreign event, supplier or purchase-order records in an offline expense mutation.
- The test verifies each mutation remains pending, no local expense is written and all foreign records remain unchanged.
- Laravel: **679 passed / 1 skipped / 4,665 assertions**; `OfflineExpenseReferenceIsolationTest` passed.
- Zazu Quality, incremental formatting, Composer security audit, secret scan, PHPMD, PostgreSQL migration/rollback/registration/backup/private-file recovery, Director Health and Push-on-main: **PASS**.
- SonarCloud: **SKIPPED**.
- Exact-head offline-browser acceptance in [Laravel workflow 44987764883](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44987764883): **63 desktop Chromium + 7 mobile viewport + 1 mobile host-outage + 1 phone-local attachment/reconnect passed**; public/auth accessibility acceptance also passed.
- Evidence maturity remains **77/100**; V1 remains **NOT READY**. This server-side isolation regression does not close physical Android acceptance, owner-PC recovery, live-business commercial reconciliation, POPIA or name clearance.

**Next engineering target:** offline account/session transition adversarial review, with emphasis on expiry, re-authentication, preserving queued local work and blocking stale-session online mutations.

---

# Dependency refresh verification — 2026-11-22

**Exact repository HEAD:** [7005bc8](https://github.com/Leano-Jordan/ZazuEMP/commit/7005bc805bdf94429579e4b0cdb2208eec322235) on `main`.

The recent Dependabot refreshes are merged; there are no open pull requests in the repository. The current lockfiles contain the upgraded versions below:

- Laravel Framework **13.43.0**; Laravel Tinker **3.2.0**.
- PHPUnit **13.4.7**; Laravel Pint **1.32.3**; Laravel Pail **1.3.0**; Laravel Pao **1.1.5**.
- Vite **8.3.8**; Playwright **1.65.1**; Node types **26.6.6**; Tailwind CSS **4.3.5**; Laravel Multiplex **0.4.5**; Laravel Vite plugin **3.2.0**.
- Symfony Console and HttpFoundation **7.4.23**.

The package constraints remain compatible with the resolved lockfile versions; no unnecessary constraint widening or manual lockfile edits were made.

**Exact-main verification on 7005bc8:**
- Laravel: **678 passed, 1 skipped, 4,655 assertions** — [run 44961259381](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44961259381).
- Offline browser acceptance: **PASS**, including desktop, mobile viewport, simulated host outage and phone-local attachment/reconnect journeys — same run.
- PostgreSQL migration, rollback/reapply, registration, backup/restore and private-file recovery: **PASS** — same run.
- Windows installation candidate build and fresh PowerShell install/recovery: **PASS** — [run 44961259393](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44961259393).
- Zazu Quality, Composer security audit, secret-history scan, incremental Pint formatting, production asset build/budget and documentation validation: **PASS** — [run 44961259439](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44961259439).
- Director contract lint and CodeQL language/Actions/Python analysis: **PASS** — [run 44961259432](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44961259432) and [run 44961259058](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44961259058).
- Windows prerelease publication was **skipped**, not passed.

The first full-repository Pint attempt exposed pre-existing formatting debt. The incremental changed-PHP gate now passes on this exact HEAD without mass-reformatting legacy files.

**Release disposition:** V1 remains **NOT READY**. Keep the evidence score at **77/100** until physical Android acceptance, owner-PC off-disk recovery, populated live-business financial reconciliation, POPIA operational sign-off and ZAZU name clearance are completed. CI green does not substitute for these operational proofs.

---

# Director wrap-up — 2026-11-22

**Current `main` HEAD:** [da43fde](https://github.com/Leano-Jordan/ZazuEMP/commit/da43fde04dcfc75b5d031efc86945799315a3c05).

- The initial whole-repository Pint gate failed because existing formatting drift spans many legacy and test PHP files. No mass formatting rewrite was applied.
- Pint is now scoped to PHP files changed by each push/PR. This is a blocking, non-mutating incremental gate; its first exact-main run is pending.
- On the previous exact application baseline [aa93a470](https://github.com/Leano-Jordan/ZazuEMP/commit/aa93a4701f562f72bf91a8b26e49eea80ee2ad64), Laravel, offline browser/emulation, PostgreSQL recovery, Windows install, dependency/security, docs, bundle and accessibility checks passed as recorded below.
- The repository remains **V1 NOT READY**. Evidence maturity remains **77/100**. No score increase is claimed from documentation/CI wiring alone.

**Final release blockers that cannot be certified by repository CI alone:**
1. Physical Android installed-app cold launch, host-offline work, force-close/restart and reconnect.
2. Owner-operated PC backup with independent off-disk copy and full restore including private media.
3. Populated live-business quote → invoice → payment → costs/expenses → profit reconciliation.
4. POPIA operational controls and owner sign-off.
5. ZAZU name clearance.

The automated mobile viewport and simulated LAN outage tests are not physical-device evidence. Automated Windows/PostgreSQL recovery tests are not the owner's PC recovery drill.

---

# Exact-main verification — 2026-11-22

**Repository:** `Leano-Jordan/ZazuEMP` · **branch:** `main` · **verified HEAD:** [aa93a470](https://github.com/Leano-Jordan/ZazuEMP/commit/aa93a4701f562f72bf91a8b26e49eea80ee2ad64).

The exact HEAD completed its push-triggered engineering checks successfully:

- **Laravel:** 678 passed / 1 skipped / 4,655 assertions — [run 44954694947](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44954694947).
- **Offline browser acceptance:** 63 desktop Chromium, 7 mobile-viewport workflows, 1 simulated LAN host-outage journey and 1 phone-local attachment/reconnect journey passed. The dedicated public/auth accessibility scan also passed (1 test) in the same run. These are browser/emulation results, not physical-phone proof.
- **PostgreSQL:** complete migration, rollback/reapplication, account/workspace registration, backup/restore and private-file recovery smoke passed in [run 44954694947](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44954694947).
- **Windows:** installation candidate build and fresh Windows PowerShell install/recovery checks passed in [run 44954695096](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44954695096).
- **Quality gates:** npm dependency audit, Composer security audit, secret-history scan, maintained-document validation (32 local links across 172 Markdown files), production build, compressed asset budgets and service-worker authentication/cache tests passed in [run 44954694991](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44954694991).
- **Measured bundle guardrails:** JavaScript 23.6 KiB gzip against 64 KiB; CSS 67.2 KiB gzip against 100 KiB. These are transfer-size budgets only, not runtime latency or device resource benchmarks.
- **Coverage report:** generated and uploaded by [run 44954694947](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44954694947); the report is visibility evidence, not a release gate.
- **Analysis:** JavaScript/TypeScript, Actions and Python CodeQL jobs passed. SonarCloud and the Windows prerelease publish job were skipped; neither is counted as a pass or release approval.

**Score disposition:** evidence maturity remains **77/100**. The latest checks strengthen exact-main confidence but do not justify a numerical uplift: no new real-device, live-business or operator recovery evidence has been supplied. V1 remains **NOT READY**.

**Open owner/operational release gates:**
1. Physical Android installed-app cold launch, stop-host while Wi-Fi remains connected, supported disconnected work, force-close/restart and reconnect.
2. Actual owner-PC scheduled backup observation, independent off-disk copy and controlled full installation restore including representative private media.
3. Populated live-business quote → invoice → payment → expenses/costs → profit reconciliation.
4. POPIA operational sign-off and completion of the required customer/privacy operational controls.
5. ZAZU name clearance.

**Engineering boundary:** the browser suite's simulated phone and host-outage tests must not be described as physical-device acceptance. Automated PostgreSQL/Windows recovery must not be described as the owner's installed-PC recovery drill. Do not claim app-wide offline parity.

---

## Offline mutation operation validation — exact-head Laravel / PHPMD / PostgreSQL PASS — 2026-11-21

**Application code/test head:** [e95dd5c](https://github.com/Leano-Jordan/ZazuEMP/commit/e95dd5cd8966c7457de70e9a4bc882709db7d30b).

- Closed CASE-ZAZU-0041: customer, job and service offline handlers now explicitly allow only `create`, `upsert` and `update`.
- Unsupported `delete` mutations are rejected before domain writes; delete support has not been added.
- Expanded `OfflineMutationOperationValidationTest`: unsupported `delete` is rejected by all 17 registered offline entity handlers; each mutation remains pending with zero attempts, and all 15 entity-record tables remain empty.
- Expanded all-handler Laravel regression: **676 passed / 1 skipped / 4,647 assertions** [workflow 44948063650](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44948063650).
- PHPMD: **PASS** [run 44948063618](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44948063618); Zazu Quality: **PASS** [run 44948063564](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44948063564).
- PostgreSQL migration, registration and backup/restore/private-file recovery smoke: **PASS** in [Laravel workflow 44947482747](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44947482747).
- Runtime-identical browser acceptance: **63 desktop Chromium + 7 mobile viewport + 1 mobile host-outage + 1 phone-local attachment/reconnect passed**, no failures or flaky retries on [workflow 44947482747](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44947482747). V1 remains **NOT READY**. This targeted server-side integrity closure does not close the full offline sync matrix or any owner-operated physical acceptance gate.

---

## Windows backup scheduler and runner — runtime CI PASS — 2026-11-21

- Windows PowerShell 5.1 CI registers a temporary Task Scheduler task and verifies S4U/limited privileges, daily 02:00 trigger, missed-run catch-up, wake-to-run and IgnoreNew overlap behaviour.
- The backup runner is executed against an isolated fixture: local/secondary retention capped at 14, SHA-256 copy equality, and a simulated unavailable secondary drive that preserves the previous successful-copy timestamp while marking the latest attempt failed.
- [Windows Ops workflow 44941196865](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44941196865) passed on [db5f25e](https://github.com/Leano-Jordan/ZazuEMP/commit/db5f25e1f6b54fb28909738508ef935792a419cb).
- This closes the Windows automation runtime CI gap, **not** the owner-PC release gate. The real PC must still run the task, produce an independent off-disk copy and pass a controlled full restore with private media.

---

## Trusted-device credential boundary — exact-head acceptance PASS — 2026-11-21

**Verified code/test head:** [04db872](https://github.com/Leano-Jordan/ZazuEMP/commit/04db8722a66ff8c97d62fef855f7ba07073934c7).

- Mutation application revalidates the original credential and mutation business/device ownership inside the domain-application transaction.
- Conflict recording now revalidates the middleware credential before creating/updating conflict state. Bootstrap, pull and acknowledgement use the same shared business-first/device-row guard.
- Stale-credential apply and conflict-write regressions pass; the attachment reconnect browser test now verifies offline state and the same business-scoped IndexedDB partition before and after reload.
- Laravel: **675 passed / 1 skipped / 4,581 assertions**; PostgreSQL migration/recovery smoke: **PASS**.
- Offline browser: **63 desktop Chromium + 7 mobile viewport + 1 mobile host-outage + 1 phone-local attachment/reconnect passed**, no failures or flaky retries.
- PHPMD, Psalm, Zazu Quality, Director Health, populated upgrade/rollback, Windows V1 package/fresh install and Push-on-main: **PASS**. SonarCloud: **SKIPPED**.
- CASE-ZAZU-0040 is closed at automated-test/source level.

**Still required for V1 release:** actual Android installed-app cold launch/host-stop/force-close/restart/reconnect; owner-PC off-disk backup and full restore drill with private media; populated live-business quote-to-payment-to-profit reconciliation; POPIA operational sign-off; ZAZU name clearance. Mobile emulation and CI recovery tests do not replace those owner-side acceptance steps. V1 remains **NOT READY**.

---

## Empty private-storage restore — automated verification PASS — 2026-11-21

- Restore now treats the v2 manifest declaration as authoritative even when private storage contains zero files.
- An empty staging directory replaces stale target private files; rollback remains in the existing guarded path.
- Added a database + empty-private-storage integration regression proving stale files are removed. Exact-head Laravel/PostgreSQL run [44937037910](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44937037910) passed; Windows populated and empty-private-storage restore passed [44937732880](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44937732880).
- Owner-operated restore and private-media acceptance remain open.

---

## Backup/restore archive compatibility — implementation checkpoint 2026-11-21

- Fixed a mismatch where backup could publish up to 50,002 entries but restore accepted only 50,000.
- Backup and restore now share limits: 50,000 private files, 50,002 ZIP entries, and 2 GiB uncompressed total. Backup verifies the completed ZIP including its manifest before publication.
- Added boundary regression tests and an empty-private-storage restore integration test that proves stale target files are removed. Exact-head workflow [44937037910](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44937037910) passed Laravel **673 / 1 skipped / 4,575 assertions**, PostgreSQL migration/recovery smoke, **63 desktop + 7 mobile + 1 host-outage + 1 phone-local attachment/reconnect browser journeys**, PHPMD and Zazu Quality.
- Windows package build, fresh PowerShell install, populated database/private-media restore and empty-private-storage restore passed [workflow 44937732880](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44937732880) against the same runtime source. The owner-operated isolated restore drill and independent off-host copy remain mandatory; automated CI does not close the physical recovery gate.

---

## Offline host-outage monitor — exact-head acceptance PASS — 2026-11-21

**Source:** [41d7a25](https://github.com/Leano-Jordan/ZazuEMP/commit/41d7a256f80b1e1d98cbb43509f81dc26cd600ec).

- Added periodic HEAD /up health checks because navigator.onLine can remain true while the Zazu LAN host is stopped.
- A visible, non-destructive notice now explains that the host is unavailable and links to the cached local workspace. It does not auto-redirect or discard an open form.
- Dashboard offline workspace visibility now responds to actual Zazu-origin availability, not only the network-interface state.
- Regression simulates host 503/200 transitions while the browser remains online, on desktop Chromium and Pixel 7 viewport.
- Exact-head Laravel: **670 passed / 1 skipped / 4,552 assertions**; PostgreSQL migration/recovery smoke: **PASS**; offline browser: **63 desktop + 7 mobile viewport + 1 mobile host-outage + 1 phone-local attachment journey passed with no retries**. Zazu Quality, Director Health, Contract Lint and Push-on-main: **PASS**. [Exact-head run 44934728827](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44934728827). Windows V1 package and fresh-install acceptance passed on runtime implementation head [44934642257](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44934642257). SonarCloud skipped.

**Physical acceptance still required:** install/update the PWA on the actual phone while online, open a supported route, stop the Zazu host while Wi-Fi remains connected, verify the outage notice and local workspace shortcut, then verify local reads/writes, force-close/relaunch, reconnect and queue drain. Capture the exact app build and observed result. Do not substitute browser emulation for this drill.

## Exact-head verification update — 2026-11-21

**Application code head:** [7d4b097](https://github.com/Leano-Jordan/ZazuEMP/commit/7d4b097a13d02f3d5369bf8e75e2dc85c361beb7).

- Laravel: **670 passed / 1 skipped / 4,552 assertions**.
- PostgreSQL migration/recovery smoke: PASS.
- Offline browser acceptance: **62 desktop Chromium**, **7 mobile viewport**, **1 phone-local Work/attachment/reconnect journey** passed.
- Windows V1 package: PASS.
- PHPMD, Psalm, Zazu Quality, populated upgrade/rollback and Director Health: PASS.
- SonarCloud: SKIPPED; not counted as a pass.
- Trusted-device stale-snapshot authorization case CASE-ZAZU-0036: CLOSED on this code head.

Evidence: [Laravel + browser + PostgreSQL run 44931555043](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44931555043) · [Windows package 44931554910](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44931554910) · [PHPMD 44931555002](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44931555002) · [Psalm 44931555131](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44931555131) · [Upgrade/rollback 44931555044](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44931555044) · [Director Health 44931554979](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44931554979).

**Release disposition remains NOT READY.** Physical Android cold launch/restart/reconnect, actual owner-PC off-disk restore, populated live-business commercial reconciliation, POPIA operational controls and ZAZU name clearance remain open. No readiness score uplift is claimed.

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

## Offline expense reconciliation — 2026-11-21

**Exact source/test head:** `3c8db9bd02fec06603d48b3050f7d306d7b41070` on `main`.  
**Laravel workflow:** [44902438027](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44902438027) · **Zazu Quality:** [44902438064](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44902438064) · **Push-on-main:** [44902438499](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44902438499).

- The existing offline expense form now has browser acceptance: save while disconnected, preserve the job/supplier/purchase-order local identities, reconnect, receive the canonical expense server ID and drain the durable queue.
- Server-side feature coverage applies the real offline expense handler and verifies the ZAR amount, paid status, event/supplier/purchase-order ownership, expense identity, audit event and duplicate-safe replay.
- Laravel: **651 passed / 1 skipped / 4,480 assertions**.
- Offline browser: **61 desktop Chromium + 7 mobile viewport + 1 phone-local attachment journey passed**.
- PostgreSQL migration/registration/backup/restore/private-file recovery smoke: **PASS**.
- Zazu Quality, Composer security audit, secret scan and Push-on-main: **PASS**. PHPMD and Director Health passed on the immediately preceding PHP-identical source head. SonarCloud: **SKIPPED**.

**Evidence boundary:** the browser acknowledgement is deterministic/mocked. The PHP feature test exercises the real server-side mutation handler and database transaction. The full populated live-business commercial walkthrough remains a separate acceptance gate. Physical Android cold launch/restart/reconnect, intended-PC recovery and POPIA operational sign-off remain separate gates. V1 remains **NOT READY**; no score increase is claimed.

**Next engineering target:** strengthen business archive restore acceptance by proving a workspace with existing trusted-device state is rejected before any imported data or files are written.

---

## Offline payment reconciliation — 2026-11-21

**Exact source/test head:** `e1debd77c9d064f92895768f120ac752a34d897b` on `main`.  
**Laravel workflow:** [44901265394](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44901265394).

- The server-side offline payment handler is exercised with a real invoice and trusted device. A 25.00 ZAR payment is recorded once; invoice paid amount becomes 25.00, balance becomes 75.00, and the invoice sync identity advances from revision 1 to 2.
- The server returns the invoice as a related identity for the payment command. Replaying an already-applied mutation does not create a second payment or advance the invoice revision again.
- Browser acceptance verifies the offline payment receives its canonical payment identity and applies the related invoice revision acknowledgement; the durable mutation queue drains.
- Laravel: **650 passed / 1 skipped / 4,466 assertions**.
- Offline browser: **60 desktop Chromium + 7 mobile viewport + 1 phone-local attachment journey passed**.
- PostgreSQL migration/registration/backup/restore/private-file recovery smoke: **PASS**.
- Zazu Quality, Composer security audit, secret scan, PHPMD, Director Health and Push-on-main: **PASS**. SonarCloud: **SKIPPED**.
- No production source change was required for this payment target; this batch closes the backend and browser verification gap.

**Evidence boundary:** the browser acknowledgement is deterministic/mocked. The PHP feature test exercises the real server-side mutation handler and database transaction. The populated online commercial reconciliation remains a separate live-business acceptance gate. Physical Android cold launch/restart/reconnect, intended-PC recovery and POPIA operational sign-off remain separate gates. V1 remains **NOT READY**; no score increase is claimed.

**Next engineering target:** exercise the existing offline expense workflow end-to-end, including its linked business records, canonical identity acknowledgement and duplicate-safe replay.

---

## Offline quote-to-invoice sync acceptance — 2026-11-21

**Exact source/test head:** `36a2f605481e3985b647dda710ead7bef157b70f` · [workflow 44898943264](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44898943264).

- [x] Disconnected quote acceptance is queued and reflected in local state.
- [x] An invoice is created locally from the newly accepted quote.
- [x] Reconnect acknowledges both queued mutations and advances quote/Work revisions.
- [x] The invoice receives its server identity using its actual generated local ID; its quote-local relationship is retained.
- [x] Durable queue drains after both acknowledgements.
- [x] Offline browser 60 desktop Chromium + 7 mobile viewport + 1 phone-local attachment journey passed.
- [x] Laravel 649 passed / 1 skipped / 4,456 assertions; PostgreSQL migration/registration/backup/restore/private-file recovery smoke passed.
- [x] CASE-ZAZU-0037 / ENG-FIXTURE-007 closed as an F2/F3 test-fixture correction; production code was unchanged.
- [ ] Full offline sync matrix and live-business commercial reconciliation remain OPEN; mocked browser acknowledgements do not replace live server persistence evidence.

V1 remains NOT READY. Physical Android cold launch/restart/reconnect, intended-PC recovery and POPIA operational sign-off remain separate gates.

---

# Zazu EMP — V1 Release Checklist

Latest application/test source verification: e1debd77c9d064f92895768f120ac752a34d897b — 650 passed / 1 skipped / 4,466 assertions; PostgreSQL migration/recovery smoke PASS; offline browser 60 desktop + 7 mobile + 1 phone-local journey PASS.
Latest Windows host-operations source: 365609b4d30ca4978ba9480cf3571bb792ad442b; Windows package/fresh-install PowerShell acceptance PASS [run 44892813237]. Exact-head PHPMD, Psalm, Zazu Quality, populated upgrade/rollback and Director Health all PASS [runs 44892813263, 44892813233, 44892813235, 44892813238, 44892813214].
Disposition: **NOT READY** · Evidence maturity: **77/100**. Code implementation does not substitute for owner-PC recovery, physical Android or POPIA evidence.

## Operational reliability implementation — 2026-11-21

- [x] Daily 02:00 Windows backup task, missed-run catch-up, 14-archive retention and protected local destination implemented.
- [x] Startup backup-age warning, business-owner dashboard banner and Founder Control Centre missing/stale archive warning implemented.
- [x] Optional independent USB/synced-folder copy configured by `ZAZU_BACKUP_SECONDARY_PATH`, SHA-256 verified by the runner, with 14-archive retention and failure status; recovery-drill procedure documented.
- [ ] Observe the scheduled backup on the owner's actual PC, copy an archive off-disk and pass the controlled restore drill.
- [x] Owner approver recorded on each newly paired/re-registered device; sync audit entries use the approver as actor.
- [x] Active legacy devices with no recorded approver can be explicitly re-approved by a business owner; historical activity remains unattributed rather than guessed.
- [x] Activity audit labels trusted-device changes as performed via a trusted device approved by the recorded owner.
- [x] Trust and logout prompts disclose that device sync changes are attributed to the approving owner, not necessarily the operator.
- [x] Shared-device/per-user authority limitation and legacy-device attribution boundary recorded in docs/V1_KNOWN_LIMITATIONS.md.
- [x] Query-builder mass-write guard for revision-tracked aggregates added.
- [x] Backup status health calculation refactored below PHPMD complexity thresholds; owner warning and independent-copy status regressions covered by Laravel tests.
- [x] Existing PC installs migrate to LOG_STACK=daily at startup; 14-day log retention is configured, and startup invalidates a stale Laravel config cache before serving so the setting takes effect.
- [x] New registrations and password resets require at least 10 characters; no online breached-password service is required.
- [x] iOS offline limitations are explicitly documented; V1 physical offline acceptance is Android-only.
- [ ] Physical Android cold-launch/restart/reconnect acceptance.
- [ ] Live populated commercial reconciliation.
- [ ] POPIA operational sign-off and ZAZU name clearance.
- [ ] Full PostgreSQL commercial/sync suite only if a PostgreSQL host/pilot is selected; the current V1 owner-PC host uses SQLite.

Evidence maturity remains **77/100**. V1 remains **NOT READY** until the owner-dependent gates above are evidenced.

---

## Stale-write protection closure — 2026-11-21

**Source/test head:** `51a75eb1d06017211bd7808093d1863c930b6d71`.

- [x] Laravel: 634 passed / 1 skipped / 4,399 assertions.
- [x] PostgreSQL migration/recovery smoke.
- [x] Offline browser: 59 desktop Chromium, 7 mobile-viewport and 1 phone-local Work/attachment/reconnect journey passed.
- [x] PHPMD, Zazu Quality, Composer security audit, secret scan and application checks passed. SonarCloud was skipped.
- [x] Paired-device stale-write rejection with authoritative remote snapshot and preserved local payload.
- [x] Same-device causal chains, intervening server writes, rejected-first-mutation rebase, immutable new mutation IDs and conflict payload preservation.
- [x] Online root/nested writer review and parent revision propagation; primary-contact creation mass-update bypass corrected.
- [x] CASE-ZAZU-0033 / ENG-DATA-003 CLOSED.

The full offline sync workflow matrix remains OPEN separately, along with physical Android cold launch/restart/reconnect, intended-PC recovery drill, live-business commercial reconciliation and POPIA operational sign-off. V1 remains NOT READY; evidence maturity is 77/100 (+2 for verified stale-write protection).

[Exact-head workflow](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44881431689).

---

## Receiver reconciliation and bootstrap authorization — 2026-11-21

**Source head:** 559e66b87de8617f967b722b55b0fbfd9b116495.

- [x] Laravel: 632 passed / 1 skipped / 4,388 assertions.
- [x] PostgreSQL migration/registration/recovery smoke.
- [x] PHPMD, Zazu Quality, application, Composer audit, secret scan, Director Health, Push-on-main and Actions/Python/JavaScript analysis. SonarCloud was skipped.
- [x] Receiver bootstrap includes the exact pulled delivery prefix and all primary/related aggregates outside the normal 500-record window.
- [x] Normal bounded collections promote recently revised aggregates while retaining display order.
- [x] Explicit Customer/Work/Quote/Preparation pairing selections remain pinned; targeted delivery cannot silently broaden them.
- [x] Bootstrap revalidates active device status and token hash under business-first locks; revocation and credential-replacement regressions pass.
- [x] Offline-browser acceptance: 59 desktop Chromium passed; 7 mobile-viewport passed; 1 phone-local Work/attachment/reconnect journey passed.
- [ ] Full sync matrix and physical-device cold launch/restart/reconnect remain open. CASE-ZAZU-0034 and CASE-ZAZU-0035 are CLOSED on this exact head.

Evidence maturity is 75/100 (+2) after exact-head backend, PostgreSQL and desktop/mobile browser verification. This does not close broader V1 readiness.

---
## Exact-head automated acceptance — 2026-11-20

- [x] Laravel [44810699348](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44810699348): 623 passed / 1 skipped / 4,283 assertions.
- [x] PostgreSQL migration, registration, database backup/restore and private-file recovery smoke: PASS in the same run.
- [x] Offline desktop Chromium: 57 passed; mobile viewport: 7 passed; dedicated Pixel 7-viewport Work-create/attachment/reconnect journey: 1 passed.
- [x] PHPMD [44810699344], Zazu Quality [44810699254], Director Health [44810699270] and Push-on-main [44810699529]: PASS. SonarCloud skipped.
- [x] Offline account/session transition automated acceptance: current-main desktop E2E + Laravel feature acceptance passed. Physical-device and operational lost-device testing remain distinct.
- [x] Active Work attachment automated acceptance: current-main desktop and mobile-viewport E2E passed, including phone-local file creation, queueing and reconnect sync. Physical Android restart remains open.
- [x] Command-side aggregate revision regression: quote acceptance → Work, payment → invoice, purchase receipt → purchase order/inventory, inventory movement → inventory item, asset allocation → asset passed in `SyncRevisionAggregateTrackingTest`.
- [ ] Full offline sync workflow matrix, physical Android cold launch/restart/reconnect, intended-PC recovery drill, real-business commercial reconciliation, POPIA operations and ZAZU name clearance remain OPEN.

DB-001 is DORMANT / HISTORICAL / NON-CRITICAL by the 2026-11-22 Founder clarification: it occurred during offline event-creation/calendar-related task testing and has not recurred after subsequent offline-architecture work. Root cause remains unproven; it is not an active release blocker. Installation recovery remains OPEN independently. V1 remains NOT READY; evidence maturity is 75/100, with physical Android, intended-PC recovery, real-business reconciliation and POPIA operations still open.

---
## Dormant database incident — owner installation (Founder clarification 2026-11-22)

- **Disposition: DORMANT / HISTORICAL / NON-CRITICAL — not an active V1 blocker.** The Founder clarifies that the occurrence under discussion happened only during offline event-creation and calendar-related task testing. It has not recurred after subsequent offline-architecture work.
- Historical references and diagnostic tooling remain in the record for traceability; they do not create a current investigation task.
- Root cause and fix remain unproven. Do not claim resolution, request captures or spend active engineering time on it unless it recurs on current `main` or materially new evidence appears.
- Windows startup source checks migration status, creates and verifies a full database/private-files backup before applying pending migrations, and verifies final migration state.
- [ ] **Separate V1 gate:** complete the controlled installation recovery drill on the intended Windows installation with representative data and private files. This remains open independently of the dormant DB-001 anomaly.

## Current code follow-up — 2026-11-17

- Job date/time relationships now use one validator for online creation/editing and offline sync.
- The job form uses shared labels, plain-English guidance and browser date/time limits.
- DB-001 and account error copy is simpler; the product-wide language rule is in `docs/ZAZU_PLAIN_LANGUAGE_STANDARD.md`.
- Source and regression tests are committed on `main`; exact-head CI is pending. No readiness uplift is claimed.

## Event scheduling and rental logistics — owner-authorised V1 addition (2026-11-17)
Implementation baseline: `71c8fc5c7aeba392b95e26aee694945b286f2c8e` on `main`.
- [x] Multi-day event date range, start/end time, setup and collection dates persist and validate online.
- [x] Schedule clash warnings identify overlapping active work without blocking an operator save.
- [x] Offline-created schedule fields survive sync and are included in the device workspace bootstrap.
- [x] Calendar displays multi-day events and includes setup/collection logistics days.
- [x] Asset booking defaults use the event setup/collection window; existing overlap and independent-return protections remain intact.
- [x] Exact-head PostgreSQL migration/recovery and desktop/mobile offline-browser regression pass (run 44152431508).

## Offline sync reliability — owner-directed active target (2026-11-20)

Offline synchronization is a separate V1 reliability target from phone pairing and from standalone phone-local launch.

**Verified narrow corrections (2026-11-20):** Offline forms prevent duplicate submission until saved fields change; a changed receipt quantity or payment amount can be deliberately submitted as a new local mutation. The service-worker cache was advanced for the related client changes. Revision-aware stale-write protection is now implemented: the server enforces aggregate revisions, rejects stale snapshots, preserves the remote version and supports safe keep-local rebase/use-server restoration. These corrections do not close the broader sync matrix or physical phone acceptance. The Founder reports that sync is not yet up to standard across supported workflows; do not infer parity from one successful customer/job or attachment journey.

- [x] Complete a static source trace for every currently declared offline entity/operation from UI action → IndexedDB state → mutation queue → server handler → identity mapping → acknowledgement/pull/bootstrap → visible reconciled state. This closes the inventory task only; runtime acceptance remains open.

**Receiver bootstrap completeness correction — 2026-11-21 (implementation committed; exact-head CI pending)**

The receiving client now sends the exact delivery sequence prefix to /api/sync/bootstrap. The controller validates and caps the list; SyncBootstrapDeliveryTargetResolver resolves primary and related aggregates only for the current business and destination device. The builder adds those records to the current receiver snapshot even when they are outside a normal 500-record collection. Ordinary bounded collections are ranked by latest sync-identity update time, fallback to record updated_at, and then returned in the existing display order. The client applies and persists the snapshot before acknowledging the prefix. Service-worker cache v58 delivers the updated precached offline client.


**Writer-audit correction — 2026-11-21:** primary-contact creation previously used a relationship mass update that bypassed parent Customer revision tracking. The create path now locks and updates existing primary contacts through their models, with a regression asserting revision advancement. Exact-head Laravel on `51a75eb` passed **634 / 1 skipped / 4,399 assertions**; PostgreSQL migration/recovery, PHPMD and desktop/mobile browser acceptance passed. The two-paired-device stale-write regression is included. CASE-ZAZU-0033 / ENG-DATA-003 is CLOSED; the complete offline sync matrix remains OPEN.

Regression tests cover revision-ranked refresh, delivery inclusion outside 500, cross-device sequence isolation, commercial second-device reconciliation, receipt/inventory reconciliation, and browser pull → targeted bootstrap → acknowledgement ordering. CASE-ZAZU-0034 / ENG-DATA-004 records this defect. CASE-ZAZU-0033 / ENG-DATA-003 is now CLOSED; the complete V1 offline-sync matrix remains OPEN.

**Source-level sync path map — verified against main on 2026-11-21**

Shared transport: `resources/js/app.js::setupZazuOfflineForms` and the local customer/Work editors build the supported payloads. `public/zazu-offline.js::queueMutation` persists each mutation alongside the business-scoped workspace in IndexedDB; `pending` orders the queue by creation time and `push` sends batches to `SyncController::push`. The server records mutation identity/sequence, applies it under the business → device → mutation transaction/lock order, and publishes receiver deliveries atomically. `OfflineDomainMutationHandler::apply` dispatches the entity-specific handler. Model results are registered through `SyncEntityIdentityRegistry` and returned as canonical IDs; command effects without their own model identity are reconciled through their owning records. On the receiving device, `SyncMutationProtocol::pull` returns only a contiguous delivery prefix; the client fetches the authoritative bootstrap snapshot, applies it only when its local queue/conflicts are clear, then acknowledges the contiguous delivery sequence.

| Entity / operation | User-facing offline entry | Local state target | Server handler | Identity / reconciliation |
|---|---|---|---|---|
| Customer — create/update | `customers/create`, `customers/edit`; local customer relationship editor | `customers` | `customer` | Customer identity; canonical server ID returned and refreshed from bootstrap |
| Job — create/update | `work/create`; local Work inspector editor | `jobs` | `event` | Job identity; requirement children reconcile inside the job snapshot |
| Quote — create | `work/quotes/create` | `quotes` | `quote` | Quote identity; quote version/lines are part of the refreshed quote snapshot |
| Quote acceptance — command | Dashboard quote panel and `quotes/show` acceptance form | Existing quote status/version | `quoteAcceptance` | No separate command identity; accepted quote state is confirmed by bootstrap |
| Supplier — create/update | `suppliers/create` (create/edit form) | `suppliers` | `supplier` | Supplier identity and canonical ID |
| Service/capability — create/update | `capabilities/create`, `capabilities/edit` | `catalogue` | `capability` | Service identity and canonical ID |
| Inventory item — create | `inventory/create` | `inventory_items` | `inventoryItem` | Inventory-item identity and canonical ID |
| Asset — create/update | `assets/create` (create/edit form) | `assets` | `asset` | Asset identity; allocations are nested in the asset snapshot |
| Expense — create | `finance/expense-create` | `expenses` | `expense` | Expense identity and canonical ID |
| Payment — create | `finance/payment-create` | `invoices[].payments` | `payment` | Nested payment identity; client resolves it through its parent invoice |
| Preparation — create | `preparation/create` | `preparations` | `preparation` | Preparation identity and canonical ID |
| Event cost — create | `costs/create` | `costs` | `eventCost` | Event-cost identity and canonical ID |
| Inventory movement — create | Inventory movement forms on `inventory/index` | `inventory_items[].movements` and on-hand quantity | `inventoryMovement` | Nested movement identity; client resolves through the inventory item |
| Purchase receipt — command | Receive form on `purchasing/show` | Purchase-order items and inventory/movement state | `purchaseReceipt` | No separate receipt identity; receipt effect is reconciled through purchase order and inventory snapshots |
| Invoice — create | `finance/invoice-create` | `invoices` | `invoice` | Invoice identity and canonical ID |
| Asset allocation — create/return | Allocation/release forms on `assets/index` | `assets[].allocations` and asset status | `assetAllocation` | Nested allocation identity; client resolves through the asset |
| Purchase order — create | `purchasing/create` | `purchase_orders` | `purchaseOrder` | Purchase-order identity; items are nested under the order |

**Coverage boundary recorded from source:** the UI currently exposes offline update forms for customers, Work, suppliers, services/capabilities and assets. The server also accepts update/upsert operations for preparation items, quote/draft versions, inventory items, event costs and purchase orders, although their current offline UI entry is create-only. These handlers remain part of the server protocol contract and must receive stale-write protection rather than being assumed create-only. The local list/detail renderer remains concentrated on Customers, Work and the dashboard quote panel; for other entities, an IndexedDB record or queued handler does not prove an offline directory/detail screen. Quote acceptance and purchase receipt are commands whose effects are reflected through their parent records, not standalone synced records. Command effects can also change other synchronised aggregates (for example invoice payments, inventory receipts/movements and asset allocations); their idempotency and atomicity must be retained while affected revisions advance.

- [ ] Verify every supported mutation is durable across reload, force-close and restart, and remains visibly pending until the server confirms it.
- [ ] Verify safe retries for transient/offline/timeout/partial responses; duplicate and reordered delivery must not duplicate consequential records or lose queued work.
- [ ] Verify stable local identities, parent/child references, business scoping, idempotency and server canonical IDs across create → update → related mutation sequences.
- [ ] Verify pull cursors, pagination, acknowledgements and bootstrap/re-bootstrap do not skip or replay changes across devices and businesses.
- [ ] Verify conflict detection and retry/discard handling preserve both authoritative and local data; consequential conflicts must not be silently overwritten.
- [ ] Verify concurrent edits from two trusted devices cannot silently overwrite a newer server record across every aggregate and indirect writer. The revision/predecessor protocol is implemented and has focused server regression coverage; complete the exhaustive writer/transaction audit and end-to-end cross-device replay. Confirm stale conflicts preserve both local and remote values, safe rebase creates a new immutable mutation ID, and sequential same-device edits chain to the actual predecessor result.
- [ ] Verify attachment bytes, metadata, job linkage, checksums/idempotency, retry and download/open behavior, including the supported offline-created Work linkage path.
- [ ] Verify supported financial, inventory, purchase/receipt, schedule and asset mutations preserve server validation, money/date precision and lifecycle rules.
- [ ] Run targeted server/client regression coverage and the full exact-head Laravel + browser acceptance suites; then verify representative sync journeys on the actual PC and phone.
- [ ] Record every unsupported offline operation as online-only or otherwise explicitly limited. Do not imply full app-wide offline parity.

**Latest application test evidence:** Laravel PASS — 621 passed / 1 skipped / 4,271 assertions; PostgreSQL migration/registration/backup/restore/private-file recovery PASS on `5b56a7c`. Offline-browser acceptance on that commit: 56 passed, 1 failed because the final Settings conflict-success message was hidden after the last conflict resolved. The UI correction is committed on `f50fcb1`; exact-head browser acceptance is pending. Do not report the earlier 526bdf3 test count as current.

**Sync source corrections verified in this batch:** preserved failed/conflicted local records from bootstrap replacement; fixed canonical identity reconciliation for event costs, nested payments, inventory movements and asset allocations; applied disconnected stock receipts to local inventory balance/history; aligned final offline payment with paid invoice status; and scheduled retryable attachment failures through existing backoff while preserving idempotency. Targeted E2E coverage is included.

**Still unaccepted:** complete sync coverage across all supported operations; exhaustive online/indirect/bulk writer audit; exact-head browser acceptance; actual Android standalone offline launch/restart/reconnect. Revision-aware stale-write protection is implemented but does not close the full sync gate. PC-to-phone pairing remains CLOSED. Overall V1 remains NOT READY.

## Release acceptance gates
- [ ] Physical Android acceptance: secure-origin install, disconnected cold launch/restart, local records/attachments and reconnect sync on actual device.
- [ ] Installed-target recovery: controlled database + private-media backup/restore on intended Windows installation.
- [ ] Live commercial reconciliation: populated quote → invoice → deposit/final payment → purchasing/costs → completion → profit/margin.
- [ ] POPIA operational sign-off: privacy notice, retention/deletion, access controls and incident procedure.

## Event scheduling implementation evidence — 71c8fc5

- Laravel: PASS — 532 passed / 1 skipped / 3,714 assertions.
- PHPMD: PASS.
- Windows candidate build, fresh PowerShell install and SQLite/private-media recovery: PASS on 2ef2a0b, the same application-code implementation before the final regression-test-only commit.
- PostgreSQL migration/recovery and desktop/mobile offline-browser acceptance: PASS on exact source HEAD 71c8fc5 (run 44152431508).
- Zazu Quality: RED on GHSA-fc5q-39xc-vjpf / CVE-2026-112366. No patched version is published; no advisory suppression or breaking Vite downgrade applied.

## Historical CI verification — fd82f6a (superseded by newer evidence recorded above)

- Application-code baseline a5ead69: Laravel 526 passed / 1 skipped / 3,681 assertions; offline browser acceptance 45 desktop + 6 mobile-viewport + 1 phone-local attachment; PostgreSQL migration/registration/backup/private-file recovery passed.
- Repository head fd82f6a changes only the Windows package workflow after that application-code baseline.
- Windows V1 Package run 82: PASS — candidate package built; Windows PowerShell first install passed; Windows-native backup/restore recovered both a disposable SQLite record and private-media bytes.
- Director Health, Director Contract Lint and Push-on-main passed.
- LAN asset delivery regression passed at a5ead69: a remote client with a stale loopback Vite hot marker receives compiled same-origin CSS/JS. The supplied 13 Nov HTTP screenshot predates this correction; physical Android HTTPS launch remains required.
- Zazu Quality passes with a temporary, exact-advisory exception for GHSA-fc5q-39xc-vjpf / CVE-2026-112366 in the picomatch build-tool dependency chain. The underlying advisory remains open/unpatched; the audit script allows only this finding and its exclusive dependency chain. Composer security audit and secret scan passed. No breaking Vite downgrade was applied.
- The Windows CI recovery test is supporting evidence only; it does not close the controlled recovery drill on the intended installed target.
## Closed acceptance
- [x] Windows fresh-install candidate package, PowerShell first-install smoke and Windows-native SQLite/private-media backup/restore — Windows V1 Package run 82 passed at fd82f6a; not an updater or hosted deployment.
- [x] Seeded commercial workflow automation — verified; reopen only on reproducible defect.
- [x] Offline financial mutation precision/rejection — quote lines, payments, purchase orders/receipts, event and asset costs, expenses and inventory movement use strict cents/hundredths validation.
- [x] Desktop trusted-device consent — fresh browsers do not auto-register; owner explicitly trusts the current PC from Settings.
- [x] Destructive offline workspace cleanup revocation retry — only the installation UUID is retained after an unreachable host; the next authenticated owner session retries the owner-scoped revocation. Ordinary web logout remains non-destructive.
- [x] Password reset revokes active and pending devices across all owner businesses, clears token/pairing credentials and audits each business; staff-only memberships are excluded.
- [x] Serialized-asset date availability — online/offline tests pass for non-overlap, overlap, open-ended dates and independent returns at 00af9adda.

## Current release position
V1 remains **NOT READY**. The latest exact-main verification is recorded at the top of this checklist for HEAD `aa93a470`: Laravel 678 passed / 1 skipped / 4,655 assertions; PostgreSQL migration/rollback/registration/recovery smoke passed; offline browser acceptance passed (63 desktop, 7 mobile viewport, 1 simulated host outage, 1 phone-local attachment/reconnect); Windows fresh-install acceptance passed; quality, dependency, secret, documentation-integrity and compressed frontend bundle-budget checks passed. SonarCloud and prerelease publishing were skipped. Evidence maturity remains **77/100**, unchanged because physical-device, owner-PC recovery, live-business commercial and POPIA/brand acceptance remain open.


## Scheduling exact-head acceptance closure
- GitHub Actions run `44152431508`: Laravel, PostgreSQL migration/recovery and offline-browser jobs all succeeded.
- Offline browser job `134284080089`: desktop/browser, mobile-viewport Work/customer and phone-local attachment journeys passed.
- PostgreSQL job `134284080622`: migrations, account/workspace registration and backup/restore/private-file recovery passed.
- Laravel job `134284080768`: service-worker/session contracts, LAN asset-origin checks, Blade/manifest/config checks and PHPUnit/Pest passed.
- Scheduling engineering/CI subgate: CLOSED. Physical Android installation and disconnected cold-launch/reconnect remain a separate owner-operated gate.


### DB-001 diagnostic contract clarification — 2026-11-17

The incident logger records privacy-minimised metadata only (request ID, classification, exception class, route/action, method/path, timestamp and source file/line). It intentionally excludes exception messages, SQL, bindings and stack traces. The incident-framework documentation has been corrected to match this implementation.

At that historical checkpoint, the owner-installation entry matching `aa9080f4-9580-41c9-a643-439b31928ffa` was requested to identify the exception class, source file/line and route. That request is superseded by the 2026-11-22 Founder clarification at the top of this checklist; do not pursue it unless DB-001 recurs on current `main`.


### Backup-first verification hardening — 2026-11-17

- [x] Backup creation now reopens the completed ZIP and validates ZIP consistency, manifest/driver, exact payload membership and every SHA-256 checksum before publishing it.
- [x] Added a regression for valid archive acceptance and tampered private-payload rejection.
- [ ] Exact-head Laravel and Windows workflow verification.
- [ ] Run backup-first migration on the intended installation and confirm the previously failing workflow succeeds.


### Zazu quality gate — 2026-11-17

- [x] Exact-head quality workflow run `44179163841` passed on `76be1f234e7726352c2e15b511b37b9084d2ddfa`: application, Composer security audit and secret scan.
- [x] Application job passed dependency installation, the scoped npm audit policy and service-worker authentication cache-race tests.
- [ ] Replace the temporary GHSA-fc5q-39xc-vjpf exception when a verified picomatch fix is published or the advisory is withdrawn. This exception is not a dependency fix.


### New-job form regression closure — 2026-11-17

- [x] Restore required Work / event name input on the new-job form; controller validation and persistence remain unchanged.
- [x] Keep the offline-browser acceptance tests intact; all three previously blocked journeys now pass.
- [x] Exact-head Laravel run `44184183003`: Laravel, PostgreSQL migration/recovery and offline-browser jobs all PASS, including mobile customer-to-Work and phone-local attachment acceptance.
- [x] Zazu Quality `44184183085`, Windows package + PowerShell fresh install `44184182900`, Director Health `44184182984` and Push-on-main `44184180721`: PASS. SonarCloud skipped.
- Historical status at this checkpoint: DB-001 was listed as open. That status is superseded by the 2026-11-22 Founder clarification at the top of this checklist; V1 remains NOT READY for its separate acceptance gates.


### Historical DB-001 diagnostic work — 2026-11-18 (superseded disposition)

This section records the diagnostic tooling added at the time. Its original open-task wording is superseded by the 2026-11-22 Founder clarification at the top of this checklist; it is not current work.

- Historical reference retained: `895e2c23-bc6b-4919-a891-1e8e5c1e27e4`; no verified root cause or fix was established.
- [x] Safe, reference-specific Windows log extraction exists at `ops/windows/Get-ZazuIncident.ps1`; output is restricted to allowlisted incident metadata and database diagnostics.
- The read-only schema utility remains available only if a fresh, reproducible recurrence later requires diagnosis. Do not run it now or request another owner-side capture.
- Do not reset, reinstall or alter business data as an investigative step.


### COM-01 customer document handoff — PARTIAL (2026-11-18)

- [x] Signed customer quote view supports browser Print / Save as PDF.
- [x] Quote detail provides WhatsApp handoff to the signed customer view.
- [x] Issued/paid invoice detail provides WhatsApp handoff to a seven-day signed read-only invoice view; the view supports browser Print / Save as PDF.
- [x] Invoice-linked payment receipts have a signed, read-only customer view with WhatsApp handoff and browser Print / Save as PDF.
- [x] Online invoices and offline invoice sync share consecutive business/month-scoped numbers in the existing `INV-YYYYMM-` format; offline records refresh to the canonical server number and voided numbers are not reused.
- [ ] Tax-adviser/legal review of invoice numbering and tax-invoice presentation.
- [ ] Direct generated PDF-file attachment and desktop/Android practical acceptance remain open.
- DB-001 is DORMANT / HISTORICAL / NON-CRITICAL. The Founder clarifies it occurred during offline event-creation/calendar-related task testing and has not recurred after subsequent offline-architecture work. The separate installation-recovery gate remains OPEN.


### Offline revision-aware sync hardening — 2026-11-20

- [x] Server-side monotonic aggregate revisions and stale-update rejection are implemented.
- [x] Same-device mutation chains use actual predecessor result revisions; unresolved predecessors remain queued/retryable instead of creating duplicate conflicts.
- [x] Keep-local conflict resolution uses a new immutable mutation ID; use-server resolution records the current revision for later dependent edits.
- [x] Parent revision advancement covers quote acceptance → Work and purchase receipt → affected inventory items, as well as payment, inventory movement and asset allocation parent changes.
- [x] Push acknowledgements expose all affected related identities; client reconciles them even when the command has no standalone identity.
- [x] Regression coverage added for cross-device stale writes, same-device chains, conflict rebase/discard, cross-record command revisions and multi-aggregate browser acknowledgement.
- [x] Service-worker cache v51 and matching test fixtures.
- [x] Exact-head offline browser acceptance including the new related-aggregate case — run 44810699348, SHA 041314f.
- [ ] Final exhaustive inventory of indirect, queued and command-side writers.
- [ ] Physical installed-device offline/restart/reconnect acceptance remains a separate owner-operated gate.

Exact-head Laravel, PostgreSQL migration smoke, PHPMD, Zazu Quality, Director Health, Push-on-main and offline browser/mobile acceptance passed on `041314f` (run 44810699348). CASE-ZAZU-0033 remains OPEN and V1 remains NOT READY until the remaining gates are evidenced. This implementation increment does not uplift the readiness score.


## Director verification — 2026-11-21 (finance total integrity)

**Verified `main` HEAD:** `c43ab8db309a2ccedd48d2298077e3a602b72a5d`.

- Finance dashboard invoiced totals now exclude draft invoices; only issued and paid invoices are included.
- Regression proves a 999.00 draft is excluded while 100.00 issued + 200.00 paid produces exactly 300.00.
- Exact-head Laravel, offline browser acceptance (including mobile viewport, host-outage monitor and phone-local attachment journey), PostgreSQL migration/recovery, Zazu Quality, PHPMD, Director Health and Push on main: **PASS**.
- SonarCloud: **SKIPPED**, not counted.

**Release decision:** This closes one financial reporting defect only. V1 remains **NOT READY** pending physical Android acceptance, owner-operated installed-target recovery, populated live-business commercial reconciliation and POPIA operational controls.


### Quote single-unit safety — implementation checkpoint 2026-11-23

- [x] Default one-quantity starter lines render blank in quote entry and require the operator to enter the intended quantity.
- [x] An entered quantity of exactly one requires a per-line confirmation on both new quote and draft revision screens.
- [x] Server-side validation rejects quantity one without confirmation on both actions.
- [x] Regression tests cover rejection, explicit confirmation and revision-update behaviour.
- [ ] Exact-head Laravel, PHPMD, Zazu Quality/Pint and browser acceptance for this implementation commit.


### Quote single-unit browser coverage — 2026-11-23

- [x] Added a desktop Playwright journey using the actual Job and Quote forms.
- [x] Wired the journey into the per-push Laravel workflow.
- [ ] Exact-head browser/Laravel CI passes on the current main commit.
