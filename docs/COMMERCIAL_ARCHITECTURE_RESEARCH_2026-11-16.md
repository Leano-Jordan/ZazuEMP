# Zazu EMP — Commercial Architecture Research

**Research date:** 2026-11-16  
**Purpose:** Translate current event/catering software patterns and commercial user feedback into evidence-based V1 architecture and release standards for Zazu EMP.

## Executive view

The strongest commercial event/catering products converge on one operational booking record. Client communication, proposals/contracts, event orders, deposits/invoices, purchasing/preparation and reporting are connected to that record. Their value is not simply breadth: it is fewer re-keyed facts, fewer missed handoffs and a reliable current version of each event.

Zazu's existing Business → Customer → Event/Work → Requirements → Quote → Confirmation/Payment → Purchasing → Preparation → Costs/Finance chain is directionally aligned. The V1 job is to make that existing chain dependable, legible and installable—not to copy every competitor's optional module.

## Market evidence

| Reference | Public evidence checked 16 Nov 2026 | Commercial pattern worth learning from |
|---|---|---|
| Tripleseat | Official features include inquiry inbox, proposals/contracts/BEOs, deposits/payments, dashboards, guest portal and floor plans. Capterra displayed 4.7/5 across 576 reviews; review page last updated 9 Nov 2026. | A booking is the centre of client, sales and on-site operations; teams need one place to find current event details. |
| Event Temple | Official feature pages connect lead pipeline, proposals, contracts, BEOs, invoicing, deposits/payments and production reporting. Its current site says BEOs stay in sync with the booking's latest version. | Changes made during sales must reach the operations handoff without staff maintaining a second, stale event record. |
| Perfect Venue | Official product description centralizes inquiries, proposals, contracts, payments and BEOs. Capterra displayed 4.8/5 across 85 reviews, last updated 9 Nov 2026. Recent reviewers praised a single rental/event hub and reduced email back-and-forth; some requested more flexible proposal policies, stronger invoice details and automatic deposit/final-payment receipts. | Fast quoting and centralization are valuable, but financial documents and event-specific exceptions must remain credible and adaptable. |
| HoneyBook | Official FAQs describe lead capture, project management, client communication, templates, invoices, proposals/contracts, questionnaires, online payments, reminders and automations. | Small businesses value a guided path from first inquiry to final payment and reusable templates rather than disconnected admin screens. |
| CaterZen | Capterra displayed 4.8/5 across 55 reviews, last updated 30 Sep 2026. Review feedback praised templates and ease of use, but also reported disabled controls, stale or disappearing menus after refresh, and occasional quote/order totals that needed reopening to correct. | Interface state and arithmetic accuracy are commercial trust requirements, not minor polish. |

### Evidence limits

Vendor feature pages describe intended capabilities, not independent proof of quality. Review platforms are self-selected samples; ratings are directional signals, not controlled benchmarks. The complaints above are concrete user reports, not evidence that every customer experiences the same defects.

## Architecture principles that translate to Zazu

### 1. One event/job record, multiple role-specific views

Keep customer, requirements, quote versions, accepted scope, purchasing, preparation, costs, invoices, payments, documents and activity linked to the same event/work identity. Sales, operations and finance may need different views, but must not maintain competing copies of the event's commercial truth.

**Zazu mapping:** the current event/work-centred model and Job Operational chain are aligned. Keep the event as the relationship anchor; do not introduce a second event record for quoting, production or reporting.

### 2. Versioned commercial changes with explicit operational impact

An updated requirement or proposal must not silently rewrite what the customer accepted. Preserve quote snapshots, identify stale drafts, make acceptance explicit and ensure downstream documents use the intended accepted version. Operational handoff must be traceable to that version.

**Zazu mapping:** versioned quote snapshots, stale-quote detection and revision review are already part of the codebase. The next proof is real operator use of a changed requirement through revision, re-acceptance and downstream documents—not another duplicate seeded test.

### 3. Financial truth must survive every handoff

Use exact decimal/cents arithmetic, explicit tax basis, idempotent financial mutations and an auditable relationship between quote, invoice, payments, purchasing receipts and incurred costs. Keep void/cancelled records and unincurred plans out of actual contribution. Distinguish gross invoiced cash obligations from net-of-output-tax event contribution.

**Zazu mapping:** the 16 Nov commercial reconciliation now distinguishes R11,500 invoice total from R10,000 net sales, subtracts R2,800 actual incurred costs and reports R7,200 indicative event contribution (72%). This label should remain indicative, not be represented as audited accounting profit or a complete tax return.

### 4. Surface the next operational risk, not only static KPIs

Useful event software answers: what is awaiting a quote, what changed, what is not purchased, what preparation is overdue, what is unpaid and what needs action before the event. A dashboard is strongest when it directs a user to a real record and a valid next action.

**Zazu mapping:** its attention/Helper and event chain should read from existing business records and respect role permissions. Avoid alerts that have no actionable route or duplicate the underlying state.

### 5. Templates reduce admin only when records remain editable

Reusable services, quote patterns and communication templates reduce repeated entry. Event-specific exceptions still need to be possible without breaking the default template or making a user leave the workflow.

**Zazu mapping:** keep reusable capability/catalogue data separate from the event's accepted price and scope snapshots. Do not let later catalogue edits rewrite historical quotes.

### 6. Reliability and usability are a single commercial concern

Recent CaterZen and Tripleseat feedback reinforces two risks: state that becomes stale after refresh and workflows whose complexity obscures simple work. A visually attractive screen is insufficient if totals change, buttons silently disable, or the next action is unclear.

**Zazu mapping:** preserve the owner-confirmed design system, validate the actual rendered PC/phone experience at the target installation, and keep arithmetic/state-transition regression coverage. Do not use repeated broad smoke tests as a substitute for verifying a specific changed surface.

### 7. Keep the architecture deployable and recoverable

For a first commercial release, a modular monolith with explicit business boundaries is a more economical fit than distributing one operator's workflows across services. Laravel's production guidance calls for debug mode off, deployment-time optimization/configuration, a health endpoint and deliberate handling of long-running workers. The selected deployment still needs durable database and private-file storage plus off-host recovery.

**Zazu mapping:** the existing SaaS-readiness architecture already sets a modular-monolith boundary and keeps local/hosted deployment modes separate. The new Windows candidate is a fresh local installation package; it is not a hosted SaaS deployment or an in-place updater.

### 8. Treat security and privacy as part of the product contract

OWASP ASVS 5.0 is a current verification reference. Relevant areas include session uniqueness and invalidation, documented file types/size limits and safe file delivery, and security-event logging that avoids unnecessary personal data. Security records should be useful for investigation without becoming a second store of customer secrets.

**Zazu mapping:** business isolation, server-side authorization, private attachments, audit logging, security headers and session controls are established foundations. POPIA operator inventory, executed operator terms where required, retention/disposal operations and incident procedure must still be signed off for the actual deployment.

## What this means for the V1 finish line

| V1 objective | Current repository position | Release evidence still needed |
|---|---|---|
| Connected commercial lifecycle | Quote acceptance, invoice/deposit/final payment, purchase receipt, incurred costs and event contribution are covered by populated automated reconciliation. | Reconcile one real business's records and confirm the displayed amounts agree with source documents. |
| Desktop/mobile usability | Shared theme, responsive layouts and browser acceptance exist. | Owner acceptance on the actual PC/browser and phone installation; viewport emulation is not device proof. |
| Local Windows delivery | A clean ZIP build, production dependencies, compiled assets, checksum and first-run installer are implemented. | Exact latest-head artifact must pass, then fresh-install and operator startup must be exercised on the intended Windows PC. |
| Recovery | Business-scoped export/import and installation backup/restore logic have automated coverage. | Run the operator recovery drill on an isolated copy of the intended installation, including private media. |
| Privacy operations | Security/privacy technical controls and draft procedures exist. | Complete the real provider/operator inventory and POPIA operational sign-off. |

Future enterprise integrations, advanced client portals, AI automation, multi-property management and high-scale distributed infrastructure are not automatic V1 blockers. Promote them only when a real customer workflow and commercial case require them.

## Sources

- Tripleseat features: https://tripleseat.com/features/
- Tripleseat reviews (Capterra, accessed 2026-11-16): https://www.capterra.com/p/118047/Tripleseat/reviews/
- Event Temple features: https://www.eventtemple.com/features
- Event Temple platform overview: https://www.eventtemple.com/
- Perfect Venue platform overview: https://www.perfectvenue.com/about-us
- Perfect Venue reviews (Capterra, accessed 2026-11-16): https://www.capterra.com/p/266878/Perfect-Venue/reviews/
- HoneyBook feature FAQ: https://www.honeybook.com/faqs
- CaterZen reviews (Capterra, accessed 2026-11-16): https://www.capterra.com/p/82293/Restaurant-Catering-System/reviews/
- Laravel 13 deployment guidance: https://laravel.com/framework/docs/13.x/deployment
- OWASP Application Security Verification Standard 5.0: https://github.com/OWASP/ASVS/
- OWASP ASVS 5.0 V7 Session Management: https://github.com/OWASP/ASVS/blob/master/5.0/en/0x16-V7-Session-Management.md
- OWASP ASVS 5.0 V16 Security Logging and Error Handling: https://github.com/OWASP/ASVS/blob/master/5.0/en/0x25-V16-Security-Logging-and-Error-Handling.md
- OWASP ASVS 5.0 V5 File Handling: https://github.com/OWASP/ASVS/blob/master/5.0/en/0x14-V5-File-Handling.md

## Commercial positioning and execution direction — 2026-11-19

**Decision status:** Adopted as commercial-readiness direction; does not expand or override the V1 scope freeze.

### Positioning

Lead with the outcome Zazu can credibly demonstrate:

> **Run your business. Manage every job. Know what you actually earn. Even offline.**

Zazu remains a reusable event and catering operations platform for catering, event equipment hire, sound/DJ, baking, decor, rentals, photography and related event-service businesses. Do not narrow the product to one trade without customer evidence. The wedge is the connected event/job workflow and reliable financial visibility under local-first operating conditions—not generic quoting or invoicing alone.

The central commercial proof is the existing chain:

**Event/Job → Quote → Acceptance → Invoice → Payment → Purchasing/Receiving → Incurred Costs → Event Contribution → Export**

Describe the final figure accurately as event contribution/indicative job profitability where appropriate. Do not market it as audited accounting profit, tax advice or a complete set of accounts.

### Customer communication

- Evaluate a direct **Send via WhatsApp** action using the existing signed public quote URL and a normal wa.me deep link. No paid WhatsApp Business API is required for this simple handoff.
- This is an evaluation item, not authorization to build a new messaging subsystem. First inspect the existing quote share actions, URL expiry/signature behavior, mobile handling and regression coverage. Only implement if a concrete customer workflow gap is found and the change stays within the V1 scope/owner authorization.
- Never expose private customer data or unsigned/internal URLs in the message payload.

### Trust and financial-document credibility

- Complete the existing POPIA operational gate: actual data/provider inventory, owner-approved retention and disposal, data-subject request handling, incident response, access responsibilities and working contact channel. Technical controls or draft documents alone do not equal operational sign-off.
- Verify invoice output against the applicable SARS tax-invoice requirements and the business's VAT status. Invoice numbering implementation is not proof of tax compliance. Obtain appropriate tax-adviser/legal review before making a compliance claim; do not hardcode VAT assumptions for non-VAT vendors.
- Publish only accurate statements about data ownership, export/recovery, offline limits, security and support. Existing business archive/export is a foundation, not proof that every installation-level recovery scenario is customer self-service.
- A public changelog and plain-language limitations page are useful launch assets, but remain subordinate to V1 workflow completion and are not new engineering release gates by themselves.

### Visual and demonstration standard

Prioritize the first commercial impressions: landing page, dashboard, quote creation/review, signed public quote page, and customer-facing PDF. Improve the existing Zazu visual system rather than replacing it speculatively. Judge rendered screens at real desktop and phone sizes; source inspection or generated mockups alone do not establish visual acceptance.

Maintain a realistic demonstration business with ZAR-valued customers, events, quotes, invoices, payments, supplier purchases, receipts and incurred costs. Use clearly fictional demo data, credible empty states and a complete event example that makes the financial story easy to understand. Do not seed demo records into a real customer installation.

### Execution sequence

1. Finish the existing V1 engineering and acceptance blockers; do not interrupt the scope-frozen finish sequence for optional marketing work.
2. Prove the populated commercial workflow with traceable source records and reconcile its displayed totals.
3. Review the actual signed quote, public quote page, PDF and sharing flow; decide whether WhatsApp deep-linking closes a demonstrated gap.
4. Complete POPIA operational sign-off and invoice/tax review before making trust or compliance claims.
5. Polish the existing first-impression surfaces and prepare a fictional demonstration business for prospect demonstrations.
6. Use early operator feedback to validate positioning and prioritize only observed workflow friction.

### Guardrails

- No new product tier, feature subsystem, trade-specific fork or broad redesign is authorized by this strategy.
- No claims of customer validation, market share, adoption statistics, tax compliance or complete offline parity without current, attributable evidence.
- Keep external research estimates distinct from verified Zazu capabilities and customer evidence.
- V1 scope freeze, current readiness register and explicit Founder authority remain controlling.
