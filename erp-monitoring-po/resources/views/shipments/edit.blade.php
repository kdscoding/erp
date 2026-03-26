@extends('layouts.erp')
@php($title = 'Edit Draft Shipment')
@php($header = 'Edit Draft Shipment')
@php($headerSubtitle = 'Revisi draft shipment sebelum dikonfirmasi menjadi shipped.')

@section('content')
    <div class="page-shell">
        <section class="page-head">
            <div class="page-head-main">
                <h2 class="page-section-title">{{ $shipment->shipment_number }}</h2>
                <p class="page-section-subtitle">Draft masih bisa direvisi sebelum dikonfirmasi menjadi shipped.</p>
            </div>

            <div class="page-actions">
                <a href="{{ route('shipments.create') }}" class="btn btn-sm btn-light">Back to Create Draft</a>
                <a href="{{ route('shipments.show', $shipment->id) }}" class="btn btn-sm btn-light">Lihat Detail</a>
                <a href="{{ route('shipments.export-excel', $shipment->id) }}" class="btn btn-sm btn-outline-success">Export Excel</a>
            </div>
        </section>

        <form method="POST" action="{{ route('shipments.update', $shipment->id) }}">
            @csrf
            @method('PUT')

            <section class="info-grid">
                <div class="info-box"><div class="info-label">No Shipment</div><div class="info-value">{{ $shipment->shipment_number }}</div></div>
                <div class="info-box"><div class="info-label">Supplier</div><div class="info-value">{{ $shipment->supplier_name }}</div></div>
                <div class="info-box"><div class="info-label">Status</div><div class="info-value">{{ $shipment->status }}</div></div>
            </section>

            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Header Draft Shipment</h3>
                        <div class="ui-surface-subtitle">Perbarui dokumen supplier, qty line, dan harga invoice draft.</div>
                    </div>
                </div>

                <div class="ui-surface-body">
                    <div class="filter-grid px-0 pt-0 pb-0">
                        <div class="span-4"><label class="field-label">Supplier</label><input type="text" class="form-control form-control-sm" value="{{ $shipment->supplier_name }}" disabled></div>
                        <div class="span-3"><label class="field-label">Delivery Note</label><input type="text" name="delivery_note_number" class="form-control form-control-sm" value="{{ old('delivery_note_number', $shipment->delivery_note_number) }}" required></div>
                        <div class="span-2"><label class="field-label">Tanggal Dokumen</label><input type="date" name="shipment_date" class="form-control form-control-sm" value="{{ old('shipment_date', \Carbon\Carbon::parse($shipment->shipment_date)->format('Y-m-d')) }}" required></div>
                        <div class="span-3"><label class="field-label">No Invoice</label><input type="text" name="invoice_number" class="form-control form-control-sm" value="{{ old('invoice_number', $shipment->invoice_number) }}"></div>
                        <div class="span-3"><label class="field-label">Tanggal Invoice</label><input type="date" name="invoice_date" class="form-control form-control-sm" value="{{ old('invoice_date', $shipment->invoice_date ? \Carbon\Carbon::parse($shipment->invoice_date)->format('Y-m-d') : '') }}"></div>
                        <div class="span-2"><label class="field-label">Currency</label><input type="text" name="invoice_currency" class="form-control form-control-sm" value="{{ old('invoice_currency', $shipment->invoice_currency) }}" maxlength="10"></div>
                        <div class="span-7"><label class="field-label">Catatan</label><input type="text" name="supplier_remark" class="form-control form-control-sm" value="{{ old('supplier_remark', $shipment->supplier_remark) }}"></div>
                    </div>
                </div>
            </section>

            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Item Draft Shipment</h3>
                        <div class="ui-surface-subtitle">User bisa ubah qty draft, harga invoice, atau mengeluarkan line yang tidak dipakai.</div>
                    </div>
                </div>

                <div class="table-wrap table-responsive">
                    <table class="table table-hover ui-table">
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
                                        <input type="hidden" name="shipment_items[{{ $loop->index }}][id]" value="{{ $line->shipment_item_id }}">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="shipment_items[{{ $loop->index }}][keep]" value="1" id="keep_{{ $line->shipment_item_id }}" {{ old("shipment_items.{$loop->index}.keep", '1') === '1' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="keep_{{ $line->shipment_item_id }}">Pertahankan</label>
                                        </div>
                                    </td>
                                    <td>{{ $line->po_number }}</td>
                                    <td><div class="doc-number">{{ $line->item_code }}</div><div class="doc-meta">{{ $line->item_name }}</div></td>
                                    <td>{{ $line->po_unit_price !== null ? \App\Support\NumberFormatter::trim($line->po_unit_price) : '-' }}</td>
                                    <td><input type="number" step="0.01" min="0.01" max="{{ \App\Support\NumberFormatter::input($maxQty) }}" name="shipment_items[{{ $loop->index }}][shipped_qty]" class="form-control form-control-sm edit-qty-input" data-index="{{ $loop->index }}" value="{{ \App\Support\NumberFormatter::input($qtyValue) }}" required></td>
                                    <td><input type="number" step="0.0001" min="0" name="shipment_items[{{ $loop->index }}][invoice_unit_price]" class="form-control form-control-sm edit-price-input" data-index="{{ $loop->index }}" value="{{ $invoicePriceValue }}"></td>
                                    <td><input type="text" class="form-control form-control-sm bg-light edit-line-total" data-index="{{ $loop->index }}" readonly></td>
                                    <td>{{ \App\Support\NumberFormatter::trim($maxQty) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="ui-surface-body d-flex justify-content-end pt-0">
                    <button class="btn btn-primary btn-sm">Simpan Perubahan Draft</button>
                </div>
            </section>
        </form>
    </div>

    <script>
        (function() {
            const formatNumber = (value) => {
                const parsed = parseFloat(value || 0);
                if (Number.isNaN(parsed)) return '-';
                return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(parsed);
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
