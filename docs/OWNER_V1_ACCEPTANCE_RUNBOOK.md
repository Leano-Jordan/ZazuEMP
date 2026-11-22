# Zazu EMP — Owner V1 Acceptance Runbook

This is the short hands-on check that engineering cannot honestly complete from source code or CI alone. Use a test business and clearly fake records; do not use live customer or financial data.

## Owner-verified physical and populated-workspace evidence — 2026-11-22

**Status: PARTIAL OWNER ACCEPTANCE RECORDED; no duplicate setup required.**

The Founder reports that the current Zazu installation has been physically exercised on both the PC and the linked mobile phone, and that both devices operate against the same Zazu EMP workspace. The Founder also confirms that the fictional test workspace is already populated with customer/client, quote, invoice, supplier, purchase-order and inventory records, and that these areas work as intended.

The supplied dashboard screenshots show the Zazu Demo Catering workspace, two customer records, one active event (Isaac's Birthday Party), and the dashboard's connected workspace-health indicators. They are useful visual evidence of a populated, rendered workspace, but they do not expose the detailed quote, invoice, supplier, PO or inventory records.

The Founder notes that the business areas can feel operationally separate/disjointed, while still working as intended in their respective modules. Record module-level functionality as owner-reported; do not infer a fully integrated end-to-end transaction chain from the populated workspace alone.

**Acceptance recorded from owner report:**
- PC physical operation: **OWNER-VERIFIED PASS** for normal application use.
- Linked mobile physical operation: **OWNER-VERIFIED PASS** for normal application use.
- Shared/linked Zazu workspace: **OWNER-VERIFIED PASS**.
- Populated business modules (customers/clients, quotes, invoices, suppliers, purchasing/POs and inventory): **OWNER-REPORTED FUNCTIONAL**.
- No need to recreate customers, jobs, quotes, invoices, suppliers, POs or stock records for this acceptance exercise.

**Evidence boundary:** this report does not yet independently demonstrate installed-app cold launch with the host unavailable, airplane-mode operation, offline-created record persistence and exactly-once reconnect sync, attachment round-trip, owner-PC off-disk backup/full restore, or a reconciled quote-to-profit calculation across one identified job. Those remain separate checks unless separately evidenced. The screenshots are dashboard captures, not transaction-level proof. All supplied business data is fictional and contains no live customer or financial information.

## Before testing

- Zazu must be reachable from both the PC and phone at the **same stable HTTPS origin**. A temporary LAN `http://` address is not sufficient for installed-PWA acceptance.
- Sign in while connected and allow the dashboard plus the supported offline workspace to finish loading.
- Confirm the device has been paired/authorised for sync and has completed an initial successful sync.
- Keep the PC/server available for the connected preparation phase only.

For the exact Android LAN-HTTPS setup and host-stop test, follow [ZAZU_ANDROID_OFFLINE_INSTALL_ACCEPTANCE.md](ZAZU_ANDROID_OFFLINE_INSTALL_ACCEPTANCE.md). For the owner-PC backup/restore procedure, follow [RELEASE_BACKUP_RESTORE_RUNBOOK.md](RELEASE_BACKUP_RESTORE_RUNBOOK.md).

If the stable HTTPS environment is not deployed yet, stop here. That is a deployment prerequisite, not a failed phone test.

## Phone — disconnected launch and local work

1. Open Zazu in the phone browser and use **Install app / Add to Home screen**. Launch it once from the installed Zazu icon.
2. While connected, open the dashboard and the supported offline work areas. Wait for initial local data to finish loading.
3. Reuse the existing fictional test customer and linked job/event if they are already present. Do not create duplicate records merely to repeat this runbook. Confirm the records appear on the device before disconnecting.
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

## Business workflow — quote to profit

Use a separate test business and fake records. Do not use real customer or financial data.

1. Create a test customer and a job/event for that customer.
2. Build a quote with the agreed services/items, quantities, prices and tax. Send it and accept it through the customer acceptance flow.
3. Create the invoice from the accepted quote. Check that the invoice retains the quote's line items and totals.
4. Record a deposit, then the remaining payment. Confirm the invoice reaches **Paid** with a zero balance and that replaying a payment does not duplicate it.
5. Create a purchase order for the job, receive the items and record the actual incurred cost against that job.
6. Complete the job and open the event profitability/report view.
7. Reconcile net sales less incurred actual costs. Confirm void invoices, planned costs and cancelled costs do not inflate profit.

**Pass:** the quote, invoice, payments, purchase/receipt, actual costs, job completion and report all refer to the same business and job; invoice balance is zero; profit/margin match the underlying records; audit history is present; retries do not duplicate money or stock.

The automated seeded reference scenario is tests/Feature/CommercialReconciliationTest.php. It exercises a R10,000 net sale, R1,500 tax, R11,500 invoice, R3,450 deposit, R8,050 final payment, R2,800 incurred cost and R7,200 indicative contribution (72% margin). The live-business walkthrough remains a separate owner acceptance gate.
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
