Item: <?php echo e($item->item_code); ?> - <?php echo e($item->item_name); ?>

Ordered: <?php echo e(\App\Support\NumberFormatter::trim($item->ordered_qty)); ?> <?php echo e($item->unit_name); ?>

Received: <?php echo e(\App\Support\NumberFormatter::trim($item->received_qty)); ?> <?php echo e($item->unit_name); ?>

Outstanding: <?php echo e(\App\Support\NumberFormatter::trim($item->outstanding_qty)); ?> <?php echo e($item->unit_name); ?>

PO Date: <?php echo e(\Carbon\Carbon::parse($po->po_date)->format('d/m/Y')); ?>


Timeline:
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $timeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
    <?php echo e($loop->iteration); ?>. <?php echo e($entry['date']); ?> | <?php echo e($entry['description']); ?>

       Qty Order: <?php echo e($entry['ordered_qty']); ?> <?php echo e($entry['ordered_qty'] !== '-' ? $item->unit_name : ''); ?>

       Qty Masuk: <?php echo e($entry['received_qty']); ?> <?php echo e($entry['received_qty'] !== '-' ? $item->unit_name : ''); ?>

       Sisa (OS): <?php echo e($entry['outstanding_qty']); ?> <?php echo e($entry['outstanding_qty'] !== '-' ? $item->unit_name : ''); ?>

       No Shipment: <?php echo e($entry['shipment_number']); ?>

       No GR: <?php echo e($entry['gr_number']); ?>

       Status: <?php echo e($entry['status']); ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
<?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views\po\exports\tracking-copy.blade.php ENDPATH**/ ?>