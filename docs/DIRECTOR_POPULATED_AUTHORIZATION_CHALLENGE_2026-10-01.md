# Zazu EMP — Director Populated Workflow & Authorization Challenge

Date: 2026-10-01
Repository: Leano-Jordan/ZazuEMP
Branch: main
Observed main head at inspection: ebdebe9365181c1632dcbb804c51fd0905785a76

## 1. Populated business walkthrough

The seeded demo represents an established South African catering operation with:

Customer → Job/Event → Requirements → Quote → Preparation → Purchasing → Inventory/Assets → Invoice → Payment → Reports.

Source evidence confirms:
- Quote/version accepted.
- Event/job completed.
- Requirements match the accepted quote version.
- One event-linked PO is fully received and one open PO is general stock replenishment.
- Inventory and asset history is populated.
- Invoice total is ZAR 11,500.00.
- Deposit is ZAR 3,450.00.
- Final payment is ZAR 8,050.00.
- Total recorded payments equal the invoice total; balance is ZAR 0.00.
- Demo seeding is designed to be idempotent and has dedicated populated-surface regression coverage.

Walkthrough disposition: SOURCE-COHERENT / RUNTIME-UNVERIFIED.

## 2. Authorization challenge

### Challenge A — owner
Owner receives the full permission set by design, with explicit owner middleware retained for owner-only areas.

### Challenge B — staff
Existing automated evidence covers staff denial of owner-only dashboard/settings/catalogue areas and denial of finance mutation actions while retaining Finance view access.

### Challenge C — cross-business access
Active business context checks every directly business-owned route-bound model for the active business. Existing business-isolation tests cover cross-business directory, write, update and job/customer paths.

### Challenge D — seeded manager
AUTH-ROLE-001 — BLOCKING

The demo seeds an Operations Manager membership with role manager. The current permission map has a staff entry but no manager entry. PermissionService therefore resolves the manager to an empty permission set.

This is an authorization role-model completeness defect. It is not evidence of privilege escalation; it is evidence that the declared manager role is currently unusable for permission-protected operations.

### Required release disposition

Before final authorization certification, Zazu must have an explicit policy for the manager role:
- map manager to an intentional permission set and test its protected routes; or
- stop presenting/seeding manager as a supported role and use a defined supported role instead.

No silent assumption is made about which permission set the owner intends.

## 3. Verification boundary

This execution had GitHub repository access but no local Laravel/browser runtime and no accessible deployed Zazu URL. Therefore rendered populated navigation, mobile/tablet behaviour and live adversarial HTTP/browser responses remain UNVERIFIED.

The correct next gate is:

ROLE POLICY CLOSURE → RUNTIME POPULATED WALKTHROUGH → FULL AUTHORIZATION/ISOLATION CHALLENGE → RECOVERY/UPGRADE/ROLLBACK.