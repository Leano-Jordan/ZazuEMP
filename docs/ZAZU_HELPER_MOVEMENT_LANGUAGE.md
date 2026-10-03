# Zazu Helper — Movement Language

**Status:** Foundation / design-ready
**Date:** 2026-10-03
**Authority:** Zazu Helper character system
**Scope:** Semantic movement, attention movement, workspace traversal and physical interaction language

## 1. Purpose

Movement is part of the Helper's communication system.

The Helper does not move continuously for decoration. Movement has meaning: it can direct attention, communicate state, show progress, acknowledge a result, or represent an approved task.

This document defines the shared movement language independently of the chosen skin.

## 2. Core rule

**Movement communicates intent.**

Every deliberate movement should answer one question:

> What is the Helper communicating by moving?

If the movement communicates nothing useful, it should normally not happen.

## 3. Rest is the default

The Helper spends most of its time resting.

Rest means:
- the user can work without distraction;
- the Helper remains available;
- the interface remains visually calm;
- movement retains meaning.

Resting does not mean the Helper is inactive as a system. It may still be available to respond to user interaction or relevant system events.

## 4. Semantic movement actions

Application behaviour should request semantic movement rather than animation-specific commands.

Core actions:

- **stay** — remain at the current resting position;
- **notice** — acknowledge something relevant;
- **approach(target)** — move toward a meaningful workspace target;
- **point(target)** — direct attention to a target;
- **inspect(target)** — visually examine or indicate examination;
- **follow(target)** — accompany a workflow or moving target where appropriate;
- **work(context)** — visually represent an approved or system-controlled task;
- **wait** — remain available while awaiting information, approval or completion;
- **return(home)** — return to the Helper's resting position;
- **celebrate** — brief positive completion reaction;
- **warn** — controlled attention reaction;
- **error** — controlled failure reaction;
- **sleep** — reduced-presence state when appropriate.

The skin decides how each action is physically expressed.

## 5. Movement intensity

Movement has three practical levels.

### Subtle

Used for:
- noticing;
- hover/attention;
- small reactions;
- availability;
- confirmation.

Examples:
- look toward target;
- small posture change;
- brief gesture;
- slight movement toward a nearby target.

### Directed

Used when the user needs to understand a location or workflow.

Examples:
- travel to a workspace anchor;
- approach a field;
- point toward a problem;
- move beside a relevant card or section.

### Task movement

Used when the Helper is representing work.

Examples:
- leave its resting position;
- travel to relevant areas;
- inspect multiple targets;
- return with a result.

Task movement should be purposeful and short enough that the user understands what happened without watching an animation sequence for its own sake.

## 6. Attention sequence

The preferred attention pattern is:

**Notice → Approach → Orient → Communicate → Wait**

The Helper should not immediately perform a long animation.

Example:

A purchasing issue is detected.

1. Helper notices it.
2. Helper approaches the Purchasing anchor.
3. Helper orients toward the relevant item.
4. Helper communicates the issue through normal UI.
5. Helper waits.

## 7. User-requested navigation

If the user asks:

> “Show me purchasing.”

The Helper may:

1. identify the semantic destination;
2. move toward the destination;
3. indicate it;
4. allow the normal interface navigation to complete.

The Helper must not become the only navigation mechanism.

## 8. Semantic workspace anchors

The Helper navigates to **meaningful UI targets**, not fixed pixel coordinates.

Examples:

### Dashboard
- priority;
- upcoming;
- recent activity.

### Work
- customer;
- requirements;
- preparation;
- documents;
- schedule.

### Quotes
- commercial summary;
- quote status;
- approval area.

### Purchasing
- outstanding purchases;
- supplier information;
- purchase order.

### Inventory
- stock issue;
- item;
- adjustment area.

### Finance
- payment;
- invoice;
- financial summary.

### Settings
- relevant preference or configuration.

The semantic target resolver determines the current physical location.

## 9. Responsive traversal

A semantic destination may appear in different places on:

- desktop;
- tablet;
- mobile.

The Helper must therefore ask the interface:

> “Where is the current anchor for this semantic target?”

rather than assuming:

> “Purchasing is always at x=700, y=300.”

This keeps movement independent from screen dimensions and responsive layout changes.

## 10. Travel rules

The Helper should prefer the shortest understandable path.

### Desktop

Movement can visibly cross a meaningful workspace when that improves orientation.

### Tablet

Movement should be shorter and avoid covering controls.

### Mobile

The Helper should generally use compact movement.

It may:
- move within the visible workspace;
- appear near a relevant anchor;
- use a brief directional transition.

It should not make the user watch a long character journey across a phone screen.

## 11. Occlusion rules

The Helper must never obscure:

- primary buttons;
- form fields;
- important values;
- validation messages;
- navigation;
- critical status information.

If its destination is occupied, it should select an alternate anchor or use a nearby position.

The system should prefer **beside** rather than **on top of** important content.

## 12. Following the user

The Helper must not continuously follow:

- the cursor;
- the finger;
- scrolling;
- every page transition.

Following is reserved for deliberate contextual behaviour.

Example:

The user is completing a guided workflow and the Helper is assisting through several related steps.

Even then, it should periodically rest rather than cling to the user.

## 13. Entering and leaving

The Helper may enter or leave the visible workspace when appropriate.

Entry should communicate:

> “I have something relevant.”

Exit should communicate:

> “That interaction is complete.”

It should not repeatedly disappear and reappear simply to create activity.

## 14. Working movement

Future agent-style work may be represented physically.

Example:

1. Helper notices missing purchasing information.
2. Helper approaches the relevant work.
3. Helper enters a working state.
4. Helper performs a short sequence of task representations.
5. Normal UI shows the actual operation/result.
6. Helper reports completion.
7. Helper returns to rest.

The animation represents the work; it does not replace the system's actual operation.

## 15. Waiting

Waiting should look calm.

Waiting may mean:
- waiting for user approval;
- waiting for user input;
- waiting for a system operation;
- waiting for synchronisation.

The Helper should not appear frustrated or impatient.

## 16. Success

Success is brief:

**complete → acknowledge → return**

Possible physical language:
- small celebration;
- positive posture;
- short gesture;
- return to rest.

Avoid repeated celebration loops.

## 17. Warning

Warning movement should become more deliberate, not frantic.

Pattern:

**notice → approach → orient → communicate → wait**

The normal UI remains responsible for explaining the warning.

## 18. Error

Error movement should communicate:

> “Something needs attention.”

It should not communicate panic.

Pattern:

**stop → acknowledge → indicate → explain → recover**

Once the error is understood or resolved, the Helper returns to its normal state.

## 19. Offline

Offline should not trigger a failure animation.

The Helper can transition into a calm offline state and remain available for supported local work.

The normal interface must communicate:
- current connection condition;
- what can continue locally;
- what is waiting for connectivity.

## 20. Synchronisation

Synchronisation can use movement to communicate progress:

**prepare → sync → confirm → return**

The Helper should not imply that synchronisation completed until the system confirms completion.

## 21. Skin translation

The semantic movement is shared.

Example:

**APPROACH(Purchasing)**

Gecko:
- walks/climbs toward the area.

Ant:
- walks toward the area, potentially carrying an appropriate visual object in future designs.

Chameleon:
- approaches while using posture/eye direction/body expression to communicate attention.

The application does not know which physical expression was used.

## 22. Movement personality

Movement personality can differ by skin without changing behaviour.

Possible future interpretation:

- Gecko: agile, curious, climbing-oriented.
- Ant: purposeful, persistent, task-oriented.
- Chameleon: observant, expressive, adaptive.

These are movement styles, not different intelligence or authority levels.

## 23. Reduced motion

Reduced-motion mode must replace movement with simpler transitions.

For example:

Full:
**travel → approach → point**

Reduced:
**fade/short transition → appear at target → point**

Minimal:
**static presence → target highlight**

The user must retain the same information and functionality.

## 24. Performance rule

Movement must remain lightweight.

Prefer:
- reusable animations;
- short transitions;
- shared state machines;
- compact assets;
- CSS/SVG/vector or equivalent lightweight rendering where appropriate.

Avoid:
- long video sequences;
- large animation payloads;
- page-specific animation implementations;
- unnecessary physics;
- game-like simulation.

## 25. Anti-patterns

Do not build:

- a Helper that constantly walks around;
- random idle wandering;
- cursor-following by default;
- full-screen character travel on mobile;
- animations that cover controls;
- long celebration sequences;
- movement without semantic meaning;
- skin-specific application logic;
- hard-coded screen coordinates;
- animation that claims an action succeeded before the underlying system confirms it.

## 26. Movement test

Before approving a movement behaviour, verify:

1. What does the movement communicate?
2. Is the destination semantic?
3. Does the movement work responsively?
4. Does it avoid obscuring UI?
5. Does it have a reduced-motion equivalent?
6. Does the normal UI communicate the same meaning?
7. Does the selected skin merely change expression rather than system behaviour?
8. Does the movement stop when its communication purpose is complete?

## 27. Behaviour-to-movement backbone

The current shared loop is:

**Rest → Notice → Approach → Orient → Communicate → Assist → Confirm → Return → Rest**

Task workflows may extend this to:

**Rest → Notice → Approach → Inspect → Work → Verify → Report → Return → Rest**

This is the movement foundation for all future Helper skins.

## 28. Current boundary

This document defines semantic movement language.

It does not yet define:
- final character artwork;
- exact animation timings;
- final rig structure;
- final skin anatomy;
- final animation technology;
- production assets.

Those remain later design/technology decisions.
