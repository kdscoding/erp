@extends('layouts.erp')

@php($title = 'Tracking')
@php($header = 'Fulfillment Tracking')

@section('content')
    <style>
        .stage-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; }
        .stage-waiting { background: #f0f0f0; color: #666; }
        .stage-confirmed { background: #fff3cd; color: #856404; }
        .stage-shipped { background: #cce5ff; color: #004085; }
        .stage-partial { background: #d1ecf1; color: #0c5460; }
        .stage-closed { background: #d4edda; color: #155724; }
        .stage-late { background: #f8d7da; color: #721c24; }
        .stage-cancelled { background: #e2e3e5; color: #383d41; }

        .detail-row { background: #fafdf5; }
        .detail-row td { padding: .85rem 1.1rem !important; }
        .detail-item { border: 1px solid #e0e6c8; border-radius: 10px; padding: .75rem .9rem; margin-bottom: .65rem; background: #fff; }
        .detail-item:last-child { margin-bottom: 0; }
        .detail-item-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: .4rem; flex-wrap: wrap; gap: .4rem; }
        .detail-item-code { font-weight: 700; color: #314216; font-size: 12px; }
        .detail-item-name { font-size: 11px; color: #7a8660; }
        .detail-item-stats { display: flex; gap: 1rem; flex-wrap: wrap; font-size: 11px; color: #52603d; }
        .detail-item-stats strong { color: #314216; }
        .detail-shipment-table { width: 100%; border-collapse: collapse; margin-top: .5rem; font-size: 11px; }
        .detail-shipment-table th { background: #f2f6cf; padding: .4rem .55rem; text-align: left; border-bottom: 1px solid var(--lemon-line); font-size: .69rem; text-transform: uppercase; letter-spacing: .08em; color: #5f7331; }
        .detail-shipment-table td { padding: .35rem .55rem; border-bottom: 1px solid var(--lemon-line); vertical-align: middle; }
        .filter-advanced { overflow: hidden; max-height: 0; opacity: 0; transition: max-height .3s ease, opacity .3s ease, margin .3s ease; margin-top: 0 !important; }
        .filter-advanced.open { max-height: 300px; opacity: 1; margin-top: .75rem !important; }
        .filter-bar { display: flex; flex-direction: row; align-items: flex-end; gap: .75rem; flex-wrap: wrap; padding: .5rem 0; }
        .filter-bar-field { display: flex; flex-direction: column; min-width: 140px; flex: 1; }
        .filter-bar-field label { font-size: 11px; font-weight: 600; color: #666; margin-bottom: 3px; }
        .filter-bar-field select, .filter-bar-field input { font-size: 12px; padding: 4px 8px; height: 32px; }
        .filter-bar-actions { display: flex; gap: .4rem; align-items: flex-end; margin-left: auto; }
        .filter-bar-actions .btn { height: 32px; font-size: 12px; }
        .filter-toggle-btn { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; border: 1px solid var(--lemon-line); background: #f8f9fa; color: #666; cursor: pointer; margin-bottom: .5rem; }
        .filter-toggle-btn:hover { background: #e9ecef; color: #333; border-color: #dee2e6; }
        .filter-toggle-btn i { font-size: 12px; }

        .shipment-progress-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; }
        .progress-none { background: #f0f0f0; color: #6c757d; border: 1px solid #dee2e6; }
        .progress-partial { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .progress-fully { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }

        .progress-bar-cell { display: flex; align-items: center; gap: 6px; justify-content: center; }
        .progress-bar-cell .progress { height: 8px; }
        .progress-bar-cell .progress-bar { transition: width .3s ease; }
    </style>

    <div class="page-shell">
        <section class="ui-surface">
            <div class="ui-surface-body">
                <button type="button" class="filter-toggle-btn" id="filterToggle">
                    <i class="fas fa-sliders-h"></i> <span id="filterToggleText">Tampilkan Filter</span>
                </button>
                <div id="filterSection" style="display:none;">
                    <form method="GET" class="filter-bar" id="trackingFilterForm">
                        <div class="filter-bar-field">
                            <label for="filterSupplier">Supplier</label>
                            <select id="filterSupplier" name="supplier_id" class="form-control form-control-sm">
                                <option value="">Semua Supplier</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ (int) ($filterSupplierId ?? 0) === $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->supplier_code }} - {{ $supplier->supplier_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-bar-field">
                            <label for="filterDateFrom">Tanggal PO Dari</label>
                            <input type="date" id="filterDateFrom" name="date_from" value="{{ $filterDateFrom ?? '' }}" class="form-control form-control-sm">
                        </div>
<div class="filter-bar-field">
                             <label for="filterDateTo">Tanggal PO Sampai</label>
                             <input type="date" id="filterDateTo" name="date_to" value="{{ $filterDateTo ?? '' }}" class="form-control form-control-sm">
                         </div>
                         <div class="filter-bar-field">
                             <label for="filterPoStatus">PO Status</label>
                             <select id="filterPoStatus" name="po_status" class="form-control form-control-sm">
                                 <option value="all" {{ $filterPoStatus === 'all' ? 'selected' : '' }}>Semua</option>
                                 <option value="PO Issued" {{ $filterPoStatus === 'PO Issued' ? 'selected' : '' }}>PO Issued</option>
                                 <option value="Open" {{ $filterPoStatus === 'Open' ? 'selected' : '' }}>Open</option>
                                 <option value="Late" {{ $filterPoStatus === 'Late' ? 'selected' : '' }}>Late</option>
                                 <option value="Closed" {{ $filterPoStatus === 'Closed' ? 'selected' : '' }}>Closed</option>
                                 <option value="Cancelled" {{ $filterPoStatus === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                             </select>
                         </div>
                        <div class="filter-bar-field">
                            <label for="filterItemStatus">Item Status</label>
                            <select id="filterItemStatus" name="item_status" class="form-control form-control-sm">
                                <option value="all" {{ $filterItemStatus === 'all' ? 'selected' : '' }}>Semua</option>
                                <option value="Waiting" {{ $filterItemStatus === 'Waiting' ? 'selected' : '' }}>Waiting</option>
                                <option value="Confirmed" {{ $filterItemStatus === 'Confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="Late" {{ $filterItemStatus === 'Late' ? 'selected' : '' }}>Late</option>
                                <option value="Partial" {{ $filterItemStatus === 'Partial' ? 'selected' : '' }}>Partial</option>
                                <option value="Closed" {{ $filterItemStatus === 'Closed' ? 'selected' : '' }}>Closed</option>
                                <option value="Force Closed" {{ $filterItemStatus === 'Force Closed' ? 'selected' : '' }}>Force Closed</option>
                                <option value="Cancelled" {{ $filterItemStatus === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="filter-bar-field">
                            <label for="filterCategory">Kategori Barang</label>
                            <select id="filterCategory" name="category_id" class="form-control form-control-sm">
                                <option value="all" {{ $filterCategory === 'all' ? 'selected' : '' }}>Semua Kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ (string) ($filterCategory ?? 'all') === (string) $category->id ? 'selected' : '' }}>
                                        {{ $category->category_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                         <div class="filter-bar-actions">
                            <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-search"></i> Terapkan</button>
                            <a href="{{ route('tracking.index') }}" class="btn btn-light btn-sm"><i class="fas fa-redo"></i> Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Unified Tracking Table</h3>
                </div>
                <div class="po-search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" id="tracking-search" class="form-control form-control-sm" placeholder="Cari PO, kode barang..." aria-label="Cari PO, kode barang">
                </div>
            </div>

            <div class="table-wrap table-responsive">
                <table class="table table-hover ui-table" id="tracking-table">
<thead>
                            <tr>
                                <th>PO Number</th>
                                <th>Item Codes</th>
                                <th>Name Barang</th>
                                <th>Kategori</th>
                                <th>Tanggal</th>
                                <th>Supplier</th>
                                <th class="text-end">Ordered</th>
                                <th class="text-end">Dikirim</th>
                                <th class="text-end">Diterima</th>
                                <th class="text-end">Outstanding</th>
                                <th class="text-center">Status PO</th>
                                <th class="text-center">Status Barang</th>
                                <th class="text-center">Shipment Progress</th>
                                <th class="text-center">Progress %</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tracking-tbody">
                            @forelse($itemRows as $item)
                                <tr class="tracking-row" data-po-id="{{ $item['po_id'] }}" data-po-number="{{ strtolower($item['po_number'] ?? '') }}" data-item-code="{{ strtolower($item['item_code'] ?? '') }}" data-item-name="{{ strtolower($item['item_name'] ?? '') }}" data-status-po="{{ strtolower($item['stage'] ?? '') }}" data-status-barang="{{ strtolower($item['monitoring_status'] ?? $item['item_status'] ?? '') }}" data-supplier="{{ strtolower($item['supplier_name'] ?? '') }}" data-item-category="{{ strtolower($item['item_category_name'] ?? '') }}">
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
                        </tbody>
                    </table>
            </div>
        </section>
    </div>

    <script>
        function toggleDetail(id) {
            const el = document.getElementById(id);
            const icon = document.getElementById('icon-' + id.replace('detail-', ''));
            if (!el) return;
            const isHidden = el.style.display === 'none';
            el.style.display = isHidden ? '' : 'none';
            if (icon) {
                icon.className = isHidden ? 'fas fa-chevron-up' : 'fas fa-chevron-down';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const toggle = document.getElementById('filterToggle');
            const filterSection = document.getElementById('filterSection');
            const toggleText = document.getElementById('filterToggleText');

            if (toggle && filterSection) {
                toggle.addEventListener('click', function () {
                    const isHidden = filterSection.style.display === 'none';
                    filterSection.style.display = isHidden ? 'block' : 'none';
                    if (toggleText) {
                        toggleText.textContent = isHidden ? 'Sembunyikan Filter' : 'Tampilkan Filter';
                    }
                });
            }

            const searchInput = document.getElementById('tracking-search');
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    const query = this.value.toLowerCase().trim();
                    const itemRows = document.querySelectorAll('#tracking-table tbody tr[data-po-id]');

                    itemRows.forEach(function (row) {
                        const poNumber = (row.getAttribute('data-po-number') || row.querySelector('td:nth-child(1) a')?.textContent || '').toLowerCase();
                        const itemCode = row.getAttribute('data-item-code') || '';
                        const itemName = row.getAttribute('data-item-name') || '';
                        const itemCategory = row.getAttribute('data-item-category') || '';
                        const statusPo = row.getAttribute('data-status-po') || '';
                        const statusBarang = row.getAttribute('data-status-barang') || '';
                        const supplier = row.getAttribute('data-supplier') || '';

                        const matches = !query ||
                            poNumber.includes(query) ||
                            itemCode.includes(query) ||
                            itemName.includes(query) ||
                            itemCategory.includes(query) ||
                            statusPo.includes(query) ||
                            statusBarang.includes(query) ||
                            supplier.includes(query);

                        row.style.display = matches ? '' : 'none';
                    });
                });
            }
        });
    </script>
@endsection
