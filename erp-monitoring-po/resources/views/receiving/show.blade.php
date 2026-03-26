@extends('layouts.erp')
@php($title = 'Receipt Detail')
@php($header = 'Receipt Detail')
@php($headerSubtitle = 'Detail goods receipt, referensi shipment, dan line penerimaan barang.')

@section('content')
    <div class="page-shell">
        <section class="page-head">
            <div class="page-head-main">
                <h2 class="page-section-title">{{ $receipt->gr_number }}</h2>
                <p class="page-section-subtitle">Dokumen goods receipt lengkap dengan referensi shipment, invoice, dan item yang diterima.</p>
            </div>

            <div class="page-actions">
                <a href="{{ route('receiving.history') }}" class="btn btn-sm btn-light">Kembali ke Riwayat GR</a>
            </div>
        </section>

        <section class="info-grid">
            <div class="info-box"><div class="info-label">No GR</div><div class="info-value">{{ $receipt->gr_number }}</div></div>
            <div class="info-box"><div class="info-label">Tanggal Terima</div><div class="info-value">{{ \Carbon\Carbon::parse($receipt->receipt_date)->format('d-m-Y') }}</div></div>
            <div class="info-box"><div class="info-label">Supplier</div><div class="info-value">{{ $receipt->supplier_name }}</div></div>
            <div class="info-box"><div class="info-label">Status</div><div class="info-value"><x-status-badge :status="$receipt->status" scope="gr" /></div></div>
            <div class="info-box"><div class="info-label">Shipment</div><div class="info-value">{{ $receipt->shipment_number ?: '-' }}</div></div>
            <div class="info-box"><div class="info-label">Delivery Note</div><div class="info-value">{{ $receipt->delivery_note_number ?: '-' }}</div></div>
            <div class="info-box"><div class="info-label">No Invoice</div><div class="info-value">{{ $receipt->invoice_number ?: '-' }}</div></div>
            <div class="info-box"><div class="info-label">Tanggal Invoice</div><div class="info-value">{{ $receipt->invoice_date ? \Carbon\Carbon::parse($receipt->invoice_date)->format('d-m-Y') : '-' }}</div></div>
            <div class="info-box"><div class="info-label">Currency</div><div class="info-value">{{ $receipt->invoice_currency ?: '-' }}</div></div>
            <div class="info-box"><div class="info-label">PO</div><div class="info-value">{{ $receipt->po_number }}</div></div>
            <div class="info-box"><div class="info-label">Warehouse</div><div class="info-value">{{ $receipt->warehouse_name ?: '-' }}</div></div>
            <div class="info-box"><div class="info-label">Penerima</div><div class="info-value">{{ $receipt->receiver_name ?: '-' }}</div></div>
            <div class="info-box"><div class="info-label">No Dokumen</div><div class="info-value">{{ $receipt->document_number ?: '-' }}</div></div>
            <div class="info-box"><div class="info-label">Catatan</div><div class="info-value">{{ $receipt->remark ?: '-' }}</div></div>
            @if ($receipt->cancel_reason)
                <div class="info-box"><div class="info-label">Alasan Batal</div><div class="info-value text-danger">{{ $receipt->cancel_reason }}</div></div>
            @endif
        </section>

        @if ($receipt->status === \App\Support\DocumentTermCodes::GR_POSTED)
            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Batalkan Goods Receipt</h3>
                        <div class="ui-surface-subtitle">Gunakan hanya jika posting GR salah. Sistem akan mengembalikan qty received ke shipment dan PO terkait.</div>
                    </div>
                </div>
                <div class="ui-surface-body">
                    <form method="POST" action="{{ route('receiving.cancel', $receipt->id) }}">
                        @csrf
                        @method('PATCH')
                        <div class="mb-2">
                            <label class="field-label">Alasan Pembatalan</label>
                            <textarea name="cancel_reason" class="form-control form-control-sm" rows="3" required></textarea>
                        </div>
                        <button class="btn btn-danger btn-sm" onclick="return confirm('Batalkan GR ini dan kembalikan qty receiving?')">Batalkan GR</button>
                    </form>
                </div>
            </section>
        @endif

        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Detail Item Goods Receipt</h3>
                    <div class="ui-surface-subtitle">Accepted, rejected, variance, dan outstanding PO ditampilkan dalam satu tabel detail.</div>
                </div>
            </div>
            <div class="table-wrap table-responsive">
                <table class="table table-hover ui-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Harga PO</th>
                            <th>Harga Invoice</th>
                            <th>Total Invoice</th>
                            <th>Qty Shipment</th>
                            <th>Qty Diterima</th>
                            <th>Accepted</th>
                            <th>Rejected</th>
                            <th>Variance</th>
                            <th>Total Received PO</th>
                            <th>Outstanding PO</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td><div class="doc-number">{{ $item->item_code }}</div><div class="doc-meta">{{ $item->item_name }}</div></td>
                                <td>{{ $item->po_unit_price !== null ? \App\Support\NumberFormatter::trim($item->po_unit_price) : '-' }}</td>
                                <td>{{ $item->invoice_unit_price !== null ? \App\Support\NumberFormatter::trim($item->invoice_unit_price) : '-' }}</td>
                                <td>{{ $item->invoice_line_total !== null ? \App\Support\NumberFormatter::trim($item->invoice_line_total) : '-' }}</td>
                                <td>{{ \App\Support\NumberFormatter::trim($item->shipped_qty ?? 0) }} {{ $item->unit_name }}</td>
                                <td>{{ \App\Support\NumberFormatter::trim($item->received_qty) }} {{ $item->unit_name }}</td>
                                <td>{{ \App\Support\NumberFormatter::trim($item->accepted_qty) }} {{ $item->unit_name }}</td>
                                <td>{{ \App\Support\NumberFormatter::trim($item->rejected_qty) }} {{ $item->unit_name }}</td>
                                <td>{{ \App\Support\NumberFormatter::trim($item->qty_variance) }}</td>
                                <td>{{ \App\Support\NumberFormatter::trim($item->total_po_received_qty) }} {{ $item->unit_name }}</td>
                                <td>{{ \App\Support\NumberFormatter::trim($item->outstanding_qty) }} {{ $item->unit_name }}</td>
                                <td>{{ $item->remark ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
