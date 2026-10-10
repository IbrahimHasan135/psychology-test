# Setup lokal Windows / XAMPP

Setup ini dilakukan atas permintaan eksplisit pemilik repo untuk memasang dependency. Scope pengembangan fitur tetap Card dan Template sebagaimana `AGENTS.md`.

## Runtime

- PHP XAMPP: `C:\xampp3\php\php.exe`, versi 8.2.12.
- Laravel: 12.69.3, dipasang dari `composer.lock` dengan `install`, bukan `update`.
- Composer lokal: `composer.phar` di root repo; installer diverifikasi dengan checksum SHA-384 resmi.
- Node.js portable: 22.23.3 LTS di `.local-tools/node`; arsip diverifikasi dengan checksum SHA-256 resmi. Folder ini diabaikan Git dan tidak mengubah instalasi sistem.
- Dependency PHP ada di `vendor/`; dependency frontend di `node_modules/`. `package-lock.json` mengunci hasil instalasi frontend.

## Menjalankan aplikasi

Dari PowerShell di root repo, tambahkan runtime ke PATH terminal aktif:

```powershell
$env:PATH = "$PWD\.local-tools\node;C:\xampp3\php;$env:PATH"
php artisan serve --host=127.0.0.1 --port=8001
```

Buka `http://127.0.0.1:8001/login`, lalu Website Editor di `http://127.0.0.1:8001/admin/pages`. Jika port 8001 sudah dipakai server dari sesi setup, langsung buka URL tersebut atau pakai port lain. MySQL XAMPP harus aktif pada port 3306; setelah restart Windows, nyalakan melalui XAMPP Control Panel.

Login lokal bawaan seeder untuk pengembangan: username `admin`, password `password`. Akun bawaan ini hanya untuk lingkungan lokal.

Alternatif Apache XAMPP: nyalakan Apache dan MySQL, lalu buka `http://localhost/xampp/novabase-product`. `.env` lokal memakai URL dev server `http://127.0.0.1:8001`. Jika memakai Apache secara tetap, sesuaikan `APP_URL` lokal dengan URL Apache tersebut.

Alternatif: klik dua kali `dev-shell.cmd` untuk membuka CMD yang sudah mengenali PHP, Node, dan npm. Helper hanya mengatur PATH terminal tersebut, tanpa mengubah PATH Windows. Kebijakan PowerShell mesin ini memblokir `.ps1`, sehingga helper memakai `.cmd`. Executable PHP juga dapat dipanggil langsung:

```powershell
& C:\xampp3\php\php.exe artisan serve --host=127.0.0.1 --port=8001
```

## Dependency dan frontend

```powershell
$env:PATH = "$PWD\.local-tools\node;C:\xampp3\php;$env:PATH"
php -d extension=zip composer.phar install --no-interaction --prefer-dist
npm.cmd ci
npm.cmd run build
```

Website Editor memakai aset statis di `public/js/page-builder` dan `public/css/page-builder`; perubahan di sana tidak membutuhkan Vite. Dependency/build Vite tetap disiapkan untuk entrypoint frontend repo. Gunakan `npm.cmd run dev` jika sedang mengerjakan entrypoint Vite.

Ekstensi ZIP PHP diaktifkan hanya untuk perintah Composer melalui `-d extension=zip`; `php.ini` XAMPP tidak diubah.

## Database dan konfigurasi

`.env` dibuat dari `.env.example` dan APP_KEY lokal sudah dihasilkan. MySQL `novabase` dibuat setelah pemeriksaan memastikan database belum ada, lalu diinisialisasi dengan migration/seeder repo. Otomatis migrasi dan seeding di `.env` lokal dimatikan agar membuka halaman tidak mengubah schema atau mereset akun.

Untuk migration baru yang memang sudah diotorisasi, jalankan `php artisan migrate` secara sengaja. Jangan menjalankan `migrate:fresh` atau mengulang seeder pada database berisi pekerjaan tanpa meninjau dampaknya. Jangan commit `.env`, APP_KEY, vendor, runtime lokal atau node_modules.

## Verifikasi

```powershell
$env:PATH = "$PWD\.local-tools\node;C:\xampp3\php;$env:PATH"
php artisan --version
php composer.phar check-platform-reqs
php artisan test --filter=PageManagementTest
npm.cmd run build
```

Hasil aktual sesi setup dicatat di `WEBSITE_EDITOR_CHANGELOG.md`. Setup tidak mengubah base code aplikasi NovaBase.
