<?php ($title='Kelola User'); ?>
<?php ($header='Kelola User'); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
  <div class="col-lg-6">
    <div class="card card-primary card-outline">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Data User</h3>
        <a href="<?php echo e(route('users.index')); ?>" class="btn btn-sm btn-outline-secondary">Kembali ke Daftar</a>
      </div>
      <div class="card-body">
        <form method="POST" action="<?php echo e(route('users.update', $user)); ?>">
          <?php echo csrf_field(); ?>
          <?php echo method_field('PUT'); ?>
          <div class="mb-3">
            <label class="form-label">Nama</label>
            <input type="text" name="name" value="<?php echo e(old('name', $user->name)); ?>" class="form-control form-control-sm" required>
          </div>
          <div class="mb-3">
            <label class="form-label">NIK</label>
            <input type="text" name="nik" value="<?php echo e(old('nik', $user->nik)); ?>" class="form-control form-control-sm" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="<?php echo e(old('email', $user->email)); ?>" class="form-control form-control-sm" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role_slug" class="form-select form-select-sm" required>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <option value="<?php echo e($role->slug); ?>" <?php if(old('role_slug', $user->primaryRoleSlug()) === $role->slug): echo 'selected'; endif; ?>><?php echo e($role->name); ?></option>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>
          </div>
          <button class="btn btn-primary btn-sm">Update Data User</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card card-warning card-outline">
      <div class="card-header">
        <h3 class="card-title">Reset Password</h3>
      </div>
      <div class="card-body">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendingResetRequest): ?>
          <div class="alert alert-warning">
            <div><strong>Request pending:</strong> <?php echo e(\Carbon\Carbon::parse($pendingResetRequest->requested_at)->format('d-m-Y H:i')); ?></div>
            <div class="mt-2"><strong>Keterangan user:</strong></div>
            <div><?php echo e($pendingResetRequest->request_note); ?></div>
          </div>
          <form method="POST" action="<?php echo e(route('users.reset-password', $user)); ?>">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            <div class="mb-3">
              <label class="form-label">Password Baru</label>
              <input type="password" name="password" class="form-control form-control-sm" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Konfirmasi Password Baru</label>
              <input type="password" name="password_confirmation" class="form-control form-control-sm" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Catatan Tindak Lanjut Admin</label>
              <textarea name="admin_note" class="form-control form-control-sm" rows="3" placeholder="Contoh: reset diproses setelah verifikasi identitas user." required><?php echo e(old('admin_note')); ?></textarea>
            </div>
            <button class="btn btn-warning btn-sm">Proses Reset Password</button>
          </form>
        <?php else: ?>
          <div class="alert alert-secondary mb-0">
            Belum ada request reset password yang pending dari user ini.
          </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views\settings\users\edit.blade.php ENDPATH**/ ?>