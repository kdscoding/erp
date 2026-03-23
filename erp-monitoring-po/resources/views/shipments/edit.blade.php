@extends('layouts.erp')
@php($title = 'Edit Draft Shipment')
@php($header = 'Edit Draft Shipment')

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
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

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Ubah Draft {{ $shipment->shipment_number }}</h3>
                <div class="d-flex gap-2">
                    <a href="{{ route('shipments.show', $shipment->id) }}" class="btn btn-sm btn-light">Lihat Detail</a>
                    <a href="{{ route('shipments.history', ['focus' => $shipment->id]) }}"
                        class="btn btn-sm btn-light">Kembali ke Riwayat</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label">Supplier</label>
                        <input type="text" class="form-control form-control-sm" value="{{ $shipment->supplier_name }}"
                            disabled>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Delivery Note</label>
                        <input type="text" name="delivery_note_number" class="form-control form-control-sm"
                            value="{{ old('delivery_note_number', $shipment->delivery_note_number) }}" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Tanggal Dokumen</label>
                        <input type="date" name="shipment_date" class="form-control form-control-sm"
                            value="{{ old('shipment_date', \Carbon\Carbon::parse($shipment->shipment_date)->format('Y-m-d')) }}"
                            required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">No Invoice</label>
                        <input type="text" name="invoice_number" class="form-control form-control-sm"
                            value="{{ old('invoice_number', $shipment->invoice_number) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Tanggal Invoice</label>
                        <input type="date" name="invoice_date" class="form-control form-control-sm"
                            value="{{ old('invoice_date', $shipment->invoice_date ? \Carbon\Carbon::parse($shipment->invoice_date)->format('Y-m-d') : '') }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Currency</label>
                        <input type="text" name="invoice_currency" class="form-control form-control-sm"
                            value="{{ old('invoice_currency', $shipment->invoice_currency) }}" maxlength="10">
                    </div>

                    <div class="col-md-7">
                        <label class="form-label">Catatan</label>
                        <input type="text" name="supplier_remark" class="form-control form-control-sm"
                            value="{{ old('supplier_remark', $shipment->supplier_remark) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Item Draft Shipment</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover mb-0">
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
                                        <label class="form-check-label"
                                            for="keep_{{ $line->shipment_item_id }}">Pertahankan</label>
                                    </div>
                                </td>
                                <td>{{ $line->po_number }}</td>
                                <td><strong>{{ $line->item_code }}</strong><br>{{ $line->item_name }}</td>
                                <td>{{ $line->po_unit_price !== null ? \App\Support\NumberFormatter::trim($line->po_unit_price) : '-' }}
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

    <script>
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
