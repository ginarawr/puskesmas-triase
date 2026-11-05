@props(['name' => '', 'size' => 34])
@php
    $words = preg_split('/\s+/', trim(preg_replace('/^(dr\.?|ns\.?)\s+/i', '', $name)));
    $initials = mb_strtoupper(mb_substr($words[0] ?? '', 0, 1) . mb_substr($words[1] ?? '', 0, 1));
@endphp
<span {{ $attributes->class(['avatar']) }} style="--size: {{ $size }}px" aria-hidden="true">{{ $initials }}</span>
