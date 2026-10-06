---
name: director-recon
description: Establish the current Zazu repository state before engineering work; use when auditing, debugging, changing, or verifying repository behavior.
---

Use this skill whenever meaningful Zazu engineering work starts.

1. Confirm repository identity: Leano-Jordan/ZazuEMP, branch main unless owner explicitly says otherwise.
2. Read repository-side control state before relying on conversation history:
   - .ai/REPOSITORY_IDENTITY_LOCK.md
   - .ai/engineering/README.md
   - .ai/engineering/00_DIRECTOR_ENGINE.md
   - .ai/engineering/STATE.md
   - .ai/engineering/FAILURE_CASES.md when a failure is involved
   - .ai/engineering/ERROR_INDEX.md when an error/failure is involved
3. Establish current HEAD and identify the relevant changed surface.
4. Inspect the actual implementation before designing a fix. Trace callers, callees, route/controller/data/view or component/service relationships as applicable.
5. Check recent commits before repeating a prior fix.
6. Treat repository evidence as current truth. Mark UNKNOWN when evidence is missing.
7. Output only the state needed to choose a safe target: baseline, target, evidence, dependencies, risks, and next diagnostic/engineering action.

Do not restart a full project audit when the target is already bounded and current state is available.
Do not infer a defect from a stale historical document.
Do not change source merely because a pattern looks unfamiliar.
