# Zazu EMP — Retention and Secure-Disposal Schedule

**Status:** CONTROL DRAFT — LEGAL/ACCOUNTING PERIODS MUST BE CONFIRMED  
**Owner:** `[RESPONSIBLE PARTY]`  
**Review cycle:** `[TO BE COMPLETED]`

> Do not invent statutory retention periods here. Final periods must be confirmed against the actual business, accounting, tax, contractual and regulatory requirements.

## Rules

Retention is based on purpose and obligation, not merely on whether a database row is soft-deleted.

For every record class, the responsible party must identify:

1. why it is retained;
2. the retention trigger;
3. the approved retention period;
4. the disposal/de-identification action;
5. the owner responsible for the action;
6. exceptions such as legal hold, disputes, fraud/security investigations or accounting obligations.

## Record-class schedule

| Record class | Typical Zazu data | Retention trigger | Final period | Disposal action | Current implementation |
|---|---|---|---|---|---|
| Account identity | name, email, auth metadata | account closure | `[LEGAL/COMMERCIAL REVIEW]` | delete/de-identify where permitted | Account exists; no dedicated retention worker evidenced |
| Business/workspace | business identity/config | business closure/contract end | `[REVIEW]` | delete/export/de-identify according to contract | Implemented |
| Customers/contacts | names, phones, emails | customer relationship ends | `[REVIEW]` | delete/de-identify where permitted | Soft-delete lifecycle exists for applicable records |
| Events/jobs | event operational history | job/business closure | `[REVIEW]` | archive/delete per approved schedule | Soft-delete/history controls exist |
| Quotes/invoices | commercial records | legal/accounting trigger | `[REVIEW]` | controlled archive/destruction | Implemented; financial history preserved |
| Payments | payment records | legal/accounting trigger | `[REVIEW]` | controlled archive/destruction | Implemented |
| Attachments/media | receipts, photos, documents | purpose/relationship end | `[REVIEW]` | secure deletion from private storage | Private storage/access control implemented |
| Audit logs | security/business mutation evidence | risk/legal/business trigger | `[REVIEW]` | restricted archive/destruction | Authoritative audit log implemented |
| Error/security records | incident/error metadata | incident closure + risk period | `[REVIEW]` | secure disposal | Incident recorder implemented |
| Backups | database + private storage | backup expiry | `[REVIEW]` | secure deletion of backup artifact | Backup/restore commands implemented |
| Development/test data | demo/fake data | test no longer needed | short operational period | secure deletion | Test/demo seed data present |

## Secure-disposal rules

When disposal is approved:

- delete database records and dependent private files together where applicable;
- remove backup copies according to the backup retention policy;
- remove temporary exports and generated archives;
- preserve only the minimum evidence necessary to demonstrate the disposal action;
- use de-identification instead of deletion where the approved purpose requires retained statistics/history;
- document legal holds and do not dispose of affected records until the hold is released.

## Review triggers

Reassess the schedule when:

- Zazu changes deployment from local to hosted;
- a new external operator is introduced;
- new personal-information categories are added;
- WhatsApp or direct messaging integration is introduced;
- event photography/media storage expands;
- commercial/accounting requirements change;
- a legal hold or incident requires exceptional preservation.

## Release completion

- [ ] exact retention periods approved;
- [ ] retention triggers approved;
- [ ] disposal owner assigned;
- [ ] backup retention aligned;
- [ ] private media disposal tested;
- [ ] legal/accounting review completed where required.
