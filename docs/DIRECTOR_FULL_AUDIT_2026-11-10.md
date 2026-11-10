# Zazu EMP — Director Full Software Audit

**Audit date:** 10 November 2026  
**Repository:** `Leano-Jordan/ZazuEMP`  
**Canonical branch:** `main`  
**Verified application/test source baseline:** `90b3d1b7ad1c888a69f728df0add75d834884bbf`  
**Disposition:** **V1 RELEASE NO-GO — strong automated baseline, operational and commercial gates remain.**

## 1. Executive assessment

Zazu EMP has a substantial connected event-operations application and a real, bounded device-local continuity layer. The main business domains are represented in the application: business setup, customers, events/jobs, services and requirements, Work/preparation, quotes, purchasing/receiving, inventory/assets, costs, invoices, payments, expenses, reporting, search and attachments.

The architecture is correctly separated into:
- a Laravel application and shared database/private storage for connected operation;
- a service worker for static assets and a bounded, user/business-partitioned authenticated page cache;
- a business-scoped IndexedDB working set and durable offline mutation queue;
- a paired-device sync credential, server-side idempotency and conflict handling.

This is **not** a full offline mirror of every application screen. An installed device can continue only the local workflows and records already in its bounded workspace. Pending changes stay on that device until a safe reconnect. Multi-device synchronisation while the shared server is unavailable is not promised.

The developer PC is a development workstation, not the customer production host. Customer access requires a stable HTTPS deployment on persistent infrastructure. Device-local continuity is intended to keep declared workflows useful during an internet/server outage; it does not remove the need for a production origin while connected.

## 2. Current exact-head engineering evidence

All listed runs are for application/test source `90b3d1b7ad1c888a69f728df0add75d834884bbf`.

| Verification | Result | Evidence |
|---|---:|---|
| Laravel | **417 passed / 2,954 assertions** | [Run 42792043006](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42792043006) |
| Browser regression | **65 passed / 22 skipped / 0 failed** | [Run 42792043223](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42792043223) |
| Quality + secret scan | **PASS** | [Run 42792042787](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42792042787) |
| Psalm security scan | **PASS** | [Run 42792043008](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42792043008) |
| PHPMD | **PASS** | [Run 42792042981](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42792042981) |
| Populated database upgrade/restore/rollback | **PASS** | [Run 42792043233](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42792043233) |
| Director health | **PASS** | [Run 42792042897](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42792042897) |
| Push-on-main / CodeQL | **PASS** | [Run 42792044052](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42792044052) |
| SonarCloud | **SKIPPED** | [Run 42792042834](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42792042834) |

The 22 browser skips are reported separately and are not counted as passes. The E2E suite deliberately avoids repeating certain onboarding/session/offline flows across all viewport projects; the browser run still does not substitute for a physical phone/tablet acceptance.

The populated upgrade/rollback workflow proves its seeded CI recovery journey. It is not evidence that a real customer installation has been backed up and restored by an operator.

## 3. V1 evidence-maturity scorecard

The existing canonical 13-domain score remains **69/100 (3.46/5)**. This is an evidence-maturity score, not a percentage of features completed. Its last formal calculation was 9 November; this audit keeps it unchanged because the new code work improves evidence within an already-scored security domain but does not close any physical or operational release gate.

| Domain | Score | Current disposition |
|---|---:|---|
| Platform, accounts and onboarding | 4/5 | Automated registration/onboarding coverage passes; real customer onboarding still needs acceptance. |
| Core customer → event → Work lifecycle | 4/5 | Connected workflow and populated regressions exist; operator walkthrough remains. |
| Quote → acceptance → invoice → payment | 4/5 | Automated commercial-path coverage passes; live reconciliation/concurrency drill remains. |
| Purchasing, receiving, inventory and costs | 4/5 | Automated domain coverage passes; physical receiving/stock reconciliation remains. |
| Data integrity and auditability | 4/5 | Business scoping, transaction/idempotency and adversarial tests are present; real concurrent-use proof remains. |
| Security and tenant isolation | 4/5 | Security scan and regression pass; trusted-device operating boundary, incident-log controls and remaining CSP attribute migration need closure. |
| Offline continuity | 3/5 | Bounded local data, mutation queue and reconnect logic have automated coverage; installed-device disconnected/restart proof is open. |
| Documents and attachments | 4/5 | Active Work attachment queue, private retrieval and reconnect coverage pass; physical local-file persistence remains unproven. |
| Search, reporting and Zazu Helper | 3/5 | Core search/report surfaces exist; real customer-data usability acceptance remains. |
| UX, mobile, tablet and accessibility | 3/5 | Multi-viewport browser coverage passes; real-device and full accessibility acceptance remain. |
| Recovery and data portability | 3/5 | Populated CI restore verifies database and private media; real operator restore is open. |
| Deployment, upgrade and rollback | 4/5 | Automated populated migration/rollback passes; selected-host deployment rollback is open. |
| Legal, privacy and brand clearance | 1/5 | Registers and drafts exist; operational sign-off and official brand clearance are not evidenced. |
| **Total** | **45/65 = 69/100** | **NO-GO** |

### Additional commercial licensing assessment — not included in the historical 13-domain rubric

**Offline commercial licensing: 1/5 — foundation only.** The `business_licenses` table, `BusinessLicense` model and `OfflineLicenseService` can record and check local entitlement dates/status. Current code search found the service usage in its tests, not an application activation/enforcement flow. The licensing specification still lists signed offline licence format, owner activation, installation identity/reactivation, visible expiry/status, offline renewal/import and enforcement as future work.

If the launch model charges customers for an expiring offline entitlement/subscription, this is a commercial release blocker until implemented and tested. Adding it as a fourteenth equally weighted domain would produce an expanded index of approximately **66/100**; that figure is not directly comparable with the existing 13-domain score.

## 4. Release blockers — required to call this a working customer V1

### P0 — customer operation and data safety

1. **Physical phone and tablet acceptance.** Use the secure HTTPS origin, install the PWA, then force-close it and disable both internet and the PC/development host. Verify the declared offline screens, local reads/writes, visible pending changes, attachment open/save, full restart persistence, reconnect, idempotency and conflict recovery. Record device models, OS/browser versions, tested commit and results. Browser emulation is not a substitute.
2. **Select and deploy the production environment.** Choose the paid persistent host, database and durable private-file storage. Deploy a named release commit behind HTTPS; verify onboarding, authentication, business isolation, media, application health, monitoring, backups and production configuration. Do not use the developer PC or Laravel development server as the customer host.
3. **Run an operator backup/restore drill on a controlled target.** Create a real backup, restore it into an isolated populated installation, then verify customer/event/work, quote/invoice/payment, private media, permissions, tenant isolation and migration state. CI round-trip is supporting evidence only. Current backup/restore commands operate on the entire installation, not one business.
4. **Prove a deployment rollback.** Perform a controlled failed-upgrade/rollback exercise on the chosen deployment, including database and private-file recovery. Record release commit, backup identifier, migration result, smoke result and exceptions.
5. **Complete launch privacy/security operations.** Finalise the real privacy/collection notice, purposes and lawful processing assessment, responsible-party/operator roles, provider inventory, retention/disposal, data-subject request channel, incident escalation contacts and required written operator terms. The current documents are drafts/templates with outstanding placeholders and approvals; repository code is not legal sign-off.

### P1 — commercial and security readiness

6. **Close the offline licensing decision.** For the documented paid offline-entitlement model, implement a signed licence payload, server-held signing key/public-key verification, owner activation/status/expiry UI, installation identity/reactivation, offline renewal/import and enforcement at the correct business boundary. Do not ship a shared signing secret in the client. If the first pilot uses a deliberately manual arrangement, document exactly how issuance, expiry and renewal are controlled before taking payment.
7. **Close trusted-device operations.** Make owner-visible paired-device inventory/revocation and lost-device steps clear; define screen-lock/custody expectations; verify revoke, re-pair, account/business transitions and interrupted cleanup on actual devices. The approved phone workspace is business-scoped: normal staff web permissions are not a per-record isolation boundary inside an owner-trusted offline device.
8. **Finish CSP style hardening.** This audit added per-response nonces to the three standalone inline style blocks and a CSP3 `style-src-elem` nonce directive. `style-src-attr 'unsafe-inline'` and the legacy `style-src` fallback remain for existing dynamic style attributes/browser compatibility. Inventory and remove those attributes before dropping the unsafe allowance.
9. **Complete incident-log controls.** Verify who can read/export application logs, retention/deletion, vendor access and redaction of credentials, tokens, personal data and sensitive SQL values.
10. **Verify GitHub branch rules.** The prior security audit recorded that `main` had no required status-check contexts. The branch-protection endpoint was not readable through this audit connection, so the current setting is not re-certified here. Require the actual quality and security checks in repository settings.

### P1 — identity and commercial clearance

11. **Clear the Zazu name.** The existing brand register records material South African same-name/software collisions and marks clearance HIGH/OPEN. Complete official CIPC searches for relevant marks/classes and obtain professional clearance before meaningful commercial launch; keep a fallback name available. This is a risk requiring clearance, not a finding of infringement.

## 5. Existing scope that should remain deferred

Do not expand V1 to chase every roadmap idea. The capability map and commercial requirements already distinguish some team-tier or later work:
- staff assignment and scheduled team reminders;
- product/service and supplier bulk import beyond the current customer import path;
- full conversational BI/predictive analytics;
- disconnected multi-device peer synchronisation;
- complete offline parity for every server-rendered screen.

These should remain deferred unless the owner deliberately changes the V1 offer. The current V1 offline promise must be workflow-specific and honest, not “the whole application works offline.”

## 6. Development executed from this audit

The source audit found that the CSP nonce protected inline scripts but did not constrain inline style blocks.

Changes committed directly to canonical `main`:
- `app/Http/Middleware/ApplySecurityHeaders.php`: added per-response `style-src-elem` nonce enforcement and explicit `style-src-attr` handling while retaining a legacy fallback for compatibility.
- `resources/views/errors/layout.blade.php`, `resources/views/landing.blade.php`, `resources/views/purchasing/create.blade.php`: added the response nonce to each standalone inline style block.
- `tests/Feature/SecurityHeadersTest.php`: added assertions for the style policy and nonce coverage across those views.

The resulting source/test commit `90b3d1b` passed the complete exact-head verification batch recorded above. **CSP hardening is improved, not complete.**

## 7. Audit limits and next Director sequence

This was a GitHub source/configuration/documentation and CI audit. No production/staging URL, customer test account, local Windows runtime, physical phone/tablet, production database or customer data was available in this session. Therefore live black-box behaviour, real deployment, physical offline operation, operator recovery and legal/brand clearance remain **UNVERIFIED / OPEN**, not assumed to have passed.

Next execution order:
1. Keep the current source/test head green and reconcile this audit into the canonical release ledger and Director state.
2. Execute the documented secure-origin phone/tablet acceptance.
3. Select a production host and run deployment, populated restore and rollback drills there.
4. Implement/decide offline commercial licensing and close trusted-device/logging/CSP residuals.
5. Complete privacy/brand decisions, then perform the final V1 release audit.

**Final disposition: V1 is close to a strong automated engineering baseline, but it is not yet a proven, independently deployable customer release.**
