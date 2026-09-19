@props([
    'field' => [],
    'module' => null,
    'name' => null,
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'help' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'options' => [],
    'attributes' => [],
    'error' => null,
    'class' => '',
    'labelClass' => 'field-label',
    'inputClass' => 'form-control form-control-sm',
    'wrapperClass' => 'form-group',
])

<?php
$module = $module ?? (request()->route()->getPrefix() ?? 'default');
$fieldName = $name ?? ($field['name'] ?? '');
$fieldConfig = $field ?: [];

$resolvedLabel = $label ?? $fieldConfig['label'] ?? ($fieldName ? \App\Support\LabelRegistry::fieldLabel($module, $fieldName) : '');
$resolvedPlaceholder = $placeholder ?? $fieldConfig['placeholder'] ?? ($fieldName ? \App\Support\LabelRegistry::fieldPlaceholder($module, $fieldName) : '');
$resolvedHelp = $help ?? $fieldConfig['help'] ?? ($fieldName ? \App\Support\LabelRegistry::fieldHelp($module, $fieldName) : '');
$resolvedRequired = $required ?? $fieldConfig['required'] ?? false;
$resolvedOptions = $options ?: ($fieldConfig['options'] ?? []);
$resolvedValue = $value ?? old($fieldName) ?? ($fieldConfig['value'] ?? '');
$resolvedError = $error ?? $errors->first($fieldName);
?>

<div class="{{ $wrapperClass }} {{ $resolvedError ? 'has-error' : '' }}" {{ $attributes }}>
    @if ($resolvedLabel)
        <label class="{{ $labelClass }}" {{ $fieldName ? 'for="' . $fieldName . '"' : '' }}>
            {{ $resolvedLabel }}
            @if ($resolvedRequired)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    @switch ($type)
        @case ('select')
            <select name="{{ $fieldName }}" id="{{ $fieldName }}" class="{{ $inputClass }} {{ $resolvedError ? 'is-invalid' : '' }}" {{ $disabled ? 'disabled' : '' }} {{ $readonly ? 'readonly' : '' }}>
                @if (isset($resolvedOptions['placeholder']) || empty($resolvedOptions))
                    <option value="">{{ $resolvedOptions['placeholder'] ?? 'Pilih...' }}</option>
                @endif
                @foreach ($resolvedOptions as $key => $optionLabel)
                    @if (is_array($optionLabel))
                        <optgroup label="{{ $key }}">
                            @foreach ($optionLabel as $optKey => $optLabel)
                                <option value="{{ $optKey }}" {{ (string)old($fieldName, $resolvedValue) === (string)$optKey ? 'selected' : '' }}>{{ $optLabel }}</option>
                            @endforeach
                        </optgroup>
                    @else
                        <option value="{{ $key }}" {{ (string)old($fieldName, $resolvedValue) === (string)$key ? 'selected' : '' }}>{{ $optionLabel }}</option>
                    @endif
                @endforeach
            </select>
            @break

        @case ('textarea')
            <textarea name="{{ $fieldName }}" id="{{ $fieldName }}" class="{{ $inputClass }} {{ $resolvedError ? 'is-invalid' : '' }}" placeholder="{{ $resolvedPlaceholder }}" {{ $disabled ? 'disabled' : '' }} {{ $readonly ? 'readonly' : '' }} rows="{{ $fieldConfig['rows'] ?? 3 }}">{{ $resolvedValue }}</textarea>
            @break

        @case ('checkbox')
            <div class="form-check">
                <input type="hidden" name="{{ $fieldName }}" value="0">
                <input type="checkbox" name="{{ $fieldName }}" id="{{ $fieldName }}" class="form-check-input" value="1" {{ (bool)old($fieldName, $resolvedValue) ? 'checked' : '' }} {{ $disabled ? 'disabled' : '' }}>
                <label class="form-check-label" for="{{ $fieldName }}">{{ $resolvedLabel }}</label>
            </div>
            @break

        @case ('radio')
            <div class="form-check">
                @foreach ($resolvedOptions as $key => $optionLabel)
                    <div class="form-check form-check-inline">
                        <input type="radio" name="{{ $fieldName }}" id="{{ $fieldName }}_{{ $key }}" class="form-check-input" value="{{ $key }}" {{ (string)old($fieldName, $resolvedValue) === (string)$key ? 'checked' : '' }} {{ $disabled ? 'disabled' : '' }}>
                        <label class="form-check-label" for="{{ $fieldName }}_{{ $key }}">{{ $optionLabel }}</label>
                    </div>
                @endforeach
            </div>
            @break

        @case ('file')
            <input type="file" name="{{ $fieldName }}" id="{{ $fieldName }}" class="{{ $inputClass }} {{ $resolvedError ? 'is-invalid' : '' }}" {{ $disabled ? 'disabled' : '' }} {{ $fieldConfig['accept'] ? 'accept="' . $fieldConfig['accept'] . '"' : '' }}>
            @break

        @default
            <input type="{{ $type }}" name="{{ $fieldName }}" id="{{ $fieldName }}" class="{{ $inputClass }} {{ $resolvedError ? 'is-invalid' : '' }}" value="{{ $resolvedValue }}" placeholder="{{ $resolvedPlaceholder }}" {{ $disabled ? 'disabled' : '' }} {{ $readonly ? 'readonly' : '' }} {{ $resolvedRequired ? 'required' : '' }} {{ $fieldConfig['maxlength'] ? 'maxlength="' . $fieldConfig['maxlength'] . '"' : '' }} {{ $fieldConfig['step'] ? 'step="' . $fieldConfig['step'] . '"' : '' }} {{ $fieldConfig['min'] ? 'min="' . $fieldConfig['min'] . '"' : '' }} {{ $fieldConfig['max'] ? 'max="' . $fieldConfig['max'] . '"' : '' }}>
    @endswitch

    @if ($resolvedError)
        <div class="invalid-feedback">{{ $resolvedError }}</div>
    @elseif ($resolvedHelp)
        <div class="form-text text-muted">{{ $resolvedHelp }}</div>
    @endif
</div>