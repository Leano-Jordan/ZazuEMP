# Zazu EMP — Icon library and asset provenance audit

**Audit date:** 2026-11-24  
**Repository:** `Leano-Jordan/ZazuEMP`  
**Scope:** Current UI icon implementation, package dependencies, commercial-use licensing and a low-risk path to a more communicative interface.

## Executive decision

**Recommended source for future UI icons: Lucide (ISC License).** It permits commercial use, modification and redistribution subject to retaining the applicable copyright and permission notices when redistributing the icons themselves. It is a permissive fit for a paid desktop/web product. Keep the selected SVGs local to Zazu; do not add a runtime icon font, CDN dependency or a large framework solely to render icons.

This is a source/license review, not legal advice. Preserve the exact upstream license text with any copied icon assets and re-check the upstream license at release time.

## Current implementation findings

- `package.json` has no icon-library dependency.
- `resources/views/components/icon.blade.php` contains a hand-maintained PHP map of inline SVG path fragments.
- The component renders an SVG with `aria-hidden="true"` and `focusable="false"`; surrounding controls therefore need their own accessible names.
- Unknown icon names silently fall back to the `briefcase` icon. This can hide misspelled/missing icon references and produce misleading visuals.
- The path data visually resembles a common outline-icon style, but the repository does not record its source or author. **Do not assume these existing paths are original or that they are licensed by Lucide/Tabler/another project.** Their provenance remains unverified until source history or original design files establish it.

## License review

### Lucide — recommended

- Upstream: https://github.com/lucide-icons/lucide
- License: ISC; the upstream project explicitly states personal and commercial use are allowed.
- Modification is permitted.
- Keep the ISC notice with redistributed copies of the icon assets. Lucide's license also identifies icons inherited from Feather and includes the relevant MIT notice.
- Lucide does not include brand logos; use official brand assets only where separately licensed/approved.

Sources: Lucide upstream README and license:
- https://github.com/lucide-icons/lucide#license
- https://github.com/lucide-icons/lucide/blob/main/LICENSE

### Practical alternatives

- Heroicons: MIT; permissive for commercial product interfaces. https://github.com/tailwindlabs/heroicons
- Tabler Icons: MIT; permissive, but retain its license notice when redistributing the icon assets. https://github.com/tabler/tabler-icons
- Bootstrap Icons: MIT; viable, but its visual language is less aligned with the current outline system. https://github.com/twbs/icons

Lucide is the strongest default because it offers a broad, consistent outline family and multiple export formats without requiring a framework-specific runtime. This is a product/design choice, not a claim that alternatives are legally unsuitable.

## Recommended implementation approach

1. Keep the current server-rendered `<x-icon>` API so templates do not need a broad rewrite.
2. Replace or extend the path map using explicitly selected Lucide SVGs copied into the repository, retaining the upstream license notice in a dedicated third-party notices file.
3. Add a test that enumerates icon names used by Blade templates and fails on unknown names. Remove the silent `briefcase` fallback for production rendering; use a deliberate missing-icon marker in development/test so defects are visible.
4. Add semantic naming guidance: icons supplement text; icon-only buttons must have an accessible label; status must never be conveyed by color or icon alone.
5. Audit every icon use by meaning and context before replacing paths. Avoid mechanical name-only swaps where a visually similar icon communicates a different action.
6. Keep icons local and bundled. This preserves offline use, avoids external requests, avoids runtime package overhead and makes the deployed UI deterministic.

## Scope boundary

This audit does **not** authorize a wholesale visual redesign, animation dependency, icon font, external CDN, or bulk icon replacement. The immediate quality issue is consistent meaning and reliable coverage, not maximizing the number of icons.

## Open actions

- [ ] Establish provenance of the current inline SVG path set from original design/source history; until then, treat it as unverified.
- [ ] Select the initial Lucide subset against actual Zazu navigation, workflow, status, finance, inventory and recovery actions.
- [ ] Add third-party license notice and icon-name validation before introducing copied upstream paths.
- [ ] Run desktop and mobile visual/accessibility acceptance after the controlled replacement.

**Director disposition:** Adopt Lucide as the preferred future icon source; do not represent the current custom path set as cleared until provenance is established.
