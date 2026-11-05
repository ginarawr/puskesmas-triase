@props([
    'variant' => 'primary', // primary | danger | outline | soft | ghost
    'size' => 'md',         // sm | md | lg
    'href' => null,
    'type' => 'button',
    'icon' => null,
    'iconRight' => null,
    'block' => false,
])
@php
    $classes = "btn btn-{$variant} btn-{$size}" . ($block ? ' btn-block' : '');
    $iconSize = $size === 'sm' ? 15 : 18;
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->class([$classes]) }}>
        @if ($icon)<x-ui.icon :name="$icon" :size="$iconSize" />@endif
        <span class="btn-label">{{ $slot }}</span>
        @if ($iconRight)<x-ui.icon :name="$iconRight" :size="$iconSize" />@endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class([$classes]) }}>
        @if ($icon)<x-ui.icon :name="$icon" :size="$iconSize" />@endif
        <span class="btn-label">{{ $slot }}</span>
        @if ($iconRight)<x-ui.icon :name="$iconRight" :size="$iconSize" />@endif
    </button>
@endif
