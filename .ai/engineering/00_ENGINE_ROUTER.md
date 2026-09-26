# ROSSCORE ENGINE ROUTER

This file defines how repository-side engineering specialists activate and hand work to each other.

## Default flow

MASTER DIRECTOR (external operator/system prompt)
        |
        v
TASK ROUTER
        |
        +--> RECON ENGINE (context required)
        +--> FRESH EYES ENGINE (product/workflow/user/system coherence?)
        |
        +--> ARCHITECTURE ENGINE (structural decision?)
        +--> DATA ENGINE (data/schema/persistence?)
        +--> SECURITY ENGINE (trust boundary?)
        +--> FAILURE DEBUG ENGINE (defect/failure?)
        |
        v
IMPLEMENTATION ENGINE
        |
        v
VERIFICATION ENGINE
        |
        v
REGRESSION ENGINE
        |
        +--> RELEASE ENGINE (release/commercial impact?)
        |
        v
RECORD / REPORT

## Routing rules

### Always

RECON when the affected area is unfamiliar, cross-cutting, ambiguous, or materially changed since the last verified context.

IMPLEMENTATION for approved repository changes.

VERIFICATION after every meaningful change.

REGRESSION after every meaningful behavioural, shared-code, database, authorization, navigation, refactor or defect change.

### Conditional specialists

FRESH EYES:
- explicit "fresh eyes" or "devil's advocate" request
- product or workflow sanity checking
- onboarding or first-use flow
- navigation and information architecture
- forms and multi-step journeys
- business-process sequencing
- cross-module user journeys
- terminology/comprehension concerns
- dashboards and operational visibility
- feature design before implementation
- challenging an existing design decision
- detecting friction, unnecessary steps, missing states or confusing handoffs
- identifying when a user-facing problem may actually be architectural or data-model related

FRESH EYES may be active during development rather than only as a final review. When active, it can raise a FRESH EYES ALERT as soon as evidence reveals a meaningful product, workflow, usability or system-flow problem.

ARCHITECTURE:
- shared abstractions
- domain boundaries
- business ownership model
- reusable services/components
- major refactors
- integration contracts
- future SaaS foundation changes

DATA:
- migration/schema
- Eloquent relationships
- queries
- transaction boundaries
- data backfills/imports
- historical records
- reporting truth
- concurrency-sensitive writes

SECURITY:
- authentication
- authorization
- business isolation
- sessions
- roles/permissions
- file handling
- secrets
- sensitive data
- external trust boundaries
- security-sensitive dependencies

FAILURE DEBUG:
- bug reports
- intermittent failures
- inconsistent state
- partial writes
- duplicate effects
- race conditions
- retries/timeouts
- production-like incidents

RELEASE:
- release candidate
- deployment
- dependency upgrade
- production configuration
- migration-heavy deployment
- commercial readiness

## Escalation

A specialist may escalate work to another specialist when evidence requires it.

Examples:
- A UI bug reveals an authorization defect -> SECURITY.
- A controller bug reveals a transaction problem -> DATA.
- A feature request reveals a shared-boundary problem -> ARCHITECTURE.
- A workflow exposes a domain ownership problem -> ARCHITECTURE.
- A workflow requires missing or contradictory data -> DATA.
- A user-comprehension problem reveals a broken system transition -> FRESH EYES + relevant engineering specialist.
- A database fix changes workflows -> REGRESSION.
- A regression reveals an incorrect original assumption -> RECON, then return to the affected specialist.

Do not activate every specialist by default. Depth must match risk and blast radius.

## Handoff contract

Every specialist handoff must state:
- task
- evidence gathered
- affected components
- invariants
- identified risks
- work performed or proposed
- required next specialist
- verification requirements

A specialist cannot declare the entire task complete unless its own output and required downstream gates are satisfied.
