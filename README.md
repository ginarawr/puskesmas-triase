# Sistem Triase Puskesmas (Laravel 12)

Aplikasi web triase pasien untuk puskesmas, dengan dua peran: **perawat** (input hasil tes, klasifikasi prioritas, panggil pasien, hubungi dokter/UGD) dan **dokter** (panggilan darurat, daftar pasien, pemeriksaan). Tampilan mengikuti desain di `Untitled.zip`, alurnya mengikuti diagram alur sistem. Responsif untuk HP, tablet, dan layar PC.

## Menjalankan di komputer lokal

Prasyarat: **PHP 8.2+** (ekstensi `pdo_sqlite`, `mbstring`, `openssl`, `fileinfo`), **Composer**, dan koneksi internet saat setup pertama. Tidak perlu Node/npm, MySQL, atau Docker.

```bash
# macOS / Linux / WSL / Git Bash
./setup.sh

# Windows
setup.bat
```

Skrip akan mengunduh kerangka Laravel 12 resmi, menumpuk kode proyek ini di atasnya (file milik proyek tidak ditimpa), membuat database SQLite, mengisi data contoh, lalu menjalankan server di <http://127.0.0.1:8000>. Selanjutnya cukup `php artisan serve`.

Akun contoh (kata sandi `password123`):

| Peran   | Email                    | NIP    |
|---------|--------------------------|--------|
| Perawat | perawat@puskesmas.test   | PRW-001 |
| Dokter  | dokter@puskesmas.test    | DR-001  |

Reset data contoh kapan saja: `php artisan migrate:fresh --seed`.

## Mencoba alur (disarankan buka dua browser/jendela privat)

1. Login perawat. Di daftar pasien, Tn. Rahmat berprioritas **tinggi**: klik **Hubungi Dokter / UGD**.
2. Login dokter. Banner **panggilan darurat** muncul (maksimal 10 detik, halaman memperbarui diri). Klik **Segera Menuju Pasien**.
3. Di halaman pemeriksaan isi diagnosis, catatan, resep/tindakan, disposisi. Pilih **Tandai Pasien Selesai Ditangani** atau **Perlu Penanganan Lanjutan / Observasi** (pasien tetap di daftar).
4. Perawat: **+ Pasien Baru** menampilkan evaluasi prioritas langsung saat form diisi. **Panggil Pasien** menampilkan pesan dan membacakan panggilan dengan suara browser (tombol "Putar suara" bila browser menahan suara otomatis).

## Struktur & cara mengubah tampilan

Layout dan komponen ada di `resources/views/components` (Blade anonymous components, dipakai sebagai `<x-...>`):

| Komponen | Fungsi |
|---|---|
| `layouts/app`, `layouts/guest` | Kerangka halaman. Slot: `header`, isi utama, `footer`. Parameter `width="wide\|narrow"` |
| `navbar`, `navbar-back` | Bilah atas (dashboard / halaman detail) |
| `ui/button`, `input`, `select`, `textarea`, `choice` | Kontrol form & tombol (`variant`, `size`, `icon`) |
| `ui/card`, `stat-card`, `badge`, `data-table`, `filter-tabs`, `flash`, `empty-state`, `page-header` | Blok halaman |
| `patient/*`, `dokter/*`, `auth/card` | Komponen khusus domain |

Contoh halaman baru cukup menyusun komponen:

```blade
<x-layouts.app title="Judul">
    <x-slot:header><x-navbar title="Dashboard" /></x-slot:header>
    <x-ui.card title="Kartu"> ... </x-ui.card>
</x-layouts.app>
```

Warna, jarak, dan font diatur lewat variabel di `public/css/app.css` (`:root`). CSS ditulis tangan (tanpa build step) supaya langsung jalan.

Peta alur → kode:

- Login / daftar akun / cek peran → `AuthController`, `EnsureRole`
- Dashboard perawat, ada pasien baru?, form hasil tes → `Perawat\DashboardController`, `Perawat\PatientController`
- Prioritas tinggi/sedang/rendah → `App\Services\TriageClassifier`
- Dashboard dokter, panggilan darurat, menuju pasien, selesai? → `Dokter\DashboardController`, `Dokter\ExaminationController`
- Rute: `routes/web.php`

## Catatan penting

- **Klasifikasi prioritas adalah aturan sederhana**, bukan model ML terlatih dan bukan alat diagnosis. Ambang batas (glukosa, tekanan darah, nadi, SpO2, skor risiko) ada sebagai konstanta di `TriageClassifier` dan **harus ditinjau tenaga medis** sebelum dipakai nyata. Perawat dapat mengganti prioritas secara manual di form.
- Ini aplikasi demo: belum ada audit log, reset kata sandi ("Lupa sandi?" hanya menampilkan petunjuk), verifikasi NIP/STR, atau enkripsi data pasien. Tambahkan sebelum dipakai dengan data pasien sungguhan.
- Pembaruan "realtime" memakai polling tiap 10 detik (`config/triase.php`), bukan WebSocket.
- Zona waktu bawaan Asia/Jakarta; ubah `APP_TIMEZONE` di `.env`.
- Jalankan tes: `php artisan test`.
