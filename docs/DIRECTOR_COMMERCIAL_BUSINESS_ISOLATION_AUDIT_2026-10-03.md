# Director Commercial Business-Isolation Audit — 2026-10-03

## Scope

Target: commercial SaaS isolation readiness of the current Zazu modular monolith.

Inspected:
- active business context;
- route-bound parent records;
- model-level business ownership;
- nested parent/child relationships;
- search;
- private attachments and profile media;
- purchase, inventory and finance relationships;
- current adversarial feature tests.

No speculative distributed infrastructure was introduced.

## Findings

| Control | Source evidence | State |
|---|---|---|
| Active business resolution | CurrentBusiness resolves only an active business belonging to the authenticated user | Implemented |
| Route-bound business records | EnsureActiveBusinessContext checks every bound model that declares business_id before controller execution | Implemented |
| Business-owned model writes | BelongsToBusiness prevents creating or moving a record into another active business context | Implemented |
| Parent/child DB integrity | Composite business+parent foreign keys exist for key purchasing, inventory, asset, cost, preparation and attachment relationships | Implemented |
| Search isolation | Search handlers explicitly scope customer/event/service/supplier/purchasing/quote/invoice/cost/asset/inventory/expense queries to the supplied business | Implemented |
| Private attachment access | Event attachment downloads check active business ownership; storage paths are private | Implemented |
| Profile media | Customer photos are queried by business; user photos require active membership in the current business | Implemented |
| Finance isolation | Invoice/payment/expense operations use business-scoped queries and financial services | Implemented |
| Nested business validation | Quote/version, invoice/event, inventory/event and purchasing relationships are covered by existing parent/child tests | Implemented |
| Adversarial test coverage | Current suite includes direct-ID foreign record checks plus new foreign search and attachment-download checks | Automated evidence added |
| Runtime proof | GitHub workflows for the latest main head are queued; local production/runtime execution is not available through this audit | Not yet verified |

## Important design conclusion

Zazu's isolation model should remain **business-first and deployment-independent**.

The same business ownership rules must hold in:
- a local installation;
- a local-network Zazu host;
- a future device-sync architecture;
- a future hosted SaaS deployment.

Offline-first does not weaken isolation. It makes ownership even more important because local mutations eventually need to synchronize without crossing business boundaries.

## No-go changes avoided

This audit deliberately did not add:
- global query scopes that could hide domain mistakes;
- speculative API gateways;
- microservices;
- remote authorization calls;
- centralized cloud identity dependencies;
- new tenancy infrastructure.

The current business context + explicit parent/child validation + database constraints provide the appropriate foundation.

## Remaining evidence gate

The remaining security target is not another source rewrite. It is execution against realistic populated multi-business data:

**Business A data + Business B data → direct route challenge → nested mutation challenge → search challenge → private media challenge → financial mutation challenge → confirm no leakage or cross-business effect.**

That runtime challenge should be completed before hosted SaaS certification.
