# NovaBase

Base Laravel hijau untuk produk Novalynk: website utama, login, admin panel, akun, role, page management, dan folder addon untuk fitur produk berikutnya.

## Setup Local XAMPP

1. Buat database MySQL bernama `novabase` atau sesuaikan di `.env`.
2. Pastikan `.env` mengarah ke database local:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=novabase
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

## Auto Create/Update Table

`DB_AUTO_MIGRATE=true` membuat Laravel menjalankan migration otomatis saat website diakses. Ini akan membuat tabel yang belum ada dan menjalankan perubahan tabel yang sudah ditulis di `database/migrations`. `DB_AUTO_SEED=true` memastikan data awal wajib, termasuk akun Super Admin, otomatis diisi kalau belum ada.

Catatan penting: database MySQL-nya tetap harus dibuat dulu, misalnya `novabase`. Laravel migration mengurus tabel di dalam database, bukan membuat database MySQL baru.

Kalau di hosting production ingin lebih aman, ubah ke:

```env
DB_AUTO_MIGRATE=false
```

Lalu jalankan migration manual dari terminal atau import SQL.
## Akun Default

| Role | Login | Password |
| --- | --- | --- |
| Super Admin | `novalynk.superadmin` | `N0v4.lynk` |
| Admin | `admin` | `password` |
| User | `user` | `password` |

Seeder akan memperbarui akun default jika sudah ada, jadi perubahan password Super Admin ikut diterapkan saat `DB_AUTO_SEED=true` atau saat menjalankan `php artisan db:seed`.

## Addon System

NovaBase sudah punya fondasi addon seperti konsep NovaStore, tetapi memakai pola Laravel.

- Daftar addon aktif ada di `config/addons.php`.
- Setiap addon punya manifest `addons/NamaAddon/addon.php`.
- Route addon ada di `addons/NamaAddon/routes/web.php`.
- Class addon memakai namespace `Addons\NamaAddon\...` dan file-nya berada di `addons/NamaAddon/app`.
- View addon otomatis bisa dipanggil dengan namespace slug, contoh `view('demo::index')`.
- Migration addon otomatis dibaca dari `addons/NamaAddon/database/migrations`.
- Sidebar admin dan dashboard membaca menu/card dari manifest addon.
- Role mempengaruhi akses addon lewat tabel `role_addon_permissions`.

Contoh aktif saat ini ada di `addons/Demo`.

## Deploy Tanpa Composer di Hosting

Folder `vendor/` sengaja ikut Git agar setelah clone/upload hosting bisa langsung menemukan dependency Laravel. Yang tetap tidak ikut Git adalah `.env`, jadi copy dari `.env.example` lalu sesuaikan database hosting.

Setelah database dibuat, jalankan migration jika hosting menyediakan terminal:

```bash
php artisan migrate --seed
```

Kalau hosting tidak punya terminal, jalankan migration di local lalu export/import SQL ke database hosting.

## Page Management

Admin panel punya menu `Page Management` untuk mengatur Home page. Mode tampilannya bisa `Section scroll` atau `Tabs`. Isi website dibangun dari section/tab dan card. Desain card dipisahkan di `resources/views/website/card-templates`, sehingga next project bisa mengganti template visual tanpa mengubah data konten.

## Struktur Fitur

- Website utama: `WebsiteController`, `resources/views/website`.
- Login/logout: `Auth/LoginController`, `resources/views/auth`.
- Admin panel: `Admin/*Controller`, `resources/views/admin`.
- User area: `User/*Controller`, `resources/views/user`.
- Role: `app/Enums/UserRole.php` dan `app/Http/Middleware/EnsureUserHasRole.php`.
- Addon registry: `app/Core/Addons`, `config/addons.php`, dan `addons/*`.
- Tabel otomatis: tambah migration di `database/migrations`, lalu jalankan `artisan migrate`.

Catatan arsitektur lebih lengkap ada di `docs/ARCHITECTURE.md`.
