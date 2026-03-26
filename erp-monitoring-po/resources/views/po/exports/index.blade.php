<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <table border="1">
        <tr>
            <th colspan="7">Monitoring Purchase Order</th>
        </tr>
        <tr>
            <td colspan="7">Generated At: {{ $generatedAt->format('d-m-Y H:i:s') }}</td>
        </tr>
        <tr>
            <th>PO Number</th>
            <th>PO Date</th>
            <th>Supplier</th>
            <th>Status</th>
            <th>ETA</th>
            <th>Notes</th>
            <th>Cancel Reason</th>
        </tr>
        @foreach ($rows as $row)
            <tr>
                <td>{{ $row->po_number }}</td>
                <td>{{ $row->po_date }}</td>
                <td>{{ $row->supplier_name }}</td>
                <td>{{ $row->status }}</td>
                <td>{{ $row->eta_date ?: '-' }}</td>
                <td>{{ $row->notes ?: '-' }}</td>
                <td>{{ $row->cancel_reason ?: '-' }}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>
