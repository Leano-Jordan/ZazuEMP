# Zazu EMP — Commercial Operating Profile

**Status:** OWNER-CONFIRMED WORKING BASELINE — NOT A PUBLISHED OFFER OR LEGAL OPINION
**Record date:** 2026-11-10
**Current operator:** Isaac Junior Lehlogonolo Maluleka (individual / solo operator)
**Future business identity:** Rosscore Labs; not to be represented as incorporated until registration is complete.
**Public service area:** Pretoria North, Gauteng, South Africa (area only; not a registered-office or street-address claim).

## 1. Owner-confirmed decisions

- The product is operated by the owner as an individual / solo operator.
- The owner currently handles product operation, support, privacy requests, incidents and customer administration.
- The three customer-facing capability levels are **Basic, Intermediate and Professional**. They are one product with increasing operational capability, not separate applications.
- No prices, billing terms or numerical capacity limits are approved by the current Founder decision. Earlier R99 and four-tier pricing proposals are historical hypotheses and must not be published or implemented.
- The existing 30-day trial currently grants the highest capability ceiling, now labelled Professional, then falls back to Basic continuity unless a valid higher entitlement is installed.
- On subscription expiry or non-payment, the customer is **not locked out**. Basic Free continuity remains available. Expiry alone does not authorise deletion, withholding or destruction of customer data.
- Trial expiry never silently starts paid billing.
- Both hosted and local/offline deployment models are intended.
- Customer content remains the customer's content. Zazu receives only the limited rights needed to host, process, secure, support and provide the contracted service.
- The approved public-facing service-area wording is **Pretoria North, Gauteng, South Africa**. Do not publish the owner's residential street address or imply that this area is a registered office.
- Direct personal contact details remain outside this public repository until their exact public-facing use is approved.

## 2. Three-tier capability model

The three capability levels are Basic, Intermediate and Professional. A business-level entitlement sets the maximum capability level; each member keeps an independent presentation preference within that ceiling. See [ZAZU_THREE_TIER_CAPABILITY_MATRIX.md](ZAZU_THREE_TIER_CAPABILITY_MATRIX.md) for the current capability design.

### Basic — Free, ongoing
The permanent continuity tier. It must remain useful for day-to-day work and preserve access to existing customer-owned records after a trial or paid subscription ends. No active-event, user, storage or other numerical cap is approved for enforcement yet.

### Intermediate
The middle tier for businesses that need more connected operational control across jobs, resources, purchasing, costs, suppliers and finance. The price and exact included features/quotas are **not approved**. Do not publish a price or enforce a module restriction on the basis of this description alone.

### Professional
The highest capability level for complex operations, consolidated oversight, advanced analytics, resource/capacity management and deeper administration. These are intended capabilities and must be mapped to implemented evidence before enforcement.

### Commercial and engineering safeguards

- A new eligible workspace receives a 30-day Professional trial from its creation timestamp.
- At trial expiry, the effective tier becomes Basic continuity unless an active higher-level entitlement is installed.
- A valid Intermediate entitlement permits Basic and Intermediate capability levels; a valid Professional entitlement (internal identifier `advanced`) permits all three.
- An expired, revoked, not-yet-active or unsupported entitlement resolves to Basic and does not restart a trial.
- No silent paid conversion, workspace lockout, data deletion or withholding of customer-owned records because of expiry or non-payment.
- The current implementation enforces the **experience-level ceiling only**. It does not yet enforce a module-by-module paid-feature matrix, quotas, team-size limits or storage caps.
- Earlier suggestions of five active events, one owner, three users, 250 MB and 2 GB remain unapproved hypotheses. They must not be implemented until the owner approves the full matrix and downgrade behaviour.
- Do not advertise a module as a paid-tier entitlement until its access boundary and downgrade behaviour are implemented and verified.
- Keep role-based permissions and business data isolation independent from commercial tier checks.

## 3. Trial and commercialisation boundary

- Trial duration: **30 days**.
- The existing 30-day trial falls back to Basic continuity at expiry.
- Trial expiry must not silently start billing.
- Pricing, checkout, billing cadence, cancellation, payment methods and fees are outside the current capability-design scope and require a later explicit Founder decision.
- No storage, media, uptime or support commitment is approved as part of a tier at this stage.

## 4. Deployment and hosting recommendation

### Hosted pilot

Recommended first infrastructure shortlist: **xneelo Cloud, Johannesburg**. It offers local infrastructure, rand pricing, compute and separately billed volumes, snapshots, security groups and self-managed virtual machines. It currently advertises one Johannesburg region / availability zone, so this is a practical low-cost pilot choice, not a multi-zone high-availability design.

Indicative starting configuration from the published price list:
- 2 vCPU / 4 GB RAM instance: approximately R198/month.
- 10 GB premium boot volume: approximately R18/month.
- 50 GB premium NVMe data volume: approximately R90/month.
- Indicative server total: **about R306/month**, including VAT, before independent offsite backup, domain, email, monitoring or payment fees.

**Recommended initial cash budget: R450–R500/month.** This is a planning allowance: R306 server resources plus a provisional R100–R150 for an independent backup destination and small operational overhead. The backup/overhead allowance is not a quoted provider price and must be replaced with actual selected-vendor pricing before procurement. Domain registration is usually annual and should be budgeted separately if not included.

This is a starting pilot estimate, not a purchase or final production sizing decision. Reassess CPU, RAM, storage, database load, media growth and recovery objectives before onboarding paying customers. Upgrade to 4 vCPU / 8 GB RAM when measured usage justifies it.

### Storage and backup

- **Storage quotas are not approved for any tier.** Do not enforce a Basic, Intermediate or Professional quota, upload cap or downgrade restriction until the owner approves the complete three-tier matrix and its data-safe downgrade/export behaviour. Earlier 250 MB / 2 GB figures are proposals only, not product policy.
- Keep application database and private media storage separated from public web assets.
- Encrypt backups and restrict access to the owner/operator.
- Keep at least one backup copy outside the primary server/provider failure domain.
- Test restore regularly; snapshots alone are not a complete backup strategy.
- Monitor storage growth and set alerts before quotas are reached.
- Do not promise a recovery time or recovery point until it has been measured and operationally supported.

xneelo Cloud is self-managed. The owner remains responsible for server hardening, patching, application deployment, database administration, backups and recovery. Its published terms place backup/recovery responsibility on the customer.

### Local/offline deployments

Local installations must remain useful without a live connection. Hosted services must not become a hidden dependency for core local workflows. Local customers are responsible for their own device, power, network and local backup environment unless a separately agreed support/backup service is provided.

## 5. Privacy role allocation

- For personal information a customer enters about its own clients, the customer will ordinarily determine the purpose and means of processing and is expected to act as the responsible party.
- In the hosted model, the Zazu service operator will ordinarily process that information on the customer's documented instructions and should be contractually treated as an operator where POPIA applies.
- For local installations, the customer ordinarily controls the local environment. Rosscore only accesses data on an authorised support request or another agreed purpose.
- For account administration, subscription billing, fraud prevention and the provider's own legal obligations, the provider may separately be responsible party for limited data it processes for own purposes.
- Hosting, email, backup and other third-party providers must be inventoried and assessed as sub-operators before production personal information is sent to them.
- No claim of POPIA compliance, certification or Information Officer registration is made by this profile.

## 6. Owner-operated support and incident handling

The owner is the initial operational contact for support, privacy requests and security incidents. Use owner-approved business contact details in final customer-facing documents. Public service area: **Pretoria North, Gauteng, South Africa**.

Until a staffed support operation exists:
- support is owner-handled and subject to reasonable availability;
- no 24/7 support or guaranteed response-time SLA is promised;
- urgent security incidents receive priority handling;
- customer support access to hosted data must be limited, authorised, logged where feasible and ended when no longer needed.

## 7. Retention baseline — proposed policy, not blanket statutory periods

- Tax records: retain for at least the applicable period under the Tax Administration Act and SARS guidance; the common baseline is five years, subject to audit, objection, investigation and other exceptions.
- Company records: once a company exists, records covered by Companies Act section 24 generally have a seven-year retention requirement, or longer where another law requires it.
- Customer financial records: customers remain responsible for their own statutory recordkeeping. Zazu must support export and must not imply that the vendor's account-retention policy replaces the customer's legal duties.
- General account/profile data: proposed deletion or de-identification within 90 days after account closure, following a 30-day export/retrieval window, unless lawful retention, a dispute, security investigation or legal hold requires longer.
- Support and security logs: proposed 24-month baseline, with access restriction and data minimisation; shorten where the purpose no longer justifies retention, extend only where documented necessity exists.
- Backups: proposed rolling 30-day retention, followed by secure expiry, subject to legal holds and verified backup capability.
- Uploaded media: retain while needed for the customer's service, then delete according to the account closure process and backup expiry schedule, subject to lawful holds.
- No personal-information category receives an automatic seven-year retention period merely because accounting records may need seven years.

These are operational defaults for legal/accounting review. They must be implemented consistently across databases, private media, exports, logs and backups.

## 8. Decisions still required before public launch

- Map the Basic/Intermediate/Professional capability matrix to current source and tests. Obtain separate Founder approval for any numerical capacity limits or enforced restrictions.
- Confirm VAT treatment, payment channel, billing terms and refund/cancellation flow.
- Confirm final support hours and approved public contact details.
- Confirm final hosting provider, region, data-processing terms and independent backup destination.
- Confirm the public service-area wording remains Pretoria North; do not represent it as a registered office.
- Complete actual sub-operator inventory and written agreements where applicable.
- Implement/test export, account closure, retention and disposal workflows.
- Complete legal review and official Zazu brand clearance.

## 9. Evidence and references

- xneelo Cloud product, Johannesburg region, instance and storage pricing: https://xneelo.co.za/cloud/
- xneelo Cloud terms, including customer responsibility for backup/recovery: https://xneelo.co.za/legal/specific-terms-and-conditions/
- SARS recordkeeping guidance: https://www.sars.gov.za/client-segments/record-keeping/
- Companies Act 71 of 2008, official text: https://www.justice.gov.za/legislation/acts/2008-071.pdf

Last updated: 2026-11-10.


## Three-tier owner direction — 2026-11-13

The current product has three customer-facing capability levels: **Basic**, **Intermediate** and **Professional**. These map to the existing `basic`, `intermediate` and `advanced` internal identifiers. Basic is the permanent continuity level. The 30-day trial exposes Professional and falls back to Basic at expiry. The levels represent genuine increases in operational capability, not cosmetic presentation modes.

The capability allocation is recorded in `docs/ZAZU_THREE_TIER_CAPABILITY_MATRIX.md`. No prices, billing terms or numerical quotas are approved. Earlier R99 and multi-tier price suggestions are historical hypotheses only. Do not publish or enforce guessed feature limits. Existing customer data, active work and data export remain available regardless of tier or expiry.


## Founder decision update — 2026-11-26 (authoritative)

See [Founder Legal and Brand Decisions](FOUNDER_LEGAL_BRAND_DECISIONS_2026-11-26.md). This update supersedes any older assumption that Rosscore Labs is the current operator or owner.

- Current owner/operator: Founder personally, as an individual solo operator. Rosscore Labs is not registered and does not own Zazu yet. Any future transfer requires a separate written instrument after the company is properly established.
- The Founder currently works alone; no staff, co-founders or contractors are involved.
- Zazu is not launching commercially. Product stability is the priority. A controlled non-commercial test with close trusted members is acceptable when authorised; prefer synthetic data. Real personal information still triggers applicable duties.
- Intended data scope is business/staff account details, customer contact details, event/job details and notes, quotes/invoices/payment records, and documents/receipts/photos/attachments. Identity numbers, bank-account details, children's information and medical/sensitive information are not intended V1 data categories.
- No Zazu production hosting, cloud storage, analytics, payment, messaging, monitoring or external support provider has been provisioned/configured. Hosted and local/offline remain strategic deployment directions; actual first deployment and providers are undecided. xneelo remains an unselected shortlist only.
- Dedicated privacy mailbox approved in principle but not created; address/domain remains TBD. Existing `rosscorelabs@outlook.com` remains provisional contact pending a dedicated channel and monitoring test.
- The Founder has requested a retention proposal; the existing retention schedule remains provisional and unapproved pending legal/accounting review.
- Do not publish pricing, deployment, provider, support or compliance claims from this working profile as final launch terms.
