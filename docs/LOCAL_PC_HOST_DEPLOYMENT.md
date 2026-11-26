# Zazu EMP — Production Deployment Readiness

**Status:** FUTURE PRODUCTION TARGET — no production host selected or purchased  
**Current environment:** Owner PC can run a temporary, trusted-Wi-Fi LAN instance for same-network access and acceptance  
**Production direction:** Paid, persistent public server only when the owner is ready  
**Repository policy:** `main` only; no feature branches


## Authoritative phone-access route and port map

**Use the HTTPS launcher as the single supported phone/tablet route for current PWA and offline acceptance.** Do not choose a URL by combining an IP from an earlier session with a port from another setup. The current Wi-Fi IP is detected at startup and printed by the launcher.

| Address / port | Purpose | Where it works |
|---|---|---|
| `http://127.0.0.1:8000` | Laravel development server / HTTPS proxy upstream | Host PC only |
| `https://<current-PC-private-Wi-Fi-IP>:8443` | Caddy HTTPS reverse proxy | Phone/tablet on the same trusted private Wi-Fi; **canonical phone URL** |
| `http://<current-PC-private-Wi-Fi-IP>:8000` | Legacy/plain HTTP LAN mode via `Start-Zazu-LAN.cmd` | Same Wi-Fi only; not the PWA/offline acceptance origin |
| `127.0.0.1:2019` | Caddy admin API | Host PC loopback only; never a phone URL |
| `5173` | Vite HMR/development asset server when explicitly running the Vite workflow | Development tooling only; not the HTTPS phone route |

### Correct startup sequence for phone testing

1. Stop any manually running `php artisan serve` in the VS Code terminal with **Ctrl+C**. The HTTPS launcher manages its own Laravel process; do not try to run two servers on port 8000.
2. From the Zazu repository root on the PC, run `Start-Zazu-LAN-HTTPS.cmd`.
3. Copy the exact line printed as **Phone/tablet URL**. It will have the form `https://<detected-PC-Wi-Fi-IP>:8443`. The IP is dynamic; do not reuse a previous IP unless the launcher prints it again.
4. On the phone, stay on the same trusted private Wi-Fi and open that exact URL. It must load without a certificate warning.
5. Stop this mode with `Stop-Zazu-LAN-HTTPS.cmd`, which stops both the Caddy proxy and its Laravel upstream.

The HTTPS launcher starts Laravel itself on `127.0.0.1:8000`, enables secure session cookies for that process, verifies/uses compiled Vite assets, and starts Caddy on the selected private Wi-Fi IPv4 address at HTTPS port `8443`. The phone connects only to Caddy; it must not connect directly to port 8000.

### PHP upload limits and child-process inheritance

The business archive restore form accepts files up to 100 MiB. The supported Windows launcher creates a project-local PHP scan file setting `upload_max_filesize=100M`, `post_max_size=110M`, and 300-second execution/input limits. It passes `PHP_INI_SCAN_DIR` and the HTTPS-only `SESSION_SECURE_COOKIE` override through Laravel's Windows `artisan serve` child-process environment filter. Passing PHP `-d` options to the Artisan parent alone is insufficient: Laravel starts a separate PHP HTTP worker, and those command-line options are not inherited by that worker.

The launcher records the runtime configuration signature after the HTTP server passes its readiness check. If an older Zazu worker is already listening without that signature, the launcher safely stops that verified project-owned process and starts it again with the required limits. **After updating the repository, run the supported launcher again; do not leave an older server process running.** A manually started `php artisan serve` process does not receive the project-local upload overrides.


### Certificate identity

The launcher prints the exact Caddy public CA certificate path (`root.crt`). An Android certificate installed previously is suitable only if it is this Caddy installation's matching root certificate. The source repository cannot inspect the phone's installed certificate store, so verify by opening the exact printed HTTPS URL and confirming Chrome shows no certificate warning. Do not reinstall a different certificate just because the URL/IP changed; the root CA and the IP-specific leaf certificate are different things. Never transfer the CA private key.

### Avoid mixing origins and launchers

- `Start-Zazu-Dev.cmd` is the host-PC loopback development workflow.
- `Start-Zazu-LAN.cmd` is plain HTTP LAN access and is not the supported installed-PWA/offline route.
- `Start-Zazu-LAN-HTTPS.cmd` is the canonical phone/tablet route.
- `php artisan serve` in VS Code is not, by itself, the Caddy HTTPS route.
- HTTP and HTTPS are different browser origins with separate IndexedDB, service-worker and installation state. Preserve pending local work and do not clear browser storage when switching origins.


## 1. Deployment boundary

The owner's Windows PC is not the planned permanent public production server. It can, however, host Zazu temporarily on a trusted private Wi-Fi network so phones/tablets on that same network can reach it. This LAN mode is for controlled development and acceptance, not internet-facing production. No domain or paid hosting is required for same-network HTTP access.

Current observed development runtime:
- Project: `C:\Projects\ZazuEMP`
- PHP CLI: standalone PHP 8.4.26 at `C:\PHP84\php.exe`
- Web process: Laravel's built-in PHP development server on port 8000
- Active database: SQLite at `database/database.sqlite`
- Private business files: `storage/app/private`
- AMPPS is installed but is not used to run Zazu. Its bundled PHP 7.4.33 does not satisfy the application's PHP `^8.4` requirement.
- `Start-Zazu-Dev.cmd` starts loopback-only development mode independently of VS Code.
- `Start-Zazu-LAN.cmd` selects the active Wi-Fi interface only when Windows classifies that network as Private, then binds the server to that interface's IPv4 address.
- `Start-Zazu-LAN-HTTPS.cmd` keeps Laravel on loopback and uses Caddy with a local CA to expose a trusted HTTPS origin on the selected Private Wi-Fi IPv4 address; see section 1B.

The LAN launcher does not change Windows Firewall, router settings or network classification. If Windows Firewall blocks inbound TCP 8000, allow it only on the Private profile. Do not expose the development server on public/guest Wi-Fi, enable router port forwarding, or publish port 8000 to the internet. Use the same non-guest Wi-Fi network on the phone/tablet; client isolation can prevent device-to-device access.

## 1A. Temporary same-Wi-Fi access (current near-term target)

For access from a phone/tablet on the same trusted Wi-Fi network:

1. Start `Start-Zazu-LAN.cmd` on the host PC.
2. The launcher requires an active Wi-Fi connection classified as **Private** in Windows and binds only to that Wi-Fi IPv4 address; it does not bind to every network interface.
3. Read the LAN URL printed by the launcher and open it on the phone/tablet while both devices remain on the same Wi-Fi network.
4. If the page cannot be reached, check that the Windows firewall permits inbound TCP 8000 on the **Private** profile only, and that the Wi-Fi/router does not enable client isolation.
5. Stop the server with `Stop-Zazu-Dev.cmd`.

This is HTTP development access. It is sufficient for same-network page/workflow testing, but a phone browser does not treat an arbitrary HTTP LAN IP as a secure context. Service-worker installation and full PWA offline acceptance therefore remain blocked until a trusted HTTPS origin is configured for the LAN. Do not interpret LAN reachability as proof of offline PWA behaviour.

### 1A-1. Vite development assets on the LAN

The Laravel page and Vite asset server are separate HTTP origins during composer run dev. Binding Vite to 0.0.0.0 makes the server listen on the LAN, but it does not automatically make generated CSS/JavaScript URLs point to the PC's Wi-Fi address. VITE_HMR_HOST alone selects the HMR websocket host; it does not set the asset origin.

**Normal phone/LAN access uses compiled assets automatically.** `Start-Zazu-LAN.cmd` removes a stale `public/hot` marker, verifies `public/build/manifest.json` and every referenced asset, and builds the bundle locally when it is missing and `node_modules` is already installed. It then serves CSS/JavaScript from the same Laravel origin (port 8000), so a stopped or unreachable Vite server cannot leave the phone with an unstyled page. The HTTPS launcher uses the same compiled-assets path behind Caddy. The local loopback `Start-Zazu-Dev.cmd` remains the normal Vite/development workflow.

For live same-LAN development with Vite running (HMR), set these local .env values to the PC's current private Wi-Fi IPv4 address (do not commit the real address):

```dotenv
VITE_HMR_HOST=192.168.1.20
VITE_DEV_SERVER_ORIGIN=http://192.168.1.20:5173
```

Replace the example address with the address printed by the LAN launcher, then restart Vite so Laravel's public/hot file is regenerated. The asset origin and HMR websocket must use the same reachable PC address. If starting Laravel manually with built assets instead, stop Vite, remove a stale runtime `public/hot` file, run `npm run build`, and then start Laravel. Never commit `public/hot`.

### 1B. Private LAN HTTPS (implemented in repository; physical trust still open)

The local acceptance origin now uses **Caddy as a TLS reverse proxy with its internal CA**.

**Host setup**
1. Download the official Windows x64 ZIP from [Caddy's download page](https://caddyserver.com/download), extract it, and place `caddy.exe` in the Zazu repository's `tools` folder (`tools\\caddy.exe`). Create the folder if it does not exist. No installer, PATH edit, or administrator access is required. The launcher checks this repository-local file first and also accepts Caddy on PATH.
2. Start `Start-Zazu-LAN-HTTPS.cmd` on the host PC.
3. The launcher requires an active Wi-Fi network classified as **Private**. It starts Laravel on loopback (`127.0.0.1:8000`) and binds Caddy only to that Wi-Fi IPv4 address on HTTPS port `8443`.
4. Open the printed HTTPS URL on the phone/tablet while both devices are on the same trusted Wi-Fi network.
5. If blocked, allow inbound TCP `8443` on the Windows **Private** firewall profile only. Do not open port 8000 to the LAN.
6. Stop the proxy and application with `Stop-Zazu-LAN-HTTPS.cmd`.

The launcher supports the documented standalone PHP location `C:\\PHP84\\php.exe` when PHP is not on PATH. It does not change firewall, router, DNS, Windows network classification or certificate trust settings.

**Certificate trust**
- The launcher prints the path to Caddy's public `root.crt`.
- On the host PC, run `caddy trust --address 127.0.0.1:2019` once under the Windows account that runs Zazu. This matches the loopback-only Caddy admin endpoint; approve any Windows trust prompt.
- Transfer **only** `root.crt` to each test phone/tablet (for example by USB). Never copy the CA private key or the rest of Caddy's private PKI directory.
- On Android, use Settings → Security / Encryption & credentials → Install a certificate → CA certificate (labels vary by device), then select the public root certificate. Android may require a screen lock and will display a user-CA warning.
- Reopen the printed HTTPS URL. A certificate warning means trust has not been completed; do not bypass it and call the device accepted.

**Security boundary**
- Caddy listens only on the selected private Wi-Fi IPv4 address; Laravel remains loopback-only.
- Laravel trusts forwarded HTTPS/host headers only from `127.0.0.1` and `::1`. Regression tests cover trusted proxy HTTPS and spoofing attempts from an untrusted LAN client.
- Caddy's admin endpoint is loopback-only. No router port forwarding, public DNS, public server or Tailscale is required.
- Do not use this on public/guest Wi-Fi or expose ports 8000/8443 to the internet. Wi-Fi client isolation can still block phone-to-PC access.

**Origin migration warning:** HTTP and HTTPS are different browser storage origins. Before moving a phone to the HTTPS URL, reconcile pending mutations and attachments on the existing HTTP origin. Keep the old origin available until queues are empty and verified. Do not clear IndexedDB as a migration shortcut.

**Implementation status:** launcher, reverse-proxy configuration, loopback binding, trusted-proxy restriction and regression tests are committed. Caddy installation, firewall reachability, root-CA installation on the actual phone/tablet, and physical PWA/offline acceptance remain unverified.

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
| Trusted private-Wi-Fi LAN launcher | IMPLEMENTED in repository; owner-PC/firewall/phone reachability not yet verified |
| Production provider/server selected | NOT SELECTED — intentionally deferred |
| Production release artifact/deployment pipeline | NOT ESTABLISHED |
| Production database engine certified | NOT ESTABLISHED |
| Production backup/restore drill | NOT PROVEN |
| Installed-device offline workflows | PARTIALLY IMPLEMENTED; full acceptance pending |
| Offline attachment transfer | NOT ACCEPTED |
| Reconnect/recovery under realistic outage | NOT PROVEN |

## 10. Current next engineering target

**Private LAN HTTPS origin — repository implementation and physical acceptance.**

Implement the Caddy internal-CA reverse proxy, loopback-only Laravel upstream, restricted trusted-proxy handling and stable HTTPS URL generation. Add tests for secure request detection, secure session behaviour and service-worker registration. Then verify root-certificate trust and the offline journey on the actual phone. Keep public hosting/provider selection and spending deferred.
