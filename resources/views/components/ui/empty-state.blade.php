@props(['icon' => 'check-circle', 'title', 'text' => null])
<div class="empty">
    <x-ui.icon :name="$icon" :size="32" />
    <p class="empty-title">{{ $title }}</p>
    @if ($text)<p class="empty-text">{{ $text }}</p>@endif
    {{ $slot }}
</div>
