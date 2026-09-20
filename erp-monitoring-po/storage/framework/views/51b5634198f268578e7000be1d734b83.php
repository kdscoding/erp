<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'status' => '',
    'scope' => 'item',
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
    'status' => '',
    'scope' => 'item',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $helpText = match ($scope) {
        'item' => \App\Support\PurchaseOrderItemStatusResolver::statusHelpText((string) $status),
        'po' => match ((string) $status) {
            \App\Support\DocumentTermCodes::PO_ISSUED => 'PO sudah dikeluarkan, menunggu proses.',
            \App\Support\DocumentTermCodes::PO_OPEN => 'PO aktif, belum selesai diterima.',
            \App\Support\DocumentTermCodes::PO_LATE => 'PO melewati ETD, belum selesai.',
            \App\Support\DocumentTermCodes::PO_CLOSED => 'PO sudah final, semua item selesai diterima.',
            \App\Support\DocumentTermCodes::PO_CANCELLED => 'PO dibatalkan.',
            'Full' => 'Semua item sudah diterima penuh.',
            'Partial' => 'Beberapa item sudah diterima sebagian.',
            'Delayed' => 'PO tertunda, belum ada proses receiving.',
            default => null,
        },
        default => null,
    };
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($helpText): ?>
    <div class="small text-muted mt-1"><?php echo e($helpText); ?></div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views/components/status-help.blade.php ENDPATH**/ ?>