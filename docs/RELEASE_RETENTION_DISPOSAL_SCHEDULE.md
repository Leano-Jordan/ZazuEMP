# Zazu EMP — Retention and Secure-Disposal Schedule

**Status:** PROVISIONAL OPERATING POLICY — LEGAL/ACCOUNTING REVIEW REQUIRED
**Policy owner:** Isaac Junior Lehlogonolo Maluleka, solo operator
**Review cycle:** At least annually and whenever deployment, data categories, legal obligations or operators materially change

> This schedule distinguishes statutory recordkeeping duties from proposed product/service retention defaults. It is not a blanket legal retention rule for every customer record.

## 1. Core rules

Retention is based on purpose and legal obligation, not merely on whether a database row is soft-deleted.

For every record class, document:
1. purpose and lawful basis/condition;
2. retention trigger;
3. applicable statutory, contractual or operational period;
4. disposal/de-identification action;
5. accountable owner;
6. exceptions such as legal hold, dispute, fraud/security investigation or accounting obligations.

Apply data minimisation. Do not retain personal information indefinitely simply because storage is available.

## 2. South African statutory recordkeeping context

- **Tax records:** SARS guidance describes a common five-year retention period, with important exceptions for outstanding returns, audits, investigations, objections and appeals. The period and trigger depend on the taxpayer's circumstances and applicable tax law. See [SARS recordkeeping guidance](https://www.sars.gov.za/client-segments/record-keeping/) and [Tax Administration Act 28 of 2011](https://www.gov.za/documents/tax-administration-act).
- **Company records:** Companies Act 71 of 2008 section 24 generally requires company records covered by that section to be kept for seven years, or longer where another applicable law requires it. See the [official Act](https://www.justice.gov.za/legislation/acts/2008-071.pdf) and [CIPC company records guidance](https://www.cipc.co.za/?page_id=15683).
- These periods do **not** mean every customer contact, event note, photo or account profile must automatically be retained for five or seven years.
- Each Zazu customer remains responsible for its own statutory business recordkeeping. Zazu must provide a practical export route and must not imply that the vendor's retention policy replaces the customer's duties.

## 3. Record-class schedule

| Record class | Proposed retention / trigger | Disposal action | Notes |
|---|---|---|---|
| Account identity and access metadata | While account is active; proposed deletion/de-identification within 90 days after closure, after a 30-day export window | Delete or de-identify, except minimum records needed for lawful claims/security | Operational default; not a statutory period |
| Business/workspace configuration | While active; closure + 30-day export window, then deletion within 90 days where lawful | Delete/de-identify | Customer export must be offered |
| Customers and contacts | Customer-defined purpose; delete/de-identify when no longer needed, subject to the customer's legal duties | Customer-controlled deletion/export | Do not impose a blanket 7-year period |
| Events/jobs and operational notes | While needed for business operations; proposed closure + 90-day service retention unless customer exports/requests earlier deletion | Delete/de-identify | Legal holds and customer obligations override |
| Quotes, invoices, payments and tax-relevant records | Customer must retain for applicable tax/accounting periods; commonly five years for tax records and seven years for company records covered by Companies Act s24 | Controlled archive/export and disposal only after applicable obligations expire | Exact trigger depends on record and customer circumstances |
| Attachments and media | While needed for service; proposed deletion after account closure/export window, with backup expiry thereafter | Securely delete from private storage and expire backup copies | Legal holds and accounting evidence may require retention |
| Audit/security logs | Proposed 24 months from creation, reviewed for necessity and minimised | Restricted archive then secure disposal | Extend only for documented investigation/legal hold |
| Support records | Proposed 24 months after case closure, unless a dispute/legal obligation requires longer | Delete or minimise | Avoid copying customer data into tickets unnecessarily |
| Backups | Proposed rolling 30-day retention | Secure expiry/deletion | Offsite copy required; backup policy must match actual infrastructure |
| Development/test data | Only as long as needed for testing | Delete or regenerate with synthetic data | Never use live customer personal data as ordinary test fixtures |

## 4. Account closure and deletion sequence

1. Confirm the authorised customer request and account authority.
2. Provide an export opportunity and allow the proposed 30-day retrieval window, unless the customer requests earlier action and no legal hold applies.
3. Identify records that must be retained for tax, accounting, security, dispute or other lawful purposes.
4. Delete or de-identify the remaining account data and associated private media.
5. Allow backup copies to expire under the 30-day backup cycle; document any delayed deletion.
6. Retain only the minimum evidence needed to record completion and lawful exceptions.
7. Confirm completion to the requester.

A subscription expiry alone is not an account-closure or deletion instruction. Under the Zazu commercial model, expired subscriptions downgrade to Basic and customer data remains accessible.

## 5. Secure-disposal rules

- Delete database records and dependent private files together where applicable.
- Remove temporary exports and generated archives when their purpose ends.
- Expire backup copies according to the approved backup schedule.
- Restrict retained financial, legal-hold and incident records to authorised access.
- Use de-identification where retained aggregate information remains useful and re-identification risk is appropriately controlled.
- Record legal holds and do not dispose of affected records until the hold is released.

## 6. Review triggers

Reassess when hosted and local deployment models change; a new hosting, email, analytics, backup or support operator is introduced; new personal-information categories are added; messaging/WhatsApp integration is introduced; event photography/media storage expands; tax/accounting requirements change; or a legal hold/security incident requires exceptional preservation.

## 7. Implementation and release completion

Current repository evidence indicates there is no general-purpose retention scheduler that automatically enforces every period in this schedule. The policy is therefore not fully implemented merely because it is documented.

- [ ] export and account-closure process tested;
- [ ] deletion of database records and private media tested;
- [ ] backup expiry configured and verified;
- [ ] audit/support log retention configured;
- [ ] statutory exceptions and legal holds documented;
- [ ] customer-facing policy reviewed and published;
- [ ] legal/accounting review completed.

## 8. References

- SARS: https://www.sars.gov.za/client-segments/record-keeping/
- Tax Administration Act: https://www.gov.za/documents/tax-administration-act
- Companies Act 71 of 2008: https://www.justice.gov.za/legislation/acts/2008-071.pdf
- CIPC company records: https://www.cipc.co.za/?page_id=15683

Last reviewed: 2026-11-10.
