@props(['title' => null, 'subtitle' => null, 'step' => null, 'icon' => null, 'flush' => false])
<section {{ $attributes->class(['card']) }}>
    @if ($title || isset($actions))
        <header class="card-head">
            <div class="card-title-wrap">
                @if ($step)<span class="step">{{ $step }}</span>
                @elseif ($icon)<x-ui.icon :name="$icon" :size="20" class="card-icon" />@endif
                <div>
                    <h2 class="card-title">{{ $title }}</h2>
                    @if ($subtitle)<p class="card-sub">{{ $subtitle }}</p>@endif
                </div>
            </div>
            @isset($actions)<div class="card-actions">{{ $actions }}</div>@endisset
        </header>
    @endif
    <div @class(['card-body', 'is-flush' => $flush])>{{ $slot }}</div>
    @isset($footer)<footer class="card-foot">{{ $footer }}</footer>@endisset
</section>
