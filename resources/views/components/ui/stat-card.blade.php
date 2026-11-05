{{-- Kartu ringkasan prioritas. tone: danger | warn | ok --}}
@props(['tone' => 'ok', 'icon', 'kicker', 'title', 'count' => 0, 'description' => null])
<article {{ $attributes->class(['stat', "stat-{$tone}"]) }}>
    <span class="stat-icon"><x-ui.icon :name="$icon" :size="22" /></span>
    <div class="stat-main">
        <p class="stat-kicker">{{ $kicker }}</p>
        <p class="stat-title">{{ $title }}</p>
        @if ($description)<p class="stat-desc">{{ $description }}</p>@endif
    </div>
    <span class="stat-count">{{ $count }} Pasien</span>
</article>
