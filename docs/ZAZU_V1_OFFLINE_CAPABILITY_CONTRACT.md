# Zazu V1 Offline Capability Contract

**Status:** Engineering contract — automated behaviour mapped; physical Android and owner-PC acceptance remain OPEN.  
**Original contract baseline:** `main` at `803c02c23a14347810ae51c3afbfa0e88c6f627e` (2026-11-24). Current implementation and acceptance status are tracked in `.ai/engineering/STATE.md`.  
**Product requirement:** Zazu must retain full application parity while disconnected: the same application shell, navigation, page layouts, components, forms, workflows and visual identity. Offline is an operating condition of Zazu, not a second reduced product. The binding product and architecture standard is [Zazu Offline Product Parity Standard](ZAZU_OFFLINE_PRODUCT_PARITY_STANDARD.md).

**Implementation boundary:** this document records what the current implementation has actually demonstrated; it does not lower the product requirement. A capability not yet implemented or evidenced remains an engineering gap, not an acceptable permanent offline limitation. Do not market an unimplemented capability as available.

## 1. Operating model

Zazu's current offline client is an installed-capable web application using a service worker, cached route/assets, IndexedDB business workspace, durable mutation queue and a trusted sync-device credential. It is not yet an independently hosted native desktop/mobile application.

- **Host unavailable, Wi-Fi still connected:** the client must detect that the Zazu origin is unavailable, preserve the same cached application page and clearly indicate stale/offline state without switching to a secondary interface.
- **No network:** already-cached normal application routes and device-local data can be read; supported forms can persist local mutations in IndexedDB. Uncached routes must show a clear connection-required state in the same application shell.
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

### Offline visibility invariant

All route isolation and offline fallback visibility changes rely on the HTML `hidden` attribute. Component styles must not override that semantic state. The shared stylesheet therefore enforces `[hidden] { display: none !important; }`; future dashboard or component display rules must preserve this invariant. Browser acceptance must assert that hidden Dashboard content is actually not rendered, not merely that the attribute was set.

### Offline navigation contract — parity target supersedes the legacy fallback

**Required behaviour:** offline navigation must preserve the same route, page structure, navigation hierarchy, page-specific layout, controls and visual treatment used online. The normal application page must not be hidden and replaced by a generic Dashboard/offline collection screen merely because connectivity is lost.

The service worker may serve a cached page or an offline-capable application document, but the resulting route must render the corresponding normal Zazu page using local data and the same UI components. When a route document is not cached, the application must still resolve the requested route into its normal page composition where that page's offline capability is implemented. A connection-required restriction may disable only the server-dependent action or data operation; it must not silently replace the entire page with a lesser interface.

A previous implementation used a shared Dashboard shell and broad route-family matching to select collection views. That is a transitional implementation detail, not the product contract. In particular, broad route matching must never hide a cached server-rendered form, detail page, attachment control or settings component. A route being in the same family as a collection does not prove that the collection view is the correct page for that URL.

The browser must distinguish an exact cached page from an actual service-worker fallback response using an explicit, testable response/document marker—not referrer heuristics, window enumeration or assumptions based on an empty client ID. Exact cached pages remain intact. A true fallback must render the same page composition for the requested route, or clearly explain the specific unavailable operation without exposing unrelated data.

Do not mark navigation accepted until exact-head browser acceptance proves route and visual parity across cached and fallback navigation, and the Founder’s physical host-loss test passes. See [Zazu Offline Product Parity Standard](ZAZU_OFFLINE_PRODUCT_PARITY_STANDARD.md) for the binding architecture and acceptance requirements.

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


## Founder-observed offline route milestone — 2026-11-26

The Founder confirmed that the Android phone, with the Zazu PC host OFF, now opens offline workspace pages and renders locally stored business data. The visible offline banner correctly reports server unavailability, device-local saved records and the supported-screen limitation. This supersedes the earlier owner-reported symptom of a frozen last-online Dashboard with multiple offline sections collapsed together.

The initial observation called out missing Settings navigation and visual/layout refinement. Subsequent source inspection established that Settings is intentionally excluded from the offline route allowlist because those settings are not implemented as local views. Keep unsupported Settings destinations hidden. Continue improving the existing offline workspace in place: render the correct route-specific content and refine the layout without redesigning or replacing the UI. Preserve the banner's truthful capability wording.

This milestone is a partial physical observation, not full offline acceptance. It does not establish airplane-mode cold launch, local mutation persistence, force-close/restart survival, attachment round-trip, or reconnect reconciliation. Do not ask the Founder to repeat the PC-off test or confirm again that pages and local data load. Request only a new, specific physical acceptance check when the remaining engineering work is ready.

## Mobile launchpad composition and Settings boundary — 2026-11-26

The Founder’s later Android screenshots (09:48 and 09:49) show the offline server-unavailable state and device counters of 13 saved records, 0 waiting to sync and 0 needing attention. They also expose a mobile composition defect: the offline workspace title/description are squeezed into a narrow column beside the full-height module navigation.

Source inspection traced this to the interaction between the shared flex header and the responsive `.zazu-action-group { width: 100%; }` rule. The earlier navigation-visibility correction made the links visible but did not provide a separate mobile header layout. The offline workspace now has a scoped responsive grid in `resources/css/zazu-dashboard-authority.css`, with the title first, module actions below it and the device-local status last. On normal phone widths the actions use two columns; very narrow screens use one. The markup scoping is in `resources/views/dashboard.blade.php`.

The browser contract now measures the heading and navigation rectangles at 390×844 and asserts that the navigation sits below the heading with matching left/right edges. Changes are on `main` at CSS commit `52152f2`, Blade commit `20e313e` and test commit `66f7392`. Exact-head offline browser CI passed on `4a5bedfc`: 75 Chromium offline journeys, 11 mobile offline Work/visual checks, a mobile host-outage check and the phone-local attachment journey all passed. The 390×844 heading/navigation geometry assertion is included in the 75-test offline suite. Treat the correction as automated-browser-verified; physical Android visual review remains open.

**Settings is intentionally outside the current offline route allowlist.** `resources/js/app.js` exposes only routes with implemented offline views. Since `/settings` is not mapped, the unsupported Settings link and its empty navigation group are hidden while disconnected; pairing, owner administration and server-backed business settings remain online-only. Do not expose a generic Settings route or imply it works offline. Add a destination only when a particular settings function has a real local implementation and explicit regression coverage. The shell theme control remains separate from the Settings route.

This screenshot review does not expand offline scope or close physical acceptance. It does not prove local-write persistence, cold launch with all radios disabled, force-close/restart, attachment round-trip or reconnect reconciliation.

## Atomic sync acknowledgement and browser storage retention — 2026-11-26

### Queue acknowledgement transaction

The client must not acknowledge a server mutation by deleting its local queue entry separately from persisting the resulting workspace state. `public/zazu-offline.js` now performs the following in one IndexedDB read/write transaction over the mutation queue and workspace state:

1. Read the persisted workspace and durable queue.
2. Remove only server-confirmed applied/discarded queue entries; preserve retryable failures and explicit failed/conflict records.
3. Reconcile canonical server IDs/revisions and related parent/child revisions.
4. Resolve `pending_sync` flags against the remaining queue and open conflicts.
5. Recalculate `local_dirty` and write the reconciled workspace before the same transaction commits.

A transaction abort must leave both stores unacknowledged; a commit must leave both stores reconciled. Retries remain protected by the server mutation idempotency key. Do not restore a separate post-acknowledgement state-only write.

**Automated evidence:** source commit `4cd9b5f`; browser regression test `2e3bca4`; feature-contract test/helper commits `f0de754` and `76868dc`. Exact checkpoint `4a5bedfc` passed 75 desktop browser journeys, 765 Laravel tests (1 skipped, 5,471 assertions), separate mobile checks, and PostgreSQL migration/recovery smoke. PHPMD and the Windows install/archive checks passed on the same application source at `76868dc`. The browser test injects a storage failure boundary to ensure the acknowledgement path does not rely on a second state-only transaction; the `4a5bedf` test-only timing refinement passed on exact-head rerun. These results do not replace a physical abrupt-termination/restart/reconnect test.

### Browser-managed storage retention

Zazu stores local workspace and attachment data in IndexedDB and service-worker assets in Cache Storage. The current implementation adds `getStorageProtectionStatus()` and `requestStorageProtection()` to the offline client. Settings → Data protection exposes the request while connected; the offline workspace mirrors current status and remains usable when protection is declined or unsupported. Both panels report the browser's persistence grant and estimated usage/quota. A warning is shown at 90% of estimated quota. IndexedDB quota write failures are normalized into an explicit “not saved” error and displayed without deleting existing records or queued changes.

Browser-managed origin storage is best-effort by default and may be evicted under storage pressure; a persistent-storage grant reduces automatic eviction but does not prevent the user from explicitly clearing site data. See [MDN: storage quotas and eviction criteria](https://developer.mozilla.org/en-US/docs/Web/API/Storage_API/Storage_quotas_and_eviction_criteria) and [MDN: StorageManager](https://developer.mozilla.org/en-US/docs/Web/API/StorageManager).

**Tracked as:** CASE-ZAZU-0052 / ENG-DATA-007. No eviction incident is reported; this remains a platform durability boundary even after the source mitigation.

**Implemented in source, pending acceptance:** the persistent-storage request is available from a deliberate user action in both online Settings and the offline workspace. The call to `persist()` occurs before the first await so the browser's user-activation requirement is respected. The storage estimate and near-quota warning are visible; quota write failures explicitly state that the attempted change was not saved. The service-worker static cache is now `zazu-static-v63` and precaches the updated `/zazu-offline.js`, including the 12-digit phone-pairing contract. Browser regression tests cover grant, denial, unsupported API, 90% capacity warning and a forced quota failure. Exact-head CI and physical Android verification remain open. Do not market browser storage as infallible or treat persistence grants as a replacement for business export/import and full recovery.
