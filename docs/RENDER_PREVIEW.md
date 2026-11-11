# Zazu EMP — Render deployment image

## Purpose

The repository Docker image supports the disposable preview and a persistent Render staging/pilot configuration. The default remains a disposable SQLite preview. **Do not enter real customer or business data until persistent database and private-file storage are configured and recovery has been proven.**

## Repository and runtime

- Repository: `Leano-Jordan/ZazuEMP`
- Engineering source: `main`
- Runtime: Docker / PHP 8.4 / Apache
- Web root: Laravel `public/`
- Port: `10000`
- Health endpoint: `/up`

The image builds locked Composer dependencies and Vite assets. Runtime PHP includes both SQLite and PostgreSQL PDO drivers.

## Disposable preview defaults

When no database connection is supplied, the entrypoint defaults to:

- `DB_CONNECTION=sqlite`
- `DB_DATABASE=/tmp/zazu-preview.sqlite`
- file-backed sessions and cache
- synchronous queue

This preview data is ephemeral and can be lost when the service restarts or redeploys. It is for temporary UI/workflow checks only.

## Persistent staging/pilot configuration

For a persistent Render deployment:

1. Attach a managed Render PostgreSQL database in the same region as the web service.
2. Set `DB_CONNECTION=pgsql` and provide either `DB_URL` (the database connection URL) or the standard `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` and `DB_PASSWORD` values. Do not commit credentials.
3. Attach a persistent disk mounted at `/var/www/html/storage`. This retains Laravel's private uploaded files and file-backed sessions across container replacement. Confirm the mount is writable by the service; the entrypoint prepares ownership for `www-data`.
4. Keep `APP_KEY` stable and secret. Set `APP_ENV=production`, `APP_DEBUG=false` and the correct HTTPS `APP_URL`.
5. Run the deployment smoke, representative backup/restore and rollback drills before allowing customer data.

The entrypoint honours explicit database/session/cache/queue environment variables. It runs migrations against the configured database at startup and fails closed if a non-SQLite connection has neither `DB_URL` nor `DB_HOST`.

**This change makes the container capable of using persistent PostgreSQL and mounted storage; it does not create or attach Render resources, change service settings, or prove that a live deployment is durable.** Infrastructure configuration and real recovery acceptance remain separate release gates.

## Verification boundary

A successful Docker build/deploy and HTTP health check do not prove registration, onboarding, offline operation, data durability, backup/restore, or production readiness. Those require their own automated and operator acceptance evidence.
