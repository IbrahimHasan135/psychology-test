# Aturan branch: Card dan Template Website Editor

Aturan ini berlaku untuk developer manusia dan coding agent yang bekerja di repo ini. Baca [scope pengembangan](docs/WEBSITE_EDITOR_DEVELOPMENT_SCOPE.md) dan [catatan perubahan](docs/WEBSITE_EDITOR_CHANGELOG.md) sebelum mengedit.

- Fokus pekerjaan hanya Card dan Template di Website Editor. Jangan memperluas pekerjaan ke modul lain, termasuk perbaikan sampingan dan refactor umum.
- Branch yang teramati saat aturan dibuat: `feat/website-editor-frontend`. Jangan membuat, mengganti, merge, atau push branch tanpa instruksi pekerjaan yang sesuai. Aturan ini tetap berlaku jika dokumen ikut dibawa ke branch lain sampai pemilik repo mengubah scope secara eksplisit.
- Perubahan frontend hanya boleh menyentuh bagian Card/Template yang disebut dalam scope. File bersama bukan izin mengubah seluruh isinya.
- Base code NovaBase dilindungi. Jika fitur memerlukan perubahan core/backend, hentikan bagian yang bergantung padanya, tampilkan `WARNING: PERUBAHAN BASE CODE NOVABASE DIPERLUKAN`, dan catat kebutuhan tersebut. Jangan mengeksekusi perubahan core secara otomatis.
- Serahkan kebutuhan core ke pemilik/maintainer NovaBase. Pelaksana Card/Template tetap mengerjakan bagian yang aman dan independen. Implementasi core harus menjadi pekerjaan terpisah dengan otorisasi eksplisit pemilik repo.
- Jangan menonaktifkan validasi, permission, tenant isolation, CSRF, atau escaping untuk membuat desain bekerja.
- Setiap pekerjaan wajib mencatat file dan bagian yang diubah, alasan, dampak, pemeriksaan aktual, serta warning/pending di `docs/WEBSITE_EDITOR_CHANGELOG.md`. Jangan menulis hasil pengujian yang belum dilakukan.
- Jangan menghapus perubahan developer lain. Jangan deploy, menjalankan migrasi/seeder, mengubah data website, atau memperbarui dependency sebagai bagian otomatis dari pekerjaan desain.

Instruksi eksplisit terbaru pemilik repo menentukan perubahan scope. Permintaan umum seperti “selesaikan Card” bukan izin implisit mengubah core.
