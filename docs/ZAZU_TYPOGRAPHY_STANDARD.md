# Zazu EMP — Typography Standard

**Status:** DESIGN RESEARCH / CANDIDATE STANDARD  
**Director update:** 2026-10-01

## Principle

Typography should provide much of Zazu's visual personality without turning the operational UI into decoration.

Fonts should be self-hosted for predictable rendering, offline use and version control.

Fontsource is a useful packaging route for self-hosting open-source fonts.

## Candidate families

### Manrope
Character: modern, friendly, professional.

Potential use:
- primary application UI;
- headings;
- forms;
- dashboard.

### Plus Jakarta Sans
Character: polished, approachable, business-oriented.

Potential use:
- primary application UI;
- landing page body;
- forms and navigation.

### DM Sans
Character: clean and highly readable.

Potential use:
- dense operational screens;
- tables;
- forms;
- secondary UI.

### Space Grotesk
Character: distinctive, contemporary, technical without being sterile.

Potential use:
- Zazu landing-page headings;
- major marketing statements;
- brand moments.

### Outfit
Character: geometric and modern.

Potential use:
- landing page and promotional surfaces;
- headings if the final Zazu artwork supports it.

## Initial combinations to test

### Option A — restrained
Space Grotesk + Manrope

### Option B — friendly business
Plus Jakarta Sans + Manrope

### Option C — operational clarity
Space Grotesk + DM Sans

Do not ship a combination solely from a text description. Render actual Zazu screens and compare:
- numeric readability;
- small labels;
- mobile forms;
- dashboard headings;
- landing hero;
- South African names/addresses;
- currency values.

## Font rules

- Maximum two families in the product by default.
- Use weight and size before adding another font.
- Financial numbers must remain highly legible.
- Do not use a display font for dense tables/forms merely because it looks attractive on the landing page.
- Bundle fonts locally; avoid making the application dependent on a remote font CDN.
- Verify the exact font license and package version at adoption time.

## Current recommendation

**Candidate direction: Space Grotesk for brand/landing headings + Manrope for application UI.**

This is a design candidate, not yet an implementation mandate.
