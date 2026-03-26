<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <table border="1">
        <tr>
            <th colspan="8">Monitoring Summary Per Purchase Order</th>
        </tr>
        <tr>
            <td colspan="8">Generated At: {{ $generatedAt->format('d-m-Y H:i:s') }}</td>
        </tr>
        <tr>
            <th>PO Number</th>
            <th>Supplier</th>
            <th>PO Header Status</th>
            <th>Waiting</th>
            <th>Confirmed</th>
            <th>Late</th>
            <th>Partial</th>
            <th>Closed</th>
            <th>Force Closed</th>
        </tr>
        @foreach ($poMonitoringSummary as $summary)
            <tr>
                <td>{{ $summary->po_number }}</td>
                <td>{{ $summary->supplier_name }}</td>
                <td>{{ $summary->po_status }}</td>
                <td>{{ $summary->waiting_items }}</td>
                <td>{{ $summary->confirmed_items }}</td>
                <td>{{ $summary->late_items }}</td>
                <td>{{ $summary->partial_items }}</td>
                <td>{{ $summary->closed_items }}</td>
                <td>{{ $summary->force_closed_items }}</td>
            </tr>
        @endforeach
    </table>

    <br>

    <table border="1">
        <tr>
            <th colspan="10">Monitoring Detail Per Item</th>
        </tr>
        <tr>
            <th>PO Number</th>
            <th>Supplier</th>
            <th>Item Code</th>
            <th>Item Name</th>
            <th>Ordered Qty</th>
            <th>Received Qty</th>
            <th>Outstanding Qty</th>
            <th>ETD</th>
            <th>Status Item</th>
            <th>Keterangan</th>
        </tr>
        @foreach ($itemMonitoringList as $item)
            <tr>
                <td>{{ $item->po_number }}</td>
                <td>{{ $item->supplier_name }}</td>
                <td>{{ $item->item_code }}</td>
                <td>{{ $item->item_name }}</td>
                <td>{{ \App\Support\NumberFormatter::trim($item->ordered_qty) }}</td>
                <td>{{ \App\Support\NumberFormatter::trim($item->received_qty) }}</td>
                <td>{{ \App\Support\NumberFormatter::trim($item->outstanding_qty) }}</td>
                <td>{{ $item->etd_date ?: '-' }}</td>
                <td>{{ $item->monitoring_status }}</td>
                <td>{{ $item->monitoring_note }}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>
