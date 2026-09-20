<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'route' => null,
    'label' => null,
    'entity' => null,
    'icon' => '+',
    'class' => '',
    'title' => null,
    'position' => 'bottom-right',
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
    'route' => null,
    'label' => null,
    'entity' => null,
    'icon' => '+',
    'class' => '',
    'title' => null,
    'position' => 'bottom-right',
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
$resolvedRoute = $route ?? route("{$entity}.create");
$resolvedLabel = $label ?? \App\Support\LabelRegistry::action($entity, 'create');
$resolvedTitle = $title ?? $resolvedLabel;
?>

<a href="<?php echo e($resolvedRoute); ?>" class="fab-add <?php echo e($class); ?>" title="<?php echo e($resolvedTitle); ?>" style="<?php echo e($position === 'bottom-right' ? 'bottom: 24px; right: 24px;' : ($position === 'bottom-left' ? 'bottom: 24px; left: 24px;' : '')); ?>">
    <span class="fab-icon"><?php echo e($icon); ?></span>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($label || $entity): ?>
        <span class="fab-label"><?php echo e($resolvedLabel); ?></span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</a>

<style>
    .fab-add {
        position: fixed;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: var(--lemon-green, #9ecb3c);
        color: var(--lemon-ink, #304218);
        border: none;
        box-shadow: 0 4px 12px rgba(158, 203, 60, 0.35);
        cursor: pointer;
        z-index: 100;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .fab-add:hover {
        transform: scale(1.08) translateY(-2px);
        box-shadow: 0 6px 20px rgba(158, 203, 60, 0.5);
        color: var(--lemon-ink, #304218);
        text-decoration: none;
    }

    .fab-icon {
        font-size: 22px;
        font-weight: 700;
        line-height: 1;
    }

    .fab-label {
        font-size: 9px;
        font-weight: 600;
        margin-top: 1px;
        white-space: nowrap;
    }

    @media (max-width: 768px) {
        .fab-add {
            width: 48px;
            height: 48px;
            bottom: 16px;
            right: 16px;
        }
    }
</style><?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views\components\ui\fab.blade.php ENDPATH**/ ?>