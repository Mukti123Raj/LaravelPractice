@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'required' => false,
    'placeholder' => '',
    'rows' => null,
    'options' => [],
    'multiple' => false,
    'accept' => null,
    'min' => null,
    'max' => null,
    'step' => null,
    'readonly' => false,
    'disabled' => false,
    'class' => '',
    'id' => null
])

@php
    $inputId = $id ?? $name;
    $inputValue = $value ?? old($name);
    $isInvalid = $errors->has($name);
    $inputClass = 'form-control ' . ($isInvalid ? 'is-invalid' : '') . ' ' . $class;
@endphp

<div class="mb-3">
    @if($label)
        <label for="{{ $inputId }}" class="form-label">
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif
    
    @if($type === 'textarea')
        <textarea 
            class="{{ $inputClass }}" 
            id="{{ $inputId }}" 
            name="{{ $name }}" 
            rows="{{ $rows ?? 3 }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            {{ $disabled ? 'disabled' : '' }}
        >{{ $inputValue }}</textarea>
    @elseif($type === 'select')
        <select 
            class="{{ str_replace('form-control', 'form-select', $inputClass) }}" 
            id="{{ $inputId }}" 
            name="{{ $multiple ? $name . '[]' : $name }}"
            {{ $required ? 'required' : '' }}
            {{ $multiple ? 'multiple' : '' }}
            {{ $disabled ? 'disabled' : '' }}
        >
            @if(!$required)
                <option value="">{{ $placeholder ?: 'Select ' . $label }}</option>
            @endif
            @foreach($options as $optionValue => $optionLabel)
                <option 
                    value="{{ $optionValue }}" 
                    {{ (is_array($inputValue) ? in_array($optionValue, $inputValue) : $inputValue == $optionValue) ? 'selected' : '' }}
                >
                    {{ $optionLabel }}
                </option>
            @endforeach
        </select>
    @elseif($type === 'file')
        <input 
            type="file" 
            class="{{ $inputClass }}" 
            id="{{ $inputId }}" 
            name="{{ $name }}"
            {{ $accept ? 'accept="' . $accept . '"' : '' }}
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
        >
    @else
        <input 
            type="{{ $type }}" 
            class="{{ $inputClass }}" 
            id="{{ $inputId }}" 
            name="{{ $name }}" 
            value="{{ $inputValue }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $min !== null ? 'min="' . $min . '"' : '' }}
            {{ $max !== null ? 'max="' . $max . '"' : '' }}
            {{ $step !== null ? 'step="' . $step . '"' : '' }}
        >
    @endif
    
    @error($name)
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div>
