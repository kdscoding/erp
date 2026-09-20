<?php ($title = 'Pending Receiving'); ?>
<?php ($header = 'Pending Receiving'); ?>
<?php ($headerSubtitle = 'Daftar shipment yang siap diterima gudang. Pilih dokumen untuk memulai proses receiving.'); ?>

<?php $__env->startSection('content'); ?>
    <section class="ui-surface mb-3">
        <div class="ui-surface-head">
            <div>
                <h3 class="ui-surface-title">Filter Shipment</h3>
                <div class="ui-surface-subtitle">Cari dokumen supplier yang siap diterima gudang.</div>
            </div>
        </div>
        <div class="ui-surface-body">
            <form method="GET" action="<?php echo e(route('receiving.pending')); ?>" class="filter-grid">
                <div class="span-3">
                    <label class="field-label">Supplier</label>
                    <select name="supplier_id" class="form-control form-control-sm">
                        <option value="">Semua Supplier</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($supplier->id); ?>" <?php if(request('supplier_id') == $supplier->id): echo 'selected'; endif; ?>>
                                <?php echo e($supplier->supplier_name); ?>

                            </option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>

                <div class="span-3">
                    <label class="field-label">Delivery Note</label>
                    <input type="text" name="document_number" value="<?php echo e(request('document_number')); ?>"
                        class="form-control form-control-sm" placeholder="No surat jalan supplier">
                </div>

                <div class="span-4">
                    <label class="field-label">Cari Shipment / PO / Invoice</label>
                    <input type="text" name="keyword" value="<?php echo e(request('keyword')); ?>"
                        class="form-control form-control-sm" placeholder="Shipment, PO, invoice, supplier">
                </div>

                <div class="span-1">
                    <button class="btn btn-primary btn-sm w-100">Apply</button>
                </div>

                <div class="span-1">
                    <a href="<?php echo e(route('receiving.pending')); ?>" class="btn btn-light btn-sm w-100">Reset</a>
                </div>
            </form>
        </div>
    </section>

    <section class="summary-chips mb-3">
        <div class="summary-chip">
            <div class="summary-chip-label">Total Dokumen</div>
            <div class="summary-chip-value"><?php echo e($shipmentDocuments->count()); ?></div>
        </div>
        <div class="summary-chip">
            <div class="summary-chip-label">Siap Diproses</div>
            <div class="summary-chip-value"><?php echo e($shipmentDocuments->whereNotIn('status', ['Closed', 'Cancelled'])->count()); ?></div>
        </div>
        <div class="summary-chip">
            <div class="summary-chip-label">Total Outstanding Qty</div>
            <div class="summary-chip-value"><?php echo e(\App\Support\NumberFormatter::trim($shipmentDocuments->sum('outstanding_qty'))); ?></div>
        </div>
    </section>

    <section class="ui-surface">
        <div class="ui-surface-head">
            <div>
                <h3 class="ui-surface-title">Shipment Worklist</h3>
                <div class="ui-surface-subtitle">Klik "Mulai Receiving" untuk memproses qty fisik yang datang di gudang.</div>
            </div>
        </div>
        <div class="table-wrap table-responsive">
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
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $shipmentDocuments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
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
                    <p>Belum ada dokumen shipment yang siap diterima.</p>
                    <p class="small">Pastikan shipment berstatus <strong>Shipped</strong> atau <strong>Partial Received</strong> dan masih memiliki sisa qty.</p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views\receiving\pending.blade.php ENDPATH**/ ?>