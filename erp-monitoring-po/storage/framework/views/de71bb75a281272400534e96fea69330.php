<?php ($title = 'Receiving Entry - ' . $selectedShipment->shipment_number); ?>
<?php ($header = 'Receiving Entry'); ?>
<?php ($headerSubtitle = 'Input qty fisik untuk shipment: ' . $selectedShipment->shipment_number); ?>

<?php $__env->startSection('content'); ?>
    <div class="row g-3 mb-3">
        <div class="col-12 col-md-6">
            <section class="ui-surface h-100">
                <div class="ui-surface-head">
                    <h3 class="ui-surface-title mb-0">Informasi Shipment</h3>
                </div>
                <div class="ui-surface-body">
                    <div class="info-grid">
                        <div class="info-box">
                            <div class="info-label">Shipment</div>
                            <div class="info-value"><?php echo e($selectedShipment->shipment_number); ?></div>
                        </div>
                        <div class="info-box">
                            <div class="info-label">Delivery Note</div>
                            <div class="info-value"><?php echo e($selectedShipment->delivery_note_number ?: '-'); ?></div>
                        </div>
                        <div class="info-box">
                            <div class="info-label">Invoice</div>
                            <div class="info-value"><?php echo e($selectedShipment->invoice_number ?: '-'); ?></div>
                        </div>
                        <div class="info-box">
                            <div class="info-label">Supplier</div>
                            <div class="info-value"><?php echo e($selectedShipment->supplier_name); ?></div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <div class="col-12 col-md-6">
            <section class="ui-surface h-100">
                <div class="ui-surface-head">
                    <h3 class="ui-surface-title mb-0">Form Receiving</h3>
                    <div class="ui-surface-subtitle">Isi qty yang benar-benar datang di gudang.</div>
                </div>
                <div class="ui-surface-body">
                    <form method="POST" action="<?php echo e(route('receiving.store')); ?>" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="shipment_id" value="<?php echo e($selectedShipment->id); ?>">
                        <input type="hidden" name="supplier_id" value="<?php echo e(request('supplier_id')); ?>">
                        <input type="hidden" name="search_document_number" value="<?php echo e(request('document_number')); ?>">
                        <input type="hidden" name="keyword" value="<?php echo e(request('keyword')); ?>">

                        <div class="filter-grid px-0 pt-0 pb-3">
                            <div class="span-6">
                                <label class="field-label">Tanggal Terima</label>
                                <input type="date" name="receipt_date" class="form-control form-control-sm"
                                    value="<?php echo e(old('receipt_date', now()->format('Y-m-d'))); ?>" required>
                            </div>

                            <div class="span-6">
                                <label class="field-label">No Dokumen Receiving</label>
                                <input type="text" name="document_number" class="form-control form-control-sm"
                                    value="<?php echo e(old('document_number', $selectedShipment->delivery_note_number)); ?>" required>
                            </div>
                        </div>

                        <div class="filter-grid px-0 pt-0 pb-3">
                            <div class="span-6">
                                <label class="field-label">Lampiran</label>
                                <input type="file" name="attachment" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.pdf">
                            </div>

                            <div class="span-6">
                                <label class="field-label">Catatan</label>
                                <input type="text" name="note" class="form-control form-control-sm" value="<?php echo e(old('note')); ?>" placeholder="Opsional">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-bordered ui-table">
                                <thead>
                                    <tr>
                                        <th>PO</th>
                                        <th>Item</th>
                                        <th>Harga PO</th>
                                        <th>Harga Invoice</th>
                                        <th>Total Invoice</th>
                                        <th>Qty Dikirim</th>
                                        <th>Sudah Diterima</th>
                                        <th>Sisa Bisa Diterima</th>
                                        <th style="min-width: 150px;">Qty Diterima Sekarang</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $shipmentItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <tr>
                                            <td><?php echo e($item->po_number); ?></td>
                                            <td>
                                                <div class="doc-number"><?php echo e($item->item_code); ?></div>
                                                <div class="doc-meta"><?php echo e($item->item_name); ?></div>
                                            </td>
                                            <td><?php echo e($item->unit_price !== null ? \App\Support\NumberFormatter::trim($item->unit_price) : '-'); ?></td>
                                            <td><?php echo e($item->invoice_unit_price !== null ? \App\Support\NumberFormatter::trim($item->invoice_unit_price) : '-'); ?></td>
                                            <td><?php echo e($item->invoice_line_total !== null ? \App\Support\NumberFormatter::trim($item->invoice_line_total) : '-'); ?></td>
                                            <td><?php echo e(\App\Support\NumberFormatter::trim($item->shipped_qty)); ?></td>
                                            <td><?php echo e(\App\Support\NumberFormatter::trim($item->shipment_received_qty)); ?></td>
                                            <td>
                                                <span class="badge bg-warning text-dark">
                                                    <?php echo e(\App\Support\NumberFormatter::trim($item->shipment_outstanding_qty)); ?>

                                                </span>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0" max="<?php echo e($item->shipment_outstanding_qty); ?>"
                                                    name="received_qty[<?php echo e($item->shipment_item_id); ?>]"
                                                    value="<?php echo e(old('received_qty.' . $item->shipment_item_id)); ?>"
                                                    class="form-control form-control-sm"
                                                    placeholder="Isi jika datang">
                                            </td>
                                        </tr>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="<?php echo e(route('receiving.pending', ['supplier_id' => request('supplier_id'), 'document_number' => request('document_number'), 'keyword' => request('keyword')])); ?>"
                                class="btn btn-outline-secondary btn-sm">Kembali</a>
                            <button type="submit" class="btn btn-success btn-sm">Posting Receiving</button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views\receiving\create.blade.php ENDPATH**/ ?>