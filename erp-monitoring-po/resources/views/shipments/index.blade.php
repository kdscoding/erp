@extends('layouts.erp')
@php($title = 'Shipment Tracking')
@php($header = 'Shipment Tracking')

@section('content')
    @php($focusedShipmentId = (int) request('focus'))
    @php($selectedItemIds = collect(request('selected_items', []))->map(fn($id) => (int) $id)->filter()->values()->all())
    @php($isBuilderView = request()->routeIs('shipments.create') || request('view') === 'draft')
    @php($activeRowsData = isset($activeRows) ? $activeRows : null)
    @php($archiveRowsData = isset($archiveRows) ? $archiveRows : null)
    @php($activeCollection = $activeRowsData ? $activeRowsData->getCollection() : collect())
    @php($archiveCollection = $archiveRowsData ? $archiveRowsData->getCollection() : collect())

    <style>
        .ui-card {
            border: 1px solid rgba(111, 150, 40, .14);
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 14px 28px rgba(111, 150, 40, .05);
        }

        .ui-card .card-header {
            background: linear-gradient(135deg, rgba(245, 249, 221, .95), rgba(255, 255, 255, .98));
            border-bottom: 1px solid rgba(111, 150, 40, .12);
            padding: 1rem 1rem .85rem;
        }

        .ui-card .card-title {
            font-size: 1rem;
            font-weight: 800;
            color: #314216;
            margin: 0;
        }

        .ui-card .card-body {
            padding: 1rem;
        }

        .section-note {
            font-size: .82rem;
            color: #74805f;
            margin-top: .2rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 1rem .9rem;
        }

        .span-12 {
            grid-column: span 12;
        }

        .span-8 {
            grid-column: span 8;
        }

        .span-6 {
            grid-column: span 6;
        }

        .span-4 {
            grid-column: span 4;
        }

        .span-3 {
            grid-column: span 3;
        }

        .span-2 {
            grid-column: span 2;
        }

        .span-1 {
            grid-column: span 1;
        }

        .field-label {
            display: block;
            font-size: .76rem;
            font-weight: 700;
            letter-spacing: .02em;
            color: #52603d;
            margin-bottom: .35rem;
        }

        .form-control-sm,
        .form-select-sm {
            min-height: 38px;
            border-radius: 10px;
        }

        .field-help {
            font-size: .72rem;
            color: #7d866f;
            margin-top: .3rem;
            line-height: 1.3;
        }

        .soft-box {
            border: 1px solid #e7eadf;
            background: #fafcf5;
            border-radius: 14px;
            padding: .85rem .9rem;
        }

        .soft-box-title {
            font-size: .74rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #7d866f;
            margin-bottom: .2rem;
        }

        .soft-box-value {
            font-size: .95rem;
            font-weight: 700;
            color: #2f3c1b;
        }

        .builder-table th {
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            white-space: nowrap;
            vertical-align: middle;
        }

        .builder-table td {
            vertical-align: middle;
        }

        .sticky-submit {
            position: sticky;
            bottom: 0;
            z-index: 5;
            background: rgba(255, 255, 255, .96);
            border-top: 1px solid #e9ecef;
            padding-top: .8rem;
            margin-top: 1rem;
            backdrop-filter: blur(6px);
        }

        .compact-note {
            font-size: .72rem;
            color: #7b8174;
            line-height: 1.3;
        }

        .history-shell {
            display: grid;
            gap: 1rem;
        }

        .history-hero {
            border: 1px solid rgba(111, 150, 40, .12);
            border-radius: 18px;
            background:
                radial-gradient(circle at top right, rgba(241, 217, 59, .24), transparent 30%),
                linear-gradient(135deg, rgba(255, 255, 255, .96), rgba(245, 249, 221, .96));
            box-shadow: 0 14px 32px rgba(111, 150, 40, .05);
            padding: 1rem 1.1rem;
        }

        .history-hero-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: #314216;
            margin-bottom: .25rem;
        }

        .history-hero-copy {
            color: #6f7d52;
            margin-bottom: 0;
            font-size: .88rem;
        }

        .history-stat-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .75rem;
        }

        .history-stat {
            padding: .85rem .95rem;
            border-radius: 16px;
            border: 1px solid rgba(111, 150, 40, .1);
            background: rgba(255, 255, 255, .8);
        }

        .history-stat-label {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #7a8660;
            margin-bottom: .25rem;
        }

        .history-stat-value {
            font-size: 1.45rem;
            font-weight: 800;
            color: #314216;
            line-height: 1;
        }

        .history-filter {
            display: grid;
            grid-template-columns: 1.2fr 1fr 1fr 1fr auto auto;
            gap: .75rem;
            padding: 1rem;
            align-items: end;
        }

        .history-table-wrap {
            padding: 1rem;
        }

        .history-table {
            margin-bottom: 0;
        }

        .history-table thead th {
            font-size: .69rem;
            letter-spacing: .08em;
            white-space: nowrap;
        }

        .shipment-number {
            font-weight: 700;
            color: #314216;
        }

        .shipment-meta {
            font-size: .8rem;
            color: #7a8660;
        }

        .action-stack {
            display: flex;
            gap: .35rem;
            flex-wrap: wrap;
        }

        .progress-mini {
            display: flex;
            flex-direction: column;
            gap: .15rem;
        }

        .progress-mini strong {
            color: #314216;
            font-size: .84rem;
        }

        .worklist-topbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .worklist-topbar h2 {
            font-size: 1.2rem;
            font-weight: 800;
            color: #314216;
            margin-bottom: .2rem;
        }

        .worklist-topbar-copy {
            color: #738056;
            font-size: .88rem;
            margin: 0;
        }

        .worklist-toolbar {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
        }

        @media (max-width: 991.98px) {

            .span-8,
            .span-6,
            .span-4,
            .span-3,
            .span-2,
            .span-1 {
                grid-column: span 12;
            }

            .history-stat-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .history-filter {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767.98px) {
            .history-stat-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- WORKLIST / LANDING PAGE --}}
    <div class="worklist-topbar">
        <div>
            <h2>Shipment Worklist</h2>
            <p class="worklist-topbar-copy">
                Fokus ke dokumen shipment aktif lebih dulu. Draft, shipped, dan partial received tampil di depan.
            </p>
        </div>
        <div class="worklist-toolbar">
            <a href="{{ route('shipments.create') }}" class="btn btn-primary btn-sm">Create Shipment</a>
            <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal"
                data-bs-target="#importDraftModal">
                Import Draft Excel
            </button>
            <a href="{{ route('shipments.template') }}" class="btn btn-outline-secondary btn-sm">
                Download Template
            </a>
        </div>
    </div>

    <div class="history-shell">
        <section class="history-hero">
            <div class="row g-3 align-items-end">
                <div class="col-lg-5">
                    <div class="history-hero-title">Shipment sekarang berfungsi seperti inbox dokumen.</div>
                    <p class="history-hero-copy">
                        Draft tidak langsung dianggap selesai. User bisa buat draft, export Excel, import balik, lalu
                        lanjut receiving saat dokumen sudah siap.
                    </p>
                </div>
                <div class="col-lg-7">
                    <div class="history-stat-grid">
                        <div class="history-stat">
                            <div class="history-stat-label">Draft</div>
                            <div class="history-stat-value">
                                {{ $activeCollection->where('status', \App\Support\DocumentTermCodes::SHIPMENT_DRAFT)->count() }}
                            </div>
                        </div>
                        <div class="history-stat">
                            <div class="history-stat-label">Shipped</div>
                            <div class="history-stat-value">
                                {{ $activeCollection->where('status', \App\Support\DocumentTermCodes::SHIPMENT_SHIPPED)->count() }}
                            </div>
                        </div>
                        <div class="history-stat">
                            <div class="history-stat-label">Partial</div>
                            <div class="history-stat-value">
                                {{ $activeCollection->where('status', \App\Support\DocumentTermCodes::SHIPMENT_PARTIAL_RECEIVED)->count() }}
                            </div>
                        </div>
                        <div class="history-stat">
                            <div class="history-stat-label">Archive</div>
                            <div class="history-stat-value">{{ $archiveCollection->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="card ui-card">
            <div class="card-header">
                <h3 class="card-title">Filter Shipment Worklist</h3>
                <div class="section-note">Cari berdasarkan supplier, delivery note, invoice, keyword, atau status dokumen.
                </div>
            </div>

            <form method="GET" class="history-filter">
                <div>
                    <label class="field-label">Supplier</label>
                    <select name="supplier_id" class="form-control form-control-sm">
                        <option value="">Semua Supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected((int) request('supplier_id') === (int) $supplier->id)>
                                {{ $supplier->supplier_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="field-label">Delivery Note</label>
                    <input type="text" name="delivery_note_number" value="{{ request('delivery_note_number') }}"
                        class="form-control form-control-sm" placeholder="No surat jalan supplier">
                </div>

                <div>
                    <label class="field-label">Invoice</label>
                    <input type="text" name="invoice_number" value="{{ request('invoice_number') }}"
                        class="form-control form-control-sm" placeholder="No invoice supplier">
                </div>

                <div>
                    <label class="field-label">Keyword</label>
                    <input type="text" name="keyword" value="{{ request('keyword') }}"
                        class="form-control form-control-sm" placeholder="Shipment / PO / supplier">
                </div>

                <div>
                    <label class="field-label">Status</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">Semua Status</option>
                        @foreach (\App\Support\DocumentTermCodes::shipmentStatuses() as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div><button class="btn btn-primary btn-sm w-100">Apply</button></div>
                <div><a href="{{ route('shipments.index') }}" class="btn btn-light btn-sm w-100">Reset</a></div>
            </form>
        </section>

        <section class="card ui-card">
            <div class="card-header">
                <h3 class="card-title">Active Documents</h3>
                <div class="section-note">Draft, Shipped, dan Partial Received tampil paling depan untuk diproses.</div>
            </div>

            <div class="history-table-wrap table-responsive">
                <table class="table table-hover history-table">
                    <thead>
                        <tr>
                            <th>Shipment</th>
                            <th>Supplier</th>
                            <th>PO Terkait</th>
                            <th>Delivery Note</th>
                            <th>Invoice</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($activeRowsData ?? [] as $r)
                            <tr class="{{ $focusedShipmentId === (int) $r->id ? 'table-success' : '' }}">
                                <td>
                                    <div class="shipment-number">{{ $r->shipment_number }}</div>
                                    <div class="shipment-meta">
                                        {{ \Carbon\Carbon::parse($r->shipment_date)->format('d-m-Y') }}
                                    </div>
                                    @if ($focusedShipmentId === (int) $r->id)
                                        <div class="shipment-meta text-success font-weight-bold">Dokumen terbaru</div>
                                    @endif
                                </td>
                                <td>{{ $r->supplier_name ?: '-' }}</td>
                                <td>
                                    {{ $r->po_numbers ?: '-' }}
                                    <br>
                                    <span class="shipment-meta">{{ $r->po_count }} PO • {{ $r->line_count }} line</span>
                                </td>
                                <td>{{ $r->delivery_note_number ?: '-' }}</td>
                                <td>
                                    {{ $r->invoice_number ?: '-' }}
                                    @if ($r->invoice_date)
                                        <br>
                                        <span class="shipment-meta">
                                            {{ \Carbon\Carbon::parse($r->invoice_date)->format('d-m-Y') }}
                                            {{ $r->invoice_currency ?: '' }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <x-status-badge :status="$r->status" scope="shipment" />
                                </td>
                                <td>
                                    <div class="progress-mini">
                                        <strong>
                                            {{ \App\Support\NumberFormatter::trim($r->total_received_qty ?? 0) }}
                                            /
                                            {{ \App\Support\NumberFormatter::trim($r->total_shipped_qty ?? 0) }}
                                        </strong>
                                        <span class="shipment-meta">
                                            Open
                                            {{ \App\Support\NumberFormatter::trim($r->total_open_qty ?? 0) }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-stack">
                                        <a href="{{ route('shipments.show', $r->id) }}"
                                            class="btn btn-sm btn-light">View</a>

                                        @if ($r->status === \App\Support\DocumentTermCodes::SHIPMENT_DRAFT)
                                            <a href="{{ route('shipments.edit', $r->id) }}"
                                                class="btn btn-sm btn-outline-secondary">Edit</a>

                                            <a href="{{ route('shipments.export-excel', $r->id) }}"
                                                class="btn btn-sm btn-outline-success">Export Excel</a>

                                            <button type="button" class="btn btn-sm btn-outline-success"
                                                onclick="openImportDraftModal({{ $r->id }}, '{{ $r->shipment_number }}')">
                                                Import Excel
                                            </button>

                                            <form method="POST" action="{{ route('shipments.mark-shipped', $r->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-primary">Mark Shipped</button>
                                            </form>

                                            <form method="POST" action="{{ route('shipments.cancel-draft', $r->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Batalkan draft shipment ini?')">
                                                    Cancel Draft
                                                </button>
                                            </form>
                                        @elseif (in_array(
                                                $r->status,
                                                [\App\Support\DocumentTermCodes::SHIPMENT_SHIPPED, \App\Support\DocumentTermCodes::SHIPMENT_PARTIAL_RECEIVED],
                                                true))
                                            <a href="{{ route('receiving.process', ['supplier_id' => $r->supplier_id, 'shipment_id' => $r->id, 'document_number' => $r->delivery_note_number]) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                Continue Receiving
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">Tidak ada dokumen aktif.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($activeRowsData)
                <div class="px-3 pb-3">{{ $activeRowsData->links() }}</div>
            @endif
        </section>

        <section class="card ui-card">
            <div class="card-header">
                <h3 class="card-title">Archive / Completed Documents</h3>
                <div class="section-note">Dokumen yang sudah selesai diterima atau dibatalkan dipindahkan ke arsip.</div>
            </div>

            <div class="history-table-wrap table-responsive">
                <table class="table table-hover history-table">
                    <thead>
                        <tr>
                            <th>Shipment</th>
                            <th>Supplier</th>
                            <th>PO Terkait</th>
                            <th>Delivery Note</th>
                            <th>Invoice</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($archiveRowsData ?? [] as $r)
                            <tr>
                                <td>
                                    <div class="shipment-number">{{ $r->shipment_number }}</div>
                                    <div class="shipment-meta">
                                        {{ \Carbon\Carbon::parse($r->shipment_date)->format('d-m-Y') }}
                                    </div>
                                </td>
                                <td>{{ $r->supplier_name ?: '-' }}</td>
                                <td>
                                    {{ $r->po_numbers ?: '-' }}
                                    <br>
                                    <span class="shipment-meta">{{ $r->po_count }} PO • {{ $r->line_count }} line</span>
                                </td>
                                <td>{{ $r->delivery_note_number ?: '-' }}</td>
                                <td>
                                    {{ $r->invoice_number ?: '-' }}
                                    @if ($r->invoice_date)
                                        <br>
                                        <span class="shipment-meta">
                                            {{ \Carbon\Carbon::parse($r->invoice_date)->format('d-m-Y') }}
                                            {{ $r->invoice_currency ?: '' }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <x-status-badge :status="$r->status" scope="shipment" />
                                </td>
                                <td>
                                    <div class="progress-mini">
                                        <strong>
                                            {{ \App\Support\NumberFormatter::trim($r->total_received_qty ?? 0) }}
                                            /
                                            {{ \App\Support\NumberFormatter::trim($r->total_shipped_qty ?? 0) }}
                                        </strong>
                                        <span class="shipment-meta">
                                            Open
                                            {{ \App\Support\NumberFormatter::trim($r->total_open_qty ?? 0) }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('shipments.show', $r->id) }}"
                                        class="btn btn-sm btn-light">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">Belum ada dokumen arsip.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($archiveRowsData)
                <div class="px-3 pb-3">{{ $archiveRowsData->links() }}</div>
            @endif
        </section>
    </div>

    {{-- BUILDER MANUAL --}}
    @if ($isBuilderView)
        <div class="card ui-card mt-4 mb-3">
            <div class="card-header">
                <h3 class="card-title">Pembuatan Draft Shipment</h3>
                <div class="section-note">Form manual tetap dipertahankan untuk user yang ingin membuat draft langsung dari
                    kandidat item PO.</div>
            </div>
            <div class="card-body">
                @if ($selectedItems->isNotEmpty())
                    <div class="alert alert-warning mb-3">
                        Supplier terkunci ke <strong>{{ $selectedItems->first()->supplier_name }}</strong> agar satu draft
                        shipment tetap konsisten dalam satu supplier.
                    </div>
                @endif

                <form method="GET" class="shipment-selection-form">
                    <div class="form-grid">
                        <div class="span-3">
                            <label class="field-label">Supplier</label>
                            <select name="supplier_id" class="form-control form-control-sm"
                                {{ $selectedItems->isNotEmpty() ? 'disabled' : '' }}>
                                <option value="">Semua Supplier</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" @selected((int) request('supplier_id', $selectedSupplierId) == (int) $supplier->id)>
                                        {{ $supplier->supplier_name }}
                                    </option>
                                @endforeach
                            </select>
                            @if ($selectedItems->isNotEmpty())
                                <input type="hidden" name="supplier_id" value="{{ $selectedSupplierId }}">
                            @endif
                        </div>

                        <div class="span-8">
                            <label class="field-label">Cari Item / PO / Supplier</label>
                            <input type="text" name="keyword" value="{{ request('keyword') }}"
                                class="form-control form-control-sm"
                                placeholder="Contoh: item code, nama item, nomor PO, nama supplier">
                            <div class="field-help">Gunakan pencarian ini untuk mempersempit kandidat item sebelum
                                dimasukkan ke draft.</div>
                        </div>

                        <div class="span-1 d-flex align-items-end">
                            <button class="btn btn-outline-primary btn-sm w-100">Cari</button>
                        </div>
                    </div>

                    <input type="hidden" name="view" value="draft">

                    @foreach ($selectedItemIds as $selectedItemId)
                        <input type="hidden" name="selected_items[]" value="{{ $selectedItemId }}">
                    @endforeach

                    @foreach ($draftQuantities as $itemId => $qty)
                        <input type="hidden" name="shipped_qty[{{ $itemId }}]" value="{{ $qty }}">
                    @endforeach

                    @foreach ($draftInvoicePrices as $itemId => $price)
                        <input type="hidden" name="invoice_unit_price[{{ $itemId }}]" value="{{ $price }}">
                    @endforeach
                </form>
            </div>
        </div>

        <div class="card ui-card mb-3">
            <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h3 class="card-title">1. Pilih Item yang Akan Dikirim</h3>
                    <div class="section-note">Centang item yang akan masuk ke dokumen shipment draft.</div>
                </div>
                @if ($hasSearch)
                    <button type="button" class="btn btn-primary btn-sm" onclick="addCheckedCandidateItems()">
                        Tambahkan ke Draft
                    </button>
                @endif
            </div>
            <div class="card-body table-responsive p-0">
                @if (!$hasSearch)
                    <div class="p-3 text-muted">
                        Kandidat item akan muncul setelah kamu memilih supplier atau melakukan pencarian.
                    </div>
                @else
                    <table class="table table-hover mb-0 builder-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" onchange="toggleCandidateCheckboxes(this.checked)"></th>
                                <th>Supplier</th>
                                <th>PO</th>
                                <th>Item</th>
                                <th>Harga PO</th>
                                <th>Outstanding PO</th>
                                <th>Dialokasikan</th>
                                <th>Sisa Bisa Dikirim</th>
                                <th>ETD</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($candidateItems as $candidate)
                                @php($isAllocatable = (float) $candidate->available_to_ship_qty > 0)
                                <tr
                                    class="{{ in_array((int) $candidate->purchase_order_item_id, $selectedItemIds, true) ? 'table-primary' : (!$isAllocatable ? 'table-light' : '') }}">
                                    <td>
                                        <input type="checkbox" class="candidate-item-checkbox"
                                            value="{{ $candidate->purchase_order_item_id }}"
                                            {{ $isAllocatable ? '' : 'disabled' }}>
                                    </td>
                                    <td>{{ $candidate->supplier_name }}</td>
                                    <td>
                                        {{ $candidate->po_number }}<br>
                                        <x-status-badge :status="$candidate->po_status" scope="po" />
                                    </td>
                                    <td>
                                        <strong>{{ $candidate->item_code }}</strong><br>
                                        {{ $candidate->item_name }}
                                    </td>
                                    <td>
                                        {{ $candidate->unit_price !== null ? \App\Support\NumberFormatter::trim($candidate->unit_price) : '-' }}
                                    </td>
                                    <td>{{ \App\Support\NumberFormatter::trim($candidate->outstanding_qty) }}</td>
                                    <td>{{ \App\Support\NumberFormatter::trim($candidate->open_shipment_qty) }}</td>
                                    <td>
                                        <span class="badge {{ $isAllocatable ? 'bg-warning text-dark' : 'bg-secondary' }}">
                                            {{ \App\Support\NumberFormatter::trim(max(0, $candidate->available_to_ship_qty)) }}
                                        </span>
                                        @if (!$isAllocatable)
                                            <div class="compact-note mt-1">Sudah teralokasi di shipment aktif.</div>
                                        @endif
                                    </td>
                                    <td>{{ $candidate->etd_date ? \Carbon\Carbon::parse($candidate->etd_date)->format('d-m-Y') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">
                                        Belum ada kandidat. Coba ubah filter atau kata kunci pencarian.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <div class="card ui-card mb-3">
            <div class="card-header">
                <h3 class="card-title">2. Review Draft Shipment</h3>
                <div class="section-note">Isi dokumen supplier dan periksa qty serta harga invoice tiap item.</div>
            </div>
            <div class="card-body">
                @if ($selectedItems->isNotEmpty())
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="soft-box">
                                <div class="soft-box-title">Supplier</div>
                                <div class="soft-box-value">{{ $selectedItems->first()->supplier_name }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="soft-box">
                                <div class="soft-box-title">Jumlah Item</div>
                                <div class="soft-box-value">{{ $selectedItems->count() }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="soft-box">
                                <div class="soft-box-title">PO Terkait</div>
                                <div class="soft-box-value">
                                    {{ $selectedItems->pluck('purchase_order_id')->unique()->count() }}
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning">
                        Pilih minimal satu item dari tabel kandidat sebelum membuat draft shipment.
                    </div>
                @endif

                <form method="POST" action="{{ route('shipments.store') }}">
                    @csrf

                    <div class="form-grid mb-3">
                        <div class="span-4">
                            <label class="field-label">Supplier</label>
                            <input type="text" class="form-control form-control-sm"
                                value="{{ optional($selectedItems->first())->supplier_name ?: '-' }}" disabled>
                        </div>

                        <div class="span-3">
                            <label class="field-label">No Delivery Note</label>
                            <input type="text" name="delivery_note_number" value="{{ old('delivery_note_number') }}"
                                class="form-control form-control-sm" placeholder="No surat jalan supplier" required>
                        </div>

                        <div class="span-2">
                            <label class="field-label">Tanggal Dokumen</label>
                            <input type="date" name="shipment_date"
                                value="{{ old('shipment_date', now()->format('Y-m-d')) }}"
                                class="form-control form-control-sm" required>
                        </div>

                        <div class="span-3">
                            <label class="field-label">No Invoice</label>
                            <input type="text" name="invoice_number" value="{{ old('invoice_number') }}"
                                class="form-control form-control-sm" placeholder="Nomor invoice supplier">
                        </div>

                        <div class="span-3">
                            <label class="field-label">Tanggal Invoice</label>
                            <input type="date" name="invoice_date" value="{{ old('invoice_date') }}"
                                class="form-control form-control-sm">
                        </div>

                        <div class="span-2">
                            <label class="field-label">Currency</label>
                            <input type="text" name="invoice_currency" value="{{ old('invoice_currency', 'IDR') }}"
                                class="form-control form-control-sm" maxlength="10" placeholder="IDR">
                        </div>

                        <div class="span-3 d-flex align-items-end">
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" value="1"
                                    name="po_reference_missing" id="po_reference_missing" @checked(old('po_reference_missing') === '1')>
                                <label class="form-check-label" for="po_reference_missing">
                                    Nomor PO tidak ada di dokumen supplier
                                </label>
                            </div>
                        </div>

                        <div class="span-4">
                            <label class="field-label">Catatan</label>
                            <input type="text" name="supplier_remark" value="{{ old('supplier_remark') }}"
                                class="form-control form-control-sm"
                                placeholder="Catatan internal atau info tambahan supplier">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                        <div class="compact-note">
                            Harga invoice dicatat di draft shipment agar tidak perlu diinput ulang saat receiving.
                        </div>
                        @if ($selectedItems->isNotEmpty())
                            <button type="button" class="btn btn-outline-danger btn-sm"
                                onclick="removeCheckedDraftItems()">Keluarkan Item Terpilih</button>
                        @endif
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0 builder-table">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" onchange="toggleDraftCheckboxes(this.checked)"></th>
                                    <th>PO</th>
                                    <th>Item</th>
                                    <th>Harga PO</th>
                                    <th>Sisa Bisa Dikirim</th>
                                    <th>Qty Draft</th>
                                    <th>Harga Invoice</th>
                                    <th>Total Invoice</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($selectedItems as $item)
                                    @php($draftPrice = old('invoice_unit_price.' . $item->purchase_order_item_id, $draftInvoicePrices[$item->purchase_order_item_id] ?? ''))
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="draft-item-checkbox"
                                                value="{{ $item->purchase_order_item_id }}">
                                        </td>
                                        <td>{{ $item->po_number }}</td>
                                        <td>
                                            <strong>{{ $item->item_code }}</strong><br>
                                            {{ $item->item_name }}
                                        </td>
                                        <td>
                                            {{ $item->unit_price !== null ? \App\Support\NumberFormatter::trim($item->unit_price) : '-' }}
                                        </td>
                                        <td>{{ \App\Support\NumberFormatter::trim($item->available_to_ship_qty) }}</td>
                                        <td style="min-width: 140px;">
                                            <input type="hidden" name="selected_items[]"
                                                value="{{ $item->purchase_order_item_id }}">
                                            <input type="number" step="0.01" min="0.01"
                                                max="{{ \App\Support\NumberFormatter::input($item->available_to_ship_qty) }}"
                                                name="shipped_qty[{{ $item->purchase_order_item_id }}]"
                                                value="{{ \App\Support\NumberFormatter::input(old('shipped_qty.' . $item->purchase_order_item_id, $draftQuantities[$item->purchase_order_item_id] ?? $item->available_to_ship_qty)) }}"
                                                class="form-control form-control-sm draft-qty-input"
                                                data-item-id="{{ $item->purchase_order_item_id }}" required>
                                        </td>
                                        <td style="min-width: 160px;">
                                            <input type="number" step="0.0001" min="0"
                                                name="invoice_unit_price[{{ $item->purchase_order_item_id }}]"
                                                value="{{ $draftPrice }}"
                                                class="form-control form-control-sm draft-price-input"
                                                data-item-id="{{ $item->purchase_order_item_id }}"
                                                placeholder="Opsional">
                                        </td>
                                        <td style="min-width: 140px;">
                                            <input type="text"
                                                class="form-control form-control-sm bg-light draft-line-total"
                                                data-item-id="{{ $item->purchase_order_item_id }}" value="-"
                                                readonly>
                                        </td>
                                        <td class="text-nowrap">
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="removeDraftItem('{{ $item->purchase_order_item_id }}')">
                                                Batal Pilih
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">Belum ada item terpilih.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="sticky-submit d-flex justify-content-end">
                        <button class="btn btn-primary btn-sm" {{ $selectedItems->isEmpty() ? 'disabled' : '' }}>
                            Simpan Draft Shipment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- GLOBAL IMPORT MODAL --}}
    <div class="modal fade" id="importDraftModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('shipments.import-excel') }}" enctype="multipart/form-data"
                class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Draft Shipment Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="shipment_id" id="import_shipment_id">
                    <div class="mb-2 small text-muted" id="import_shipment_label">
                        Pilih draft shipment dari tombol baris.
                    </div>
                    <label class="field-label">File Excel</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary btn-sm">Import</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const draftStorageKey = 'shipment-draft-state-{{ auth()->id() ?? 'guest' }}';

        @if (session('shipment_builder_reset'))
            localStorage.removeItem(draftStorageKey);
            sessionStorage.removeItem('shipment-draft-rehydrated');
        @endif

        function openImportDraftModal(id, number) {
            const shipmentIdInput = document.getElementById('import_shipment_id');
            const shipmentLabel = document.getElementById('import_shipment_label');

            if (shipmentIdInput) shipmentIdInput.value = id;
            if (shipmentLabel) shipmentLabel.textContent = 'Draft target: ' + number;

            const modalElement = document.getElementById('importDraftModal');
            if (modalElement && typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            }
        }

        const getDraftQuantities = () => {
            const quantities = {};
            document.querySelectorAll('form[method="POST"] input[name^="shipped_qty["]').forEach((input) => {
                quantities[input.name] = input.value;
            });
            return quantities;
        };

        const getDraftInvoicePrices = () => {
            const prices = {};
            document.querySelectorAll('form[method="POST"] input[name^="invoice_unit_price["]').forEach((input) => {
                prices[input.name] = input.value;
            });
            return prices;
        };

        const getSelectedItems = () => {
            const selectedItems = [];
            document.querySelectorAll('form[method="POST"] input[name="selected_items[]"]').forEach((input) => {
                selectedItems.push(input.value);
            });
            return selectedItems;
        };

        const persistDraftState = () => {
            const draftState = {
                supplier_id: document.querySelector('input[name="supplier_id"][type="hidden"]')?.value ||
                    document.querySelector('select[name="supplier_id"]')?.value || '',
                keyword: document.querySelector('.shipment-selection-form input[name="keyword"]')?.value || '',
                selected_items: getSelectedItems(),
                quantities: getDraftQuantities(),
                invoice_prices: getDraftInvoicePrices(),
            };

            localStorage.setItem(draftStorageKey, JSON.stringify(draftState));
        };

        const restoreDraftState = () => {
            const rawState = localStorage.getItem(draftStorageKey);
            if (!rawState) return;

            try {
                const state = JSON.parse(rawState);

                Object.entries(state.quantities || {}).forEach(([name, value]) => {
                    document.querySelectorAll(`input[name="${name.replace(/"/g, '\\"')}"]`).forEach((input) => {
                        input.value = value;
                    });
                });

                Object.entries(state.invoice_prices || {}).forEach(([name, value]) => {
                    document.querySelectorAll(`input[name="${name.replace(/"/g, '\\"')}"]`).forEach((input) => {
                        input.value = value;
                    });
                });

                const keywordInput = document.querySelector('.shipment-selection-form input[name="keyword"]');
                if (keywordInput && !keywordInput.value && state.keyword) {
                    keywordInput.value = state.keyword;
                }
            } catch (error) {
                localStorage.removeItem(draftStorageKey);
            }
        };

        const submitDraftSelectionState = (selectedItemsOverride = null, quantitiesOverride = null, invoicePricesOverride =
            null) => {
            let savedState = {};
            try {
                const rawState = localStorage.getItem(draftStorageKey);
                savedState = rawState ? JSON.parse(rawState) : {};
            } catch (error) {
                localStorage.removeItem(draftStorageKey);
            }

            const selectedItems = selectedItemsOverride ?? getSelectedItems();
            const quantities = quantitiesOverride ?? getDraftQuantities();
            const invoicePrices = invoicePricesOverride ?? getDraftInvoicePrices();

            const form = document.createElement('form');
            form.method = 'GET';
            form.action = '{{ route('shipments.create') }}';

            const appendHidden = (name, value) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                form.appendChild(input);
            };

            appendHidden('view', 'draft');

            const supplierId = document.querySelector('input[name="supplier_id"][type="hidden"]')?.value ||
                document.querySelector('.shipment-selection-form select[name="supplier_id"]')?.value ||
                savedState.supplier_id || '';
            const keyword = document.querySelector('.shipment-selection-form input[name="keyword"]')?.value || savedState
                .keyword || '';

            if (supplierId) appendHidden('supplier_id', supplierId);
            if (keyword) appendHidden('keyword', keyword);

            if (selectedItems.length === 0) {
                appendHidden('clear_selection', '1');
            }

            selectedItems.forEach((itemId) => appendHidden('selected_items[]', itemId));
            Object.entries(quantities).forEach(([name, value]) => appendHidden(name, value));
            Object.entries(invoicePrices).forEach(([name, value]) => appendHidden(name, value));

            document.body.appendChild(form);
            form.submit();
        };

        const rehydrateDraftSelection = () => {
            const rawState = localStorage.getItem(draftStorageKey);
            if (!rawState) return;

            const hasSelectedDraftRows = getSelectedItems().length > 0;
            if (hasSelectedDraftRows) {
                sessionStorage.removeItem('shipment-draft-rehydrated');
                return;
            }

            if (sessionStorage.getItem('shipment-draft-rehydrated') === '1') {
                sessionStorage.removeItem('shipment-draft-rehydrated');
                return;
            }

            try {
                const state = JSON.parse(rawState);
                if (!Array.isArray(state.selected_items) || state.selected_items.length === 0) return;

                sessionStorage.setItem('shipment-draft-rehydrated', '1');
                submitDraftSelectionState(state.selected_items, state.quantities || {}, state.invoice_prices || {});
            } catch (error) {
                sessionStorage.removeItem('shipment-draft-rehydrated');
                localStorage.removeItem(draftStorageKey);
            }
        };

        const syncDraftInputs = (form) => {
            form.querySelectorAll('input[name^="shipped_qty["]').forEach((input) => input.remove());
            form.querySelectorAll('input[name^="invoice_unit_price["]').forEach((input) => input.remove());

            Object.entries(getDraftQuantities()).forEach(([name, value]) => {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = name;
                hidden.value = value;
                form.appendChild(hidden);
            });

            Object.entries(getDraftInvoicePrices()).forEach(([name, value]) => {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = name;
                hidden.value = value;
                form.appendChild(hidden);
            });

            persistDraftState();
        };

        const formatNumber = (value) => {
            const parsed = parseFloat(value || 0);
            if (Number.isNaN(parsed)) return '-';
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(parsed);
        };

        const recalcDraftLineTotals = () => {
            document.querySelectorAll('.draft-line-total').forEach((totalInput) => {
                const itemId = totalInput.dataset.itemId;
                const qtyInput = document.querySelector(`.draft-qty-input[data-item-id="${itemId}"]`);
                const priceInput = document.querySelector(`.draft-price-input[data-item-id="${itemId}"]`);

                const qty = parseFloat(qtyInput?.value || 0);
                const price = parseFloat(priceInput?.value || 0);

                if (!priceInput || priceInput.value === '' || Number.isNaN(price)) {
                    totalInput.value = '-';
                    return;
                }

                const total = qty * price;
                totalInput.value = formatNumber(total);
            });
        };

        restoreDraftState();
        rehydrateDraftSelection();
        recalcDraftLineTotals();

        document.querySelectorAll(
            'form[method="POST"] input[name^="shipped_qty["], form[method="POST"] input[name^="invoice_unit_price["], .shipment-selection-form input[name="keyword"]'
        ).forEach((input) => {
            input.addEventListener('input', () => {
                persistDraftState();
                recalcDraftLineTotals();
            });
            input.addEventListener('change', () => {
                persistDraftState();
                recalcDraftLineTotals();
            });
        });

        document.querySelectorAll('.shipment-selection-form').forEach((form) => {
            form.addEventListener('submit', () => syncDraftInputs(form));
        });

        window.removeDraftItem = (itemId) => {
            persistDraftState();

            const normalizedItemId = String(itemId);
            const nextSelectedItems = getSelectedItems().filter((selectedItemId) => String(selectedItemId) !==
                normalizedItemId);
            const nextQuantities = {
                ...getDraftQuantities()
            };
            const nextInvoicePrices = {
                ...getDraftInvoicePrices()
            };

            delete nextQuantities[`shipped_qty[${normalizedItemId}]`];
            delete nextInvoicePrices[`invoice_unit_price[${normalizedItemId}]`];

            let draftState = {};
            try {
                draftState = JSON.parse(localStorage.getItem(draftStorageKey) || '{}');
            } catch (error) {
                draftState = {};
            }

            draftState.selected_items = nextSelectedItems;
            draftState.quantities = nextQuantities;
            draftState.invoice_prices = nextInvoicePrices;
            localStorage.setItem(draftStorageKey, JSON.stringify(draftState));

            submitDraftSelectionState(nextSelectedItems, nextQuantities, nextInvoicePrices);
        };

        window.toggleCandidateCheckboxes = (checked) => {
            document.querySelectorAll('.candidate-item-checkbox').forEach((checkbox) => {
                checkbox.checked = checked;
            });
        };

        window.toggleDraftCheckboxes = (checked) => {
            document.querySelectorAll('.draft-item-checkbox').forEach((checkbox) => {
                checkbox.checked = checked;
            });
        };

        window.addCheckedCandidateItems = () => {
            persistDraftState();

            const checkedItems = Array.from(document.querySelectorAll('.candidate-item-checkbox:checked')).map((
                checkbox) => checkbox.value);
            if (checkedItems.length === 0) return;

            const nextSelectedItems = Array.from(new Set([...getSelectedItems(), ...checkedItems]));
            const nextQuantities = {
                ...getDraftQuantities()
            };
            const nextInvoicePrices = {
                ...getDraftInvoicePrices()
            };

            let draftState = {};
            try {
                draftState = JSON.parse(localStorage.getItem(draftStorageKey) || '{}');
            } catch (error) {
                draftState = {};
            }

            draftState.selected_items = nextSelectedItems;
            draftState.quantities = nextQuantities;
            draftState.invoice_prices = nextInvoicePrices;
            localStorage.setItem(draftStorageKey, JSON.stringify(draftState));

            submitDraftSelectionState(nextSelectedItems, nextQuantities, nextInvoicePrices);
        };

        window.removeCheckedDraftItems = () => {
            persistDraftState();

            const checkedItems = Array.from(document.querySelectorAll('.draft-item-checkbox:checked')).map((checkbox) =>
                checkbox.value);
            if (checkedItems.length === 0) return;

            const checkedSet = new Set(checkedItems.map(String));
            const nextSelectedItems = getSelectedItems().filter((selectedItemId) => !checkedSet.has(String(
                selectedItemId)));
            const nextQuantities = {
                ...getDraftQuantities()
            };
            const nextInvoicePrices = {
                ...getDraftInvoicePrices()
            };

            checkedItems.forEach((itemId) => {
                delete nextQuantities[`shipped_qty[${itemId}]`];
                delete nextInvoicePrices[`invoice_unit_price[${itemId}]`];
            });

            let draftState = {};
            try {
                draftState = JSON.parse(localStorage.getItem(draftStorageKey) || '{}');
            } catch (error) {
                draftState = {};
            }

            draftState.selected_items = nextSelectedItems;
            draftState.quantities = nextQuantities;
            draftState.invoice_prices = nextInvoicePrices;
            localStorage.setItem(draftStorageKey, JSON.stringify(draftState));

            submitDraftSelectionState(nextSelectedItems, nextQuantities, nextInvoicePrices);
        };

        document.querySelectorAll(
            'form[action="{{ route('shipments.store') }}"], form[action="{{ route('shipments.create') }}"]').forEach(
            (form) => {
                form.addEventListener('submit', () => {
                    persistDraftState();
                    recalcDraftLineTotals();
                });
            });
    </script>
@endsection