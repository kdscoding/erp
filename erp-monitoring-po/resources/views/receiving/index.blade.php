@extends('layouts.erp')

@php($mode = $mode ?? 'process')
@php($rows = $rows ?? collect())
@php($shipmentDocuments = $shipmentDocuments ?? collect())
@php($shipmentItems = $shipmentItems ?? collect())
@php($selectedShipment = $selectedShipment ?? null)
@php($suppliers = $suppliers ?? collect())

@php($title = $mode === 'history' ? 'Receiving History' : 'Open Receiving')
@php($header = $mode === 'history' ? 'Receiving History' : 'Open Receiving')
@php($headerSubtitle = $mode === 'history' ? 'Riwayat goods receipt yang sudah diposting.' : 'Proses receiving untuk shipment yang siap diterima gudang.')

@section('content')
    @php($rowsCollection = method_exists($rows, 'getCollection') ? $rows->getCollection() : collect($rows))
    @php($shipmentCollection = collect($shipmentDocuments ?? []))
    @php($shipmentItemCollection = collect($shipmentItems ?? []))

    @php($historyCount = $rowsCollection->count())
    @php($cancelledCount = $rowsCollection->where('status', \App\Support\DocumentTermCodes::GR_CANCELLED)->count())

    @php($documentCount = $shipmentCollection->count())
    @php($readyCount = $shipmentCollection->whereNotIn('status', ['Closed', 'Cancelled'])->count())
    @php($selectedLineCount = $shipmentItemCollection->count())
    @php($outstandingQty = $shipmentItemCollection->sum(fn ($item) => (float) ($item->shipment_outstanding_qty ?? 0)))

    <div class="page-shell">
        @if ($mode === 'process')
            <section class="page-head">
                <div class="page-head-main">
                    <h2 class="page-section-title">Receiving Queue</h2>
                    <p class="page-section-subtitle">Pilih shipment, lalu posting qty fisik yang benar-benar datang di gudang.</p>
                </div>
            </section>

            <section class="summary-chips">
                <div class="summary-chip">
                    <div class="summary-chip-label">Dokumen</div>
                    <div class="summary-chip-value">{{ $documentCount }}</div>
                </div>
                <div class="summary-chip">
                    <div class="summary-chip-label">Siap Diproses</div>
                    <div class="summary-chip-value">{{ $readyCount }}</div>
                </div>
                <div class="summary-chip">
                    <div class="summary-chip-label">Line Terpilih</div>
                    <div class="summary-chip-value">{{ $selectedLineCount }}</div>
                </div>
                <div class="summary-chip">
                    <div class="summary-chip-label">Sisa Qty</div>
                    <div class="summary-chip-value">{{ \App\Support\NumberFormatter::trim($outstandingQty) }}</div>
                </div>
            </section>

            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Filter Dokumen Shipment</h3>
                        <div class="ui-surface-subtitle">Cari dokumen supplier yang siap diterima gudang.</div>
                    </div>
                </div>

                <form method="GET" action="{{ route('receiving.process') }}" class="filter-grid">
                    <div class="span-3">
                        <label class="field-label">Supplier</label>
                        <select name="supplier_id" class="form-control form-control-sm">
                            <option value="">Semua Supplier</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>
                                    {{ $supplier->supplier_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="span-3">
                        <label class="field-label">Delivery Note</label>
                        <input type="text" name="document_number" value="{{ request('document_number') }}"
                            class="form-control form-control-sm" placeholder="No surat jalan supplier">
                    </div>

                    <div class="span-4">
                        <label class="field-label">Cari Shipment / PO / Invoice</label>
                        <input type="text" name="keyword" value="{{ request('keyword') }}"
                            class="form-control form-control-sm" placeholder="Shipment, PO, invoice, supplier">
                    </div>

                    <div class="span-1">
                        <button class="btn btn-primary btn-sm w-100">Apply</button>
                    </div>

                    <div class="span-1">
                        <a href="{{ route('receiving.process') }}" class="btn btn-light btn-sm w-100">Reset</a>
                    </div>
                </form>
            </section>

            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Shipment Worklist</h3>
                        <div class="ui-surface-subtitle">Pilih satu dokumen shipment untuk diteruskan ke proses receiving.</div>
                    </div>
                </div>

                <div class="table-wrap table-responsive">
                    <table class="table table-hover ui-table">
                        <thead>
                            <tr>
                                <th>Shipment</th>
                                <th>Supplier</th>
                                <th>Delivery Note</th>
                                <th>Invoice</th>
                                <th>Tanggal</th>
                                <th>PO</th>
                                <th>Line</th>
                                <th>Sisa Kiriman</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shipmentDocuments as $document)
                                <tr>
                                    <td>
                                        <div class="doc-number">{{ $document->shipment_number }}</div>
                                        <div class="doc-meta"><x-status-badge :status="$document->status" scope="shipment" /></div>
                                    </td>
                                    <td>{{ $document->supplier_name }}</td>
                                    <td>{{ $document->delivery_note_number ?: '-' }}</td>
                                    <td>{{ $document->invoice_number ?: '-' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($document->shipment_date)->format('d-m-Y') }}</td>
                                    <td>{{ $document->po_count }}</td>
                                    <td>{{ $document->line_count }}</td>
                                    <td>{{ \App\Support\NumberFormatter::trim($document->outstanding_qty) }}</td>
                                    <td class="text-end">
                                        <div class="action-stack">
                                            <a href="{{ route('receiving.process', ['shipment_id' => $document->id, 'supplier_id' => request('supplier_id'), 'document_number' => request('document_number'), 'keyword' => request('keyword')]) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                Pilih
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">Belum ada dokumen shipment yang siap diterima.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Form Receiving</h3>
                        <div class="ui-surface-subtitle">Receiving hanya mencatat qty fisik yang datang. Harga invoice dibaca dari shipment draft.</div>
                    </div>
                </div>

                <div class="ui-surface-body">
                    @if ($selectedShipment)
                        <div class="info-grid mb-3">
                            <div class="info-box">
                                <div class="info-label">Shipment</div>
                                <div class="info-value">{{ $selectedShipment->shipment_number }}</div>
                            </div>
                            <div class="info-box">
                                <div class="info-label">Delivery Note</div>
                                <div class="info-value">{{ $selectedShipment->delivery_note_number ?: '-' }}</div>
                            </div>
                            <div class="info-box">
                                <div class="info-label">Invoice</div>
                                <div class="info-value">{{ $selectedShipment->invoice_number ?: '-' }}</div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <div class="soft-alert">
                                Warehouse akan memproses dokumen untuk supplier
                                <strong>{{ $selectedShipment->supplier_name }}</strong>.
                            </div>

                            <a href="{{ route('receiving.process', ['clear_selection' => 1, 'supplier_id' => request('supplier_id'), 'document_number' => request('document_number'), 'keyword' => request('keyword')]) }}"
                                class="btn btn-sm btn-outline-secondary">
                                Batalkan Pilihan Dokumen
                            </a>
                        </div>

                        <form method="POST" action="{{ route('receiving.store') }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="shipment_id" value="{{ $selectedShipment->id }}">
                            <input type="hidden" name="supplier_id" value="{{ request('supplier_id') }}">
                            <input type="hidden" name="search_document_number" value="{{ request('document_number') }}">

                            <div class="filter-grid px-0 pt-0 pb-3">
                                <div class="span-3">
                                    <label class="field-label">Tanggal Terima</label>
                                    <input type="date" name="receipt_date" class="form-control form-control-sm"
                                        value="{{ old('receipt_date', now()->format('Y-m-d')) }}" required>
                                </div>

                                <div class="span-3">
                                    <label class="field-label">No Dokumen Receiving</label>
                                    <input type="text" name="document_number" class="form-control form-control-sm"
                                        value="{{ old('document_number', $selectedShipment->delivery_note_number) }}" required>
                                </div>

                                <div class="span-3">
                                    <label class="field-label">Lampiran</label>
                                    <input type="file" name="attachment" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.pdf">
                                </div>

                                <div class="span-3">
                                    <label class="field-label">Catatan</label>
                                    <input type="text" name="note" class="form-control form-control-sm" value="{{ old('note') }}" placeholder="Opsional">
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover table-bordered ui-table">
                                    <thead>
                                        <tr>
                                            <th>PO</th>
                                            <th>Item</th>
                                            <th>Harga PO</th>
                                            <th>Harga Invoice</th>
                                            <th>Total Invoice</th>
                                            <th>Qty Dikirim</th>
                                            <th>Sudah Diterima</th>
                                            <th>Sisa Bisa Diterima</th>
                                            <th>Qty Diterima Sekarang</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($shipmentItems as $item)
                                            <tr>
                                                <td>{{ $item->po_number }}</td>
                                                <td>
                                                    <div class="doc-number">{{ $item->item_code }}</div>
                                                    <div class="doc-meta">{{ $item->item_name }}</div>
                                                </td>
                                                <td>{{ $item->unit_price !== null ? \App\Support\NumberFormatter::trim($item->unit_price) : '-' }}</td>
                                                <td>{{ $item->invoice_unit_price !== null ? \App\Support\NumberFormatter::trim($item->invoice_unit_price) : '-' }}</td>
                                                <td>{{ $item->invoice_line_total !== null ? \App\Support\NumberFormatter::trim($item->invoice_line_total) : '-' }}</td>
                                                <td>{{ \App\Support\NumberFormatter::trim($item->shipped_qty) }}</td>
                                                <td>{{ \App\Support\NumberFormatter::trim($item->shipment_received_qty) }}</td>
                                                <td>
                                                    <span class="badge bg-warning text-dark">
                                                        {{ \App\Support\NumberFormatter::trim($item->shipment_outstanding_qty) }}
                                                    </span>
                                                </td>
                                                <td style="min-width: 150px;">
                                                    <input type="number" step="0.01" min="0" max="{{ $item->shipment_outstanding_qty }}"
                                                        name="received_qty[{{ $item->shipment_item_id }}]"
                                                        value="{{ old('received_qty.' . $item->shipment_item_id) }}"
                                                        class="form-control form-control-sm"
                                                        placeholder="Isi jika datang">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                <button class="btn btn-success btn-sm">Posting Receiving</button>
                            </div>
                        </form>
                    @else
                        <div class="text-muted">Pilih dulu satu dokumen shipment di tabel atas. Setelah itu item akan muncul dan form receiving menjadi aktif.</div>
                    @endif
                </div>
            </section>
        @endif

        @if ($mode === 'history')
            <section class="page-head">
                <div class="page-head-main">
                    <h2 class="page-section-title">Riwayat Goods Receipt</h2>
                    <p class="page-section-subtitle">Lihat dokumen GR yang sudah diposting atau dibatalkan beserta referensi shipment dan PO.</p>
                </div>
            </section>

            <section class="summary-chips">
                <div class="summary-chip">
                    <div class="summary-chip-label">Total Dokumen</div>
                    <div class="summary-chip-value">{{ $historyCount }}</div>
                </div>
                <div class="summary-chip">
                    <div class="summary-chip-label">Posted</div>
                    <div class="summary-chip-value">{{ $historyCount - $cancelledCount }}</div>
                </div>
                <div class="summary-chip">
                    <div class="summary-chip-label">Cancelled</div>
                    <div class="summary-chip-value">{{ $cancelledCount }}</div>
                </div>
            </section>

            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Daftar Goods Receipt</h3>
                        <div class="ui-surface-subtitle">Gunakan detail untuk melihat item yang diterima pada tiap transaksi GR.</div>
                    </div>
                </div>

                <div class="table-wrap table-responsive">
                    <table class="table table-hover ui-table">
                        <thead>
                            <tr>
                                <th>No GR</th>
                                <th>Tanggal</th>
                                <th>PO</th>
                                <th>Supplier</th>
                                <th>Shipment</th>
                                <th>Delivery Note</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr>
                                    <td>{{ $row->gr_number }}</td>
                                    <td>{{ \Carbon\Carbon::parse($row->receipt_date)->format('d-m-Y') }}</td>
                                    <td>{{ $row->po_number }}</td>
                                    <td>{{ $row->supplier_name }}</td>
                                    <td>{{ $row->shipment_number ?: '-' }}</td>
                                    <td>{{ $row->delivery_note_number ?: ($row->document_number ?: '-') }}</td>
                                    <td><x-status-badge :status="$row->status" scope="gr" /></td>
                                    <td class="text-end">
                                        <a href="{{ route('receiving.show', $row->id) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Belum ada histori goods receipt.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

      
    </div>

    <div class="mt-2">{{ $rows->links() }}</div>
@endsection
