@props([
    'fields' => [],
    'module' => null,
    'context' => 'default',
    'method' => 'GET',
    'action' => null,
    'inline' => false,
    'showReset' => true,
    'showSubmit' => true,
    'submitLabel' => 'Terapkan',
    'resetLabel' => 'Reset',
    'class' => '',
    'formAttributes' => [],
])

<?php
$module = $module ?? (request()->route()->getPrefix() ?? 'default');
$filterFields = $fields ?: \App\Support\LabelRegistry::filterFields($module);
$contextFields = $filterFields[$context] ?? $filterFields;
$formAction = $action ?? request()->url();
$hasActiveFilters = request()->query()->count() > 0;
?>

@if ($hasActiveFilters)
    <div class="filter-tags mb-3">
        <span class="text-sm text-muted">Filter aktif:</span>
        @foreach (request()->query() as $key => $value)
            @if ($key !== '_token' && $value !== '')
                <span class="filter-tag">
                    {{ $key }}: "{{ $value }}"
                    <a href="{{ request()->fullUrlWithQuery([$key => null]) }}" class="tag-remove" aria-label="Hapus filter {{ $key }}">×</a>
                </span>
            @endif
        @endforeach
        @if ($showReset)
            <a href="{{ request()->url() }}" class="tag-clear">{{ $resetLabel }}</a>
        @endif
    </div>
@endif

<form method="{{ $method }}" action="{{ $formAction }}" class="filter-form {{ $inline ? 'filter-inline' : 'filter-grid' }} {{ $class }}" {{ $formAttributes }}>
    @foreach ($contextFields as $key => $field)
        @php
            $fieldName = $field['name'] ?? $key;
            $fieldLabel = $field['label'] ?? \App\Support\LabelRegistry::fieldLabel($module, $fieldName);
            $fieldType = $field['type'] ?? 'text';
            $fieldPlaceholder = $field['placeholder'] ?? '';
            $fieldOptions = $field['options'] ?? [];
            $fieldValue = request()->input($fieldName);
            $fieldRequired = $field['required'] ?? false;
            $fieldClass = $field['class'] ?? '';
            $fieldAttributes = $field['attributes'] ?? [];
            $fieldSpan = $field['span'] ?? 'auto';
        @endphp

        <div class="filter-field {{ $fieldClass }}" style="{{ $fieldSpan !== 'auto' ? 'grid-column: span ' . $fieldSpan . ';' : '' }}">
            <label class="field-label" for="filter_{{ $fieldName }}">{{ $fieldLabel }}</label>

            @switch ($fieldType)
                @case ('select')
                    <select name="{{ $fieldName }}" id="filter_{{ $fieldName }}" class="form-control form-control-sm" {{ $fieldAttributes }}>
                        @if (isset($fieldOptions['placeholder']) || empty($fieldOptions))
                            <option value="">{{ $fieldOptions['placeholder'] ?? 'Semua' }}</option>
                        @endif
                        @if (isset($field['dynamic_options']))
                            @php $options = \App\Support\LabelRegistry::statusOptions($field['dynamic_options']); @endphp
                            @foreach ($options as $optKey => $optLabel)
                                <option value="{{ $optKey }}" {{ (string)$fieldValue === (string)$optKey ? 'selected' : '' }}>{{ $optLabel }}</option>
                            @endforeach
                        @else
                            @foreach ($fieldOptions as $optKey => $optLabel)
                                <option value="{{ $optKey }}" {{ (string)$fieldValue === (string)$optKey ? 'selected' : '' }}>{{ $optLabel }}</option>
                            @endforeach
                        @endif
                    </select>
                    @break

                @case ('date')
                    <input type="date" name="{{ $fieldName }}" id="filter_{{ $fieldName }}" class="form-control form-control-sm" value="{{ $fieldValue }}" {{ $fieldAttributes }}>
                    @break

                @case ('number')
                    <input type="number" name="{{ $fieldName }}" id="filter_{{ $fieldName }}" class="form-control form-control-sm" value="{{ $fieldValue }}" placeholder="{{ $fieldPlaceholder }}" {{ $fieldAttributes }}>
                    @break

                @default
                    <input type="text" name="{{ $fieldName }}" id="filter_{{ $fieldName }}" class="form-control form-control-sm" value="{{ $fieldValue }}" placeholder="{{ $fieldPlaceholder }}" {{ $fieldAttributes }}>
            @endswitch
        </div>
    @endforeach

    <div class="filter-actions {{ $inline ? 'd-flex gap-2' : '' }}">
        @if ($showSubmit)
            <button type="submit" class="btn btn-primary btn-sm {{ $inline ? '' : 'w-100' }}">{{ $submitLabel }}</button>
        @endif
        @if ($showReset)
            <a href="{{ request()->url() }}" class="btn btn-light btn-sm {{ $inline ? '' : 'w-100' }}">{{ $resetLabel }}</a>
        @endif
    </div>
</form>