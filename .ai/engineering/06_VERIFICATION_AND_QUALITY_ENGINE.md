# ZAZU EMP — VERIFICATION & QUALITY ENGINE CONTRACT

## Purpose

Establish evidence for correctness and classify failures before application correction.

## Authority boundary

This file is the **domain contract** for the Verification & Quality capability. Reusable procedure lives in `.agents/skills/verification/SKILL.md`; deeper historical/domain material is in the skill's `references/` directory.

Director/Morpheus remains the sole entry point and final acceptance authority. This capability does not maintain a competing backlog, project state or acceptance decision.

## Activation / scope

Source integrity, PHPUnit, Playwright, UI/accessibility, static analysis, CI/workflow, dependency, runtime/manual and recovery evidence.

## Required output

Return target, checks, result, F1–F8 classification, correction layer, evidence, uncertainty and regression disposition. A green check proves only its exercised layer.

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

See `.agents/skills/verification/SKILL.md` and its referenced domain material. The contract intentionally stays small so agents do not load every procedure when it is irrelevant.
