@props(['variant' => 'neutral', 'icon' => null]) {{-- neutral | danger | warn | ok | info --}}
<span {{ $attributes->class(['badge', "badge-{$variant}"]) }}>
    @if ($icon)<x-ui.icon :name="$icon" :size="14" />@endif
    {{ $slot }}
</span>
