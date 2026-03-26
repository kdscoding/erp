@extends('layouts.erp')
@php($title = 'Detail Shipment')
@php($header = 'Detail Shipment')

@section('content')
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

        .soft-box {
            border: 1px solid #e7eadf;
            background: #fafcf5;
            border-radius: 14px;
            padding: .85rem .9rem;
            height: 100%;
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
            word-break: break-word;
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

        .action-stack {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
        }
    </style>

    <div class="card ui-card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h3 class="card-title">Dokumen Shipment {{ $shipment->shipment_number }}</h3>
                <div class="section-note">Detail shipment, dokumen supplier, dan line item yang ikut terkirim.</div>
            </div>
            <div class="action-stack">
                <a href="{{ route('shipments.index', ['focus' => $shipment->id]) }}" class="btn btn-sm btn-light">
                    Back to Worklist
                </a>
                @if ($shipment->status === \App\Support\DocumentTermCodes::SHIPMENT_DRAFT)
                    <a href="{{ route('shipments.edit', $shipment->id) }}" class="btn btn-sm btn-primary">Edit Draft</a>
                    <a href="{{ route('shipments.export-excel', $shipment->id) }}"
                        class="btn btn-sm btn-outline-success">Export Excel</a>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="soft-box">
                        <div class="soft-box-title">No Shipment</div>
                        <div class="soft-box-value">{{ $shipment->shipment_number }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="soft-box">
                        <div class="soft-box-title">Supplier</div>
                        <div class="soft-box-value">{{ $shipment->supplier_name }}</div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="soft-box">
                        <div class="soft-box-title">Status</div>
                        <div class="soft-box-value">
                            <x-status-badge :status="$shipment->status" scope="shipment" />
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="soft-box">
                        <div class="soft-box-title">Tanggal Dokumen</div>
                        <div class="soft-box-value">
                            {{ \Carbon\Carbon::parse($shipment->shipment_date)->format('d-m-Y') }}
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="soft-box">
                        <div class="soft-box-title">Delivery Note</div>
                        <div class="soft-box-value">{{ $shipment->delivery_note_number ?: '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="soft-box">
                        <div class="soft-box-title">Invoice</div>
                        <div class="soft-box-value">{{ $shipment->invoice_number ?: '-' }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="soft-box">
                        <div class="soft-box-title">Invoice Date</div>
                        <div class="soft-box-value">
                            {{ $shipment->invoice_date ? \Carbon\Carbon::parse($shipment->invoice_date)->format('d-m-Y') : '-' }}
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="soft-box">
                        <div class="soft-box-title">Currency</div>
                        <div class="soft-box-value">{{ $shipment->invoice_currency ?: '-' }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="soft-box">
                        <div class="soft-box-title">Catatan</div>
                        <div class="soft-box-value">{{ $shipment->supplier_remark ?: '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card ui-card">
        <div class="card-header">
            <h3 class="card-title">Item Dalam Dokumen Shipment</h3>
            <div class="section-note">Harga invoice tetap menjadi referensi dari dokumen shipment, bukan dari receiving.</div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0 builder-table">
                <thead>
                    <tr>
                        <th>PO</th>
                        <th>Item</th>
                        <th>Harga PO</th>
                        <th>Harga Invoice</th>
                        <th>Total Invoice</th>
                        <th>Qty Dikirim</th>
                        <th>Sudah Diterima</th>
                        <th>Sisa Kiriman</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lines as $line)
                        <tr>
                            <td>{{ $line->po_number }}</td>
                            <td>
                                <strong>{{ $line->item_code }}</strong><br>{{ $line->item_name }}
                            </td>
                            <td>
                                {{ $line->po_unit_price !== null ? \App\Support\NumberFormatter::trim($line->po_unit_price) : '-' }}
                            </td>
                            <td>
                                {{ $line->invoice_unit_price !== null ? \App\Support\NumberFormatter::trim($line->invoice_unit_price) : '-' }}
                            </td>
                            <td>
                                {{ $line->invoice_line_total !== null ? \App\Support\NumberFormatter::trim($line->invoice_line_total) : '-' }}
                            </td>
                            <td>{{ \App\Support\NumberFormatter::trim($line->shipped_qty) }}</td>
                            <td>{{ \App\Support\NumberFormatter::trim($line->received_qty) }}</td>
                            <td>{{ \App\Support\NumberFormatter::trim(max(0, $line->shipped_qty - $line->received_qty)) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection