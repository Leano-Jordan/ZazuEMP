# ZAZU EMP — MODULAR ENGINE SKILL REGISTRY

The Director control plane is deliberately small. Reusable procedures are loaded as skills; specialist engine documents remain deeper domain references.

## Control-plane skills

| Skill | Purpose |
|---|---|
| director-recon | Current repository/state reconnaissance |
| failure-forensics | Recurring failure recognition, fingerprinting and loop breaking |
| regression-audit | Post-change regression protection |
| verification | Test/runtime/CI evidence and F1–F8 classification |

## Domain skills

| Skill | Purpose |
|---|---|
| discovery-design | Reconnaissance, impact, architecture and workflow design |
| builder | Bounded implementation and debugging |
| guardian | Independent challenge, regression, security and integrity review |
| release-readiness | Commercial release, recovery, deployment and evidence gates |
| ui-recovery | Safe visual recovery/refinement |

## Architecture

DIRECTOR CONTROL PLANE → selects target and capability
SKILL → supplies reusable procedure
SPECIALIST ENGINE → supplies domain depth/reference rules
TOOLS → provide actual capabilities
REPOSITORY STATE → provides current facts
VERIFICATION / GUARDIAN → challenge the result
DIRECTOR → reconciles evidence and accepts/rejects

## Loading rule

Load only the skill(s) relevant to the active target.

Do not duplicate skill instructions into AGENTS.md, 00_DIRECTOR_ENGINE.md, or specialist contracts unless a rule is genuinely global.

## Anti-bloat rule

The master control plane contains authority, invariants and routing—not every engineering procedure.

Skills contain repeatable workflows.

Specialist engines contain deeper domain rules where those rules are still needed.

References contain background knowledge.

Ledgers/state contain current project facts.

Failure index contains institutional memory.

When a procedure becomes broadly reusable and independently triggerable, extract it into a skill instead of enlarging the master prompt.