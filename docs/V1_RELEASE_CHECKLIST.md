# Zazu EMP — V1 Commercial Release Checklist

**Authority:** ACTIVE / CANONICAL V1 RELEASE GATE  
**Last Director update:** 2026-10-04  
**Repository:** `Leano-Jordan/ZazuEMP`  
**Branch:** `main`  
**Latest verified repository head:** `2595459029e63b2f3b074176268b34d0dd9448be`  
**Landing media:** real stock photography is vendored under `public/images/landing/stock/`; current-head runtime verification remains pending.  
**Latest application-changing candidate:** `33ec396f69ddcf50b217f986611b5120e9a58d4d` (current Director commits add verification/control evidence).  
**Note:** later UI/media hardening commits are part of the current candidate but do not constitute fresh runtime proof.

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
| Business isolation | ✅ Implemented | 🟢 Verified / Proven — populated two-business challenge |
| Roles / permissions | ✅ Implemented | 🟢 Verified / Proven — current role and populated authorization evidence |
| Auditability | ✅ Implemented | 🟡 End-to-end mutation trace |
| Experience level | ✅ Implemented | 🟡 Fresh-account traversal |
| Runtime regression suite | ✅ Implemented | 🟡 Historical pre-hardening run: 202 passed / 1,174 assertions; fresh current-head run pending |

# Gate 2 — Onboarding

| Area | Current state | Release proof |
|---|---|---|
| Registration → catalogue/setup | ✅ Implemented | 🟡 Historical browser journey passed; fresh current-head run pending |
| Basic / Intermediate / Advanced | ✅ Implemented | 🟡 Intermediate onboarding was exercised in a historical browser run; current-head run pending |
| Business identity/setup | ✅ Implemented | 🟡 Historical browser journey passed; fresh current-head run pending |
| Deferral/resume behaviour | ✅ Implemented | 🟡 Current-head runtime |
| Experience-level changes | ✅ Implemented | 🟡 Current-head runtime |
| Orphan-account workspace recovery | ✅ Implemented | 🟡 Historical Laravel coverage passed; fresh current-head run pending |
| Desktop onboarding | ✅ Implemented | 🟡 Historical browser journey passed; fresh current-head run pending |
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

- [x] Current-head Laravel suite passed: **242 tests / 1,381 assertions**.
- [x] Current-head browser smoke passed: **25 passed / 2 documented skips**.
- [x] Current-head Quality, PHPMD, Psalm Security Scan and CodeQL passed; SonarCloud is conditionally skipped.
- [x] Fresh migration succeeds.
- [x] Populated database upgrade succeeds without integrity loss — run `37203066817`.
- [ ] Populated end-to-end business workflow succeeds.
- [x] Search succeeds on populated data with authorization boundaries intact — populated two-business challenge passed.
- [ ] Quote → acceptance → invoice → deposit/payment reconciliation succeeds.
- [x] Desktop critical workflows pass in current browser smoke.
- [x] Mobile critical workflows pass in current browser smoke / Pixel 7 emulation; physical-device acceptance remains separately open.
- [x] Tablet critical workflows pass in current browser smoke emulation; physical-device acceptance remains separately open.
- [x] Accessibility smoke passes in current browser/Laravel evidence.
- [x] Backup succeeds — run `37203066817`.
- [x] Restore succeeds — run `37203066817`.
- [x] Restored records and private media are verified — run `37203066817`.
- [x] Rollback procedure is exercised — run `37203066817`.
- [x] Final populated authorization/security challenge passes — Laravel run `37203066802`.
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

Historical/current-candidate evidence remains useful context but is not treated as fresh certification for the latest hardening ref. Populated-data workflow proof, full authorization challenge, backup/restore, representative upgrade and rollback remain open.

## Immediate execution priority

**Release Gate Sprint — remaining:**

1. Legal / privacy operational closure
2. Release-level dependency / transitive licence audit
3. Zazu brand / trade-mark clearance
4. Physical phone/tablet acceptance
5. Final Director re-audit

No broad feature expansion should displace these gates while any Critical item remains open.



# Director commercial audit reconciliation — 2026-10-03

The canonical V1 checklist remains the release authority. The Director commercial audit adds the following explicit interpretation:

**Overall commercial-readiness maturity: 67/100.**

This score measures maturity across product, code, data, UX, mobile, security, recovery, deployment, legal and evidence domains. It is not a percentage of code completed.

### Highest-risk unresolved gates

| Gate | /100 | Work left | Release effect |
|---|---:|---:|---|
| Populated end-to-end commercial workflow | 60 | 40 | Blocks certification |
| Commercial reconciliation | 60 | 40 | Blocks certification |
| Current-head verification | 54 | 46 | Blocks certification |
| Mobile/tablet critical workflows | 65 | 35 | Blocks mobile acceptance |
| Backup / restore | 42 | 58 | Blocks certification |
| Populated upgrade | 44 | 56 | Blocks certification |
| Rollback exercise | 40 | 60 | Blocks certification |
| Legal/privacy operational closure | 44 | 56 | Launch gate |
| Brand/trade-mark clearance | 58 | 42 | Commercial gate |
| Helper implementation | 42 | 58 | Helper-specific gate |

### Director rule for this release

Do not interpret “implemented” as “commercially proven”. V1 remains uncertified until the unchecked finish-line items in this document have current evidence.

### Mobile-first acceptance rule

Critical workflow acceptance must include a representative phone viewport and a tablet viewport, not merely responsive source inspection. Review:
- navigation;
- forms;
- tables/lists;
- quote presentation and acceptance;
- finance/payment;
- purchasing;
- documents/media;
- toasts/confirmation;
- Helper presence/placement;
- overflow, clipping and occlusion;
- keyboard/focus/reduced-motion behaviour.

### Current dependency/legal benchmark

For accessibility, use WCAG 2.2 as the current target framework.  
For web application security verification, use OWASP ASVS 5.0.0 as the engineering benchmark.

These are benchmarks, not certification claims.

Last updated: 2026-10-03


## Director release-documentation closure — 2026-10-04

### Documentation / compliance

| Area | Status | Evidence |
|---|---|---|
| Release documentation set | 🟢 VERIFIED COMPLETE — design set | `docs/RELEASE_DOCUMENTATION_INDEX.md` |
| Privacy notice | 🟡 FINALIZATION PENDING | Draft exists; real responsible-party/contact/deployment details required |
| Data-subject requests | 🟡 MANUAL CONTROL | Procedure exists; no dedicated self-service request portal found |
| Retention/disposal | 🟡 MANUAL CONTROL | Schedule exists; legal/accounting periods require approval |
| Incident response | 🟡 OPERATIONAL COMPLETION PENDING | Technical incident recorder exists; named organisational escalation required |
| Operator/data-processing | 🟡 CONDITIONAL | Template exists; execute where third-party processing exists |
| Customer terms | 🟡 FINALIZATION PENDING | Draft exists; legal/commercial review required |
| PAIA | 🟡 APPLICABILITY/PROCESS REVIEW | Operational control exists; legal determination required |
| Media rights/provenance | 🟡 FINAL SIGN-OFF PENDING | Register exists; final asset evidence required |
| Deployment / backup / restore / upgrade runbooks | 🟢 DOCUMENTED | Runbooks now present and aligned to implemented commands |

The documentation-design gate is closed. The legal/privacy compliance gate remains open until the real-world completion evidence exists.

### Brand

`docs/ZAZU_BRAND_CLEARANCE.md` is now the active brand-clearance record.

**Disposition:** 🔴 OPEN / HIGH RISK — official CIPC search and appropriate professional clearance remain outstanding.

### Physical devices

`docs/PHYSICAL_DEVICE_ACCEPTANCE.md` is now the acceptance record.

**Disposition:** 🟡 OPEN — Playwright emulation is not promoted to physical-device acceptance.

Last updated: 2026-10-04
