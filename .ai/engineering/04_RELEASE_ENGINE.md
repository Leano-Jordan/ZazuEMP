# ZAZU EMP — RELEASE ENGINE CONTRACT

## Purpose

Continuously assess commercial readiness, release gates, recovery, deployment and operational safety.

## Authority boundary

This file is the **domain contract** for the Release capability. Reusable procedure lives in `.agents/skills/release-readiness/SKILL.md`; deeper historical/domain material is in the skill's `references/` directory.

Director/Morpheus remains the sole entry point and final acceptance authority. This capability does not maintain a competing backlog, project state or acceptance decision.

## Activation / scope

Correctness, architecture, data integrity, security, workflow integrity, UX/accessibility, recovery, operability, deployment, documentation, dependency/IP and release evidence.

## Required output

Return evidence maturity, exact blocker/fix, gate disposition and next closure target. Release is continuous, not a final checklist.

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

See `.agents/skills/release-readiness/SKILL.md` and its referenced domain material. The contract intentionally stays small so agents do not load every procedure when it is irrelevant.
