<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <table border="1">
        <tr>
            <th colspan="7">Monitoring Purchase Order</th>
        </tr>
        <tr>
            <td colspan="7">Generated At: <?php echo e($generatedAt->format('d-m-Y H:i:s')); ?></td>
        </tr>
        <tr>
            <th>PO Number</th>
            <th>PO Date</th>
            <th>Supplier</th>
            <th>Status</th>
            <th>ETA</th>
            <th>Notes</th>
            <th>Cancel Reason</th>
        </tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <tr>
                <td><?php echo e($row->po_number); ?></td>
                <td><?php echo e($row->po_date); ?></td>
                <td><?php echo e($row->supplier_name); ?></td>
                <td><?php echo e($row->status); ?></td>
                <td><?php echo e($row->eta_date ?: '-'); ?></td>
                <td><?php echo e($row->notes ?: '-'); ?></td>
                <td><?php echo e($row->cancel_reason ?: '-'); ?></td>
            </tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </table>
</body>
</html>
<?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views\po\exports\index.blade.php ENDPATH**/ ?>