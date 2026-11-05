@props([
    'name',
    'label' => null,
    'type' => 'text',
    'icon' => null,
    'suffix' => null,
    'hint' => null,
    'value' => null,
    'wrapClass' => null,
])
@php $id = $attributes->get('id', $name); @endphp
<div class="field {{ $wrapClass }}">
    @if ($label)
        <label for="{{ $id }}" class="field-label">
            <span>{{ $label }}</span>
            @isset($labelRight)<span class="field-label-right">{{ $labelRight }}</span>@endisset
        </label>
    @endif
    <div class="input-wrap @error($name) has-error @enderror">
        @if ($icon)<x-ui.icon :name="$icon" class="input-icon" />@endif
        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}"
               @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
               {{ $attributes->except('id')->class(['input', 'has-icon' => (bool) $icon, 'has-suffix' => (bool) $suffix || $type === 'password']) }}>
        @if ($suffix)<span class="input-suffix">{{ $suffix }}</span>@endif
        @if ($type === 'password')
            <button type="button" class="input-toggle" data-toggle-password aria-label="Tampilkan kata sandi">
                <x-ui.icon name="eye" />
            </button>
        @endif
    </div>
    @error($name)<p class="field-error">{{ $message }}</p>@enderror
    @if ($hint && ! $errors->has($name))<p class="field-hint">{{ $hint }}</p>@endif
</div>
