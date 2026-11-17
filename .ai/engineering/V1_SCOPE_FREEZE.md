# Zazu EMP V1 — Scope Freeze and Finish Rule

**Status:** OWNER-DIRECTED / ACTIVE — OFFLINE V1 WORK RESUMED 2026-11-17  
**Effective:** 2026-11-12  
**Authority:** Owner instruction; this file is binding on Director and all specialist agents.

## Objective

Finish and release a dependable, usable Zazu V1. Do not pursue theoretical perfection.

## Scope is frozen

Only work required to make the agreed V1 customer workflows reliable is in scope. The current release checklist and verified defects define that scope. The owner explicitly resumed offline V1 engineering on 2026-11-17: service worker, IndexedDB, attachment queue/local file workflow, sync, PWA installation and recovery work may proceed where required for V1 acceptance.

**Explicit owner-approved addition — 2026-11-17:** Focused event scheduling and rental logistics are admitted to V1: multi-day event dates, start/end times, setup/collection dates, schedule clash warnings and asset allocation defaults using the logistics window. This is a bounded owner-authorised scope exception, not permission to add staff scheduling, quantity-based rental stock, workforce management or other adjacent features. Close this addition against the existing release checklist and exact-head regression evidence.

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

## Deployment boundary

The active product target is Zazu's local-first PC and phone experience. Do not introduce Render, AMPPS or an unapproved hosted deployment path. Validate the actual local installation and phone workflows; the Windows fresh-install package is not proof of full commercial readiness.

## Decision rule

**If it is not necessary for a customer to use the agreed Zazu V1 workflows reliably, it does not block V1.**

Director must load and obey this document at the start of every Zazu engineering cycle. It overrides speculative or excessively expansive task interpretation, while remaining subordinate to explicit newer owner instructions.
