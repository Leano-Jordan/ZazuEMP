

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
