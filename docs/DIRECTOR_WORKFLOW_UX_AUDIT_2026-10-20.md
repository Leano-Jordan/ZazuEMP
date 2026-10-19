# Director workflow UX audit — 2026-10-20

## Scope and evidence rule

Bounded audit of the P0 commercial hand-off and P1 operational consistency paths in `Leano-Jordan/ZazuEMP`. This is a source audit with regression coverage added; it is not a claim that current-main CI or a rendered browser session passed.

## P0 — accepted quote → invoice → payment reconciliation

### Confirmed source path
1. Customer and event/job are linked.
2. Quote revisions preserve their own lines and tax snapshot.
3. Acceptance moves the quote/latest version to accepted and can confirm a draft event.
4. Invoice conversion accepts only an accepted quote/latest version, copies quote lines and historical tax treatment, rejects duplicate invoice conversion and wraps writes in a transaction.
5. Payment recording updates invoice state; existing `CommercialReconciliationTest` exercises an R11,500 invoice with a R3,450 deposit and R8,050 final payment.

### Defect found
The invoice creation page showed four conflicting controls together: accepted quote, independent job, direct invoice lines, and direct tax treatment. The backend correctly rejected quote/job mismatches and derived quote invoice lines/tax from the accepted quote version, but the screen invited a mismatched submission and could cause an avoidable 422 dead end.

### Improvement applied
- Quote options expose their linked event/customer/currency/total as metadata.
- Selecting a quote links the event selector to that event and disables it.
- Direct lines are hidden and disabled in quote mode.
- Tax selection is disabled in quote mode because the accepted quote version supplies the historical tax snapshot.
- A live source summary explains which record is authoritative.
- Clearing the quote restores direct-invoice mode and the previously selected manual job.
- A focused feature-test assertion checks the source-mode hooks and quote-to-event binding metadata.

### Verification still required
- Run `QuoteCommercialHandoffTest` and `CommercialReconciliationTest` on current main.
- Browser-test select/switch/clear flows, including keyboard-only interaction and mobile viewport.
- Confirm direct-line fields are not submitted in quote mode and remain usable in direct mode.
- Capture exact current-main CI URL and SHA. No readiness score uplift until observed.

## P1 — asset availability and screen continuity

- Online asset allocation performs an overlapping-date check under transaction/locking.
- Offline allocation mutation also checks overlapping allocations under a transaction/lock and rejects conflicts.
- Existing offline operational test coverage includes allocation/release boundaries.
- Remaining proof: execute tests on current main and run a populated job across Work, Quote, Finance, Payments, asset allocation and completion; compare customer/job identity, quote version, invoice/tax totals, paid balance, asset dates and final lifecycle state across screens.

## Defect disposition

- `ZAZU-UX-FIN-001`: mixed-source invoice form — source fix applied; current-head browser/CI verification pending.
- `ZAZU-FLOW-P1-001`: populated cross-screen consistency — no concrete data corruption proven by this source pass; full traversal remains an open verification gate.
- `ZAZU-ASSET-P1-001`: overlapping asset allocations — guards found in online/offline paths; current-head execution remains pending.

## Release boundary

Source inspection and a passing focused test are not substitutes for current-main CI, rendered browser proof, populated customer workflow, backup/restore or upgrade/rollback. Do not mark any of those gates closed until their evidence is observed.
