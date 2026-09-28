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
