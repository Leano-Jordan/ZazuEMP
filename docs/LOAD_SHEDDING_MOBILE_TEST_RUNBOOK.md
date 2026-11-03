# Zazu EMP — Local Development and Mobile Test Runbook

**Purpose:** Run the development copy without keeping VS Code open, and distinguish local-network testing from real installed-device offline acceptance.

## Start and stop local development

The repository includes double-click launchers:
- `Start-Zazu-Dev.cmd` starts the Laravel development server as a detached process, defaulting to loopback on port 8000.
- `Stop-Zazu-Dev.cmd` stops only a PHP listener whose process chain can be verified as belonging to this Zazu project.

The launcher:
- uses the PHP executable already available on PATH and rejects versions below PHP 8.4;
- checks that the project, Composer dependencies and local `.env` exist;
- refuses to take over an occupied port;
- writes timestamped stdout/stderr logs under `storage/logs`;
- makes no Windows service, registry, firewall, router, DNS or certificate changes.

This is a convenience for local development and testing. It is not a production service and is not a replacement for the future paid production server.

## Phone-on-LAN testing

For temporary phone testing, the PHP development server can be bound to the PC's LAN interfaces on a trusted private network. The phone and PC must be on the same LAN/hotspot. Do not expose the development server to public networks.

**Important:** plain HTTP on a LAN IP is not a secure browser context. Service-worker registration is skipped there, so this setup can test responsive screens and server-backed flows but cannot certify installed-PWA offline behaviour on the phone. Do not interpret a reachable page or home-screen shortcut as proof that the service worker controls the app.

## What this test proves

- The local Laravel app can run without VS Code remaining open.
- The PC browser can reach the local development app.
- A phone can reach the app over a trusted LAN when the server is deliberately bound to the LAN interface.
- Server-backed flows work while the local development host is available.

## What this test does not prove

- Production hosting, HTTPS, automatic startup after reboot or service recovery.
- Phone operation when the development PC is off.
- Service-worker control from an insecure LAN origin.
- Full offline parity across every Zazu business workflow.
- Durable production backups or successful customer recovery.

## Offline/PWA implementation boundary

Zazu includes a user-scoped authenticated page cache, a phone-local IndexedDB workspace and durable mutation queue, owner-approved pairing/sync foundations, and domain-specific offline mutation handlers.

The current phone workspace is not a complete disconnected replacement for every web-app screen and workflow. In particular, the standalone `public/offline-attachments.js` file is legacy/orphaned code: it is not loaded by the active app layout and expects globals that the current app does not provide. Offline attachment queue/upload/download is therefore **NOT IMPLEMENTED in the active app**. Do not load that legacy file directly.

Offline reads, writes, attachments, conflict handling, restart persistence and reconnect reconciliation must be judged against their specific automated and physical-device acceptance tests. Do not infer complete parity from the presence of a PWA or service worker.

## Future production deployment

The owner's Windows PC is development-only. The planned production environment is a paid persistent server, selected later when funding and release readiness permit. No provider or server size has been selected. See [Production Deployment Readiness](LOCAL_PC_HOST_DEPLOYMENT.md) for provider-neutral requirements and acceptance gates.

Production infrastructure selection does not replace the offline work: supported device-local workflows must continue while internet is unavailable and reconcile safely when connectivity returns.
