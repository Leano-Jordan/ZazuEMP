# Zazu EMP — Commercial Completion Backlog

**Status:** Recorded for later execution  
**Canonical repository:** `Leano-Jordan/ZazuEMP`  
**Branch:** `main`  
**Owner authority:** Founder  
**Last recorded:** 2026-11-18

This backlog captures the commercial workstreams requested by the owner. Recording them does not mean they are implemented, approved for immediate execution, or release-complete. Keep them visible in future Director status reports and release planning until each item has implementation and acceptance evidence.

## Current release priority

DB-001 remains the active release blocker: repeated database read/write failures have occurred on the real Windows installation. The leading stale-schema hypothesis is not yet proven. Preserve the existing diagnostic-first approach; do not treat the commercial backlog as permission to bypass this investigation.

## Deferred commercial workstreams

### COM-01 — WhatsApp and PDF document sharing
- [ ] Generate professional, printable PDFs for supported customer-facing documents, beginning with quotes, invoices and receipts.
- [ ] Add a WhatsApp share handoff that uses the generated document and appropriate customer context.
- [ ] Verify behaviour on desktop and Android, including missing WhatsApp, unavailable network, cancelled share and file-generation failure.
- [ ] Keep private business documents access-controlled; do not expose public permanent file URLs by default.

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
- [ ] Implement consecutive, collision-safe invoice numbering with transaction-safe allocation.
- [ ] Define numbering scope (at minimum business/issuer and financial document type), uniqueness, concurrency behaviour and treatment of voided documents.
- [ ] Verify invoice and tax-invoice presentation against the customer's actual VAT/tax status and applicable SARS requirements.
- [ ] Test concurrent invoice creation, rollback, retries, imports/restores and numbering continuity. Never silently reuse an issued number.

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
