@props(['title' => null, 'subtitle' => null, 'href' => null])
@php $tag = $href ? 'a' : 'div'; @endphp
<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->class(['brand']) }}>
    <span class="brand-mark"><x-ui.icon name="brand" :size="22" /></span>
    <span class="brand-text">
        <span class="brand-name">{{ $title ?? config('triase.name') }}</span>
        @if ($subtitle)<span class="brand-sub">{{ $subtitle }}</span>@endif
    </span>
</{{ $tag }}>
