# ZAZU EMP — ENGINEERING DECISION LOG

Durable decisions governing the Zazu engineering system.

## DEC-001 — Repository isolation
**Date:** 2026-09-28  
**Decision:** Current Zazu repository state and explicit owner instruction outrank historical conversation memory and external project context.  
**Reason:** Prevent wrong-project contamination.  
**Status:** ACTIVE

## DEC-002 — Morpheus control plane
**Date:** 2026-09-28  
**Decision:** Morpheus/Jarvis owns state, sequencing, routing and acceptance. Specialists perform bounded work.  
**Reason:** Prevent competing authorities and uncontrolled scope.  
**Status:** ACTIVE

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
**Decision:** Ordinary Director execution targets main directly. Temporary branches are not part of the Zazu execution model unless the owner explicitly authorizes another ref.
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

**Authority:** Director/Morpheus remains the single entry point. The detailed contract is docs/ZAZU_SAAS_READINESS_ARCHITECTURE.md.

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
