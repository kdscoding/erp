<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <table border="1">
        <tr>
            <th colspan="2">Purchase Order Detail</th>
        </tr>
        <tr><td>Generated At</td><td>{{ $generatedAt->format('d-m-Y H:i:s') }}</td></tr>
        <tr><td>PO Number</td><td>{{ $po->po_number }}</td></tr>
        <tr><td>PO Date</td><td>{{ $po->po_date }}</td></tr>
        <tr><td>Supplier</td><td>{{ $po->supplier_name }}</td></tr>
        <tr><td>Status</td><td>{{ $po->status }}</td></tr>
        <tr><td>Notes</td><td>{{ $po->notes ?: '-' }}</td></tr>
    </table>

    <br>

    <table border="1">
        <tr>
            <th colspan="8">Item Monitoring</th>
        </tr>
        <tr>
            <th>Item Code</th>
            <th>Item Name</th>
            <th>Ordered Qty</th>
            <th>Received Qty</th>
            <th>Outstanding Qty</th>
            <th>Item Status</th>
            <th>ETD</th>
            <th>Cancel Reason</th>
        </tr>
        @foreach ($items as $item)
            <tr>
                <td>{{ $item->item_code }}</td>
                <td>{{ $item->item_name }}</td>
                <td>{{ \App\Support\NumberFormatter::trim($item->ordered_qty) }} {{ $item->unit_name }}</td>
                <td>{{ \App\Support\NumberFormatter::trim($item->received_qty) }} {{ $item->unit_name }}</td>
                <td>{{ \App\Support\NumberFormatter::trim($item->outstanding_qty) }} {{ $item->unit_name }}</td>
                <td>{{ $item->monitoring_status }}</td>
                <td>{{ $item->etd_date ?: '-' }}</td>
                <td>{{ $item->cancel_reason ?: '-' }}</td>
            </tr>
        @endforeach
    </table>

    <br>

    <table border="1">
        <tr>
            <th colspan="9">Tracking Shipment / GR</th>
        </tr>
        <tr>
            <th>Item Code</th>
            <th>Tanggal</th>
            <th>Deskripsi Aktivitas</th>
            <th>Qty Order</th>
            <th>Qty Masuk</th>
            <th>Sisa (OS)</th>
            <th>No Shipment</th>
            <th>No GR</th>
            <th>Status</th>
        </tr>
        @foreach ($items as $item)
            @php($runningReceivedQty = 0)
            @php($initialTimelineStatus = match (true) {
                $item->monitoring_status === \App\Support\DocumentTermCodes::ITEM_CANCELLED => \App\Support\DocumentTermCodes::ITEM_CANCELLED,
                $item->monitoring_status === \App\Support\DocumentTermCodes::ITEM_FORCE_CLOSED => \App\Support\DocumentTermCodes::ITEM_FORCE_CLOSED,
                $item->etd_date && \Carbon\Carbon::parse($item->etd_date)->isPast() && (float) $item->received_qty <= 0 => \App\Support\DocumentTermCodes::ITEM_LATE,
                $item->etd_date => \App\Support\DocumentTermCodes::ITEM_CONFIRMED,
                default => \App\Support\DocumentTermCodes::ITEM_WAITING,
            })
            <tr>
                <td>{{ $item->item_code }}</td>
                <td>{{ \Carbon\Carbon::parse($po->po_date)->format('d/m/Y') }}</td>
                <td>PO Created</td>
                <td>{{ \App\Support\NumberFormatter::trim($item->ordered_qty) }}</td>
                <td>0</td>
                <td>{{ \App\Support\NumberFormatter::trim($item->ordered_qty) }}</td>
                <td>-</td>
                <td>-</td>
                <td>{{ $initialTimelineStatus }}</td>
            </tr>
            @foreach ($item->tracking_rows as $tracking)
                @php($shipmentDate = $tracking->shipment_date ? \Carbon\Carbon::parse($tracking->shipment_date)->format('d/m/Y') : '-')
                @php($shipmentNumber = $tracking->shipment_number ?: 'Belum ada nomor shipment')
                @php($deliveryNoteNumber = $tracking->delivery_note_number ?: '-')
                @php($shipmentLabel = 'Pengiriman ke-' . $loop->iteration . ' | DN ' . $deliveryNoteNumber)
                @if ($tracking->gr_rows->isEmpty())
                    @php($shipmentTimelineStatus = $runningReceivedQty > 0 ? \App\Support\DocumentTermCodes::ITEM_PARTIAL : ($initialTimelineStatus === \App\Support\DocumentTermCodes::ITEM_WAITING ? \App\Support\DocumentTermCodes::ITEM_CONFIRMED : $initialTimelineStatus))
                    <tr>
                        <td>{{ $item->item_code }}</td>
                        <td>{{ $shipmentDate }}</td>
                        <td>{{ $shipmentLabel }} (Belum GR)</td>
                        <td>-</td>
                        <td>0</td>
                        <td>{{ \App\Support\NumberFormatter::trim(max(0, (float) $item->ordered_qty - $runningReceivedQty)) }}</td>
                        <td>{{ $shipmentNumber }}</td>
                        <td>-</td>
                        <td>{{ $shipmentTimelineStatus }}</td>
                    </tr>
                @else
                    @foreach ($tracking->gr_rows as $gr)
                        @php($runningReceivedQty += (float) ($gr->gr_received_qty ?? 0))
                        @php($remainingQty = max(0, (float) $item->ordered_qty - $runningReceivedQty))
                        @php($activityLabel = $shipmentLabel . ($tracking->gr_rows->count() > 1 ? ' / GR ' . $loop->iteration : ''))
                        @php($timelineStatus = $remainingQty <= 0 ? \App\Support\DocumentTermCodes::ITEM_CLOSED : ($runningReceivedQty > 0 ? \App\Support\DocumentTermCodes::ITEM_PARTIAL : $initialTimelineStatus))
                        <tr>
                            <td>{{ $item->item_code }}</td>
                            <td>{{ $gr->receipt_date ? \Carbon\Carbon::parse($gr->receipt_date)->format('d/m/Y') : '-' }}</td>
                            <td>{{ $activityLabel }}</td>
                            <td>-</td>
                            <td>{{ \App\Support\NumberFormatter::trim($gr->gr_received_qty ?? 0) }}</td>
                            <td>{{ \App\Support\NumberFormatter::trim($remainingQty) }}</td>
                            <td>{{ $shipmentNumber }}</td>
                            <td>{{ $gr->gr_number ?: '-' }}</td>
                            <td>{{ $timelineStatus }}</td>
                        </tr>
                    @endforeach
                @endif
            @endforeach
        @endforeach
    </table>
</body>
</html>
