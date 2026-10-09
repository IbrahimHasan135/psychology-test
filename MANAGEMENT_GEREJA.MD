# GerejaHub Management Architecture

Dokumen ini merancang implementasi NovaBase untuk produk GerejaHub. Dokumen ini adalah rancangan arsitektur dan belum mengubah kode aplikasi.

## 1. Ringkasan Produk

GerejaHub adalah satu platform milik Novalynk yang melayani banyak organisasi gereja.

Contoh URL:

- Platform: gerejahub.com
- Tenant gereja: gerejahub.com/gbi
- Website publik tenant: gerejahub.com/{tenant-slug}
- Login tenant: gerejahub.com/{tenant-slug}/login
- Admin tenant: gerejahub.com/{tenant-slug}/admin

Setiap gereja mempunyai:

- Website atau landing page sendiri.
- Data anggota dan jemaat sendiri.
- User, admin, dan role sendiri.
- Addon dan paket fitur sendiri.
- Area login dan admin panel sendiri.

Data dan session tenant tidak boleh tercampur dengan tenant lain.

## 2. Keputusan Arsitektur Utama

### 2.1 NovaBase tetap menjadi core

NovaBase tetap menjadi core yang dipakai oleh Psychology Test dan GerejaHub. Namun, untuk mendukung GerejaHub, NovaBase perlu memiliki fondasi multi-tenant.

Pembagian yang direkomendasikan:

NovaBase Core:
- authentication;
- tenant resolution;
- authorization;
- role and permission engine;
- admin shell;
- page builder;
- addon lifecycle;
- database bootstrap.

GerejaHub Product:
- public platform landing page;
- tenant onboarding;
- subscription and plan;
- tenant provisioning.

Gereja Tenant Addons:
- church profile;
- members and jemaat;
- attendance;
- events;
- donations or payments;
- reports.

### 2.2 Single application, shared database schema

Tahap awal menggunakan satu aplikasi Laravel dan satu database dengan shared schema. Semua tabel tenant-aware memiliki tenant_id.

Keuntungannya:

- Deploy dan update lebih sederhana.
- Satu source code untuk semua gereja.
- Addon dapat digunakan lintas tenant.
- Backup dan migration lebih mudah pada fase awal.

Database terpisah per gereja belum diperlukan. Opsi itu dapat dipertimbangkan untuk kebutuhan enterprise, regulasi, atau skala besar.

### 2.3 URL path sebagai tenant context

Fase awal memakai path:

- /gbi
- /gbi/login
- /gbi/admin
- /gbi/members

Domain custom dan subdomain dapat ditambahkan kemudian melalui tabel tenant_domains tanpa mengubah model permission.

## 3. Jenis Account dan Scope Role

Nama super_admin yang sekarang dipakai NovaBase terlalu ambigu. GerejaHub memerlukan dua scope administrasi.

### 3.1 Platform scope

| Role | Scope | Fungsi |
| --- | --- | --- |
| master_admin | Platform | Mengelola seluruh GerejaHub, tenant, plan, addon, dan konfigurasi global |
| platform_staff | Platform | Operasional platform dengan permission terbatas |
| platform_support | Platform | Support tanpa akses penuh ke data sensitif |

master_admin menggantikan makna global dari super_admin.

### 3.2 Tenant scope

| Role | Scope | Fungsi |
| --- | --- | --- |
| tenant_owner | Tenant | Pemilik gereja yang mendaftar; mengelola role, user, website, dan addon tenant |
| tenant_admin | Tenant | Admin operasional yang dibuat oleh tenant owner |
| user | Tenant | User internal dengan permission terbatas |
| jemaat | Tenant | Anggota atau jemaat yang memakai fitur publik/member |

tenant_owner adalah pengganti konsep super_admin di dalam tenant. Ia bukan master_admin dan tidak boleh mengakses tenant lain atau konfigurasi platform.

Role tambahan dapat dibuat tenant_owner melalui Role Management dengan batasan role yang boleh dibuat.

### 3.3 Jangan menyimpan role tenant sebagai string tunggal di users

Struktur saat ini menyimpan users.role. Itu cukup untuk single-tenant, tetapi tidak cukup bila satu user dapat menjadi admin di lebih dari satu gereja.

Target arsitektur:

- users: identitas global;
- tenant_user_memberships: hubungan user dengan tenant;
- roles: definisi role platform atau tenant;
- role_permissions: izin fitur;
- role_creatable_roles: aturan pembuatan role.

## 4. Model Data yang Direkomendasikan

### 4.1 tenants

Kolom utama:

- id;
- slug, unique;
- name;
- legal_name, nullable;
- status: pending, active, suspended, archived;
- plan_id, nullable;
- owner_user_id, nullable;
- settings_json, nullable;
- timestamps.

slug menjadi bagian URL, sehingga harus lowercase, stabil, dan tidak boleh memakai reserved words seperti login, admin, api, atau assets.

### 4.2 tenant_domains

Dipakai saat custom domain atau subdomain diperlukan:

- id;
- tenant_id;
- host, unique;
- is_primary;
- verified_at, nullable.

### 4.3 users

Tetap menyimpan identitas login global:

- id;
- name;
- username, nullable;
- email;
- password;
- status;
- email_verified_at;
- last_login_at.

Untuk fleksibilitas, email global sebaiknya unique. Username dapat unique per tenant melalui membership atau account alias jika produk membutuhkan username berbeda antar tenant.

### 4.4 tenant_user_memberships

- id;
- tenant_id;
- user_id;
- role_id;
- status: invited, active, suspended;
- display_name, nullable;
- joined_at, nullable;
- timestamps.

Tambahkan unique constraint pada tenant_id + user_id.

### 4.5 roles

- id;
- tenant_id, nullable;
- scope: platform atau tenant;
- name;
- slug;
- is_system;
- is_admin;
- timestamps.

scope platform dengan tenant_id kosong dipakai role Novalynk. scope tenant dengan tenant_id terisi dipakai role milik gereja.

### 4.6 Permission tables

permissions:
- id;
- addon_slug;
- permission;
- description.

role_permissions:
- role_id;
- permission_id.

role_creatable_roles:
- role_id;
- creatable_role_id.

Permission harus mengacu pada role ID atau role scope yang valid, bukan hanya slug global. Slug admin milik GBI tidak boleh secara tidak sengaja memberi akses ke admin tenant lain.

### 4.7 Website tables

Semua data website tenant harus memiliki tenant ownership.

site_pages:
- tenant_id;
- slug;
- name;
- template_id;
- state atau status.

site_blocks:
- site_page_id;
- tenant_id;
- block_uid;
- type;
- data_json;
- sort_order.

tenant_id pada site_blocks dapat dipertahankan sebagai defense-in-depth walaupun sudah ada melalui site_page_id.

### 4.8 Addon tables

Addon tenant-aware wajib mempunyai tenant_id pada tabel utamanya. Contoh:

- church_profiles.tenant_id;
- church_members.tenant_id;
- church_events.tenant_id;
- church_attendance.tenant_id;
- church_payments.tenant_id.

Tidak boleh mengambil data dengan query global tanpa scope tenant.

## 5. Request Lifecycle

Urutan request yang direkomendasikan:

HTTP request
-> host normalization
-> ResolveTenant middleware
-> start session
-> authenticate user
-> ResolveTenantMembership middleware
-> role/permission middleware
-> controller
-> tenant-scoped service/query

### 5.1 ResolveTenant

Tanggung jawab:

- Membaca slug dari URL /gbi/... .
- Menolak reserved slug.
- Mencari tenant aktif.
- Menyimpan tenant ke TenantContext yang scoped per request.
- Menentukan apakah request berada pada platform scope atau tenant scope.

Gunakan service seperti TenantContext. Jangan memakai static mutable state yang dapat bertahan antar request pada worker jangka panjang.

### 5.2 ResolveTenantMembership

Setelah user authenticated, middleware memastikan:

- master_admin dapat masuk ke platform scope dan hanya masuk tenant melalui mode support yang eksplisit;
- tenant_owner, tenant_admin, user, dan jemaat memiliki membership aktif pada tenant di URL;
- user tenant GBI tidak dapat memakai session yang sama untuk mengakses /xyz tanpa proses context switch yang tervalidasi.

### 5.3 Tenant-aware query

Pilih satu pola dan terapkan konsisten:

1. BelongsToTenant contract dengan global scope.
2. Repository atau service yang selalu menerima TenantContext.
3. Query helper forCurrentTenant.

Rekomendasi NovaBase adalah contract plus service/repository. Global scope membantu mencegah kebocoran data, tetapi operasi platform dan migration perlu cara eksplisit untuk bypass scope.

## 6. Authentication dan Login Flow

### 6.1 Platform login

- GET /login
- POST /login

Hanya untuk akun platform Novalynk seperti master_admin. Tenant owner tidak login di sini.

Setelah login:

- master_admin -> /admin/platform
- platform_staff -> /admin/platform

### 6.2 Tenant login

- GET /{tenant}/login
- POST /{tenant}/login

Login harus memvalidasi credentials + tenant_id + active membership.

Akun tenant yang mencoba login dari /login harus ditolak atau diarahkan ke halaman tenant yang benar, bukan masuk ke platform admin.

### 6.3 Login dari landing page tenant

- GET /gbi
- GET /gbi/login

Landing page tenant menampilkan CTA login ke /gbi/login.

Setelah berhasil:

- tenant_owner/admin -> /gbi/admin
- user/jemaat -> /gbi/app atau halaman tenant sesuai permission

### 6.4 Session isolation

Minimal tahap awal:

- Session menyimpan tenant_id aktif.
- Setiap request tenant membandingkan session context dengan route context.
- Login tenant melakukan session regeneration.
- Logout menghapus auth dan tenant context.

Jika platform dan tenant menggunakan domain berbeda pada masa depan, pertimbangkan cookie/session name berbeda untuk platform dan tenant.

## 7. Route Architecture

Route dipisah menjadi tiga kelompok.

### 7.1 Platform routes

- /
- /login
- /admin/platform
- /admin/platform/tenants
- /admin/platform/plans
- /admin/platform/addons

Hanya master_admin atau platform permission tertentu.

### 7.2 Tenant public routes

- /{tenant}
- /{tenant}/login
- /{tenant}/register
- /{tenant}/about

/{tenant} merender page builder milik tenant tersebut.

### 7.3 Tenant application routes

- /{tenant}/admin
- /{tenant}/admin/users
- /{tenant}/admin/roles
- /{tenant}/web-editor
- /{tenant}/members
- /{tenant}/events
- /{tenant}/payments

Route tenant harus memakai middleware tenant sebelum auth, lalu membership dan permission.

Jangan memakai catch-all /{page:slug} tanpa prioritas route tenant. Resolver harus membedakan reserved platform paths, tenant slug, dan page slug.

## 8. Provisioning Tenant Baru

Saat seseorang mendaftar di gerejahub.com:

1. Validasi nama gereja dan slug.
2. Buat tenant dengan status pending atau active sesuai kebijakan pembayaran.
3. Buat user owner.
4. Buat default tenant roles: tenant_owner, tenant_admin, user, jemaat.
5. Buat membership owner sebagai tenant_owner.
6. Aktifkan addon sesuai plan.
7. Buat page home tenant.
8. Isi template atau onboarding state.
9. Commit semua langkah dalam transaction.
10. Kirim owner ke /{slug}/login atau /{slug}/admin.

Provisioning harus idempotent. Retry tidak boleh membuat duplicate slug, role, membership, atau page.

## 9. Subscription dan Tier

Tier adalah entitlement platform, bukan role.

Contoh:

| Plan | Fitur |
| --- | --- |
| Free | Website basic, member registration, limited users |
| Pro | Custom templates, events, attendance, lebih banyak users |
| Full | Semua church addons, reports, payments, exports |

Permission menentukan apa yang boleh dilakukan role. Entitlement plan menentukan addon apa yang tersedia untuk tenant.

Pemeriksaan akses harus memenuhi dua kondisi:

user permission allows action
AND tenant plan/addon is enabled

Jangan menyamakan is_admin=true dengan akses seluruh addon. Itu hanya menentukan kemampuan masuk ke admin shell.

## 10. Addon Contract Baru

Addon saat ini sudah memiliki manifest, menu, permission, dashboard card, route, migration, dan view namespace. Untuk GerejaHub, manifest perlu metadata tenancy.

Contoh metadata:

- slug: church-members;
- name: Church Members;
- scope: tenant;
- requires: core.tenant;
- permissions: church-members.view, church-members.create, church-members.update.

Nilai scope:

- platform: hanya berjalan di admin Novalynk;
- tenant: tersedia per organisasi dan seluruh datanya tenant-scoped;
- shared: fitur publik yang dapat membaca konfigurasi tenant tetapi tidak memiliki data operasional sendiri.

Setiap addon tenant wajib mendeklarasikan:

- migration dengan tenant_id;
- policy atau permission;
- menu context tenant/platform;
- dashboard card yang menghormati tenant context;
- service provisioning default data;
- uninstall/deactivation behavior;
- export/delete behavior untuk data tenant.

## 11. Pembagian Core dan Addon Gereja

### NovaBase Core

Tetap berada di core:

- user identity dan authentication;
- platform/tenant context;
- membership dan role engine;
- permission middleware;
- admin shell;
- web editor/page builder;
- addon registry dan plan entitlement;
- audit log dasar;
- notification abstraction;
- database bootstrap dan migration runner.

### GerejaHub platform module

Dapat berupa addon platform atau product layer:

- tenant registration;
- church slug reservation;
- onboarding wizard;
- plan/subscription;
- tenant activation/suspension;
- platform tenant management;
- invoice atau payment subscription platform.

### Gereja tenant addons

Contoh paket modular:

- addons/ChurchProfile;
- addons/ChurchMembers;
- addons/ChurchEvents;
- addons/ChurchAttendance;
- addons/ChurchGiving;
- addons/ChurchReports.

Jangan memasukkan semua domain gereja ke NovaBase core. Core hanya menyediakan kontrak yang dibutuhkan addon.

## 12. Website dan Web Editor Tenant

Web Editor NovaBase tetap dapat dipakai, tetapi semua state harus tenant-scoped.

Alur:

tenant_owner/admin
-> /gbi/web-editor
-> edit site_pages/site_blocks where tenant_id = gbi
-> preview /gbi
-> publish

Pisahkan status draft dan published jika website dikelola banyak admin. Target production sebaiknya memiliki site_page_versions dengan:

- site_page_id;
- tenant_id;
- version;
- state_json;
- status: draft, published, archived;
- published_by;
- published_at.

Tahap awal boleh memakai state langsung seperti sekarang, tetapi production sebaiknya memiliki versioning dan rollback.

## 13. Authorization Matrix

| Capability | master_admin | tenant_owner | tenant_admin | user | jemaat |
| --- | ---: | ---: | ---: | ---: | ---: |
| Manage all tenants | yes | no | no | no | no |
| Platform plans/addons | yes | no | no | no | no |
| Edit tenant website | support or explicit | yes | configurable | no | no |
| Role Management tenant | no by default | yes | no | no | no |
| User Management tenant | no by default | yes | configurable | no | no |
| Manage jemaat data | optional | yes | configurable | configurable | own profile |
| Access tenant admin | support mode | yes | yes | permission | no |
| Access another tenant | no by default | no | no | no | no |

master_admin tidak otomatis menjadi anggota semua tenant. Untuk support, gunakan explicit tenant impersonation atau support session yang diaudit dan memiliki expiry.

## 14. Security Rules Wajib

1. Semua query tenant data harus punya tenant boundary.
2. Jangan percaya tenant_id dari hidden input.
3. Tenant diambil dari route atau host yang sudah di-resolve middleware.
4. Verifikasi ownership di policy atau service, bukan hanya di Blade.
5. Semua create, update, dan delete addon memakai current tenant context.
6. Global admin tidak boleh bypass tanpa audit.
7. Tenant slug tidak boleh diubah bebas tanpa redirect atau alias policy.
8. Login harus mencegah tenant account masuk ke platform scope.
9. Upload file ditempatkan pada path tenant, misalnya tenants/{tenant_id}/.
10. Export, PDF, queue job, notification, dan cache key harus menyertakan tenant ID.
11. Cache key tidak boleh hanya memakai user_id atau slug fitur.
12. Job asynchronous harus membawa tenant context secara eksplisit.
13. Test wajib mencakup cross-tenant access denial.

## 15. Dampak terhadap NovaBase Saat Ini

NovaBase saat ini masih single-tenant pada beberapa bagian:

- users.role masih berupa role string tunggal;
- role dan permission belum memiliki tenant scope;
- site_pages dan site_blocks belum memiliki tenant boundary;
- EnsureUserHasRole belum membedakan platform scope dan tenant membership;
- login masih global di /login;
- AddonRegistry belum memiliki metadata scope dan entitlement plan;
- catch-all website route belum memiliki resolver tenant.

Artinya, GerejaHub tidak aman jika hanya menambah folder addon tanpa perubahan core. Addon bisnis bisa tetap modular, tetapi fondasi tenancy dan membership harus dikerjakan di NovaBase terlebih dahulu.

## 16. Tahapan Implementasi

### Phase 0 - Contract freeze

- Setujui istilah role dan scope.
- Setujui path URL.
- Setujui apakah email global unique.
- Setujui apakah tenant owner dapat mengakses root platform.
- Setujui tier dan addon entitlement.

### Phase 1 - Tenant foundation

- tenants dan tenant_domains;
- TenantContext;
- ResolveTenant middleware;
- reserved slug registry;
- tenant-aware session context;
- tenant-aware route groups.

### Phase 2 - Identity migration

- Pisahkan global user dari tenant membership.
- Migrasikan role lama menjadi platform atau tenant roles.
- Seed master_admin.
- Seed default tenant roles.
- Migrasikan user lama menjadi tenant membership untuk tenant default.

### Phase 3 - Tenant authentication

- Tenant login URL.
- Login scope validation.
- Redirect per role.
- Logout dan session regeneration.
- Cross-tenant authorization tests.

### Phase 4 - Tenant website

- Tambah tenant_id ke page builder tables.
- Tenant public renderer.
- Tenant web editor.
- Draft/publish boundary.
- Media path tenant.

### Phase 5 - Platform onboarding

- Register church.
- Slug reservation.
- Provisioning transaction.
- Plan and addon entitlement.
- Master admin tenant management.

### Phase 6 - Church addons

- Church profile.
- Members/jemaat.
- Events.
- Attendance.
- Giving/payment.
- Reports/export.

## 17. Keputusan yang Perlu Disepakati Sebelum Coding

1. Apakah satu email boleh memiliki membership di beberapa gereja?
2. Apakah satu user boleh switch tenant setelah login?
3. Apakah jemaat harus dapat login atau hanya menjadi data anggota?
4. Apakah pendaftaran gereja langsung aktif atau menunggu pembayaran/verifikasi?
5. Apakah plan dibuat oleh Novalynk atau dapat dipilih sendiri oleh tenant?
6. Apakah payment berarti subscription GerejaHub, donasi gereja, atau keduanya?
7. Apakah website tenant hanya satu landing page atau dapat memiliki banyak page?
8. Apakah tenant owner boleh mengubah slug setelah website aktif?
9. Apakah master_admin memerlukan support impersonation?
10. Apakah custom domain diperlukan pada fase pertama?
11. Apakah data gereja membutuhkan backup, export, dan delete mandiri?
12. Apakah Psychology Test juga akan menjadi tenant-aware atau tetap single-tenant?

## 18. Kesimpulan

GerejaHub dapat dibangun di atas NovaBase, tetapi arsitektur yang benar adalah:

NovaBase multi-tenant core
+ platform administration
+ tenant authentication and membership
+ tenant-aware web editor
+ addon registry with scope and entitlement
+ GerejaHub onboarding module
+ modular church addons

Inti desainnya adalah memisahkan:

- Identity: siapa orangnya.
- Membership: dia anggota tenant mana dan role-nya apa.
- Permission: fitur apa yang boleh dia gunakan.

Dengan pemisahan ini, GerejaHub dapat memiliki banyak gereja dalam satu aplikasi tanpa mencampur user, role, website, pembayaran, dan data operasional antar tenant.

