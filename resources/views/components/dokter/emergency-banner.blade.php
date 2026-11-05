{{-- Banner panggilan darurat di dashboard dokter. --}}
@props(['patient', 'others' => 0])
<section class="alert-banner" role="alert">
    <span class="alert-banner-icon"><x-ui.icon name="bell" :size="26" /></span>
    <div class="alert-banner-body">
        <p class="alert-banner-tags">
            <span class="tag-solid">Darurat medis</span>
            <span>Panggilan baru masuk{{ $others > 0 ? " (+{$others} lainnya)" : '' }}</span>
        </p>
        <p class="alert-banner-title">Panggilan darurat: {{ $patient->name }} ({{ $patient->age }} Th)@if ($patient->complaint) – {{ \Illuminate\Support\Str::limit($patient->complaint, 70) }}@endif</p>
        <p class="alert-banner-meta">
            Hasil tes kunci: <strong>TD {{ $patient->bp_label }} mmHg</strong>
            @if ($patient->spo2) | <strong>SpO2 {{ $patient->spo2 }}%</strong>@endif
            @if ($patient->heart_rate) | <strong>HR {{ $patient->heart_rate }} bpm</strong>@endif
            | <strong>GDS {{ $patient->glucose }}</strong>
            @if ($patient->escalator) | Dihubungi {{ $patient->escalator->name }} pukul {{ $patient->emergency_called_at->format('H:i') }}@endif
        </p>
    </div>
    <form method="POST" action="{{ route('dokter.pasien.start', $patient) }}">
        @csrf
        <x-ui.button type="submit" variant="danger" size="lg" icon="zap">Segera Menuju Pasien</x-ui.button>
    </form>
</section>
