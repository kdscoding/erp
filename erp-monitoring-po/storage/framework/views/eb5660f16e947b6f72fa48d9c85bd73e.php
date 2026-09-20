<?php ($title = 'Receiving History'); ?>
<?php ($header = 'Receiving History'); ?>
<?php ($headerSubtitle = 'Riwayat goods receipt yang sudah diposting atau dibatalkan beserta referensi shipment dan PO.'); ?>

<?php $__env->startSection('content'); ?>
    <?php ($rowsCollection = method_exists($rows, 'getCollection') ? $rows->getCollection() : collect($rows)); ?>
    <?php ($historyCount = $rowsCollection->count()); ?>
    <?php ($cancelledCount = $rowsCollection->where('status', \App\Support\DocumentTermCodes::GR_CANCELLED)->count()); ?>

    <section class="summary-chips mb-3">
        <div class="summary-chip">
            <div class="summary-chip-label">Total Dokumen</div>
            <div class="summary-chip-value"><?php echo e($historyCount); ?></div>
        </div>
        <div class="summary-chip">
            <div class="summary-chip-label">Posted</div>
            <div class="summary-chip-value"><?php echo e($historyCount - $cancelledCount); ?></div>
        </div>
        <div class="summary-chip">
            <div class="summary-chip-label">Cancelled</div>
            <div class="summary-chip-value"><?php echo e($cancelledCount); ?></div>
        </div>
    </section>

    <section class="ui-surface">
        <div class="ui-surface-head">
            <div>
                <h3 class="ui-surface-title">Daftar Goods Receipt</h3>
                <div class="ui-surface-subtitle">Gunakan detail untuk melihat item yang diterima pada tiap transaksi GR.</div>
            </div>
        </div>

        <div class="table-wrap table-responsive">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rows->isNotEmpty()): ?>
                <table class="table table-hover ui-table">
                    <thead>
                        <tr>
                            <th>No GR</th>
                            <th>Tanggal</th>
                            <th>PO</th>
                            <th>Supplier</th>
                            <th>Shipment</th>
                            <th>Delivery Note</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td><?php echo e($row->gr_number); ?></td>
                                <td><?php echo e(\Carbon\Carbon::parse($row->receipt_date)->format('d-m-Y')); ?></td>
                                <td><?php echo e($row->po_number); ?></td>
                                <td><?php echo e($row->supplier_name); ?></td>
                                <td><?php echo e($row->shipment_number ?: '-'); ?></td>
                                <td><?php echo e($row->delivery_note_number ?: ($row->document_number ?: '-')); ?></td>
                                <td><?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $row->status,'scope' => 'gr']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($row->status),'scope' => 'gr']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?></td>
                                <td class="text-end">
                                    <a href="<?php echo e(route('receiving.show', $row->id)); ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">Belum ada histori goods receipt.</td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
                <div class="px-3 pb-3"><?php echo e($rows->links()); ?></div>
            <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-history fa-2x mb-2 opacity-25"></i>
                    <p>Belum ada histori goods receipt.</p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views/receiving/history.blade.php ENDPATH**/ ?>