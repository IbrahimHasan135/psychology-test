# NovaBase 2.0.0 Architecture Master Plan

Status: Architecture baseline for production implementation

This document defines the identity, tenant, role, permission, database, and
scaling direction for NovaBase 2.0.0. NovaBase is intended to become a real
product foundation for multiple products, not only a demo application.

## Executive Decision

NovaBase 2.0.0 will use a shared global identity model with tenant-scoped
membership and authorization:

- A user account is created once in the global `users` table.
- A user can belong to many tenants through `tenant_memberships`.
- A user's tenant role is resolved from the membership, not from the global
  user record.
- Platform roles and tenant roles are separate concerns.
- Tenant data is isolated by `tenant_id` and enforced by application services,
  policies, middleware, and database constraints.
- The browser has one active tenant context at a time unless a future product
  explicitly requires isolated sessions per tenant.
- Platform addons and tenant addons have different scopes.

This is a shared-database, row-scoped tenancy model. It can support a large
number of tenants and users when its indexes, query patterns, session store,
and operational practices are implemented correctly.

## 100 Million User Assessment

The current prototype is **not ready for 100 million users as-is**. The basic
model is directionally correct, but several current implementation patterns
would become bottlenecks or create authorization risk at that scale.

One MySQL-compatible database can store 100 million users. Storage capacity is
not the only concern. The production design must also handle:

- high concurrent login and session traffic;
- large membership and audit tables;
- index size and write amplification;
- deep pagination and search;
- read/write separation;
- backups, replication, and point-in-time recovery;
- tenant isolation under every query path;
- migrations without long table locks;
- rate limits and account security;
- observability and operational recovery.

The target is therefore not "make one query work with 100 million rows". The
target is predictable behavior as each high-volume table grows independently.

## Current Prototype Findings

The current code already contains useful foundations:

- global users with unique username and email;
- tenant records with unique slugs;
- tenant memberships with a tenant/user uniqueness constraint;
- tenant-scoped website pages;
- tenant-scoped roles and addon permission rows;
- active tenant context in the browser session;
- addon scope support for platform-only modules;
- automatic migration support for local development.

The following areas must be hardened before calling the architecture
production-ready:

1. `users.role` and `tenant_memberships.role` duplicate role meaning.
2. Role and permission references use slugs/strings instead of stable IDs.
3. Membership indexes are not sufficient for the most common user-first
   queries.
4. User lifecycle fields such as status, suspension, last login, and soft
   deletion are missing.
5. `owner_user_id` and the owner membership can become inconsistent without a
   single owner-management service.
6. Deep `OFFSET` pagination will become increasingly expensive.
7. Dashboard role counts can produce repeated queries.
8. Database sessions are not the right default for very high concurrent
   traffic.
9. Automatic migrations on a request path must remain a local-development
   convenience, not a production deployment mechanism.
10. Global scopes are useful protection, but they must not be the only tenant
    isolation mechanism.
11. Login, password reset, invitation, and account recovery need rate limits,
    audit events, and explicit lifecycle rules.

## Target Identity Model

### Global users

`users` represents a global identity and should contain only global account
attributes:

```text
id
name
username
email
password
status
email_verified_at
last_login_at
created_at
updated_at
deleted_at
```

Recommended rules:

- `username` is globally unique if it is used as a platform login identifier.
- `email` is globally unique if one person represents one NovaBase identity.
- Normalize username and email before validation and lookup.
- Use a case-insensitive collation or normalized lookup columns consistently.
- Use `status` values such as `active`, `pending`, `suspended`, and `disabled`.
- Use soft deletion only when business retention rules allow it.
- Never use the global user role to determine a tenant user's role.

The global uniqueness decision is important. If the same email must be able to
represent separate identities in different tenants, the identity model must be
changed deliberately. For the current NovaBase product direction, one global
identity shared across tenants is the cleaner model.

### Tenants

`tenants` represents a customer workspace or product installation:

```text
id
slug
name
status
owner_user_id
settings_json
created_at
updated_at
deleted_at
```

`slug` is a public routing key. It must remain stable or use a redirect/history
mechanism when renamed. Internal foreign keys must use `tenant_id`, never the
slug.

### Tenant memberships

`tenant_memberships` is the authorization boundary between users and tenants:

```text
id
tenant_id
user_id
role_id
status
display_name
invited_at
joined_at
last_seen_at
created_at
updated_at
deleted_at
```

Recommended constraints and indexes:

```text
unique(tenant_id, user_id)
index(user_id, status)
index(tenant_id, status)
index(tenant_id, role_id, status)
```

The current `role` string should be migrated to `role_id` through a staged
dual-read/dual-write migration before the old column is removed.

## Target Role and Permission Model

### Roles

Roles should use stable IDs:

```text
roles
  id
  tenant_id nullable
  slug
  name
  is_system
  is_admin
```

Recommended uniqueness:

```text
unique(tenant_id, slug)
```

System roles may use `tenant_id = null`. Custom roles belong to one tenant.
The application must not assume that a slug alone identifies one role.

### Permissions

Addon manifests may remain the source of permission definitions, but the
database should use a stable permission catalog or a validated permission key:

```text
permissions
  id
  addon_slug
  permission_key
  label
  scope
```

Role assignment should use a pivot such as:

```text
role_permissions
  role_id
  permission_id
```

This avoids storing authorization relationships against mutable role slugs.

### Addon scope

Every addon must declare a scope:

```text
platform
tenant
both
```

Platform addons are available only in the main/default context. Tenant addons
are enabled per tenant. The core must enforce this scope consistently in:

- sidebar menus;
- dashboard cards;
- Web Editor blocks;
- permission catalogs;
- route middleware;
- addon services.

Platform addons must not be inserted into `tenant_addons`.

## Database and Query Strategy for 100 Million Users

### Indexing

Every high-volume query must be designed around an index, not added after a
slow production query appears. Minimum indexes include:

```text
users(username)
users(email)
users(status, id)
tenant_memberships(user_id, status)
tenant_memberships(tenant_id, status)
tenant_memberships(tenant_id, role_id, status)
roles(tenant_id, slug)
role_permissions(role_id, permission_id)
```

Use `EXPLAIN` on login, membership lookup, user listing, authorization, and
tenant page queries with production-like row counts.

### Pagination

Normal numbered pagination is acceptable for the first pages of an admin list,
but large datasets must not rely on deep `OFFSET` values.

Use cursor/keyset pagination for:

- user lists;
- audit logs;
- membership lists;
- activity feeds;
- exports.

Stable ordering should use an indexed key such as `(created_at, id)` or `id`.

### Search

Prefix lookup by indexed username/email is suitable for login. Fuzzy search
across 100 million users should not use `%term%` against the primary users
table. Use a dedicated search index or a search service when the product
requires fuzzy name/email search.

### Aggregations

Do not run one `COUNT` query per role or per tenant on every dashboard request.
Use grouped queries, cached counters, asynchronous reporting, or precomputed
statistics tables depending on freshness requirements.

### Database topology

The first production stage can use one primary database with read replicas.
The application should keep repository/service boundaries ready for:

- primary writes;
- replica reads where eventual consistency is acceptable;
- separate analytics/reporting storage;
- tenant partitioning or sharding for exceptional tenants.

Do not introduce sharding before query and ownership boundaries are clear. A
shared database with strong `tenant_id` discipline is easier to operate first.

## Sessions and Tenant Switching

NovaBase 2.0.0 uses one authenticated browser session and one active tenant
context:

```text
auth user = global identity
active_tenant = selected workspace
```

Expected behavior:

- Browsing another tenant's public page does not expose the current tenant's
  portal link.
- Opening another tenant's login page is allowed.
- Successful login changes the active tenant context.
- Failed login does not destroy the current valid session.
- Admin routes require both membership and active tenant context.
- Public pages remain readable without tenant membership.

If simultaneous independent tenant sessions in different browser tabs become a
business requirement, path-based tenancy is not enough. Use tenant subdomains
or isolated guards/cookies in a later architecture decision.

For high traffic, use Redis or another dedicated session store rather than
relying on a heavily contended database sessions table.

## Security and Operations Required Before Production

- Login and password reset rate limiting.
- Account lock or progressive delay after repeated failures.
- Email verification and invitation expiry.
- Explicit authorization policies for every tenant-owned model.
- Audit logs for identity, membership, role, permission, and content changes.
- Database backup and point-in-time recovery.
- Read replica lag monitoring.
- Slow query logging and request tracing.
- Queue-based exports and reports.
- Online migration strategy for large tables.
- No request-time schema migration in production.
- No unbounded `get()` on user or membership collections.
- No tenant query that can run without an explicit tenant boundary unless it is
  a deliberate platform operation.

## Migration Plan

The user database should not be rewritten in one destructive migration.

### Phase 1: 2.0.0 foundation

- Add user status and lifecycle timestamps.
- Add missing membership indexes.
- Add audit event foundation.
- Make active tenant context explicit.
- Add platform/tenant/both addon scope.
- Ensure platform addons never enter tenant addon records.
- Add policies and services for tenant-owned models.
- Replace dashboard N+1 counts with grouped queries.
- Define cursor pagination contracts.

### Phase 2: 2.0.x compatibility migration

- Add `role_id` to memberships.
- Backfill role IDs from existing role slugs.
- Dual-write `role` and `role_id` temporarily.
- Read from `role_id` first, with a controlled fallback.
- Add foreign keys and integrity checks.

### Phase 3: 2.1.x scale features

- Remove legacy role string columns after all consumers migrate.
- Add invitation and account recovery flows.
- Add audit log UI and exports.
- Add read replicas where justified.
- Add asynchronous reporting and large exports.
- Add search infrastructure if product requirements need fuzzy search.

## Version Decision

The current tenancy prototype should not be marketed as production-ready for
100 million users. The architecture direction is valid, but the hardening work
belongs in the NovaBase 2.0.0 foundation before product-specific development is
locked.

Recommended release meaning:

```text
1.0.0  Modular core, roles, addons, and Web Editor baseline.
2.0.0  Production tenancy and identity foundation.
2.0.1  Bug and security fixes without schema/contract changes.
2.1.0  Backward-compatible scale features and product capabilities.
3.0.0  Only if the public architecture changes again, such as a new identity
       contract, separate tenant databases, or incompatible addon API.
```

If the role storage changes from string references to role IDs, or if the login
and tenant session contract changes for existing addon developers, that change
belongs to `2.0.0`, not `2.1.0`.

## Non-Goals for 2.0.0

- Premature database sharding.
- Separate database per tenant by default.
- Simultaneous multi-tenant browser sessions.
- A generic microservice split.
- Full-text search inside the primary users table.
- Treating request-time auto-migration as a deployment system.

The first goal is a correct, observable, indexed, and migration-safe shared
database architecture. Scale-out mechanisms should be introduced when actual
traffic and tenant size justify them.
