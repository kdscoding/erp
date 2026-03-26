@extends('layouts.erp')
@php($title = 'Edit Draft Shipment')
@php($header = 'Edit Draft Shipment')

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

        .field-label {
            display: block;
            font-size: .76rem;
            font-weight: 700;
            letter-spacing: .02em;
            color: #52603d;
            margin-bottom: .35rem;
        }

        .field-readonly {
            background: #f8faf4;
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

        .action-stack {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
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

    <form method="POST" action="{{ route('shipments.update', $shipment->id) }}">
        @csrf
        @method('PUT')

        <div class="card ui-card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h3 class="card-title">Ubah Draft {{ $shipment->shipment_number }}</h3>
                    <div class="section-note">Dokumen shipment draft masih bisa direvisi sebelum dikonfirmasi shipped.</div>
                </div>
                <div class="action-stack">
                    <a href="{{ route('shipments.export-excel', $shipment->id) }}"
                        class="btn btn-sm btn-outline-success">Export Excel</a>
                    <button type="button" class="btn btn-sm btn-outline-success"
                        onclick="openImportDraftModal({{ $shipment->id }}, '{{ $shipment->shipment_number }}')">
                        Import Excel
                    </button>
                    <a href="{{ route('shipments.show', $shipment->id) }}" class="btn btn-sm btn-light">Lihat Detail</a>
                    <a href="{{ route('shipments.index', ['focus' => $shipment->id]) }}"
                        class="btn btn-sm btn-light">Back to Worklist</a>
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
                    <div class="col-md-5">
                        <div class="soft-box">
                            <div class="soft-box-title">Supplier</div>
                            <div class="soft-box-value">{{ $shipment->supplier_name }}</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="soft-box">
                            <div class="soft-box-title">Status</div>
                            <div class="soft-box-value">{{ $shipment->status }}</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="soft-box">
                            <div class="soft-box-title">Total Line</div>
                            <div class="soft-box-value">{{ $lines->count() }}</div>
                        </div>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="field-label">Supplier</label>
                        <input type="text" class="form-control form-control-sm"
                            value="{{ $shipment->supplier_name }}" disabled>
                    </div>

                    <div class="col-md-3">
                        <label class="field-label">Delivery Note</label>
                        <input type="text" name="delivery_note_number" class="form-control form-control-sm"
                            value="{{ old('delivery_note_number', $shipment->delivery_note_number) }}" required>
                    </div>

                    <div class="col-md-2">
                        <label class="field-label">Tanggal Dokumen</label>
                        <input type="date" name="shipment_date" class="form-control form-control-sm"
                            value="{{ old('shipment_date', \Carbon\Carbon::parse($shipment->shipment_date)->format('Y-m-d')) }}"
                            required>
                    </div>

                    <div class="col-md-3">
                        <label class="field-label">No Invoice</label>
                        <input type="text" name="invoice_number" class="form-control form-control-sm"
                            value="{{ old('invoice_number', $shipment->invoice_number) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="field-label">Tanggal Invoice</label>
                        <input type="date" name="invoice_date" class="form-control form-control-sm"
                            value="{{ old('invoice_date', $shipment->invoice_date ? \Carbon\Carbon::parse($shipment->invoice_date)->format('Y-m-d') : '') }}">
                    </div>

                    <div class="col-md-2">
                        <label class="field-label">Currency</label>
                        <input type="text" name="invoice_currency" class="form-control form-control-sm"
                            value="{{ old('invoice_currency', $shipment->invoice_currency) }}" maxlength="10">
                    </div>

                    <div class="col-md-7">
                        <label class="field-label">Catatan</label>
                        <input type="text" name="supplier_remark" class="form-control form-control-sm"
                            value="{{ old('supplier_remark', $shipment->supplier_remark) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="card ui-card">
            <div class="card-header">
                <h3 class="card-title">Item Draft Shipment</h3>
                <div class="section-note">User bisa ubah qty draft, harga invoice, atau mengeluarkan line yang tidak jadi
                    dipakai.</div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover mb-0 builder-table">
                    <thead>
                        <tr>
                            <th>Pakai</th>
                            <th>PO</th>
                            <th>Item</th>
                            <th>Harga PO</th>
                            <th>Qty Draft</th>
                            <th>Harga Invoice</th>
                            <th>Total Invoice</th>
                            <th>Maks Bisa Dipakai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lines as $line)
                            @php($maxQty = $line->available_to_ship_qty)
                            @php($qtyValue = old("shipment_items.{$loop->index}.shipped_qty", $line->shipped_qty))
                            @php($invoicePriceValue = old("shipment_items.{$loop->index}.invoice_unit_price", $line->invoice_unit_price))
                            <tr>
                                <td>
                                    <input type="hidden" name="shipment_items[{{ $loop->index }}][id]"
                                        value="{{ $line->shipment_item_id }}">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                            name="shipment_items[{{ $loop->index }}][keep]" value="1"
                                            id="keep_{{ $line->shipment_item_id }}"
                                            {{ old("shipment_items.{$loop->index}.keep", '1') === '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="keep_{{ $line->shipment_item_id }}">
                                            Pertahankan
                                        </label>
                                    </div>
                                </td>
                                <td>{{ $line->po_number }}</td>
                                <td>
                                    <strong>{{ $line->item_code }}</strong><br>{{ $line->item_name }}
                                </td>
                                <td>
                                    {{ $line->po_unit_price !== null ? \App\Support\NumberFormatter::trim($line->po_unit_price) : '-' }}
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0.01"
                                        max="{{ \App\Support\NumberFormatter::input($maxQty) }}"
                                        name="shipment_items[{{ $loop->index }}][shipped_qty]"
                                        class="form-control form-control-sm edit-qty-input"
                                        data-index="{{ $loop->index }}"
                                        value="{{ \App\Support\NumberFormatter::input($qtyValue) }}" required>
                                </td>
                                <td>
                                    <input type="number" step="0.0001" min="0"
                                        name="shipment_items[{{ $loop->index }}][invoice_unit_price]"
                                        class="form-control form-control-sm edit-price-input"
                                        data-index="{{ $loop->index }}" value="{{ $invoicePriceValue }}">
                                </td>
                                <td>
                                    <input type="text"
                                        class="form-control form-control-sm field-readonly edit-line-total"
                                        data-index="{{ $loop->index }}" readonly>
                                </td>
                                <td>{{ \App\Support\NumberFormatter::trim($maxQty) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-end">
                <button class="btn btn-primary btn-sm">Simpan Perubahan Draft</button>
            </div>
        </div>
    </form>

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
                    <input type="hidden" name="shipment_id" id="import_shipment_id" value="{{ $shipment->id }}">
                    <div class="mb-2 small text-muted" id="import_shipment_label">
                        Draft target: {{ $shipment->shipment_number }}
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
        function openImportDraftModal(id, number) {
            document.getElementById('import_shipment_id').value = id;
            document.getElementById('import_shipment_label').textContent = 'Draft target: ' + number;

            const modalElement = document.getElementById('importDraftModal');
            if (modalElement && typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            }
        }

        (function() {
            const formatNumber = (value) => {
                const parsed = parseFloat(value || 0);
                if (Number.isNaN(parsed)) return '-';
                return new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(parsed);
            };

            const recalcTotals = () => {
                document.querySelectorAll('.edit-line-total').forEach((output) => {
                    const index = output.dataset.index;
                    const qtyInput = document.querySelector(`.edit-qty-input[data-index="${index}"]`);
                    const priceInput = document.querySelector(`.edit-price-input[data-index="${index}"]`);

                    const qty = parseFloat(qtyInput?.value || 0);
                    const price = parseFloat(priceInput?.value || 0);

                    if (!priceInput || priceInput.value === '' || Number.isNaN(price)) {
                        output.value = '-';
                        return;
                    }

                    output.value = formatNumber(qty * price);
                });
            };

            document.querySelectorAll('.edit-qty-input, .edit-price-input').forEach((input) => {
                input.addEventListener('input', recalcTotals);
                input.addEventListener('change', recalcTotals);
            });

            recalcTotals();
        })();
    </script>
@endsection