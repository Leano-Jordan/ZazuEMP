## Helper event-component context slice — 2026-11-23

**Implemented directly on `main`:**
- On Job detail only, Helper now reads the current event's saved `EventRequirement` records and surfaces up to four distinct component descriptions under “This booking includes”.
- The query is anchored to the route's current `Event`, verifies that event belongs to the active business, and scopes any capability fallback lookup to that same business.
- Added a feature test with a tent/chair booking to assert the actual saved components are shown and unrelated catering/camera prompts are absent.
- This is event-scope awareness, not a new workflow engine. It does not yet infer component-specific preparation actions, change navigation, hide modules, or implement preset composition.

**Verification:** source/test changes read back from `main`; exact-head CI remains unverified. Physical acceptance remains outstanding.

**Next target:** derive Helper's actionable prompts from existing event component types and their real linked operational records (assets/allocations, preparation, purchasing, costs and delivery). Only recommend work when the underlying record and permission support it. Avoid generic “all modules” guidance.

---

## Helper business-focus guidance slice — 2026-11-23

**Implemented directly on `main`:**
- The existing saved `primary_niche` preference now selects the Helper's third guidance step on Dashboard, Jobs and Job detail for `chairs_tents`, `sound_dj` and `catering_baking`.
- The copy reflects relevant operational concerns: hire quantities/availability/logistics/asset condition; sound equipment/crew/programme/setup/transport; or menus/quantities/dietary needs/purchasing/preparation/delivery.
- The Helper exposes the resolved focus as a semantic data attribute for verification.
- Added `ZazuHelperTest::test_helper_guidance_adapts_to_the_saved_business_focus` for all three configured focus values.
- This is a presentation/guidance slice only. It does **not** implement multi-preset selection, capability-driven navigation, event-component-specific Helper state, or full workspace adaptation. It does not alter permissions, authorization, records or offline sync.

**Verification:** source and test files read back from `main`; exact-head CI has not been run/verified. Do not mark this slice green until the normal GitHub Actions checks complete. Physical PC/phone/offline acceptance is unchanged.

**Next target:** map existing primary focus, active catalogue categories, event requirements and operational records into a single capability-to-surface matrix. Then extend Helper relevance from primary focus to the actual components on the current event, without duplicating workflow state or treating every possible step as mandatory.

---

## Adaptive business presets and event-component experience — 2026-11-23

**Founder requirement recorded as mandatory product direction:** Zazu must shape its workspace around the business capabilities selected by the owner. A DJ, tent/chair hire operator, caterer, photographer and multi-service event provider must not be forced through one generic service catalogue or cluttered workflow. Business selections must materially affect relevant navigation, catalogue defaults, event intake, requirements, operational surfaces, reporting and Helper recommendations.

- Added the product contract: [Adaptive Business Presets and Event Components](../../docs/ADAPTIVE_BUSINESS_PRESETS_AND_EVENT_COMPONENTS.md).
- Updated README product direction so this cannot be mistaken for a generic catalogue-only feature.
- Guardrails: presets are configurable starting points; multiple capabilities compose into one workspace; event components determine relevant downstream work; irrelevant options are progressively disclosed rather than destructively removed; disabling capabilities preserves historical/active records and accepted quote snapshots; visibility never replaces authorization; offline parity must be proven.
- **Current status: DOCUMENTED / IMPLEMENTATION NOT YET VERIFIED.** No claim that adaptive presets or complete specialist workflows already exist.
- **Next execution target:** inspect the current capability configuration/catalogue source, onboarding and business settings, navigation, event requirements, Helper recommendation sources and offline synchronization. Produce a capability-to-surface map from actual code, then implement one narrow preset-to-Helper vertical slice without introducing a competing configuration source.
- Continue Helper hardening in that slice: recommendations must be relevant to the business's enabled capabilities and the current event's selected components, while reusing existing permission-filtered state and routes. Do not invent workflow states or recommend disabled modules.

---

## Zazu Helper — origin-aware operational recommendations — 2026-11-22

**Implementation:** committed directly to `main`. Latest implementation/test commits: [f11ca76](https://github.com/Leano-Jordan/ZazuEMP/commit/f11ca767110e10e8a2e98f412947321bb73da86b), [670703c](https://github.com/Leano-Jordan/ZazuEMP/commit/670703cf706c9a50cd88187ff9c61f9b6d0bca4f), [4a4513b](https://github.com/Leano-Jordan/ZazuEMP/commit/4a4513b2e340e2096bc4774faf817c59c326eb4a), [df4e304](https://github.com/Leano-Jordan/ZazuEMP/commit/df4e304169e06bbd6c00ff10d2da089c290605b3) and [a8bc03b](https://github.com/Leano-Jordan/ZazuEMP/commit/a8bc03b72f60b49d959ce664a9f542544b18a4d5).

- Helper recommendation priority: use the Job detail page's existing state-derived `work.next-action`; on other guided workspaces, promote the first server-rendered attention item from `ZazuHelperService`.
- Attention source remains business-scoped and permission-filtered by the existing service. The Helper does not calculate new business state or create another workflow authority.
- Recommendation links are accepted only when same-origin. They are shown only after the existing origin-health monitor confirms server availability; on outage they pause, and the offline explanation clears after reconnection.
- Added semantic source attributes to the existing attention list; existing visible attention content and links remain intact.
- The recommendation card uses workspace-neutral wording because its source may be a job action or a dashboard/purchasing/finance attention item.
- Repaired a malformed PHP assertion chain in `ZazuHelperTest` that would prevent the feature test file from parsing.
- Added Laravel assertions for the attention source and Playwright coverage for online recommendation promotion, outage suppression and reconnect recovery across desktop/mobile browser projects.

**Evidence status: IMPLEMENTED, EXACT-HEAD CI PENDING.** All changed files were read back from `main`. The Founder reports the previous checks were green; these additional commits were made afterward and require a fresh exact-head run before they can be called green. No physical Android or owner-PC recovery evidence is inferred from browser automation.

---

## Commercial event workflow benchmark and intake correction — 2026-11-22
### Requirement correction nuance

The online Requirements page and route set expose list/create only, and online `WorkController::update()` does not apply a submitted services list. However, `OfflineDomainMutationHandler::syncOfflineJobRequirements()` already reconciles a submitted offline service list, including removals while preserving matching rows' detail fields. Treat this as an online discoverability/capability gap with existing offline semantics—not as proof that the domain has no correction behavior. Any implementation must unify online/offline behavior and preserve quote snapshots. See the benchmark report's corrected finding.


**Current-main correction — requirement lifecycle now implemented:** the initial source snapshot above is historical and has been superseded. Current `main` exposes edit/update/remove routes and controls in the Requirements workspace. `RequirementController` applies business scoping and event/requirement ownership checks, locks the event inside a transaction, prevents scope changes after quote acceptance, prevents removing the final quoted requirement, and preserves quote-version snapshots when a requirement is changed or removed. `WorkPlanningWorkflowTest` covers online correction, removal with detached historical quote lines, revision generation, final-line protection and accepted-quote scope locking. The earlier statement that online correction was unavailable is no longer current. Do not reopen this as an unimplemented feature; audit only if new evidence exposes a gap.


**Research:** [Director Commercial Event Workflow Benchmark](../../docs/DIRECTOR_COMMERCIAL_EVENT_WORKFLOW_BENCHMARK_2026-11-22.md)

Reviewed official workflow material from Caterease, Total Party Planner, Planning Pod and HoneyBook, plus practitioner discussions on Reddit. Common patterns: guided intake; event-centred home; visible upcoming/waiting/overdue/completed work; contextual document hand-offs; reusable templates; conditional/parallel operational work. The research distinguishes vendor feature claims from documented workflow steps and community anecdotes.

**Direct Zazu findings:**
- New-job intake previously asked for service scope before date/time/location. Caterease's published onboarding sequence places event facts before menu building.
- The new-job form's service selections are persisted immediately as starter `EventRequirement` rows with quantity 1 and unit `service`; that behavior was not disclosed clearly.
- The online requirements controller/routes expose list/create only; no update/delete UI route was found. Correcting an initially selected requirement therefore needs a carefully guarded workflow, especially when quote snapshots exist.
- The Job home progress strip and Next action remain too coarse for the full commercial/operational lifecycle.
- Visible terminology mixes Job, Work and Event.

**Implemented on main:**
- Reordered new-job form: customer → job name/type → when/where → service scope.
- Removed duplicate job-type heading and added an explicit Event type legend.
- Clarified that selected services become starter requirements and must be reviewed for quantity, unit and special details before quoting.
- Clarified the next-step panel and service review step labels.
- Aligned the Job name label between create and edit forms.
- Added `WorkWorkflowTest` coverage for intake order, starter requirement persistence and create/edit naming.

Application behavior/data model and existing routes were not changed. The change is a narrow presentation/copy correction. Exact-head tests and CI are pending verification; do not claim a pass until the run completes.

**Next engineering priority:** validate the state-derived Job-home orientation model and Helper recommendations against the full quote → customer decision → invoice/payment → delivery/purchasing/receiving → costs/profit → close-out lifecycle. Keep conditional and parallel work visible without forcing every job through a rigid sequence. Do not move module destinations or perform a broad navigation rewrite.

---

## Founder workflow-discovery clarification — 2026-11-22

The Founder clarifies that "separated features" means the user does not see an obvious business-process trail. They move between pages and click around without confidence that they are following the right order. This is a workflow orientation/discoverability concern, not evidence that the individual modules are broken or should be merged.

**Workspace count correction:** the populated fictional workspace contains **4 customers and 2 jobs**. This supersedes the earlier screenshot-derived count of 2 customers. Founder confirms normal connected use on PC and linked mobile.

Source inspection identified a narrow Job-page next-action decision, a five-stage progress strip that omits several consequential business states, an operational chain that exposes destinations without explaining their sequence, and a floating Helper that provides optional page guidance rather than a persistent state-aware journey. Details and acceptance criteria: [Director Workflow Discovery Review](../../docs/DIRECTOR_WORKFLOW_DISCOVERY_REVIEW_2026-11-22.md).

**Director guardrail:** do not move, rename, merge or reorder existing modules/navigation based on this finding alone. First trace actual lifecycle transitions, prerequisites, permissions and conditional/parallel work. The safe direction is a job-contextual, state-aware orientation layer over existing records and routes, validated with a human walkthrough before broader navigation changes. No duplicate workflow authority or new source of truth.

No application code changed in this review. No readiness-score uplift is claimed.

---

## Founder acceptance evidence update — 2026-11-22

**Repository baseline reviewed:** [`cd7762a`](https://github.com/Leano-Jordan/ZazuEMP/commit/cd7762a18976eeae7cef6d2c4fb132f69b32b0f5).

Founder reports that Zazu EMP has been physically used on both PC and linked mobile, operating against the same workspace. The fictional workspace already contains customer/client, quote, invoice, supplier, purchase-order and inventory records; the Founder reports these areas work as intended. Dashboard screenshots reviewed show two customers, one active event and connected workspace-health indicators.

The Founder notes that the business areas can feel operationally separate/disjointed, while still working as intended in their respective modules. Record module-level functionality as owner-reported; do not infer a fully integrated end-to-end transaction chain from the populated workspace alone.

Acceptance ledger:
- PC normal connected operation: **OWNER-VERIFIED PASS**.
- Linked mobile normal connected operation: **OWNER-VERIFIED PASS**.
- Shared workspace use: **OWNER-VERIFIED PASS**.
- Business module population/functionality: **OWNER-REPORTED FUNCTIONAL**.
- All supplied test records are fictional per Founder.

Do not send the Founder back through basic setup or ask them to recreate records. Detailed transaction screenshots were not supplied, so do not claim quote-to-profit reconciliation, payment idempotency, stock receipt reconciliation or exact margin validation. Physical connected use does not by itself prove cold-start host outage, fully disconnected offline write/relaunch/reconnect, attachment round-trip, or installation-wide PC backup/restore. Preserve these as separate evidence gates. The supplied dashboard images are visual workspace evidence, not transaction-level evidence.

## Business archive invoice-sequence portability — 2026-11-22

**Implementation:** [e52d1bf](https://github.com/Leano-Jordan/ZazuEMP/commit/e52d1bfd0f8fbbfa4148f05ad77ed3a387c8124f). **Legacy compatibility:** [c8afa0e](https://github.com/Leano-Jordan/ZazuEMP/commit/c8afa0e4117980519463574917f1c8cdce4ae831). **Regression coverage:** [c9f0c7b](https://github.com/Leano-Jordan/ZazuEMP/commit/c9f0c7b8e113eec67d93d77a22a8a0150882bb69).

- Added `invoice_number_sequences` to the tenant-scoped business archive dataset. Its rows are remapped to the destination business and are included in the empty-target check.
- Existing format-v1 archives created before this addition remain restorable. When the only missing dataset is `invoice_number_sequences`, the reader supplies an empty dataset; the invoice generator reconciles its next allocation against restored invoice numbers.
- Regression coverage verifies both that the counter survives export/import and that the real generator allocates the expected next invoice number, plus that a pre-sequence archive remains importable.
- Exact Laravel workflow [45022740123](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45022740123): **682 passed / 1 skipped / 4,712 assertions**. PostgreSQL migration/recovery and PHPMD passed. Final-head Zazu Quality [45022856858](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45022856858): formatting, application checks, secret scan and Composer audit **PASS**. SonarCloud **SKIPPED**.
- This closes an archive-portability gap; it does not close the owner-operated off-disk installation recovery gate or change the V1 readiness score.

---

## DB-001 disposition — Founder clarification (2026-11-22)

**DORMANT / HISTORICAL / NON-CRITICAL — not an active engineering task or V1 release blocker.** The Founder clarifies that the DB-001 occurrence under discussion happened while testing offline event creation and a calendar-related task. It has not recurred since the subsequent offline-architecture work. It is not a confirmed current database defect, and no root cause or fix is claimed.

Do not chase this historical anomaly, request diagnostic captures, or ask the Founder to run `Test-ZazuDatabaseSchema.ps1` or `Get-ZazuIncident.ps1`. Reopen only if the same failure is reproduced in a supported workflow on current `main`, or materially new diagnostic evidence appears. The separate installation backup/restore acceptance gate remains independent and must not be conflated with DB-001. All later dated DB-001 OPEN/active-priority notes in this file are historical snapshots superseded by this disposition; their earlier account/workspace wording is also superseded by the Founder clarification.

## Director recovery integrity checkpoint — secondary backup presence — 2026-11-22

**Implementation head:** [c992b98](https://github.com/Leano-Jordan/ZazuEMP/commit/c992b986497ca1c233437212d55b3e93ad29c455).

- Closed a backup-health false positive: the dashboard previously trusted a recent `status=verified` timestamp without checking whether the secondary archive still existed.
- Windows runner now persists the secondary archive's absolute path, SHA-256, size and last-write timestamp. PHP health checks require a valid absolute path, a regular non-symlink file, and matching recorded size/timestamp. Missing/unavailable/changed metadata becomes **ATTENTION**.
- SHA-256 is verified during copy. The dashboard does not re-hash a multi-gigabyte archive on every page load; it checks presence and recorded metadata. It also cannot prove a destination is physically off-disk or that cloud sync completed; operator configuration/verification remains required.
- Exact-head Laravel: **679 passed / 1 skipped / 4,671 assertions**; `InstallationBackupStatusTest` passed, including missing and changed secondary archive cases.
- Exact-head Zazu Quality (formatting, Composer audit, secret scan and application checks), PHPMD, Psalm, populated upgrade/rollback, PostgreSQL migration/rollback/registration/backup-recovery, Windows installation candidate build and fresh PowerShell install: **PASS**. SonarCloud: **SKIPPED**.
- Windows PowerShell 5.1 scheduled-backup runner test [44988789562](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44988789562) passed its metadata persistence and failed-copy preservation assertions.
- Exact-head offline-browser acceptance [44989474410](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44989474410): **63 desktop + 7 mobile viewport + 1 host-outage + 1 phone-local attachment/reconnect passed**; public/auth accessibility acceptance passed.
- Evidence maturity remains **77/100**. V1 remains **NOT READY** until the owner PC proves a scheduled backup, genuinely independent copy and controlled full restore with representative private media.

**Next active target:** DB-001 is not an active target. Continue V1 convergence from current `main` against reproducible defects and the remaining genuine acceptance gaps. Do not request or run DB-001 diagnostics unless the failure recurs on the current offline architecture. Owner-operated commercial and recovery acceptance remain separate release gates.

---

## Director security review — offline session and trusted-device transitions — 2026-11-22

**Reviewed source/test head:** [8803746](https://github.com/Leano-Jordan/ZazuEMP/commit/88037465ddacbc4b60a81efe343e4210b6b1dc02). Exact-head Laravel/browser/PostgreSQL [44987764883](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44987764883) **PASS**.

- Traced login/session expiry, explicit logout, interrupted logout, business switching, device revocation/expiry, stale bootstrap responses, re-pair token replacement, in-flight sync pushes and attachment response races.
- Existing coverage includes `e2e/offline-session-transition.spec.js` (unreachable logout, expiry/re-authentication, owner/manager account transition, workspace switching), `e2e/pwa-offline.spec.js` (late bootstrap cannot overwrite the active workspace), `e2e/offline-attachment-sync.spec.js` (old push and late attachment responses after re-pair/logout), and `tests/Feature/SyncDeviceManagementTest.php` (owner revocation, expired bearer rejection, idle-expiry renewal).
- Client guards increment session generations, abort active sync, compare the request token again after responses, and reject stale state writes. The service worker invalidates user/business page caches only after confirmed logout and refuses to mask online 401/403 with stale authenticated HTML. Server device APIs independently enforce active, unexpired, owner-revocable device credentials.
- **No new confirmed defect found.** The business-scoped trusted-device token intentionally survives ordinary web-account logout under DEC-019; this is an owner-approved shared-device model, not per-staff offline isolation. Owner revocation remains the control for lost/reassigned devices. Do not silently change this architecture.
- Exact-head evidence: Laravel **679 passed / 1 skipped / 4,665 assertions**; offline browser **63 desktop + 7 mobile viewport + 1 host-outage + 1 phone-local attachment/reconnect passed**; PostgreSQL migration/recovery, Zazu Quality, PHPMD, Director Health and Push-on-main **PASS**. SonarCloud **SKIPPED**.
- This is automated evidence, not physical lost-device/Android proof. Evidence maturity remains **77/100**; V1 remains **NOT READY**.

**Next engineering target:** recovery integrity review — distinguish business archive export/import from installation-wide backup/restore; verify trusted-device safeguards, private-media consistency, rollback and recovery on the intended Windows installation.

---

## Director execution checkpoint — offline expense identity isolation challenge — 2026-11-22

**Source/test head:** [8803746](https://github.com/Leano-Jordan/ZazuEMP/commit/88037465ddacbc4b60a81efe343e4210b6b1dc02) on `main`.

- Added an adversarial regression for corrupted `sync_entity_identities` rows whose business scope and entity type look local/correct, but whose record target belongs to another business.
- The test challenges offline expense links for event, supplier and purchase order. Each mutation must remain pending; no expense is created and no foreign record is changed.
- The server-side resolver already fails closed: it checks the expected morph type and loads the target record through a business-scoped query. The event resolver likewise scopes both identity and event to the authenticated business. Finance transaction checks also enforce purchase-order/job and supplier/order consistency. No application-code change was warranted.
- Exact-head Laravel feature/unit suite: **679 passed / 1 skipped / 4,665 assertions**; the new `OfflineExpenseReferenceIsolationTest` passed.
- Zazu Quality (incremental Pint, application checks, Composer security audit and secret scan), PHPMD, PostgreSQL migration/rollback/registration/backup/private-file recovery, Director Health and Push-on-main: **PASS**. SonarCloud: **SKIPPED**.
- Exact-head offline-browser acceptance in Laravel workflow [44987764883](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44987764883): **63 desktop Chromium + 7 mobile viewport + 1 mobile host-outage + 1 phone-local attachment/reconnect passed**; public/auth accessibility acceptance also passed. No failures were reported.
- Evidence maturity remains **77/100**; no score uplift is claimed from one additional adversarial regression. V1 remains **NOT READY** pending the owner-operated Android, PC recovery, commercial and legal/brand gates.

**Next engineering target:** continue the offline account/session transition adversarial review, especially expiry/re-authentication, preservation of queued local work, and ensuring a stale session cannot regain online mutation authority.

---

## Director execution checkpoint — trusted-device credential race closure — 2026-11-21

**Verified code/test head:** [04db872](https://github.com/Leano-Jordan/ZazuEMP/commit/04db8722a66ff8c97d62fef855f7ba07073934c7).

- Closed the post-record mutation-apply race: HTTP push now applies only through the authenticated applier, which verifies business/device ownership and revalidates the original credential under the business-first/device-row lock in the same transaction as the domain mutation.
- Closed the conflict-write sibling race: request-originated conflict recording now uses the same guard. A re-paired device's stale middleware snapshot cannot create or update conflict state.
- Centralized the same guard for bootstrap, pull and acknowledgement, so these paths share one active-device and persisted-token comparison rather than maintaining subtly different copies.
- Added adversarial regressions proving stale apply returns 401 with the mutation still pending and no delivery, and stale conflict recording returns 401 with no conflict row. Existing bootstrap/pull/ack revocation checks remain in place.
- Strengthened the attachment reconnect browser test to assert the phone is actually offline, the attachment is durably present before reload, and the same business-scoped storage partition is used after reload. The exact-head rerun had no flaky retries.
- Laravel: **675 passed / 1 skipped / 4,581 assertions**. PostgreSQL migration/recovery smoke: **PASS**. Offline browser: **63 desktop Chromium + 7 mobile viewport + 1 mobile LAN-host-outage + 1 phone-local attachment/reconnect passed**, no failures or flaky retries.
- PHPMD, Psalm, Zazu Quality (secret scan, Composer security audit and application checks), Director Health, populated upgrade/rollback, Windows V1 package/fresh install and Push-on-main: **PASS**. SonarCloud: **SKIPPED**, not counted as a pass.
- CASE-ZAZU-0040 is closed at source/automated-test level.
- Windows owner-PC backup automation now has runtime CI on [db5f25e](https://github.com/Leano-Jordan/ZazuEMP/commit/db5f25e1f6b54fb28909738508ef935792a419cb): the workflow registers and inspects a real temporary Task Scheduler task under Windows PowerShell 5.1 (S4U, Limited, daily 02:00, catch-up, wake, IgnoreNew), executes the backup runner, verifies local/secondary retention of 14, SHA-256 equality, and confirms a failed secondary destination preserves the previous successful timestamp. [Windows Ops workflow 44941196865](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44941196865) PASS. This is CI evidence, not the actual owner-PC observation/off-disk restore drill. No exploit was demonstrated in a running installation; this was a source-level time-of-check/time-of-use path closed with adversarial regression coverage.

**Remaining V1 gates — do not mark release-ready yet:** physical Android installed-app cold launch/host-stop/force-close/restart/reconnect; actual owner-PC off-disk backup and controlled full restore with private media; populated live-business quote-to-payment-to-profit reconciliation; POPIA operational sign-off; ZAZU name clearance. Browser mobile emulation is not physical-device proof. Evidence maturity remains **77/100**; no score uplift.

---


## Director execution checkpoint — empty private-storage restore correctness — 2026-11-21

**Implementation:** [1906fd4](https://github.com/Leano-Jordan/ZazuEMP/commit/1906fd4da1876df66d745a57be9c3bd1d1714c48).  
**Regression:** [1cb1154](https://github.com/Leano-Jordan/ZazuEMP/commit/1cb1154b4b813dca0cf04fe401dbdf38375a5808).

- Found a recovery edge case: a valid backup declares database and private storage even when private storage is empty. ZIP files omit empty directories, so restore previously saw no private payload entry and left old private files on the target installation.
- Restore now uses the v2 contents manifest as the authority for whether private storage is part of the snapshot, creates an empty staging directory when required, and swaps it in just like a populated private-storage snapshot.
- Added an integration regression that backs up an empty private directory, creates a stale private file after backup, restores, and verifies the stale file is removed while the database returns to its backed-up state.
- Exact-head workflow [44937037910](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44937037910) on [d716d5d](https://github.com/Leano-Jordan/ZazuEMP/commit/d716d5da615335c1afb3509867631c42b416ffbc): Laravel **673 passed / 1 skipped / 4,575 assertions**; PostgreSQL migration/recovery smoke **PASS**; offline browser **63 desktop + 7 mobile + 1 host-outage + 1 phone-local attachment/reconnect passed**, no failures or flaky retries; PHPMD, Zazu Quality, Composer security audit, secret scan and Director Health **PASS**. The empty-private-storage restore integration test and archive-limit boundary tests are included in the passing Laravel run. SonarCloud skipped.
- Windows package/fresh-install and Windows-native populated + empty private-storage restore **PASS** on workflow [44937732880](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44937732880), packaging exact runtime source [d716d5d](https://github.com/Leano-Jordan/ZazuEMP/commit/d716d5da615335c1afb3509867631c42b416ffbc). This validates Windows CI filesystem behaviour, not the owner's installed PC.

---

## Director execution checkpoint — backup/restore capacity alignment — 2026-11-21

**Implementation:** [84dba32](https://github.com/Leano-Jordan/ZazuEMP/commit/84dba32ae96ececf6c1dd71034d79d0b93070b7e).  
**Shared policy:** [4824469](https://github.com/Leano-Jordan/ZazuEMP/commit/48244694d5419df50ce9cdd3938f65aaf77d02eb).  
**Regression:** [675742e](https://github.com/Leano-Jordan/ZazuEMP/commit/675742eef40a819b6e509ea168303a8d239fd584).

- Found a restore incompatibility: backup allowed 50,000 private files plus database and manifest (50,002 ZIP entries), while restore capped the archive at 50,000 entries. Backup could therefore publish an archive its own restore command rejected.
- Restore also capped total uncompressed archive content at 2 GiB, while backup did not enforce the same total before publication.
- Added one shared archive-limit policy, aligned entry counts, enforced the 2 GiB payload ceiling during backup staging, and rechecked the complete ZIP including its manifest before publishing.
- Added boundary tests for maximum private files, ZIP entries and uncompressed bytes.
- **Verification:** exact-head Laravel, quality and backup/restore round-trip CI pending. No pass is claimed until the run completes.

Next: close the exact-head recovery checks, then continue V1 recovery and commercial acceptance. Main only; no branches.

---

## Director execution checkpoint — LAN host-outage offline acceptance CLOSED — 2026-11-21

**Verified source/test head:** [7df9d7c](https://github.com/Leano-Jordan/ZazuEMP/commit/7df9d7c176cb5fe931bf62617d2908cc74e8d041).  
**Runtime implementation:** [96e57e4](https://github.com/Leano-Jordan/ZazuEMP/commit/96e57e4674b93c028b8aa5ffb2805d12b15a0457).  
**Exact-head Laravel + desktop/mobile browser + PostgreSQL:** [run 44934728827](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44934728827).

- Laravel: **670 passed / 1 skipped / 4,552 assertions**.
- PostgreSQL migration/recovery smoke: **PASS**.
- Offline browser: **63 desktop Chromium passed**, **7 mobile-viewport Work/customer acceptance passed**, **1 Pixel 7 viewport host-outage monitor test passed**, and **1 phone-local Work attachment/reconnect journey passed**. No failures or flaky retries in the final exact-head run.
- Zazu Quality [44934728822], Director Health [44934728782], Director Contract Lint [44934728823] and Push-on-main [44934728600]: **PASS**.
- Windows V1 package and fresh-install acceptance passed on the same runtime implementation head [run 44934642257](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44934642257).
- SonarCloud: **SKIPPED**, not counted as a pass.
- CASE-ZAZU-0037 is CLOSED for the simulated LAN host-outage UI defect. Physical Android acceptance remains an independent open release gate.

**Correction history:** the first browser attempt exposed a race between concurrent origin probes and offline-state changes. The runtime now uses one shared origin-availability state, queues transitions while a probe is in flight, and refreshes on focus. The final exact-head desktop/mobile run passed without retries.

**Remaining V1 gates:** physical Android installed-app cold launch/stop-host/force-close/restart/reconnect; actual owner-PC off-disk backup and full restore drill; populated live-business quote-to-payment-to-profit reconciliation; POPIA operational sign-off; ZAZU name clearance. Evidence maturity remains **77/100**; no score uplift from simulated browser evidence. V1 remains **NOT READY**.

---

## Director execution checkpoint — live host-outage detection for offline PWA — 2026-11-21

**Implementation:** [ae2a900](https://github.com/Leano-Jordan/ZazuEMP/commit/ae2a90080590f4aad8bfffc459e211f6b98c0bf8).  
**UI styling:** [87521db](https://github.com/Leano-Jordan/ZazuEMP/commit/87521dbc6996bcfb8d7eb31b3e3a235fcdfedf5d).  
**Desktop regression:** [3fe87a0](https://github.com/Leano-Jordan/ZazuEMP/commit/3fe87a062f9e079a0ad08caa0f09915eb14f3f22).  
**Phone-viewport coverage:** [41d7a25](https://github.com/Leano-Jordan/ZazuEMP/commit/41d7a256f80b1e1d98cbb43509f81dc26cd600ec).

- Root cause found in the current UI lifecycle: origin reachability was checked during render/event paths, but no periodic check ran while Wi-Fi remained connected. A stopped LAN host therefore did not trigger the browser's offline event and the visible page could remain a stale server snapshot.
- Added a 10-second HEAD /up monitor, outage notice and non-destructive link to the cached dashboard workspace. It does not auto-navigate or discard an open form.
- The same health result now controls dashboard local-workspace visibility; the notice explicitly states that only supported offline screens are available.
- Added a regression that simulates /up returning 503 while navigator.onLine remains true, then restoring 200. It runs on desktop Chromium and Pixel 7 viewport.
- Verification on test head 41d7a25: Laravel **670 passed / 1 skipped / 4,552 assertions**, PostgreSQL migration/recovery smoke **PASS**, Zazu Quality **PASS**, Push-on-main **PASS**. The offline-browser acceptance job is still running; no pass is claimed for the new outage test until the exact-head run completes.
- Physical Android acceptance remains open. Browser mobile emulation cannot establish installed-app cold launch, force-close/restart or real phone persistence.

Next: close exact-head browser acceptance; fix any failures; then continue physical-device, owner-PC recovery and commercial acceptance gates. Main only; no branches.

## Director execution checkpoint — transactional trusted-device authorization — 2026-11-21

**Current code head:** [7d4b097](https://github.com/Leano-Jordan/ZazuEMP/commit/7d4b097a13d02f3d5369bf8e75e2dc85c361beb7).  
**Regression coverage:** [01e0b4b](https://github.com/Leano-Jordan/ZazuEMP/commit/01e0b4bc771d25f537779c0e5c17cfc9e619420a).

- Added a shared business-first/device-row authorization guard that revalidates active status and the persisted token hash inside the caller's transaction.
- Applied it to conflict listing/resolution, attachment upload/list/download and device revocation. This serializes these operations with revocation and rejects stale middleware snapshots.
- A stale credential can no longer revoke a replacement credential registered for the same installation.
- Added five regression tests for revoked-device conflict listing/resolution, attachment upload, attachment listing and stale-token revocation.
- Existing bootstrap, pull and acknowledgement revalidation remains in place; this correction does not reopen those already-covered paths.
- **Verification on code head `7d4b097`:** Laravel **670 passed / 1 skipped / 4,552 assertions** and PostgreSQL migration/recovery smoke passed in [run 44931555043](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44931555043). PHPMD [44931555002](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44931555002), Psalm [44931555131](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44931555131), populated upgrade/rollback [44931555044](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44931555044) and Director Health [44931554979](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44931554979) passed. Offline-browser acceptance passed: **62 desktop Chromium**, **7 mobile-viewport**, and **1 phone-local Work/attachment/reconnect** journey. Windows V1 package passed [44931554910](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44931554910); Zazu Quality passed [44931555024](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44931555024). SonarCloud is skipped. No readiness score change.

**Next:** continue the remaining V1 gates: physical Android cold launch/restart/reconnect, actual owner-PC off-disk recovery, populated live-business reconciliation, POPIA operations and ZAZU name clearance. Automated browser emulation is not physical-device proof. Main only; no branches.

---

## Director execution checkpoint — offline tenant-boundary hardening verified — 2026-11-21

**Current main:** [2ef47e8](https://github.com/Leano-Jordan/ZazuEMP/commit/2ef47e8ac94ac75db0384eb2d45705a360e109d1).  
**Latest code/test head:** [f25216a](https://github.com/Leano-Jordan/ZazuEMP/commit/f25216ab4ae5c99b772fbf8d932070a4ec009c6f).  
**Core implementation:** [8268504](https://github.com/Leano-Jordan/ZazuEMP/commit/826850474aee06a79a2ea7bb8917a470ef1da2fe).

- Completed adversarial hardening across offline mutation identity acknowledgements, bootstrap fallback identity resolution, related asset identity snapshots, selected-record customer details, and invoice quote references.
- Expanded identity-to-model validation to cover all persisted offline mutation models. Added rejection tests for malformed/cross-business references and positive tests ensuring valid InventoryMovement identities still sync and appear in conflict snapshots.
- Latest exact code/test head passed Laravel **665 passed / 1 skipped / 4,545 assertions**, PostgreSQL migration/recovery smoke, desktop Chromium offline acceptance, mobile viewport customer/Work acceptance, and phone-local attachment acceptance: [Laravel run 44924557047](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44924557047).
- PHPMD passed [44924557089](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44924557089). Zazu Quality, Composer dependency security audit, secret scan and application checks passed [44924557021](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44924557021). Director Health passed [44924557064](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44924557064).
- On the same implementation generation, Psalm security scan and CodeQL passed [44924540403](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44924540403); populated upgrade/rollback passed [44924540447](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44924540447); Windows V1 package build and fresh PowerShell installation passed [44924540392](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44924540392). SonarCloud is skipped, not passed.
- Latest Director Health, Zazu Quality, Contract Lint and Push-on-main also passed on [2ef47e8](https://github.com/Leano-Jordan/ZazuEMP/commit/2ef47e8ac94ac75db0384eb2d45705a360e109d1).

**What this closes:** the previously outstanding CI/browser acceptance gate for the latest offline identity/snapshot changes. This is strong engineering evidence, not proof of production readiness under every real customer environment.

**Remaining V1 gates:**
1. Owner-PC data safety and recovery: complete the documented business export/import and full installation recovery drill using an actual populated installation.
2. Device authority/security: finish owner/device credential and session-transition acceptance, including revoked/expired device behavior.
3. Phone offline acceptance: owner must verify the disconnected physical phone remains usable after server shutdown and restart; browser mobile emulation is not a substitute.
4. Commercial acceptance: run one populated end-to-end quote → invoice → payment → expenses/costs → profit reconciliation and verify totals.
5. Legal/brand: POPIA operational controls and Zazu name clearance remain outside automated code CI.

**Evidence maturity:** 77/100 remains the last scored baseline. The score is not automatically raised just because CI is green; operational gates above still require evidence. V1 remains NOT READY for commercial release until they are closed.

**Next Director target:** continue source-level security review of remaining pull/conflict/bootstrap relationship boundaries, then prepare a short owner-run acceptance pack for the remaining PC/phone/commercial drills. Main only; no branches.

---

## Director execution checkpoint — complete conflict snapshot model mapping — 2026-11-21

**Implementation:** [8268504](https://github.com/Leano-Jordan/ZazuEMP/commit/826850474aee06a79a2ea7bb8917a470ef1da2fe).  
**Regression:** [f25216a](https://github.com/Leano-Jordan/ZazuEMP/commit/f25216ab4ae5c99b772fbf8d932070a4ec009c6f).

- Adversarial review caught an unintended compatibility risk in the earlier snapshot tenant guard: its expected-model map covered only common aggregates, which would make valid snapshots for other persisted offline mutation types appear deleted.
- Expanded the map to cover every persisted offline mutation model type, including service, supplier, preparation, payment, inventory movement, expense, event cost, asset allocation and purchase-order items.
- Added a positive InventoryMovement snapshot regression proving a valid tenant-owned identity is not misclassified as deleted.
- Exact test-head Laravel [44924557047](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44924557047) passed **665 / 1 skipped / 4,545 assertions**; PostgreSQL migration/recovery, PHPMD [44924557089](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44924557089), Zazu Quality [44924557021](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44924557021), Composer security audit, secret scan and Director Health [44924557064](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44924557064) all passed. Only the matching desktop/mobile offline-browser acceptance remains in progress.
- Prior complete implementation head [4c84a1f](https://github.com/Leano-Jordan/ZazuEMP/commit/4c84a1f6772ad8ed7e83ebf92c6fe8d8272cf96d) passed Laravel **665 / 1 skipped / 4,543 assertions**, PostgreSQL, PHPMD, quality, Psalm, Windows fresh install and populated upgrade/rollback. Browser acceptance remains in progress on [44924126299](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44924126299).

**Evidence maturity:** 77/100 unchanged; V1 NOT READY.

**Next target:** close the complete snapshot-map regression, then finish the desktop/mobile offline acceptance run. Main only; no branches.

---

## Director execution checkpoint — bootstrap payload complexity correction — 2026-11-21

**Refactor:** [ae61053](https://github.com/Leano-Jordan/ZazuEMP/commit/ae61053f04e2f36bc4b284a681c6f5cbdf4cfdec) + [4c84a1f](https://github.com/Leano-Jordan/ZazuEMP/commit/4c84a1f6772ad8ed7e83ebf92c6fe8d8272cf96d).

- PHPMD flagged SyncBootstrapPayloadBuilder at the configured excessive-class-complexity threshold after invoice quote-reference sanitization was added.
- Extracted the invoice relation sanitizer into SyncBootstrapInvoiceRelationGuard and injected the focused dependency into the payload builder. No suppressions or threshold changes.
- The first Laravel attempt on the builder-only commit preceded the helper-file commit and failed with a missing-class error. The subsequent complete implementation head [4c84a1f](https://github.com/Leano-Jordan/ZazuEMP/commit/4c84a1f6772ad8ed7e83ebf92c6fe8d8272cf96d) passed Laravel **665 / 1 skipped / 4,543 assertions**, PostgreSQL migration/recovery, PHPMD, Zazu Quality, Director Health, Psalm security scan and populated upgrade/rollback. Its desktop/mobile browser acceptance remains in progress: [Laravel/browser run 44924126299](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44924126299). PHPMD [44924126347](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44924126347), Quality [44924126247](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44924126247), Director Health [44924126230](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44924126230), Psalm [44924126275](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44924126275), Windows V1 build + fresh PowerShell install [44924126297](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44924126297), populated upgrade/rollback [44924126321](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44924126321) and CodeQL analysis [44924121765](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44924121765) all **PASS**. SonarCloud skipped.
- Previous exact invoice-guard head [c28588e](https://github.com/Leano-Jordan/ZazuEMP/commit/c28588eef0a39f827500035bb7d427a89518eb2d) passed Laravel **665 / 1 skipped / 4,543 assertions**, PostgreSQL, PHPMD, Zazu Quality, Composer audit, secret scan and Director Health. The latest bootstrap browser acceptance remains pending.

**Evidence maturity:** 77/100 unchanged; V1 NOT READY.

**Next target:** close the current code-head tests and browser acceptance; then finish remaining nested bootstrap and conflict/pull tenant-boundary review. Main only; no branches.

---

## Director execution checkpoint — bootstrap contact-data sanitization — 2026-11-21

**Implementation:** [c187b52](https://github.com/Leano-Jordan/ZazuEMP/commit/c187b528109e7467d19120cc46ff378bb9914e3e).  
**Regression:** [a235445](https://github.com/Leano-Jordan/ZazuEMP/commit/a2354452238a9f6bfdc5c16199ab23a48c7c98b0).

- Tightened the selected Work/Quote/Preparation relationship fix: when a selected Event's customer_id points outside the active business, bootstrap now clears the foreign customer ID and denormalized customer name, phone and email fields as well as omitting the nested Customer relation.
- Extended the end-to-end bootstrap regression to populate the corrupted Event with copied foreign customer contact details; the complete selected payload must not contain those details.
- The preceding invoice quote-reference implementation/test head [c28588e](https://github.com/Leano-Jordan/ZazuEMP/commit/c28588eef0a39f827500035bb7d427a89518eb2d) passed Laravel **665 / 1 skipped / 4,543 assertions**, PostgreSQL migration/recovery, PHPMD, Zazu Quality, Composer security audit, secret scan and Director Health. Browser acceptance remains in progress on run [44923620655](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44923620655).
- Latest denormalized-contact regression is running CI on Laravel [44923983105](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44923983105), Zazu Quality [44923983017](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44923983017), and Director Health [44923983121](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44923983121). Code-head PHPMD [44923966272](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44923966272), Psalm [44923965972](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44923965972), Windows package [44923965887](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44923965887) and populated upgrade/rollback [44923965967](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44923965967) are in progress. SonarCloud skipped.

**Evidence maturity:** 77/100 unchanged; V1 NOT READY.

**Next target:** verify current exact-head suite, then continue only where unscoped references or denormalized linked data can cross the business boundary. Main only; no branches.

---

## Director execution checkpoint — invoice quote-reference isolation — 2026-11-21

**Implementation:** [1c88144](https://github.com/Leano-Jordan/ZazuEMP/commit/1c88144120e9c7c4a1094d2b27680cf661ab2fd0) + [a938757](https://github.com/Leano-Jordan/ZazuEMP/commit/a93875716f8d546302009d0a7639a30d6bedebe8).  
**Regression:** [e9a1cfd](https://github.com/Leano-Jordan/ZazuEMP/commit/e9a1cfdfba043bd9101b58e99d5c9f2c21e77429) + [c28588e](https://github.com/Leano-Jordan/ZazuEMP/commit/c28588eef0a39f827500035bb7d427a89518eb2d).

- Expanded invoice bootstrap validation beyond QuoteVersion ownership: invoice.quote_id must resolve to a Quote whose Event belongs to the active business; the QuoteVersion must belong to that same Quote and business.
- Foreign quote and version IDs are removed from the serialized invoice, not merely hidden in the nested relation. Deposit terms fall back to zero.
- Added an adversarial regression with a business-A invoice deliberately referencing a business-B Quote and QuoteVersion; bootstrap must clear both foreign IDs and omit the version/foreign deposit amount.
- The earlier selected Work/Quote/Preparation nested-customer regression passed **664 / 1 skipped / 4,538 assertions** with PostgreSQL recovery, PHPMD, Zazu Quality and Director Health green on the 7d08c9d test head. Its mobile/desktop browser acceptance was still running at last poll.
- Current invoice quote-reference regression: Laravel **665 passed / 1 skipped / 4,543 assertions**; PostgreSQL migration/recovery smoke, PHPMD, Zazu Quality, Composer security audit, secret scan and Director Health all **PASS** on the current implementation/test head. [Laravel + PostgreSQL + browser run 44923620655](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44923620655), [PHPMD 44923620616](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44923620616), [Zazu Quality 44923620654](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44923620654), [Director Health 44923620619](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44923620619). Only the matching desktop/mobile offline-browser acceptance remains in progress. SonarCloud skipped.

**Evidence maturity:** 77/100 unchanged; V1 NOT READY.

**Next target:** close current exact-head checks, then continue reviewing only unconstrained nested references that can cross business boundaries. Main only; no branch creation.

---

## Director execution checkpoint — invoice quote-version bootstrap isolation — 2026-11-21

**Implementation:** [1c88144](https://github.com/Leano-Jordan/ZazuEMP/commit/1c88144120e9c7c4a1094d2b27680cf661ab2fd0).  
**Regression:** [e9a1cfd](https://github.com/Leano-Jordan/ZazuEMP/commit/e9a1cfdfba043bd9101b58e99d5c9f2c21e77429).

- Invoice bootstrap eager-loaded QuoteVersion by its numeric foreign key without checking the owning Quote/Event business. Unlike several other aggregate relationships, this reference is not protected by a composite business foreign key.
- Bootstrap now verifies that the QuoteVersion's Quote matches the Invoice.quote_id and that the Quote's Event belongs to the active business. Foreign/mismatched versions are omitted and deposit terms fall back safely.
- Added an adversarial regression with a local invoice pointing to a foreign QuoteVersion.
- The preceding selected Work/Quote/Preparation nested-customer fix passed Laravel **664 passed / 1 skipped / 4,538 assertions**, PHPMD, Zazu Quality and Director Health on [run 44923073728](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44923073728) and companion checks [44923073757](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44923073757), [44923073689](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44923073689), [44923073663](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44923073663). Browser acceptance from that run remains in progress.
- Current invoice-version regression CI: Laravel [44923396867](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44923396867) is in progress; PHPMD [44923396811](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44923396811) and Zazu Quality [44923396878](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44923396878) are queued; Director Health [44923396879](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44923396879) passed. SonarCloud skipped.

**Evidence maturity:** 77/100 unchanged; V1 NOT READY.

**Next target:** close the invoice-version regression and remaining bootstrap acceptance checks; then review any other unconstrained nested foreign-key references in the bootstrap payload. Main only; no branches.

---

## Director execution checkpoint — bootstrap nested relationships and regression correction — 2026-11-21

**Nested relationship guard:** [4cbd1f7](https://github.com/Leano-Jordan/ZazuEMP/commit/4cbd1f7d83dad9e3b6000b89ebe04028023e569e).  
**Bootstrap regression:** [530ff81](https://github.com/Leano-Jordan/ZazuEMP/commit/530ff81c47b9d7e82cef87bcdaf473353247f3b2).  
**Test-fixture correction:** [7d08c9d](https://github.com/Leano-Jordan/ZazuEMP/commit/7d08c9dc2a2720e6c9fa3582e55b45de82eb0558).

- Selected Work, Quote and Preparation bootstrap records now constrain nested Customer relationships to the active business before serialization.
- The full bootstrap regression deliberately links a business-A event to a business-B customer and verifies no foreign customer name or identity appears in selected Work, Quote or Preparation payloads.
- The earlier Laravel run exposed a separate test-fixture error: the asset-allocation foreign-business setup was rejected by the existing composite business/asset and business/event foreign keys. This confirms the database already enforces that invariant. Removed the impossible fixture; retained the snapshot guard as defense in depth for imported/legacy malformed state. No production test or constraint was weakened.
- PHPMD passed on the preceding bootstrap regression head [530ff81](https://github.com/Leano-Jordan/ZazuEMP/commit/530ff81c47b9d7e82cef87bcdaf473353247f3b2). Latest Laravel run after removing the impossible fixture is [44923073728](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44923073728); PHPMD [44923073757](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44923073757), Zazu Quality [44923073689](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44923073689), Director Health [44923073663](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44923073663). At checkpoint these are queued/in progress. SonarCloud skipped.
- The preceding run [44922914337](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44922914337) is also still running; do not claim its result until complete.

**Evidence maturity:** 77/100 unchanged; V1 NOT READY.

**Next target:** close the latest Laravel/PHPMD/quality checks. Then continue selected-record and conflict delivery review only where the schema and code indicate a real tenant boundary. Main only; no branches.

---

## Director execution checkpoint — selected bootstrap nested tenant boundary — 2026-11-21

**Implementation:** [4cbd1f7](https://github.com/Leano-Jordan/ZazuEMP/commit/4cbd1f7d83dad9e3b6000b89ebe04028023e569e).  
**Regression:** [530ff81](https://github.com/Leano-Jordan/ZazuEMP/commit/530ff81c47b9d7e82cef87bcdaf473353247f3b2).

- Found another concrete tenant boundary in bootstrap serialization: selected Work, Quote and Preparation records loaded nested Customer relationships without constraining the customer to the active business.
- Work now constrains its customer relationship by business_id. Quote and Preparation constrain the related Event and nested Customer to the same business before serialization.
- Added an end-to-end bootstrap regression using a local Work/Quote/Preparation set deliberately linked to a foreign Customer. The foreign Customer name and identity must not appear in any selected payload.
- PHPMD passed on the preceding split-test head [1e67154](https://github.com/Leano-Jordan/ZazuEMP/commit/1e67154405fa6fb96f7d0bafd67694274cb47025); Director Health passed there as well. The latest selected-relationship regression is queued on [Laravel run 44922914337](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44922914337), [PHPMD run 44922914398](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44922914398), [Zazu Quality run 44922914336](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44922914336), and [Director Health run 44922914334](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44922914334). SonarCloud skipped.
- Prior full offline acceptance [44919777572](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44919777572) remains green for desktop Chromium, mobile customer/Work and phone-local attachments. Latest bootstrap relationship changes still require their own exact-head verification.

**Evidence maturity:** 77/100 unchanged. V1 remains NOT READY.

**Next target:** close exact-head tests and quality checks, then continue reviewing selected-record relationships and conflict/pull serialization for tenant leaks. Main only; no branch creation.

---

## Director execution checkpoint — offline identity acknowledgement compatibility — 2026-11-21

**Latest code:** [3f8d2cd](https://github.com/Leano-Jordan/ZazuEMP/commit/3f8d2cd9bc70f718ff991eb9dc9503b204aa8a62) adds the InventoryMovement model to the shared primary identity guard.  
**Latest regression:** [3be50c7](https://github.com/Leano-Jordan/ZazuEMP/commit/3be50c76bbb62c99b3834731157fac0c48072977) proves a valid business-owned InventoryMovement identity is still accepted.  
**Related asset snapshot guard:** [1fdbb02](https://github.com/Leano-Jordan/ZazuEMP/commit/1fdbb0212c099df1f8403628b9e71423f35c5434) + [f53e17a](https://github.com/Leano-Jordan/ZazuEMP/commit/f53e17ab33e82d960dc40bc4a67e19bc8da349b8).

- Completed the follow-through on primary acknowledgement mapping: InventoryMovement is a real persisted mutation result and must remain acknowledgeable; command-only quote acceptance and purchase receipt do not create a primary model identity.
- The conflict snapshot's related asset identity now verifies the actual Asset's business ownership, not only the tenant identity row and allocation.
- Added a positive InventoryMovement regression and a negative cross-business related-asset snapshot regression.
- PHPMD on [f53e17a](https://github.com/Leano-Jordan/ZazuEMP/commit/f53e17ab33e82d960dc40bc4a67e19bc8da349b8) passed; Director Health passed. Current exact regression-head checks are [Laravel run 44922555573](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44922555573), [PHPMD run 44922555612](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44922555612), [Zazu Quality run 44922555513](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44922555513), and [Director Health run 44922555567](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44922555567). At checkpoint these were queued. The code-head PHPMD/Psalm/upgrade runs from [3f8d2cd](https://github.com/Leano-Jordan/ZazuEMP/commit/3f8d2cd9bc70f718ff991eb9dc9503b204aa8a62) are in progress; Laravel is queued. SonarCloud skipped.
- Previous complete desktop/mobile offline acceptance remains green on [44919777572](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44919777572); no claim is made that the latest changed PHP has already passed the browser suite.

**Evidence maturity:** 77/100 unchanged; V1 NOT READY.

**Next target:** close exact-head checks, then inspect any remaining identity paths used by receiver pull, conflict resolution and bootstrap selected-record serialization. Main only; no branches.

---

## Director execution checkpoint — related snapshot ownership completed — 2026-11-21

**Latest implementation:** [1fdbb02](https://github.com/Leano-Jordan/ZazuEMP/commit/1fdbb0212c099df1f8403628b9e71423f35c5434).  
**Latest adversarial regression:** [f53e17a](https://github.com/Leano-Jordan/ZazuEMP/commit/f53e17ab33e82d960dc40bc4a67e19bc8da349b8).  
**Bootstrap fallback refactor:** [438d322](https://github.com/Leano-Jordan/ZazuEMP/commit/438d3220be5eb02078ed7e5f690c38161b612e25) + [e587bc0](https://github.com/Leano-Jordan/ZazuEMP/commit/e587bc078f1dba9ad4ac50141f021fbdb0207bbc).  
**Primary acknowledgement guard:** [6b27c41](https://github.com/Leano-Jordan/ZazuEMP/commit/6b27c41912f5a4fe98f50e4bbf78f73c4dca422e) + [80f509d](https://github.com/Leano-Jordan/ZazuEMP/commit/80f509d6a910715aa1656650ba3625c72647e309) + [1c2d5e1](https://github.com/Leano-Jordan/ZazuEMP/commit/1c2d5e13d27d08fece18c878bcfb847a77b302f1).  
**Regression fixture correction:** [2a18c86](https://github.com/Leano-Jordan/ZazuEMP/commit/2a18c867403c3096f4d170a2f7b45a77332df7e4).

- The bootstrap fallback and primary push acknowledgement now reject identity rows whose morph target is not the expected model or whose resolved record is not owned by the current business.
- The first PHPMD failure was a genuine complexity-threshold failure; it was corrected by extracting fallback validation and replacing the large match with a lookup table. The PHPMD run on [e587bc0](https://github.com/Leano-Jordan/ZazuEMP/commit/e587bc078f1dba9ad4ac50141f021fbdb0207bbc) passed.
- The conflict snapshot path now validates the actual related Asset record before returning its identity metadata. A new regression constructs a local allocation pointing to a foreign-business Asset and expects no related identity in the snapshot.
- Current exact test-head verification on [Laravel + PostgreSQL + browser run 44922365071](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44922365071), [PHPMD run 44922365016](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44922365016), [Zazu Quality run 44922365022](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44922365022), and [Director Health run 44922365096](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44922365096). At checkpoint: PHPMD in progress, Laravel and other checks queued; SonarCloud skipped.
- Prior full offline acceptance run [44919777572](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44919777572) passed 659 / 1 skipped / 4,527 assertions, PostgreSQL recovery, desktop Chromium, mobile customer/Work, and phone-local attachment acceptance.

**Evidence maturity:** 77/100 remains unchanged; V1 remains NOT READY. Do not award evidence points until the latest exact-head suite passes and the remaining operational acceptance gates are completed.

**Next Director target:** close current CI, then continue review of bootstrap selection and conflict delivery for any remaining cross-tenant linked aggregates. Main only; no branch creation.

---

## Director execution checkpoint — bootstrap + primary acknowledgement hardening — 2026-11-21

**Current implementation head:** [e587bc0](https://github.com/Leano-Jordan/ZazuEMP/commit/e587bc078f1dba9ad4ac50141f021fbdb0207bbc).  
**Primary acknowledgement guard:** [6b27c41](https://github.com/Leano-Jordan/ZazuEMP/commit/6b27c41912f5a4fe98f50e4bbf78f73c4dca422e) + [80f509d](https://github.com/Leano-Jordan/ZazuEMP/commit/80f509d6a910715aa1656650ba3625c72647e309) + [1c2d5e1](https://github.com/Leano-Jordan/ZazuEMP/commit/1c2d5e13d27d08fece18c878bcfb847a77b302f1).  
**Bootstrap fallback guard:** [cb944b2](https://github.com/Leano-Jordan/ZazuEMP/commit/cb944b2fe0b3c74caec4eee99b0feda1a2188183) + [3d1586c](https://github.com/Leano-Jordan/ZazuEMP/commit/3d1586c04ba35e70e292a433f038e3b7ed3828d6).  
**Regression:** [604cb07](https://github.com/Leano-Jordan/ZazuEMP/commit/604cb07b2c1a226056d413a559859514297f3d7a) + [bb959cc](https://github.com/Leano-Jordan/ZazuEMP/commit/bb959cc33b1c2262e94f065349d7160592fd36fb) + [2a18c86](https://github.com/Leano-Jordan/ZazuEMP/commit/2a18c867403c3096f4d170a2f7b45a77332df7e4).  
**PHPMD complexity correction:** [438d322](https://github.com/Leano-Jordan/ZazuEMP/commit/438d3220be5eb02078ed7e5f690c38161b612e25) + [e587bc0](https://github.com/Leano-Jordan/ZazuEMP/commit/e587bc078f1dba9ad4ac50141f021fbdb0207bbc).

- The bootstrap fallback and primary push acknowledgement no longer return a raw server record ID from a tenant-scoped identity without validating its expected Eloquent model type and actual business ownership.
- Shared identity validation now covers the offline domain model types used by bootstrap and mutation acknowledgement.
- The first PHPMD run on [2a18c86](https://github.com/Leano-Jordan/ZazuEMP/commit/2a18c867403c3096f4d170a2f7b45a77332df7e4) failed because the new bootstrap resolver exceeded configured cyclomatic/NPath complexity. The resolver was decomposed and its model map changed to a lookup table; no rule was suppressed or threshold weakened.
- Corrected regression fixture now targets the malformed primary invoice identity directly, ensuring the fallback would have returned it before the guard.
- Latest exact-code-head verification is running on [Laravel + PostgreSQL + browser run 44922161545](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44922161545), [PHPMD run 44922161649](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44922161649), [Zazu Quality run 44922161606](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44922161606), [Psalm run 44922161824](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44922161824), [Windows package run 44922161573](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44922161573), and [populated upgrade/rollback run 44922161727](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44922161727). Director Health [44922161921](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44922161921) and populated upgrade/rollback have passed. Remaining jobs are pending/in progress. SonarCloud is skipped.
- Prior full offline acceptance is green on [44919777572](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44919777572): Laravel 659 passed / 1 skipped / 4,527 assertions, PostgreSQL recovery, desktop Chromium, mobile customer/Work, and phone-local attachment acceptance.

**Evidence maturity:** 77/100 remains the last independently recorded baseline. V1 remains NOT READY; no score uplift until current exact-head checks and remaining operational acceptance gates close.

**Next target:** close current CI; then continue adversarial review of bootstrap/pull acknowledgement and conflict delivery, without reopening already-passed browser journeys unnecessarily. Main only; no branches.

---

## Director execution checkpoint — bootstrap delivery fallback hardened — 2026-11-21

**Primary acknowledgement guard:** [6b27c41](https://github.com/Leano-Jordan/ZazuEMP/commit/6b27c41912f5a4fe98f50e4bbf78f73c4dca422e).  
**Latest implementation:** [cb944b2](https://github.com/Leano-Jordan/ZazuEMP/commit/cb944b2fe0b3c74caec4eee99b0feda1a2188183) + [3d1586c](https://github.com/Leano-Jordan/ZazuEMP/commit/3d1586c04ba35e70e292a433f038e3b7ed3828d6).  
**Regression:** [604cb07](https://github.com/Leano-Jordan/ZazuEMP/commit/604cb07b2c1a226056d413a559859514297f3d7a).

- Extended the related identity audit to the bootstrap delivery target resolver. Its fallback previously returned a raw server ID from a tenant-scoped identity row without checking the polymorphic target model or revalidating the record's business ownership.
- Fallback resolution now uses an explicit entity-to-model mapping, requires the matching morph type, resolves the record, and confirms tenant ownership before returning a target.
- Primary push acknowledgements also used a separate raw identity lookup. They now pass through the shared model-type/tenant-ownership validator before returning a server ID or revision.
- Added a regression where an invoice identity points to a Customer and must not create an invoice bootstrap target.
- Documentation updated in docs/ZAZU_OFFLINE_FIRST_ARCHITECTURE.md.
- Prior full acceptance remains green on run [44919777572](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44919777572): 659 passed / 1 skipped / 4,527 assertions; PostgreSQL recovery; desktop Chromium; mobile customer/Work; phone-local attachments.
- The current primary-acknowledgement regression head is running CI under [Laravel run 44921977586](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44921977586), [Zazu Quality run 44921977614](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44921977614), [PHPMD run 44921977686](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44921977686), and [Director Health run 44921977622](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44921977622). Earlier security and populated-upgrade runs on the preceding code commit are also active: [Psalm 44921963812](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44921963812) and [upgrade/rollback 44921963643](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44921963643). Do not count the new work verified until Laravel, PostgreSQL, browser acceptance, quality and security checks finish. SonarCloud is skipped by current workflow configuration.

**Evidence maturity:** 77/100 remains the last independently recorded baseline; V1 remains NOT READY.

**Next target:** verify the current CI; then continue reviewing device-scoped bootstrap, pull acknowledgement and conflict delivery paths for raw identity dereferences. Main only; no branch creation.

---

## Director execution checkpoint — related offline identity paths hardened — 2026-11-21

**Implementation commits:** [c919d4f](https://github.com/Leano-Jordan/ZazuEMP/commit/c919d4fc7fb611d835b966b6babc70288fe5263f), [043de08](https://github.com/Leano-Jordan/ZazuEMP/commit/043de08cf28389e2a4b4ff07f24971f95835922d), [e21fe2b](https://github.com/Leano-Jordan/ZazuEMP/commit/e21fe2b5a77948602d2b42984b4ba3a530f26e86), [683e920](https://github.com/Leano-Jordan/ZazuEMP/commit/683e9208b7dfc1fdd1fbf630c2b77e23132c6b1f), [1d18c90](https://github.com/Leano-Jordan/ZazuEMP/commit/1d18c90c46462e2af8c32ba77b56603c54611c72).  
**Regression commits:** [8952da2](https://github.com/Leano-Jordan/ZazuEMP/commit/8952da23a87be4aa7ef5ac7143b8caa0e2163a08), [50a0efe](https://github.com/Leano-Jordan/ZazuEMP/commit/50a0efe0cfa9b9aff23a37832488c6807436cada), [d33f395](https://github.com/Leano-Jordan/ZazuEMP/commit/d33f395b9f60e13ab5886692999f9896a61cd141), [19bb269](https://github.com/Leano-Jordan/ZazuEMP/commit/19bb26973c086bc2dfdc0a733483305643c36763).

- The previous full acceptance run [44919777572](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44919777572) is now fully green: Laravel 659 passed / 1 skipped / 4,527 assertions; PostgreSQL migration/recovery; desktop Chromium; mobile customer/Work acceptance; phone-local attachment journey.
- Extended the security audit beyond mutation application to related identity acknowledgements, parent revision advancement and conflict snapshot serialization.
- Related identity acknowledgements now validate expected Eloquent morph type and tenant ownership before returning related local/server identity mappings.
- Related revision advancement now validates parent identity model type and ownership, scopes purchase-order line resolution to the specific business/order, and checks tenant ownership before incrementing related identity revisions.
- Conflict snapshots now reject both foreign-business targets and same-business wrong-model targets.
- Added adversarial tests for a payment acknowledgement whose invoice identity points to a Customer and a conflict snapshot whose customer identity points to an Event in the same business.
- Updated docs/ZAZU_OFFLINE_FIRST_ARCHITECTURE.md with the new threat boundaries and regression contracts.

**Verification of this additional hardening:** PENDING exact-head Laravel, quality, PostgreSQL and browser acceptance. No new score uplift until these checks pass.

**Evidence maturity:** 77/100 remains the last independently recorded baseline; V1 is NOT READY. Keep it unchanged until the broader owner-PC, recovery, commercial, physical-device and POPIA acceptance evidence is closed.

**Next Director target:** verify the new exact-head checks, fix any failures, then continue the same adversarial review across bootstrap delivery target resolution and remaining offline linked-record paths. Main only; no branch creation.

---

## Director execution checkpoint — offline acceptance closure + identity ownership hardening — 2026-11-21

**Current source/test head before this checkpoint:** `d7d93152da24c128b1a48b3eb3191762cfe8d4a1` on `main`.  
**Ownership guard:** [15f0a17](https://github.com/Leano-Jordan/ZazuEMP/commit/15f0a176e46aaf19c389aa17e36e2d74028d9f50).  
**Morph-type validation:** [57c4888](https://github.com/Leano-Jordan/ZazuEMP/commit/57c48885459c956906abebb13782722d1eb7e383).  
**Snapshot guard:** [89891a6](https://github.com/Leano-Jordan/ZazuEMP/commit/89891a6ea787028951020757b9de734030872023).  
**Regression coverage:** [601b944](https://github.com/Leano-Jordan/ZazuEMP/commit/601b94493f9b3c037a8c4e4e26fa264fee2aebc2) + [e249313](https://github.com/Leano-Jordan/ZazuEMP/commit/e249313deb9cf73373e1acc11f881c7d631a63a6) + [3713553](https://github.com/Leano-Jordan/ZazuEMP/commit/37135539e9de7e4548f67c972fced14138911d95) + [906233c](https://github.com/Leano-Jordan/ZazuEMP/commit/906233ce0d3183e811d3afed6ba4d111509b060d) + [ad6268c](https://github.com/Leano-Jordan/ZazuEMP/commit/ad6268c08b0697d87a9f0f4464455637e5d25acb).

- Closed the previously pending event-cost reconnect acceptance: both the production-fix and test-head Chromium jobs completed successfully. The broader browser acceptance passed, including desktop Chromium, mobile viewport flows and the phone-local attachment journey.
- Exact application/test-head checks: Laravel **656 passed / 1 skipped / 4,516 assertions**; PostgreSQL migration/backup/restore/private-file recovery smoke **PASS**; Zazu Quality, Director Health, Windows V1 package/fresh-install verification, Director Contract Lint and Push-on-main **PASS**. SonarCloud was **SKIPPED**, not passed.
- Continued adversarial review of `OfflineDomainMutationHandler::existing()`. Found a defense-in-depth gap: after a tenant-scoped identity lookup, the generic resolver dereferenced its polymorphic target without revalidating the target record's business ownership.
- Hardened the resolver to fail closed when the target model is missing, lacks a resolvable business owner, or belongs to a different business. Added an adversarial regression with a deliberately mismatched identity row targeting another business's Customer; the expected outcome is rejection with no mutation or ownership transfer.
- This is a corrupted/stale identity mapping defense, not a demonstrated remote client exploit. Do not overstate the threat model.
- Updated `docs/ZAZU_OFFLINE_FIRST_ARCHITECTURE.md` with the acceptance closure, ownership guard, morph-type validation and stale-snapshot protection.

**Verification of the ownership + morph-type + snapshot hardening:** exact source-head Laravel [44919777572](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44919777572) **PASS — 659 passed / 1 skipped / 4,527 assertions**; PostgreSQL migration/recovery smoke **PASS**; Zazu Quality, PHPMD and Director Health **PASS**. The matching offline-browser acceptance job remains in progress; do not close the full verification gate until it finishes. SonarCloud is skipped. Do not mark the new guard complete until those checks pass.

**Evidence maturity:** **77/100, unchanged**. No score uplift. V1 remains **NOT READY**.

**Next target after CI:** continue adversarial tenant-boundary review across offline linked records and identity types; then return to the remaining owner-PC recovery, physical-device, commercial workflow and POPIA acceptance gates. No branch creation; `main` remains authoritative.

---

## Director execution checkpoint — event-cost offline reconciliation — 2026-11-21

**Current documentation head:** `3f41be195c76b0af4932f8410ce7759604de9f3f` on `main`.  
**Application/test source:** `764f53682123481579a276303dc5da45c7c6038b` + `aa77c2677b8e32a9e232075182332103b43e9af8`.

- Reviewed the last event-cost tenant-isolation work and its exact-head CI. The foreign-business job identity is rejected by business-scoped identity and Event lookups; the Laravel isolation regression passed.
- The exact-head browser run on the preceding code exposed a real client-state defect: event-cost records were created without `pending_sync: true`, so the generic acknowledgement path could assign the canonical server identity and drain the queue while leaving the local sync flag null.
- Fixed the event-cost form to set the pending flag and strengthened the existing browser regression to assert pending before reconnect and synced after canonical identity reconciliation.
- Updated `docs/ZAZU_OFFLINE_FIRST_ARCHITECTURE.md` with the defect, root cause and evidence status.

**Verification on the application/test source:** Laravel **656 passed / 1 skipped / 4,516 assertions**; PostgreSQL migration/recovery **PASS**; Zazu Quality, Composer security audit, secret scan, Windows V1 package/fresh-install verification, Director Contract Lint and Push-on-main **PASS**. SonarCloud **SKIPPED**. The full offline-browser acceptance job is still running; do not mark the event-cost correction verified until it completes.

**Evidence maturity:** **77/100, unchanged**. No score uplift. V1 remains **NOT READY**.

**Next engineering target after browser closure:** continue adversarial review of offline tenant boundaries and linked-record ownership, then converge the remaining physical-device, owner-PC recovery, commercial workflow and POPIA acceptance gates.

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

## Director execution checkpoint — offline expense reconciliation — 2026-11-21

**Current verified source/test head:** `3c8db9bd02fec06603d48b3050f7d306d7b41070` on `main`.  
**Exact-head evidence:** Laravel [44902438027](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44902438027), Zazu Quality [44902438064](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44902438064), Push-on-main [44902438499](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44902438499).

- Laravel: **651 passed / 1 skipped / 4,480 assertions**.
- Offline browser: **61 desktop Chromium + 7 mobile viewport + 1 phone-local attachment journey passed**.
- PostgreSQL migration/registration/backup/restore/private-file recovery smoke: **PASS**.
- Zazu Quality, Composer audit, secret scan and Push-on-main: **PASS**. PHPMD and Director Health passed on the immediately preceding PHP-identical source head; SonarCloud skipped.
- Existing offline expense UI capture and sync now have browser coverage for job/supplier/purchase-order local-link preservation, canonical identity acknowledgement and queue drainage.
- Server feature coverage verifies the actual offline handler, exact amount/currency/status, business-bound linked records, audit entry and idempotent replay.
- No production source change was required for this target.

**Next target:** business archive restore must refuse an existing trusted-device/sync workspace before importing any records or files.

---

## Director execution checkpoint — offline payment reconciliation — 2026-11-21

**Current verified source/test head:** `e1debd77c9d064f92895768f120ac752a34d897b` on `main`.  
**Exact-head evidence:** Laravel [44901265394](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44901265394), Zazu Quality [44901265498](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44901265498), PHPMD [44901265404](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44901265404), Director Health [44901265367](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44901265367), Push-on-main [44901264787](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44901264787).

- Laravel: **650 passed / 1 skipped / 4,466 assertions**.
- Offline browser: **60 desktop Chromium + 7 mobile viewport + 1 phone-local attachment journey passed**.
- PostgreSQL migration/registration/backup/restore/private-file recovery smoke: **PASS**.
- Zazu Quality, Composer audit, secret scan, PHPMD, Director Health and Push-on-main: **PASS**. SonarCloud skipped.
- Offline payment server application now has a feature regression proving exact payment amount, invoice paid/balance calculations, related invoice revision advancement, and no duplicate payment/revision change on replay.
- Browser regression proves payment canonical identity and parent invoice revision reconciliation.
- No production code change was required for the payment target. The prior receipt acknowledgement reconciliation code remains covered by the same passing browser suite.

**Next target:** verify the existing offline expense flow through UI capture, server application, linked-record ownership, canonical identity acknowledgement and idempotent replay.

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

**The full offline sync workflow matrix remains OPEN and separate.** Physical Android cold launch/restart/reconnect, intended-installation recovery, live-business commercial reconciliation and POPIA operational sign-off remain OPEN. Evidence maturity: **77/100 (+2)** for exact-head closure of stale-write protection and the online writer bypass; V1 remains NOT READY. V1 remains **NOT READY**.

---

## Director deep-audit increment — 2026-11-21 (customer-contact create revision bypass)

**Source/test head:** `51a75eb1d06017211bd7808093d1863c930b6d71` on `main`. Laravel **634 passed / 1 skipped / 4,399 assertions**, PostgreSQL migration/recovery smoke **PASS**, PHPMD **PASS**; desktop/mobile browser acceptance passed (59 desktop, 7 mobile and 1 phone-local attachment/reconnect). Added an explicit two-paired-device stale-write regression: device B commits revision 2, device A's revision-1 edit is rejected with the authoritative remote snapshot and its local payload preserved.

- The repository-wide online-writer review found a second primary-contact mass-update bypass in `CustomerContactController::store`. The update-contact path had already been converted to per-model writes, but creating a replacement primary still used a relationship query-builder update that skipped `CustomerContact` model events and therefore left the registered Customer aggregate revision unchanged.
- Replaced that mass update with a locked collection of currently-primary contact models and per-model updates. Each affected contact now emits the nested-model event that advances the parent Customer revision.
- Added `CustomerContactLifecycleTest::test_adding_a_primary_contact_advances_customer_sync_revision`, asserting the former primary is demoted, exactly one primary remains, and the customer revision advances.
- The writer correction and paired-device regression are now verified on exact source/test head 51a75eb. CASE-ZAZU-0033 is CLOSED; the complete offline sync workflow matrix remains a separate OPEN gate.
- No schema changes, production data changes or branches. V1 remains NOT READY; evidence maturity remains 75/100 pending broader sync-matrix and owner acceptance.

## Director execution checkpoint — 2026-11-21 (receiver sync completeness)

**Canonical repository:** `Leano-Jordan/ZazuEMP` · **write target:** `main` only.  
**Application/test source head:** `559e66b87de8617f967b722b55b0fbfd9b116495`.  
**Exact-head verification — `559e66b`:** Laravel **632 passed / 1 skipped / 4,388 assertions**; offline browser **59 desktop Chromium passed**, **7 mobile-viewport passed**, and **1 phone-local Work/attachment/reconnect journey passed**; PostgreSQL migration, registration, backup/restore and private-file recovery smoke **PASS**. PHPMD, Zazu Quality, secret scan, Composer security audit, application, Director Health, Push-on-main and Actions/Python/JavaScript analysis **PASS**. SonarCloud **SKIPPED**, not counted.

### Confirmed defect and correction
- The receiver pulled an ordered delivery prefix, then applied the ordinary bounded bootstrap and acknowledged the prefix. A delivered aggregate outside a capped 500-record collection or a selected-record window could be omitted from the stored snapshot while the receiver's cursor advanced.
- Ordinary online changes had a second freshness issue: capped collections sorted by display name or creation order could continue to omit an older aggregate with a newer server revision.
- The client now includes the exact `delivery_sequences[]` returned by receiver pull when requesting bootstrap. The controller validates at most 100 positive integer values.
- `SyncBootstrapDeliveryTargetResolver` resolves only delivery rows belonging to that business, destination device and business stream. It resolves command primary/related aggregate identities as well as ordinary mutation identities.
- The bootstrap builder unions delivered targets into the normal bounded record sets/selected records, without widening another device's scope or removing the 500-record limit.
- Explicit pairing selections remain pinned: delivery-driven customer, Work, quote and preparation records are included only when the owner had already selected them.
- The ordinary bounded collections now select their working set by latest sync-identity update time (fallback to record `updated_at`) and retain their existing display order.
- The client only acknowledges after the complete bootstrap snapshot has been applied locally. The service-worker static cache is `zazu-static-v60` so installed clients fetch the updated cache-first offline client.
- Bootstrap access now uses the canonical business → device lock order, re-reads the active device and compares the persisted sync-token hash against the middleware-authenticated snapshot before updating last-seen activity and building data. Revoked/replaced devices return 401.

### Added regression coverage
- A supplier outside the default 500-record alphabetical window is promoted after an online revision update.
- When the identity timestamp is made older, the delivered sequence still forces that supplier into the intended receiving device's snapshot.
- A different device cannot use the same numeric delivery sequence to include that record.
- Cross-device quote → acceptance → invoice → payment reconciliation checks ordered deliveries, invoice/payment values and contiguous acknowledgement.
- Purchase receipt reconciliation asserts both the purchase-order state and every affected inventory aggregate.
- Browser acceptance asserts pull → targeted bootstrap → acknowledgement ordering and the exact sequence query value.

**Failure records:** CASE-ZAZU-0034 / ENG-DATA-004 (receiver snapshot omission) and CASE-ZAZU-0035 / ENG-AUTH-001 (bootstrap stale-device authorization). CASE-ZAZU-0033 (stale-write preconditions and full writer audit) remains separate and OPEN.

**V1 disposition:** NOT READY. Evidence maturity is now **75/100 (+2)** for the newly verified receiver reconciliation and bootstrap authorization controls. This is automated evidence maturity, not a production-readiness claim; broader sync matrix and owner acceptance remain open. Physical Android cold launch/restart/reconnect, intended-installation recovery drill, live-business commercial reconciliation and POPIA operational sign-off remain independent gates. ZAZU name clearance remains deferred by owner direction. No production data was changed and no branches were created.

---

## Director execution checkpoint — 2026-11-20

**Canonical repository:** `Leano-Jordan/ZazuEMP` · **write target:** `main` only.  
**Verified code SHA:** `041314fd80147758dd2edb33c187b16328aea938`.  
**Laravel run:** [44810699348](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44810699348) — **PASS**.  
**PHPMD:** [44810699344](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44810699344) — **PASS**.  
**Zazu Quality:** [44810699254](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44810699254) — **PASS**.  
**Director Health:** [44810699270](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44810699270) — **PASS**.  
**Push on main:** [44810699529](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44810699529) — **PASS**. SonarCloud [44810699192](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44810699192) was **SKIPPED**, not counted as a pass.

### Exact-head evidence
- Laravel: **623 passed / 1 skipped / 4,283 assertions**. This includes `SyncRevisionAggregateTrackingTest`, including the new command-parent revision regression.
- PostgreSQL migration, registration, database backup/restore and private-file recovery smoke: **PASS**.
- Offline desktop Chromium acceptance: **57 passed**; mobile-viewport acceptance: **7 passed**; dedicated phone-local Work-create/attachment/reconnect journey on Pixel 7 emulation: **1 passed**.
- PHPMD, Zazu Quality (application, Composer security audit and secret scan), Director Health and Push-on-main: **PASS**.

### Gate disposition
- **Offline session/device transition — AUTOMATED ACCEPTANCE PASS.** The suite exercises failed/unreachable logout, session preservation, account re-entry, expired web-session reauthentication, trusted-device revocation retry, cross-business pairing denial, stale response protection and queue preservation. Physical-device and lost-device operational acceptance remain separate.
- **Active Work attachments — AUTOMATED ACCEPTANCE PASS.** The suite exercises local file persistence, offline queueing, retry/idempotency, reconnect upload, server identity reconciliation, private download caching, cross-tab re-pair and late-response/logout protection. Pixel 7 emulation is not a physical phone restart test.
- **Revision-aware sync — IMPLEMENTED; FULL MATRIX OPEN.** The new regression verifies quote acceptance → Work, payment → invoice, purchase receipt → purchase order and affected inventory, inventory movement → inventory item, and asset allocation → asset revision propagation. Full workflow replay/reconciliation across all supported entities and physical-device acceptance remain open.
- **Commercial workflow — SEEDED AUTOMATED PASS; REAL-BUSINESS RECONCILIATION OPEN.** Current seeded proof: invoice R11,500 including VAT, deposit R3,450, final payment R8,050, net sales R10,000 excluding VAT, actual cost R2,800, indicative contribution R7,200 (72%). This is not audited accounting profit and does not verify live customer records.
- **DB-001 — DORMANT / HISTORICAL / NON-CRITICAL.** The Founder clarifies it occurred during offline event-creation/calendar-related task testing and has not recurred since offline-architecture work. No root cause or fix is claimed. **Installation recovery remains OPEN** pending the controlled drill on the intended installed PC.
- Still owner-dependent: physical Android cold launch/restart/reconnect; intended-PC recovery drill; real-business commercial reconciliation; POPIA operational controls; ZAZU name clearance.

**Release remains NOT READY. Evidence maturity remains 73/100 with no uplift.** No production deployment, live business data or device storage was changed. No branches were created.

---

## Director update — 2026-11-20 (current main reconciliation)

**Repository:** `Leano-Jordan/ZazuEMP` · **branch:** `main` only · **code commits:** `353ba56` (command-revision regression added), corrected in `98f5921` (inventory baseline captured before command effects). No branches created.

- **DB-001 disposition: DORMANT / HISTORICAL / NON-CRITICAL.** The Founder clarifies it occurred during offline event-creation/calendar-related task testing and has not recurred since offline-architecture work. No root cause or fix is claimed. Do not spend active engineering time on it unless it recurs or materially new evidence appears. Preserve references and incident records. This supersedes dated entries below that described DB-001 as an active blocker. **Installation recovery remains OPEN independently** until the controlled recovery drill is completed on the intended installed PC.
- **Revision-aware stale-write protection is present on main.** The server uses aggregate revisions, immutable same-device causal mutation chains, stale conflict snapshots and explicit keep-local/use-server resolution. Do not reopen the old “missing base revision” finding as though the implementation were absent.
- **Writer audit — bounded source pass:** inspected the Eloquent revision listeners and the customer/contact, Work, service/capability, supplier, quote, invoice/payment, purchasing, inventory and asset write paths, including relevant relationship/query-builder updates. No additional confirmed revision bypass was found in those inspected paths. Keep the remaining audit classified as source review until automated and physical acceptance close the wider matrix.
- **New regression:** `SyncRevisionAggregateTrackingTest::test_offline_commands_advance_every_related_aggregate_revision` exercises quote acceptance → Work, payment → invoice, purchase receipt → purchase order and inventory, and inventory movement → inventory item revision propagation. The test was added on main at `98f5921`; exact-head Laravel CI is pending.
- **Exact-head baseline before this new regression:** at `7d288c6`, Laravel tests and PostgreSQL migration/registration/backup/restore/private-file recovery passed; the offline-browser and mobile viewport suites passed, with the phone-local attachment substep still running at last observation. Zazu Quality and Push-on-main passed; SonarCloud was skipped. Do not attribute those results to `98f5921` until its run completes.

**Active release gates:** full offline sync matrix and exact-head acceptance; physical Android standalone offline cold launch/restart/reconnect; installation recovery drill on the target PC; populated quote-to-payment-to-cost/profit reconciliation; POPIA operational controls; ZAZU name clearance. PC-to-phone pairing remains CLOSED. V1 remains NOT READY; evidence maturity remains 73/100 with no uplift.

---

## Director engineering update — 2026-11-20

**Repository:** `Leano-Jordan/ZazuEMP` · **write target:** `main` only. **Current engineering head at this update:** `1d746d9`. No branches created.

### Active engineering — CASE-ZAZU-0033, revision-aware offline sync

A substantial implementation slice is now committed directly to `main`:

- `sync_entity_identities.revision` is the server-owned aggregate revision. The migration seeds pre-existing identity rows at revision 2 so devices carrying pre-revision snapshots cannot silently overwrite them; new identities registered by the application start at revision 1.
- `sync_mutations.result_revision` records the exact server revision produced by an applied mutation. `superseded_by_mutation_id` preserves immutable mutation history while allowing explicit rebase chains.
- Online Eloquent writes advance revisions for registered aggregate roots and snapshot children: customers/contacts, Work/requirements, quotes/versions/items, invoices/items/payments, purchase orders/items/receipts, inventory/items/movements, assets/allocations, suppliers, capabilities, preparation items and event costs.
- Offline application suspends those model listeners during the domain handler, validates the device's expected revision under the existing business/device/mutation transaction boundary, then advances the affected aggregate once. Payment, quote acceptance, purchase receipt, inventory movement and asset allocation also advance the parent snapshot revision.
- Bootstrap records now carry revisions. `queueMutation` records the expected revision for the first edit and a predecessor mutation ID for subsequent same-device edits. The server accepts a successor only against the exact result revision of its applied predecessor; an intervening online or other-device write breaks the chain.
- Stale edits become `stale_record_version` conflicts with the original local payload and a complete current remote aggregate snapshot. Keep-local resolution creates a new immutable mutation ID against the latest revision; use-server resolution discards the local mutation and refreshes the local record. A dependent edit cannot be rebased before its predecessor is resolved.
- The service-worker static cache is now `zazu-static-v42`, forcing installed clients to fetch the revision-aware offline queue implementation.

### Regression coverage added

`SyncRevisionConcurrencyTest` covers online revision advancement, stale-device rejection with remote snapshot, same-device sequential mutation chaining, an intervening external write, explicit keep-local rebase with a new mutation ID, application of the rebased mutation and a later successor still referencing the immutable original ID, use-server discard followed by a valid successor, conflict field comparison, and online revision bumps for Work, supplier, service and asset aggregates. Existing offline update fixtures now supply expected revisions.

- Conflict rebase and presentation logic were split into `SyncConflictRebaseService` and `SyncConflictPresenter` to bring the resolver back within PHPMD complexity limits. A first PHPMD run exposed excessive resolver complexity; the refactor is awaiting exact-head CI confirmation.

### Verification status

- PHPMD: PASS on implementation commit `2b4d73b` after conflict-service decomposition (the earlier interim run correctly exposed resolver complexity).
- Populated upgrade/rollback: PASS on `2b4d73b`.
- Director Health: PASS on `2b4d73b`.
- Psalm PHP security scan: analysis job PASS on `2b4d73b`; result-publishing job was still queued.
- Laravel unit/feature, PostgreSQL migration smoke and offline browser acceptance: running on `2b4d73b`.
- Windows V1 package: installation candidate build PASS; fresh-install verification still running.
- Zazu Quality: queued for `2b4d73b`.
- Historical full application baseline `526bdf3` remains historical evidence only; it does not verify this revision-protocol code.
- CASE-ZAZU-0033 remains OPEN pending exact-head Laravel/PostgreSQL/offline-browser acceptance and a final audit for online query-builder/bulk writers that may bypass Eloquent model events.

### Product boundary and remaining gates

- Current offline UI remains bounded IndexedDB workspace and mutation queue; customer and Work local directories/inspection, plus dashboard quote detail/acceptance. Cached Laravel HTML remains a snapshot, not a general local route renderer. Do not claim app-wide offline parity.
- Still open: full sync acceptance matrix; physical Android standalone offline cold launch/restart/reconnect; installed-target recovery; live commercial reconciliation; POPIA operations; ZAZU name clearance.
- V1 remains NOT READY. Keep the official evidence-maturity score at 73/100 until physical and operational acceptance evidence is completed.

---

## Founder-directed target addition — offline sync reliability across V1 (2026-11-20)

- The Founder has explicitly added **offline sync reliability across the full supported V1 workflow matrix** as an active release target. Sync quality is not currently at the required standard across the board.
- Track this separately from standalone phone-local offline launch and separately from PC-to-phone pairing. **PC-to-phone pairing is CLOSED by Founder confirmation and must not be reopened.**
- Acceptance must cover the complete declared UI → IndexedDB → durable mutation queue → server handler → stable entity identity → acknowledgement/pull/bootstrap → reconciled UI path for every supported offline operation.
- Required checks include restart durability; retries after offline, timeout and partial responses; duplicate/reordered delivery idempotency; parent/child references; cursor/pagination and bootstrap consistency; device/business isolation; explicit conflict preservation and resolution; attachment bytes/metadata/linkage; and domain rules for financial, inventory, purchasing, scheduling and asset mutations.
- No new offline engine, duplicate queue, cache-only workaround, relaxed isolation or idempotency assertion, or blanket offline claim. Inspect and extend the existing architecture only where evidence requires it.
- Release checklist: `docs/V1_RELEASE_CHECKLIST.md`; sole readiness register: `.ai/engineering/READINESS_REGISTER.md`; canonical offline policy: `Leano-Jordan/Rosscore-Labs/docs/ROSCOR_OFFLINE_FIRST_STANDARD.md`.
- **Latest exact-head evidence — `526bdf38dab6ff540c143b0e57dfcee468006dc3` (2026-11-20):** Laravel PASS — 607 passed / 1 skipped / 4,158 assertions; PostgreSQL migration/registration/backup/restore/private-file recovery PASS; offline browser PASS — 51 desktop Chromium tests, 6 mobile-viewport tests and 1 phone-local Work/attachment journey. This head adds a fresh-page/cold-launch browser regression proving the authenticated cached shell, local customer/Work records and durable queued mutations survive a new page in the same installed-browser context. Zazu Quality and Push-on-main PASS; SonarCloud skipped. PHPMD, Psalm, Windows V1 package and populated upgrade/rollback also PASS on application-equivalent parent `30e70e929dc848c0119c8452395ad225a23b7c47`; `ae3d49c` and `526bdf3` are test-only commits after that application-code baseline.
- **Sync source corrections on this head:** bootstrap replacement now preserves local state while failed mutations/open conflicts remain; the client reconciles canonical identities for `event_cost`, nested `payment`, `inventory_movement` and `asset_allocation`; disconnected inventory receipts update local stock/history; final offline invoice payment sets local status to paid; and retryable attachment uploads re-enter the backoff scheduler while retaining their idempotency key. Regression tests cover the relevant paths.
- Stale six-digit E2E fixtures were aligned with the existing eight-digit pairing contract. This is test maintenance, not a reopening of the Founder-confirmed PC-to-phone pairing, which remains CLOSED.
- **Still OPEN:** broad offline sync acceptance across the whole supported workflow matrix; stale concurrent-update detection/base-version conflict handling; actual Android standalone offline launch/restart/reconnect; installed-target recovery; live commercial reconciliation; POPIA operations and ZAZU mark clearance. Keep release NO-GO and do not uplift the official 73/100 evidence-maturity score from CI alone.
- Engineering corrections committed directly to `main`: `b07110b` allows a deliberate second offline form save only after the saved fields change and advances the service-worker cache to v41; `30e70e9` splits the two workspace purge command responsibilities without changing deletion safeguards; `ae3d49c` corrects the payment E2E assertion to compare the pushed mutation with the queued payment identity. No branches were created.
- The offline form correction is verified by the 51 desktop + 6 mobile + 1 phone-local attachment browser acceptance on exact head. It does not close stale-update detection, full sync parity or physical Android acceptance. Keep release NO-GO; no readiness uplift.

---

## Phone pairing discoverability and eight-digit contract correction — 2026-11-19

- Root cause confirmed from current source: owner settings generated an eight-digit code and `SyncController::provision` required eight digits, but `public/zazu-offline.js` silently truncated the phone submission to six digits. The phone entry itself was only exposed through a hidden query-string prompt.
- Corrected the client to normalize and strictly validate all eight digits before sending the provisioning request; it no longer truncates or submits incomplete codes.
- Added a discoverable **Pair this phone** form to the authenticated app shell. It is revealed on phone-sized/mobile user-agent layouts only while the device is unpaired, provides numeric/one-time-code input, eight-digit validation and inline error feedback, and keeps the owner-generated code's 10-minute/single-use approval model intact.
- Removed the hidden query-string/browser-prompt flow. The phone reloads after successful pairing so the newly bootstrapped business workspace is loaded.
- Bumped the precached service-worker static cache from v38 to v39 and updated the service-worker cache regression test. This is required so installed phones do not keep executing the old truncated client.
- Added a focused feature regression for the rendered pairing entry, strict eight-digit client contract, removal of the prompt and cache version. Existing server tests already cover eight-digit generation and one-time provisioning.
- Updated `docs/ZAZU_OFFLINE_FIRST_ARCHITECTURE.md` with the user-visible pairing flow and cache invalidation rule.
- Source and regression changes committed directly to `main`; no branches created. **Tests/CI and rendered phone acceptance have not yet been verified on this exact head.** The owner's old plain-HTTP Wi-Fi address remains unsuitable for installed PWA/service-worker acceptance; the separate private HTTPS setup must complete before physical phone acceptance.
- No change to logout/session semantics, local queues, business-scoped IndexedDB identity or server-side pairing authorization.

---

## Commercial direction adopted — 2026-11-19

- Added the Founder-approved commercial positioning and execution direction to `docs/COMMERCIAL_ARCHITECTURE_RESEARCH_2026-11-16.md`.
- Positioning: **Run your business. Manage every job. Know what you actually earn. Even offline.** Keep Zazu broad across event/catering and related service businesses; do not narrow to one trade without customer evidence.
- Commercial proof remains the existing event/job → quote → acceptance → invoice → payment → purchasing/receiving → incurred costs → event contribution → export chain. Do not describe indicative event contribution as audited accounting profit.
- WhatsApp quote handoff is an evaluation of the existing signed-link workflow, not approval for a new messaging subsystem. Inspect existing behavior before deciding whether a gap exists.
- Trust requirements are clarified: POPIA operational sign-off remains open; invoice numbering does not establish SARS tax compliance; claims about privacy, export/recovery and offline capability must match demonstrated behavior.
- First-impression priorities are the landing page, dashboard, quote workflow, signed public quote page and customer PDF. Improve the existing visual system; require rendered desktop/phone evidence for UI acceptance.
- A fictional, ZAR-valued demo business is a commercial preparation task. Do not seed demo data into real customer installations.
- This direction does not amend the V1 scope freeze or create new release gates. Engineering remains focused on existing V1 blockers and observed workflow defects.
- No new branch created. Documentation committed directly to `main`.

---

## Sentinel Owner Admin consolidation — 2026-11-19

- Integrated business-scoped Sentinel checks into the existing `/owner` Owner Admin page. The checks are limited to read-only database response, active owner membership and this business's customer/event/service data paths; they do not call the installation-wide incident recorder or return global incident metadata.
- Removed the standalone Sentinel Rescue controller/view. The authenticated owner-only legacy `/sentinel/rescue` route redirects to `/owner#business-health`. Persistent System navigation now exposes Owner Admin; the Founder destination is labelled Founder Control Centre and provides a direct return to the business workspace when one is active.
- Removed the Owner Admin metric tiles that repeated dashboard metrics. The page now focuses on business health and owner actions (settings, audit, business export/recovery, compliance and services).
- Founder Control Centre now states that the registered customer licensing registry is not live fleet telemetry. Remote customer installations do not currently report health/connectivity; no heartbeat or remote telemetry was introduced.
- Focused feature coverage was updated/added. **Tests and rendered UI are not yet verified on the runtime or exact CI head.** No branch was created; all changes were committed directly to `main`.

---

## Rosscore Labs Founder Control Centre and Sentinel direction — 2026-11-19

- The user is the sole Founder, owner and final authority for Rosscore Labs and Zazu EMP. Address him directly; never frame the Founder as hypothetical, replaceable or a role that may pass to someone else. Company identity: **Rosscore Labs**.
- `/admin/sentinel` is the Founder's installation-wide oversight dashboard. Its purpose is to monitor every Zazu instance and platform health. It should provide fleet/instance status and operational visibility without exposing unnecessary customer or business-sensitive records.
- Sentinel Rescue (`/sentinel/rescue`) is to merge into the existing business Owner Admin experience as its health, diagnostics and safe-recovery capability. Avoid a separate owner-facing Rescue page/destination and duplicated owner dashboard information.
- Maintain strict scopes: Founder Control Centre = cross-instance/platform oversight; Owner Admin with integrated Sentinel Rescue = diagnostics for the active authorised business only. Never expose the global incident ledger or another business's data to a business owner.
- Use one coherent Sentinel capability with server-enforced data scopes. Both surfaces must be discoverable through persistent navigation; no manual URL entry as the intended workflow. Keep a clear return path to the business workspace. Link visibility is not a security boundary.
- This records the agreed direction only. No application code, routes, permissions or UI have been changed by this documentation update. Implementation requires an authorised execution batch and source-level inspection first.

---

## Provisional customer/privacy contact confirmed — 2026-11-19

- Owner-approved current contact: `rosscorelabs@outlook.com`.
- Recorded as a provisional Zazu customer-support, privacy/data-subject request and initial customer incident-reporting channel in the privacy notice draft, data-subject request procedure, incident response runbook, customer terms draft, documentation index and commercial/legal register.
- Replace consistently when a dedicated Zazu address is established. This email is not a substitute for statutory regulator reporting or an approved emergency escalation process.
- These documents remain pre-launch drafts; publication still requires operational mailbox testing, provider/identity details and appropriate legal review.

## Manual offline licence issuer implementation — 2026-11-19

- Added the existing-app Artisan command `zazu:license:issue` for manual first-customer signed-token issuance. It supports the approved tier identifiers, explicit expiry or deliberate never-expire choice, signed feature metadata, renewal IDs and revocation IDs.
- The command reads a base64 Ed25519 secret key only from an external file, rejects key files inside the Zazu project, and verifies that the derived public key matches the configured app verification key. It does not create or store a key, change customer data, add feature restrictions or contact a remote service.
- Added feature tests for verifier compatibility, default feature-object serialization, expiry choice, revocation identity, project-local key rejection, public/private key mismatch, invalid timestamps and invalid feature metadata.
- Added the local Business ID to the existing owner-only licence settings section so a customer can provide the exact business reference needed for manual issuance; added a focused settings regression test.
- Updated the existing offline licensing specification with command usage and the remaining key-custody boundary.
- Issuer source/test and owner-reference UI/test changes are committed directly to `main`; latest application/test commit: `1c5033bbba9f028238dbedc1f7fcd7a408997e16`. The licensing specification and this state record were updated afterward.
- Local syntax check: command passes `php -l` on PHP 8.4.24. Timestamp parser spot-check accepts explicit-offset/Z timestamps and rejects an invalid calendar date.
- **Not yet verified:** GitHub Actions / full Laravel tests on the exact current head. The latest combined commit-status query returned no status records; this is not a CI pass.
- **Production boundary remains open:** no production key was generated or used. Owner-controlled key provisioning, NTFS ACL restriction, protected recovery copy, and verification that the customer package excludes the private key are required before issuing real customer licences. No score uplift is claimed.



## Operating reset and current V1 mission — 2026-11-18

**Source:** Founder-supplied read-only research report of main at cb7ab270. The report states that Laravel and browser suites were not run. Its test/CI statements are repository-documentation claims; market numbers and adoption scenarios are research estimates, not verified forecasts.

**Current assessment:** Late Alpha; the report estimates V1 completion at approximately 74%. This historical 2026-11-18 snapshot treated DB-001 as OPEN; that status is superseded by the Founder clarification at the top of this file. The actual installed workflow has not yet been demonstrated error-free. There are no paying customers or completed customer pilots.

**Historical priority (superseded 2026-11-22):** DB-001 diagnostic follow-up was proposed at this time. Do not execute that old proposal; the current Founder disposition at the top of this file is authoritative and no DB-001 owner action is required.

**Next priorities, in order:**
1. Prove the populated-record quote → invoice → payment recording → costs/expenses → event profit workflow. Manual/EFT payment recording is enough for the first pilot.
2. Establish manual EFT-paid signed licence-key issuance and activation for the first ten customers; no subscription automation without approval.
3. Complete legal/privacy information, Information Officer registration and operational POPIA responsibilities, plus a working WhatsApp support contact.
4. Run a two-week pilot with 2–3 friendly operators and ship only pilot-evidenced fixes.

**Operating constraints:** Focused execution batches, but engineering remains active; Sentinel and diagnostics remain active development targets; no new branches; main only; no Render or AMPPS in Zazu; no unrelated offline/sync expansion or new subsystem without explicit Founder approval; avoid repeating unchanged browser smoke tests; cap docs-only work. Pricing remains unresolved because existing tier models conflict and requires Founder decision.

**Progress labels are separate:** ENGINEERING COMPLETE; TESTS PASSED; VERIFIED ON FOUNDER PC; COMMERCIALLY USABLE; CUSTOMER VALIDATED. Never infer a later state from an earlier one. For installed-workflow acceptance, Founder confirmation on the actual PC is required. Documentation updates do not count as application acceptance.

**Owner/operator model:** The Founder is the sole human operator and final authority. Jarvis must state the intended scope, repository/ref, planned action and stop boundary before each distinct autonomous work batch. Do not invent staff or teams, and do not claim local-device verification without evidence.

## Sentinel local recovery implementation — 2026-11-18

- Added `/api/sentinel/recovery` as a stateless, loopback-only host-operator recovery surface outside Laravel's web/session middleware.
- The route explicitly excludes Laravel's database-backed `throttle:api` middleware; the recovery path therefore does not rely on the default database cache store.
- Access requires a loopback peer address and loopback Host header. POST also requires same-origin Origin and rejects cross-site Fetch Metadata.
- Added `zazu:sentinel-recovery-code`: creates a 256-bit random code, stores only SHA-256 hash plus five-minute expiry, invalidates prior codes, and displays the new code once.
- The code is consumed atomically under a filesystem lock and cannot be replayed. It is entered in a local form, never placed in a URL.
- The unlocked report reuses read-only `ZazuSystemHealth` checks. It contains installation health only and no customer records, attachment bytes, global incident ledger or founder console.
- Added feature coverage for unauthenticated local screen access, LAN denial, cross-origin POST denial and one-use report access.
- Updated Sentinel, DB-001 and Windows package documentation.
- **Acceptance remains pending:** GitHub Actions must pass; verify the command, route, file permissions and database/session-outage behaviour on the packaged Windows installation. This path cannot diagnose a stopped host, PHP process or broken Laravel bootstrap.
- All changes committed directly to `main`; no branches created.

## Sentinel access isolation correction — 2026-11-18

- Removed the one-year signed bearer URL from `/api/sentinel/rescue`; unauthenticated requests can no longer reach Sentinel Rescue.
- Moved Rescue to `/sentinel/rescue` behind `auth`, `auth.session`, `business.context` and the active-business `owner` middleware.
- Added explicit `sentinel.admin` route authorization for `/admin/sentinel`, separate from ordinary business-owner roles.
- Founder authorization now resolves only to the one explicitly configured email identity (`ZAZU_PLATFORM_ADMIN_EMAIL`). The legacy plural `ZAZU_PLATFORM_ADMIN_EMAILS` variable is ignored completely; business `admin` roles do not grant platform access. Existing installations must set the singular variable for the designated founder.
- Founder navigation is visible independently of business-owner navigation. Business owners see only their owner Rescue entry; the founder sees the Control Centre only when the configured identity matches.
- Added regression coverage for unauthenticated denial, removal of the old API route, authenticated owner access and updated founder identity configuration.
- **Resilience trade-off:** authenticated business-owner Rescue still depends on the normal user/session path. The separate loopback-only one-time-code host recovery path now provides installation health checks without that dependency. Packaged Windows acceptance remains open; this does not diagnose a stopped host or broken PHP/Laravel bootstrap.
- Changes were committed directly to `main`; no branches were created. Automated tests and runtime acceptance have not yet been executed in this environment.
# Zazu EMP — Current Director State

## DB-001 support capture — 2026-11-18

The DB-001 error page now offers a reference-scoped download of the matching redacted incident record through the local API. It is rate-limited and exposes only an allowlist of diagnostic fields; SQL, bindings, exception messages, customer data and credentials are excluded. This removes the need to copy diagnostic files between the Windows host and phone. The endpoint is useful only when the running installation contains the updated incident recorder. **Historical status only:** DB-001 was recorded as open at the time this diagnostic endpoint was added. That status is superseded by the 2026-11-22 Founder clarification at the top of this file. No root cause or fix is claimed.


As of: 2026-11-17
Repository: Leano-Jordan/ZazuEMP
Last verified application-code baseline: ba94b39e0460e4e57c560e98541808c762f50e25
Canonical branch: main only; never create branches. Engineering changes are active on main; no branches created.
Owner mandate: ship a dependable first-business V1; preserve scope; no Render or AMPPS workflow.

## Owner-approved event scheduling implementation — 2026-11-17

Owner authorised the focused V1 addition: multi-day event dates, start/end times, setup/collection dates, schedule clash warnings and connected rental allocation dates. Staff scheduling, quantity-based rental stock and workforce management remain deferred.

Implementation candidate is on `main` at source HEAD `71c8fc5c7aeba392b95e26aee694945b286f2c8e` (17 Nov 2026). Added an additive events migration; online Work create/edit persistence and validation; offline mutation payload/server application and workspace bootstrap; calendar multi-day expansion; schedule clash detection; and rental allocation date defaults. Added focused online/offline feature tests, including preservation of new schedule fields when older queued offline mutations arrive. At the current baseline, Laravel passes 532 tests / 1 skipped / 3,714 assertions; PHPMD passes; the Windows package and PowerShell first-install + private-media recovery pass on the immediately preceding code baseline 2ef2a0b. PostgreSQL and desktop/mobile offline-browser acceptance completed PASS for 71c8fc5 in run 44152431508. Zazu Quality remains red on the unpatched picomatch advisory. Rendered/physical acceptance is pending; no readiness uplift is claimed.

## Event scheduling implementation verification — 71c8fc5 (17 Nov 2026)

- Online event creation/editing persists event date range, start/end times, setup and collection dates; invalid date/time ranges are rejected.
- Active schedule overlaps produce a warning while preserving operator save; setup and collection logistics are included in the conflict window.
- Calendar now spans event and logistics dates and labels setup/collection days.
- Offline job mutations carry the schedule fields; server-side validation protects the fields and older queued mutations cannot erase schedule values they do not know about.
- Asset allocation defaults follow the event setup/collection window; existing serialized-asset overlap/return protections remain in force.
- Laravel: PASS — 532 passed / 1 skipped / 3,714 assertions. PHPMD: PASS.
- Windows V1 package, fresh PowerShell install and Windows SQLite/private-media recovery: PASS at 2ef2a0b (same scheduling implementation; latest commit after that is regression-test-only).
- PostgreSQL migration/recovery and desktop/mobile offline-browser acceptance: PASS at 71c8fc5 (run 44152431508; all three jobs succeeded).
- Zazu Quality remains RED on the unpatched high-severity picomatch build-tool advisory; no breaking downgrade or suppression applied.
- No readiness uplift. Physical Android, intended-installation recovery, populated-business financial reconciliation and POPIA remain operator gates.

## Current repository verification — fd82f6a (17 Nov 2026)

The only repository change between application-code baseline a5ead69 and fd82f6a is `.github/workflows/zazu-windows-package.yml`; application source and tests are unchanged across that range.

Application-code CI at a5ead69:
- Laravel: PASS — 526 passed / 1 skipped / 3,681 assertions.
- Offline browser acceptance: PASS — 45 desktop Chromium, 6 mobile-viewport and 1 phone-local attachment journey.
- PostgreSQL migration, account/workspace registration, backup/restore and private-file recovery: PASS.

Windows V1 Package run 82 at fd82f6a: PASS. The production candidate built, Windows PowerShell fresh installation passed, and a new Windows-native recovery drill created a disposable SQLite record and private-media file, backed up both, mutated both, restored the archive and verified the original database value and private-file bytes.

Director Health, Director Contract Lint and Push-on-main passed at fd82f6a. Composer security audit and secret scan passed.
LAN visual asset delivery is addressed in the current source: non-loopback clients use compiled same-origin Vite assets, a stale `public/hot` marker cannot send phones to an unreachable dev server, and the Windows LAN launcher removes stale hot state and verifies the built manifest/assets. `ViteAssetDeliveryTest` specifically exercises a remote client with a stale loopback hot URL and asserts compiled CSS delivery. These changes post-date the supplied 13 Nov phone screenshot. The actual Android launch still requires the HTTPS-origin physical-device acceptance in `docs/ZAZU_ANDROID_OFFLINE_INSTALL_ACCEPTANCE.md`; the old HTTP :8000 screenshot is not proof of current behavior or offline capability.

Zazu Quality is currently RED at `npm audit --audit-level=high`. The current lockfile contains the development/build-tool dependency chain that resolves to picomatch versions flagged by [GHSA-fc5q-39xc-vjpf](https://github.com/advisories/GHSA-fc5q-39xc-vjpf) / CVE-2026-112366. GitHub's advisory currently lists affected versions through 4.0.7 and no patched version. The finding is in the npm development/build toolchain; the Windows runtime candidate excludes npm manifests and node_modules. This still blocks the quality workflow. Do not suppress the advisory or apply the suggested breaking Vite downgrade without a verified fix.

The Windows CI round-trip is supporting evidence only. It does not close the installed-target operator gate because it is not a recovery drill on the intended owner/customer installation.
## Verified work
Rental asset date allocation was reproduced at c7be735: a second non-overlapping booking returned HTTP 422 before date-overlap evaluation. The online/offline correction now permits non-overlapping reservations, rejects overlapping and open-ended conflicts, supports per-allocation returns, and keeps asset status allocated until all active bookings are returned. Manual physical return remains operator-confirmed.

Online and offline regressions pass at current HEAD. The optional end date is normalized once and used consistently for validation, overlap checks, persistence and audit.

Trusted-device security corrections at e5dc263: fresh desktop browsers no longer receive long-lived sync credentials automatically; an owner must explicitly trust the current device in Settings. The installed-phone behaviour remains an accepted owner limitation. If destructive local workspace cleanup cannot reach the host to revoke a device, only its business-scoped installation UUID is retained and the next authenticated owner session retries revocation through an owner-only endpoint. Ordinary web logout remains non-destructive and preserves the trusted workspace by design.

Password recovery now revokes active and pending sync devices across every business where the resetting user has the owner role, clears token and pairing credentials, and records per-business audit events. Staff-only business devices are left untouched. The regression covers an owner whose currently selected business is a staff membership, protecting cross-business recovery from the normal active-workspace save guard.

Offline financial push validation uses integer-cent / hundredths parsing and rejects excess precision before posting. Regression coverage includes quote line quantity and price, event and asset costs, inventory movement, expenses, purchase-order quantity and price, purchase receipts, and payments.

## Earlier application-code CI — e5dc263 (historical baseline)
- Laravel run 44127089629: PASS — 523 passed / 1 skipped / 3,662 assertions.
- Offline browser acceptance in the same run: PASS — 45 desktop Chromium, 6 mobile-viewport, and 1 phone-local attachment journey.
- PostgreSQL migration, registration, backup/restore and private-file recovery: PASS.
- Windows V1 package and PowerShell fresh-install verification: PASS.
- Populated upgrade/rollback, Zazu quality, Psalm, PHPMD, Director Health and Push-on-main: PASS.
- SonarCloud: SKIPPED, not counted as a pass.

No tests were weakened. Evidence maturity remains 73/100; no score uplift. Physical Android, intended-installation recovery, live commercial reconciliation and POPIA remain operator gates.

## Independent audit reconciliation
All eight Claude findings remain in the sole readiness register. Invoice numbering is unchanged pending South African tax-adviser guidance. Session auto-trust scope, commercial tiers and feature expansions remain owner decisions. Pairing-code hardening is deferred; hosted proxy behavior is an accepted limitation for local Windows/LAN V1.

## Active V1 acceptance gates
1. Physical Android installed-app cold launch, offline restart and reconnect.
2. Installed-target recovery drill including private media.
3. Live populated-business quote → invoice/payment → costs → profit reconciliation.
4. POPIA operational/privacy sign-off.

## Release
V1: NOT READY. Historical state/register/memory, checklist and changelog are preserved in docs/archive/engineering/2026-11-17-state-register-memory-history.md.


## Exact-head scheduling acceptance closure — 2026-11-17

- Source: `71c8fc5c7aeba392b95e26aee694945b286f2c8e`.
- GitHub Actions run `44152431508`: Laravel tests, PostgreSQL migration/recovery and offline-browser acceptance all completed successfully.
- Offline-browser job `134284080089`: PASS; browser suite, mobile-viewport Work/customer acceptance and phone-local attachment journey all passed.
- PostgreSQL job `134284080622`: PASS; full migration set, account/workspace registration and backup/restore/private-file recovery passed.
- Laravel job `134284080768`: PASS; service-worker/session contracts, LAN asset-origin checks, Blade/manifest/config checks and PHPUnit/Pest passed.
- Documentation-only follow-up on `main` is `8bf76fe034f888138c0542dc308e3d4d752bd304`; no application code changed after the tested source baseline.
- Scheduling engineering/CI subgate is CLOSED. Physical Android and other operator gates remain OPEN. Release remains NOT READY.


## Director follow-up — job date integrity, startup migration safety and plain English (2026-11-17)

- Introduced `EventScheduleValidator` as the single relationship check for online job create/edit and offline job sync.
- The validator rejects an end date before the start date, setup after the start date, equipment collection before the job end date, and an earlier/equal end time for a one-day job. Multi-day jobs may end at an earlier clock time on their final day.
- Job create/edit screens now share the same clearly labelled schedule fields and explain what each date/time means. The form sets browser date/time limits as values change.
- Added online and offline regression cases for conflicting dates, shared-validator relationship coverage, and form labels/date limits.
- Simplified the DB-001 and account-error wording. Added `docs/ZAZU_PLAIN_LANGUAGE_STANDARD.md` as a standing product-wide writing rule.
- Windows startup now checks migration status before starting Laravel. When updates are pending, it must create and verify a full database + private-files backup before applying them, then confirm no migrations remain pending. Backup or migration failure blocks server startup.
- **Verification:** Source is committed on `main`. Exact-head CI and PowerShell execution for this follow-up have not yet been observed. No readiness uplift is claimed.

## Historical DB-001 incident notes — 2026-11-17 (superseded by 2026-11-22 Founder clarification)

- Historical reports had grouped these references as repeated DB-001 incidents: `2bc77a48-5c6c-4095-afe2-fa079f60e4ba` and `aa9080f4-9580-41c9-a643-439b31928ffa`. The Founder clarification on 2026-11-22 supersedes that grouping for the occurrence under discussion: it arose during offline event-creation/calendar-related task testing and has not recurred after subsequent offline-architecture work.
- The contemporaneous note associated the reference with an Account access error screen, but the Founder clarified on 2026-11-22 that the occurrence under discussion happened during offline event-creation and calendar-related task testing. That later clarification supersedes the earlier characterization.
- Source inspection found that the Work controller writes the five new scheduling columns, while the previous Windows start path did not check or apply pending migrations. A local SQLite database behind the current code is therefore the leading explanation, **not a confirmed root cause**.
- Backup-first migration handling has now been added to the shared Windows startup script. It has not yet run against the owner's installed database. The incident was recorded as OPEN at that historical checkpoint; its current disposition is the dormant, non-critical status at the top of this file.


## DB-001 diagnostic contract correction — 2026-11-17

- Inspected `app/Support/ZazuIncidentRecorder.php`, `bootstrap/app.php`, `AttachRequestId.php`, `ZazuErrorCatalog.php` and `ErrorHandlingTest.php` at the current source baseline.
- Confirmed that the authoritative incident logger deliberately records bounded metadata only: request ID, classification, exception class, route/action, method/path, timestamp and source file/line. It intentionally omits exception messages, SQL, bindings and stack traces to prevent customer-data/credential leakage. Request middleware adds the same request ID to log context and the response header.
- Corrected `docs/ZAZU_ERROR_INCIDENT_FRAMEWORK.md` on `main` (documentation-only commit `f88af665ae09b6f87f3c8d73a52c3cc5e4854dbd`) because its prior wording incorrectly claimed the routine incident log contained technical exception messages, stack traces and safe query context.
- This clarifies the diagnostic contract but does not establish the cause of either owner-reported DB-001. At that historical checkpoint, the matching log entry was requested to identify exception class, source file/line and route. That request is superseded by the 2026-11-22 Founder clarification at the top of this file; do not pursue it unless DB-001 recurs on current `main`.
- No application code changed and no database was touched. The OPEN status written here was historical and is superseded by the 2026-11-22 Founder clarification.


## Backup publication integrity hardening — 2026-11-17

- Closed a source-level safety gap found while reviewing the backup-first Windows startup path: the launcher previously accepted a non-empty ZIP based on process exit code and file size, while full manifest/checksum validation happened only during restore.
- `ZazuBackupCommand` now reopens the completed temporary ZIP with consistency checking, validates the Zazu format/driver/manifest, checks exact payload membership and verifies every payload SHA-256 before publishing the archive. Failed verification prevents publication.
- Added a focused regression proving the verifier accepts a valid archive and rejects a tampered private-storage payload.
- Changes are on `main`; exact-head Laravel/Windows CI has not yet been observed. This is a source correction, not proof of the owner's installed backup or migration. DB-001 was recorded as OPEN at this historical checkpoint; that status is superseded by the 2026-11-22 Founder clarification.


## Zazu quality workflow — scoped npm advisory exception — 2026-11-17

- Exact-head GitHub Actions run `44179163841` passed all three jobs on `76be1f234e7726352c2e15b511b37b9084d2ddfa`: application, Composer security audit and secret scan. The application job completed `npm ci`, the scoped npm audit policy and the service-worker authentication cache-race tests.
- The underlying npm audit still reports GHSA-fc5q-39xc-vjpf / CVE-2026-112366 against picomatch (locked versions through 4.0.7). No patched npm version is currently published. The npm CLI's suggested force downgrade is not adopted.
- Added `scripts/audit-npm.mjs`: it permits only this exact advisory and only packages whose complete npm audit dependency chain leads to that finding. Every other high/critical finding remains blocking. The exception is temporary and must be removed when the advisory is fixed or withdrawn.
- This is a CI policy exception, **not a dependency remediation** and not a claim that the advisory is resolved. Zazu's Vite/Laravel build configuration uses repository-defined static glob patterns; no user-supplied glob input was found in the inspected Vite configuration.
- DB-001 was listed as OPEN at this historical checkpoint; that status is superseded by the 2026-11-22 Founder clarification. No release-readiness uplift was claimed at the time.


## New-job form regression closure — 2026-11-17

- Root cause of the single failing Laravel workflow: `resources/views/work/create.blade.php` omitted the required `name` field while `WorkController` continued to validate and persist it. Three offline-browser journeys consequently stalled on the missing field selector; the tests were exercising the real required workflow correctly.
- Restored the required **Work / event name** field and validation feedback on `main` in application commit `ba94b39e0460e4e57c560e98541808c762f50e25`. No tests were weakened and no branches were created.
- Exact-head Laravel run `44184183003`: PASS — Laravel tests, PostgreSQL migration/registration/backup/restore/private-file recovery, and offline-browser acceptance including mobile customer-to-Work and phone-local attachment journeys.
- Supporting exact-head checks: Zazu Quality `44184183085` PASS; Windows V1 Package `44184182900` PASS including PowerShell fresh-install verification; Director Health `44184182984` PASS; Push-on-main `44184180721` PASS. SonarCloud was skipped and is not counted as a pass.
- This closes the form regression and associated CI failure only. DB-001's OPEN label in this historical note is superseded by the 2026-11-22 Founder clarification; the separate V1 release gates remained open at that time.


## Historical DB-001 diagnostic record — reference 895e2c23-bc6b-4919-a891-1e8e5c1e27e4 (2026-11-18; superseded)

- This contemporaneous summary described the reference as an account/workspace issue. The Founder clarified on 2026-11-22 that the occurrence under discussion happened during offline event-creation/calendar-related task testing; that clarification supersedes the earlier description. Reference retained for traceability only: `895e2c23-bc6b-4919-a891-1e8e5c1e27e4`.
- Existing incident recorder now emits bounded database diagnostics (connection, driver, SQLSTATE, numeric driver code and operation) without SQL, bindings, exception messages or credentials.
- Added `ops/windows/Get-ZazuIncident.ps1` and `docs/DB001_INCIDENT_CAPTURE.md` on `main`. The script finds the exact request ID in Laravel logs and outputs only an allowlist of diagnostic fields. This is a safe evidence-capture path, not a root-cause fix.
- The matching record and repeat test were proposed at this historical checkpoint. That request is superseded by the 2026-11-22 Founder clarification; no DB-001 action is currently required. No database changes, reset or restore were performed by that investigation.


## DB-001 read-only SQLite inspection added — 2026-11-18

- Added `ops/windows/Test-ZazuDatabaseSchema.ps1` and `docs/DB001_SQLITE_SCHEMA_CHECK.md` on `main`.
- The Windows diagnostic reads Laravel migration status, checks the event schedule migration ledger entry and all five expected `events` columns, checks the historical `business_user.primary_niche` column, and runs SQLite `PRAGMA integrity_check`.
- It emits only bounded schema/status metadata to a desktop JSON report. It does not run migrations, create backups, restore archives, or alter database records/schema.
- Added a Windows Ops workflow contract check for the diagnostic's required checks and non-mutating boundary.
- This enables evidence-based selection of a repair once the owner's actual installed SQLite database is inspected. The source repository cannot inspect the owner's local database or recover the omitted SQL exception from the request ID alone.
- DB-001 reference `895e2c23-bc6b-4919-a891-1e8e5c1e27e4` is retained for historical traceability only. The read-only report is not currently required; see the 2026-11-22 Founder disposition at the top of this file.


## Offline cache-priming input hardening — 2026-11-18

- Reviewed the service-worker page-priming message path under issue #35.
- Found that the route filter accepted any string beginning with /; URL parsing treats protocol-relative and backslash-normalized paths as cross-origin URLs. The priming fetch uses credentials: include, so this was an unnecessary credentialed cross-origin request surface.
- Updated public/sw.js to parse and normalize each route before fetching; reject cross-origin URLs, embedded URL credentials, /api and /api/*, non-string values, and oversized route strings.
- Added tests/js/service-worker-auth-cache.test.js regression coverage proving only a same-origin non-API route is fetched.
- Commits: service-worker correction 51e71414a293b4584d1b304dcbf0d0d2d1b15b97; regression test 6517703a4d2b2263547b603d1b8dc1a9c0c2e0e1.
- Exact-head CI is queued/running. No test pass is claimed until observed. This is a narrow defense-in-depth correction; it does not close physical Android acceptance or establish full offline workflow parity.


## Sentinel control-centre mandate — 2026-11-18

- Owner directive: prioritise Sentinel as the next major engineering focus. The target is a local, self-service operations and diagnostic control centre that explains failures and collects the evidence a developer would otherwise request manually. It must remain useful without internet access.
- Audited the actual implementation at `main` HEAD `68f8729e728fd43e238a5d4ad6376ab07dbb561f`: independent privacy-filtered incident ledger, fingerprint grouping/timeline, platform-admin UI, redacted reference-scoped DB-001 download, Windows incident capture and read-only SQLite schema tooling already exist.
- Important capability gap: `/admin/sentinel` is protected by `auth`, `auth.session` and `platform.admin`; it is not currently the business-owner control surface. The installation-wide ledger has no tested per-business incident ownership boundary. Do not remove platform-admin access controls or expose the global ledger to business owners as a shortcut.
- Important resilience gap: `config/session.php` defaults `SESSION_DRIVER` to `database`. The diagnostic interface must not rely invisibly on the business database/session path it is expected to diagnose. Its fallback access path needs an explicit security design and packaged-installation verification.
- Created `docs/SENTINEL_CONTROL_CENTRE.md` defining the product contract, five control surfaces, access/isolation rules, resilience requirements, delivery sequence and acceptance criteria. Updated `docs/ZAZU_ERROR_INCIDENT_FRAMEWORK.md` to remove its obsolete statement that no dashboard exists.
- This is a scope/architecture definition only. No runtime capability was added and no CI or production acceptance was claimed. The DB-001 OPEN label in this historical note is superseded by the 2026-11-22 Founder clarification; V1's separate release gates remain independently assessed.


## Sentinel control-centre implementation — 2026-11-18

- Current source head at verification: `f608effe6b890f618e2cdd5b93bb7d26e58f502a` on `main`.
- Added owner-discoverable **System → Sentinel Rescue** with a one-year temporary signed URL. The route is outside the regular authenticated web/session middleware; it was tested while `session.driver=database` and the configured SQLite database path was intentionally unavailable. The request still rendered the rescue page.
- Sentinel Rescue now performs read-only database connectivity and migration-history checks; private storage permission checks; offline support-file presence/readability checks; and independent Sentinel ledger folder/segment/write-read-delete checks.
- A failed database connection or migration read is captured through the independent ledger. The page displays a safe error code/failure category/reference and a link to the allowlisted DB-001 report.
- Raw PDO failures now inherit only the validated configured connection alias and driver, allowing SQLite failures during connection/bootstrap to be classified without storing private exception messages.
- Sentinel admin timeline DB-001 references now include a direct download action. The owner rescue access is installation-level and signed-link protected; it does not expose the platform-admin timeline or the raw global ledger.
- Exact-head run `44318909570`: Laravel/Pest/PHPUnit **563 passed, 1 skipped, 3,915 assertions**; PostgreSQL migration smoke **PASS**. The offline-browser acceptance job was still running at the time of this note.
- Exact-head run `44318909679`: PHPMD **PASS**. Exact-head run `44318909551`: Zazu quality application, Composer security audit and secret scan **PASS**.
- Windows V1 package run `44318697559` on app-source-equivalent commit `e5bb8c602b7b66403dbda49ca023cb7000856170`: package build and Windows PowerShell fresh-install verification **PASS**. No physical owner installation acceptance is inferred from CI.
- Remaining Sentinel gaps: package export/manifest, full failure knowledge base, attachment health, offline queue/sync checks, session/cache diagnostics, backup integrity and recovery checks, richer fault injection, and on-device/offline packaged acceptance.
- The DB-001 reference `895e2c23-bc6b-4919-a891-1e8e5c1e27e4` and diagnostic tooling are retained for traceability. The then-OPEN status is superseded by the 2026-11-22 Founder clarification; do not request a report or repeat the diagnostic unless the issue recurs. No database migration or owner-device mutation was performed by this repository investigation.


## Sentinel Rescue owner-dashboard refinement — 2026-11-18

- Owner reported that the standalone Rescue screen consumed too much vertical space and offered no route back into the Zazu workspace.
- Kept the standalone read-only diagnostic surface so it remains useful when the normal application shell/database path is unhealthy; added a persistent compact workspace link to the owner dashboard.
- Reworked the large introduction and instructional panels into a compact status summary and responsive health-check grid. Detailed migration names remain collapsed until requested.
- Founder environment setting confirmed by owner as `Rosscorelabs@outlook.com`. `config/zazu.php` normalizes the configured `ZAZU_PLATFORM_ADMIN_EMAIL` to lowercase. Do not hard-code the founder identity into shared defaults; each installation must set it in its private `.env`.
- Source/test update is not visually accepted until rendered on the owner's installation. Existing Sentinel security boundaries remain unchanged.


## Sentinel Rescue interaction and visual refinement — 2026-11-18

- Owner requested stronger borders and meaningful interaction rather than a static report.
- Diagnostic cards and dashboard surfaces now use higher-contrast borders and clearer hover/focus states.
- Added client-side status filters (all / failed / attention / healthy), live text search, visible-result count, expand/collapse for diagnostic details, and a recheck control that reloads the read-only health inspection.
- Controls are progressively enhanced: the filter toolbar remains hidden until its script initialises; without JavaScript the complete diagnostic report remains visible.
- No new server write endpoints, external requests, business-record access, or telemetry were introduced. Recheck reruns the existing GET health inspection.
- Automated verification is pending for the current main head; visual acceptance remains with the owner on the installed application.


## Sentinel Rescue actionable diagnostics — 2026-11-18

- Failed and attention checks now show a contextual **Recommended next step** panel based on the stable diagnostic key.
- Guidance covers database connectivity, migration state, private storage, installed-app assets, the private diagnostics directory, incident-ledger integrity and the temporary write/read probe.
- Existing DB-001 safe incident report remains the only linked diagnostic action. No automatic repair, migration, file deletion or business-data mutation was added.
- Updated the page copy to distinguish read-only database checks from the existing temporary diagnostics-file write/read probe, which attempts cleanup after each check.
- Added a focused Blade-render regression test for safe incident-ledger guidance and updated Sentinel documentation.
- Verification is pending for the new commits. Visual acceptance remains pending on the owner's installation. All commits are directly on `main`; no branches created.


## Sentinel local recovery complexity cleanup — 2026-11-18

- Refactored `EnsureLocalSentinelRecovery` into focused loopback and same-origin POST guards; retained exact local address/host restrictions, content-type, origin scheme/host/port checks and Fetch Metadata behavior.
- Refactored `ZazuLocalSentinelRecovery` into code-format, locked-read, challenge-validation and invalidation helpers. The 256-bit code, SHA-256 hash storage, five-minute expiry, lock-protected single-use behavior and invalidation fallback are preserved.
- Added feature coverage for expired and malformed challenges alongside existing cross-origin, different-port and one-time-use tests.
- PHPMD had reported complexity violations in the two recovery methods. Refactor verified by PHPMD run `44372419579` on commit `a3dc514`; scan completed successfully with no suppression or threshold change.
- Removed a duplicate incident-report link from the owner guidance panel; the existing reference-scoped DB-001 report action remains in the diagnostic facts row.
- Changes committed directly to `main`; no branches created. Full Laravel and physical-device acceptance remain pending.


## Sentinel CI regression closure — 2026-11-18

- Corrected the founder layout so workspace footer destinations are rendered only when an active business workspace exists; founders with a business retain normal workspace navigation. This resolves the founder-console navigation assertion without exposing the console to business users.
- Local recovery feature requests now explicitly model the local host request context (loopback address, HTTP scheme, host and configured port) and submit the required form content type. The recovery middleware remains unchanged and continues to enforce loopback-only access plus exact same-origin scheme/host/port and Fetch Metadata checks.
- Laravel run 44374252678 on commit a9ab1f2: **573 passed, 1 skipped, 3,956 assertions**. PostgreSQL migration smoke and offline browser acceptance were still running when this note was prepared; the preceding run 44373939367 had PostgreSQL and offline browser acceptance **PASS**.
- PHPMD run 44374538225 and Zazu quality run 44374538448 on 3d809a6: **PASS**. Laravel run 44374538236 on 3d809a6: **573 passed, 1 skipped, 3,956 assertions**. PostgreSQL migration smoke and offline browser acceptance for that exact commit were still running at this note; earlier commit 9d5ae4f had both checks **PASS**.
- Windows V1 package build and fresh-install PowerShell verification passed on commit 9d5ae4f. Director Contract Lint passed on 21a2f4e.
- No security checks were relaxed, no branches were created, and no physical-device acceptance is inferred from CI. V1 remains **NOT READY** pending physical installation/offline acceptance and remaining release gates.

## Sentinel request and incident-capture hardening — 2026-11-18

- Tightened the local recovery POST content-type guard to compare the parsed media type exactly with `application/x-www-form-urlencoded`; values that merely share the prefix are rejected. Added a regression test for the malformed suffix case. Existing loopback, origin scheme/host/port, Fetch Metadata and one-time challenge controls are unchanged.
- Hardened `ZazuIncidentRecorder`: if both the primary Laravel log channel and fallback Laravel error log fail, the recorder emits only the failure class to PHP's local `error_log` and does not allow the secondary logging failure to escape the exception-reporting path.
- Added `IncidentRecorderResilienceTest` proving the independent redacted incident ledger still receives the record during a simulated primary/fallback log-channel outage and that private exception details are not written to the ledger.
- Exact-head commit `529bd6fe47e9d7624a47c3237b87e03f508ae872`: Laravel **575 passed, 1 skipped, 3,962 assertions**; PostgreSQL migration/recovery smoke **PASS**; PHPMD **PASS**; Zazu quality application, Composer security audit and secret scan **PASS**. Offline browser acceptance was still running at the time of this note.
- Earlier CI failures on intermediate commits were traced to a malformed namespace separator in the newly added test and a PHPMD public-method count after adding the test to an already-large test class. The test was moved into its own feature-test class, syntax corrected, and PHPMD plus Laravel now pass on 529bd6f. No production safeguard was weakened.
- All changes are committed directly to `main`; no branches created. Physical Windows/Android acceptance remains separate from CI, and V1 remains **NOT READY** pending the outstanding operator gates.

## Incident ledger filesystem hardening — 2026-11-18

- `ZazuIncidentLedger` now reapplies private filesystem modes when writing: diagnostics directory `0700`, lock file `0600`, JSONL ledger `0600` (best effort on Windows).
- This tightens pre-existing paths from older installations as well as newly created paths. No incident fields or retention policy changed.
- Windows security remains dependent on effective NTFS ACLs; PHP `chmod` is not treated as proof. Installed-package ACL verification remains an acceptance requirement.
- Change committed directly to `main` as `396960a`; CI for this exact head is pending. Prior application commit `529bd6f` had Laravel 575 passed / 1 skipped / 3,962 assertions, PostgreSQL smoke PASS, PHPMD PASS and quality scans PASS; offline-browser acceptance was still running at last check.
- No branches created. V1 remains NOT READY pending exact-head CI and physical-device/package acceptance.

- Added a POSIX feature test that verifies the diagnostics directory is `0700` and the incident lock/ledger files are `0600`; the test explicitly skips on Windows because NTFS ACLs, not POSIX mode bits, determine effective access there. Commit `d1923af`; exact-head CI pending.

- PHPMD caught a malformed fully-qualified exception name in the new permission regression test (`\\RuntimeException`); corrected to `\RuntimeException` in `61dbd19`. This was test syntax only, not a production-code finding. Fresh PHPMD/Laravel runs are required before considering the permission test verified.


## Windows private-storage ACL hardening — 2026-11-18

- Windows installer `ops/windows/Install-Zazu.ps1` now removes inherited parent permissions from `storage/app/private` and normalises existing descendants to allow Full Control only for the installing Windows account, Local System and local Administrators. It refuses reparse points in this private tree.
- The Windows package workflow now boots the installed app, writes a real redacted incident-ledger probe, and verifies the private root, diagnostics directory, lock and JSONL file contain only the allowed SIDs; child entries must inherit the restricted ACL.
- Windows Ops Scripts validation on `409056b`: **PASS**. Windows package build and fresh-install smoke on `409056b`: **PASS** (before the new explicit ACL assertions). The exact workflow with ACL assertions on `d39396f` is still running; a subsequent package workflow on `ceb6c8c` is also running.
- Application hardening verification on `61dbd19`: Laravel, PostgreSQL migration smoke, offline-browser acceptance, PHPMD and Zazu quality/security scans **PASS**. The POSIX permission regression test is included in that Laravel run.
- Documentation updated in `docs/V1_PACKAGE_INSTALLATION.md` and `docs/SENTINEL_CONTROL_CENTRE.md`. Current `main` documentation head is `707dcb0`; its quality and Director Contract Lint checks are running.
- Do not claim NTFS ACL acceptance until the exact `d39396f` or later Windows package workflow completes successfully. Physical-device acceptance remains separate. No branches created; V1 remains NOT READY.

- Exact Windows package workflow `44380197892` on commit `ceb6c8c`: package build **PASS**; Windows PowerShell fresh install **PASS**, including the new real-ledger NTFS ACL assertions for the private root, diagnostics directory, lock and JSONL file. The ACL test confirmed only the installer account, Local System and local Administrators are allowed, with diagnostic children inheriting the protected ACL.
- Latest main state/documentation commit `e278d9c`: Zazu quality, Director Contract Lint and Director Health Report **PASS**. No code changes after the Windows ACL-verified package commit; only documentation/state updates.


## Expanded Windows local-data ACL boundary — 2026-11-18

- Expanded installer ACL enforcement beyond private attachments to `.env`, `database`, `storage/framework`, `storage/logs` and `bootstrap/cache`. This protects the SQLite business database, application secrets, sessions/cache, logs and cached configuration from broad inherited access in shared Windows folders.
- The generic ACL routine normalises existing descendants, gives Full Control only to the installing account, Local System and local Administrators, and refuses reparse points in protected paths. New database and runtime files inherit the protected parent ACL.
- Windows Ops Scripts validation passed for the installer change on `eded442`; its Windows package build and fresh-install smoke also passed. The expanded workflow assertions now inspect all protected roots plus inherited database/diagnostic child ACLs; exact runs on `ffae4d3` and `cc90359` are still in progress.
- Documentation updated in V1 package and Sentinel runbooks. Do not mark expanded ACL assertions accepted until a Windows package workflow containing the latest assertion set completes successfully.

- Expanded Windows ACL test initially failed because the backup/restore acceptance drill had already created `database/database.sqlite` before installation. The installer correctly normalised that pre-existing file to its own protected explicit ACL; the test incorrectly required it to inherit. The assertion now verifies the database file has an explicit protected ACL with only the allowed SIDs, while newly created diagnostics must inherit. Workflow correction committed as `c628604`; fresh Windows acceptance is queued. This was a test expectation correction; no ACL restriction was loosened.

## Existing-server migration bypass guard — 2026-11-18

- Inspection of `ops/windows/Start-ZazuDev.ps1` found that its already-running-server fast path returned before calling the backup-first database preparation routine.
- If repository files were updated while the PHP server remained running, launching the wrapper again could report “already running” without detecting newly pending migrations. This is a concrete startup-control gap, not proof of the owner-reported DB-001 cause.
- The fast path now performs read-only `migrate:status` first. If migrations are pending or the migration ledger is missing, it refuses to claim the launch is current and tells the operator to stop then restart Zazu. It never migrates while the existing server is active.
- A Windows Ops workflow contract check now verifies this guard and its non-mutating boundary.
- The normal stopped-server path remains unchanged: verified full database/private-files backup, migration, final migration-status verification, then server start.
- **Evidence pending:** Windows Ops workflow must pass on exact `main` HEAD. The owner installation has not been modified or tested by this repository change. The one pending invoice-numbering migration is not evidence that the earlier account/workspace DB-001 has recurred; the historical `business_user.primary_niche` column is present in the supplied Sentinel schema report.


## Director continuation and DB-001 isolation — 2026-11-18

- Founder explicitly confirmed: do not freeze Jarvis. Engineering, Sentinel and hardening remain active; focused work batches are a delivery discipline, not a development freeze.
- Repository review confirms the pending `2026_11_18_000001_create_invoice_number_sequences_table` migration is used by invoice issuance only: online `FinanceController::storeInvoice` and offline invoice sync through `OfflineDomainMutationHandler`. It is not called by business/account settings or dashboard account loading. It is therefore not a supported explanation for the historical test-time DB-001 anomaly.
- An earlier diagnostic review recorded `GET /dashboard` querying `business_user.primary_niche` as a historical fingerprint and noted that the supplied Sentinel schema report showed the column present. This was not established as the cause of the test-time event-creation/calendar anomaly and is not a current defect finding. The separate invoice-sequence migration remains pending and still requires backup-first installation handling before invoice issuance is considered ready.
- Windows Ops workflow initially exposed a PowerShell parse failure in the new existing-server migration guard. The failure was diagnosed and corrected on main. Exact current main HEAD `a7798c41224876aa4a6e2d98963e8cd793680909`: Windows Ops Scripts, Windows V1 Package, Zazu quality and Push on main all completed SUCCESS. This confirms CI only, not the Founder's installed PC.
- At this historical checkpoint DB-001 was still listed as OPEN. That status is superseded by the 2026-11-22 Founder clarification at the top of this file; no new owner-side incident evidence was established by this repository investigation.


## Sentinel overview accuracy correction — 2026-11-18

- Source review found that the control-centre overview derived fingerprint count, occurrence total and latest-incident timestamp from the display list capped at 100 fingerprints. This made the headline figures incomplete once the retained ledger contained more than 100 distinct failure patterns; the "latest incident" could also be stale because the list is ranked by recurrence.
- Added a separate full-retained-ledger overview aggregation. The visible recurring-pattern table remains intentionally capped at 100 rows and ranked by occurrence count; headline totals and latest timestamp now use all valid records in both retained ledger segments.
- Added a feature regression test with 101 fingerprints / 201 occurrences, where the newest low-frequency fingerprint falls outside the visible top 100.
- Changes committed directly to `main`: summary `0ad2178`, controller `58bcda2`, regression `fcd7345`.
- Updated the Director contract: Sentinel/diagnostics are active engineering targets, not frozen. Founder-only platform incident access remains unchanged.
- **Verification pending:** run the application/quality workflows on the resulting exact main head. No owner installation or incident data was changed.


- Follow-on inspection found the same top-100 truncation also affected search: low-frequency fingerprints outside the displayed top 100 were impossible to find. Search now filters the complete retained fingerprint set before applying its 100-row result cap. The regression test also searches for a deliberately low-frequency fingerprint and verifies unrelated top-ranked entries are absent.
- Follow-on commits: summary search `1ac1cff`, controller `7c3a043`, search regression `fa9308b`. CI for the final combined head remains pending.

## Sentinel diagnostic-link access hardening — 2026-11-18

- Source review found the DB-001 diagnostic endpoint was rate-limited and returned only allowlisted fields, but its reference UUID URL had no signature or expiry. A copied URL could therefore be reused indefinitely.
- The endpoint now requires Laravel's signed-URL middleware in addition to the existing rate limit. Sentinel Control Centre, Sentinel Rescue and the DB-001 error page generate temporary signed report links that expire after 15 minutes.
- The report remains reference-scoped and redacted; this does not grant access to the founder console or the full incident ledger.
- Added feature coverage for unsigned, expired and path-tampered links, and assertions that all three user-facing link surfaces include signature and expiry parameters.
- Verification is pending on the exact resulting `main` head. This is source-level hardening only; no installed device, database or incident records were changed.


## Sentinel founder licensing workspace — 2026-11-19

- Added a Licensing & Entitlements section to the existing founder-only Sentinel console on `main`. It searches businesses by name/ID, displays installed licence status and dates, and provides issue/renew/revoke actions.
- Added `SentinelLicenseController` routes under the existing `auth`, `auth.session` and `sentinel.admin` boundary; issue/revoke POST actions are rate-limited. Business-owner routes and customer activation remain unchanged.
- The UI invokes the existing Ed25519 issuer server-side. The signing key path is configured through `ZAZU_LICENSE_PRIVATE_KEY_FILE` and must point outside the project; the existing issuer still validates key format, project exclusion and match against `ZAZU_LICENSE_PUBLIC_KEY`. No key was generated or deployed.
- The signed token is returned once in the founder's authenticated session for manual delivery. Application logs record metadata only; token and key bytes are not logged. Revocation requires the owner to import the signed revocation token while offline; it is not a remote kill switch.
- Added focused feature tests for founder issuance, non-founder denial and missing-key fail-closed behavior. Exact-head CI has not yet been observed. Do not mark this workflow accepted until Laravel tests, quality/security checks and relevant Windows package checks pass.
- No feature matrix or paid-feature enforcement was invented. No branches created; all changes are directly on `main`.


## Sentinel founder licensing — UUID-backed offline customer registry — 2026-11-19

- Reworked the Sentinel licensing workspace to fit Zazu's documented first-customer model: independent local installations, no mandatory cloud licensing server. The founder maintains a local registry of customer businesses using the stable licensing UUID supplied by each owner.
- Added `businesses.licensing_uuid`: migration backfills existing businesses and new Business records generate a UUID. Business Settings now displays this as the Business Licensing Reference; the numeric Business ID is explicitly local/support-only.
- Signed licence payloads are now version 2 and bind to `business_uuid`. The local verifier compares that UUID with the UUID in the customer's Business record. Version-1 tokens bound only to local numeric IDs are rejected because separate customer installations can reuse those IDs.
- Added `platform_license_customers` founder registry for business UUID, business name, optional contact email and latest licence metadata. It does not store signed token contents or private-key material.
- Sentinel now supports founder-only customer registration/search, signed issue/renewal and revocation. All actions remain inside `auth`, `auth.session`, `sentinel.admin`; issue/revoke/register actions are rate-limited. Customers still import tokens through their existing owner-only Business Settings screen.
- Issuance uses the external `ZAZU_LICENSE_PRIVATE_KEY_FILE` path and matching `ZAZU_LICENSE_PUBLIC_KEY`. The private key never enters the browser, customer installation, database or logs. No production key was generated or used.
- Split issuer payload validation and token signing into focused services to keep the command maintainable. Added feature coverage for registry registration, UUID-bound issuance, revocation, founder authorization and missing-key fail-closed behaviour; updated local import and CLI tests to version 2.
- Exact-head Laravel, PHPMD, Psalm, Quality, populated migration/rollback and Windows package evidence is still pending. Do not claim the founder workflow is accepted until those exact-head runs pass and the Windows local installation has the new migration. No production licence has been issued.
- Business-level identity is implemented; per-installation seat limits/reactivation, production key custody/rotation, approved tier matrix and module-level feature enforcement remain open. Do not infer any device-count restrictions.
- All work committed directly to `main`; no branches created.


## Navigation discoverability audit — 2026-11-19

- Founder review identified the owner-only Data Export & Recovery page as effectively hidden: it was reachable from Owner Admin and Reports, but absent from the persistent sidebar and command palette. Its route was already protected by the owner middleware; this was a discoverability defect, not an authorization defect.
- Added Data Export & Recovery to the owner System navigation, command palette and footer Control navigation. Export routes now activate the System navigation group and show a contextual section tab.
- The same audit found Compliance and Activity Audit were available through settings sub-navigation, command palette and footer, but not the persistent owner System navigation. Added direct owner-only System links for both.
- Added a focused regression test for owner discovery and active navigation on the export page. Existing route middleware and export authorization were not changed.
- Customer spreadsheet import is already discoverable from the Customers page action; event costs, preparation, requirements, travel and attachments are contextual job-level tools, not standalone workspace destinations. No duplicate top-level links added for those contextual workflows.
- Changes committed directly to main; automated test execution and rendered desktop/mobile verification remain pending. Do not claim UI acceptance until verified.

## LAN HTTPS launcher output visibility — 2026-11-19

- The double-click launcher now pauses after a successful or failed startup so the owner can read/copy the generated HTTPS URL and Caddy root-certificate path. Previously the successful command window could close immediately and hide both values.
- This is a launcher usability adjustment only. It does not change the PHP/Caddy startup sequence, certificates, firewall, database, browser storage or phone data.
- Committed directly to `main`; no branches created. Physical startup and phone certificate trust remain unverified.

## Playwright contamination guard and navigation acceptance — 2026-11-20

- Registration E2E now skips unless both `ZAZU_E2E_ALLOW_REGISTRATION=1` and `ZAZU_E2E_ISOLATED_DATABASE=1` are explicitly supplied. These flags are acknowledgements only; they do not provision or verify an isolated database.
- Added `docs/PLAYWRIGHT_DATABASE_SAFETY.md` documenting the real risk created by local `reuseExistingServer` behavior and the disposable-database requirement.
- Founder navigation feature coverage now asserts that Founder Control Centre, Owner Admin and Data export & recovery are inside the System navigation panel.
- Service-worker cache version is v40; service-worker cache tests were updated to expect v40 (the prior v39 assertions would otherwise fail after the version bump).
- No local PHP/Node runtime or live browser was available during this repository-only work. These source changes have not been runtime-tested; GitHub CI status must be checked after push.
- Existing synthetic business cleanup remains a separate local database operation. Do not broaden its exact `Browser Catering + 10 hex characters` matcher.


## Sync writer lock normalization — 2026-11-20

- Began the prerequisite lock-boundary correction for CASE-ZAZU-0033 on `main`.
- Customer updates now lock the business row before the customer aggregate and primary contact; customer-contact create/update/delete use the same business → customer → contact order.
- Business capability updates now run transactionally and lock business → capability before applying changes.
- These are prerequisite serialization improvements only. They do **not** close stale-snapshot protection: remaining online writers, revision advancement, expected-revision validation, conflict preservation and client causal chaining remain open.
- Commits: `2be54cb`, `0e1c878`, `d8df68d`. Exact-head automated verification is pending; do not mark these changes tested until their workflows complete.


## CASE-ZAZU-0033 — shared writer lock foundation — 2026-11-20

- Committed a first normalization slice across customer/contact, capability, supplier, asset, quote draft and Work customer-snapshot writes.
- All changed code is on `main`; no branches created.
- The intent is to establish the common business-first serialization boundary before revision tokens are introduced. This is prerequisite engineering, not stale-write protection.
- Automated evidence on `acba664`: PHPMD, Zazu Quality, Psalm Security Scan, populated upgrade/rollback and Director Health passed. Laravel and Windows V1 package were still running at the time of this state update. SonarCloud remains skipped by workflow configuration.
- Next: finish exact-head CI disposition, then implement aggregate revision propagation across every online/offline writer and causal queue/conflict handling. CASE-ZAZU-0033 remains OPEN; V1 readiness unchanged.


## Verified online writer lock slice — 2026-11-20

- The customer/contact/capability correction is committed as `47aaa1a`; source was re-fetched after the earlier transformation missed these three controller files. Customer updates now lock business → customer → primary contact; contact create/update/delete lock business → customer → contact; capability updates lock business → capability in one transaction.
- The wider lock slice is on `main`, including supplier, asset update/allocation/release, quote draft, Work create/update and the lifecycle/finance/inventory/purchasing paths already using business-first locks.
- Exact-head evidence on `47aaa1a`: Laravel unit/feature tests passed; PostgreSQL migration, registration and backup/restore smoke passed; Windows V1 package passed; PHPMD, Zazu Quality, Psalm Security Scan, populated upgrade/rollback and Director Health passed. Offline browser acceptance was still running.
- This closes only the prerequisite lock-normalization stage. CASE-ZAZU-0033 remains OPEN until monotonic revision advancement, expected-revision rejection, remote snapshot preservation, same-device causal chaining and safe rebase are implemented and tested.


## CASE-ZAZU-0033 follow-through — conflict rebase chain and complexity correction — 2026-11-20

- Added causal continuity after an explicit use-server discard: the discarded mutation records the latest server revision, and the next same-device mutation can use that result revision rather than being stranded behind a predecessor with no result.
- Expanded regression coverage to prove both paths: a keep-local replacement mutation applies under its new immutable ID and a later edit can still follow the original mutation's supersession link; a use-server discard records the current revision and a later queued edit applies against it.
- Split conflict rebase operations and conflict presentation/comparison out of the resolver to address the PHPMD complexity failure. Exact-head CI is pending; no test result is being inferred from the refactor.
- Current implementation/test head: `7343a8f`. CASE-ZAZU-0033 remains OPEN.


## Exact-head regression follow-through — 2026-11-20

- Fixed the legacy-identity default surfaced by the first Laravel run: SyncEntityIdentity now defaults to revision 2 for direct/unregistered creation, while the identity registry explicitly sets revision 1 for genuinely new registered records. The migration retains database default 2.
- Added online revision coverage for Work, supplier, service/capability and asset roots, plus explicit conflict field comparison and both keep-local/use-server causal successor paths.
- On `7343a8f`: Laravel unit/feature PASS (615 passed, 1 skipped, 4,198 assertions); PHPMD PASS; Zazu Quality PASS; Director Health PASS. PostgreSQL migration smoke and offline browser acceptance remain in progress.


## Bootstrap serialization and conflict UI correction — 2026-11-20

- `SyncBootstrapPayloadBuilder` now opens one transaction and locks the business row before reading workspace data or registering/reading entity revisions. This prevents a concurrent online writer from producing a bootstrap payload containing stale record fields paired with a newer revision (or the reverse). The lock order matches the business-first online writer and sync mutation protocol.
- Added a regression test proving the bootstrap's first business read occurs inside the builder's own transaction.
- Exact-head evidence: on `a8201a7`, Laravel unit/feature and PostgreSQL migration smoke passed; PHPMD, Zazu Quality and Director Health passed on `7343a8f`; Windows install candidate and populated upgrade/rollback passed on `98efc29`.
- The 51-test offline browser run on `7343a8f` exposed a real settings-page JavaScript defect: `offlineConflictsRefreshed` was read before declaration, causing the catch handler to hide both conflict and re-pair UI. Commit `1d746d9` initializes that state explicitly. The rerun is pending; do not mark browser acceptance green yet.
- CASE-ZAZU-0033 remains OPEN pending the exact-head offline browser result and full supported-aggregate writer coverage closure.


- Offline client behavior changed in `public/zazu-offline.js`; service-worker cache advanced to `zazu-static-v51` and matching cache fixtures updated so installed clients fetch the revision-aware queue code after worker activation.


## CASE-ZAZU-0033 — related aggregate acknowledgement and v1 gate — 2026-11-20

**Implementation head:** `7d5f861` on `main`. This section supersedes earlier notes that the cache was v50 or that the current source was still at the October baseline.

- The server now returns `related_identities` alongside the backwards-compatible singular `related_identity`. Quote acceptance returns both the Quote and linked Work identities; a purchase receipt returns the Purchase Order and each inventory item whose movement history was changed. Existing payment, inventory movement and asset allocation parent acknowledgements remain represented.
- The browser reconciler now processes related identities even when the command itself has no standalone identity. This is required for quote acceptance, payment and purchase receipt commands. Optimistic offline quote acceptance also updates the linked Work status when it is still draft, matching the server transition.
- Service-worker static cache is `zazu-static-v51`; test fixtures were advanced with it. A browser regression covers identity-less quote-acceptance and purchase-receipt acknowledgements across several related roots.
- Command-side parent revision advancement is explicit for quote acceptance → Work and purchase receipt → affected inventory items, in addition to the existing quote, invoice, Purchase Order, inventory and asset parents.
- Online write trace reviewed: customer/contact, import, Work/lifecycle, quote, capability, supplier, asset, event cost/preparation, invoice/payment/expense, inventory and purchasing controllers/services. The inspected updates to existing tracked roots use the business-first lock boundary and Eloquent revision events. The inspected raw/bulk operations either also update the aggregate root in the same transaction (e.g. quote status replacement) or restore into a newly-created empty business without existing sync identities. This is a broad source trace, not yet proof that no future or indirect writer can bypass the contract; keep the final writer audit gate open until the repository-wide write inventory is closed.
- Exact-head evidence at `7d5f861`: Laravel unit/feature PASS, PostgreSQL migration smoke PASS, PHPMD PASS, Zazu Quality PASS, Director Health PASS. Offline browser acceptance, including the new multi-aggregate acknowledgement case, was still running when this state was written. SonarCloud is skipped by workflow configuration.
- CASE-ZAZU-0033 remains OPEN until the exact-head browser result and remaining supported-aggregate writer audit pass. V1 readiness and the documented 73/100 evidence-maturity score are unchanged. Physical Android/PC acceptance, operator recovery drill, populated commercial reconciliation and legal/operational sign-off remain separate gates. The historical DB-001 anomaly is dormant and non-critical.


## CASE-ZAZU-0033 — operator conflict-resolution acceptance added — 2026-11-20

- Current write target remains `main`; no branches created.
- Added `e2e/offline-conflict-resolution.spec.js` to exercise the Settings operator journey for two stale customer edits: compare local/server values, keep-local rebase to a new immutable mutation ID/current revision, and use-server restoration of the authoritative record.
- Added the new spec to the Laravel workflow's Chromium offline-browser acceptance command. This closes a coverage omission: the Settings UI already exposed both actions, but the existing browser command did not exercise them.
- Test fixture uses a disconnected local queue before reconnection, seeds failed mutations/conflicts in the business-scoped IndexedDB workspace, then mocks only the conflict API boundary. It asserts the old mutation is replaced on keep-local and the server record is restored on use-server.
- Source implementation was not changed; this is acceptance coverage for the existing conflict protocol.
- Commits: `c5ef89d`, `0a2ada0`, `a57108d`, `3a82905`, `e40878d`, `e43392e`.
- Exact-head Laravel/browser and quality verification on `e43392e` is pending. Do not claim this test passes until its workflow reports success. CASE-ZAZU-0033 remains OPEN; V1 remains NOT READY.


### Host-outage offline workspace visibility regression added

- Added a second browser acceptance case in `e2e/offline-work-creation.spec.js` for the precise LAN failure mode where `navigator.onLine` remains true but the Zazu host is unreachable.
- The test keeps the browser online, aborts the lightweight `/up` connectivity probe, and requires previously synced customer data to appear in the device-local workspace.
- This is distinct from airplane-mode coverage and directly targets the reported “last online page” symptom at the workspace-visibility boundary. It does not replace the physical Android cold-launch/restart test.
- Commit: `c9df48a`. Exact-head browser verification is pending.


### Conflict UI restart durability strengthened

- The new Settings conflict-journey test now reloads the browser after seeding failed mutations and conflict snapshots in the business-scoped IndexedDB database, then verifies the operator can still compare and resolve both records.
- Sync pull/bootstrap/push endpoints are bounded by deterministic test routes so the test isolates local persistence and the conflict-resolution API contract rather than depending on unrelated sync timing.
- Commit: `5b56a7c`. Exact-head Chromium acceptance remains pending.


## Offline conflict resolution feedback correction — 2026-11-20

- Exact CI evidence on `5b56a7c`: Laravel unit/feature tests and PostgreSQL migration/recovery passed; offline-browser acceptance had 56 passing tests and one failure in the new Settings stale-conflict journey.
- Failure root cause: resolving the final open conflict caused `renderOfflineConflicts` to hide the entire section immediately after the local state event, so the success status was present in the DOM but inaccessible/hidden. The underlying discard operation and IndexedDB assertions were not reached by the failing visibility assertion; do not claim their browser acceptance from this run.
- Correction on `main`: keep the conflict section visible while it contains a resolution/status message, even after the last open conflict is removed. The existing E2E journey already asserts the final server-version confirmation and local queue/record state.
- Exact-head browser acceptance is pending for this correction. Release remains NOT READY; this is a user-feedback defect fix, not evidence of full sync parity or Android physical acceptance.


## Online aggregate writer audit — customer-contact revision bypass corrected (2026-11-20)

- Source audit found one real Eloquent-event bypass: promoting a contact used a relationship mass update to clear other primary contacts. If the selected contact was already primary and its attributes were unchanged, the selected model emitted no update event, so the customer aggregate revision could remain unchanged while another contact row changed.
- Corrected the promotion path to lock and update only competing primary contacts through their Eloquent models. Their existing parent-revision listener now advances the customer snapshot revision. Added a feature regression for the unchanged-selected-contact / duplicate-primary case.
- This is one confirmed writer correction, not completion of the writer audit. Continue checking indirect, query-builder and command-side writers. Exact-head Laravel/browser acceptance for this change is pending.


## Job workspace next-action state mapping — 2026-11-22

- Replaced the generic “Open quote” default with state-derived guidance on the Job workspace.
- The next action now distinguishes missing service scope, no quote, draft quote, sent quote, declined/expired quote, accepted quote awaiting invoice, outstanding invoice balance, open/blocked preparation, active purchase orders and planned costs.
- Completed and cancelled jobs no longer display a misleading operational next-action button.
- The job detail now eager-loads invoice payments so invoice balance decisions use the already-loaded job data.
- Added focused feature coverage for sent-quote customer decision guidance and accepted-quote invoice hand-off.
- Commits: `f413fb7`, `5db08de`, `4431e04`, `31f54aa`.
- Runtime tests and exact-head CI have not yet been verified. Do not mark this change accepted until the Laravel workflow and quality/security checks report results.

- Follow-through: the accepted-quote action now opens invoice creation with the accepted quote preselected; an outstanding invoice opens payment entry with that invoice preselected. This uses the existing `quote_id` and `invoice_id` query contracts in FinanceController.
- CI caught one existing extra blank line in WorkController when that PHP file entered the changed-file formatting gate. Corrected it in `051374f`; exact-head Laravel, quality, security, Windows package and recovery workflows are queued/running.

## Job workspace progress state display — 2026-11-22

- Replaced the five static Job progress links with six state-derived stages: Job, Services, Quote & customer decision, Invoice & payment, Delivery readiness, and Close-out.
- Stage details now distinguish customer response waiting, quote acceptance, invoice creation/payment balance, open preparation and purchase orders, planned costs, and completed/cancelled jobs. These are orientation signals derived from existing persisted records; they do not introduce a new lifecycle or make optional operational modules mandatory.
- Preserved the existing module routes and the Job workspace as the orientation surface. The six-stage strip now has a matching six-column desktop layout and retains the existing responsive breakpoints.
- Split the two new Job workspace hand-off tests into a focused JobWorkspaceProgressTest class. This also resolves the current WorkWorkflowTest PHPMD public-method limit and the Pint class-attribute ordering finding.
- Commits: d992046 (test class correction), b8a819e (progress state), 9d58102 / 04dfb32 (focused tests), e949b7f (six-column layout).
- A final contextual fallback keeps the Invoice & payment stage inside the current Job (quote creation or quote detail) until an accepted quote exists; commit 8bdc66c. Exact-head Laravel, formatting, PHPMD, offline-browser and security checks are queued for the current main head; do not treat this implementation as accepted until those checks complete.
- Rendered visual and mobile review remain unverified; source-level changes do not establish visual acceptance.

## Quote quantity review — 2026-11-22

**Founder usability finding:** quote creation allowed starter requirements to carry a default quantity of 1 without a clear review step. The quote editor showed the quantity only as secondary metadata and its live total calculation defaulted to 1 when no explicit quantity value was present. This could produce a commercially misleading quote.

**Implemented on main:**
- Initial quote creation and draft revision editors now expose a prominent, required quantity input beside the unit price for every line.
- Each line displays its own live line total; the quote subtotal, tax, total and deposit recalculate from the entered quantities and prices.
- The editor copy explicitly asks the user to review quantities, units and prices before saving.
- Submitted quantities update the underlying job requirements and quote snapshots in one database transaction, preserving the requirement-to-quote freshness contract. Previous quote versions remain unchanged.
- Existing service callers that do not submit a quantity array retain the stored requirement quantities for compatibility.
- Added a feature regression covering quantity changes during initial quote creation and a later draft revision.

**Verification status:** implementation and regression test are committed; local PHPUnit, Blade compilation, JS execution and rendered desktop/mobile + light/dark visual review have not been run in this environment. Exact-head GitHub Actions results must be checked before acceptance. Do not mark this target VERIFIED until those gates are evidenced.

**Known UI acceptance points:** confirm the quantity field, unit hint, line total and live summary are clear at phone width; confirm validation errors remain visible and no horizontal overflow is introduced. This is a workflow correctness fix as well as a visual hierarchy change.


## Quote line-item clarity and quantity guard — 2026-11-22

**User finding:** a draft quote containing one product and one service was created with both quantities left at the starter value of one. The editor did not make the quantity decision sufficiently visible.

**Current implementation on main:**
- Starter requirements whose quantity is exactly 1.00 now render with a blank quantity input. The user must deliberately enter the quantity instead of unknowingly accepting the starter value.
- Initial quote creation and draft quote revision saves both require a quantity array server-side; HTML required is not the only protection.
- Quantity and unit are shown prominently beside unit price. Each line has a live line total; the live quote summary recalculates subtotal, tax, total and deposit.
- The editor labels each line by its catalogue type (Product, Service, Hire / rental, Package, Other, or Custom line) and names the section Products and services rather than implying every line is a service.
- The selected quantities are synchronized to the job requirements and quote snapshot inside the quote transaction. Previous quote versions remain immutable snapshots.
- Added focused regression coverage for quantity confirmation, quantity changes across revisions, missing quantity rejection on create and revision, and visible product/service type distinctions.
- Split quote tests into QuoteWorkflowTest, QuoteQuantityReviewTest and QuoteRevisionWorkflowTest to keep test classes within the PHPMD public-method limit.

**CI findings and corrections:**
- The first Laravel run exposed three test defects: an HTML assertion needed literal matching for an ampersand, a workflow test lacked its EventRequirement import, and an older revision fixture did not submit the now-required quantity. These have been corrected.
- PHPMD then caught a malformed test class reference and a public-method count over the configured limit. The reference was corrected and the tests were split.
- Pint caught trait/import ordering in QuoteWorkflowTest; corrected.
- PHPMD passed on commit 8c6a8f90364a81245c1bf7bcecc47e92265d61b1. Later product/service presentation and its feature test are on the newer main head and still require exact-head CI confirmation.

**Acceptance status:** NOT VERIFIED. Latest main implementation includes product/service labels and a focused presentation regression, but exact-head Laravel, quality, formatting, security, offline browser and package workflows must complete. Rendered desktop/mobile and light/dark review is still outstanding; no visual acceptance is claimed from source inspection alone.


## Offline cache warning — interrupted page priming — 2026-11-22

**Founder-reported defect:** the app repeatedly displayed “Offline workspace is not fully prepared” / could not cache pages during ordinary page navigation.

**Root cause found in source:** each page load starts the same multi-route priming operation. The service worker aborted an in-flight priming operation when a navigation occurred, and a subsequent page load started another operation that aborted the previous one again. The client treated that interrupted run as a genuine failure and displayed the warning. Repeated navigation could therefore prevent the five required routes from ever being confirmed, even while the app remained online.

**Implemented on main:**
- Same-user/business page-prime requests now share the in-flight operation instead of cancelling and restarting it.
- Normal page navigation no longer aborts background cache preparation.
- Existing cached HTML is reused while its referenced static assets and private media are still checked/primed.
- Static service-worker cache version advanced from v60 to v61 so installed clients receive the corrected worker.
- Extended `PwaOfflineFoundationTest` source regression assertions for in-flight task reuse, cache reuse and continued asset priming.
- Extended the existing offline-browser acceptance test to issue three concurrent same-workspace priming requests, require all three to complete successfully, and assert that the warning is absent before disconnecting.

**Commits:** `0e2cc49` (worker), `7c8b93e` (initial regression assertions), `e1d6f8a` (preserve asset priming), `b26564c` (final source assertions), and `f1ff0b1` (concurrent offline-browser regression).

**Acceptance:** implementation is committed to `main`; exact-head Laravel, offline-browser, quality and PHPMD runs are pending for the latest regression commit. Do not mark the founder-reported device symptom resolved until the new worker is deployed/served and the phone/PC navigation experience is confirmed. If the warning persists after v61 is active, inspect which required route is failing rather than suppressing the notice.


## Offline page-cache warning — priming lifecycle correction — 2026-11-22

**Founder-reported symptom:** ordinary page navigation repeatedly displayed the offline-cache preparation warning.

**Confirmed source defect:** page boot starts the same authenticated multi-route cache prime. Navigation aborted the active worker task, and the next page boot restarted it. The client then interpreted the interrupted run as a genuine cache failure. A second lifecycle edge case allowed logout/session clearing to leave a stale in-flight task reference that a later same-workspace prime could join.

**Implemented on main:**
- Same-user/business prime requests now join the active task instead of aborting/restarting it.
- Normal page navigation no longer aborts background priming.
- Successfully fetched route HTML is refreshed in the private workspace cache, and its static/private assets continue to be primed.
- Clearing the authenticated workspace aborts and releases the in-flight task reference so a later login can start a fresh prime.
- Service-worker static cache advanced to `zazu-static-v61`.
- Regression coverage now exercises concurrent same-workspace prime requests across navigation and a fresh prime after logout invalidates an earlier task.

**Verification on source/test head `a0baea5901b139b1c7397a12d780af245841d3ce`:** Laravel **691 passed / 1 skipped / 4,794 assertions**; PostgreSQL migration/recovery smoke **PASS**; Zazu Quality **PASS**; PHPMD **PASS**; Director Health **PASS**. Exact-head acceptance [Laravel workflow 45052231006](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45052231006) **PASS**: 691 Laravel tests / 1 skipped / 4,794 assertions; PostgreSQL migration/recovery smoke; 63 desktop Chromium, 7 mobile viewport, 1 mobile host-outage and 1 phone-local attachment/reconnect journeys; public/auth accessibility 1 passed. Zazu Quality [45052230840](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45052230840), PHPMD [45052230971](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45052230971), Director Health [45052230859](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45052230859) and Push-on-main [45052231209](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45052231209) passed. SonarCloud is skipped. Windows V1 package/fresh-install [45052196760](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45052196760) passed on runtime source head `b9f9a146c5a1310ef75e1f3edf5a341851be005b`.

**Physical acceptance seed:** the Founder-confirmed fictional PC/phone workspace is already populated. A controlled Android offline/restart/reconnect runbook is prepared in `docs/V1_RELEASE_CHECKLIST.md`. Automated gates are green; physical acceptance is now seeded and READY for the Founder-run installed-phone sequence. The actual physical-device gate remains OPEN until that run is completed. Do not ask the Founder to recreate the seed records.


## Job requirement correction and quote-history integrity — 2026-11-22

**Implementation on main:** online requirement records now have edit and remove actions from the job Requirements register. The shared editor preserves the current catalogue link, description, category, quantity, unit and notes. Mutations use the existing business → event → requirement lock order and the operational lifecycle guard; a requirement from another job cannot be changed through the nested route. Removing the final requirement is blocked when the job already has a quote, so a revision cannot accidentally become an empty commercial offer. Regression coverage verifies that the final line and its quote item remain intact when that removal is rejected, and that legitimate offline custom quote lines do not trigger false stale detection.

**Quote-history protection:** requirement edits change only the live job requirement. Existing quote item descriptions, quantities, prices and source snapshots remain immutable. Removing a requirement detaches the old quote item through the existing nullable foreign key; `QuoteVersion::matchesRequirements()` now treats a detached requirement snapshot as stale rather than silently ignoring it, while leaving offline custom quote lines (which have no requirement identity in their source snapshot) outside the requirement comparison. A new revision is rebuilt from the current requirements, while the previous version retains its historical line.

**Regression coverage added:** correction after quote creation preserves the original line snapshot and marks the quote stale; removal preserves the detached historical line and forces a new revision containing only the remaining requirement.

**Accepted-offer guard:** when a quote is accepted, its agreed requirement scope is now read-only in the online register and in offline job edits. Add/edit/remove controls are hidden in the register, and server-side checks reject direct online or queued offline scope changes. Additional work must be recorded as a separate job rather than silently changing the accepted offer.

**Compatibility detail:** existing legacy categories and custom units are retained by the shared edit form. Offline custom quote lines are not mistaken for deleted requirement snapshots; detached requirement snapshots still force a new revision.

**Acceptance:** implementation is committed to `main`; exact-head Laravel, Pint/quality, PHPMD, security and browser verification are queued and not yet accepted. No physical acceptance status is changed by this code increment.


## Helper linked-booking operations slice — 2026-11-23

**Implemented directly on `main`:**
- Job-detail Helper now loads counts from the current business-owned Event's existing preparation, purchase-order, asset-allocation, cost and travel-cost relationships.
- It lists only record types that actually have linked records. An empty relationship is not treated as a missing task or operational failure.
- Added feature coverage that seeds a tent/chair booking with one linked preparation item and one event-linked purchase order, then asserts both appear in Helper context.
- This remains contextual visibility, not a recommendation engine. It does not create tasks, infer prerequisites, alter permissions, or supersede the existing Job next-action authority.

**Verification:** source and test changes were read back from `main`. The Founder reports prior checks passed; these two new commits were made after that check. GitHub combined status currently returns no status entries for the new head, so the new feature test is added but not yet independently verified here. No physical offline acceptance is inferred.

**Next target:** map each saved requirement/capability to only the existing relevant operational records and permissions, then add narrow DJ, hire and catering cases. Avoid recommending a workflow action unless the domain state and user permission make it valid.


## Permission-aware Helper booking context — 2026-11-23

**Implemented directly on `main`:**
- Linked booking record counts are now individually gated by the same permission families used by their workspaces: `work.view` for preparation/cost/travel, `purchasing.view` for purchase orders, and `assets.view` for asset allocations.
- Added a feature test proving a staff member with work access but no purchasing permission sees preparation context but not the purchase-order count.
- Existing event/business ownership guard remains in place. This reduces unnecessary disclosure in the Helper; it does not replace route middleware or server-side authorization.

**Verification:** source and test are committed and should be run by the repository checks. No successful test execution is claimed for this new change from this session.


### 2026-11-23 — UI and Helper refinement follow-up

- Replaced catering/equipment-specific Job inspector tab labels with business-neutral Scope & preparation, Resources & setup, and Commercial position.
- Improved inspector visual hierarchy: card-like Job summary, readable three-column tab strip, sticky heading, contained content scrolling, full-width phone drawer, and aligned action controls.
- Hardened tab accessibility with explicit labelled tab/panel relationships, native hidden state, aria-hidden synchronisation and keyboard navigation coverage.
- Helper now surfaces active business service focuses and Job requirement components as compact chips; next action and its reason have stronger visual hierarchy.
- Added browser assertions for inspector tab switching, panel visibility and arrow-key navigation.
- PHPMD workflow on the previous batch completed with failure. Exact log detail is not available through the repository connector; treat PHPMD as open until the report is retrieved. Do not claim full CI success.
- All changes remain direct to main; no branch creation.


## Landing media, workspace wallpaper and dashboard image correction — 2026-11-23

**Implemented directly on main:**
- Removed bundled SVG illustration fallbacks from the landing-media configuration so the platform library exposes photographic assets and validated uploads only.
- Kept the six per-position dropdowns and added direct, keyboard-accessible assignment from the image library to the focused position.
- Added actual slot crop previews (hero 16:9; supporting placements 4:3), plus a save guard requiring six selected entries and successfully loaded previews.
- Corrected workspace wallpaper layering: a valid translucent gradient sits above the selected wallpaper, and shell/content surfaces no longer paint an opaque canvas over it.
- Re-enabled the business-selected dashboard image. The previous dashboard stylesheet explicitly set it to display:none; the new layout shows it with a restrained text gradient.

**Verification:** feature assertions and documentation were committed. Exact-head CI and physical PC/phone visual acceptance are pending; do not mark this slice green until checked.

**Next execution target remains the adaptive business capability-to-surface map.** Trace the current authoritative capability source, onboarding, catalogue categories, navigation, event requirements and offline sync; implement one narrow end-to-end preset slice without introducing a parallel configuration system.


## Adaptive catalogue vertical slice — 2026-11-23

**Implemented directly on main:** BusinessCapability remains the source of catalogue truth. The catalogue now separates rental items from all non-rental capability types (service, package, product and other), instead of incorrectly treating only catering/food/per-person records as the second stream. Labels and pricing basis are business-neutral, and empty streams are omitted. This allows DJ, decor, photography and other service businesses to use their actual catalogue without a catering-only tab.

**Architecture map:** membership primary_niche remains a single presentation focus; active catalogue categories supplement Helper focus through NicheFocus. Onboarding persists one focus. Navigation is still role/permission-driven and static. Dashboard tool visibility remains tier/permission-driven. EventRequirement remains the per-job component source. Capability records already participate in aggregate revision/sync, but the future multi-capability workspace configuration still needs offline parity tests. Full mapping is in docs/ADAPTIVE_BUSINESS_PRESETS_AND_EVENT_COMPONENTS.md.

**Next:** extend the owner-facing capability selection/review using existing BusinessCapability records, then test add/deactivate effects on catalogue and Helper. Do not introduce a parallel preset store or retroactively rewrite historical event/quote scope.


## CI regression repair and catalogue visibility hardening — 2026-11-23

**Failure causes traced from GitHub job logs:**
- Laravel tests had stale inspector label expectations, a brittle landing-preview substring count, a route-integrity false positive for `request()->route('event')`, and Helper tests expecting event relationship counts that were never loaded.
- Offline and populated browser tests still selected the retired “Financial Ledger” tab.
- PHPMD identified `LandingMediaService::library()` cyclomatic complexity 11, `PlatformAdminTest` and `ZazuHelperTest` above the public-method limit, and an invalid DemoScenarioSeeder test file ending.
- Zazu Quality found the unused Route import / class spacing issue in that demo test.

**Corrections committed on main:**
- Helper now loads only permitted event record counts, scoped to the active business.
- Updated test assertions to verify the actual neutral UI labels and count six preview image elements, not matching text in scripts.
- RouteIntegrity ignores `->route('parameter')` access while still checking `route('name')`.
- Updated both browser suites to use “Commercial position”.
- Repaired the demo test class and removed the unused import.
- Refactored the landing-media library into smaller methods and split high-public-method test classes.
- PHPMD passed on implementation/refactor head `f64d143c1f137261c356fe3258901dbbdcbc89c6`.

**Catalogue refinement:** controller now separates rental items from service/package/product/other items. Owners retain visibility of hidden capabilities for recovery; staff/manager catalogue views are filtered to active entries. Asset allocation links are permission-gated. Regression tests cover mixed-provider, single-specialist and staff-permission cases.

**Current verification boundary:** later catalogue filtering changes triggered new exact-head Laravel, PHPMD, Zazu Quality, Psalm and Director Health runs. Do not attribute the earlier PHPMD pass to the latest head until those newer results complete. PC/phone visual and physical offline acceptance are still not verified.

**Next execution target:** connect active business-owned catalogue offerings to New Job service requirements through the existing `services[]` contract used by offline creation. Update online and offline paths together; preserve requirement quantity/unit correction, historical quote snapshots and capability/business isolation. No parallel configuration store.



## Director execution — catalogue-driven Job intake parity — 2026-11-23

**Implemented directly on `main`; latest implementation commit:** [0a4fea3](https://github.com/Leano-Jordan/ZazuEMP/commit/0a4fea35df95df86df106bb54675cd9813dba209).

- New Job now reads its selectable offerings from active `BusinessCapability` records belonging to the current business. It no longer presents the generic static service-category list as the business's catalogue.
- Disabled offerings are omitted from new intake. An empty catalogue explains how to add an offering, while one-off custom services remain available separately.
- Online Job creation validates selected names against the active business catalogue inside the transaction, locks the selected catalogue records, and creates `EventRequirement` rows linked to the correct `capability_id`, category and default unit.
- Offline create/update keeps the existing `services[]` payload contract. Server reconciliation resolves names only against the mutation's business, links matching capability records and carries category/default-unit context. Legacy queued names retain the former static-category fallback; custom one-off services remain unlinked.
- Duplicate same-name catalogue entries fail closed during offline reconciliation rather than linking an arbitrary capability. Existing requirement quantity, notes and status are retained on matching offline updates; accepted quote scope checks remain unchanged.
- Added feature coverage for active versus disabled catalogue visibility and online requirement linkage/default unit.

**Verification boundary:** implementation and test files were read back from `main`. Exact-head Laravel, PHPMD, Zazu Quality, Psalm, Director Health, browser/offline, PostgreSQL and Windows workflows have not yet been confirmed for this implementation head. No test pass or physical offline acceptance is claimed. The latest GitHub combined-status response contained no status entries.

**Next execution:** inspect/run exact-head CI and repair only failures introduced or exposed by this slice. Add explicit offline regression coverage for capability linkage, business isolation, legacy queued payloads and custom one-off services. Do not expand into a new preset store or navigation rewrite.
