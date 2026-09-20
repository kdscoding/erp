<?php ($title = 'Receipt Detail'); ?>
<?php ($header = 'Receipt Detail'); ?>
<?php ($headerSubtitle = 'Detail goods receipt, referensi shipment, dan line penerimaan barang.'); ?>

<?php $__env->startSection('content'); ?>
    <div class="page-shell">
        <section class="page-head">
            <div class="page-head-main">
                <h2 class="page-section-title"><?php echo e($receipt->gr_number); ?></h2>
                <p class="page-section-subtitle">Dokumen goods receipt lengkap dengan referensi shipment, invoice, dan item yang diterima.</p>
            </div>

            <div class="page-actions">
                <a href="<?php echo e(route('receiving.history')); ?>" class="btn btn-sm btn-light">Kembali ke Riwayat GR</a>
            </div>
        </section>

        <section class="info-grid">
            <div class="info-box"><div class="info-label">No GR</div><div class="info-value"><?php echo e($receipt->gr_number); ?></div></div>
            <div class="info-box"><div class="info-label">Tanggal Terima</div><div class="info-value"><?php echo e(\Carbon\Carbon::parse($receipt->receipt_date)->format('d-m-Y')); ?></div></div>
            <div class="info-box"><div class="info-label">Supplier</div><div class="info-value"><?php echo e($receipt->supplier_name); ?></div></div>
            <div class="info-box"><div class="info-label">Status</div><div class="info-value"><?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $receipt->status,'scope' => 'gr']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($receipt->status),'scope' => 'gr']); ?>
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
<?php endif; ?></div></div>
            <div class="info-box"><div class="info-label">Shipment</div><div class="info-value"><?php echo e($receipt->shipment_number ?: '-'); ?></div></div>
            <div class="info-box"><div class="info-label">Delivery Note</div><div class="info-value"><?php echo e($receipt->delivery_note_number ?: '-'); ?></div></div>
            <div class="info-box"><div class="info-label">No Invoice</div><div class="info-value"><?php echo e($receipt->invoice_number ?: '-'); ?></div></div>
            <div class="info-box"><div class="info-label">Tanggal Invoice</div><div class="info-value"><?php echo e($receipt->invoice_date ? \Carbon\Carbon::parse($receipt->invoice_date)->format('d-m-Y') : '-'); ?></div></div>
            <div class="info-box"><div class="info-label">Currency</div><div class="info-value"><?php echo e($receipt->invoice_currency ?: '-'); ?></div></div>
            <div class="info-box"><div class="info-label">PO</div><div class="info-value"><?php echo e($receipt->po_number); ?></div></div>
            <div class="info-box"><div class="info-label">Warehouse</div><div class="info-value"><?php echo e($receipt->warehouse_name ?: '-'); ?></div></div>
            <div class="info-box"><div class="info-label">Penerima</div><div class="info-value"><?php echo e($receipt->receiver_name ?: '-'); ?></div></div>
            <div class="info-box"><div class="info-label">No Dokumen</div><div class="info-value"><?php echo e($receipt->document_number ?: '-'); ?></div></div>
            <div class="info-box"><div class="info-label">Catatan</div><div class="info-value"><?php echo e($receipt->remark ?: '-'); ?></div></div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($receipt->cancel_reason): ?>
                <div class="info-box"><div class="info-label">Alasan Batal</div><div class="info-value text-danger"><?php echo e($receipt->cancel_reason); ?></div></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </section>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($receipt->status === \App\Support\DocumentTermCodes::GR_POSTED): ?>
            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Batalkan Goods Receipt</h3>
                        <div class="ui-surface-subtitle">Gunakan hanya jika posting GR salah. Sistem akan mengembalikan qty received ke shipment dan PO terkait.</div>
                    </div>
                </div>
                <div class="ui-surface-body">
                    <form method="POST" action="<?php echo e(route('receiving.cancel', $receipt->id)); ?>">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PATCH'); ?>
                        <div class="mb-2">
                            <label class="field-label">Alasan Pembatalan</label>
                            <textarea name="cancel_reason" class="form-control form-control-sm" rows="3" required></textarea>
                        </div>
                        <button class="btn btn-danger btn-sm" onclick="return confirm('Batalkan GR ini dan kembalikan qty receiving?')">Batalkan GR</button>
                    </form>
                </div>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Detail Item Goods Receipt</h3>
                    <div class="ui-surface-subtitle">Qty diterima, accepted, variance, dan outstanding PO ditampilkan dalam satu tabel detail.</div>
                </div>
            </div>
            <div class="table-wrap table-responsive">
                <table class="table table-hover ui-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Harga PO</th>
                            <th>Harga Invoice</th>
                            <th>Total Invoice</th>
                            <th>Qty Shipment</th>
                            <th>Qty Diterima</th>
                            <th>Accepted</th>
                            <th>Variance</th>
                            <th>Total Received PO</th>
                            <th>Outstanding PO</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td><div class="doc-number"><?php echo e($item->item_code); ?></div><div class="doc-meta"><?php echo e($item->item_name); ?></div></td>
                                <td><?php echo e($item->po_unit_price !== null ? \App\Support\NumberFormatter::trim($item->po_unit_price) : '-'); ?></td>
                                <td><?php echo e($item->invoice_unit_price !== null ? \App\Support\NumberFormatter::trim($item->invoice_unit_price) : '-'); ?></td>
                                <td><?php echo e($item->invoice_line_total !== null ? \App\Support\NumberFormatter::trim($item->invoice_line_total) : '-'); ?></td>
                                <td><?php echo e(\App\Support\NumberFormatter::trim($item->shipped_qty ?? 0)); ?> <?php echo e($item->unit_name); ?></td>
                                <td><?php echo e(\App\Support\NumberFormatter::trim($item->received_qty)); ?> <?php echo e($item->unit_name); ?></td>
                                <td><?php echo e(\App\Support\NumberFormatter::trim($item->accepted_qty)); ?> <?php echo e($item->unit_name); ?></td>
                                <td><?php echo e(\App\Support\NumberFormatter::trim($item->qty_variance)); ?></td>
                                <td><?php echo e(\App\Support\NumberFormatter::trim($item->total_po_received_qty)); ?> <?php echo e($item->unit_name); ?></td>
                                <td><?php echo e(\App\Support\NumberFormatter::trim($item->outstanding_qty)); ?> <?php echo e($item->unit_name); ?></td>
                                <td><?php echo e($item->remark ?: '-'); ?></td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views/receiving/show.blade.php ENDPATH**/ ?>