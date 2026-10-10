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

## Format entri pekerjaan berikutnya

```markdown
## YYYY-MM-DD - Judul task Card/Template

Status: SELESAI / PARSIAL / PENDING CORE
Pelaksana:
Branch dan referensi commit (jika tersedia):
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
