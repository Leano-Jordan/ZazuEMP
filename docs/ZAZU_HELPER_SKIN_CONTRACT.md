# Zazu Helper — Skin Contract

**Status:** Foundation / design-ready
**Date:** 2026-10-03

## Purpose
A skin is a physical expression of the Zazu Helper, not a separate assistant.

Every skin must implement the same semantic behaviour, interaction and accessibility contract so Zazu can change appearance without changing product logic.

Initial candidates: Gecko, Ant, Chameleon. They remain design candidates until the owner selects a final visual direction.

## Shared identity
Every skin must communicate the same Zazu Helper identity, calm/helpful/observant personality, attention philosophy, approval boundaries, uncertainty behaviour, offline/sync meaning and accessibility expectations.

A skin must never introduce a different authority model or intelligence level.

## Required semantic states
IDLE, CURIOUS, NOTICE, APPROACH, POINT, EXPLAIN, LISTEN, THINK, WORKING, WAITING, SUCCESS, WARNING, ERROR, OFFLINE, SYNCING, RETURN and SLEEP.

A skin may combine states visually when appropriate, but the semantic state must remain available to the Helper engine.

## Required movement capabilities
Every skin must support practical equivalents for stay/rest, notice, approach, orient, point or otherwise direct attention, inspect, communicate, work, wait, return, enter/leave and reduced-motion presentation.

The physical action does not need to be identical between skins.

## Anatomy adapter
The skin owns physical expression.

Gecko may use climbing, body orientation, eyes and tail. Ant may use walking, carrying, antennae and coordinated movement. Chameleon may use approach, eye direction, posture and controlled colour/expression changes.

The application must never depend on those anatomical features existing.

Application logic should request semantic intent such as approach(purchasing.outstanding), not skin-specific actions.

## Expression kit
Every skin must support neutral, curious, happy, focused, thinking, concerned, confused, waiting, successful and resting expressions.

Expressions must remain readable at the smallest supported Helper size.

## Interaction kit
Every skin must provide equivalents for attention, selection, active, disabled/unavailable, working, completion, warning and error.

## Traversal kit
Each skin must define short movement, medium movement, longer workspace travel, target arrival, blocked destination, mobile layout and return-home behaviour.

A skin does not receive permission to ignore workspace safety because its anatomy makes movement interesting.

## Workspace safety
Every skin must avoid obscuring important controls, remain within the intended visual layer, respect semantic anchor boundaries, provide alternate placement when a target is occupied, and avoid trapping interaction behind the character.

## Responsive requirements
The same semantic action must remain understandable on desktop, tablet and mobile. Mobile may use abbreviated movement or target-adjacent presentation instead of full traversal.

## Reduced-motion contract
Every meaningful motion state must have a reduced-motion equivalent: static posture, small opacity transition, instant target placement, non-animated status or equivalent.

No essential information may depend on motion.

## Asset and performance contract
Prefer compact 2D/vector-oriented artwork, reusable states, shared structural assets where practical, small payloads and no unnecessary video or 3D model requirement.

Each additional skin should add a controlled asset cost rather than a separate animation system.

## Technology independence
The skin contract must not assume Rive, Lottie, SVG, CSS, Canvas or another animation technology. The semantic contract sits above the rendering technology.

## Skin acceptance test
A new skin is acceptable only if it implements every required semantic state and movement capability, works with the same Helper engine and semantic anchors, supports reduced motion, remains accessible without animation, does not obscure important UI, stays lightweight enough for target devices, and feels like Zazu Helper rather than a different assistant.

## Core rule
**One Helper. One behaviour system. Many possible bodies.**

The skin is replaceable. The Helper identity is not.