# Zazu EMP — V1 Capability Map

This document translates the Product Specification into a practical V1 boundary.

## Legend

- **CORE** — required for V1
- **SUPPORTING** — required where necessary to make a V1 capability reliable or commercially usable
- **POST-V1** — valid product direction, deliberately deferred
- **FOUNDATION** — architecture/data/security work that may not be visible to users

## 1. Platform & Account

| Capability | Priority | V1 |
|---|---|---|
| Registration | CORE | Yes |
| Authentication/session handling | CORE | Yes |
| Business creation/setup | CORE | Yes |
| Business settings | CORE | Yes |
| Basic/Intermediate/Advanced selection | CORE | Yes |
| Change experience level later | CORE | Yes |
| Roles & permissions | CORE | Yes |
| Auditability of important actions | SUPPORTING | Yes |
| Enterprise identity/SSO | POST-V1 | No |

## 2. Onboarding

| Capability | Priority | V1 |
|---|---|---|
| Guided registration flow | CORE | Yes |
| Business information setup | CORE | Yes |
| Products/services setup after registration | CORE | Yes |
| Defer setup where appropriate | CORE | Yes |
| Experience-level selection | CORE | Yes |
| Progressive setup | CORE | Yes |
| Advanced onboarding automation | POST-V1 | No |

## 3. Clients

| Capability | Priority | V1 |
|---|---|---|
| Client records | CORE | Yes |
| Client contact information | CORE | Yes |
| Client ↔ event relationship | CORE | Yes |
| Client activity/context | SUPPORTING | Yes |
| Advanced CRM automation | POST-V1 | No |

## 4. Events

| Capability | Priority | V1 |
|---|---|---|
| Event creation | CORE | Yes |
| Event editing | CORE | Yes |
| Event lifecycle/state | CORE | Yes |
| Client association | CORE | Yes |
| Event planning information | CORE | Yes |
| Event work/process | CORE | Yes |
| Event resources | CORE | Yes |
| Event costs | CORE | Yes |
| Event purchasing | CORE | Yes |
| Event financial relationship | CORE | Yes |
| Event completion | CORE | Yes |
| Exceptional/cancellation handling | SUPPORTING | Yes |
| Advanced event forecasting | POST-V1 | No |

## 5. Products & Services

| Capability | Priority | V1 |
|---|---|---|
| Product records | CORE | Yes |
| Service records | CORE | Yes |
| Pricing/basic commercial information | CORE | Yes |
| Product/service association with events | CORE | Yes |
| Product/service usage in work | SUPPORTING | Yes |
| Advanced catalogue intelligence | POST-V1 | No |

## 6. Work & Process Management

| Capability | Priority | V1 |
|---|---|---|
| Work/task creation | CORE | Yes |
| Event work association | CORE | Yes |
| Status/lifecycle | CORE | Yes |
| Assignment | CORE | Yes |
| Due dates/planning | CORE | Yes |
| Completion tracking | CORE | Yes |
| Relationship to resources/costs | CORE | Yes |
| Complex workflow designer | POST-V1 | No |

## 7. Resources

| Capability | Priority | V1 |
|---|---|---|
| Resource records | CORE | Yes |
| Event/resource association | CORE | Yes |
| Resource requirements | CORE | Yes |
| Availability/basic status | SUPPORTING | Yes |
| Advanced optimisation | POST-V1 | No |

## 8. Suppliers & Purchasing

| Capability | Priority | V1 |
|---|---|---|
| Supplier records | CORE | Yes |
| Purchase records | CORE | Yes |
| Purchase status | CORE | Yes |
| Event/work association | CORE | Yes |
| Receipts/supporting documents | CORE | Yes |
| Cost relationship | CORE | Yes |
| Advanced procurement automation | POST-V1 | No |

## 9. Costs

| Capability | Priority | V1 |
|---|---|---|
| Cost records | CORE | Yes |
| Event association | CORE | Yes |
| Work/resource/purchase relationships | CORE | Yes |
| Cost status/traceability | CORE | Yes |
| Reconciliation controls | SUPPORTING | Yes |
| Predictive costing | POST-V1 | No |

## 10. Finance

| Capability | Priority | V1 |
|---|---|---|
| Core financial records | CORE | Yes |
| Invoice/financial relationships where required by current architecture | CORE | Yes |
| Payment status | CORE | Yes |
| Event financial visibility | CORE | Yes |
| Cost/finance relationship | CORE | Yes |
| Reconciliation | SUPPORTING | Yes |
| Audit trail | SUPPORTING | Yes |
| Advanced financial intelligence | POST-V1 | No |
| Predictive finance | POST-V1 | No |

## 11. Documents & Attachments

| Capability | Priority | V1 |
|---|---|---|
| File attachments | CORE | Yes |
| Receipts | CORE | Yes |
| Photos | CORE | Yes |
| Supporting documents | CORE | Yes |
| Contextual record association | CORE | Yes |
| Document search | SUPPORTING | Yes |
| Full document-management platform | POST-V1 | No |

## 12. Search

| Capability | Priority | V1 |
|---|---|---|
| Global search foundation | CORE | Yes |
| Cross-domain record search | CORE | Yes |
| Permission-aware results | CORE | Yes |
| Filters | CORE | Yes |
| Date/status/type filtering | CORE | Yes |
| Fast record navigation | CORE | Yes |
| Natural-language search | SUPPORTING | Foundation/initial capability |
| Full conversational BI | POST-V1 | No |

## 13. Zazu Helper

| Capability | Priority | V1 |
|---|---|---|
| Contextual help | CORE | Yes |
| Explain screens/fields | CORE | Yes |
| Explain workflow requirements | CORE | Yes |
| Next-action guidance | SUPPORTING | Yes |
| Operational queries | SUPPORTING | Initial capability |
| Natural-language business queries | SUPPORTING | Initial capability where practical |
| Autonomous actions | POST-V1 | No |
| Autonomous business decisions | POST-V1 | No |

## 14. UX & Responsive Product

| Capability | Priority | V1 |
|---|---|---|
| Coherent design system | CORE | Yes |
| Mobile experience | CORE | Yes |
| Desktop experience | CORE | Yes |
| Tablet responsiveness | SUPPORTING | Yes |
| Responsive navigation | CORE | Yes |
| Professional forms | CORE | Yes |
| Professional tables | CORE | Yes |
| Meaningful notifications/toasts | CORE | Yes |
| Empty/loading/error states | CORE | Yes |
| Accessibility fundamentals | SUPPORTING | Yes |
| Decorative redesigns without product value | OUT OF SCOPE | No |

## 15. Reporting

| Capability | Priority | V1 |
|---|---|---|
| Essential operational reporting | CORE | Yes |
| Essential financial visibility | CORE | Yes |
| Experience-level report exposure | SUPPORTING | Yes |
| Advanced analytics/BI | POST-V1 | No |
| Predictive reporting | POST-V1 | No |

## 16. Security & Reliability

| Capability | Priority | V1 |
|---|---|---|
| Server-side authorization | FOUNDATION | Yes |
| Input validation | FOUNDATION | Yes |
| Data isolation | FOUNDATION | Yes |
| Secure attachments | FOUNDATION | Yes |
| Controlled state transitions | FOUNDATION | Yes |
| Financial integrity | FOUNDATION | Yes |
| Safe failure/transaction boundaries | FOUNDATION | Yes |
| Auditability | FOUNDATION | Yes |
| Enterprise security stack | POST-V1 | No |

## 17. V1 Boundary Test

A proposed feature belongs in V1 when it is required to make the defined core lifecycle reliable and commercially usable.

The primary V1 chain is:

**Registration → Onboarding → Client → Event → Work → Resources → Purchasing → Costs → Finance → Completion**

The product must also make that chain understandable through:

**Experience Level + Navigation + Search + Zazu Helper + Responsive UX**

Anything that does not strengthen this chain or a necessary platform foundation should be challenged before entering V1.

## 18. Anti-Overengineering Rule

Do not build future capability merely because the architecture could support it.

Prefer:
- Clear boundaries
- Simple reliable workflows
- Extensible foundations
- Explicit deferral

over:
- Premature abstraction
- Complex generic frameworks
- Unlimited configurability
- Speculative AI
- Feature accumulation

**V1 is a complete product foundation, not the final form of Zazu.**
