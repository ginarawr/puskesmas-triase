{{-- Bilah atas dashboard. Slot "actions" untuk tombol tambahan (mis. + Pasien Baru). --}}
@props(['title' => null, 'subtitle' => null])
<header class="navbar">
    <div class="container navbar-inner">
        <x-ui.brand :title="$title ?? config('triase.name')" :subtitle="$subtitle" />
        <div class="navbar-right">
            @isset($actions){{ $actions }}<span class="navbar-sep" aria-hidden="true"></span>@endisset
            <div class="user-chip">
                <x-ui.avatar :name="auth()->user()->name" />
                <span class="user-chip-text">
                    <span class="user-chip-name">{{ auth()->user()->name }}</span>
                    @if (auth()->user()->isDoctor())<span class="user-chip-role">Dokter jaga</span>@endif
                </span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-ui.button type="submit" variant="outline-danger" size="sm" icon="log-out">Logout</x-ui.button>
            </form>
        </div>
    </div>
</header>
