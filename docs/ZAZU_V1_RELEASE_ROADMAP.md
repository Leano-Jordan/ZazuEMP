# Zazu EMP — V1 Commercial Release Roadmap

**Status:** LIVE  
**Last Director cycle:** 2026-10-01  
**Repository:** `Leano-Jordan/ZazuEMP`  
**Branch:** `main`  
**Application HEAD assessed:** `c83474e1e32ca8c29950eac3723b854c163e5786`  
**Release-control docs commits:** documentation-only commits may follow and do not change the application assessment baseline.  
**Evidence rule:** Implemented ≠ Verified ≠ Proven.

## Director operating rule

Every cycle follows:

**INSPECT → AUDIT → PRIORITISE → EXECUTE → VERIFY → RE-AUDIT → UPDATE ROADMAP**

A requirement is **🟢 VERIFIED COMPLETE** only when it is implemented, verified, regression-checked and supported by recorded evidence.

### Status legend

- ⬜ NOT STARTED
- 🟡 IN PROGRESS
- 🟢 VERIFIED COMPLETE
- 🔴 BLOCKED
- 🔵 REGRESSION
- ⚪ N/A

---

# Phase 0 — Release control and evidence

| ID | Area | Requirement | Status | Evidence | Risk | Next Action |
|---|---|---|---|---|---|---|
| CTRL-01 | Release control | Treat docs/V1_RELEASE_CHECKLIST.md as the single authoritative V1 release-gate ledger | 🟢 VERIFIED COMPLETE | Checklist rewritten on current main HEAD and explicitly designated canonical | Low | Update checklist every Director cycle; keep roadmap as supporting strategy/history |
| CTRL-02 | Repository truth | Inspect branch and HEAD before every execution | 🟢 VERIFIED COMPLETE | Current branch main; current HEAD recorded in both roadmap and canonical checklist | Low | Repeat each cycle |
| CTRL-03 | Evidence discipline | Separate implemented/tested/verified/proven claims | 🟢 VERIFIED COMPLETE | Existing readiness register + release documentation | Low | Preserve distinction |
| CTRL-04 | Regression control | Stop improvement work when a regression is found | 🟢 VERIFIED COMPLETE | Director operating rule | Medium | Apply every batch |
| CTRL-05 | Release evidence | Current-head CI/browser/runtime evidence | 🟡 IN PROGRESS | Existing register rates Release Evidence 1/5; current audit records source inspection but not current runtime/browser execution | High | Execute runtime/browser verification on current HEAD |

# Phase 1 — Core operational workflow

| ID | Area | Requirement | Status | Evidence | Risk | Next Action |
|---|---|---|---|---|---|---|
| CORE-01 | Core workflow | Registration → Onboarding → Client → Event → Work → Resources → Purchasing → Costs → Finance → Completion is connected | 🟡 IN PROGRESS | Product spec/capability map and deep-dive show major domains implemented; populated end-to-end walkthrough remains outstanding | High | Run populated-data end-to-end traversal |
| CORE-02 | Authentication | Registration and authentication foundation | 🟡 IN PROGRESS | Existing implementation documented as present; fresh desktop/mobile runtime walkthrough outstanding | High | Runtime verify fresh account |
| CORE-03 | Onboarding | Registration enters services/catalogue setup, experience selection, business setup and dashboard | 🟡 IN PROGRESS | Deep-dive documents implemented sequence; fresh-account traversal unverified | Medium | Browser verify desktop/mobile |
| CORE-04 | Clients | Customer records and event relationship | 🟡 IN PROGRESS | Existing domain present; runtime chain proof outstanding | Medium | Include in end-to-end traversal |
| CORE-05 | Event lifecycle | Valid lifecycle transitions and completion/cancellation guards | 🟡 IN PROGRESS | Centralized transition service documented; Planned remains intentionally unresolved product decision | High | Verify transitions and dependent-state guards |
| CORE-06 | Work/process | Work, preparation, requirements and event linkage remain coherent | 🟡 IN PROGRESS | Operational chain implemented and visible; populated-data proof outstanding | High | Verify work → preparation → completion |
| CORE-07 | Resources | Resource/asset/inventory foundations remain connected to work | 🟡 IN PROGRESS | Existing foundations documented; runtime proof outstanding | High | Verify representative allocation/movement flows |
| CORE-08 | Purchasing | Purchase orders and receiving remain event/work/cost aware | 🟡 IN PROGRESS | Existing purchasing/receiving foundation; complete workflow verification outstanding | High | Verify create → status → receive → cost relationship |
| CORE-09 | Costs | Costs remain traceable to work and purchasing without duplicate authority | 🟡 IN PROGRESS | Existing event-cost foundation and operational chain visibility | High | Reconcile populated records |
| CORE-10 | Finance | Financial records remain consistent with commercial/operational state | 🟡 IN PROGRESS | Invoice/payment/expense safeguards documented; runtime reconciliation drill outstanding | Critical | Verify financial mutation and reconciliation paths |
| CORE-11 | Completion | Completion/cancellation cannot leave contradictory operational/procurement/financial states | 🟡 IN PROGRESS | Central lifecycle guard now blocks completion/cancellation with unresolved preparation, planned-cost, active-purchasing or active-finance states; populated-data walkthrough outstanding | Critical | Verify populated-data transition matrix |

# Phase 2 — Financial correctness and data integrity

| ID | Area | Requirement | Status | Evidence | Risk | Next Action |
|---|---|---|---|---|---|---|
| DATA-01 | Financial precision | Money/quantity precision is consistent across financial paths | 🟡 IN PROGRESS | Existing finance safeguards documented; complete path verification outstanding | Critical | Audit all money/quantity mutations |
| DATA-02 | Reconciliation | Invoice/payment/expense state reconciles without contradictory balances | 🟡 IN PROGRESS | Existing reconciliation/idempotency controls documented; runtime drill missing | Critical | Run reconciliation drill |
| DATA-03 | Parent/child integrity | Critical parent/child relationships reject invalid orphan/foreign-business states | 🟡 IN PROGRESS | Business isolation and domain protections documented; full verification outstanding | High | Adversarial integrity scan |
| DATA-04 | Concurrency | Critical mutations remain safe under concurrent requests | 🟡 IN PROGRESS | Transaction/idempotency protections documented; runtime/concurrency evidence incomplete | High | Verify critical mutation paths |
| DATA-05 | Historical snapshots | Commercial records preserve required historical/immutable state | 🟡 IN PROGRESS | Listed as release requirement; evidence incomplete | High | Inspect quote/invoice/version persistence |
| DATA-06 | Existing data | Representative populated database can migrate/upgrade safely | 🟡 IN PROGRESS | Explicit release gap in V1 status/checklist | Critical | Perform representative migration/upgrade drill |

# Phase 3 — Security and authorization

| ID | Area | Requirement | Status | Evidence | Risk | Next Action |
|---|---|---|---|---|---|---|
| SEC-01 | Authentication | Session/auth lifecycle is safe and failure-aware | 🟡 IN PROGRESS | Authentication foundation present; runtime verification outstanding | High | Runtime auth traversal |
| SEC-02 | Business isolation | Cross-business data cannot leak through direct routes/search/media | 🟡 IN PROGRESS | Permission-aware search and business context documented | Critical | Adversarial cross-business verification |
| SEC-03 | Permissions | Granular permissions cover all protected operations | 🟡 IN PROGRESS | Core permission foundation present; complete coverage remains open | Critical | Audit routes/controllers/actions |
| SEC-04 | Authorization | Server-side authorization is authoritative | 🟡 IN PROGRESS | OWASP-aligned server-side checks documented | Critical | Complete policy/permission audit |
| SEC-05 | Private media | Attachments/profile/business media enforce ownership and authorization | 🟡 IN PROGRESS | Secure attachment architecture exists; verification remains open | High | Direct-access adversarial checks |
| SEC-06 | Audit trail | Important mutations have continuous, attributable activity/audit evidence | 🟡 IN PROGRESS | Auditability foundation present; continuity remains release gap | High | Trace representative mutations end-to-end |
| SEC-07 | Privacy | Operational privacy/retention controls match actual product handling | 🟡 IN PROGRESS | Privacy baseline and legal register exist; operational verification/sign-off outstanding | High | Finalize release privacy controls |

# Phase 4 — Commercial workflow completion

| ID | Area | Requirement | Status | Evidence | Risk | Next Action |
|---|---|---|---|---|---|---|
| COMM-01 | Quotes | Customer-facing quote presentation/delivery | 🟡 IMPLEMENTED / VERIFICATION PENDING | routes/web.php signed quotes.public route and resources/views/quotes/public.blade.php exist at current HEAD | Critical | Browser-verify signed customer-facing quote journey |
| COMM-02 | Quotes | Quote acceptance lifecycle | 🟡 IMPLEMENTED / VERIFICATION PENDING | Signed quotes.public.accept route, QuoteController::publicAccept() and QuoteWorkflowTest exist at current HEAD | Critical | Verify acceptance transition, idempotency and resulting commercial state |
| COMM-03 | Deposits | Deposit lifecycle is tied to commercial state | 🟡 IMPLEMENTED / VERIFICATION PENDING | quote_versions stores deposit percent/amount; finance accepts deposit payment type; invoice/finance services reconcile required vs received deposit | High | Run populated quote → invoice → deposit reconciliation drill |
| COMM-04 | Invoices | Complete invoice/document workflow | 🟡 IMPLEMENTED / VERIFICATION PENDING | Invoice creation/show/store path and quote snapshot linkage exist at current HEAD | Critical | Verify invoice creation → document presentation → payment |
| COMM-05 | Payments | Payment/reconciliation completion | 🟡 IMPLEMENTED / VERIFICATION PENDING | Payment flow supports invoice payment/deposit and idempotency controls; runtime reconciliation remains open | Critical | Execute populated reconciliation drill |
| COMM-06 | Purchasing | Complete purchasing/receiving workflow | 🟡 IN PROGRESS | Create/status/receive routes exist; full verification outstanding | High | Verify against event/cost/finance state |
| COMM-07 | Inventory | Reservation/allocation where required by V1 | 🟡 IN PROGRESS | Inventory movement foundation exists; requirement boundary still needs populated-data verification | High | Verify actual V1 operational need before expansion |
| COMM-08 | Assets | Condition/damage/loss evidence | 🟡 IN PROGRESS | Asset allocation/release foundation exists; evidence workflow remains incomplete | Medium | Verify whether current V1 requirement is satisfied without expansion |
| COMM-09 | Activity | Activity/audit continuity across commercial workflow | 🟡 IN PROGRESS | Audit foundation exists; continuity not fully proven | High | Trace quote → invoice → payment → completion |
| COMM-10 | Notifications | Required reminders/notifications | ⬜ NOT STARTED | Listed as release work; no verified evidence in current record | Medium | Determine minimum V1 operational requirement; avoid notification sprawl |

# Phase 5 — UX, responsive and accessibility

| ID | Area | Requirement | Status | Evidence | Risk | Next Action |
|---|---|---|---|---|---|---|
| UX-01 | Desktop | Critical workflows render and operate correctly on desktop | 🟡 IN PROGRESS | Responsive foundation and static audits exist; rendered QA not proven | High | Browser QA current HEAD |
| UX-02 | Mobile | Critical workflows render and operate correctly on mobile | 🟡 IN PROGRESS | Mobile navigation foundation exists; current runtime verification incomplete | High | Browser QA current HEAD |
| UX-03 | Tablet | Core workflows remain usable at tablet widths | 🟡 IN PROGRESS | Responsive source work exists; rendered tablet proof missing | Medium | Tablet viewport audit |
| UX-04 | Accessibility | Focus, touch targets, contrast, semantics and keyboard paths are usable | 🟡 IN PROGRESS | Static visual/accessibility refinement performed; runtime smoke missing | High | Accessibility smoke on critical pages |
| UX-05 | Navigation | Navigation remains coherent without dead ends or excessive chrome | 🟡 IN PROGRESS | Mobile/navigation refinements and command palette exist; runtime verification outstanding | Medium | Traverse critical routes |
| UX-06 | Error states | Validation, empty, loading and failure states are usable | 🟡 IN PROGRESS | Existing UI foundations; broad runtime verification outstanding | High | Targeted failure-state sweep |
| UX-07 | Dashboard | Dashboard refinement preserves working metrics/workflows | 🟡 IN PROGRESS | Current dashboard source inspected; visual cycles completed, but rendered verification remains outstanding | Medium | Render current dashboard at desktop/mobile/tablet |
| UX-08 | Search | Search experience is usable across desktop/mobile | 🟡 IN PROGRESS | Database-backed permission-aware search implemented; rendered/runtime QA missing | Medium | Browser verify |

# Phase 6 — Reliability, recovery and operations

| ID | Area | Requirement | Status | Evidence | Risk | Next Action |
|---|---|---|---|---|---|---|
| REL-01 | Failure safety | Critical mutations fail atomically and safely | 🟡 IN PROGRESS | Transactions/idempotency protections documented; runtime proof incomplete | Critical | Adversarial failure-path verification |
| REL-02 | Backup | Real backup procedure works | ⬜ NOT STARTED | Explicit release gap | Critical | Execute actual backup drill |
| REL-03 | Restore | Representative business data and private media restore successfully | ⬜ NOT STARTED | Explicit release gap | Critical | Execute restore drill |
| REL-04 | Rollback | Deployment rollback procedure is documented and usable | ⬜ NOT STARTED | Explicit release gap | High | Produce and exercise rollback procedure |
| REL-05 | Diagnostics | Owner can diagnose runtime failures | 🟡 IN PROGRESS | Operability foundation exists; final evidence missing | High | Verify logs/diagnostics/health visibility |
| REL-06 | Observability | Critical application failures leave actionable evidence | 🟡 IN PROGRESS | Release documentation identifies observability as incomplete | High | Inspect exception/logging/operational surfaces |

# Phase 7 — Deployment and upgrade safety

| ID | Area | Requirement | Status | Evidence | Risk | Next Action |
|---|---|---|---|---|---|---|
| DEP-01 | Fresh install | Fresh installation is reproducible | 🟡 IN PROGRESS | Composer/Vite setup scripts exist; current-head runtime proof missing | High | Execute clean-install verification |
| DEP-02 | Existing DB | Existing populated database upgrades safely | 🟡 IN PROGRESS | Explicit release gap | Critical | Representative migration drill |
| DEP-03 | Configuration | Production configuration is reviewed for safe defaults | 🟡 IN PROGRESS | Configuration/deployment review listed as outstanding | High | Final configuration audit |
| DEP-04 | Dependencies | Exact dependency versions and licences are release-reviewed | 🟡 IN PROGRESS | Third-party register explicitly requires transitive audit | Medium | Inspect composer.lock/package-lock.json |
| DEP-05 | Build assets | Built asset manifest matches deployed assets | 🟡 IN PROGRESS | Latest HEAD fixes manifest paths; runtime/browser proof still missing | Medium | Build and verify deployed asset references |
| DEP-06 | Release candidate | Current HEAD passes final release audit | ⬜ NOT STARTED | Release evidence maturity currently 1/5 | Critical | Complete all release gates first |

# Phase 8 — Documentation, legal, privacy and ownership

| ID | Area | Requirement | Status | Evidence | Risk | Next Action |
|---|---|---|---|---|---|---|
| DOC-01 | Documentation | Current product/release state is documented | 🟢 VERIFIED COMPLETE | Product spec, capability map, status, checklist and readiness register exist | Low | Keep current |
| DOC-02 | Ownership/IP | Ownership and proprietary-use position is recorded | 🟢 VERIFIED COMPLETE | README, IP ownership and commercial legal register | Medium | Final release legal review |
| DOC-03 | Privacy | POPIA/privacy engineering baseline exists | 🟡 IN PROGRESS | ZAZU_PRIVACY_BASELINE exists; operational sign-off remains | High | Finalize actual processing/retention controls |
| DOC-04 | Licences | Third-party notices and commercial-use licence evidence complete | 🟡 IN PROGRESS | THIRD_PARTY_NOTICES exists; transitive audit remains | Medium | Complete dependency audit |
| DOC-05 | Runbooks | Deployment, backup, restore, rollback and incident procedures documented | ⬜ NOT STARTED | Release checklist explicitly lists operational runbooks | High | Produce minimum V1 runbook set |

# Phase 9 — Release candidate certification

| ID | Area | Requirement | Status | Evidence | Risk | Next Action |
|---|---|---|---|---|---|---|
| RC-01 | Runtime | Current HEAD Laravel test/runtime suite executed | ⬜ NOT STARTED | No current execution evidence available in this Director inspection | Critical | Execute locally/CI |
| RC-02 | Browser | Critical desktop workflows traversed | ⬜ NOT STARTED | Existing audit says browser proof remains required | Critical | Execute browser traversal |
| RC-03 | Browser | Critical mobile workflows traversed | ⬜ NOT STARTED | Existing audit says mobile proof remains required | Critical | Execute browser traversal |
| RC-04 | Accessibility | Critical accessibility smoke passes | ⬜ NOT STARTED | Static work exists; runtime evidence absent | High | Execute accessibility smoke |
| RC-05 | Security | Final authorization/security challenge passes | ⬜ NOT STARTED | Security foundation strong but final review open | Critical | Execute adversarial review |
| RC-06 | Data | Populated-data workflow/reconciliation proof passes | ⬜ NOT STARTED | Explicit release gap | Critical | Execute representative scenario |
| RC-07 | Recovery | Backup/restore/rollback proof passes | ⬜ NOT STARTED | Explicit release gap | Critical | Execute recovery drills |
| RC-08 | Upgrade | Representative upgrade proof passes | ⬜ NOT STARTED | Explicit release gap | Critical | Execute migration/upgrade drill |
| RC-09 | Release audit | Final Director re-audit has no release-critical blockers | ⬜ NOT STARTED | Not yet at release-candidate stage | Critical | Re-audit after evidence closure |

---

# Explicit V1 exclusions

These are not to be pulled into V1 unless a concrete release requirement proves otherwise:

- Enterprise SSO
- Enterprise-scale warehouse management
- Enterprise asset management
- Advanced BI/predictive analytics
- Predictive finance
- Autonomous AI actions or decisions
- Large integration frameworks
- Advanced client portal ecosystem
- High-scale performance programme
- Multi-location expansion
- Full compliance-management platform
- Complex generic workflow designer

**Planned event state:** remains a product decision, not an automatic defect. Current lifecycle is Draft → Confirmed → In Progress → Completed with cancellation handling. Do not add a new state merely to make terminology match a conceptual diagram.

---


# Phase 10 — Deferred product capabilities and integrations

These are **post-core hardening / later-release priorities**. They are recorded now so they are not lost or accidentally pulled into the current release-critical path.

| ID | Area | Requirement | Status | Priority | Release impact | Next Action |
|---|---|---|---|---|---|---|
| LATER-01 | Zazu Helper | Decide whether the in-app Zazu Helper is a maintained product capability or should be retired/hidden | 🟡 DEFERRED REVIEW | P2 | Medium | Audit actual usage, value and maintenance cost before expanding it |
| LATER-02 | Platform Admin | Decide whether the platform-admin surface is a supported product capability or an internal maintenance surface | 🟡 DEFERRED REVIEW | P2 | Low–Medium | Audit current purpose, routes, authorization and actual operational need |
| LATER-03 | Media | Complete wallpaper support where logo/workspace image already work | 🟡 PARTIAL | P2 | Medium | Trace wallpaper storage → settings → rendering → responsive/background CSS and fix the missing path |
| LATER-04 | Spreadsheet import | Import existing business Excel workbooks into Zazu for supported domains | ⬜ NOT STARTED | P2 | High | Define supported workbook templates/columns and safe preview → validate → import flow |
| LATER-05 | Spreadsheet export | Export relevant Zazu business data back to Excel | ⬜ NOT STARTED | P2 | Medium | Define domain exports for customers, assets/equipment, products/services, suppliers, purchasing and finance where appropriate |
| LATER-06 | Spreadsheet migration safety | Prevent bad spreadsheets from corrupting live business data | ⬜ NOT STARTED | P1 | High | Add staging/preview, row-level validation, duplicate matching, business scoping, transaction/rollback and import audit trail |
| LATER-07 | Document ingestion | Ingest business documents/photos/scans and extract useful structured data into Zazu | ⬜ NOT STARTED | P2 | High | Define document types, extraction pipeline, human review and source-document retention |
| LATER-08 | Email connection | Connect a business mailbox to Zazu for controlled inbound/outbound operational workflows | ⬜ NOT STARTED | P2 | Medium–High | Define provider/auth model, mailbox permissions, message linking, attachments and audit rules |
| LATER-09 | WhatsApp connection | Connect WhatsApp business conversations/messages to relevant Zazu records | ⬜ NOT STARTED | P2 | High | Define provider/API route, consent/privacy boundaries, message/media linking and record ownership |
| LATER-10 | Unified ingestion | Establish a common ingestion architecture for Excel, documents, email and WhatsApp | ⬜ NOT STARTED | P2 | High | Design a staged ingestion boundary so external data never writes directly into core domain tables |
| LATER-11 | Import/export auditability | Make all imports, exports and external-ingestion actions attributable and reversible where appropriate | ⬜ NOT STARTED | P1 | High | Define import batch IDs, actor/source metadata, change summaries and rollback/reversal strategy |

## Deferred data-ingestion design rule

Do **not** let Excel/document/email/WhatsApp integrations write directly into production domain tables without a validation boundary.

Target flow:

**Source → Ingestion/Staging → Parse/Map → Validate → Preview/Review → Commit → Audit**

For Excel specifically, initial supported migration candidates should be practical business sheets such as:

- Customers
- Products/services
- Suppliers
- Assets/equipment
- Inventory items
- Event/job records where a safe mapping exists

The importer should not assume every customer's spreadsheet layout is identical. Template detection, column mapping and a review screen should be treated as part of the feature.

## Deferred integration principle

Email and WhatsApp should connect to existing Zazu records rather than become separate communication silos. Messages, attachments and documents should be linkable to customers, jobs/events, quotes, invoices or other supported records without bypassing existing permissions and audit rules.

# Current Director scoreboard

Scoring is deliberately conservative and evidence-based. These figures are not test-count scores.

| Measure | Current position |
|---|---:|
| Engineering Quality | **78%** |
| V1 Release Readiness | **59%** |
| Critical Open | **9** |
| High Open | **19** |
| Medium Open | **8** |
| Regressions | **0 observed in source inspection** |
| Release Blockers | **Critical workflow completion, runtime proof, recovery, populated-data verification, upgrade verification** |
| Missing Evidence | **Current-head runtime/CI, browser, accessibility, backup/restore, migration/upgrade, final security/release proof** |

## Score interpretation

**Engineering Quality 76%** reflects the substantial existing architecture, business-domain coverage, isolation, lifecycle controls, transaction/idempotency protections, responsive system and documentation.

**V1 Release Readiness 58%** is intentionally lower because several release gates are implemented but not yet proven at runtime/recovery level. The product is materially advanced but is **not a release candidate**.

---

# Immediate Director hardening result — 2026-09-28

- Central event lifecycle boundary retained as the authority for downstream mutation paths.
- Cancellation now rejects unresolved planned costs, preventing a cancelled work record from leaving a live planned-cost obligation behind.
- Existing completion guards remain unchanged: open/blocked preparation, planned costs and active purchase orders still block completion.
- Existing finance guards remain unchanged: cancelled work cannot receive new financial records.
- No test-contract changes were made to manufacture green tests; the purchasing `received` 422 remains an intentional business-rule check until the product workflow is reviewed.

# Immediate next batch

## BATCH ZR-01 — Current-HEAD runtime/release evidence baseline

**Priority:** Critical  
**Why:** The repository is already in an integration-and-verification phase. Further broad feature work would have lower release value than proving the current system.

### Before
- Source-level evidence is strong.
- Current HEAD is `aa01ad1`.
- Runtime/browser/recovery evidence is incomplete.
- The latest HEAD changed built asset manifest paths.

### Change
Do **not** redesign or add features.

Run a controlled verification batch against current HEAD:

1. Laravel application/runtime/test suite.
2. Blade/view syntax coverage.
3. Asset build and manifest consistency.
4. Critical route traversal.
5. Authentication/onboarding traversal.
6. Event → work → purchasing → costs → finance traversal.
7. Permission/cross-business isolation checks.
8. Desktop/mobile/tablet smoke.
9. Accessibility smoke.

### Verify
Record exact failures, environment, commands/results and affected files.

### Regression scan
Any existing workflow failure stops new improvement work.

### Re-audit
Update this roadmap, readiness register and release position only from observed evidence.

**Next Director target after ZR-01:** fix the highest-severity verified defect, then re-run the same evidence slice before touching the next domain.

---

# Director cycle record — 2026-09-28

## ZR-01 access + verification consolidation

Application changes:
- Public landing remains available to authenticated accounts even when no active workspace can be resolved.
- Guests see Register / Log in; authenticated users see You’re signed in and, where available, Open workspace / Sign out.
- Missing workspace context is now distinguished from ordinary permission denial, with a public-site/sign-out recovery path.
- Landing copy now speaks directly to South African event businesses, including small operators and growing businesses.
- Mobile landing keeps Register and Log in visible.

Verification infrastructure:
- Laravel CI now compiles Blade views with php artisan view:cache.
- Laravel CI now checks every emitted public/build/manifest.json asset exists.
- Browser CI now creates the SQLite database file before migration.
- The real registration/onboarding browser test now lives under e2e/ and is executed by the Zazu browser workflow.
- Stale generic Playwright coverage was removed.
- Browser projects now cover desktop Chrome, Pixel 7 mobile and iPad tablet.
- Browser onboarding includes accessibility-oriented assertions.

Current evidence:
- Final application commit: f714253a8b1cebfae70b394bcd87281a4e240828.
- Laravel workflow run 36483769899: QUEUED.
- Zazu browser smoke run 36483769888: QUEUED.
- Psalm run 36483769887: QUEUED.
- PHPMD run 36483769876: QUEUED.
- CodeQL run 36483769482: QUEUED.
- SonarCloud run 36483769880: SKIPPED.

Re-audit:
- No source-level regression observed in the modified access, error, landing and browser-control areas.
- Release-readiness score remains unchanged until current-head runtime/browser evidence completes.
- Cycle status remains IN PROGRESS.

## ZR-02 Vite manifest regression — 2026-09-28

- **Finding:** app-layout.blade.php loaded resources/css/zazu-mobile-refinement.css, but vite.config.js did not declare that stylesheet as an input.
- **Observed evidence:** 42 feature tests failed while rendering protected Blade pages with "Unable to locate file in Vite manifest: resources/css/zazu-mobile-refinement.css".
- **Root cause:** Blade/Vite input mismatch, not 42 independent application defects.
- **Fix:** Added resources/css/zazu-mobile-refinement.css to the Laravel Vite input list.
- **Status:** 🟡 IN PROGRESS until the current-head Laravel suite completes.
- **Required verification:** Vite build, Blade compilation, full Laravel feature suite and browser smoke.

## ZR-03 Workspace access restoration — 2026-09-28

### Finding
The current access path had a release-blocking Blade compilation failure in `resources/views/landing.blade.php`. CI showed the landing page failed before the registration flow could begin, so browser verification could not reach the application.

A second structural gap was confirmed: an authenticated account with no active `business_user` membership had no legitimate route from the public landing back into workspace setup.

### Fix
- Replaced nested `@guest/@else/@if` landing conditionals with explicit authenticated-state conditionals so the public entry page compiles deterministically.
- Added an authenticated workspace-recovery route for accounts with no active workspace.
- Workspace recovery creates one active business, attaches the signed-in account as owner, stores the selected business in session and resumes the existing catalogue onboarding flow.
- Login now routes an authenticated account with no active workspace to recovery instead of sending it into the protected dashboard dead-end.
- Authenticated landing state now exposes **Set up workspace** when no active workspace exists.
- Added feature coverage for orphan-account recovery and registration → dashboard → logout → login → dashboard round-trip.
- Corrected the browser tablet project to use Chromium emulation rather than the iPad WebKit device profile.

### Evidence
- Pre-fix Laravel run `36485085109` failed on the landing Blade syntax error plus unrelated existing feature failures.
- Pre-fix browser run `36485085175` failed immediately at the public landing assertion; tablet also attempted to launch missing WebKit.
- Application fixes are now on `main` through HEAD `8fc24ff9c5497928266d8a3cff32418674b68cab`.
- Current-head CI verification is pending; no green runtime claim is made yet.

### Release status
- **CORE-02 Authentication:** 🟡 IN PROGRESS — access path fixed in source; current-head runtime verification pending.
- **CORE-03 Onboarding:** 🟡 IN PROGRESS — fresh registration path retained; orphan-account recovery added; current-head browser verification pending.
- **RC-01 Runtime:** 🟡 IN PROGRESS — prior run exposed a landing compilation blocker; rerun required after fix.
- **RC-02/03 Browser:** 🟡 IN PROGRESS — desktop/mobile path was blocked at landing; tablet browser definition corrected; rerun required.

### Next action
Run the current HEAD Laravel and browser workflows. If a failure remains, fix only the verified blocker before further feature/refinement work.



## ZR-04 Current-HEAD access verification — 2026-09-28

### Verification scope
Access path only. No feature/refinement work was performed during this verification pass.

### Browser evidence
Current HEAD browser smoke run: `36490728441`  
Result: **SUCCESS** — **4 passed, 8 skipped**.

Verified path:
`public landing → register → catalogue setup → experience setup → business setup → dashboard → authenticated landing → logout → login → dashboard`

The browser smoke explicitly checked `.zazu-error-shell` was absent after:
- public landing
- login
- catalogue setup
- experience setup
- business setup
- dashboard
- authenticated landing
- post-logout landing
- login surface

Responsive entry smoke also ran for Chromium desktop, Pixel 7 mobile and Chromium tablet emulation and passed.

### Authentication evidence
Current-head Laravel run `36490448966`:
- `AuthenticationTest`: **PASS**
- Registration creates user + business + owner membership: **PASS**
- Orphaned account workspace recovery: **PASS**
- Username login/logout: **PASS**
- Blade compilation: **PASS**
- Vite asset manifest verification: **PASS**
- Psalm security scan: **PASS**

The full Laravel suite still has **9 unrelated existing failures** (151 passed) and therefore remains open as a broader regression gate. Those failures are not the access-path failure that originally prevented the landing page from rendering.

### Release status
- **CORE-02 Authentication:** 🟢 VERIFIED COMPLETE for the tested registration/login/access path.
- **CORE-03 Onboarding:** 🟢 VERIFIED COMPLETE for the tested registration → setup → dashboard path.
- **RC-01 Runtime:** 🟡 IN PROGRESS — full suite still has 9 unrelated failures.
- **RC-02 Browser desktop:** 🟢 VERIFIED COMPLETE for the targeted critical access traversal.
- **RC-03 Browser mobile:** 🟢 VERIFIED COMPLETE for responsive landing/login no-error smoke; full registration traversal intentionally runs once on Chromium to avoid shared-IP registration throttling.
- **Access blocker:** **CLEARED in CI verification.**

### Director conclusion
The original landing-page access blocker is no longer reproducing in the current HEAD verification environment. The current browser evidence reaches the real landing page and completes registration/onboarding/dashboard without a Zazu error page.

This does **not** certify the user's separate deployed/local environment as live-green; no deployment URL was available for direct external browser verification.


## ZR-05 Release regression contract alignment — 2026-09-29

### Scope
Test-contract and release-evidence hygiene only. No application business logic, routes, controllers, views, database schema or authentication behavior was changed in this cycle.

### Executed hardening
- Updated public landing assertions from the retired **Create workspace** wording to the current **Register** action.
- Updated logout coverage to the current public-landing destination.
- Updated the work customer-lock regression to assert Laravel validation errors rather than a generic session key.
- Updated search regression coverage to inspect the returned result set instead of treating the echoed search input as a result.
- Updated dashboard regression coverage to the current command-surface contract.
- Allowed onboarding pages that intentionally redirect after setup to remain valid in the skeleton reachability regression.
- Added a regression test proving work cannot be cancelled while a linked cost remains in **planned** state.

### Evidence
- Current HEAD: `57530369d9d5a589df7ef8671e84cab44f14eeea`.
- Compare from verified browser baseline `5764b7112840a359c1b4d7299ca082b53813c6af` to current HEAD shows changes only in:
  - `docs/ZAZU_V1_RELEASE_ROADMAP.md`
  - `resources/css/zazu-mobile-refinement.css`
  - feature-test files.
- No application runtime logic changed after the verified browser baseline.
- Fresh CI result for the current HEAD is not yet available through the repository evidence interface.

### Release interpretation
- This cycle removes obsolete regression noise and adds direct coverage for the latest lifecycle hardening.
- It does **not** upgrade RC-01 to green because current-head Laravel execution evidence is still pending.
- The previously verified browser registration/login/dashboard path remains applicable to runtime code because this cycle changed only CSS, tests and release documentation after that baseline.

### Next Director target
Obtain fresh current-head CI evidence. Fix only verified runtime failures. After a green runtime baseline, move to populated-data reconciliation, authorization challenge testing, backup/restore proof and upgrade safety before adding non-critical product features.


## ZR-06 Architectural debt hardening — 2026-09-29

### Director scope
Four-pass hardening loop focused on architectural debt and static engineering quality. No new product feature scope was introduced.

### Cycle 1 — Complexity isolation
- Split the monolithic `WorkspaceSearchService` into a thin orchestrator plus focused search handlers for customer, event, service, supplier, purchasing, quote, invoice, cost, asset, inventory and expense domains.
- Extracted shared search filtering/text helpers into `AbstractWorkspaceSearchHandler`.
- Decomposed `ZazuHelperService::attention()` into focused attention rules.
- Purpose: reduce class/method complexity, isolate change impact and make search rules independently maintainable.

### Cycle 2 — Persistence boundary audit
- Replaced direct `DB::table('platform_landing_settings')` access in the public route and platform-admin controller.
- Added `PlatformLandingSetting` model with a single canonical `current()` accessor.
- Added `LandingMediaService` as the landing-media application boundary.
- Caught one integration contract mismatch during the cycle: the admin view expected the settings model, not the resolved image array. Corrected before final pass.
- Result: public routing, controller and persistence responsibilities are now separated.

### Cycle 3 — Integration and hygiene audit
- Re-read the orchestrator, search handlers, landing-media service, controller and route wiring after refactor.
- Removed unused handler imports.
- Confirmed business scoping remains inside each search query and existing permission filtering remains in the orchestrator.
- Confirmed platform-admin routes remain behind `auth`, `auth.session` and `platform.admin`.
- Confirmed no TODO/FIXME debt was introduced by this cycle.

### Cycle 4 — Final destructive-change gate
- Re-audited the changed architecture for duplicate authorities, hidden cross-domain coupling, dead code and unnecessary scope.
- No additional safe high-value refactor was identified that could be made without materially widening the change surface or risking unrelated runtime behavior.
- **Decision:** stop architectural mutation here. Further broad refactoring at this point would be diminishing-return / potentially destructive rather than controlled hardening.

### Current verification state
- Current HEAD after the four-cycle execution is pending CI completion.
- GitHub has queued fresh Laravel, browser, Psalm and PHPMD runs for the latest push.
- SonarCloud remains skipped by repository workflow conditions.
- Static architecture position is materially improved, but no test/runtime gate is marked green until fresh workflow evidence completes.

### Gate target
Non-test engineering gates targeted by this cycle:
- Architecture / separation of concerns: **90+ target**
- Complexity / maintainability: **90+ target**
- Security boundary: **90+ target**
- Configuration / persistence boundaries: **90+ target**
- Release hygiene / change containment: **90+ target**

These are engineering assessments, not automated test scores. Runtime, browser, recovery, populated-data and upgrade gates remain evidence-dependent.


# Director cycle record — 2026-10-01 — long-sprint usability + foundation hardening

## Scope

Page-by-page audit of the operator experience from solo/non-technical use through larger teams, with reliability and productivity treated as one system. Existing architecture was reused; no new domain was introduced merely to improve presentation.

## Executed

- Shared UI storage access was hardened so denied/unavailable browser storage does not break theme or last-destination persistence.
- Ordinary form submissions now disable the active submit control after the browser has accepted the submit event, reducing accidental duplicate requests while preserving confirmation-dialog behaviour.
- Purchase-order creation now exposes the backend's existing multi-line capability with line management and live estimated totals instead of forcing users through a one-line-at-a-time workaround.
- Quote creation and revision now expose a live subtotal/tax/total/deposit checkpoint beside the consequential form, matching the existing UI disclosure decision for financial workflows.
- Job-scoped purchasing cancellation and invoice-scoped payment cancellation preserve the user's originating context.
- Regression contracts were added for these foundations.
- A test-source namespace corruption introduced during the cycle was caught in re-audit and corrected before the cycle was closed.

## Page-to-page decision

The following surfaces were reviewed and intentionally kept within their current information architecture because source evidence did not justify more complexity:

Landing/authentication → Onboarding → Dashboard → Work → Requirements → Customers/Contacts → Inventory → Assets → Preparation → Travel/Costs → Finance → Reports → Settings/Compliance → Search/Calendar.

The governing rule remains:

When the page already gives the user the next useful action with enough context, stop. Do not add another card, menu, wizard or abstraction just because the feature exists elsewhere in the system.

## Re-audit

- Quote summary rendering was corrected after the first source check found the JavaScript without corresponding sidebar markup.
- AppModels, AppSupport, AppHttp and AppServices flattened namespace tokens were re-scanned and found only in documentation/history, not application or test source.
- Existing tenant isolation, composite parent/child database constraints, idempotency, lifecycle locking and error containment remain intact.
- No new permission authority or parallel navigation authority was created.
- No further source-only improvement met the threshold for safe high-value change without runtime evidence.

## Verification boundary

Current GitHub branch is main. The repository source was re-read after the final correction. The connector still provides no usable workflow result for the current main commits, and the development PC is off; therefore current-head Laravel, browser/mobile, migration/upgrade and backup/restore execution remain unproven.

## Next release gate

Runtime populated walkthrough is now the next priority. The next pass should consume actual CI/runtime evidence before any wider feature expansion:

1. fresh CI/build/migration;
2. populated desktop journey;
3. populated mobile/tablet journey;
4. intentional validation/error/recovery checks;
5. backup/restore drill;
6. representative populated-database upgrade;
7. final Director re-audit.


## Authority update — 2026-10-01

The release-control relationship is now explicit:

**docs/V1_RELEASE_CHECKLIST.md = canonical V1 gate authority.**  
**docs/ZAZU_V1_RELEASE_ROADMAP.md = strategy, historical evidence, rationale and deferred-work roadmap.**

The checklist was rewritten after the Director found that the roadmap/checklist had fallen behind implementation. In particular, customer-facing quote presentation, signed quote acceptance and deposit handling were incorrectly represented as "not started" despite current source and regression coverage. They remain release-critical until runtime evidence proves the complete journey, but they are no longer build-from-zero work.

The roadmap must not reintroduce stale status. When implementation changes, update the canonical checklist first; then record the reasoning/evidence in this roadmap.
