# Zazu Helper — Engine Specification

**Status:** Foundation / architecture-ready  
**Date:** 2026-10-03  
**Authority:** Zazu Helper character system

## 1. Purpose

The Zazu Helper Engine is the application-side system that gives the Helper consistent behaviour regardless of which animal skin the owner selects.

The engine owns **meaning and behaviour**.

The selected skin owns **physical expression**.

The renderer owns **how that expression is drawn and animated**.

The engine must therefore remain independent of Gecko, Ant or Chameleon anatomy, specific SVG artwork, individual Blade pages, screen coordinates, a particular animation vendor and business-domain mutation logic.

## 2. Core architecture

The intended flow is:

**Application event / user intent** → **Helper intent** → **Behaviour state machine** → **Semantic action** → **Semantic target resolver** → **Skin adapter** → **Renderer** → **Accessible status / UI communication**

Example: an overdue-work event becomes a notice, resolves to the semantic work.overdue target, moves the selected skin to the resolved anchor and exposes the same meaning through accessible UI text.

The Helper never decides the business fact itself. The application supplies the fact or intent.

## 3. Engine boundaries

### The engine is responsible for
- receiving semantic Helper events;
- validating supported actions;
- resolving behaviour transitions;
- managing the current semantic state;
- requesting semantic targets;
- asking the UI for resolved anchor positions;
- selecting the appropriate skin adapter;
- coordinating animation start, stop and return;
- handling interruptions;
- respecting reduced-motion and minimal-motion settings;
- exposing accessible text/status alongside visual behaviour;
- representing offline and syncing states;
- exposing deterministic events for testing.

### The engine is not responsible for
- creating or editing jobs;
- changing invoices, quotes, stock or payments;
- deciding permissions;
- performing destructive or consequential business actions;
- owning page layout;
- storing pixel coordinates as product truth;
- generating final mascot artwork;
- tracking business rules that belong elsewhere.

## 4. Semantic event contract

Application code should emit meaning, not animation commands.

Initial event vocabulary:

| Event | Meaning |
|---|---|
| helper.notice | Something relevant should be brought to the user's attention |
| helper.guide | User asked to be taken to a meaningful workspace location |
| helper.explain | User asked for an explanation |
| helper.listen | Helper is ready for user input |
| helper.work | An approved Helper task is actively progressing |
| helper.wait | Work is waiting on something |
| helper.success | A requested operation completed successfully |
| helper.warning | Something needs attention without being a hard failure |
| helper.error | An operation failed or cannot continue |
| helper.offline | Current operating state is offline |
| helper.sync | Synchronisation activity is taking place |
| helper.return | Current interaction is finished and Helper should return to rest |
| helper.sleep | Helper presence should become minimal or dormant |

Events may carry a semantic target, safe human-readable message, contextual presentation data, action identifier, approval status and correlation identifier for testing.

They must not carry skin-specific instructions such as gecko_climb_left.

## 5. State machine

The initial shared state vocabulary is:

IDLE → CURIOUS → NOTICE → APPROACH → POINT → EXPLAIN → LISTEN → THINK → WORKING → WAITING → SUCCESS → WARNING → ERROR → OFFLINE → SYNCING → RETURN → SLEEP

Not every event must pass through every state.

### Typical paths

**Attention:** IDLE → NOTICE → APPROACH → POINT → EXPLAIN → RETURN → IDLE

**Guide request:** IDLE → NOTICE → APPROACH → POINT → EXPLAIN → RETURN → IDLE

**Explanation:** IDLE → EXPLAIN → LISTEN → RETURN → IDLE

**Approved task:** IDLE → THINK → WORKING → SUCCESS → RETURN → IDLE

**Failure:** WORKING → ERROR → EXPLAIN → RETURN → IDLE

**Offline:** IDLE → OFFLINE

**Sync:** OFFLINE → SYNCING → SUCCESS/RETURN → IDLE

State transitions must be deterministic from the same input.

## 6. Interruptions and priorities

Helper behaviour needs a small priority model.

**Critical:** error, offline, explicit user request, approval result.

**High:** important contextual warning, blocked operational condition.

**Normal:** contextual notice, explanation, progress acknowledgement.

**Low:** idle expression and decorative micro-motion.

Higher-priority events may interrupt lower-priority behaviour.

Idle animation must never interrupt an active task, user interaction or important message.

Repeated identical notices should be coalesced so the Helper does not repeatedly demand attention for the same unresolved condition.

## 7. User control and approval

The Helper may explain, highlight, guide, inspect, show progress and ask for confirmation.

The Helper must not silently execute a consequential business action merely because an animation or agent event exists.

The product/domain layer remains the authority for permissions, validation, confirmation, mutation and audit logging.

The Helper represents those decisions; it does not replace them.

## 8. Semantic target resolution

The engine requests semantic targets such as dashboard.priority, dashboard.upcoming, work.customer, work.requirements, work.preparation, work.documents, quotes.summary, purchasing.outstanding, inventory.stock_issue, finance.payment and settings.relevant_preference.

The UI resolves a semantic target to a currently available anchor.

The engine must gracefully handle a target that is present, off-screen, hidden in a disclosure, unavailable or duplicated, including desktop, tablet and mobile layouts.

The Helper should never require a hard-coded page coordinate as its only route to a target.

## 9. Skin adapter contract

The engine sends semantic commands to the selected skin adapter.

Examples: APPROACH maps to the skin's approach(targetPosition) implementation; POINT maps to point(targetPosition); THINK maps to think(); WORKING maps to work(); SUCCESS maps to celebrate().

The adapter decides how that meaning maps to the selected body's anatomy.

A Gecko may climb or crawl. An Ant may walk and gesture with antennae. A Chameleon may approach, orient and express attention through eyes and body.

The engine does not know or care which implementation is used.

## 10. Renderer contract

The production V1 renderer baseline is **SVG artwork + CSS + Web Animations API (WAAPI)**.

No paid animation service is required.

The renderer should support transform-based movement, short state animations, position interpolation, looping idle animation, interruptible motion, animation cancellation, reduced-motion variants and static fallback.

Complex character artwork may be split into reusable SVG groups so parts can move independently.

The renderer must remain behind an adapter boundary so a different renderer can be evaluated later without rewriting Helper behaviour.

## 11. Lightweight production rule

The Helper must not become a mini-game.

V1 should avoid continuous wandering, physics simulation, 3D models, game engines, large frame-by-frame video assets, canvas-heavy rendering when SVG can do the job, page-specific animation implementations and multiple animation libraries doing overlapping work.

The preferred unit is a small semantic animation state, not a long animation sequence.

## 12. Responsive behaviour

The same semantic destination must work on desktop, tablet and mobile.

The UI supplies the current physical anchor.

The Helper adapts to available space rather than forcing a fixed travel path.

On narrow screens it must avoid covering controls, avoid travelling behind dialogs or sticky UI, shorten travel distance where appropriate, prefer nearby contextual placement and allow text/panel communication when physical travel is not practical.

## 13. Accessibility

Three presentation levels are required:

**Full motion** — normal movement and expressions.

**Reduced motion** — short fades, small translations and minimal character movement.

**Minimal / static** — little or no animation; semantic state communicated through text, iconography and panel state.

The Helper must never communicate essential information by motion alone.

The system should respect the user's reduced-motion preference and provide an explicit way to reduce or disable Helper animation.

## 14. Offline and synchronisation

Offline is a real product state, not an error animation.

The Helper must distinguish application offline, task waiting, synchronisation in progress, synchronisation success and synchronisation failure.

The visual expression can make the state understandable, but the UI must also expose clear text/status.

The Helper must never imply that an online operation succeeded when the application has not confirmed it.

## 15. Persistence and recovery

Business persistence belongs to the application/domain layer.

Helper presentation state may be transient unless there is a deliberate UX reason to persist a user preference such as selected skin, motion level or presence level.

After navigation or reload, the Helper should recover to a valid resting state rather than attempting to resume an obsolete animation.

## 16. Testing contract

The engine must be testable without visual inspection.

At minimum, tests should verify that every supported event produces an allowed state transition, unsupported commands are rejected cleanly, higher-priority events interrupt lower-priority events correctly, semantic targets resolve without pixel coordinates, missing targets produce a safe fallback, reduced-motion changes presentation without changing meaning, offline never reports success without confirmation, changing skin does not change the event/state contract and interrupted movement always settles into a valid state.

Visual/browser tests may then verify actual placement and animation.

## 17. Relationship to current Zazu implementation

The current Zazu Helper surface already provides contextual attention through App/Support/ZazuHelperService.php and renders the Helper through resources/views/components/zazu-helper.blade.php.

The current component contains a hard-coded bird SVG and route-specific guide content.

Those are the current presentation implementation, not the long-term Helper architecture.

Migration must converge toward one Helper engine, one semantic event vocabulary, one target-resolution system, one skin contract and one renderer boundary.

Do not leave multiple competing Helper systems active.

## 18. R0 implementation boundary

V1 does not require purchasing an animation editor subscription, animation hosting service, paid runtime, commercial character library or game engine.

Original Zazu artwork supplied by the owner remains the preferred source.

External assets, when used, must have a documented license suitable for the intended use.

## 19. Definition of done for the engine foundation

1. Semantic Helper events are defined.
2. State transitions are deterministic.
3. Semantic targets are independent of coordinates.
4. Skin behaviour is renderer-independent.
5. Reduced-motion and static modes are defined.
6. Offline/sync states have explicit semantics.
7. Consequential business actions remain outside the Helper engine.
8. The first production behaviours can be implemented without adding a paid animation dependency.