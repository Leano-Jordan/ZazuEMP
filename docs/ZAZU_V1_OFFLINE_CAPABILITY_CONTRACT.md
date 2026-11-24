# Zazu V1 Offline Capability Contract

**Status:** Engineering contract — automated behaviour mapped; physical Android and owner-PC acceptance remain OPEN.  
**Original contract baseline:** `main` at `803c02c23a14347810ae51c3afbfa0e88c6f627e` (2026-11-24). Current implementation and acceptance status are tracked in `.ai/engineering/STATE.md`.  
**Purpose:** state exactly what an operator may expect when the Zazu host is unreachable. This contract is intentionally narrower than a promise that every screen or workflow is a standalone offline application.

## 1. Operating model

Zazu's current offline client is an installed-capable web application using a service worker, cached route/assets, IndexedDB business workspace, durable mutation queue and a trusted sync-device credential. It is not yet an independently hosted native desktop/mobile application.

- **Host unavailable, Wi-Fi still connected:** the client must detect that the Zazu origin is unavailable, expose the cached offline workspace and avoid leaving the operator on a stale server snapshot.
- **No network:** already-cached supported routes and device-local data can be read; supported forms can persist local mutations in IndexedDB.
- **Reconnect:** queued mutations are pushed in ordered batches (maximum 100 per request), server acknowledgements reconcile canonical IDs/revisions, and the queue drains. A lost response/retry must not duplicate a business operation.
- **Server-dependent collaboration:** other devices cannot receive live changes while the host is unavailable. They reconcile only after connectivity returns.
- **Trust boundary:** offline mutation sync requires a business-matched, owner-approved device pairing. Revoked/replaced/expired credentials stop sync; they must not erase local business data or queued changes. Re-pairing is an owner-controlled recovery action.

A cached page, service-worker registration or `navigator.onLine` value alone is not offline acceptance.

## 2. Supported device-local mutations

The following actions have explicit client form/queue handling in `resources/js/app.js` and server mutation handling. Their browser regression coverage is linked below. The operator must have the relevant business data available in the local workspace before creating dependent records.

| Business area | Supported while disconnected | Boundaries |
|---|---|---|
| Customers | Create and edit saved customer details | Profile photo upload/removal is deferred until online; metadata still saves locally. |
| Work / jobs | Create Work and edit supported saved Work fields | Linked customer must exist in the local workspace. |
| Quotes | Create a draft quote | Requires a locally available Work record; at least one priced line. |
| Quote acceptance | Accept a locally available sent quote | Only sent quotes are eligible. |
| Invoices | Create an invoice | Requires a locally available accepted quote. |
| Payments / deposits | Record a payment against a local issued invoice | Amount, outstanding balance and deposit requirement are validated locally and again by the server. |
| Suppliers | Create and edit supplier details | Dependent purchase/expense records require the supplier locally available. |
| Services / catalogue | Create and edit service metadata | Service image upload is deferred until online. |
| Inventory items | Create and edit inventory item details | Stock movements are separate operations. |
| Inventory movements | Record supported stock movements | Inventory item must be locally available; linked Work must also be local when selected. |
| Purchase orders | Create a draft purchase order | Supplier must be local; optional Work and catalogue links must resolve locally. |
| Purchase receipts | Record partial or complete receipt quantities | Only an open `ordered` PO with its lines available locally; quantities cannot exceed remaining ordered amounts. |
| Expenses | Record an expense | Optional supplier, Work and PO references must resolve locally; PO state and relationship rules are revalidated server-side. |
| Preparations | Create a preparation item | Requires a locally available Work record. |
| Event costs | Record a planned/projected/actual event cost | Requires a locally available Work record. |
| Assets | Create and edit asset details | Linked catalogue data must be locally available when selected. |
| Asset allocations | Allocate an asset to local Work and return an existing allocation | Asset and Work must be locally available; return is an update to the existing allocation. |

**Mutation semantics:** local save is not server confirmation. A successful disconnected save must be shown as saved on this device/pending sync. Updates carry a base revision; same-record updates preserve queue order and predecessor dependency. A failed or conflicting sync remains visible for retry or deliberate resolution.

## 3. Cached/local read capability

The local workspace renders device-stored records and sync state for the following collections where data has been bootstrapped or locally created:

- Customers and Work.
- Quotes and quote versions.
- Invoices and payment history.
- Suppliers and service/catalogue entries.
- Inventory items and movement history.
- Purchase orders and line/receipt details.
- Expenses and event costs.
- Assets and allocations.
- Preparations and supported Work attachments.

This is a local snapshot, not a live view of another device or server. Fresh records, server-side changes and unprimed routes are unavailable until reconnection. The dashboard fallback links into the specifically cached Customers and Work routes; do not infer that every application route is cached.

### Offline navigation contract

The service worker may return the cached Dashboard HTML shell for a requested authenticated route when the host is unavailable; it preserves the requested URL. The client must then render one route-specific offline view inside that shell. The Dashboard offline landing view is navigation-only and must not expand every local collection into one long page. Supported routes map to their corresponding local collection view. Routes without an implemented offline view must show an explicit connection-required message and must not expose unrelated collections.

A physical run on 2026-11-24 exposed a regression against this contract: links fell back to the Dashboard shell and multiple offline sections appeared together. Root cause: the cached Dashboard slot is nested in `.zazu-content`, but the earlier route isolation attempted to hide Dashboard sections as direct children of `main#main-content`; that selector did not match. Local workspace update events also did not reapply route selection.

The correction isolates content at the actual `.zazu-content` boundary, preserves only the offline workspace shell, maps supported URLs to one local collection, presents a connection-required state for unsupported URLs, and restores Dashboard content when the origin is reachable. Browser regression coverage must include actual service-worker fallback navigation to an unprimed supported route and an unsupported route, with explicit assertions that the online Dashboard is hidden. History-only route manipulation is supplementary, not sufficient.

Do not mark offline navigation accepted until exact-head CI and the Founder’s physical host-loss test both pass.

The current cached-Dashboard fallback maps these route families to one device-local view:

| Requested path | Offline view |
|---|---|
| `/dashboard` | Offline workspace navigation overview |
| `/customers` | Customers |
| `/work` and `/work/{id}` | Jobs |
| `/work/{id}/quotes` and `/quotes` | Quotes |
| `/work/{id}/costs` and `/work/{id}/travel` | Event costs |
| `/capabilities` | Services and catalogue |
| `/suppliers` | Suppliers |
| `/inventory` | Inventory |
| `/assets` | Assets |
| `/purchasing` | Purchase orders |
| `/finance` and `/finance/invoices` and `/finance/payments` | Invoices |
| `/finance/expenses` | Expenses |
| Any route without a mapped offline view | Connection-required message |

The service worker separately preserves exact cached HTML for the currently supported offline creation routes (`/customers/create`, `/work/create`, `/finance/invoices/create`) and cached job detail routes. Those are full cached pages, not the Dashboard-shell collection fallback.

## 4. Online-only, deferred or conditional operations

| Operation | Offline behaviour |
|---|---|
| Customer profile photo add/remove | Customer metadata is saved; photo change is explicitly deferred. |
| Service/catalogue image upload | Service metadata is saved; image change is explicitly deferred. |
| Server-side account actions, invitations, password/reset flows and owner administration | Require the server. |
| New device pairing, device approval and revocation | Require the server and owner approval. Existing trusted local pairing may continue to queue work offline. |
| Live multi-device sync, fresh bootstrap and server search | Require the server. Cached/local search and records only. |
| Any route not explicitly primed/cached or not rendered by the offline workspace | Not guaranteed; reconnect before relying on it. |
| Any mutation not listed in section 2 | Treat as online-only until separately implemented and tested. |

Work attachments have a separate phone-local file workflow. The attachment bytes are stored locally and queued for upload; metadata-only saves or deferred image changes must not be represented as uploaded attachments. Attachment acceptance is tracked separately from ordinary form mutations.

## 5. Session, business and sync safety invariants

1. Local workspace and mutation queue are business-scoped. Switching business must not expose another business's records or queue.
2. A web logout that cannot reach the host must not falsely claim the server session was ended. The user remains informed; trusted-device revocation is retried after the next owner login without persisting the sync token in the retry marker.
3. Expired/revoked/replaced sync credentials must preserve local data and queued work while blocking further sync.
4. A stale request/response from an earlier device session must not overwrite the active workspace.
5. Server push/apply, pull, acknowledgement, bootstrap and conflict recording revalidate device ownership, active status and credential under the operation transaction.
6. Mutations are idempotent and ordered; updates use revision checks; conflicts remain explicit. Never silently discard, auto-overwrite or duplicate financial operations.
7. Re-pairing must preserve the local workspace and pending queue until reconciliation is safe.

## 6. Local data-at-rest security boundary

The current browser persistence path writes the business workspace, mutation queue and attachment blobs to IndexedDB. The inspected client persistence path does not add an application-level encryption layer before those writes. Protection therefore depends on the operating-system account/device controls, browser-profile protections and same-origin security boundary; this must not be described as encrypted local storage.

This is an explicit security review item for Gate 1 / Gate 4:
- Preserve DEC-019: a trusted device is an owner-approved shared business workspace, not per-staff offline authentication; authorised staff using it inherit the approved offline capability.
- Confirm supported Windows and Android device profiles have appropriate device lock and storage-encryption controls, and restrict physical access to authorised business personnel.
- Review local sync-token exposure, XSS/content injection and shared-browser-profile risks.
- Decide whether a device-local unlock/key-management design is justified without making offline recovery or cold launch fragile.
- Never claim application-level encryption at rest until an implemented design and recovery-safe tests prove it.
- docs/ZAZU_OFFLINE_STORAGE_THREAT_MODEL.md: verified local persistence, credential lifecycle, logout/revocation boundary, residual risks and V1 controls.

## 7. Evidence map

Current regression coverage is in:

- `e2e/pwa-offline.spec.js`: host outage while browser remains online; local collection rendering; queue revision/order; related aggregate acknowledgements; retry after reconnect; unsupported photo/image deferral; purchase receipts; stock movements; asset allocation; event costs; payments; expenses; logout/session preservation; revoked/expired credentials; cross-business pairing; true browser-offline customer creation; cached navigation and local quote/invoice/payment and Work views.
- `e2e/offline-session-transition.spec.js`: failed logout revocation retry; unreachable logout preservation; business switching with separate queues; expired web-session preservation.
- `e2e/offline-work-creation.spec.js`: local Work/customer creation; supplier and inventory edits; customer edits; pending workspace visibility.
- `tests/Feature/OfflineSyncFoundationTest.php`, `OfflineLicenseContinuityTest.php`, `SyncDeviceAuthorizationRaceTest.php`, `SyncDeviceManagementTest.php` and related offline feature tests: server-side identity, revision, ownership, credential, revocation and license continuity controls.
- `docs/ZAZU_ANDROID_OFFLINE_INSTALL_ACCEPTANCE.md`: physical Android install, host-loss, fully disconnected cold launch, restart, local attachment and reconnect procedure.

The exact-head CI at the source baseline is recorded separately in `.ai/engineering/STATE.md`. Automated Chromium/mobile-viewport tests are not physical Android acceptance.

## 8. Acceptance still required

**Automated evidence:** exact-head CI for the baseline is green (Laravel, PostgreSQL migration/recovery, offline browser, Windows package, quality, PHPMD, secret/dependency scans and CodeQL; SonarCloud is skipped, not passed).

**Physical Android:** OPEN. Install from the documented trusted HTTPS origin, stop the PC host, test Wi-Fi-connected host loss, fully disable Wi-Fi/mobile data, force-close/cold-launch, create and edit supported records, attach a file, restart, reconnect and reconcile exactly once.

**Owner PC:** OPEN. Verify the installed Windows package with the host stopped/restarted and confirm the offline workspace and queued records survive application/browser restart. This is separate from the Windows CI packaging smoke.

**Product promise:** do not market all-screen or fully standalone offline operation. Current supported capability is the bounded list above. Expand only after each action has durable local persistence, restart survival, safe sync/replay and device acceptance evidence.
