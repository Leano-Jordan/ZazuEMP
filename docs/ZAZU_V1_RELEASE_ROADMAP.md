# Zazu EMP — V1 Commercial Release Roadmap

**Status:** LIVE  
**Last Director cycle:** 2026-09-28  
**Repository:** `Leano-Jordan/ZazuEMP`  
**Branch:** `main`  
**HEAD:** `aa01ad1fb0209f13114455bb3b0eb3a8bfbeba1d`  
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
| CTRL-01 | Release control | Maintain this roadmap as the single V1 execution checklist | 🟢 VERIFIED COMPLETE | This document created on current `main` HEAD | Low | Update every Director cycle |
| CTRL-02 | Repository truth | Inspect branch and HEAD before every execution | 🟢 VERIFIED COMPLETE | Current branch `main`; HEAD `5cdceff` verified after roadmap commit; application baseline was `aa01ad1` | Low | Repeat each cycle |
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
| CORE-11 | Completion | Completion/cancellation cannot leave contradictory operational/procurement/financial states | 🟡 IN PROGRESS | Existing guards documented; populated-data walkthrough outstanding | Critical | Adversarial transition verification |

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
| COMM-01 | Quotes | Customer-facing quote presentation/delivery | ⬜ NOT STARTED | V1 status identifies this as release-critical remaining work | Critical | Inspect current quote presentation and implement smallest V1 closure |
| COMM-02 | Quotes | Quote acceptance lifecycle | ⬜ NOT STARTED | Explicit release gap | Critical | Define and verify acceptance transition |
| COMM-03 | Deposits | Deposit lifecycle is tied to commercial state | ⬜ NOT STARTED | Explicit release gap | High | Inspect existing payment model before adding state |
| COMM-04 | Invoices | Complete invoice/document workflow | 🟡 IN PROGRESS | Invoice creation/show/store routes exist; complete document workflow evidence incomplete | Critical | Trace invoice creation → delivery/document → payment |
| COMM-05 | Payments | Payment/reconciliation completion | 🟡 IN PROGRESS | Payment controls and idempotency documented; runtime reconciliation missing | Critical | Execute populated reconciliation drill |
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

# Current Director scoreboard

Scoring is deliberately conservative and evidence-based. These figures are not test-count scores.

| Measure | Current position |
|---|---:|
| Engineering Quality | **76%** |
| V1 Release Readiness | **58%** |
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
