# Zazu EMP — Data-Subject Request Procedure

**Status:** OPERATIONAL DRAFT — COMPLETE WITH REAL CONTACTS BEFORE RELEASE  
**Operational privacy owner:** Isaac Junior Lehlogonolo Maluleka  
**Operating address:** [Operating address — see private records]
**Request channel:** rosscorelabs@outlook.com (provisional owner-confirmed contact; replace when a dedicated Zazu privacy channel is established)  
**Information Officer:** Isaac Junior Lehlogonolo Maluleka — formal registration/appointment status to be confirmed before publication

## Purpose

Provide one controlled process for receiving, authenticating, logging, assessing and responding to privacy/data-subject requests.

## Request types

The operating process must be able to route requests that concern:

- access to personal information;
- correction or updating of inaccurate information;
- deletion/erasure where applicable;
- objection or limitation requests where applicable;
- questions about how information is used;
- complaints or escalation concerning privacy handling.

The responsible party must determine which rights apply to the specific request.

## Intake

1. Record the date received, requester details, requested action and affected business/customer context.
2. Do not place unnecessary personal information into general support tickets or public issue trackers.
3. Verify the requester's identity using a proportionate method before disclosing protected information.
4. Identify the applicable Zazu customer/business and responsible-party relationship.
5. Acknowledge receipt through the approved channel.

## Request routes and scope

Use the route that matches the request; do not treat every request as a request to delete an account.

- **Objection to processing:** where the objection rights in POPIA section 11(3) apply, the 2025 amended Regulations provide for a request substantially similar to Form 1, free of charge and reasonably accessible. Their listed submission methods include hand, fax, post, email, SMS and WhatsApp.
- **Correction or deletion of personal information:** POPIA section 24 and regulation 3 cover correction/deletion where information is inaccurate, irrelevant, excessive, out of date, incomplete, misleading or unlawfully obtained.
- **Destruction/deletion of a record:** the section 24/regulation 3 route applies where the responsible party is no longer authorised to retain that record. A request does not automatically override a lawful retention duty, legal hold or another person's rights.
- **Access to records:** handle access requests under the applicable POPIA section 23 / PAIA process and the PAIA control document. Do not mistake POPIA Form 2 (correction/deletion) for a PAIA access request form.
- **Questions, complaints or other rights:** log the request and assess the applicable POPIA right and the responsible party's role before deciding how to respond.

For correction/deletion requests, Form 2 or a substantially similar form must be made free of charge and reasonably accessible. A telephone request must be electronically recorded and made available to the requester on request. The amended Regulations also require written notification of the action taken within the period described in regulation 3.6. Do not promise a blanket 30-day completion period for every request: confirm the applicable statutory procedure and timing for the particular request with the responsible party's legal adviser.

Official source: [Information Regulator, amended POPIA Regulations published 17 April 2025 (GG 52523, GoN 6126)](https://www.justice.gov.za/legislation/notices/2025/20250417-gg52523gon6126-POPIA-Regulations-Amendment.pdf). The consolidated text is available from the [Information Regulator](https://inforegulator.org.za/wp-content/uploads/2025/04/POPIA-2021-Regulations-FINAL-21-Jan-2025.pdf).

## Role routing

Before acting, identify who is the responsible party for the specific record:

- A Zazu customer will ordinarily decide why and how it processes information about its own customers, guests, staff and event participants.
- In a hosted deployment, the Zazu provider will ordinarily act as an operator for those customer records, subject to documented instructions and written operator terms where required.
- The Zazu provider may be a separate responsible party for limited account, billing, support and service-security information it processes for its own purposes.
- In a local/offline deployment, the customer ordinarily controls the local database, device, files and backups. The Zazu provider must not claim it can access or delete local records remotely unless that access actually exists and is authorised.

When Zazu receives a request concerning data held for a customer, promptly route it to the appropriate customer/responsible party through the agreed operator channel. Do not disclose or alter customer-managed records merely because a person emails the software provider.

## Assessment

For each request, determine:

- what information is within scope;
- whether the requester is entitled to the requested action;
- whether another person's information is mixed into the record;
- whether information must be retained for legal, contractual, accounting, security or evidentiary reasons;
- whether a request affects a business's own records rather than Zazu's direct responsibility.

Record the decision and reason.

## Fulfilment

Use the least-privileged workflow available.

Where the application does not yet provide a self-service function, the responsible party must use a controlled administrative process rather than claiming that Zazu has an automated export/deletion capability.

Corrections must preserve sufficient audit/history to explain material business changes.

Deletion must follow the approved retention/disposal schedule and must not silently destroy records that must lawfully or contractually remain available.

## Refusal or partial fulfilment

Where a request cannot be fully fulfilled, record:

- the scope affected;
- the reason;
- the information retained;
- the approving person/role;
- the response issued to the requester;
- any escalation or appeal route.

## Evidence

Use the blank [Privacy Operations Register Template](RELEASE_PRIVACY_OPERATIONS_REGISTER_TEMPLATE.md) for the minimum request metadata. Keep the populated working register and all supporting personal information in an access-restricted location outside GitHub.


For every completed request, retain a minimal evidence record showing:

- request identifier;
- received/completed dates;
- decision;
- responsible operator;
- action performed;
- exceptions or retained records;
- final response.

Do not store unnecessary copies of the requested personal information in the evidence log.

## Application implementation boundary

Current Zazu has business-scoped records, private media and audit/security foundations, but this repository does not currently provide a dedicated end-user data-subject request/export/deletion portal.

**Release implication:** this procedure is presently a manual/operational control, not an implemented self-service feature.

## Completion checklist

- [x] provisional owner-approved request channel recorded: rosscorelabs@outlook.com; access, monitoring and publication test still required;
- [ ] responsible party confirmed for each actual deployment and record class;
- [ ] Information Officer appointment/registration and any Deputy escalation confirmed before publication;
- [ ] identity-verification method agreed;
- [ ] request log location secured;
- [ ] response templates reviewed;
- [ ] legal response-time rules and the applicable POPIA/PAIA request route confirmed for the deployment;
