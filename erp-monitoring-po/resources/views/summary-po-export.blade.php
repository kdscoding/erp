<table>
    <tr><th colspan="5">Summary Outstanding PO</th></tr>
    <tr>
        <th>Outstanding PO</th>
        <th>Outstanding Item</th>
        <th>Total Order</th>
        <th>Total Pengiriman</th>
        <th>Total Outstanding</th>
    </tr>
    <tr>
        <td>{{ $summaryMetrics['outstanding_po'] ?? 0 }}</td>
        <td>{{ $summaryMetrics['outstanding_item'] ?? 0 }}</td>
        <td>{{ $summaryMetrics['total_order_qty'] ?? 0 }}</td>
        <td>{{ $summaryMetrics['total_shipped_qty'] ?? 0 }}</td>
        <td>{{ $summaryMetrics['total_outstanding_qty'] ?? 0 }}</td>
    </tr>
</table>

<table>
    <tr><th colspan="8">Outstanding per PO</th></tr>
    <tr>
        <th>PO</th>
        <th>Supplier</th>
        <th>Tanggal PO</th>
        <th>ETA</th>
        <th>Item Outstanding</th>
        <th>Total Order</th>
        <th>Total Pengiriman</th>
        <th>Outstanding</th>
    </tr>
    @foreach ($outstandingPoRows as $row)
        <tr>
            <td>{{ $row->po_number }}</td>
            <td>{{ $row->supplier_name }}</td>
            <td>{{ $row->po_date }}</td>
            <td>{{ $row->eta_date }}</td>
            <td>{{ $row->outstanding_item_count }}</td>
            <td>{{ $row->total_order_qty }}</td>
            <td>{{ $row->total_shipped_qty }}</td>
            <td>{{ $row->total_outstanding_qty }}</td>
        </tr>
    @endforeach
</table>
