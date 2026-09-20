<?php
    $title = 'Detail PO';
    $header = 'Detail Purchase Order';
    $unit = $itemUnit ?? ($items->isNotEmpty() ? ($items->first()->unit_name ?? '') : '');
?>

<?php $__env->startSection('content'); ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="quick-actions-toolbar d-flex gap-2 flex-wrap mb-3">
        <a href="<?php echo e(route('po.index')); ?>" class="btn btn-sm btn-light">
            <i class="fas fa-arrow-left"></i> Kembali ke List
        </a>
        <a href="<?php echo e(route('po.export-detail-excel', $po->po_number)); ?>" class="btn btn-sm btn-outline-success">
            <i class="fas fa-file-excel"></i> Export Excel
        </a>
        <button class="btn btn-sm btn-outline-info" data-toggle="modal" data-target="#editHeaderModal">
            <i class="fas fa-edit"></i> Edit Header PO
        </button>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($poCanCancel): ?>
            <button class="btn btn-sm btn-outline-danger" data-toggle="modal" data-target="#cancelPoModal">
                <i class="fas fa-times"></i> Batalkan PO
            </button>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <button class="btn btn-sm btn-outline-primary" id="refreshStatusBtn">
            <i class="fas fa-sync-alt"></i> Refresh Status
        </button>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card card-outline card-primary mb-3">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h3 class="card-title mb-0">Header PO</h3>
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <a href="<?php echo e(route('po.export-detail-excel', $po->po_number)); ?>" class="btn btn-sm btn-outline-success">
                                Export Excel
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body row g-2">
                    <div class="col-md-6"><strong>Nomor PO:</strong> <?php echo e($po->po_number); ?></div>
                    <div class="col-md-6"><strong>Tanggal PO:</strong>
                        <?php echo e(\Carbon\Carbon::parse($po->po_date)->format('d-m-Y')); ?></div>
                    <div class="col-md-6"><strong>Supplier:</strong> <?php echo e($po->supplier_name); ?></div>
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $po->status,'scope' => 'po']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($po->status),'scope' => 'po']); ?>
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
<?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <strong>ETA PO:</strong>
                        <?php echo e($po->eta_date ? \Carbon\Carbon::parse($po->eta_date)->format('d-m-Y') : '-'); ?>

                    </div>
                    <div class="col-md-6">
                        <strong>Plant:</strong> <?php echo e($po->plant_name ?: '-'); ?>

                    </div>
                    <div class="col-md-12"><strong>Catatan:</strong> <?php echo e($po->notes ?: '-'); ?></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($poIsFinal): ?>
                        <div class="col-md-12">
                            <div class="alert alert-light border mb-0 mt-2">
                                Dokumen ini sudah final. Aksi operasional seperti cancel PO, cancel item, force close, dan
                                update ETD dinonaktifkan.
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <?php if (isset($component)) { $__componentOriginal3d05c8acf062b983e9cf0b62ec42b146 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3d05c8acf062b983e9cf0b62ec42b146 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.item-summary','data' => ['counts' => $itemSummary,'received' => $totalReceived,'ordered' => $totalOrdered,'unit' => $unit]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('item-summary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['counts' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($itemSummary),'received' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($totalReceived),'ordered' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($totalOrdered),'unit' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($unit)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3d05c8acf062b983e9cf0b62ec42b146)): ?>
<?php $attributes = $__attributesOriginal3d05c8acf062b983e9cf0b62ec42b146; ?>
<?php unset($__attributesOriginal3d05c8acf062b983e9cf0b62ec42b146); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3d05c8acf062b983e9cf0b62ec42b146)): ?>
<?php $component = $__componentOriginal3d05c8acf062b983e9cf0b62ec42b146; ?>
<?php unset($__componentOriginal3d05c8acf062b983e9cf0b62ec42b146); ?>
<?php endif; ?>

            <div class="card card-outline card-info mb-3">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h3 class="card-title mb-0">Item PO & Monitoring ETD</h3>
                    <span class="text-muted small">Status item otomatis: Waiting / Confirmed / Late / Partial / Closed /
                        Cancelled. Tracking shipment dan GR tersedia per item.</span>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$poIsFinal): ?>
                    <div class="card-body border-bottom">
                        <form id="bulkEtdForm" method="POST" action="<?php echo e(route('po.items.bulk-schedule', $po->id)); ?>" class="row g-2 align-items-end">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PATCH'); ?>
                            <div class="col-md-4">
                                <label class="form-label">ETD Dasar Bulk Update</label>
                                <input type="date" name="etd_date" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Offset Hari</label>
                                <input type="number" name="day_offset" class="form-control form-control-sm" value="0" min="-30" max="30">
                            </div>
                            <div class="col-md-4">
                                <div class="small text-muted mb-2">Pilih item aktif dari checklist di tabel lalu apply satu ETD untuk semua item terpilih.</div>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-sm btn-primary w-100">Bulk Update ETD</button>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="card-body border-bottom">
                        <div class="alert alert-light border mb-0">
                            PO sudah final. Bulk update ETD dinonaktifkan.
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="card-body table-responsive p-0">
                    <table class="table table-hover mb-0" style="min-width: 1380px;">
                        <thead>
                            <tr>
                                <th style="min-width: 70px;">
                                    <input type="checkbox" id="bulkSelectAll" <?php if($poIsFinal): ?> disabled <?php endif; ?>>
                                    <span id="bulkSelectedCount" class="po-bulk-count"></span>
                                </th>
                                <?php
                                    $sortableHeaders = [
                                        'item_code' => 'Kode',
                                        'item_name' => 'Nama Item',
                                        'ordered_qty' => 'Ordered',
                                        'received_qty' => 'Received',
                                        'outstanding_qty' => 'Outstanding',
                                        'etd_date' => 'ETD',
                                        'monitoring_status' => 'Status',
                                    ];
                                    $currentSort = $sort ?? 'item_code';
                                    $currentDirection = $direction ?? 'asc';
                                ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $sortableHeaders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <?php ($nextDirection = $currentSort === $field && $currentDirection === 'asc' ? 'desc' : 'asc'); ?>
                                    <th style="min-width: 120px;">
                                        <a href="?sort=<?php echo e($field); ?>&direction=<?php echo e($nextDirection); ?>"
                                            class="text-decoration-none d-flex align-items-center gap-1">
                                            <?php echo e($label); ?>

                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentSort === $field): ?>
                                                <i class="fas fa-sort-<?php echo e($currentDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </a>
                                    </th>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <th style="min-width: 300px;">Tracking Shipment / GR</th>
                                <th style="min-width: 160px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <?php ($rowClass = match(true) {
                                        in_array($item->monitoring_status, [\App\Support\DocumentTermCodes::ITEM_LATE]) => 'table-danger',
                                        in_array($item->monitoring_status, [\App\Support\DocumentTermCodes::ITEM_WAITING]) => 'table-warning',
                                        default => '',
                                    }); ?>
                                    <tr class="<?php echo e($rowClass); ?>">
                                        <td class="align-top">
                                            <input type="checkbox"
                                                name="item_ids[]"
                                                value="<?php echo e($item->id); ?>"
                                                class="bulk-item-checkbox"
                                                form="bulkEtdForm"
                                                <?php if(!$item->can_update_etd || $poIsFinal): ?> disabled <?php endif; ?>>
                                        </td>
                                        <td class="align-top"><?php echo e($item->item_code); ?></td>
                                        <td class="align-top">
                                            <?php echo e($item->item_name); ?>

                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->cancel_reason): ?>
                                                <div class="small text-danger mt-1">Alasan: <?php echo e($item->cancel_reason); ?></div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                        <td class="align-top"><?php echo e(\App\Support\NumberFormatter::trim($item->ordered_qty)); ?> <?php echo e($item->unit_name); ?></td>
                                        <td class="align-top"><?php echo e(\App\Support\NumberFormatter::trim($item->received_qty)); ?> <?php echo e($item->unit_name); ?></td>
                                        <td class="align-top"><?php echo e(\App\Support\NumberFormatter::trim($item->outstanding_qty)); ?> <?php echo e($item->unit_name); ?></td>
                                        <td class="align-top">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->etd_date): ?>
                                                <?php echo e(\Carbon\Carbon::parse($item->etd_date)->format('d-m-Y')); ?>

                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                        <td class="align-top">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->tracking_rows->isEmpty()): ?>
                                                <div class="small text-muted">Belum ada shipment / GR untuk item ini.</div>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-view-tracking"
                                                    data-action="view-tracking"
                                                    data-item-id="<?php echo e($item->id); ?>"
                                                    data-item-code="<?php echo e($item->item_code); ?>"
                                                    data-copy-url="<?php echo e(route('po.item.tracking.copy-text', [$po->id, $item->id])); ?>"
                                                    data-excel-url="<?php echo e(route('po.item.tracking.export-excel', [$po->id, $item->id])); ?>">
                                                    Lihat Tracking
                                                </button>
                                                <div class="small text-muted mt-1">
                                                    <?php echo e($item->tracking_rows->count()); ?> shipment trace
                                                </div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                        <td class="align-top">
                                            <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $item->monitoring_status,'scope' => 'item']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->monitoring_status),'scope' => 'item']); ?>
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
<?php endif; ?>
                                            <?php if (isset($component)) { $__componentOriginalff790f57795a61931511adf462769e77 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalff790f57795a61931511adf462769e77 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-help','data' => ['status' => $item->monitoring_status,'scope' => 'item']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-help'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->monitoring_status),'scope' => 'item']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalff790f57795a61931511adf462769e77)): ?>
<?php $attributes = $__attributesOriginalff790f57795a61931511adf462769e77; ?>
<?php unset($__attributesOriginalff790f57795a61931511adf462769e77); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalff790f57795a61931511adf462769e77)): ?>
<?php $component = $__componentOriginalff790f57795a61931511adf462769e77; ?>
<?php unset($__componentOriginalff790f57795a61931511adf462769e77); ?>
<?php endif; ?>
                                        </td>
                                    <?php ($etdUrl = $item->can_update_etd && !$poIsFinal ? route('po.items.schedule', [$item->id]) : ''); ?>
                                    <?php ($cancelUrl = $item->can_cancel && !$poIsFinal ? route('po.items.cancel', [$item->id]) : ''); ?>
                                    <?php ($forceCloseUrl = $item->can_force_close && !$poIsFinal ? route('po.items.force-close', [$item->id]) : ''); ?>
                                    <td class="align-top">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($item->monitoring_status, [\App\Support\DocumentTermCodes::ITEM_CLOSED])): ?>
                                            <span class="badge bg-light text-muted">Final — Diterima Penuh</span>
                                        <?php elseif($item->can_update_etd || $item->can_cancel || $item->can_force_close): ?>
                                            <div class="d-flex gap-1 flex-wrap">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->can_update_etd): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                                        data-action="edit-etd"
                                                        data-item-id="<?php echo e($item->id); ?>"
                                                        data-item-code="<?php echo e($item->item_code); ?>"
                                                        data-etd-url="<?php echo e($etdUrl); ?>"
                                                        data-etd-date="<?php echo e($item->etd_date); ?>"
                                                        data-cancel-reason="<?php echo e($item->cancel_reason); ?>">
                                                        Edit ETD
                                                    </button>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->can_cancel): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                                        data-action="cancel-item"
                                                        data-item-id="<?php echo e($item->id); ?>"
                                                        data-item-code="<?php echo e($item->item_code); ?>"
                                                        data-cancel-url="<?php echo e($cancelUrl); ?>">
                                                        Cancel
                                                    </button>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->can_force_close): ?>
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        data-action="force-close"
                                                        data-item-id="<?php echo e($item->id); ?>"
                                                        data-item-code="<?php echo e($item->item_code); ?>"
                                                        data-force-close-url="<?php echo e($forceCloseUrl); ?>">
                                                        Force Close
                                                    </button>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted">Tidak dapat diubah</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Belum ada item.</td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$item->tracking_rows->isEmpty()): ?>
                        <div class="tracking-content d-none" data-item-id="<?php echo e($item->id); ?>">
                            <div class="small text-muted mb-3">
                                <?php echo e($item->item_name); ?> | Qty Order PO <?php echo e(\App\Support\NumberFormatter::trim($item->ordered_qty)); ?> <?php echo e($item->unit_name); ?> |
                                Qty Sudah Masuk PO <?php echo e(\App\Support\NumberFormatter::trim($item->received_qty)); ?> <?php echo e($item->unit_name); ?> |
                                Qty Outstanding PO <?php echo e(\App\Support\NumberFormatter::trim($item->outstanding_qty)); ?> <?php echo e($item->unit_name); ?>

                            </div>

                            <?php ($initialTimelineStatus = match (true) {
                                $item->monitoring_status === \App\Support\DocumentTermCodes::ITEM_CANCELLED => \App\Support\DocumentTermCodes::ITEM_CANCELLED,
                                $item->monitoring_status === \App\Support\DocumentTermCodes::ITEM_FORCE_CLOSED => \App\Support\DocumentTermCodes::ITEM_FORCE_CLOSED,
                                $item->etd_date && \Carbon\Carbon::parse($item->etd_date)->isPast() && (float) $item->received_qty <= 0 => \App\Support\DocumentTermCodes::ITEM_LATE,
                                $item->etd_date => \App\Support\DocumentTermCodes::ITEM_CONFIRMED,
                                default => \App\Support\DocumentTermCodes::ITEM_WAITING,
                            }); ?>
                            <?php ($runningReceivedQty = 0); ?>

                            <div class="timeline-row d-flex align-items-start gap-2 mb-3">
                                <div class="timeline-dot rounded-circle bg-<?php echo e($initialTimelineStatus === \App\Support\DocumentTermCodes::ITEM_CONFIRMED ? 'info' : 'secondary'); ?>"></div>
                                <div class="timeline-content">
                                    <div class="fw-bold small"><?php echo e(\Carbon\Carbon::parse($po->po_date)->format('d/m/Y')); ?> | PO Created</div>
                                    <div class="small">Qty Order: <?php echo e(\App\Support\NumberFormatter::trim($item->ordered_qty)); ?> <?php echo e($item->unit_name); ?> |
                                        Qty Masuk: 0 <?php echo e($item->unit_name); ?> |
                                        Qty Outstanding: <?php echo e(\App\Support\NumberFormatter::trim($item->ordered_qty)); ?> <?php echo e($item->unit_name); ?>

                                    </div>
                                    <div class="text-<?php echo e($initialTimelineStatus === \App\Support\DocumentTermCodes::ITEM_CONFIRMED ? 'warning' : 'muted'); ?> small"><?php echo e($initialTimelineStatus); ?></div>
                                </div>
                            </div>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $item->tracking_rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tracking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php ($shipmentDate = $tracking->shipment_date ? \Carbon\Carbon::parse($tracking->shipment_date)->format('d/m/Y') : '-'); ?>
                                <?php ($shipmentNumber = $tracking->shipment_number ?: 'Belum ada nomor shipment'); ?>
                                <?php ($deliveryNoteNumber = $tracking->delivery_note_number ?: '-'); ?>
                                <?php ($shipmentLabel = 'Pengiriman ke-'.$loop->iteration.' | DN '.$deliveryNoteNumber); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tracking->gr_rows->isEmpty()): ?>
                                    <?php ($shipmentTimelineStatus = $runningReceivedQty > 0
                                        ? \App\Support\DocumentTermCodes::ITEM_PARTIAL
                                        : ($initialTimelineStatus === \App\Support\DocumentTermCodes::ITEM_WAITING
                                            ? \App\Support\DocumentTermCodes::ITEM_CONFIRMED
                                            : $initialTimelineStatus)); ?>

                                    <div class="timeline-row d-flex align-items-start gap-2 mb-3">
                                        <div class="timeline-dot rounded-circle bg-<?php echo e($shipmentTimelineStatus === \App\Support\DocumentTermCodes::ITEM_PARTIAL ? 'primary' : 'info'); ?>"></div>
                                        <div class="timeline-content">
                                            <div class="fw-bold small"><?php echo e($shipmentDate); ?> | <?php echo e($shipmentLabel); ?> (Belum GR)</div>
                                            <div class="small">
                                                <?php echo e($shipmentNumber); ?>

                                            </div>
                                            <div class="text-<?php echo e($shipmentTimelineStatus === \App\Support\DocumentTermCodes::ITEM_PARTIAL ? 'primary' : 'muted'); ?> small"><?php echo e($shipmentTimelineStatus); ?></div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $tracking->gr_rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <?php ($runningReceivedQty += (float) ($gr->gr_received_qty ?? 0)); ?>
                                        <?php ($grDate = $gr->receipt_date ? \Carbon\Carbon::parse($gr->receipt_date)->format('d/m/Y') : '-'); ?>
                                        <?php ($remainingQty = max(0, (float) $item->ordered_qty - $runningReceivedQty)); ?>

                                        <div class="timeline-row d-flex align-items-start gap-2 mb-3">
                                            <div class="timeline-dot rounded-circle <?php echo e($remainingQty <= 0 ? 'bg-success' : ($runningReceivedQty > 0 ? 'bg-primary' : 'bg-secondary')); ?>"></div>
                                            <div class="timeline-content">
                                                <div class="fw-bold small"><?php echo e($grDate); ?> | <?php echo e($shipmentLabel); ?></div>
                                                <div class="small">
                                                    No Shipment: <?php echo e($shipmentNumber); ?> | No GR: <?php echo e($gr->gr_number ?: '-'); ?>

                                                </div>
                                                <div class="small">
                                                    Qty Order: - | Qty Masuk: <?php echo e(\App\Support\NumberFormatter::trim($gr->gr_received_qty ?? 0)); ?> <?php echo e($item->unit_name); ?> |
                                                    Qty Outstanding: <?php echo e(\App\Support\NumberFormatter::trim($remainingQty)); ?> <?php echo e($item->unit_name); ?>

                                                </div>
                                                <div class="text-<?php echo e($remainingQty <= 0 ? 'success' : ($runningReceivedQty > 0 ? 'primary' : 'muted')); ?> small">
                                                    <?php echo e($remainingQty <= 0 ? \App\Support\DocumentTermCodes::ITEM_CLOSED : ($runningReceivedQty > 0 ? \App\Support\DocumentTermCodes::ITEM_PARTIAL : $initialTimelineStatus)); ?>

                                                </div>
                                            </div>
                                        </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-outline card-danger mb-3">
                <div class="card-header">
                    <h3 class="card-title">Batalkan PO</h3>
                </div>
                <div class="card-body">
                    <button class="btn btn-danger btn-sm w-100" data-toggle="modal" data-target="#cancelPoModal"
                        <?php if(!$poCanCancel): ?> disabled <?php endif; ?>>
                        Batalkan PO
                    </button>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$poCanCancel): ?>
                        <div class="small text-muted mt-2">PO dengan status final tidak bisa dibatalkan lagi.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($po->cancel_reason): ?>
                        <div class="alert alert-danger mt-2 mb-0"><strong>Alasan:</strong> <?php echo e($po->cancel_reason); ?></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Riwayat Status</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $histories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $history): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <li class="list-group-item">
                                <div class="fw-semibold">
                                    <?php echo e($history->from_status ? \App\Support\TermCatalog::label('po_status', $history->from_status, $history->from_status) : 'N/A'); ?>

                                    ->
                                    <?php echo e(\App\Support\TermCatalog::label('po_status', $history->to_status, $history->to_status)); ?>

                                </div>
                                <small class="text-muted">
                                    <?php echo e($history->changed_by_name ?: 'System'); ?> |
                                    <?php echo e(\Carbon\Carbon::parse($history->changed_at)->format('d-m-Y H:i')); ?>

                                </small>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($history->note): ?>
                                    <div><?php echo e($history->note); ?></div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </li>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <li class="list-group-item text-muted">Belum ada histori status.</li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="itemActionModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Item Action</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="item_id" value="">
                    <label class="form-label">Alasan *</label>
                    <textarea name="cancel_reason" class="form-control form-control-sm" required rows="3"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
                    <button class="btn btn-danger btn-sm">Konfirmasi</button>
                </div>
            </form>
        </div>
    </div>

    
    <div class="modal fade" id="etdModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PATCH'); ?>
                <input type="hidden" name="item_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title">Edit ETD Item</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">ETD Date</label>
                    <input type="date" name="etd_date" class="form-control form-control-sm">
                    <label class="form-label mt-2">Remarks (opsional)</label>
                    <textarea name="remarks" class="form-control form-control-sm" rows="2"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
                    <button class="btn btn-primary btn-sm">Simpan ETD</button>
                </div>
            </form>
        </div>
    </div>

    
    <div class="modal fade" id="trackingModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1">Tracking Shipment / GR</h5>
                        <div class="small text-muted">Detail per shipment dan histori GR tersedia dalam satu popup ringkas.</div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-sm btn-outline-secondary js-copy-tracking">
                            Copy
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success js-export-tracking">
                            Export Excel
                        </button>
                        <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
                <div class="modal-body">
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="cancelPoModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="<?php echo e(route('po.cancel', $po->id)); ?>" class="modal-content">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title">Batalkan PO <?php echo e($po->po_number); ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Alasan Pembatalan *</label>
                    <textarea name="cancel_reason" class="form-control form-control-sm" required rows="3"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
                    <button class="btn btn-danger btn-sm">Konfirmasi Cancel PO</button>
                </div>
            </form>
        </div>
    </div>

    
    <div class="modal fade" id="editHeaderModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="<?php echo e(route('po.update', $po->id)); ?>" class="modal-content" id="editHeaderForm">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div class="modal-header">
                    <h5 class="modal-title">Edit Header PO <?php echo e($po->po_number); ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="editPoNumber">Nomor PO *</label>
                        <input type="text" class="form-control form-control-sm" id="editPoNumber" name="po_number"
                            value="<?php echo e($po->po_number); ?>" required>
                    </div>
                    <div class="form-group mt-2">
                        <label class="form-label" for="editPoDate">Tanggal PO *</label>
                        <input type="date" class="form-control form-control-sm" id="editPoDate" name="po_date"
                            value="<?php echo e(\Carbon\Carbon::parse($po->po_date)->format('Y-m-d')); ?>" required>
                    </div>
                    <div class="form-group mt-2">
                        <label class="form-label" for="editSupplierId">Supplier *</label>
                        <select class="form-control form-control-sm supplier-select" id="editSupplierId" name="supplier_id" required>
                            <option value="">-- Pilih supplier --</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($supplier->id); ?>" <?php echo e((int) $po->supplier_id === (int) $supplier->id ? 'selected' : ''); ?>>
                                    <?php echo e($supplier->supplier_code); ?> - <?php echo e($supplier->supplier_name); ?>

                                </option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group mt-2">
                        <label class="form-label" for="editNotes">Catatan</label>
                        <input type="text" class="form-control form-control-sm" id="editNotes" name="notes"
                            value="<?php echo e($po->notes ?? ''); ?>" placeholder="Catatan internal (opsional)">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
    <?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <?php echo app('Illuminate\Foundation\Vite')('resources/js/po-show.js'); ?>
    <script>
        window.PO_SHOW_CONFIG = {
            refreshUrl: "<?php echo e(route('po.refresh-status', $po->id)); ?>",
            csrfToken: document.querySelector('meta[name="csrf-token"]').content,
        };
    </script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        .timeline-dot {
            min-width: 12px;
        }
        .timeline-container {
            position: relative;
            padding-left: 14px;
        }
        .timeline-node {
            position: relative;
        }
        .timeline-node:before {
            content: '';
            position: absolute;
            left: 5px;
            top: 24px;
            bottom: -16px;
            width: 2px;
            background: #d0d7bb;
        }
        .timeline-node:last-child:before {
            display: none;
        }

        .po-page {
            --po-primary: #2563eb;
            --po-primary-soft: #eff6ff;
            --po-border: #e2e8f0;
            --po-muted: #64748b;
            --po-surface: #ffffff;
        }

        .po-show-header-card {
            border: 1px solid var(--po-border);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        }

        .po-toolbar-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 6px;
            transition: all 0.15s ease;
        }

        .po-quick-actions .btn {
            white-space: nowrap;
        }

        .po-card-compact {
            padding: 16px 18px;
        }

        .po-card-compact .card-header {
            padding: 12px 16px;
        }

        .po-header-grid > div {
            padding: 4px 0;
        }

        .po-header-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--po-muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 2px;
        }

        .po-header-value {
            font-size: 14px;
            font-weight: 600;
            color: #0f172a;
        }

        .po-item-table-wrap {
            border: 1px solid var(--po-border);
            border-radius: 10px;
            overflow: hidden;
        }

        .po-item-table {
            margin-bottom: 0;
            font-size: 12.5px;
        }

        .po-item-table thead th {
            padding: 10px 12px;
            border-bottom-width: 1px;
            background: #f8fafc;
            color: #475569;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .po-item-table tbody td {
            padding: 8px 12px;
            vertical-align: middle;
            font-size: 12px;
        }

        .po-item-table tbody tr.table-danger {
            background-color: #fef2f2;
        }

        .po-item-table tbody tr.table-warning {
            background-color: #fffbeb;
        }

        .po-item-table tbody tr:hover {
            background-color: #f1f5f9;
        }

        .po-item-actions .btn {
            padding: 3px 8px;
            font-size: 11px;
            font-weight: 600;
        }

        .po-tracking-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            color: var(--po-muted);
        }

        .po-bulk-count {
            font-size: 11px;
            font-weight: 600;
            color: var(--po-primary);
            margin-left: 8px;
        }

        .po-edit-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        @media (max-width: 1199.98px) {
            .po-header-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }
        }

        @media (max-width: 767.98px) {
            .po-header-grid {
                grid-template-columns: 1fr !important;
            }

            .po-quick-actions .btn {
                flex: 1;
                justify-content: center;
            }

            .po-item-table thead th,
            .po-item-table tbody td {
                padding: 6px 8px;
                font-size: 11px;
            }

            .po-item-actions .btn {
                padding: 2px 5px;
                font-size: 10px;
            }
        }

        @media (max-width: 575.98px) {
            .po-header-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views/po/show.blade.php ENDPATH**/ ?>