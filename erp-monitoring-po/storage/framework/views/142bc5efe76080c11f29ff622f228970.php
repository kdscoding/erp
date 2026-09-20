<?php ($title='Edit Unit'); ?>
<?php ($header='Edit Unit'); ?>
<?php ($headerSubtitle='Gunakan kode singkat dan nama unit yang konsisten.'); ?>

<?php $__env->startSection('content'); ?>
<form method="POST" action="<?php echo e(route('units.update', $unit->id)); ?>">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>
    <?php if (isset($component)) { $__componentOriginalc3e6ca54352aeac0889a0cb7aed9009e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc3e6ca54352aeac0889a0cb7aed9009e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.master-edit-layout','data' => ['title' => 'Form Edit Unit','subtitle' => 'Gunakan kode singkat dan nama unit yang konsisten agar tidak terjadi variasi satuan di item atau transaksi.','backRoute' => route('units.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('master-edit-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Form Edit Unit','subtitle' => 'Gunakan kode singkat dan nama unit yang konsisten agar tidak terjadi variasi satuan di item atau transaksi.','back-route' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('units.index'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <div class="col-md-4"><label class="form-label">Kode Unit</label><input class="form-control form-control-sm" name="unit_code" value="<?php echo e(old('unit_code', $unit->unit_code)); ?>" required></div>
        <div class="col-md-8"><label class="form-label">Nama Unit</label><input class="form-control form-control-sm" name="unit_name" value="<?php echo e(old('unit_name', $unit->unit_name)); ?>" required></div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc3e6ca54352aeac0889a0cb7aed9009e)): ?>
<?php $attributes = $__attributesOriginalc3e6ca54352aeac0889a0cb7aed9009e; ?>
<?php unset($__attributesOriginalc3e6ca54352aeac0889a0cb7aed9009e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc3e6ca54352aeac0889a0cb7aed9009e)): ?>
<?php $component = $__componentOriginalc3e6ca54352aeac0889a0cb7aed9009e; ?>
<?php unset($__componentOriginalc3e6ca54352aeac0889a0cb7aed9009e); ?>
<?php endif; ?>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views\masters\units\edit.blade.php ENDPATH**/ ?>