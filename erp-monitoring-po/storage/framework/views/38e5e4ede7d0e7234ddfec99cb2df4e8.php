<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title',
    'subtitle' => null,
    'backRoute',
    'backLabel' => 'Kembali',
    'submitLabel' => 'Simpan Perubahan',
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
    'title',
    'subtitle' => null,
    'backRoute',
    'backLabel' => 'Kembali',
    'submitLabel' => 'Simpan Perubahan',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="page-shell">
    <section class="page-head">
        <div class="page-head-main">
            <h2 class="page-section-title"><?php echo e($title); ?></h2>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($subtitle): ?>
                <p class="page-section-subtitle"><?php echo e($subtitle); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="page-actions">
            <a href="<?php echo e($backRoute); ?>" class="btn btn-sm btn-light"><?php echo e($backLabel); ?></a>
        </div>
    </section>

    <section class="ui-surface">
        <div class="ui-surface-body">
            <div class="row g-3">
                <?php echo e($slot); ?>

            </div>

            <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="<?php echo e($backRoute); ?>" class="btn btn-light btn-sm"><?php echo e($backLabel); ?></a>
                <button class="btn btn-primary btn-sm"><?php echo e($submitLabel); ?></button>
            </div>
        </div>
    </section>
</div>
<?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views\components\master-edit-layout.blade.php ENDPATH**/ ?>