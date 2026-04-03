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
            @php($splitShipmentBoard = $splitShipmentBoard ?? collect())

            <section class="info-grid">
                <div class="info-box"><div class="info-label">No Shipment</div><div class="info-value">{{ $shipment->shipment_number }}</div></div>
                <div class="info-box"><div class="info-label">Supplier</div><div class="info-value">{{ $shipment->supplier_name }}</div></div>
                <div class="info-box"><div class="info-label">Status</div><div class="info-value">{{ $shipment->status }}</div></div>
            </section>

            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Tools Draft Shipment</h3>
                        <div class="ui-surface-subtitle">Export, import ulang, atau batalkan draft dari satu area kerja.</div>
                    </div>
                </div>

                <div class="ui-surface-body">
                    <div class="info-grid">
                        <div class="info-box">
                            <div class="info-label">Export Draft</div>
                            <div class="info-value mb-2">Unduh file Excel draft shipment ini.</div>
                            <a href="{{ route('shipments.export-excel', $shipment->id) }}" class="btn btn-sm btn-outline-success">Export Excel</a>
                        </div>

                        <div class="info-box">
                            <div class="info-label">Import Revisi</div>
                            <div class="info-value mb-2">Upload file hasil export untuk update header dan line draft ini.</div>
                            <form method="POST" action="{{ route('shipments.import-excel') }}" enctype="multipart/form-data" class="d-flex flex-column gap-2">
                                @csrf
                                <input type="hidden" name="shipment_id" value="{{ $shipment->id }}">
                                <input type="file" name="file" class="form-control form-control-sm" accept=".xlsx,.xls" required>
                                <button class="btn btn-sm btn-primary align-self-start">Import Excel</button>
                            </form>
                        </div>

                        <div class="info-box">
                            <div class="info-label">Cancel Draft</div>
                            <div class="info-value mb-2">Batalkan draft jika dokumen supplier tidak jadi diproses.</div>
                            <form method="POST" action="{{ route('shipments.cancel-draft', $shipment->id) }}">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Batalkan draft shipment ini?')">Cancel Draft</button>
                            </form>
                        </div>
                    </div>
                </div>
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

                <div class="ui-surface-body pt-0">
                    <div class="mb-3">
                        <h4 class="ui-surface-title mb-1">Split Shipment Board</h4>
                        <div class="ui-surface-subtitle">Bandingkan draft ini dengan shipment event lain yang masih terkait ke item PO yang sama.</div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered ui-table mb-0">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>PO Outstanding</th>
                                    <th>Shipment Lain Masih Open</th>
                                    <th>Draft Ini</th>
                                    <th>Maks Bisa Dipakai</th>
                                    <th>Milestone</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lines as $line)
                                    @php($relatedEvents = collect($splitShipmentBoard->get($line->purchase_order_item_id, [])))
                                    <tr>
                                        <td>
                                            <div class="doc-number">{{ $line->item_code }}</div>
                                            <div class="doc-meta">{{ $line->item_name }}</div>
                                            <div class="doc-meta">{{ $line->po_number }}</div>
                                        </td>
                                        <td>{{ \App\Support\NumberFormatter::trim($line->outstanding_qty) }}</td>
                                        <td>{{ \App\Support\NumberFormatter::trim($line->other_open_shipment_qty) }}</td>
                                        <td>{{ \App\Support\NumberFormatter::trim($line->shipped_qty) }}</td>
                                        <td>{{ \App\Support\NumberFormatter::trim($line->available_to_ship_qty) }}</td>
                                        <td style="min-width: 360px;">
                                            <div class="shipment-progress-track">
                                                @if ($relatedEvents->isEmpty())
                                                    <div class="doc-meta">Belum ada shipment event lain untuk item ini.</div>
                                                @else
                                                    @foreach ($relatedEvents as $event)
                                                        @php($progressTotal = max((float) $event->shipped_qty, 0.01))
                                                        @php($receivedPercent = min(100, round(((float) $event->received_qty / $progressTotal) * 100, 1)))
                                                        <div class="shipment-progress-card">
                                                            <div class="shipment-progress-header">
                                                                <div>
                                                                    <div class="doc-number">{{ $event->shipment_number }}</div>
                                                                    <div class="doc-meta">{{ \Carbon\Carbon::parse($event->shipment_date)->format('d-m-Y') }} | DN {{ $event->delivery_note_number ?: '-' }}</div>
                                                                </div>
                                                                <x-status-badge :status="$event->status" scope="shipment" />
                                                            </div>
                                                            <div class="shipment-progress-bar">
                                                                <div class="shipment-progress-fill" style="width: {{ $receivedPercent }}%"></div>
                                                            </div>
                                                            <div class="shipment-progress-meta">
                                                                <span>Shipped {{ \App\Support\NumberFormatter::trim($event->shipped_qty) }}</span>
                                                                <span>Received {{ \App\Support\NumberFormatter::trim($event->received_qty) }}</span>
                                                                <span>Open {{ \App\Support\NumberFormatter::trim($event->open_qty) }}</span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif

                                                <div class="shipment-progress-card shipment-progress-card-current">
                                                    <div class="shipment-progress-header">
                                                        <div>
                                                            <div class="doc-number">Draft Ini</div>
                                                            <div class="doc-meta">Line yang sedang diedit pada draft shipment aktif.</div>
                                                        </div>
                                                        <span class="badge bg-primary">Current</span>
                                                    </div>
                                                    <div class="shipment-progress-bar">
                                                        @php($draftPercent = (float) $line->outstanding_qty > 0 ? min(100, round(((float) $line->shipped_qty / (float) $line->outstanding_qty) * 100, 1)) : 0)
                                                        <div class="shipment-progress-fill shipment-progress-fill-current" style="width: {{ $draftPercent }}%"></div>
                                                    </div>
                                                    <div class="shipment-progress-meta">
                                                        <span>Draft {{ \App\Support\NumberFormatter::trim($line->shipped_qty) }}</span>
                                                        <span>Outstanding {{ \App\Support\NumberFormatter::trim($line->outstanding_qty) }}</span>
                                                        <span>Open Lain {{ \App\Support\NumberFormatter::trim($line->other_open_shipment_qty) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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
