@props(['icon', 'title', 'subtitle' => null])
<div class="auth-card">
    <span class="auth-icon"><x-ui.icon :name="$icon" :size="26" /></span>
    <h1 class="auth-title">{{ $title }}</h1>
    @if ($subtitle)<p class="auth-sub">{{ $subtitle }}</p>@endif
    {{ $slot }}
</div>
