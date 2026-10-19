# Zazu EMP — Commercial Engineering Gate Map

This document is the current execution map. Dated implementation records remain historical evidence.

## Focused Director workflow audit — 2026-10-20

Scope is intentionally bounded to **P0 commercial hand-off** and **P1 operational consistency**.

- [x] Inspect quote → invoice → payment source path and confirm server-side quote/job mismatch protection.
- [x] Fix invoice form ambiguity: quote mode derives/locks the linked job, disables direct lines and tax override, and explains the authoritative source.
- [x] Add focused regression assertion for the quote-bound invoice form.
- [x] Confirm source-level overlap guards exist in both online and offline asset allocation paths.
- [x] Confirm existing populated reconciliation scenario covers quote acceptance → R11,500 invoice → R3,450 deposit → R8,050 final payment → paid state.
- [ ] Run current-main `QuoteCommercialHandoffTest`, `CommercialReconciliationTest` and relevant asset/offline tests.
- [ ] Observe current-main CI result and record exact SHA/run URL.
- [ ] Browser-test quote selection/switch/clear, direct-invoice fallback, keyboard interaction, validation recovery and mobile layout.
- [ ] Traverse one populated job across Work, Quote, Finance, Payments, Assets and completion; compare linked IDs, version, tax, totals, balances and asset dates.
- [ ] Reconcile any failures and rerun affected tests before closing P0/P1.

Evidence boundary: source changes and test additions are **IMPLEMENTED**; current-head execution and rendered browser behaviour remain **UNVERIFIED**. No readiness score uplift.

## 01 Core product workflow

- [x] Authentication/business foundation implemented
- [x] Customer/work foundation
- [x] Requirements/capability foundation
- [x] Quotes and quote revisions foundation
- [x] Travel/cost planning foundation
- [x] Preparation/readiness foundation
- [x] Finance foundation
- [x] Purchasing foundation
- [x] Inventory transaction foundation
- [x] Physical asset foundation
- [x] Operational reporting foundation
- [ ] Customer-facing commercial completion
- [ ] Final end-to-end workflow closure

## 02 Architecture & correctness

- [ ] Remove remaining high-risk coupling/duplication
- [ ] Enforce parent/child invariants consistently
- [ ] Verify critical state transitions under concurrency
- [ ] Confirm historical/immutable commercial snapshots
- [ ] Confirm money/quantity precision across every financial path
- [ ] Verify migration compatibility with representative existing data

## 03 Security & control

- [x] Authentication foundation
- [x] Active business context
- [x] Core business isolation
- [x] Owner/staff boundary foundation
- [ ] Complete granular permission coverage
- [ ] Full authorization-policy coverage
- [ ] Authenticated/private media verification
- [ ] Privacy/retention operational controls
- [ ] Security response procedure

## 04 Commercial completion

- [ ] Quote customer-facing presentation/delivery
- [ ] Quote acceptance
- [ ] Deposit lifecycle
- [ ] Invoice/document generation
- [ ] Payment/reconciliation completion
- [ ] Complete purchasing/receiving workflow
- [ ] Inventory reservation/allocation where required
- [ ] Asset condition/damage/loss evidence
- [ ] Activity/audit continuity
- [ ] Reminders/notifications

## 05 UX & operability

- [ ] Critical workflow browser traversal
- [ ] Desktop runtime verification
- [ ] Mobile runtime verification
- [ ] Light/dark runtime verification
- [ ] Accessibility runtime verification
- [ ] Error/recovery state verification
- [ ] Navigation and workflow continuity review
- [ ] Owner-facing diagnostics/health visibility

## 06 Recovery & release

- [ ] Backup drill
- [ ] Restore drill with representative business data/media
- [ ] Rollback procedure
- [ ] Fresh-install verification
- [ ] Existing-database upgrade verification
- [ ] Dependency/license review
- [ ] Release configuration review
- [ ] Operational runbooks
- [ ] Final release-candidate audit


## 07 Deferred product capabilities & integrations

These items are deliberately **later priorities**, not current release blockers unless a future release scope promotes them.

### Product surfaces to review
- [ ] Zazu Helper — audit usefulness, actual usage and maintenance cost; decide maintain / simplify / retire
- [ ] Platform Admin — audit actual operational purpose, authorization and maintenance value; decide supported surface vs internal tooling
- [ ] Wallpaper — complete the missing wallpaper path while preserving working logo and workspace-image behavior

### Excel / spreadsheet interoperability
- [ ] Excel import foundation
- [ ] Excel export foundation
- [ ] Customers workbook import
- [ ] Products/services workbook import
- [ ] Suppliers workbook import
- [ ] Assets/equipment workbook import
- [ ] Inventory workbook import
- [ ] Safe event/job workbook import where a reliable mapping exists
- [ ] Import template/column mapping
- [ ] Import preview before commit
- [ ] Row-level validation and useful error reporting
- [ ] Duplicate matching / conflict handling
- [ ] Staging area before production writes
- [ ] Transactional commit and rollback
- [ ] Import audit trail
- [ ] Export permissions and business scoping

### Document ingestion
- [ ] Document ingestion boundary
- [ ] PDF/document extraction
- [ ] Scan/photo/OCR extraction
- [ ] Source-document retention and linkage
- [ ] Human review before committing extracted data
- [ ] Structured extraction into supported Zazu records
- [ ] Ingestion audit trail

### Email integration
- [ ] Business mailbox connection model
- [ ] Provider authentication and permissions
- [ ] Inbound message ingestion
- [ ] Outbound operational email integration
- [ ] Attachment handling
- [ ] Link messages to customers/jobs/quotes/invoices where supported
- [ ] Email audit and privacy controls

### WhatsApp integration
- [ ] WhatsApp Business provider/API integration model
- [ ] Inbound message ingestion
- [ ] Outbound operational messaging where appropriate
- [ ] Photo/document/media handling
- [ ] Link conversations/messages to supported Zazu records
- [ ] Consent/privacy/retention controls
- [ ] WhatsApp audit trail

### Unified ingestion architecture
- [ ] Common Source → Staging → Parse/Map → Validate → Preview/Review → Commit → Audit pipeline
- [ ] External sources cannot bypass core business permissions
- [ ] External data cannot write directly to production domain tables without validation
- [ ] Import/ingestion batch identity and source metadata
- [ ] Reversal/rollback strategy for imported changes

## Execution rule

Do not work down this list mechanically.

Morpheus selects the next target based on:
1. release risk;
2. dependency order;
3. evidence available;
4. blast radius;
5. smallest safe action that materially advances readiness.

Tests are supporting evidence, not the objective.

Source of truth: `.ai/engineering/STATE.md` + `.ai/engineering/READINESS_REGISTER.md`.
