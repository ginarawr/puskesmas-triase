<x-layouts.app title="Skrining Diabetes" width="wide">
<x-slot:header>
    <header class="navbar navbar-plain">
        <div class="container navbar-inner">
            <x-ui.brand :href="url('/screening')" subtitle="Skrining Risiko Diabetes" />
            <div class="navbar-right">
                @auth
                    <a href="{{ auth()->user()->homeUrl() }}" class="btn btn-sm btn-outline">
                        <x-ui.icon name="arrow-left" :size="16" />
                        Dashboard {{ auth()->user()->isDoctor() ? 'Dokter' : 'Perawat' }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-sm btn-primary">
                        <x-ui.icon name="user" :size="16" />
                        Masuk Sistem Triase
                    </a>
                @endauth
            </div>
        </div>
    </header>
</x-slot:header>

{{-- ============================================================ --}}
{{-- 1. BANNER PERINGATAN KLINIS PERMANEN                         --}}
{{-- ============================================================ --}}
<div class="alert-banner" style="margin-bottom:24px; border-color:var(--warn-line); background:var(--warn-tint);">
    <div class="alert-banner-icon" style="background:var(--warn);">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    </div>
    <div>
        <div class="alert-banner-title" style="color:var(--warn);">⚠ Peringatan Klinis</div>
        <p style="font-size:13.5px; margin-top:6px; color:#5b4a1a; line-height:1.6;">
            {{ $metadata['disclaimer'] }}
        </p>
    </div>
</div>

<div class="stack-lg">

{{-- ============================================================ --}}
{{-- 2. FORMULIR SKRINING & SLIDER THRESHOLD                     --}}
{{-- ============================================================ --}}
<div class="card" id="form-section">
    <div class="card-head">
        <div class="card-title-wrap">
            <div class="card-icon">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M16 4h2a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>
            </div>
            <div>
                <div class="card-title">Formulir Skrining Risiko Diabetes</div>
                <div class="card-sub">Pilih mode, isi data pasien, lalu klik "Hitung Risiko"</div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <form id="screening-form" autocomplete="off">
            {{-- Mode selector --}}
            <div class="form-grid form-grid-1" style="margin-bottom:18px;">
                <fieldset class="field">
                    <label class="field-label">Mode Skrining</label>
                    <div class="choice-group" style="--cols:2;">
                        <label class="choice">
                            <input type="radio" name="mode" value="versi_b" checked>
                            <span class="choice-box">🏥 Triase Posbindu (6 input non-lab)</span>
                        </label>
                        <label class="choice">
                            <input type="radio" name="mode" value="versi_a">
                            <span class="choice-box">🔬 Skrining Lengkap (8 input + Lab)</span>
                        </label>
                    </div>
                    <span class="field-hint" id="mode-hint">Default: Versi B — tanpa hasil lab Glucose &amp; Insulin</span>
                </fieldset>
            </div>

            {{-- Preset demo buttons --}}
            <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:18px;">
                <button type="button" class="btn btn-sm btn-outline-danger" id="demo-high">
                    🔴 Contoh Pasien Berisiko Tinggi
                </button>
                <button type="button" class="btn btn-sm btn-soft" id="demo-low">
                    🟢 Contoh Pasien Risiko Rendah
                </button>
                <button type="button" class="btn btn-sm btn-outline" id="demo-clear">
                    Kosongkan Form
                </button>
            </div>

            {{-- Dynamic feature inputs --}}
            <div id="feature-fields" class="form-grid" style="margin-bottom:18px;"></div>

            {{-- Threshold slider --}}
            <div class="card" style="background:var(--tint); border-color:var(--line); margin-bottom:18px;">
                <div class="card-body" style="padding:16px;">
                    <div class="field">
                        <label class="field-label">
                            <span>Ambang Keputusan (Threshold)</span>
                            <span class="field-label-right" id="threshold-display" style="color:var(--brand); font-size:16px; font-weight:800;">0.40</span>
                        </label>
                        <input type="range" id="threshold-slider" min="0.25" max="0.60" step="0.05" value="0.40"
                               style="width:100%; accent-color:var(--brand); height:6px;">
                        <div style="display:flex; justify-content:space-between; font-size:11px; color:var(--muted);">
                            <span>0.25 (Sensitif)</span><span>0.60 (Spesifik)</span>
                        </div>
                        <div id="threshold-preview" class="field-hint" style="margin-top:4px;"></div>
                    </div>
                </div>
            </div>

            {{-- Submit button --}}
            <div class="form-actions">
                <button type="submit" class="btn btn-lg btn-primary btn-block" id="btn-predict">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                    Hitung Risiko Diabetes
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ============================================================ --}}
{{-- 3. KARTU HASIL PREDIKSI                                      --}}
{{-- ============================================================ --}}
<div id="result-section" style="display:none;">
    {{-- Disclaimer on result --}}
    <div class="toast toast-error" style="margin-bottom:16px;">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <p style="font-size:12.5px;">Hasil ini bukan diagnosis medis. Harus dikonfirmasi oleh dokter/tenaga kesehatan.</p>
    </div>

    <div class="card" id="result-card">
        <div class="card-head" id="result-header">
            <div class="card-title-wrap">
                <div class="card-icon" id="result-icon"></div>
                <div>
                    <div class="card-title" id="result-title"></div>
                    <div class="card-sub" id="result-subtitle"></div>
                </div>
            </div>
            <div id="result-badge"></div>
        </div>
        <div class="card-body">
            {{-- Probability gauge --}}
            <div style="text-align:center; margin-bottom:20px;">
                <div style="font-size:48px; font-weight:800; line-height:1;" id="result-prob-pct"></div>
                <div style="font-size:13px; color:var(--muted); margin-top:4px;">Probabilitas Risiko Diabetes</div>
                <div style="margin-top:10px; height:12px; background:#e0e0e0; border-radius:6px; overflow:hidden; max-width:400px; margin-inline:auto;">
                    <div id="result-prob-bar" style="height:100%; border-radius:6px; transition:width .6s;"></div>
                </div>
                <div style="display:flex; justify-content:space-between; max-width:400px; margin-inline:auto; font-size:11px; color:var(--muted); margin-top:4px;">
                    <span>0%</span><span>Threshold: <span id="result-threshold-label"></span></span><span>100%</span>
                </div>
            </div>

            {{-- Recommendation --}}
            <div id="result-recommendation" class="note-box" style="margin-bottom:16px;"></div>

            {{-- Imputed fields --}}
            <div id="result-imputed" style="display:none; margin-bottom:16px;">
                <div class="subsection">Atribut yang Diimputasi Otomatis (nilai 0 / kosong)</div>
                <div class="chips" id="result-imputed-chips"></div>
            </div>

            {{-- Feature importances chart --}}
            <div class="subsection">Faktor Paling Berpengaruh</div>
            <div id="result-factors" style="margin-bottom:8px;"></div>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- 4. PANEL TRANSPARANSI EVALUASI MODEL                        --}}
{{-- ============================================================ --}}
<div class="card" id="eval-section">
    <div class="card-head">
        <div class="card-title-wrap">
            <div class="card-icon">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M18 20V10"/><path d="M12 20V4"/><path d="M6 20v-6"/></svg>
            </div>
            <div>
                <div class="card-title">Transparansi & Evaluasi Model</div>
                <div class="card-sub">Metrik kinerja, confusion matrix, dan analisis per kelompok usia</div>
            </div>
        </div>
    </div>
    <div class="card-body">
        {{-- Dataset info --}}
        <div class="note-box" style="margin-bottom:20px; font-size:13px; line-height:1.7;">
            <strong>📊 Dataset:</strong> {{ $metadata['dataset_summary']['source'] ?? '-' }}<br>
            <strong>Total:</strong> {{ $metadata['dataset_summary']['total_samples'] ?? '-' }} sampel
            (Latih: {{ $metadata['dataset_summary']['train_samples'] ?? '-' }},
            Uji: {{ $metadata['dataset_summary']['test_samples'] ?? '-' }})<br>
            <strong>Distribusi kelas:</strong>
            Negatif: {{ $metadata['dataset_summary']['class_distribution']['negative_0'] ?? '-' }} (65.1%),
            Positif: {{ $metadata['dataset_summary']['class_distribution']['positive_1'] ?? '-' }} (34.9%)<br>
            <strong>Keterbatasan:</strong> {{ $metadata['dataset_summary']['limitation'] ?? '-' }}
        </div>

        {{-- Why accuracy is not enough --}}
        <div class="toast toast-ok" style="margin-bottom:20px;">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
            <p style="font-size:12.5px; line-height:1.6;">
                <strong>Mengapa akurasi saja tidak cukup?</strong>
                Pada data tidak seimbang (65,1% negatif vs 34,9% positif), model yang menebak semua pasien "negatif" akan memperoleh akurasi 65,1% — tetapi <strong>Sensitivitas 0%</strong> (tidak mendeteksi satu pun kasus positif). Oleh karena itu, metrik seperti <strong>Sensitivitas (Recall)</strong>, <strong>Spesifisitas</strong>, dan <strong>AUC-ROC</strong> lebih penting untuk evaluasi skrining kesehatan.
            </p>
        </div>

        {{-- Model metrics tabs --}}
        <div class="subsection">Metrik Evaluasi — Mode Aktif</div>
        <div class="tabs" style="margin-bottom:14px;">
            <button class="tab is-active" data-eval-mode="versi_b" onclick="switchEvalMode('versi_b')">Versi B (Posbindu)</button>
            <button class="tab" data-eval-mode="versi_a" onclick="switchEvalMode('versi_a')">Versi A (Lengkap)</button>
        </div>

        {{-- Metrics cards --}}
        <div class="metrics" id="eval-metrics" style="margin-bottom:20px;"></div>

        {{-- Confusion matrix --}}
        <div class="subsection">Confusion Matrix (Data Uji)</div>
        <div id="eval-cm" style="margin-bottom:20px;"></div>

        {{-- Age group evaluation --}}
        <div class="subsection">Kinerja per Kelompok Usia</div>
        <div class="table-wrap" style="margin-bottom:20px;">
            <table class="table" id="eval-age-table">
                <thead><tr>
                    <th>Kelompok</th><th>n</th><th>Positif</th><th>Accuracy</th><th>Sensitivity</th><th>Specificity</th><th>AUC-ROC</th><th>Catatan</th>
                </tr></thead>
                <tbody id="eval-age-body"></tbody>
            </table>
        </div>

        {{-- Benchmark comparison --}}
        <div class="subsection">Perbandingan Model & Threshold Tuning</div>
        <div class="table-wrap" style="margin-bottom:12px;">
            <table class="table" id="benchmark-table">
                <thead><tr>
                    <th>Skenario</th><th>Model</th><th>CV AUC</th><th>Accuracy</th><th>Sensitivity</th><th>Specificity</th><th>Precision</th><th>F1</th><th>AUC-ROC</th>
                </tr></thead>
                <tbody id="benchmark-body"></tbody>
            </table>
        </div>

        {{-- Threshold tuning table --}}
        <div class="subsection">Tabel Threshold Tuning — Mode Aktif</div>
        <div class="table-wrap">
            <table class="table" id="tuning-table">
                <thead><tr>
                    <th>Threshold</th><th>Sensitivity</th><th>Specificity</th><th>Precision</th><th>F1</th><th>Accuracy</th><th>TN</th><th>FP</th><th>FN</th><th>TP</th>
                </tr></thead>
                <tbody id="tuning-body"></tbody>
            </table>
        </div>
    </div>
</div>

</div>{{-- /.stack-lg --}}

{{-- ============================================================ --}}
{{-- JAVASCRIPT                                                    --}}
{{-- ============================================================ --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const META = @json($metadata);
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    // Feature labels in Indonesian
    const LABELS = {
        Pregnancies: 'Jumlah Kehamilan',
        Glucose: 'Glukosa Darah (mg/dL)',
        BloodPressure: 'Tekanan Darah Diastolik (mmHg)',
        SkinThickness: 'Ketebalan Lipatan Kulit Trisep (mm)',
        Insulin: 'Insulin Serum 2 Jam (μU/mL)',
        BMI: 'Indeks Massa Tubuh (kg/m²)',
        DiabetesPedigreeFunction: 'Fungsi Silsilah Diabetes (DPF)',
        Age: 'Usia (tahun)'
    };

    const DEMO_HIGH = {
        versi_a: {Pregnancies:4,Glucose:148,BloodPressure:76,SkinThickness:30,Insulin:0,BMI:33.6,DiabetesPedigreeFunction:0.627,Age:45},
        versi_b: {Pregnancies:4,BloodPressure:76,SkinThickness:30,BMI:33.6,DiabetesPedigreeFunction:0.627,Age:45}
    };
    const DEMO_LOW = {
        versi_a: {Pregnancies:1,Glucose:85,BloodPressure:66,SkinThickness:20,Insulin:0,BMI:26.6,DiabetesPedigreeFunction:0.351,Age:21},
        versi_b: {Pregnancies:1,BloodPressure:66,SkinThickness:20,BMI:26.6,DiabetesPedigreeFunction:0.351,Age:21}
    };

    const form = document.getElementById('screening-form');
    const fieldsDiv = document.getElementById('feature-fields');
    const slider = document.getElementById('threshold-slider');
    const sliderDisplay = document.getElementById('threshold-display');
    const thresholdPreview = document.getElementById('threshold-preview');
    const resultSection = document.getElementById('result-section');

    // ---- Build feature inputs ----
    function getMode() {
        return document.querySelector('input[name="mode"]:checked').value;
    }
    function renderFields() {
        const mode = getMode();
        const features = META.models[mode].features;
        fieldsDiv.innerHTML = '';
        features.forEach(f => {
            const hint = f === 'DiabetesPedigreeFunction' ? 'Contoh: 0.627' : '';
            const step = (f === 'BMI' || f === 'DiabetesPedigreeFunction') ? '0.001' : '1';
            fieldsDiv.innerHTML += `
                <div class="field">
                    <label class="field-label" for="f-${f}">${LABELS[f] || f}</label>
                    <div class="input-wrap">
                        <input type="number" step="${step}" min="0" name="${f}" id="f-${f}"
                               class="input" placeholder="Masukkan nilai...">
                    </div>
                    ${hint ? `<span class="field-hint">${hint}</span>` : ''}
                </div>`;
        });
        // Update threshold default
        const defTh = META.models[mode].default_threshold;
        slider.value = defTh;
        sliderDisplay.textContent = defTh.toFixed(2);
        updateThresholdPreview();
        // Update mode hint
        document.getElementById('mode-hint').textContent = mode === 'versi_a'
            ? 'Versi A — 8 fitur termasuk hasil lab (Glucose & Insulin)'
            : 'Versi B — 6 fitur non-lab, cocok untuk Posbindu/triase awal';
    }

    function updateThresholdPreview() {
        const mode = getMode();
        const val = parseFloat(slider.value);
        const tuning = META.models[mode].threshold_tuning || [];
        const row = tuning.find(r => Math.abs(r.Threshold - val) < 0.001);
        if (row) {
            thresholdPreview.innerHTML = `Pada threshold ${val.toFixed(2)}: <strong>Sensitivitas ${(row.Test_Sensitivity*100).toFixed(1)}%</strong> | <strong>Spesifisitas ${(row.Test_Specificity*100).toFixed(1)}%</strong>`;
        } else {
            thresholdPreview.textContent = '';
        }
    }

    // ---- Events ----
    document.querySelectorAll('input[name="mode"]').forEach(r => r.addEventListener('change', renderFields));
    slider.addEventListener('input', function() {
        sliderDisplay.textContent = parseFloat(this.value).toFixed(2);
        updateThresholdPreview();
    });

    document.getElementById('demo-high').addEventListener('click', function() {
        const mode = getMode();
        const vals = DEMO_HIGH[mode];
        for (const [k,v] of Object.entries(vals)) {
            const el = document.getElementById('f-'+k);
            if (el) el.value = v;
        }
    });
    document.getElementById('demo-low').addEventListener('click', function() {
        const mode = getMode();
        const vals = DEMO_LOW[mode];
        for (const [k,v] of Object.entries(vals)) {
            const el = document.getElementById('f-'+k);
            if (el) el.value = v;
        }
    });
    document.getElementById('demo-clear').addEventListener('click', function() {
        fieldsDiv.querySelectorAll('input').forEach(i => i.value = '');
    });

    // ---- Submit ----
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-predict');
        btn.disabled = true;
        btn.textContent = 'Menghitung...';

        const data = new URLSearchParams(new FormData(form));
        data.set('threshold', slider.value);

        try {
            const resp = await fetch('{{ route("screening.predict") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: data
            });
            const result = await resp.json();
            if (result.error) {
                alert('Error: ' + result.error);
                return;
            }
            showResult(result);
        } catch(err) {
            alert('Gagal menghubungi server: ' + err.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg> Hitung Risiko Diabetes';
        }
    });

    // ---- Show result ----
    function showResult(r) {
        const isPositive = r.prediction === 1;
        const color = isPositive ? 'var(--danger)' : 'var(--brand)';
        const bg = isPositive ? 'var(--danger-tint)' : 'var(--tint)';

        // Header
        document.getElementById('result-header').style.background = bg;
        document.getElementById('result-icon').innerHTML = isPositive
            ? '<svg width="28" height="28" fill="none" stroke="' + color + '" stroke-width="2.5" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>'
            : '<svg width="28" height="28" fill="none" stroke="' + color + '" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';
        document.getElementById('result-title').textContent = r.risk_label;
        document.getElementById('result-title').style.color = color;
        document.getElementById('result-subtitle').textContent = r.model_title + ' | Threshold: ' + r.used_threshold.toFixed(2);
        document.getElementById('result-badge').innerHTML = `<span class="badge ${isPositive ? 'badge-danger' : 'badge-ok'}">${isPositive ? 'PRIORITAS TINGGI' : 'RISIKO RENDAH'}</span>`;

        // Probability
        document.getElementById('result-prob-pct').textContent = r.probability_pct + '%';
        document.getElementById('result-prob-pct').style.color = color;
        document.getElementById('result-prob-bar').style.width = r.probability_pct + '%';
        document.getElementById('result-prob-bar').style.background = color;
        document.getElementById('result-threshold-label').textContent = (r.used_threshold * 100).toFixed(0) + '%';

        // Recommendation
        document.getElementById('result-recommendation').innerHTML = '<strong>Rekomendasi:</strong> ' + r.recommendation;

        // Imputed fields
        const impDiv = document.getElementById('result-imputed');
        const impChips = document.getElementById('result-imputed-chips');
        if (r.imputed_fields && r.imputed_fields.length > 0) {
            impDiv.style.display = '';
            impChips.innerHTML = r.imputed_fields.map(f =>
                `<span class="chip chip-warn">${LABELS[f] || f} → median</span>`
            ).join('');
        } else {
            impDiv.style.display = 'none';
        }

        // Feature importances
        const factorsDiv = document.getElementById('result-factors');
        const factors = r.top_factors || [];
        const maxImp = factors.length > 0 ? factors[0].importance : 1;
        factorsDiv.innerHTML = factors.map(fi => {
            const pct = ((fi.importance / maxImp) * 100).toFixed(0);
            const dir = fi.coefficient > 0 ? '+' : '−';
            const dirColor = fi.coefficient > 0 ? 'var(--danger)' : 'var(--brand)';
            return `<div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                <span style="width:180px; font-size:13px; font-weight:600; flex:none;">${LABELS[fi.feature] || fi.feature}</span>
                <div style="flex:1; height:22px; background:var(--tint); border-radius:6px; overflow:hidden; position:relative;">
                    <div style="width:${pct}%; height:100%; background:${dirColor}; border-radius:6px; opacity:0.7;"></div>
                </div>
                <span style="font-size:12px; font-weight:700; color:${dirColor}; min-width:70px; text-align:right;">${dir} ${(fi.importance*100).toFixed(1)}%</span>
            </div>`;
        }).join('');

        resultSection.style.display = '';
        resultSection.scrollIntoView({behavior:'smooth', block:'start'});
    }

    // ---- Evaluation panel ----
    let currentEvalMode = 'versi_b';

    window.switchEvalMode = function(mode) {
        currentEvalMode = mode;
        document.querySelectorAll('[data-eval-mode]').forEach(btn => {
            btn.classList.toggle('is-active', btn.dataset.evalMode === mode);
        });
        renderEvalMetrics();
        renderAgeTable();
        renderTuningTable();
    };

    function renderEvalMetrics() {
        const model = META.models[currentEvalMode];
        const mr = model.metrics_recommended;
        const metricsDiv = document.getElementById('eval-metrics');
        const items = [
            ['Sensitivitas (Recall)', (mr.sensitivity*100).toFixed(1)+'%', 'Kemampuan mendeteksi kasus positif'],
            ['Spesifisitas', (mr.specificity*100).toFixed(1)+'%', 'Kemampuan menghindari false positive'],
            ['Akurasi', (mr.accuracy*100).toFixed(1)+'%', 'Ketepatan keseluruhan'],
            ['Presisi', (mr.precision*100).toFixed(1)+'%', 'Dari yang diprediksi positif, berapa yang benar'],
            ['F1-Score', (mr.f1_score*100).toFixed(1)+'%', 'Harmonik rata-rata Presisi & Recall'],
            ['AUC-ROC', (mr.roc_auc*100).toFixed(1)+'%', 'Area under ROC curve']
        ];
        metricsDiv.innerHTML = items.map(([label, value, hint]) => `
            <div class="metric">
                <div class="metric-label">${label}</div>
                <div class="metric-value">${value}</div>
                <div class="metric-note">${hint}</div>
            </div>`).join('');

        // Confusion matrix
        const cm = mr.confusion_matrix;
        document.getElementById('eval-cm').innerHTML = `
            <div style="display:grid; grid-template-columns:auto 1fr 1fr; gap:2px; max-width:360px; font-size:13px; text-align:center;">
                <div></div>
                <div style="font-weight:700; padding:8px; background:var(--tint); border-radius:6px 6px 0 0;">Prediksi Negatif</div>
                <div style="font-weight:700; padding:8px; background:var(--tint); border-radius:6px 6px 0 0;">Prediksi Positif</div>
                <div style="font-weight:700; padding:8px; background:var(--tint); border-radius:6px 0 0 6px; writing-mode:horizontal-tb;">Aktual Negatif</div>
                <div style="padding:12px; background:#e8f5e9; font-weight:800; font-size:18px; border-radius:4px;">TN=${cm.tn}</div>
                <div style="padding:12px; background:#ffebee; font-weight:800; font-size:18px; border-radius:4px;">FP=${cm.fp}</div>
                <div style="font-weight:700; padding:8px; background:var(--tint); border-radius:0 0 0 6px;">Aktual Positif</div>
                <div style="padding:12px; background:#fff3e0; font-weight:800; font-size:18px; border-radius:4px;">FN=${cm.fn}</div>
                <div style="padding:12px; background:#e8f5e9; font-weight:800; font-size:18px; border-radius:4px;">TP=${cm.tp}</div>
            </div>
            <div class="field-hint" style="margin-top:8px;">Threshold yang direkomendasikan: ${mr.threshold}</div>`;
    }

    function renderAgeTable() {
        const model = META.models[currentEvalMode];
        const rows = model.age_group_evaluation || [];
        const tbody = document.getElementById('eval-age-body');
        tbody.innerHTML = rows.map(r => `<tr>
            <td data-label="Kelompok">${r['Kelompok Usia']}</td>
            <td data-label="n">${r['Jumlah Sampel (n)']}</td>
            <td data-label="Positif">${r['Positif Aktual']}</td>
            <td data-label="Accuracy">${(r.Accuracy*100).toFixed(1)}%</td>
            <td data-label="Sensitivity">${(r.Sensitivity*100).toFixed(1)}%</td>
            <td data-label="Specificity">${(r.Specificity*100).toFixed(1)}%</td>
            <td data-label="AUC-ROC">${(r['AUC-ROC']*100).toFixed(1)}%</td>
            <td data-label="Catatan"><span class="chip ${r.Catatan.includes('kecil') ? 'chip-warn' : ''}">${r.Catatan}</span></td>
        </tr>`).join('');
    }

    function renderTuningTable() {
        const model = META.models[currentEvalMode];
        const rows = model.threshold_tuning || [];
        const tbody = document.getElementById('tuning-body');
        const defTh = model.default_threshold;
        tbody.innerHTML = rows.map(r => {
            const isDefault = Math.abs(r.Threshold - defTh) < 0.001;
            return `<tr style="${isDefault ? 'background:var(--tint); font-weight:700;' : ''}">
                <td data-label="Threshold">${r.Threshold.toFixed(2)} ${isDefault ? '⬅ default' : ''}</td>
                <td data-label="Sensitivity">${(r.Test_Sensitivity*100).toFixed(1)}%</td>
                <td data-label="Specificity">${(r.Test_Specificity*100).toFixed(1)}%</td>
                <td data-label="Precision">${(r.Test_Precision*100).toFixed(1)}%</td>
                <td data-label="F1">${(r.Test_F1*100).toFixed(1)}%</td>
                <td data-label="Accuracy">${(r.Test_Accuracy*100).toFixed(1)}%</td>
                <td data-label="TN">${r.Test_TN}</td>
                <td data-label="FP">${r.Test_FP}</td>
                <td data-label="FN">${r.Test_FN}</td>
                <td data-label="TP">${r.Test_TP}</td>
            </tr>`;
        }).join('');
    }

    // Benchmark table (static)
    function renderBenchmarks() {
        const rows = META.all_benchmarks || [];
        const tbody = document.getElementById('benchmark-body');
        tbody.innerHTML = rows.map(b => `<tr>
            <td data-label="Skenario">${b.Skenario}</td>
            <td data-label="Model">${b.Model}</td>
            <td data-label="CV AUC">${b['CV AUC Mean'].toFixed(4)}</td>
            <td data-label="Accuracy">${(b.Accuracy*100).toFixed(1)}%</td>
            <td data-label="Sensitivity">${(b['Sensitivity (Recall)']*100).toFixed(1)}%</td>
            <td data-label="Specificity">${(b.Specificity*100).toFixed(1)}%</td>
            <td data-label="Precision">${(b.Precision*100).toFixed(1)}%</td>
            <td data-label="F1">${(b['F1-score']*100).toFixed(1)}%</td>
            <td data-label="AUC-ROC">${(b['AUC-ROC (Test)']*100).toFixed(1)}%</td>
        </tr>`).join('');
    }

    // ---- Init ----
    renderFields();
    renderEvalMetrics();
    renderAgeTable();
    renderTuningTable();
    renderBenchmarks();
});
</script>

</x-layouts.app>
