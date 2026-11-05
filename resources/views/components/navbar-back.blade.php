{{-- Bilah atas halaman detail: tombol kembali + petunjuk alur + identitas petugas. --}}
@props(['href', 'label' => 'Kembali', 'hint' => null])
<header class="navbar">
    <div class="container navbar-inner">
        <div class="navbar-left">
            <a href="{{ $href }}" class="back-link"><x-ui.icon name="arrow-left" :size="18" /><span>{{ $label }}</span></a>
            @if ($hint)<span class="navbar-hint"><span class="dot"></span>{{ $hint }}</span>@endif
        </div>
        <div class="navbar-right">
            <span class="duty-chip">Dokter jaga: <strong>{{ auth()->user()->name }}</strong></span>
        </div>
    </div>
</header>
