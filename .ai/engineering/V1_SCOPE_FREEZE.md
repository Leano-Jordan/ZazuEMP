# Zazu EMP V1 — Scope Freeze and Finish Rule

**Status:** OWNER-DIRECTED / ACTIVE  
**Effective:** 2026-11-12  
**Authority:** Owner instruction; this file is binding on Director and all specialist agents.

## Objective

Finish and release a dependable, usable Zazu V1. Do not pursue theoretical perfection.

## Scope is frozen

Only work required to make the agreed V1 customer workflows reliable is in scope. The current release checklist and verified defects define that scope.

A new item may enter V1 only when:
1. it is a reproducible defect in an agreed V1 workflow;
2. it creates a demonstrated security, privacy or data-integrity failure in that workflow; or
3. the owner explicitly authorises it.

Everything else—including speculative edge cases, optional polish, new features, architecture experiments, speculative compliance expansion and future hosting decisions—is deferred.

## Finish sequence

1. Close the known, concrete V1 blockers already recorded in the release checklist.
2. Verify the agreed core workflows end-to-end: account/workspace setup, event and catering operations, inventory where in scope, offline operation and synchronisation.
3. Run one final regression pass against the release candidate.
4. Fix only failures found in that pass that affect V1 reliability, security or data integrity.
5. Record known limitations and issue the V1 release decision.

## Stop rule

- Do not create new gates from hypothetical risks.
- Do not reopen a closed gate without new, reproducible evidence.
- Do not repeat investigations that produce no new evidence.
- Maximum two correction attempts per concrete failure hypothesis; if still failing, classify the cause and report the actual blocker rather than adding patches.
- Once the final regression pass is green, stop engineering scope expansion and prepare the release.
- A missing piece of evidence is only a blocker when that evidence is explicitly required to accept an agreed V1 workflow or release obligation.

## Render boundary

Render is an interim remote-development workbench so the owner can access Zazu away from the home development PC. It is not the final production destination. Do not treat Render production persistence, production hosting, or production infrastructure as V1 development gates unless the owner explicitly changes this direction. Keep the workbench usable; avoid expensive or destructive infrastructure changes.

## Decision rule

**If it is not necessary for a customer to use the agreed Zazu V1 workflows reliably, it does not block V1.**

Director must load and obey this document at the start of every Zazu engineering cycle. It overrides speculative or excessively expansive task interpretation, while remaining subordinate to explicit newer owner instructions.
