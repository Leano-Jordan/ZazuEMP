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
