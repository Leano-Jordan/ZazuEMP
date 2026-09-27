# ZAZU EMP — REPOSITORY IDENTITY LOCK

This repository is **Zazu EMP only**.

## Canonical identity

- Repository: `Leano-Jordan/ZazuEMP`
- Canonical branch: `main`
- Product: Zazu – Event Management Platform
- Local working root: `C:\Projects\ZazuEMP`

## HARD WRITE GATE

Before any file creation, modification, deletion, package installation, migration, database mutation, commit, or other state-changing operation:

1. Confirm the working directory is the Zazu working root.
2. Confirm the Git repository root resolves to `C:\Projects\ZazuEMP`.
3. Confirm the Git remote resolves to `Leano-Jordan/ZazuEMP`.
4. Confirm the intended branch is `main`, unless the owner explicitly names another Zazu branch.
5. Confirm the target path is inside the Zazu repository root.
6. If any check fails, **STOP. Do not repair, switch, checkout, pull, clone, or modify another repository.**
7. Report the mismatch to the owner and wait for explicit correction.

The agent must never infer a repository from a similarly named folder, parent directory, open editor tab, stale conversation, remembered project, or another repository's documentation.

## CROSS-PROJECT ISOLATION

The following are external projects and are never implementation targets for Zazu:

- SwiftOrder
- `Leano-Jordan/store-ordering-system`
- Any other repository unless the owner explicitly changes the active project

References to external projects may exist only when explaining why they must not be mixed into Zazu. They are not sources of Zazu implementation truth.

Do not copy:
- code
- architecture
- database schemas
- migrations
- business rules
- product requirements
- prompts
- memory
- release specifications
- project instructions

from another project into Zazu unless the owner explicitly requests a reviewed adaptation.

## SOURCE-OF-TRUTH ORDER

For Zazu work, use this order:

1. Current Zazu repository files and current Git state
2. Explicit owner instruction in the current task
3. Zazu project documentation
4. Verified external research when explicitly relevant

Never allow another project's documentation to outrank Zazu repository state.

## NO AUTOMATIC PROJECT SWITCHING

If a task mentions another product for comparison, research, competitive analysis, migration or historical context:

- research it without treating it as the implementation repository;
- do not open or modify its working tree;
- do not change Git remotes;
- do not change directories to it for implementation;
- do not commit there;
- return to Zazu before any write.

## FAILURE CONDITION

If repository identity becomes uncertain at any point:

**STOP WRITING.**

Do not guess.

A repository mismatch is a safety failure, not a reason to continue.

## REQUIRED PRE-WRITE PROOF

The agent should be able to establish:

```
ROOT    = C:\Projects\ZazuEMP
REMOTE  = Leano-Jordan/ZazuEMP
BRANCH  = main
TARGET  = inside ROOT
```

Only then may a write proceed.
