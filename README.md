# Psychology Test

Base Laravel untuk website utama, login, admin panel, akun, dan role.

## Setup Local XAMPP

1. Buat database MySQL bernama `psychology_test`.
2. Pastikan `.env` mengarah ke database local:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=psychology_test
DB_USERNAME=root
DB_PASSWORD=
```

3. Jalankan migration dan seeder:

```bash
/opt/lampp/bin/php artisan migrate --seed
```

4. Akses lewat Apache tanpa `/public`:

```text
http://localhost/github/psychology-test
```

Root project sudah punya `index.php` dan `.htaccess` agar bisa dipakai di shared hosting/subfolder. Mode paling ideal tetap mengarahkan document root hosting ke folder `public/` kalau panel hosting mendukungnya.

Atau lewat Laravel dev server:

```bash
/opt/lampp/bin/php artisan serve
```

## Akun Default

Semua akun default memakai password `password`.

| Role | Email |
| --- | --- |
| Super Admin | `superadmin@example.com` |
| Admin | `admin@example.com` |
| User | `user@example.com` |

## Deploy Tanpa Composer di Hosting

Folder `vendor/` sengaja ikut Git agar setelah clone/upload hosting bisa langsung menemukan dependency Laravel. Yang tetap tidak ikut Git adalah `.env`, jadi copy dari `.env.example` lalu sesuaikan database hosting.

Setelah database dibuat, jalankan migration jika hosting menyediakan terminal:

```bash
php artisan migrate --seed
```

Kalau hosting tidak punya terminal, jalankan migration di local lalu export/import SQL ke database hosting.

## Struktur Fitur

- Website utama: `WebsiteController`, `resources/views/website`.
- Login/logout: `Auth/LoginController`, `resources/views/auth`.
- Admin panel: `Admin/*Controller`, `resources/views/admin`.
- User area: `User/*Controller`, `resources/views/user`.
- Role: `app/Enums/UserRole.php` dan `app/Http/Middleware/EnsureUserHasRole.php`.
- Tabel otomatis: tambah migration di `database/migrations`, lalu jalankan `artisan migrate`.

Catatan arsitektur lebih lengkap ada di `docs/ARCHITECTURE.md`.