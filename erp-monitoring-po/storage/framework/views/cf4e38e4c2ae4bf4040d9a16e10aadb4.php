<table>
    <tr><th colspan="5">Summary Outstanding Item</th></tr>
    <tr>
        <th>Outstanding PO</th>
        <th>Outstanding Item</th>
        <th>Total Order</th>
        <th>Total Pengiriman</th>
        <th>Total Outstanding</th>
    </tr>
    <tr>
        <td><?php echo e($summaryMetrics['outstanding_po'] ?? 0); ?></td>
        <td><?php echo e($summaryMetrics['outstanding_item'] ?? 0); ?></td>
        <td><?php echo e($summaryMetrics['total_order_qty'] ?? 0); ?></td>
        <td><?php echo e($summaryMetrics['total_shipped_qty'] ?? 0); ?></td>
        <td><?php echo e($summaryMetrics['total_outstanding_qty'] ?? 0); ?></td>
    </tr>
</table>

<table>
    <tr><th colspan="7">Outstanding per Item</th></tr>
    <tr>
        <th>PO</th>
        <th>Item</th>
        <th>Supplier</th>
        <th>ETD</th>
        <th>Order</th>
        <th>Pengiriman</th>
        <th>Outstanding</th>
    </tr>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $outstandingItemRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <tr>
            <td><?php echo e($row->po_number); ?></td>
            <td><?php echo e($row->item_code); ?> - <?php echo e($row->item_name); ?></td>
            <td><?php echo e($row->supplier_name); ?></td>
            <td><?php echo e($row->etd_date); ?></td>
            <td><?php echo e($row->ordered_qty); ?></td>
            <td><?php echo e($row->received_qty); ?></td>
            <td><?php echo e($row->outstanding_qty); ?></td>
        </tr>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
</table>
<?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views\summary-item-export.blade.php ENDPATH**/ ?>