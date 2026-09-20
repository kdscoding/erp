<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'stats' => [],
    'module' => null,
    'context' => 'default',
    'class' => '',
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'stats' => [],
    'module' => null,
    'context' => 'default',
    'class' => '',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$module = $module ?? (request()->route()->getPrefix() ?? 'default');
$resolvedStats = $stats ?: \App\Support\LabelRegistry::get($module, 'summary_stats', []);
$contextStats = $resolvedStats[$context] ?? $resolvedStats;
?>

<div class="summary-chips <?php echo e($class); ?>">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $contextStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <?php
            $label = $stat['label'] ?? $key;
            $value = $stat['value'] ?? 0;
            $color = $stat['color'] ?? 'primary';
            $url = $stat['url'] ?? null;
        ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($url): ?>
            <a href="<?php echo e($url); ?>" class="summary-chip summary-chip-<?php echo e($color); ?>">
        <?php else: ?>
            <div class="summary-chip summary-chip-<?php echo e($color); ?>">
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="summary-chip-label"><?php echo e($label); ?></div>
                <div class="summary-chip-value"><?php echo e($value); ?></div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($url): ?>
            </a>
        <?php else: ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
</div>

<style>
    .summary-chips {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }

    .summary-chip {
        min-width: 120px;
        padding: 12px 16px;
        border-radius: 10px;
        border: 1px solid var(--po-border, #e2e8f0);
        background: #f8fafc;
        text-decoration: none;
        color: #334155;
        transition: all 0.15s ease;
        flex: 1 0 auto;
    }

    .summary-chip:hover {
        border-color: #93c5fd;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.08);
        transform: translateY(-1px);
    }

    .summary-chip-label {
        display: block;
        font-size: 11px;
        line-height: 1.25;
        color: var(--po-muted, #64748b);
        margin-bottom: 4px;
    }

    .summary-chip-value {
        display: block;
        font-size: 20px;
        line-height: 1.1;
        font-weight: 700;
        color: #0f172a;
    }

    .summary-chip-primary { border-left: 3px solid #2563eb; }
    .summary-chip-success { border-left: 3px solid #16a34a; }
    .summary-chip-warning { border-left: 3px solid #d97706; }
    .summary-chip-danger { border-left: 3px solid #dc2626; }
    .summary-chip-secondary { border-left: 3px solid #64748b; }
</style><?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views\components\ui\summary-chips.blade.php ENDPATH**/ ?>