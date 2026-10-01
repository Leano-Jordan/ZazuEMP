# Zazu EMP — Director Populated Operator Audit
## 2026-10-01

### Target

Audit the populated demo business as an established operator rather than as empty test fixtures.

Journey reviewed:

Dashboard → Job → Requirements → Quote → Purchasing → Inventory / Assets → Preparation → Invoice → Payment → Reports

The test was deliberately biased toward continuity: every screen should expose the records created by the previous step, preserve job context where appropriate, and present the next useful operator action.

## Findings and action

| Area | Finding exposed by populated data | Action |
|---|---|---|
| Dashboard | A completed business with open procurement/resources could still report “No immediate priority” because the priority queue only considered setup, future jobs and draft quotes. | Added open PO, low-stock and unpaid-invoice attention signals and live resource counts for Intermediate/Advanced users. |
| Job register | Function-sheet inspector used `HTMLTemplateElement.firstElementChild`, so the populated template data was not read correctly. The preview itself contained placeholder values instead of live requirements/finance/resources. | Switched to `template.content`, populated the inspector from real requirements, preparation, rental demand, asset allocation history, quote and invoice data. |
| Job register | Guest headcount was displayed as “not recorded” even when a guest requirement existed. | Surface the recorded guest quantity in the register. |
| Job workspace | An extra closing section could disturb the document structure/layout around the operational chain. | Removed the orphaned closing tag. |
| Purchasing | Job → Purchasing discarded event context by opening the global register. | Added event-scoped purchasing filter and preserved query state. |
| Purchasing | A received PO still exposed a “Receive goods” workflow even though another receipt could not be validly processed. | Receiving now accepts only ordered POs; received POs no longer expose the receipt form. |
| Purchasing | Job-scoped “New purchase order” did not carry the selected job into the creation form. | Carry `event_id` into create and preselect the job. |
| Inventory | Issue form supported server-side job attribution but offered no event selector in the UI. | Added active-job selector to stock issue forms while retaining a general-stock option. |
| Finance | Finance index had transaction data but no direct entry actions for invoice, payment or expense creation. | Added explicit workspace CTAs. |
| Invoice | Invoice detail showed reconciliation but not payment history or a direct job hand-off. | Added payment history, paid/outstanding position and Job workspace link. Issued invoices now expose Record payment. |
| Payment | Invoice → payment hand-off could not preserve the invoice selection. | Payment form now accepts and preselects `invoice_id`. |
| Reports | Reporting page still described finance as gated even though invoice/payment/expense records already existed. | Added currency-safe financial snapshot and expense totals. |
| Demo integrity | Demo PO-002 was linked to a completed event while remaining ordered, contradicting the lifecycle rule that blocks completion with active POs. | Made PO-002 a general replenishment order. |
| Demo chronology | Invoice due date was later than the seeded payment history in a way that obscured the intended 7-day terms. | Set due date to one day before the current audit date, making the eight-day-old issue and seven-day terms coherent. |

## Populated scenario now demonstrates

- established South African catering business identity;
- owner, manager and finance staff memberships;
- service/rental catalogue;
- customer and primary contact;
- completed historical job;
- linked requirements and accepted quote;
- preparation completion;
- received and open purchasing records;
- inventory receipts and event usage;
- asset inventory and released allocation history;
- event costs and travel calculation;
- compliance records;
- invoice, deposit and final payment;
- finance expenses;
- operational and financial reporting.

## Regression coverage added

`tests/Feature/DemoScenarioSeederTest.php` now exercises the populated operator surfaces and verifies:

- command-centre resource signals;
- live job function-sheet content;
- job → purchasing navigation state;
- received PO does not present a receipt workflow;
- invoice payment history;
- Finance entry actions;
- financial reporting visibility;
- removal of the known malformed section sequence;
- PO-002 remains general rather than attached to completed work;
- demo seeder remains idempotent.

`tests/Feature/FinancePurchasingInventoryTest.php` now additionally covers:

- rejection of further receipts after a PO is fully received;
- active-job attribution in the inventory issue form.

## Verification boundary

Repository state was re-read after the changes.

The current GitHub connector exposes no status entries or workflow runs for the latest main-branch commit. Its workflow-run wrapper only exposes pull-request-triggered runs for this repository, so a fresh main-branch CI result cannot be honestly claimed from this environment.

Local Laravel/PHPUnit/browser execution is also not claimed because the local environment previously could not clone the public repository due unavailable outbound DNS/network access.

The existing PHPMD/quality failures observed before this cycle remain a separate technical-debt track unless a new run proves otherwise.

## Director assessment

The populated business is now a materially better end-to-end test fixture.

The important shift is that the UI is being checked against real relationships rather than against isolated records:

Customer → Job → Services → Quote → Preparation → Purchasing → Resources → Invoice → Payment → Reporting

The remaining release-critical verification is runtime execution of this populated journey on desktop and mobile, followed by any defects that execution actually exposes.

## Next target

**RUNTIME POPULATED WALKTHROUGH**

Execute the seeded business through the actual browser and verify:

1. desktop navigation and rendered job inspector;
2. mobile drawer/inspector/forms;
3. job-scoped purchasing;
4. inventory issue attribution;
5. invoice → payment hand-off;
6. reports rendering;
7. no visual overflow/clipping introduced by the populated content;
8. seeded scenario still passes idempotency and lifecycle invariants.
