<?php ($title = 'Receiving Dashboard'); ?>
<?php ($header = 'Receiving Dashboard'); ?>
<?php ($headerSubtitle = 'Overview dokumen shipment yang siap diterima dan riwayat goods receipt terbaru.'); ?>

<?php $__env->startSection('content'); ?>
    <section class="summary-chips">
        <div class="summary-chip">
            <div class="summary-chip-label">Dokumen Shipment</div>
            <div class="summary-chip-value"><?php echo e($documentCount); ?></div>
        </div>
        <div class="summary-chip">
            <div class="summary-chip-label">Siap Diproses</div>
            <div class="summary-chip-value"><?php echo e($readyCount); ?></div>
        </div>
        <div class="summary-chip">
            <div class="summary-chip-label">Total Outstanding Qty</div>
            <div class="summary-chip-value"><?php echo e(\App\Support\NumberFormatter::trim($outstandingQty)); ?></div>
        </div>
        <div class="summary-chip">
            <div class="summary-chip-label">Total GR Posted</div>
            <div class="summary-chip-value"><?php echo e($recentHistoryCount - $cancelledCount); ?></div>
        </div>
        <div class="summary-chip">
            <div class="summary-chip-label">GR Cancelled</div>
            <div class="summary-chip-value"><?php echo e($cancelledCount); ?></div>
        </div>
    </section>

    <div class="row g-3">
        <div class="col-lg-8">
            <section class="ui-surface h-100 d-flex flex-column">
                <div class="ui-surface-head">
                    <div class="d-flex align-items-center gap-2">
                        <h3 class="ui-surface-title mb-0">Shipment Menunggu Receiving</h3>
                        <span class="badge bg-primary"><?php echo e($documentCount); ?></span>
                    </div>
                    <a href="<?php echo e(route('receiving.pending')); ?>" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="ui-surface-body table-responsive">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($shipmentDocuments->isNotEmpty()): ?>
                        <table class="table table-hover ui-table">
                            <thead>
                                <tr>
                                    <th>Shipment</th>
                                    <th>Supplier</th>
                                    <th>Delivery Note</th>
                                    <th>Invoice</th>
                                    <th>Tanggal</th>
                                    <th>PO</th>
                                    <th>Line</th>
                                    <th>Sisa Kiriman</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $shipmentDocuments->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <tr>
                                        <td>
                                            <div class="doc-number"><?php echo e($document->shipment_number); ?></div>
                                            <div class="doc-meta"><?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $document->status,'scope' => 'shipment']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($document->status),'scope' => 'shipment']); ?>
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
<?php endif; ?></div>
                                        </td>
                                        <td><?php echo e($document->supplier_name); ?></td>
                                        <td><?php echo e($document->delivery_note_number ?: '-'); ?></td>
                                        <td><?php echo e($document->invoice_number ?: '-'); ?></td>
                                        <td><?php echo e(\Carbon\Carbon::parse($document->shipment_date)->format('d-m-Y')); ?></td>
                                        <td><?php echo e($document->po_count); ?></td>
                                        <td><?php echo e($document->line_count); ?></td>
                                        <td><?php echo e(\App\Support\NumberFormatter::trim($document->outstanding_qty)); ?></td>
                                        <td class="text-end">
                                            <a href="<?php echo e(route('receiving.create', ['shipment' => $document->id, 'supplier_id' => request('supplier_id'), 'document_number' => request('document_number'), 'keyword' => request('keyword')])); ?>"
                                                class="btn btn-sm btn-primary">Mulai Receiving</a>
                                        </td>
                                    </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-box-open fa-2x mb-2 opacity-25"></i>
                            <p>Tidak ada shipment yang siap diterima.</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>
        </div>

        <div class="col-lg-4">
            <section class="ui-surface h-100 d-flex flex-column">
                <div class="ui-surface-head">
                    <h3 class="ui-surface-title mb-0">Quick Actions</h3>
                </div>
                <div class="ui-surface-body d-flex flex-column gap-2">
                    <a href="<?php echo e(route('receiving.pending')); ?>" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i> Mulai Receiving Baru
                    </a>
                    <a href="<?php echo e(route('receiving.history')); ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-history me-2"></i> Riwayat Goods Receipt
                    </a>
                    <a href="<?php echo e(route('shipments.index')); ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-ship me-2"></i> Shipment Worklist
                    </a>
                    <a href="<?php echo e(route('po.index')); ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-file-alt me-2"></i> Purchase Orders
                    </a>
                </div>
            </section>
        </div>
    </div>

    <div class="row g-3 mt-2">
        <div class="col-12">
            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Riwayat Goods Receipt Terbaru</h3>
                        <div class="ui-surface-subtitle">10 dokumen GR terakhir yang diposting atau dibatalkan.</div>
                    </div>
                    <a href="<?php echo e(route('receiving.history')); ?>" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="table-wrap table-responsive">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($recentRows->isNotEmpty()): ?>
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
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $recentRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
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
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </tbody>
                        </table>
                        <div class="px-3 pb-3"><?php echo e($recentRows->links()); ?></div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-history fa-2x mb-2 opacity-25"></i>
                            <p>Belum ada histori goods receipt.</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views\receiving\index.blade.php ENDPATH**/ ?>