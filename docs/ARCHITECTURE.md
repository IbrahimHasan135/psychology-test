# NovaBase Architecture Notes

NovaBase memakai Laravel MVC standar supaya setiap produk Novalynk bisa dimulai dari pondasi yang sama dan fitur baru tinggal masuk sebagai addon atau modul admin.

## Lapisan Utama

- `routes/web.php`: peta URL. Tambahkan route fitur baru di sini atau pecah ke route file lain kalau modul sudah besar.
- `app/Http/Controllers`: menerima request, validasi ringan, memilih service/model, lalu mengembalikan view atau redirect.
- `app/Http/Middleware`: penjaga akses sebelum controller. Saat ini ada `EnsureUserHasRole` untuk Super Admin, Admin, dan User.
- `app/Models`: representasi tabel database. `User` sudah punya helper role dan redirect dashboard.
- `app/Enums`: kontrak nilai tetap seperti role akun agar string tidak tercecer di banyak file.
- `app/Core/Addons`: registry dan metadata addon. Core membaca manifest addon dari `config/addons.php`.
- `database/migrations`: definisi tabel. Untuk bikin tabel otomatis, buat migration baru lalu jalankan `php artisan migrate`.
- `database/seeders`: data awal untuk local/dev, termasuk akun default.
- `resources/views`: Blade template untuk tampilan website, auth, admin, dan user.
- `addons`: fitur produk modular. Setiap addon punya manifest, route, view, dan class sendiri.

## Role Awal

- `super_admin`: akses admin panel penuh.
- `admin`: akses admin panel operasional.
- `user`: akses dashboard user.

## Pola Menambah Fitur

Untuk fitur core:

1. Buat migration untuk tabel fitur.
2. Buat model di `app/Models`.
3. Buat controller sesuai area: `Admin`, `User`, atau controller publik.
4. Tambahkan route dengan middleware yang sesuai.
5. Tambahkan Blade view di folder area yang sama.
6. Tambahkan seeder kalau butuh data awal local.

Untuk fitur produk/addon:

1. Buat folder `addons/NamaAddon`.
2. Buat manifest `addon.php`.
3. Daftarkan route di `routes/web.php`.
4. Buat class di `app` dengan namespace `Addons\NamaAddon`.
5. Buat view di `resources/views`.
6. Daftarkan addon di `config/addons.php`.
7. Tambahkan permission addon ke seeder atau UI role.

## Database Local XAMPP

Konfigurasi default diarahkan ke MySQL XAMPP:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=novabase
DB_USERNAME=root
DB_PASSWORD=
```

Buat database `novabase` di phpMyAdmin atau MySQL CLI, lalu jalankan migration dan seeder.

## Website Page Builder

Modul website builder dipisahkan antara data konten dan template desain.

- `site_pages`: page utama, saat ini seed default membuat `home`.
- `site_sections`: section atau tab di dalam page. Mode page menentukan apakah tampil sebagai scroll section atau tab anchor.
- `site_cards`: unit konten di dalam section. Card menyimpan template, judul, teks, URL gambar, posisi gambar, tombol, urutan, dan status aktif.
- `resources/views/website/card-templates`: tempat desain Blade untuk tiap template card. Tambah template baru di sini, lalu daftarkan key-nya di `App\Models\SiteCard::TEMPLATES`.
- `resources/views/website/home.blade.php`: renderer publik yang membaca data dari database.
- `app/Http/Controllers/Admin/PageManagementController.php`: fitur admin untuk mengatur page, section/tab, dan card.

Pola next feature: jangan campur logika konten dengan layout visual. Data tetap di model/tabel; desain tampilan tetap di Blade partial template.

## Addon System

NovaBase mengadopsi konsep modular NovaStore dengan pola Laravel-native.

- `config/addons.php`: daftar addon aktif.
- `app/Core/Addons/AddonRegistry.php`: membaca manifest addon, menu admin, permission, dashboard card, route, migration, dan view namespace.
- `app/Providers/AddonServiceProvider.php`: mendaftarkan registry, autoloader addon, view namespace, migration path, dan route addon.
- `role_addon_permissions`: tabel permission addon per role.
- `permission` middleware: guard route berdasarkan permission addon.
- `resources/views/admin/partials/sidebar.blade.php`: sidebar membaca menu addon dan mengelompokkan per addon.
- `resources/views/admin/dashboard.blade.php`: dashboard membaca card dari addon aktif.

Contoh implementasi ada di `addons/Demo` dengan permission `demo.view`.
