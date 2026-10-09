# NovaBase Addon Web Editor Architecture

Dokumen ini merancang agar addon NovaBase dapat menambahkan block atau card baru ke Web Editor. Ini masih rancangan; belum ada perubahan kode.

## 1. Temuan Saat Ini

Addon NovaBase saat ini sudah dapat menyediakan manifest, route, view, migration, permission, menu admin, dan dashboard card.

Web Editor belum extensible dari addon karena:

- definisi block masih hard-coded di App/Core/PageBuilder/BlockRegistry;
- block factory mengambil definisi dari data core;
- public renderer memiliki daftar type hard-coded;
- inspector field bergantung pada type block;
- addon belum dapat mendaftarkan asset, schema, dan renderer block.

Kesimpulan: addon saat ini bisa menambah menu dan dashboard card, tetapi belum bisa menambah block Web Editor secara resmi.

## 2. Bedakan Tiga Kebutuhan

### Card item core

Addon hanya menambah item ke block core seperti Card Grid. Ini cocok untuk konten statis sederhana, tetapi membuat core mengetahui domain addon.

### Block baru addon

Addon menambah item baru ke palette, misalnya Member Summary, Upcoming Events, Donation CTA, atau Psychology Result CTA. Ini pola utama yang direkomendasikan.

### Dynamic block

Block mengambil data dari service addon, misalnya jumlah jemaat, event terbaru, atau jadwal ibadah. Ini memerlukan cache, policy, visibility, dan fallback.

Untuk NovaBase 1.0.0, mulai dari static addon block dan siapkan kontrak dynamic block untuk tahap berikutnya.

## 3. Keputusan Arsitektur

Addon boleh menambahkan block baru ke Web Editor, tetapi tidak boleh mengubah file core secara langsung.

Alur yang diinginkan:

Addon manifest -> block definition -> PageBuilderRegistry -> palette/inspector -> editor renderer -> public renderer.

Semua block addon wajib memakai namespaced type:

- core.hero
- core.cards
- church-members.member-summary
- church-events.upcoming-events
- psychology-test.result-cta

Namespaced type mencegah bentrok antar addon.

## 4. Manifest Addon

Manifest perlu memiliki bagian web_editor.blocks. Setiap block minimal mendeklarasikan:

- type;
- label;
- category;
- icon;
- permission;
- definition class;
- editor asset;
- public asset jika diperlukan.

Contoh konseptual:

    'web_editor' => [
        'blocks' => [
            [
                'type' => 'church-members.member-summary',
                'label' => 'Member Summary',
                'category' => 'Church',
                'permission' => 'church-members.web-editor',
                'definition' => MemberSummaryBlock::class,
                'asset' => 'public/js/web-editor/member-summary.js',
            ],
        ],
    ],

Manifest hanya deklarasi. Logic block berada pada definition class dan asset addon.

## 5. Block Definition Contract

Core memerlukan contract untuk definition block. Method yang dibutuhkan:

- type();
- label();
- category();
- icon();
- defaults();
- schema();
- permission();
- isDynamic();
- editorAsset();
- publicAsset();

Definition menjelaskan data yang sah, default state, permission, dan renderer. Core tidak boleh mengetahui detail domain gereja atau Psychology Test.

## 6. PageBuilderRegistry

BlockRegistry saat ini perlu dipecah menjadi registry gabungan:

- CoreBlockRegistry untuk hero, cards, split, gallery, video, logos, testimonials, pricing, dan CTA;
- AddonBlockRegistry untuk block yang berasal dari addon;
- PageBuilderRegistry sebagai facade gabungan.

Tanggung jawab PageBuilderRegistry:

- mendaftarkan core dan addon block;
- menolak duplicate type;
- memvalidasi manifest;
- mengirim definitions ke editor;
- memfilter berdasarkan permission dan plan;
- memvalidasi type serta data saat save;
- mengumpulkan asset editor dan public;
- menyediakan fallback saat addon tidak aktif.

Authorization tetap dilakukan backend. Data dari frontend tidak boleh dipercaya.

## 7. Penyimpanan

Tabel site_blocks yang sudah ada dapat dipakai kembali.

- type menyimpan namespaced block type;
- data_json menyimpan data block;
- sort_order menyimpan urutan;
- nav_enabled dan nav_label tetap berlaku.

Data JSON wajib divalidasi dengan schema definition. Jika addon dinonaktifkan, block tidak langsung dihapus. Editor menampilkan status unavailable dan public renderer memakai fallback agar tidak terjadi HTTP 500.

## 8. Editor Frontend

Server mengirim:

- core definitions;
- addon definitions yang tersedia;
- inspector schema;
- permission-filtered palette;
- asset/module list;
- page state.

Client kemudian:

1. Menggabungkan definition core dan addon.
2. Menampilkan palette berdasarkan category.
3. Membuat block dari defaults.
4. Membuat inspector dari schema.
5. Memakai renderer registry berdasarkan namespaced type.
6. Mengirim state kembali ke backend.

Renderer tidak boleh memakai daftar if panjang untuk setiap addon. Gunakan renderer registry. Hanya module JS yang sudah terdaftar di backend yang boleh dimuat; URL asset dari database tidak boleh dieksekusi.

## 9. Asset Strategy Versi 1.0.0

Karena NovaBase saat ini belum mewajibkan pipeline frontend, versi 1.0.0 dapat memakai asset statis:

- JS dan CSS disimpan di folder addon;
- AddonRegistry mengumpulkan asset terdaftar;
- editor melakukan dynamic import dari asset yang sudah divalidasi backend;
- public page hanya memuat asset block yang digunakan;
- npm build tidak wajib untuk menjalankan addon dasar.

Struktur contoh:

    addons/ChurchMembers/
      addon.php
      app/PageBuilder/MemberSummaryBlock.php
      public/js/web-editor/member-summary.js
      public/css/web-editor/member-summary.css
      resources/views/
      database/migrations/

Vite atau bundling dapat ditambahkan saat jumlah addon dan asset sudah besar.

## 10. Static dan Dynamic Block

### Static block

Menyimpan heading, text, image, button, item, dan link di data_json. Ini cepat, mudah dipreview, mudah dicache, dan cocok untuk 1.0.0.

### Dynamic block

Mengambil data dari addon service. Dynamic block wajib memiliki public resolver, editor preview resolver, policy visibility, tenant-aware cache key, timeout, fallback, sanitization, dan batas data.

Dynamic block sebaiknya memakai BlockRenderContext yang berisi tenant, user, mode editor/preview/public, locale, route, permission, dan entitlement addon.

## 11. Rendering

Static presentation block boleh client-rendered pada 1.0.0.

Dynamic atau SEO-sensitive block sebaiknya memiliki server renderer atau Blade renderer terpisah dari editor renderer.

Target contract:

- editor_renderer untuk canvas editor;
- public_renderer untuk website publik;
- fallback_renderer saat addon tidak tersedia.

Public output tidak boleh membocorkan data private hanya karena block dapat dibuka dari editor.

## 12. Permission dan Plan

Block tersedia hanya jika dua kondisi terpenuhi:

    user permission allows action
    AND addon is enabled for tenant/plan

Contoh: user memiliki church-members.web-editor tetapi addon tidak aktif, maka block tidak tersedia. Jika addon aktif tetapi permission tidak ada, block tidak muncul atau tidak dapat disimpan.

is_admin hanya menentukan kemampuan masuk admin shell; bukan akses semua block.

## 13. Contoh Block Gereja

church-members.member-summary:
- data statis: title, text, image, buttonLabel, buttonUrl, layout;
- dynamic optional: active_members;
- permission: church-members.web-editor.

church-events.upcoming-events:
- title, limit, showLocation, showRegistrationButton;
- public resolver hanya mengambil event published dari tenant yang sama.

church-giving.donation-cta:
- hanya menampilkan CTA publik;
- tidak boleh menampilkan data finansial private tanpa policy eksplisit.

## 14. Tenant Readiness

Karena NovaBase akan dipakai banyak project, setiap block harus menerima TenantContext. Untuk Psychology Test single-tenant, context dapat memakai default tenant. Renderer tetap sama.

Semua query block dinamis, cache, upload, queue, dan asset data harus tenant-aware saat fondasi multi-tenant diterapkan.

## 15. Testing Contract

Setiap addon block wajib diuji untuk:

1. Manifest dapat dibaca.
2. Type unik.
3. Defaults valid.
4. Schema menolak data invalid.
5. User tanpa permission tidak dapat menambah atau menyimpan block.
6. Addon nonaktif tidak dapat menambah block.
7. State menyimpan namespaced type.
8. Public renderer tidak error.
9. Fallback bekerja saat addon dinonaktifkan.
10. Tenant A tidak dapat membaca atau menyimpan block tenant B.
11. Asset hanya berasal dari path addon yang tervalidasi.
12. Dynamic block tidak mengembalikan data private.

## 16. Tahap Implementasi

### Phase A - Contract

- Buat definition interface.
- Buat PageBuilderRegistry.
- Pisahkan core definitions dari addon definitions.
- Pertahankan BlockRegistry lama sebagai adapter.

### Phase B - Static addon block

- Tambah web_editor.blocks pada manifest.
- Tambah asset registry.
- Tambah category palette.
- Tambah client renderer registry.
- Tambah inspector schema.
- Tambah backend validation.
- Buat satu Demo static block sebagai proof of concept.

### Phase C - Public rendering

- Muat asset hanya bila block digunakan.
- Tambah unavailable fallback.
- Pastikan public route tidak error tanpa JS addon.
- Tambah publish validation.

### Phase D - Dynamic block

- Tambah BlockRenderContext.
- Tambah dynamic resolver.
- Tambah tenant-aware cache.
- Tambah visibility policy.
- Buat contoh Upcoming Events.

## 17. Dampak Release 1.0.0

Scope yang sehat untuk NovaBase 1.0.0:

- addon registry stabil;
- Web Editor stabil;
- kontrak addon block tersedia;
- satu Demo static block dari addon;
- validation dan fallback tersedia;
- belum perlu payment atau dynamic member data;
- vendor tetap disimpan di repository saat release.

Setelah release selesai dan dites, vendor hanya dihapus dari repository jika deployment contract sudah jelas. Project hasil clone harus menjalankan composer install berdasarkan composer.lock.

Environment sementara:

- PHP 8.2 atau lebih baru sesuai Laravel 12;
- Apache dengan mod_rewrite;
- MySQL atau MariaDB kompatibel;
- Composer 2.x;
- Node.js/npm hanya bila frontend build ditambahkan;
- extension Laravel umum seperti PDO, pdo_mysql, Mbstring, OpenSSL, Tokenizer, XML, Ctype, JSON, Fileinfo, BCMath, dan Curl sesuai dependency.

Versi final harus dikunci dari composer.json dan composer.lock saat release.

## 18. Keputusan Sebelum Coding

1. Apakah 1.0 hanya static addon block?
2. Apakah public renderer boleh bergantung pada JS addon?
3. Apakah Blade server renderer wajib sejak awal?
4. Apakah block dibatasi oleh plan?
5. Apa fallback visual ketika addon dinonaktifkan?
6. Apakah block boleh memakai data private di preview admin?
7. Apakah Psychology Test dan GerejaHub memakai renderer yang sama?
8. Apakah Vite diperlukan pada phase pertama?
9. Apakah addon boleh menyediakan theme atau hanya block?

## 19. Kesimpulan

Addon memang dapat menambahkan card atau block ke Web Editor, tetapi harus melalui kontrak resmi NovaBase:

Addon manifest -> block definition -> PageBuilderRegistry -> permission/plan filter -> editor palette -> inspector schema -> editor renderer -> public renderer -> backend validation.

Untuk 1.0.0, mulai dari static namespaced addon block. Jangan membuat core mengetahui fitur gereja atau Psychology Test. Dynamic block dan tenant-aware block dapat ditambahkan setelah kontrak dasar stabil.
