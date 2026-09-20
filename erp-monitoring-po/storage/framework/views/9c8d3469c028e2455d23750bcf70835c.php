<?php ($title = 'Edit Kategori Item'); ?>
<?php ($header = 'Edit Kategori Item'); ?>
<?php ($headerSubtitle = 'Kategori dipakai untuk pengelompokan item, filter, dan pelaporan.'); ?>

<?php $__env->startSection('content'); ?>
<form method="POST" action="<?php echo e(route('item-categories.update', $category->id)); ?>">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>
    <?php if (isset($component)) { $__componentOriginalc3e6ca54352aeac0889a0cb7aed9009e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc3e6ca54352aeac0889a0cb7aed9009e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.master-edit-layout','data' => ['title' => 'Form Edit Kategori','subtitle' => 'Kategori dipakai untuk pengelompokan item, filter, dan pelaporan. Jaga istilahnya tetap ringkas dan konsisten.','backRoute' => route('item-categories.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('master-edit-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Form Edit Kategori','subtitle' => 'Kategori dipakai untuk pengelompokan item, filter, dan pelaporan. Jaga istilahnya tetap ringkas dan konsisten.','back-route' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('item-categories.index'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <div class="col-md-4">
            <label class="form-label">Kode Kategori</label>
            <input class="form-control form-control-sm" name="category_code" value="<?php echo e(old('category_code', $category->category_code)); ?>" required>
        </div>
        <div class="col-md-8">
            <label class="form-label">Nama Kategori</label>
            <input class="form-control form-control-sm" name="category_name" value="<?php echo e(old('category_name', $category->category_name)); ?>" required>
        </div>
        <div class="col-12">
            <label class="form-label">Deskripsi</label>
            <textarea class="form-control form-control-sm" name="description" rows="3"><?php echo e(old('description', $category->description)); ?></textarea>
        </div>
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

<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views\masters\item-categories\edit.blade.php ENDPATH**/ ?>