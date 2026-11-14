# Zazu Android Offline Install & Acceptance

**Purpose:** establish a real Android service-worker installation for local V1 acceptance. This is a development/test route only. It does not turn the Windows PC into a production host.

## Why the old phone URL is not sufficient

The LAN address `http://<PC-LAN-IP>:8000` is plain HTTP. Android Chrome does not treat an ordinary LAN IP over HTTP as a secure context, so Zazu cannot register its service worker there. A page that remains visible after the PC server stops is only the already-rendered page; it is not proof of offline navigation or local workflow support.

The HTTPS URL is a different browser origin. The app must be opened, authenticated and installed from that HTTPS origin. Existing HTTP site data and installation state do not transfer to it.

## One-time development setup

### 1. Start the HTTPS route on the Windows PC

Requirements:
- PC and Android device are connected to the same trusted private Wi-Fi.
- Windows identifies that Wi-Fi network as **Private**.
- The official Caddy Windows executable (`caddy.exe`) is installed and available on PATH.
- Do not use a public/guest Wi-Fi network or expose this development port to the internet.

From the Zazu repository root, double-click:

```text
Start-Zazu-LAN-HTTPS.cmd
```

The launcher starts Laravel on loopback (`127.0.0.1:8000`) and Caddy on the PC's private Wi-Fi IP at port `8443`. It prints the phone URL and the Caddy public root certificate path. The expected URL shape is:

```text
https://<PC-LAN-IP>:8443
```

Use the exact URL printed by the launcher. The launcher does not change Windows Firewall or router settings. If Windows Firewall blocks the connection, allow inbound TCP 8443 for the Private profile only.

### 2. Trust the Caddy root certificate on Android

Caddy's internal HTTPS certificate is trusted only by devices that trust its root CA. This is a one-time trust step for each test device.

1. On the PC, use the exact `root.crt` path printed by the launcher. Copy **only** `root.crt` to the Android device using a trusted transfer method.
2. Never copy, upload or share the CA private key. The certificate is public; the private key is not.
3. On Android, open Settings and search for **Install a certificate** or **CA certificate**. On Samsung devices the setting is generally under Security and privacy / More security settings / Install from device storage; labels vary by Android and One UI version.
4. Choose **CA certificate**, select the copied `root.crt`, and confirm the device lock-screen prompt.
5. If Android warns that network traffic may be monitored, that is the consequence of trusting a private certificate authority. Only install this certificate on a device you control for this development test. Remove it after testing if you no longer need the local HTTPS route.

Caddy's root certificate is not a public internet certificate. Do not bypass a browser certificate warning. If Chrome still reports the connection as untrusted, stop and fix certificate trust before installing Zazu.

Reference: [Caddy local HTTPS and internal CA documentation](https://caddyserver.com/docs/automatic-https).

### 3. Install Zazu from the HTTPS origin

1. On Android, open Chrome and enter the exact `https://<PC-LAN-IP>:8443` URL printed by the launcher.
2. Confirm the page opens without a certificate warning.
3. Sign in normally and allow the dashboard to finish loading while the PC server is running.
4. Reload once so the service worker can take control after registration.
5. Use Chrome's **Install app** / **Add to Home screen** option. Launch the installed Zazu icon once while connected.
6. Keep using this HTTPS address and installed icon for this test. Do not switch back to the old HTTP address; HTTP and HTTPS have separate origin storage, service workers and PWA installations.

If Zazu displays **Offline installation unavailable from this address** or **Zazu could not enable offline app support**, the device is not ready for offline acceptance. Do not count the visible page as offline capability.

## Required disconnected acceptance

Run these as two separate checks. The first isolates loss of the PC host; the second proves the installed app can operate without any network.

### A. PC host unavailable, phone still on Wi-Fi

1. Keep the phone connected to the same private Wi-Fi used for installation.
2. Stop the PC-hosted app and Caddy proxy:

```text
Stop-Zazu-LAN-HTTPS.cmd
```

3. Launch Zazu from the installed app icon, not a remembered browser tab.
4. Verify the cached dashboard opens and the device-local workspace shows pending customer/job records.
5. Open the cached Customers and Work directories. Confirm locally saved records remain visible.
6. Create one supported customer or Work record and confirm it is visibly pending on the phone.

### B. Fully disconnected, including cold launch

1. Leave the PC host and Caddy proxy stopped.
2. Turn off Wi-Fi and mobile data on the phone.
3. Force-close Zazu/Chrome, then launch Zazu again from the installed app icon.
4. Verify the dashboard, Customers and Work directories open without network access.
5. Create a supported customer and Work record. Reload, force-close and reopen; confirm the records remain visible and pending.
6. Attach a small test file to a locally created Work record. Confirm the attachment remains available after reload and force-close/reopen.

### C. Reconnect and reconcile

1. Restore Wi-Fi and mobile data.
2. Start the PC host and HTTPS proxy with `Start-Zazu-LAN-HTTPS.cmd`.
3. Open Zazu and verify queued customers, Work records and attachments synchronize exactly once.
4. Confirm the server-side records and attachment bytes match the phone's local working set.

Record host-loss, fully disconnected, restart and reconnect results separately, including device/browser/OS, build/commit and installation origin. A cached dashboard alone is not a pass for offline workflows or attachments.

## What this route does not prove

- It does not make every Zazu screen or every business workflow available offline.
- It does not provide multi-device sync while the server is unreachable.
- It does not replace production HTTPS hosting.
- It does not prove phone acceptance until the disconnected/restart/reconnect checks above are completed on the physical device.

## Troubleshooting

| Symptom | Meaning / next check |
|---|---|
| Old `http://<IP>:8000` page still opens but cannot install offline | Expected for plain HTTP on a LAN IP. Use the HTTPS URL. |
| HTTPS page reports certificate not trusted | Install the matching Caddy `root.crt` on Android; do not bypass the warning. |
| Phone cannot connect to port 8443 | Confirm same private Wi-Fi, Caddy is running, and Windows Firewall permits TCP 8443 on Private profile. |
| HTTPS opens but Zazu reports service-worker registration failure | Reload after certificate trust is established; verify the HTTPS URL and inspect the browser's site settings. |
| Dashboard opens offline but work pages do not | Those routes may not have been primed/cached. Reconnect, visit the required pages, then repeat the disconnected test. |
| Offline changes do not synchronize after restart | Preserve the device state and record the visible sync error; do not clear browser storage or re-pair as a first response. |

## Acceptance status

**Status: OWNER PHYSICAL TEST REQUIRED.** Repository automation can verify service-worker logic and offline browser scenarios, but it cannot install a trusted CA on the owner's Android device or prove that the physical phone can launch and operate while the PC server is stopped.
