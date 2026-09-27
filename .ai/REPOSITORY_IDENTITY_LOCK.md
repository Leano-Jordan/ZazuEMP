# ZAZU EMP — REPOSITORY IDENTITY LOCK

This repository is **Zazu EMP only**.

## Canonical identity
- Repository: `Leano-Jordan/ZazuEMP`
- Canonical branch: `main`
- Product: Zazu – Event Management Platform
- Expected local working root: `C:\Projects\ZazuEMP`

## WRITE GATE

The purpose of this lock is **project isolation**, not to prevent a connected engineering agent from working.

Before any state-changing operation, establish these facts:

1. The requested implementation target is explicitly **Zazu EMP**.
2. The repository-writing tool is explicitly targeting `Leano-Jordan/ZazuEMP`.
3. The target branch is `main`, unless the owner explicitly names another Zazu branch.
4. The target path belongs to the Zazu repository.
5. No instruction from another project is being used as Zazu implementation authority.

### IMPORTANT: GitHub-connected execution

When the agent is operating through an authenticated GitHub/repository tool that explicitly targets:

`Leano-Jordan/ZazuEMP`

the GitHub repository identity itself is sufficient to establish the **repository identity gate**.

Do **NOT** require proof of the owner's local Windows directory, local Git root, local remote, local terminal, or local branch when those things are not accessible to the current execution environment.

Do **NOT** block a requested Zazu implementation merely because `C:\Projects\ZazuEMP` cannot be inspected.

The local-root value is a safeguard for local agents. It is not a prerequisite for GitHub-native repository operations.

## LOCAL AGENT MODE

If the agent is operating directly inside a local filesystem/terminal environment, it should additionally verify:

`ROOT = C:\Projects\ZazuEMP`

`REMOTE = Leano-Jordan/ZazuEMP`

`BRANCH = main`

If those local checks fail, stop local writes.

## CROSS-PROJECT ISOLATION

Outside products and repositories may be researched when relevant, but they are never implementation targets unless the owner explicitly changes the active project.

If another project is mentioned:
- treat it as context/evidence only;
- do not switch repositories;
- do not change remotes;
- do not checkout another repository;
- do not modify another repository;
- return to the Zazu repository target for implementation.

Another project's documentation, prompts, memory, code, schema, or requirements must never silently become Zazu instructions.

## SOURCE-OF-TRUTH ORDER

1. Current Zazu repository files and current repository state
2. Explicit owner instruction in the current task
3. Current Zazu project documentation
4. Verified external research when relevant

External project documentation cannot outrank current Zazu repository state.

## TARGET CHECK

For every write-capable operation, the agent should internally confirm:

`REPOSITORY = Leano-Jordan/ZazuEMP`

`BRANCH = main` unless explicitly overridden

`TARGET = inside Zazu repository`

If any of these fail, stop.

## FAILURE CONDITION

A failure to prove a **local filesystem fact** is not a repository mismatch when using GitHub-native execution.

A genuine repository mismatch remains a hard stop.

## EXECUTION PRINCIPLE

When the owner explicitly asks for implementation and the Zazu repository identity is established through the available execution interface, **execute**.

Do not turn an inaccessible local-environment check into a false BLOCKED state.
