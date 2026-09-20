<div class="mb-3 text-muted">
    Akun tidak akan dihapus permanen. Sistem akan menonaktifkan akun ini agar tidak bisa digunakan untuk login kembali.
</div>

<form method="POST" action="<?php echo e(route('profile.destroy')); ?>" class="row g-3">
    <?php echo csrf_field(); ?>
    <?php echo method_field('DELETE'); ?>

    <div class="col-md-6">
        <label for="password" class="form-label">Konfirmasi Password</label>
        <input id="password" name="password" type="password" class="form-control form-control-sm" placeholder="Masukkan password untuk konfirmasi">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->userDeletion->has('password')): ?><div class="text-danger small mt-1"><?php echo e($errors->userDeletion->first('password')); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="col-12 d-flex justify-content-end">
        <button class="btn btn-danger btn-sm">Nonaktifkan Akun</button>
    </div>
</form>
<?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views/profile/partials/delete-user-form.blade.php ENDPATH**/ ?>