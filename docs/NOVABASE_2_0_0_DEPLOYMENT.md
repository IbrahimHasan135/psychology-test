# NovaBase 2.0.0 Production Deployment

NovaBase uses a shared database with row-scoped tenancy. Every tenant-owned row carries `tenant_id`; the application sets the active tenant from the URL and never trusts a client-supplied tenant id for authorization.

## Recommended topology

- Nginx or Apache terminates TLS and serves the Laravel `public/` directory only.
- PHP-FPM runs the application. Use OPcache with production validation timestamps disabled after each deploy.
- MySQL 8.0+ is the source of truth. Keep the database private and use a least-privilege application user.
- Redis is recommended for sessions, cache, queues, and rate limiting when more than one PHP worker or server is used.
- Run queue workers under Supervisor or systemd. Do not use the `sync` queue for long-running addon jobs.
- Keep uploads on shared object storage or a shared filesystem when multiple application servers are used.

## Production environment

Set these values explicitly in `.env`:

```dotenv
APP_ENV=production
APP_DEBUG=false
DB_AUTO_MIGRATE=false
DB_AUTO_SEED=false
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
```

Use `php artisan migrate --force` once per release from a single deployment runner. The request middleware intentionally does not run migrations in production. Run `php artisan config:cache`, `php artisan route:cache`, and `php artisan view:cache` after environment changes.

## Scaling rules

- Keep tenant pages, memberships, permissions, and audit queries scoped by `tenant_id`.
- Use cursor pagination for large directories. Search uses indexed prefix fields; avoid unbounded `LIKE '%term%'` queries in operational screens.
- Public page state is cached for five minutes and keyed by page update timestamp. A page save changes its builder version and timestamp, so new content gets a new cache key.
- Permission and membership lookups are memoized per request. Redis is still required for shared cross-request cache and sessions.
- Optimistic builder versions reject stale editor saves with HTTP 409 instead of silently overwriting another administrator's work.
- Add read replicas only after measuring database load. Route writes, login, memberships, permissions, and editor saves to the primary connection.

## Operations checklist

1. Take encrypted, tested MySQL backups and verify point-in-time recovery.
2. Monitor PHP-FPM saturation, request latency, MySQL slow queries, Redis memory, queue depth, failed jobs, 4xx/5xx rates, and storage usage.
3. Retain and rotate `audit_logs` according to the product's legal and operational policy; archive old records before deleting them.
4. Deploy migrations before application code that depends on new columns, then warm caches.
5. Use a rolling deployment only when all workers and web nodes can safely read the current schema.

## Tenant capacity boundary

Shared-database tenancy is appropriate for many tenants with moderate per-tenant data and gives simple cross-tenant operations. If one tenant becomes exceptionally large or needs legal isolation, introduce a tenant database driver behind the tenancy layer. Do not place tenant-specific branching throughout controllers or addons.
