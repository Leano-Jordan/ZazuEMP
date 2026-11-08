# Zazu EMP — Security & Repository Audit

**Audit date:** 3 November 2026  
**Repository:** `Leano-Jordan/ZazuEMP`  
**Branch:** `main`  
**Source baseline:** `6aec3b3e48c10a713dc1786a7f6df93baa64384c`  
**Assessment type:** source-level white-box review, test-coverage review, CI/configuration review and documentation reconciliation.

## Executive disposition

**Do not label Zazu security-cleared or production-ready from this audit.** The repository has meaningful security controls: business-scoped models and queries, role middleware, rate-limited authentication, one-time device pairing, hashed sync tokens, transactional mutation handling, conflict ownership checks and private media delivery. Existing tests also include adversarial cross-business record access.

This review found and addressed:
- CSV/XLSX import resource limits were checked too late or not at all, allowing authenticated import users to submit oversized row sets or highly expanded XLSX packages.
- The customer import controller read a workbook using the wrong return shape; it requested a top-level `rows` key even though the reader returns worksheets keyed by sheet name.
- The API middleware group did not receive the same security headers and request correlation IDs as web requests.

The main offline security boundary is **owner-approved trusted-device access**, not per-staff account isolation. Repository decision DEC-019 explicitly defines pairing as business consent: an owner generates the code, selects the initial data set, and the paired device receives a bounded business-scoped workspace. The settings screen discloses that business data is stored on the paired device. The business-scoped IndexedDB design is therefore intentional and supports the approved shared-device workflow; forcing user-scoped storage would contradict that architecture and risk breaking offline continuity. It does mean that anyone with access to a trusted paired device/browser may be able to use the owner's approved offline data even when their normal web role is narrower. Treat the physical device and browser profile as part of the business trust boundary. Owner revocation, lost-device handling, secure screen/device lock and staff-use expectations must be explicit before customer deployment.

## Findings register

| ID | Severity | Finding | Disposition |
|---|---|---|---|
| ZAZU-SEC-01 | Medium / accepted architecture constraint | Owner-approved phone workspace is business-scoped; normal staff web permissions are not a per-record security boundary inside that explicitly trusted device workspace. | Owner pairing and data disclosure exist; physical device trust, device inventory and lost-device/revocation procedure remain release controls |
| ZAZU-SEC-02 | Medium | CSV/XLSX parsing lacked bounded row, cell, column and expanded-archive limits during parsing. | Fixed in this audit; CI proof pending |
| ZAZU-SEC-03 | Medium | Customer import controller expected a response shape the spreadsheet reader does not return. | Fixed in this audit; feature regression added |
| ZAZU-SEC-04 | Low | API requests were outside the web-only request-ID and security-header middleware. | Fixed in this audit; feature regression added |
| ZAZU-SEC-05 | Medium | CSP script policy required inline nonces; inline styles remain permitted. | Partially fixed — nonce-based script policy in source; style policy and browser acceptance remain open |
| ZAZU-SEC-06 | Medium | Trusted sync-device credentials use a rolling inactivity expiry; rotation is not automatic. | Implemented in source — 180-day rolling inactivity expiry and owner revocation; physical lost-device acceptance remains open |
| ZAZU-SEC-07 | Medium | Incident logs retain exception messages, stack traces and SQL text. | Open — verify log access, retention and sensitive-field redaction |
| ZAZU-SEC-08 | Medium | Main is protected but currently has no required status-check contexts. | Owner action — require quality and security checks |
| ZAZU-SEC-09 | Not assessed | GitHub code-scanning, Dependabot and secret-scanning alert endpoints were not readable through the available connector. | Verify in GitHub Security tab; no clean result is claimed |

## Detail and evidence

### ZAZU-SEC-01 — Owner-approved trusted-device boundary (Medium / release control)

`public/zazu-offline.js` names its primary IndexedDB database using `business_id` only and accepts an existing state when the stored business ID matches the active business. The authenticated user ID is present in the page shell and is used by the service worker's HTML cache, but it is not part of the offline database identity or state acceptance check. The database stores the sync bearer token and broad business workspace data.

The repository's explicit design decision is owner-approved, business-scoped device trust. Pair-code creation is restricted to the owner route; the owner chooses or accepts a bounded bootstrap dataset; the settings UI states that business data is stored on the paired device. The sync token represents that trusted device, and its workspace is deliberately business-scoped so authorised operational staff can use the paired phone for the selected business work.

This is not the same security boundary as the normal server-rendered web UI. A narrower staff role does not automatically make records copied to an owner-trusted device private from that device's other users. Explicit logout clears the local workspace and attempts remote token revocation, but an interrupted logout cannot guarantee server revocation. A lost, stolen or reassigned paired device therefore remains a meaningful exposure risk until its token is revoked.

**Release controls:** document that the owner is authorising the selected data for use by anyone with access to the trusted paired device; require device screen lock and business custody; make the owner able to identify/revoke devices; train the owner to revoke immediately on loss/reassignment; retain the local-clear-before-revoke behaviour. If per-staff record isolation on shared phones becomes a requirement, design it as a separate capability with explicit server permission projection and offline tests. Do not silently change the business-scoped offline architecture or drop local queues.

### ZAZU-SEC-02 — Spreadsheet parser resource exhaustion (Medium / fixed in source)

The upload endpoint already limited the compressed file to 5 MB and the parser used `LIBXML_NONET`, but the parser accumulated CSV rows before the controller's row-count check and expanded XLSX archive entries without a decompressed-size budget.

The reader now enforces:
- 5 MB source-file limit;
- 5,000 data rows (plus header);
- 100 columns;
- 8 KB per cell;
- 250,000 XLSX cells;
- 20 worksheets;
- 256 archive entries;
- 8 MB per expanded archive entry and 20 MB total expanded archive data.

Regression tests cover the CSV row and column limits and an oversized XLSX archive entry. Exact-main CI must pass before this is treated as verified.

### ZAZU-SEC-03 — Customer import response-shape mismatch (Medium / fixed in source)

The reader returns a map of worksheet names to `headers` and `rows`; the controller previously indexed that map as if `rows` existed at its top level. The controller now previews rows from the first worksheet, with a feature test proving preview is read-only and does not create customers.

### ZAZU-SEC-04 — API response middleware (Low / fixed in source)

The `web` middleware group already received `AttachRequestId` and `ApplySecurityHeaders`; the `api` group did not. Both are now attached to `api`, with a feature test covering the sync provisioning validation response. This applies baseline headers and request correlation to API responses as well.

### ZAZU-SEC-05 — Inline CSP allowance (Medium / partially fixed)

The middleware now creates a fresh cryptographically random nonce per response, stores it on the request, and authorizes inline scripts only when they carry that nonce. The identified inline Blade script entrypoints have been annotated, and `script-src` no longer includes `'unsafe-inline'`. `style-src` still includes `'unsafe-inline'`; style attributes and inline style blocks require a separate inventory and migration. Do not mark this finding closed until the style policy is tightened and browser regression passes across every affected entrypoint.

### ZAZU-SEC-06 — Sync token lifecycle (Medium / implemented with acceptance outstanding)

The raw device token is generated with high entropy and only its SHA-256 hash is stored server-side. The current implementation adds a 180-day rolling inactivity expiry: authenticated sync refreshes the expiry, missing or expired expiry is rejected, and owner revocation clears the token hash and expiry. This is inactivity expiry, not periodic token rotation. Automated lifecycle coverage is recorded in the offline architecture evidence; physical lost-device and reassignment procedures remain release acceptance items.

### ZAZU-SEC-07 — Incident-log sensitivity (Medium / open)

The incident recorder logs exception text, stack trace, file/line and SQL text for database exceptions. These are useful diagnostics but may carry business or personal data. Restrict log access, confirm vendor retention and export controls, and redact credentials, bearer tokens and sensitive SQL bindings before production use.

### ZAZU-SEC-08 — Branch protection (Medium / owner action)

The current `main` branch is protected, but GitHub reports empty required status-check contexts. Protection is also configured with enforcement for non-admins, so repository administrators can bypass the checks. Require the actual quality/security workflows before merge and avoid relying on protection as evidence that CI passed.

## Controls observed in source

- Authentication uses identifier/IP rate limits and generic failed-login messaging; registration and password-reset requests are throttled.
- Password reset rotates the remember token and removes other database-backed sessions.
- Business context and business-aware model safeguards prevent explicit cross-business writes; current tests cover direct-ID reads across customers, jobs, invoices, purchasing and assets.
- Permission middleware is applied to business routes.
- Device pairing codes are random, hashed, time-limited and rate-limited; device bearer tokens are stored as hashes.
- Sync mutation IDs are immutable/idempotent; domain writes and delivery publication run transactionally; acknowledgements cannot skip a delivery sequence.
- Conflict resolution checks both business and device ownership.
- Event attachments and customer/profile/business media use private storage and authorization-aware download routes.
- Explicit logout clears the local offline workspace before remote token revocation.
- Phone-local attachment queuing is **not implemented** in the active app. The precached legacy script must not be represented as a shipped feature.

## Test and verification boundary

**White-box:** source and test review completed for routing/middleware, authentication/session handling, business tenancy, sync protocols, attachment/media access, PWA cache/storage, spreadsheet parsing and CI workflows.

**Automated cases added in this change:** API security headers/request IDs; customer import preview data shape/read-only behavior; CSV row cap; CSV column cap; XLSX expanded-entry cap.

**Black-box:** not run against a live Zazu deployment in this repository-only review. No Zazu runtime URL or authenticated test account was provided for an external black-box session. No production database or customer credentials were accessed.

**Security tooling:** repository branch metadata was readable; GitHub Security alert endpoints were not available through the current connector. Secret/dependency/code-scanning status remains unverified until checked directly in GitHub.

**CI:** the pre-audit `Rosscore Director Health Report` run for the baseline commit succeeded, but that is not the Laravel/PHP test suite. The new commit must be evaluated by the exact-head `Zazu quality` and Laravel workflows before any test is marked passed.

## Required next security gate

1. Complete trusted-device lifecycle controls: visible device inventory/revocation, owner lost-device runbook and physical-device lock acceptance; keep business-scoped offline storage as the owner-approved V1 model.
2. Require quality, test and security checks on protected `main`.
3. Review GitHub secret/dependency/code-scanning alerts and the Gitleaks history-scan result.
4. Tighten CSP with nonces/hashes after removing inline script dependence.
5. Verify the 180-day inactivity expiry and revocation behaviour on the designated deployment; document the physical lost-device and reassignment response.
6. Validate deployed HTTPS, trusted-proxy/HSTS behavior, persistent private storage, backup/restore and rate limits on the actual paid host.
7. Run authenticated and unauthenticated black-box tests against a designated Zazu staging deployment before release.

## References

- OWASP File Upload Cheat Sheet: https://cheatsheetseries.owasp.org/cheatsheets/File_Upload_Cheat_Sheet.html
- PHP `ZipArchive::statIndex`: https://www.php.net/manual/en/ziparchive.statindex.php
- Laravel 13 middleware groups: https://laravel.com/docs/13.x/middleware
