# Zazu EMP UI/UX Audit and Hardening Record

Date: 2026-09-26

## Scope

This audit covers the shared application shell and the currently implemented Zazu EMP views in resources/views, with attention to visual hierarchy, interaction consistency, responsive behaviour, forms, content clarity, colour relationships, keyboard access, assistive technology cues, and suitability for South African small-business users.

## Design direction applied

The supplied visual direction was translated into a Zazu-specific system rather than copied literally:

- Warm cream and white surfaces with a deep forest-green navigation shell in light mode.
- Deep emerald surfaces with bright mint primary actions in dark mode.
- Distinct cards and sections using spacing, elevation and restrained top accents.
- One coherent visual system across dashboard, Work, Customers, Requirements, Quotes, Calendar, Travel, Catalogue and Settings.
- Rounded controls and cards with stronger separation between adjacent information groups.
- Mobile layouts preserve the same hierarchy and allow dense calendar content to scroll instead of collapsing into unreadable cells.

## Accessibility and interaction hardening

Implemented:

- Skip-to-content link.
- Focus-visible indicators with high-contrast outlines.
- Minimum 44px interactive controls for core buttons, fields and theme control.
- Required-field visual markers normalised at runtime.
- Inline error association using aria-invalid and aria-describedby.
- Form error summary with links back to affected fields.
- Native telephone input type and telephone autocomplete on contact forms.
- Email input mode and autocomplete handling.
- Decimal input mode for numeric fields.
- Larger calendar event targets and full weekday names exposed through labels.
- Reduced-motion support.
- Forced-colour support.
- Higher-contrast mode support.
- Mobile calendar horizontal scrolling for dense month layouts.
- Content wrapping for long customer names, references, addresses and descriptions.
- Theme metadata follows light/dark mode.

## Content and South African usability

Customer-facing copy was simplified to use common words and short, task-focused descriptions. Developer terms such as skeleton, engine not connected, and similar implementation language were removed from visible product surfaces and replaced with honest user-facing status language.

The interface avoids assuming specialist English vocabulary. Labels describe the business task first, while deeper operational concepts remain in supporting copy. Examples continue to use familiar South African contexts such as weddings, funerals, catering, hires and phone numbers in local format.

The UI remains English-first while keeping text simple enough to translate later. No false language selector was introduced before translation content exists.

## Commercial UX maturity

Improved:

- Clear primary actions.
- Consistent secondary and tertiary actions.
- Distinct page heading and page content hierarchy.
- Predictable placement of navigation, context, actions and records.
- Clear empty states.
- Honest planned-module states rather than fake live metrics.
- Consistent historical-record language around quotes and removed records.
- Better separation between catalogue definitions, Work requirements and commercial quoting.
- Responsive behaviour for desktop, tablet and mobile.
- Persistent theme choice.
- Project metadata and CI PHP runtime alignment.

## Remaining product-level maturity boundaries

These are not hidden by the UI:

- Inventory is planned, not represented as live data.
- Supplier management is planned, not represented as live data.
- Asset management is planned, not represented as live data.
- Reporting is planned, not represented as live data.
- Authentication, active business context, server-side business isolation and role permissions remain production architecture work rather than UI decoration.

## Verification basis

The hardening was checked against WCAG 2.2 interaction and visual guidance and current South African GCIS web guidance on accessibility, plain language, mobile-first design, information architecture and navigation.

Colour contrast was checked for the principal foreground/background relationships in both themes. Primary text and controls exceed common WCAG AA contrast thresholds, and stronger border values were selected so component boundaries remain visible rather than depending only on shadow.

## CI verification

The repository workflow was aligned to PHP 8.4 because the current dependency lock contains Symfony 8.1 packages requiring PHP 8.4.1 or newer while the project itself permits PHP 8.3 and above.

A pre-hardening CI failure was confirmed at Composer installation because the workflow was running PHP 8.3 against the current lock set. The workflow was corrected to PHP 8.4. Subsequent commits continue to verify the resulting application state through GitHub Actions.
