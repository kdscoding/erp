<?php ($title = 'Monitoring Hub'); ?>
<?php ($header = 'Monitoring Hub'); ?>
<?php ($headerSubtitle = 'Satu layar utama untuk monitoring outstanding, dengan mode PO View dan Item View.'); ?>
<?php ($monitoringMode = $monitoringMode ?? 'po'); ?>

<?php $__env->startSection('content'); ?>
    <style>
        .monitoring-tabs {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .75rem;
        }

        .monitoring-tab {
            display: block;
            padding: .9rem 1rem;
            border-radius: 16px;
            border: 1px solid rgba(111, 150, 40, .12);
            background: rgba(255, 255, 255, .94);
            color: #314216;
            text-decoration: none;
        }

        .monitoring-tab.active {
            border-color: #b9d044;
            background: linear-gradient(135deg, #fffde8, #eef7d2);
        }

        .monitoring-tab-title {
            font-size: .84rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #5e7230;
        }

        .monitoring-tab-note {
            margin-top: .3rem;
            font-size: .8rem;
            color: #728058;
        }

        @media (max-width: 767.98px) {
            .monitoring-tabs {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="page-shell">
        <section class="page-head">
            <div class="page-head-main">
                <h2 class="page-section-title">Monitoring Hub</h2>
                <p class="page-section-subtitle">`Summary PO` dan `Summary Item` sekarang dikonsolidasikan ke satu hub agar
                    user tidak bingung memilih halaman.</p>
            </div>
            <div class="page-actions">
                <a href="<?php echo e(route('monitoring.export-excel', request()->query())); ?>" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-file-excel"></i> Export Monitoring
                </a>
                <a href="<?php echo e(route('traceability.index')); ?>" class="btn btn-sm btn-light">Buka Traceability</a>
            </div>
        </section>

        <section class="ui-surface">
            <form method="GET" class="filter-grid">
                <input type="hidden" name="mode" value="<?php echo e($monitoringMode); ?>">
                <div class="span-6">
                    <label class="field-label">Supplier Code</label>
                    <select name="supplier_code" class="form-control form-control-sm">
                        <option value="">Semua Supplier</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($supplier->supplier_code); ?>" <?php if(request('supplier_code') === $supplier->supplier_code): echo 'selected'; endif; ?>><?php echo e($supplier->supplier_code); ?> - <?php echo e($supplier->supplier_name); ?>

                            </option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>
                <div class="span-2">
                    <label class="field-label">PO Dari</label>
                    <input type="date" name="date_from" value="<?php echo e($dateFrom); ?>" class="form-control form-control-sm">
                </div>
                <div class="span-2">
                    <label class="field-label">PO Sampai</label>
                    <input type="date" name="date_to" value="<?php echo e($dateTo); ?>" class="form-control form-control-sm">
                </div>
                <div class="span-1"><button class="btn btn-primary btn-sm w-100">Apply</button></div>
                <div class="span-1"><a href="<?php echo e(route('monitoring.index', ['mode' => $monitoringMode])); ?>"
                        class="btn btn-light btn-sm w-100">Reset</a></div>
            </form>
        </section>

        <section class="monitoring-tabs">
            <a href="<?php echo e(route('monitoring.index', array_filter(request()->query() + ['mode' => 'po']))); ?>"
                class="monitoring-tab <?php echo e($monitoringMode === 'po' ? 'active' : ''); ?>">
                <div class="monitoring-tab-title">PO View</div>
                <div class="monitoring-tab-note">Ringkasan outstanding per purchase order.</div>
            </a>
            <a href="<?php echo e(route('monitoring.index', array_filter(request()->query() + ['mode' => 'item']))); ?>"
                class="monitoring-tab <?php echo e($monitoringMode === 'item' ? 'active' : ''); ?>">
                <div class="monitoring-tab-title">Item View</div>
                <div class="monitoring-tab-note">Detail outstanding per item untuk follow up operasional.</div>
            </a>
        </section>

        <section class="summary-chips">
            <div class="summary-chip">
                <div class="summary-chip-label">Outstanding PO</div>
                <div class="summary-chip-value">
                    <?php echo e(number_format((float) ($summaryMetrics['outstanding_po'] ?? 0), 0, ',', '.')); ?></div>
            </div>
            <div class="summary-chip">
                <div class="summary-chip-label">Outstanding Item</div>
                <div class="summary-chip-value">
                    <?php echo e(number_format((float) ($summaryMetrics['outstanding_item'] ?? 0), 0, ',', '.')); ?></div>
            </div>
            <div class="summary-chip">
                <div class="summary-chip-label">Total Order</div>
                <div class="summary-chip-value">
                    <?php echo e(\App\Support\NumberFormatter::trim($summaryMetrics['total_order_qty'] ?? 0)); ?></div>
            </div>
            <div class="summary-chip">
                <div class="summary-chip-label">Total Pengiriman</div>
                <div class="summary-chip-value">
                    <?php echo e(\App\Support\NumberFormatter::trim($summaryMetrics['total_shipped_qty'] ?? 0)); ?></div>
            </div>
            <div class="summary-chip">
                <div class="summary-chip-label">Total Outstanding</div>
                <div class="summary-chip-value">
                    <?php echo e(\App\Support\NumberFormatter::trim($summaryMetrics['total_outstanding_qty'] ?? 0)); ?></div>
            </div>
        </section>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($monitoringMode === 'po'): ?>
            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Monitoring Summary Per Purchase Order</h3>
                        <div class="ui-surface-subtitle">Satu baris per PO untuk pembacaan summary outstanding yang cepat.
                        </div>
                    </div>
                </div>
                <div class="table-wrap table-responsive">
                    <table class="table table-hover ui-table">
                        <thead>
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
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $outstandingPoRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo e(route('po.show', $row->po_number)); ?>"
                                            class="doc-number text-decoration-none"><?php echo e($row->po_number); ?></a>
                                        <div class="doc-meta"><?php echo e($row->po_status); ?></div>
                                    </td>
                                    <td><?php echo e($row->supplier_name); ?></td>
                                    <td><?php echo e($row->po_date ? \Carbon\Carbon::parse($row->po_date)->format('d-m-Y') : '-'); ?>

                                    </td>
                                    <td><?php echo e($row->eta_date ? \Carbon\Carbon::parse($row->eta_date)->format('d-m-Y') : '-'); ?>

                                    </td>
                                    <td><?php echo e(number_format((float) $row->outstanding_item_count, 0, ',', '.')); ?></td>
                                    <td><?php echo e(\App\Support\NumberFormatter::trim($row->total_order_qty)); ?></td>
                                    <td><?php echo e(\App\Support\NumberFormatter::trim($row->total_shipped_qty)); ?></td>
                                    <td><?php echo e(\App\Support\NumberFormatter::trim($row->total_outstanding_qty)); ?></td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Tidak ada outstanding PO pada filter
                                        ini.</td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($monitoringMode === 'item'): ?>
            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Monitoring Detail Per Item</h3>
                        <div class="ui-surface-subtitle">Item-level visibility untuk follow up ETD, shipment, dan receiving.
                        </div>
                    </div>
                </div>
                <div class="table-wrap table-responsive">
                    <table class="table table-hover ui-table">
                        <thead>
                            <tr>
                                <th>PO</th>
                                <th>Supplier</th>
                                <th>Item</th>
                                <th>Status</th>
                                <th>ETD</th>
                                <th>Ordered</th>
                                <th>Received</th>
                                <th>Outstanding</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $outstandingItemRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr>
                                    <td><a href="<?php echo e(route('po.show', $row->po_number)); ?>"
                                            class="doc-number text-decoration-none"><?php echo e($row->po_number); ?></a></td>
                                    <td><?php echo e($row->supplier_name); ?></td>
                                    <td><?php echo e($row->item_code); ?> - <?php echo e($row->item_name); ?></td>
                                    <td><?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
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
<?php endif; ?></td>
                                    <td><?php echo e($row->etd_date ? \Carbon\Carbon::parse($row->etd_date)->format('d-m-Y') : '-'); ?>

                                    </td>
                                    <td><?php echo e(\App\Support\NumberFormatter::trim($row->ordered_qty)); ?></td>
                                    <td><?php echo e(\App\Support\NumberFormatter::trim($row->received_qty)); ?></td>
                                    <td><?php echo e(\App\Support\NumberFormatter::trim($row->outstanding_qty)); ?></td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Tidak ada item outstanding pada filter
                                        ini.</td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views\monitoring.blade.php ENDPATH**/ ?>