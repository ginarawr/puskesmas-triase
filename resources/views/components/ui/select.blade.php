@props(['name', 'options' => [], 'label' => null, 'value' => null, 'placeholder' => null, 'hint' => null])
@php $id = $attributes->get('id', $name); $current = (string) old($name, $value); @endphp
<div class="field">
    @if ($label)<label for="{{ $id }}" class="field-label"><span>{{ $label }}</span></label>@endif
    <div class="input-wrap @error($name) has-error @enderror">
        <select id="{{ $id }}" name="{{ $name }}" {{ $attributes->except('id')->class(['input', 'select']) }}>
            @if ($placeholder !== null)<option value="">{{ $placeholder }}</option>@endif
            @foreach ($options as $key => $text)
                <option value="{{ $key }}" @selected((string) $key === $current)>{{ $text }}</option>
            @endforeach
        </select>
    </div>
    @error($name)<p class="field-error">{{ $message }}</p>@enderror
    @if ($hint && ! $errors->has($name))<p class="field-hint">{{ $hint }}</p>@endif
</div>
