# ZAZU EMP — ENGINEERING DECISION LOG

Durable decisions governing the Zazu engineering system.

## DEC-027 — Historical three-tier entitlement foundation

- **Date:** 2026-11-21
- **Status:** SUPERSEDED FOR CUSTOMER-FACING NAMING by DEC-030; entitlement continuity rules remain active.
- **Decision retained:** Zazu has exactly three workspace entitlement levels. Basic remains the permanent free fallback; the existing 30-day trial, signed entitlement lifecycle, data-preservation rules and permission boundaries remain in force.
- **Historical naming:** The third level was previously called Advanced. Its internal signed identifier remains `advanced` for compatibility; the customer-facing name is now Professional.
- **Implementation boundary retained:** No numerical quotas or feature gates may be inferred without explicit Founder approval.
- **Reason:** Preserve the working entitlement architecture while allowing the Founder to update product naming and capability positioning.

## DEC-001 — Repository isolation
**Date:** 2026-09-28  
**Decision:** Current Zazu repository state and explicit owner instruction outrank historical conversation memory and external project context.  
**Reason:** Prevent wrong-project contamination.  
**Status:** ACTIVE

## DEC-002 — Historical control-plane decision (superseded)
**Date:** 2026-09-28  
**Historical decision:** Morpheus/Jarvis owned state, sequencing, routing and acceptance. Specialists performed bounded work.  
**Reason at the time:** Prevent competing authorities and uncontrolled scope.  
**Status:** SUPERSEDED by the consolidated single-role Director model in `.ai/engineering/README.md`, `.ai/engineering/00_DIRECTOR_ENGINE.md` and the Commercial V1 Mandate. The Zazu Engine Director (Jarvis) is the sole accountable role; Morpheus is retired as a separate identity.

## DEC-003 — State-driven delivery
**Date:** 2026-09-28  
**Decision:** Meaningful work follows baseline → target → inspect → design → change → verify → break → accept → record → next.  
**Reason:** Prevent loops, stagnation and untracked progress.  
**Status:** ACTIVE

## DEC-004 — Tests are evidence
**Date:** 2026-09-28  
**Decision:** Architecture, correctness, hardening, integrity and commercial risk reduction are the primary objectives. Tests are used when they improve evidence.  
**Reason:** Prevent metric gaming.  
**Status:** ACTIVE

## DEC-005 — Forensics before repeated patching
**Date:** 2026-09-28  
**Decision:** Two failed correction attempts against one root cause trigger forensic analysis.  
**Reason:** Prevent symptom patching and regression chains.  
**Status:** ACTIVE

## DEC-006 — Commercial readiness is continuous
**Date:** 2026-09-28  
**Decision:** Release risk is tracked during normal engineering, not only at the end.  
**Reason:** Keep work pointed at real release blockers.  
**Status:** ACTIVE



## DEC-007 — Progressive disclosure over capability removal
**Date:** 2026-10-01
**Decision:** Zazu will manage interface complexity primarily through progressive disclosure. Capability should remain available underneath the interface rather than being removed to make screens look simple.
**Reason:** Preserve broad business capability while keeping the default experience approachable.
**Status:** ACTIVE

## DEC-008 — Landing development visuals are not release assets by default
**Date:** 2026-10-01
**Decision:** Development-stage landing imagery, visual references, custom-art experiments and mascot concepts may be used to establish Zazu identity. Release requires a separate IP/license/ownership audit.
**Reason:** Design exploration and commercial release clearance are different gates.
**Status:** ACTIVE

## DEC-009 — Optional capabilities must not compromise local-first core
**Date:** 2026-10-01
**Decision:** OCR, voice, maps, external messaging, translation, cloud services and similar capabilities are optional enhancements unless explicitly promoted to core. Core workflows must remain functional without them where local-first architecture permits.
**Reason:** Preserve reliability, offline operation, privacy and continuity.
**Status:** ACTIVE

## DEC-010 — External dependency register
**Date:** 2026-10-01
**Decision:** Every new open-source library or external API considered for Zazu must be recorded with license, data-egress, offline, security, product-surface and release-priority information before adoption.
**Reason:** Prevent dependency sprawl, licensing surprises and accidental online coupling.
**Status:** ACTIVE


## DEC-011 — Verification is a first-class control plane
**Date:** 2026-10-01
**Decision:** Test, browser, static-analysis, CI/workflow, dependency, runtime and recovery evidence are separate verification layers. Failures must be classified before application correction.
**Reason:** Prevent test defects, fixtures, CI/tooling failures and application defects from being mixed together and causing regression chains.
**Status:** ACTIVE

## DEC-012 — Main-only Director execution
**Date:** 2026-10-01
**Decision:** All Director execution targets canonical `main` directly. Non-`main` branches are prohibited for normal and exceptional work alike. Only a new explicit owner instruction in the current task may change this hard rule; generic Git workflow conventions, historical records, and tool defaults cannot override it.
**Reason:** Prevent abandoned branches, sync residue and fragmented working state.
**Status:** ACTIVE

## DEC-013 — Single visual token authority
**Date:** 2026-10-01
**Decision:** Zazu must not accumulate append-only visual token/sweep layers. Shared visual tokens are consolidated into one light-theme root and one dark-theme root; components inherit them.
**Reason:** Prevent conflicting colour, typography and surface relationships.
**Status:** ACTIVE


## DEC-014 — Failure Case / Loop-Breaking control

**Date:** 2026-10-01

**Decision:** Repeated and release-significant failures use persistent case identity, failure fingerprints, hypothesis history, attempt budgets and mandatory diagnostic escalation. The Director must load the existing case before authorizing another correction.

**Reason:** Prevent the engineering system from looping through variations of the same diagnosis or patch without materially new evidence.

**Status:** ACTIVE


## DEC-015 — SaaS-ready boundary without premature distributed infrastructure

**Date:** 2026-10-02

**Decision:** Zazu shall be architecturally prepared for hosted multi-business SaaS while remaining a coherent modular monolith until measured evidence requires scaling or service extraction. Business ownership, parent/child invariants, state transitions, idempotency, persistence boundaries and infrastructure seams are strengthened now; distributed infrastructure is not added speculatively.

**Reason:** Preserve the current product's simplicity and local-first reliability while preventing future SaaS migration from requiring a rewrite of business rules or historical data.

**Authority:** Zazu Engine Director (Jarvis) is the single entry point. The detailed architecture reference is docs/ZAZU_SAAS_READINESS_ARCHITECTURE.md.

**Status:** ACTIVE


## DEC-016 — One product, tiered operational sophistication
**Date:** 2026-10-02

**Decision:** Zazu commercial packaging will use one product with increasing operational sophistication rather than four separate products or arbitrary record-count restrictions.

Working commercial hypotheses:
- R299 Basic — keep the business organised;
- R499 Professional — run jobs and money;
- R799 Business — run people and operations;
- R1,299 Business Plus — control larger/more complex operations.

**Reason:** Give each higher tier a meaningful business outcome while protecting V1 from feature sprawl. The immediate commercial bridge is Event → Quote → Deposit → Invoice → Payment → Purchasing → Receiving → Costs → Profit → Export.

**Authority:** Detailed requirements live in docs/ZAZU_COMMERCIAL_REQUIREMENTS.md and the Product Specification.

**Status:** ACTIVE


## DEC-017 — Niche focus is non-exclusive and adaptive

**Date:** 2026-10-03

**Decision:** A primary niche is a presentation emphasis, not a business classification, capability filter or permission boundary. Zazu may detect supporting niches from the business's active capability catalogue and surface them alongside the selected focus.

**Reason:** Real event businesses overlap. A DJ may also hire chairs, tents, décor or provide other services for the same job. Zazu must adapt to the actual business rather than forcing a single-industry identity.

**Status:** ACTIVE

## DEC-018 — Offline-first is the product architecture constraint

**Date:** 2026-10-03

**Decision:** Everyday Zazu business operation must be designed to work without ordinary internet access. Online mode enriches the product; it does not become the dependency for core local work.

**Reason:** Connectivity and electricity availability are uneven and cannot be treated as universal infrastructure.

**Status:** ACTIVE

## DEC-019 — Owner-approved pairing and selected bootstrap

**Date:** 2026-10-03

**Decision:** New sync devices require an owner-approved pairing code. Initial bootstrap is limited to business settings/catalogue plus records explicitly selected by the owner. A device must not receive a full historical business snapshot merely because it was paired.

**Reason:** Establish explicit business consent, minimize cross-device data exposure and preserve a bounded initial-sync contract.

**Authority:** Confirmed by the product owner during the offline sync foundation cycle.

**Status:** ACTIVE


## DEC-020 — Director decision gate for finding weight, churn and sprint scope

**Date:** 2026-10-04

**Decision:** The Zazu Engine Director (Jarvis) acts as the decision gate above the existing specialist engines. Before meaningful work is authorised, findings are classified, weighed by impact/evidence/urgency/effort, checked for modification churn, assigned an explicit scope decision and assessed for release impact.

**Scope rule:** Discovery may change priority without automatically changing the active sprint. New enhancements enter an active target only when required by the target, required by a newly discovered security/data-integrity/release risk, exchanged for comparable in-scope work, or explicitly authorised by the owner.

**Churn rule:** Repeated modification of the same file, behaviour or architectural boundary triggers GREEN/AMBER/RED churn assessment. RED stops symptom patching and routes to architecture/forensics. Existing failure-case attempt limits remain authoritative.

**Progress rule:** Commits, test-count growth, refactoring volume and reports are not progress metrics by themselves. Meaningful progress requires reduced risk, established causality, satisfied acceptance criteria, removed release blockers, clearer architecture or materially stronger evidence.

**Reason:** Combine the new Senior Architect/Sprint Controller discipline with Zazu's existing single-entry Director architecture without creating parallel engines, backlogs or acceptance authority.

**Status:** ACTIVE


## DEC-021 — One canonical Director read order and eval-owner gate

**Date:** 2026-10-06

**Decision:** `.ai/engineering/00_DIRECTOR_ENGINE.md` is the single canonical Director control contract and session read-order authority. `.ai/engineering/00_ENGINE_ROUTER.md` remains only as a compatibility pointer. Entry files must point to the canonical contract and must not define competing read-order rules. Director control-plane eval dispositions are pinned and changes to `.agents/evals/**` require repository-owner review.

**Reason:** Remove contradictory entry paths, prevent stale control-plane pointers and protect the Director regression contract from unilateral weakening.

**Status:** ACTIVE


## DEC-022 — Director control-plane review corrections

- **Date:** 2026-10-06
- **Status:** ACTIVE
- **Finding:** Independent review identified five control-plane gaps after the prior reconciliation: executable workflow YAML was incorrectly included in the literal backslash-n guard; eval dispositions were documentation-only pins; stale numbered router read-order pointers remained; three router rules were absent from the canonical contract; the active-case heading was misleading.
- **Decision:** Keep workflow YAML out of the literal backslash-n scan; hard-pin all 12 eval dispositions in the validator; make the canonical Director contract explicit on bounded refinement, active-target admission and test-failure classification; reconcile known read-order pointers; add a stale numbered-router guard; rename the active case section.
- **Evidence boundary:** Repository edits are implemented on main. CI/runtime verification remains pending and no application or control-plane closure is claimed from source edits alone.
- **Owner review:** Eval disposition changes remain owner-review-sensitive. CODEOWNERS declares the owner for .agents/evals/**; GitHub branch-protection enforcement is a repository setting and is not claimed as configured by this record.

## DEC-023 — Source-map-js security patch

- **Date:** 2026-10-06
- **Status:** ACTIVE
- **Finding:** The transitive development dependency `source-map-js` was pinned at vulnerable version 1.2.1; patched version 1.2.2 is available.
- **Decision:** Update the lockfile entry to `source-map-js` 1.2.2 without changing application code. Treat the asset build, dependency installation and browser checks as required verification before considering the security finding closed.
- **Reason:** Remove the known indexed-source-map offset validation denial-of-service exposure while keeping the dependency surface unchanged.
- **Evidence boundary:** Lockfile change is implemented; CI/runtime verification remains required.


## DEC-024 — Execution-first Director interaction

- **Date:** 2026-10-06
- **Status:** ACTIVE
- **Finding:** The control plane already required continuous execution, but its owner-facing contract did not explicitly prohibit intention-heavy/status-only interaction. In practice this allowed Jarvis sessions to consume conversational turns on planning/narration instead of repository progress.
- **Decision:** Owner-issued implementation requests are execution commands by default. After minimum identity and baseline checks, Jarvis must act immediately, continue through safe bounded cycles without re-authorisation, suppress internal reasoning/play-by-play narration, communicate only on meaningful deltas or genuine blockers, and automatically select the next safe target while the mission remains active.
- **Acceptance rule:** A response that only announces intention, repeats unchanged observations, or asks the owner to re-authorise already authorised work is an execution failure unless a defined stop condition applies.
- **Reason:** Maximise engineering progress per owner interaction and prevent the Director from behaving like a planning chatbot instead of an active engineering control plane.


## DEC-025 — Three-tier workspace entitlement and experience ceiling

- **Date:** 2026-11-13
- **Status:** ACTIVE
- **Owner authority:** Basic Free continuity, trial expiry, Advanced entitlement enforcement and the instruction that the three experience levels are the three payment tiers.
- **Decision:** The business/workspace entitlement sets the maximum selectable experience level. Each member keeps an individual presentation preference within that ceiling. Basic is the permanent free fallback; Intermediate and Advanced require their respective active signed entitlement, with a 30-day Advanced trial for eligible new workspaces.
- **Lifecycle:** Trial time is derived from workspace creation. An installed licence supersedes trial state; expired, revoked, not-yet-active or unknown entitlements resolve to Basic and do not restart a trial. The legacy `solo` plan identifier remains an Advanced compatibility alias.
- **Data/authorization invariant:** Tier expiry or downgrade never locks the workspace, deletes or withholds customer records, or weakens role/permission and business-isolation checks. No blanket plan middleware is applied to customer-data routes.
- **Matrix boundary:** This change enforces the experience ceiling only. It does not invent or enforce module restrictions, event/user/storage quotas or Intermediate pricing. Those remain pending explicit owner approval.
- **Reason:** Make the three commercial tiers meaningful in the existing experience preference and licensing architecture without introducing unapproved restrictions or risking customer-data continuity.


## DEC-026 — Founder-led commercial V1 convergence and one-target WIP

- **Date:** 2026-11-18
- **Status:** ACTIVE, with the initial DB-001-first sequencing superseded by DEC-028.
- **Authority:** Explicit Founder direction and the Founder-supplied current-state research report.
- **Decision:** Zazu execution is governed by a one-active-target WIP limit and a commercial V1 convergence mission. The original proposal to make DB-001 the first owner-assisted target is historical and is explicitly superseded by DEC-028. The one-target WIP limit, scope discipline, commercial convergence mission and evidence vocabulary remain active.
- **Historical priority sequence:** The original sequence placed DB-001 real-install closure first. Do not execute that superseded priority. Continue with reproducible engineering risks and genuine remaining acceptance gaps.
- **Scope freeze:** Sentinel and diagnostics are feature-frozen except verified maintenance; offline/sync scope does not expand without evidence; no new subsystem, broad refactor or subscription automation without explicit Founder approval. Known unchanged browser smoke tests are not repeated. CI syntax churn is reduced through local PHP verification where available and mandatory failure classification.
- **Commercial decisions:** Manual payment recording is sufficient for initial pilot acceptance. Pricing remains unresolved unless explicitly approved by the Founder.
- **Founder/operator model:** The Founder is the sole human operator and final authority. No fictional staff/team assumptions.
- **Acceptance vocabulary:** ENGINEERING COMPLETE, TESTS PASSED, VERIFIED ON FOUNDER PC, COMMERCIALLY USABLE and CUSTOMER VALIDATED are separate evidence states. Documentation and CI cannot substitute for actual installed-workflow or customer evidence.
- **Reason:** Prevent the engineering loop from optimising for internal tooling and documentation instead of a reliable, usable and sellable first release.

## DEC-028 — DB-001 historical disposition; stop repeat investigation

- **Date:** 2026-11-22
- **Status:** ACTIVE
- **Authority:** Explicit Founder clarification.
- **Finding:** The DB-001 occurrence under discussion happened during testing of offline event creation and a calendar-related task. It has not happened again since the subsequent offline-architecture work. It is not a confirmed current database defect; no root cause or corrective fix has been established.
- **Decision:** Classify DB-001 as **DORMANT / HISTORICAL / NON-CRITICAL**. It is not an active engineering task, not a V1 release blocker and not the current sprint target. Stop requesting owner-side schema/incident captures and stop spending engineering cycles investigating this occurrence.
- **Reopen condition:** Reopen only if the same failure is reproduced in a supported workflow on current `main`, or materially new evidence establishes a current defect. Do not infer recurrence from old diagnostic references, stale migration notes or unrelated installation-recovery work.
- **Separation of concerns:** The owner-operated installation backup/off-disk copy/full-restore acceptance remains a separate V1 recovery gate. It must not be labelled DB-001 or used to revive the dormant incident.
- **Recordkeeping:** Retain historical references and diagnostic tooling for traceability, but mark old DB-001 OPEN/critical/next-target notes as superseded. The top-level dispositions in `.ai/engineering/STATE.md` and `docs/V1_RELEASE_CHECKLIST.md` are authoritative.
- **Reason:** Keep Director effort focused on reproducible defects and real release risks instead of repeatedly pursuing an unconfirmed historical test-time anomaly.


## DEC-029 — Founder-directed public credibility and trust-surface work

- **Date:** 2026-11-26
- **Status:** ACTIVE
- **Authority:** Founder-directed execution following the public-credibility assessment.
- **Decision:** Improve Zazu's public trust surface before visual redesign. Build accurate Privacy, Terms, Contact, About, Security and What's New destinations, but keep Privacy and Terms as review copies until publication details and owner/legal review are complete. Defer a public status page until real monitoring exists.
- **Sequence:** Trust-surface foundation → source-derived quote/deposit/cost/profit demonstration → Founder-authorised non-commercial private pilot (synthetic data by default) → bounded CSS polish after critical release gates.
- **Credibility baseline:** Founder-provided judgement score 22/40 (55%), distinct from the formal evidence-maturity score 71/100. Do not combine or silently revise either.
- **Positioning boundary:** Zazu remains an event management platform, not a catering product. “From enquiry to profit” is accepted as a concise workflow-story direction, not proof that every stage is fully production-accepted. Preserve the V1 mandate's current promise until formally changed. Do not imply native WhatsApp integration or advertise module count as the primary value.
- **Commercial boundary:** No ads, charging, public pricing, public beta, paid pilot, fabricated testimonials, uptime/SLA claims or premature commercialisation.
- **Legal/identity boundary:** Follow `FOUNDER_LEGAL_BRAND_DECISIONS_2026-11-26.md`. Founder personally owns current IP and is current solo operator; Rosscore Labs is not registered/current owner; ZAZU name clearance is unresolved/high risk; Information Officer registration is incomplete; no production hosting or third-party processor is provisioned; provisional Outlook contact is not a dedicated mailbox. No legal notice or terms may be represented as effective until their publication gates close.
- **Engineering boundary:** Keep security/recovery/physical offline/commercial gates active. Trust-surface code must be low-risk public GET-only work, reuse existing Laravel architecture, and include access/content/route/accessibility tests. No branches; main only.
- **Record:** `docs/DIRECTOR_TRUST_SURFACE_EXECUTION_2026-11-26.md` and `.ai/engineering/STATE.md` are the execution handover and current state.


## DEC-030 — Basic / Intermediate / Professional capability model

- **Date:** 2026-11-27
- **Status:** ACTIVE
- **Authority:** Explicit Founder approval in the Zazu Engine Director session.
- **Decision:** Zazu's three customer-facing capability levels are **Basic, Intermediate and Professional**. Basic must let a small business genuinely operate; Intermediate adds connected operational control for growing businesses; Professional supports established and more complex operations.
- **Product model:** One product, three levels. Tier entitlement and approved capacity determine available business capabilities. Individual presentation preference controls surfaced detail only. Role-based permissions and business isolation remain independent.
- **Shared foundation:** Reliability, supported offline operation, security, data integrity, backup/export and access to existing customer-owned records are foundational across all levels. Offline reliability is never a premium feature.
- **Capacity boundary:** Customer, active-job, user, device, location, storage and automation limits are candidate dimensions only. No numerical thresholds, prices, billing rules or exact enforcement boundaries are approved by this decision.
- **Safe limit behaviour:** Limits must not delete or hide historical records, strand existing active work, discard queued offline mutations or prevent export. Prefer restricting creation of additional capacity after a clear explanation; retain access to existing data.
- **Compatibility:** The existing internal `advanced` entitlement/preference identifier maps to customer-facing Professional. Do not change signed token formats or stored identifiers without a separately tested migration.
- **Specification:** `docs/ZAZU_THREE_TIER_CAPABILITY_MATRIX.md` is the canonical capability design baseline. It does not assert that every listed capability is already implemented or authorize immediate enforcement.
- **Reason:** Create a meaningful growth path without undermining Zazu's offline-first reliability, data ownership or product simplicity.
## DEC-031 — Production portability without development-infrastructure coupling

- **Date:** 2026-11-28
- **Status:** ACTIVE
- **Authority:** Explicit Founder approval of the Director recommendation.
- **Decision:** Build Zazu for deployment, not around development. Application and domain logic must remain independent of the current owner-PC development host, temporary network addresses, local certificate authority and development access techniques.
- **Development boundary:** The current Windows LAN HTTPS/Caddy route is a development and physical-acceptance harness only. Do not add LAN discovery daemons, .local naming, VPN/tunnel clients, router port-forwarding or cloud hosting merely to remove development inconvenience.
- **Application boundary:** Use existing Laravel contracts for database, filesystem, session, cache and queue selection. Keep a supported standalone/local default. Do not introduce provider SDKs or managed infrastructure before a provider and production workload are selected.
- **Storage rule:** Private business files remain behind the logical private filesystem disk and application authorization. Provider configuration alone does not establish provider support. A remote storage choice requires adapter installation and security, tenant isolation, upload/download, archive, backup, restore, retention and recovery acceptance.
- **Recovery rule:** Existing installation backup/restore is local-directory recovery. It must not be represented as a remote-object-storage backup path. Remote storage requires its own verified consistency-aware backup and restore mechanism.
- **Database rule:** SQLite remains the fully exercised standalone baseline. PostgreSQL 16 currently has migration and selected recovery smoke coverage, not full application certification. No production database is selected by this decision.
- **Commercial boundary:** Production provider, server, domain, region, storage, database selection and recurring cost remain deferred until workload/cost analysis and explicit Founder approval. No infrastructure purchase is authorized.
- **Reason:** Avoid paying to build temporary development infrastructure twice, prevent production migration rewrites, and preserve a small, supportable application architecture.
- **Execution record:** docs/LOCAL_PC_HOST_DEPLOYMENT.md and .ai/engineering/STATE.md, 2026-11-28.
