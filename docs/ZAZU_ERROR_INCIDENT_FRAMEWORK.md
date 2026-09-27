# Zazu Error & Incident Framework

## Purpose

Zazu distinguishes expected user-facing failures from unexpected application incidents while keeping technical diagnostics out of the operator interface.

The request UUID is a correlation/reference identifier. It is not the diagnostic explanation.

## Error contract

Every classified failure has:

- Error code
- Category
- Severity
- HTTP status
- User-facing headline
- User-facing message
- Technical exception message
- Originating component / route action
- Request ID
- Timestamp
- Exception class, source file and line
- Stack trace
- Safe query context for database exceptions

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

**Unable to load Customers**  
**DB-001**  
We could not load the requested data. Please try again.

Reference: request UUID

The diagnostic log contains the technical exception, source location, route/component, timestamp, stack trace and request correlation identifier.

Never expose SQL bindings, stack traces, environment secrets or internal infrastructure details to the user.

## Handling rules

- Validation remains a normal correction workflow rather than a generic 500.
- Authentication and authorization failures retain their meaningful HTTP behaviour.
- Not-found, conflict, API, storage and server failures receive distinct classifications.
- Unexpected exceptions fall back to APP-001.
- Response-only 5xx failures are classified even when no throwable is available.
- Error rendering is asset-independent so a broken frontend build does not remove the basic diagnostic surface.
- Incident logging must never become the reason the original request fails.

## Future extension

This foundation intentionally does not add an incident database table or admin dashboard yet.

The next useful layer, after repeated operational evidence, is a searchable owner/admin incident view filtered by error code, date, route, severity and reference ID.
