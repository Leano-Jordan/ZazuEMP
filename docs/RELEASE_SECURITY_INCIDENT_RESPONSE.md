# Zazu EMP — Security Incident Response

**Status:** OPERATIONAL DRAFT — REAL CONTACTS REQUIRED BEFORE RELEASE  
**Incident owner:** Isaac Junior Lehlogonolo Maluleka  
**Operating address:** [Operating address — see private records]
**Information Officer:** Isaac Junior Lehlogonolo Maluleka — formal registration/appointment status to be confirmed before publication  
**Deputy / escalation:** Not applicable to the current solo-operator model; external legal/regulatory support to be engaged when required  
**Regulatory contact process:** `[APPROVED CHANNEL TO BE CONFIRMED BEFORE PUBLICATION]`

## Purpose

Control suspected or confirmed unauthorised access, disclosure, loss, corruption, malicious modification, credential compromise or private-media exposure.

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

Determine:

- what happened;
- when it started and ended;
- which accounts/businesses were affected;
- which record classes were involved;
- whether data was viewed, changed, downloaded, deleted or exposed;
- whether a backup/recovery operation is required;
- whether legal/regulatory notification assessment is triggered.

Do not edit or delete incident evidence to make the system appear cleaner.

## Notification assessment

Where POPIA or another applicable law requires notification, the responsible party must use the approved legal/regulatory process and act within the applicable timing requirements.

For operator relationships, the operator must promptly notify the responsible party according to the contract.

This document intentionally does not invent statutory deadlines or legal conclusions.

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
