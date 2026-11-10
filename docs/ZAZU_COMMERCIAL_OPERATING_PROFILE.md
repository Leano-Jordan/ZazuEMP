# Zazu EMP — Commercial Operating Profile

**Status:** OWNER-CONFIRMED WORKING BASELINE — NOT A PUBLISHED OFFER OR LEGAL OPINION
**Record date:** 2026-11-10
**Current operator:** Isaac Junior Lehlogonolo Maluleka (individual / solo operator)
**Future business identity:** Rosscore Labs; not to be represented as incorporated until registration is complete.
**Public service area:** Pretoria North, Gauteng, South Africa (area only; not a registered-office or street-address claim).

## 1. Owner-confirmed decisions

- The product is operated by Isaac Junior Lehlogonolo Maluleka as a solo operator.
- The owner currently handles product operation, support, privacy requests, incidents and customer administration.
- Initial paid subscription target: **R99 per business/workspace per month**.
- Free trial: **30 days**, with access to the trial-enabled advanced feature set.
- At trial end, the workspace moves to the permanent Basic Free tier unless a paid Advanced subscription is active.
- On subscription expiry or non-payment, the customer is **not locked out**. Basic functionality remains available and paid/advanced features are restricted. Expiry alone does not authorise deletion, withholding or destruction of customer data.
- Both hosted and local/offline deployment models are intended.
- Customer content remains the customer's content. Zazu/Rosscore receives only the limited rights needed to host, process, secure, support and provide the contracted service.
- The approved public-facing service-area wording is **Pretoria North, Gauteng, South Africa**. Do not publish the owner's residential street address or imply that this area is a registered office.
- Direct personal contact details remain outside this public repository until their exact public-facing use is approved.

## 2. Recommended initial commercial tiers — PROPOSAL TO VERIFY

The following is the recommended product packaging, not a claim that every entitlement is already enforced in the application. The release feature matrix must be checked against routes, permissions, limits and actual hosted behaviour before sale.

### Basic — Free, ongoing
- One business/workspace.
- One owner account.
- Core customer and event/job records.
- Basic calendar and event status tracking.
- Create and view a limited number of active events (recommended initial cap: 5).
- Basic quote creation and customer-facing document export where already supported.
- Manual/local data export where supported.
- No paid automation, advanced reporting, team controls or hosted media allowance beyond a small operational cap.

### Advanced — R99 per business/workspace/month
- Everything in Basic.
- Higher or no artificial active-event cap, subject to fair use.
- Full operational workflow across quotes, purchasing, inventory/assets, invoices, documents and reporting where those modules are enabled in the release.
- Additional staff accounts and role-based collaboration (recommended initial allowance: up to 3 users total).
- Larger hosted storage allowance (recommended initial allowance: 2 GB per workspace).
- Advanced workflow and productivity features that are implemented, tested and explicitly listed at checkout.

### Packaging safeguards
- Do not remove or restrict access to customer-owned records solely because a trial or paid subscription ends.
- Basic must remain useful for data access and simple ongoing operations; it must not be a dead-end viewer.
- Do not advertise a module as included in either tier until its actual entitlement and downgrade behaviour are verified.
- Keep the R99 tier simple. Add a higher Team tier only after real demand for more users, storage or automation is demonstrated.
- Revisit the 5-active-event, 3-user and 2-GB suggestions after pilot usage; they are proposed launch limits, not owner-approved final limits.

## 3. Trial and billing recommendation

- Trial duration: **30 days**.
- Trial should not require a payment method unless the checkout flow clearly explains the billing transition and obtains valid consent.
- Trial expiry automatically moves the workspace to Basic Free; it must not silently start a paid subscription.
- Advanced access resumes after successful payment/renewal.
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

- Recommended initial hosted quota: **2 GB per paid workspace** and **250 MB on Basic Free**, with uploads capped and visible usage indicators.
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

- Verify the Basic/Advanced matrix, active-event cap, user allowance and storage quotas against the actual release and licence enforcement.
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
