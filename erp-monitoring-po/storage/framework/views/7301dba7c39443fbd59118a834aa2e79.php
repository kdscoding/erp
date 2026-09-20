<?php ($title='Tambah User'); ?>
<?php ($header='Tambah User'); ?>
<?php $__env->startSection('content'); ?>
<div class="card card-primary card-outline">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title">Form Tambah User</h3>
    <a href="<?php echo e(route('users.index')); ?>" class="btn btn-sm btn-outline-secondary">Kembali ke Daftar</a>
  </div>
  <div class="card-body">
    <form method="POST" action="<?php echo e(route('users.store')); ?>">
      <?php echo csrf_field(); ?>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Nama</label>
          <input type="text" name="name" value="<?php echo e(old('name')); ?>" class="form-control form-control-sm" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">NIK</label>
          <input type="text" name="nik" value="<?php echo e(old('nik')); ?>" class="form-control form-control-sm" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" value="<?php echo e(old('email')); ?>" class="form-control form-control-sm" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Role</label>
          <select name="role_slug" class="form-select form-select-sm" required>
            <option value="">Pilih role</option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
              <option value="<?php echo e($role->slug); ?>" <?php if(old('role_slug') === $role->slug): echo 'selected'; endif; ?>><?php echo e($role->name); ?></option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
          </select>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Password Awal</label>
          <input type="password" name="password" class="form-control form-control-sm" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Konfirmasi Password Awal</label>
          <input type="password" name="password_confirmation" class="form-control form-control-sm" required>
        </div>
      </div>
      <button class="btn btn-primary btn-sm">Simpan User</button>
    </form>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views\settings\users\create.blade.php ENDPATH**/ ?>