# Zazu Helper — Semantic Workspace Anchor Architecture

**Status:** Foundation / architecture-ready
**Date:** 2026-10-03

## Purpose
The Helper must move toward meaningful areas of Zazu without hard-coded screen coordinates.

The application therefore exposes semantic anchors. The Helper requests a semantic destination; the current interface resolves that destination to a safe physical location.

## Architecture
Helper intent → semantic target → anchor registry → current UI element → safe Helper position

The Helper does not own page layout. The page does not own Helper behaviour.

## Semantic target
A semantic target identifies meaning, not pixels.

Examples: dashboard.priority, dashboard.upcoming, work.customer, work.requirements, work.preparation, work.documents, quotes.summary, purchasing.outstanding, inventory.stock_issue, finance.payment, settings.relevant_preference.

Targets should remain stable even if markup, layout or screen size changes.

## Anchor registration
A UI element that can receive Helper attention registers itself as an anchor.

An anchor conceptually provides a semantic identifier, current UI reference, preferred Helper side, fallback position, visibility state and interaction-safety information.

## Resolution
When the Helper receives an approach request, the resolver locates the current semantic anchor, confirms it is visible or can be revealed safely, determines a safe nearby position, accounts for viewport boundaries and overlays, and returns the physical destination.

If no valid anchor exists, the Helper must not invent a coordinate.

## Anchor priority
1. Exact relevant element.
2. Containing workspace section.
3. Page-level semantic region.
4. Safe fallback Helper position.

## Safe positioning
The resolver must account for viewport edges, sticky headers, bottom navigation, open panels, modals, dropdowns, toast messages, form controls and other important UI.

The Helper should normally appear beside a target rather than over it.

## Desktop
Desktop can support larger traversal distances when movement improves orientation. The resolver should still prefer the shortest useful route.

## Mobile
Mobile should not reproduce desktop traversal literally. Prefer nearby target positions, compact movement, edge-safe placement, brief transitions and direct target association.

## Scroll behaviour
A Helper target should not automatically cause disruptive scrolling. The system should distinguish visible targets, safely revealable targets, targets requiring navigation and targets that cannot currently be shown.

The normal interface owns scrolling and navigation decisions.

## Hidden targets
If an anchor is inside a collapsed or hidden region, the Helper must not pretend it is visible. Future guided workflows may explicitly reveal the region before moving.

## Multiple anchors
A semantic target may have multiple physical anchors. The resolver chooses the currently visible, contextually appropriate anchor.

## Context
Resolution may consider current route, workspace, job/customer, viewport, device type, scroll position, visible panels and user interaction state.

It must not require the Helper to understand raw DOM structure.

## Mobile-first fallback
If animation is unavailable, the semantic target should still produce a normal UI response such as highlight, focus, text instruction, status message or navigation affordance.

## Initial anchor vocabulary
Dashboard: priority, upcoming, recent.
Work: customer, requirements, preparation, documents, schedule.
Quotes: summary, status, approval.
Purchasing: outstanding, supplier, purchase_order.
Inventory: stock_issue, item, adjustment.
Finance: payment, invoice, summary.
Settings: relevant_preference.

Expand only when an actual Helper use case requires another anchor.

## Anti-patterns
Do not use fixed x/y coordinates, page-specific mascot movement logic, one anchor ID per visual pixel position, animation logic embedded inside business modules, desktop/mobile layout assumptions, hidden targets presented as visible, or Helper movement that blocks the target itself.

## Acceptance test
The same semantic Helper command must operate across desktop, tablet, mobile and changed responsive layouts without rewriting Helper behaviour for each screen.

## Current boundary
This document defines semantic architecture, not exact DOM APIs, CSS, JavaScript classes, rendering libraries or final coordinates.