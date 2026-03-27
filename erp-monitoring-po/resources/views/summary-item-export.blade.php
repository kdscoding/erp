<table>
    <tr><th colspan="5">Summary Outstanding Item</th></tr>
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
    <tr><th colspan="7">Outstanding per Item</th></tr>
    <tr>
        <th>PO</th>
        <th>Item</th>
        <th>Supplier</th>
        <th>ETD</th>
        <th>Order</th>
        <th>Pengiriman</th>
        <th>Outstanding</th>
    </tr>
    @foreach ($outstandingItemRows as $row)
        <tr>
            <td>{{ $row->po_number }}</td>
            <td>{{ $row->item_code }} - {{ $row->item_name }}</td>
            <td>{{ $row->supplier_name }}</td>
            <td>{{ $row->etd_date }}</td>
            <td>{{ $row->ordered_qty }}</td>
            <td>{{ $row->received_qty }}</td>
            <td>{{ $row->outstanding_qty }}</td>
        </tr>
    @endforeach
</table>
