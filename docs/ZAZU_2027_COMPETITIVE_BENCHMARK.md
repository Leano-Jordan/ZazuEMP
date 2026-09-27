# Zazu EMP 2027 Competitive Benchmark

## Purpose

This is a product-design benchmark, not a feature-copy exercise. Zazu should borrow proven workflow patterns while keeping its own event-operations model.

The comparison set below was selected from highly rated, directly relevant event/catering platforms with substantial public review evidence. Ratings and review counts are current public Capterra evidence and can change over time.

## Reference set

| Product | Public rating signal | Strong pattern to study |
|---|---:|---|
| Event Temple | 4.9/5, 76 reviews | Event operations, proposals, contracts, BEOs, invoicing, workflow automation |
| Perfect Venue | 4.8/5, 82 reviews | Simple event CRM, automated BEOs/docs/emails, online deposits, analytics |
| CaterZen | 4.8/5, 55 reviews | Catering operations, production reports, delivery, CRM, rebooking |
| Tripleseat | 4.7/5, 576 reviews | Event source-of-truth, proposals/contracts/payments, portals, inquiries |
| Caterease | 4.4/5, 110 reviews | Booking wizards, menus/recipes/packing, staffing conflicts, reminders, AI assistance |

## What Zazu should adapt

### 1. Make the Event/Job the operational source of truth

Everything important should converge on one event workspace:

Customer -> Event/Job -> Quote -> Confirmation/Deposit -> Requirements -> Purchasing -> Inventory/Assets -> Preparation -> Finance -> Completion/Rebooking

Do not make users jump between disconnected CRUD screens to understand one job.

### 2. Turn event state into next actions

Zazu should continuously answer:

"What needs attention now?"

Examples:
- Quote accepted -> create the next operational requirements.
- Requirements changed -> flag affected quote lines.
- Purchase order sent but not received -> show a supply risk.
- Stock below required quantity -> show a procurement gap.
- Event approaching -> surface unresolved preparation items.
- Invoice partially paid -> show the balance and follow-up action.
- Completed event -> prompt review, close-out and rebooking opportunity.

This becomes the core of a future Zazu Operational Action Graph.

### 3. Generate documents from operational truth

Proposals, quote revisions, invoices, BEO-style operational sheets, preparation lists, packing lists and purchasing documents should be generated from the same underlying event state.

The goal is fewer duplicate data-entry points.

### 4. Use progressive disclosure for event setup

Borrow the idea behind configurable booking wizards and conditional fields:
- Ask only what matters for the selected event/service.
- Reveal advanced fields when a user needs them.
- Keep a complete record available for review.

### 5. Bring production into the event workspace

Catering-oriented platforms expose production information such as kitchen reports, packing lists and operational prints.

Zazu's equivalent should grow from its existing requirements and inventory model:
Requirements -> preparation tasks -> purchase gaps -> stock checks -> packing/dispatch -> event-day readiness.

### 6. Build a lightweight client collaboration layer

A future Zazu client portal should let a customer:
- review a proposal
- approve/sign
- see agreed event information
- pay a deposit or invoice
- review agreed changes

The authoritative record remains Zazu. Email should not become the source of truth.

### 7. Treat communication as workflow data

Add templated, event-aware communication later:
- enquiry acknowledgement
- quote sent
- reminder
- payment follow-up
- event confirmation
- post-event/rebooking follow-up

Messages should inherit event/customer values rather than forcing repeated copy-and-paste.

### 8. Add operational conflict detection

Future checks should detect:
- asset double-booking
- staff/shift conflicts
- overlapping event commitments
- impossible preparation windows
- insufficient inventory
- supplier lead-time risk

### 9. Add domain-grounded AI, not a generic chatbot

A future Zazu assistant should understand only what the current user is allowed to see and should answer with record-backed facts.

Useful examples:
- "What is blocking this Friday's wedding?"
- "Which items still need to be purchased?"
- "Which invoices for completed jobs remain unpaid?"
- "What changed on this job since the last quote revision?"

Every suggested action should be explainable and permission-aware.

### 10. Make reminders and guidance contextual

The Zazu Helper introduced in the first hardening sweep is intentionally lightweight:
- on by default
- can be disabled
- remembers the preference locally
- explains the current workflow
- does not block the user
- does not replace normal navigation

This is the beginning of contextual guidance, not a tutorial maze.

### 11. Keep the dashboard operational

Do not clone generic SaaS KPI dashboards. The dashboard should emphasize:
- work needing attention
- approaching events
- blocked preparation
- quote/payment risk
- procurement gaps
- inventory risk
- exceptions

### 12. Design for 2027 expansion without premature complexity

Keep the architecture ready for:
- subscriptions and entitlements
- client portals
- online payments
- messaging integrations
- dispatch/delivery
- staff scheduling
- multi-business or multi-location expansion
- import/migration tooling
- privacy-safe benchmark analytics

Do not implement every future module before its workflow is validated.

## Zazu's 2027 product thesis

Zazu should become the system that turns an event from:

"something somebody booked"

into:

"a continuously managed operational job with one trusted state, visible next actions, controlled changes and traceable financial consequences."

That is the useful competitive pattern across the reference products.
