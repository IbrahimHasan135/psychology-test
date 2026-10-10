# Rancangan Card Baru dan Template Design

Tanggal analisis: 10 Oktober 2026  
Branch: `feat/website-editor-frontend`  
Status: rancangan implementasi; fitur di bawah belum diimplementasikan oleh dokumen ini.

## 1. Tujuan dan batas pekerjaan

Branch ini difokuskan pada pengembangan Web Editor: menambah pilihan Card, menambah template desain, dan memastikan hasil editor sama dengan halaman publik. Perubahan backend diperbolehkan hanya untuk mendukung registrasi dan validasi fitur tersebut.

Dokumen ini berdasarkan pembacaan kode pada working directory dan nama branch dari `.git/HEAD`. Dokumen ini bukan audit selisih commit terhadap branch utama; Git CLI tidak tersedia di PATH saat analisis. Nama Card dan template baru merupakan usulan awal untuk membatasi implementasi, bukan kebutuhan produk yang sudah dikonfirmasi.

Deliverable saat ini hanya dokumen rancangan. Tahapan implementasi berikut adalah acuan pekerjaan berikutnya dalam branch ini.

## 2. Hasil analisis kode saat ini

| Area | Kondisi yang ditemukan | Referensi |
| --- | --- | --- |
| Definisi block | Ada 9 block inti: `hero`, `cards`, `split`, `gallery`, `video`, `logos`, `testimonials`, `pricing`, `cta` | `app/Core/PageBuilder/BlockRegistry.php` |
| Template desain | Ada `template-studio`, `template-bold`, `template-editorial`, `template-neon`, `template-earth` | `app/Core/PageBuilder/BlockRegistry.php`, `public/js/page-builder/editor/templates.js` |
| Registry addon | Definisi inti digabung dengan block addon sesuai akses pengguna | `app/Core/PageBuilder/PageBuilderRegistry.php` |
| Renderer | Editor dan halaman publik menggunakan renderer JavaScript bersama; dispatch jenis block ditulis eksplisit | `public/js/page-builder/editor/siteRenderer.js` |
| Inspector | Mendukung link, gambar, posisi gambar, serta penambahan item untuk beberapa block; banyak kontrol masih spesifik jenis block | `public/js/page-builder/editor/inspectorRenderer.js`, `editor.js` |
| Penyimpanan | Block disimpan melalui relasi halaman dengan `data_json`, urutan, identitas block, dan navigasi; penyimpanan memakai transaksi serta pemeriksaan versi | `app/Core/PageBuilder/PageBuilderService.php` |
| Tema | State memakai satu `template` untuk situs; save menerapkan `template_id` yang sama ke semua halaman dalam payload | `app/Core/PageBuilder/PageBuilderService.php` |
| Styling | Tema menggunakan CSS variables di bawah `.website-theme` | `public/css/page-builder/templates.css` |
| Validasi | Jenis block/template dibatasi registry; field data mengikuti defaults, termasuk field item berulang dan pemeriksaan URL | `app/Http/Controllers/Admin/PageManagementController.php` |
| Card legacy | `SiteCard`, tabel `site_cards`, dan template Blade masih tersedia; impor legacy menghasilkan block `cards` | `app/Models/SiteCard.php`, `resources/views/website/card-templates/`, `PageBuilderService.php` |
| Pengujian | Sudah ada feature test pengelolaan halaman, Card legacy, dan penolakan save dengan versi usang | `tests/Feature/PageManagementTest.php` |

### Implikasi untuk rancangan

- Menambah file Blade Card saja tidak menambah pilihan di editor utama. Fitur baru harus terhubung ke registry, factory/defaults, renderer, inspector, dan aksi editor.
- Template desain adalah tema visual. Mengganti template harus mempertahankan isi, ID, urutan block, serta navigasi.
- Penambahan block inti membutuhkan renderer eksplisit; registrasi metadata saja belum cukup.
- Metadata PHP sudah disuntikkan ke JavaScript. Fallback JavaScript perlu tetap konsisten dengan registry PHP.
- Validator saat ini terutama mengenali string dan array berdasarkan defaults. Enum, field wajib, dan aturan khusus fitur baru perlu validasi eksplisit; jangan menganggap defaults sebagai schema lengkap.
- Import legacy hanya memetakan judul, teks, dan link Card ke item `cards`; gambar serta variasi template legacy tidak ikut dipetakan. Perbaikan konversi historis dipisahkan dari lingkup branch ini.

## 3. Lingkup implementasi yang diusulkan

### 3.1 Pengembangan Card Grid yang ada

Pertahankan type `cards` dan struktur item lama: `icon`, `title`, `text`, `link`.

Tambahkan opsi tingkat block `variant` dengan nilai `outlined`, `soft`, atau `elevated`. Jika field belum ada pada data lama, renderer memakai `outlined`. Tambahkan pengaturan melalui inspector dengan validasi enum di server; jangan membiarkan nilai masuk langsung sebagai class bebas.

Lengkapi pengelolaan item Card: tambah, hapus, duplikat, dan pindah urutan. Aksi harus hanya mengubah item dalam block terpilih, mempertahankan isi item lain, serta menyimpan urutan setelah reload. Kontrol editor tidak tampil di halaman publik.

### 3.2 Dua block Card baru

| Type usulan | Kegunaan | Data tingkat block | Data setiap item |
| --- | --- | --- | --- |
| `image-cards` | Layanan atau portofolio dengan gambar | `title`, `text`, `items` | `image`, `alt`, `title`, `text`, `buttonLabel`, `link` |
| `stat-cards` | Ringkasan angka yang ditulis manual | `title`, `text`, `items` | `value`, `label`, `text` |

Ketentuan:

- Defaults berisi contoh singkat dan dapat diedit, tanpa mengambil data bisnis secara otomatis.
- `value` berupa string agar mendukung nilai seperti `120+`, `98%`, dan `Rp2jt`; tidak menambah tipe numerik baru ke kontrak validator generik.
- Gambar menggunakan mekanisme URL/upload yang sudah tersedia. Inspector perlu mendukung `items.N.image` dan teks alternatif.
- Link kosong tidak menghasilkan tombol kosong; gambar kosong memakai placeholder lokal atau layout tanpa gambar.
- Semua teks di-escape, URL divalidasi, dan renderer tidak menerima HTML/CSS/JavaScript bebas dari pengguna.
- Pengelolaan item menggunakan aksi yang sama dengan Card Grid sejauh memungkinkan; hindari refactor seluruh editor.
- Usulan batas UI adalah 1–12 item per block untuk ketiga jenis Card. Server menerapkan batas yang sama untuk data baru. Data lama di luar batas tidak boleh dipotong diam-diam; sediakan pesan saat penyimpanan membutuhkan penyesuaian.

Contoh data block baru, dengan identitas dan navigasi tetap mengikuti kontrak existing:

```json
{
  "id": "block_image_cards_01",
  "type": "image-cards",
  "navEnabled": true,
  "navLabel": "Layanan",
  "data": {
    "title": "Layanan kami",
    "text": "Pilihan layanan untuk bisnis Anda.",
    "items": [
      {
        "image": "/images/service-example.webp",
        "alt": "Contoh layanan desain",
        "title": "Desain Website",
        "text": "Website untuk memperkenalkan bisnis.",
        "buttonLabel": "Lihat detail",
        "link": "/layanan"
      }
    ]
  }
}
```

Path gambar pada contoh adalah ilustrasi kontrak data; asset tersebut belum dibuat.

### 3.3 Dua template desain baru

| ID usulan | Nama | Arah visual |
| --- | --- | --- |
| `template-corporate` | Corporate Trust | Biru tua, permukaan terang, tipografi sans-serif, tombol tegas, ruang konten teratur |
| `template-minimal` | Minimal Mono | Monokrom, border tipis, bayangan ringan, tipografi sederhana, ruang kosong lebih luas |

Template baru wajib:

- Menggunakan CSS variables yang ada untuk warna, font, radius, surface, hero, dan CTA; tambahkan token hanya bila dibutuhkan bersama.
- Menyediakan swatch pada daftar template editor.
- Mendukung seluruh 9 block inti lama serta 2 block baru, bukan hanya contoh Card.
- Mengubah presentasi tanpa mengganti konten atau membuat halaman contoh otomatis.
- Mengikuti satu tema tingkat situs. Pemilihan tema berbeda untuk setiap halaman tidak termasuk branch ini.
- Memiliki kontras teks yang terbaca, fokus keyboard terlihat, dan layout responsif.

## 4. Peta perubahan file

| File/area | Perubahan yang diperbolehkan |
| --- | --- |
| `app/Core/PageBuilder/BlockRegistry.php` | Defaults dan label 2 block baru, opsi Card Grid, metadata 2 tema baru |
| `app/Http/Controllers/Admin/PageManagementController.php` | Validasi enum, jumlah item, dan struktur field baru; pertahankan sanitasi URL |
| `public/js/page-builder/editor/blockRegistry.js` | Sinkronisasi daftar fallback block |
| `public/js/page-builder/editor/blockFactory.js` | Pastikan defaults dan fallback menghasilkan data baru tanpa berbagi referensi antarblock |
| `public/js/page-builder/editor/siteRenderer.js` | Renderer block baru, varian Card Grid, fallback data lama |
| `public/js/page-builder/editor/inspectorRenderer.js` | Pilihan varian, gambar/alt item, dan kontrol item baru |
| `public/js/page-builder/editor/editor.js` | Aksi item dan pemilihan block; perbarui daftar type pada prompt yang masih statis |
| `public/js/page-builder/editor/templates.js` | Sinkronisasi metadata fallback template |
| `public/css/page-builder/templates.css` | Tema baru dan styling Card yang terisolasi dalam website |
| `public/css/page-builder/editor.css` | Swatch serta kontrol inspector bila diperlukan |
| `tests/Feature/PageManagementTest.php` | Pengujian save dan validasi yang terkait fitur baru |
| `docs/` | Dokumentasi kontrak dan hasil verifikasi |

`PageBuilderService.php`, route, dan Blade editor hanya diubah jika ada kebutuhan integrasi yang dapat dijelaskan. Rancangan ini tidak memerlukan perubahan schema database karena data baru tetap berada di `data_json` dan memakai `template_id` yang tersedia.

## 5. Di luar lingkup branch

- Refactor autentikasi, role, permission, tenant resolver, atau provisioning tenant.
- Perubahan fitur bisnis addon, dashboard Card addon, atau alur pendaftaran tenant.
- Menghapus tabel/model/API legacy atau menjalankan migrasi ulang konten yang sudah ada.
- Redesign seluruh admin panel, halaman login, dan user portal.
- Marketplace tema, import/export paket tema, dan template yang menyisipkan susunan halaman otomatis.
- Theme builder bebas, custom CSS/HTML/JavaScript pengguna, dan tema berbeda per halaman.
- Backend katalog, stok, checkout, pembayaran, API statistik, dan integrasi layanan eksternal.
- Media library, object storage, kompresi gambar server, atau perubahan arsitektur upload.
- Mengganti JavaScript editor dengan framework baru, menambah bundler, atau mengganti Laravel.
- Draft/publish workflow baru, kolaborasi realtime, undo/redo menyeluruh, SEO/SSR, dan perubahan deployment.

Jika implementasi membutuhkan salah satu area tersebut, catat sebagai pekerjaan terpisah. Perbaikan bug kecil boleh masuk hanya bila langsung menghalangi Card atau template yang dirancang di sini dan disertai alasan serta verifikasi.

## 6. Tahapan pengerjaan

1. Tetapkan kontrak defaults, enum, dan perilaku fallback untuk data lama.
2. Implementasikan varian Card Grid serta kontrol item; verifikasi save dan reload.
3. Tambahkan `image-cards` dan `stat-cards` dari registry sampai renderer publik dan inspector.
4. Tambahkan Corporate Trust dan Minimal Mono beserta swatch dan token visual.
5. Uji kombinasi block/tema, kompatibilitas konten lama, validasi, dan akses existing.
6. Catat hasil verifikasi aktual, file yang berubah, serta keterbatasan dalam deskripsi PR.

## 7. Kriteria selesai dan verifikasi

- Editor menampilkan 11 block inti dan 7 template; block addon tetap mengikuti akses existing.
- Dua block baru dapat ditambah, diedit, diduplikat, dipindah, dihapus, disimpan, dan dibuka kembali.
- Pengelolaan item mempertahankan isi dan urutan setelah save/reload, termasuk batas minimum/maksimum.
- Mengganti template tidak mengubah isi, ID, urutan, path halaman, atau navigasi.
- Data Card Grid tanpa `variant` masih terbaca tanpa migrasi manual.
- Preview dan publik memperlihatkan konten serta tema yang sama; publik bebas toolbar dan atribut edit.
- Periksa seluruh 11 block pada 7 tema pada lebar contoh 360, 768, dan 1440 piksel; tidak ada overflow horizontal, teks terpotong, atau fokus yang hilang.
- Feature test mencakup save block/tema baru, penolakan enum tidak valid, field/item tidak dikenal, URL berbahaya, serta konflik versi existing.
- Verifikasi smoke akses admin dan isolasi tenant menggunakan mekanisme existing; tidak menambah sistem permission baru.
- Jalankan `php artisan test --filter=PageManagementTest`, lalu test akses/tenant yang relevan jika perubahan menyentuh jalur tersebut. Sesuaikan executable PHP dengan lingkungan XAMPP.

Pengujian runtime di atas adalah rencana untuk tahap implementasi. Pada penyusunan dokumen ini belum dilakukan implementasi fitur, pengujian visual browser, atau pengujian runtime aplikasi.

## 8. Risiko dan keputusan teknis

| Risiko | Penanganan dalam lingkup |
| --- | --- |
| Registry PHP dan fallback JavaScript berbeda | Perbarui pasangan definisi dalam perubahan yang sama |
| Field baru ditolak validator | Perbarui defaults dan validasi sebelum menghubungkan UI |
| Defaults tidak cukup membatasi enum/kelengkapan item | Tambahkan aturan khusus yang eksplisit untuk fitur baru |
| Tema merusak block lama | Verifikasi matriks seluruh block inti dan tema |
| Data lama tidak memiliki field tambahan | Gunakan fallback saat render; jangan menulis ulang semua data |
| Upload base64 memperbesar payload | Gunakan mekanisme existing dan catat batas aktual lingkungan; media library tetap pekerjaan terpisah |
| Perubahan tersimpan langsung pada halaman yang sudah terbit | Pertahankan perilaku existing dan lakukan verifikasi dengan konten pengujian |
| Tema tersimpan berbeda pada halaman existing | Jangan menambah semantik tema per halaman; gunakan kontrak satu tema situs pada save existing |

## 9. Batas review branch

Review dinyatakan sesuai lingkup bila setiap perubahan dapat ditelusuri ke Card Grid, dua block Card baru, dua template desain baru, atau dukungan validasi dan verifikasinya. Dokumen ini tidak menyatakan bahwa perubahan lain yang mungkin sudah ada di working directory merupakan bagian rancangan ini.
