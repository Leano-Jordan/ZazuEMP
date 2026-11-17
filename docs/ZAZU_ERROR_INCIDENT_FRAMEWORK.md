# Zazu Error & Incident Framework

## Purpose

Zazu distinguishes expected user-facing failures from unexpected application incidents while keeping technical diagnostics out of the operator interface.

The request UUID is a correlation/reference identifier. It is not the diagnostic explanation.

## Error contract

Every classified failure has:

- Error code, category, severity and HTTP status;
- safe user-facing headline and message;
- exception class and source file/line when a throwable is available;
- originating route action, route name, HTTP method and path when available;
- request ID and timestamp.

The production incident logger deliberately does **not** persist raw exception messages, SQL statements, bindings or stack traces. Those values can contain customer data, personal information or credentials. Do not describe the redacted incident log as containing them.

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

## User versus diagnostic surface

User-facing pages expose a named code and safe explanation, for example:

**We could not open or save your information.**  
**DB-001**  
Please try again. If the problem continues, share this reference number with support.

Reference number: request ID

The authoritative application incident log records the request ID, error classification, exception class, source file/line, route/action, method/path and timestamp. Request middleware also adds the request ID to the log context and response header. For database exceptions, the logger additionally records bounded diagnostic fields when available: configured connection name, database driver, five-character SQLSTATE, numeric driver error code and read/write operation type.

The incident logger intentionally omits exception messages, SQL, bindings, stack traces, connection hosts, database names and credentials. The reference ID can now correlate a database incident with the SQLSTATE and driver code when the database supplies them, but it still does not identify the failed query or schema object. If those fields are insufficient, obtain deeper evidence through a controlled local reproduction or a separately approved, access-restricted diagnostic process. Never add raw customer data, credentials, SQL bindings or stack traces to routine production logs.

Never expose technical diagnostics, environment secrets or internal infrastructure details to the user.

## Handling rules

- Validation remains a normal correction workflow rather than a generic 500.
- Authentication and authorization failures retain their meaningful HTTP behaviour.
- Not-found, conflict, API, storage and server failures receive distinct classifications.
- Unexpected exceptions fall back to APP-001.
- Response-only 5xx failures are classified even when no throwable is available.
- Error rendering is asset-independent so a broken frontend build does not remove the basic diagnostic surface.
- Incident logging must never become the reason the original request fails.
- Keep incident records privacy-minimised; do not weaken redaction to make an individual incident easier to debug.

## Future extension

This foundation intentionally does not add an incident database table or admin dashboard yet.

The next useful layer, after repeated operational evidence, is a searchable owner/admin incident view filtered by error code, date, route, severity and reference ID, preserving the same redaction boundary.
