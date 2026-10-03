# Zazu Helper — Skin Design Briefs

**Status:** Functional/design brief; final visual design deferred  
**Date:** 2026-10-03  
**Authority:** Zazu Helper character system

## 1. Purpose

These briefs define what each candidate skin must be able to express physically. They do **not** define final artwork, colour palettes, proportions, facial styling, clothing, accessories or branding.

The owner will decide the final visual design.

## 2. Shared rule

**The animal is the appearance. The Helper is the character.**

Gecko, Ant and Chameleon must implement the same semantic behaviour contract, the same approval boundaries, the same accessibility model and the same movement vocabulary.

No skin may imply that it has different intelligence, permissions or product authority.

## 3. Shared functional requirements

Every skin must support the same minimum behaviour set:

| Capability | Requirement |
|---|---|
| Rest | Can remain visually alive without demanding attention |
| Notice | Can visibly acknowledge relevant information |
| Approach | Can move toward a resolved semantic target |
| Orient | Can make attention direction understandable |
| Point | Can indicate a target without covering it |
| Explain | Can support a visible explanation/panel |
| Listen | Can communicate readiness for user input |
| Think | Can show brief processing without pretending to perform work |
| Work | Can represent approved active work/progress |
| Wait | Can show that progress is temporarily waiting |
| Success | Can acknowledge confirmed completion |
| Warning | Can indicate a non-fatal attention condition |
| Error | Can indicate a failed or blocked operation |
| Offline | Can clearly represent offline status |
| Syncing | Can represent active synchronisation |
| Return | Can return to a sensible resting location |
| Sleep | Can reduce presence substantially |

## 4. Gecko brief

### Functional character direction
The Gecko should make the Helper feel spatially aware, observant and comfortable moving around the interface.

### Movement advantages to preserve
- natural climbing and edge traversal;
- ability to stop near panels, cards and controls;
- strong orientation cues through body/head direction;
- short darting or creeping approaches;
- ability to perch or cling rather than occupy large screen areas.

### Required physical expression
The final design should allow enough separable anatomy or layered artwork for head/body orientation and at least one clear directional gesture.

### Target-traversal implication
The Gecko is particularly suited to moving along safe UI edges or between nearby semantic anchors without requiring a large free-space path.

### Production constraint
Keep the final rig simple. A small number of independently transformable groups is preferable to a highly articulated creature.

## 5. Ant brief

### Functional character direction
The Ant should make the Helper feel persistent, organised and task-oriented without becoming a cartoon of labour.

### Movement advantages to preserve
- reliable ground travel;
- clear start/stop behaviour;
- following a defined route;
- small carrying or delivery gestures where useful;
- compact footprint for dense business screens.

### Required physical expression
The final design should provide a readable body/head orientation and at least one gesture or appendage that can communicate direction.

### Target-traversal implication
The Ant is well suited to deliberate movement along a route from the Helper's resting position to a semantic target.

### Production constraint
Avoid designing a character that requires many tiny articulated limbs to animate convincingly. The visual design should remain practical for a small SVG rig.

## 6. Chameleon brief

### Functional character direction
The Chameleon should make the Helper feel context-aware and observant, with attention and expression doing more work than large movement.

### Movement advantages to preserve
- subtle head/body orientation;
- independently readable eyes where practical;
- short approach movements;
- expressive pause and attention changes;
- optional restrained colour/state treatment where it remains accessible.

### Required physical expression
The design should allow clear orientation and expression without depending entirely on colour.

### Target-traversal implication
The Chameleon can use short movements plus strong orientation to communicate that it has noticed or is inspecting a specific area.

### Production constraint
Do not make dynamic colour changes the sole carrier of status. The same meaning must remain understandable through shape, motion, text or UI state.

## 7. Cross-skin design test

Before any skin is approved, the same ten semantic behaviours must be demonstrated with each candidate.

The evaluator should be able to watch the behaviour without knowing which skin was used and still identify:
- what the Helper noticed;
- where it is directing attention;
- whether it is thinking, working, waiting or finished;
- whether something is wrong;
- whether the application is offline or syncing.

## 8. Artwork construction guidance

The visual design should favour a small reusable SVG structure rather than dozens of independent parts.

Recommended conceptual separation:
- body/core;
- head or orientation unit;
- expression unit;
- directional/gesture unit;
- optional accessory unit;
- optional effect layer.

The final number of parts is a design decision. The engineering requirement is simply that the design can support the semantic states without frame-by-frame artwork for every state.

## 9. Motion and accessibility requirements

Each skin must have a reduced-motion and static presentation path.

Full motion may use travel, orientation and expressive gestures.

Reduced motion should preserve meaning with shorter movement, fades or small transforms.

Static mode should communicate state through UI/panel status even when the character barely moves.

Essential information must not depend on motion, colour or spatial position alone.

## 10. Production-light acceptance

A skin is production-light when:
1. The artwork can be delivered as compact local assets.
2. The skin can express the shared semantic states using reusable transforms/animations.
3. Ordinary interactions do not require video files.
4. The skin does not require a game engine.
5. The skin can operate on mobile without continuous high-frequency animation.
6. The same semantic behaviour can be reused across the other skins.

## 11. Owner/design boundary

The owner retains the final decisions on silhouette, proportions, facial features, colours, line treatment, branding, personality cues and visual polish.

This brief exists to protect those future visual choices from creating an animation system that is expensive or difficult to maintain.

## 12. Decision state

Current candidates remain:

**Gecko · Ant · Chameleon**

No candidate is the production selection yet. The next visual-design phase should produce static concepts first, then a minimal animation proof against the shared behaviour contract.