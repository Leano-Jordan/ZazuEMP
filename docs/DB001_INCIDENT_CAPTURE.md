# Zazu incident capture (Windows)

Zazu records each new application exception in two places:

1. The normal Laravel application log, for operational troubleshooting.
2. The independent private incident ledger at `storage/app/private/zazu-diagnostics/incidents.jsonl`, which does not use the business database or depend on the configured Laravel log channel.

The ledger stores only an explicit allowlist of diagnostic fields. It excludes exception messages, SQL, bindings, stack traces, customer data and credentials. Writes are serialized with a lock, and the active ledger rotates at 5 MiB while retaining one previous segment.

Each record receives:
- The unique request/incident reference.
- A stable `failure_fingerprint` and fingerprint schema version.
- Error code, exception class, route/action and source filename/line.
- Bounded database driver, SQLSTATE, driver code, operation and failure classification where available.

The fingerprint groups failures with matching technical characteristics. It is a correlation aid, not proof of root cause. A future fingerprint version may change the identity rules; compare versions explicitly.

## Retrieve a diagnostic report

Use the reference-scoped endpoint:

`/api/support/incidents/{reference-id}`

It prefers the independent ledger and falls back to existing Laravel logs for older incidents. The response is an allowlisted JSON attachment with private/no-store caching headers.

The endpoint is rate-limited. Treat the reference ID as sensitive operational information and share the report only with the maintainer.

## Existing and historical incidents

A reference ID is a correlation identifier, not a complete diagnostic record. Incidents captured before the independent ledger existed can only be retrieved if their matching structured Laravel log entry still exists. The new ledger cannot reconstruct missing historical exception details from a UUID alone.

For Windows installations where the endpoint is unavailable, the existing `ops/windows/Get-ZazuIncident.ps1` utility remains a fallback when the project folder and logs are accessible. Do not copy full Laravel logs or share the environment file.

## Operational limits

The ledger is local to an installation and is not a remote monitoring service. It must be included in the installation's protected operational data and considered during backup/recovery design. If the disk is unavailable or full, the application must continue to fail safely; the PHP error channel receives only the ledger writer's exception class, not the incident payload.

Do not reset, reinstall, or alter a business database merely to investigate an incident. Use captured evidence to select a targeted correction.


## Sentinel recurrence report

The private incident ledger also powers the local Sentinel recurrence report. It groups incidents by the stable `failure_fingerprint` and reports occurrence count, first seen, last seen, error code, bounded failure class and latest reference.

Run from the application directory:

```powershell
php artisan zazu:sentinel
php artisan zazu:sentinel --limit=50
php artisan zazu:sentinel --json
```

The report reads only the active and immediately previous private ledger segments. It does not query the business database, expose exception messages, SQL, bindings or customer records, or transmit diagnostics externally. The JSON option is intended for local tooling and automation.

This is a recurrence index, not proof of root cause. Fingerprint groups are only comparable within the same fingerprint version; future fingerprint algorithm changes must increment `failure_fingerprint_version`.


## Director Sentinel console

Platform administrators can open the searchable diagnostics console at `/admin/sentinel`.
The route is protected by the existing `auth`, `auth.session`, and `platform.admin`
middleware chain. It reads the private incident ledger only; it does not query business
records or expose raw exception messages, SQL, bindings, customer data, or stack traces.

The console provides:
- retained fingerprint and occurrence totals;
- search across fingerprint, error code, failure class, exception class, and source file;
- a fingerprint drill-down with a timestamped occurrence timeline and safe technical fields.

The timeline is limited to 500 matching records and the overview to the 100 most frequent
fingerprints. Both active and previous ledger segments are read. The console does not
increase the underlying two-segment retention window. DB-001 references in the timeline
include a direct link to download that incident's allowlisted JSON report; other incident
families do not display the database-only export action.

The reference-scoped download endpoint is unauthenticated but rate-limited and requires a
high-entropy UUID reference. It returns only allowlisted diagnostic fields with private,
no-store caching headers. Treat the reference as sensitive and share the report deliberately.


## Owner Sentinel Rescue access

Business owners can open **System → Sentinel Rescue** to get a private signed link that bypasses the normal Zazu login/session path. The link is valid for one year when generated and should be kept private. Anyone who receives it can view this installation-level health snapshot.

The standalone page checks database connectivity and migration history using read-only queries; private storage permissions; required installed-app support files; and Sentinel's own diagnostic directory, active/previous ledger readability, malformed-record counts and temporary write/read/delete probe. When the database probe or a migration query throws, Sentinel attempts to capture a safe incident and shows the reference-specific DB-001 report link.

The route is tested with an unavailable SQLite database path while `SESSION_DRIVER=database`. It returned the rescue page and retained independent diagnostic capture without requiring a normal database-backed session. It still depends on the local Zazu web process and application bootstrap remaining operational.

This is not full diagnosis yet. No diagnostic ZIP/manifest, attachment health, offline queue/sync verification, backup integrity/recovery check, session/cache check or local knowledge base has been completed. A present service-worker file is not proof of phone offline capability. Physical Android and owner-operated packaged-installation acceptance remain separate.

The console, timeline and export action require automated feature-test and production-build
verification before being treated as release-accepted.
