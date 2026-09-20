<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'columns' => [],
    'rows' => [],
    'entity' => null,
    'view' => 'index',
    'emptyState' => null,
    'sortable' => false,
    'exportable' => false,
    'striped' => true,
    'hoverable' => true,
    'rowClass' => null,
    'rowId' => null,
    'tableClass' => 'table table-hover ui-table',
    'theadClass' => '',
    'tbodyClass' => '',
    'attributes' => [],
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
    'columns' => [],
    'rows' => [],
    'entity' => null,
    'view' => 'index',
    'emptyState' => null,
    'sortable' => false,
    'exportable' => false,
    'striped' => true,
    'hoverable' => true,
    'rowClass' => null,
    'rowId' => null,
    'tableClass' => 'table table-hover ui-table',
    'theadClass' => '',
    'tbodyClass' => '',
    'attributes' => [],
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
$resolvedColumns = $columns ?: (function () use ($entity, $view) {
    return \App\Support\LabelRegistry::tableColumns($entity, $view);
})();

$emptyStateContent = $emptyState ?? [
    'icon' => '📦',
    'title' => 'Belum ada data',
    'subtitle' => 'Mulai tambah data baru untuk melihat data di sini.',
    'action' => null,
];
?>

<div class="table-wrap table-responsive">
    <table class="<?php echo e($tableClass); ?>" <?php echo e($attributes->merge(['data-export-title' => $entity])); ?>>
        <thead class="<?php echo e($theadClass); ?>">
            <tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $resolvedColumns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php
                        $label = is_array($column) ? ($column['label'] ?? $column['title'] ?? \App\Support\LabelRegistry::columnLabel($entity, $key, $view)) : $column;
                        $colAttributes = is_array($column) && is_array($column['attributes'] ?? null) ? $column['attributes'] : [];
                        $colAttributesBag = (new \Illuminate\View\ComponentAttributeBag())->merge($colAttributes);
                        $isSortable = $sortable && (is_array($column) ? ($column['sortable'] ?? true) : true);
                    ?>
                    <th <?php echo e($colAttributesBag); ?> <?php echo e($isSortable ? 'data-sortable="true"' : ''); ?>>
                        <?php echo e($label); ?>

                    </th>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </tr>
        </thead>
        <tbody class="<?php echo e($tbodyClass); ?>">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $rowClassName = $rowClass ? (is_callable($rowClass) ? $rowClass($row) : $rowClass) : '';
                    $rowIdValue = $rowId ? (is_callable($rowId) ? $rowId($row) : $row->{$rowId} ?? null) : null;
                ?>
                <tr class="<?php echo e($rowClassName); ?>" <?php echo e($rowIdValue ? 'data-row-id="' . $rowIdValue . '"' : ''); ?>>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $resolvedColumns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $render = is_array($column) ? ($column['render'] ?? null) : null;
                            $value = $render ? $render($row) : ($row->{$key} ?? ($row[$key] ?? '-'));
                            $tdAttributes = is_array($column) && is_array($column['tdAttributes'] ?? null) ? $column['tdAttributes'] : [];
                            $tdAttributesBag = (new \Illuminate\View\ComponentAttributeBag())->merge($tdAttributes);
                        ?>
                        <td <?php echo e($tdAttributesBag); ?>>
                            <?php echo $value; ?>

                        </td>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <tr>
                    <td colspan="<?php echo e(count($resolvedColumns)); ?>">
                        <?php if (isset($component)) { $__componentOriginal3607a477fdef7402bc742abad5df9c51 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3607a477fdef7402bc742abad5df9c51 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.empty-state','data' => ['icon' => $emptyStateContent['icon'],'title' => $emptyStateContent['title'],'subtitle' => $emptyStateContent['subtitle'],'action' => $emptyStateContent['action']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($emptyStateContent['icon']),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($emptyStateContent['title']),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($emptyStateContent['subtitle']),'action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($emptyStateContent['action'])]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3607a477fdef7402bc742abad5df9c51)): ?>
<?php $attributes = $__attributesOriginal3607a477fdef7402bc742abad5df9c51; ?>
<?php unset($__attributesOriginal3607a477fdef7402bc742abad5df9c51); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3607a477fdef7402bc742abad5df9c51)): ?>
<?php $component = $__componentOriginal3607a477fdef7402bc742abad5df9c51; ?>
<?php unset($__componentOriginal3607a477fdef7402bc742abad5df9c51); ?>
<?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>
</div>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($exportable): ?>
    <div class="table-export-actions mt-2">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-export-table="<?php echo e($entity); ?>">
            <i class="fas fa-file-excel"></i> Export Excel
        </button>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views\components\ui\data-table.blade.php ENDPATH**/ ?>