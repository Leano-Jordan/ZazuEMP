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


## Error-return investigation — consolidation pass

The first populated-operator pass returned errors. Re-inspection of the current repository identified four concrete issues in the audit output itself:

1. **Regression-test write corruption — fixed**
   - `tests/Feature/FinancePurchasingInventoryTest.php` contained flattened PHP model namespaces (`AppModelsSupplier`, `AppModelsPurchaseOrder`, `AppModelsInventoryItem`).
   - This was a repository-write integrity defect, not an application design defect.
   - All three references were restored to valid `\\App\\Models\\...` namespaces.
   - A repository-wide check of the touched files now finds no flattened `AppModels` / `AppSupport` / `AppHttp` / `AppServices` tokens.

2. **Received PO still exposed the receiving panel — fixed**
   - The original UI hid only the button while still rendering the “Receive goods” panel/form for a `received` PO.
   - The lifecycle test expected the closed PO not to expose that workflow.
   - The entire receiving panel is now state-aware: ordered → receive form; received → closed/read-only message; other states → awaiting order message.
   - The server-side receiving guard remains authoritative.

3. **Finance workspace ignored create permissions — fixed**
   - Finance index header actions initially rendered New Invoice / Record Payment / New Expense for every user who could view Finance.
   - Routes correctly enforce separate create permissions, so this created a UI-to-authorization mismatch.
   - Actions now render only when the corresponding permission is granted.
   - Staff regression coverage now verifies the create actions are absent while Finance itself remains accessible.

4. **Vite manifest issue rechecked — confirmed fixed**
   - `resources/css/zazu-mobile-refinement.css` is present in both `vite.config.js` input and `public/build/manifest.json`.
   - This is not an outstanding error in the current head.

### Current verification boundary

The current repository source has been re-read after these corrections. Runtime PHPUnit/browser execution is still not claimed because this environment does not have a usable local Laravel runtime and the GitHub connector currently reports no workflow runs/statuses for the current main commits.

The populated operator audit is therefore **source-corrected and consolidated, but runtime certification remains outstanding**.


## Hardening pass — crash resistance and loose-screw consolidation

With the development PC unavailable, Director continued with repository-level hardening that can be verified from source without pretending runtime success.

### Executed

- Financial permission boundary tightened: staff no longer receive invoice creation, payment recording or expense creation permissions. Finance remains viewable; financial mutation is now owner-level by default. This aligns the role configuration with the existing UI regression expectation and route-level permission gates.
- CI strengthened: the Laravel workflow now caches and clears configuration before tests, runs the full migration set against a fresh SQLite database, then runs the existing PHPUnit suite. This moves migration and configuration failures earlier in CI instead of allowing them to surface only after deployment.
- Environment example corrected: .env.example had literal escaped newline text in the demo credential section; it is now valid line-separated environment configuration.
- Existing crash containment retained: Zazu already has request IDs, incident logging, categorized error handling and a user-facing error page. The recorder has its own failure guard so an error while recording an error does not replace the original failure with a second exception.
- Commercial mutation protection retained: invoice, payment, expense, inventory and purchasing flows use idempotency keys and database uniqueness constraints, while lifecycle-sensitive mutations use transactions and row/business locking.
- Tenant isolation retained: active-business middleware structurally rejects foreign business-bound route models, while mutation paths additionally bind queried records to the active business.

### Verification boundary

Source verification was performed against the post-hardening commits. GitHub currently exposes no usable workflow-run/status result for the latest main commits, so these changes are not called runtime-green yet. The next PC-on pass must consume the CI result and execute the browser/mobile populated journey.

### Release-risk focus after this pass

The main remaining unknown is no longer whether obvious error handling exists; it is whether the complete populated workflow survives real runtime execution under the current build, migrations, database state and responsive layouts. That is the next hard gate before treating Zazu as payment-user ready.
