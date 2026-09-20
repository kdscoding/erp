<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
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
$filterFields = $fields ?: \App\Support\LabelRegistry::filterFields($module);
$contextFields = $filterFields[$context] ?? $filterFields;
$formAction = $action ?? request()->url();
$hasActiveFilters = count(request()->query()) > 0;
$formAttributesBag = (new \Illuminate\View\ComponentAttributeBag())->merge(is_array($formAttributes) ? $formAttributes : []);
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasActiveFilters): ?>
    <div class="filter-tags mb-3">
        <span class="text-sm text-muted">Filter aktif:</span>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = request()->query(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($key !== '_token' && $value !== ''): ?>
                <span class="filter-tag">
                    <?php echo e($key); ?>: "<?php echo e($value); ?>"
                    <a href="<?php echo e(request()->fullUrlWithQuery([$key => null])); ?>" class="tag-remove" aria-label="Hapus filter <?php echo e($key); ?>">×</a>
                </span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showReset): ?>
            <a href="<?php echo e(request()->url()); ?>" class="tag-clear"><?php echo e($resetLabel); ?></a>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<form method="<?php echo e($method); ?>" action="<?php echo e($formAction); ?>" class="filter-form <?php echo e($inline ? 'filter-inline' : 'filter-grid'); ?> <?php echo e($class); ?>" <?php echo e($formAttributesBag); ?>>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $contextFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <?php
            $fieldName = $field['name'] ?? $key;
            $fieldLabel = $field['label'] ?? \App\Support\LabelRegistry::fieldLabel($module, $fieldName);
            $fieldType = $field['type'] ?? 'text';
            $fieldPlaceholder = $field['placeholder'] ?? '';
            $fieldOptions = $field['options'] ?? [];
            $fieldValue = request()->input($fieldName);
            $fieldRequired = $field['required'] ?? false;
            $fieldClass = $field['class'] ?? '';
            $fieldAttributes = $field['attributes'] ?? [];
            $fieldAttributesBag = (new \Illuminate\View\ComponentAttributeBag())->merge(is_array($fieldAttributes) ? $fieldAttributes : []);
            $fieldSpan = $field['span'] ?? 'auto';
        ?>

        <div class="filter-field <?php echo e($fieldClass); ?>" style="<?php echo e($fieldSpan !== 'auto' ? 'grid-column: span ' . $fieldSpan . ';' : ''); ?>">
            <label class="field-label" for="filter_<?php echo e($fieldName); ?>"><?php echo e($fieldLabel); ?></label>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php switch($fieldType):
                case ('select'): ?>
                    <select name="<?php echo e($fieldName); ?>" id="filter_<?php echo e($fieldName); ?>" class="form-control form-control-sm" <?php echo e($fieldAttributesBag); ?>>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(is_array($fieldOptions) && (isset($fieldOptions['placeholder']) || empty($fieldOptions))): ?>
                            <option value=""><?php echo e($fieldOptions['placeholder'] ?? 'Semua'); ?></option>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($field['dynamic_options'])): ?>
                            <?php $options = \App\Support\LabelRegistry::statusOptions($field['dynamic_options']); ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $optKey => $optLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($optKey); ?>" <?php echo e((string)$fieldValue === (string)$optKey ? 'selected' : ''); ?>><?php echo e($optLabel); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php else: ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $fieldOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $optKey => $optLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($optKey); ?>" <?php echo e((string)$fieldValue === (string)$optKey ? 'selected' : ''); ?>><?php echo e($optLabel); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php break; ?>

                <?php case ('date'): ?>
                    <input type="date" name="<?php echo e($fieldName); ?>" id="filter_<?php echo e($fieldName); ?>" class="form-control form-control-sm" value="<?php echo e($fieldValue); ?>" <?php echo e($fieldAttributesBag); ?>>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php break; ?>

                <?php case ('number'): ?>
                    <input type="number" name="<?php echo e($fieldName); ?>" id="filter_<?php echo e($fieldName); ?>" class="form-control form-control-sm" value="<?php echo e($fieldValue); ?>" placeholder="<?php echo e($fieldPlaceholder); ?>" <?php echo e($fieldAttributesBag); ?>>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php break; ?>

                <?php default: ?>
                    <input type="text" name="<?php echo e($fieldName); ?>" id="filter_<?php echo e($fieldName); ?>" class="form-control form-control-sm" value="<?php echo e($fieldValue); ?>" placeholder="<?php echo e($fieldPlaceholder); ?>" <?php echo e($fieldAttributesBag); ?>>
            <?php endswitch; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

    <div class="filter-actions <?php echo e($inline ? 'd-flex gap-2' : ''); ?>">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showSubmit): ?>
            <button type="submit" class="btn btn-primary btn-sm <?php echo e($inline ? '' : 'w-100'); ?>"><?php echo e($submitLabel); ?></button>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showReset): ?>
            <a href="<?php echo e(request()->url()); ?>" class="btn btn-light btn-sm <?php echo e($inline ? '' : 'w-100'); ?>"><?php echo e($resetLabel); ?></a>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</form><?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views\components\ui\filter-bar.blade.php ENDPATH**/ ?>