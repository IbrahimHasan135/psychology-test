# NovaStore Integration Plan for NovaBase

Dokumen ini merancang cara mengambil konsep modular dari `NovaStore_UMKM` ke NovaBase Laravel. Targetnya: NovaBase menjadi base package Novalynk yang bisa dipakai berulang, lalu fitur produk berikutnya cukup masuk sebagai addon tanpa mengotori core.

## Tujuan

- NovaBase tetap menjadi core: auth, role, admin shell, page builder, database bootstrap, design system, dan report/export driver.
- Addon berisi fitur produk spesifik seperti Sales, Accounting, Cases, IoT Device, Inventory, atau modul lain.
- Admin panel otomatis membaca addon aktif untuk membentuk sidebar, dashboard card, permission, route, migration, seeder, listener, dan report.
- Role user menentukan addon dan kemampuan apa saja yang bisa diakses.
- Tampilan admin mengikuti pola NovaStore, tetapi branding, warna, dan istilah tetap NovaBase.

## Pelajaran Dari NovaStore

NovaStore memakai custom PHP modular. Bagian yang relevan untuk NovaBase:

- `config/modules.php`: daftar modul aktif.
- `modules/{Module}/module.php`: manifest modul yang mengembalikan metadata.
- `Core\ModuleRegistry`: registry global untuk menu, tabel, dashboard card, module info, dan boot status.
- `Core\Module::bootAll()`: load semua module, register menu, table, card, listener, lalu menjalankan hook `boot()`.
- `Core\Module::loadRoutes()`: load `routes.php` dari modul aktif.
- `Core\Sidebar`: render sidebar dengan grouping per module.
- `views/dashboard.php`: dashboard membagi card berdasarkan module, hanya menampilkan card dari module yang boleh diakses role.
- `Core\Rbac`: role punya izin module dan kemampuan manajemen user/role.
- `Core\EventBus`: komunikasi antar module memakai event.
- `Core\Report`: report dibuat sebagai definisi standar lalu dirender/export.

Konsep ini bagus, tetapi implementasinya di NovaBase harus memakai cara Laravel: service provider, route group, migration path, Blade namespace, Gate/Policy, Event/Listener, dan service container.

## Konsep Addon NovaBase

Struktur addon yang disarankan:

```text
addons/
  Sales/
    addon.php
    routes/
      web.php
    database/
      migrations/
      seeders/
    app/
      Http/Controllers/
      Models/
      Services/
      Reports/
      Listeners/
    resources/
      views/
    public/
      assets/
    README.md
```

`addon.php` menjadi manifest addon. File ini harus ringan dan deklaratif.

Contoh konsep:

```php
return [
    'slug' => 'sales',
    'name' => 'Sales',
    'description' => 'Lead, opportunity, follow up, dan pipeline penjualan.',
    'icon' => 'briefcase',
    'enabled' => true,
    'admin_menu' => [
        ['label' => 'Overview', 'route' => 'admin.sales.index', 'icon' => 'layout-dashboard'],
        ['label' => 'Leads', 'route' => 'admin.sales.leads.index', 'icon' => 'users'],
        ['label' => 'Opportunities', 'route' => 'admin.sales.opportunities.index', 'icon' => 'kanban'],
    ],
    'permissions' => [
        'sales.view',
        'sales.create',
        'sales.update',
        'sales.delete',
        'sales.export',
    ],
    'dashboard_cards' => [
        SalesDashboardCard::class,
    ],
    'reports' => [
        SalesPipelineReport::class,
    ],
    'listeners' => [
        OpportunityWon::class => [
            CreateAccountingIncome::class,
        ],
    ],
];
```

## Core Yang Perlu Dibuat

### 1. Addon Registry

Lokasi:

```text
app/Core/Addons/AddonRegistry.php
app/Core/Addons/AddonMeta.php
config/addons.php
```

Tugas:

- Membaca daftar addon aktif dari `config/addons.php`.
- Load `addons/{Name}/addon.php`.
- Menyimpan metadata addon: slug, label, icon, path, menu, permission, card, report, listener.
- Menyediakan API untuk admin UI:
  - `enabledAddons()`
  - `adminMenuFor(User $user)`
  - `dashboardCardsFor(User $user)`
  - `permissions()`
  - `reportsFor(User $user)`

`config/addons.php`:

```php
return [
    'sales' => base_path('addons/Sales'),
    'accounting' => base_path('addons/Accounting'),
];
```

### 2. Addon Service Provider

Lokasi:

```text
app/Providers/AddonServiceProvider.php
```

Tugas:

- Register registry ke service container.
- Load route addon.
- Load migration addon.
- Load Blade view namespace.
- Register listener addon.
- Register report exporter addon.

Contoh efek:

- `addons/Sales/routes/web.php` otomatis masuk ke route admin.
- `addons/Sales/database/migrations` otomatis dibaca migration.
- `addons/Sales/resources/views` bisa dipanggil sebagai `view('sales::index')`.

### 3. Sidebar Admin Berbasis Addon

NovaBase saat ini sidebar masih hardcode:

- Dashboard
- Akun & Role
- Page Management

Target baru:

```text
Admin Panel
  Dashboard
  Akun & Role
  Page Management

Addons
  Sales
    Overview
    Leads
    Opportunities
  Accounting
    Overview
    Income
    Expenses
    Reports
```

Aturan:

- Menu core tetap muncul berdasarkan role core.
- Menu addon muncul hanya kalau user punya permission minimal `addon.view`.
- Setiap addon punya kategori sendiri di sidebar.
- Sidebar punya search/filter menu seperti NovaStore.
- Tampilan pakai style NovaBase hijau, bukan branding NovaStore.

File yang nanti diubah:

```text
resources/views/admin/partials/sidebar.blade.php
resources/views/layouts/app.blade.php
```

### 4. Dashboard Card Dari Addon

Dashboard admin saat ini hanya menampilkan total akun dan role count. Target baru:

- Bagian ringkasan core tetap ada.
- Setelah itu dashboard dibagi per addon.
- Setiap addon boleh menyumbang satu atau beberapa card.
- Card hanya tampil jika user punya akses addon.

Konsep tampilan:

```text
Core Overview
  Total Akun
  Super Admin
  Admin
  User

Sales
  Sales Pipeline Card
  Follow Up Hari Ini

Accounting
  Cashflow Bulan Ini
  Piutang Terbuka
```

Interface card:

```php
interface AddonDashboardCard
{
    public function id(): string;
    public function title(): string;
    public function icon(): string;
    public function order(): int;
    public function permission(): string;
    public function render(): string|View;
}
```

### 5. Role Dan Permission Addon

NovaStore mengatur role dengan allowed module. NovaBase sebaiknya lebih detail:

- Role tetap punya base role: Super Admin, Admin, User.
- Tambahkan permission per addon.
- Super Admin otomatis punya semua permission.
- Admin bisa diberi permission tertentu.
- User bisa diberi akses terbatas jika produk membutuhkan dashboard user.

Tabel yang disarankan:

```text
roles
permissions
role_permissions
addon_states
```

Atau kalau ingin tetap sederhana:

```text
role_addon_permissions
  id
  role
  addon_slug
  permission
```

Fitur UI role:

- Role Management menampilkan daftar addon aktif.
- Setiap addon punya checklist permission.
- Bisa pilih cepat:
  - No Access
  - View Only
  - Operator
  - Manager
  - Full Access
- Admin panel menyesuaikan sidebar dan route guard berdasarkan permission.

Middleware:

```text
addon:sales
permission:sales.view
permission:sales.export
```

### 6. Report Dan Export Driver

NovaStore punya konsep `ReportDefinition`, `HtmlReportRenderer`, dan `ExcelExporter`. NovaBase perlu versi Laravel:

```text
app/Core/Reports/ReportDefinition.php
app/Core/Reports/ReportManager.php
app/Core/Reports/Exporters/PdfExporter.php
app/Core/Reports/Exporters/ExcelExporter.php
app/Core/Reports/Exporters/CsvExporter.php
app/Core/Reports/Exporters/HtmlExporter.php
```

Addon cukup membuat report definition:

```php
class SalesPipelineReport implements AddonReport
{
    public function definition(array $filters): ReportDefinition
    {
        return new ReportDefinition(
            title: 'Sales Pipeline',
            metrics: [...],
            sections: [...],
            sheets: [...]
        );
    }
}
```

Core NovaBase yang mengurus export:

- HTML preview
- PDF
- Excel
- CSV

Permission export:

- `sales.export`
- `accounting.export`
- `reports.manage`

### 7. Event Antar Addon

NovaStore memakai `EventBus`. Di NovaBase pakai Laravel Event:

```text
app/Core/Addons/AddonEventRegistry.php
```

Contoh:

- Sales mengirim event `OpportunityWon`.
- Accounting listener membuat income.
- Cases listener membuat onboarding case.

Aturan:

- Event tidak boleh langsung query addon lain secara liar.
- Komunikasi antar addon lewat event atau kontrak service.
- Listener addon hanya aktif kalau addon tersebut enabled.

### 8. Auto Migration Dan Seeder Addon

NovaBase sudah punya auto migration core. Target berikutnya:

- Saat web diakses, core memastikan migration addon aktif sudah terdaftar.
- Addon migration dijalankan otomatis jika `DB_AUTO_MIGRATE=true`.
- Addon seeder dijalankan otomatis jika `DB_AUTO_SEED=true`.
- Tetap simpan catatan migration di tabel Laravel `migrations`.

Urutan boot:

```text
1. Load config/addons.php
2. Register addon metadata
3. Register route/view/migration/listener/report
4. Run core migration
5. Run addon migration
6. Run core seeder
7. Run addon seeder
8. Render request
```

### 9. Tampilan Admin NovaBase

Desain mengikuti pola NovaStore secara fungsi, bukan brand:

- Sidebar kiri dengan group `Admin Panel` dan group per addon.
- Topbar dengan breadcrumb, search, greeting, dan account menu.
- Dashboard greeting bar.
- Dashboard card per addon.
- Card tetap green NovaBase.
- Jangan pakai teks NovaStore, semua menjadi NovaBase / Novalynk.

Komponen UI yang perlu dibuat:

```text
resources/views/admin/partials/topbar.blade.php
resources/views/admin/partials/sidebar.blade.php
resources/views/admin/dashboard.blade.php
resources/views/admin/roles/edit.blade.php
```

### 10. Kontrak Addon

Setiap addon harus mengikuti kontrak:

- Punya slug unik.
- Semua table memakai prefix slug, contoh `sales_leads`, `accounting_incomes`.
- Semua route admin memakai prefix addon, contoh `/admin/addons/sales`.
- Semua permission memakai format `addon.action`.
- Tidak mengubah file core tanpa alasan.
- Jika perlu integrasi ke addon lain, pakai event atau service contract.
- View addon menggunakan namespace, contoh `sales::leads.index`.
- Report addon menggunakan `ReportDefinition`.

## Pembagian Core Dan Addon

Core NovaBase:

- Login/logout
- Role dan permission
- User management
- Admin shell
- Website page builder
- Addon registry
- Addon boot loader
- Dashboard renderer
- Sidebar renderer
- Report/export driver
- Event registration
- Auto migration/seeder

Addon:

- Domain feature
- Controller feature
- Model feature
- View feature
- Migration dan seeder feature
- Dashboard card feature
- Report feature
- Listener feature

## Urutan Implementasi Yang Disarankan

### Phase 1 - Addon Foundation

- Buat `config/addons.php`.
- Buat `AddonMeta`.
- Buat `AddonRegistry`.
- Buat `AddonServiceProvider`.
- Buat contoh addon kosong `addons/Demo`.
- Buat test load addon metadata.

Exit criteria:

- NovaBase bisa membaca addon aktif.
- Addon aktif muncul di debug/registry.
- Tidak mengubah behavior dashboard lama.

### Phase 2 - Admin Sidebar Dan Dashboard

- Refactor sidebar agar menerima menu dari core dan addon.
- Refactor dashboard agar card core dan addon bisa tampil.
- Tambah topbar admin seperti pola NovaStore.
- Tambah search menu sidebar.

Exit criteria:

- Sidebar punya group Admin Panel dan Addons.
- Dashboard bisa menampilkan card dari Demo addon.
- Role belum detail, tapi Super Admin melihat semuanya.

### Phase 3 - Permission Addon

- Buat migration permission addon.
- Buat middleware `permission`.
- Buat UI role permission per addon.
- Terapkan permission ke menu, dashboard card, dan route.

Exit criteria:

- Admin hanya melihat addon yang diizinkan.
- User tanpa permission tidak bisa akses route addon.
- Super Admin selalu punya akses penuh.

### Phase 4 - Report Driver

- Buat `ReportDefinition`.
- Buat exporter HTML, CSV, Excel, PDF.
- Buat route admin report.
- Demo addon menyumbang report sederhana.

Exit criteria:

- Report bisa preview HTML.
- Report bisa export minimal CSV/Excel.
- PDF bisa ditambahkan dengan library setelah dependency dipilih.

### Phase 5 - Event Antar Addon

- Daftarkan event/listener dari manifest addon.
- Buat contoh event Demo.
- Pastikan listener hanya aktif kalau addon enabled.

Exit criteria:

- Addon A bisa dispatch event.
- Addon B bisa merespons tanpa hard dependency langsung.

## Catatan Risiko

- Auto migration saat request web nyaman untuk local/shared hosting, tetapi production sebaiknya bisa dimatikan dengan `.env`.
- PDF export butuh dependency tambahan. Kalau vendor harus ikut Git, dependency harus dikunci dan vendor ikut commit.
- Permission terlalu detail bisa membuat UI role rumit. Karena itu perlu preset seperti View Only, Operator, Manager, Full Access.
- Addon yang terlalu bebas bisa merusak core. Manifest dan kontrak folder harus tegas.

## Keputusan Rekomendasi

Saya sarankan NovaBase mengadopsi konsep NovaStore dengan bentuk Laravel-native:

- `config/addons.php` menggantikan `config/modules.php`.
- `AddonRegistry` menggantikan `ModuleRegistry`.
- `AddonServiceProvider` menggantikan manual boot di `index.php`.
- Laravel Event menggantikan `EventBus`.
- Laravel middleware/Gate menggantikan RBAC custom.
- Blade partial dashboard/sidebar menggantikan renderer custom.
- `ReportDefinition` tetap dipertahankan sebagai konsep standar lintas addon.

Dengan arah ini, NovaBase akan terasa seperti base product Novalynk yang modular: core stabil, addon jelas batasnya, admin panel otomatis berkembang mengikuti fitur yang dipasang.
