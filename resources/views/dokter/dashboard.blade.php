<x-layouts.app title="Dashboard Dokter">
    <x-slot:header>
        <x-navbar :title="config('triase.name')" subtitle="Dashboard Dokter" />
    </x-slot:header>

    <x-ui.flash />

    <div data-live="board" class="stack-lg">
        @if ($emergencies->isNotEmpty())
            <x-dokter.emergency-banner :patient="$emergencies->first()" :others="$emergencies->count() - 1" />
        @endif

        <x-ui.card title="Daftar Pasien Triase" subtitle="Prioritas klinis dan ringkasan hasil tes pasien." flush>
            <x-slot:actions>
                <x-ui.filter-tabs :active="$filter" :items="[
                    'semua' => 'Semua Pasien (' . $counts['semua'] . ')',
                    'tinggi' => 'Prioritas Tinggi (' . $counts['tinggi'] . ')',
                    'sedang' => 'Prioritas Sedang (' . $counts['sedang'] . ')',
                    'rendah' => 'Prioritas Rendah (' . $counts['rendah'] . ')',
                ]" />
            </x-slot:actions>

            @if ($patients->isEmpty())
                <x-ui.empty-state title="Tidak ada pasien menunggu" text="Pasien baru dari perawat akan muncul di sini otomatis." />
            @else
                <x-ui.data-table :headers="[
                    ['label' => 'No. antrean'],
                    ['label' => 'Nama & usia pasien'],
                    ['label' => 'Hasil tes kunci (TD, Nadi, GDS)'],
                    ['label' => 'Klasifikasi prioritas'],
                    ['label' => 'Aksi', 'class' => 'th-right'],
                ]">
                    @foreach ($patients as $p)
                        <tr @class(['row-high' => $p->priority === 'tinggi'])>
                            <td data-label="No. antrean" class="cell-queue">
                                @if ($p->status === 'darurat')<span class="dot dot-danger"></span>@endif{{ $p->queue_number }}
                            </td>
                            <td data-label="Pasien">
                                <p class="cell-name">{{ $p->name }}</p>
                                <p class="cell-sub">{{ $p->age }} Tahun • {{ $p->gender }} • {{ $p->medical_record_no }}</p>
                            </td>
                            <td data-label="Hasil tes">
                                <p @class(['cell-key', 'is-danger' => $p->priority === 'tinggi'])>{{ $p->key_results }}</p>
                                @if ($p->spo2)<p class="cell-sub">SpO2: {{ $p->spo2 }}%</p>@endif
                            </td>
                            <td data-label="Prioritas">
                                <x-patient.priority-badge :priority="$p->priority" />
                                @if ($p->status !== 'menunggu' && $p->status !== 'dipanggil')
                                    <p class="cell-sub">{{ $p->status_label }}</p>
                                @endif
                            </td>
                            <td data-label="Aksi" class="cell-actions">
                                <form method="POST" action="{{ route('dokter.pasien.start', $p) }}">
                                    @csrf
                                    <x-ui.button type="submit" size="sm"
                                        :variant="$p->priority === 'tinggi' ? 'danger' : ($p->priority === 'sedang' ? 'primary' : 'soft')">
                                        {{ in_array($p->status, ['ditangani', 'observasi'], true) ? 'Lanjutkan Pemeriksaan' : 'Periksa Pasien' }}
                                    </x-ui.button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </x-ui.data-table>
            @endif

            <x-slot:footer>
                <span>Menampilkan antrean pasien aktif terintegrasi triase</span>
                <span class="live" data-live-indicator><span class="dot dot-live"></span><span data-live-text>Total: {{ $patients->count() }} pasien menunggu</span></span>
            </x-slot:footer>
        </x-ui.card>
    </div>
</x-layouts.app>
