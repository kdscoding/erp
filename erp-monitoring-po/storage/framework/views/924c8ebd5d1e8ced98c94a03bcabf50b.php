<?php ($title = 'Tambah Supplier'); ?>
<?php ($header = 'Tambah Supplier'); ?>
<?php ($headerSubtitle = 'Input data supplier baru.'); ?>

<?php $__env->startSection('content'); ?>
    <div class="page-shell">
        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Form Supplier</h3>
                </div>
            </div>

            <div class="ui-surface-body">
                <div class="form-wrapper">
                    <form method="POST" action="<?php echo e(route('suppliers.store')); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="form-group">
                            <label class="field-label">Kode Supplier</label>
                            <input class="form-control form-control-sm" name="supplier_code"
                                placeholder="Kode unik" value="<?php echo e(old('supplier_code')); ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="field-label">Nama Supplier</label>
                            <input class="form-control form-control-sm" name="supplier_name"
                                placeholder="Nama perusahaan supplier" value="<?php echo e(old('supplier_name')); ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="field-label">Alamat</label>
                            <input class="form-control form-control-sm" name="address"
                                placeholder="Alamat lengkap supplier"
                                value="<?php echo e(old('address')); ?>">
                        </div>

                        <div class="form-group">
                            <label class="field-label">Telepon</label>
                            <input class="form-control form-control-sm" name="phone"
                                placeholder="No. telepon supplier"
                                value="<?php echo e(old('phone')); ?>">
                        </div>

                        <div class="form-group">
                            <label class="field-label">Email</label>
                            <input class="form-control form-control-sm" name="email"
                                type="email"
                                placeholder="Email supplier"
                                value="<?php echo e(old('email')); ?>">
                        </div>

                        <div class="form-group">
                            <label class="field-label">Kontak</label>
                            <input class="form-control form-control-sm" name="contact_person"
                                placeholder="Nama PIC supplier"
                                value="<?php echo e(old('contact_person')); ?>">
                        </div>

                        <div class="form-actions">
                            <a href="<?php echo e(route('suppliers.index')); ?>" class="btn btn-light btn-sm">Batal</a>
                            <button type="submit" class="btn btn-primary btn-sm px-5">Simpan Supplier</button>
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

<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views\masters\suppliers\create.blade.php ENDPATH**/ ?>