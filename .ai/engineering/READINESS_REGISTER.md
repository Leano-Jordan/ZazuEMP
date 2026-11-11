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



## Signed offline licence verifier — current-head verification — 2026-11-10

- Refactored Ed25519 token verification into `SignedOfflineLicenseVerifier`; `OfflineLicenseService` now coordinates verification, anti-rollback and persistence. The service no longer exposes unsigned local entitlement issuance.
- Current regression coverage: valid import, expiry, tamper rejection without replacing an existing entitlement, cross-business rejection, signed revocation, missing public key fail-closed, and rejection of an older renewal token.
- PHPMD initially rejected the monolithic verifier for cyclomatic/NPath/method-length complexity. The verifier was split into bounded methods and a dedicated verifier class; the PHPMD workflow now passes.
- Full Laravel suite on commit `b9fc97d58e923200caf7e515926453b390aab0f3`: **422 passed, 2,985 assertions**. This run also caught two stale source-contract assertions in `PwaOfflineFoundationTest`; those were corrected to match the actual `dataset` API and local-event-ID method signature before this passing run.
- Current-head checks: PHPMD **PASS**; Psalm security scan **PASS**; Zazu quality workflow **PASS**; populated upgrade/rollback workflow **PASS**; Laravel **PASS**. Browser smoke is still running and is not yet accepted as passed.
- Relevant commits: `79f41fc` initial verifier; `f01371a` remove unsigned issuer; `39194b5` input bound/anti-rollback; `5544e07` verifier refactor; `632e148` dedicated verifier; `9e82b7c` DI; `5a54a21` attachment DOM regression correction; `b9fc97d` local attachment method-signature regression correction.
- Remaining licensing blockers: owner activation/status UI, production issuer and private-key custody, installation identity/reactivation, renewal UX, and enforcement at the correct business boundary. No readiness score uplift yet; V1 remains **NO-GO** pending browser smoke and remaining release gates.


## Signed licence import hardening — 2026-11-10

- Removed the unsigned `issueLocalEntitlement` service method. Entitlement creation through the service now requires a valid Ed25519 signature; no application call sites depended on the removed method.
- Added a 16 KiB token size bound and signed `issued_at` requirement. The verifier stores the issue timestamp and rejects a different signed licence whose issue time is not newer than the currently installed signed entitlement, preventing a previously issued renewal token from rolling the local entitlement backwards.
- Added regression coverage for missing public-key configuration and stale renewal replay. Test fixtures generate ephemeral deterministic keys in the test process only; no private key is committed or configured for production.
- Follow-up commits: `f01371a` remove unsigned issuer; `742cb16` migrate tests; `6fa0299` import/config test hardening; `7b7d473` PHP namespace correction; `6dd4d58` test import correction; `39194b5` size and rollback guard; `27f2f6f` rollback guard namespace correction; `74a5c7f` rollback regression.
- Verification boundary: source re-fetch and structural inspection only. PHPUnit / PHP syntax checks and exact-head CI remain unrun. Activation UI, production issuer/key custody, installation identity, owner reactivation, renewal UX and enforcement remain open. No readiness score uplift; V1 remains NO-GO.


## Signed offline licence verification foundation — 2026-11-10

- Added local Ed25519 detached-signature verification and import to `OfflineLicenseService`; the exact signed payload bytes are verified before JSON parsing and entitlement persistence.
- Added fail-closed checks for missing/invalid public-key configuration, malformed/tampered tokens, required payload fields, unsupported version/status, invalid UUID/plan, wrong business binding, malformed dates and expiry-before-start.
- Supports signed `active` and `revoked` states. A revoked entitlement imports inactive; all validity checks remain local and require no network request.
- Configures only `ZAZU_LICENSE_PUBLIC_KEY` (base64 public key). No signing private key is included or permitted in the app repository.
- Added feature-test source for valid import, tamper rejection without overwriting an existing entitlement, cross-business rejection and signed revocation.
- Commits: `79f41fc` verifier; `ca9aefa` config; `90f971e` environment example; `cd9edfd` tests; `a99308f` required-field hardening.
- Verification boundary: files re-fetched after writes; PHPUnit, sodium runtime compatibility, Blade/browser flows and exact-head CI have not been run. No owner activation UI, issuer, installation identity, renewal workflow or enforcement middleware exists yet. This is a service-level security foundation, not completed commercial licensing. No readiness score uplift; V1 remains NO-GO.


## CSP inline-style attribute reduction — 2026-11-10

- Removed the six dynamic image `style=` attributes from the public landing page. Their image URLs now flow through response-nonce-authorized CSS custom properties and class selectors in the landing document head.
- Removed the dynamic inline `style=` attribute from the admin landing-image preview. Its per-field preview rule now uses a response-nonce-authorized style element and a stable field-derived element ID.
- Replaced the workspace-recovery sign-out form's inline `display:inline` declaration with the `zazu-auth-inline-form` class in the application stylesheet.
- Added SecurityHeadersTest source assertions that these active Blade views contain no inline style attributes and that the landing/admin dynamic CSS uses nonce-authorized style blocks.
- Commits: `4c80e28` landing page; `40711e5` admin preview; `b51f15a` recovery form; `e6735d0` stylesheet; `f8a236a` regression assertions.
- Guardian review: source-level inspection confirms the three changed views have zero `style=` attributes. The legacy `public/offline-attachments.js` contains inline attributes but is not imported by the active authenticated layout; it remains a dormant legacy asset and was not reactivated or modified. `resources/js/app.js` also uses CSSOM `style.setProperty/removeProperty` for the floating helper's measured nav-avoidance position, so the policy's `style-src-attr 'unsafe-inline'` allowance is deliberately retained until that runtime behaviour is redesigned and tested.
- Verification boundary: changed files re-fetched; no PHPUnit, Blade compilation, browser run or exact-head CI execution is available here. No CSP relaxation was made, no readiness score uplift is claimed, and V1 remains **NO-GO**.


## CSP style migration — workspace background variables — 2026-11-10

- Moved the business-specific dashboard and wallpaper CSS custom properties off the authenticated document's `body style=` attribute and into a response-nonce-authorized `<style>` element in the document head.
- Preserved the existing media routes, cache-busting version, conditional wallpaper behaviour and CSS variable names; no visual or route behaviour was intentionally changed.
- Added a focused `SecurityHeadersTest` source regression asserting the nonce block and absence of a body inline-style attribute.
- Commits: `3a77c92` (layout), `c31347b` (initial regression), `8e17a61` (precise body-tag assertion), `ccb6239` (move style block into head).
- Verification boundary: changed source re-fetched and inspected. PHPUnit, Blade compilation, browser regression and exact-head CI have **not** been run for this change. Other inline style attributes remain; CSP unsafe-style allowances are intentionally unchanged. No score uplift; V1 remains **NO-GO**.

# ZAZU EMP — COMMERCIAL READINESS REGISTER

## Current verified snapshot — 2026-11-09

**Verified application/test source:** `d55539ddfd52c7fa5140714288c5885dbcc99e3a` on canonical `main`.  
**V1 score:** **69/100 (3.46/5)**, up from 60/100 after five evidence-based domain uplifts. See `docs/ZAZU_V1_SCORECARD_2026-11-04.md`.

- Laravel: **414 passed / 2,938 assertions — PASS** ([run 42702419420](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42702419420)).
- Browser smoke: **65 passed / 22 skipped / 0 failed or flaky — PASS** ([run 42702419540](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42702419540)).
- Zazu Quality + secret scan: **PASS** ([run 42702419210](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42702419210)).
- Psalm Security: **PASS** ([run 42702419299](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42702419299)).
- PHPMD: **PASS** ([run 42702419074](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42702419074)).
- Populated upgrade/rollback: **PASS** ([run 42702419767](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42702419767)).
- Director Contract Lint: **PASS** ([run 42698440506](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42698440506)).
- Push on main: **PASS** ([run 42702420093](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42702420093)).
- SonarCloud: **SKIPPED**, not a pass.

**Why the score increased:** platform/onboarding, core lifecycle, security/tenant controls and active documents/attachments now have relevant automated evidence passing on the exact current head. No 5/5 scores were awarded.

**Still blocks release:** physical secure-origin offline acceptance; real installed-environment populated database/private-media restore; real deployment rollback; trusted-device/lost-device acceptance; operational privacy/legal closure; official Zazu brand clearance. Commercial release remains **NO-GO**.

---


## Purpose

This is the live release-risk register.

Readiness is tracked by domain and evidence maturity rather than by a single inflated percentage.

## Current verified snapshot — 2026-11-08

**Verified application/test source:** `d44ec33a57a4958380f2cfe4624f040859cbcf7a` on canonical `main`.  
**CI workflow correction commit:** `7946a87e78cf4dd2305d12fb20d3f77ed5653c87` (workflow-only; no application source changes).

- Laravel: **407 passed / 2,758 assertions — PASS** ([run 42481998972](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481998972)).
- Zazu quality + secret scan: **PASS** ([run 42481999066](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481999066)); the quality workflow now uses the same complete Laravel bootstrap as the dedicated workflow.
- Browser smoke: **65 passed / 22 skipped / 0 failed — PASS** ([run 42481998954](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481998954)).
- Psalm: **PASS** ([run 42481998947](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481998947)).
- PHPMD: **PASS** ([run 42481998949](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481998949)).
- Populated upgrade/rollback: **PASS** ([run 42481998959](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481998959)).
- Director Contract Lint: **PASS** ([run 42481998956](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481998956)).
- SonarCloud: **SKIPPED**, not a pass ([run 42481998971](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481998971)).
- Offline Work attachment integration and trusted-device transition coverage are automated-tested in this batch; physical installed-device disconnected launch/restart/reconnect remains unverified.
- Backup-v2 checksum and legacy-v1 compatibility tests passed in the Laravel suite; checksums do not establish archive authenticity, and the representative operator restore drill remains open.
- No numeric score uplift is awarded from CI alone. Commercial release remains **NO-GO** pending physical-device acceptance, real operator recovery and deployment rollback drills, operational privacy/legal closure and official Zazu brand clearance.

Exact run links and release gates are maintained in `docs/V1_RELEASE_CHECKLIST.md`. Older snapshots below are historical and must not override this verified state.

## Director audit snapshot — 2026-10-19

**Canonical ref:** `main` only. Current main is the source of truth; dated SHA values below identify evidence, not a moving branch pointer.

### Verified documentation/control defects corrected
- The V1 gate ledger previously labelled a 2026-10-07 SHA as current and presented earlier CI results as current-head PASS. It now distinguishes the last broad green evidence baseline (`7940033b6fd4f691692eec5e9d7f6ad587486f61`) from unverified later commits.
- `docs/health/latest.md` and `latest.json` were an October 12 Dependabot-branch report with queued CI. They have been moved to `docs/health/archive/`; the misleading root-level `latest.*` pointers were removed. The current health workflow is artifact-and-summary only, read-only for repository contents.
- The V1 scorecard now exposes a current evidence snapshot while preserving prior numeric scores as historical.
- A bounded scan across four active authored CSS files found and removed two duplicate `font-variant-numeric: tabular-nums` declarations. The duplicate `REG-017` entry was corrected to `REG-022` and ledger headings are now unique.
- Removed unreferenced `@fontsource/anton`; package/lock were changed together and the dependency licence audit/notice pointer refreshed. Latest-main install/build is pending.

### Implementation / verification boundary
- A Node built-in behavioral harness now runs the actual `public/sw.js` and covers three async logout races: stale persisted-identity read, late authenticated HTML response, and identity-state write already in flight during logout.
- `package.json` exposes `npm run test:service-worker`; `.github/workflows/quality.yml` executes it.
- Service-worker identity mutation and logout cache cleanup are serialized through a shared promise queue so logout cleanup runs after already-started identity writes. Read-after-write source reconciliation succeeded; **runtime/CI result for the latest main is still UNVERIFIED**.
- This is not a full offline-parity claim. Multi-account installed-browser proof and fully disconnected parity remain outstanding.

### Current V1 evidence disposition

| Domain | Current disposition | Remaining V1 gate |
|---|---|---|
| Correctness | New behavioral coverage IMPLEMENTED; latest-head execution unverified | Observe current-main CI and fix confirmed regressions |
| Architecture | Cache identity writes/clears serialized at source | Current-run regression evidence; continue evidence-led dead/stale-code review |
| Data integrity | Earlier transaction/idempotency/ownership evidence remains tied to its recorded SHA | Current-head suite plus populated commercial reconciliation |
| Security | Logout/cache race guards and three behavioral cases added | Current-head CI and real multi-account browser challenge |
| Workflow integrity | Main operational chain exists; earlier populated run is historical | Current-head and realistic populated chain evidence |
| UX/accessibility | Prior responsive and owner-device acceptance remain dated evidence | Reconfirm after latest deploy/runtime changes where affected |
| Reliability/recovery | Automated backup/restore and upgrade/rollback evidence exists at earlier heads | Representative customer-environment restore including private media; actual rollback drill |
| Operability | Health output is now explicitly artifact/summary-based; stale persistent snapshot removed | Reconcile actual current-main workflow runs and URLs |
| Upgrade/deployment | Earlier populated upgrade/rollback pass is historical | Current-head workflow plus real deployment rollback |
| Documentation/compliance | V1 ledger and health-report authority corrected | Legal/privacy operations, brand clearance, final distribution/licence review |
| Release evidence | Current exact-head conclusions not yet observed in this audit | Record current SHA, run URLs and conclusions before any readiness uplift |

### Numeric score policy

The previously recorded **80/100 commercial-readiness figure is historical (2026-10-04)**. Do not present it as a recalculated current score. No numeric uplift is awarded for source/test additions until the exact current-main checks pass and remaining operational V1 gates are reconciled.

Next hard gate: **current-main CI reconciliation for the new service-worker race suite, then controlled customer-environment backup/restore and rollback drills.**

---

## Evidence maturity

0 — not started  
1 — designed  
2 — implemented  
3 — automated evidence  
4 — runtime/CI verified  
5 — repeatedly proven through realistic/production/recovery evidence

## Release domains

- **Correctness** — critical workflows produce the intended result.
- **Architecture** — core structure is understandable, bounded and maintainable.
- **Data Integrity** — transactions, parent/child invariants, ownership, precision and concurrency are protected.
- **Security** — authentication, authorization, isolation, storage and trust boundaries are enforced.
- **Workflow Integrity** — real jobs can move through valid states, including failure/recovery.
- **UX / Accessibility** — critical flows are understandable and usable across required devices/themes.
- **Reliability / Recovery** — failures are observable and data can be restored.
- **Operability** — the owner can diagnose incidents and understand application health.
- **Deployment / Upgrade Safety** — clean and representative existing installations can be upgraded safely.
- **Documentation / Ownership / Compliance** — release/runbook, dependency, IP and privacy evidence exists.
- **Release Evidence** — claims are based on current CI/runtime/browser evidence.

## What blocks commercial release

A release-critical blocker can cause:
- data loss or corruption;
- unauthorized access;
- broken critical workflow;
- unrecoverable upgrade/deployment;
- inability to restore;
- materially incorrect financial/commercial state;
- inability to operate or diagnose the product.

## Future-scale work

Not automatic V1 blockers unless explicitly required:
- SaaS billing/entitlements;
- high-scale performance proof;
- multi-location expansion;
- production soak evidence;
- advanced messaging/portal ecosystem;
- advanced AI automation.

Future architecture should remain compatible with these possibilities without allowing them to displace current release-risk work.

## Current directive

**Close existing correctness, architecture, integrity, security, reliability and release-evidence gaps before speculative feature expansion.**

Last updated: 2026-10-06

## Offline operational depth update — 2026-10-07

Current main now contains phone-local and server synchronization coverage for inventory items/movements, operational event costs, assets and asset allocation/return, alongside the previously implemented commercial offline chain. Direct and paired bootstrap payloads carry these domains, and focused regression tests have been added.

Evidence boundary: IMPLEMENTED only for this new cycle until the current regression suite executes successfully in CI/runtime. No numerical readiness uplift is claimed from source changes alone.

Remaining offline gaps: runtime/device proof, broader disconnected parity across every desktop capability, and full recovery/real-world sync proof. Explicit conflict detection/resolution is implemented at source. Phone-local attachment upload/download is NOT integrated into the active Work screen; the dormant transfer client must not be counted as a working feature.

Last updated: 2026-10-07


## Evidence snapshot — 2026-10-01

Current Director assessment:

| Domain | Evidence maturity | Release condition |
|---|---:|---|
| Correctness | 4/5 | Current-head Laravel suite passed; populated business proof remains |
| Architecture | 4/5 | Refactors reduced import complexity; no major V1 blocker identified |
| Data Integrity | 3/5 | Populated-data/reconciliation proof required |
| Security | 4/5 | Current-head automated security/isolation coverage passed; final adversarial review remains |
| Workflow Integrity | 4/5 | Populated end-to-end walkthrough required |
| UX / Accessibility | 3/5 | Desktop/mobile/tablet/accessibility verification required |
| Reliability / Recovery | 2/5 | Backup/restore proof required |
| Operability | 3/5 | Final operational evidence required |
| Deployment / Upgrade Safety | 2/5 | Existing populated database migration proof required |
| Documentation / Ownership / Compliance | 4/5 | Release/runbook sign-off remains |
| Release Evidence | 4/5 | Current-head Laravel, browser, PHPMD, Psalm and Quality evidence observed |

The major V1 capability surface is present. Current-head CI/runtime evidence is now green. Remaining release risk is concentrated in populated workflow proof, recovery, upgrade/rollback and final authorization/device acceptance.


Current UI cycle does not create a new V1 feature expansion requirement.

## Source-hardening snapshot — 2026-10-01

The long-sprint page audit tightened shared form submission safety, browser-storage failure tolerance, quote financial visibility, multi-line purchasing usability and context-preserving return paths. The current release candidate now has green Laravel/browser/static-quality evidence. The remaining maturity gap is proof of realistic populated operations, recovery and upgrade behaviour.



## Latest visual-system audit — 2026-09-28

Target: colour theory, contrast, positioning and shared visual authority.

Completed:
- consolidated light-mode identity around cobalt-iris rather than generic utility blue;
- introduced sea-glass as a distinct secondary accent;
- separated semantic status colours into warning / success / danger / information roles;
- aligned base app utility colour tokens with the canonical visual layer;
- removed the repeated active-workspace navigation treatment;
- preserved the flat navigation structure;
- corrected header optical alignment;
- strengthened active/hover navigation states without light text on light surfaces;
- applied explicit dark foregrounds to light-mode cards, panels, forms, lists and dashboard surfaces;
- applied explicit semantic colours to dashboard lifecycle statuses;
- retained reduced-motion support;
- confirmed no literal non-dark light-on-light colour/background rule in the final visual layer by static source inspection.

Evidence boundary:
- static source inspection completed;
- rendered visual/device verification is still required before release acceptance.


## Director authorization challenge snapshot — 2026-10-01

The populated source walkthrough is coherent, but final authorization certification is currently blocked by a concrete role-policy gap.

AUTH-ROLE-001 — Seeded manager role has no permission mapping
- Demo fixture creates an Operations Manager membership with role manager.
- PermissionService grants all permissions to owners and otherwise reads the role entry from config/zazu.php.
- config/zazu.php defines staff permissions but no manager entry.
- Result: the seeded manager resolves to an empty permission set and cannot reach permission-protected workspaces such as Dashboard, Work, Customers, Quotes, Purchasing or Finance.
- Classification: authorization completeness / role-model consistency, not proven privilege escalation.
- Release impact: blocks final multi-role authorization certification until the role policy is made explicit.

The populated business financial chain is source-consistent at ZAR 11,500.00 total with ZAR 3,450.00 deposit plus ZAR 8,050.00 final payment. This remains source evidence only until executed against a current runtime.

Current Security maturity remains 4/5: automated isolation/authorization foundations exist, but the final adversarial challenge and role-policy closure remain outstanding.

## Manager role policy closure — 2026-10-01

**AUTH-ROLE-001 RESOLVED AT SOURCE:** the previously seeded `manager` role now has an explicit permission matrix.

Manager policy:
- operational dashboard/calendar/work/customer/quote/purchasing/inventory/assets/supplier/report capabilities;
- Finance view only;
- no invoice creation, payment recording or expense creation;
- no work deletion;
- owner-only settings/catalogue/administration boundaries remain enforced.

Regression coverage has been added for representative allowed and denied manager routes plus direct permission resolution.

**Release status:** role-policy gap closed. Final authorization certification remains pending runtime/adversarial execution, not source implementation.


## SaaS-readiness architecture gate — 2026-10-02

A hosted multi-business SaaS path is now an explicit architectural readiness concern, while public SaaS release remains a separate certification gate.

### Architecture position
- Current deployment remains a coherent modular monolith.
- Business ownership and parent/child invariants are the required isolation seam.
- Local-first core workflows remain protected from accidental online dependencies.
- Scaling is evidence-driven: optimise first, then targeted caching/queues, then infrastructure scaling, then service extraction only if required.
- Distributed infrastructure is not a current release requirement.

### SaaS certification gates added
Before hosted multi-business release can be accepted, Director must have evidence for:
1. cross-business read isolation;
2. cross-business mutation isolation;
3. nested parent/child ownership consistency;
4. search/filter/export isolation;
5. attachment/download isolation;
6. background-job ownership where jobs exist;
7. populated multi-business authorization challenge;
8. migration of existing business data into explicit ownership boundaries;
9. backup/restore with multiple businesses;
10. deployment and upgrade safety;
11. operational logging/diagnostics without cross-business leakage.

### Status
**Architecture foundation:** established.

**Hosted SaaS certification:** not yet claimed.

The next Director target is isolation proof against actual repository implementation and populated multi-business fixtures. This does not expand V1 feature scope by itself.

Last updated: 2026-10-03


## Director niche + offline + isolation cycle — 2026-10-03

- Primary niche is explicitly non-exclusive: it controls emphasis, while active business capabilities determine supporting niche signals.
- Sound & DJ was used as the reference niche; overlapping Chairs & tents capabilities remain visible when the business catalogue supports them.
- Offline-first is now an explicit architecture contract in `docs/ZAZU_OFFLINE_FIRST_ARCHITECTURE.md`.
- Static PWA caching and local licensing remain foundations; broad disconnected phone data/write/sync capability is now implemented in the workspace, but final release claims remain gated on runtime/device evidence and broader parity/recovery proof.
- Commercial isolation source audit found no justified application rewrite. Active-business context, route-bound ownership, model save protection, parent/child database constraints, search scoping and private media checks are present.
- Additional adversarial coverage was added for foreign search results and foreign attachment downloads.
- Latest main-head CI workflows are queued, so current-head runtime/CI verification remains pending.

Next Director target:
**CURRENT-HEAD RUNTIME VERIFICATION → consume the queued Laravel, quality, PHPMD and browser results; classify failures; then run the populated multi-business isolation challenge against the latest verified head.**



## Director commercial audit snapshot — 2026-10-03

### Overall health

**Commercial-readiness maturity: 67/100**

This is a weighted maturity score, not a percentage of source-code completion. The remaining maturity gap is concentrated in proof and release controls rather than basic CRUD capability.

### Scorecard

| Domain | /100 | Work left | Current Director position |
|---|---:|---:|---|
| Correctness | 78 | 22 | Core paths implemented; populated end-to-end proof required |
| Architecture | 84 | 16 | Modular monolith and staged offline architecture are coherent |
| Data Integrity | 74 | 26 | Transactions/idempotency/ownership exist; populated reconciliation proof open |
| Security | 76 | 24 | Isolation/authz foundations exist; final adversarial certification open |
| Workflow Integrity | 72 | 28 | Connected chain exists; realistic populated traversal open |
| UX / Accessibility | 68 | 32 | Strong source foundation; rendered mobile/tablet/WCAG-oriented proof open |
| Reliability / Recovery | 42 | 58 | Real backup/restore drill remains release-critical |
| Operability | 58 | 42 | Error/audit foundations exist; runbooks and recovery evidence remain |
| Deployment / Upgrade Safety | 44 | 56 | Existing populated upgrade and rollback remain unproven |
| Documentation / Ownership / Compliance | 68 | 32 | Governance strong; privacy/terms/brand/licence closure remains |
| Release Evidence | 54 | 46 | Historical green evidence cannot certify latest observed main ref |
| **Commercial readiness** | **67** | **33** | **Not certified** |

### Release-critical findings

**AUD-CRIT-01 — Populated operational workflow partially evidenced**
The seeded owner journey now passes through Work inspection, requirements, preparation, purchasing, inventory/assets, invoice history and reporting; payment replay/overpayment and manager/staff authorization also pass. A controlled customer → job → quote creation/acceptance → invoice/payment → completion run remains required.

**AUD-CRIT-02 — Backup/restore unproven**  
Commands and safety logic exist, but customer recovery has not been exercised and evidenced.

**AUD-CRIT-03 — Populated upgrade/rollback unproven**  
Migration/repair history makes representative populated upgrade verification mandatory.

**AUD-CRIT-04 — Current-head CI evidence unavailable**
Local Laravel and desktop/mobile/tablet Playwright verification is observed for main `4b1fbb8c6139215d5338b442b73a943d820948d9`. GitHub Actions status remains unverified because the GitHub CLI is unauthenticated in this environment; local evidence does not substitute for CI.

**AUD-HIGH-01 — Physical-device acceptance incomplete**
Responsive critical workflows pass in Pixel 7 and tablet Playwright emulation, and a rendered inspector screenshot was reviewed. Physical phone/tablet acceptance remains open.

**AUD-HIGH-02 — Legal/privacy operational layer incomplete**  
Privacy notice, POPIA operational controls, operator agreements where applicable, retention/deletion, incident response and customer terms remain open.

**AUD-HIGH-03 — Zazu brand clearance required**  
Current public market evidence shows unrelated Zazu uses, including a South African business-finance product and an event/catering business. This is a clearance risk, not a legal conclusion.

**AUD-HIGH-04 — Helper implementation trails direction**  
New Helper engine/skin architecture is documented, while the live component remains the legacy bird implementation.

### Contradictions requiring reconciliation

- Helper documentation now defines selectable skins; older Director wording used “bird mascot”.
- Multiple status files contain historical/current HEAD values; living documents must distinguish the current main ref from the last application-assessment ref.
- “Offline-first” is the target architecture; current release must not be described as fully disconnected phone-local operation.

### Next Director priority

**CURRENT-HEAD VERIFICATION → POPULATED COMMERCIAL WORKFLOW → RECOVERY → UPGRADE/ROLLBACK → LEGAL/LICENCE/BRAND CLOSURE → FINAL DIRECTOR CERTIFICATION**

No feature expansion should displace a release-critical gate.

Last updated: 2026-10-03

## Current-head verification update — 2026-10-04

Baseline: remote `main` `4b1fbb8c6139215d5338b442b73a943d820948d9`.

- Laravel: **241 passed / 1,377 assertions** (`composer test`).
- Playwright: **25 passed / 2 intentionally skipped** across desktop Chromium, Pixel 7 emulation and tablet emulation. The only skips are the registration flow on mobile/tablet; that journey is run once on desktop to avoid shared-IP registration throttling.
- The suite ran against a freshly migrated and demo-seeded temporary SQLite database. Theme/navigation checks no longer require external storage state.
- Remaining release gates: authenticated CI status, complete newly created commercial workflow proof, physical-device acceptance, backup/restore drill, populated upgrade/rollback, and legal/licence/brand closure.
- `pdo_firebird` startup warning remains a local PHP configuration issue; all tests completed.

The 2026-10-03 numeric scorecard above is not recalculated from this single local evidence cycle.

Last updated: 2026-10-04


## Current populated authorization/isolation evidence — 2026-10-04

Target: final populated multi-business authorization/isolation challenge.

Evidence:
- PopulatedAuthorizationIsolationTest seeds Business A from the Demo Scenario and creates a separate Business B owner/customer.
- Business B cannot view Business A's populated customer, Work/Event or invoice.
- Business B search cannot surface Business A's customer.
- Business B cannot create Work against Business A's customer; no unauthorized event is created.
- Business B's own customer remains available.
- Laravel CI run 37203066802 passed the new challenge as part of 242 tests / 1,381 assertions.
- Current-head Psalm Security Scan, PHPMD, Quality, CodeQL, browser smoke and populated upgrade/rollback also passed.

Disposition: Security evidence is VERIFIED / PROVEN at current runtime level. This is not a formal security certification.

Remaining material release gates: physical phone/tablet acceptance; legal/privacy operational closure; release-level dependency/transitive licence audit; Zazu brand/trade-mark clearance; final Director re-audit.

Last updated: 2026-10-04


## Director release-documentation closure — 2026-10-04

The minimum V1 release documentation set now exists in `docs/RELEASE_*.md` and has been audited against the repository implementation.

**Documentation-design maturity:** 5/5.

The audit confirms:

- technical privacy/security controls are implemented in the application foundation;
- data-subject rights, retention/disposal, PAIA handling and customer terms are currently manual/documentary controls, not self-service application features;
- incident response has technical evidence but still requires named organisational escalation and notification handling;
- operator/data-processing controls depend on the actual deployment/vendor relationships;
- deployment, backup/restore and upgrade/rollback runbooks now have explicit operating procedures.

**Release interpretation:** this closes the missing-document-set problem but does **not** make Zazu legally cleared.

Remaining material gates are now explicitly:

1. final privacy/legal operational completion;
2. release-level dependency/licence notice completion;
3. Zazu brand/trade-mark clearance;
4. physical phone/tablet acceptance;
5. final Director re-audit against the selected release commit.

Last updated: 2026-10-04


## Director defect refinement — 2026-10-04

**Wallpaper:** previously documented as implemented, but the rendered path was not actually effective because later global body background rules overrode the selected wallpaper image. **Resolved at source** with a final specific wallpaper rule; regression coverage added.

**New-job customer creation:** existing flow was valid but operationally inefficient because a new customer required leaving the job flow. **Resolved at source** with an in-context customer-creation dialog using the existing permission/business boundary and immediate selection in the current job form.

**No regression intent:** existing job creation, customer storage, business isolation, private media serving and branding storage were preserved. New tests cover the new boundaries.

Last updated: 2026-10-04

## Director UI/UX + commercial re-audit — 2026-10-04

Source-level refinement completed:
- base form surfaces are theme-aware;
- final visual CSS duplicate selectors were reduced from approximately 1,561 blocks to 1,263;
- dark-form regression coverage added;
- shared command-band/calendar artwork and compact holiday markers retained;
- repository hygiene scan found no dump(), var_dump(), console.log(), browser alert(), or NotImplemented residue.

Updated assessment:
- Correctness: 84/100
- Architecture: 87/100
- Data Integrity: 82/100
- Security: 88/100
- Workflow Integrity: 78/100
- UX / Accessibility: 78/100
- Reliability / Recovery: 80/100
- Operability: 72/100
- Deployment / Upgrade Safety: 82/100
- Documentation / Ownership / Compliance: 72/100
- Release Evidence: 82/100
- Overall commercial readiness: 80/100

This is a maturity score, not a release approval.

Remaining material gates:
1. final populated commercial workflow traversal;
2. physical phone/tablet acceptance;
3. disconnected phone-local store/pairing/bootstrap;
4. legal/privacy operational closure;
5. dependency/licence notice closure;
6. Zazu brand/trademark clearance.

Current valuation view:
- replacement-cost reference: R450k–R900k;
- realistic current early software/product asset value: R150k–R400k;
- recurring revenue and retention are required to support a materially higher SaaS valuation.

Last updated: 2026-10-04


## Control-plane cleanup status — 2026-10-06

The Director control plane has been reconciled against current `main`. Cases 0012 and 0013 are **CORRECTED / VERIFICATION PENDING**, not closed. The canonical read order is now owned by `.ai/engineering/00_DIRECTOR_ENGINE.md`; the historical router path is compatibility-only. Director eval dispositions are pinned and `.agents/evals/**` is owner-review scoped.

**Release evidence rule:** no readiness gate is marked CLOSED from source inspection alone. The exact current-head CI run must be recorded before closing verification-dependent cases.

Current application gates remain:
1. current-head CI/browser evidence;
2. populated commercial workflow traversal;
3. backup/restore;
4. populated upgrade/rollback;
5. physical phone/tablet acceptance;
6. legal/privacy operational completion;
7. dependency/licence notice closure;
8. Zazu brand/trade-mark clearance;
9. final Director re-audit.

Last updated: 2026-10-06


### 2026-10-07 — offline attachment evidence boundary

Jarvis added the attachment transfer layer to the offline implementation. Repository evidence now covers business-scoped listing, upload/download endpoints, idempotency, separate local binary storage, and online retry wiring. Runtime/device proof remains a release gate; this change does not by itself certify physical-device offline recovery.

## Offline parity hardening — 2026-10-07

Current main closed several source-level offline workflow gaps without creating a second UI/workspace:
- quote acceptance is locally queueable against the existing accepted-status action;
- purchasing receipt is locally queueable against the existing receiving form;
- asset allocation/return uses the existing asset controls and offline allocation contract;
- service edits reuse the existing service editor and preserve the existing offline service contract;
- offline submit validation no longer rejects legitimate mutations merely because they lack a `name` field;
- inventory movement and asset allocation bootstrap data are normalized into the same local record shape as the other synced collections;
- the purchasing receipt idempotency UUID helper typo is corrected.

Relevant main commits: `34d3cc7`, `9e6c6a0`, `31ba0d6`, `127ee92`, `94921ed`, `dc435ad`.

Evidence boundary: **IMPLEMENTED / targeted source regression added; current-head runtime and CI evidence remains pending**. GitHub Actions for the current main head are queued.


## Focused P0/P1 workflow audit — 2026-10-20

### P0 — commercial source consistency
- Finding: invoice creation showed accepted quote, independent job, direct invoice lines and tax treatment together. Backend mismatch guards existed, so this was confirmed workflow ambiguity/avoidable validation failure, not a proven data-integrity bypass.
- Source correction: quote mode binds the job to the quote's event, disables job and tax controls, hides/disables direct lines and displays an explicit source summary. Clearing quote selection restores direct mode and the manually selected job.
- Regression assertion added to `QuoteCommercialHandoffTest`.
- Existing populated reconciliation test covers quote acceptance → R11,500 invoice → R3,450 deposit → R8,050 final payment → paid status.
- Disposition: **IMPLEMENTED at source; current-head test/CI/browser verification UNVERIFIED.**

### P1 — operational consistency and double booking
- Online asset allocation and offline mutation paths both contain overlap checks protected by transaction/locking; existing offline operational tests cover allocation/release boundaries.
- Populated cross-screen job traversal and rendered mobile/keyboard review remain open.
- No current readiness score uplift. Source inspection and added tests are not evidence of current-head execution.

### Tool-selection discipline
- GitHub is used for repository changes and CI evidence.
- Firecrawl exhausted its credits earlier in this task and is not retried without new quota evidence; use native web search/official sources for external competitor research.
- Quartr is for investor relations/filings, not UX flow audits. EdgePilot is for strategy/backtesting, not source or browser testing.
- Select tools for a specific evidence need; do not call plugins merely because they are connected or named.

Next gate: targeted Laravel tests → current-main CI/run URL → rendered browser traversal → populated job reconciliation. Continue backup/restore and rollback gates separately. Do not mark them closed.

Last updated: 2026-10-20


## Director execution update — 2026-11-01

**Scope:** offline continuity and the customer → Work dependency path. Changes were committed directly to canonical `main`.

| Area | Source disposition | V1 evidence still required |
|---|---|---|
| Origin-loss fallback | Implemented bounded origin probe; marked forms queue locally when the host is unavailable even if the browser reports online | Current-head browser/Quality run and physical installed-device test |
| Local persistence | Mutation, optimistic workspace state and dirty marker now share one IndexedDB transaction | Current-head browser regression, force-close/reopen and storage-failure acceptance |
| Customer offline create | Full supported text profile and primary/day/night contacts are included; photo upload is honestly blocked while disconnected | Current-head Laravel/E2E and later private-file sync parity |
| Pairing bootstrap | Empty selection produces a bounded recent customer/job/quote/preparation working set and owner disclosure | Current-head Laravel test, paired-device bootstrap and data-volume/performance check |
| Offline Work creation | Stable customer local identity, job type/date/address/services and requirement creation are applied business-scoped | Current-head Laravel/E2E, reversed dependency push and populated Work workflow |
| Security / isolation | Customer identity resolution is explicitly business-scoped; cross-business regression added | Current-head Psalm/Laravel and adversarial multi-business device test |

**Verification boundary:** changes are **IMPLEMENTED** at source and regression tests are **ADDED**. Current-main Actions results must be observed on the exact resulting SHA before marking these tests passed. No numerical readiness uplift is claimed.

**Still release-blocking:** stable HTTPS hosting with durable database/private-file storage; actual disconnected app-icon launch and full close/reopen; complete V1 offline screen/data parity; deterministic attachment handling; end-to-end conflict recovery; customer-environment backup/restore and rollback; final privacy, legal, licence and brand clearance.


## Security audit update — 2026-11-03

| Control | Evidence now recorded | Current status / required proof |
|---|---|---|
| Spreadsheet import resource bounds | Source caps CSV/XLSX bytes, rows, columns, cell size, expanded archive size/entries, workbook sheets and cell count; regression cases added | Implemented; exact-head PHP/quality CI pending |
| Customer import preview | Controller now extracts first worksheet rows using the reader's real response contract; read-only feature case added | Implemented; exact-head PHP/quality CI pending |
| API response security baseline | Request-ID and security-header middleware now append to API group; regression case added | Implemented; exact-head PHP/quality CI pending |
| Trusted-device security boundary | Owner-only pairing, bounded business bootstrap, explicit settings disclosure, local logout clearing and token revocation exist | V1 model is intentionally business-scoped; complete device inventory/revocation UI and lost-device/physical-lock acceptance |
| CSP inline allowance | Per-response nonce authorizes identified inline Blade scripts; inline styles still use unsafe-inline | Partially fixed — script policy source updated; style migration and browser acceptance remain |
| Sync token lifecycle | Hashed bearer token, owner revoke and 180-day rolling inactivity expiry | Implemented in source/tests; physical lost-device and reassignment acceptance remains |
| GitHub branch checks | Protected main reports no required status-check contexts | Owner action — require quality/security checks |
| Live black-box verification | No Zazu runtime URL and authenticated test account were supplied for this repository-only audit | Open — run black-box tests against the owner-designated Zazu environment |

**Evidence rule:** source changes and tests added are implementation evidence only. Do not mark the new cases as passed until Actions reports success for the resulting exact `main` SHA.


## Recovery authority finding — 2026-11-04

A newly confirmed security defect was the mismatch between business-scoped settings access and installation-wide backup/restore commands. A business owner could download the full installation archive or replace the complete installation database/private storage. Business-owner routes and UI have been removed; CLI recovery remains operator-only.

| Domain | Disposition | Evidence still required |
|---|---|---|
| Security / tenant isolation | Source correction IMPLEMENTED | Exact-head CI and route/view regression pass |
| Installation backup/restore | CLI IMPLEMENTED / AUTOMATED | Real operator recovery drill on intended deployment |
| Tenant self-service export/restore | NOT IMPLEMENTED | Define and test tenant-scoped export/import semantics |
| Release readiness | OPEN | Do not claim tenant backup/restore as a V1 capability |

No numeric uplift is awarded until exact-head CI passes. This correction reduces exposure; it does not complete the recovery gate.


## V1 scorecard reconciliation — 2026-11-04

The detailed 13-domain V1 evidence scorecard is `docs/ZAZU_V1_SCORECARD_2026-11-04.md`. Its unweighted average is **3.0/5 (60/100)**. This is evidence maturity, not percentage of code or features complete. Exact assessment head `d1e2a4397b064d3fbfe7fc2cf1dc59236e00922f` had CI queued at assessment time. Keep commercial readiness NO-GO until the critical operational, security, legal and brand gates are closed.


## Exact source-head CI — 2026-11-04

Application source SHA `d1e2a4397b064d3fbfe7fc2cf1dc59236e00922f` has passing Laravel, Zazu quality/secret scan, Psalm, PHPMD, populated upgrade/rollback and push-on-main checks. Browser smoke is still running. SonarCloud is skipped. The latest documentation commits do not alter application source. Maintain the NO-GO release position until browser and operational gates close.


### 2026-11-04 — trusted-device transition and attachment lifecycle

- Retained DEC-019: the offline workspace is business-scoped by design because the owner authorizes the paired device as a business-trusted device. Do not redesign it as user-scoped without an approved architecture change.
- Added a separate business-scoped attachment IndexedDB database, included pending local attachments in logout assessment, and clear the active business attachment store during local session cleanup.
- The old unscoped attachment database is intentionally not migrated or deleted because its records lack reliable business ownership metadata.
- Active Work attachment transfer remains unimplemented. The legacy transfer client is dormant and not loaded by the authenticated layout.
- Remaining acceptance: logout/expired-session/business-switch/same-business account transition/interrupted cleanup/late response/device revocation; then integrate the attachment flow into the active Work page and test upload/retry/download/business isolation/logout.
- Source/test exact head before this documentation update: `cdea317643dd5e226d685f84c32f51d875defc80`. GitHub Actions run `41882179271` was still in progress; no CI pass is claimed.


## Phone-local Work attachment integration — 2026-11-07

| Control | Evidence now recorded | Current status / required proof |
|---|---|---|
| Business-scoped attachment persistence | Active client reads/writes the existing business-namespaced attachment DB; selected files are atomically stored with UUID idempotency keys | IMPLEMENTED at source; exact-head browser persistence test pending |
| Reconnect upload / retry | Multipart upload uses the paired device bearer token and existing idempotency contract; local Blob remains until an attachment ID is confirmed | IMPLEMENTED at source; exact-head Laravel + Playwright execution pending |
| Private attachment retrieval | Active Work view lists server attachments and caches private download responses for local access | IMPLEMENTED at source; exact-head browser test and installed-device check pending |
| Multi-file save integrity | Up to 10 files are written in a single IndexedDB transaction | IMPLEMENTED at source; storage-quota/transaction-abort runtime case not yet observed |
| Logout/revocation lifecycle | Uses the existing pending attachment assessment and business workspace cleanup functions | Existing source path retained; logout/revocation adversarial acceptance pending |

The service-worker cache is `zazu-static-v30`. Browser regression: `e2e/offline-attachment-sync.spec.js`. Laravel source contract: `PwaOfflineFoundationTest`. Server idempotency regressions: `SyncAttachmentIdempotencyTest` (exact retry, changed payload 409, changed event 409). Latest observed implementation SHA before this ledger update: `1c05cb5359695e0a5588593c1de0fdf3407b3c2e`.

**Verification:** source files were re-fetched and reconciled. Combined commit status had no contexts on `bc1de6224971803bdecee18dc8bcbed7781c5287`; the commit workflow-run query exposed no push-run evidence for the later SHA. Laravel, frontend build, browser and static-quality checks remain **UNVERIFIED**. No numerical evidence-maturity uplift is awarded. Physical installed-device proof, independent offline Work page launch, and attachment upload following offline-created Work identity resolution remain open.


### Offline-created Work identity handoff — 2026-11-07

Pending phone attachment records that retain `event_local_id` are now reconciled against the synced business `state.jobs` record before upload. The corresponding Playwright regression verifies that the resolved server event ID and original idempotency key are sent. This closes the source-level orphaning gap for records created by the previous phone queue. The active Work detail upload form still requires a persisted job; the full rendered offline-create → reconnect → attachment journey remains unverified.

**Evidence:** IMPLEMENTED at source; test added, not yet executed. No readiness score change.


### Paired-device attachment API contract — 2026-11-07

The server regression suite now covers the full phone-consumed API boundary: upload response idempotency/event identity, list metadata including idempotency key/name/size, private download success, foreign-business event-list denial, and foreign-business download denial using an existing private file fixture. Test commit: `bd47ec672ea0f4b609614ce24c3baa9d1ac4bd0a`.

**Evidence:** tests added; Laravel execution and exact-head CI remain UNVERIFIED. No readiness score change.


### Trusted-device session transition regression — 2026-11-07

| Control | Evidence added | Status |
|---|---|---|
| Authentication host unreachable at logout | Browser test verifies authenticated session, paired business workspace and pending mutation remain intact when `/up` fails | Test added; exact-head Chromium execution pending |
| Same-account logout/re-login | Browser test verifies business pairing and queued mutation persist through successful account logout and re-authentication | Test added; exact-head Chromium execution pending |
| Owner device revocation | Laravel test checks old token rejection across bootstrap, pull, push and attachment-list endpoints | Test added; exact-head Laravel execution pending |
| Non-owner / foreign-business revocation | Existing negative authorization tests retained | Existing tests; exact-head execution pending |

No score uplift is awarded. Expired-session, interrupted logout, stale in-flight response, physical-device acceptance and lost-device operating procedure remain open.


### Backup archive integrity — 2026-11-07

| Control | Evidence added | Status |
|---|---|---|
| Backup payload integrity | Version-2 manifest records SHA-256 for database and every private-storage file | Implemented; exact-head Laravel test pending |
| Restore integrity gate | Restore checks exact archive payload/checksum membership and streams SHA-256 before extraction or installation mutation | Implemented; tamper regression added, execution pending |
| Legacy backup compatibility | Version-1 archives remain accepted without checksums; new version-2 archives require them | Implemented; dedicated compatibility runtime case still pending |
| Backup authenticity | No signature/key custody or provenance proof | Open; hashes alone are not authenticity |
| Real recovery drill | Operator restore against representative installation data/media, followed by access and integrity checks | OPEN / release blocker |

No readiness score uplift. Recovery remains 2/5 until exact-head tests and a real operator drill pass.


### Backup format compatibility regression — 2026-11-07

The backup/restore feature suite now also exercises both format boundaries: a version-2 archive without its required checksum manifest is rejected before touching installed private data, and a real format-version-1 archive built from a valid database/private-media snapshot is restored successfully in the round-trip scenario. These tests are **ADDED / NOT YET EXECUTED**. This improves compatibility evidence but does not close the real operator recovery drill or prove backup authenticity.


### Offline-created Work attachment UI journey — 2026-11-10

| Control | Evidence added | Status |
|---|---|---|
| Actual offline job creation | Browser test uses the real customer and Work forms while disconnected | Test added; runtime execution pending |
| Operator attachment entry point | Browser test selects a file through the rendered local Work row Attach files action | Test added; runtime execution pending |
| Local Blob persistence | Test reloads attachment by local job ID and reads the original file content | Test added; runtime execution pending |
| No premature upload | Test reconnects before the job has a server ID and asserts zero uploads / one pending attachment | Test added; runtime execution pending |
| Identity handoff and upload | Test assigns the server identity to the locally queued job and checks normal reconciliation uploads the original file/key against that ID | State-boundary simulation added; live job sync round-trip remains open |

Regression: e2e/offline-attachment-sync.spec.js; commit 876f0828adba101221f89df10cbbced0890a2a89. Source was re-fetched and inspected. Playwright, Laravel, frontend build and exact-head CI were not executed. No readiness score uplift. V1 remains NO-GO.

### Local Work attachment visibility and retrieval — 2026-11-10

- Local Work rows now list locally stored attachment filenames and sync state.
- Each file entry can retrieve the business-scoped Blob and trigger a download using its original filename.
- The UI refresh is isolated from the row render so it does not reset the attachment input or save feedback.
- Playwright coverage asserts file visibility and download initiation through the actual operator row.
- Implementation commits: 0c202680, 5a653874, d4c574e3, cd677995, 22ea45fe.
- Source re-fetch confirms implementation anchors. Browser runtime, storage persistence, exact-head CI and real server sync remain unverified. No readiness score uplift; V1 remains NO-GO.

## Same-ID signed licence replay protection — 2026-11-10

- Anti-rollback now applies regardless of whether a renewal/revocation reuses the same license_id.
- Exact signed payload bytes are SHA-256 fingerprinted after signature verification. Exact-token re-import is idempotent; a different payload cannot reuse the same issue timestamp.
- Per-business row locking serializes imports; malformed/incomplete installed signed metadata fails closed.
- Added feature regression source for same-ID replay after revocation, exact-token re-import, same-timestamp payload substitution and incomplete metadata.
- Source re-fetch/structural review only. Automated execution remains unverified; no readiness uplift and V1 remains NO-GO.


## Offline bootstrap race protection — 2026-11-10

A verified source-level race allowed an in-flight bootstrap snapshot to overwrite a local mutation queued after the request began. The client now reconciles bootstrap application inside one IndexedDB read/write transaction spanning persisted workspace state and the mutation queue. It applies only when the device token is unchanged and no pending mutation exists; otherwise it discards the snapshot and leaves the pull cursor unacknowledged. Both pull and refresh paths use the guard.

Regression: `e2e/pwa-offline.spec.js` holds the bootstrap response, queues a customer mutation through the actual offline queue, then verifies the old snapshot cannot replace local state or erase the pending mutation. Commits: `a75f6d6`, `e63f97a`.

**Status:** IMPLEMENTED at source; Playwright and exact-head CI execution UNVERIFIED. No score uplift. V1 remains NO-GO.


## Failed mutation and conflict atomic persistence — 2026-11-10

A server-rejected offline mutation was previously persisted as `failed` in IndexedDB, while its operator-visible conflict was added to in-memory state and written later. A tab close or state-write failure in between could strand the failed mutation without its recovery UI.

The push acknowledgement now uses one IndexedDB read/write transaction across `mutations` and `state`. The failed record and open conflict are committed together. Added a Chromium Playwright regression using an actual queued mutation and the real API response shape (pending status plus error and conflict ID), then checking both persisted records and `local_dirty=false`.

Commits: `5b15209`, `78917ad`, `d0377f8`.

**Status:** IMPLEMENTED at source; exact-head Playwright/CI execution UNVERIFIED. No score uplift. V1 remains NO-GO.


## Retryable offline mutation failure handling — 2026-11-10

- Corrected a sync failure-path gap where unexpected non-validation exceptions could leave a server mutation pending while the client incorrectly marked it terminally failed without a conflict record.
- Server response now explicitly marks unexpected exceptions retryable; deterministic validation/identity failures retain explicit conflict IDs.
- Client preserves retryable failures in the pending queue with their last error and reserves the failed/conflict state for responses carrying an operator-resolvable conflict ID.
- Regression source added for a simulated persistence exception and no-orphan-conflict invariant.
- Commits: `3d22994`, `8df076c`, `3eba5cf`.
- Source re-fetch/structural review only. PHPUnit, Playwright and exact-head CI are **UNVERIFIED**. No readiness score uplift; V1 remains **NO-GO**.


### Idempotent offline conflict recording — 2026-11-10

- Conflict creation now serializes on the owning active sync-device row and reuses an existing open conflict for the same device/mutation/type.
- Applied to both deterministic domain rejection and changed-payload identity mismatch paths.
- Regression added for duplicate changed-payload delivery: same conflict ID returned and one open row remains.
- Source re-fetch/structural inspection only. Runtime concurrency, PHPUnit and exact-head CI are **UNVERIFIED**. No readiness uplift; V1 remains **NO-GO**.


### Sync credential rejection recovery — 2026-11-10

- Client now persists an explicit re-pair instruction after HTTP 401/403 from push, pull, bootstrap or attachment upload. Existing pending mutations and local credentials are preserved until the owner-authorised pairing transition; no silent queue deletion occurs.
- Chromium regression source verifies a 401 push response leaves the queued mutation and token intact while recording the recovery message.
- Source/E2E structural review only; Playwright and exact-head CI remain **UNVERIFIED**. Device lifecycle acceptance remains open. No score uplift; V1 remains **NO-GO**.


### Device re-pair recovery and operator warning — 2026-11-10

- Settings now exposes the persisted re-pair-required state and explicitly warns against clearing browser storage while local work may be pending.
- Re-pairing preserves the business-scoped local workspace when mutation records or dirty state exist, retains the queue, binds only a token issued for the active business, and defers bootstrap replacement until queue reconciliation is safe.
- Regression source covers the visible warning, HTTP 401 queue preservation and a re-pair with temporary push outage. Execution remains **UNVERIFIED**; full device/browser acceptance remains open. No score uplift; V1 remains **NO-GO**.

- Cross-business re-pair rejection now has regression coverage asserting the wrong-business token is revoked and local queued data remains intact; test execution is **UNVERIFIED**.

- Cross-business token cleanup only reports automatic revocation when the revoke endpoint confirms success; failed cleanup directs the owner to revoke through Trusted phone access. Source verified; runtime remains unverified.


## Backup private-storage copy failure guard — 2026-11-10

A concrete backup integrity defect was fixed: the backup command previously ignored a false return from `File::copyDirectory()`, allowing archive creation to continue after private media failed to copy. It now aborts before checksum/archive publication and reports the failure. Added a regression that simulates copy failure and asserts no archive is published.

Commits: `3e5b9c7` (guard), `cf332c2` (regression). Source re-fetch/structural review: PASS. PHPUnit and exact-head CI: **UNVERIFIED** (no status contexts or workflow runs returned for exact SHA `cf332c246b001fcb5547e28b5b5d904088471368`). No evidence-maturity uplift; actual operator restore drill remains a release gate.


## True browser-disconnected customer acceptance coverage — 2026-11-10

Added a Chromium Playwright scenario that establishes service-worker control and warms the customer-create route while online, switches the browser genuinely offline, reloads the cached form, creates a customer through the normal UI, then reloads again offline and checks that both the queued mutation and local customer survive. This closes a gap between origin-unreachable fallback coverage and a real browser-offline mutation scenario.

Commits: `c88ace2`, `236d4f6`. Source/test structural review only; Playwright execution and physical installed-device acceptance remain **UNVERIFIED**. No evidence-maturity uplift; V1 remains NO-GO.


### Offline browser acceptance CI gate — 2026-11-10

The Laravel GitHub Actions workflow now includes a separate `offline-browser-acceptance` job. It provisions PHP/Node dependencies, builds assets, seeds the demo business in a clean SQLite database, installs Chromium and runs the full `e2e/pwa-offline.spec.js` suite in the Chromium project. This closes the automation-configuration gap; it does not constitute a test pass.

**Status:** workflow committed as `3944745847842d94d55337327fe38cf154791316`; first execution pending. Exact-head browser runtime remains **UNVERIFIED**. No evidence-maturity uplift until the workflow completes successfully. V1 remains **NO-GO**.


### Sync stream input boundary — 2026-11-10

A sync device bearer token could request arbitrary stream names. Because pull/acknowledgement create per-device cursors, this permitted unbounded cursor-row creation despite the mutation recorder currently publishing only the `business` stream. The protocol now rejects names outside the declared supported list before database cursor operations. A feature regression covers both pull and acknowledgement and asserts no cursor is created.

Commits: `4f4f9f63e617b201fb68ab612ef902805c8e1727`, `a281567ab1c9b384dffe47405d6632fa8c16595f`.

**Status:** implemented and source re-fetched; runtime tests/CI are **UNVERIFIED**. No readiness score uplift. V1 remains **NO-GO**.


## Offline sync delivery/recovery hardening — 2026-11-10

- Implemented producer-side and publisher-side enforcement of the declared `business` sync stream. Unsupported stream names fail before mutation/counter creation and cannot be published.
- Closed the revoked-destination publication race by rechecking the destination's business and active state while holding its row lock.
- Tightened acknowledgement to advance only across pending deliveries. Sequence validation and cursor/delivery changes remain atomic.
- Added three regressions: unsupported recorder stream does not allocate rows; a destination revoked between the initial scan and lock is skipped; a non-pending delivery cannot advance the cursor.
- Source changes: `9334fc3`, `2670fee`; regression commit: `d138701`.
- Re-fetched current-main source/test blobs and confirmed all guards and regression methods are present.
- Exact-head automated offline acceptance is **PENDING**. The workflow has a `push: main` trigger and the dedicated Chromium job targets `e2e/pwa-offline.spec.js`; source configuration is not a passing run.
- Commit status lookup currently returns no status records. The connected commit-run lookup is pull-request-only; no workflow-dispatch action is exposed. Do not mark PHPUnit or Playwright as passed until exact-head run evidence exists.
- No score uplift. Commercial release remains **NO-GO** pending completed automated and operator/device recovery evidence.



## Automated sync/offline acceptance — PASS — 2026-11-10

- Tested source: `31fd732952a36ae99638ec8975706e95aa180c99` on `main`.
- Laravel feature suite: **438 passed / 3,046 assertions** (Actions run `42824939605`).
- Dedicated Chromium offline acceptance: **18 passed / 0 failed**, 56.5 seconds (job `129859701759`). Coverage includes a true browser-disconnected customer save and reload persistence, sync credential rejection/re-pair recovery, conflict persistence/recovery, logout/session transitions, offline navigation and workspace preservation.
- Sync delivery hardening regressions executed as part of Laravel suite: unsupported producer stream rejection/no sequence allocation; destination revocation recheck before delivery creation; acknowledgement blocked across a non-pending sequence.
- Supporting exact-head gates passed: PHPMD (`42824939822`), quality + secret scan (`42824939746`), Psalm (`42824939755`), populated upgrade and rollback (`42824939920`).
- Test-fixture-only corrections: E2E job uses isolated array cache to prevent repeated sign-ins sharing a rate-limit bucket; recovery assertion matches current operator copy; browser business ID is passed explicitly from page context into the Node route stub. Production authentication throttling and user-facing recovery copy were not changed.
- Evidence status: **AUTOMATED OFFLINE ACCEPTANCE = PASS**. Physical phone/browser installation and operator restore drill remain **OPEN**. No score uplift; commercial V1 remains **NO-GO**.



## Sync delivery and recovery boundary — 2026-11-10

- Closed source-level gaps in recorder-side stream validation and destination-device revocation during delivery publication.
- Acknowledgement now counts only pending delivery rows in its contiguous sequence range; cursor/delivery updates remain transactional.
- Regression source covers unsupported stream rejection before sequence allocation and a device revoked after initial destination selection.
- Exact-head GitHub Actions execution is pending. The offline-browser acceptance job exists in `.github/workflows/laravel.yml`; status must be read from the run for the resulting head.
- No readiness score uplift. V1 remains **NO-GO** until automated offline acceptance and remaining installed-device / operator recovery gates pass.


## Sync pull sequence-gap integrity — 2026-11-10

- Fixed a pull/acknowledgement contract mismatch: pull previously omitted non-pending rows and could return later pending deliveries across a failed sequence, while acknowledgement correctly rejected the gap.
- Pull now returns only a contiguous pending prefix from the current cursor; a gap or non-pending row blocks later delivery exposure until recovery is resolved.
- Regression source covers failed sequence 1 followed by pending sequence 2 and asserts no later mutation is returned and the cursor does not advance.
- Source inspection only. PHPUnit, concurrency runtime and exact-head CI remain **UNVERIFIED**. No score uplift. V1 remains **NO-GO**.


## Receiver-specific sync acknowledgement — 2026-11-10

- Fixed the sequence-domain mismatch between business mutation ordering and per-device delivery acknowledgement.
- Server pull payload now exposes `delivery_sequence`; phone acknowledgement uses that value instead of `mutation.sequence`.
- Regression models a destination's own mutation consuming business sequence 1, followed by a remote mutation at business sequence 2 delivered as receiver sequence 1. It verifies pull metadata and successful cursor acknowledgement.
- Source re-fetch/structural checks only. PHPUnit, browser runtime and exact-head CI remain **UNVERIFIED**. No score uplift; V1 remains **NO-GO**.


## Lost acknowledgement response retry — 2026-11-10

- Confirmed the acknowledgement endpoint is idempotent for a retry at or below the persisted device cursor.
- Added a regression simulating the client retry after an acknowledgement response is lost; it checks the cursor remains stable and the delivery is applied once.
- This is server-side retry-safety coverage. It does not replace the phone-level interrupted-network acceptance test.
- Exact-head PHPUnit/CI remains **UNVERIFIED** until GitHub reports the run. No score uplift; V1 remains **NO-GO**.

## Attachment upload/logout race — 2026-11-10

- Found a late-response race: an attachment POST could complete after local logout cleanup and persist its response into the cleared business attachment database.
- Added a monotonically increasing local session generation. Uploads capture the generation and paired token; response handling and attachment-record writes refuse to persist when logout/session transition has invalidated that generation.
- Added Chromium regression: hold the attachment upload response, complete local logout and remote token revocation, release the response, then assert the business attachment store remains empty.
- Commits: `20dd2d7d0fb2df7028675ed321c4ee0ea0b1c2cc` (fix), `5fc554809c0ae51ab5b998a6e6710a271423955c` (browser regression).
- Source and test are committed on `main`. Exact-head Playwright/Laravel/quality execution is **PENDING**; this is not yet a verified runtime pass. No readiness score uplift. V1 remains **NO-GO**.

## Private attachment cache/logout race — 2026-11-10

- Extended the session-generation boundary to remote attachment listing and private-file downloads. A stale list/download response is now discarded after logout or token replacement, and private attachment cache writes use the captured session generation.
- The upload-completion/logout race has a dedicated Chromium regression. The download-completion/logout guard is implemented but does not yet have its own runtime regression.
- Source committed on `main`; exact-head Laravel/Playwright/quality execution remains **PENDING**. No readiness score uplift. V1 remains **NO-GO**.

## Re-pair session transition — 2026-11-10
- Closed a credential-boundary gap in the phone sync lifecycle: successful re-pair now invalidates old attachment operations, aborts the old sync request, and guarantees a fresh sync pass is not lost behind the single-flight lock.
- Added delayed old-token push regression proving the intended sequence at test-source level: old request in flight → new pairing → old session aborted → queued mutation retried with new token.
- Commits: `2b3828c4a49b03e7e2823559e60a3453781f7d47` (source), `7fbf4ed933c28872b5a4cd8cb12c81eced2e25b6` (test), `d3e44ec438cb59de4da95a74895c02ee9e3d4435` (test fixture).
- Source/test syntax checks pass. Runtime Playwright, Laravel, quality and exact-head CI remain **UNVERIFIED**. This is not release acceptance and does not change the V1 **NO-GO** disposition.

## Private attachment download/logout regression — 2026-11-10
- Added a delayed private-file response regression to cover the download/cache path that previously had source-level guards but no dedicated browser test.
- The fixture holds the private file response, completes local logout, releases the response, and asserts the business attachment store remains empty.
- Commit: `9c8dd6147112814773d551ae5b959d4e2af48dad`.
- Syntax/source checks only. Playwright runtime and exact-head CI remain **UNVERIFIED**. V1 remains **NO-GO**.

## Cross-tab device session replacement — 2026-11-10
- Closed a multi-tab session consistency gap. A successful pairing now broadcasts a non-secret session-replaced signal after persistence; peer tabs abort old sync work, reload the durable business-scoped token and resume with the current credential.
- Cross-tab logout also invalidates the receiving tab's session generation and active sync controller before cleanup.
- Added a two-tab Chromium regression proving a tab that loaded the old token adopts the new token and authenticates its next sync request with the replacement token.
- Commits: `a0ef99a40769b758582fd07da7f53804ee08cada` (source), `e5e9f5befe694e85cbdff3299ca393c4494ff5d1` (test), `192edbe6529203aab7d07fc86cd595ce2e46df80` (fixture).
- Syntax/source checks only; browser runtime and exact-head CI remain **UNVERIFIED**. No readiness uplift; V1 remains **NO-GO**.

## Cross-tab stale-write fencing — 2026-11-10
- Hardened the cross-tab replacement boundary against late IndexedDB writes from a stale tab. State persistence, offline mutation enqueue, sync acknowledgement application and conflict resolution now compare the durable token before committing changes.
- Only the pairing path may intentionally replace the durable token; it does so before broadcasting the non-secret session-replaced signal.
- Added regression assertions that a tab forced back to an old in-memory token cannot persist stale state or enqueue a mutation, and that the durable replacement token remains unchanged.
- Commits: `74871c891a4e012affc88c50485e4542057062da`, `aea04e1e9929bc6fc465cc22b8319adecaf001ba`, `d6a897370d592e2bafed8888ee064e55c1887361`, `dea69a2237200ad1393389bf521d8f6f9fd6b46a`.
- Syntax/source checks only; browser runtime and exact-head CI remain **UNVERIFIED**. No readiness uplift; V1 remains **NO-GO**.


## Trusted-device lifecycle concurrency hardening — 2026-11-10

- Added a conditional active-token/expiry check to the middleware activity refresh, so a revoke that lands between initial lookup and refresh blocks the request instead of extending a stale credential.
- Owner revocation now serializes on the device row and writes its audit transition once. Device-originated revocation is transactional and clears all sync/pairing credential metadata.
- Provisioning now filters expired pairing codes before taking row locks; regressions cover the lookup/revoke race, idempotent owner audit, self-revoke credential cleanup and expired code rejection.
- Source and regression assertions are committed on main; exact-head Laravel/quality/browser execution remains PENDING. Do not raise readiness from source coverage alone. V1 remains NO-GO.


## Revocation fencing at mutation record/apply boundaries — 2026-11-10

- The recorder now rechecks and locks the durable active device and matching credential inside the mutation-creation transaction, before sequence allocation.
- The applier rechecks and locks the durable active device before the domain handler runs. Push preserves 401 rather than swallowing it as a per-mutation failure.
- Regression cases cover revocation after middleware snapshot but before record, and revocation after record but before apply. Source-level reconciliation completed; runtime/PHPUnit/exact-head CI are still **PENDING**. V1 remains **NO-GO**.


## Quality-gate follow-up — 2026-11-10

- PHPMD found a push-method length regression and two oversized test classes. The controller guard was extracted into a private method; tests were moved into focused classes without removing assertions or disabling rules. A PHPMD parser error caused by escaped namespace separators was corrected.
- Latest exact-head workflow verification is still queued/pending; no green claim until PHPMD, Laravel, quality, Psalm and browser results are observed. V1 remains **NO-GO**.


## Offline session fixture reconciliation and CI evidence — 2026-11-10

- Corrected eight E2E fixture paths that seeded a replacement sync token using ordinary persistence; they now use the explicit session-replacement path and preserve the production compare-before-write guard. The conflict retry/discard fixture persists its new session before queueing mutations.
- Exact-head `c021eb2` evidence: Laravel **446 passed / 3,083 assertions**; offline browser acceptance **18 passed**; PHPMD, quality/secret scan, Psalm, populated upgrade/rollback and Director contract lint **success**. The separate browser smoke suite was still running at the time of this record.
- This is verification progress, not release acceptance. V1 remains **NO-GO** pending final browser smoke and full current-head verification.


## Commercial/legal baseline — 2026-11-10

- Owner supplied current solo-operator identity and private contact channels. Public repository intentionally excludes the supplied residential address and direct contact details pending approval of the exact public-facing service address/contact fields.
- R99 per business/workspace/month is the initial target; 14-day advanced-feature trial is a proposed default. Expiry downgrades to Basic without lockout or data deletion. Basic/Advanced feature matrix, VAT, storage quota and payment disclosures are still open.
- Hosted and local/offline models are both intended. Customer content remains customer-owned. Hosted processing requires appropriate POPIA operator terms where applicable; local installations require a distinct responsibility/support boundary.
- xneelo Cloud Johannesburg is the preferred low-cost hosted pilot shortlist, not yet contracted or provisioned. Indicative 2 vCPU / 4 GB + 10 GB boot + 50 GB NVMe data volume is approximately R306/month at currently published prices. Single availability zone; external/offsite backup and owner-operated server management remain necessary.
- Retention draft now separates tax records (SARS commonly five years, subject to exceptions) from Companies Act s24 company records (generally seven years for records covered by that section). No blanket five/seven-year retention is imposed on all personal information. Proposed account closure/export, log and backup periods are documented but require legal/accounting review and implementation.
- Documentation progress only. No compliance certification or launch clearance is claimed. V1 remains **NO-GO**.


## Private uploaded-image offline cache correction — 2026-11-11

The service worker previously required `Request.destination === 'image'` before caching private profile/business media. This metadata is not dependable across every image-loading path, notably CSS background images. The private-media route matcher now keys off the authenticated `/media/profile/*` and `/media/business/*` routes instead. Media remains in the active user-and-business cache, never the shared static cache. Cache generation advanced to v33; a worker regression now covers an empty request destination. Commits: `ad81391`, `87cb501`, `19ca15d`. Source committed; automated runtime and physical-device verification remain pending. No readiness uplift; V1 remains **NO-GO**.
