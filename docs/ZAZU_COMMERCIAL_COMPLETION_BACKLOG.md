# Zazu EMP — Commercial Completion Backlog

**Status:** Active — first customer-document sharing increment in progress  
**Canonical repository:** `Leano-Jordan/ZazuEMP`  
**Branch:** `main`  
**Owner authority:** Founder  
**Last recorded:** 2026-11-18

This backlog captures the commercial workstreams requested by the owner. Recording them does not mean they are implemented, approved for immediate execution, or release-complete. Keep them visible in future Director status reports and release planning until each item has implementation and acceptance evidence.

## Current release priority

DB-001 remains the active release blocker: repeated database read/write failures have occurred on the real Windows installation. The leading stale-schema hypothesis is not yet proven. Preserve the existing diagnostic-first approach; do not treat the commercial backlog as permission to bypass this investigation.

## Deferred commercial workstreams

### COM-01 — WhatsApp and PDF document sharing — PARTIAL
- [x] Quote customer view can be printed/saved as PDF through the browser's native print dialog.
- [x] Invoice customer view can be printed/saved as PDF through the browser's native print dialog.
- [x] Quote and invoice pages provide WhatsApp message handoff to the corresponding customer view.
- [x] Invoice customer sharing uses a signed, read-only link with a seven-day expiry; void/draft invoices are not shareable.
- [ ] Direct PDF-file generation and attachment sharing (rather than a customer link) remains open.
- [x] Invoice-linked payments have signed, read-only receipt pages with WhatsApp handoff and browser Print / Save as PDF.
- [ ] Verify desktop/Android handoff, including WhatsApp missing, offline use, cancellation and expiry behaviour.
- [ ] Keep private business documents access-controlled; do not expose permanent public file URLs.

### COM-02 — Payment links and payment reconciliation
- [ ] Select and document a suitable payment-provider approach for the South African launch market.
- [ ] Keep provider integration replaceable; do not hard-wire financial workflow logic to one gateway.
- [ ] Attach payment links to the relevant invoice/payment request.
- [ ] Verify provider callbacks/webhooks, signature validation, duplicate events, failed/expired payments and reconciliation into Zazu records.
- [ ] Never mark an invoice paid solely because a user returned from a payment page.

### COM-03 — Billing and licence enforcement
- [ ] Confirm the commercial plans, paid-feature matrix, pricing and billing cadence with the owner before implementation.
- [ ] Implement owner activation, signed licence payloads, installation identity and safe reactivation.
- [ ] Provide clear licence status, expiry, renewal and offline renewal/import flows.
- [ ] Define grace-period behaviour and enforce entitlements at the correct application boundary.
- [ ] Preserve access to customer data and essential export/recovery paths after expiry; expiry must not silently start billing or lock the owner out.
- [ ] Test clock manipulation, copied installations, revoked/invalid licences, offline periods and recovery scenarios.

### COM-04 — Legal pages and consecutive invoice numbering
- [ ] Publish customer-facing privacy and terms pages after appropriate legal review.
- [ ] Close operational POPIA requirements: responsible ownership/contact channels, Information Officer status, data-subject request handling, retention/disposal, operator arrangements and incident escalation.
- [ ] Verify applicable PAIA and other legal-document obligations with qualified South African advice.
- [x] Implement consecutive, collision-safe invoice numbering using the existing `INV-YYYYMM-` prefix, scoped per business and calendar month.
- [x] Serialize allocation with the business transaction lock and a durable per-business/month sequence; voided numbers remain consumed and existing numeric numbers seed the first sequence allocation.
- [x] Online issuance and offline invoice sync use the same allocator. An offline temporary number is replaced by the canonical server number when the synced workspace bootstrap refreshes.
- [ ] Verify invoice and tax-invoice presentation against the customer's actual VAT/tax status and applicable SARS requirements.
- [x] Test sequence continuation, tenant/month isolation, legacy-number coexistence, transaction rollback and idempotent invoice retries. Imports/restores retain the sequence table; older imports seed from existing numeric invoice numbers.
- [ ] Legal review of the numbering scheme and invoice/tax-invoice presentation remains open.

### COM-05 — Customer support path
- [ ] Provide a clear in-app support route and published support contact channel.
- [ ] Include a support reference ID and safe diagnostic bundle for reproducible failures.
- [ ] Ensure diagnostics exclude passwords, session tokens, payment secrets and unnecessary personal/business data.
- [ ] Document severity, response expectations, escalation and owner/operator responsibilities.
- [ ] Test the support path from a real installed customer environment, including offline failure capture and later submission.

## Acceptance and closure rules

- Each workstream requires a scoped implementation plan, security/privacy review where relevant, automated regression coverage and practical acceptance evidence.
- Payment-provider, pricing, legal wording and commercial-plan decisions require explicit owner approval; do not invent these decisions.
- Keep all work on canonical `main`; do not create branches unless the owner explicitly changes that instruction.
- Do not claim a workstream is complete because its specification or draft documentation exists.
- Update this backlog and the living V1 status/release checklist when work begins, changes state or is accepted.

## Source specifications and registers

- `docs/ZAZU_OFFLINE_LICENSING_SPEC.md`
- `docs/ZAZU_COMMERCIAL_LEGAL_REGISTER.md`
- `docs/ZAZU_V1_STATUS.md`
- `docs/V1_RELEASE_CHECKLIST.md`

**Director disposition:** All five commercial workstreams are recorded and deferred for planned execution. DB-001 and the existing V1 release gates retain priority.


## COM-01 implementation checkpoint — 2026-11-18

The first customer-document handoff increment adds browser print-to-PDF on the signed public quote page, WhatsApp handoff from the quote detail, and a seven-day signed read-only invoice customer view with WhatsApp handoff. This is not a generated PDF attachment workflow: the browser creates the PDF when the user chooses Print / Save as PDF, while WhatsApp sends the expiring customer link. Invoice-linked payment receipts now have the same signed view, print and WhatsApp handoff. Direct PDF file generation/attachment and physical desktop/Android acceptance remain open. DB-001 remains a separate release blocker.
