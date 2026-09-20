<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => 'q',
    'placeholder' => null,
    'entity' => null,
    'value' => null,
    'ariaLabel' => null,
    'class' => '',
    'autofocus' => false,
    'withIcon' => true,
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
    'name' => 'q',
    'placeholder' => null,
    'entity' => null,
    'value' => null,
    'ariaLabel' => null,
    'class' => '',
    'autofocus' => false,
    'withIcon' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$entity = $entity ?? (request()->route()->getPrefix() ?? 'default');
$resolvedPlaceholder = $placeholder ?? \App\Support\LabelRegistry::get($module ?? $entity, 'search.placeholder', 'Cari...');
$resolvedAriaLabel = $ariaLabel ?? \App\Support\LabelRegistry::get($module ?? $entity, 'search.aria_label', 'Cari');
$resolvedValue = $value ?? request()->input($name);
?>

<div class="search-input-wrapper <?php echo e($class); ?>" style="position: relative; width: <?php echo e($width ?? '100%'); ?>;">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($withIcon): ?>
        <i class="fas fa-search" style="position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;"></i>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <input
        type="text"
        name="<?php echo e($name); ?>"
        class="form-control form-control-sm <?php echo e($withIcon ? 'ps-5' : ''); ?> <?php echo e($class); ?>"
        placeholder="<?php echo e($resolvedPlaceholder); ?>"
        value="<?php echo e($resolvedValue); ?>"
        aria-label="<?php echo e($resolvedAriaLabel); ?>"
        <?php echo e($autofocus ? 'autofocus' : ''); ?>

    >
</div><?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views\components\ui\search-input.blade.php ENDPATH**/ ?>