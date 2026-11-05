<li class="rx-row">
    <input type="text" name="prescriptions[{{ $i }}][name]" value="{{ $item['name'] ?? '' }}" class="input" placeholder="Contoh: Metformin 500 mg (2 x 1 tab sesudah makan)" aria-label="Nama obat atau tindakan">
    <select name="prescriptions[{{ $i }}][type]" class="input select" aria-label="Jenis">
        <option value="apotek" @selected(($item['type'] ?? 'apotek') === 'apotek')>Apotek</option>
        <option value="tindakan_ugd" @selected(($item['type'] ?? '') === 'tindakan_ugd')>Tindakan UGD</option>
    </select>
    <button type="button" class="icon-btn" data-rx-remove aria-label="Hapus item"><x-ui.icon name="trash" :size="16" /></button>
</li>
