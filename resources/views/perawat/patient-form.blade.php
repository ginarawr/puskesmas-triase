<x-layouts.app title="Pasien baru" width="narrow">
    <x-ui.page-header title="Form Input Data Kesehatan Pasien (Hasil Tes)" subtitle="Lengkapi data untuk penentuan prioritas penanganan pasien secara otomatis.">
        <x-slot:actions>
            <x-ui.button :href="route('perawat.dashboard')" variant="outline" size="sm" icon="arrow-left">Kembali ke Dashboard</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <form method="POST" action="{{ route('perawat.pasien.store') }}" class="stack-lg" novalidate
          data-evaluate-url="{{ route('perawat.pasien.evaluate') }}">
        @csrf

        @if ($errors->any())
            <div class="toast toast-error" role="alert">
                <x-ui.icon name="alert-circle" :size="20" />
                <p>Ada {{ $errors->count() }} isian yang perlu diperbaiki. Cek kolom bertanda merah.</p>
            </div>
        @endif

        <x-ui.card step="1" title="Identitas Pasien">
            <div class="form-grid">
                <x-ui.input name="name" label="Nama pasien" placeholder="Contoh: Ny. Siti Rahmah" required wrap-class="col-2" />
                <x-ui.select name="gender" label="Jenis kelamin" value="P" :options="['P' => 'Perempuan', 'L' => 'Laki-laki']" />
                <x-ui.input name="age" label="Usia / Age (tahun)" type="number" min="0" max="120" inputmode="numeric" required />
                <x-ui.input name="medical_record_no" label="No. Rekam Medis (RM)" :value="$nextRm" class="mono" required />
                <x-ui.input name="pregnancies" label="Riwayat kehamilan (Pregnancies)" type="number" min="0" max="20" value="0" inputmode="numeric" required />
            </div>
        </x-ui.card>

        <x-ui.card step="2" title="Parameter & Hasil Tes Klinis (8 Atribut Pima Diabetes)">
            <x-slot:actions><x-ui.badge>Pima standard</x-ui.badge></x-slot:actions>
            <div class="form-grid form-grid-3">
                <x-ui.input name="glucose" label="1. Glukosa darah sewaktu (Glucose)" type="number" min="20" max="600" inputmode="numeric" suffix="mg/dL" required />
                <x-ui.input name="diastolic_bp" label="2. Tekanan darah diastolik (BloodPressure)" type="number" min="20" max="200" inputmode="numeric" suffix="mmHg" required />
                <x-ui.input name="skin_thickness" label="3. Ketebalan lipatan kulit (SkinThickness)" type="number" min="0" max="99" inputmode="numeric" suffix="mm" required />
                <x-ui.input name="insulin" label="4. Kadar insulin serum (Insulin)" type="number" min="0" max="900" step="0.1" inputmode="decimal" suffix="mu U/ml" required />
                <x-ui.input name="bmi" label="5. Indeks massa tubuh (BMI)" type="number" min="10" max="70" step="0.1" inputmode="decimal" suffix="kg/m²" required />
                <x-ui.input name="dpf" label="6. Fungsi silsilah diabetes (Pedigree / DPF)" type="number" min="0" max="3" step="0.001" inputmode="decimal" suffix="skor" required />
            </div>

            <h3 class="subsection">Tanda vital tambahan <span class="muted">(opsional, dipakai untuk TD dan Nadi di dashboard)</span></h3>
            <div class="form-grid form-grid-3">
                <x-ui.input name="systolic_bp" label="Tekanan sistolik" type="number" min="50" max="300" inputmode="numeric" suffix="mmHg" />
                <x-ui.input name="heart_rate" label="Nadi" type="number" min="20" max="250" inputmode="numeric" suffix="x/menit" />
                <x-ui.input name="spo2" label="SpO2" type="number" min="50" max="100" inputmode="numeric" suffix="%" />
            </div>

            <div class="form-grid form-grid-1">
                <x-ui.textarea name="complaint" label="Catatan tambahan & keluhan klinis" rows="3" placeholder="Contoh: lemas, sering haus, riwayat keluarga diabetes tipe 2." />
                <x-ui.input name="allergies" label="Alergi (jika ada)" placeholder="Contoh: Penisilin / Amoksisilin" />
                <x-ui.select name="priority_override" label="Ubah prioritas secara manual (opsional)" placeholder="Ikuti hasil evaluasi otomatis"
                             :options="['tinggi' => 'Tinggi', 'sedang' => 'Sedang', 'rendah' => 'Rendah']"
                             hint="Gunakan bila penilaian klinis Anda berbeda dari hasil otomatis." />
            </div>
        </x-ui.card>

        <section class="eval" data-eval data-tone="none" aria-live="polite">
            <header class="eval-head">
                <span class="eval-kicker">Hasil evaluasi & prediksi otomatis (Outcome / Prioritas)</span>
                <x-ui.badge variant="neutral" data-eval-outcome>Outcome = –</x-ui.badge>
                <span class="live"><span class="dot dot-live"></span>Evaluasi langsung</span>
            </header>
            <div class="eval-body">
                <span class="eval-icon"><x-ui.icon name="alert-triangle" :size="24" /></span>
                <div class="eval-main">
                    <p class="eval-label">Klasifikasi risiko</p>
                    <p class="eval-title" data-eval-title>Isi hasil tes untuk melihat prioritas.</p>
                </div>
                <div class="eval-side">
                    <p data-eval-triggers></p>
                    <p class="eval-route" data-eval-route></p>
                </div>
            </div>
            <p class="eval-note">Estimasi otomatis berbasis aturan sederhana, bukan diagnosis. Keputusan akhir tetap pada tenaga kesehatan.</p>
        </section>

        <div class="form-actions">
            <x-ui.button :href="route('perawat.dashboard')" variant="outline">Batal</x-ui.button>
            <x-ui.button type="submit" icon="check-circle">Simpan & Tentukan Prioritas</x-ui.button>
        </div>
    </form>

    <x-slot:footer><footer class="footer">{{ config('triase.name') }} • Alur form data kesehatan pasien terintegrasi</footer></x-slot:footer>
</x-layouts.app>
