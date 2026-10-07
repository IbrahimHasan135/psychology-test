# Architecture Notes

Base ini memakai Laravel MVC standar supaya mudah dibaca walaupun latar utama bukan web engineering.

## Lapisan Utama

- `routes/web.php`: peta URL. Tambahkan route fitur baru di sini atau pecah ke route file lain kalau modul sudah besar.
- `app/Http/Controllers`: menerima request, validasi ringan, memilih service/model, lalu mengembalikan view atau redirect.
- `app/Http/Middleware`: penjaga akses sebelum controller. Saat ini ada `EnsureUserHasRole` untuk Super Admin, Admin, dan User.
- `app/Models`: representasi tabel database. `User` sudah punya helper role dan redirect dashboard.
- `app/Enums`: kontrak nilai tetap seperti role akun agar string tidak tercecer di banyak file.
- `database/migrations`: definisi tabel. Untuk bikin tabel otomatis, buat migration baru lalu jalankan `php artisan migrate`.
- `database/seeders`: data awal untuk local/dev, termasuk akun default.
- `resources/views`: Blade template untuk tampilan website, auth, admin, dan user.

## Role Awal

- `super_admin`: akses admin panel penuh.
- `admin`: akses admin panel operasional.
- `user`: akses dashboard user.

## Pola Menambah Fitur

1. Buat migration untuk tabel fitur.
2. Buat model di `app/Models`.
3. Buat controller sesuai area: `Admin`, `User`, atau controller publik.
4. Tambahkan route dengan middleware yang sesuai.
5. Tambahkan Blade view di folder area yang sama.
6. Tambahkan seeder kalau butuh data awal local.

## Database Local XAMPP

Konfigurasi default diarahkan ke MySQL XAMPP:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=psychology_test
DB_USERNAME=root
DB_PASSWORD=
```

Buat database `psychology_test` di phpMyAdmin atau MySQL CLI, lalu jalankan migration dan seeder.
## Website Page Builder

Modul website builder dipisahkan antara data konten dan template desain.

- `site_pages`: page utama, saat ini seed default membuat `home`.
- `site_sections`: section atau tab di dalam page. Mode page menentukan apakah tampil sebagai scroll section atau tab anchor.
- `site_cards`: unit konten di dalam section. Card menyimpan template, judul, teks, URL gambar, posisi gambar, tombol, urutan, dan status aktif.
- `resources/views/website/card-templates`: tempat desain Blade untuk tiap template card. Tambah template baru di sini, lalu daftarkan key-nya di `App\Models\SiteCard::TEMPLATES`.
- `resources/views/website/home.blade.php`: renderer publik yang membaca data dari database.
- `app/Http/Controllers/Admin/PageManagementController.php`: fitur admin untuk mengatur page, section/tab, dan card.

Pola next feature: jangan campur logika konten dengan layout visual. Data tetap di model/tabel; desain tampilan tetap di Blade partial template.