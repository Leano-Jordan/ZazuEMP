# Zazu EMP — Commercial Operating Profile

**Status:** OWNER-CONFIRMED WORKING BASELINE — NOT A PUBLISHED OFFER OR LEGAL OPINION
**Record date:** 2026-11-10
**Current operator:** Isaac Junior Lehlogonolo Maluleka (individual / solo operator)
**Future business identity:** Rosscore Labs; not to be represented as incorporated until registration is complete.
**Public service area:** Pretoria North, Gauteng, South Africa (area only; not a registered-office or street-address claim).

## 1. Owner-confirmed decisions

- The product is operated by the owner as an individual / solo operator.
- The owner currently handles product operation, support, privacy requests, incidents and customer administration.
- The three commercial tiers are **Basic (Free), Intermediate (paid) and Advanced (paid)**. Intermediate pricing and the final tier-by-tier feature matrix are not yet approved.
- The existing **R99 per business/workspace/month** amount remains a working Advanced price target, not a published offer or finalized billing contract.
- Free trial: **30 days**, with the Advanced experience available during the trial.
- At trial end, the workspace moves to Basic Free unless an active paid Intermediate or Advanced entitlement is installed.
- On subscription expiry or non-payment, the customer is **not locked out**. Basic Free continuity remains available. Expiry alone does not authorise deletion, withholding or destruction of customer data.
- Trial expiry never silently starts paid billing.
- Both hosted and local/offline deployment models are intended.
- Customer content remains the customer's content. Zazu receives only the limited rights needed to host, process, secure, support and provide the contracted service.
- The approved public-facing service-area wording is **Pretoria North, Gauteng, South Africa**. Do not publish the owner's residential street address or imply that this area is a registered office.
- Direct personal contact details remain outside this public repository until their exact public-facing use is approved.

## 2. Three-tier commercial model — feature matrix pending approval

The three experience levels are the three workspace payment tiers. A business-level entitlement sets the maximum selectable experience; each member keeps an individual presentation preference within that ceiling.

### Basic — Free, ongoing
The permanent continuity tier. It must remain useful for day-to-day work and preserve access to existing customer-owned records after a trial or paid subscription ends. No active-event, user, storage or other numerical cap is approved for enforcement yet.

### Intermediate — paid
The middle tier for businesses that need more connected operational control across jobs, resources, purchasing, costs, suppliers and finance. The price and exact included features/quotas are **not approved**. Do not publish a price or enforce a module restriction on the basis of this description alone.

### Advanced — paid
The highest tier for the fullest operational picture, detailed controls, deeper search and guidance. R99 per business/workspace/month remains a working price target, not a published offer. The 30-day Advanced trial uses this same tier ceiling.

### Commercial and engineering safeguards

- A new eligible workspace receives a 30-day Advanced trial from its creation timestamp.
- At trial expiry, the effective tier becomes Basic Free unless an active signed Intermediate or Advanced entitlement is installed.
- A valid Intermediate entitlement permits Basic and Intermediate experience levels; a valid Advanced entitlement permits all three.
- An expired, revoked, not-yet-active or unsupported entitlement resolves to Basic and does not restart a trial.
- No silent paid conversion, workspace lockout, data deletion or withholding of customer-owned records because of expiry or non-payment.
- The current implementation enforces the **experience-level ceiling only**. It does not yet enforce a module-by-module paid-feature matrix, quotas, team-size limits or storage caps.
- Earlier suggestions of five active events, one owner, three users, 250 MB and 2 GB remain unapproved hypotheses. They must not be implemented until the owner approves the full matrix and downgrade behaviour.
- Do not advertise a module as a paid-tier entitlement until its access boundary and downgrade behaviour are implemented and verified.
- Keep role-based permissions and business data isolation independent from commercial tier checks.

## 3. Trial and billing recommendation

- Trial duration: **30 days**.
- Trial should not require a payment method unless the checkout flow clearly explains the billing transition and obtains valid consent.
- Trial expiry automatically moves the workspace to Basic Free; it must not silently start a paid subscription.
- Access to the purchased tier resumes after successful payment/renewal.
- Recommended billing: monthly in advance, cancellable before the next billing date.
- State whether R99 includes VAT where applicable, payment method, billing date, cancellation process and any payment fees before checkout.
- No unlimited storage, unlimited media uploads, guaranteed uptime or 24/7 support promise at R99.

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

- **Storage quotas are not approved for any tier.** Do not enforce a Basic, Intermediate or Advanced quota, upload cap or downgrade restriction until the owner approves the complete three-tier matrix and its data-safe downgrade/export behaviour. Earlier 250 MB / 2 GB figures are proposals only, not product policy.
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

- Approve the complete Basic/Intermediate/Advanced feature matrix, including any event, team or storage limits, before implementing or publishing those restrictions. Earlier numeric proposals remain unapproved.
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

The commercial experience has three canonical tiers: **Basic**, **Intermediate** and **Advanced**. These map directly to the existing `basic`, `intermediate` and `advanced` experience-level identifiers. Basic is the permanent free continuity tier. The 30-day trial exposes Advanced and falls back to Basic Free at expiry. Intermediate is a genuine middle commercial tier, not merely a cosmetic preference.

The exact Intermediate/Advanced feature allocation, quotas and public prices are not yet approved. The earlier two-tier packaging and suggestion to add a separate Team tier are superseded by this three-tier direction. Do not publish or enforce guessed feature limits. The confirmed R99/month figure remains an initial paid-subscription target only; it is not assigned to a specific tier by this note. Existing customer data and data export remain available regardless of tier or expiry.
