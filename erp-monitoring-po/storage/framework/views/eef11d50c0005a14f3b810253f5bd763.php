<?php
    $title = 'Purchase Orders';
    $header = 'Purchase Orders';
?>

<?php $__env->startSection('content'); ?>
    <div class="page-shell po-page">
        <section class="ui-surface po-control-panel">
            <div class="po-panel-head">
                <div>
                    <div class="po-eyebrow">PROCUREMENT / PURCHASE ORDER</div>
                </div>
                <div class="po-panel-actions">
                    <a href="<?php echo e(route('po.export-excel', request()->query())); ?>" class="btn btn-light btn-sm">
                        <i class="fas fa-file-excel"></i> Export Monitoring
                    </a>
                    <a href="<?php echo e(route('po.import-template')); ?>" class="btn btn-light btn-sm">
                        <i class="fas fa-file-download"></i> Template Import
                    </a>
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#importPoModal">
                        <i class="fas fa-file-import"></i> Import Excel
                    </button>
                    <a href="<?php echo e(route('po.create')); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Buat PO
                    </a>
                </div>
            </div>

            <div class="po-summary-tabs">
                <div class="po-summary-tab-list" role="tablist">
                    <button role="tab" class="po-summary-tab is-active" data-tab="po-status" aria-selected="true">PO Status</button>
                    <button role="tab" class="po-summary-tab" data-tab="item-status" aria-selected="false">Item Status</button>
                </div>
                <div class="po-summary-tab-panels">
                    <div role="tabpanel" class="po-summary-tab-panel is-active" id="po-status-panel">
                        <div class="po-summary-grid">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $poStatusChips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php
                                    $filterQuery = request()->query();
                                    if ($chip['value'] !== '') {
                                        $filterQuery['po_status'] = $chip['value'];
                                    } else {
                                        unset($filterQuery['po_status']);
                                    }

                                    $summaryTone = match ($chip['value']) {
                                        'Late', 'Delayed' => 'danger',
                                        'Partial' => 'primary',
                                        'Closed' => 'success',
                                        'Cancelled' => 'secondary',
                                        default => 'warning',
                                    };
                                ?>
                                <a href="<?php echo e(route('po.index', $filterQuery)); ?>" class="po-summary-card po-summary-<?php echo e($summaryTone); ?> <?php if(request('po_status') === (string) $chip['value']): ?> is-active <?php endif; ?>">
                                    <span class="po-summary-icon">
                                        <i class="fas <?php echo e($chip['value'] === '' ? 'fas fa-boxes' : 'fas fa-filter'); ?>"></i>
                                    </span>
                                    <span class="po-summary-copy">
                                        <span class="po-summary-label"><?php echo e($chip['label'] ?? ucfirst(strtolower($chip['value'] ?? 'Semua'))); ?></span>
                                        <strong><?php echo e((int) ($chip['count'] ?? 0)); ?></strong>
                                    </span>
                                </a>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                    <div role="tabpanel" class="po-summary-tab-panel" id="item-status-panel" hidden>
                        <div class="po-summary-grid">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $itemStatusChips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php
                                    $filterQuery = request()->query();
                                    if ($chip['value'] !== '') {
                                        $filterQuery['item_status'] = $chip['value'];
                                    } else {
                                        unset($filterQuery['item_status']);
                                    }
                                ?>
                                <a href="<?php echo e(route('po.index', $filterQuery)); ?>" class="po-summary-card po-summary-secondary <?php if(request('item_status') === (string) $chip['value']): ?> is-active <?php endif; ?>">
                                    <span class="po-summary-icon">
                                        <i class="fas <?php echo e($chip['value'] === '' ? 'fas fa-layer-group' : 'fas fa-filter'); ?>"></i>
                                    </span>
                                    <span class="po-summary-copy">
                                        <span class="po-summary-label"><?php echo e($chip['label'] ?? ucfirst(strtolower($chip['value'] ?? 'Semua'))); ?></span>
                                        <strong><?php echo e((int) ($chip['count'] ?? 0)); ?></strong>
                                    </span>
                                </a>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="po-filter-panel">
                <button type="button" class="filter-toggle-btn" id="filterToggle">
                    <i class="fas fa-sliders-h"></i> <span id="filterToggleText">Tampilkan Filter</span>
                </button>
                <div id="filterSection" style="display:none;">
                    <form method="GET" class="filter-bar" id="poFilterForm">
                        <div class="filter-bar-field">
                            <label for="filterPoNumber">Nomor PO</label>
                            <input type="text" id="filterPoNumber" name="po_number" value="<?php echo e($filterPoNumber ?? ''); ?>" class="form-control form-control-sm" placeholder="Cari nomor PO">
                        </div>
                        <div class="filter-bar-field">
                            <label for="filterSupplier">Supplier</label>
                            <select id="filterSupplier" name="supplier_code" class="form-control form-control-sm supplier-select">
                                <option value="">Semua supplier</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($supplier->supplier_code); ?>" <?php echo e(($filterSupplierCode ?? '') === $supplier->supplier_code ? 'selected' : ''); ?>>
                                        <?php echo e($supplier->supplier_code); ?> - <?php echo e($supplier->supplier_name); ?>

                                    </option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </div>
                        <div class="filter-bar-field">
                            <label for="filterDateFrom">Dari Tanggal</label>
                            <input type="date" id="filterDateFrom" name="date_from" value="<?php echo e($filterDateFrom ?? ''); ?>" class="form-control form-control-sm">
                        </div>
                        <div class="filter-bar-field">
                            <label for="filterDateTo">Sampai Tanggal</label>
                            <input type="date" id="filterDateTo" name="date_to" value="<?php echo e($filterDateTo ?? ''); ?>" class="form-control form-control-sm">
                        </div>
                        <div class="filter-bar-field">
                            <label for="filterPoStatus">PO Status</label>
                            <select id="filterPoStatus" name="po_status" class="form-control form-control-sm">
                                <option value="">Semua PO Status</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = \App\Support\TermCatalog::options('po_status', \App\Support\DomainStatus::legacyOptions(\App\Support\DomainStatus::GROUP_PO_STATUS)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($value); ?>" <?php echo e(($filterPoStatus ?? '') === $value ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </div>
                        <div class="filter-bar-field">
                            <label for="filterItemStatus">Item Status</label>
                            <select id="filterItemStatus" name="item_status" class="form-control form-control-sm">
                                <option value="">Semua Item Status</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = \App\Support\TermCatalog::options('po_item_status', \App\Support\DomainStatus::legacyOptions(\App\Support\DomainStatus::GROUP_PO_ITEM_STATUS)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($value); ?>" <?php echo e(($filterItemStatus ?? '') === $value ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </div>
                        <div class="filter-bar-actions">
                            <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-search"></i> Terapkan</button>
                            <a href="<?php echo e(route('po.index')); ?>" class="btn btn-light btn-sm"><i class="fas fa-redo"></i> Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section class="ui-surface po-table-panel">
            <div class="po-table-head">
                <div>
                    <h3 class="po-section-title">Daftar PO</h3>
                    <p class="po-section-subtitle"><?php echo e($rows->total()); ?> dokumen ditemukan pada halaman ini.</p>
                </div>
                <div class="po-search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" id="po-table-search" class="form-control form-control-sm" placeholder="Cari PO, supplier..." aria-label="Cari PO">
                </div>
            </div>

            <div class="table-wrap table-responsive">
                <table class="table table-hover po-table" id="po-table">
                    <thead>
                        <tr>
                            <th>PO</th>
                            <th>Supplier</th>
                            <th>Tanggal</th>
                            <th>ETA</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php ($rowClass = match(true) {
                                in_array($r->status, [\App\Support\DocumentTermCodes::PO_LATE, 'Delayed']) => 'table-danger',
                                in_array($r->status, [\App\Support\DocumentTermCodes::PO_ISSUED, 'Open']) => 'table-warning',
                                default => '',
                            }); ?>
                            <tr class="<?php echo e($rowClass); ?>">
                                <td>
                                    <a href="<?php echo e(route('po.show', $r->po_number)); ?>" class="po-doc-link"><?php echo e($r->po_number); ?></a>
                                    <div class="po-doc-meta"><?php echo e(\Carbon\Carbon::parse($r->po_date)->format('d-m-Y')); ?></div>
                                </td>
                                <td>
                                    <div class="po-doc-link"><?php echo e($r->supplier_code); ?></div>
                                    <div class="po-doc-meta"><?php echo e($r->supplier_name); ?></div>
                                </td>
                                <td><?php echo e(\Carbon\Carbon::parse($r->po_date)->format('d-m-Y')); ?></td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($r->eta_date): ?>
                                        <strong><?php echo e(\Carbon\Carbon::parse($r->eta_date)->format('d-m-Y')); ?></strong>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td><?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $r->status,'scope' => 'po']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($r->status),'scope' => 'po']); ?>
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
                                <td class="text-right">
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
                                            Aksi
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item" href="<?php echo e(route('po.show', $r->po_number)); ?>">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                            <a class="dropdown-item" href="<?php echo e(route('po.export-detail-excel', $r->po_number)); ?>">
                                                <i class="fas fa-file-excel"></i> Export Excel
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="po-empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <strong>Belum ada PO yang sesuai.</strong>
                                        <span>Ubah filter atau buat purchase order baru.</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="po-pagination"><?php echo e($rows->links()); ?></div>
        </section>
    </div>

    <div class="modal fade" id="importPoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="<?php echo e(route('po.import')); ?>" enctype="multipart/form-data" class="modal-content">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title">Import Purchase Order</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="field-label" for="poImportFile">File Excel/CSV</label>
                        <input type="file" id="poImportFile" name="file" class="form-control form-control-sm" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <div class="alert alert-info mb-0" style="font-size: 12px;">
                        Gunakan template yang tersedia. Format mendukung <strong>.xlsx</strong>, <strong>.xls</strong>, dan <strong>.csv</strong>.
                        Setiap baris barang wajib memiliki kode barang, qty, dan harga yang valid.
                        <a href="<?php echo e(route('po.import-template')); ?>">Unduh template</a>.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Import PO</button>
                </div>
            </form>
        </div>
    </div>
 <?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <?php echo app('Illuminate\Foundation\Vite')('resources/js/po-index.js'); ?>
    <script>
        window.PO_INDEX_CONFIG = {};
    </script>
    <script>
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
        });
    </script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        .po-page {
            --po-primary: #2563eb;
            --po-primary-soft: #eff6ff;
            --po-border: #e2e8f0;
            --po-muted: #64748b;
            --po-surface: #ffffff;
        }

        .po-control-panel,
        .po-table-panel {
            border: 1px solid var(--po-border);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        }

        .po-control-panel {
            padding: 18px;
            margin-bottom: 16px;
        }

        .po-panel-head,
        .po-table-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            flex-wrap: wrap;
        }

        .po-eyebrow,
        .po-section-subtitle,
        .po-panel-subtitle {
            color: var(--po-muted);
        }

        .po-eyebrow {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            margin-bottom: 4px;
        }

        .po-panel-title,
        .po-section-title {
            margin-bottom: 4px;
            color: #0f172a;
            font-weight: 700;
        }

        .po-panel-subtitle,
        .po-section-subtitle {
            font-size: 13px;
            margin-bottom: 0;
        }

        .po-panel-actions {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            flex-wrap: wrap;
        }

        .po-summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(128px, 1fr));
            gap: 10px;
            margin: 18px 0;
        }

        .po-summary-tabs {
            margin: 18px 0;
        }

        .po-summary-tab-list {
            display: flex;
            gap: 4px;
            border-bottom: 1px solid var(--po-border);
            margin-bottom: 12px;
            padding-bottom: 0;
        }

        .po-summary-tab {
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 600;
            color: var(--po-muted);
            background: transparent;
            border: none;
            border-bottom: 2px solid transparent;
            cursor: pointer;
            transition: color 0.15s ease, border-color 0.15s ease;
        }

        .po-summary-tab:hover {
            color: #0f172a;
        }

        .po-summary-tab.is-active {
            color: #1d4ed8;
            border-bottom-color: #1d4ed8;
        }

        .po-summary-tab-panel[hidden] {
            display: none;
        }

        .po-summary-card {
            min-height: 74px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            border: 1px solid var(--po-border);
            border-radius: 10px;
            background: #f8fafc;
            color: #334155;
            text-decoration: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, transform 0.15s ease;
        }

        .po-summary-card:hover,
        .po-summary-card.is-active {
            border-color: #93c5fd;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.08);
            transform: translateY(-1px);
        }

        .po-summary-icon {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            flex: 0 0 auto;
        }

        .po-summary-warning .po-summary-icon { background: #fef3c7; color: #b45309; }
        .po-summary-danger .po-summary-icon { background: #fee2e2; color: #b91c1c; }
        .po-summary-primary .po-summary-icon { background: #dbeafe; color: #1d4ed8; }
        .po-summary-success .po-summary-icon { background: #dcfce7; color: #15803d; }
        .po-summary-secondary .po-summary-icon { background: #e2e8f0; color: #475569; }

        .po-summary-copy small {
            display: block;
            font-size: 11px;
            line-height: 1.25;
            color: var(--po-muted);
        }

        .po-summary-copy strong {
            display: block;
            margin-top: 2px;
            font-size: 20px;
            line-height: 1.1;
            color: #0f172a;
        }

        .po-filter-panel {
            border-top: 1px solid var(--po-border);
            padding-top: 14px;
        }

        .filter-toggle-btn { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; border: 1px solid var(--lemon-line); background: #f8f9fa; color: #666; cursor: pointer; margin-bottom: .5rem; }
        .filter-toggle-btn:hover { background: #e9ecef; color: #333; border-color: #dee2e6; }
        .filter-toggle-btn i { font-size: 12px; }
        .filter-bar { display: flex; flex-direction: row; align-items: flex-end; gap: .75rem; flex-wrap: wrap; padding: .5rem 0; }
        .filter-bar-field { display: flex; flex-direction: column; min-width: 140px; flex: 1; }
        .filter-bar-field label { font-size: 11px; font-weight: 600; color: #666; margin-bottom: 3px; }
        .filter-bar-field select, .filter-bar-field input { font-size: 12px; padding: 4px 8px; height: 32px; }
        .filter-bar-actions { display: flex; gap: .4rem; align-items: flex-end; margin-left: auto; }
        .filter-bar-actions .btn { height: 32px; font-size: 12px; }

        .po-table-panel {
            padding: 18px;
        }

        .po-table-head {
            align-items: center;
            margin-bottom: 14px;
        }

        .po-section-title {
            font-size: 16px;
        }

        .po-search-wrap {
            position: relative;
            width: min(100%, 260px);
        }

        .po-search-wrap > i {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
        }

        .po-search-wrap .form-control {
            padding-left: 32px;
        }

        .po-table {
            margin-bottom: 0;
            font-size: 13px;
        }

        .po-table thead th {
            padding: 10px 12px;
            border-top: 0;
            border-bottom-width: 1px;
            background: #f8fafc;
            color: #475569;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .po-table tbody td {
            padding: 12px;
            vertical-align: middle;
        }

        .po-doc-link {
            color: #1d4ed8;
            font-weight: 700;
            text-decoration: none;
        }

        .po-doc-link:hover {
            color: #1e40af;
            text-decoration: underline;
        }

        .po-doc-meta {
            margin-top: 3px;
            color: var(--po-muted);
            font-size: 11px;
        }

        .po-empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            padding: 32px 10px;
            color: var(--po-muted);
        }

        .po-empty-state i {
            font-size: 28px;
            color: #94a3b8;
        }

        .po-empty-state strong {
            color: #334155;
        }

        .po-empty-state span {
            font-size: 12px;
        }

        .po-pagination {
            margin-top: 14px;
        }

        @media (max-width: 1199.98px) {
            .filter-bar {
                flex-wrap: wrap;
            }
            .filter-bar-field {
                flex: 1 1 calc(33.333% - .5rem);
                min-width: 140px;
            }
        }

        @media (max-width: 767.98px) {
            .po-control-panel,
            .po-table-panel {
                padding: 14px;
            }

            .po-panel-head,
            .po-table-head {
                align-items: stretch;
                flex-direction: column;
            }

            .po-panel-actions {
                width: 100%;
            }

            .po-panel-actions .btn {
                flex: 1;
            }

            .filter-bar-actions {
                width: 100%;
            }

            .filter-bar-actions .btn {
                flex: 1;
            }

            .po-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .filter-bar-field {
                flex: 1 1 calc(50% - .375rem);
            }

            .po-search-wrap {
                width: 100%;
            }
        }

        @media (max-width: 575.98px) {
            .po-summary-grid {
                grid-template-columns: 1fr;
            }

            .filter-bar,
            .filter-bar-field,
            .filter-bar-actions {
                flex-direction: column;
                width: 100%;
            }

            .filter-bar-field {
                flex: 1 1 auto;
            }

            .po-summary-card {
                min-height: 66px;
            }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabList = document.querySelector('.po-summary-tab-list');
            if (!tabList) return;

            const tabs = tabList.querySelectorAll('.po-summary-tab');
            const panels = document.querySelectorAll('.po-summary-tab-panel');

            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    const targetId = this.dataset.tab + '-panel';

                    tabs.forEach(function (t) {
                        t.classList.remove('is-active');
                        t.setAttribute('aria-selected', 'false');
                    });
                    panels.forEach(function (p) {
                        p.hidden = true;
                        p.classList.remove('is-active');
                    });

                    this.classList.add('is-active');
                    this.setAttribute('aria-selected', 'true');

                    const targetPanel = document.getElementById(targetId);
                    if (targetPanel) {
                        targetPanel.hidden = false;
                        targetPanel.classList.add('is-active');
                    }
                });
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views\po\index.blade.php ENDPATH**/ ?>