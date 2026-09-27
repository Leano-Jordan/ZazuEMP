# Zazu EMP Development & Hardening Record — 2026-09-27

## Scope

Director hardening pass across the previously identified release gaps, with an explicit regression-avoidance rule: existing working workflows were preserved and changes were additive or boundary-tightening unless required to prevent duplicate commercial state.

## Implemented

### Finance integrity
- Added business-scoped idempotency keys for invoices, payments and finance expenses.
- Payment posting remains invoice-row locked and balance checked inside a transaction.
- Duplicate browser submissions using the same idempotency key now resolve to the existing operation instead of creating a second posting.
- Important invoice, payment and expense actions are recorded in the operational audit trail.

### Inventory / procurement integrity
- Added idempotency keys to purchase orders and inventory movements.
- Purchase-order receipt processing remains serialized on the purchase-order row.
- Inventory auto-creation during receipt now also serializes on the business row to reduce duplicate inventory-item races.
- Purchase-order child lines are explicitly checked against their locked parent before receipt processing.
- Existing stock-negative protection remains in place.
- Purchase-order status changes and stock movements are audited.

### Permissions
- Added a centralized permission map for staff capabilities.
- Added server-side permission middleware.
- Finance, purchasing and inventory routes now enforce permission names at the route boundary.
- Owners retain the full permission set.
- Existing owner-only settings/catalogue boundaries remain intact.

### Auditability
- Added the audit log store with business, user, action, subject, metadata and request-ID context.
- Added an owner-only Activity Audit screen.
- Added audit records for onboarding milestones, finance actions, purchasing actions and inventory actions.
- Audit writes occur within relevant transactions where the commercial mutation is transactional.

### Backup / restore
- Added `zazu:backup` for SQLite/MySQL database plus private-storage backup into a ZIP archive.
- Added `zazu:restore` with manifest validation and SQLite/MySQL restoration paths.
- Backup staging is outside the private storage tree to avoid recursive self-copying.
- Commands fail clearly when the required PHP Zip extension is unavailable.

### End-to-end testing
- Added a Playwright browser smoke test covering registration → catalogue setup → business setup → dashboard.
- Added a CI workflow that installs Chromium, builds the frontend, starts Laravel and executes the onboarding smoke test.

### UX continuity
- Preserved the existing onboarding sequence and skip/defer behavior.
- Browser verification targets the current Zazu onboarding UI rather than replacing the working flow.

## Recheck

The modified controllers, routes, support classes, commands, views and tests were re-fetched after modification. The Director rechecked the changed source for write-integrity errors and corrected one malformed PHP namespace introduced during the hardening write. A settings view markup defect (`</div>>`) was also corrected and covered by a regression test.

Idempotency was rechecked at the database boundary: generated keys are now established before lookup, and commercial mutations perform their duplicate check inside the transaction rather than relying on a pre-transaction read. Invoice conversion additionally locks the quote version before creating the invoice.

Backup/restore was rechecked for storage isolation and archive-entry safety. Backup archives now live outside `storage/app/private`, and restore rejects unsafe absolute, drive-qualified and `..` archive paths before extraction.

GitHub status/workflow results for the latest hardening commits are not currently exposed by the repository connector. A local clone/runtime verification was also unavailable because the execution environment could not resolve GitHub. Therefore this pass is **source-rechecked but not declared CI-green** until the repository's own Laravel, PHPMD, Psalm and browser checks run successfully.

## Regression findings addressed during the loop

- Settings page: corrected malformed closing markup and added a focused render regression test.
- Idempotency tests: added explicit invoice and expense duplicate-submission coverage after rechecking the transaction paths.
- Repository write integrity: corrected a malformed PHP namespace produced during an automated edit before final review.

## Remaining evidence

- Run the repository's full Laravel/PHPMD/Psalm pipeline.
- Run the new browser smoke workflow successfully.
- Perform a real installation backup and restore using `zazu:backup` and `zazu:restore`, then verify representative customers, jobs, invoices, payments, stock and private media.
- Review migration behaviour against any populated production-like database before release.
