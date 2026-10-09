# NovaBase Optional Tenancy Architecture

## Purpose

NovaBase supports two product modes from the same codebase:

1. Single-product mode with no visible tenant concept.
2. Multi-tenant mode where one installation serves multiple organizations.

Products select the mode through environment configuration. Core controllers, addons, page builder, authorization, and storage contracts remain shared.

## Important Terminology

- A tenant is an organization or customer workspace.
- An addon is a feature package installed by the product or enabled for a tenant.
- A tenant is not a folder and does not require a new source file.
- `/gbi` is a dynamic route resolved from the `tenants.slug` database column.

## Configuration

Single-product mode:

    NOVABASE_TENANCY_ENABLED=false
    NOVABASE_TENANCY_DRIVER=single
    NOVABASE_DEFAULT_TENANT_SLUG=default

Path tenant mode:

    NOVABASE_TENANCY_ENABLED=true
    NOVABASE_TENANCY_DRIVER=path

In single mode, middleware still resolves the internal `default` tenant. The tenant is hidden from the URL and UI. This keeps every tenant-aware query consistent without nullable `tenant_id` branches.

## Request Lifecycle

    HTTP request
      -> ResolveTenant
      -> authentication
      -> EnsureTenantMembership
      -> role and permission middleware
      -> controller/service
      -> tenant-scoped query

`TenantContext` is the request-scoped source of truth. It exposes the active tenant and whether the request came from a tenant URL.

## Data Model

Core tables:

- `tenants`: slug, name, status, owner.
- `tenant_memberships`: user-to-tenant relationship and effective tenant role.
- `tenant_addons`: addon entitlement per tenant.
- `site_pages.tenant_id`: website ownership.
- `site_blocks`: belongs to a tenant through its page.

The existing `users.role` remains the platform role for compatibility. In a tenant-scoped request, the membership role is the effective role. This permits the same user to have different roles in different tenants.

## Role Boundaries

Platform Super Admin:

- Logs in through the root application.
- Can inspect and provision tenants from the platform context.
- Can access platform-enabled addons.

Tenant owner:

- Logs in through `/{tenant}/login`.
- Has the tenant-scoped role `super_admin` for that tenant.
- Can manage tenant users and tenant roles when those modules are enabled.
- Cannot access platform-only addon screens or other tenant data.

The same role label does not mean the same scope. Scope comes from the request context and membership.

## Tenant Creation Flow

The Demo addon demonstrates the initial provisioning flow:

1. Platform Super Admin opens Demo Addon tenant management.
2. The system validates tenant name, slug, and owner credentials.
3. A tenant row is created.
4. An owner user is created with a neutral platform role.
5. A membership is created with tenant role `super_admin`.
6. A tenant-owned Home page is created.
7. The tenant is immediately available at `/{slug}`.

No `gbi` or `hkbp` directory is created.

## Addon Entitlements

An addon has two separate concerns:

- Registration: the addon exists in NovaBase.
- Entitlement: the addon is enabled for the current tenant.

Platform requests use the existing addon registry. Tenant requests require an active `tenant_addons` record before an addon menu or dashboard feature is exposed. This prevents a tenant owner from seeing platform-only Demo screens by inheriting the `super_admin` label.

## Route Strategy

The same route names support root and tenant contexts through an optional tenant prefix. URL defaults are set by `ResolveTenant`, so controller redirects and shared Blade components continue to use the same route names.

Tenant routes are dynamic. Apache only forwards the request to Laravel's front controller; it does not need a physical tenant folder.

Reserved slugs should be rejected before production onboarding, including `login`, `admin`, `platform`, `api`, `assets`, `build`, and `storage`.

## Product Compatibility

Single-product projects keep the same addon APIs and page builder APIs. They only use the `single` tenant driver. Multi-tenant projects enable the `path` driver and add tenant provisioning or subscription features as product addons.

No NovaBase core fork is required per product.

## Security Rules

- Never trust a tenant ID sent by the browser.
- Resolve tenant from the route or configured default only.
- Check membership before tenant admin actions.
- Scope tenant-owned queries by `tenant_id` or a tenant-owned relation.
- Keep platform and tenant roles separate by context.
- Keep tenant addon entitlements separate from global addon registration.
- Use tenant-aware cache keys, upload paths, queues, and exports as those features are added.

## Current Demo Scope

The current foundation includes:

- Single/path tenancy configuration.
- Tenant and membership tables.
- Tenant context middleware.
- Tenant-scoped page queries.
- Tenant membership-aware user management.
- Tenant addon entitlement checks.
- Demo tenant creation and listing for platform Super Admin.

Future work should add tenant-scoped role records, tenant-aware uploads/cache, domain resolver support, suspension handling, invitations, and plan-based addon entitlements.
