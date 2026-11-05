<x-layouts.app title="Dashboard Perawat">
    <x-slot:header>
        <x-navbar title="Dashboard Perawat">
            <x-slot:actions>
                <x-ui.button :href="route('perawat.pasien.create')" icon="user-plus">
                    <span class="hide-sm">+ Pasien Baru (Input Hasil Tes)</span><span class="show-sm">Pasien Baru</span>
                </x-ui.button>
            </x-slot:actions>
        </x-navbar>
    </x-slot:header>

    <x-ui.flash />

    <div data-live="board" class="stack-lg">
        <div class="stat-grid">
            <x-ui.stat-card tone="danger" icon="asterisk" kicker="Prioritas tinggi" title="Segera hubungi dokter & bawa ke UGD"
                            description="Kondisi gawat darurat / henti napas / syok" :count="$counts['tinggi']" />
            <x-ui.stat-card tone="warn" icon="timer" kicker="Prioritas sedang" title="Antrean didahulukan"
                            description="Nyeri akut / febris tinggi / DM tinggi" :count="$counts['sedang']" />
            <x-ui.stat-card tone="ok" icon="check-circle" kicker="Prioritas rendah" title="Antrean normal"
                            description="Kondisi stabil / ISPA ringan / kontrol rutin" :count="$counts['rendah']" />
        </div>

        <x-ui.card title="Daftar Pasien" subtitle="Cek daftar pasien sesuai klasifikasi prioritas dan lakukan tindakan segera." flush>
            <x-slot:actions>
                <x-ui.filter-tabs :active="$filter" :items="[
                    'semua' => 'Semua (' . $counts['semua'] . ')',
                    'tinggi' => 'Tinggi (' . $counts['tinggi'] . ')',
                    'sedang' => 'Sedang (' . $counts['sedang'] . ')',
                    'rendah' => 'Rendah (' . $counts['rendah'] . ')',
                ]" />
            </x-slot:actions>

            @if ($patients->isEmpty())
                <x-ui.empty-state title="Belum ada pasien di antrean" text="Tambahkan pasien baru dan isi hasil tesnya untuk menentukan prioritas.">
                    <x-ui.button :href="route('perawat.pasien.create')" icon="user-plus" size="sm">Pasien baru</x-ui.button>
                </x-ui.empty-state>
            @else
                <x-ui.data-table :headers="[
                    ['label' => 'No.<br>Antrean'],
                    ['label' => 'Nama pasien'],
                    ['label' => 'Hasil tes ringkas'],
                    ['label' => 'Kategori prioritas'],
                    ['label' => 'Status / Aksi', 'class' => 'th-right'],
                ]">
                    @foreach ($patients as $p)
                        <tr @class(['row-high' => $p->priority === 'tinggi'])>
                            <td data-label="No. antrean" class="cell-queue">
                                @if ($p->priority === 'tinggi')<span class="dot dot-danger"></span>@endif{{ $p->queue_number }}
                            </td>
                            <td data-label="Nama pasien">
                                <p class="cell-name">{{ $p->name }} ({{ $p->age }} Th)</p>
                                <p class="cell-sub">{{ $p->complaint ?: 'Tanpa keluhan tercatat' }}</p>
                            </td>
                            <td data-label="Hasil tes"><x-patient.vitals :patient="$p" /></td>
                            <td data-label="Prioritas"><x-patient.priority-badge :priority="$p->priority" detail /></td>
                            <td data-label="Aksi" class="cell-actions">
                                <div class="actions">
                                    @if ($p->status === 'ditangani')
                                        <x-ui.badge variant="info" icon="stethoscope">Sedang diperiksa</x-ui.badge>
                                    @else
                                        @if ($p->status === 'darurat')
                                            <x-ui.badge variant="danger" icon="phone">Dokter dihubungi</x-ui.badge>
                                        @elseif ($p->status === 'observasi')
                                            <x-ui.badge variant="info" icon="hourglass">Observasi</x-ui.badge>
                                        @endif

                                        <form method="POST" action="{{ route('perawat.pasien.call', $p) }}">
                                            @csrf
                                            <x-ui.button type="submit" size="sm" icon="volume"
                                                :variant="$p->priority === 'sedang' ? 'primary' : 'outline'">
                                                {{ $p->priority === 'tinggi' ? 'Panggil' : 'Panggil Pasien' }}
                                            </x-ui.button>
                                        </form>

                                        @if ($p->priority === 'tinggi' && in_array($p->status, ['menunggu', 'dipanggil'], true))
                                            <form method="POST" action="{{ route('perawat.pasien.escalate', $p) }}">
                                                @csrf
                                                <x-ui.button type="submit" size="sm" variant="danger" icon="phone">Hubungi Dokter / UGD</x-ui.button>
                                            </form>
                                        @endif
                                    @endif
                                    @if ($p->status === 'dipanggil' && $p->called_at)
                                        <p class="cell-sub">Dipanggil {{ $p->called_at->format('H:i') }}</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-ui.data-table>
            @endif

            <x-slot:footer>
                <span>Menampilkan <strong>{{ $patients->count() }}</strong> pasien aktif dalam antrean triase</span>
                <span class="live" data-live-indicator><span class="dot dot-live"></span><span data-live-text>Diperbarui otomatis</span></span>
            </x-slot:footer>
        </x-ui.card>
    </div>
</x-layouts.app>
