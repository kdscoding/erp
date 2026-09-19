@php
    $columns = [
        'Item Code', 'Item Name', 'Ordered Qty', 'Received Qty', 'Outstanding Qty',
        'Unit', 'Tanggal', 'Deskripsi Aktivitas', 'Qty Order', 'Qty Masuk',
        'Sisa (OS)', 'No Shipment', 'No GR', 'Status',
    ];
    echo implode("\t", $columns) . "\n";
    foreach ($timeline as $entry) {
        $values = [
            $item->item_code,
            $item->item_name,
            \App\Support\NumberFormatter::trim($item->ordered_qty),
            \App\Support\NumberFormatter::trim($item->received_qty),
            \App\Support\NumberFormatter::trim($item->outstanding_qty),
            $item->unit_name,
            $entry['date'],
            $entry['description'],
            $entry['ordered_qty'],
            $entry['received_qty'],
            $entry['outstanding_qty'],
            $entry['shipment_number'],
            $entry['gr_number'],
            $entry['status'],
        ];
        echo implode("\t", array_map(fn($v) => '"' . str_replace('"', '""', (string) $v) . '"', $values)) . "\n";
    }
@endphp
