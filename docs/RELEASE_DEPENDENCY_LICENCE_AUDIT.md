# Zazu EMP — Release Dependency Licence Audit

**Audit date:** 2026-10-07  
**Basis:** `composer.lock` and `package-lock.json` on `main`  
**Scope:** locked Composer packages and npm packages, including development/build dependencies

## Result

The locked dependency manifests contain explicit licence metadata for all audited packages.

### Composer

| Licence | Locked packages |
|---|---:|
| MIT | 77 |
| BSD-3-Clause | 29 |
| BSD-3-Clause / GPL-2.0-only / GPL-3.0-only | 2 |
| Apache-2.0 | 1 |

The two dual/multi-licensed packages are:

- `nette/schema` v1.3.6
- `nette/utils` v4.1.5

Their metadata permits selection among the listed licensing options; release distribution must preserve the applicable upstream notices.

### npm

| Licence | Locked package entries |
|---|---:|
| MIT | 108 |
| MPL-2.0 | 24 |
| ISC | 7 |
| Apache-2.0 | 5 |
| OFL-1.1 | 1 |
| BSD-3-Clause | 1 |
| 0BSD | 1 |
| MIT OR CC0-1.0 | 1 |

The MPL-2.0 entries are the `lightningcss` package and its platform builds, including nested Vite copies. These are build-tool dependencies, not application source code. Distribution of generated assets must still retain any applicable third-party notices and the project must not misrepresent MPL-covered source as proprietary.

The OFL-1.1 dependency is the bundled Anton font package.

## Release handling

1. Keep `composer.lock` and `package-lock.json` fixed for a release candidate.
2. Preserve required upstream copyright/licence notices in the release artifact or accompanying notices.
3. Do not claim the top-level notice register is an exhaustive source-code attribution list.
4. Re-run this audit whenever locked dependencies change.
5. Legal/commercial owner review remains required for final distribution terms.

## Status

**Engineering licence inventory: VERIFIED.**

This closes the repository-side dependency metadata gap. It does **not** by itself close legal review, proprietary distribution terms, trademark clearance, or final release certification.
