@extends('layouts.erp')

@php($title = 'Shipment')
@php($header = $isArchiveView ?? false ? 'Shipment Archive' : ($isBuilderView ?? false ? 'Create Draft Shipment' : 'Shipment Worklist'))
@php($headerSubtitle = $isArchiveView ?? false ? 'Arsip dokumen shipment yang sudah selesai atau dibatalkan.' : ($isBuilderView ?? false ? 'Susun draft shipment dari kandidat item PO yang siap dikirim.' : 'Dokumen shipment aktif yang masih perlu diproses.'))

@section('content')
    @php($selectedItemIds = collect(request('selected_items', []))->map(fn($id) => (int) $id)->filter()->values()->all())
    @php($isBuilderView = request()->routeIs('shipments.create') || request('view') === 'draft')
    @php($isArchiveView = request()->routeIs('shipments.history') || request('view') === 'history')
    @php($isWorklistView = !$isBuilderView && !$isArchiveView)
    @php($focusedShipmentId = (int) request('focus'))
    @php($activeRowsData = $activeRows ?? null)
    @php($archiveRowsData = $archiveRows ?? null)
    @php($activeCollection = $activeRowsData ? collect($activeRowsData->items()) : collect())
    @php($archiveCollection = $archiveRowsData ? collect($archiveRowsData->items()) : collect())

    <div class="page-shell">
        <section class="page-head">
            <div class="page-head-main">
                @if ($isBuilderView)
                    <h2 class="page-section-title">Create Draft Shipment</h2>
                    <p class="page-section-subtitle">Buat draft shipment dari kandidat item PO yang masih bisa dikirim.</p>
                @elseif ($isArchiveView)
                    <h2 class="page-section-title">Shipment Archive</h2>
                    <p class="page-section-subtitle">Dokumen shipment yang sudah selesai diterima atau dibatalkan.</p>
                @else
                    <h2 class="page-section-title">Shipment Worklist</h2>
                    <p class="page-section-subtitle">Fokus ke draft, shipped, dan partial received yang masih perlu ditindaklanjuti.</p>
                @endif
            </div>

            <div class="page-actions">
                <a href="{{ route('shipments.index') }}" class="btn btn-sm {{ $isWorklistView ? 'btn-primary' : 'btn-light' }}">Worklist</a>
                <a href="{{ route('shipments.create') }}" class="btn btn-sm {{ $isBuilderView ? 'btn-warning' : 'btn-light' }}">Create Draft</a>
                <a href="{{ route('shipments.history') }}" class="btn btn-sm {{ $isArchiveView ? 'btn-secondary' : 'btn-light' }}">Archive</a>
            </div>
        </section>

        @if ($isWorklistView)
            <section class="summary-chips">
                <div class="summary-chip">
                    <div class="summary-chip-label">Draft</div>
                    <div class="summary-chip-value">{{ $activeCollection->where('status', \App\Support\DocumentTermCodes::SHIPMENT_DRAFT)->count() }}</div>
                </div>
                <div class="summary-chip">
                    <div class="summary-chip-label">Shipped</div>
                    <div class="summary-chip-value">{{ $activeCollection->where('status', \App\Support\DocumentTermCodes::SHIPMENT_SHIPPED)->count() }}</div>
                </div>
                <div class="summary-chip">
                    <div class="summary-chip-label">Partial</div>
                    <div class="summary-chip-value">{{ $activeCollection->where('status', \App\Support\DocumentTermCodes::SHIPMENT_PARTIAL_RECEIVED)->count() }}</div>
                </div>
                <div class="summary-chip">
                    <div class="summary-chip-label">Archive</div>
                    <div class="summary-chip-value">{{ $archiveCollection->count() }}</div>
                </div>
            </section>

            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Filter Shipment Worklist</h3>
                        <div class="ui-surface-subtitle">Cari dokumen aktif berdasarkan supplier, delivery note, invoice, keyword, atau status.</div>
                    </div>
                </div>

                <form method="GET" class="filter-grid">
                    <div class="span-3">
                        <label class="field-label">Supplier</label>
                        <select name="supplier_id" class="form-control form-control-sm">
                            <option value="">Semua Supplier</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected((int) request('supplier_id') === (int) $supplier->id)>
                                    {{ $supplier->supplier_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="span-2">
                        <label class="field-label">Delivery Note</label>
                        <input type="text" name="delivery_note_number" value="{{ request('delivery_note_number') }}" class="form-control form-control-sm" placeholder="No surat jalan">
                    </div>

                    <div class="span-2">
                        <label class="field-label">Invoice</label>
                        <input type="text" name="invoice_number" value="{{ request('invoice_number') }}" class="form-control form-control-sm" placeholder="No invoice">
                    </div>

                    <div class="span-2">
                        <label class="field-label">Keyword</label>
                        <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control form-control-sm" placeholder="Shipment / PO / supplier">
                    </div>

                    <div class="span-2">
                        <label class="field-label">Status</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">Semua Status</option>
                            @foreach (\App\Support\DocumentTermCodes::shipmentStatuses() as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="span-1"><button class="btn btn-primary btn-sm w-100">Apply</button></div>
                    <div class="span-1"><a href="{{ route('shipments.index') }}" class="btn btn-light btn-sm w-100">Reset</a></div>
                </form>
            </section>

            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Active Documents</h3>
                        <div class="ui-surface-subtitle">Draft, shipped, dan partial received tampil paling depan untuk diproses.</div>
                    </div>

                    <div class="page-actions">
                        <a href="{{ route('shipments.create') }}" class="btn btn-success btn-sm">Create Draft</a>
                        <a href="{{ route('shipments.template') }}" class="btn btn-light btn-sm">Download Template</a>
                    </div>
                </div>

                <div class="table-wrap table-responsive">
                    <table class="table table-hover ui-table">
                        <thead>
                            <tr>
                                <th>Shipment</th>
                                <th>Supplier</th>
                                <th>PO Terkait</th>
                                <th>Delivery Note</th>
                                <th>Invoice</th>
                                <th>Status</th>
                                <th>Progress</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($activeRowsData ?? [] as $r)
                                <tr class="{{ $focusedShipmentId === (int) $r->id ? 'table-success' : '' }}">
                                    <td>
                                        <div class="doc-number">{{ $r->shipment_number }}</div>
                                        <div class="doc-meta">{{ \Carbon\Carbon::parse($r->shipment_date)->format('d-m-Y') }}</div>
                                    </td>
                                    <td>{{ $r->supplier_name ?: '-' }}</td>
                                    <td>
                                        {{ $r->po_numbers ?: '-' }}<br>
                                        <span class="doc-meta">{{ $r->po_count }} PO • {{ $r->line_count }} line</span>
                                    </td>
                                    <td>{{ $r->delivery_note_number ?: '-' }}</td>
                                    <td>
                                        {{ $r->invoice_number ?: '-' }}
                                        @if ($r->invoice_date)
                                            <br><span class="doc-meta">{{ \Carbon\Carbon::parse($r->invoice_date)->format('d-m-Y') }} {{ $r->invoice_currency ?: '' }}</span>
                                        @endif
                                    </td>
                                    <td><x-status-badge :status="$r->status" scope="shipment" /></td>
                                    <td>
                                        <div class="doc-number">{{ \App\Support\NumberFormatter::trim($r->total_received_qty ?? 0) }} / {{ \App\Support\NumberFormatter::trim($r->total_shipped_qty ?? 0) }}</div>
                                        <div class="doc-meta">Open {{ \App\Support\NumberFormatter::trim($r->total_open_qty ?? 0) }}</div>
                                    </td>
                                    <td class="text-end">
                                        <div class="action-stack">
                                            <a href="{{ route('shipments.show', $r->id) }}" class="btn btn-sm btn-light">View</a>
                                            @if ($r->status === \App\Support\DocumentTermCodes::SHIPMENT_DRAFT)
                                                <a href="{{ route('shipments.edit', $r->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                                <a href="{{ route('shipments.export-excel', $r->id) }}" class="btn btn-sm btn-outline-success">Export</a>
                                                <form method="POST" action="{{ route('shipments.mark-shipped', $r->id) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="btn btn-sm btn-outline-primary">Mark Shipped</button>
                                                </form>
                                                <form method="POST" action="{{ route('shipments.cancel-draft', $r->id) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Batalkan draft shipment ini?')">Cancel</button>
                                                </form>
                                            @elseif (in_array($r->status, [\App\Support\DocumentTermCodes::SHIPMENT_SHIPPED, \App\Support\DocumentTermCodes::SHIPMENT_PARTIAL_RECEIVED], true))
                                                <a href="{{ route('receiving.process', ['supplier_id' => $r->supplier_id, 'shipment_id' => $r->id, 'document_number' => $r->delivery_note_number]) }}" class="btn btn-sm btn-outline-primary">Continue Receiving</a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Tidak ada dokumen aktif.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($activeRowsData)
                    <div class="px-3 pb-3">{{ $activeRowsData->links() }}</div>
                @endif
            </section>
        @endif

        @if ($isBuilderView)
            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Filter Kandidat Item PO</h3>
                        <div class="ui-surface-subtitle">Pilih supplier atau cari item/PO untuk menyusun draft shipment.</div>
                    </div>
                </div>

                <div class="ui-surface-body">
                    <form method="GET" class="shipment-selection-form">
                        <div class="filter-grid px-0 pt-0 pb-0">
                            <div class="span-3">
                                <label class="field-label">Supplier</label>
                                <select name="supplier_id" class="form-control form-control-sm" {{ $selectedItems->isNotEmpty() ? 'disabled' : '' }}>
                                    <option value="">Semua Supplier</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" @selected((int) request('supplier_id', $selectedSupplierId) === (int) $supplier->id)>
                                            {{ $supplier->supplier_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if ($selectedItems->isNotEmpty())
                                    <input type="hidden" name="supplier_id" value="{{ $selectedSupplierId }}">
                                @endif
                            </div>

                            <div class="span-8">
                                <label class="field-label">Cari Item / PO / Supplier</label>
                                <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control form-control-sm" placeholder="Item code, nama item, nomor PO, nama supplier">
                            </div>

                            <div class="span-1 d-flex align-items-end">
                                <button class="btn btn-outline-primary btn-sm w-100">Cari</button>
                            </div>
                        </div>

                        <input type="hidden" name="view" value="draft">

                        @foreach ($selectedItemIds as $selectedItemId)
                            <input type="hidden" name="selected_items[]" value="{{ $selectedItemId }}">
                        @endforeach
                        @foreach ($draftQuantities as $itemId => $qty)
                            <input type="hidden" name="shipped_qty[{{ $itemId }}]" value="{{ $qty }}">
                        @endforeach
                        @foreach ($draftInvoicePrices as $itemId => $price)
                            <input type="hidden" name="invoice_unit_price[{{ $itemId }}]" value="{{ $price }}">
                        @endforeach
                    </form>
                </div>
            </section>

            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Pilih Item yang Akan Dikirim</h3>
                        <div class="ui-surface-subtitle">Centang item yang ingin dimasukkan ke draft shipment.</div>
                    </div>
                    @if ($hasSearch)
                        <button type="button" class="btn btn-primary btn-sm" onclick="addCheckedCandidateItems()">Tambahkan ke Draft</button>
                    @endif
                </div>

                <div class="table-wrap table-responsive">
                    @if (!$hasSearch)
                        <div class="text-muted">Kandidat item akan muncul setelah memilih supplier atau melakukan pencarian.</div>
                    @else
                        <table class="table table-hover ui-table">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" onchange="toggleCandidateCheckboxes(this.checked)"></th>
                                    <th>Supplier</th>
                                    <th>PO</th>
                                    <th>Item</th>
                                    <th>Harga PO</th>
                                    <th>Outstanding PO</th>
                                    <th>Dialokasikan</th>
                                    <th>Sisa Bisa Dikirim</th>
                                    <th>ETD</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($candidateItems as $candidate)
                                    @php($isAllocatable = (float) $candidate->available_to_ship_qty > 0)
                                    <tr class="{{ in_array((int) $candidate->purchase_order_item_id, $selectedItemIds, true) ? 'table-primary' : (!$isAllocatable ? 'table-light' : '') }}">
                                        <td><input type="checkbox" class="candidate-item-checkbox" value="{{ $candidate->purchase_order_item_id }}" {{ $isAllocatable ? '' : 'disabled' }}></td>
                                        <td>{{ $candidate->supplier_name }}</td>
                                        <td>{{ $candidate->po_number }}<br><x-status-badge :status="$candidate->po_status" scope="po" /></td>
                                        <td><div class="doc-number">{{ $candidate->item_code }}</div><div class="doc-meta">{{ $candidate->item_name }}</div></td>
                                        <td>{{ $candidate->unit_price !== null ? \App\Support\NumberFormatter::trim($candidate->unit_price) : '-' }}</td>
                                        <td>{{ \App\Support\NumberFormatter::trim($candidate->outstanding_qty) }}</td>
                                        <td>{{ \App\Support\NumberFormatter::trim($candidate->open_shipment_qty) }}</td>
                                        <td><span class="badge {{ $isAllocatable ? 'bg-warning text-dark' : 'bg-secondary' }}">{{ \App\Support\NumberFormatter::trim(max(0, $candidate->available_to_ship_qty)) }}</span></td>
                                        <td>{{ $candidate->etd_date ? \Carbon\Carbon::parse($candidate->etd_date)->format('d-m-Y') : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="text-center text-muted">Belum ada kandidat. Coba ubah filter atau kata kunci pencarian.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    @endif
                </div>
            </section>

            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Review Draft Shipment</h3>
                        <div class="ui-surface-subtitle">Isi dokumen supplier dan periksa qty serta harga invoice tiap item.</div>
                    </div>
                </div>

                <div class="ui-surface-body">
                    @if ($selectedItems->isNotEmpty())
                        <div class="info-grid mb-3">
                            <div class="info-box"><div class="info-label">Supplier</div><div class="info-value">{{ $selectedItems->first()->supplier_name }}</div></div>
                            <div class="info-box"><div class="info-label">Jumlah Item</div><div class="info-value">{{ $selectedItems->count() }}</div></div>
                            <div class="info-box"><div class="info-label">PO Terkait</div><div class="info-value">{{ $selectedItems->pluck('purchase_order_id')->unique()->count() }}</div></div>
                        </div>
                    @else
                        <div class="alert alert-warning">Pilih minimal satu item dari tabel kandidat sebelum membuat draft shipment.</div>
                    @endif

                    <form method="POST" action="{{ route('shipments.store') }}">
                        @csrf
                        <div class="filter-grid px-0 pt-0 pb-3">
                            <div class="span-4">
                                <label class="field-label">Supplier</label>
                                <input type="text" class="form-control form-control-sm" value="{{ optional($selectedItems->first())->supplier_name ?: '-' }}" disabled>
                            </div>
                            <div class="span-3">
                                <label class="field-label">No Delivery Note</label>
                                <input type="text" name="delivery_note_number" value="{{ old('delivery_note_number') }}" class="form-control form-control-sm" placeholder="No surat jalan supplier" required>
                            </div>
                            <div class="span-2">
                                <label class="field-label">Tanggal Dokumen</label>
                                <input type="date" name="shipment_date" value="{{ old('shipment_date', now()->format('Y-m-d')) }}" class="form-control form-control-sm" required>
                            </div>
                            <div class="span-3">
                                <label class="field-label">No Invoice</label>
                                <input type="text" name="invoice_number" value="{{ old('invoice_number') }}" class="form-control form-control-sm" placeholder="Nomor invoice supplier">
                            </div>
                            <div class="span-3">
                                <label class="field-label">Tanggal Invoice</label>
                                <input type="date" name="invoice_date" value="{{ old('invoice_date') }}" class="form-control form-control-sm">
                            </div>
                            <div class="span-2">
                                <label class="field-label">Currency</label>
                                <input type="text" name="invoice_currency" value="{{ old('invoice_currency', 'IDR') }}" class="form-control form-control-sm" maxlength="10" placeholder="IDR">
                            </div>
                            <div class="span-3 d-flex align-items-end">
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="checkbox" value="1" name="po_reference_missing" id="po_reference_missing" @checked(old('po_reference_missing') === '1')>
                                    <label class="form-check-label" for="po_reference_missing">Nomor PO tidak ada di dokumen supplier</label>
                                </div>
                            </div>
                            <div class="span-4">
                                <label class="field-label">Catatan</label>
                                <input type="text" name="supplier_remark" value="{{ old('supplier_remark') }}" class="form-control form-control-sm" placeholder="Catatan internal atau info tambahan supplier">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                            <div class="doc-meta">Harga invoice dicatat di draft shipment agar tidak perlu diinput ulang saat receiving.</div>
                            @if ($selectedItems->isNotEmpty())
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeCheckedDraftItems()">Keluarkan Item Terpilih</button>
                            @endif
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered ui-table">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" onchange="toggleDraftCheckboxes(this.checked)"></th>
                                        <th>PO</th>
                                        <th>Item</th>
                                        <th>Harga PO</th>
                                        <th>Sisa Bisa Dikirim</th>
                                        <th>Qty Draft</th>
                                        <th>Harga Invoice</th>
                                        <th>Total Invoice</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($selectedItems as $item)
                                        @php($draftPrice = old('invoice_unit_price.' . $item->purchase_order_item_id, $draftInvoicePrices[$item->purchase_order_item_id] ?? ''))
                                        <tr>
                                            <td><input type="checkbox" class="draft-item-checkbox" value="{{ $item->purchase_order_item_id }}"></td>
                                            <td>{{ $item->po_number }}</td>
                                            <td><div class="doc-number">{{ $item->item_code }}</div><div class="doc-meta">{{ $item->item_name }}</div></td>
                                            <td>{{ $item->unit_price !== null ? \App\Support\NumberFormatter::trim($item->unit_price) : '-' }}</td>
                                            <td>{{ \App\Support\NumberFormatter::trim($item->available_to_ship_qty) }}</td>
                                            <td style="min-width: 140px;">
                                                <input type="hidden" name="selected_items[]" value="{{ $item->purchase_order_item_id }}">
                                                <input type="number" step="0.01" min="0.01" max="{{ \App\Support\NumberFormatter::input($item->available_to_ship_qty) }}" name="shipped_qty[{{ $item->purchase_order_item_id }}]" value="{{ \App\Support\NumberFormatter::input(old('shipped_qty.' . $item->purchase_order_item_id, $draftQuantities[$item->purchase_order_item_id] ?? $item->available_to_ship_qty)) }}" class="form-control form-control-sm draft-qty-input" data-item-id="{{ $item->purchase_order_item_id }}" required>
                                            </td>
                                            <td style="min-width: 160px;">
                                                <input type="number" step="0.0001" min="0" name="invoice_unit_price[{{ $item->purchase_order_item_id }}]" value="{{ $draftPrice }}" class="form-control form-control-sm draft-price-input" data-item-id="{{ $item->purchase_order_item_id }}" placeholder="Opsional">
                                            </td>
                                            <td style="min-width: 140px;">
                                                <input type="text" class="form-control form-control-sm bg-light draft-line-total" data-item-id="{{ $item->purchase_order_item_id }}" value="-" readonly>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeDraftItem('{{ $item->purchase_order_item_id }}')">Batal Pilih</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="9" class="text-center text-muted">Belum ada item terpilih.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <button class="btn btn-primary btn-sm" {{ $selectedItems->isEmpty() ? 'disabled' : '' }}>Simpan Draft Shipment</button>
                        </div>
                    </form>
                </div>
            </section>
        @endif

        @if ($isArchiveView)
            <section class="summary-chips">
                <div class="summary-chip">
                    <div class="summary-chip-label">Received</div>
                    <div class="summary-chip-value">{{ $archiveCollection->where('status', \App\Support\DocumentTermCodes::SHIPMENT_RECEIVED)->count() }}</div>
                </div>
                <div class="summary-chip">
                    <div class="summary-chip-label">Cancelled</div>
                    <div class="summary-chip-value">{{ $archiveCollection->where('status', \App\Support\DocumentTermCodes::SHIPMENT_CANCELLED)->count() }}</div>
                </div>
            </section>

            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Shipment Archive</h3>
                        <div class="ui-surface-subtitle">Dokumen shipment yang sudah selesai diterima atau dibatalkan.</div>
                    </div>
                </div>

                <div class="table-wrap table-responsive">
                    <table class="table table-hover ui-table">
                        <thead>
                            <tr>
                                <th>Shipment</th>
                                <th>Supplier</th>
                                <th>PO Terkait</th>
                                <th>Delivery Note</th>
                                <th>Invoice</th>
                                <th>Status</th>
                                <th>Progress</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($archiveRowsData ?? [] as $r)
                                <tr>
                                    <td><div class="doc-number">{{ $r->shipment_number }}</div><div class="doc-meta">{{ \Carbon\Carbon::parse($r->shipment_date)->format('d-m-Y') }}</div></td>
                                    <td>{{ $r->supplier_name ?: '-' }}</td>
                                    <td>{{ $r->po_numbers ?: '-' }}<br><span class="doc-meta">{{ $r->po_count }} PO • {{ $r->line_count }} line</span></td>
                                    <td>{{ $r->delivery_note_number ?: '-' }}</td>
                                    <td>{{ $r->invoice_number ?: '-' }}</td>
                                    <td><x-status-badge :status="$r->status" scope="shipment" /></td>
                                    <td><div class="doc-number">{{ \App\Support\NumberFormatter::trim($r->total_received_qty ?? 0) }} / {{ \App\Support\NumberFormatter::trim($r->total_shipped_qty ?? 0) }}</div><div class="doc-meta">Open {{ \App\Support\NumberFormatter::trim($r->total_open_qty ?? 0) }}</div></td>
                                    <td class="text-end"><div class="action-stack"><a href="{{ route('shipments.show', $r->id) }}" class="btn btn-sm btn-outline-primary">View</a></div></td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted">Belum ada dokumen arsip.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($archiveRowsData)
                    <div class="px-3 pb-3">{{ $archiveRowsData->links() }}</div>
                @endif
            </section>
        @endif
    </div>

    <script>
        const formatNumber = (value) => {
            const parsed = parseFloat(value || 0);
            if (Number.isNaN(parsed)) return '-';
            return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(parsed);
        };

        const recalcDraftLineTotals = () => {
            document.querySelectorAll('.draft-line-total').forEach((totalInput) => {
                const itemId = totalInput.dataset.itemId;
                const qtyInput = document.querySelector(`.draft-qty-input[data-item-id="${itemId}"]`);
                const priceInput = document.querySelector(`.draft-price-input[data-item-id="${itemId}"]`);
                const qty = parseFloat(qtyInput?.value || 0);
                const price = parseFloat(priceInput?.value || 0);

                if (!priceInput || priceInput.value === '' || Number.isNaN(price)) {
                    totalInput.value = '-';
                    return;
                }

                totalInput.value = formatNumber(qty * price);
            });
        };

        document.querySelectorAll('.draft-qty-input, .draft-price-input').forEach((input) => {
            input.addEventListener('input', recalcDraftLineTotals);
            input.addEventListener('change', recalcDraftLineTotals);
        });

        recalcDraftLineTotals();

        window.toggleCandidateCheckboxes = (checked) => {
            document.querySelectorAll('.candidate-item-checkbox').forEach((checkbox) => checkbox.checked = checked);
        };

        window.toggleDraftCheckboxes = (checked) => {
            document.querySelectorAll('.draft-item-checkbox').forEach((checkbox) => checkbox.checked = checked);
        };

        window.addCheckedCandidateItems = () => {
            const checkedItems = Array.from(document.querySelectorAll('.candidate-item-checkbox:checked')).map((checkbox) => checkbox.value);
            if (checkedItems.length === 0) return;
            const form = document.querySelector('.shipment-selection-form');
            checkedItems.forEach((id) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_items[]';
                input.value = id;
                form.appendChild(input);
            });
            form.submit();
        };

        window.removeDraftItem = (itemId) => {
            const normalizedItemId = String(itemId);
            document.querySelectorAll(`input[name="selected_items[]"][value="${normalizedItemId}"]`).forEach((node) => node.remove());
            document.querySelectorAll(`input[name="shipped_qty[${normalizedItemId}]"]`).forEach((node) => node.remove());
            document.querySelectorAll(`input[name="invoice_unit_price[${normalizedItemId}]"]`).forEach((node) => node.remove());
            window.location.reload();
        };

        window.removeCheckedDraftItems = () => {
            const checkedItems = Array.from(document.querySelectorAll('.draft-item-checkbox:checked')).map((checkbox) => checkbox.value);
            if (checkedItems.length === 0) return;
            checkedItems.forEach((itemId) => removeDraftItem(itemId));
        };
    </script>
@endsection
