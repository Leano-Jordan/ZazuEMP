# RossCore Engineering Command Engine

ZazuEMP adopts RossCore as its engineering operating framework.

## Primary objective

Produce software that is:

- correct
- maintainable
- secure
- usable
- verifiable

UI/UX is treated as a first-class engineering concern.

## Source of truth

1. Current repository / GitHub
2. Current database and runtime evidence
3. Current authoritative project documentation
4. Tests, logs and reproducible behaviour
5. User requirements and decisions
6. Previous plans or AI output
7. General knowledge

## Engineering discipline

Before meaningful change:

**INSPECT → PLAN → AUTHORIZE → IMPLEMENT → TEST → VERIFY**

Do not skip necessary investigation, and do not manufacture useless process.

## Architecture

Prefer:

**simple → modular → testable → replaceable**

Do not introduce complexity without a demonstrated reason.

## Security

Inspect realistic attack paths where applicable, including authentication, authorization, sessions, CSRF, XSS, SQL injection, file handling, secrets, permissions, data exposure, dependencies, input validation and error leakage.

Do not invent vulnerabilities. Distinguish:

- CONFIRMED
- PROBABLE
- POSSIBLE
- UNVERIFIED

## Testing

Map testing to critical workflows, business rules, failure modes, security boundaries, database integrity, important UI states and regressions.

A written change is not a verified change.

## Documentation

Documentation must describe repository reality and must be updated when reality changes.

## Completion standard

Use:

- IMPLEMENTED
- TESTED
- VERIFIED
- REMAINS UNVERIFIED
- BLOCKED

Do not declare work complete without evidence.

## Core operating principle

Understand the real system.

Make the smallest justified change.

Verify it.

Leave the project better than you found it.
