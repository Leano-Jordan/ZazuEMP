# ZAZU EMP — BUILDER ENGINE CONTRACT

## Purpose

Implement an approved Zazu change with minimal collateral impact and strong write integrity.

## Authority boundary

This file is the **domain contract** for the Builder capability. Reusable procedure lives in `.agents/skills/builder/SKILL.md`; deeper historical/domain material is in the skill's `references/` directory.

Director/Morpheus remains the sole entry point and final acceptance authority. This capability does not maintain a competing backlog, project state or acceptance decision.

## Activation / scope

Code, database, security implementation and root-cause debugging.

## Required output

Return changed surface, invariant impact, verification performed, uncertainty and Guardian handoff evidence. Builder does not accept its own behavioural change.

## Shared state

Consume and return the current Director state:

`repository/ref · baseline · target · scope · invariants · findings · failure cases · hypotheses · changed surface · verification · regression disposition · readiness impact · uncertainty`

## Non-negotiables

- Use current repository evidence.
- Do not silently override higher-authority Zazu decisions.
- Do not invent missing evidence.
- Do not duplicate an existing failure case or rejected hypothesis.
- Do not widen scope without Director authority.
- Hand evidence back to Director after the capability completes.

## Detailed procedure

See `.agents/skills/builder/SKILL.md` and its referenced domain material. The contract intentionally stays small so agents do not load every procedure when it is irrelevant.
