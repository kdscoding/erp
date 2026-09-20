<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'status' => '',
    'scope' => 'po', // po | item | shipment | gr
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
    'scope' => 'po', // po | item | shipment | gr
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $status = trim((string) $status);

    $group = match ($scope) {
        'item' => \App\Support\DocumentTermCodes::GROUP_PO_ITEM_STATUS,
        'shipment' => \App\Support\DocumentTermCodes::GROUP_SHIPMENT_STATUS,
        'gr' => \App\Support\DocumentTermCodes::GROUP_GOODS_RECEIPT_STATUS,
        default => \App\Support\DocumentTermCodes::GROUP_PO_STATUS,
    };

    $classes = \App\Support\DocumentTermStatus::badgeClasses($group, $status, 'bg-secondary text-white');
    $label = \App\Support\DocumentTermStatus::label($group, $status, $status !== '' ? $status : '-');
    $internalCode = \App\Support\DocumentTermStatus::internalCode($group, $status);
    $legacyValue = \App\Support\DocumentTermStatus::legacyValue($group, $status);
?>

<span
    data-status-code="<?php echo e($internalCode ?? ''); ?>"
    data-status-term="<?php echo e($legacyValue ?? ''); ?>"
    <?php echo e($attributes->merge(['class' => 'badge ' . $classes])); ?>

>
    <?php echo e($label); ?>

</span>
<?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views\components\status-badge.blade.php ENDPATH**/ ?>