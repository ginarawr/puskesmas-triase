<x-layouts.guest title="Daftar akun">
    <x-auth.card icon="user-check" title="Daftar Akun Tenaga Kesehatan" subtitle="Lengkapi data untuk akses layanan triase faskes">
        <form method="POST" action="{{ route('register.store') }}" class="stack" novalidate>
            @csrf
            <x-ui.choice name="role" label="Peran petugas" value="perawat" :options="[
                'perawat' => ['label' => 'Perawat', 'icon' => 'briefcase-medical'],
                'dokter' => ['label' => 'Dokter', 'icon' => 'stethoscope'],
            ]" />
            <x-ui.input name="name" label="Nama lengkap & gelar" icon="user" placeholder="Contoh: dr. Sarah Pratama / Ns. Andi, S.Kep" autocomplete="name" required />
            <x-ui.input name="nip" label="Nomor NIP / STR" icon="id-card" placeholder="Nomor STR atau NIP resmi petugas" required />
            <x-ui.input name="contact" label="Email / No. WhatsApp" icon="at-sign" placeholder="nama@instansi.go.id atau 08123456789" autocomplete="email" required />
            <x-ui.input name="password" type="password" label="Kata sandi" icon="lock" placeholder="Minimal 8 karakter, huruf dan angka" autocomplete="new-password" required />
            <x-ui.button type="submit" block size="lg" icon="user-plus">Daftar Akun</x-ui.button>
        </form>
        <div class="auth-foot">Sudah punya akun? <a href="{{ route('login') }}" class="link">Masuk</a></div>
    </x-auth.card>
</x-layouts.guest>
