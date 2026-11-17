# Zazu Plain-English Content Standard

**Status:** Active product writing standard  
**Audience:** South African small-business owners and staff who may use English as an additional language.

Zazu must be easy to understand for someone with a basic English foundation. The software can be capable and detailed without making people learn technical or corporate language to use it.

## Writing rules

1. Use familiar everyday words. Prefer short, direct sentences.
2. Describe the user's task, not the software's internal process.
3. Use clear labels that say exactly what information is needed or what an action will do.
4. Put extra explanation under the label, not inside a long label.
5. Use active wording and name the next action.
6. Keep one term for the same thing across pages. Prefer **job** for the work record, **customer** for the customer record, **quote** for a price offer and **business** where the interface currently says workspace.
7. Use South African examples and familiar formats where they help. Do not assume every user knows specialist catering or technology terms.
8. Explain a specialist business term the first time it matters. Do not replace a correct business term with a misleading simpler word.
9. Keep error messages calm, respectful and useful. Say what could not be done and what the user can do next.
10. Keep technical detail, error codes and reference numbers available for support, but do not make users understand them to continue ordinary work.

## Labels and field help

- Avoid vague labels such as **Details**, **Type**, **Date**, **End**, **Options** or **Process** when the page context does not make their meaning obvious.
- Prefer specific labels such as **Job start date**, **Event end time**, **Equipment collection date** and **Customer phone number**.
- Use helper text to explain why a field is needed, what date/time it refers to, and whether it can be left blank.
- Make required and optional fields clear.
- Use examples only when they show a realistic answer, not as a substitute for a label.
- A button must describe the result, for example **Save changes**, **Create job** or **Record payment**.

## Words to avoid in customer-facing copy

Avoid technical or corporate terms unless the user is likely to know them or they are explained in plain words.

Examples:
- database operation → open or save information
- workspace → business / business account, where that is what is meant
- schema or migration → database update (only when needed)
- mutation → saved change
- idempotency → safe retry / prevent a duplicate
- infrastructure → server or service, where relevant
- configuration → settings
- authorization → access
- reconcile → check that records match
- operational → day-to-day work
- attribution → which customer or job something belongs to

These are writing examples, not automatic word replacements. Preserve technical accuracy.

## Error-message pattern

Use this order:

1. **What happened:** one short, plain sentence.
2. **What to do next:** one clear action.
3. **Support reference:** label it **Reference number** and keep the code easy to copy.

Never show SQL, database names, file paths, stack traces, developer terms or claims about a cause that has not been confirmed.

## Review checklist

Before customer-facing copy is accepted, check:

- Can someone with basic English understand the label without asking for help?
- Does the wording describe the user's task rather than Zazu's implementation?
- Is the next step clear?
- Are the same terms used on desktop, phone, offline screens, forms, calendars, reports and exports?
- Does the text remain accurate when data is missing, saved offline or waiting to sync?

This standard applies to page titles, navigation, field labels, buttons, helper text, validation messages, empty states, warnings, error pages, notifications, reports, exports and offline status messages. It is a standing review requirement for future UI work, not a one-off copy update.
