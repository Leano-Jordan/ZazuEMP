# Zazu EMP — Commercial Operating Profile

**Status:** OWNER-CONFIRMED WORKING BASELINE — NOT A PUBLISHED OFFER OR LEGAL OPINION
**Record date:** 2026-11-10
**Current operator:** Isaac Junior Lehlogonolo Maluleka (individual / solo operator)
**Future business identity:** Rosscore Labs; not to be represented as incorporated until registration is complete.

## 1. Owner-confirmed decisions

- The product is operated by Isaac Junior Lehlogonolo Maluleka as a solo operator.
- The owner currently handles product operation, support, privacy requests, incidents and customer administration.
- Initial target subscription: **R99 per business/workspace per month**.
- Proposed trial: **14 days**, with trial access to advanced features. At trial end, the workspace moves to the Basic tier unless a paid subscription is active.
- On subscription expiry or non-payment, the customer is **not locked out**. Basic functionality remains available and advanced features are restricted. Expiry alone does not authorise deletion, withholding or destruction of customer data.
- Both hosted and local/offline deployment models are intended.
- Customer content remains the customer's content. Zazu/Rosscore receives only the limited rights needed to host, process, secure, support and provide the contracted service.
- Contact details supplied by the owner are held in private business records and are deliberately not copied into this public repository.

## 2. Commercial model — release baseline

The R99 amount is the initial target price, not yet a fully costed public promise. Before publication, confirm whether it includes VAT (if applicable), payment-provider fees, number of users, storage quota, support scope and any optional add-ons.

Recommended initial offer structure:
- One business/workspace per subscription.
- Monthly billing, cancellable for a future billing period.
- A 14-day trial; no payment method should be required unless the actual payment flow and disclosures are approved.
- Trial expiry and paid-subscription expiry both downgrade to Basic, preserving access to customer data.
- Advanced features resume after successful renewal/payment.
- No unlimited storage, unlimited media uploads, guaranteed uptime or 24/7 support promise at the R99 entry price.
- Clearly disclose any storage quota, fair-use limits, maintenance windows and feature differences before checkout.

The exact Basic/Advanced feature matrix must match the implemented product. Do not advertise a feature as available in Basic or Advanced until verified against the release build.

## 3. Deployment and hosting recommendation

### Hosted pilot

Recommended first infrastructure shortlist: **xneelo Cloud, Johannesburg**. It offers local infrastructure, hourly rand pricing, compute and separately billed volumes, snapshots, security groups and self-managed virtual machines. It currently advertises one Johannesburg region / availability zone, so this is a practical low-cost pilot choice, not a multi-zone high-availability design.

Indicative starting configuration from the published price list:
- 2 vCPU / 4 GB RAM instance: approximately R198/month.
- 10 GB premium boot volume: approximately R18/month.
- 50 GB premium NVMe data volume: approximately R90/month.
- Indicative total: **about R306/month**, before any additional resources or changes in published prices.

This is a starting pilot estimate, not a purchase or final production sizing decision. Reassess CPU, RAM, storage, database load, media growth and recovery objectives before onboarding paying customers. Upgrade to 4 vCPU / 8 GB RAM when measured usage justifies it.

### Storage and backup

- Keep application database and private media storage separated from public web assets.
- Encrypt backups and restrict access to the owner/operator.
- Keep at least one backup copy outside the primary server/provider failure domain.
- Test restore regularly; snapshots alone are not a complete backup strategy.
- Set storage quotas and monitor growth from day one.
- Do not promise a recovery time or recovery point until it has been measured and operationally supported.

xneelo Cloud is self-managed. The owner remains responsible for server hardening, patching, application deployment, database administration, backups and recovery. Its published terms place backup/recovery responsibility on the customer.

### Local/offline deployments

Local installations must remain useful without a live connection. Hosted services must not become a hidden dependency for core local workflows. Local customers are responsible for their own device, power, network and local backup environment unless a separately agreed support/backup service is provided.

## 4. Privacy role allocation

- For personal information a customer enters about its own clients, the customer will ordinarily determine the purpose and means of processing and is expected to act as the responsible party.
- In the hosted model, the Zazu service operator will ordinarily process that information on the customer's documented instructions and should be contractually treated as an operator where POPIA applies.
- For local installations, the customer ordinarily controls the local environment. The provider should not access customer records unless the customer requests support or another agreed service requiring access.
- For account administration, subscription billing, fraud prevention and the provider's own legal obligations, the provider may separately determine limited purposes and must describe those purposes accurately.
- Hosting, email, backup and other third-party providers must be inventoried and assessed as sub-operators before production personal information is sent to them.
- No claim of POPIA compliance, certification or Information Officer registration is made by this profile.

## 5. Owner-operated support and incident handling

The owner is the initial operational contact for support, privacy requests and security incidents. Use the owner's approved business email and telephone number in the final customer-facing documents; do not publish residential details in source control.

Until a staffed support operation exists:
- support is owner-handled and subject to reasonable availability;
- no 24/7 support or guaranteed response-time SLA is promised;
- urgent security incidents receive priority handling;
- customer support access to hosted data must be limited, authorised, logged where feasible and ended when no longer needed.

## 6. Retention baseline — proposed policy, not blanket statutory periods

- Tax records: retain for at least the applicable period under the Tax Administration Act and SARS guidance; the common baseline is five years, subject to audit, objection, investigation and other exceptions.
- Company records: once a company exists, records covered by Companies Act section 24 generally have a seven-year retention requirement, or longer where another law requires it.
- Customer financial records: customers remain responsible for their own statutory recordkeeping. Zazu must support export and must not imply that the vendor's account-retention policy replaces the customer's legal duties.
- General account/profile data: proposed deletion or de-identification within 90 days after account closure, following a 30-day export/retrieval window, unless lawful retention, a dispute, security investigation or legal hold requires longer.
- Support and security logs: proposed 24-month baseline, with access restriction and data minimisation; shorten where the purpose no longer justifies retention, extend only where documented necessity exists.
- Backups: proposed rolling 30-day retention, followed by secure expiry, subject to legal holds and verified backup capability.
- Uploaded media: retain while needed for the customer's service, then delete according to the account closure process and backup expiry schedule, subject to lawful holds.
- No personal-information category receives an automatic seven-year retention period merely because accounting records may need seven years.

These are operational defaults for legal/accounting review. They must be implemented consistently across databases, private media, exports, logs and backups.

## 7. Decisions still required before public launch

- Confirm the exact trial length (14 days is the proposed default).
- Approve the Basic vs Advanced feature matrix.
- Confirm whether R99 is VAT-inclusive where VAT applies, and confirm billing/payment method.
- Set the included storage quota and overage/upgrade treatment.
- Confirm customer support hours and the customer-facing contact details.
- Confirm the final hosting provider, region, data-processing terms and backup destination.
- Confirm public business/service address; residential address is not to be published without explicit approval.
- Complete trademark/name clearance and final legal review.

## 8. Evidence and references

- xneelo Cloud product, Johannesburg region, instance and storage pricing: https://xneelo.co.za/cloud/
- xneelo Cloud terms, including customer responsibility for backup/recovery: https://xneelo.co.za/legal/specific-terms-and-conditions/
- SARS recordkeeping guidance: https://www.sars.gov.za/client-segments/record-keeping/
- Companies Act 71 of 2008, official text: https://www.justice.gov.za/legislation/acts/2008-071.pdf

Last updated: 2026-11-10.
