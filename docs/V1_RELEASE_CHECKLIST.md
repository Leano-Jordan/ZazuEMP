# Zazu EMP — V1 Commercial Release Checklist

**Authority:** ACTIVE / CANONICAL V1 RELEASE GATE  
**Last Director update:** 2026-10-01  
**Repository:** `Leano-Jordan/ZazuEMP`  
**Branch:** `main`  
**Application assessment HEAD:** `52d9b26bc17ddee8d1427032ecaf2fb8fc0b6350`  
**Note:** subsequent documentation-only commits do not change the application assessment baseline.

## Authority rule

This file is the **single authoritative V1 release-gate ledger**.

- `docs/ZAZU_V1_RELEASE_ROADMAP.md` records strategy, historical Director cycles, evidence context and deferred roadmap work.
- This checklist records the **current state of V1 release gates**.
- No item is considered release-complete merely because source code exists.
- **Implemented** means source-reviewed and present.
- **Verified** means exercised by tests/runtime against the relevant current release candidate.
- **Proven** means the verification has recorded evidence sufficient for release sign-off.
- Never downgrade a proven result based on stale documentation; update the documentation instead.

### Status key

- ✅ **IMPLEMENTED** — source-reviewed and present.
- 🟡 **IMPLEMENTED / VERIFICATION PENDING** — implementation exists, but required current-head proof is outstanding.
- 🟢 **VERIFIED / PROVEN** — current release evidence supports the claim.
- 🔴 **BLOCKED** — a verified defect prevents the gate.
- ⚪ **DEFERRED / NOT V1** — deliberately outside the current release.

---

# Gate 1 — Platform foundation

| Area | Current state | Release proof |
|---|---|---|
| Registration / authentication | ✅ Implemented | 🟡 Fresh current-head runtime |
| Active business context | ✅ Implemented | 🟡 Current-head runtime |
| Workspace switching | ✅ Implemented | 🟡 Current-head runtime |
| Business isolation | ✅ Implemented | 🟡 Adversarial verification |
| Roles / permissions | ✅ Implemented | 🟡 Full protected-action audit |
| Auditability | ✅ Implemented | 🟡 End-to-end mutation trace |
| Experience level | ✅ Implemented | 🟡 Fresh-account traversal |
| Runtime regression suite | ✅ Implemented | 🟢 Current-head Laravel suite: 202 passed / 1,174 assertions |

# Gate 2 — Onboarding

| Area | Current state | Release proof |
|---|---|---|
| Registration → catalogue/setup | ✅ Implemented | 🟢 Current-head browser journey passed |
| Basic / Intermediate / Advanced | ✅ Implemented | 🟢 Intermediate onboarding exercised in current-head browser run |
| Business identity/setup | ✅ Implemented | 🟢 Current-head browser journey passed |
| Deferral/resume behaviour | ✅ Implemented | 🟡 Current-head runtime |
| Experience-level changes | ✅ Implemented | 🟡 Current-head runtime |
| Orphan-account workspace recovery | ✅ Implemented | 🟢 Current-head Laravel suite passes recovery coverage |
| Desktop onboarding | ✅ Implemented | 🟢 Current-head browser journey passed |
| Mobile onboarding | ✅ Implemented | 🟡 Responsive entry smoke passed; full registration journey remains Chromium-only |

# Gate 3 — Core operations

| Domain | Current state | Release proof |
|---|---|---|
| Customers / contacts | ✅ Implemented | 🟡 Populated-data traversal |
| Jobs / events | ✅ Implemented | 🟡 Populated-data traversal |
| Lifecycle control | ✅ Implemented | 🟡 Transition matrix |
| Work / preparation | ✅ Implemented | 🟡 Populated-data traversal |
| Requirements / services | ✅ Implemented | 🟡 Populated-data traversal |
| Suppliers | ✅ Implemented | 🟡 Populated-data traversal |
| Purchasing / receiving | ✅ Implemented | 🟡 Populated-data traversal |
| Assets / inventory | ✅ Implemented | 🟡 Populated-data traversal |
| Event costs / travel | ✅ Implemented | 🟡 Populated-data traversal |
| Finance / invoices / payments | ✅ Implemented | 🟡 Reconciliation drill |
| Documents | ✅ Implemented | 🟡 Critical document workflow |
| Reports | ✅ Implemented | 🟡 Populated-data verification |

# Gate 4 — Commercial truth

The previous checklist incorrectly treated some commercial capabilities as not started. Current source at HEAD contains the following implementations:

| Area | Current state | Release proof |
|---|---|---|
| Customer-facing quote presentation | ✅ Implemented — signed public quote route/view exists | 🟡 Browser verification |
| Quote acceptance lifecycle | ✅ Implemented — signed public acceptance route and controller path exist | 🟡 Browser + business-state verification |
| Deposit definition | ✅ Implemented — quote version stores deposit percentage/amount | 🟡 Financial reconciliation |
| Deposit recording | ✅ Implemented — finance payment flow supports `deposit` type | 🟡 Reconciliation drill |
| Invoice linkage | ✅ Implemented — invoices retain quote relationship/snapshot | 🟡 End-to-end verification |
| Payment/idempotency controls | ✅ Implemented | 🟡 Runtime reconciliation |
| Quote revision/history | ✅ Implemented | 🟡 Historical snapshot verification |
| Purchasing / receiving | ✅ Implemented | 🟡 End-to-end verification |
| Inventory/resource continuity | ✅ Implemented | 🟡 Populated-data verification |
| Activity/audit continuity | ✅ Implemented | 🟡 Mutation trace |

**Commercial interpretation:** these are no longer “build from zero” release items. They are **prove-the-workflow** items.

# Gate 5 — Connected business truth

| Chain | Current state | Release proof |
|---|---|---|
| Customer → Job/Event | ✅ Implemented | 🟡 Populated traversal |
| Job → Requirements/Work | ✅ Implemented | 🟡 Populated traversal |
| Work → Resources | ✅ Implemented | 🟡 Populated traversal |
| Work → Purchasing | ✅ Implemented | 🟡 Populated traversal |
| Work → Costs | ✅ Implemented | 🟡 Reconciliation |
| Job/Quote → Invoice | ✅ Implemented | 🟡 End-to-end verification |
| Invoice → Deposit/payment | ✅ Implemented | 🟡 Reconciliation |
| Completion/cancellation guards | ✅ Implemented | 🟡 Transition matrix |
| Full operational chain | ✅ Implemented | 🔴 Must be proven on populated data |

# Gate 6 — Search and commandability

| Area | Current state | Release proof |
|---|---|---|
| Unified header search | ✅ Implemented | 🟡 Runtime search suite |
| Domain search coverage | ✅ Implemented | 🟡 Current-head runtime |
| Type/status/date filters | ✅ Implemented | 🟡 Current-head runtime |
| Permission-aware results | ✅ Implemented | 🟡 Adversarial verification |
| Business isolation in search | ✅ Implemented | 🟡 Cross-business challenge |
| Desktop search UX | ✅ Implemented | 🟡 Rendered QA |
| Mobile search UX | ✅ Implemented | 🟡 Rendered QA |

# Gate 7 — Zazu Helper

| Area | Current state | Release proof |
|---|---|---|
| Contextual guidance | ✅ Implemented | 🟡 Current-head runtime |
| Workflow explanations | ✅ Implemented | 🟡 Current-head runtime |
| Experience awareness | ✅ Implemented | 🟡 Current-head runtime |
| Attention signals / next actions | ✅ Implemented | 🟡 Current-head runtime |
| Autonomous V1 decisions | ⚪ Deliberately excluded | 🟢 Scope locked |
| Broader natural-language commands | ⚪ Post-V1 | 🟢 Scope locked |

# Gate 8 — UX / responsive / accessibility

| Area | Current state | Release proof |
|---|---|---|
| Zazu visual system | ✅ Implemented | 🟡 Rendered release QA |
| Desktop shell | ✅ Implemented | 🟡 Rendered QA |
| Mobile shell/navigation | ✅ Implemented | 🟡 Rendered QA |
| Forms / lists / tables | ✅ Implemented | 🟡 Critical-page QA |
| Empty/loading/error states | ✅ Implemented | 🟡 Failure-state sweep |
| Toasts / consequential-action confirmation | ✅ Implemented | 🟡 Runtime smoke |
| Dashboard attention surface | ✅ Implemented | 🟡 Rendered QA |
| Calendar/workflow visibility | ✅ Implemented | 🟡 Rendered QA |
| Desktop visual QA | ✅ Test coverage available | 🟡 Current-head execution |
| Mobile visual QA | ✅ Test coverage available | 🟡 Current-head execution |
| Tablet QA | ✅ Test configuration exists | 🟡 Current-head execution |
| Accessibility smoke | ✅ Assertions/foundation exist | 🟡 Current-head execution |

# Gate 9 — Security / privacy

| Area | Current state | Release proof |
|---|---|---|
| Authentication boundary | ✅ Implemented | 🟡 Release-candidate runtime |
| Server-side authorization | ✅ Implemented | 🟡 Full challenge |
| Business isolation | ✅ Implemented | 🟡 Cross-business adversarial test |
| Permission-aware search | ✅ Implemented | 🟡 Adversarial test |
| Secure attachments/private media | ✅ Implemented | 🟡 Direct-access challenge |
| Input validation | ✅ Implemented | 🟡 Failure-path sweep |
| Audit trail | ✅ Implemented | 🟡 Representative mutation trace |
| Privacy baseline | ✅ Documented | 🟡 Final operational review |
| Final security review | ✅ Scope/foundation exists | 🟡 Release sign-off |

# Gate 10 — Database / recovery

| Area | Current state | Release proof |
|---|---|---|
| Experience-level migration | ✅ Implemented | 🟡 Current-head migration |
| Existing-membership backfill | ✅ Implemented | 🟡 Current-head verification |
| Populated production-like migration | ✅ Tooling/schema exists | 🔴 Evidence outstanding |
| Backup creation | ✅ Capability exists | 🔴 Real backup drill outstanding |
| Restore | ✅ Capability exists | 🔴 Real restore drill outstanding |
| Record verification after restore | ✅ Test target defined | 🔴 Evidence outstanding |
| Private media after restore | ✅ Test target defined | 🔴 Evidence outstanding |

# Gate 11 — Deployment / upgrade

| Area | Current state | Release proof |
|---|---|---|
| Fresh install | ✅ Foundation exists | 🟡 Clean-install run |
| Existing database upgrade | ✅ Migration path exists | 🔴 Populated upgrade drill outstanding |
| Production configuration | ✅ Configuration exists | 🟡 Final audit |
| Dependency/licence review | ✅ Registers exist | 🟡 Final audit |
| Vite/build manifest | ✅ Build pipeline exists | 🟡 Current-head build proof |
| Rollback procedure | ✅ Release requirement defined | 🔴 Exercise required |

# Gate 12 — Release candidate finish line

All of the following are required before V1 is treated as release-ready:

- [x] Current-head Laravel suite has an accepted release disposition with no unexplained release-blocking failures.
- [x] Current-head browser smoke passes.
- [x] Current-head static analysis passes or every deviation is explicitly dispositioned.
- [x] Fresh migration succeeds.
- [ ] Populated database upgrade succeeds without integrity loss.
- [ ] Populated end-to-end business workflow succeeds.
- [ ] Search succeeds on populated data with authorization boundaries intact.
- [ ] Quote → acceptance → invoice → deposit/payment reconciliation succeeds.
- [ ] Desktop critical workflows pass.
- [ ] Mobile critical workflows pass.
- [ ] Tablet critical workflows pass.
- [ ] Accessibility smoke passes.
- [ ] Backup succeeds.
- [ ] Restore succeeds.
- [ ] Restored records and private media are verified.
- [ ] Rollback procedure is exercised.
- [ ] Final authorization/security challenge passes.
- [ ] Final Director re-audit records no release-critical defects.

# V1 scope lock

These remain outside V1 unless a verified release requirement proves they are necessary:

- Autonomous AI actions or decisions
- Predictive finance
- Advanced BI/predictive analytics
- Enterprise workflow designer
- Warehouse-scale inventory
- Enterprise asset management
- Heavy integration expansion
- Broad collaboration suite
- Enterprise SSO
- Unlimited UI configurability

**Rule:** finish and prove the product before expanding the product.

## Evidence baseline

The repository contains earlier CI/browser evidence for the access/onboarding path, including a successful targeted browser traversal. That evidence is retained as historical proof but does **not** automatically certify the current HEAD.

Current-head evidence is now available on the release candidate: Laravel **202 passed / 1,174 assertions**, browser **7 passed / 8 skipped**, Zazu Quality **passed**, PHPMD **passed**, and Psalm **passed** using the narrow Laravel 13.34.0 compatibility shim. Fresh migration/build/Blade/asset-manifest checks also passed. Populated-data workflow proof, full authorization challenge, backup/restore, representative upgrade and rollback remain open.

## Immediate execution priority

**Release Gate Sprint — remaining:**

1. Populated end-to-end business workflow
2. Commercial reconciliation: quote → acceptance → invoice → deposit/payment
3. Authorization/security challenge
4. Desktop/mobile/tablet critical workflow + accessibility verification
5. Real backup/restore + private-media recovery
6. Representative populated-database upgrade
7. Rollback exercise
8. Final Director re-audit

No broad feature expansion should displace these gates while any Critical item remains open.
