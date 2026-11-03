# Zazu EMP — Production Deployment Readiness

**Status:** FUTURE PRODUCTION TARGET — no production host selected or purchased  
**Current environment:** Owner development workstation; temporary development runtime only  
**Production direction:** Paid, persistent server when the owner is ready  
**Repository policy:** `main` only; no feature branches

## 1. Deployment boundary

The owner's Windows PC is a development machine. It is not the planned permanent Zazu host and must not be configured, budgeted or documented as the customer production server.

Current observed development runtime:
- Project: `C:\Projects\ZazuEMP`
- PHP CLI: standalone PHP 8.4.26 at `C:\PHP84\php.exe`
- Web process: Laravel's built-in PHP development server on port 8000, launched from the VS Code integrated terminal
- Active database: SQLite at `database/database.sqlite`
- Private business files: `storage/app/private`
- AMPPS is installed but is not used to run Zazu. Its bundled PHP 7.4.33 does not satisfy the application's PHP `^8.4` requirement.

The development server is temporary. Its process lifetime is tied to the terminal/process chain. The purpose of local development is implementation and testing, not production hosting.

## 2. Future production outcome

When funding and release readiness permit, deploy Zazu to a paid persistent server with:
- a stable public HTTPS domain;
- supported PHP 8.4+ runtime and required extensions;
- a production web server (Nginx or Apache with PHP-FPM);
- a durable production database selected through compatibility and recovery testing;
- persistent private attachment storage;
- a managed process for Laravel queue workers and scheduled tasks where required;
- automated, off-server backups and a tested restore procedure;
- monitoring, logs, security updates and a documented recovery path.

No cloud provider, server size, operating system image, database engine, or monthly price is selected by this document. Those decisions require a release workload estimate and current market pricing. Do not purchase infrastructure merely to remove the development terminal dependency.

## 3. Application deployment requirements

- Serve only Laravel's `public/` directory; never expose the repository root, `.env`, database files, or private storage.
- Use `APP_ENV=production`, `APP_DEBUG=false`, a stable HTTPS `APP_URL`, secure session cookies and a unique production `APP_KEY`.
- Install PHP dependencies from the committed lock file with production dependencies only; do not run development setup scripts blindly against live data.
- Build frontend assets before deployment. Node/Vite and VS Code must not be required by the running customer application.
- Keep private business attachments outside the public web root and preserve existing authorization checks.
- Configure writable Laravel runtime directories with least privilege; source code should not be broadly writable by the web process.
- Run database migrations as a controlled release step after a verified backup, not automatically on every web process start.
- Configure queue workers and the Laravel scheduler only for workloads that actually require them; ensure process supervision and restart behaviour.
- Keep secrets, production environment files, signing keys and customer data out of Git.

## 4. Database and file-storage decision gate

The current development environment uses SQLite. The repository also contains SQLite/MySQL backup and restore paths. This does not by itself certify a production database choice.

Before selecting the production database:
1. Run the full migration and feature-test suite against the candidate engine.
2. Audit application queries, constraints, locking, transaction boundaries and concurrency-sensitive workflows for engine-specific assumptions.
3. Exercise business isolation, financial mutations, inventory movements, idempotency and offline synchronization under concurrent requests.
4. Run populated backup, restore, upgrade and rollback drills using representative private attachments.
5. Compare operational cost, managed-service availability, backup retention and recovery objectives.

Do not silently switch engines or assume a database migration is a simple file copy. SQLite-to-server-database migration needs a rehearsed export/import and reconciliation procedure.

Private attachments are part of the business data set. A database-only backup is incomplete. The backup set must include the database, private storage, required configuration/recovery material and a manifest/checksum strategy. Backups must be stored independently of the production server.

## 5. Offline behaviour is a separate product requirement

A paid cloud server does not make Zazu offline-first. The app must continue to distinguish:
- **Online:** server-backed workflows and synchronization available.
- **Offline on a device:** only declared local workflows and previously available local records work from the installed app.
- **Reconnect:** queued mutations are retried safely, with idempotency, conflict handling and no duplicate financial or inventory effects.

If a customer loses internet access, their device cannot reach the cloud server. Their installed PWA may continue only the workflows actually implemented in its local IndexedDB workspace and cached shell. The current app is not a complete local mirror of Laravel; do not claim that every screen or workflow remains available.

Service-worker registration requires a secure browser context. Production HTTPS is therefore required for normal PWA installation and service-worker operation. Offline attachment handling and physical disconnected-device proof remain explicit acceptance gates.

## 6. Origin and customer-data migration

Browser storage is origin-scoped. Changing scheme, hostname or port creates a separate storage partition for IndexedDB, Cache Storage, service workers and cookies.

When moving from the current development origin to a production domain:
1. Do not clear browser storage or uninstall the existing app as a migration shortcut.
2. Inspect local mutation queues, conflicts and attachment queues on every paired device.
3. Synchronize and reconcile all pending work while the old origin is still available.
4. Back up the server database and private files, then restore/import into the production environment.
5. Sign in and pair on the new HTTPS origin; verify business identity, record relationships and representative attachments.
6. Submit a harmless test mutation and prove it synchronizes exactly once.
7. Retain the old origin/profile until reconciliation is complete.

Until a tested local-workspace export/import mechanism exists, unsynchronized device-only work must be drained before origin migration. A new origin cannot automatically read the old origin's IndexedDB.

## 7. Security and operations

Production acceptance requires:
- HTTPS with automated renewal and verified certificate chain;
- least-privilege server and database access;
- database not exposed directly to the public internet;
- firewall rules limited to required public endpoints;
- secure production secrets and key recovery procedures;
- routine OS, PHP and dependency security updates;
- application and infrastructure logs with rotation and alerting;
- health checks and documented incident/restart procedures;
- automated off-server backups with retention;
- a successful restore drill to an isolated target;
- documented deployment, rollback and data-recovery steps.

Do not expose development tools, debug endpoints, database ports, private storage or administrative services publicly.

## 8. Delivery sequence

### Phase A — Development independence
- Stop treating the VS Code terminal as the only way to keep a local development instance running when a repeatable local runtime is useful.
- Keep local setup disposable and reproducible.
- Do not configure the owner's PC as a customer production host.
- Keep local development data clearly separate from future customer/production data.

### Phase B — Production portability
- Audit environment-specific paths, URLs, filesystem assumptions, PHP extensions and process dependencies.
- Define a reproducible production build/release artifact.
- Document production environment variables and secret provisioning.
- Verify deployment does not require Node/Vite or development dependencies at runtime.
- Add deployment smoke checks and safe migration/rollback gates.

### Phase C — Production infrastructure selection
- Estimate expected users, concurrent requests, storage growth, attachment traffic and background jobs.
- Compare paid server options and total monthly operating costs.
- Select the database, backup destination and monitoring approach based on tested compatibility and recovery.
- Purchase infrastructure only after owner approval.

### Phase D — Production deployment
- Provision the selected server and domain.
- Deploy a release artifact, configure HTTPS, secrets, database, private storage, queues and scheduler.
- Restore/migrate representative data safely.
- Run security, workflow, backup and recovery acceptance.

### Phase E — Offline and reconnect acceptance
- Install on supported phones/tablets from the production HTTPS origin.
- Close and reopen the app with internet disabled.
- Verify only declared offline workflows and records are available.
- Verify local writes survive reload and device restart.
- Restore connectivity and verify queue retries, idempotency, conflicts and attachment transfer.
- Repeat realistic outage and recovery tests; record limitations honestly.

## 9. Current acceptance status

| Area | Status |
|---|---|
| Local PHP 8.4 development runtime | OBSERVED |
| Current local SQLite database | OBSERVED |
| VS Code-independent local runtime launcher | IMPLEMENTED; owner-PC execution not yet verified |
| Production provider/server selected | NOT SELECTED — intentionally deferred |
| Production release artifact/deployment pipeline | NOT ESTABLISHED |
| Production database engine certified | NOT ESTABLISHED |
| Production backup/restore drill | NOT PROVEN |
| Installed-device offline workflows | PARTIALLY IMPLEMENTED; full acceptance pending |
| Offline attachment transfer | NOT ACCEPTED |
| Reconnect/recovery under realistic outage | NOT PROVEN |

## 10. Current next engineering target

**Production portability audit — repository-only first.**

Inspect environment-specific assumptions, runtime dependencies, asset build requirements, storage paths, queue/scheduler needs, database compatibility and deployment automation. Fix verified portability defects and add regression checks where justified. Keep production provider selection and spending deferred until the software is ready and the owner authorizes infrastructure procurement.
