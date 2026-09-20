@extends('layouts.erp')

@php($title = 'Receiving Dashboard')
@php($header = 'Receiving Dashboard')
@php($headerSubtitle = 'Overview dokumen shipment yang siap diterima dan riwayat goods receipt terbaru.')

@section('content')
    <section class="summary-chips">
        <div class="summary-chip">
            <div class="summary-chip-label">Dokumen Shipment</div>
            <div class="summary-chip-value">{{ $documentCount }}</div>
        </div>
        <div class="summary-chip">
            <div class="summary-chip-label">Siap Diproses</div>
            <div class="summary-chip-value">{{ $readyCount }}</div>
        </div>
        <div class="summary-chip">
            <div class="summary-chip-label">Total Outstanding Qty</div>
            <div class="summary-chip-value">{{ \App\Support\NumberFormatter::trim($outstandingQty) }}</div>
        </div>
        <div class="summary-chip">
            <div class="summary-chip-label">Total GR Posted</div>
            <div class="summary-chip-value">{{ $recentHistoryCount - $cancelledCount }}</div>
        </div>
        <div class="summary-chip">
            <div class="summary-chip-label">GR Cancelled</div>
            <div class="summary-chip-value">{{ $cancelledCount }}</div>
        </div>
    </section>

    <div class="row g-3">
        <div class="col-lg-8">
            <section class="ui-surface h-100 d-flex flex-column">
                <div class="ui-surface-head">
                    <div class="d-flex align-items-center gap-2">
                        <h3 class="ui-surface-title mb-0">Shipment Menunggu Receiving</h3>
                        <span class="badge bg-primary">{{ $documentCount }}</span>
                    </div>
                    <a href="{{ route('receiving.pending') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="ui-surface-body table-responsive">
                    @if ($shipmentDocuments->isNotEmpty())
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
                                @foreach ($shipmentDocuments->take(10) as $document)
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
                                            <a href="{{ route('receiving.create', ['shipment' => $document->id, 'supplier_id' => request('supplier_id'), 'document_number' => request('document_number'), 'keyword' => request('keyword')]) }}"
                                                class="btn btn-sm btn-primary">Mulai Receiving</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-box-open fa-2x mb-2 opacity-25"></i>
                            <p>Tidak ada shipment yang siap diterima.</p>
                        </div>
                    @endif
                </div>
            </section>
        </div>

        <div class="col-lg-4">
            <section class="ui-surface h-100 d-flex flex-column">
                <div class="ui-surface-head">
                    <h3 class="ui-surface-title mb-0">Quick Actions</h3>
                </div>
                <div class="ui-surface-body d-flex flex-column gap-2">
                    <a href="{{ route('receiving.pending') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i> Mulai Receiving Baru
                    </a>
                    <a href="{{ route('receiving.history') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-history me-2"></i> Riwayat Goods Receipt
                    </a>
                    <a href="{{ route('shipments.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-ship me-2"></i> Shipment Worklist
                    </a>
                    <a href="{{ route('po.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-file-alt me-2"></i> Purchase Orders
                    </a>
                </div>
            </section>
        </div>
    </div>

    <div class="row g-3 mt-2">
        <div class="col-12">
            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Riwayat Goods Receipt Terbaru</h3>
                        <div class="ui-surface-subtitle">10 dokumen GR terakhir yang diposting atau dibatalkan.</div>
                    </div>
                    <a href="{{ route('receiving.history') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="table-wrap table-responsive">
                    @if ($recentRows->isNotEmpty())
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
                                @foreach ($recentRows as $row)
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
                                @endforeach
                            </tbody>
                        </table>
                        <div class="px-3 pb-3">{{ $recentRows->links() }}</div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-history fa-2x mb-2 opacity-25"></i>
                            <p>Belum ada histori goods receipt.</p>
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
@endsection