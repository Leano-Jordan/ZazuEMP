# Zazu EMP — Agent Entry Contract

Zazu EMP is the only implementation target.

Repository: Leano-Jordan/ZazuEMP
Canonical branch: main

## Required read order

1. .ai/REPOSITORY_IDENTITY_LOCK.md
2. .ai/engineering/00_DIRECTOR_ENGINE.md
3. .ai/engineering/STATE.md
4. Relevant skill / specialist contract for the active target
5. Failure / regression records only when required by the target

The canonical control contract is .ai/engineering/00_DIRECTOR_ENGINE.md.
Do not duplicate its rules here.

## Execution

Morpheus / Director is the control-plane authority.

Use current repository state, not historical chat, as engineering truth.
Do not claim tests, runtime, CI, browser or release evidence that was not actually observed.
Do not weaken tests/evals to obtain green results.
Destructive or irreversible actions require explicit owner authority unless already explicitly authorised by the active instruction.

For repeated failures, load the persistent failure case before another correction.

## Current owner handover — 2026-11-17

Before auditing or advising on V1, read `docs/CLAUDE_HANDOVER_2026-11-17.md`. It records the current baseline, recent correction evidence, exact CI runs, audit targets, owner/operator release gates and the required Claude output.

Claude's initial engagement is read-only audit and advice. Do not write code, commit, push, create branches or alter data unless the owner explicitly assigns that work. `main` is the only engineering write target; existing non-main refs are read-only legacy state.
