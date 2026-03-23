@props([
    'status' => '',
    'scope' => 'po', // po | item | shipment | gr
])

@php
    $status = trim((string) $status);

    $group = match ($scope) {
        'item' => \App\Support\DocumentTermCodes::GROUP_PO_ITEM_STATUS,
        'shipment' => \App\Support\DocumentTermCodes::GROUP_SHIPMENT_STATUS,
        'gr' => \App\Support\DocumentTermCodes::GROUP_GOODS_RECEIPT_STATUS,
        default => \App\Support\DocumentTermCodes::GROUP_PO_STATUS,
    };

    $classes = \App\Support\DocumentTermStatus::badgeClasses($group, $status, 'bg-secondary text-white');
    $label = \App\Support\DocumentTermStatus::label($group, $status, $status !== '' ? $status : '-');
@endphp

<span {{ $attributes->merge(['class' => 'badge ' . $classes]) }}>
    {{ $label }}
</span>
