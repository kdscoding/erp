<?php
    $entity = 'supplier';
    $title = entity_label($entity, 'plural');
    $header = $title;
    $headerSubtitle = label($entity, 'entity.description');
?>

<?php if (isset($component)) { $__componentOriginal91a231a9270579fa1ae9246bd51fb785 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91a231a9270579fa1ae9246bd51fb785 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.page-header','data' => ['entity' => $entity,'actions' => [
        ['label' => action_label($entity, 'create'), 'url' => route('suppliers.create'), 'icon' => 'fas fa-plus', 'class' => 'btn btn-primary btn-sm'],
    ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['entity' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($entity),'actions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['label' => action_label($entity, 'create'), 'url' => route('suppliers.create'), 'icon' => 'fas fa-plus', 'class' => 'btn btn-primary btn-sm'],
    ])]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal91a231a9270579fa1ae9246bd51fb785)): ?>
<?php $attributes = $__attributesOriginal91a231a9270579fa1ae9246bd51fb785; ?>
<?php unset($__attributesOriginal91a231a9270579fa1ae9246bd51fb785); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal91a231a9270579fa1ae9246bd51fb785)): ?>
<?php $component = $__componentOriginal91a231a9270579fa1ae9246bd51fb785; ?>
<?php unset($__componentOriginal91a231a9270579fa1ae9246bd51fb785); ?>
<?php endif; ?>

<?php if (isset($component)) { $__componentOriginal14f469cfc51ebc3cb5d7fb0ffa2702ed = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal14f469cfc51ebc3cb5d7fb0ffa2702ed = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.filter-bar','data' => ['module' => $entity,'context' => 'default','inline' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.filter-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['module' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($entity),'context' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('default'),'inline' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal14f469cfc51ebc3cb5d7fb0ffa2702ed)): ?>
<?php $attributes = $__attributesOriginal14f469cfc51ebc3cb5d7fb0ffa2702ed; ?>
<?php unset($__attributesOriginal14f469cfc51ebc3cb5d7fb0ffa2702ed); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal14f469cfc51ebc3cb5d7fb0ffa2702ed)): ?>
<?php $component = $__componentOriginal14f469cfc51ebc3cb5d7fb0ffa2702ed; ?>
<?php unset($__componentOriginal14f469cfc51ebc3cb5d7fb0ffa2702ed); ?>
<?php endif; ?>

<x-ui.data-table
    :entity="$entity"
    :view="'index'"
    :rows="$suppliers"
    :emptyState="[
        'icon' => '📦',
        'title' => label($entity, 'empty.title', 'Belum ada data supplier'),
        'subtitle' => label($entity, 'empty.subtitle', 'Mulai tambah supplier baru untuk melihat data di sini.'),
        'action' => ['label' => action_label($entity, 'create'), 'url' => route('suppliers.create'), 'class' => 'btn btn-primary btn-sm px-4 mt-2'],
    ]"
    :rowClass="function ($row) { return !$row->status ? 'row-inactive' : ''; }"
    :rowId="'id'"
    :columns="[
        'supplier_code' => ['label' => column_label($entity, 'supplier_code'), 'render' => function ($row) { return '<div class=\"doc-number\">' . $row->supplier_code . '</div>'; }],
        'supplier_name' => ['label' => column_label($entity, 'supplier_name'), 'render' => function ($row) { return '<div class=\"supplier-name\">' . $row->supplier_name . '</div>'; }],
        'updated_at' => ['label' => column_label($entity, 'updated_at'), 'render' => function ($row) { return $row->updated_at ? '<small class=\"text-muted\">' . \Carbon\Carbon::parse($row->updated_at)->diffForHumans() . '</small>' : '<span class=\"text-muted\">-</span>'; }],
        'status' => ['label' => column_label($entity, 'status'), 'render' => function ($row) {
            return '<form action=\"' . route('suppliers.toggle-status', $row->id) . '\" method=\"POST\" class=\"d-inline status-toggle-form\">'
                . csrf_field()
                . method_field('PATCH')
                . '<label class=\"status-toggle\">
                    <input type=\"checkbox\" name=\"status\" ' . ($row->status ? 'checked' : '') . ' onchange=\"this.form.submit()\">
                    <span class=\"toggle-slider\"></span>
                </label>
                <span class=\"status-text ' . ($row->status ? 'text-success' : 'text-muted') . '\">
                    ' . ($row->status ? 'Aktif' : 'Nonaktif') . '
                </span>
            </form>';
        }],
        'actions' => ['label' => column_label($entity, 'actions'), 'tdAttributes' => ['class' => 'text-end'], 'render' => function ($row) {
            return '<div class=\"action-stack\">
                <a href=\"' . route('suppliers.edit', $row->id) . '\" class=\"btn btn-sm btn-outline-primary\" title=\"' . action_label($entity, 'edit') . '\">✏️</a>
            </div>';
        }],
    ]"
/>

<?php if (isset($component)) { $__componentOriginal1d835d4f0562cefb78be03aea9d0ee19 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1d835d4f0562cefb78be03aea9d0ee19 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.fab','data' => ['entity' => $entity]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.fab'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['entity' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($entity)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1d835d4f0562cefb78be03aea9d0ee19)): ?>
<?php $attributes = $__attributesOriginal1d835d4f0562cefb78be03aea9d0ee19; ?>
<?php unset($__attributesOriginal1d835d4f0562cefb78be03aea9d0ee19); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1d835d4f0562cefb78be03aea9d0ee19)): ?>
<?php $component = $__componentOriginal1d835d4f0562cefb78be03aea9d0ee19; ?>
<?php unset($__componentOriginal1d835d4f0562cefb78be03aea9d0ee19); ?>
<?php endif; ?><?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views/suppliers/index.blade.php ENDPATH**/ ?>