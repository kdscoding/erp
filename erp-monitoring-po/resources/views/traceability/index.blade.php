@extends('layouts.erp')

@php($title='Traceability')
@php($header='Traceability')
@php($headerSubtitle='Workspace investigasi untuk melihat jejak PO, shipment, dan receiving tanpa bercampur dengan halaman monitoring.')
@php($suppliers = $suppliers ?? collect())
@php($itemStatuses = $itemStatuses ?? [])
@php($rowCollection = collect($rows ?? []))
@php($groupedPo = $rowCollection->groupBy('po_number'))
@php($selectedPoNumber = request('selected_po') ?: ($groupedPo->keys()->first() ?? null))
@php($selectedPoRows = $selectedPoNumber ? collect($groupedPo->get($selectedPoNumber, [])) : collect())
@php($selectedPoSummary = $selectedPoRows->first())

@section('content')
    <style>
        .traceability-layout { display:grid; grid-template-columns: 360px minmax(0,1fr); gap:1rem; align-items:start; }
        .traceability-list { display:grid; gap:.75rem; }
        .traceability-po-card {
            display:block;
            padding:.9rem 1rem;
            border-radius:16px;
            border:1px solid rgba(111,150,40,.12);
            background:rgba(255,255,255,.96);
            color:#314216;
            text-decoration:none;
        }
        .traceability-po-card.active { border-color:#bfd730; background:linear-gradient(135deg,#fffde8,#eef7d2); }
        .traceability-po-title { font-size:.92rem; font-weight:800; color:#314216; }
        .traceability-po-meta { font-size:.8rem; color:#728058; margin-top:.2rem; }
        .timeline-stack { display:grid; gap:.75rem; }
        .timeline-card {
            border:1px solid #e4eabc;
            border-radius:16px;
            background:linear-gradient(135deg,#fffef7,#f6f9e6);
            padding:1rem;
        }
        .timeline-head { display:flex; justify-content:space-between; gap:.75rem; align-items:flex-start; flex-wrap:wrap; }
        .timeline-title { font-size:.9rem; font-weight:800; color:#314216; }
        .timeline-meta { font-size:.8rem; color:#728058; }
        .timeline-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.75rem; margin-top:.75rem; }
        .timeline-box {
            border:1px solid #e7eadf;
            border-radius:14px;
            background:#fafcf5;
            padding:.8rem .9rem;
        }
        .timeline-box-label { font-size:.72rem; text-transform:uppercase; letter-spacing:.08em; color:#7d866f; margin-bottom:.25rem; }
        .timeline-box-value { font-size:.92rem; font-weight:700; color:#2f3c1b; }
        @media (max-width: 1199.98px) {
            .traceability-layout { grid-template-columns:1fr; }
        }
        @media (max-width: 767.98px) {
            .timeline-grid { grid-template-columns:1fr; }
        }
    </style>

    <div class="page-shell">
        <section class="page-head">
            <div class="page-head-main">
                <h2 class="page-section-title">Traceability Workspace</h2>
                <p class="page-section-subtitle">Cari PO lalu fokus pada satu konteks investigasi, bukan membaca seluruh hasil dalam satu tabel panjang.</p>
            </div>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Filter Traceability</h3>
                    <div class="ui-surface-subtitle">Gunakan supplier, nomor PO, item, dan status item untuk mempersempit hasil investigasi.</div>
                </div>
            </div>
            <form method="GET" class="filter-grid">
                <div class="span-3">
                    <label class="field-label">Supplier</label>
                    <select name="supplier_id" class="form-control form-control-sm">
                        <option value="">Semua Supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected((int) request('supplier_id') === (int) $supplier->id)>{{ $supplier->supplier_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="span-3">
                    <label class="field-label">Nomor PO</label>
                    <input name="po_number" class="form-control form-control-sm" placeholder="Cari nomor PO" value="{{ request('po_number') }}">
                </div>
                <div class="span-3">
                    <label class="field-label">Item</label>
                    <input name="item_keyword" class="form-control form-control-sm" placeholder="Kode atau nama item" value="{{ request('item_keyword') }}">
                </div>
                <div class="span-2">
                    <label class="field-label">Status Item</label>
                    <select name="item_status" class="form-control form-control-sm">
                        <option value="">Semua Status</option>
                        @foreach($itemStatuses as $status)
                            <option value="{{ $status }}" @selected(request('item_status') === $status)>{{ \App\Support\TermCatalog::label('po_item_status', $status, $status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="span-1"><button class="btn btn-primary btn-sm w-100">Cari</button></div>
                <div class="span-1"><a href="{{ route('traceability.index') }}" class="btn btn-light btn-sm w-100">Reset</a></div>
            </form>
        </section>

        <section class="traceability-layout">
            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Daftar PO</h3>
                        <div class="ui-surface-subtitle">Pilih satu PO untuk membaca timeline dan rincian item di panel kanan.</div>
                    </div>
                </div>
                <div class="ui-surface-body">
                    <div class="traceability-list">
                        @forelse($groupedPo as $poNumber => $poRows)
                            @php($poHead = collect($poRows)->first())
                            <a
                                href="{{ route('traceability.index', array_filter(request()->query() + ['selected_po' => $poNumber])) }}"
                                class="traceability-po-card {{ $selectedPoNumber === $poNumber ? 'active' : '' }}">
                                <div class="traceability-po-title">{{ $poNumber }}</div>
                                <div class="traceability-po-meta">{{ $poHead->supplier_name ?? '-' }}</div>
                                <div class="traceability-po-meta">
                                    {{ collect($poRows)->count() }} item | Shipment {{ collect($poRows)->sum('shipment_count') }}x | Receipt {{ collect($poRows)->sum('receipt_count') }}x
                                </div>
                            </a>
                        @empty
                            <div class="text-muted">Belum ada data traceability pada filter ini.</div>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Timeline Detail</h3>
                        <div class="ui-surface-subtitle">
                            @if($selectedPoSummary)
                                Fokus investigasi untuk {{ $selectedPoSummary->po_number }}.
                            @else
                                Pilih PO dari panel kiri untuk melihat detail.
                            @endif
                        </div>
                    </div>
                </div>
                <div class="ui-surface-body">
                    @if($selectedPoSummary)
                        <div class="info-grid mb-3">
                            <div class="info-box"><div class="info-label">PO</div><div class="info-value">{{ $selectedPoSummary->po_number }}</div></div>
                            <div class="info-box"><div class="info-label">Supplier</div><div class="info-value">{{ $selectedPoSummary->supplier_name }}</div></div>
                            <div class="info-box"><div class="info-label">Tanggal PO</div><div class="info-value">{{ \Carbon\Carbon::parse($selectedPoSummary->po_date)->format('d-m-Y') }}</div></div>
                        </div>

                        <div class="timeline-stack">
                            @foreach($selectedPoRows as $row)
                                <article class="timeline-card">
                                    <div class="timeline-head">
                                        <div>
                                            <div class="timeline-title">{{ $row->item_code }} - {{ $row->item_name }}</div>
                                            <div class="timeline-meta">Status <x-status-badge :status="$row->item_status" scope="item" /> | Ordered {{ \App\Support\NumberFormatter::trim($row->ordered_qty) }} | Received {{ \App\Support\NumberFormatter::trim($row->received_qty) }}</div>
                                        </div>
                                        <a href="{{ route('po.show', $row->po_id) }}" class="btn btn-sm btn-light">Buka Detail PO</a>
                                    </div>

                                    <div class="timeline-grid">
                                        <div class="timeline-box">
                                            <div class="timeline-box-label">PO Created</div>
                                            <div class="timeline-box-value">{{ \Carbon\Carbon::parse($row->po_date)->format('d-m-Y') }}</div>
                                        </div>
                                        <div class="timeline-box">
                                            <div class="timeline-box-label">ETD</div>
                                            <div class="timeline-box-value">{{ $row->etd_date ? \Carbon\Carbon::parse($row->etd_date)->format('d-m-Y') : '-' }}</div>
                                        </div>
                                        <div class="timeline-box">
                                            <div class="timeline-box-label">First Shipment</div>
                                            <div class="timeline-box-value">{{ $row->first_shipment_date ? \Carbon\Carbon::parse($row->first_shipment_date)->format('d-m-Y') : '-' }}</div>
                                            <div class="timeline-meta">Shipment {{ $row->shipment_count }}x</div>
                                            @if($row->shipment_numbers)
                                                <div class="timeline-meta">No Shipment: {{ $row->shipment_numbers }}</div>
                                            @endif
                                            @if($row->delivery_note_numbers)
                                                <div class="timeline-meta">Delivery Note: {{ $row->delivery_note_numbers }}</div>
                                            @endif
                                        </div>
                                        <div class="timeline-box">
                                            <div class="timeline-box-label">Last Receipt</div>
                                            <div class="timeline-box-value">{{ $row->last_receipt_date ? \Carbon\Carbon::parse($row->last_receipt_date)->format('d-m-Y') : '-' }}</div>
                                            <div class="timeline-meta">Parsial: {{ $row->receipt_count }}x</div>
                                            @if($row->cancel_reason)
                                                <div class="text-danger mt-1">Cancel Reason: {{ $row->cancel_reason }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="text-muted">Belum ada PO yang dipilih untuk investigasi.</div>
                    @endif
                </div>
            </section>
        </section>
    </div>
@endsection
