# Zazu EMP — Render preview deployment

**Purpose:** disposable preview of the canonical `main` codebase only.  
**Not production-ready. Do not enter real customer or business data.**

## Repository and source

- Repository: `Leano-Jordan/ZazuEMP`
- Deployment source: `main`
- Runtime: Docker / PHP 8.4 / Apache
- Web root: Laravel `public/`
- Health endpoint: `/up`

The root Dockerfile builds locked Composer dependencies and Vite assets, then serves Laravel through Apache on Render's expected port 10000. Apache rewrite support and `AllowOverride` are enabled for Laravel's public `.htaccess`.

## Deliberate preview-only data model

The entrypoint forces SQLite at `/tmp/zazu-preview.sqlite`, file-backed sessions/cache, and synchronous queues. The database, sessions, cache and private uploads are on the container filesystem. Render free instances have ephemeral storage and may restart or redeploy; data can be lost.

This deployment is suitable only for temporary UI and workflow preview. It is not suitable for pilots, customer onboarding, business records, or any production use. A persistent database and durable private-file storage must be selected and tested before real data is accepted.

## Required Render environment

- `APP_KEY`: a unique generated Laravel key; keep it in Render environment variables, never Git.
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://zazu-emp-staging.onrender.com`

The entrypoint fails closed if `APP_KEY` is missing. Migrations run at container startup against the disposable preview database.

## Verification boundary

A successful Docker build/deploy and HTTP health check do not prove registration, onboarding, offline operation, data durability, backup/restore, or production readiness. Those require their own automated and operator acceptance evidence.
