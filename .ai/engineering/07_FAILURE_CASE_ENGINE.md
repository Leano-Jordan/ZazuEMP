# ZAZU EMP — FAILURE CASE / LOOP-BREAKING ENGINE CONTRACT

## Purpose

Prevent recurring diagnosis and patch loops by treating failures as persistent engineering cases.

## Authority boundary

This file is the **domain contract** for the Failure Case / Loop-Breaking capability. Reusable procedure lives in `.agents/skills/failure-forensics/SKILL.md`; deeper historical/domain material is in the skill's `references/` directory.

Director/Morpheus remains the sole entry point and final acceptance authority. This capability does not maintain a competing backlog, project state or acceptance decision.

## Activation / scope

Fingerprinting, case history, hypotheses, experiments, attempt budgets, escalation and closure evidence.

## Required output

Return existing/new case identity, fingerprint, classified layer, next diagnostic step and whether correction is permitted.

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

See `.agents/skills/failure-forensics/SKILL.md` and its referenced domain material. The contract intentionally stays small so agents do not load every procedure when it is irrelevant.
