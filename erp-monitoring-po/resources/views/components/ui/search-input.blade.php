@props([
    'name' => 'q',
    'placeholder' => null,
    'entity' => null,
    'value' => null,
    'ariaLabel' => null,
    'class' => '',
    'autofocus' => false,
    'withIcon' => true,
])

<?php
$entity = $entity ?? (request()->route()->getPrefix() ?? 'default');
$resolvedPlaceholder = $placeholder ?? \App\Support\LabelRegistry::get($module ?? $entity, 'search.placeholder', 'Cari...');
$resolvedAriaLabel = $ariaLabel ?? \App\Support\LabelRegistry::get($module ?? $entity, 'search.aria_label', 'Cari');
$resolvedValue = $value ?? request()->input($name);
?>

<div class="search-input-wrapper {{ $class }}" style="position: relative; width: {{ $width ?? '100%' }};">
    @if ($withIcon)
        <i class="fas fa-search" style="position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;"></i>
    @endif
    <input
        type="text"
        name="{{ $name }}"
        class="form-control form-control-sm {{ $withIcon ? 'ps-5' : '' }} {{ $class }}"
        placeholder="{{ $resolvedPlaceholder }}"
        value="{{ $resolvedValue }}"
        aria-label="{{ $resolvedAriaLabel }}"
        {{ $autofocus ? 'autofocus' : '' }}
    >
</div>