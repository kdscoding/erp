<form id="send-verification" method="POST" action="<?php echo e(route('verification.send')); ?>">
    <?php echo csrf_field(); ?>
</form>

<form method="POST" action="<?php echo e(route('profile.update')); ?>" class="row g-3">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PATCH'); ?>

    <div class="col-12">
        <label for="name" class="form-label">Nama</label>
        <input id="name" name="name" type="text" class="form-control form-control-sm" value="<?php echo e(old('name', $user->name)); ?>" required autofocus autocomplete="name">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small mt-1"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="col-12">
        <label for="email" class="form-label">Email</label>
        <input id="email" name="email" type="email" class="form-control form-control-sm" value="<?php echo e(old('email', $user->email)); ?>" required autocomplete="username">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small mt-1"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail()): ?>
            <div class="alert alert-warning mt-3 mb-0">
                Email Anda belum terverifikasi.
                <button form="send-verification" class="btn btn-link btn-sm p-0 align-baseline">Kirim ulang verifikasi</button>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status') === 'verification-link-sent'): ?>
                    <div class="small text-success mt-2">Link verifikasi baru sudah dikirim ke email Anda.</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="col-12 d-flex justify-content-end">
        <button class="btn btn-primary btn-sm">Simpan Profil</button>
    </div>
</form>
<?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views/profile/partials/update-profile-information-form.blade.php ENDPATH**/ ?>