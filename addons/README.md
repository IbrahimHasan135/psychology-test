# NovaBase Addons

Folder ini disiapkan untuk fitur produk Novalynk berikutnya. Satu addon sebaiknya punya folder sendiri agar tidak mencampur fitur produk dengan core NovaBase.

Struktur yang disarankan:

```text
addons/
  nama-addon/
    README.md
    routes.php
    migrations/
    seeders/
    Controllers/
    Models/
    views/
```

Kontrak sederhana:

- Core NovaBase tetap berisi auth, role, admin shell, page management, dan website renderer.
- Addon berisi fitur produk spesifik.
- Jika addon butuh tabel, buat migration di folder addon lalu nanti bisa dipindah/di-load ke `database/migrations` saat fitur diaktifkan.
- Jika addon butuh menu admin, dokumentasikan route dan label menu di README addon.
- Jika addon butuh tampilan website, tambah template di `resources/views/website/card-templates` atau buat renderer addon sendiri.

Untuk tahap sekarang folder ini adalah boundary kerja agar next development tinggal menambah folder addon tanpa merusak base.