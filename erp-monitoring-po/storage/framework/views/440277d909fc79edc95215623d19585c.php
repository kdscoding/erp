<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
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
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
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
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

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

<div class="<?php echo e($wrapperClass); ?> <?php echo e($resolvedError ? 'has-error' : ''); ?>" <?php echo e($attributes); ?>>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($resolvedLabel): ?>
        <label class="<?php echo e($labelClass); ?>" <?php echo e($fieldName ? 'for="' . $fieldName . '"' : ''); ?>>
            <?php echo e($resolvedLabel); ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($resolvedRequired): ?>
                <span class="text-danger">*</span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </label>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php switch($type):
        case ('select'): ?>
            <select name="<?php echo e($fieldName); ?>" id="<?php echo e($fieldName); ?>" class="<?php echo e($inputClass); ?> <?php echo e($resolvedError ? 'is-invalid' : ''); ?>" <?php echo e($disabled ? 'disabled' : ''); ?> <?php echo e($readonly ? 'readonly' : ''); ?>>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($resolvedOptions['placeholder']) || empty($resolvedOptions)): ?>
                    <option value=""><?php echo e($resolvedOptions['placeholder'] ?? 'Pilih...'); ?></option>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $resolvedOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $optionLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(is_array($optionLabel)): ?>
                        <optgroup label="<?php echo e($key); ?>">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $optionLabel; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $optKey => $optLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($optKey); ?>" <?php echo e((string)old($fieldName, $resolvedValue) === (string)$optKey ? 'selected' : ''); ?>><?php echo e($optLabel); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </optgroup>
                    <?php else: ?>
                        <option value="<?php echo e($key); ?>" <?php echo e((string)old($fieldName, $resolvedValue) === (string)$key ? 'selected' : ''); ?>><?php echo e($optionLabel); ?></option>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php break; ?>

        <?php case ('textarea'): ?>
            <textarea name="<?php echo e($fieldName); ?>" id="<?php echo e($fieldName); ?>" class="<?php echo e($inputClass); ?> <?php echo e($resolvedError ? 'is-invalid' : ''); ?>" placeholder="<?php echo e($resolvedPlaceholder); ?>" <?php echo e($disabled ? 'disabled' : ''); ?> <?php echo e($readonly ? 'readonly' : ''); ?> rows="<?php echo e($fieldConfig['rows'] ?? 3); ?>"><?php echo e($resolvedValue); ?></textarea>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php break; ?>

        <?php case ('checkbox'): ?>
            <div class="form-check">
                <input type="hidden" name="<?php echo e($fieldName); ?>" value="0">
                <input type="checkbox" name="<?php echo e($fieldName); ?>" id="<?php echo e($fieldName); ?>" class="form-check-input" value="1" <?php echo e((bool)old($fieldName, $resolvedValue) ? 'checked' : ''); ?> <?php echo e($disabled ? 'disabled' : ''); ?>>
                <label class="form-check-label" for="<?php echo e($fieldName); ?>"><?php echo e($resolvedLabel); ?></label>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php break; ?>

        <?php case ('radio'): ?>
            <div class="form-check">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $resolvedOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $optionLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="form-check form-check-inline">
                        <input type="radio" name="<?php echo e($fieldName); ?>" id="<?php echo e($fieldName); ?>_<?php echo e($key); ?>" class="form-check-input" value="<?php echo e($key); ?>" <?php echo e((string)old($fieldName, $resolvedValue) === (string)$key ? 'checked' : ''); ?> <?php echo e($disabled ? 'disabled' : ''); ?>>
                        <label class="form-check-label" for="<?php echo e($fieldName); ?>_<?php echo e($key); ?>"><?php echo e($optionLabel); ?></label>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php break; ?>

        <?php case ('file'): ?>
            <input type="file" name="<?php echo e($fieldName); ?>" id="<?php echo e($fieldName); ?>" class="<?php echo e($inputClass); ?> <?php echo e($resolvedError ? 'is-invalid' : ''); ?>" <?php echo e($disabled ? 'disabled' : ''); ?> <?php echo e($fieldConfig['accept'] ? 'accept="' . $fieldConfig['accept'] . '"' : ''); ?>>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php break; ?>

        <?php default: ?>
            <input type="<?php echo e($type); ?>" name="<?php echo e($fieldName); ?>" id="<?php echo e($fieldName); ?>" class="<?php echo e($inputClass); ?> <?php echo e($resolvedError ? 'is-invalid' : ''); ?>" value="<?php echo e($resolvedValue); ?>" placeholder="<?php echo e($resolvedPlaceholder); ?>" <?php echo e($disabled ? 'disabled' : ''); ?> <?php echo e($readonly ? 'readonly' : ''); ?> <?php echo e($resolvedRequired ? 'required' : ''); ?> <?php echo e($fieldConfig['maxlength'] ? 'maxlength="' . $fieldConfig['maxlength'] . '"' : ''); ?> <?php echo e($fieldConfig['step'] ? 'step="' . $fieldConfig['step'] . '"' : ''); ?> <?php echo e($fieldConfig['min'] ? 'min="' . $fieldConfig['min'] . '"' : ''); ?> <?php echo e($fieldConfig['max'] ? 'max="' . $fieldConfig['max'] . '"' : ''); ?>>
    <?php endswitch; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($resolvedError): ?>
        <div class="invalid-feedback"><?php echo e($resolvedError); ?></div>
    <?php elseif($resolvedHelp): ?>
        <div class="form-text text-muted"><?php echo e($resolvedHelp); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views\components\ui\form-field.blade.php ENDPATH**/ ?>