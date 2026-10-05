# ZAZU EMP — DISCOVERY & DESIGN ENGINE CONTRACT

## Purpose

Determine what Zazu should do, where the behaviour lives, what can safely change, and what must remain invariant.

## Authority boundary

This file is the **domain contract** for the Discovery & Design capability. Reusable procedure lives in `.agents/skills/discovery-design/SKILL.md`; deeper historical/domain material is in the skill's `references/` directory.

Director/Morpheus remains the sole entry point and final acceptance authority. This capability does not maintain a competing backlog, project state or acceptance decision.

## Activation / scope

Routes reconnaissance, impact/workflow tracing, architecture/design boundaries, product/user/competitive review and fresh-eyes challenge.

## Required output

Before implementation, return target, observed/desired behaviour, affected surfaces, invariants, risk, chosen approach, acceptance and verification strategy.

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

See `.agents/skills/discovery-design/SKILL.md` and its referenced domain material. The contract intentionally stays small so agents do not load every procedure when it is irrelevant.
