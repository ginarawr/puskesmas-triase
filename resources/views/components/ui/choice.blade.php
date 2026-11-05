{{-- Pilihan tunggal bergaya tombol (radio). options: ['kunci' => ['label' => '...', 'icon' => 'opsional']] --}}
@props(['name', 'options', 'value' => null, 'label' => null])
<fieldset class="field">
    @if ($label)<legend class="field-label"><span>{{ $label }}</span></legend>@endif
    <div class="choice-group" style="--cols: {{ count($options) }}">
        @foreach ($options as $key => $opt)
            <label class="choice">
                <input type="radio" name="{{ $name }}" value="{{ $key }}" @checked((string) old($name, $value) === (string) $key)>
                <span class="choice-box">
                    @if (! empty($opt['icon']))<x-ui.icon :name="$opt['icon']" />@endif
                    {{ $opt['label'] }}
                </span>
            </label>
        @endforeach
    </div>
    @error($name)<p class="field-error">{{ $message }}</p>@enderror
</fieldset>
