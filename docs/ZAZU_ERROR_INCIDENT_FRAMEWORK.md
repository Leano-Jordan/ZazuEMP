# Zazu Error & Incident Framework

## Purpose

Zazu distinguishes expected user-facing failures from unexpected application incidents while keeping sensitive technical diagnostics out of ordinary operator messages.

The request UUID is a correlation/reference identifier. It is not the diagnostic explanation.

Sentinel is the local operations and diagnostic control centre built on this incident framework. Its product contract and delivery gates are defined in [Sentinel — Local Operations & Diagnostic Control Centre](SENTINEL_CONTROL_CENTRE.md).

## Error contract

Every classified failure should provide:

- Error code, category, severity and HTTP status;
- safe user-facing headline and message;
- exception class and source file/line when a throwable is available;
- originating route action, route name and HTTP method when available;
- request ID and timestamp;
- bounded diagnostics relevant to the failure domain, where available.

Core code families:

| Code | Category |
| --- | --- |
| AUTH-001 | Authentication |
| AUTHZ-001 | Authorization / permissions |
| VAL-001 | Validation |
| ROUTE-001 | Routing / page not found |
| DB-001 | Database |
| BUS-001 | Business-rule conflict |
| FILE-001 | File / storage |
| API-001 | Integration / API |
| CFG-001 | Configuration |
| APP-001 | Unexpected application error |
| SYS-001 | Server / infrastructure |

Additional transport/status mappings are defined for session expiry, method-not-allowed and rate limiting.

## Privacy boundary

The production incident ledger deliberately excludes raw exception messages, SQL statements, bindings, stack traces, customer/business records, connection hosts, database names and credentials. These may contain personal information, customer data or secrets.

For database exceptions, current capture includes bounded fields where available: configured connection name, database driver, SQLSTATE, numeric driver code, operation type and classified failure family.

The reference can correlate an incident with this metadata, but it does not necessarily identify the failed query or schema object. Sentinel must report that limitation plainly and collect additional safe evidence through controlled read-only checks. Never weaken redaction merely to make an individual incident easier to debug.

## User versus diagnostic surface

Ordinary error pages should explain the impact in plain language, show the error code and preserve the request reference. They must not expose internal infrastructure details or secrets.

Sentinel translates the safe technical record into:
- the affected subsystem and current health;
- recurring fingerprints and an occurrence timeline;
- evidence-backed likely causes with an explicit confidence level;
- affected capabilities and safe next actions;
- a locally generated diagnostic package the owner can inspect and deliberately share.

A fingerprint is a recurrence/correlation aid, not proof of root cause. Missing evidence must be labelled unknown or not checked, never reported as healthy.

## Handling rules

- Validation remains a normal correction workflow rather than a generic 500.
- Authentication and authorization failures retain their meaningful HTTP behaviour.
- Not-found, conflict, API, storage, database and server failures receive distinct classifications.
- Unexpected exceptions fall back to APP-001.
- Response-only 5xx failures are classified even when no throwable is available.
- Error rendering should remain asset-independent so a broken frontend build does not remove the basic diagnostic surface.
- Incident logging must never become the reason the original request fails.
- Incident capture must remain independent of the business database and primary log channel.
- Diagnostic health checks are read-only by default. Any repair that changes data or installation state requires explicit owner confirmation and verified recovery safeguards.
- The diagnostic entry point must not depend invisibly on the same database/session service it is diagnosing.
- All owner-facing diagnostics must respect business/installation isolation. Platform-wide incident access remains platform-admin-only.

## Existing implementation

The repository currently contains an independent private incident ledger, versioned failure fingerprints, recurrence summaries, a platform-admin console at `/admin/sentinel`, a reference-scoped redacted DB-001 report endpoint, and Windows read-only inspection tooling.

The platform-admin console is not yet the owner-facing control centre defined by Sentinel's product contract. The existing global ledger must not be opened to business owners until ownership/installation scoping is implemented and tested. The current default database-backed session configuration must also be addressed in the resilience design before claiming that Sentinel remains accessible during database failure.

See [Sentinel — Local Operations & Diagnostic Control Centre](SENTINEL_CONTROL_CENTRE.md) for the staged build plan and definition of done.
