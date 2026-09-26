# Third-Party Notices

## Purpose

This file records third-party software, assets and other material used by ZazuEMP.

Third-party material is not automatically owned by the ZazuEMP project.

## Register

| Component / Asset | Version | Licence | Source | Notes |
|---|---|---|---|---|
| Laravel Framework | 13.x project dependency | MIT | laravel/framework | Open-source framework; retain upstream licence notice/terms. Upstream metadata identifies Laravel Framework as MIT. |
| Laravel Tinker | ^3.0 | MIT | laravel/tinker | Open-source development/runtime utility; verify exact installed version in lockfile at release. |
| FakerPHP/Faker | ^1.23 (dev) | MIT | FakerPHP/Faker | Development/test dependency. Upstream metadata identifies FakerPHP/Faker as MIT. |
| Vite | ^8.0.0 | MIT | vitejs/vite | Frontend build tooling; published artifacts can contain bundled dependencies under multiple licences, so the exact generated licence output should be reviewed for each release. |
| Tailwind CSS | ^4.0.0 | MIT | tailwindlabs/tailwindcss | Frontend styling tooling. Upstream package metadata identifies Tailwind CSS as MIT. |
| laravel-vite-plugin | ^3.1 | Open-source | laravel/vite-plugin | Exact licence and installed version should be verified from lockfile/package metadata before release. |
| concurrently | ^10.0.3 | MIT | open-cli-tools/concurrently | Development tooling; verify exact installed version in lockfile at release. |
| @laravel/multiplex | ^0.4.1 (optional) | MIT | kolossal-io/laravel-multiplex | Optional dependency. Upstream repository identifies this package as MIT. |

## Rules

Before adding a dependency or external asset:

1. Identify the exact component and version.
2. Record its licence.
3. Check compatibility with the project's proprietary distribution model.
4. Preserve required copyright and licence notices.
5. Record material obligations or restrictions.
6. Prefer dependencies with clear provenance and sustainable maintenance.

The register must be updated when dependencies are introduced.

### Release-level licence control

This register is a top-level inventory, not a complete transitive dependency audit. Before a commercial release, inspect `composer.lock` and `package-lock.json`, verify exact installed versions and licences, and preserve any required notices. Vite supports generating a licence report for bundled dependencies.

## Licence caution

A dependency's licence may impose obligations that affect distribution, source availability, notices, attribution or commercial use. Review the actual licence before adopting it.
