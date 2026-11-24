# Sistem Triase & Skrining Diabetes AI — Puskesmas Harapan Sehat

Aplikasi web Laravel berbasis triase pasien rawat jalan dengan modul skrining risiko diabetes menggunakan inferensi model Machine Learning **100% Pure PHP** (tanpa Python runtime).

---

## Daftar Isi

1. [Tujuan Proyek](#1-tujuan-proyek)
2. [Sumber Data](#2-sumber-data)
3. [Alat & Versi](#3-alat--versi)
4. [Prasyarat Sistem](#4-prasyarat-sistem)
5. [Cara Menjalankan](#5-cara-menjalankan)
6. [Akun Demo & Login](#6-akun-demo--login)
7. [Simulasi Perawat — 3 Skenario Lengkap](#7-simulasi-perawat--3-skenario-lengkap)
8. [Simulasi Dokter — Respons Lengkap](#8-simulasi-dokter--respons-lengkap)
9. [Simulasi Skrining Diabetes AI](#9-simulasi-skrining-diabetes-ai)
10. [Menjalankan Tes Otomatis](#10-menjalankan-tes-otomatis)
11. [Struktur Proyek Penting](#11-struktur-proyek-penting)

---

## 1. Tujuan Proyek

Sistem ini dikembangkan sebagai **prototipe penelitian** untuk mendukung alur kerja klinis di puskesmas, dengan dua fungsi utama:

### 🏥 Triase Pasien Rawat Jalan
Membantu perawat menilai dan mengklasifikasikan prioritas pasien rawat jalan secara cepat berdasarkan tanda vital dan keluhan klinis ke dalam tiga kategori:

| Prioritas | Warna | Kondisi |
|---|---|---|
| 🔴 **Tinggi / Darurat** | Merah | Tanda vital kritis, perlu penanganan segera |
| 🟡 **Sedang / Waspada** | Kuning | Perlu observasi & konsultasi dokter |
| 🟢 **Rendah / Normal** | Hijau | Kondisi stabil, masuk antrian biasa |

### 🤖 Skrining Risiko Diabetes Berbasis AI
Membantu petugas Posbindu memprioritaskan peserta skrining yang membutuhkan pemeriksaan laboratorium lanjutan menggunakan model **Logistic Regression** yang dilatih dengan dataset Pima Indians Diabetes. Tersedia dalam dua mode:

- **Versi A — Skrining Lengkap (8 Fitur):** Menggunakan seluruh parameter termasuk Glucose dan Insulin.
- **Versi B — Triase Posbindu (6 Fitur Non-Lab):** Tanpa Glucose dan Insulin, untuk kondisi lapangan saat hasil lab belum tersedia.

> [!NOTE]
> Sistem ini adalah alat bantu skrining awal dan **bukan diagnosis medis**. Seluruh hasil harus dikonfirmasi oleh tenaga kesehatan yang berwenang.

---

## 2. Sumber Data

| Aspek | Detail |
|---|---|
| **Nama Dataset** | Pima Indians Diabetes Database |
| **Sumber** | National Institute of Diabetes and Digestive and Kidney Diseases (NIDDK) — dipublikasikan via [UCI Machine Learning Repository](https://archive.ics.uci.edu/dataset/34/diabetes) |
| **Jumlah Baris** | 768 sampel |
| **Fitur Prediktor** | 8 fitur: `Pregnancies`, `Glucose`, `BloodPressure`, `SkinThickness`, `Insulin`, `BMI`, `DiabetesPedigreeFunction`, `Age` |
| **Target** | `Outcome` — `1` = Positif Diabetes, `0` = Negatif |
| **Distribusi Kelas** | 65,1% negatif (500) vs 34,9% positif (268) — tidak seimbang |
| **File Lokal** | `ml/pima-indians-diabetes.csv` |

### Pra-pemrosesan Data
- **Nilai Nol Tidak Masuk Akal:** Kolom `Glucose`, `BloodPressure`, `SkinThickness`, `Insulin`, dan `BMI` yang bernilai `0` dikonversi ke `NaN` lalu diimputasi dengan **median data latih** (di dalam pipeline agar bebas *data leakage*).
- **Outlier Dipertahankan:** Nilai ekstrem seperti `Insulin > 500` atau `Glucose > 190` merupakan kondisi nyata pada pasien risiko tinggi, sehingga tidak dihapus.
- **Pembagian Data:** Stratified split 80% latih / 20% uji (`random_state=42`).

---

## 3. Alat & Versi

### 🖥️ Runtime & Framework (Produksi / Web)

| Alat | Versi | Keterangan |
|---|---|---|
| **PHP** | `^8.2` | Runtime utama aplikasi web |
| **Laravel** | `^12.0` | Framework web PHP |
| **Laravel Tinker** | `^2.10.1` | REPL interaktif untuk Laravel |
| **SQLite** | (bawaan PHP) | Database — extension `pdo_sqlite` |
| **Composer** | `2.x` | Package manager PHP |

### 🎨 Frontend & Build Tools

| Alat | Versi | Keterangan |
|---|---|---|
| **Vite** | `^6.0.11` | Build tool frontend |
| **Tailwind CSS** | `^4.0.0` | Framework CSS utility-first |
| **Laravel Vite Plugin** | `^1.2.0` | Integrasi Vite dengan Laravel |
| **Axios** | `^1.7.4` | HTTP client JavaScript |

### 🧪 Testing & Development (PHP)

| Alat | Versi | Keterangan |
|---|---|---|
| **PHPUnit** | `^11.5.3` | Framework pengujian PHP |
| **Laravel Pint** | `^1.13` | Code style fixer |
| **Faker PHP** | `^1.23` | Library data palsu untuk seeding |
| **Mockery** | `^1.6` | Mocking library untuk pengujian |

### 🤖 Machine Learning (Pelatihan Model — Python)

| Alat | Versi | Keterangan |
|---|---|---|
| **Python** | `3.x` (Google Colab) | Digunakan hanya untuk melatih model |
| **scikit-learn** | `1.6.1` | Pipeline ML: Logistic Regression, StandardScaler, SimpleImputer |
| **pandas** | — | Manipulasi dan analisis data |
| **numpy** | — | Komputasi numerik |
| **matplotlib** | — | Visualisasi data |
| **seaborn** | — | Visualisasi statistik |
| **joblib** | — | Ekspor model (`.joblib`) |

> [!IMPORTANT]
> Python **tidak diperlukan** untuk menjalankan aplikasi web. Model yang sudah dilatih diekspor ke `model_web_bundle.json` dan seluruh inferensi dilakukan langsung di PHP oleh `DiabetesScreeningService`.

---

## 4. Prasyarat Sistem

| Komponen | Versi Minimum | Catatan |
|---|---|---|
| PHP | 8.2 | Disarankan `C:\php82\php.exe` |
| Composer | 2.x | Download dari getcomposer.org |
| SQLite | (bawaan PHP) | Extension `pdo_sqlite` harus aktif |
| Browser | Chrome / Edge terbaru | Untuk akses UI |

> **Tidak perlu:** Python, MySQL, PostgreSQL, Redis, atau Node.js.

---

## 5. Cara Menjalankan

### 5.1 — Install PHP 8.2

1. Unduh **PHP 8.2 Thread Safe (TS)** untuk Windows dari:
   https://windows.php.net/download/ → pilih `VS16 x64 Thread Safe` → `.zip`

2. Ekstrak ke `C:\php82\` sehingga `C:\php82\php.exe` tersedia.

3. **Tambahkan ke PATH Windows:**
   - Tekan `Win + R` → ketik `sysdm.cpl` → Enter
   - Tab **Advanced** → **Environment Variables**
   - Di bagian *System variables*, klik `Path` → **Edit** → **New** → ketik `C:\php82`
   - Klik OK semua

4. **Aktifkan ekstensi wajib** di `C:\php82\php.ini`
   (salin dari `php.ini-development` jika belum ada):

   Cari baris berikut dan hilangkan titik koma (`;`) di depannya:

   ```ini
   extension=sqlite3
   extension=pdo_sqlite
   extension=mbstring
   extension=openssl
   extension=fileinfo
   extension=curl
   ```

5. Verifikasi:
   ```powershell
   C:\php82\php.exe -v
   # Output: PHP 8.2.x (cli) ...
   ```

---

### 5.2 — Install Composer

1. Unduh installer dari https://getcomposer.org/Composer-Setup.exe
2. Jalankan installer, arahkan ke `C:\php82\php.exe` saat diminta
3. Verifikasi:
   ```powershell
   composer --version
   # Output: Composer version 2.x.x ...
   ```

---

### 5.3 — Siapkan Proyek

Buka **PowerShell** di folder proyek:

```powershell
cd D:\Myproject\puskesmas-triase
```

**Install dependensi PHP** (jika folder `vendor/` belum ada):
```powershell
composer install
```

**Salin file konfigurasi lingkungan:**
```powershell
Copy-Item .env.example .env
```

**Generate application key:**
```powershell
C:\php82\php.exe artisan key:generate
```

---

### 5.4 — Konfigurasi Database SQLite

Pastikan isi `.env` menggunakan SQLite (bukan MySQL). Buka `.env` dan pastikan bagian database seperti ini:

```env
APP_NAME="Puskesmas Harapan Sehat"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=root
# DB_PASSWORD=
```

Buat file database SQLite kosong:
```powershell
New-Item -ItemType File database\database.sqlite -Force
```

**Jalankan migrasi & seeding data awal:**
```powershell
C:\php82\php.exe artisan migrate:fresh --seed
```

Output yang diharapkan:
```
INFO  Preparing database.
...
INFO  Seeding database.
  UserSeeder ........ DONE
  PatientSeeder ..... DONE
```

---

### 5.5 — Pastikan Model ML Tersedia

File model harus ada di:
```
storage\app\ml\model_web_bundle.json
```

Jika belum ada folder `ml`, buat:
```powershell
New-Item -ItemType Directory storage\app\ml -Force
```

Lalu salin file `model_web_bundle.json` ke folder tersebut.

---

### 5.6 — Jalankan Server Lokal

```powershell
cd D:\Myproject\puskesmas-triase
C:\php82\php.exe artisan serve
```

Output:
```
INFO  Server running on [http://127.0.0.1:8000].
```

Buka browser → **http://127.0.0.1:8000**

> Biarkan terminal ini tetap terbuka selama pengujian. Untuk menghentikan server tekan `Ctrl + C`.

---

## 6. Akun Demo & Login

Buka: **http://127.0.0.1:8000/masuk**

### Akun Perawat

| Field | Nilai |
|---|---|
| NIP atau Email | `PRW-001` atau `perawat@puskesmas.test` |
| Kata Sandi | `password123` |

Setelah login → diarahkan ke **/perawat** (Dashboard Perawat)

### Akun Dokter

| Field | Nilai |
|---|---|
| NIP atau Email | `DR-001` atau `dokter@puskesmas.test` |
| Kata Sandi | `password123` |

Setelah login → diarahkan ke **/dokter** (Dashboard Dokter)

---

## 7. Simulasi Perawat — 3 Skenario Lengkap

Login sebagai Perawat terlebih dahulu, lalu klik tombol **"+ Pasien Baru"** di dashboard.

---

### 🔴 SKENARIO 1 — Prioritas TINGGI (Darurat)

**Tujuan:** Mensimulasikan pasien dengan hiperglikemia berat, hipertensi urgensi, dan saturasi oksigen rendah — yang harus segera dirujuk ke dokter/UGD.

#### Isi Formulir Sebagai Berikut:

**Bagian 1 — Identitas Pasien:**

| Field | Nilai |
|---|---|
| Nama pasien | `Ny. Dewi Lestari` |
| Jenis kelamin | `Perempuan` |
| Usia / Age | `58` |
| No. Rekam Medis (RM) | *(biarkan auto, misal: RM-2026-00005)* |
| Riwayat kehamilan (Pregnancies) | `3` |

**Bagian 2 — Parameter & Hasil Tes Klinis:**

| Field | Nilai | Satuan |
|---|---|---|
| Glukosa darah sewaktu (Glucose) | `240` | mg/dL |
| Tekanan darah diastolik (BloodPressure) | `110` | mmHg |
| Ketebalan lipatan kulit (SkinThickness) | `35` | mm |
| Kadar insulin serum (Insulin) | `180` | mu U/ml |
| Indeks massa tubuh (BMI) | `36.5` | kg/m² |
| Fungsi silsilah diabetes (Pedigree/DPF) | `0.780` | skor |

**Tanda Vital Tambahan:**

| Field | Nilai | Satuan |
|---|---|---|
| Tekanan sistolik | `185` | mmHg |
| Nadi | `118` | x/menit |
| SpO2 | `90` | % |

**Catatan tambahan & keluhan klinis:**
```
Nyeri kepala hebat, pandangan kabur, sesak napas, riwayat DM tidak terkontrol, tidak rutin minum obat.
```

**Alergi (jika ada):**
```
Penisilin
```

#### Hasil yang Diharapkan:

- Sistem menampilkan label **🔴 PRIORITAS TINGGI**
- Alasan: Glucose >200, BP diastolik >100, SpO2 <92%, Nadi >100
- Kartu pasien muncul di dashboard dengan warna merah
- Tombol **"Hubungi Dokter / UGD"** aktif → klik untuk mengirim notifikasi darurat ke dokter

---

### 🟡 SKENARIO 2 — Prioritas SEDANG (Waspada)

**Tujuan:** Mensimulasikan pasien dengan hiperglikemia sedang dan faktor risiko keluarga — perlu konsultasi dokter namun bukan darurat segera.

#### Isi Formulir Sebagai Berikut:

**Bagian 1 — Identitas Pasien:**

| Field | Nilai |
|---|---|
| Nama pasien | `Tn. Agus Setiawan` |
| Jenis kelamin | `Laki-laki` |
| Usia / Age | `45` |
| No. Rekam Medis (RM) | *(biarkan auto)* |
| Riwayat kehamilan (Pregnancies) | `0` |

**Bagian 2 — Parameter & Hasil Tes Klinis:**

| Field | Nilai | Satuan |
|---|---|---|
| Glukosa darah sewaktu (Glucose) | `160` | mg/dL |
| Tekanan darah diastolik (BloodPressure) | `88` | mmHg |
| Ketebalan lipatan kulit (SkinThickness) | `28` | mm |
| Kadar insulin serum (Insulin) | `140` | mu U/ml |
| Indeks massa tubuh (BMI) | `29.8` | kg/m² |
| Fungsi silsilah diabetes (Pedigree/DPF) | `0.520` | skor |

**Tanda Vital Tambahan:**

| Field | Nilai | Satuan |
|---|---|---|
| Tekanan sistolik | `150` | mmHg |
| Nadi | `95` | x/menit |
| SpO2 | `95` | % |

**Catatan tambahan & keluhan klinis:**
```
Lemas, sering haus, kencing malam hari lebih dari 3 kali, riwayat keluarga diabetes (ayah), nafsu makan meningkat.
```

**Alergi:** *(kosongkan)*

#### Hasil yang Diharapkan:

- Sistem menampilkan label **🟡 PRIORITAS SEDANG**
- Alasan: Glucose 160 (di atas normal), TD sistolik 150 (borderline hipertensi)
- Kartu pasien muncul di dashboard dengan warna kuning
- Perawat dapat memantau dan mendorong konsultasi ke dokter saat giliran tiba

---

### 🟢 SKENARIO 3 — Prioritas RENDAH (Normal)

**Tujuan:** Mensimulasikan pasien kontrol rutin dengan semua nilai dalam batas normal — masuk antrean biasa.

#### Isi Formulir Sebagai Berikut:

**Bagian 1 — Identitas Pasien:**

| Field | Nilai |
|---|---|
| Nama pasien | `Ny. Fitri Handayani` |
| Jenis kelamin | `Perempuan` |
| Usia / Age | `32` |
| No. Rekam Medis (RM) | *(biarkan auto)* |
| Riwayat kehamilan (Pregnancies) | `1` |

**Bagian 2 — Parameter & Hasil Tes Klinis:**

| Field | Nilai | Satuan |
|---|---|---|
| Glukosa darah sewaktu (Glucose) | `95` | mg/dL |
| Tekanan darah diastolik (BloodPressure) | `72` | mmHg |
| Ketebalan lipatan kulit (SkinThickness) | `20` | mm |
| Kadar insulin serum (Insulin) | `85` | mu U/ml |
| Indeks massa tubuh (BMI) | `23.4` | kg/m² |
| Fungsi silsilah diabetes (Pedigree/DPF) | `0.280` | skor |

**Tanda Vital Tambahan:**

| Field | Nilai | Satuan |
|---|---|---|
| Tekanan sistolik | `115` | mmHg |
| Nadi | `76` | x/menit |
| SpO2 | `98` | % |

**Catatan tambahan & keluhan klinis:**
```
Pemeriksaan rutin tahunan. Tidak ada keluhan berarti. Pola makan baik, rutin olahraga 3x seminggu.
```

**Alergi:** *(kosongkan)*

#### Hasil yang Diharapkan:

- Sistem menampilkan label **🟢 PRIORITAS RENDAH**
- Semua nilai dalam batas normal
- Kartu pasien muncul di dashboard dengan warna hijau
- Masuk antrean biasa, tidak membutuhkan intervensi segera

---

## 8. Simulasi Dokter — Respons Lengkap

Login sebagai Dokter: **http://127.0.0.1:8000/masuk** dengan akun `DR-001` / `password123`

Dashboard Dokter menampilkan daftar pasien yang sudah ditriase oleh perawat.

---

### 🔴 Respons Dokter untuk Skenario 1 (Ny. Dewi Lestari — DARURAT)

**Situasi:** Banner merah muncul di bagian atas dashboard — "⚠️ PASIEN DARURAT — Ny. Dewi Lestari membutuhkan perhatian segera!"

#### Langkah Dokter:

1. Klik banner merah atau tombol **"Segera Menuju Pasien"** / **"Tangani Sekarang"**
2. Halaman pemeriksaan pasien terbuka → isi formulir pemeriksaan dokter:

**Diagnosis:**
```
Hipertensi Urgensi dengan Hiperglikemia Tidak Terkontrol. Suspect Diabetes Mellitus Tipe 2 dengan komplikasi hipertensi grade III. Perlu observasi ketat dan pemeriksaan laboratorium lanjutan.
```

**Catatan Klinis / SOAP:**
```
Pasien datang dengan keluhan nyeri kepala hebat, pandangan kabur, dan sesak napas. 
Pemeriksaan: TD 185/110 mmHg, Nadi 118x/mnt, SpO2 90%, GDS 240 mg/dL.
Tindakan awal: Tirah baring posisi semi-Fowler, pasang oksigen 3L/menit via nasal kanul, 
pasang infus NaCl 0.9% 20 tetes/menit. Pantau tanda vital tiap 15 menit.
Periksa GDS ulang setelah 2 jam. Jadwalkan pemeriksaan GDP dan HbA1c segera.
Alergi penisilin sudah dicatat — HINDARI golongan penisilin.
```

**Resep Obat:**
```
1. Amlodipine 10 mg — 1 x 1 (pagi) — untuk kontrol tekanan darah
2. Captopril 25 mg — 2 x 1 (pagi & malam) — untuk proteksi ginjal
3. Metformin 500 mg — 2 x 1 (makan pagi & malam) — mulai setelah kondisi stabil
4. Oksigen 3 L/menit via nasal kanul — hingga SpO2 ≥ 95%
```

**Tindakan / Disposisi:**
- Pilih: **"Perlu Penanganan Lanjutan / Observasi"**
- Atau: **"Rujuk ke Fasilitas Lanjutan"** jika SpO2 tidak membaik setelah oksigen

Klik **"Simpan & Selesai"** → Status pasien berubah menjadi "Sedang Ditangani / Observasi".

---

### 🟡 Respons Dokter untuk Skenario 2 (Tn. Agus Setiawan — SEDANG)

**Situasi:** Kartu pasien kuning muncul di daftar antrian dokter.

#### Langkah Dokter:

1. Klik nama pasien atau tombol **"Periksa"** pada kartu Tn. Agus Setiawan
2. Isi formulir pemeriksaan:

**Diagnosis:**
```
Hiperglikemia Sedang — Suspect Prediabetes / Diabetes Mellitus Tipe 2 Awal. 
Hipertensi Grade I (TD 150/88 mmHg). Faktor risiko: obesitas ringan (BMI 29.8), 
riwayat keluarga DM positif, disfungsi silsilah diabetes (DPF 0.52).
```

**Catatan Klinis / SOAP:**
```
Pasien datang dengan keluhan lemas, polidipsi, dan nokturia. GDS 160 mg/dL (di atas 
batas normal <140 mg/dL pp). TD 150/88 mmHg — hipertensi grade I. BMI 29.8 (overweight).
Rencana: Periksa GDP (Gula Darah Puasa) dan HbA1c dalam 1 minggu ke depan.
Edukasi: Pola makan rendah gula dan karbohidrat sederhana, perbanyak serat dan sayur,
olahraga aerobik minimal 150 menit/minggu, kurangi konsumsi minuman manis dan makanan berlemak.
Kontrol ulang 2 minggu untuk evaluasi hasil lab dan respons terapi awal.
```

**Resep Obat:**
```
1. Metformin 500 mg — 1 x 1 (makan malam) — terapi awal hiperglikemia
2. Vitamin B Kompleks — 1 x 1 (pagi) — untuk keluhan lemas
3. Amlodipin 5 mg — 1 x 1 (pagi) — kontrol hipertensi grade I
```

**Tindakan / Disposisi:**
- Pilih: **"Tandai Selesai Ditangani"**
- Tambahkan jadwal kontrol: **2 minggu ke depan**

Klik **"Simpan & Selesai"** → Status berubah menjadi "Selesai Ditangani".

---

### 🟢 Respons Dokter untuk Skenario 3 (Ny. Fitri Handayani — RENDAH)

**Situasi:** Kartu pasien hijau di daftar antrian — kontrol rutin.

#### Langkah Dokter:

1. Klik nama pasien atau tombol **"Periksa"** pada kartu Ny. Fitri Handayani
2. Isi formulir pemeriksaan:

**Diagnosis:**
```
Tidak ditemukan indikasi diabetes mellitus maupun prediabetes. 
Semua parameter dalam batas normal. Kondisi kesehatan umum baik.
```

**Catatan Klinis / SOAP:**
```
Pasien datang untuk pemeriksaan rutin tahunan. GDS 95 mg/dL (normal), 
TD 115/72 mmHg (normal), Nadi 76x/mnt (normal), SpO2 98% (normal), BMI 23.4 (normal).
Tidak ada keluhan berarti. Pola hidup sehat sudah berjalan dengan baik.
Anjuran: Lanjutkan pola hidup sehat — konsumsi makanan bergizi, rutin olahraga, 
istirahat cukup, hindari stres berlebihan. Kontrol rutin 1 tahun ke depan atau 
bila ada keluhan baru.
```

**Resep Obat:**
```
(tidak ada — kondisi baik, tidak memerlukan terapi farmakologi)
```

**Tindakan / Disposisi:**
- Pilih: **"Tandai Selesai Ditangani"**

Klik **"Simpan & Selesai"** → Status berubah menjadi "Selesai Ditangani".

---

## 9. Simulasi Skrining Diabetes AI

Akses modul AI secara terpisah di: **http://127.0.0.1:8000/screening**

> ⚠️ **PERINGATAN KLINIS:** Sistem ini adalah alat bantu skrining awal dan **BUKAN diagnosis medis**. Hasil skrining harus selalu dikonfirmasi oleh tenaga kesehatan yang berwenang.

### Mode A — Skrining Lengkap (8 Fitur, termasuk Lab)

#### Skenario Pasien Berisiko Tinggi:

| Parameter | Nilai |
|---|---|
| Pregnancies | `4` |
| Glucose | `148` |
| BloodPressure | `76` |
| SkinThickness | `30` |
| Insulin | `0` *(akan diimputasi otomatis)* |
| BMI | `33.6` |
| DiabetesPedigreeFunction | `0.627` |
| Age | `45` |
| Threshold | `0.35` (default Versi A) |

**Hasil yang Diharapkan:**
- **Probabilitas Risiko: ~77.57%**
- **Status: 🔴 Prioritas Pemeriksaan Laboratorium Lanjut**
- Insulin diimputasi otomatis dari nilai median dataset (nilai `0` pada insulin dianggap tidak terukur)
- Faktor teratas: Glucose, BMI, Age, DiabetesPedigreeFunction

#### Skenario Pasien Risiko Rendah:

| Parameter | Nilai |
|---|---|
| Pregnancies | `1` |
| Glucose | `95` |
| BloodPressure | `70` |
| SkinThickness | `20` |
| Insulin | `85` |
| BMI | `23.4` |
| DiabetesPedigreeFunction | `0.200` |
| Age | `28` |
| Threshold | `0.35` |

**Hasil yang Diharapkan:**
- **Probabilitas Risiko: <30%**
- **Status: 🟢 Risiko Rendah — Pemantauan Rutin**

---

### Mode B — Triase Posbindu (6 Fitur Non-Lab)

**Gunakan Mode B jika tidak ada hasil lab** (Glucose dan Insulin tidak diperlukan).

#### Skenario Pasien Berisiko Tinggi:

| Parameter | Nilai |
|---|---|
| Pregnancies | `4` |
| BloodPressure | `76` |
| SkinThickness | `30` |
| BMI | `33.6` |
| DiabetesPedigreeFunction | `0.627` |
| Age | `45` |
| Threshold | `0.40` (default Versi B) |

**Hasil yang Diharapkan:**
- **Probabilitas Risiko: ~63.81%**
- **Status: 🔴 Prioritas Pemeriksaan Laboratorium Lanjut**

---

### Fitur Slider Threshold Tuning

Di bawah form skrining terdapat **slider Ambang Keputusan** (0.25 – 0.60):

- **Threshold rendah (misal 0.25):** Sensitivitas lebih tinggi → lebih banyak kasus positif terdeteksi (cocok untuk skrining massal)
- **Threshold tinggi (misal 0.55):** Spesifisitas lebih tinggi → lebih sedikit false positive (cocok untuk konfirmasi)
- Geser slider → panel pratinjau menampilkan nilai Sensitivitas & Spesifisitas secara real-time dari tabel threshold_tuning model

---

## 10. Menjalankan Tes Otomatis

```powershell
cd D:\Myproject\puskesmas-triase
C:\php82\php.exe artisan test
```

Output yang diharapkan:
```
   PASS  Tests\Feature\AuthTest
   PASS  Tests\Feature\TriaseTest
   PASS  Tests\Feature\ScreeningTest

  Tests:    13 passed (43 assertions)
  Duration: X.XXs
```

Verifikasi khusus prediksi ML:
```powershell
C:\php82\php.exe artisan test --filter=ScreeningTest
```

---

## 11. Struktur Proyek Penting

```
puskesmas-triase/
├── app/
│   ├── Http/Controllers/
│   │   ├── ScreeningController.php     ← Controller skrining AI
│   │   ├── TriaseController.php        ← Controller triase pasien
│   │   └── Auth/LoginController.php    ← Autentikasi
│   ├── Services/
│   │   └── DiabetesScreeningService.php ← Inferensi ML Pure PHP
│   └── Models/
│       ├── User.php
│       └── Patient.php
├── database/
│   ├── database.sqlite                 ← Database SQLite
│   └── seeders/
│       ├── UserSeeder.php              ← 2 user demo
│       └── PatientSeeder.php           ← 4 pasien awal
├── ml/                                 ← Artefak Machine Learning
│   ├── pima-indians-diabetes.csv       ← Dataset sumber (UCI / NIDDK)
│   ├── notebook_skrining_diabetes_final.ipynb ← Notebook pelatihan model
│   ├── model_skrining_diabetes.joblib  ← Model Python (scikit-learn)
│   └── model_web_bundle.json           ← Model diekspor ke JSON untuk PHP
├── resources/views/
│   ├── screening/index.blade.php       ← UI Skrining AI
│   ├── perawat/                        ← Dashboard Perawat
│   └── dokter/                         ← Dashboard Dokter
├── routes/web.php                      ← Semua rute
├── storage/app/ml/
│   └── model_web_bundle.json           ← Model ML aktif (Logistic Regression)
├── tests/Feature/
│   └── ScreeningTest.php               ← Tes prediksi ML
└── .env                                ← Konfigurasi (SQLite, APP_KEY, dll.)
```

---

## 10. Reset Data — Ulangi Simulasi dari Awal

Jika ingin mengulangi simulasi dan menghapus semua data yang sudah diinput, cukup jalankan **satu perintah** berikut:

```powershell
cd D:\Myproject\puskesmas-triase
C:\php82\php.exe artisan migrate:fresh --seed
```

### Apa yang terjadi:

| Langkah | Efek |
|---|---|
| `migrate:fresh` | Hapus semua tabel & data (termasuk pasien hasil simulasi) |
| `--seed` | Isi ulang dengan **2 user demo** + **4 pasien awal** dari seeder |

### Output yang diharapkan:
```
INFO  Preparing database.
  Dropping all tables ........................................ 10ms DONE

INFO  Running migrations.
  ...

INFO  Seeding database.
  UserSeeder ......... DONE
  PatientSeeder ...... DONE
```

Setelah selesai, **tanpa perlu restart server** — langsung buka browser dan login lagi. Data sudah kembali bersih ke kondisi sebelum simulasi. ✅

> **Tips:** Jika server `php artisan serve` sedang berjalan, **jangan dimatikan**. Buka tab/window PowerShell baru untuk menjalankan perintah reset di atas, lalu kembali ke browser dan mulai simulasi ulang.

---

## Troubleshooting

### ❌ Error: "No application encryption key has been specified"
```powershell
C:\php82\php.exe artisan key:generate
```

### ❌ Error: "SQLSTATE[HY000]: General error: 1 no such table"
```powershell
C:\php82\php.exe artisan migrate:fresh --seed
```

### ❌ Error: "Class DiabetesScreeningService not found"
```powershell
composer dump-autoload
```

### ❌ Login gagal (kredensial benar tapi ditolak)
Database mungkin kosong atau seed gagal:
```powershell
C:\php82\php.exe artisan migrate:fresh --seed
```

### ❌ Error: "failed to open stream: No such file or directory" pada model JSON
Pastikan file ada di path yang benar:
```powershell
Test-Path D:\Myproject\puskesmas-triase\storage\app\ml\model_web_bundle.json
# Harus output: True
```

### ❌ Halaman error 500 setelah setup
Periksa log error:
```powershell
Get-Content D:\Myproject\puskesmas-triase\storage\logs\laravel.log -Tail 50
```

---

## Penjelasan Teknis: Bagaimana Model AI Dihubungkan ke Web

### Pipeline Inferensi Pure PHP

Model yang digunakan adalah **Logistic Regression** yang dilatih di Python (scikit-learn) dan diekspor ke format JSON melalui file `model_web_bundle.json`.

Saat inferensi (prediksi) di PHP:

1. **Load JSON:** `DiabetesScreeningService` membaca `model_web_bundle.json` sekali saat pertama kali diakses (cached).

2. **Imputasi Nilai Kosong:** Jika field klinis (Glucose, Insulin, dll.) bernilai `0` atau kosong, nilainya diganti dengan **median dataset** yang tersimpan di JSON — persis seperti `SimpleImputer` di scikit-learn.

3. **Standarisasi Fitur (StandardScaler):**
   ```
   z = (nilai_input - scaler_mean) / scaler_scale
   ```

4. **Kalkulasi Logit:**
   ```
   logit = intercept + Σ (koefisien[f] × z[f])
   ```

5. **Fungsi Sigmoid:**
   ```
   probabilitas = 1 / (1 + e^(-logit))
   ```

6. **Keputusan:** Jika `probabilitas ≥ threshold` → **Positif Risiko Tinggi**

Seluruh pipeline ini **identik dengan** `sklearn.pipeline.Pipeline([StandardScaler, LogisticRegression]).predict_proba()` — sehingga hasil PHP dan Python **selalu sama persis**.

---

*Sistem ini dikembangkan sebagai prototipe penelitian. Tidak untuk digunakan sebagai dasar keputusan klinis tanpa validasi tenaga kesehatan yang berwenang.*
