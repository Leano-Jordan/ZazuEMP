# Zazu EMP — Release Documentation vs Implementation Audit

**Director audit date:** 2026-10-04  
**Audit owner:** Isaac Junior Lehlogonolo Maluleka, solo developer / solo business owner  
**Operating address:** [Operating address — see private records]
**Repository:** `Leano-Jordan/ZazuEMP`  
**Branch:** `main`

## Purpose

Audit the new release-documentation set against the repository's actual implementation. A document is not treated as implemented merely because the policy text exists.

### Status meanings

- **IMPLEMENTED** — repository contains the underlying technical capability.
- **PARTIAL / MANUAL** — some technical controls exist, but the release requirement depends on an operational procedure or human action.
- **DOCUMENT-ONLY** — no dedicated application capability was found.
- **PENDING EXTERNAL** — requires real commercial/legal/vendor/device evidence outside the repository.
- **VERIFIED** — supported by current or previously recorded runtime/CI evidence.

## Audit matrix

| Release artefact | Repository evidence | Disposition |
|---|---|---|
| Privacy Notice | Customer/contact/event/media data classes are represented in models/workflows; privacy engineering baseline exists. No dedicated published privacy-notice route/page was found in the application. | **PARTIAL / MANUAL** — final notice remains an operational/legal artefact. |
| Data-subject Request Procedure | No dedicated `deleteAccount`, `exportData`, privacy-request portal or equivalent route was found. Normal record deletion/removal is business-operation functionality, not a general data-subject rights workflow. | **MANUAL CONTROL ONLY** — no self-service claim. |
| Retention & Disposal Schedule | Soft-delete lifecycle exists; private media and backups are controlled; no dedicated retention scheduler/policy enforcement was found. | **PARTIAL / MANUAL** — policy must be completed and operated outside the normal CRUD lifecycle. |
| Security Incident Response | `ZazuIncidentRecorder`, exception reporting in `bootstrap/app.php`, authoritative `AuditLog`, business-context/authorization boundaries and private media routes exist. | **IMPLEMENTED TECH FOUNDATION** — organisational notification/escalation remains procedural. |
| Operator/Data-Processing Control | Current deployment code does not evidence a dedicated operator registry/contract manager. External providers must therefore be tracked administratively. | **PENDING EXTERNAL / MANUAL**. |
| Customer Terms | No dedicated Terms-of-Service publication surface was found. | **DOCUMENT-ONLY / EXTERNAL LEGAL REVIEW**. |
| PAIA Operational Control | No dedicated PAIA request portal was found. Application authorization is not a substitute for records-access procedure. | **MANUAL CONTROL / LEGAL REVIEW**. |
| Media Rights Register | Landing imagery is vendored locally under `public/images/landing/stock/`; private application media is served through authorized routes. Final asset ownership/licensing remains release evidence. | **PARTIAL** — final rights/provenance sign-off external. |
| Deployment Runbook | Laravel migrations, locked dependencies, Vite build and clean-install/test paths exist; current release still needs a release-recorded deployment smoke on the selected artifact. | **IMPLEMENTED / VERIFICATION-BOUND**. |
| Backup/Restore Runbook | `zazu:backup` and `zazu:restore` exist; restore stages/validates data and private storage. Populated upgrade/rollback CI evidence is recorded in Director state. | **IMPLEMENTED / VERIFIED at recorded CI level**; final customer-environment drill remains required. |
| Upgrade/Rollback Runbook | Migration path and recovery process exist; populated upgrade/restore sequence was exercised by recorded GitHub Actions run `37202719119`. | **IMPLEMENTED / VERIFIED at recorded CI level**; final release artifact still needs sign-off. |

## Direct implementation findings

### Privacy

The codebase has meaningful privacy/security engineering: business isolation, authenticated private media access, session/authentication controls, roles/permissions, audit logging and incident recording. This supports the technical portion of the documentation but does not create the legal notice automatically.

### Data-subject requests

The repository contains ordinary business record removal actions and soft-delete lifecycle handling. Those controls must not be presented as a complete POPIA data-subject request implementation. A controlled manual procedure is currently the honest boundary.

### Retention

Soft deletion preserves history and is intentionally not treated as a permanent retention policy. The final schedule must therefore be an operational control, with disposal actions performed under the approved schedule.

### Incident response

The technical evidence is stronger than the documentation gap: incident recording and request/error identity exist, but contacts, escalation authority and the actual organisational notification process are not stored as application configuration.

### External contractual controls

Nothing in the current repository should be treated as proof that an operator agreement, customer contract, PAIA manual or privacy notice has been legally executed.

## Release decision

The documentation set closes the **documentation-design gap** and exposes the actual implementation boundary.

It does **not** close the legal/privacy gate until:

- real responsible-party/contact information is supplied;
- legal/accounting review is completed where required;
- the manual processes are actually adopted;
- final production documents are published through the chosen commercial channel.

This is a release-readiness control record, not legal advice.


## Director implementation correction notes — 2026-10-04

### Wallpaper

Previous audit treated wallpaper as implemented because storage, route and body-variable plumbing existed. A deeper cascade review found that later global `body` background declarations in the same visual stylesheet overrode the wallpaper rule.

**Corrective status:** source fixed; regression test added. Rendered device verification remains part of final UI acceptance.

### New-job customer creation

Previous audit correctly identified normal customer creation as implemented but did not treat the missing in-context creation path as a UX gap.

**Corrective status:** the new-job flow now provides an inline/secondary customer creation dialog. The existing job form remains authoritative; the new customer is returned into the existing selector and selected automatically.



## Director commercial/legal refresh — 2026-11-10

### Owner-confirmed commercial baseline

The owner confirmed solo operation by Isaac Junior Lehlogonolo Maluleka, target pricing of R99 per business/workspace/month, a proposed 14-day advanced-feature trial, downgrade to Basic after expiry without account lockout or automatic data deletion, and intended hosted plus local/offline deployments. Customer content remains customer-owned. The owner handles support, privacy requests and incidents initially.

The owner supplied direct contact details and a residential street address. Those values are intentionally not copied into this public repository. Final public documents must use only contact fields and a service address expressly approved for publication.

### Documentation changes

- Added ZAZU_COMMERCIAL_OPERATING_PROFILE.md to record the operating model, pricing baseline, privacy role allocation, hosting shortlist and unresolved launch decisions.
- Updated customer terms to describe the R99 target, proposed 14-day trial, Basic downgrade, no lockout, customer content ownership, hosted/local privacy boundaries and support limitations.
- Updated privacy notice draft to distinguish hosted operator processing from local customer-controlled deployments.
- Replaced blank retention placeholders with a provisional schedule separating tax and company statutory recordkeeping from ordinary personal-information retention.
- Updated release documentation index and Director state/readiness records.

### Hosting recommendation

xneelo Cloud in Johannesburg is the current low-cost hosted-pilot shortlist. Published indicative pricing for 2 vCPU / 4 GB RAM, a 10 GB premium boot volume and 50 GB premium NVMe data volume totals approximately R306/month. It is self-managed and currently offers one Johannesburg availability zone. It is not yet procured, and it does not by itself provide an offsite backup strategy or high availability. Confirm live pricing, terms, processing locations and backup arrangements before production use.

### Retention interpretation

SARS guidance describes a common five-year tax-record period with exceptions. Companies Act section 24 generally requires seven-year retention for company records covered by that section. Neither period should be applied indiscriminately to all customer personal information. Proposed operational defaults (30-day export window, deletion/de-identification within 90 days after closure where lawful, 24-month support/security logs and 30-day rolling backups) require legal/accounting approval and actual technical enforcement.

### Open release gates

- Approve 14-day trial and final Basic/Advanced feature matrix.
- Confirm VAT treatment, payment channel, storage quota and fair-use limits.
- Approve public service address and final public contact fields.
- Procure and configure hosted infrastructure and independent encrypted offsite backups.
- Complete actual sub-operator inventory and written agreements where applicable.
- Implement/test export, account closure, retention and disposal workflows.
- Complete legal review and final trademark clearance.

**Disposition:** Documentation design has advanced; legal/commercial release clearance remains OPEN. V1 remains **NO-GO**.
