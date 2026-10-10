# Scope develop Card dan Template Website Editor

Tanggal analisis: 2026-10-10 (Asia/Jakarta). Branch menurut `.git/HEAD`: `feat/website-editor-frontend`.

## Tujuan

Mengembangkan tampilan Card dan Template di Website Editor, termasuk kontrol edit yang langsung berkaitan, preview, responsivitas, dan hasil render publiknya. Dokumen ini adalah batas kerja pelaksana berikutnya; bukan perintah untuk mengimplementasikan seluruh fitur sekarang.

Card berarti Card Grid (`type: cards`) beserta itemnya, atau card addon yang secara eksplisit disebut dalam task. Dashboard card, pricing, testimonial, signup tenant, serta fitur bisnis addon tidak otomatis masuk scope karena berbentuk kartu.

Template berarti tema desain Website Editor. Lima ID saat ini: `template-studio`, `template-bold`, `template-editorial`, `template-neon`, `template-earth`. Template halaman berisi preset konten belum dianggap sebagai fitur yang tersedia; jika diminta, rancang hanya menggunakan kontrak state yang ada dan eskalasikan kebutuhan kontrak baru.

## Temuan repo dan alur aktif

- Laravel 12 / PHP ^8.2; frontend editor memakai JavaScript ES modules dan CSS statis. Jangan menambahkan build system hanya untuk desain.
- `resources/views/admin/pages/editor.blade.php` menyediakan canvas, inspector, daftar template, serta data server `NOVABASE_BLOCK_DEFINITIONS` dan `NOVABASE_DESIGN_TEMPLATES`.
- `app/Core/PageBuilder/BlockRegistry.php` menyimpan definisi/default block dan daftar template backend. `PageBuilderRegistry.php` menggabungkan block core dengan definisi addon.
- `public/js/page-builder/editor/blockFactory.js` memakai default server saat tersedia; default frontend merupakan fallback. Mengubah fallback saja tidak mengubah default pada runtime normal.
- `siteRenderer.js` merender editor dan dipakai jalur publik melalui `public-renderer.js`. Perubahan render perlu diperiksa di kedua konteks.
- `PageManagementController.php` memvalidasi ID template, jenis block, dan field data; `PageBuilderService.php` mengurus state/persistensi. Save editor aktif memakai POST, sesuai `routes/web.php` dan `editor.js`.
- Card Grid aktif berbeda dari `SiteCard` dan Blade `resources/views/website/card-templates/*.blade.php`. Jalur legacy tetap ada untuk kompatibilitas. Mengubah Blade legacy tidak otomatis mengubah Card Grid editor.
- `docs/ADDON_WEB_EDITOR_ARCHITECTURE.md` dan `docs/VISUAL_PAGE_EDITOR_PLAN.md` berisi rancangan/histori yang sebagian berbeda dari kode sekarang. Contohnya registry addon sudah ada dan save aktif memakai POST. Verifikasi kode aktual sebelum mengikuti instruksi teknis lama.

## Batas file dan perubahan

| Area | Status | Batas tindakan |
| --- | --- | --- |
| `public/css/page-builder/templates.css` | Diizinkan | Desain Card, tema, breakpoint, tipografi, warna dan spacing dalam canvas website |
| `public/css/page-builder/editor.css` | Terbatas | Kontrol/preview Card dan selector/swatch Template saja; jangan redesign seluruh admin/editor |
| `public/js/page-builder/editor/siteRenderer.js` | Terbatas | Markup Card dan penerapan visual Template; pertahankan renderer block lain dan helper keamanan |
| `public/js/page-builder/editor/inspectorRenderer.js` | Terbatas | Kontrol Card yang memakai kontrak data yang sudah diterima backend |
| `public/js/page-builder/editor/editor.js` | Terbatas | Handler Card dan pilihan Template saja; jangan mengubah save, page routing, auth atau siklus state global |
| `public/js/page-builder/editor/templates.js` | Terbatas | Sinkronisasi metadata/fallback ID yang sudah terdaftar; ID baru membutuhkan warning backend |
| `public/js/page-builder/editor/blockFactory.js` | Terbatas | Fallback Card sesuai kontrak server; bukan perluasan schema |
| `resources/views/admin/pages/editor.blade.php` | Terbatas | Markup kontrol Card/Template saja; jangan mengubah injection data, route, token, atau shell global |
| Aset lokal khusus Card/Template di `public/` | Diizinkan | Aset yang digunakan langsung; hindari overwrite aset global dan catat path tepatnya |
| `tests/Feature/PageManagementTest.php`, pengujian khusus Card/Template | Terbatas | Pemeriksaan relevan tanpa melemahkan assertion/kontrak existing |
| `AGENTS.md`, dua dokumen scope/changelog ini | Diizinkan | Pemeliharaan aturan dan rekam pekerjaan sesuai instruksi pemilik |
| `resources/views/website/card-templates/*` | Bersyarat | Hanya jika task secara eksplisit meminta Card legacy; bukan target default |
| Definisi/manifest addon existing | Bersyarat | Hanya Card addon yang disebut eksplisit, memakai extension point existing; bukan izin mengubah bisnis, permission, tenant signup, route atau migrasi addon |
| Seluruh file lain | Di luar scope default | Baca untuk memahami dependensi; jangan edit tanpa penetapan scope yang eksplisit |

File frontend yang diizinkan tetap merupakan bagian repo NovaBase. Pengecualian di atas hanya untuk perubahan lokal Card/Template, bukan perubahan arsitektur engine. Modul `state.js`, `siteInteractions.js`, `public-renderer.js`, registry umum, serta layout global bukan target otomatis. Jika perlu diedit, jelaskan kebutuhan dan eskalasikan batasnya sebelum implementasi.

## Base code yang dilindungi

Termasuk `app/Core/**`, controller, model, middleware, provider, `routes/**`, `config/**`, `bootstrap/**`, `database/**`, layout/CSS/JS global, dependency/lockfile, `.env*`, entrypoint hosting, dan sistem addon umum. Auth, role, permission, tenant isolation, cache, penyimpanan, API save, serta konversi legacy tidak boleh diubah dalam task ini.

Contoh kebutuhan warning: mendaftarkan ID Template baru di `BlockRegistry::templates()`, menambah jenis block core, menambah field Card yang ditolak validator, mengubah format JSON/state atau database, dan menambahkan mekanisme renderer addon baru. Jangan memalsukan ID template atau melewati validator sebagai solusi frontend.

## Prosedur warning dan handoff

1. Identifikasi file/fungsi core yang menjadi dependensi dan buktikan keterbatasan dari kode atau respons validasi.
2. Tampilkan warning berikut kepada pemilik repo dan tulis entri berstatus `PENDING CORE` pada changelog.
3. Siapkan spesifikasi field/ID, contoh payload, dampak, kriteria penerimaan dan rencana verifikasi untuk maintainer; jangan membuat patch core yang diterapkan diam-diam.
4. Lanjutkan pekerjaan frontend yang independen. Jangan memasukkan kontrol yang tampak siap dipakai tetapi pasti gagal disimpan.
5. Setelah maintainer menyediakan kontrak tersebut, verifikasi integrasi lalu lanjutkan bagian Card/Template. Perubahan core hanya boleh dikerjakan di penugasan terpisah dengan otorisasi eksplisit.

```text
WARNING: PERUBAHAN BASE CODE NOVABASE DIPERLUKAN
Task Card/Template:
Kebutuhan yang terblokir:
Bukti keterbatasan saat ini:
File/fungsi core terkait:
Usulan kontrak/perubahan untuk maintainer:
Dampak ke data lama, tenant, keamanan, dan kompatibilitas:
Bagian frontend yang tetap dapat dilanjutkan:
Kriteria penerimaan dan pemeriksaan integrasi:
Status: PENDING CORE - belum dieksekusi oleh pelaksana Card/Template.
```

## Aturan implementasi

- Sebelum edit, daftar target file dan tujuan perubahan. Periksa diff awal dan aturan lokal. Jangan menganggap semua file dalam folder editor bebas diubah.
- Pertahankan ID block/item yang digunakan sistem, struktur state dan data existing. Beralih tema tidak boleh mengganti konten, menghapus block, atau mereset halaman.
- Gunakan selector di bawah `.website-theme`/kelas template yang sesuai; hindari aturan global seperti `body`, `.card`, `button` yang memengaruhi admin/login.
- Pertahankan escaping teks dan pemeriksaan URL; jangan merender input pengguna sebagai HTML mentah. Jangan ubah permission atau akses tenant.
- Desain harus terbaca di desktop/mobile, dapat dipakai dengan keyboard, memiliki fokus terlihat dan label kontrol yang jelas. Jangan menyisipkan logika bisnis ke desain.
- Tidak ada refactor massal, rename folder, upgrade dependency, implementasi modul lain atau perbaikan sampingan. Catat temuan di luar scope sebagai handoff.

## Pemeriksaan dan kriteria selesai

Untuk perubahan implementasi berikutnya, jalankan pemeriksaan sesuai dampak:

- Card: tambah/edit item melalui kontrol yang tersedia, ubah judul/teks/icon/link, simpan, reload, dan bandingkan preview dengan render publik. Periksa jumlah item bervariasi, konten panjang/kosong, serta URL tidak valid.
- Template: periksa seluruh ID existing, pindah tema tanpa kehilangan konten, simpan/reload, konsistensi editor/publik, desktop sekitar 1440px dan mobile sekitar 375px, overflow dan fokus keyboard.
- JavaScript yang diubah: `node --check <file>`. Jika kode PHP relevan diubah oleh maintainer: `php -l <file>`. Jalankan `php artisan test --filter=PageManagementTest` jika menyentuh perilaku save/integrasi dan runtime tersedia; test existing tidak menggantikan pemeriksaan browser Card/Template.
- Periksa diff agar tidak ada perubahan di luar scope; gunakan `git diff --check` jika Git tersedia.
- Catat hasil aktual, kegagalan, serta pemeriksaan yang belum dijalankan beserta alasannya. Jangan menginstal dependency atau menjalankan migrasi/seeder secara otomatis untuk menutupi runtime yang belum siap.

Pekerjaan selesai ketika perubahan tetap dalam scope, perilaku existing terjaga, pemeriksaan relevan tercatat, dan changelog lengkap. Jika terblokir core, laporkan sebagai parsial/PENDING CORE, bukan mengklaim fitur selesai.
