@props(['priority', 'detail' => false])
@php
    $map = [
        'tinggi' => ['danger', 'alert-circle', 'Tinggi'],
        'sedang' => ['warn', 'alert-triangle', $detail ? 'Sedang (Didahulukan)' : 'Sedang'],
        'rendah' => ['ok', 'check-circle', $detail ? 'Rendah (Normal)' : 'Rendah'],
    ];
    [$variant, $icon, $label] = $map[$priority] ?? ['neutral', 'alert-circle', ucfirst($priority)];
@endphp
<x-ui.badge :variant="$variant" :icon="$icon">{{ $label }}</x-ui.badge>
