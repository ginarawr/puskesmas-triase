{{-- Kartu satu atribut klinis. tone: normal | warn | danger --}}
@props(['label', 'value', 'unit' => null, 'note' => null, 'tone' => 'normal', 'wide' => false])
<div {{ $attributes->class(['metric', "metric-{$tone}", 'metric-wide' => $wide]) }}>
    <p class="metric-label">{{ $label }}</p>
    <p class="metric-value">{{ $value }}@if ($unit)<span class="metric-unit"> {{ $unit }}</span>@endif</p>
    @if ($note)<p class="metric-note">{{ $note }}</p>@endif
</div>
