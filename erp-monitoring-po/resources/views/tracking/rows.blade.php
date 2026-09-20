@forelse($itemRows as $item)
    <tr class="tracking-row tracking-row-{{ $item['progress_class'] }}" data-po-id="{{ $item['po_id'] }}" data-po-number="{{ strtolower($item['po_number'] ?? '') }}" data-item-code="{{ strtolower($item['item_code'] ?? '') }}" data-item-name="{{ strtolower($item['item_name'] ?? '') }}" data-status-po="{{ strtolower($item['stage'] ?? '') }}" data-status-barang="{{ strtolower($item['monitoring_status'] ?? $item['item_status'] ?? '') }}" data-supplier="{{ strtolower($item['supplier_name'] ?? '') }}" data-item-category="{{ strtolower($item['item_category_name'] ?? '') }}">
        <td>
            <a href="{{ route($item['ref_type'], $item['ref_param']) }}" class="doc-number text-decoration-none">
                {{ $item['po_number'] }}
            </a>
        </td>
        <td>
            <span class="doc-meta">{{ $item['item_code'] }}</span>
        </td>
        <td>
            <span class="doc-meta">{{ $item['item_name'] ?? '-' }}</span>
        </td>
        <td>
            <span class="doc-meta" style="font-size:10px;">{{ $item['item_category_name'] ?? 'Tanpa Kategori' }}</span>
        </td>
        <td>
            <div class="doc-meta">{{ $item['po_date'] ?? '-' }}</div>
        </td>
        <td>{{ $item['supplier_name'] }}</td>
        <td class="text-end">{{ \App\Support\NumberFormatter::trim($item['ordered_qty']) }}</td>
        <td class="text-end">{{ \App\Support\NumberFormatter::trim($item['shipped_qty']) }}</td>
        <td class="text-end">{{ \App\Support\NumberFormatter::trim($item['received_qty']) }}</td>
        <td class="text-end">
            @php($remaining = (float) $item['outstanding_qty'])
            @if($remaining > 0)
                <span class="badge bg-danger">{{ \App\Support\NumberFormatter::trim($remaining) }}</span>
            @else
                <span class="badge bg-success">0</span>
            @endif
        </td>
        <td class="text-center">
            <span class="stage-badge {{ $item['stage_class'] ?? 'stage-waiting' }}">
                {{ $item['stage'] }}
            </span>
        </td>
        <td class="text-center">
            @php($itemStatusClass = match($item['monitoring_status'] ?? '') {
                'Waiting' => 'stage-waiting',
                'Confirmed' => 'stage-confirmed',
                'Late' => 'stage-late',
                'Partial' => 'stage-partial',
                'Closed' => 'stage-closed',
                'Force Closed' => 'stage-cancelled',
                'Cancelled' => 'stage-cancelled',
                default => 'stage-waiting',
            })
            <span class="stage-badge {{ $itemStatusClass }}" style="font-size:10px;">{{ $item['monitoring_status'] ?? $item['item_status'] ?? '-' }}</span>
        </td>
        <td class="text-center">
            <span class="shipment-progress-badge {{ $item['progress_class'] ?? 'progress-none' }}" title="Shipment Progress">
                <i class="fas {{ $item['progress_class'] === 'progress-fully' ? 'fas fa-check-circle' : ($item['progress_class'] === 'progress-partial' ? 'fas fa-truck-loading' : 'fas fa-box') }}"></i>
                <span>{{ $item['shipment_progress'] }}</span>
            </span>
        </td>
        <td class="text-center">
            <span class="progress-bar-cell">
                <div class="progress progress-sm" style="width:80px;">
                    <div class="progress-bar {{ $item['progress_class'] === 'progress-fully' ? 'bg-success' : ($item['progress_class'] === 'progress-partial' ? 'bg-warning' : 'bg-secondary') }}" role="progressbar" style="width: {{ $item['progress_percent'] }}%"></div>
                </div>
                <small class="text-muted">{{ $item['progress_percent'] }}%</small>
            </span>
        </td>
        <td class="text-center">
            @if(!empty($item['shipments']))
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleDetail('detail-{{ $item['item_id'] }}')">
                    <i class="fas fa-chevron-down" id="icon-{{ $item['item_id'] }}"></i> Detail
                </button>
            @else
                <span class="text-muted small">-</span>
            @endif
        </td>
    </tr>
    @if(!empty($item['shipments']))
        <tr class="detail-row" id="detail-{{ $item['item_id'] }}" style="display:none;">
            <td colspan="15">
                <table class="detail-shipment-table">
                    <thead>
                        <tr>
                            <th>Shipment</th>
                            <th>Tanggal</th>
                            <th>DN</th>
                            <th class="text-end">Shipped</th>
                            <th class="text-end">Received</th>
                            <th class="text-end">Outstanding</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($item['shipments'] as $shipment)
                            <tr>
                                <td>{{ $shipment['shipment_number'] }}</td>
                                <td>{{ $shipment['shipment_date'] }}</td>
                                <td>{{ $shipment['delivery_note_number'] }}</td>
                                <td class="text-end">{{ \App\Support\NumberFormatter::trim($shipment['shipped_qty']) }}</td>
                                <td class="text-end">{{ \App\Support\NumberFormatter::trim($shipment['received_qty']) }}</td>
                                <td class="text-end">
                                    @if($shipment['remaining_qty'] > 0)
                                        <span class="badge bg-danger" style="font-size:9px;">{{ \App\Support\NumberFormatter::trim($shipment['remaining_qty']) }}</span>
                                    @else
                                        <span class="badge bg-success" style="font-size:9px;">0</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
    @endif
@empty
    <tr>
        <td colspan="15" class="text-center text-muted">Belum ada data PO pada filter ini.</td>
    </tr>
@endforelse
