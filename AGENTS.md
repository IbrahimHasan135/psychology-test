# Aturan branch: Card dan Template Website Editor

Aturan ini berlaku untuk developer manusia dan coding agent yang bekerja di repo ini. Baca [scope pengembangan](docs/WEBSITE_EDITOR_DEVELOPMENT_SCOPE.md) dan [catatan perubahan](docs/WEBSITE_EDITOR_CHANGELOG.md) sebelum mengedit.

- Fokus pekerjaan hanya Card dan Template di Website Editor. Jangan memperluas pekerjaan ke modul lain, termasuk perbaikan sampingan dan refactor umum.
- Branch aktif saat pembaruan 2026-10-11: `feat/web-editor-frontend`. Verifikasi branch aktual sebelum bekerja. Pemilik repo mengotorisasi commit dan push otomatis setiap selesai satu task/prompt ke branch kerja di `origin`. Jangan membuat, mengganti, atau merge branch tanpa instruksi yang sesuai. Jangan push ke `main`, `master`, atau `upstream` secara otomatis.
- Perubahan frontend hanya boleh menyentuh bagian Card/Template yang disebut dalam scope. File bersama bukan izin mengubah seluruh isinya.
- Base code NovaBase dilindungi. Jika fitur memerlukan perubahan core/backend, hentikan bagian yang bergantung padanya, tampilkan `WARNING: PERUBAHAN BASE CODE NOVABASE DIPERLUKAN`, dan catat kebutuhan tersebut. Jangan mengeksekusi perubahan core secara otomatis.
- Serahkan kebutuhan core ke pemilik/maintainer NovaBase. Pelaksana Card/Template tetap mengerjakan bagian yang aman dan independen. Implementasi core harus menjadi pekerjaan terpisah dengan otorisasi eksplisit pemilik repo.
- Jangan menonaktifkan validasi, permission, tenant isolation, CSRF, atau escaping untuk membuat desain bekerja.
- Setiap pekerjaan wajib mencatat file dan bagian yang diubah, alasan, dampak, pemeriksaan aktual, serta warning/pending di `docs/WEBSITE_EDITOR_CHANGELOG.md`. Jangan menulis hasil pengujian yang belum dilakukan.
- Jangan menghapus perubahan developer lain. Jangan deploy, menjalankan migrasi/seeder, mengubah data website, atau memperbarui dependency sebagai bagian otomatis dari pekerjaan desain.

Instruksi eksplisit terbaru pemilik repo menentukan perubahan scope. Permintaan umum seperti “selesaikan Card” bukan izin implisit mengubah core.

## Wajib commit dan push setiap task/prompt

Atas instruksi pemilik repo tanggal 2026-10-11, setiap task/prompt yang menghasilkan perubahan harus ditutup dengan commit dan push ke GitHub sebelum laporan akhir. Ini berlaku juga untuk dokumentasi dan setup yang diminta secara eksplisit. Tidak perlu meminta konfirmasi commit/push lagi selama tetap di scope dan branch kerja yang benar.

1. Periksa branch, remote, status dan diff awal. Pisahkan perubahan task dari perubahan milik developer lain; jangan memakai `git add .` atau `git add -A` tanpa meninjau setiap file.
2. Selesaikan pemeriksaan yang relevan dan perbarui `docs/WEBSITE_EDITOR_CHANGELOG.md`. Stage hanya file/hunk milik task; jangan commit secret, `.env`, runtime, dependency terpasang atau hasil build yang diabaikan Git.
3. Periksa `git diff --cached` dan `git diff --cached --check`. Buat minimal satu commit untuk setiap task/prompt yang menghasilkan perubahan siap disimpan, dengan pesan yang menjelaskan hasil. Jangan menggabungkan beberapa task yang sudah selesai ke satu commit tertunda.
4. Jalankan `git push origin <branch-kerja-aktual>` setelah commit; gunakan `-u` bila upstream branch belum ditetapkan. Verifikasi SHA HEAD lokal sama dengan SHA branch remote melalui `git ls-remote origin refs/heads/<branch>`.
5. Laporan akhir wajib memuat SHA commit, branch tujuan, status push dan hasil pemeriksaan. Jangan mengklaim sudah push hanya karena commit lokal berhasil.

Jika tidak ada perubahan, tidak perlu membuat empty commit. Jika task terblokir core, commit/push bagian independen yang sudah aman beserta dokumentasi handoff; jangan commit fitur rusak sebagai selesai. Jika pemeriksaan gagal, perbaiki dahulu; jika belum bisa, laporkan alasan dan bagian yang belum dipush secara jelas. Jika push ditolak, akses Git/network tidak tersedia, atau remote memiliki perubahan baru, pertahankan commit lokal dan laporkan kendalanya; jangan force push, reset, amend histori yang sudah dipush, melewati hook atau mengubah remote agar terlihat berhasil. Integrasi perubahan remote harus mempertahankan pekerjaan developer lain dan batas scope.
