# Zazu EMP — Compliance & Privacy Engineering Baseline

**Status:** Engineering baseline, not legal advice  
**Context:** South African commercial software project  
**Last reviewed:** 2026-09-26

## Purpose

Zazu will handle customer, contact, event and business information. Compliance is therefore treated as a release requirement, not a last-minute document exercise.

The current implementation is **not declared legally compliant** by this document. Each relevant obligation must be verified before commercial release and again when the product, jurisdiction or processing changes.

## Current engineering rules

- Never commit passwords, API keys, tokens, private keys or other secrets.
- Never commit production customer records or production database exports.
- Minimise personal information collected for each workflow.
- Do not expose customer information in URLs, logs or error messages unnecessarily.
- Validate input server-side.
- Escape rendered user content through normal Laravel/Blade output handling.
- Keep business-data access server-side and enforce business isolation before production multi-business use.
- Keep audit/recovery requirements in scope as the product becomes operationally critical.
- Record third-party software and licence obligations in `THIRD_PARTY_NOTICES.md`.
- Keep ownership/provenance records in `IP_OWNERSHIP.md`.

## South African legal review gates

Where South African law is relevant, the release review must explicitly assess at least:

- Protection of Personal Information Act (POPIA)
- Promotion of Access to Information Act (PAIA)
- Electronic Communications and Transactions Act (ECTA)
- Consumer Protection Act (CPA)
- Cybercrimes Act
- tax/VAT obligations where Zazu generates or supports tax-related records
- copyright, trade-mark and other intellectual-property obligations
- sector-specific rules where a customer operates in a regulated sector

The exact obligations depend on Zazu's processing activities, contracts, customers, deployment model and jurisdictions. Do not treat this checklist as a legal conclusion.

## Privacy-by-design release questions

Before production use of customer data, verify:

1. What personal information is collected?
2. Why is each category collected?
3. Who can access it?
4. How is access restricted between businesses?
5. How long is it retained?
6. How can records be corrected or removed where required?
7. What happens after a security compromise?
8. What third parties receive or process the information?
9. Where is the information stored?
10. What notices, contracts or policies are required?

## Evidence rule

Every compliance claim must have one of:

- authoritative legal/regulatory source,
- documented project requirement,
- verified engineering control,
- or explicit legal review.

Do not label Zazu "POPIA compliant", "fully compliant" or equivalent merely because a checklist exists.

## Current project position

Authentication, roles, permissions and server-side business isolation are not yet complete. They remain production-critical before Zazu is used by multiple businesses or exposed to real customer data at scale.
