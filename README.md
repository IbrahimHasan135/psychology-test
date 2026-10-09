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

The current working example is `addons/Demo`.

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
