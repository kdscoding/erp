@extends('layouts.erp')
@php($title = 'Preview Draft Shipment')
@php($header = 'Preview Draft Shipment')
@php($headerSubtitle = 'Lihat isi draft shipment sebelum dikonfirmasi menjadi shipped.')

@push('styles')
    <style>
        .detail-label {
            font-size: .76rem;
            color: #7a8660;
        }

        .detail-cell {
            background: #f2f6cf;
        }

        .preview-section {
            border: 1px solid var(--lemon-line);
            border-radius: 12px;
            background: #fffef8;
            padding: .9rem 1rem;
            margin-bottom: 1rem;
        }

        .preview-section-title {
            font-size: .9rem;
            font-weight: 800;
            color: var(--lemon-ink);
            margin-bottom: .5rem;
            padding-bottom: .4rem;
            border-bottom: 2px solid var(--lemon-line);
        }

        .total-box {
            background: linear-gradient(135deg, #f9fbcf 0%, #f0f4d4 100%);
            border: 1px solid var(--lemon-line-strong);
            border-radius: 10px;
            padding: .7rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .total-item {
            text-align: center;
            min-width: 100px;
        }

        .total-label {
            font-size: .66rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #7a8660;
        }

        .total-value {
            font-size: 1rem;
            font-weight: 800;
            color: var(--lemon-ink);
            margin-top: 2px;
        }
    </style>
@endpush

@section('content')
    <div class="page-shell">

        <section class="page-head">
            <div class="page-actions">
                <a href="{{ route('shipments.preview', $shipment->id) }}" class="btn btn-sm btn-outline-info"><i
                        class="fas fa-redo"></i> Refresh</a>
                <a href="{{ route('shipments.export-excel', $shipment->id) }}"
                    class="btn btn-sm btn-outline-success">Export</a>
                <a href="{{ route('shipments.edit', $shipment->id) }}" class="btn btn-sm btn-outline-primary"><i
                        class="fas fa-edit"></i> Edit</a>
                <form method="POST" action="{{ route('shipments.cancel-draft', $shipment->id) }}" style="display:inline">
                    @csrf @method('PATCH')
                    <button class="btn btn-sm btn-outline-danger"
                        onclick="return confirm('Batalkan draft?')">Cancel</button>
                </form>
                <a href="{{ route('shipments.index') }}" class="btn btn-sm btn-light"><i class="fas fa-arrow-left"></i>
                    Back</a>
            </div>
        </section>

        <section class="preview-section">
            {{-- <div class="preview-section-title">{{ $shipment->shipment_number }}</div>
            <div class="mb-2">
                <span class="stage-badge stage-waiting">Draft</span>
                <span class="doc-meta ml-2">Supplier: {{ $shipment->supplier_name ?? '-' }}
                    ({{ $shipment->supplier_code ?? '-' }})</span>
            </div> --}}
            <div class="d-flex gap-3">
                <div class="flex-fill">
                    <div class="table-wrap table-responsive">
                        <table class="table table-sm table-bordered ui-table">
                            <tbody>
                                <tr>
                                    <th style="width:50%" class="detail-cell"><span class="detail-label">Tanggal
                                            Shipment</span></th>
                                    <td style="width:50%" class="align-middle">
                                        {{ \Carbon\Carbon::parse($shipment->shipment_date)->format('d-m-Y') }}</td>
                                </tr>
                                <tr>
                                    <th class="detail-cell"><span class="detail-label">Nomor Shipment</span></th>
                                    <td class="align-middle">{{ $shipment->shipment_number }}</td>
                                </tr>
                                <tr>
                                    <th class="detail-cell"><span class="detail-label">Currency</span></th>
                                    <td class="align-middle">{{ $shipment->invoice_currency ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="detail-cell"><span class="detail-label">Status</span></th>
                                    <td class="align-middle"><span class="stage-badge stage-waiting">Draft</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="flex-fill">
                    <div class="table-wrap table-responsive">
                        <table class="table table-sm table-bordered ui-table">
                            <tbody>
                                <tr>
                                    <th style="width:50%" class="detail-cell"><span class="detail-label">Tgl Invoice</span>
                                    </th>
                                    <td style="width:50%" class="align-middle">
                                        {{ $shipment->invoice_date ? \Carbon\Carbon::parse($shipment->invoice_date)->format('d-m-Y') : '-' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="detail-cell"><span class="detail-label">No Invoice</span></th>
                                    <td class="align-middle">{{ $shipment->invoice_number ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="detail-cell"><span class="detail-label">No Delivery Note</span></th>
                                    <td class="align-middle">{{ $shipment->delivery_note_number ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="detail-cell"><span class="detail-label">Supplier</span></th>
                                    <td class="align-middle">{{ $shipment->supplier_name ?? '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @if ($shipment->supplier_remark)
                <div class="mt-2 text-sm">{{ $shipment->supplier_remark }}</div>
            @endif


            <div class="table-wrap table-responsive">
                <table class="table table-sm table-bordered ui-table">
                    <thead>
                        <tr>
                            <th style="width:5%">No</th>
                            <th style="width:12%">PO</th>
                            <th style="width:12%">Item</th>
                            <th style="width:10%">Nama Item</th>
                            <th style="width:10%">Harga PO</th>
                            <th style="width:10%">Qty Kirim</th>
                            <th style="width:10%">Harga Inv</th>
                            <th style="width:12%">Total</th>
                            <th style="width:10%">Maks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lines as $index => $line)
                            @php($qty = (float) ($line->shipped_qty ?? 0))
                            @php($price = (float) ($line->invoice_unit_price ?? 0))
                            @php($total = $qty * $price)
                            @php($maxQty = (float) ($line->available_to_ship_qty ?? 0))
                            @php($poUnitPrice = (float) ($line->po_unit_price ?? 0))
                            <tr>
                                <td class="align-middle">{{ $index + 1 }}</td>
                                <td class="align-middle">{{ $line->po_number ?? '-' }}</td>
                                <td class="align-middle">
                                    <div class="doc-number">{{ $line->item_code }}</div>
                                </td>
                                <td class="align-middle">{{ $line->item_name }}</td>
                                <td class="text-end align-middle">
                                    {{ $poUnitPrice > 0 ? \App\Support\NumberFormatter::trim($poUnitPrice) : '-' }}</td>
                                <td class="text-end align-middle">{{ \App\Support\NumberFormatter::trim($qty) }}</td>
                                <td class="text-end align-middle">
                                    {{ $price > 0 ? \App\Support\NumberFormatter::trim($price) : '-' }}</td>
                                <td class="text-end align-middle font-weight-700">
                                    {{ $total > 0 ? \App\Support\NumberFormatter::trim($total) : '-' }}</td>
                                <td class="text-end align-middle">{{ \App\Support\NumberFormatter::trim($maxQty) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background: var(--lemon-bg); font-weight: 700;">
                            <td colspan="5" class="text-end">TOTAL</td>
                            <td class="text-end">
                                {{ \App\Support\NumberFormatter::trim($lines->sum(fn($l) => (float) ($l->shipped_qty ?? 0))) }}
                            </td>
                            <td></td>
                            <td class="text-end">
                                {{ \App\Support\NumberFormatter::trim($lines->sum(fn($l) => (float) ($l->shipped_qty ?? 0) * (float) ($l->invoice_unit_price ?? 0))) }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>

        {{-- <section class="preview-section">
            <div class="preview-section-title">Ringkasan</div>
            @php($totalQty = $lines->sum(fn($l) => (float) ($l->shipped_qty ?? 0)))
            @php($totalReceived = $lines->sum(fn($l) => (float) ($l->received_qty ?? 0)))
            @php($totalOpen = $lines->sum(fn($l) => (float) ($l->shipped_qty ?? 0) - (float) ($l->received_qty ?? 0)))
            @php($grandTotal = $lines->sum(fn($l) => (float) ($l->shipped_qty ?? 0) * (float) ($l->invoice_unit_price ?? 0)))
            <div class="total-box">
                <div class="total-item">
                    <div class="total-label">Total Qty Kirim</div>
                    <div class="total-value">{{ \App\Support\NumberFormatter::trim($totalQty) }}</div>
                </div>
                <div class="total-item">
                    <div class="total-label">Total Received</div>
                    <div class="total-value">{{ \App\Support\NumberFormatter::trim($totalReceived) }}</div>
                </div>
                <div class="total-item">
                    <div class="total-label">Open</div>
                    <div class="total-value">{{ \App\Support\NumberFormatter::trim($totalOpen) }}</div>
                </div>
                <div class="total-item">
                    <div class="total-label">Grand Total</div>
                    <div class="total-value">{{ \App\Support\NumberFormatter::trim($grandTotal) }}</div>
                </div>
            </div>
        </section> --}}

    </div>
@endsection
