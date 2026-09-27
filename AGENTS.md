# Zazu EMP Agent Instructions

## HARD PROJECT BOUNDARY
This repository is **Zazu EMP only**.

Before any state-changing operation, read and obey `.ai/REPOSITORY_IDENTITY_LOCK.md`.

Canonical identity:
- Repository: `Leano-Jordan/ZazuEMP`
- Local root: `C:\Projects\ZazuEMP`
- Active branch: `main`

### Mandatory repository gate
Before **every write-capable operation**:
1. Prove the Git root is `C:\Projects\ZazuEMP`.
2. Prove the remote is `Leano-Jordan/ZazuEMP`.
3. Prove the branch is `main`, unless the owner explicitly names another Zazu branch.
4. Prove the target path is inside that root.
5. Ensure the write-capable tool/action explicitly names `Leano-Jordan/ZazuEMP`.
6. If any proof is unavailable or mismatched, **STOP**. Do not switch repositories, repair another checkout, change remotes, checkout another branch/repository, or continue speculatively.

Natural-language references to another project never override this gate.

## PROJECT ISOLATION
External products and repositories can be researched when relevant, but they are never implementation targets for Zazu unless the owner explicitly changes the active project.

Never import another project's code, architecture, schema, business rules, product requirements, prompts, memory, release specifications, or project instructions into Zazu without explicit owner approval.

## Laravel Application
Use the existing PHP, Composer, Laravel, Node and frontend setup. Do not install, upgrade, downgrade, or replace development tooling merely because a generic guide recommends it.

Do not automatically run package installation, migrations, database resets, destructive commands, or environment changes unless required for the owner's requested task.

## Rosscore Engineering System
Before meaningful Zazu engineering work, read `.ai/engineering/README.md` and `.ai/engineering/00_ENGINE_ROUTER.md`. Activate only the specialist engines required by the task. Every specialist remains subordinate to the repository identity gate.

## Execution
- Inspect the current Zazu repository before changing it.
- Repository state outranks stale conversation memory.
- Read relevant current files before editing.
- Verify what can be verified and identify runtime-only checks as unverified.
- After automated writes to PHP, re-read the exact file and verify namespace/import/class integrity.
- Verification and regression gates are mandatory for meaningful changes.
- Keep changes scoped and reversible.

## Emergency rule
If an agent discovers that it has touched, inspected for implementation purposes, or selected the wrong repository:
1. Stop further writes immediately.
2. Report the repository identity mismatch.
3. Do not attempt to repair the wrong repository unless the owner explicitly requests that separate task.
4. Resume Zazu only after the Zazu identity gate passes.
