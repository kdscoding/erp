<table>
    <tr><th colspan="5">Summary Outstanding PO</th></tr>
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
    <tr><th colspan="8">Outstanding per PO</th></tr>
    <tr>
        <th>PO</th>
        <th>Supplier</th>
        <th>Tanggal PO</th>
        <th>ETA</th>
        <th>Item Outstanding</th>
        <th>Total Order</th>
        <th>Total Pengiriman</th>
        <th>Outstanding</th>
    </tr>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $outstandingPoRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <tr>
            <td><?php echo e($row->po_number); ?></td>
            <td><?php echo e($row->supplier_name); ?></td>
            <td><?php echo e($row->po_date); ?></td>
            <td><?php echo e($row->eta_date); ?></td>
            <td><?php echo e($row->outstanding_item_count); ?></td>
            <td><?php echo e($row->total_order_qty); ?></td>
            <td><?php echo e($row->total_shipped_qty); ?></td>
            <td><?php echo e($row->total_outstanding_qty); ?></td>
        </tr>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
</table>
<?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views\summary-po-export.blade.php ENDPATH**/ ?>