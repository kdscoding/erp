<?php ($title='Daftar User'); ?>
<?php ($header='Daftar User'); ?>
<?php $__env->startSection('content'); ?>
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title">Daftar User</h3>
    <a href="<?php echo e(route('users.create')); ?>" class="btn btn-primary btn-sm">Tambah User</a>
  </div>
  <div class="card-body table-responsive">
    <table class="table table-striped data-table">
      <thead>
        <tr>
          <th>Nama</th>
          <th>NIK</th>
          <th>Email</th>
          <th>Role</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <tr>
          <td><?php echo e($user->name); ?></td>
          <td><?php echo e($user->nik); ?></td>
          <td><?php echo e($user->email); ?></td>
          <td><?php echo e($user->roles->pluck('name')->join(', ') ?: '-'); ?></td>
          <td>
            <span class="badge <?php echo e($user->is_active ? 'bg-success' : 'bg-secondary'); ?>">
              <?php echo e($user->is_active ? 'Aktif' : 'Nonaktif'); ?>

            </span>
          </td>
          <td class="text-nowrap">
            <form method="POST" action="<?php echo e(route('users.toggle-status', $user)); ?>" class="d-inline">
              <?php echo csrf_field(); ?>
              <?php echo method_field('PATCH'); ?>
              <button class="btn btn-sm <?php echo e($user->is_active ? 'btn-outline-danger' : 'btn-outline-success'); ?>">
                <?php echo e($user->is_active ? 'Nonaktifkan' : 'Aktifkan'); ?>

              </button>
            </form>
            <a href="<?php echo e(route('users.edit', $user)); ?>" class="btn btn-sm btn-outline-primary">Kelola</a>
          </td>
        </tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views\settings\users\index.blade.php ENDPATH**/ ?>