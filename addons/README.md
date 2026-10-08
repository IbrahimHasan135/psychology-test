# NovaBase Addons

Folder ini disiapkan untuk fitur produk Novalynk berikutnya. Satu addon sebaiknya punya folder sendiri agar tidak mencampur fitur produk dengan core NovaBase.

Struktur yang disarankan:

```text
addons/
  NamaAddon/
    addon.php
    README.md
    routes/
      web.php
    database/
      migrations/
      seeders/
    app/
      Http/Controllers/
      Models/
      Services/
      Dashboard/
      Reports/
      Listeners/
    resources/
      views/
```

Kontrak sederhana:

- Core NovaBase tetap berisi auth, role, admin shell, page management, dan website renderer.
- Addon berisi fitur produk spesifik.
- Jika addon butuh tabel, buat migration di `addons/NamaAddon/database/migrations`.
- Jika addon butuh menu admin, daftarkan di `addon.php`.
- Jika addon butuh card dashboard, daftarkan class card di `dashboard_cards`.
- Jika addon butuh permission, daftarkan di `permissions` dengan format `slug.action`.
- Jika addon butuh tampilan website, tambah template di `resources/views/website/card-templates` atau buat renderer addon sendiri.

Contoh aktif saat ini ada di `addons/Demo`. Addon baru diaktifkan dari `config/addons.php`.
