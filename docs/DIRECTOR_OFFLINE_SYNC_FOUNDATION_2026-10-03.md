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


## Layering now in place

The foundation is now separated into four responsibilities:

1. Business data remains the authoritative local application state.
2. Mutation recording is a transport-independent application boundary. SyncMutationRecorder records a durable pending mutation only after a device is active and business-scoped.
3. Replay tracking remains represented by stable mutation IDs and per-device cursors. Repeating the same mutation ID returns the existing mutation instead of creating a second logical change.
4. Conflict recording is isolated in SyncConflictRecorder; conflict storage does not decide how a business domain should resolve the conflict.

Query boundaries are explicit:

- SyncDevice::active() finds usable installations.
- SyncMutation::pending() provides ordered pending work.
- SyncConflict::open() provides unresolved conflicts.

This is deliberately not a transport implementation. HTTP, LAN, cloud sync and background workers can be attached later without making business models depend on a particular network mechanism.

### Required next layer

The next implementation layer is the sync application protocol:

local business transaction → mutation record → device pull cursor → idempotent apply → acknowledgement → cursor advance → conflict path

That layer must define payload/version rules and domain-specific conflict policies before any real LAN/cloud transport is introduced.


## Ordered protocol layer

The foundation now gives mutations an explicit stream and monotonic business-scoped sequence.

Protocol responsibilities:
1. A mutation is recorded locally with a stable mutation ID.
2. The mutation receives a sequence within its stream.
3. A sync device pulls pending mutations strictly after its acknowledged cursor.
4. Acknowledgement can only advance through a contiguous sequence; gaps are rejected.
5. Acknowledged mutations become applied and the device cursor advances atomically.
6. Device status and business ownership are checked before synchronization.

The protocol is deliberately transport-independent. LAN HTTP, phone-local storage, and future cloud transport can use the same pull/acknowledgement boundary.

Still intentionally outside this layer:
- applying arbitrary remote payloads directly to business models;
- conflict resolution policy;
- attachment transfer;
- LAN/cloud transport;
- cloud tenancy.

Those require domain-specific rules before they are safe to automate.


## Domain-safe application boundary

Remote mutations are not allowed to write arbitrary model fields. The protocol now requires an explicit SyncMutationHandler for each entity type.

SyncMutationApplier:
- accepts only persisted pending mutations;
- requires an explicitly registered handler;
- locks the mutation before application;
- runs the handler and status transition in one database transaction;
- records applied time and attempt count;
- safely treats an already-applied mutation as a no-op.

Unknown entity types therefore fail closed instead of becoming a generic database update mechanism.

Conflict recording also now rejects a device that belongs to another business or an inactive device.


## Per-device delivery correction

The ordered mutation stream is a business-wide source sequence, but acknowledgement is a device-specific delivery concern.

Zazu now records a sync_deliveries row for each active destination device when a mutation is created. The originating device does not receive its own mutation back as a remote change.

Each destination has its own delivery sequence and cursor. Acknowledging a phone therefore marks that phone's delivery as applied without globally marking the source mutation applied. This prevents one device from consuming a mutation and accidentally hiding it from other devices.

This distinction is required before real LAN or cloud transport is introduced:
- mutation = durable source change;
- delivery = a particular device's copy of that change;
- cursor = that device's confirmed delivery position.

New devices and initial dataset/bootstrap remain a separate provisioning concern.
