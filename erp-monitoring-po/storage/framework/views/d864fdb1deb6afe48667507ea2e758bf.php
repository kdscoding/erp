<?php ($title = 'Units'); ?>
<?php ($header = 'Units'); ?>
<?php ($headerSubtitle = ''); ?>

<?php $__env->startSection('content'); ?>
    <div class="page-shell">

        
        <section class="ui-surface mb-3">
            <div class="ui-surface-body py-2">
                <form method="GET" class="filter-row">
                    <div class="filter-search">
                        <input class="form-control form-control-sm" name="q" value="<?php echo e(request('q')); ?>"
                            placeholder="Cari kode atau nama unit..." autofocus>
                    </div>
                </form>
            </div>
        </section>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('q')): ?>
        <div class="filter-tags mb-3">
            <span class="text-sm text-muted">Filter aktif:</span>
            <span class="filter-tag">
                Q: "<?php echo e(request('q')); ?>"
                <a href="<?php echo e(request()->fullUrlWithQuery(['q' => null])); ?>" class="tag-remove">×</a>
            </span>
            <a href="<?php echo e(route('units.index')); ?>" class="tag-clear">Reset semua</a>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Daftar Unit</h3>
                </div>
            </div>

            <div class="table-wrap table-responsive">
                <table class="table table-hover ui-table data-table-advanced" data-export-title="units">
                    <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Unit</th>
                                <th>Terakhir Diubah</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr>
                                    <td><div class="doc-number"><?php echo e($row->unit_code); ?></div></td>
                                    <td>
                                        <div class="unit-name"><?php echo e($row->unit_name); ?></div>
                                    </td>
                                    <td>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row->updated_at): ?>
                                            <small class="text-muted"><?php echo e(\Carbon\Carbon::parse($row->updated_at)->diffForHumans()); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="action-stack">
                                            <a href="<?php echo e(route('units.edit', $row->id)); ?>"
                                                class="btn btn-sm btn-outline-primary" title="Edit">✏️</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr>
                                    <td colspan="4">
                                    <div class="empty-state">
                                        <div class="empty-icon">📦</div>
                                        <div class="empty-title">Belum ada data unit</div>
                                        <div class="empty-subtitle">Mulai tambah unit baru untuk melihat data di sini.</div>
                                        <a href="<?php echo e(route('units.create')); ?>" class="btn btn-primary btn-sm px-4 mt-2">
                                            ⚕ Tambah Unit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    
    <a href="<?php echo e(route('units.create')); ?>" class="fab-add" title="Tambah Unit">
        <span class="fab-icon">+</span>
        <span class="fab-label">Tambah</span>
    </a>

    
    <style>
        .filter-row {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-search {
            flex: 1;
            max-width: 400px;
        }

        .filter-search input {
            width: 100%;
        }

        .filter-segmented {
            display: flex;
            gap: 4px;
            background: var(--bg-muted, #f1f5f9);
            padding: 3px;
            border-radius: 6px;
        }

        .segment-btn {
            padding: 6px 14px;
            border: none;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
            background: transparent;
            color: var(--text-secondary, #64748b);
            transition: all 0.15s;
            white-space: nowrap;
        }

        .segment-btn.active {
            background: var(--ui-surface, #fff);
            color: var(--text-primary, #1e293b);
            font-weight: 600;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .segment-btn:hover:not(.active) {
            color: var(--text-primary, #1e293b);
        }

        .filter-tags {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .filter-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            background: var(--bg-primary, #eff6ff);
            color: var(--text-primary, #1e293b);
            border-radius: 16px;
            font-size: 12px;
            font-weight: 500;
        }

        .tag-remove {
            color: var(--text-secondary, #64748b);
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            line-height: 1;
        }

        .tag-remove:hover {
            color: #ef4444;
        }

        .tag-clear {
            font-size: 12px;
            color: var(--primary, #3b82f6);
            text-decoration: underline;
            cursor: pointer;
        }

        .row-inactive {
            opacity: 0.5;
            transition: opacity 0.3s;
        }

        .row-inactive:hover {
            opacity: 0.8;
        }

        .unit-name {
            font-weight: 500;
        }

        /* FAB */
        .fab-add {
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--lemon-green, #9ecb3c);
            color: var(--lemon-ink, #304218);
            border: none;
            box-shadow: 0 4px 12px rgba(158, 203, 60, 0.35);
            cursor: pointer;
            z-index: 100;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .fab-add:hover {
            transform: scale(1.08) translateY(-2px);
            box-shadow: 0 6px 20px rgba(158, 203, 60, 0.5);
        }

        .fab-icon {
            font-size: 22px;
            font-weight: 700;
            line-height: 1;
        }

        .fab-label {
            font-size: 9px;
            font-weight: 600;
            margin-top: 1px;
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-group .field-label {
            display: block;
            margin-bottom: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--lemon-olive, #566d2a);
        }

        /* Table Modernization */
        .ui-table tbody tr {
            transition: background-color 0.15s;
        }

        .ui-table tbody tr:nth-child(even) {
            background: rgba(247, 248, 234, 0.5);
        }

        .ui-table tbody tr:hover {
            background: rgba(158, 203, 60, 0.08);
        }

        .doc-number {
            font-family: 'SF Mono', 'Consolas', monospace;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--lemon-olive, #566d2a);
            background: var(--lemon-bg-soft, #fcfced);
            padding: 2px 8px;
            border-radius: 4px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
        }

        .empty-icon {
            font-size: 48px;
            margin-bottom: 12px;
        }

        .empty-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary, #1e293b);
            margin-bottom: 4px;
        }

        .empty-subtitle {
            font-size: 13px;
            color: var(--text-secondary, #64748b);
            margin-bottom: 16px;
        }

        @media (max-width: 768px) {
            .filter-row {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-search {
                max-width: 100%;
            }

            .filter-segmented {
                justify-content: center;
            }

            .segment-btn {
                padding: 6px 10px;
                font-size: 12px;
            }
        }
    </style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views/masters/units/index.blade.php ENDPATH**/ ?>