# Zazu EMP — Security Incident Response

**Status:** OPERATIONAL DRAFT — REAL CONTACTS REQUIRED BEFORE RELEASE  
**Incident owner:** Isaac Junior Lehlogonolo Maluleka  
**Initial customer incident-reporting channel:** rosscorelabs@outlook.com (provisional; this does not replace the Information Regulator's prescribed reporting process)
**Operating address:** [Operating address — see private records]
**Information Officer:** Isaac Junior Lehlogonolo Maluleka — formal registration/appointment status to be confirmed before publication  
**Deputy / escalation:** Not applicable to the current solo-operator model; external legal/regulatory support to be engaged when required  
**Regulatory contact process:** Information Regulator eServices Security Compromise portal — https://eservices.inforegulator.org.za/compromises/default.aspx

## Purpose

Control suspected or confirmed unauthorised access, disclosure, loss, corruption, malicious modification, credential compromise or private-media exposure.

**Role boundary:** do not describe the named solo operator as a formally registered Information Officer until the appointment and registration status have been verified. The internal incident owner is not, by itself, proof of regulatory registration. The current provisional customer-reporting email is rosscorelabs@outlook.com; it must be monitored and tested. It is not the Regulator reporting channel.

## Severity

**S1 — Critical:** credible exposure of sensitive personal information, broad business isolation failure, destructive compromise, active unauthorised access or material integrity compromise.

**S2 — High:** confirmed business-scoped security compromise or significant private-data exposure with contained scope.

**S3 — Medium:** suspicious or contained security event requiring investigation but no confirmed material disclosure.

**S4 — Low:** non-security operational anomaly with no evidence of compromise.

## Immediate sequence

1. Create a unique incident identifier.
2. Record time detected, reporter, affected business/workspace and observed symptoms.
3. Preserve relevant logs/evidence without unnecessarily copying personal information.
4. Contain the suspected access path.
5. Protect credentials/tokens that may be compromised.
6. Determine affected systems, records, media and businesses.
7. Preserve the state needed for forensics and recovery.
8. Restore normal operation only after the risk is understood and containment is verified.

## Zazu technical evidence

Current engineering contains request/error identification, incident recording, structured error handling, authentication/session protections, business-scoped authorization, private media boundaries and an authoritative audit-log surface.

The incident response process must treat these as evidence sources, not as substitutes for the organisational response itself.

Relevant implementation areas include:

- `app/Support/ZazuIncidentRecorder.php`;
- `bootstrap/app.php` exception reporting;
- `app/Models/AuditLog.php`;
- `app/Support/Audit.php`;
- business-context and authorization middleware;
- private media controllers/routes.

## Lost or stolen trusted phone

A paired phone contains a business-scoped offline workspace. Treat loss, theft or reassignment of that phone as a potential business-data exposure, even if the owner can still sign in to Zazu elsewhere.

### Immediate owner containment

1. From another trusted device, sign in to the affected business as its owner.
2. Open **Settings → Trusted phone access**.
3. Identify the missing phone using its device name, type and last-seen time. If identity is uncertain, stop and confirm the correct device before revoking.
4. Select **Revoke access** and confirm.
5. Verify the device is shown as revoked. The action invalidates its sync credential and pairing metadata; the server audit log records the action 'sync.device.revoked'.
6. Record the incident time, affected business, device details, last-seen time and revocation result in the incident record. Assess whether personal information may have been exposed and follow the notification-assessment process below.

If the owner cannot access another trusted session, use the organisation's approved hosting/operator escalation channel. Do not invent or rely on an unapproved contact route.

### Important limitation

**Revocation is not remote wipe.** It blocks the phone from making further authenticated sync requests, but it does not erase business records, attachments, cached pages or other data already downloaded to the phone. Zazu currently has no verified remote-wipe capability. Device-level screen lock, encryption and physical custody remain part of the trust boundary.

Queued changes that exist only on the missing phone will not be uploaded after revocation unless that device is authorised and paired again. Do not promise that those changes can be recovered from the server.

If the phone is recovered, preserve its browser storage until the owner decides whether locally queued work must be reconciled. Do not clear site data or uninstall the PWA before that decision. Re-pair only after the device is back under business control and the owner has authorised its return to service.

## Investigation

Use the blank [Privacy Operations Register Template](RELEASE_PRIVACY_OPERATIONS_REGISTER_TEMPLATE.md) to record incident metadata and protected evidence references. Keep completed incident records and all supporting personal information in a restricted location outside GitHub.


Determine:

- what happened;
- when it started and ended;
- which accounts/businesses were affected;
- which record classes were involved;
- whether data was viewed, changed, downloaded, deleted or exposed;
- whether a backup/recovery operation is required;
- whether legal/regulatory notification assessment is triggered.

Do not edit or delete incident evidence to make the system appear cleaner.

## POPIA section 22 notification decision

Where POPIA applies, do not use the S1–S4 severity rating as a notification threshold. Severity helps prioritise containment; it does not decide whether a reportable security compromise is notified.

The statutory trigger is reasonable grounds to believe that a data subject's personal information has been accessed or acquired by an unauthorised person. Once that trigger is met:

1. The responsible party must notify the Information Regulator and the affected data subject(s), unless a data subject's identity cannot be established.
2. Notify as soon as reasonably possible after discovery. Do not wait for the investigation to be complete or for a final impact/risk score. Submit the information available and update the notification as further facts are established.
3. Notify affected data subjects in writing using an allowed channel and give enough information for them to take protective measures.
4. A delay in notifying data subjects is permitted only where the Regulator or a public body responsible for preventing, detecting or investigating offences determines that notification will impede a criminal investigation. Record the direction and reason.
5. An operator that has reasonable grounds to believe personal information was accessed or acquired by an unauthorised person must notify the responsible party immediately. The responsible party retains the regulator/data-subject notification duty.

**No low-risk carve-out:** the Information Regulator's published fact sheet states that POPIA has no risk threshold for reporting security compromises; reportable compromises must be reported irrespective of the assessed level of risk. Do not close an incident as S3/S4 to avoid notification. If the facts are uncertain, escalate promptly to the Information Officer and obtain legal advice without delaying required notification.

**Reporting channel:** from 1 April 2025, the Regulator requires security-compromise reports to be submitted through its eServices portal rather than by email:
https://eservices.inforegulator.org.za/compromises/default.aspx

The incident record must capture at least: incident identifier; date/time of compromise if known; discovery date/time; affected systems and responsible-party/operator roles; categories of personal information; estimated/confirmed number of affected data subjects; unauthorised access/acquisition facts; potential consequences; containment actions; notification decision and rationale; date/reference of the Regulator submission; data-subject notification method/date; any permitted delay direction; and subsequent updates. Minimise personal information in the incident record and restrict access.

References:
- POPIA, sections 19–22: https://www.justice.gov.za/legislation/acts/2013-004.pdf
- Information Regulator, Fact Sheet: Handling of Security Compromises: https://inforegulator.org.za/2025/08/19/fact-sheet-handling-of-security-compromises/
- Information Regulator, 7 April 2025 statement on mandatory eServices reporting: https://inforegulator.org.za/wp-content/uploads/2025/04/MEDIA-STATEMENT-INVITATION-TO-REPORT-SECURITY-COMPROMISES-THROUGH-THE-eSERVICES-PORTAL-.pdf

This procedure is operational guidance, not a substitute for deployment-specific legal advice or the responsible party's own assessment of whether POPIA applies.

## Recovery

Use the approved backup/restore runbook. After recovery:

- verify application integrity;
- verify migration state;
- verify private-media availability and business isolation;
- rotate credentials where necessary;
- review audit logs;
- record residual risk and follow-up actions.

## Closure

An incident may be closed only when:

- containment is verified;
- affected scope is understood as far as reasonably possible;
- required notifications/escalations are completed;
- recovery is verified;
- corrective actions are assigned;
- evidence is preserved according to the retention policy.

## Release completion

- [ ] named incident owner;
- [ ] Information Officer / deputy named;
- [ ] real escalation channels tested;
- [ ] incident record location secured;
- [ ] backup/restore procedure linked and tested;
- [ ] notification assessment reviewed legally where required.


## Founder decision update — 2026-11-26

The Founder is the sole current owner/operator and has not registered an Information Officer. The Founder is the proposed IO for the current individual operation, subject to confirmation of the correct registration route and completion of registration. No Deputy Information Officer is currently appointed. The dedicated privacy mailbox is approved in principle but not yet created; `rosscorelabs@outlook.com` remains the provisional incident-reporting contact and must be monitored/tested before publication.

Do not represent Rosscore Labs as incorporated or as the current operator. No production hosting or external incident/support provider has been provisioned for Zazu. A private non-commercial test is not a commercial launch, but any real personal-information processing must still be handled lawfully. The Information Regulator notification route remains separate from the customer contact mailbox.

See [FOUNDER_LEGAL_BRAND_DECISIONS_2026-11-26.md](FOUNDER_LEGAL_BRAND_DECISIONS_2026-11-26.md).
