# Director Offline Sync Foundation — 2026-10-03

## Result

The offline-architecture review incorporated the important lesson from the reviewed iOS offline-architecture material: offline is a data-ownership and persistence problem, not a service-worker/cache problem.

Zazu already had the correct high-level direction:

- local relational data is authoritative for the local installation;
- the service worker caches static assets only;
- disconnected business data is not falsely presented as browser-offline capable;
- synchronization is a future layer rather than a second business source of truth.

## Foundation executed

The repository now has host-side synchronization metadata foundations:

- `sync_devices` identifies an installation/device independently of network availability;
- `sync_mutations` provides a durable mutation/replay record;
- mutations carry business ownership, device origin, stable mutation identity, entity identity, operation, payload, retry count and failure state;
- device revocation and last-seen state are represented;
- indexes support business-scoped pending work and entity reconciliation.

This is deliberately a foundation, not a claim that phone-local offline synchronization is complete.

## Protocol boundary

Future flow:

**local business transaction → durable local mutation → pending state → transport → idempotent apply → acknowledgement → reconciliation**

The mutation record is transport/replay history. It does not replace the authoritative business record.

## Guardrails established

- Stable mutation IDs must be used for retry/idempotency.
- Distributed synchronization must not depend on local numeric primary keys being globally unique.
- Financial, inventory and permission conflicts require explicit domain handling.
- Attachments remain a separate synchronization concern.
- The existing static-only service worker remains unchanged until a real local business-data store exists.

## Next implementation layer

The next bounded architecture target is the **local business-data store + durable client mutation queue**, followed by a host synchronization protocol.

No cloud dependency is required for that layer.
