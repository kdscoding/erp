<?php ($title = 'Tracking'); ?>
<?php ($header = 'Fulfillment Tracking'); ?>

<?php $__env->startSection('content'); ?>
    <style>
        .stage-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; }
        .stage-waiting { background: #f0f0f0; color: #666; }
        .stage-confirmed { background: #fff3cd; color: #856404; }
        .stage-shipped { background: #cce5ff; color: #004085; }
        .stage-partial { background: #d1ecf1; color: #0c5460; }
        .stage-closed { background: #d4edda; color: #155724; }
        .stage-late { background: #f8d7da; color: #721c24; }
        .stage-cancelled { background: #e2e3e5; color: #383d41; }

        .detail-row { background: #fafdf5; }
        .detail-row td { padding: .85rem 1.1rem !important; }
        .detail-item { border: 1px solid #e0e6c8; border-radius: 10px; padding: .75rem .9rem; margin-bottom: .65rem; background: #fff; }
        .detail-item:last-child { margin-bottom: 0; }
        .detail-item-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: .4rem; flex-wrap: wrap; gap: .4rem; }
        .detail-item-code { font-weight: 700; color: #314216; font-size: 12px; }
        .detail-item-name { font-size: 11px; color: #7a8660; }
        .detail-item-stats { display: flex; gap: 1rem; flex-wrap: wrap; font-size: 11px; color: #52603d; }
        .detail-item-stats strong { color: #314216; }
        .detail-shipment-table { width: 100%; border-collapse: collapse; margin-top: .5rem; font-size: 11px; }
        .detail-shipment-table th { background: #f2f6cf; padding: .4rem .55rem; text-align: left; border-bottom: 1px solid var(--lemon-line); font-size: .69rem; text-transform: uppercase; letter-spacing: .08em; color: #5f7331; }
        .detail-shipment-table td { padding: .35rem .55rem; border-bottom: 1px solid var(--lemon-line); vertical-align: middle; }
        .filter-advanced { overflow: hidden; max-height: 0; opacity: 0; transition: max-height .3s ease, opacity .3s ease, margin .3s ease; margin-top: 0 !important; }
        .filter-advanced.open { max-height: 300px; opacity: 1; margin-top: .75rem !important; }
        .filter-bar { display: flex; flex-direction: row; align-items: flex-end; gap: .75rem; flex-wrap: wrap; padding: .5rem 0; }
        .filter-bar-field { display: flex; flex-direction: column; min-width: 140px; flex: 1; }
        .filter-bar-field label { font-size: 11px; font-weight: 600; color: #666; margin-bottom: 3px; }
        .filter-bar-field select, .filter-bar-field input { font-size: 12px; padding: 4px 8px; height: 32px; }
        .filter-bar-actions { display: flex; gap: .4rem; align-items: flex-end; margin-left: auto; }
        .filter-bar-actions .btn { height: 32px; font-size: 12px; }
        .filter-toggle-btn { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; border: 1px solid var(--lemon-line); background: #f8f9fa; color: #666; cursor: pointer; margin-bottom: .5rem; }
        .filter-toggle-btn:hover { background: #e9ecef; color: #333; border-color: #dee2e6; }
        .filter-toggle-btn i { font-size: 12px; }

        .shipment-progress-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; }
        .progress-none { background: #f0f0f0; color: #6c757d; border: 1px solid #dee2e6; }
        .progress-partial { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .progress-fully { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }

        .progress-bar-cell { display: flex; align-items: center; gap: 6px; justify-content: center; }
        .progress-bar-cell .progress { height: 8px; }
        .progress-bar-cell .progress-bar { transition: width .3s ease; }

        .tracking-row-progress-fully { background-color: #d4edda !important; }
        .tracking-row-progress-partial { background-color: #fff3cd !important; }
        .tracking-row-progress-none { background-color: #f0f0f0 !important; }

        .tracking-pagination-wrap { margin-top: 14px; text-align: center; }
    </style>

    <div class="page-shell">
        <section class="ui-surface">
            <div class="ui-surface-body">
                <button type="button" class="filter-toggle-btn" id="filterToggle">
                    <i class="fas fa-sliders-h"></i> <span id="filterToggleText">Tampilkan Filter</span>
                </button>
                <div id="filterSection" style="display:none;">
                    <form method="GET" class="filter-bar" id="trackingFilterForm">
                        <div class="filter-bar-field">
                            <label for="filterSupplier">Supplier</label>
                            <select id="filterSupplier" name="supplier_id" class="form-control form-control-sm">
                                <option value="">Semua Supplier</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($supplier->id); ?>" <?php echo e((int) ($filterSupplierId ?? 0) === $supplier->id ? 'selected' : ''); ?>>
                                        <?php echo e($supplier->supplier_code); ?> - <?php echo e($supplier->supplier_name); ?>

                                    </option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </div>
                        <div class="filter-bar-field">
                            <label for="filterDateFrom">Tanggal PO Dari</label>
                            <input type="date" id="filterDateFrom" name="date_from" value="<?php echo e($filterDateFrom ?? ''); ?>" class="form-control form-control-sm">
                        </div>
<div class="filter-bar-field">
                             <label for="filterDateTo">Tanggal PO Sampai</label>
                             <input type="date" id="filterDateTo" name="date_to" value="<?php echo e($filterDateTo ?? ''); ?>" class="form-control form-control-sm">
                         </div>
                         <div class="filter-bar-field">
                             <label for="filterPoStatus">PO Status</label>
                             <select id="filterPoStatus" name="po_status" class="form-control form-control-sm">
                                 <option value="all" <?php echo e($filterPoStatus === 'all' ? 'selected' : ''); ?>>Semua</option>
                                 <option value="PO Issued" <?php echo e($filterPoStatus === 'PO Issued' ? 'selected' : ''); ?>>PO Issued</option>
                                 <option value="Open" <?php echo e($filterPoStatus === 'Open' ? 'selected' : ''); ?>>Open</option>
                                 <option value="Late" <?php echo e($filterPoStatus === 'Late' ? 'selected' : ''); ?>>Late</option>
                                 <option value="Closed" <?php echo e($filterPoStatus === 'Closed' ? 'selected' : ''); ?>>Closed</option>
                                 <option value="Cancelled" <?php echo e($filterPoStatus === 'Cancelled' ? 'selected' : ''); ?>>Cancelled</option>
                             </select>
                         </div>
                        <div class="filter-bar-field">
                            <label for="filterItemStatus">Item Status</label>
                            <select id="filterItemStatus" name="item_status" class="form-control form-control-sm">
                                <option value="all" <?php echo e($filterItemStatus === 'all' ? 'selected' : ''); ?>>Semua</option>
                                <option value="Waiting" <?php echo e($filterItemStatus === 'Waiting' ? 'selected' : ''); ?>>Waiting</option>
                                <option value="Confirmed" <?php echo e($filterItemStatus === 'Confirmed' ? 'selected' : ''); ?>>Confirmed</option>
                                <option value="Late" <?php echo e($filterItemStatus === 'Late' ? 'selected' : ''); ?>>Late</option>
                                <option value="Partial" <?php echo e($filterItemStatus === 'Partial' ? 'selected' : ''); ?>>Partial</option>
                                <option value="Closed" <?php echo e($filterItemStatus === 'Closed' ? 'selected' : ''); ?>>Closed</option>
                                <option value="Force Closed" <?php echo e($filterItemStatus === 'Force Closed' ? 'selected' : ''); ?>>Force Closed</option>
                                <option value="Cancelled" <?php echo e($filterItemStatus === 'Cancelled' ? 'selected' : ''); ?>>Cancelled</option>
                            </select>
                        </div>
                        <div class="filter-bar-field">
                            <label for="filterCategory">Kategori Barang</label>
                            <select id="filterCategory" name="category_id" class="form-control form-control-sm">
                                <option value="all" <?php echo e($filterCategory === 'all' ? 'selected' : ''); ?>>Semua Kategori</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($category->id); ?>" <?php echo e((string) ($filterCategory ?? 'all') === (string) $category->id ? 'selected' : ''); ?>>
                                        <?php echo e($category->category_name); ?>

                                    </option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </div>
                         <div class="filter-bar-actions">
                            <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-search"></i> Terapkan</button>
                            <a href="<?php echo e(route('tracking.index')); ?>" class="btn btn-light btn-sm"><i class="fas fa-redo"></i> Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Unified Tracking Table</h3>
                </div>
                <div class="po-search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" id="tracking-search" class="form-control form-control-sm" placeholder="Cari PO, kode barang..." aria-label="Cari PO, kode barang">
                </div>
            </div>

            <div class="table-wrap table-responsive">
                <table class="table table-hover ui-table" id="tracking-table">
<thead>
                            <tr>
                                <th>PO Number</th>
                                <th>Item Codes</th>
                                <th>Name Barang</th>
                                <th>Kategori</th>
                                <th>Tanggal</th>
                                <th>Supplier</th>
                                <th class="text-end">Ordered</th>
                                <th class="text-end">Dikirim</th>
                                <th class="text-end">Diterima</th>
                                <th class="text-end">Outstanding</th>
                                <th class="text-center">Status PO</th>
                                <th class="text-center">Status Barang</th>
                                <th class="text-center">Shipment Progress</th>
                                <th class="text-center">Progress %</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tracking-tbody">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $itemRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr class="tracking-row tracking-row-<?php echo e($item['progress_class']); ?>" data-po-id="<?php echo e($item['po_id']); ?>" data-po-number="<?php echo e(strtolower($item['po_number'] ?? '')); ?>" data-item-code="<?php echo e(strtolower($item['item_code'] ?? '')); ?>" data-item-name="<?php echo e(strtolower($item['item_name'] ?? '')); ?>" data-status-po="<?php echo e(strtolower($item['stage'] ?? '')); ?>" data-status-barang="<?php echo e(strtolower($item['monitoring_status'] ?? $item['item_status'] ?? '')); ?>" data-supplier="<?php echo e(strtolower($item['supplier_name'] ?? '')); ?>" data-item-category="<?php echo e(strtolower($item['item_category_name'] ?? '')); ?>">
                                    <td>
                                        <a href="<?php echo e(route($item['ref_type'], $item['ref_param'])); ?>" class="doc-number text-decoration-none">
                                            <?php echo e($item['po_number']); ?>

                                        </a>
                                    </td>
                                    <td>
                                        <span class="doc-meta"><?php echo e($item['item_code']); ?></span>
                                    </td>
                                    <td>
                                        <span class="doc-meta"><?php echo e($item['item_name'] ?? '-'); ?></span>
                                    </td>
                                    <td>
                                        <span class="doc-meta" style="font-size:10px;"><?php echo e($item['item_category_name'] ?? 'Tanpa Kategori'); ?></span>
                                    </td>
                                    <td>
                                        <div class="doc-meta"><?php echo e($item['po_date'] ?? '-'); ?></div>
                                    </td>
                                    <td><?php echo e($item['supplier_name']); ?></td>
                                    <td class="text-end"><?php echo e(\App\Support\NumberFormatter::trim($item['ordered_qty'])); ?></td>
                                    <td class="text-end"><?php echo e(\App\Support\NumberFormatter::trim($item['shipped_qty'])); ?></td>
                                    <td class="text-end"><?php echo e(\App\Support\NumberFormatter::trim($item['received_qty'])); ?></td>
                                    <td class="text-end">
                                        <?php ($remaining = (float) $item['outstanding_qty']); ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($remaining > 0): ?>
                                            <span class="badge bg-danger"><?php echo e(\App\Support\NumberFormatter::trim($remaining)); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-success">0</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="stage-badge <?php echo e($item['stage_class'] ?? 'stage-waiting'); ?>">
                                            <?php echo e($item['stage']); ?>

                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php ($itemStatusClass = match($item['monitoring_status'] ?? '') {
                                            'Waiting' => 'stage-waiting',
                                            'Confirmed' => 'stage-confirmed',
                                            'Late' => 'stage-late',
                                            'Partial' => 'stage-partial',
                                            'Closed' => 'stage-closed',
                                            'Force Closed' => 'stage-cancelled',
                                            'Cancelled' => 'stage-cancelled',
                                            default => 'stage-waiting',
                                        }); ?>
                                        <span class="stage-badge <?php echo e($itemStatusClass); ?>" style="font-size:10px;"><?php echo e($item['monitoring_status'] ?? $item['item_status'] ?? '-'); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="shipment-progress-badge <?php echo e($item['progress_class'] ?? 'progress-none'); ?>" title="Shipment Progress">
                                            <i class="fas <?php echo e($item['progress_class'] === 'progress-fully' ? 'fas fa-check-circle' : ($item['progress_class'] === 'progress-partial' ? 'fas fa-truck-loading' : 'fas fa-box')); ?>"></i>
                                            <span><?php echo e($item['shipment_progress']); ?></span>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="progress-bar-cell">
                                            <div class="progress progress-sm" style="width:80px;">
                                                <div class="progress-bar <?php echo e($item['progress_class'] === 'progress-fully' ? 'bg-success' : ($item['progress_class'] === 'progress-partial' ? 'bg-warning' : 'bg-secondary')); ?>" role="progressbar" style="width: <?php echo e($item['progress_percent']); ?>%"></div>
                                            </div>
                                            <small class="text-muted"><?php echo e($item['progress_percent']); ?>%</small>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($item['shipments'])): ?>
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleDetail('detail-<?php echo e($item['item_id']); ?>')">
                                                <i class="fas fa-chevron-down" id="icon-<?php echo e($item['item_id']); ?>"></i> Detail
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($item['shipments'])): ?>
                                    <tr class="detail-row" id="detail-<?php echo e($item['item_id']); ?>" style="display:none;">
                                        <td colspan="15">
                                            <table class="detail-shipment-table">
                                                <thead>
                                                    <tr>
                                                        <th>Shipment</th>
                                                        <th>Tanggal</th>
                                                        <th>DN</th>
                                                        <th class="text-end">Shipped</th>
                                                        <th class="text-end">Received</th>
                                                        <th class="text-end">Outstanding</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $item['shipments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shipment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                        <tr>
                                                            <td><?php echo e($shipment['shipment_number']); ?></td>
                                                            <td><?php echo e($shipment['shipment_date']); ?></td>
                                                            <td><?php echo e($shipment['delivery_note_number']); ?></td>
                                                            <td class="text-end"><?php echo e(\App\Support\NumberFormatter::trim($shipment['shipped_qty'])); ?></td>
                                                            <td class="text-end"><?php echo e(\App\Support\NumberFormatter::trim($shipment['received_qty'])); ?></td>
                                                            <td class="text-end">
                                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($shipment['remaining_qty'] > 0): ?>
                                                                    <span class="badge bg-danger" style="font-size:9px;"><?php echo e(\App\Support\NumberFormatter::trim($shipment['remaining_qty'])); ?></span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-success" style="font-size:9px;">0</span>
                                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr>
                                    <td colspan="15" class="text-center text-muted">Belum ada data PO pada filter ini.</td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
            </div>
            <div id="tracking-pagination" class="tracking-pagination-wrap">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($paginator)): ?>
                    <?php echo e($paginator->links()); ?>

                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>
    </div>

    <script>
        function toggleDetail(id) {
            const el = document.getElementById(id);
            const icon = document.getElementById('icon-' + id.replace('detail-', ''));
            if (!el) return;
            const isHidden = el.style.display === 'none';
            el.style.display = isHidden ? '' : 'none';
            if (icon) {
                icon.className = isHidden ? 'fas fa-chevron-up' : 'fas fa-chevron-down';
            }
        }

        let trackingDebounceTimer = null;

        function fetchTrackingData(params) {
            const tbody = document.getElementById('tracking-tbody');
            const paginationWrap = document.getElementById('tracking-pagination');
            if (!tbody || !paginationWrap) return;

            tbody.innerHTML = '<tr><td colspan="15" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin"></i> Memuat data...</td></tr>';

            const queryString = new URLSearchParams(params).toString();
            const url = '<?php echo e(route('tracking.data')); ?>?' + queryString;

            fetch(url)
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    tbody.innerHTML = data.rows;
                    paginationWrap.innerHTML = data.pagination;
                    bindPaginationEvents();
                })
                .catch(function () {
                    tbody.innerHTML = '<tr><td colspan="15" class="text-center text-muted py-4">Gagal memuat data. Silakan coba lagi.</td></tr>';
                });
        }

        function bindPaginationEvents() {
            const paginationContainer = document.getElementById('tracking-pagination');
            if (!paginationContainer) return;
            paginationContainer.querySelectorAll('a.page-link').forEach(function (link) {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    var href = this.getAttribute('href');
                    var urlParams = new URLSearchParams(href.split('?')[1] || '');
                    fetchTrackingData(urlParams);
                });
            });
        }

        function getFilterParams() {
            const form = document.getElementById('trackingFilterForm');
            const params = new URLSearchParams();
            if (form) {
                const formData = new FormData(form);
                formData.forEach(function (value, key) {
                    if (value !== '') {
                        params.set(key, value);
                    }
                });
            }
            const searchInput = document.getElementById('tracking-search');
            if (searchInput && searchInput.value.trim() !== '') {
                params.set('search', searchInput.value.trim());
            }
            return params;
        }

        document.addEventListener('DOMContentLoaded', function () {
            const toggle = document.getElementById('filterToggle');
            const filterSection = document.getElementById('filterSection');
            const toggleText = document.getElementById('filterToggleText');

            if (toggle && filterSection) {
                toggle.addEventListener('click', function () {
                    const isHidden = filterSection.style.display === 'none';
                    filterSection.style.display = isHidden ? 'block' : 'none';
                    if (toggleText) {
                        toggleText.textContent = isHidden ? 'Sembunyikan Filter' : 'Tampilkan Filter';
                    }
                });
            }

            const filterForm = document.getElementById('trackingFilterForm');
            if (filterForm) {
                filterForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    fetchTrackingData(getFilterParams());
                });
            }

            const searchInput = document.getElementById('tracking-search');
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    clearTimeout(trackingDebounceTimer);
                    trackingDebounceTimer = setTimeout(function () {
                        fetchTrackingData(getFilterParams());
                    }, 300);
                });
            }

            bindPaginationEvents();
        });
    </script>
    <?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views/tracking.blade.php ENDPATH**/ ?>