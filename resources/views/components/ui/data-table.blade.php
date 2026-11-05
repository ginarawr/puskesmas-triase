{{-- Tabel yang berubah menjadi kartu di layar kecil. Sel wajib punya atribut data-label. --}}
@props(['headers' => []])
<div class="table-wrap">
    <table {{ $attributes->class(['table']) }}>
        <thead>
            <tr>
                @foreach ($headers as $h)
                    <th scope="col" class="{{ $h['class'] ?? '' }}">{!! $h['label'] !!}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>{{ $slot }}</tbody>
    </table>
</div>
