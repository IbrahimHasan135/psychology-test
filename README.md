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

## Deploy Without Composer On Hosting

The `vendor/` folder is intentionally committed so cloned/uploaded hosting copies can find Laravel dependencies immediately. The `.env` file is still not committed, so copy it from `.env.example` and adjust hosting database credentials.

After the database is created, run migrations if your hosting provides terminal access:

```bash
php artisan migrate --seed
```

If terminal access is unavailable, run migrations locally and export/import the SQL database.

## Page Management

The admin panel includes `Page Management` for managing the Home page. Display mode can be `Section scroll` or `Tabs`. Website content is built from sections/tabs and cards. Card designs are separated in `resources/views/website/card-templates`, so future projects can replace visual templates without changing content data.

## Feature Structure

- Public website: `WebsiteController`, `resources/views/website`.
- Login/logout: `Auth/LoginController`, `resources/views/auth`.
- Admin panel: `Admin/*Controller`, `resources/views/admin`.
- User area: `User/*Controller`, `resources/views/user`.
- Roles: `app/Enums/UserRole.php` and `app/Http/Middleware/EnsureUserHasRole.php`.
- Addon registry: `app/Core/Addons`, `config/addons.php`, and `addons/*`.
- Automatic tables: add migrations to `database/migrations` or addon migration folders, then run `artisan migrate`.

Detailed architecture notes are in `docs/ARCHITECTURE.md`.
