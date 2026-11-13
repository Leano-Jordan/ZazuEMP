# Zazu EMP — Director Full-Scale Software Audit

**Audit date:** 2026-11-13  
**Repository:** Leano-Jordan/ZazuEMP  
**Branch audited:** main  
**Exact source / CI head:** 909d002587358f47a09b069f7fb84bc9449f1076  
**Audit type:** repository-wide source review, architecture and control review, CI evidence reconciliation, V1 and commercial readiness assessment  
**Overall evidence-maturity score:** **71/100 (53/75)**  
**Technical V1 disposition:** **NO-GO — current exact-head CI is red and physical installed-device acceptance is still open**  
**Paid commercial launch disposition:** **NO-GO — issuer/key custody, approved feature matrix, legal/privacy and brand clearance remain open**

---

## 1. Executive assessment

Zazu EMP is a substantial Laravel modular-monolith application with connected operational workflows, tenant-scoped business records, purchasing and stock foundations, commercial documents, payment records, a bounded offline client, synchronization controls, private attachments, and a non-trivial automated test system.

This is not a prototype that needs a rewrite. The core product direction and architecture are coherent. The remaining work is controlled release closure, not feature expansion.

The immediate engineering blocker is the current main-branch verification state:

- Laravel: **472 passed, 7 failed, 1 skipped** (3,323 assertions).
- The seven failures are concentrated in stale or contradictory visual-system tests. Several still assert a retired blue palette; others look for design tokens in the component stylesheet even though the current architecture deliberately places those tokens in app.css.
- PHPMD fails on two test-maintainability thresholds.
- Browser regression: **86 passed, 46 skipped, 0 failed**.
- Dedicated offline browser acceptance: **22 passed**.
- PostgreSQL migration/registration/recovery, populated upgrade/rollback, Psalm security scan, Director contract lint and secret scan all completed successfully.
- SonarCloud was skipped and is not counted as a pass.

The red Laravel suite is a real release gate even though the failing tests appear stale. A test contract cannot be ignored just because the application implementation is newer. The correct correction is to reconcile tests to the currently approved stylesheet authority and palette—not to reintroduce an old palette merely to make tests green.

The audit score measures the maturity of engineering evidence, not the percentage of features implemented. A score above 70 does not override a failed release gate.

## 2. Scope, evidence and limitations

Reviewed the current GitHub main branch at the exact SHA above, including:

- Application routes, controllers, middleware, models, services and business-support classes.
- Database migration inventory and the relational business ownership model.
- Offline browser client, IndexedDB persistence, service worker, sync API, conflict and mutation handling.
- Licensing verifier and entitlement resolution.
- Backup and restore commands, PostgreSQL backup support and restore validation paths.
- Vite/Tailwind manifests, application stylesheet load order and current CSS token authority.
- PHPUnit/feature tests, JavaScript configuration tests, Playwright suites and GitHub Actions workflows.
- Director state and readiness records, offline architecture, known limitations, licensing specification, commercial operating profile, security audit, legal/compliance register, dependency licence inventory and brand-clearance register.

**Not performed:** an independent penetration test, a legal opinion, a production-host inspection, a live customer-data review, or physical phone/tablet testing. The owner’s local machine and ignored local .env file are not available through the repository. No claim is made that those environments were inspected.

This audit is therefore a source-and-evidence audit, not a certification of a running production installation.

## 3. Scorecard

Scoring scale: 0 = absent; 1 = documented/design only; 2 = partial implementation or incomplete evidence; 3 = implemented with meaningful but bounded verification; 4 = strong implementation with recent automated evidence; 5 = repeatedly proven in real customer/production operation.

| Audit domain | Score | Assessment |
|---|---:|---|
| Architecture and code organisation | **3/5** | Coherent Laravel modular monolith with distinct domain services and a clear relational business model. Several source files and CSS layers are oversized and increase regression/maintenance cost. |
| Identity, onboarding and workspace context | **4/5** | Registration, login, recovery, membership and business context are present and covered by feature/browser tests. |
| Core customer → event/Work lifecycle | **4/5** | Connected customer and Work lifecycle, requirements, preparation and lifecycle guards are materially implemented and tested. |
| Quotes, acceptance and financial workflow | **4/5** | Quote versions, acceptance, invoice handoff, payments, expenses, tax snapshots and reconciliation have meaningful implementation and regression coverage. |
| Purchasing, receiving, inventory and resources | **4/5** | Supplier, purchase-order, partial receiving, inventory movement, assets, allocation and costing foundations are present. |
| Data integrity and auditability | **4/5** | Transactions, row locks, mutation identity, idempotency, conflict handling, parent/child ownership and audit records are evidenced in source and tests. |
| Authorization and tenant isolation | **4/5** | Role permissions and business context middleware are present; bound records declaring business_id are checked against the active business. Security regression coverage is substantial. |
| Application security controls | **4/5** | Nonce-based CSP, restrictive style attributes, request/security headers, rate limits, hashed sync tokens, input bounds and passing Psalm evidence. Not an independent penetration test. |
| Offline continuity and synchronization | **3/5** | Device-local IndexedDB workspace, durable mutation/attachment queues, service-worker cache, pairing, replay/conflict handling and reconnect paths exist. Scope is explicitly bounded; physical device proof remains open. |
| Documents and private attachments | **4/5** | Private storage, attachment lifecycle and offline attachment queue/reconnect handling have implementation and automated regression coverage. |
| Search, reporting and Zazu Helper | **4/5** | Business-scoped reports/search and permission-aware Helper attention have targeted regression coverage. Helper remains contextual guidance, not an autonomous operator. |
| UX, responsive layout and accessibility | **3/5** | Browser suite is green and layout/accessibility contracts exist. Current PHP visual tests are inconsistent with the current token authority; installed hardware acceptance remains outstanding. |
| Test system and CI reliability | **2/5** | Broad coverage and multiple useful gates exist, but exact-head Laravel and PHPMD are failing. Skipped tests and conditional SonarCloud are not passes. |
| Backup, recovery and upgrade safety | **4/5** | Backup/restore commands include archive/path/checksum validation and rollback safeguards. PostgreSQL recovery and populated upgrade/rollback jobs pass in CI. |
| Licensing, commercial controls, privacy and brand | **2/5** | Safe Basic fallback and signed-token verification foundations exist. Production issuer/key custody, installation identity/reactivation, approved paid feature matrix, final legal/privacy operating controls and brand clearance remain incomplete. |

**Total: 53/75 = 70.7%, reported as 71/100.**

No domain is scored 5/5. The main reason is that CI and browser emulation are not the same as repeated use and recovery on real customer installations.

## 4. Exact-head CI evidence

All results below belong to the current application/test head 909d002587358f47a09b069f7fb84bc9449f1076 unless stated otherwise.

| Check | Result | Evidence |
|---|---|---|
| Laravel test workflow | **FAIL** — 472 passed / 7 failed / 1 skipped | [Run 43443797721](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43443797721) |
| PostgreSQL migration, registration and recovery job | **PASS** | [Run 43443797721](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43443797721) |
| Offline browser acceptance | **PASS** — 22 passed | [Run 43443797721](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43443797721) |
| Full browser regression | **PASS** — 86 passed / 46 skipped / 0 failed | [Run 43443797695](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43443797695) |
| Zazu quality + secret scan | **FAIL overall** — application test job failed; secret scan passed | [Run 43443798059](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43443798059) |
| Psalm security scan | **PASS** | [Run 43443797968](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43443797968) |
| PHPMD | **FAIL** — two test-code thresholds | [Run 43443797722](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43443797722) |
| Populated database upgrade/rollback | **PASS** | [Run 43443797716](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43443797716) |
| Director contract lint | **PASS** | [Run 43443798079](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43443798079) |
| Director health report | **PASS as a report-generation job**; report itself flags current-head CI failure | [Run 43445061479](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43445061479) |
| Push-on-main workflow | **PASS** | [Run 43443797189](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43443797189) |
| SonarCloud | **SKIPPED** — not a pass | [Run 43443797890](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43443797890) |

The Laravel job also completed the frontend build, Vite LAN asset-origin test and built asset-manifest verification before PHPUnit failed. The dedicated offline browser job passed 22 tests. The separate full browser suite passed 86 tests; its 46 skips remain skips and are not included in the pass count.

### 4.1 The seven Laravel failures

The failures are in the following test contracts:

- InterfaceRegressionTest — commercial visual system.
- UiAccessibilityTest — shell/surface theme contrast.
- UiDashboardResponsiveTest — topbar/title/theme palette.
- UiProportionFoundationTest — canonical visual token palette.
- UiProportionFoundationTest — product-wide visual authority.
- UiShellThemeRegressionTest — light/dark blue shell palette.
- UiVisualSystemRegressionTest — authenticated module surface palette.

Source inspection confirms that app.css currently owns the single canonical light/dark token authority. The current palette includes light values such as page #DCE5ED, sidebar #D5E0E8 and primary #426987, with dark values held in the same canonical stylesheet. The shared layout loads app.css followed by the responsive, mobile, visual-sweep, dashboard and product component stylesheets.

The failing tests still include previous values such as #F5FBFE, #8ED8F8, #159DD2 and #17445F, or search for canonical token declarations inside zazu-product-authority.css even though the current contract explicitly says that file must not own root tokens. The newer visual-system test itself asserts the current palette in app.css, then later repeats token-presence assertions against the component extension file. That is internally inconsistent.

**Finding:** this is a stale/contradictory test-contract regression, not confirmed evidence that the rendered app has lost its CSS. The asset build/manifest and browser suite passed. Still, the test suite must be corrected and made green before release certification.

### 4.2 PHPMD findings

PHPMD reports:

1. ExperienceLevelTest.php:15 — TooManyPublicMethods; 11 public methods, threshold under 10.
2. UiVisualSystemRegressionTest.php:171 — ExcessiveMethodLength; one test method is 101 lines, threshold 100.

These are test-maintainability issues, not reported production logic defects. Correct the test structure; do not suppress the rule simply to obtain a green check.

## 5. Principal findings and risk register

### AUD-01 — Current main verification is red

**Severity: Release blocker**

The latest exact-head Laravel suite has seven failures and PHPMD has two findings. The failures appear to arise from stale visual assertions rather than a missing CSS bundle, but a red suite remains a release blocker. Reconcile the tests against the current canonical palette and stylesheet ownership, then rerun every required check on one exact source SHA.

### AUD-02 — Real installed-device offline acceptance remains unproven

**Severity: Release acceptance gate**

The browser evidence is meaningful: offline browser acceptance passed 22 tests and the full browser regression passed 86 tests. It does not prove the installed PWA works on the intended physical phone/tablet and trusted HTTPS origin across force-close, relaunch, local persistence and reconnect.

The remaining owner acceptance should stay bounded to the documented workflow: install/open on the chosen secure origin, exercise the supported customer-to-Work local flow, force-close and relaunch while disconnected, then reconnect and verify the queued mutation synchronises exactly once. Do not expand this into a claim of full offline parity or disconnected multi-device sync.

### AUD-03 — Public repository contains proprietary source

**Severity: High owner decision; not an application vulnerability**

GitHub currently exposes the repository publicly. The repository's LICENSE file states that Zazu EMP is proprietary and all rights are reserved. A proprietary licence notice restricts reuse; it does not make publicly visible source confidential.

This may be deliberate. The owner should explicitly confirm that public source visibility is intended. If confidentiality is intended, repository visibility needs to be private. No visibility change was made during this audit.

### AUD-04 — Paid licensing is not yet full commercial enforcement

**Severity: Commercial launch blocker; not a reason to lock out Basic users**

The current entitlement implementation verifies signed Ed25519 tokens locally, binds them to a business, validates dates/status, rejects older renewals, serializes imports and records a safe local entitlement. BusinessEntitlement applies a 30-day Advanced trial and falls back to Basic when a trial expires or an installed entitlement becomes inactive.

The implementation enforces the experience-level ceiling only. It does not yet provide:

- Production issuer workflow and private signing-key custody.
- Installation identity and safe reactivation.
- An approved module-by-module feature/usage matrix.
- Enforced paid module boundaries and tested downgrade behaviour.

The owner-confirmed commercial profile defines Basic as ongoing Free continuity, Intermediate and Advanced as paid, and the exact paid feature matrix/pricing as not yet approved. Expiry must not lock the owner out, remove access to customer data, or silently begin paid billing.

**Director rule:** do not invent limits or feature restrictions. Keep the three-tier matrix as an explicit commercial dependency until approved, then implement and test only those approved boundaries.

### AUD-05 — Trusted offline device is a business-level trust boundary

**Severity: Medium accepted architecture constraint**

The offline IndexedDB workspace is keyed by business, while the service worker's authenticated page cache is scoped to user and business. This reflects the documented owner-approved shared business-device model. A user with access to a paired device/browser may be able to use the business data copied to that device even if their normal server role is narrower.

Device revocation prevents future authenticated sync; it cannot guarantee remote deletion of data already downloaded to a device. Owner pairing consent, physical custody, screen lock, device inventory and lost-device revocation procedures therefore matter. This is a known architecture constraint, not a newly discovered code defect. Do not silently change the offline database identity or destroy local queues.

### AUD-06 — Secret scan is not a full-history clearance

**Severity: Medium security assurance gap**

The latest Gitleaks workflow reported no leaks, which is useful evidence for the scanned change. Its logged invocation uses git log options limiting the scan to the latest commit. That does not prove the entire repository history is free of accidentally committed secrets.

Run a controlled full-history scan and establish a recurring history-aware process. No secret exposure was confirmed by this audit.

### AUD-07 — Legal, privacy and brand controls are still pre-launch work

**Severity: Commercial launch blocker**

The repository contains a legal/compliance control register, privacy and retention procedures, release documentation and a brand-clearance register. These are useful preparation, not proof of legal compliance or completed professional review.

The Zazu brand register explicitly remains open and calls for official trade-mark search and appropriate clearance. Customer terms, privacy roles/notices, retention/disposal, operator arrangements and incident responsibilities still require owner and qualified professional review before commercial launch.

### AUD-08 — Maintainability debt is concentrated in a few large files

**Severity: Medium engineering debt**

The source contains useful domain separation, but the inspected snapshot includes an 1,811-line SyncController, a 676-line restore command, an 8,281-line app.css and a 6,642-line visual-sweep stylesheet. The sync controller suppresses complexity/public-method rules, and the visual styles are spread across several authority layers.

This is not a reason for a V1 rewrite. It increases the cost of future changes and makes the CSS ownership contract especially important. Keep future extraction incremental and tied to defects or measurable change risk.

### AUD-09 — Repository packaging identity is generic

**Severity: Low**

composer.json still identifies the package as laravel/laravel. This does not affect runtime behaviour, but it is unfinished product packaging metadata for a proprietary Zazu EMP application.

## 6. Positive controls confirmed

- The application is organised around a business ownership spine and domain-specific services rather than a collection of disconnected mock screens.
- Active business context and route-bound business ownership checks reduce accidental cross-tenant record exposure.
- Permission checks are server-side and separate from experience/tier presentation preferences.
- Sync tokens are hashed at rest, expire after 180 days of inactivity and are checked against persisted active/revocation state.
- Sync mutations use transactions, business/record locks, identity handling and conflict controls.
- Current CSP uses a per-response nonce, blocks style attributes, and does not rely on unsafe-inline. Production HSTS is added on secure requests.
- The backup/restore implementation includes archive validation, path/symlink checks, checksums, database-driver matching and pre-restore rollback handling.
- PostgreSQL migration/registration/recovery and populated upgrade/rollback CI passed on the audited head.
- The Vite LAN asset-origin configuration test and asset-manifest verification passed on the audited head.
- The current licence fallback protects continuity: an expired or unsupported entitlement resolves to Basic rather than locking the business out of customer data.

## 7. Release priorities

### Priority 0 — Restore trustworthy green CI

1. Reconcile the seven visual-system test failures with the current canonical token values and stylesheet ownership.
2. Keep app.css as the single token authority; do not put root token declarations back into component extension files.
3. Correct the two PHPMD test-structure findings without blanket suppressions.
4. Rerun Laravel, Zazu quality, PHPMD, Psalm, browser regression, offline acceptance, PostgreSQL smoke, populated upgrade/rollback and Director checks against one exact SHA.
5. Record the precise pass/skip/fail counts. Do not treat skipped tests or conditional SonarCloud as passes.

### Priority 1 — Complete bounded V1 acceptance

1. Record the physical installed-device offline workflow on the approved secure origin.
2. Verify local persistence across force-close/relaunch and duplicate-safe reconnect synchronisation.
3. Record any limitation that is outside the agreed offline boundary; do not imply full offline parity.
4. After CI and device acceptance pass, issue the final technical V1 release decision.

### Priority 1 — Owner decisions

1. Confirm whether public GitHub visibility for proprietary source is intentional.
2. Approve the final Basic Free / Intermediate / Advanced feature matrix and downgrade behaviour before paid feature enforcement.

### Priority 2 — Commercial and assurance closure

1. Implement production licence issuance/key custody and installation identity/reactivation once the approved commercial model is ready.
2. Complete legal/privacy/terms and operating-procedure review with appropriate professional input.
3. Complete official Zazu trade-mark clearance and record the decision.
4. Run full-history secret scanning and establish recurring history-aware checks.
5. Refactor the largest controllers and stylesheet layers incrementally, without broad UI churn.

## 8. Explicitly not release blockers under the current frozen scope

The following remain known limitations or later commercial phases, not reasons to expand V1 now:

- Full offline parity across every screen.
- Multi-device peer synchronisation while every device is disconnected from the server.
- Autonomous Zazu Helper actions.
- Advanced forecasting, predictive finance and enterprise BI.
- Staff assignment and scheduled staff reminders that belong to a later team-management phase.
- Production host selection and production deployment exercises before the actual target is selected.

These boundaries are recorded in the current offline architecture, known-limitations and Director readiness documents. A new blocker must be a reproducible defect in an agreed V1 workflow, a demonstrated security/privacy/data-integrity failure, or an explicitly owner-approved requirement.

## 9. Final Director decision

**Technical V1: NO-GO today.** The product foundation is strong, but exact-head CI is red and the bounded physical-device acceptance is not yet recorded. The current CSS failures should be corrected in the tests, not “fixed” by rolling the interface back to an obsolete palette.

**Paid commercial launch: NO-GO today.** Basic Free continuity is the correct fallback and must remain intact. Paid module enforcement, production licence issuance/key custody, legal/privacy closure and Zazu brand clearance are not complete.

**Recommended execution order:** make the test contract truthful, make the quality gates green, complete the single bounded real-device acceptance, then issue the release decision. Do not add speculative features or broaden the offline promise.

**Audit change boundary:** this audit adds documentation only. No application runtime, database, customer data, deployment settings, Render resources or deploy/render-staging branch were changed.
