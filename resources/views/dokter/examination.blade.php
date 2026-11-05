@php
    $rx = old('prescriptions', $patient->prescriptions ?? []);
    $disposition = old('disposition', $patient->disposition);
@endphp
<x-layouts.app :title="'Pemeriksaan ' . $patient->name">
    <x-slot:header>
        <x-navbar-back :href="route('dokter.dashboard')" label="Kembali ke Dashboard Dokter" hint="Alur: cek data medis pasien" />
    </x-slot:header>

    <x-ui.flash />

    <div class="stack-lg">
        {{-- Identitas pasien --}}
        <x-ui.card>
            <div class="patient-head">
                <span class="patient-avatar">{{ $patient->initials }}</span>
                <div class="patient-head-main">
                    <p class="patient-name">{{ $patient->name }}
                        <span class="patient-meta">• {{ $patient->age }} Tahun ({{ $patient->gender_label }})</span>
                        <x-ui.badge variant="ok">No. RM: {{ $patient->medical_record_no }}</x-ui.badge>
                    </p>
                    <p>
                        <x-ui.badge variant="ok">Antrean: {{ $patient->queue_number }}</x-ui.badge>
                        <x-patient.priority-badge :priority="$patient->priority" />
                    </p>
                    <p class="cell-sub">
                        Waktu triase: {{ $patient->created_at->format('H:i T') }}
                        @if ($patient->pregnancies) • Riwayat: {{ $patient->pregnancies }}× kehamilan @endif
                    </p>
                </div>
                @if ($patient->outcome)
                    <x-ui.badge variant="danger" class="patient-risk">Risiko positif diabetes</x-ui.badge>
                @else
                    <x-ui.badge variant="ok" class="patient-risk">Risiko rendah</x-ui.badge>
                @endif
            </div>
        </x-ui.card>

        <form method="POST" action="{{ route('dokter.pasien.update', $patient) }}" class="stack-lg" novalidate>
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="toast toast-error" role="alert">
                    <x-ui.icon name="alert-circle" :size="20" />
                    <p>{{ $errors->first() }}</p>
                </div>
            @endif

            <div class="exam-grid">
                {{-- Kiri: data medis --}}
                <x-ui.card icon="id-card" title="1. Data Medis & Hasil Tes (Dataset Pima Diabetes)">
                    <x-slot:actions><span class="muted">Data triase & lab</span></x-slot:actions>

                    <h3 class="subsection">8 atribut klinis diabetes</h3>
                    <div class="metrics">
                        <x-patient.metric wide label="1. Glukosa (Glucose)" :value="$patient->glucose" unit="mg/dL" :note="$patient->glucoseNote()" :tone="$patient->tone('glucose')" />
                        <x-patient.metric label="2. Tekanan darah (BP)" :value="$patient->diastolic_bp" unit="mmHg" :note="$patient->bpNote()" :tone="$patient->tone('bp')" />
                        <x-patient.metric label="3. Indeks massa tubuh (BMI)" :value="$patient->bmi" unit="kg/m²" :note="$patient->bmiNote()" :tone="$patient->tone('bmi')" />
                        <x-patient.metric label="4. Kehamilan (Pregnancies)" :value="$patient->pregnancies" unit="kali" note="Riwayat kehamilan" />
                        <x-patient.metric label="5. Lipatan kulit (Skin)" :value="$patient->skin_thickness" unit="mm" note="Trisep" />
                        <x-patient.metric label="6. Insulin serum" :value="$patient->insulin" unit="µU/mL" :note="$patient->tone('insulin') === 'warn' ? 'Meningkat' : 'Dalam rentang'" :tone="$patient->tone('insulin')" />
                        <x-patient.metric label="7. Riwayat silsilah (DPF)" :value="$patient->dpf" :note="$patient->dpf >= 0.5 ? 'Risiko genetik tinggi' : 'Risiko genetik rendah'" :tone="$patient->tone('dpf')" />
                        <x-patient.metric wide label="8. Usia pasien (Age)" :value="$patient->age" unit="Tahun" :note="$patient->age >= 45 ? 'Faktor risiko usia matang' : null" :tone="$patient->tone('age')" />
                    </div>

                    @if ($patient->systolic_bp || $patient->heart_rate || $patient->spo2)
                        <h3 class="subsection">Tanda vital tambahan</h3>
                        <div class="metrics">
                            @if ($patient->systolic_bp)<x-patient.metric label="Tekanan darah" :value="$patient->bp_label" unit="mmHg" :tone="$patient->tone('bp')" />@endif
                            @if ($patient->heart_rate)<x-patient.metric label="Nadi" :value="$patient->heart_rate" unit="x/menit" :tone="$patient->tone('hr')" />@endif
                            @if ($patient->spo2)<x-patient.metric label="SpO2" :value="$patient->spo2" unit="%" :tone="$patient->tone('spo2')" />@endif
                        </div>
                    @endif

                    <h3 class="subsection">Keluhan utama pasien</h3>
                    <p class="note-box">{{ $patient->complaint ?: 'Tidak ada keluhan tercatat.' }}</p>
                    @if ($patient->allergies)
                        <p class="note-box note-danger"><x-ui.icon name="alert-triangle" :size="16" /> Alergi: <strong>{{ $patient->allergies }}</strong></p>
                    @endif
                </x-ui.card>

                {{-- Kanan: form dokter --}}
                <x-ui.card icon="file-edit" title="2. Form Pemeriksaan & Penanganan Dokter">
                    <x-slot:actions><x-ui.badge variant="ok">RME aktif</x-ui.badge></x-slot:actions>

                    <div class="stack">
                        <x-ui.input name="diagnosis" label="Diagnosis dokter (ICD-10)" :value="$patient->diagnosis" placeholder="Contoh: E11.65 - Tipe 2 Diabetes Mellitus dengan Hiperglikemia" />
                        <x-ui.textarea name="clinical_notes" label="Catatan klinis dokter" rows="4" :value="$patient->clinical_notes" />

                        <div class="rx" data-rx>
                            <div class="rx-head">
                                <h3 class="subsection">Resep & tindakan medis</h3>
                                <span class="muted" data-rx-count>{{ count($rx) }} item tercatat</span>
                            </div>
                            <ul class="rx-list" data-rx-list>
                                @foreach ($rx as $i => $item)
                                    @include('dokter.partials.rx-row', ['i' => $i, 'item' => $item])
                                @endforeach
                            </ul>
                            <template data-rx-template>
                                @include('dokter.partials.rx-row', ['i' => '__i__', 'item' => ['name' => '', 'type' => 'apotek']])
                            </template>
                            <x-ui.button variant="soft" size="sm" icon="plus" data-rx-add>Tambah item</x-ui.button>
                        </div>

                        <x-ui.choice name="disposition" label="Pilihan disposisi akhir" :value="$disposition" :options="[
                            'rawat_jalan' => ['label' => 'Rawat Jalan'],
                            'observasi' => ['label' => 'Observasi'],
                            'rujuk_rs' => ['label' => 'Rujuk RS'],
                        ]" />
                    </div>
                </x-ui.card>
            </div>

            {{-- Keputusan alur --}}
            <x-ui.card class="decision">
                <div class="decision-inner">
                    <div>
                        <p class="decision-kicker"><x-ui.icon name="git-branch" :size="16" /> Keputusan alur</p>
                        <p class="decision-title">Pasien selesai ditangani?</p>
                        <p class="cell-sub">Tentukan kelanjutan alur klinis pasien sebelum menutup rekam medis.</p>
                    </div>
                    <div class="decision-actions">
                        <x-ui.button type="submit" name="action" value="observasi" variant="outline" icon="hourglass">Perlu Penanganan Lanjutan / Observasi</x-ui.button>
                        <x-ui.button type="submit" name="action" value="selesai" icon="check-circle">Tandai Pasien Selesai Ditangani</x-ui.button>
                    </div>
                </div>
            </x-ui.card>
        </form>
    </div>
</x-layouts.app>
