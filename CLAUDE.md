# Zazu EMP Agent Instructions

Read and obey `.ai/REPOSITORY_IDENTITY_LOCK.md` before any write.

## Repository identity
- Repository: `Leano-Jordan/ZazuEMP`
- Local root: `C:\Projects\ZazuEMP`
- Branch: `main`
- Product: Zazu EMP

If the current Git root, remote, branch, or target path cannot be proven to match the Zazu identity lock, **STOP**. Never switch repositories or modify another project.

Every repository write must explicitly target `Leano-Jordan/ZazuEMP`.

## Isolation
Other products, repositories and historical project material are external context only. They are never implementation authority or an implementation target for Zazu unless the owner explicitly changes the active project.

Do not copy external project code, architecture, data model, business rules, prompts, memory, release specifications or instructions into Zazu without explicit owner approval.

## Laravel
Use the existing project environment and dependencies. Do not automatically install packages, change PHP versions, run destructive database commands, or alter environment configuration unless required by the requested task.

## Engineering
For meaningful work, read `.ai/engineering/README.md` and `.ai/engineering/00_ENGINE_ROUTER.md`. All engines remain bound to Zazu EMP.

Before edits:
- inspect current repository state;
- verify repository identity;
- inspect relevant files;
- verify requested scope.

After meaningful edits:
- verify changed behaviour where possible;
- perform required regression checks;
- do not claim runtime verification without evidence.
