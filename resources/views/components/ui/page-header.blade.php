@props(['title', 'subtitle' => null])
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $title }}</h1>
        @if ($subtitle)<p class="page-sub">{{ $subtitle }}</p>@endif
    </div>
    @isset($actions)<div class="page-actions">{{ $actions }}</div>@endisset
</div>
