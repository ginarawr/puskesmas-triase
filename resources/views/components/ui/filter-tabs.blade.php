{{-- Filter berbasis tautan (?prioritas=...). items: ['kunci' => 'Label (n)'] --}}
@props(['items', 'active' => 'semua', 'param' => 'prioritas'])
<nav class="tabs" aria-label="Filter prioritas">
    @foreach ($items as $key => $text)
        <a href="{{ $key === 'semua' ? url()->current() : url()->current() . '?' . $param . '=' . $key }}"
           @class(['tab', "tab-{$key}", 'is-active' => $active === $key])
           @if ($active === $key) aria-current="page" @endif>{{ $text }}</a>
    @endforeach
</nav>
