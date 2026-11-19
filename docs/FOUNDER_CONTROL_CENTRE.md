# Founder Control Centre

## Purpose

The Founder Control Centre is the platform-level administration surface for the explicitly configured Zazu founder identity. It is separate from a customer's business-owner workspace.

Access is protected by the existing `sentinel.admin` middleware and the configured `ZAZU_PLATFORM_ADMIN_EMAIL` identity. It must never be exposed through ordinary business-owner permissions.

## Live business directory

The Founder Control Centre reads the actual `businesses` and `business_user` records. This directory is distinct from `platform_license_customers`, which is a manually maintained licensing registry and must not be treated as the source of truth for registered Zazu accounts.

The directory currently provides:
- Business name and status.
- Linked user names and business roles.
- Counts of linked users, customers and events.
- Search by business name, business email, member name or member email.
- A founder-only account detail page with membership and aggregate counts.

The detail page deliberately does not expose customer records, event details, financial records, passwords, reset tokens, session credentials or offline device secrets.

## Current access boundary

The live directory and account detail page are read-only. They do not change the active business session, impersonate a user, or bypass `EnsureActiveBusinessContext`.

A founder identity is not automatically a member of every business. Do not add the founder to customer `business_user` memberships as a shortcut to support access.

## Requirements before enabling support actions

Any future account mutation or temporary support workspace must be implemented as a separate, explicit founder capability. At minimum:

1. A dedicated platform-level audit record, not the business-scoped `audit_logs` table.
2. Actor identity, target business, action, reason, timestamp, request ID and outcome recorded for every access and mutation.
3. Re-authentication or an equivalent confirmation for sensitive actions.
4. Temporary support access with a short expiry, explicit end action and automatic expiry.
5. Read-only access by default; write access must be separately authorised.
6. No password disclosure, credential reset-token disclosure or silent user impersonation.
7. Support access must not weaken tenant scoping or change the business owner’s active workspace.
8. Feature tests proving non-founders are denied, expired support sessions are denied, and every support action is audited.

## Acceptance checks

- A configured founder can open `/admin/sentinel` and see businesses from the live database.
- A configured founder can open a business detail page and see its linked users and aggregate counts.
- A non-founder receives HTTP 403 for both routes.
- Ordinary business-owner routes continue to enforce the active business context.
- The licensing registry remains clearly distinguished from the live business directory.

## Implementation status

- Live directory and search: implemented.
- Founder-only business detail and membership view: implemented.
- Directory and detail access-control tests: added; run them in the local Zazu environment before operational acceptance.
- Audited temporary support access and account mutations: not yet implemented.
