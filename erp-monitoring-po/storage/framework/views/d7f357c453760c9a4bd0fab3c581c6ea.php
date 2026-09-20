<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'icon' => '📦',
    'title' => 'Belum ada data',
    'subtitle' => 'Mulai tambah data baru untuk melihat data di sini.',
    'action' => null,
    'class' => '',
    'size' => 'md',
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
    'icon' => '📦',
    'title' => 'Belum ada data',
    'subtitle' => 'Mulai tambah data baru untuk melihat data di sini.',
    'action' => null,
    'class' => '',
    'size' => 'md',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="empty-state <?php echo e($class); ?> empty-state-<?php echo e($size); ?>" style="text-align: center; padding: <?php echo e($size === 'sm' ? '20px' : ($size === 'lg' ? '60px' : '40px')); ?> 20px;">
    <div class="empty-icon" style="font-size: <?php echo e($size === 'sm' ? '32px' : ($size === 'lg' ? '64px' : '48px')); ?>; margin-bottom: 12px;"><?php echo e($icon); ?></div>
    <div class="empty-title" style="font-size: <?php echo e($size === 'sm' ? '14px' : ($size === 'lg' ? '20px' : '16px')); ?>; font-weight: 600; color: var(--text-primary, #1e293b); margin-bottom: 4px;"><?php echo e($title); ?></div>
    <div class="empty-subtitle" style="font-size: <?php echo e($size === 'sm' ? '12px' : ($size === 'lg' ? '16px' : '13px')); ?>; color: var(--text-secondary, #64748b); margin-bottom: 16px;"><?php echo e($subtitle); ?></div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($action): ?>
        <?php
            $actionUrl = $action['url'] ?? '#';
            $actionLabel = $action['label'] ?? 'Tambah';
            $actionClass = $action['class'] ?? 'btn btn-primary btn-sm px-4';
            $actionIcon = $action['icon'] ?? '';
        ?>
        <a href="<?php echo e($actionUrl); ?>" class="<?php echo e($actionClass); ?>">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($actionIcon): ?>
                <i class="<?php echo e($actionIcon); ?>"></i>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php echo e($actionLabel); ?>

        </a>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views\components\ui\empty-state.blade.php ENDPATH**/ ?>