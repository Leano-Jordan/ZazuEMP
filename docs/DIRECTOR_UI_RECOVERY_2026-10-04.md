# Director UI recovery — 2026-10-04

## Baseline

Repository: `Leano-Jordan/ZazuEMP`
Main at recovery start: `1607f23ccc9272535bf7b722b345fd55d6f5ab96`

The CSS files were structurally balanced. The failure mode was selector/authority drift rather than a parser failure. The supplied Dashboard screenshot showed child text collapsing together inside the hero status block, empty state, metric cards and resource/commercial sections.

## Verified findings

### Dashboard
`dashboard.blade.php` renders resource items as `<a class="zazu-quick-link">`, while older rules in `zazu-final-visual-sweep.css` targeted `.zazu-dash-resource-grid > div`. Exact dashboard child selectors were added at the dashboard component boundary so labels, values, supporting copy and resource rows have deterministic block/grid layout.

### Helper
The existing collision calculation could move the Helper toward an open navigation flyout because it calculated from the flyout's left edge and used `Math.max`. The calculation now uses the flyout's right edge and available viewport space. When avoidance is required, the Helper gains a short transform glide rather than simply jumping.

### Typography
The UI now uses bundled Manrope Variable and Space Grotesk Variable. The UI/display split is intentional: Manrope handles productive application text, while Space Grotesk provides a distinct display voice for page headings and KPI-style values. Global field spacing, line-height and selection contrast were strengthened.

### Light mode
The light surface system was moved toward a brighter blue-led palette with clearer separation between page canvas, cards, fields and interactive blue states. The existing dark token set was left untouched.

### Branding image errors
The branding file inputs sit outside the normal `.zazu-field` wrapper. Their errors therefore did not reliably join the shared server-error summary. Dedicated `data-branding-error` targets and shared error association were added. Client-side validation now blocks branding images over the existing 5 MB server limit with an explicit message.

## Preservation

No operational route, permission, navigation destination, offline route, or existing dark-theme token set was intentionally removed.

## Verification boundary

Source-level CSS/JS/Blade balance and targeted regression assertions were added. Full PHPUnit/Playwright execution and browser/device screenshot comparison still need to be run in the Zazu environment.
