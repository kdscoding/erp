@extends('layouts.erp')

@php($title = 'Receiving History')
@php($header = 'Receiving History')
@php($headerSubtitle = 'Riwayat goods receipt yang sudah diposting atau dibatalkan beserta referensi shipment dan PO.')

@section('content')
    @php($rowsCollection = method_exists($rows, 'getCollection') ? $rows->getCollection() : collect($rows))
    @php($historyCount = $rowsCollection->count())
    @php($cancelledCount = $rowsCollection->where('status', \App\Support\DocumentTermCodes::GR_CANCELLED)->count())

    <section class="summary-chips mb-3">
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
            @if ($rows->isNotEmpty())
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
                <div class="px-3 pb-3">{{ $rows->links() }}</div>
            @else
                <div class="text-center text-muted py-4">
                    <i class="fas fa-history fa-2x mb-2 opacity-25"></i>
                    <p>Belum ada histori goods receipt.</p>
                </div>
            @endif
        </div>
    </section>
@endsection