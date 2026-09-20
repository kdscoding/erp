<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <table border="1">
        <tr>
            <th colspan="2">Purchase Order Detail</th>
        </tr>
        <tr><td>Generated At</td><td><?php echo e($generatedAt->format('d-m-Y H:i:s')); ?></td></tr>
        <tr><td>PO Number</td><td><?php echo e($po->po_number); ?></td></tr>
        <tr><td>PO Date</td><td><?php echo e($po->po_date); ?></td></tr>
        <tr><td>Supplier</td><td><?php echo e($po->supplier_name); ?></td></tr>
        <tr><td>Status</td><td><?php echo e($po->status); ?></td></tr>
        <tr><td>Notes</td><td><?php echo e($po->notes ?: '-'); ?></td></tr>
    </table>

    <br>

    <table border="1">
        <tr>
            <th colspan="8">Item Monitoring</th>
        </tr>
        <tr>
            <th>Item Code</th>
            <th>Item Name</th>
            <th>Ordered Qty</th>
            <th>Received Qty</th>
            <th>Outstanding Qty</th>
            <th>Item Status</th>
            <th>ETD</th>
            <th>Cancel Reason</th>
        </tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <tr>
                <td><?php echo e($item->item_code); ?></td>
                <td><?php echo e($item->item_name); ?></td>
                <td><?php echo e(\App\Support\NumberFormatter::trim($item->ordered_qty)); ?> <?php echo e($item->unit_name); ?></td>
                <td><?php echo e(\App\Support\NumberFormatter::trim($item->received_qty)); ?> <?php echo e($item->unit_name); ?></td>
                <td><?php echo e(\App\Support\NumberFormatter::trim($item->outstanding_qty)); ?> <?php echo e($item->unit_name); ?></td>
                <td><?php echo e($item->monitoring_status); ?>

                    <?php ($helpText = \App\Support\PurchaseOrderItemStatusResolver::statusHelpText($item->monitoring_status)); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($helpText): ?>
                        <br><small><?php echo e($helpText); ?></small>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </td>
                <td><?php echo e($item->etd_date ?: '-'); ?></td>
                <td><?php echo e($item->cancel_reason ?: '-'); ?></td>
            </tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </table>

    <br>

    <table border="1">
        <tr>
            <th colspan="9">Tracking Shipment / GR</th>
        </tr>
        <tr>
            <th>Item Code</th>
            <th>Tanggal</th>
            <th>Deskripsi Aktivitas</th>
            <th>Qty Order</th>
            <th>Qty Masuk</th>
            <th>Sisa (OS)</th>
            <th>No Shipment</th>
            <th>No GR</th>
            <th>Status</th>
        </tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <?php ($runningReceivedQty = 0); ?>
            <?php ($initialTimelineStatus = match (true) {
                $item->monitoring_status === \App\Support\DocumentTermCodes::ITEM_CANCELLED => \App\Support\DocumentTermCodes::ITEM_CANCELLED,
                $item->monitoring_status === \App\Support\DocumentTermCodes::ITEM_FORCE_CLOSED => \App\Support\DocumentTermCodes::ITEM_FORCE_CLOSED,
                $item->etd_date && \Carbon\Carbon::parse($item->etd_date)->isPast() && (float) $item->received_qty <= 0 => \App\Support\DocumentTermCodes::ITEM_LATE,
                $item->etd_date => \App\Support\DocumentTermCodes::ITEM_CONFIRMED,
                default => \App\Support\DocumentTermCodes::ITEM_WAITING,
            }); ?>
            <tr>
                <td><?php echo e($item->item_code); ?></td>
                <td><?php echo e(\Carbon\Carbon::parse($po->po_date)->format('d/m/Y')); ?></td>
                <td>PO Created</td>
                <td><?php echo e(\App\Support\NumberFormatter::trim($item->ordered_qty)); ?></td>
                <td>0</td>
                <td><?php echo e(\App\Support\NumberFormatter::trim($item->ordered_qty)); ?></td>
                <td>-</td>
                <td>-</td>
                <td><?php echo e($initialTimelineStatus); ?></td>
            </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $item->tracking_rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tracking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php ($shipmentDate = $tracking->shipment_date ? \Carbon\Carbon::parse($tracking->shipment_date)->format('d/m/Y') : '-'); ?>
                <?php ($shipmentNumber = $tracking->shipment_number ?: 'Belum ada nomor shipment'); ?>
                <?php ($deliveryNoteNumber = $tracking->delivery_note_number ?: '-'); ?>
                <?php ($shipmentLabel = 'Pengiriman ke-' . $loop->iteration . ' | DN ' . $deliveryNoteNumber); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tracking->gr_rows->isEmpty()): ?>
                    <?php ($shipmentTimelineStatus = $runningReceivedQty > 0 ? \App\Support\DocumentTermCodes::ITEM_PARTIAL : ($initialTimelineStatus === \App\Support\DocumentTermCodes::ITEM_WAITING ? \App\Support\DocumentTermCodes::ITEM_CONFIRMED : $initialTimelineStatus)); ?>
                    <tr>
                        <td><?php echo e($item->item_code); ?></td>
                        <td><?php echo e($shipmentDate); ?></td>
                        <td><?php echo e($shipmentLabel); ?> (Belum GR)</td>
                        <td>-</td>
                        <td>0</td>
                        <td><?php echo e(\App\Support\NumberFormatter::trim(max(0, (float) $item->ordered_qty - $runningReceivedQty))); ?></td>
                        <td><?php echo e($shipmentNumber); ?></td>
                        <td>-</td>
                        <td><?php echo e($shipmentTimelineStatus); ?></td>
                    </tr>
                <?php else: ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $tracking->gr_rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php ($runningReceivedQty += (float) ($gr->gr_received_qty ?? 0)); ?>
                        <?php ($remainingQty = max(0, (float) $item->ordered_qty - $runningReceivedQty)); ?>
                        <?php ($activityLabel = $shipmentLabel . ($tracking->gr_rows->count() > 1 ? ' / GR ' . $loop->iteration : '')); ?>
                        <?php ($timelineStatus = $remainingQty <= 0 ? \App\Support\DocumentTermCodes::ITEM_CLOSED : ($runningReceivedQty > 0 ? \App\Support\DocumentTermCodes::ITEM_PARTIAL : $initialTimelineStatus)); ?>
                        <tr>
                            <td><?php echo e($item->item_code); ?></td>
                            <td><?php echo e($gr->receipt_date ? \Carbon\Carbon::parse($gr->receipt_date)->format('d/m/Y') : '-'); ?></td>
                            <td><?php echo e($activityLabel); ?></td>
                            <td>-</td>
                            <td><?php echo e(\App\Support\NumberFormatter::trim($gr->gr_received_qty ?? 0)); ?></td>
                            <td><?php echo e(\App\Support\NumberFormatter::trim($remainingQty)); ?></td>
                            <td><?php echo e($shipmentNumber); ?></td>
                            <td><?php echo e($gr->gr_number ?: '-'); ?></td>
                            <td><?php echo e($timelineStatus); ?></td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </table>
</body>
</html>
<?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views\po\exports\detail.blade.php ENDPATH**/ ?>