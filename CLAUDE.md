# Zazu EMP Agent Instructions

Read and obey `.ai/REPOSITORY_IDENTITY_LOCK.md` before any write.

## Repository identity

- Repository: `Leano-Jordan/ZazuEMP`
- Local root: `C:\Projects\ZazuEMP`
- Branch: `main`
- Product: Zazu EMP

If the current Git root, remote, branch, or target path does not match the Zazu identity lock, **STOP**. Do not switch repositories or modify another project.

Zazu must never be mixed with SwiftOrder, `Leano-Jordan/store-ordering-system`, or another project.

## Laravel

Use the existing project environment and dependencies. Do not automatically install Laravel Boost, packages, change PHP versions, run destructive database commands, or alter environment configuration unless the requested task requires it and the owner has directed execution.

## Engineering

For meaningful work, read `.ai/engineering/README.md` and `.ai/engineering/00_ENGINE_ROUTER.md`. Use only the engines relevant to the task. All engines remain bound to Zazu EMP and may not select another repository as an implementation target.

Before edits:
- inspect current repository state;
- inspect relevant files;
- verify the requested scope.

After meaningful edits:
- verify the changed behaviour where possible;
- perform required regression checks;
- do not claim runtime verification that was not performed.

External products and competitors may be researched as evidence, but their code, architecture, documentation and requirements are not Zazu authority.
