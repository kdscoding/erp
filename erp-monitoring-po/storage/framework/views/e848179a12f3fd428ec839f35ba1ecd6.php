<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'counts' => [],
    'received' => 0,
    'ordered' => 0,
    'unit' => '',
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
    'counts' => [],
    'received' => 0,
    'ordered' => 0,
    'unit' => '',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $percent = (float) $ordered > 0 ? round((float) $received / (float) $ordered * 100, 1) : 0;
    $progressLabel = \App\Support\NumberFormatter::trim($received) . ' / ' . \App\Support\NumberFormatter::trim($ordered);

    $statusConfig = [
        'waiting' => ['label' => 'Waiting', 'class' => 'text-secondary'],
        'confirmed' => ['label' => 'Confirmed', 'class' => 'text-warning'],
        'late' => ['label' => 'Late', 'class' => 'text-danger'],
        'partial' => ['label' => 'Partial', 'class' => 'text-primary'],
        'closed' => ['label' => 'Closed', 'class' => 'text-success'],
        'force_closed' => ['label' => 'Force Closed', 'class' => 'text-dark'],
        'cancelled' => ['label' => 'Cancelled', 'class' => 'text-danger'],
    ];
?>

<div class="item-summary">
    <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-2">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['total', 'waiting', 'confirmed', 'late', 'partial', 'closed', 'force_closed', 'cancelled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <?php ($count = $counts[$key] ?? 0); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($count > 0): ?>
                <?php ($label = $key === 'total' ? 'Total' : ($statusConfig[$key]['label'] ?? ucfirst(str_replace('_', ' ', $key)))); ?>
                <?php ($textClass = $key === 'total' ? 'text-dark' : ($statusConfig[$key]['class'] ?? '')); ?>
                <span class="badge bg-light <?php echo e($textClass); ?> border flex-nowrap">
                    <i class="fas fa-circle" style="font-size: 6px; margin-right: 4px;"></i>
                    <?php echo e($label); ?>: <?php echo e($count); ?>

                </span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((float) $ordered > 0): ?>
        <div class="progress" style="height: 10px">
            <div class="progress-bar bg-success" role="progressbar"
                style="width: <?php echo e($percent); ?>%"
                aria-valuenow="<?php echo e($percent); ?>" aria-valuemin="0" aria-valuemax="100">
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-1">
            <span class="small text-muted"><?php echo e($progressLabel); ?> <?php echo e($unit); ?></span>
            <span class="small fw-bold"><?php echo e($percent); ?>%</span>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views/components/item-summary.blade.php ENDPATH**/ ?>