# Catatan perubahan Card dan Template Website Editor

Wajib diperbarui setiap pekerjaan. Pisahkan perubahan yang sudah dilakukan dari usulan/handoff. Jangan menghapus histori. Jangan masukkan credential atau data pengguna.

## 2026-10-10 - Aturan dan scope branch

Status: SELESAI DOKUMENTASI.

Branch teramati: `feat/website-editor-frontend` melalui `.git/HEAD`.

| File | Perubahan | Alasan/dampak |
| --- | --- | --- |
| `AGENTS.md` | Menambahkan aturan pelaksana, batas Card/Template, kewajiban warning core dan changelog | Agar developer/agent berikutnya membaca batas kerja dari root repo |
| `docs/WEBSITE_EDITOR_DEVELOPMENT_SCOPE.md` | Mendokumentasikan analisis jalur aktif/legacy, daftar target, area dilindungi, handoff dan verifikasi | Mencegah pelebaran scope dan perubahan base code tanpa penugasan terpisah |
| `docs/WEBSITE_EDITOR_CHANGELOG.md` | Membuat rekam perubahan dan format untuk task berikutnya | Menyediakan jejak perubahan yang dapat direview |

Kode aplikasi, Card, Template, database, dependency dan konfigurasi tidak diubah pada pekerjaan ini.

Verifikasi: membaca struktur repo, sumber editor, registry backend, controller validasi, route, view publik, dan test page management; memastikan path dalam scope tersedia. Tidak menjalankan test runtime karena perubahan hanya dokumentasi. Git CLI tidak tersedia di PATH sesi ini sehingga status/diff Git tidak dapat diverifikasi; identitas branch dibaca dari `.git/HEAD`.

Warning/handoff: ID Template baru dan perluasan schema/jenis Card berpotensi memerlukan registrasi/validasi core. Ini temuan batas arsitektur, bukan permintaan core yang sedang dieksekusi. Belum ada task implementasi atau perubahan core yang diajukan.

## 2026-10-10 - Instalasi dependency dan runtime lokal

Status: SELESAI SETUP LOKAL.

Otorisasi: pemilik repo secara eksplisit meminta pemasangan seluruh dependency sesuai versi Laravel repo agar aplikasi dapat langsung dijalankan. Pengecualian ini khusus setup; scope fitur tetap Card/Template.

| File/area | Perubahan aktual | Alasan/dampak |
| --- | --- | --- |
| `composer.phar`, `vendor/` (diabaikan Git) | Composer 2.10.3 dan 111 package PHP termasuk dev dependency dipasang dari composer.lock | Laravel 12.69.3 berjalan dengan PHP XAMPP 8.2.12; tidak menjalankan composer update |
| `.local-tools/node/` (diabaikan Git) | Node.js 22.23.3 portable dan npm 10.9.9 | Runtime frontend lokal tanpa mengubah PATH Windows |
| `node_modules/`, `package-lock.json` | 87 package frontend dipasang, lockfile frontend dibuat | Instalasi frontend berikutnya dapat memakai npm ci |
| `public/build/` (diabaikan Git) | Build Vite 7.3.7 menghasilkan manifest dan aset | Entry frontend repo siap dipakai |
| `.env` (diabaikan Git) | Konfigurasi lokal dibuat, APP_KEY dihasilkan, APP_URL memakai http://127.0.0.1:8001, auto migration/seed dinonaktifkan | Aplikasi lokal siap tanpa perubahan database otomatis saat akses |
| Database MySQL `novabase` | Database yang sebelumnya tidak ada dibuat; 23 migration existing dan seeder dijalankan | Tabel, halaman Home dan akun default lokal tersedia |
| `public/storage` (diabaikan Git) | Storage link dibuat oleh artisan | Upload publik memakai struktur Laravel existing |
| `.gitignore` | Menambahkan pengecualian /.local-tools | Binary runtime lokal tidak ikut commit |
| `dev-shell.cmd` | Helper terminal dengan PATH PHP/Node/npm lokal | Menghindari kebijakan Windows yang memblokir script PowerShell |
| `docs/LOCAL_DEVELOPMENT_SETUP.md` | Panduan menjalankan, instalasi ulang, database dan verifikasi | Handoff setup ke developer berikutnya |
| `docs/WEBSITE_EDITOR_CHANGELOG.md` | Mencatat setup dan hasil aktual | Jejak pekerjaan |

Verifikasi aktual:

- Composer install selesai; package discovery berhasil; Laravel melaporkan 12.69.3.
- Composer check-platform-reqs: seluruh requirement lolos.
- Node melaporkan 22.23.3 dan npm 10.9.9; npm install selesai.
- npm run build berhasil, 58 modul diproses. Percobaan sandbox awal terkena spawn EPERM; pengulangan di luar sandbox berhasil tanpa mengubah config aplikasi.
- PageManagementTest: 5 test lolos, 16 assertion; database test memakai SQLite memory sesuai phpunit.xml.
- migrate:status: seluruh 23 migration berstatus Ran.
- HTTP server port 8001: Home 200, Login 200, editor tanpa login 302. Interaksi visual Card/Template di browser belum diuji karena tidak ada perubahan fitur.
- Installer Composer dan arsip Node diverifikasi terhadap checksum resmi.

Proses lokal: MySQL XAMPP dinyalakan dan Laravel dev server berjalan di 127.0.0.1:8001. Server percobaan port 8000 yang timeout sudah dihentikan. Setelah restart mesin, nyalakan MySQL melalui XAMPP dan jalankan dev server sesuai panduan.

Warning core: tidak ada perubahan source core, route, schema migration, dependency manifest, atau aturan validasi. Migrasi/seeder existing hanya dijalankan untuk database baru pada setup yang diminta. Pemeriksaan Git diff belum tersedia karena Git tidak terpasang di PATH sesi.

Rollback setup: hentikan proses server lokal milik setup; folder generated yang diabaikan Git dapat dibersihkan secara selektif. Jangan menghapus database atau `.env` setelah dipakai developer tanpa meninjau data dan membuat backup. Jangan gunakan reset repo yang menghapus pekerjaan lain.

## 2026-10-11 - Commit dan push wajib setiap task/prompt

Status: ATURAN SELESAI; hasil push dilaporkan pada respons akhir setelah verifikasi remote.

Permintaan: pemilik repo mengotorisasi commit dan push otomatis setiap selesai satu prompt agar developer junior tidak lupa menyimpan setiap perubahan.

| File | Perubahan | Dampak |
| --- | --- | --- |
| `AGENTS.md` | Mewajibkan commit/push per task, staging terpilih, verifikasi SHA remote, pelaporan kegagalan; mengganti larangan push lama dengan otorisasi eksplisit | Developer/agent tidak perlu meminta izin push lagi ke branch kerja origin |
| `docs/WEBSITE_EDITOR_DEVELOPMENT_SCOPE.md` | Menambahkan commit dan push sebagai kriteria selesai | Scope Card/Template dan perlindungan core tetap berlaku |
| `docs/WEBSITE_EDITOR_CHANGELOG.md` | Mencatat aturan baru dan menambahkan kolom pelaporan commit/push pada format entri | Handoff dapat membedakan commit lokal dan push berhasil |

Branch aktual menurut Git: `feat/web-editor-frontend`. Ini memperbarui referensi historis `feat/website-editor-frontend` dalam analisis awal. Tujuan origin: `muhaldianmaharani/novabase-product`; upstream bukan tujuan push otomatis.

Verifikasi: Git ditemukan pada instalasi GitHub Desktop; status dan diff ditinjau, git diff --check lolos. Perubahan setup sesi sebelumnya disimpan terpisah pada commit `cb51d45`. Perubahan task ini hanya dokumentasi; tidak membutuhkan pengulangan test runtime. SHA commit aturan dan hasil verifikasi SHA remote disampaikan di laporan akhir supaya tidak membuat commit tambahan hanya untuk mencatat SHA commit itu sendiri.

Warning core: tidak ada. Larangan force push dan push otomatis ke main/master/upstream tetap berlaku. Jika akses push gagal, commit lokal dipertahankan dan kendala dilaporkan.

## Format entri pekerjaan berikutnya

```markdown
## YYYY-MM-DD - Judul task Card/Template

Status: SELESAI / PARSIAL / PENDING CORE
Pelaksana:
Branch dan referensi commit (jika tersedia):
Commit/push: branch tujuan, hasil push, SHA lokal/remote (boleh dirujuk ke laporan akhir).
Permintaan/tujuan:

| File dan bagian/fungsi | Perubahan aktual sebelum -> sesudah | Alasan/dampak |
| --- | --- | --- |
| ... | ... | ... |

Kontrak data/ID: tetap / detail kebutuhan handoff.
Dampak editor, preview, publik, dan data existing:
Pemeriksaan aktual dan hasil:
Pemeriksaan belum dijalankan dan alasannya:
Warning core/temuan di luar scope: tidak ada / rincian dengan format scope.
Handoff: kebutuhan, penanggung jawab yang dituju, dependensi, kriteria penerimaan.
Bagian belum selesai:
Cara rollback perubahan task ini tanpa menimpa pekerjaan lain:
```
