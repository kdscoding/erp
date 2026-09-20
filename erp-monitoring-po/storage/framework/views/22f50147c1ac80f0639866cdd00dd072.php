<?php ($title='Traceability'); ?>
<?php ($header='Traceability'); ?>
<?php ($headerSubtitle='Workspace investigasi untuk melihat jejak PO, shipment, dan receiving tanpa bercampur dengan halaman monitoring.'); ?>
<?php ($suppliers = $suppliers ?? collect()); ?>
<?php ($itemStatuses = $itemStatuses ?? []); ?>
<?php ($rowCollection = collect($rows ?? [])); ?>
<?php ($groupedPo = $rowCollection->groupBy('po_number')); ?>
<?php ($selectedPoNumber = request('selected_po') ?: ($groupedPo->keys()->first() ?? null)); ?>
<?php ($selectedPoRows = $selectedPoNumber ? collect($groupedPo->get($selectedPoNumber, [])) : collect()); ?>
<?php ($selectedPoSummary = $selectedPoRows->first()); ?>

<?php $__env->startSection('content'); ?>
    <style>
        .traceability-layout { display:grid; grid-template-columns: 360px minmax(0,1fr); gap:1rem; align-items:start; }
        .traceability-list { display:grid; gap:.75rem; }
        .traceability-po-card {
            display:block;
            padding:.9rem 1rem;
            border-radius:16px;
            border:1px solid rgba(111,150,40,.12);
            background:rgba(255,255,255,.96);
            color:#314216;
            text-decoration:none;
        }
        .traceability-po-card.active { border-color:#bfd730; background:linear-gradient(135deg,#fffde8,#eef7d2); }
        .traceability-po-title { font-size:.92rem; font-weight:800; color:#314216; }
        .traceability-po-meta { font-size:.8rem; color:#728058; margin-top:.2rem; }
        .timeline-stack { display:grid; gap:.75rem; }
        .timeline-card {
            border:1px solid #e4eabc;
            border-radius:16px;
            background:linear-gradient(135deg,#fffef7,#f6f9e6);
            padding:1rem;
        }
        .timeline-head { display:flex; justify-content:space-between; gap:.75rem; align-items:flex-start; flex-wrap:wrap; }
        .timeline-title { font-size:.9rem; font-weight:800; color:#314216; }
        .timeline-meta { font-size:.8rem; color:#728058; }
        .timeline-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.75rem; margin-top:.75rem; }
        .timeline-box {
            border:1px solid #e7eadf;
            border-radius:14px;
            background:#fafcf5;
            padding:.8rem .9rem;
        }
        .timeline-box-label { font-size:.72rem; text-transform:uppercase; letter-spacing:.08em; color:#7d866f; margin-bottom:.25rem; }
        .timeline-box-value { font-size:.92rem; font-weight:700; color:#2f3c1b; }
        @media (max-width: 1199.98px) {
            .traceability-layout { grid-template-columns:1fr; }
        }
        @media (max-width: 767.98px) {
            .timeline-grid { grid-template-columns:1fr; }
        }
    </style>

    <div class="page-shell">
        <section class="page-head">
            <div class="page-head-main">
                <h2 class="page-section-title">Traceability Workspace</h2>
                <p class="page-section-subtitle">Cari PO lalu fokus pada satu konteks investigasi, bukan membaca seluruh hasil dalam satu tabel panjang.</p>
            </div>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Filter Traceability</h3>
                    <div class="ui-surface-subtitle">Gunakan supplier, nomor PO, item, dan status item untuk mempersempit hasil investigasi.</div>
                </div>
            </div>
            <form method="GET" class="filter-grid">
                <div class="span-3">
                    <label class="field-label">Supplier Code</label>
                    <select name="supplier_code" class="form-control form-control-sm">
                        <option value="">Semua Supplier</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($supplier->supplier_code); ?>" <?php if(request('supplier_code') === $supplier->supplier_code): echo 'selected'; endif; ?>><?php echo e($supplier->supplier_code); ?> - <?php echo e($supplier->supplier_name); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>
                <div class="span-3">
                    <label class="field-label">Nomor PO</label>
                    <input name="po_number" class="form-control form-control-sm" placeholder="Cari nomor PO" value="<?php echo e(request('po_number')); ?>">
                </div>
                <div class="span-3">
                    <label class="field-label">Item</label>
                    <input name="item_keyword" class="form-control form-control-sm" placeholder="Kode atau nama item" value="<?php echo e(request('item_keyword')); ?>">
                </div>
                <div class="span-2">
                    <label class="field-label">Status Item</label>
                    <select name="item_status" class="form-control form-control-sm">
                        <option value="">Semua Status</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $itemStatuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($status); ?>" <?php if(request('item_status') === $status): echo 'selected'; endif; ?>><?php echo e(\App\Support\TermCatalog::label('po_item_status', $status, $status)); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>
                <div class="span-1"><button class="btn btn-primary btn-sm w-100">Cari</button></div>
                <div class="span-1"><a href="<?php echo e(route('traceability.index')); ?>" class="btn btn-light btn-sm w-100">Reset</a></div>
            </form>
        </section>

        <section class="traceability-layout">
            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Daftar PO</h3>
                        <div class="ui-surface-subtitle">Pilih satu PO untuk membaca timeline dan rincian item di panel kanan.</div>
                    </div>
                </div>
                <div class="ui-surface-body">
                    <div class="traceability-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $groupedPo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $poNumber => $poRows): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php ($poHead = collect($poRows)->first()); ?>
                            <a
                                href="<?php echo e(route('traceability.index', array_filter(request()->query() + ['selected_po' => $poNumber]))); ?>"
                                class="traceability-po-card <?php echo e($selectedPoNumber === $poNumber ? 'active' : ''); ?>">
                                <div class="traceability-po-title"><?php echo e($poNumber); ?></div>
                                <div class="traceability-po-meta"><?php echo e($poHead->supplier_name ?? '-'); ?></div>
                                <div class="traceability-po-meta">
                                    <?php echo e(collect($poRows)->count()); ?> item | Shipment <?php echo e(collect($poRows)->sum('shipment_count')); ?>x | Receipt <?php echo e(collect($poRows)->sum('receipt_count')); ?>x
                                </div>
                            </a>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="text-muted">Belum ada data traceability pada filter ini.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </section>

            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Timeline Detail</h3>
                        <div class="ui-surface-subtitle">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedPoSummary): ?>
                                Fokus investigasi untuk <?php echo e($selectedPoSummary->po_number); ?>.
                            <?php else: ?>
                                Pilih PO dari panel kiri untuk melihat detail.
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="ui-surface-body">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedPoSummary): ?>
                        <div class="info-grid mb-3">
                            <div class="info-box"><div class="info-label">PO</div><div class="info-value"><?php echo e($selectedPoSummary->po_number); ?></div></div>
                            <div class="info-box"><div class="info-label">Supplier</div><div class="info-value"><?php echo e($selectedPoSummary->supplier_name); ?></div></div>
                            <div class="info-box"><div class="info-label">Tanggal PO</div><div class="info-value"><?php echo e(\Carbon\Carbon::parse($selectedPoSummary->po_date)->format('d-m-Y')); ?></div></div>
                        </div>

                        <div class="timeline-stack">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $selectedPoRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <article class="timeline-card">
                                    <div class="timeline-head">
                                        <div>
                                            <div class="timeline-title"><?php echo e($row->item_code); ?> - <?php echo e($row->item_name); ?></div>
                                            <div class="timeline-meta">Status <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $row->item_status,'scope' => 'item']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($row->item_status),'scope' => 'item']); ?>
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
<?php endif; ?> | Ordered <?php echo e(\App\Support\NumberFormatter::trim($row->ordered_qty)); ?> | Received <?php echo e(\App\Support\NumberFormatter::trim($row->received_qty)); ?></div>
                                        </div>
                                        <a href="<?php echo e(route('po.show', $row->po_number)); ?>" class="btn btn-sm btn-light">Buka Detail PO</a>
                                    </div>

                                    <div class="timeline-grid">
                                        <div class="timeline-box">
                                            <div class="timeline-box-label">PO Created</div>
                                            <div class="timeline-box-value"><?php echo e(\Carbon\Carbon::parse($row->po_date)->format('d-m-Y')); ?></div>
                                        </div>
                                        <div class="timeline-box">
                                            <div class="timeline-box-label">ETD</div>
                                            <div class="timeline-box-value"><?php echo e($row->etd_date ? \Carbon\Carbon::parse($row->etd_date)->format('d-m-Y') : '-'); ?></div>
                                        </div>
                                        <div class="timeline-box">
                                            <div class="timeline-box-label">First Shipment</div>
                                            <div class="timeline-box-value"><?php echo e($row->first_shipment_date ? \Carbon\Carbon::parse($row->first_shipment_date)->format('d-m-Y') : '-'); ?></div>
                                            <div class="timeline-meta">Shipment <?php echo e($row->shipment_count); ?>x</div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row->shipment_numbers): ?>
                                                <div class="timeline-meta">No Shipment: <?php echo e($row->shipment_numbers); ?></div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row->delivery_note_numbers): ?>
                                                <div class="timeline-meta">Delivery Note: <?php echo e($row->delivery_note_numbers); ?></div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <div class="timeline-box">
                                            <div class="timeline-box-label">Last Receipt</div>
                                            <div class="timeline-box-value"><?php echo e($row->last_receipt_date ? \Carbon\Carbon::parse($row->last_receipt_date)->format('d-m-Y') : '-'); ?></div>
                                            <div class="timeline-meta">Parsial: <?php echo e($row->receipt_count); ?>x</div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row->cancel_reason): ?>
                                                <div class="text-danger mt-1">Cancel Reason: <?php echo e($row->cancel_reason); ?></div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </div>
                                </article>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-muted">Belum ada PO yang dipilih untuk investigasi.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>
        </section>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views/traceability/index.blade.php ENDPATH**/ ?>