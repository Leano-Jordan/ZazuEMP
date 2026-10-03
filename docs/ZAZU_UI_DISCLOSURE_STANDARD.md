# Zazu EMP — UI Disclosure Standard

**Status:** ACTIVE DESIGN STANDARD  
**Director update:** 2026-10-03

## Purpose

Keep Zazu capable without making users stare at the entire system at once.

**Core rule:** complexity should exist underneath the interface, not in front of the user.

## Information layers

### 1. Primary
The user can immediately see what they came to do.

Examples:
- customer identity and next action;
- job/event date and status;
- quote lines and current total;
- inventory quantity/status.

### 2. Expandable
Useful information that should be available without taking permanent screen space.

Patterns:
- accordion;
- expandable row;
- disclosure panel;
- expandable summary card.

### 3. Advanced
Operational, financial or configuration detail that matters to experienced users but should not dominate the default workflow.

### 4. Specialist
Rare or conditional functions such as tender/compliance configuration, deep administration and specialist reporting.

## Pattern rules

| Situation | Preferred pattern |
|---|---|
| Grouped form/settings | Accordion |
| Individual record detail | Expandable row |
| Dashboard/job/event summary | Expandable card or summary |
| Quote / consequential financial workflow | Sticky financial summary |
| Dense mobile content | Progressive sections, not tiny compressed controls |
| Rare configuration | Contextual advanced section |

## Experience levels

Basic / Intermediate / Advanced are **experience presentation levels**, not permission levels.

They may change:
- wording;
- amount of visible detail;
- guidance;
- suggested actions;
- default ordering.

They must not:
- bypass permissions;
- create different sources of truth;
- hide required information permanently;
- require a user to understand a software setting before using the product.

## Context-first design

Prefer the user's business mental model over database-shaped navigation.

A job/event may expose:
**Customer → Services → Preparation → Resources → Purchasing → Costs → Finance**

This is a visibility/orchestration layer over existing domain relationships. It must not duplicate authoritative records.

## Mobile rule

Collapse complexity vertically rather than shrinking it horizontally.

Avoid:
- compressed multi-column forms;
- microscopic tables;
- hidden critical totals;
- action groups competing on one line.

## Landing-page rule

The landing page is allowed to be expressive.

Operational dashboard:
**calm, task-first, controlled density**

Landing page:
**identity, atmosphere, artwork, photography, mascot, motion and product story**

The two surfaces must not be forced into the same visual density.

## Acceptance test

Before adding a new visible panel/card/table:

1. What user task does it serve?
2. Is the information primary, expandable, advanced or specialist?
3. Can an existing surface reveal it progressively?
4. Does it duplicate another source of truth?
5. What happens on mobile?
6. Does it remain understandable without technical knowledge?
7. Does it improve the workflow enough to justify permanent screen space?



## Current implementation foundation

Primary business focus is a presentation lens stored per business membership. It supports Chairs & tents, Catering & baking, Sound & DJ and Mixed event services without creating separate applications or permission systems.

Basic / Intermediate / Advanced remains a separate presentation control. The two settings can therefore express both the kind of work a user wants emphasised and the amount of detail they are comfortable seeing.

The first applied progressive-disclosure surface is the workspace-focus setup/preferences screen: the business-focus question stays visible while experience detail is placed in one native disclosure panel.

Shared navigation was corrected at the hierarchy level. Sidebar groups, desktop flyouts and mobile child lists now use one dark relationship, with neutral surfaces for structure and blue reserved for active/selected states.
