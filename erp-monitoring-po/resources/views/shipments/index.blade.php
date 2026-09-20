@extends('layouts.erp')

@php($title = 'Shipment')
@php($header = 'Shipments')
@php($headerSubtitle = 'Kelola dokumen shipment, filter, dan proses pengiriman.')

@php($tab = request('tab', 'worklist'))
@php($isWorklist = $tab === 'worklist')
@php($isCreate = $tab === 'create')
@php($isArchive = $tab === 'archive')
@php($focusedShipmentId = (int) request('focus'))
@php($activeRowsData = $activeRows ?? null)
@php($archiveRowsData = $archiveRows ?? null)
@php($activeCollection = $activeRowsData ? collect($activeRowsData->items()) : collect())
@php($archiveCollection = $archiveRowsData ? collect($archiveRowsData->items()) : collect())
@php($splitShipmentBoard = $splitShipmentBoard ?? collect())
@php($queryParams = request()->except('tab'))

@push('styles')
<style>
    .main-nav {
        display: flex;
        gap: 0;
        border-bottom: 2px solid var(--lemon-line);
        margin-bottom: 1rem;
        background: #fffef8;
        border-radius: 12px 12px 0 0;
        padding: 0 .5rem;
        border: 1px solid var(--lemon-line);
        border-bottom: none;
    }
    .main-nav-item {
        padding: .7rem 1.2rem;
        font-size: .84rem;
        font-weight: 700;
        color: #7a8660;
        cursor: pointer;
        border-bottom: 3px solid transparent;
        transition: all .15s;
        margin-bottom: -2px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        user-select: none;
    }
    .main-nav-item:hover { color: var(--lemon-ink); background: rgba(241,217,59,.06); }
    .main-nav-item.active {
        color: var(--lemon-ink);
        border-bottom-color: var(--lemon-green);
    }
    .main-nav-item .count {
        background: var(--lemon-bg);
        border-radius: 999px;
        padding: 1px 7px;
        font-size: .7rem;
        font-weight: 700;
        color: var(--lemon-olive);
    }
    .main-nav-item.active .count {
        background: var(--lemon-green);
        color: #fff;
    }
    .batch-toolbar {
        position: sticky;
        top: 0;
        z-index: 30;
        background: linear-gradient(135deg, #f9fbcf 0%, #f0f4d4 100%);
        border: 1px solid var(--lemon-line-strong);
        border-radius: 12px;
        padding: .6rem .9rem;
        margin-bottom: .75rem;
        display: none;
        align-items: center;
        gap: .75rem;
    }
    .batch-toolbar.visible { display: flex; }
    .breadcrumb-nav {
        display: flex; align-items: center; gap: .35rem;
        font-size: .76rem; color: #7a8660; margin-bottom: .75rem;
    }
    .breadcrumb-nav a { color: var(--lemon-green-deep); text-decoration: none; }
    .breadcrumb-nav a:hover { text-decoration: underline; }
    .breadcrumb-nav .sep { color: #b5c198; }
    .sparkline { display: inline-flex; gap: 1px; align-items: flex-end; height: 16px; }
    .sparkline-bar { width: 3px; border-radius: 1px; min-height: 2px; }
    .inline-action-btn {
        padding: 2px 5px !important; font-size: 10px !important; border-radius: 6px !important;
        min-width: 24px; min-height: 24px; display: inline-flex; align-items: center;
        justify-content: center; cursor: pointer; border: 1px solid transparent; background: transparent;
    }
    .inline-action-btn:hover { background: rgba(255,255,255,.8); border-color: var(--lemon-line); }
    .kanban-columns { display: flex; gap: .5rem; overflow-x: auto; }
    .kanban-column { min-height: 200px; border: 1px solid var(--lemon-line); border-radius: 14px; background: rgba(255,255,255,.6); padding: .75rem; display: flex; flex-direction: column; gap: .5rem; min-width: 220px; flex: 1; }
    .kanban-column-header { display: flex; justify-content: space-between; align-items: center; padding-bottom: .5rem; border-bottom: 2px solid var(--lemon-line); }
    .kanban-column-title { font-size: .82rem; font-weight: 800; color: var(--lemon-ink); text-transform: uppercase; letter-spacing: .05em; }
    .kanban-count { background: var(--lemon-bg); border-radius: 999px; padding: 2px 8px; font-size: .72rem; font-weight: 700; color: var(--lemon-olive); }
    .kanban-card { border: 1px solid var(--lemon-line); border-radius: 10px; background: #fffef8; padding: .6rem .7rem; cursor: grab; transition: box-shadow .15s; }
    .kanban-card:hover { box-shadow: 0 4px 12px rgba(111,150,40,.12); transform: translateY(-1px); }
    .kanban-card .card-title { font-weight: 700; font-size: .8rem; }
    .kanban-card .card-meta { font-size: .7rem; color: #7a8660; margin-top: 1px; }
    .kanban-card .card-actions { margin-top: .3rem; display: flex; gap: .2rem; }
    .kanban-card .card-actions .btn { padding: 2px 5px; font-size: 9px; border-radius: 5px; }
    @media (max-width: 767.98px) { .kanban-columns { flex-direction: column; } .kanban-column { min-width: 0; } }

    .stage-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; }
    .stage-waiting { background: #f0f0f0; color: #666; }
    .stage-confirmed { background: #fff3cd; color: #856404; }
    .stage-shipped { background: #cce5ff; color: #004085; }
    .stage-partial { background: #d1ecf1; color: #0c5460; }
    .stage-closed { background: #d4edda; color: #155724; }
    .stage-late { background: #f8d7da; color: #721c24; }
    .stage-cancelled { background: #e2e3e5; color: #383d41; }

    .filter-toggle-btn { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; border: 1px solid var(--lemon-line); background: #f8f9fa; color: #666; cursor: pointer; margin-bottom: .5rem; }
    .filter-toggle-btn:hover { background: #e9ecef; color: #333; border-color: #dee2e6; }
    .filter-toggle-btn i { font-size: 12px; }
    .filter-bar { display: flex; flex-direction: row; align-items: flex-end; gap: .75rem; flex-wrap: wrap; padding: .5rem 0; }
    .filter-bar-field { display: flex; flex-direction: column; min-width: 140px; flex: 1; }
    .filter-bar-field label { font-size: 11px; font-weight: 600; color: #666; margin-bottom: 3px; }
    .filter-bar-field select, .filter-bar-field input { font-size: 12px; padding: 4px 8px; height: 32px; }
    .filter-bar-actions { display: flex; gap: .4rem; align-items: flex-end; margin-left: auto; }
    .filter-bar-actions .btn { height: 32px; font-size: 12px; }
    .po-search-wrap { display: flex; align-items: center; gap: 6px; }
    .po-search-wrap i { font-size: 12px; color: #7a8660; }
</style>
@endpush

@section('content')
    <div class="page-shell">

        <nav class="breadcrumb-nav" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">Home</a>
            <span class="sep">/</span>
            <span>Shipment</span>
        </nav>

        <nav class="main-nav">
            <a href="{{ route('shipments.index', ['tab' => 'worklist'] + $queryParams) }}"
               class="main-nav-item {{ $isWorklist ? 'active' : '' }}">
                <i class="fas fa-list"></i> Worklist <span class="count">{{ $activeCollection->count() }}</span>
            </a>
            <a href="{{ route('shipments.index', ['tab' => 'create'] + $queryParams) }}"
               class="main-nav-item {{ $isCreate ? 'active' : '' }}">
                <i class="fas fa-plus"></i> Create Draft
            </a>
            <a href="{{ route('shipments.index', ['tab' => 'archive'] + $queryParams) }}"
               class="main-nav-item {{ $isArchive ? 'active' : '' }}">
                <i class="fas fa-archive"></i> Archive <span class="count">{{ $archiveCollection->count() }}</span>
            </a>
        </nav>

        @if ($isWorklist)
            <section class="ui-surface">
                <div class="ui-surface-body">
                    <button type="button" class="filter-toggle-btn" id="filterToggle">
                        <i class="fas fa-sliders-h"></i> <span id="filterToggleText">Tampilkan Filter</span>
                    </button>
                    <div id="filterSection" style="display:none;">
                        <form method="GET" class="filter-bar" id="shipmentFilterForm">
                            <div class="filter-bar-field">
                                <label for="filterSupplier">Supplier</label>
                                <select id="filterSupplier" name="supplier_id" class="form-control form-control-sm">
                                    <option value="">Semua Supplier</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ (int) (request('supplier_id') ?? 0) === $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->supplier_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="filter-bar-field">
                                <label for="filterDeliveryNote">Delivery Note</label>
                                <input type="text" id="filterDeliveryNote" name="delivery_note_number" value="{{ request('delivery_note_number') }}" class="form-control form-control-sm">
                            </div>
                            <div class="filter-bar-field">
                                <label for="filterInvoice">Invoice</label>
                                <input type="text" id="filterInvoice" name="invoice_number" value="{{ request('invoice_number') }}" class="form-control form-control-sm">
                            </div>
                            <div class="filter-bar-field">
                                <label for="filterKeyword">Keyword</label>
                                <input type="text" id="filterKeyword" name="keyword" value="{{ request('keyword') }}" class="form-control form-control-sm">
                            </div>
                            <div class="filter-bar-field">
                                <label for="filterStatus">Status</label>
                                <select id="filterStatus" name="status" class="form-control form-control-sm">
                                    <option value="">Semua Status</option>
                                    @foreach (\App\Support\DocumentTermCodes::shipmentStatuses() as $status)
                                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="filter-bar-actions">
                                <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-search"></i> Terapkan</button>
                                <a href="{{ route('shipments.index', ['tab' => 'worklist']) }}" class="btn btn-light btn-sm"><i class="fas fa-redo"></i> Reset</a>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Active Documents</h3>
                        <div class="ui-surface-subtitle">Draft, shipped, dan partial received untuk diproses.</div>
                    </div>
                    <div class="page-actions">
                        <a href="{{ route('shipments.index', ['tab' => 'create']) }}" class="btn btn-success btn-sm">+ New Draft</a>
                        <a href="{{ route('shipments.bulk-template') }}" class="btn btn-light btn-sm">Bulk Template</a>
                        <form method="POST" action="{{ route('shipments.bulk-import') }}" enctype="multipart/form-data" class="d-inline-block ml-2" style="display:inline-flex;align-items:center;gap:.35rem">
                            @csrf
                            <input type="file" name="file" class="form-control form-control-sm" accept=".xlsx,.xls,.csv" style="display:inline-block;width:auto" required>
                            <button type="submit" class="btn btn-primary btn-sm">Bulk Import</button>
                        </form>
                    </div>
                    @if (session('import_success'))
                        <div class="alert alert-success mb-2" role="alert">
                            {{ session('import_success') }}
                        </div>
                    @endif
                    @if (session('import_errors'))
                    @php($errorCount = count(session('import_errors')))
                    <div class="alert alert-danger mb-2" role="alert">
                        Import dibatalkan — ditemukan {{ $errorCount }} error. Perbaiki error pada tabel di bawah dan coba lagi.
                    </div>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-bordered table-danger mb-0" style="max-width: 900px">
                            <thead class="table-light">
                                <tr>
                                    <th>Baris</th>
                                    <th>Kolom</th>
                                    <th>Kesalahan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (session('import_errors') as $error)
                                <tr>
                                    <td>{{ $error['row'] }}</td>
                                    <td>{{ $error['field'] }}</td>
                                    <td>{{ $error['message'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                    <div class="po-search-wrap">
                        <i class="fas fa-search"></i>
                        <input type="text" id="shipment-search" class="form-control form-control-sm" placeholder="Cari shipment, supplier, PO..." aria-label="Cari shipment">
                    </div>
                </div>

                <div class="px-3 pt-3">
                    <div class="batch-toolbar" id="batchToolbar">
                        <span id="batchCount" class="font-weight-700" style="color:var(--lemon-ink)">0 selected</span>
                        <div class="separator-v" style="width:1px;height:24px;background:var(--lemon-line)"></div>
                        <button type="button" class="btn btn-primary btn-sm" id="bulkMarkShipped" disabled>Mark Shipped</button>
                        <button type="button" class="btn btn-light btn-sm" id="bulkExport" disabled>Export</button>
                        <button type="button" class="btn btn-light btn-sm" id="bulkCancel" disabled>Cancel</button>
                        <div class="ml-auto"><button type="button" class="btn btn-light btn-sm" id="clearSelection">Clear</button></div>
                    </div>
                </div>

                <div class="table-wrap table-responsive">
                    <table class="table table-hover ui-table" id="shipment-table">
                        <thead>
                            <tr>
                                <th style="width:36px"><input type="checkbox" id="selectAllRows" onchange="toggleAllRows(this.checked)"></th>
                                <th>Shipment</th><th>Supplier</th><th>PO</th><th>Delivery Note</th><th>Invoice</th>
                                <th>Status</th><th>Progress</th><th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($activeRowsData ?? [] as $r)
                                @php($stageClass = match($r->status) {
                                    \App\Support\DocumentTermCodes::SHIPMENT_DRAFT => 'stage-waiting',
                                    \App\Support\DocumentTermCodes::SHIPMENT_SHIPPED => 'stage-shipped',
                                    \App\Support\DocumentTermCodes::SHIPMENT_PARTIAL_RECEIVED => 'stage-partial',
                                    \App\Support\DocumentTermCodes::SHIPMENT_RECEIVED => 'stage-closed',
                                    \App\Support\DocumentTermCodes::SHIPMENT_CANCELLED => 'stage-cancelled',
                                    default => 'stage-waiting',
                                })
                                <tr class="{{ $focusedShipmentId === (int) $r->id ? 'table-success' : '' }}" data-shipment-number="{{ strtolower($r->shipment_number ?? '') }}" data-supplier="{{ strtolower($r->supplier_name ?? '') }}" data-po="{{ strtolower($r->po_numbers ?? '') }}" data-delivery-note="{{ strtolower($r->delivery_note_number ?? '') }}" data-invoice="{{ strtolower($r->invoice_number ?? '') }}" data-status="{{ strtolower($r->status ?? '') }}">
                                    <td onclick="event.stopPropagation()"><input type="checkbox" class="row-checkbox" value="{{ $r->id }}" onchange="updateBatchToolbar()"></td>
                                    <td>
                                        <div class="doc-number">{{ $r->shipment_number }}</div>
                                        <div class="doc-meta">{{ \Carbon\Carbon::parse($r->shipment_date)->format('d-m-Y') }}</div>
                                    </td>
                                    <td>{{ $r->supplier_name ?: '-' }}</td>
                                    <td>{{ $r->po_numbers ?: '-' }}<br><span class="doc-meta">{{ $r->po_count }} PO • {{ $r->line_count }} line</span></td>
                                    <td>{{ $r->delivery_note_number ?: '-' }}</td>
                                    <td>{{ $r->invoice_number ?: '-' }}@if ($r->invoice_date)<br><span class="doc-meta">{{ \Carbon\Carbon::parse($r->invoice_date)->format('d-m-Y') }}</span>@endif</td>
                                    <td><span class="stage-badge {{ $stageClass }}">{{ $r->status }}</span></td>
                                    <td>
                                        @php($rcv = (float)($r->total_received_qty ?? 0))
                                        @php($shp = (float)($r->total_shipped_qty ?? 0))
                                        @php($opn = (float)($r->total_open_qty ?? 0))
                                        <div class="doc-number">{{ \App\Support\NumberFormatter::trim($rcv) }} / {{ \App\Support\NumberFormatter::trim($shp) }}</div>
                                        <div class="doc-meta">Open {{ \App\Support\NumberFormatter::trim($opn) }}</div>
                                        <div class="sparkline mt-1">
                                            @if($rcv > 0)<div class="sparkline-bar" style="height:{{ min(100, round(($rcv/max($shp+$rcv,0.01))*16)) }}px;background:#9ecb3c"></div>@else<div class="sparkline-bar" style="height:2px;background:#e0e0e0"></div>@endif
                                            @if($shp > 0)<div class="sparkline-bar" style="height:{{ min(100, round(($shp/max($shp+$rcv,0.01))*16)) }}px;background:#f1d93b"></div>@else<div class="sparkline-bar" style="height:2px;background:#e0e0e0"></div>@endif
                                            @if($opn > 0)<div class="sparkline-bar" style="height:{{ min(100, round(($opn/max($shp+$opn,0.01))*16)) }}px;background:#e8a0a0"></div>@else<div class="sparkline-bar" style="height:2px;background:#e0e0e0"></div>@endif
                                        </div>
                                    </td>
                                    <td class="text-end" onclick="event.stopPropagation()">
                                        <div class="action-stack">
                                            @if ($r->status === \App\Support\DocumentTermCodes::SHIPMENT_DRAFT)
                                                <a href="{{ route('shipments.edit', $r->id) }}" class="btn btn-sm btn-outline-primary inline-action-btn" title="Edit" onclick="event.stopPropagation()"><i class="fas fa-edit"></i></a>
                                                <a href="{{ route('shipments.export-excel', $r->id) }}" class="btn btn-sm btn-outline-success inline-action-btn" title="Export" onclick="event.stopPropagation()"><i class="fas fa-download"></i></a>
                                                <button type="button" class="btn btn-sm btn-outline-primary inline-action-btn" title="Import" data-toggle="modal" data-target="#importDraftModal{{ $r->id }}" onclick="event.stopPropagation()"><i class="fas fa-upload"></i></button>
                                                <form method="POST" action="{{ route('shipments.mark-shipped', $r->id) }}" style="display:inline" onsubmit="return confirm('Mark this shipment as shipped?')">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-success inline-action-btn" title="Ship" onclick="event.stopPropagation()"><i class="fas fa-truck"></i></button></form>
                                                <form method="POST" action="{{ route('shipments.cancel-draft', $r->id) }}" style="display:inline" onsubmit="return confirm('Batalkan draft?')">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-danger inline-action-btn" title="Cancel" onclick="event.stopPropagation()"><i class="fas fa-times"></i></button></form>
                                            @elseif (in_array($r->status, [\App\Support\DocumentTermCodes::SHIPMENT_SHIPPED, \App\Support\DocumentTermCodes::SHIPMENT_PARTIAL_RECEIVED]))
                                                <a href="{{ route('receiving.process', ['supplier_id' => $r->supplier_id, 'shipment_id' => $r->id, 'document_number' => $r->delivery_note_number]) }}" class="btn btn-sm btn-outline-success inline-action-btn" title="Receive" onclick="event.stopPropagation()"><i class="fas fa-box-open"></i></a>
                                            @endif
                                        </div>
                                        @if ($r->status === \App\Support\DocumentTermCodes::SHIPMENT_DRAFT)
                                            <div class="modal fade" id="importDraftModal{{ $r->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <form method="POST" action="{{ route('shipments.import-excel') }}" enctype="multipart/form-data" class="modal-content">
                                                        @csrf <input type="hidden" name="shipment_id" value="{{ $r->id }}">
                                                        <div class="modal-header"><h5 class="modal-title">Import Excel {{ $r->shipment_number }}</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                                                        <div class="modal-body"><div class="small text-muted mb-2">Upload file hasil export setelah diedit.</div><label class="field-label">File Excel</label><input type="file" name="file" class="form-control form-control-sm" accept=".xlsx,.xls" required></div>
                                                        <div class="modal-footer"><button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Tutup</button><button class="btn btn-primary btn-sm">Import</button></div>
                                                    </form>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center text-muted">Tidak ada dokumen aktif.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($activeRowsData)
                    <div class="px-3 pb-3">{{ $activeRowsData->links() }}</div>
                @endif
            </section>
        @endif

        @if ($isCreate)
            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Filter Kandidat Item PO</h3>
                        <div class="ui-surface-subtitle">Pilih supplier atau cari item/PO untuk menyusun draft shipment.</div>
                    </div>
                </div>
                <div class="ui-surface-body">
                    <form method="GET" class="shipment-selection-form" action="{{ route('shipments.index', ['tab' => 'create'] + $queryParams) }}">
                        <input type="hidden" name="sync_selection" value="1">
                        <input type="hidden" name="tab" value="create">
                        <div class="filter-grid px-0 pt-0 pb-0">
                            <div class="span-3">
                                <label class="field-label">Supplier</label>
                                <select name="supplier_id" class="form-control form-control-sm" {{ $selectedItems->isNotEmpty() ? 'disabled' : '' }}>
                                    <option value="">Semua Supplier</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" @selected((int) request('supplier_id', $selectedSupplierId) === (int) $supplier->id)>{{ $supplier->supplier_name }}</option>
                                    @endforeach
                                </select>
                                @if ($selectedItems->isNotEmpty())<input type="hidden" name="supplier_id" value="{{ $selectedSupplierId }}">@endif
                            </div>
                            <div class="span-8">
                                <label class="field-label">Cari Item / PO / Supplier</label>
                                <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control form-control-sm" placeholder="Item code, nama item, nomor PO">
                            </div>
                            <div class="span-1 d-flex align-items-end"><button class="btn btn-outline-primary btn-sm w-100">Cari</button></div>
                        </div>
                        @foreach ($selectedItemIds as $id)<input type="hidden" name="selected_items[]" value="{{ $id }}">@endforeach
                        @foreach ($draftQuantities as $itemId => $qty)<input type="hidden" name="shipped_qty[{{ $itemId }}]" value="{{ $qty }}">@endforeach
                        @foreach ($draftInvoicePrices as $itemId => $price)<input type="hidden" name="invoice_unit_price[{{ $itemId }}]" value="{{ $price }}">@endforeach
                    </form>
                    @if ($selectedItems->isNotEmpty())
                        <div class="d-flex justify-content-end mt-3">
                            <a href="{{ route('shipments.index', ['tab' => 'create', 'clear_selection' => 1]) }}" class="btn btn-light btn-sm">Reset Builder</a>
                            <a href="{{ route('shipments.bulk-template') }}" class="btn btn-light btn-sm ml-1">Download Template</a>
                        </div>
                    @endif
                </div>
            </section>

            @if (session('import_errors'))
                @php($errorCount = count(session('import_errors')))
                <div class="alert alert-danger mb-2" role="alert">
                    Import dibatalkan — ditemukan {{ $errorCount }} error. Perbaiki error pada tabel di bawah dan coba lagi.
                </div>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered table-danger mb-0" style="max-width: 900px">
                        <thead class="table-light">
                            <tr><th>Baris</th><th>Kolom</th><th>Kesalahan</th></tr>
                        </thead>
                        <tbody>
                            @foreach (session('import_errors') as $error)
                            <tr>
                                <td>{{ $error['row'] }}</td>
                                <td>{{ $error['field'] }}</td>
                                <td>{{ $error['message'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div><h3 class="ui-surface-title">Pilih Item yang Akan Dikirim</h3></div>
                    <div class="page-actions">
                        @if ($hasSearch)
                            <button type="button" class="btn btn-primary btn-sm" onclick="addCheckedCandidateItems()">Tambahkan ke Draft</button>
                            <button type="button" class="btn btn-light btn-sm" onclick="clearCandidateChecks()">Clear Check</button>
                        @endif
                    </div>
                </div>
                <div class="table-wrap table-responsive">
                    @if (!$hasSearch)
                        <div class="text-muted py-3 text-center">Pilih supplier atau cari item untuk melihat kandidat.</div>
                    @else
                        <table class="table table-hover ui-table">
                            <thead><tr><th><input type="checkbox" onchange="toggleCandidateCheckboxes(this.checked)"></th><th>Supplier</th><th>PO</th><th>Item</th><th>Harga PO</th><th>Outstanding</th><th>Dialokasikan</th><th>Sisa Kirim</th><th>ETD</th></tr></thead>
                            <tbody>
                                @forelse($candidateItems as $candidate)
                                    @php($isAllocatable = (float) $candidate->available_to_ship_qty > 0)
                                    <tr class="{{ in_array((int) $candidate->purchase_order_item_id, $selectedItemIds, true) ? 'table-primary' : (!$isAllocatable ? 'table-light' : '') }}">
                                        <td><input type="checkbox" class="candidate-item-checkbox" value="{{ $candidate->purchase_order_item_id }}" {{ $isAllocatable ? '' : 'disabled' }}></td>
                                        <td>{{ $candidate->supplier_name }}</td>
                                        <td>{{ $candidate->po_number }}<br><x-status-badge :status="$candidate->po_status" scope="po" /></td>
                                        <td><div class="doc-number">{{ $candidate->item_code }}</div><div class="doc-meta">{{ $candidate->item_name }}</div></td>
                                        <td>{{ $candidate->unit_price !== null ? \App\Support\NumberFormatter::trim($candidate->unit_price) : '-' }}</td>
                                        <td>{{ \App\Support\NumberFormatter::trim($candidate->outstanding_qty) }}</td>
                                        <td>{{ \App\Support\NumberFormatter::trim($candidate->open_shipment_qty) }}</td>
                                        <td><span class="badge {{ $isAllocatable ? 'bg-warning text-dark' : 'bg-secondary' }}">{{ \App\Support\NumberFormatter::trim(max(0, $candidate->available_to_ship_qty)) }}</span></td>
                                        <td>{{ $candidate->etd_date ? \Carbon\Carbon::parse($candidate->etd_date)->format('d-m-Y') : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="text-center text-muted">Belum ada kandidat.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    @endif
                </div>
            </section>

            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div><h3 class="ui-surface-title">Review &amp; Simpan Draft</h3></div>
                </div>
                <div class="ui-surface-body">
                    @if ($selectedItems->isEmpty())
                        <div class="alert alert-warning">Pilih minimal satu item dari tabel kandidat.</div>
                    @endif
                    @if ($selectedItems->isNotEmpty())
                        <form method="POST" action="{{ route('shipments.store') }}">
                            @csrf
                            <div class="filter-grid px-0 pt-0 pb-3">
                                <div class="span-4"><div class="field-label">Supplier</div><input type="text" class="form-control" value="{{ optional($selectedItems->first())->supplier_name }}" disabled></div>
                                <div class="span-3"><div class="field-label">No Delivery Note <span class="text-danger">*</span></div><input type="text" name="delivery_note_number" value="{{ old('delivery_note_number') }}" class="form-control" placeholder="No surat jalan" required></div>
                                <div class="span-2"><div class="field-label">Tanggal <span class="text-danger">*</span></div><input type="date" name="shipment_date" value="{{ old('shipment_date', now()->format('Y-m-d')) }}" class="form-control" required></div>
                                <div class="span-3"><div class="field-label">No Invoice</div><input type="text" name="invoice_number" value="{{ old('invoice_number') }}" class="form-control"></div>
                                <div class="span-3"><div class="field-label">Tgl Invoice</div><input type="date" name="invoice_date" value="{{ old('invoice_date') }}" class="form-control"></div>
                                <div class="span-2"><div class="field-label">Currency</div><input type="text" name="invoice_currency" value="{{ old('invoice_currency', 'IDR') }}" class="form-control" maxlength="10"></div>
                                <div class="span-4"><div class="field-label">Catatan</div><input type="text" name="supplier_remark" value="{{ old('supplier_remark') }}" class="form-control"></div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered ui-table">
                                    <thead><tr><th><input type="checkbox" onchange="toggleDraftCheckboxes(this.checked)"></th><th>PO</th><th>Item</th><th>Sisa Kirim</th><th>Qty Draft</th><th>Harga Invoice</th><th>Total</th><th>Aksi</th></tr></thead>
                                    <tbody>
                                        @forelse($selectedItems as $item)
                                            @php($draftPrice = old('invoice_unit_price.' . $item->purchase_order_item_id, $draftInvoicePrices[$item->purchase_order_item_id] ?? ''))
                                            <tr>
                                                <td><input type="checkbox" class="draft-item-checkbox" value="{{ $item->purchase_order_item_id }}"></td>
                                                <td>{{ $item->po_number }}</td>
                                                <td><div class="doc-number">{{ $item->item_code }}</div><div class="doc-meta">{{ $item->item_name }}</div></td>
                                                <td>{{ \App\Support\NumberFormatter::trim($item->available_to_ship_qty) }}</td>
                                                <td><input type="hidden" name="selected_items[]" value="{{ $item->purchase_order_item_id }}"><input type="number" step="0.01" min="0.01" max="{{ \App\Support\NumberFormatter::input($item->available_to_ship_qty) }}" name="shipped_qty[{{ $item->purchase_order_item_id }}]" value="{{ \App\Support\NumberFormatter::input(old('shipped_qty.' . $item->purchase_order_item_id, $draftQuantities[$item->purchase_order_item_id] ?? $item->available_to_ship_qty)) }}" class="form-control draft-qty-input" data-item-id="{{ $item->purchase_order_item_id }}" required></td>
                                                <td><input type="number" step="0.0001" min="0" name="invoice_unit_price[{{ $item->purchase_order_item_id }}]" value="{{ $draftPrice }}" class="form-control draft-price-input" data-item-id="{{ $item->purchase_order_item_id }}" placeholder="Opsional"></td>
                                                <td><input type="text" class="form-control bg-light draft-line-total" data-item-id="{{ $item->purchase_order_item_id }}" value="-" readonly></td>
                                                <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeDraftItem('{{ $item->purchase_order_item_id }}')">Hapus</button></td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="8" class="text-center text-muted">Belum ada item.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-primary btn-sm" {{ $selectedItems->isEmpty() ? 'disabled' : '' }}>Simpan Draft Shipment</button>
                            </div>
                        </form>
                    @endif
                </div>
            </section>
        @endif

        @if ($isArchive)
            <section class="summary-chips">
                <div class="summary-chip"><div class="summary-chip-label">Completed</div><div class="summary-chip-value">{{ $archiveCollection->where('status', \App\Support\DocumentTermCodes::SHIPMENT_RECEIVED)->count() }}</div></div>
                <div class="summary-chip"><div class="summary-chip-label">Cancelled</div><div class="summary-chip-value">{{ $archiveCollection->where('status', \App\Support\DocumentTermCodes::SHIPMENT_CANCELLED)->count() }}</div></div>
            </section>

            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div><h3 class="ui-surface-title">Shipment Archive</h3><div class="ui-surface-subtitle">Dokumen yang sudah selesai atau dibatalkan.</div></div>
                </div>
                <div class="table-wrap table-responsive">
                    <table class="table table-hover ui-table">
                        <thead><tr><th>Shipment</th><th>Supplier</th><th>PO</th><th>DN</th><th>Invoice</th><th>Status</th><th>Progress</th><th class="text-end">Aksi</th></tr></thead>
                        <tbody>
                            @forelse ($archiveRowsData ?? [] as $r)
                                <tr>
                                    <td><div class="doc-number">{{ $r->shipment_number }}</div><div class="doc-meta">{{ \Carbon\Carbon::parse($r->shipment_date)->format('d-m-Y') }}</div></td>
                                    <td>{{ $r->supplier_name ?: '-' }}</td>
                                    <td>{{ $r->po_numbers ?: '-' }}<br><span class="doc-meta">{{ $r->po_count }} PO • {{ $r->line_count }} line</span></td>
                                    <td>{{ $r->delivery_note_number ?: '-' }}</td>
                                    <td>{{ $r->invoice_number ?: '-' }}</td>
                                    <td><x-status-badge :status="$r->status" scope="shipment" /></td>
                                    <td><div class="doc-number">{{ \App\Support\NumberFormatter::trim($r->total_received_qty ?? 0) }} / {{ \App\Support\NumberFormatter::trim($r->total_shipped_qty ?? 0) }}</div><div class="doc-meta">Open {{ \App\Support\NumberFormatter::trim($r->total_open_qty ?? 0) }}</div></td>
                                    <td class="text-end"></td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted">Belum ada arsip.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($archiveRowsData)
                    <div class="px-3 pb-3">{{ $archiveRowsData->links() }}</div>
                @endif
            </section>
        @endif

    </div>

    <script>

        function toggleAllRows(checked) { document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = checked); updateBatchToolbar(); }
        function updateBatchToolbar() {
            const c = document.querySelectorAll('.row-checkbox:checked');
            const t = document.getElementById('batchToolbar');
            if (c.length > 0) { t.classList.add('visible'); document.getElementById('batchCount').textContent = c.length + ' selected'; document.getElementById('bulkMarkShipped').disabled = false; document.getElementById('bulkExport').disabled = false; document.getElementById('bulkCancel').disabled = false; }
            else { t.classList.remove('visible'); }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const toggle = document.getElementById('filterToggle');
            const filterSection = document.getElementById('filterSection');
            const toggleText = document.getElementById('filterToggleText');
            if (toggle && filterSection) {
                toggle.addEventListener('click', function () {
                    const isHidden = filterSection.style.display === 'none';
                    filterSection.style.display = isHidden ? 'block' : 'none';
                    if (toggleText) { toggleText.textContent = isHidden ? 'Sembunyikan Filter' : 'Tampilkan Filter'; }
                });
            }

            const searchInput = document.getElementById('shipment-search');
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    const query = this.value.toLowerCase().trim();
                    const rows = document.querySelectorAll('#shipment-table tbody > tr');
                    rows.forEach(function (row) {
                        const shipmentNum = row.getAttribute('data-shipment-number') || '';
                        const supplier = row.getAttribute('data-supplier') || '';
                        const po = row.getAttribute('data-po') || '';
                        const deliveryNote = row.getAttribute('data-delivery-note') || '';
                        const invoice = row.getAttribute('data-invoice') || '';
                        const status = row.getAttribute('data-status') || '';
                        const matches = !query ||
                            shipmentNum.includes(query) ||
                            supplier.includes(query) ||
                            po.includes(query) ||
                            deliveryNote.includes(query) ||
                            invoice.includes(query) ||
                            status.includes(query);
                        row.style.display = matches ? '' : 'none';
                    });
                });
            }
        });

        window.toggleCandidateCheckboxes = (checked) => { document.querySelectorAll('.candidate-item-checkbox').forEach(cb => cb.checked = checked); };
        window.toggleDraftCheckboxes = (checked) => { document.querySelectorAll('.draft-item-checkbox').forEach(cb => cb.checked = checked); };
        window.clearCandidateChecks = () => { document.querySelectorAll('.candidate-item-checkbox').forEach(cb => cb.checked = false); };
        window.addCheckedCandidateItems = () => {
            const items = Array.from(document.querySelectorAll('.candidate-item-checkbox:checked')).map(cb => cb.value);
            if (!items.length) return;
            const form = document.querySelector('.shipment-selection-form');
            const existing = new Set(Array.from(form.querySelectorAll('input[name="selected_items[]"]')).map(i => i.value));
            items.forEach(id => { if (existing.has(id)) return; const i = document.createElement('input'); i.type = 'hidden'; i.name = 'selected_items[]'; i.value = id; form.appendChild(i); });
            form.submit();
        };
        window.removeDraftItem = (id) => {
            const nid = String(id); const form = document.querySelector('.shipment-selection-form'); if (!form) return;
            form.querySelectorAll(`input[name="selected_items[]"][value="${nid}"]`).forEach(n => n.remove());
            form.querySelectorAll(`input[name="shipped_qty[${nid}]"]`).forEach(n => n.remove());
            form.querySelectorAll(`input[name="invoice_unit_price[${nid}]"]`).forEach(n => n.remove());
            form.submit();
        };
        const formatNumber = (v) => { const p = parseFloat(v || 0); if (Number.isNaN(p)) return '-'; return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(p); };
        document.querySelectorAll('.draft-qty-input, .draft-price-input').forEach(input => {
            input.addEventListener('input', () => {
                document.querySelectorAll('.draft-line-total').forEach(t => {
                    const iid = t.dataset.itemId;
                    const q = parseFloat(document.querySelector(`.draft-qty-input[data-item-id="${iid}"]`)?.value || 0);
                    const p = parseFloat(document.querySelector(`.draft-price-input[data-item-id="${iid}"]`)?.value || 0);
                    t.value = (!document.querySelector(`.draft-price-input[data-item-id="${iid}"]`) || document.querySelector(`.draft-price-input[data-item-id="${iid}"]`).value === '' || Number.isNaN(p)) ? '-' : formatNumber(q * p);
                });
            });
        });
        document.addEventListener('keydown', function(e) { if (e.ctrlKey && e.key === 'n') { e.preventDefault(); window.location.href = '{{ route('shipments.index', ['tab' => 'create']) }}'; } if (e.ctrlKey && e.key === 'f') { e.preventDefault(); const kw = document.querySelector('input[name="keyword"]'); if (kw) kw.focus(); } });
    </script>
@endsection