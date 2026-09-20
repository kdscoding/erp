<form method="POST" action="<?php echo e(route('password.update')); ?>" class="row g-3">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>

    <div class="col-12">
        <label for="update_password_current_password" class="form-label">Password Saat Ini</label>
        <input id="update_password_current_password" name="current_password" type="password" class="form-control form-control-sm" autocomplete="current-password">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->updatePassword->has('current_password')): ?><div class="text-danger small mt-1"><?php echo e($errors->updatePassword->first('current_password')); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="col-12">
        <label for="update_password_password" class="form-label">Password Baru</label>
        <input id="update_password_password" name="password" type="password" class="form-control form-control-sm" autocomplete="new-password">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->updatePassword->has('password')): ?><div class="text-danger small mt-1"><?php echo e($errors->updatePassword->first('password')); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="col-12">
        <label for="update_password_password_confirmation" class="form-label">Konfirmasi Password Baru</label>
        <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control form-control-sm" autocomplete="new-password">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->updatePassword->has('password_confirmation')): ?><div class="text-danger small mt-1"><?php echo e($errors->updatePassword->first('password_confirmation')); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="col-12 d-flex justify-content-end">
        <button class="btn btn-primary btn-sm">Simpan Password</button>
    </div>
</form>
<?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views/profile/partials/update-password-form.blade.php ENDPATH**/ ?>