@props([
    'status' => '',
    'scope' => 'item',
])

@php
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
@endphp

@if ($helpText)
    <div class="small text-muted mt-1">{{ $helpText }}</div>
@endif
