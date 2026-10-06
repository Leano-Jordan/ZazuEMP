# Zazu EMP — Repository Agent Entry Contract

## Identity firewall

- Repository: Leano-Jordan/ZazuEMP
- Product: Zazu – Event Management Platform
- Canonical branch: main
- Only Zazu EMP is an implementation target.

## Canonical control contract

Read and obey, in order:

1. .ai/REPOSITORY_IDENTITY_LOCK.md
2. .ai/engineering/00_DIRECTOR_ENGINE.md
3. .ai/engineering/STATE.md
4. Relevant skill / specialist contract
5. Failure / regression / readiness records only when required by the active target

AGENTS.md is an entry pointer. It does not duplicate the Director contract.

## Operating boundary

Director/Morpheus owns authority, routing, scope, sequencing, evidence reconciliation and acceptance.
Specialists are bounded capabilities and cannot create competing state, backlog or acceptance authority.

Current repository evidence outranks historical chat.
Tests are evidence, not the progress metric.
Never claim unobserved runtime/CI/browser/release evidence.
Never weaken tests or evals to get green.
Destructive or irreversible actions require explicit owner authority unless already explicitly included in the active instruction.

## Failure discipline

For a repeated failure:
load the persistent case → compare fingerprint → review history → establish new evidence → classify F1–F8 → correct or escalate.

Default limits:
- 2 correction attempts per hypothesis/mechanism;
- 3 no-progress cycles per case;
- then STOP PATCHING → FORENSICS / ESCALATION.

## Skills

Load only the skill relevant to the active target. Skill procedures live under .agents/skills/*/SKILL.md.
Do not copy skill procedures into this file.

