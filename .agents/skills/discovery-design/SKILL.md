---
name: discovery-design
description: Perform bounded Zazu reconnaissance, impact analysis, architecture and workflow design before implementation.
---

Use for discovery, architecture, product/workflow impact, dependency tracing and target definition.

Read the authoritative current state first, then the relevant specialist contract under .ai/engineering.

Workflow:
BASELINE → TRACE → CLASSIFY → IMPACT → DESIGN → ACCEPTANCE

Rules:
- Repository evidence outranks memory.
- Do not treat every spec/code difference as a defect.
- Trace the actual workflow and dependent surfaces before proposing change.
- Prefer the smallest architecture that satisfies the approved contract.
- Surface uncertainty explicitly.
- Return a bounded target with invariants, acceptance evidence, blast radius and stop condition.
- Do not implement unless implementation is explicitly part of the routed task.
