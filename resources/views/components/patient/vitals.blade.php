{{-- Chip hasil tes ringkas: TD, Nadi (jika ada), GDS. --}}
@props(['patient'])
<div class="chips">
    <span class="chip chip-{{ $patient->tone('bp') }}">TD: {{ $patient->bp_label }}</span>
    @if ($patient->heart_rate)
        <span class="chip chip-{{ $patient->tone('hr') }}">Nadi: {{ $patient->heart_rate }} x/m</span>
    @endif
    <span class="chip chip-{{ $patient->tone('glucose') }}">GDS: {{ $patient->glucose }} mg/dL</span>
</div>
