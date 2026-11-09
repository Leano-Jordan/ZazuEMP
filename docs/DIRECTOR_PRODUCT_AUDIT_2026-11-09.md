# Zazu EMP — Director Product Audit

**Date:** 2026-11-09  
**Repository:** `Leano-Jordan/ZazuEMP`  
**Branch:** `main`  
**Audited source/test head:** `c1ed1a451d58fb3fe77a312ffd4fa3153d44553a`  
**Audit type:** repository, architecture, test, workflow and release-evidence review. No live customer deployment or physical device was available to this audit.

## Executive decision

**Zazu EMP has a substantial connected event/catering operations core. It is not yet V1 release-certified.**

The core product is coherent: customer → event/job → requirements/preparation → quote → acceptance → invoice/payment → purchasing/receiving → inventory/assets/costs → reporting. Business ownership, role checks, transactional finance controls, audit records, private attachments, offline workspace and exports are present in the repository.

The main risks are no longer a lack of CRUD screens. They are proving that the operational chain survives real conditions: disconnected use, changing network origins, device loss, recovery, deployment failure and legal/privacy obligations.

The last formally scored V1 baseline remains **60/100 (3.0/5, 2026-11-04)**. This audit does not invent a new numerical score while exact-head verification and physical acceptance remain incomplete.

## 1. Product capability map

| Domain | Current repository position | Audit disposition |
|---|---|---|
| Accounts, business context, onboarding | Registration/authentication, membership context, onboarding and role-based route access exist | Implemented; current-head regression run pending |
| Customer and event lifecycle | Customers/contacts, events/jobs, event dates/status, requirements, preparation and completion/cancellation guards are connected | Strong foundation; populated operational acceptance still required |
| Services and quoting | Business capabilities/pricing, quote pipeline, versioning and commercial acceptance are present | Core V1 capability |
| Finance | Invoice/payment/expense paths use cent-based money handling, business locks, transaction boundaries, idempotency and audit records | Strong source controls; real reconciliation/concurrency drill remains |
| Purchasing and receiving | Suppliers, purchase orders, receiving, inventory movements and event-cost linkage exist | Core V1 capability; real stock/GRN reconciliation remains |
| Assets and resources | Asset, allocation, supplier and inventory foundations connect to Work | Implemented foundation; physical workflow acceptance remains |
| Reporting and search | Permission-aware, business-scoped record search, attachment metadata search and event profitability/reporting exist | Implemented; usability and customer-data validation remain |
| Attachments and documents | Private online attachments plus business-scoped phone-local queue/download/reconnect mechanisms are in source | Automated coverage exists; real installed-device proof remains |
| Export and import | 23 owner-only, business-scoped CSV export datasets; customer CSV/XLSX import preview/matching | Export implemented; product/service and supplier import remain commercial gaps |
| Helper and feedback | Contextual Zazu Helper foundation, status feedback, toasts and confirmations exist | V1 foundation; no need to expand autonomous AI scope |
| UX and accessibility | Responsive shell, desktop/mobile navigation, themes, shared component authority and browser regressions exist | Latest visual changes require current-head CI and rendered owner acceptance |
| Offline continuity | Service worker, IndexedDB business workspace, mutation queue, pairing/sync, conflict handling and attachment queueing exist | Bounded offline workflows only; not full application parity |
| Recovery and deployment | Installation-level backup/restore, checksum validation and populated upgrade/rollback automation exist | Automated foundation; real operator restore and deployment rollback remain open |

## 2. Confirmed release blockers and high risks

### P0 — Offline delivery and device acceptance

- Current phone access has been using a temporary PC-hosted LAN address on port 8000. The address changes with the local network and is not a permanent application origin.
- The development runtime is Laravel's built-in PHP server. It is tied to the PC process and is not a production host.
- Plain HTTP on a normal LAN IP is not a secure browser origin. Service-worker registration requires a secure context, so the current access path cannot be treated as a supported installed-PWA origin.
- The service worker caches the shell and selected authenticated pages. IndexedDB stores a bounded business working set and queued changes. This is not a complete local mirror of every Laravel screen or workflow.
- Phone-local attachments have a separate IndexedDB queue and reconnect transfer path. Automated tests do not prove physical force-close/reopen, storage persistence, offline file access or reconnect behavior on the actual phone.
- PC and phone do not currently have a proven, complete device-local offline parity contract. A local PC host is a separate operating mode: the phone still depends on that PC and network.
- Multi-device synchronization while the cloud/host is unavailable is not promised by the current architecture.

**Required outcome:** declare exact PC and mobile offline workflows, install from a secure stable origin, then prove launch, reads, writes, attachments, restart persistence, queued sync, duplicate protection and conflict recovery with internet and host unavailable.

### P0 — Recovery and operational continuity

- Backup format v2 includes SHA-256 checksums for the database and private files; restore validates archive entries before installation mutation and retains v1 compatibility.
- These are installation-operator CLI capabilities, not business self-service restore.
- 23 CSV exports are records-only and are not a restorable backup.
- No representative customer-environment restore proving database, private media, relationships, permissions and application usability has been recorded.
- No real deployment rollback exercise is recorded.

**Required outcome:** restore a representative populated backup to an isolated target, reconcile records and private files, verify access boundaries, then execute a real deployment rollback.

### P0 — Privacy, brand and commercial release controls

- The privacy engineering baseline exists, but final customer-facing privacy/terms decisions, retention/disposal ownership, data-subject request process, incident escalation and operator/data-processing controls are not evidenced as operationally closed.
- Official Zazu name/trade-mark clearance remains open.
- These are release blockers that code-only work cannot certify.

### P1 — Security and trusted-device boundary

- Server-side authentication, role checks, business scoping, private media authorization, hashed pairing/sync credentials, rate limiting and security headers are present.
- The approved offline phone model is business-scoped under owner-approved trusted-device pairing. Normal staff web roles are not a per-record isolation boundary inside that explicitly trusted offline workspace. Physical device access is therefore part of the trust boundary.
- Device revocation exists, but lost-device response, physical screen/device lock expectations and real multi-account/device transition acceptance remain open.
- The current Content Security Policy uses a per-response nonce for scripts but still permits `style-src 'unsafe-inline'`. Style hardening remains incomplete.

### P1 — Production portability

- The Windows PC is documented as a development workstation, not the future customer host.
- Development uses PHP 8.4 and SQLite. Production provider, domain, database engine, persistent file storage, monitoring and recovery targets have not been selected or certified.
- Do not make the development PC a permanent production dependency.
- A stable private development hostname is a separate task from selecting a public production domain.

## 3. Commercial scope — distinguish blockers from later tiers

These are known product gaps, not reasons to destabilize the V1 core:

- Product/service spreadsheet import and supplier import are not yet delivered.
- Staff assignment to work records and scheduled reminders are explicitly tiered later capabilities.
- Advanced autonomous Helper behavior, predictive finance and enterprise BI remain outside V1.

Do not add these ahead of correctness, offline acceptance and recovery.

## 4. Current-head automated verification

The preceding verification attempts exposed test-contract drift rather than a new business-logic regression:

- At `59a5db5`, Laravel reported **409 passed, 3 failed**. The failures were stale UI expectations for the focus colour, title-bar surface and removed route-specific coloured form-section selectors. PHPMD also found one test method at 101 lines against a 100-line limit.
- At `3746d08`, the corrected suite reported **411 passed, 1 failed**. The remaining failure expected an obsolete `.zazu-ops-actions .zazu-btn` selector that is no longer present in the product-authority stylesheet. PHPMD passed; populated upgrade/rollback passed; Psalm passed; browser smoke was still running; Quality failed on the same stale UI assertion. SonarCloud was skipped.
- The obsolete assertion was replaced with selectors that actually exist in the current shared button authority. The test method is now 90 lines. This correction is on `main` at `285fa18`.

**Current exact-head status at `c1ed1a4`:** Laravel, PHPMD, Zazu Quality, Psalm, populated upgrade/rollback and push checks passed. Browser smoke ran all three viewport projects but failed the theme-token test because it still queried obsolete `--zazu-surface-canvas` and `--zazu-blue-primary` aliases rather than the current canonical `--zazu-surface` and `--zazu-primary` tokens. The test is being updated to assert the current palette and the light/dark selected-navigation and Workspace-footer contrast. SonarCloud was skipped and is not counted as a pass.

## 5. Network address decision

Do not replace one private LAN IP with another fixed LAN IP or a `.local` hostname and call the problem solved.

For the current PC-hosted development instance, the recommended bridge is **Tailscale MagicDNS + Tailscale Serve over HTTPS**:

- Install Tailscale on the PC and phone and authorize both in the same private tailnet.
- Give the PC a non-sensitive stable machine name such as `zazu-host`.
- Enable MagicDNS and HTTPS certificates.
- Serve the local Laravel listener through Tailscale Serve, producing a private HTTPS `.ts.net` address.
- The same address can be used when the devices move between Wi-Fi networks, provided the PC/server is running and both devices can connect to the tailnet.
- This does not make the service public, does not keep the PC awake, and does not make server-dependent workflows offline. It is a secure development access path, not the final production deployment.

Tailscale's HTTPS certificate feature publishes the machine and tailnet DNS names in the public Certificate Transparency log. Choose a non-sensitive machine name before enabling certificates.

A public production hostname remains a later paid-server decision. Device-local offline use must work without depending on either hostname.

## 6. Prioritised execution order

1. Close exact-head browser CI after updating the stale theme-token assertions; repair only confirmed remaining failures.
2. Establish a stable private HTTPS development origin for PC/mobile testing; migrate deliberately because browser storage is origin-scoped. Preserve and reconcile pending local queues before changing origins.
3. Audit and implement the declared PC offline workflows.
4. Audit and implement the declared mobile offline workflows, including attachment persistence.
5. Run physical offline acceptance on both devices: disconnect internet and host, force-close, relaunch, read/write, reopen, reconnect and reconcile.
6. Complete the real backup/restore and rollback drills.
7. Close trusted-device, CSP, privacy/legal and brand gates.
8. Re-score V1 and make a release decision from evidence.

## 7. Scope and invariants

- Keep all work in this repository on `main`; do not create branches. A stale `director/v1-sync-ci-repairs` branch was found 124 commits behind main and 0 commits ahead; the available GitHub connection cannot delete refs, so it remains policy drift to remove with authorized branch administration.
- Preserve the connected commercial workflow and business ownership model.
- No new feature expansion during V1 convergence.
- Never claim full offline support from caching or automated tests alone.
- Do not migrate browser origin until pending local data has been reconciled or a tested migration path exists.
- A green source/CI result is not physical-device or commercial release certification.

**Director decision: V1 remains NO-GO until the operational, offline, recovery, privacy and brand gates above are closed.**
