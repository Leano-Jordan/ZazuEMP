# ZAZU EMP — REPOSITORY IDENTITY LOCK

This repository is **Zazu EMP only**.

## Canonical identity
- Repository: `Leano-Jordan/ZazuEMP`
- Canonical branch: `main`
- Product: Zazu – Event Management Platform
- Local working root: `C:\Projects\ZazuEMP`

## HARD WRITE GATE
Before **any** file creation, modification, deletion, package installation, migration, database mutation, commit, branch operation, or other state-changing operation:
1. Confirm the working directory is the Zazu working root.
2. Confirm the Git repository root resolves to `C:\Projects\ZazuEMP`.
3. Confirm the Git remote resolves to `Leano-Jordan/ZazuEMP`.
4. Confirm the intended branch is `main`, unless the owner explicitly names another Zazu branch.
5. Confirm the target path is inside the Zazu repository root.
6. Confirm every repository-writing tool call explicitly targets `Leano-Jordan/ZazuEMP`.
7. If any check fails, **STOP. Do not repair, switch, checkout, pull, clone, or modify another repository.**
8. Report the mismatch and wait for explicit correction.

Repository identity must never be inferred from a similar folder, parent directory, open editor tab, stale terminal, conversation memory, another repository's instructions, or copied project files.

## CROSS-PROJECT ISOLATION
Outside products and repositories may be researched when relevant, but no outside repository is an implementation target unless the owner explicitly changes the active project.

Never copy another project's code, architecture, database schema, migrations, business rules, product requirements, prompts, memory, release specifications, or project instructions into Zazu unless the owner explicitly requests a reviewed adaptation.

An external project mentioned in a task is **evidence or context only**, never an implicit implementation target.

## NO AUTOMATIC PROJECT SWITCHING
If a task mentions another product, repository, historical project, competitor, migration source, or comparison target:
- do not change Git remotes;
- do not change directories for implementation;
- do not checkout another repository;
- do not commit there;
- do not modify its working tree;
- return to the Zazu identity gate before any Zazu write.

If repository identity becomes uncertain at any point: **STOP WRITING.** Do not guess.

## SOURCE-OF-TRUTH ORDER
1. Current Zazu repository files and current Git state
2. Explicit owner instruction in the current task
3. Current Zazu project documentation
4. Verified external research when explicitly relevant

No external project's documentation can outrank Zazu repository state.

## REQUIRED PRE-WRITE PROOF
```text
ROOT    = C:\Projects\ZazuEMP
REMOTE  = Leano-Jordan/ZazuEMP
BRANCH  = main
TARGET  = inside ROOT
```

Only then may a write proceed.

## FAILURE CONDITION
A repository mismatch is a **safety failure**, not a reason to continue. If the active repository cannot be proven to be Zazu EMP, make no state-changing operation anywhere.
