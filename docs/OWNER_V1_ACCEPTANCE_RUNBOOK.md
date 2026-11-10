# Zazu EMP — Owner V1 Acceptance Runbook

This is the short hands-on check that engineering cannot honestly complete from source code or CI alone. Use a test business and clearly fake records; do not use live customer or financial data.

## Before testing

- Zazu must be reachable from both the PC and phone at the **same stable HTTPS origin**. A temporary LAN `http://` address is not sufficient for installed-PWA acceptance.
- Sign in while connected and allow the dashboard plus the supported offline workspace to finish loading.
- Confirm the device has been paired/authorised for sync and has completed an initial successful sync.
- Keep the PC/server available for the connected preparation phase only.

If the stable HTTPS environment is not deployed yet, stop here. That is a deployment prerequisite, not a failed phone test.

## Phone — disconnected launch and local work

1. Open Zazu in the phone browser and use **Install app / Add to Home screen**. Launch it once from the installed Zazu icon.
2. While connected, open the dashboard and the supported offline work areas. Wait for initial local data to finish loading.
3. Create one clearly named test customer and one test job linked to that customer. Confirm both appear in Zazu before disconnecting.
4. Close Zazu fully. Turn on Airplane mode (make sure Wi-Fi is also off). Leave the PC/server unavailable or disconnect the phone from it.
5. Launch Zazu from its installed icon. The real dashboard/workspace should open, not a browser error or a generic offline page.
6. Close the app fully and launch it again while still disconnected. Confirm the test customer and job remain visible.
7. Create a second test customer and a second test job while disconnected. Confirm Zazu shows that the changes are saved locally/pending sync.
8. Close and relaunch once more while still disconnected. Confirm the second set of records remains visible and pending.

**Pass:** installed app launches twice with the PC/server and internet unavailable; locally saved records survive relaunch; supported offline writes are visibly pending sync.

**Fail:** Zazu requires a server request to launch, loses locally saved work, or claims a record was saved when it was not.

## Phone — reconnect and duplicate-safety

1. Restore internet access and make the server reachable.
2. Open Zazu and allow synchronization to complete.
3. Confirm the two offline-created customers and jobs appear in the connected workspace.
4. Refresh/reopen the app and confirm each record appears exactly once.
5. Check that the pending queue clears only after server acknowledgement. Record any conflict or error shown; do not repeatedly press retry if the same error persists.

**Pass:** all four test records sync once, remain linked correctly, and the pending queue clears after acknowledgement.

## PC — installed app without development tooling

1. Open the same HTTPS Zazu origin in the supported desktop browser and install the app.
2. Close the browser tab and launch Zazu from the installed desktop app shortcut.
3. Confirm the dashboard and normal connected workflows open without VS Code, Artisan, or a manually started local development server.
4. Repeat the disconnected launch/relaunch and one local test-record creation if the PC browser is expected to support the same offline workflows.

**Pass:** installed desktop app opens independently of the developer workstation tooling and retains its supported local work while disconnected.

## What to send back

For each device, report:
- device and browser;
- installed-app launch: pass/fail;
- disconnected second launch: pass/fail;
- offline customer/job save and relaunch: pass/fail;
- reconnect sync exactly once: pass/fail;
- screenshot of the offline/pending indicator and any error.

Do not send customer data, passwords, licence tokens, or private business documents. A short note with pass/fail and screenshots of fake test records is enough.

## Engineering-only gates (not owner testing)

These remain separate from the device exercise: exact-head CI, application/security tests, populated database and private-media restore, production deployment smoke and rollback, trusted-device/lost-device security, operational privacy/legal closure, and official Zazu brand clearance. A successful phone test does not close those gates by itself.
