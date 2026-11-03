# Zazu EMP — V1 Software / Commercial Status

**Assessment date:** 2026-11-01
**Repository:** `Leano-Jordan/ZazuEMP`  
**Current main HEAD observed by Director:** `5f4d7f3767ed79a095a5644107000f0dcaa2566f` (before this status-document reconciliation)

## Current position

Zazu EMP has a substantial connected operational foundation across:
- authentication/business context;
- customers and jobs;
- requirements/capabilities;
- quotes and revisions;
- travel and operational costs;
- preparation;
- finance;
- purchasing;
- inventory;
- physical assets;
- reporting;
- responsive/shared UI.

The product is **not yet release-certified**. The current main line contains a broad offline operational foundation, while release certification remains gated by real recovery/rollback evidence, legal/privacy closure and Zazu brand clearance.

## Historical-chat reconciliation

| Historical idea | Current repository state on 2026-10-03 |
|---|---|
| Event/business operational core | IMPLEMENTED — customer, job/event, requirements, capabilities, quotes, preparation, purchasing, inventory/assets, finance and reporting are connected around the operational record. |
| Experience modes | IMPLEMENTED foundation — Basic / Intermediate / Advanced are stored per business membership and already influence dashboard detail. |
| Primary niche focus | NEW FOUNDATION — stored per business membership with four presentation choices. Sound & DJ is the third niche and the concrete first iteration reference. |
| Progressive disclosure | PARTIAL / FOUNDATION — hierarchical navigation already exists; the workspace-focus screen now uses a native disclosure for secondary experience detail. Wider page-by-page adoption remains future iteration. |
| Command palette | IMPLEMENTED — global workspace search / quick access is present. |
| Niche-specific dashboard emphasis | PARTIAL — selected niche now appears in workspace context and dashboard copy; deeper niche-specific surfacing is not yet implemented. |
| Full offline business-data operation | PARTIAL / IN PROGRESS — IndexedDB local store + durable queue + reconnect sync now cover customer/job/service/preparation/supplier/purchasing/receiving/quote/acceptance/invoice/payment/expense/inventory/cost/assets; offline attachment synchronization and full disconnected parity remain open; explicit sync-conflict review and retry/discard controls are implemented at source. |
| Online/offline parity | DIRECTION ACTIVE — the phone workspace is now a real local operational surface; it is not yet a complete disconnected replacement for every desktop workflow. |
| Local host / Wi-Fi clients | NOT IMPLEMENTED — remains an architecture target, not a current capability. |
| Historical infrastructure/capacity claims | EXCLUDED — not treated as requirements without repository or measured evidence. |

## Current-head verification boundary

The last proven application baseline was `92730f7cb660ae06d9df3c014eccf4f0d3e9188e`. The previously recorded candidate `aa1e3ca8921625abca16fe4bbc8546746b4bf135` is historical and must not be presented as the current application head. Subsequent source changes and the latest attachment retrieval correction require fresh exact-head Actions reconciliation. This status page is not a substitute for that run evidence.

## Release-critical work remaining

### Commercial completion
- Customer-facing quote delivery/presentation
- Quote acceptance/deposit lifecycle
- Complete invoice/document workflow
- Payment/reconciliation completion

### Integrity and control
- Complete authorization coverage
- Complete audit/activity continuity
- Parent/child and concurrency verification across critical mutations
- Representative existing-database migration/upgrade verification

### Recovery and operations
- Real backup/restore drill
- Restore of representative data and private media
- Rollback procedure
- Operational diagnostics/observability
- Final deployment/configuration review

### Previous verified release evidence

- Laravel: **202 passed / 1,174 assertions**
- Browser: **7 passed / 8 skipped**; the complete registration journey runs once on Chromium to avoid shared-IP registration throttling, while responsive entry coverage is separate
- Zazu Quality: **passed**
- PHPMD: **passed**
- Psalm: **passed** after removing the redundant init step and applying a narrowly scoped Laravel 13.34.0 compatibility shim for one unsupported `@phpstan-this-out` annotation
- Fresh SQLite migration, Blade compilation, asset build/manifest verification and application configuration validation: **passed**

### Runtime proof still required on the latest head
- Browser critical-path traversal
- Desktop/mobile verification
- Light/dark verification
- Accessibility verification
- Real local database/browser verification where CI cannot prove the owner's environment

## Engineering-system status

The repository now has a state-driven AI engineering control system:

- Morpheus/Jarvis control plane
- Discovery & Design
- Builder
- Guardian with Forensics / Break / Security / Data Integrity modes
- Release
- UI/UX Improvement
- persistent STATE / DECISION / REGRESSION / READINESS ledgers
- anti-loop and context-firewall controls

This documentation refactor is **engineering-control work**, not application feature completion.

## Evidence language

IMPLEMENTED, TESTED, VERIFIED and PROVEN are intentionally different states.

Historical CI/test figures in older records remain historical and are not restated as current evidence unless a current run is observed.

## Direction

The next Zazu engineering effort should close the highest-risk remaining release gate: populated commercial workflow → real recovery → representative upgrade → rollback → final Director certification.


## Jarvis update — 2026-10-07

Offline attachment transfer has advanced from server-only foundation to phone-local queued storage and retry:
- private, business-scoped upload/download/list endpoints;
- idempotent attachment identity exposed in sync payloads;
- separate IndexedDB binary storage on the phone;
- offline attachment queue with automatic online retry;
- local opening of stored files without a network connection.

Remaining attachment gate: prove the browser/device flow against a real populated workspace, including a newly created offline job receiving its server identity before its queued attachment uploads.


### Offline attachment integration correction — 2026-11-01

The earlier phone-local attachment transfer note is not a verified active feature. Current-main inspection shows `/offline-attachments.js` is precached but is not loaded by the authenticated application layout and expects legacy globals absent from the current app shell. The Work screen supports online job attachments only. Phone-local attachment queueing/upload/download remains **NOT IMPLEMENTED** until the component is properly integrated and covered by browser/device tests. See `docs/ZAZU_OFFLINE_FIRST_ARCHITECTURE.md`.


### Current Director reconciliation — 2026-11-01

- The Work attachment panel now exposes the existing private download endpoint alongside Remove. The endpoint retains the `work.view` permission and controller business-isolation checks.
- A feature regression now checks that the Work page renders the download link and that an authorized user receives the private file download. A separate existing regression continues to reject foreign-business attachment downloads.
- These changes are committed to canonical `main`; their exact-head Laravel/quality/browser execution is **PENDING**. No current-head test pass is claimed here.
- The active phone PWA still does not load the legacy offline attachment script. Offline attachment queue/upload/download remains **NOT IMPLEMENTED** in the active app. Do not confuse online Work attachment retrieval with offline phone attachment parity.
- Stable HTTPS local-PC hosting, origin migration, disconnected installed-device launch, full offline parity, populated backup/restore, rollback and legal/privacy/brand closure remain open release gates.


## Director recovery-scope correction — 2026-11-04

A security review found that the former business settings backup/restore endpoints invoked installation-wide commands. The commands snapshot or replace the entire configured database and private storage, so exposing them to an individual business owner created cross-business disclosure and destructive-restore risk. The HTTP endpoints and UI controls have been removed. The settings page now states that installation recovery is operator-managed and business self-service export/restore is not available in this build.

The CLI backup/restore commands remain available for controlled installation-operator use. Automated command-level round-trip and hostile-archive tests remain supporting evidence, not proof of a real recovery drill. Tenant-scoped export/restore remains an explicit V1 gap.


## V1 specification scorecard — 2026-11-04

The current evidence-maturity assessment is **3.0/5 (60/100)** across 13 V1 capability areas. This is an evidence score, not a percentage of features built. The detailed area-by-area score, method, blockers and execution order are recorded in `docs/ZAZU_V1_SCORECARD_2026-11-04.md`.

The product has a substantial connected commercial and operational core. It remains **NO-GO for commercial release** while real recovery/rollback, bounded offline acceptance, the offline account/session security challenge, tenant-scoped export/restore decision, legal/privacy operations and Zazu brand clearance remain open. Latest-head CI for `d1e2a4397b064d3fbfe7fc2cf1dc59236e00922f` was queued at assessment time.
