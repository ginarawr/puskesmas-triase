<x-layouts.guest title="Masuk">
    <x-auth.card icon="shield-plus" title="Masuk ke Sistem" :subtitle="config('triase.name')">
        <x-ui.flash />
        <form method="POST" action="{{ route('login.attempt') }}" class="stack" novalidate>
            @csrf
            <x-ui.input name="login" label="NIP atau Email" icon="id-card" placeholder="Masukkan NIP atau email"
                        autocomplete="username" required autofocus />
            <x-ui.input name="password" type="password" label="Kata Sandi" icon="key" placeholder="Masukkan kata sandi"
                        autocomplete="current-password" required>
                <x-slot:labelRight><a href="#" class="link" data-forgot>Lupa sandi?</a></x-slot:labelRight>
            </x-ui.input>
            <x-ui.button type="submit" block size="lg" icon-right="arrow-right">Masuk</x-ui.button>
        </form>
        <div class="auth-foot">Belum punya akun? <a href="{{ route('register') }}" class="link">Daftar Akun</a></div>
    </x-auth.card>
</x-layouts.guest>
