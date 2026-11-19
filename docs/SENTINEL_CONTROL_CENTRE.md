## Authority and product-surface decision — 2026-11-19

This section is the current product authority and supersedes older surface naming/placement language below where it conflicts.

- **Rosscore Labs Founder Control Centre — `/admin/sentinel`:** the sole Founder’s installation-wide oversight dashboard for monitoring every Zazu instance and platform health. It should show fleet/instance health, operational state and actionable platform signals without exposing unnecessary customer/business-sensitive records. It is not a generic business-owner dashboard.
- **Business Owner Admin with integrated Sentinel Rescue:** Sentinel Rescue (`/sentinel/rescue`) is to be merged into the existing Owner Admin experience as the business's health, diagnostics and safe-recovery capability. It should not remain a separate owner-facing page or navigation destination. Remove duplicated owner dashboard information when implementing this consolidation, based on actual source inspection.
- **Scope boundary:** Founder Control Centre provides cross-instance/platform operational oversight. Owner Admin and its integrated Sentinel capability are restricted to the active authorised business. Do not expose the installation-wide incident ledger, another business's incident metadata or customer records through Owner Admin.
- **Shared capability, separate scopes:** reuse one Sentinel health/diagnostics engine where practical, with explicit server-enforced scope filtering. Do not weaken `sentinel.admin`, `business.context`, `owner` or other route authorization to make navigation work.
- **Navigation:** each authorised surface must be discoverable through persistent in-app navigation without manual URL entry. Preserve a clear return path from Founder Control Centre to the business workspace. Hiding a link is not authorization; direct-route access must remain protected.
- **Implementation status — 2026-11-19:** Owner Admin now contains the business-scoped Sentinel health panel. The legacy `/sentinel/rescue` route redirects to `/owner#business-health`; its standalone controller and view have been removed. Owner navigation exposes Owner Admin, and Founder Control Centre navigation is named explicitly. The business health service checks database response, active owner membership and only that business's customer, event and service data paths. It does not call the installation-wide incident recorder or expose the global ledger.
- **Founder visibility limit:** the Founder Control Centre currently shows the local installation's diagnostic evidence and the registered customer licensing registry. Remote customer installations do not yet send health or connectivity telemetry. A licence record or licence status is not a live health signal. Do not imply fleet-wide live monitoring until a separate privacy-conscious reporting design is approved and implemented.
- **Verification:** focused feature tests have been updated/added for the owner surface, legacy redirect, navigation and business scope. Runtime/CI execution is not yet verified.

---

# Sentinel — Local Operations & Diagnostic Control Centre

**Status:** Engineering mandate, active V1 support work  
**Owner outcome:** A Zazu owner can understand, investigate and safely report a problem without PowerShell, log hunting, internet access or a developer standing beside them.

## Product rule

Sentinel is not merely an error log or an admin-only report. It is Zazu's local operational control centre: a plain-language map of system health, failures, evidence, risk and safe next steps.

The user should not need to understand Laravel, SQLite, migrations, service workers, PHP or Windows internals. Sentinel should collect those details and translate them into an actionable explanation.

## Current baseline and known gap

The existing foundation on `main` includes:

- A privacy-filtered incident ledger stored outside the business database and independent of the configured Laravel log channel.
- Request/reference IDs, versioned failure fingerprints, recurrence counts and first/last-seen timestamps.
- Bounded database metadata (driver, SQLSTATE, driver code, operation and classified failure family).
- A searchable platform-admin console with fingerprint timelines.
- A rate-limited, reference-scoped redacted DB-001 diagnostic download using a 15-minute temporary signed URL.
- A Windows PowerShell fallback and a read-only SQLite schema inspection script.

This is a useful capture and investigation foundation, not a finished owner control centre.

The current `/admin/sentinel` route is protected by `auth`, `auth.session` and the explicit `sentinel.admin` founder gate (configured through one exact founder email). It is not currently an owner-facing control. The existing incident ledger is installation-wide and does not currently establish safe per-business incident ownership. Do not simply remove platform-admin protection or expose the global ledger to every business owner.

The normal owner Rescue route still depends on the configured authentication/session path (`SESSION_DRIVER` defaults to `database`) and may be unavailable during a database/session outage. A separate loopback-only, one-time-code host recovery route now bypasses that dependency for local installation health checks. It does not help when PHP, Laravel bootstrap, the local web process or the host PC is unavailable; packaged Windows acceptance remains required.

## First implemented control-centre increment — 2026-11-18

The current implementation on `main` includes:

- `ZazuSentinelHealth` and `ZazuSentinelLedgerHealth`: verify the private diagnostic directory, inspect active and previous JSONL ledgers, count valid and malformed records without exposing their content, and run a disposable write/read/delete probe.
- `ZazuDatabaseHealth`: issue a read-only database probe, inspect migration history when the connection works, and report pending migration filenames without running migrations. Connection and migration-query failures are captured through the independent incident ledger.
- `ZazuLocalEnvironmentHealth`: inspect private-storage read/write permissions and the presence/readability of the service worker, offline client, web manifest and compiled asset manifest. This does not certify phone cache or sync operation.
- `ZazuSystemHealth`: aggregate the available checks using explicit status states.
- The platform-admin Sentinel console now displays Sentinel's own evidence-store health and links DB-001 timeline entries directly to their redacted reference-specific report.
- Business owners access business-scoped Sentinel diagnostics inside **System → Owner Admin**. The legacy `/sentinel/rescue` URL redirects to the Owner Admin health section and remains behind authenticated owner middleware.
- The rescue page displays the database driver and health result, migration state, storage and offline asset checks, and Sentinel ledger integrity. When a database operation throws, it attempts to record the safe DB-001 incident and provides the matching report link.

### Independent local recovery path — implemented in source, awaiting CI and packaged-host acceptance

A separate local-only recovery screen is available at `http://127.0.0.1:8000/api/sentinel/recovery`. It is deliberately outside the authenticated web/session stack and does not use the database-backed API throttle.

Access is constrained to loopback clients (`127.0.0.1` or `::1`) and loopback hostnames (`localhost`, `127.0.0.1`, or `::1`). It rejects LAN clients, non-loopback host headers and cross-origin form submissions. The screen itself reveals no health data.

To unlock one read-only installation health report, an operator physically using the Windows host runs:

```powershell
php artisan zazu:sentinel-recovery-code
```

The command generates a cryptographically random 256-bit code, stores only its SHA-256 hash in a private local framework file, and displays the code once. It expires after five minutes, is consumed atomically on successful use, and cannot be replayed. Generating a replacement invalidates the previous code. The code is entered into the local recovery page; it is never placed in a URL, sent over the network to a third party, or stored in a browser session. The POST guard requires the exact `application/x-www-form-urlencoded` media type (optional parameters such as a charset are allowed), a loopback peer/host, matching origin scheme/host/port and, when supplied, same-origin Fetch Metadata.

The recovery report runs the existing read-only `ZazuSystemHealth` checks. It reports installation health only and does not expose customer/business records, private attachment contents, the global incident ledger, or the founder Control Centre. It is not a remote/mobile rescue path and cannot help if PHP, Laravel bootstrap, the local web process or the host PC itself is unavailable.

This is a local host-operator recovery mechanism, not an alternative customer login. The normal **System → Sentinel Rescue** remains restricted to the authenticated owner of the active business. The founder Control Centre remains separately founder-gated by the single explicit `ZAZU_PLATFORM_ADMIN_EMAIL` value. The old plural `ZAZU_PLATFORM_ADMIN_EMAILS` list is no longer accepted; an installation must set the singular variable to the founder account's exact sign-in email. A normal business `admin` role never grants this access.

The earlier long-lived bearer-link implementation was removed because possession of a one-year signed URL allowed unauthenticated access. The redacted DB-001 report download now uses a 15-minute temporary signed URL, with Laravel signature and expiry validation plus rate limiting. The URL grants access only to the one reference encoded in the path; it does not expose the Sentinel console or the global ledger. The owner Rescue route itself remains protected by `auth`, `auth.session`, `business.context` and `owner`. This restores the explicit access boundary but means the route relies on the normal user/session authentication path; when the business database or database-backed session store is unavailable, access may fail before health checks run. A separate loopback-only, one-time-code recovery path has now been added for host-operator diagnosis when the normal database/session path is unavailable. It still requires the PHP/Laravel bootstrap and local web process to be operational. The route also requires the local PHP/Laravel process and bootstrap to be running.

**Not implemented yet:** diagnostic ZIP/JSON bundle and manifest, a complete local failure knowledge base, attachment and offline queue/sync probes, session/cache diagnostics, backup integrity/recovery checks, and guided recovery. A healthy database probe is not proof of a healthy schema or business workflow. A present service-worker file is not proof that the physical phone has usable offline caches or can synchronize.

Exact-source verification for application tests, PostgreSQL migration smoke, quality/security and PHPMD is recorded below in the engineering state. Offline-browser acceptance remained in progress at the time of this documentation update. Physical Android and packaged owner-operated rescue acceptance are still separate gates.

## The five control surfaces

### 1. System health — the terrain map

Show each critical subsystem with one of these states:

- **Healthy** — the check ran and passed.
- **Attention** — degraded, approaching a limit or missing optional capability.
- **Failed** — a required operation failed.
- **Not checked** — the check has not run.
- **Unknown** — Sentinel could not obtain enough evidence.

Every result must show what was checked, when it was checked, what it means, its impact, and the next safe action. Never silently convert missing evidence into a green status.

Initial health domains:

| Domain | Evidence Sentinel should collect |
|---|---|
| Application runtime | Zazu build/version, PHP and Laravel versions, required extensions, runtime/config readiness |
| Database | configured driver, read-only connectivity, file/path accessibility without disclosing the path, SQLite integrity where safe |
| Schema and migrations | pending migrations, migration ledger consistency, required tables/columns and known schema contracts |
| Storage | required directories, readable/writable checks, free-space warning where supported |
| Private attachments | private storage availability, metadata and safe read/write probe without exposing customer files |
| Sessions and cache | configured backend and a non-destructive availability check |
| Offline capability | service-worker/build assets, install origin, cache support, local queue health and pending sync state where available |
| Diagnostics itself | ledger directory access, lock/write/read/rotation health, last successful incident write and evidence retention |
| Backup and recovery | last verified backup metadata, archive integrity status and last recorded recovery drill; never imply a backup is good merely because a file exists |

Health checks must be read-only by default. A write probe may use a uniquely named disposable sentinel file in a dedicated diagnostics directory and must clean it up. Database probes must not mutate business records or apply migrations.

### 2. Investigate — explain the failure

For each incident, provide:

- Human-readable problem summary and stable error code.
- Unique reference and fingerprint (including fingerprint version).
- First seen, last seen, recurrence count and recent occurrence timeline.
- Affected component, route/action and source file where safely known.
- Relevant database/storage/session/offline signals.
- Ranked likely causes with an explicit evidence level: **confirmed**, **strong indication**, **possible**, or **unknown**.
- What is affected, what remains safe to use, and what not to do.
- A short, numbered next-step guide.

A fingerprint is a grouping mechanism, not proof of root cause. Sentinel must distinguish captured facts from inferences and must not manufacture certainty.

### 3. Diagnostic package — collect the information support needs

One action should create a portable, locally generated report containing:

- Zazu build and sanitized runtime/environment details.
- System-health results and timestamps.
- Incident references, fingerprints and safe incident metadata.
- Relevant migration/schema status and bounded infrastructure diagnostics.
- Offline queue/sync and attachment-health summaries where available.
- Sentinel's own capture/ledger health.
- A manifest describing what was included and deliberately excluded.

Default format: a human-readable HTML or text summary plus machine-readable JSON in a ZIP. The package must be generated without internet access. It must not include `.env`, secrets, session/token material, raw SQL, bindings, exception messages, stack traces, customer/business records, private attachment bytes or personal data.

Users should be able to save the report locally and share it deliberately. Do not silently transmit diagnostics.

### 4. Guided recovery — safe, explicit and reversible

Each known problem should explain the safest recovery path. Checks and diagnosis are read-only by default.

Any action that changes data, schema, credentials, installation state or files must:
1. State exactly what it will change and why.
2. Check prerequisites.
3. Create and verify a recoverable backup when data could be affected.
4. Require an explicit owner confirmation.
5. Record the action and result in an audit trail.
6. Verify the postcondition and explain failure without hiding the original evidence.

Never automatically reset, migrate, restore, delete or rewrite a business database as a diagnostic shortcut.

### 5. Local knowledge — the offline field manual

Package explanations, known failure fingerprints, safe troubleshooting steps and recovery runbooks with the application. Search and open them locally with no external service.

Each entry should have a code, plain-language symptom, detectable signals, likely causes, safe checks, recovery boundary and version/history. A generic error page should lead to the matching local explanation and preserve its incident reference.

## Access and isolation

Maintain distinct access scopes:

- **Platform administrator:** may inspect installation/platform-wide incident data through the existing protected console.
- **Business owner:** may inspect only diagnostics that can be safely attributed to their authorised business/installation. The owner view must not expose other businesses' incident metadata.
- **Staff:** no access to technical diagnostic packages or installation-wide details unless a future explicit permission is designed and tested.

Do not weaken the existing platform-admin gate. Before owner access is implemented, introduce and test an explicit incident ownership/installation scope. If a failure occurs before business context can be resolved, record it as installation-level and show it only through the appropriately protected local installation control.

## Resilience requirements

Sentinel's diagnostic path must not depend on the component it is diagnosing:

- Incident capture must remain independent of the business database and primary log channel.
- A broken business database must not prevent read-only database/schema diagnosis.
- Database-backed sessions must not be an invisible dependency of the only diagnostic entry point.
- The diagnostic ledger must report its own write, lock, read and rotation failures.
- The core health screen and local runbooks must not require external internet services.
- A PC-host outage, LAN outage, internet outage and database outage are different failure modes. Sentinel must identify which one is being tested; cached pages alone do not prove that diagnostics or business workflows are operational.

If the full Zazu application cannot boot, Sentinel needs a deliberately designed local fallback path. Do not claim this capability exists until it is implemented and tested on the packaged Windows installation.

## Delivery sequence

1. **Resilience and access audit:** map the bootstrap, session, authentication, database, storage and ledger dependencies. Define the local fallback boundary and safe owner/installation scope.
2. **Sentinel self-health:** expose ledger health and prove that capture still works when the business database and primary logger are unavailable.
3. **Read-only system checks:** implement a versioned check registry with explicit Healthy / Attention / Failed / Not checked / Unknown results. Start with DB-001, migration/schema, storage, session/cache and offline dependencies.
4. **Plain-language investigation:** connect error codes and fingerprints to evidence-backed explanations and next steps.
5. **Owner control surface:** make the safe, scoped diagnostics experience discoverable inside Zazu without opening the global platform ledger.
6. **Diagnostic package:** create a tested, redacted, offline ZIP report with a manifest.
7. **Guided recovery and local knowledge:** add only verified procedures, with backup and confirmation boundaries for mutations.
8. **Acceptance:** run fault-injection tests and the intended packaged Windows/physical-device scenarios with internet disconnected. Include database unavailable, stale schema, locked/read-only database, inaccessible storage, ledger failure, missing assets and offline sync failure.

## Definition of done

Sentinel is not production-ready until all of the following are proven:

- A non-developer owner can find Sentinel and understand its status without instructions from support.
- It explains the difference between healthy, degraded, failed, not checked and unknown.
- It can capture an incident when the business database or normal logging path is unavailable.
- It can identify the likely failure domain with supporting evidence and honest confidence.
- It produces a useful, safe diagnostic package without internet or manual commands.
- Its output excludes secrets and business/customer data and respects business isolation.
- A failed diagnostic subsystem is itself visible.
- The authenticated local entry point survives the failure modes it claims to diagnose, including the configured session dependency, without using a transferable bearer URL.
- Known recovery steps are safe, tested and explicit about when human intervention is required.
- DB-001 is demonstrated end-to-end on the intended installation: capture, explanation, package, targeted correction and successful repeat.

**No release-readiness uplift is implied by this mandate or by documentation alone.**


## Owner dashboard interaction

Sentinel Rescue is a non-destructive owner diagnostic surface. The owner can filter checks by status, search diagnostic labels/messages, expand or collapse available detail sections, and re-run the health inspection with **Recheck**. Failed and attention findings now include key-specific safe next steps. DB-001 findings with a valid reference link to the existing redacted incident report; no new repair or mutation endpoint is exposed. Database probes remain read-only; the existing diagnostics write/read probe creates and removes a uniquely named temporary file. No business records are changed and no external service is contacted.


## Local recovery code maintainability

The local recovery middleware separates loopback, same-origin POST and fetch-metadata checks into small private guards. The one-time challenge service separates code format validation, locked challenge reading, expiry/hash validation and invalidation. These are structural refactors only: the local-only boundary, form content-type requirement, origin scheme/host/port match, optional same-origin Fetch Metadata check, 256-bit random code, SHA-256 stored hash, five-minute expiry, lock-protected single use and failure invalidation remain in place. Feature coverage includes cross-origin rejection, port mismatch, one-time use, expiry and malformed challenge data.


## Incident ledger storage permissions

The private incident ledger re-applies restrictive filesystem modes during writes: the diagnostics directory is set to `0700`, and the lock and JSONL ledger files are set to `0600` (best effort on Windows, where effective access control is governed by NTFS ACLs). This also tightens pre-existing directories/files created by older installs. The ledger remains an allowlisted, redacted record store and is independent of the business database and Laravel log channel. Windows package acceptance must continue to verify the actual installed directory ACLs; PHP `chmod` success alone is not proof of NTFS isolation.


### Windows NTFS enforcement

The Windows installer protects `.env`, `database`, `storage/app/private`, `storage/framework`, `storage/logs` and `bootstrap/cache` by removing inherited parent access and applying Full Control only to the installing Windows account, Local System and local Administrators. Existing descendants are normalised; reparse points in protected paths cause installation to stop rather than follow an unexpected target. New files and directories inherit the restricted ACL. The Windows fresh-install acceptance workflow creates a real incident ledger and checks the protected roots plus database, diagnostics, lock and ledger ACLs against the allowed SIDs. This is separate from PHP `chmod`, which is not an effective Windows access-control guarantee.


## Founder licensing workspace

The founder-only `/admin/sentinel` console now includes a Licensing & Entitlements section backed by a founder-side customer registry. The founder registers a customer using the stable Business Licensing Reference UUID shown in that customer's Business Settings, then searches by business name, email or UUID and issues, renews or revokes signed tokens. Customer activation remains in the existing owner-only Business Settings workflow.

Issuance reuses the existing `zazu:license:issue` signer from a server-side request. Configure `ZAZU_LICENSE_PRIVATE_KEY_FILE` as an absolute path to the base64 Ed25519 secret-key file outside the Zazu project. `ZAZU_LICENSE_PUBLIC_KEY` must contain the matching public key. The secret-key bytes are never sent to the browser, stored in the database, or written to the application log. The signed token is shown once in the founder's authenticated session for delivery to the matching business owner.

Each issue/renew/revoke action is rate-limited and writes a metadata-only application log entry containing the business, licence reference, plan, action and actor. The signed token and private-key material are excluded. A renewal reuses the registry's current licence UUID when one is present. Tokens are version 2 and bind to the stable business UUID, never the local numeric Business ID. Revocation is a signed token and only takes effect on a customer installation after its owner imports that token; this is an offline licensing system, not a remote kill switch.

The UI exposes the existing Basic, Intermediate and Advanced plan identifiers. It does not introduce feature restrictions or claim application-wide paid-tier enforcement. Do not issue customer licences with a production key until key custody, backup and Windows ACLs for the external key file have been deliberately established and the exact-head test workflows pass.


The founder registry is required because first-customer licensing is local/offline and does not rely on a shared cloud database. It stores customer identity and current licence metadata in the founder's installation. A customer's local database is not queried. The customer supplies the UUID from Business Settings; the signed token is verified against that UUID in the customer's own installation. Version-1 numeric-ID tokens are rejected.
