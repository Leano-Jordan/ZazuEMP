# ZAZU EMP — SaaS-READINESS ARCHITECTURE CONTRACT

**Date:** 2026-10-02  
**Authority:** Director / Morpheus  
**Status:** ACTIVE  
**Scope:** Commercial SaaS readiness of the existing Zazu core without prematurely converting V1 into a distributed system.

## 1. Purpose

Zazu must be able to evolve from the current Laravel application into a commercially hosted multi-business SaaS without rewriting its business rules or corrupting historical data.

This document defines the architectural seam required for that evolution.

It does **not** authorize:
- SaaS billing;
- public multi-tenant hosting;
- microservices;
- Kubernetes;
- database sharding;
- distributed caching;
- cloud-only dependencies;
- online-only core workflows.

Those are separate product/infrastructure decisions.

## 2. Director remains the entry point

All SaaS-readiness work begins at Director/Morpheus.

The control path remains:

DIRECTOR
→ DISCOVERY / ARCHITECTURE
→ BUILDER
→ VERIFICATION
→ GUARDIAN
→ DIRECTOR
→ ACCEPT / REPAIR
→ RECORD

No specialist may create a competing architecture or SaaS roadmap.

## 3. Architectural target

The preferred evolution path is:

**Modular monolith → measured scaling → selective extraction only when evidence requires it.**

The current application should remain a coherent deployable unit while business boundaries become explicit enough that individual infrastructure layers can later be replaced.

The target boundary is:

Presentation
→ Application/workflow
→ Domain/business rules
→ Persistence
→ Infrastructure/external services

Critical business rules must not exist only inside controllers, Blade templates or frontend JavaScript.

## 4. Business ownership boundary

Every business-owned record must have an unambiguous ownership path.

Conceptually:

Business
├── Memberships / Users
├── Customers
├── Events / Work
├── Services / Catalogue
├── Suppliers
├── Purchasing
├── Costs
├── Finance
├── Documents
├── Assets / Inventory where applicable
└── Audit / operational evidence

The authenticated business context is authoritative.

A request must never be allowed to select an arbitrary business by client-supplied ID.

Every sensitive read and mutation must enforce business scope server-side.

## 5. Parent/child invariant

Business ownership is not sufficient by itself.

Related records must also preserve parent/child consistency.

Examples:

- Quote belongs to a Work/Event belonging to the same Business.
- Invoice belongs to the same Business as its source quote/work.
- Payment belongs to the same Business as its invoice.
- Purchase Order and receiving records remain within the same Business.
- Attachments remain within the owning Business.
- Membership/user access cannot be used to cross business boundaries.

Future SaaS isolation tests must deliberately attempt cross-business access through:
- route parameters;
- POST bodies;
- nested resources;
- search;
- exports;
- downloads;
- attachments;
- APIs;
- background jobs.

## 6. Source-of-truth rule

A SaaS migration must not create a second source of truth merely to make hosting easier.

The event/work record remains the operational context.

Existing domain services remain responsible for their business rules.

New infrastructure must adapt around those rules rather than duplicate them.

## 7. Deployment modes

Zazu should be capable of two deployment modes without changing core business semantics:

### Local
- local PHP/database;
- core workflows continue without ordinary internet access;
- local storage and recovery remain authoritative;
- optional online services remain optional.

### Hosted SaaS
- hosted application/runtime;
- multiple businesses;
- central operational infrastructure;
- stronger observability and automated deployment;
- optional online integrations.

The difference is deployment infrastructure, not a different business model hidden inside the application.

## 8. Offline-first constraint

Offline-capable core workflows must not acquire accidental dependencies on:
- remote APIs;
- cloud authentication;
- remote licence checks on every request;
- third-party search;
- remote document processing.

Optional enrichment may fail without taking core business operations with it.

## 9. State and idempotency

Commercial SaaS introduces retries, concurrent users and repeated requests.

Consequential mutations therefore require:
- explicit state transitions;
- transaction boundaries;
- idempotency where repeated requests could duplicate effects;
- locking where concurrent mutation can corrupt business truth;
- safe failure semantics;
- audit evidence.

A successful response must never be emitted after a failed critical mutation.

## 10. Asynchronous work rule

Queues/background workers are permitted when measured workload or user experience demonstrates a need.

Likely future candidates:
- large imports;
- document/OCR processing;
- bulk exports;
- heavy report generation;
- notifications.

Do not introduce a queue merely because SaaS architecture commonly uses one.

## 11. Caching rule

Caching is evidence-driven.

Before caching business data, record:
- source of truth;
- acceptable staleness;
- invalidation trigger;
- failure behaviour;
- tenant/business scope;
- security implications.

Financial, stock and permission state must not become stale merely to improve page speed.

## 12. Scaling rule

The architecture must evolve in this order unless evidence proves otherwise:

1. correct queries and indexes;
2. remove duplicate work;
3. measure slow paths;
4. optimise application/database boundaries;
5. introduce targeted caching or asynchronous work;
6. scale the application/database;
7. only then consider partitioning/sharding/service extraction.

No infrastructure is added solely for prestige.

## 13. Security rule

SaaS readiness requires defence in depth:

- authentication;
- server-side authorization;
- business isolation;
- resource ownership checks;
- safe uploads/downloads;
- scoped search;
- scoped exports;
- auditability;
- safe secrets;
- secure sessions;
- production-safe errors.

UI hiding is never an isolation boundary.

## 14. Data migration rule

Any future conversion of existing local business data into hosted SaaS must be:
- versioned;
- reversible where practical;
- business-identity aware;
- parent/child consistent;
- tested against populated data;
- backed up before migration;
- verified after migration.

No SaaS migration may silently assign records to the wrong business.

## 15. Architecture decision record rule

Every material architectural change records:

**Problem → Evidence → Constraints → Options → Decision → Trade-offs → Consequences → Revisit trigger**

This is the mechanism that prevents AI-assisted development from accumulating contradictory infrastructure.

## 16. Director architecture gate

Before accepting a material architecture change, Director asks:

1. What problem are we solving?
2. What evidence proves it exists?
3. Where is the authoritative state?
4. Which business owns the data?
5. What are the failure modes?
6. What happens on retry/concurrency?
7. Does offline/local operation remain intact where required?
8. Does the change preserve future hosted deployment?
9. What complexity was added?
10. How can the component later be replaced?

If the problem has no evidence, prefer the simpler architecture.

## 17. Current implementation position

Repository evidence already shows substantial foundations:
- Director-controlled modular engineering system;
- business-scoped workflows;
- permission-aware search;
- transactional/idempotent commercial mutations;
- audit logging;
- private business-scoped files;
- backup/restore tooling;
- explicit role permissions;
- versioned migrations;
- responsive local-first product direction.

Known remaining evidence boundary:
- populated cross-business adversarial runtime testing;
- populated migration/upgrade proof;
- recovery drill;
- deployment verification;
- browser/device verification;
- final runtime authorization certification.

This document therefore establishes **SaaS-readiness architecture**, not a claim that Zazu is already certified for public multi-tenant SaaS operation.

## 18. Stop conditions

Do not proceed into distributed SaaS infrastructure until:
- core workflow integrity is proven;
- business isolation is runtime-proven;
- populated data recovery is proven;
- migration/upgrade safety is proven;
- authorization is adversarially tested;
- deployment is reproducible;
- operational evidence is available.

**Director principle:** make the existing product trustworthy first; make it scalable when evidence requires scale.
