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
