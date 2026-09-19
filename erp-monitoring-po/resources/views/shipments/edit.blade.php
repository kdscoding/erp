@extends('layouts.erp')
@php($title = 'Edit Draft Shipment')
@php($header = 'Edit Draft Shipment')
@php($headerSubtitle = 'Revisi draft sebelum dikonfirmasi menjadi shipped.')

@push('styles')
<style>
    .sticky-action-bar {
        position: sticky; bottom: 0; z-index: 40;
        background: linear-gradient(180deg, rgba(255,255,255,.95) 0%, rgba(252,254,239,.98) 100%);
        border-top: 2px solid var(--lemon-line);
        padding: .7rem 1rem; margin-top: 1rem; backdrop-filter: blur(4px);
    }
    .breadcrumb-nav { display: flex; align-items: center; gap: .35rem; font-size: .76rem; color: #7a8660; margin-bottom: .5rem; }
    .breadcrumb-nav a { color: var(--lemon-green-deep); text-decoration: none; }
    .breadcrumb-nav a:hover { text-decoration: underline; }
    .breadcrumb-nav .sep { color: #b5c198; }
    .info-box { border: 1px solid var(--lemon-line); border-radius: 10px; background: #fffef8; padding: .5rem .7rem; }
    .info-label { font-size: .66rem; text-transform: uppercase; letter-spacing: .06em; color: #7a8660; }
    .info-value { font-size: .92rem; font-weight: 700; color: var(--lemon-ink); margin-top: 2px; }
</style>
@endpush

@section('content')
    <div class="page-shell">

        <nav class="breadcrumb-nav">
            <a href="{{ route('dashboard') }}">Home</a><span class="sep">/</span><a href="{{ route('shipments.index') }}">Shipment</a><span class="sep">/</span><span>Edit Draft</span>
        </nav>

        <section class="page-head">
            <div class="page-head-main">
                <h2 class="page-section-title">{{ $shipment->shipment_number }}</h2>
                <p class="page-section-subtitle">Draft masih bisa direvisi sebelum dikonfirmasi.</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('shipments.show', $shipment->id) }}" class="btn btn-sm btn-light">View</a>
                <a href="{{ route('shipments.export-excel', $shipment->id) }}" class="btn btn-sm btn-outline-success">Export</a>
                <form method="POST" action="{{ route('shipments.cancel-draft', $shipment->id) }}" style="display:inline">
                    @csrf @method('PATCH')
                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Batalkan draft?')">Cancel</button>
                </form>
            </div>
        </section>

        <section class="info-grid mb-3">
            <div class="info-box"><div class="info-label">No Shipment</div><div class="info-value">{{ $shipment->shipment_number }}</div></div>
            <div class="info-box"><div class="info-label">Supplier</div><div class="info-value">{{ $shipment->supplier_name }}</div></div>
            <div class="info-box"><div class="info-label">Status</div><div class="info-value">{{ $shipment->status }}</div></div>
        </section>

        <form method="POST" action="{{ route('shipments.update', $shipment->id) }}" id="editForm">
            @csrf @method('PUT')

            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div><h3 class="ui-surface-title">Header</h3></div>
                </div>
                <div class="ui-surface-body">
                    <div class="filter-grid px-0 pt-0 pb-0">
                        <div class="span-4"><label class="field-label">Supplier</label><input type="text" class="form-control" value="{{ $shipment->supplier_name }}" disabled></div>
                        <div class="span-3"><label class="field-label">Delivery Note</label><input type="text" name="delivery_note_number" class="form-control" value="{{ old('delivery_note_number', $shipment->delivery_note_number) }}" required></div>
                        <div class="span-2"><label class="field-label">Tanggal</label><input type="date" name="shipment_date" class="form-control" value="{{ old('shipment_date', \Carbon\Carbon::parse($shipment->shipment_date)->format('Y-m-d')) }}" required></div>
                        <div class="span-3"><label class="field-label">No Invoice</label><input type="text" name="invoice_number" class="form-control" value="{{ old('invoice_number', $shipment->invoice_number) }}"></div>
                        <div class="span-3"><label class="field-label">Tgl Invoice</label><input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date', $shipment->invoice_date ? \Carbon\Carbon::parse($shipment->invoice_date)->format('Y-m-d') : '') }}"></div>
                        <div class="span-2"><label class="field-label">Currency</label><input type="text" name="invoice_currency" class="form-control" value="{{ old('invoice_currency', $shipment->invoice_currency) }}" maxlength="10"></div>
                        <div class="span-7"><label class="field-label">Catatan</label><input type="text" name="supplier_remark" class="form-control" value="{{ old('supplier_remark', $shipment->supplier_remark) }}"></div>
                    </div>
                </div>
            </section>

            <section class="ui-surface mt-3">
                <div class="ui-surface-head">
                    <div><h3 class="ui-surface-title">Line Items</h3></div>
                </div>
                <div class="ui-surface-body pt-0">
                    <div class="mb-3">
                        <div class="ui-surface-subtitle mb-2">Alokasi per item vs shipment lain (collapsed detail):</div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered ui-table mb-0">
                                <thead><tr><th>Item</th><th>PO Outstanding</th><th>Open Lain</th><th>Draft Ini</th><th>Maks</th></tr></thead>
                                <tbody>
                                    @foreach ($lines as $line)
                                        <tr>
                                            <td><div class="doc-number">{{ $line->item_code }}</div><div class="doc-meta">{{ $line->item_name }} | {{ $line->po_number }}</div></td>
                                            <td>{{ \App\Support\NumberFormatter::trim($line->outstanding_qty) }}</td>
                                            <td>{{ \App\Support\NumberFormatter::trim($line->other_open_shipment_qty) }}</td>
                                            <td>{{ \App\Support\NumberFormatter::trim($line->shipped_qty) }}</td>
                                            <td>{{ \App\Support\NumberFormatter::trim($line->available_to_ship_qty) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="table-wrap table-responsive">
                        <table class="table table-hover ui-table">
                            <thead><tr><th>Pakai</th><th>PO</th><th>Item</th><th>Harga PO</th><th>Qty</th><th>Harga Inv</th><th>Total</th><th>Maks</th></tr></thead>
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
                                                <label class="form-check-label" for="keep_{{ $line->shipment_item_id }}">Keep</label>
                                            </div>
                                        </td>
                                        <td>{{ $line->po_number }}</td>
                                        <td><div class="doc-number">{{ $line->item_code }}</div><div class="doc-meta">{{ $line->item_name }}</div></td>
                                        <td>{{ $line->po_unit_price !== null ? \App\Support\NumberFormatter::trim($line->po_unit_price) : '-' }}</td>
                                        <td><input type="number" step="0.01" min="0.01" max="{{ \App\Support\NumberFormatter::input($maxQty) }}" name="shipment_items[{{ $loop->index }}][shipped_qty]" class="form-control edit-qty-input" data-index="{{ $loop->index }}" value="{{ \App\Support\NumberFormatter::input($qtyValue) }}" required></td>
                                        <td><input type="number" step="0.0001" min="0" name="shipment_items[{{ $loop->index }}][invoice_unit_price]" class="form-control edit-price-input" data-index="{{ $loop->index }}" value="{{ $invoicePriceValue }}"></td>
                                        <td><input type="text" class="form-control bg-light edit-line-total" data-index="{{ $loop->index }}" readonly></td>
                                        <td>{{ \App\Support\NumberFormatter::trim($maxQty) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <div class="sticky-action-bar d-flex justify-content-between align-items-center">
                <div class="d-flex gap-2">
                    <a href="{{ route('shipments.show', $shipment->id) }}" class="btn btn-sm btn-light"><i class="fas fa-arrow-left"></i> Back</a>
                </div>
                <div class="page-actions">
                    <button type="button" class="btn btn-light btn-sm" onclick="if(confirm('Simpan?')) document.getElementById('editForm').submit()">Preview</button>
                    <button type="submit" class="btn btn-success btn-sm">Simpan</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        (function() {
            const formatNumber = (v) => { const p = parseFloat(v || 0); if (Number.isNaN(p)) return '-'; return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(p); };
            const recalc = () => {
                document.querySelectorAll('.edit-line-total').forEach(o => {
                    const i = o.dataset.index;
                    const q = parseFloat(document.querySelector(`.edit-qty-input[data-index="${i}"]`)?.value || 0);
                    const p = parseFloat(document.querySelector(`.edit-price-input[data-index="${i}"]`)?.value || 0);
                    o.value = (!document.querySelector(`.edit-price-input[data-index="${i}"]`) || document.querySelector(`.edit-price-input[data-index="${i}"]`).value === '' || Number.isNaN(p)) ? '-' : formatNumber(q * p);
                });
            };
            document.querySelectorAll('.edit-qty-input, .edit-price-input').forEach(inp => { inp.addEventListener('input', recalc); inp.addEventListener('change', recalc); });
            recalc();
        })();
    </script>
@endsection