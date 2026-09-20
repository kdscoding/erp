<?php ($title = 'Tambah Unit'); ?>
<?php ($header = 'Tambah Unit'); ?>
<?php ($headerSubtitle = 'Input data unit baru.'); ?>

<?php $__env->startSection('content'); ?>
    <div class="page-shell">
        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Form Unit</h3>
                </div>
            </div>

            <div class="ui-surface-body">
                <div class="form-wrapper">
                    <form method="POST" action="<?php echo e(route('units.store')); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="form-group">
                            <label class="field-label">Kode Unit</label>
                            <input class="form-control form-control-sm" name="unit_code"
                                placeholder="Mis. PCS" value="<?php echo e(old('unit_code')); ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="field-label">Nama Unit</label>
                            <input class="form-control form-control-sm" name="unit_name"
                                placeholder="Nama lengkap unit" value="<?php echo e(old('unit_name')); ?>" required>
                        </div>

                        <div class="form-actions">
                            <a href="<?php echo e(route('units.index')); ?>" class="btn btn-light btn-sm">Batal</a>
                            <button type="submit" class="btn btn-primary btn-sm px-5">Simpan Unit</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
    <style>
        .form-wrapper {
            max-width: 540px;
            margin: 24px auto;
            padding: 24px;
        }
        .form-wrapper .form-group {
            margin-bottom: 20px;
        }
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid var(--lemon-line, #dfe6b8);
        }
    </style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views\masters\units\create.blade.php ENDPATH**/ ?>