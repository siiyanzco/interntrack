# InternTrack – Sistem Monitoring PKL

## Cara menjalankan (XAMPP / Laragon)
1. Salin folder `InternTrack` ke `htdocs` (XAMPP) atau `www` (Laragon).
2. Jalankan Apache dan MySQL.
3. Buka phpMyAdmin → tab **Import** → pilih `database/interntrack.sql` → Go.
   (File ini otomatis membuat database `interntrack`, semua tabel, dan data dummy.)
4. Jika user/password MySQL bukan `root` / kosong, ubah di `config/koneksi.php`.
5. Buka `http://localhost/InternTrack/`

## Akun demo
| Peran | Username | Password |
|---|---|---|
| Admin | admin | admin123 |
| Pembimbing | pembimbing1 … pembimbing10 | password |
| Siswa | NIS, mis. 2026001 … 2026025 | password |

Dokumentasi (analisis, flowchart, ERD, test case, manual): `docs/DOKUMENTASI.md`
