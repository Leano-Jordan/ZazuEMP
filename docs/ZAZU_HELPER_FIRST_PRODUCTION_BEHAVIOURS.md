# Zazu Helper — First 10 Production Behaviours

**Status:** Production behaviour foundation  
**Date:** 2026-10-03  
**Authority:** Zazu Helper Engine Specification

## 1. Purpose

These are the first ten behaviours worth proving in the real Zazu interface. They are deliberately small, reusable and meaningful to daily work.

They are not ten separate animation files. They are ten semantic behaviours implemented through the shared Helper state machine and expressed by the selected skin.

## 2. Behaviour 01 — Rest

**Intent:** Be present without demanding attention.

**Typical state:** IDLE

**Trigger:** Helper is not handling an active interaction.

**Expression:** A restrained idle pose or very small periodic motion.

**Rules:** No wandering. No constant movement across the workspace. No attention sound by default.

**Reduced motion:** Static or near-static.

**Acceptance:** The Helper can remain visible for a normal work session without becoming distracting.

## 3. Behaviour 02 — Contextual Notice

**Intent:** Tell the user that something relevant has been noticed.

**Typical state:** NOTICE

**Trigger:** A trusted application event, such as overdue preparation, outstanding purchasing or incomplete setup.

**Expression:** Brief attention change, then an optional short approach toward the related semantic anchor.

**Rules:** Do not repeatedly interrupt the user for the same unresolved condition. The notice must include accessible text.

**Approval:** None is required merely to surface information.

**Acceptance:** The user can identify what needs attention and why the Helper appeared.

## 4. Behaviour 03 — Guide Me

**Intent:** Take the user to a requested area of Zazu.

**Typical states:** NOTICE → APPROACH → POINT

**Trigger:** Explicit user request such as opening a module, locating a field or finding a task.

**Expression:** Move to the resolved semantic anchor and settle near it without blocking controls.

**Rules:** Use semantic targets, never permanent screen coordinates. On mobile, use the nearest useful contextual placement.

**Acceptance:** The same request works on desktop and mobile despite different layouts.

## 5. Behaviour 04 — Point / Direct Attention

**Intent:** Make a specific target obvious.

**Typical state:** POINT

**Trigger:** A contextual notice, guide request or explanation that has a resolved target.

**Expression:** Clear orientation plus a short directional gesture. The target itself may also receive a normal UI highlight.

**Rules:** The Helper must not cover the target. Do not use motion as the only information channel.

**Reduced motion:** Use orientation or a static indicator.

**Acceptance:** A tester can correctly identify the intended target without guessing.

## 6. Behaviour 05 — Explain

**Intent:** Provide useful context about a business workflow or condition.

**Typical states:** EXPLAIN → LISTEN

**Trigger:** User asks what something means, why it matters or what to do next.

**Expression:** Helper settles into an attentive pose while the normal Zazu panel or contextual UI presents the explanation.

**Rules:** Explanation content comes from application/domain context. The character never invents a business result.

**Acceptance:** The explanation remains fully understandable when the animation is disabled.

## 7. Behaviour 06 — Listen and Think

**Intent:** Show that the Helper is receiving a request and processing it.

**Typical states:** LISTEN → THINK

**Trigger:** User input has been received and the system is preparing a response.

**Expression:** Small listening acknowledgement followed by a short thinking state.

**Rules:** Thinking must not pretend that an external operation has completed. It should be interruptible.

**Reduced motion:** Simple status transition with little or no movement.

**Acceptance:** The UI clearly distinguishes waiting for input from processing input.

## 8. Behaviour 07 — Approved Work / Progress

**Intent:** Represent an authorised task currently being performed by the system.

**Typical states:** THINK → WORKING → WAITING

**Trigger:** An application/domain operation has been explicitly authorised and started.

**Expression:** A small repeatable work cycle or position near a relevant target. Progress information remains in the normal UI.

**Rules:** The animation is not the source of truth. The underlying operation determines actual progress, completion and failure.

**Acceptance:** Stopping or disabling animation does not affect the task or its business state.

## 9. Behaviour 08 — Confirmed Success

**Intent:** Acknowledge a confirmed successful result.

**Typical state:** SUCCESS

**Trigger:** The application/domain layer confirms success.

**Expression:** Short celebratory gesture, then RETURN.

**Rules:** Never show success merely because a request was sent. Success requires confirmation from the authoritative operation.

**Reduced motion:** Brief visual acknowledgement or static success state.

**Acceptance:** Failed or unconfirmed operations can never produce the success behaviour.

## 10. Behaviour 09 — Warning or Error

**Intent:** Distinguish something requiring attention from something that failed.

**Typical states:** WARNING or ERROR

**Trigger:** Application/domain state reports a warning, block or failure.

**Expression:** Brief concerned or stopped state, followed by clear panel/status communication.

**Rules:** The character should not panic, loop indefinitely or obscure the actual message. Error meaning belongs to the UI/domain message, not facial drama.

**Acceptance:** A user can tell that attention is required and can access the underlying reason without relying on the animation.

## 11. Behaviour 10 — Offline and Sync

**Intent:** Make connectivity and synchronisation state understandable.

**Typical states:** OFFLINE → SYNCING → SUCCESS/RETURN or ERROR

**Trigger:** Local/offline status changes or synchronisation begins/ends.

**Expression:** Calm offline/rest state; visible syncing activity only while synchronisation is actually running; completion/error acknowledgement afterward.

**Rules:** Offline is not an error by itself. Never imply that server/cloud work succeeded without confirmation.

**Reduced motion:** Static connectivity/status indicator plus text.

**Acceptance:** The user can distinguish offline operation, waiting, active syncing, confirmed completion and sync failure.

## 12. Shared behaviour rules

All ten behaviours must follow these rules:

- semantic intent is identical across skins;
- movement is purposeful, not decorative noise;
- user requests outrank idle motion;
- errors and offline states can interrupt low-priority motion;
- the Helper never becomes the authority for business data;
- essential information is available in normal UI text/status;
- every behaviour has a reduced-motion and static fallback;
- every movement can be cancelled safely;
- the Helper returns to a valid resting state after completion.

## 13. Production proof order

Implement and prove in this order:

**Rest → Notice → Guide Me → Point → Explain → Listen/Think → Approved Work → Success → Warning/Error → Offline/Sync**

This sequence gives Zazu a usable Helper before introducing richer agent behaviour.

## 14. Cross-skin proof

Each of the ten behaviours must be demonstrated against the same semantic test cases for Gecko, Ant and Chameleon.

The implementation should be considered correct when changing skin changes only the physical expression, not the event, state, target, approval or outcome.

## 15. R0 implementation rule

None of these ten behaviours requires a paid animation platform.

The V1 proof can be built with owner-supplied SVG artwork plus CSS and the browser Web Animations API. The engine contract must remain separate from that renderer.

## 16. Definition of done

The first production pass is complete when the ten behaviours work on desktop and mobile, respect reduced-motion preferences, survive navigation/interruption cleanly, do not cover important UI, and remain semantically identical across the selected skin implementations.