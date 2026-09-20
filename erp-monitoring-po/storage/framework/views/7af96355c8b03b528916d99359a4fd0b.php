<?php
    $title = 'Buat PO';
    $header = 'Buat Purchase Order';
    $headerSubtitle = 'Input dokumen dan barang PO dalam satu alur yang ringkas.';
?>

<?php $__env->startSection('content'); ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <strong>Periksa kembali form:</strong>
            <ul class="mb-0 mt-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <li><?php echo e($err); ?></li>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </ul>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <form method="POST" action="<?php echo e(route('po.store')); ?>" id="po-form">
        <?php echo csrf_field(); ?>

        <div class="po-create-shell">
            <div class="po-create-main">
                <section class="ui-surface po-form-card">
                    <div class="po-card-head">
                        <div>
                            <span class="po-step-badge">1</span>
                            <div>
                                <h3 class="po-card-title">Dokumen & Supplier</h3>
                                <p class="po-card-subtitle">Lengkapi data utama sebelum menambahkan barang.</p>
                            </div>
                        </div>
                        <span class="po-status-pill"><i class="fas fa-file-import"></i> PO Manual</span>
                    </div>

                    <div class="po-card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span class="po-status-pill po-status-initial"><strong>Status Awal:</strong> <?php echo e(\App\Support\DocumentTermStatus::poStatusLabel(\App\Support\DocumentTermCodes::PO_ISSUED)); ?></span>
                            </div>
                        </div>
                        <div class="po-form-grid">
                            <div class="po-field">
                                <label class="field-label" for="poNumberInput">Nomor PO</label>
                                <input type="text" id="poNumberInput" class="form-control form-control-sm" name="po_number" value="<?php echo e(old('po_number')); ?>" placeholder="Kosongkan untuk auto number">
                                <small class="po-field-help">Opsional. Nomor akan dibuat otomatis jika tidak diisi.</small>
                            </div>

                            <div class="po-field">
                                <label class="field-label" for="poDateInput">Tanggal PO <span class="text-danger">*</span></label>
                                <input type="date" id="poDateInput" class="form-control form-control-sm" name="po_date" value="<?php echo e(old('po_date')); ?>" required>
                                <small class="po-field-help">Pilih tanggal PO dari kalender.</small>
                            </div>

                            <div class="po-field po-field-wide">
                                <label class="field-label" for="supplierInput">Supplier <span class="text-danger">*</span></label>
                                <select id="supplierInput" name="supplier_id" class="form-control form-control-sm supplier-select" required>
                                    <option value="">-- Pilih supplier --</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <option value="<?php echo e($s->id); ?>" <?php echo e(old('supplier_id') == $s->id ? 'selected' : ''); ?>>
                                            <?php echo e($s->supplier_code); ?> - <?php echo e($s->supplier_name); ?>

                                        </option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                                <small class="po-field-help">Ketik kode atau nama supplier untuk mencari.</small>
                            </div>

                            <div class="po-field po-field-wide" id="notesFieldWrapper" style="display:none">
                                <label class="field-label" for="notesInput">Catatan</label>
                                <input type="text" id="notesInput" class="form-control form-control-sm" name="notes" value="<?php echo e(old('notes')); ?>" placeholder="Catatan internal (opsional)">
                            </div>
                        </div>

                        <button type="button" class="btn btn-outline-secondary btn-sm po-notes-toggle" id="btn-toggle-notes" aria-expanded="false" aria-controls="notesFieldWrapper">
                            <i class="fas fa-sticky-note"></i> <span class="po-notes-toggle-label">Tambahkan Catatan</span>
                        </button>
                    </div>
                </section>

                <section class="ui-surface po-form-card">
                    <div class="po-card-head">
                        <div>
                            <span class="po-step-badge">2</span>
                            <div>
                                <h3 class="po-card-title">Barang PO</h3>
                                <p class="po-card-subtitle">Tambahkan item, qty, dan harga. Subtotal dihitung otomatis.</p>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" id="btn-add-item">
                            <i class="fas fa-plus"></i> Tambah Barang
                        </button>
                    </div>

                    <div class="po-card-body">
                        <datalist id="item-codes">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($item->item_code); ?>" label="<?php echo e($item->item_code); ?> — <?php echo e($item->item_name); ?> (<?php echo e($item->unit_name); ?>)"></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </datalist>

                        <div class="po-table-wrap table-responsive">
                            <table class="table table-bordered po-items-table" id="po-items-table">
                                <thead>
                                    <tr>
                                        <th style="width: 5%">No</th>
                                        <th style="width: 38%">Barang</th>
                                        <th style="width: 11%">Qty</th>
                                        <th style="width: 14%">Harga</th>
                                        <th style="width: 16%">Subtotal</th>
                                        <th style="width: 16%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                        <div class="po-create-summary">
                            <div>
                                <span class="po-summary-label">Jumlah item</span>
                                <strong id="item-count-text" class="po-summary-value">0</strong>
                            </div>
                            <div>
                                <span class="po-summary-label">Grand Total</span>
                                <strong id="grand-total-text" class="po-summary-value po-total-value">0,00</strong>
                            </div>
                            <input type="hidden" name="grand_total" id="grand-total-input" value="0">
                        </div>

                        <div class="po-sticky-action-bar">
                            <a href="<?php echo e(route('po.index')); ?>" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left"></i> Batal
                            </a>
                            <div class="po-action-group">
                                <button type="submit" class="btn btn-success btn-sm" id="btn-submit" data-save-label="Simpan PO">
                                    <i class="fas fa-save"></i> Simpan PO
                                </button>
                                <button type="submit" name="save_and_new" value="1" class="btn btn-success btn-sm" id="btn-save-and-new" data-save-label="Simpan & Baru">
                                    <i class="fas fa-save"></i> Simpan & Baru
                                </button>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="po-create-side">
                <section class="ui-surface po-guide-card">
                    <h3 class="po-guide-title"><i class="fas fa-keyboard"></i> Input Cepat</h3>
                    <ul class="po-guide-list">
                        <li><span><i class="fas fa-arrow-right"></i> Enter</span> Pilih barang dan lanjut ke baris berikutnya.</li>
                        <li><span><i class="fas fa-key"></i> Alt+Shift+N</span> Tambah baris barang baru.</li>
                        <li><span><i class="fas fa-key"></i> Ctrl + S</span> Simpan PO tanpa klik tombol.</li>
                    </ul>
                    <div class="po-guide-note">
                        <i class="fas fa-info-circle"></i>
                        Gunakan kode barang yang tersedia agar nama, satuan, dan status terisi otomatis.
                    </div>
                </section>

                <section class="ui-surface po-progress-card">
                    <h3 class="po-guide-title"><i class="fas fa-list-check"></i> Checklist</h3>
                    <div class="po-checklist">
                        <div class="po-check-item"><i class="fas fa-circle"></i> Data dokumen</div>
                        <div class="po-check-item"><i class="fas fa-circle"></i> Supplier dipilih</div>
                        <div class="po-check-item"><i class="fas fa-circle"></i> Minimal 1 barang</div>
                        <div class="po-check-item"><i class="fas fa-circle"></i> Qty lebih dari 0</div>
                    </div>
                </section>
            </aside>
        </div>
    </form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <?php echo app('Illuminate\Foundation\Vite')('resources/js/po-create.js'); ?>
    <script>
        window.PO_CREATE_CONFIG = {
            items: <?php echo json_encode($items, 15, 512) ?>,
            oldItems: <?php echo json_encode(old('items', []), 512) ?>,
            searchUrl: '<?php echo e(route('po.items.search')); ?>',
        };
    </script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        .po-create-shell {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 280px;
            gap: 16px;
            align-items: start;
        }

        .po-create-main,
        .po-create-side {
            display: grid;
            gap: 16px;
        }

        .ui-surface.po-form-card,
        .ui-surface.po-guide-card,
        .ui-surface.po-progress-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        }

        .po-card-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 15px 18px;
            border-bottom: 1px solid #e2e8f0;
        }

        .po-card-head > div {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .po-step-badge {
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            border-radius: 50%;
            background: #dbeafe;
            color: #1d4ed8;
            font-weight: 800;
            font-size: 13px;
        }

        .po-card-title,
        .po-guide-title {
            margin-bottom: 2px;
            color: #0f172a;
            font-size: 15px;
            font-weight: 700;
        }

        .po-card-subtitle {
            margin-bottom: 0;
            color: #64748b;
            font-size: 12px;
        }

        .po-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 9px;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .po-card-body {
            padding: 16px 18px;
        }

        .po-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px 16px;
        }

        .po-field-wide {
            grid-column: 1 / -1;
        }

        .field-label {
            display: block;
            margin-bottom: 5px;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
        }

        .po-field-help {
            display: block;
            margin-top: 4px;
            color: #94a3b8;
            font-size: 11px;
        }

        .po-readonly-input {
            background: #f8fafc !important;
            color: #475569 !important;
        }

        .select2-container {
            width: 100% !important;
        }

        .select2-container .select2-selection--single {
            height: calc(1.5em + 0.7rem + 2px) !important;
            min-height: calc(1.5em + 0.7rem + 2px) !important;
            border: 1px solid #ced4da !important;
            border-radius: 0.375rem !important;
            padding: 0.375rem 0.75rem !important;
            display: flex !important;
            align-items: center !important;
            background-color: #fff !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #495057 !important;
            line-height: 1.5 !important;
            padding-left: 0 !important;
            padding-right: 24px !important;
            width: 100%;
        }

        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #6c757d !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100% !important;
            right: 8px !important;
        }

        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #80bdff !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.12) !important;
        }

        .select2-dropdown {
            border: 1px solid #ced4da !important;
            border-radius: 0.375rem !important;
            overflow: hidden;
            z-index: 9999 !important;
        }

        .po-table-wrap {
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            overflow: hidden;
        }

        .po-items-table {
            margin-bottom: 0;
            font-size: 13px;
        }

        .po-items-table thead th {
            padding: 10px 12px;
            border-bottom-width: 1px;
            background: #f8fafc;
            color: #475569;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .po-items-table tbody td {
            padding: 10px;
            vertical-align: middle;
        }

        .po-item-code-input,
        .po-item-name-display,
        .po-item-unit,
        .qty-input,
        .price-input,
        .subtotal-display {
            width: 100%;
        }

        .po-item-code-input {
            text-transform: uppercase;
        }

        .po-item-name-display,
        .po-item-unit,
        .subtotal-display {
            background: #f8fafc !important;
        }

        .po-item-meta {
            margin-top: 5px;
        }

        .po-item-unit {
            display: block;
            margin-top: 3px;
        }

        .po-notes-toggle {
            margin-top: 14px;
        }

        .po-code-status {
            font-size: 11px;
            margin-top: 4px;
        }

        .po-code-status.text-success {
            color: #198754 !important;
        }

        .po-code-status.text-danger {
            color: #dc3545 !important;
        }

        .po-remarks-cell {
            position: relative;
        }

        .po-remarks-cell .form-control {
            display: none;
        }

        .po-remarks-cell .remarks-toggle {
            position: absolute;
            right: 5px;
            top: 5px;
            color: #64748b;
            cursor: pointer;
            font-size: 12px;
        }

        .po-remarks-cell .remarks-toggle:hover {
            color: #334155;
        }

        .po-create-summary {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 24px;
            margin-top: 14px;
            padding: 12px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            background: #f8fafc;
        }

        .po-summary-label {
            display: block;
            color: #64748b;
            font-size: 11px;
        }

        .po-summary-value {
            display: block;
            margin-top: 2px;
            color: #0f172a;
            font-size: 18px;
            font-weight: 800;
        }

        .po-total-value {
            color: #15803d;
        }

        .po-sticky-action-bar {
            position: sticky;
            bottom: 0;
            z-index: 10;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
            background: #fff;
        }

        .po-action-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .po-guide-card,
        .po-progress-card {
            padding: 15px;
        }

        .po-guide-title {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .po-guide-list {
            display: grid;
            gap: 10px;
            margin: 13px 0 0;
            padding: 0;
            list-style: none;
        }

        .po-guide-list li {
            color: #475569;
            font-size: 12px;
            line-height: 1.45;
        }

        .po-guide-list span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 2px;
            color: #0f172a;
            font-weight: 700;
        }

        .po-guide-list span i,
        .po-guide-note i,
        .po-guide-title i {
            color: #2563eb;
        }

        .po-guide-note {
            margin-top: 13px;
            padding: 10px;
            border-radius: 8px;
            background: #eff6ff;
            color: #475569;
            font-size: 11px;
            line-height: 1.45;
        }

        .po-guide-note i {
            margin-right: 5px;
        }

        .po-checklist {
            display: grid;
            gap: 9px;
            margin-top: 13px;
        }

        .po-check-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #475569;
            font-size: 12px;
        }

        .po-check-item i {
            color: #22c55e;
            font-size: 8px;
        }

        @media (max-width: 1199.98px) {
            .po-create-shell {
                grid-template-columns: 1fr;
            }

            .po-create-side {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            .po-create-shell,
            .po-create-main,
            .po-create-side {
                grid-template-columns: 1fr;
            }

            .po-form-grid {
                grid-template-columns: 1fr;
            }

            .po-field-wide {
                grid-column: auto;
            }

            .po-card-head {
                align-items: flex-start;
                flex-direction: column;
            }

            .po-create-summary {
                align-items: flex-start;
                flex-direction: column;
                gap: 10px;
            }

            .po-sticky-action-bar {
                align-items: stretch;
                flex-direction: column;
            }

            .po-sticky-action-bar > .btn,
            .po-action-group {
                width: 100%;
            }

            .po-action-group .btn {
                flex: 1;
            }
        }
    </style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views\po\create.blade.php ENDPATH**/ ?>