# NovaBase

Green Laravel base for Novalynk products: public website, login, admin panel, accounts, roles, page management, and modular addons for future product features.

## Local XAMPP Setup

1. Create a MySQL database named `novabase`, or adjust the name in `.env`.
2. Make sure `.env` points to your local database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=novabase
DB_USERNAME=root
DB_PASSWORD=
```

3. Run migrations and seeders:

```bash
/opt/lampp/bin/php artisan migrate --seed
```

4. Open through Apache without `/public`:

```text
http://localhost/github/psychology-test
```

The project root already contains `index.php` and `.htaccess` for shared hosting or subfolder deployments. The ideal hosting setup is still pointing the document root to `public/` when your hosting panel supports it.

Or use the Laravel dev server:

```bash
/opt/lampp/bin/php artisan serve
```

## Auto Migration And Seeding

`DB_AUTO_MIGRATE=true` lets Laravel run migrations automatically when the website is accessed. It creates missing tables and applies changes already defined in `database/migrations`.

`DB_AUTO_SEED=true` keeps required seed data available, including the default Super Admin account.

Important: the MySQL database itself must still be created first, for example `novabase`. Laravel migrations manage tables inside the database, not the database creation.

For production hosting, you can disable automatic migration:

```env
DB_AUTO_MIGRATE=false
```

Then run migrations manually from terminal or import SQL from a prepared local database.

## Default Accounts

| Role | Login | Password |
| --- | --- | --- |
| Super Admin | `novalynk.superadmin` | `N0v4.lynk` |
| Admin | `admin` | `password` |
| User | `user` | `password` |

The seeder updates default accounts when they already exist, so Super Admin password changes are applied when `DB_AUTO_SEED=true` or when running `php artisan db:seed`.

## Addon System

NovaBase includes a Laravel-native addon foundation inspired by NovaStore.

- Active addons are listed in `config/addons.php`.
- Each addon has a manifest at `addons/AddonName/addon.php`.
- Addon routes live in `addons/AddonName/routes/web.php`.
- Addon classes use the `Addons\AddonName\...` namespace and live under `addons/AddonName/app`.
- Addon views are automatically namespaced by slug, for example `view('demo::index')`.
- Addon migrations are loaded from `addons/AddonName/database/migrations`.
- The admin sidebar and dashboard read addon menus and cards from addon manifests.
- Roles control addon access through `role_addon_permissions`.
- Addons may expose public pages without login through `routes/public.php`.
- Public addon routes receive tenant resolution, addon-scope checks, and rate limiting automatically.
- `PublicAccessToken` provides secure token generation and hashing; token records and submission tables remain owned by the addon.

The current working example is `addons/Demo`.

### Public Addon Pages

An addon that needs a public form or token-based progress page can opt in from
its manifest:

```php
'scope' => 'tenant',
'public_routes' => [
    'enabled' => true,
    'route_file' => 'routes/public.php',
],
```

Create `routes/public.php` inside the addon. The file is loaded with the
`web`, `resolve.tenant`, `addon.public:{slug}`, and `throttle:public-addon`
middleware. The addon controls its own URL structure and route names. Route
names are automatically prefixed with `addon.{slug}.`.

Example:

```php
use Illuminate\Support\Facades\Route;
use Addons\Psychology\Http\Controllers\PublicIntakeController;

Route::prefix('{tenant}/psychology')->group(function (): void {
    Route::get('/intake', [PublicIntakeController::class, 'create'])->name('intake');
    Route::post('/intake', [PublicIntakeController::class, 'store'])->name('intake.store');
    Route::get('/progress/{token}', [PublicIntakeController::class, 'progress'])->name('progress');
});
```

The resulting route names are `addon.psychology.intake`,
`addon.psychology.intake.store`, and `addon.psychology.progress`. Generate
links with:

```php
addon_public_url('psychology', 'intake', ['tenant' => $tenant->slug]);
```

For token-based access, use `App\Support\PublicAccessToken`:

```php
$plainToken = app(\App\Support\PublicAccessToken::class)->issue();
$tokenHash = app(\App\Support\PublicAccessToken::class)->hash($plainToken);
```

Store only `$tokenHash` in an addon-owned table. Give the plain token to the
visitor once through the generated URL. The addon is responsible for storing
submission fields, token ownership, expiration, revocation, and authorization
of the progress response. Never put patient names or sensitive data in the
URL or use a database ID as a public credential.

## Role And User Management

- `Role Management` is available only to Super Admin.
- Super Admin can create roles, mark a role as admin-panel capable, assign addon permissions, and choose which account roles that role can create.
- `User Management` is available to admin-panel roles.
- Admin users can create accounts only with roles allowed by Super Admin.
- Super Admin can manage all accounts and all roles.

## Deploy Without Composer On Hosting

The `vendor/` folder is not committed. Every clone or deployment must run `composer install` using the committed `composer.lock`. The `.env` file is also not committed, so copy it from `.env.example` and adjust the database credentials.

After the database is created, run migrations if your hosting provides terminal access:

```bash
php artisan migrate --seed
```

If terminal access is unavailable, run migrations locally and export/import the SQL database.

## Web Editor

The admin panel includes `Web Editor` for managing the Home page. Display mode can be `Section scroll` or `Tabs`. Website content is built from sections/tabs and cards. Card designs are separated in `resources/views/website/card-templates`, so future projects can replace visual templates without changing content data.

## Feature Structure

- Public website: `WebsiteController`, `resources/views/website`.
- Login/logout: `Auth/LoginController`, `resources/views/auth`.
- Admin panel: `Admin/*Controller`, `resources/views/admin`.
- User area: `User/*Controller`, `resources/views/user`.
- Roles: `app/Models/Role.php`, `app/Enums/UserRole.php`, and admin role/user controllers.
- Addon registry: `app/Core/Addons`, `config/addons.php`, and `addons/*`.
- Automatic tables: add migrations to `database/migrations` or addon migration folders, then run `artisan migrate`.

Detailed architecture notes are in `docs/ARCHITECTURE.md`.

Production topology, Redis, queue workers, backup, and scale guidance are in `docs/NOVABASE_2_0_0_DEPLOYMENT.md`.

## Exact Runtime Requirements

This repository currently targets:

- PHP ^8.2, compatible with Laravel 12.
- Laravel Framework ^12.0. The lockfile pins the installed patch versions.
- Composer 2.x.
- Apache with mod_rewrite for the subfolder entrypoint.
- MySQL or MariaDB compatible with Laravel drivers.
- Node.js/npm only when a frontend build is added. Current Web Editor assets are static.

Do not manually install a random Laravel version. This is an existing Laravel 12 project, so use the repository lockfile.

## Installation After Vendor Removal

From a fresh clone:

    git clone <repository-url>
    cd psychology-test
    composer install --no-interaction --prefer-dist
    cp .env.example .env
    php artisan key:generate

Create the MySQL database first, then configure .env:

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=novabase
    DB_USERNAME=root
    DB_PASSWORD=

Run setup:

    php artisan migrate --seed
    php artisan storage:link

For XAMPP, open http://localhost/github/psychology-test. For normal Laravel hosting, point the document root to public/.

DB_AUTO_MIGRATE=true may be used for local development. Disable it in production and run migrations deliberately.

## Optional Tenancy Mode

NovaBase supports both single-product and multi-tenant products without changing the core code. In single mode, NovaBase uses an internal `default` tenant while keeping tenant details hidden from users.

Keep tenancy disabled for a normal single-product installation:

    NOVABASE_TENANCY_ENABLED=false
    NOVABASE_TENANCY_DRIVER=single
    NOVABASE_DEFAULT_TENANT_SLUG=default

Enable path-based tenants when one installation serves multiple organizations:

    NOVABASE_TENANCY_ENABLED=true
    NOVABASE_TENANCY_DRIVER=path

Tenant URLs then use database slugs, for example `/gbi`, `/gbi/login`, and `/gbi/admin`. The `/gbi` path is a dynamic Laravel route, not a physical folder or source-code directory. Creating a tenant only inserts database records.

After changing tenancy configuration, clear cached configuration:

    php artisan config:clear
    php artisan cache:clear

The Demo addon provides a platform-only tenant management screen at `Admin Panel > Demo Addon > Manage Demo Tenants`. The platform Super Admin can create and inspect tenants. A tenant owner uses the tenant URL and receives the tenant-scoped `super_admin` role; this is not the platform Super Admin and cannot see platform-only addon screens.

## Deployment Growth Path

NovaBase can be deployed in three stages. The application and tenant model do
not need to change when moving from one stage to the next; the infrastructure
and environment configuration become stronger as traffic grows.

### Stage A: Shared Hosting

This is suitable for an initial product, a small-to-medium number of users,
and one application server.

```text
One hosting account
  - psychology.example.com -> one NovaBase installation and database
  - gerejahub.com          -> another NovaBase installation and database
  - other-product.com      -> another NovaBase installation and database
```

For one multi-tenant product, all tenants can remain inside one installation:

```text
gerejahub.com
  - tenant_id = 1 (GBI)
  - tenant_id = 2 (HKBP)
  - tenant_id = 3 (GPDI)
```

Required:

- PHP 8.2 or newer, Composer, Apache or a compatible web server, and MySQL/MariaDB.
- One database per product installation.
- The document root should point to `public/`. The repository also includes a
  subfolder entrypoint for hosting environments where the document root cannot
  be changed.
- A writable `storage/` and `bootstrap/cache/` directory.
- `.env` configured with the hosting database credentials.
- HTTPS enabled for login and session security.
- Scheduled backups from the hosting control panel or an external backup job.

Recommended local/shared-hosting environment:

```dotenv
APP_ENV=production
APP_DEBUG=false
DB_AUTO_MIGRATE=false
DB_AUTO_SEED=false
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=database
```

Deployment commands:

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
php artisan migrate --seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Do not enable request-time auto migration in production. Run migrations once
from a controlled deployment process. Shared hosting can run NovaBase, but it
usually cannot provide a load balancer, a permanent queue worker, Redis with
full administration access, read replicas, or autoscaling.

### Stage B: Single VPS

Move to a VPS when the product needs more control, queue workers, Redis, or
more predictable resources. A single VPS is still one application server, but
it can run all supporting services:

```text
One VPS
  - Nginx or Apache + PHP-FPM
  - NovaBase Laravel application
  - MySQL primary
  - Redis
  - Supervisor or systemd queue worker
  - Cron
  - Local or external backup target
```

Required configuration:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://example.com
DB_AUTO_MIGRATE=false
DB_AUTO_SEED=false
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
```

The VPS must also provide:

- PHP-FPM with the extensions required by Laravel and the project.
- MySQL with regular backups and a least-privilege database user.
- Redis protected by a password or private network rules.
- A queue worker managed by Supervisor or systemd.
- A cron entry for scheduled Laravel tasks.
- TLS, firewall rules, log rotation, disk monitoring, and swap where needed.

Example queue worker command:

```bash
php artisan queue:work redis --sleep=3 --tries=3 --timeout=120
```

The worker must be restarted after a deployment so it loads the new code:

```bash
php artisan queue:restart
```

Run production deployment in this order:

1. Put the release code on the VPS.
2. Run `composer install --no-dev --optimize-autoloader`.
3. Run `php artisan migrate --force` once.
4. Run `php artisan optimize` or the individual cache commands.
5. Restart queue workers.
6. Verify the health endpoint, login, database connection, and queue status.

### Stage C: Multi-Server Production

Use this stage when one VPS cannot handle the traffic or uptime requirement.
The application remains stateless at the web-server layer:

```text
                    +----------------+
Users -> HTTPS ->   | Load Balancer  |
                    +--------+-------+
                             |
                 +-----------+-----------+
                 |                       |
          App Server 1             App Server 2
                 |                       |
                 +-----------+-----------+
                             |
             +---------------+----------------+
             |                                |
       Shared Redis                    MySQL Primary
             |                                |
       Queue workers                    Read Replica(s)
```

Every application server must use the same:

- `APP_KEY`.
- Database primary for writes.
- Redis for sessions, cache, rate limiting, and queues.
- Application release version.
- Addon code and configuration.

Example multi-server environment contract:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://example.com
APP_KEY=the-same-key-on-every-app-server
DB_AUTO_MIGRATE=false
DB_AUTO_SEED=false

# Shared session and cache services
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=private-redis-host
REDIS_PASSWORD=strong-password

# Primary database for writes and consistency-sensitive reads
DB_HOST=private-mysql-primary
DB_DATABASE=novabase
DB_USERNAME=novabase_app
DB_PASSWORD=strong-password
```

Additional production services:

- Load balancer with TLS termination, health checks, and sticky-session-free
  routing.
- Shared object storage such as S3-compatible storage for user uploads. Do not
  rely on `storage/app` on one app server when uploads must be visible on all
  servers.
- Redis with memory limits, persistence policy, private networking, and
  monitoring.
- MySQL primary with tested backups and point-in-time recovery.
- Optional read replicas for queries where replica lag is acceptable.
- Queue workers running from a controlled worker pool, separate from web
  traffic where possible.
- Centralized logs, metrics, alerts, and deployment rollback capability.

### Database Read and Write Rules

The primary database remains the source of truth for:

- Login and account status checks.
- Tenant membership and role changes.
- Permission changes.
- Web Editor saves.
- Tenant creation and provisioning.
- Any transaction that must be immediately consistent.

Read replicas may later handle reporting, public read-heavy pages, and other
queries where a short replication delay is acceptable. Read replicas are not
required for the first VPS deployment and should only be added after slow
query and database load measurements justify them.

### Migration Rules for All Stages

- Commit migrations together with the code that uses them.
- Run migrations once from one deployment runner, never concurrently from all
  application servers.
- Set `DB_AUTO_MIGRATE=false` in production.
- Take a tested backup before destructive or large migrations.
- Deploy additive schema changes before code that requires them.
- Keep migrations compatible with the previous application version during a
  rolling deployment.

### What NovaBase Provides and What Hosting Provides

NovaBase provides the Laravel application, tenant isolation, migrations,
cache/queue configuration points, cursor pagination, page cache, permission
cache, and deployment documentation. The hosting or cloud environment must
provide the servers, DNS, TLS, load balancer, Redis service, MySQL service,
backups, object storage, monitoring, and autoscaling.

Docker images, CI/CD workflows, health-check endpoints, Supervisor files,
read/write database connections, and object-storage adapters can be added as a
separate deployment package when the project moves from Stage A to Stage B or
Stage C. They are not prerequisites for using NovaBase on shared hosting.

The complete contract is documented in `docs/NOVABASE_TENANCY.md`.

The Web Editor save endpoint uses `POST` instead of `PUT` so it works through Apache/XAMPP and shared hosting configurations that reject PUT requests before Laravel receives them.
The editor script URL includes a file version so browsers do not keep using an older cached `PUT` implementation after an update.

## Addon Development

An addon lives in addons/{AddonName} and can contain:

    addon.php
    app/
    routes/web.php
    resources/views/
    database/migrations/
    public/

Register the addon in config/addons.php. The manifest can provide admin_menu, permissions, dashboard_cards, web_editor.blocks, routes, views, and migrations.

### Add a Web Editor Block

A block definition returns its namespaced type, label, defaults, inspector fields, and renderer. The Demo example is:

- Manifest: addons/Demo/addon.php
- Definition: addons/Demo/app/PageBuilder/DemoPromoBlock.php
- Type: demo.promo-card
- Renderer: shared core addon-card renderer

The manifest registration has this shape:

    'web_editor' => [
        'blocks' => [
            [
                'type' => 'demo.promo-card',
                'permission' => 'demo.view',
                'definition' => Addons\\Demo\\PageBuilder\\DemoPromoBlock::class,
            ],
        ],
    ],

Use a unique namespaced type such as billing.invoice-cta or church-events.upcoming-events. The definition defaults become initial block data, fields become inspector inputs, and the backend validates the same definition.

### Addon Rules

- Keep business logic inside the addon folder.
- Use permission keys for every admin capability.
- Use namespaced block types.
- Validate all block data on the backend.
- Add fallback output when an addon block is unavailable.
- Add tenant_id to addon tables after multi-tenant NovaBase is implemented.
- Add tests for registration, permission denial, validation, and public rendering.

Detailed decisions are in docs/ADDON_WEB_EDITOR_ARCHITECTURE.md and MANAGEMENT_GEREJA.MD.
