@props(['name', 'label' => null, 'value' => null, 'hint' => null])
@php $id = $attributes->get('id', $name); @endphp
<div class="field">
    @if ($label)<label for="{{ $id }}" class="field-label"><span>{{ $label }}</span></label>@endif
    <textarea id="{{ $id }}" name="{{ $name }}" {{ $attributes->except('id')->class(['input', 'textarea', 'has-error' => $errors->has($name)]) }}>{{ old($name, $value) }}</textarea>
    @error($name)<p class="field-error">{{ $message }}</p>@enderror
    @if ($hint && ! $errors->has($name))<p class="field-hint">{{ $hint }}</p>@endif
</div>
