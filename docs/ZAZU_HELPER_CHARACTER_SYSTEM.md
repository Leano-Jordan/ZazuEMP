# Zazu Helper — Character System Foundation

**Status:** Foundation / design-ready, visual design intentionally deferred  
**Date:** 2026-10-03  
**Authority:** Zazu product direction  
**Scope:** Character identity, behaviour, animation architecture, traversal and future mascot skins

## 1. Purpose

Zazu Helper is a core Zazu product character, not decorative artwork.

The Helper is intended to guide, notice, explain, assist and eventually participate in controlled agent workflows while remaining lightweight enough for Zazu's web, mobile and offline-oriented operating environment.

This document establishes the **character system foundation**. It deliberately does not define the final visual appearance. Visual design, illustration, proportions, colours and final mascot artwork remain an owner/design decision.

## 2. Core decision: one character, multiple skins

The Helper is one character framework.

The animal is the **skin**, not a different personality, intelligence level or product mode.

Initial skin candidates:
- Gecko
- Ant
- Chameleon

These are the current design candidates, not final artwork commitments.

All skins must share:
- one Zazu Helper identity;
- one behaviour model;
- one interaction vocabulary;
- one animation/state model;
- one accessibility model;
- one capability contract;
- one user-control model.

The skin changes how an action is expressed physically.

Example:
- a Gecko may climb toward a target;
- an Ant may walk toward or carry something;
- a Chameleon may approach, look and change expression.

The underlying Helper action remains the same.

## 3. Experience levels remain separate

Basic / Intermediate / Advanced must **not** select different animals.

Experience level controls presentation, guidance depth and available assistance—not the Helper's identity.

### Basic
The Helper primarily teaches and guides.

### Intermediate
The Helper notices useful conditions and provides contextual assistance.

### Advanced
The Helper can participate in richer contextual and agent-style workflows, while consequential business actions remain subject to appropriate user approval and permissions.

The same selected skin must work across all three levels.

## 4. Character identity

The Helper should feel like a consistent Zazu character.

The character system must define:
- personality;
- tone;
- attention behaviour;
- when to appear;
- when to remain quiet;
- how it asks for attention;
- how it reacts to success;
- how it communicates uncertainty;
- how it requests approval;
- how it behaves during errors;
- how it represents offline and synchronising states;
- how it respects reduced-motion and accessibility preferences.

The Helper must feel helpful rather than needy.

## 5. Behaviour state foundation

The initial semantic state vocabulary is:

`IDLE → CURIOUS → NOTICE → APPROACH → POINT → EXPLAIN → LISTEN → THINK → WORKING → WAITING → SUCCESS → WARNING → ERROR → OFFLINE → SYNCING → RETURN → SLEEP`

This is a design foundation, not a requirement that every state be implemented immediately.

States should describe **intent**, not visual animation names.

For example, `NOTICE` means the Helper has identified something relevant. Each skin decides how that notice is physically expressed.

## 6. Semantic action contract

Application code should communicate with the Helper through semantic actions rather than skin-specific animation commands.

Conceptual examples:
- `notice(target)`
- `approach(target)`
- `point(target)`
- `explain(content)`
- `listen()`
- `think()`
- `work(context)`
- `wait()`
- `celebrate()`
- `warn(context)`
- `error(context)`
- `goOffline()`
- `sync()`
- `returnHome()`
- `sleep()`

The application should not need to know whether the selected skin has feet, antennae, a tail, wings or another physical feature.

## 7. Workspace traversal

The Helper should be able to move toward meaningful **semantic workspace anchors**, rather than depending on fragile hard-coded screen coordinates.

Examples of semantic anchors:
- Dashboard → Priority / Upcoming / Recent
- Work → Customer / Requirements / Preparation / Documents
- Quotes → Commercial summary
- Purchasing → Outstanding purchases
- Inventory → Stock issue
- Finance → Payment or invoice issue
- Settings → Relevant preference

The UI resolves a semantic anchor to a current responsive location. The Helper then moves to that location.

This allows the same behaviour to work across desktop, tablet and mobile layouts.

## 8. Movement philosophy

Movement is event-driven, not continuous decoration.

Preferred behaviour:
- remain mostly still during normal use;
- move when something relevant requires attention;
- travel to a requested destination when the user asks;
- visibly perform a task when a future agent action is underway;
- return to a resting position afterward;
- use short success/warning/error reactions;
- avoid wandering around the interface without purpose.

The absence of movement is an intentional part of the experience.

## 9. Interaction vocabulary

The system should support a small reusable vocabulary.

### Navigation
Walk, run, fly, climb, jump, appear, disappear.

### Attention
Look, approach, point, hover, gesture, wait.

### Interaction
Touch, inspect, follow, circle, highlight, carry where anatomically appropriate.

### Communication
Idle, curious, listening, thinking, explaining, working, celebrating, concerned, resting.

Each skin maps the same semantic action to its own physical expression.

## 10. Shared animation architecture

The intended architecture is:

`ZAZU HELPER ENGINE`
→ Character Behaviour / State
→ Semantic Target Resolver
→ Skin Adapter
→ Selected Skin

The application owns the meaning.

The skin owns the physical expression.

This separation prevents the mascot implementation from becoming coupled to individual screens or business modules.

## 11. Lightweight production constraint

Zazu Helper must remain production-light.

The foundation should favour:
- 2D artwork;
- vector or similarly compact assets;
- reusable animation states;
- state-machine-driven animation;
- small asset payloads;
- responsive rendering;
- mobile-friendly performance;
- offline-friendly static assets.

The system should **not** require:
- 3D character models;
- a game engine;
- video files for ordinary character actions;
- a separate animation implementation for every page;
- large duplicated animation assets per skin.

A Rive-like interactive/state-machine approach may be evaluated later, but no animation technology is locked by this document.

## 12. Skin framework

Each skin will eventually require a common design kit.

### Shared requirements
- neutral/idle state;
- happy/success state;
- curious state;
- confused state;
- concerned/warning state;
- thinking state;
- working state;
- sleeping/resting state;
- navigation movement;
- attention/pointing behaviour;
- selected/active state;
- reduced-motion alternative.

### Skin-specific expression

The physical anatomy should determine how the shared semantic actions are expressed.

The design phase should decide the exact:
- silhouette;
- proportions;
- facial features;
- limbs/appendages;
- movement style;
- expression system;
- interaction gestures.

## 13. User customisation

Future user preference should allow selection of the preferred Helper skin.

Potential settings:
- Companion: Gecko / Ant / Chameleon
- Movement: Full / Reduced / Minimal
- Presence: Contextual / Manual
- Personality intensity: Calm / Friendly / Energetic

These settings affect presentation, not permissions or business authority.

## 14. Accessibility and performance

The Helper must never become the only way to understand a workflow.

Every meaningful Helper action must have a normal UI equivalent.

The system must support:
- reduced motion;
- keyboard and screen-reader-compatible controls;
- non-animated status communication;
- no essential information conveyed by movement or colour alone;
- graceful absence when animation is unavailable;
- low-cost rendering on modest devices.

## 15. Future agent foundation

The character system should be capable of becoming the visual body of a future Zazu agent without requiring a new mascot architecture.

A future agent can therefore:
1. identify a relevant situation;
2. select a semantic target;
3. choose an appropriate Helper state;
4. move toward the target;
5. explain or prepare an action;
6. wait for approval where required;
7. execute only within the user's permissions and approved boundaries;
8. report the result;
9. return to an idle/resting state.

The character does not make the business decision. It provides a human-readable presence for the system's assistance.

## 16. Guardrails

The Helper must not:
- constantly interrupt;
- wander without purpose;
- obscure important controls;
- replace normal navigation;
- imply that an action happened when it did not;
- imply certainty when the system is uncertain;
- silently execute consequential business changes;
- bypass permissions;
- make Basic / Intermediate / Advanced appear to be authority levels.

## 17. Design-phase deliverables

When visual design begins, produce these as one coherent system:

1. Character Bible
2. Shared visual language
3. Master Helper rig/framework
4. Gecko skin kit
5. Ant skin kit
6. Chameleon skin kit
7. Expression set
8. Movement/interaction set
9. Workspace traversal rules
10. Responsive anchor behaviour
11. Accessibility/reduced-motion variants
12. Asset/performance budget

The visual design phase must build on this foundation rather than creating three unrelated mascots.

## 18. Current boundary

This foundation does **not** choose the final mascot appearance.

The owner will define the visual direction later.

Until that happens, engineering and product work should treat the Helper as a **skin-independent semantic character system** and avoid hard-coding assumptions around the current bird artwork.
