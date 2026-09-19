@extends('layouts.erp')

@php($title = 'Create Draft Shipment')
@php($header = 'Create Draft Shipment')
@php($headerSubtitle = 'Susun draft shipment dari kandidat item PO yang siap dikirim.')

@push('styles')
<style>
    .wizard-tabs {
        display: flex;
        gap: 0;
        border-bottom: 2px solid var(--lemon-line);
        margin-bottom: 1rem;
    }
    .wizard-tab {
        padding: .6rem 1.2rem;
        font-size: .82rem;
        font-weight: 700;
        color: #7a8660;
        cursor: pointer;
        border-bottom: 3px solid transparent;
        transition: all .15s;
        margin-bottom: -2px;
        user-select: none;
    }
    .wizard-tab:hover { color: var(--lemon-ink); }
    .wizard-tab.active {
        color: var(--lemon-ink);
        border-bottom-color: var(--lemon-green);
    }
    .wizard-tab .step-num {
        display: inline-flex;
        width: 20px; height: 20px;
        border-radius: 50%;
        background: var(--lemon-line);
        color: var(--lemon-ink);
        font-size: .7rem;
        align-items: center;
        justify-content: center;
        margin-right: 6px;
        font-weight: 800;
    }
    .wizard-tab.active .step-num {
        background: var(--lemon-green);
        color: #fff;
    }
    .wizard-tab.completed .step-num {
        background: var(--lemon-green-deep);
        color: #fff;
    }
    .wizard-tab.completed { color: var(--lemon-green-deep); }
    .wizard-step { display: none; }
    .wizard-step.active { display: block; animation: fadeIn .25s ease; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

    .sticky-action-bar {
        position: sticky;
        bottom: 0;
        z-index: 40;
        background: linear-gradient(180deg, rgba(255,255,255,.95) 0%, rgba(252,254,239,.98) 100%);
        border-top: 2px solid var(--lemon-line);
        padding: .8rem 1rem;
        margin-top: 1.5rem;
        backdrop-filter: blur(4px);
    }

    .form-card {
        border: 1px solid var(--lemon-line);
        border-radius: 12px;
        background: #fffef8;
        padding: .9rem;
        margin-bottom: .75rem;
    }
    .form-card-title {
        font-size: .82rem;
        font-weight: 700;
        color: var(--lemon-olive);
        margin-bottom: .5rem;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .field-inline-label {
        font-size: .72rem;
        font-weight: 600;
        color: #52603d;
        margin-bottom: 2px;
    }

    .confirm-summary-box {
        border: 2px solid var(--lemon-green);
        border-radius: 14px;
        background: linear-gradient(135deg, #f6fae2 0%, #eef5d0 100%);
        padding: 1rem;
    }
    .confirm-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: .75rem;
    }
    .confirm-item {
        text-align: center;
        padding: .5rem;
    }
    .confirm-item .confirm-value {
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--lemon-ink);
    }
    .confirm-item .confirm-label {
        font-size: .72rem;
        color: #7a8660;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    @media (max-width: 767.98px) {
        .wizard-tabs { overflow-x: auto; }
        .wizard-tab { white-space: nowrap; padding: .5rem .8rem; font-size: .76rem; }
    }
</style>
@endpush

@section('content')
    <div class="page-shell">

        <nav class="breadcrumb-nav" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">Home</a>
            <span class="sep">/</span>
            <a href="{{ route('shipments.index') }}">Shipment</a>
            <span class="sep">/</span>
            <span>Create Draft</span>
        </nav>

        <div class="wizard-tabs" id="wizardTabs">
            <div class="wizard-tab active" data-step="1" onclick="goToStep(1)">
                <span class="step-num">1</span> Select Items
            </div>
            <div class="wizard-tab" data-step="2" onclick="goToStep(2)">
                <span class="step-num">2</span> Review &amp; Edit
            </div>
            <div class="wizard-tab" data-step="3" onclick="goToStep(3)">
                <span class="step-num">3</span> Confirm
            </div>
        </div>

        <form method="POST" action="{{ route('shipments.store') }}" id="draftForm">
            @csrf

            @if ($selectedItems->isNotEmpty())
                <input type="hidden" name="sync_selection" value="1">
                @foreach ($selectedItemIds as $id)
                    <input type="hidden" name="selected_items[]" value="{{ $id }}">
                @endforeach
                @foreach ($draftQuantities as $itemId => $qty)
                    <input type="hidden" name="shipped_qty[{{ $itemId }}]" value="{{ $qty }}">
                @endforeach
                @foreach ($draftInvoicePrices as $itemId => $price)
                    <input type="hidden" name="invoice_unit_price[{{ $itemId }}]" value="{{ $price }}">
                @endforeach
            @endif

            <section class="ui-surface wizard-step active" id="step1">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Filter Kandidat Item PO</h3>
                        <div class="ui-surface-subtitle">Pilih supplier atau cari item/PO untuk menyusun draft shipment.</div>
                    </div>
                </div>
                <div class="ui-surface-body">
                    <form method="GET" class="shipment-selection-form" action="{{ route('shipments.create') }}" id="candidateFilterForm">
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
                    </form>

                    <div class="table-wrap table-responsive mt-3">
                        @if (!$hasSearch)
                            <div class="text-muted py-3 text-center">Kandidat item akan muncul setelah memilih supplier atau melakukan pencarian.</div>
                        @else
                            <table class="table table-hover ui-table">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" onchange="toggleCandidateCheckboxes(this.checked)"></th>
                                        <th>Supplier</th><th>PO</th><th>Item</th><th>Harga PO</th>
                                        <th>Outstanding PO</th><th>Dialokasikan</th><th>Sisa Bisa Dikirim</th><th>ETD</th>
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

                    <div class="d-flex justify-content-between mt-3">
                        <div></div>
                        <div class="page-actions">
                            @if ($hasSearch)
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearCandidateChecks()">Clear Check</button>
                            @endif
                            <button type="button" class="btn btn-primary btn-sm" onclick="addCheckedCandidateItems()">Tambahkan ke Draft →</button>
                        </div>
                    </div>
                </div>
            </section>

            <section class="ui-surface wizard-step" id="step2">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Review &amp; Edit Draft Shipment</h3>
                        <div class="ui-surface-subtitle">Sesuaikan qty, harga invoice, dan informasi dokumen supplier.</div>
                    </div>
                </div>
                <div class="ui-surface-body">
                    @php($draftQtyMap = collect(old('shipped_qty', []))->mapWithKeys(fn($qty, $itemId) => [(int) $itemId => (float) $qty])->union($draftQuantities))

                    @if ($selectedItems->isNotEmpty())
                        <div class="info-grid mb-3">
                            <div class="info-box"><div class="info-label">Supplier</div><div class="info-value">{{ $selectedItems->first()->supplier_name }}</div></div>
                            <div class="info-box"><div class="info-label">Jumlah Item</div><div class="info-value">{{ $selectedItems->count() }}</div></div>
                            <div class="info-box"><div class="info-label">PO Terkait</div><div class="info-value">{{ $selectedItems->pluck('purchase_order_id')->unique()->count() }}</div></div>
                        </div>
                    @endif

                    @if ($selectedItems->isEmpty())
                        <div class="alert alert-warning">Pilih minimal satu item dari tabel kandidat di langkah 1.</div>
                    @endif

                    @if ($selectedItems->isNotEmpty())
                        <div class="ui-surface mb-3">
                            <div class="ui-surface-head">
                                <div>
                                    <h3 class="ui-surface-title">Split Shipment Board</h3>
                                    <div class="ui-surface-subtitle">Lihat alokasi shipment per item sebelum draft baru disimpan.</div>
                                </div>
                            </div>
                            <div class="ui-surface-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered ui-table mb-0">
                                        <thead>
                                            <tr><th>Item</th><th>PO Outstanding</th><th>Dialokasikan di Shipment Lain</th><th>Draft Saat Ini</th><th>Sisa Setelah Draft</th><th>Milestone</th></tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($selectedItems as $item)
                                                @php($currentDraftQty = (float) ($draftQtyMap[$item->purchase_order_item_id] ?? $item->available_to_ship_qty))
                                                @php($remainingAfterDraft = max(0, (float) $item->available_to_ship_qty - $currentDraftQty))
                                                @php($shipmentEvents = collect($splitShipmentBoard->get($item->purchase_order_item_id, [])))
                                                <tr>
                                                    <td>
                                                        <div class="doc-number">{{ $item->item_code }}</div>
                                                        <div class="doc-meta">{{ $item->item_name }}</div>
                                                        <div class="doc-meta">{{ $item->po_number }}</div>
                                                    </td>
                                                    <td>{{ \App\Support\NumberFormatter::trim($item->outstanding_qty) }}</td>
                                                    <td>{{ \App\Support\NumberFormatter::trim($item->open_shipment_qty) }}</td>
                                                    <td>{{ \App\Support\NumberFormatter::trim($currentDraftQty) }}</td>
                                                    <td>{{ \App\Support\NumberFormatter::trim($remainingAfterDraft) }}</td>
                                                    <td style="min-width: 360px;">
                                                        <div class="shipment-progress-track">
                                                            @if ($shipmentEvents->isEmpty())
                                                                <div class="doc-meta">Belum ada shipment event lain untuk item ini.</div>
                                                            @else
                                                                @foreach ($shipmentEvents as $event)
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
                                                                        <div class="shipment-progress-bar"><div class="shipment-progress-fill" style="width: {{ $receivedPercent }}%"></div></div>
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
                                                                    <div><div class="doc-number">Draft Saat Ini</div><div class="doc-meta">Qty yang sedang disiapkan di builder shipment.</div></div>
                                                                    <span class="badge bg-primary">Planned</span>
                                                                </div>
                                                                <div class="shipment-progress-bar">
                                                                    @php($draftPercent = (float) $item->outstanding_qty > 0 ? min(100, round(($currentDraftQty / (float) $item->outstanding_qty) * 100, 1)) : 0)
                                                                    <div class="shipment-progress-fill shipment-progress-fill-current" style="width: {{ $draftPercent }}%"></div>
                                                                </div>
                                                                <div class="shipment-progress-meta">
                                                                    <span>Draft {{ \App\Support\NumberFormatter::trim($currentDraftQty) }}</span>
                                                                    <span>Outstanding {{ \App\Support\NumberFormatter::trim($item->outstanding_qty) }}</span>
                                                                    <span>Sisa {{ \App\Support\NumberFormatter::trim($remainingAfterDraft) }}</span>
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
                        </div>
                    @endif

                    <div class="form-card">
                        <div class="form-card-title">Document Information</div>
                        <div class="filter-grid px-0 pt-0 pb-0">
                            <div class="span-4">
                                <div class="field-inline-label">Supplier</div>
                                <input type="text" class="form-control form-control-sm" value="{{ optional($selectedItems->first())->supplier_name ?: '-' }}" disabled>
                            </div>
                            <div class="span-3">
                                <div class="field-inline-label">No Delivery Note <span class="text-danger">*</span></div>
                                <input type="text" name="delivery_note_number" value="{{ old('delivery_note_number') }}" class="form-control form-control-sm" placeholder="No surat jalan supplier" required>
                            </div>
                            <div class="span-2">
                                <div class="field-inline-label">Tanggal Dokumen <span class="text-danger">*</span></div>
                                <input type="date" name="shipment_date" value="{{ old('shipment_date', now()->format('Y-m-d')) }}" class="form-control form-control-sm" required>
                            </div>
                            <div class="span-3">
                                <div class="field-inline-label">No Invoice</div>
                                <input type="text" name="invoice_number" value="{{ old('invoice_number') }}" class="form-control form-control-sm" placeholder="Nomor invoice supplier">
                            </div>
                            <div class="span-3">
                                <div class="field-inline-label">Tanggal Invoice</div>
                                <input type="date" name="invoice_date" value="{{ old('invoice_date') }}" class="form-control form-control-sm">
                            </div>
                            <div class="span-2">
                                <div class="field-inline-label">Currency</div>
                                <input type="text" name="invoice_currency" value="{{ old('invoice_currency', 'IDR') }}" class="form-control form-control-sm" maxlength="10" placeholder="IDR">
                            </div>
                            <div class="span-7">
                                <div class="field-inline-label">Catatan</div>
                                <input type="text" name="supplier_remark" value="{{ old('supplier_remark') }}" class="form-control form-control-sm" placeholder="Catatan internal atau info tambahan supplier">
                            </div>
                            <div class="span-4 d-flex align-items-end">
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="checkbox" value="1" name="po_reference_missing" id="po_reference_missing" @checked(old('po_reference_missing') === '1')>
                                    <label class="form-check-label" for="po_reference_missing">Nomor PO tidak ada di dokumen supplier</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-card">
                        <div class="form-card-title">Line Items</div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered ui-table">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" onchange="toggleDraftCheckboxes(this.checked)"></th>
                                        <th>PO</th><th>Item</th><th>Harga PO</th><th>Sisa Bisa Dikirim</th>
                                        <th>Qty Draft</th><th>Harga Invoice</th><th>Total Invoice</th><th>Aksi</th>
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
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeDraftItem('{{ $item->purchase_order_item_id }}')">Keluarkan</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="9" class="text-center text-muted">Belum ada item terpilih.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <section class="ui-surface wizard-step" id="step3">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Confirm &amp; Save Draft</h3>
                        <div class="ui-surface-subtitle">Verifikasi ringkasan berikut sebelum menyimpan draft shipment.</div>
                    </div>
                </div>
                <div class="ui-surface-body">
                    @php($totalQty = $draftQuantities->sum())
                    @php($totalPrice = collect($selectedItems)->sum(function($item) use ($draftQuantities, $draftInvoicePrices) {
                        $qty = (float) ($draftQuantities[$item->purchase_order_item_id] ?? $item->available_to_ship_qty);
                        $price = (float) ($draftInvoicePrices[$item->purchase_order_item_id] ?? 0);
                        return $qty * $price;
                    }))

                    <div class="confirm-summary-box mb-3">
                        <div class="confirm-summary-grid">
                            <div class="confirm-item">
                                <div class="confirm-value">{{ $selectedItems->count() }}</div>
                                <div class="confirm-label">Line Items</div>
                            </div>
                            <div class="confirm-item">
                                <div class="confirm-value">{{ \App\Support\NumberFormatter::trim($totalQty) }}</div>
                                <div class="confirm-label">Total Qty</div>
                            </div>
                            <div class="confirm-item">
                                <div class="confirm-value">{{ \App\Support\NumberFormatter::trim($totalPrice) }}</div>
                                <div class="confirm-label">Total Invoice</div>
                            </div>
                            <div class="confirm-item">
                                <div class="confirm-value">{{ $selectedItems->pluck('purchase_order_id')->unique()->count() }}</div>
                                <div class="confirm-label">PO References</div>
                            </div>
                            <div class="confirm-item">
                                <div class="confirm-value">{{ $selectedItems->first()->supplier_name ?: '-' }}</div>
                                <div class="confirm-label">Supplier</div>
                            </div>
                            <div class="confirm-item">
                                <div class="confirm-value">{{ old('delivery_note_number', '-') }}</div>
                                <div class="confirm-label">Delivery Note</div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered ui-table">
                            <thead>
                                <tr><th>PO</th><th>Item</th><th>Qty Draft</th><th>Harga Invoice</th><th>Total Invoice</th></tr>
                            </thead>
                            <tbody>
                                @forelse($selectedItems as $item)
                                    @php($qty = (float) ($draftQuantities[$item->purchase_order_item_id] ?? $item->available_to_ship_qty))
                                    @php($price = (float) ($draftInvoicePrices[$item->purchase_order_item_id] ?? 0))
                                    <tr>
                                        <td>{{ $item->po_number }}</td>
                                        <td><div class="doc-number">{{ $item->item_code }}</div><div class="doc-meta">{{ $item->item_name }}</div></td>
                                        <td>{{ \App\Support\NumberFormatter::trim($qty) }}</td>
                                        <td>{{ $price > 0 ? \App\Support\NumberFormatter::trim($price) : '-' }}</td>
                                        <td>{{ $qty * $price > 0 ? \App\Support\NumberFormatter::trim($qty * $price) : '-' }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($selectedItems->isEmpty())
                        <div class="alert alert-warning">Belum ada item terpilih. Kembali ke langkah 1 untuk memilih item.</div>
                    @endif
                </div>
            </section>

            <div class="sticky-action-bar d-flex justify-content-between align-items-center">
                <div class="doc-meta" id="wizardStatusText">Langkah 1 dari 3 — Pilih item yang akan dikirim</div>
                <div class="page-actions">
                    <button type="button" class="btn btn-light btn-sm" id="prevStep" onclick="goToStep({{ max(1, (int) request('step', 1) - 1) }})" @click="goToStep(prev)" style="display:none">← Back</button>
                    @if ($selectedItems->isNotEmpty())
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeCheckedDraftItems()">Keluarkan Item Checklist</button>
                    @endif
                    <button type="button" class="btn btn-primary btn-sm" id="nextStep" onclick="goToStep({{ min(3, (int) request('step', 1) + 1) }})">Next →</button>
                    <button type="submit" class="btn btn-success btn-sm" id="saveDraftBtn" {{ $selectedItems->isEmpty() ? 'disabled' : '' }}>Simpan Draft Shipment</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        const formatNumber = (value) => {
            const parsed = parseFloat(value || 0);
            if (Number.isNaN(parsed)) return '-';
            return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(parsed);
        };

        let currentStep = {{ request('step', 1) }};
        const hasItems = {{ $selectedItems->isNotEmpty() ? 'true' : 'false' }};

        function recalcDraftLineTotals() {
            document.querySelectorAll('.draft-line-total').forEach((totalInput) => {
                const itemId = totalInput.dataset.itemId;
                const qtyInput = document.querySelector(`.draft-qty-input[data-item-id="${itemId}"]`);
                const priceInput = document.querySelector(`.draft-price-input[data-item-id="${itemId}"]`);
                const qty = parseFloat(qtyInput?.value || 0);
                const price = parseFloat(priceInput?.value || 0);
                if (!priceInput || priceInput.value === '' || Number.isNaN(price)) { totalInput.value = '-'; return; }
                totalInput.value = formatNumber(qty * price);
            });
        }

        function goToStep(step) {
            if (step < 1 || step > 3) return;
            if (step === 2 && !hasItems) { alert('Pilih minimal satu item terlebih dahulu.'); return; }

            document.querySelectorAll('.wizard-tab').forEach(t => {
                const s = parseInt(t.dataset.step);
                t.classList.remove('active', 'completed');
                if (s < step) t.classList.add('completed');
                if (s === step) t.classList.add('active');
            });
            document.querySelectorAll('.wizard-step').forEach(s => s.classList.remove('active'));
            document.getElementById('step' + step).classList.add('active');
            currentStep = step;

            const statusText = document.getElementById('wizardStatusText');
            const labels = { 1: 'Langkah 1 dari 3 — Pilih item yang akan dikirim', 2: 'Langkah 2 dari 3 — Review dan edit draft', 3: 'Langkah 3 dari 3 — Konfirmasi dan simpan' };
            statusText.textContent = labels[step];

            const prevBtn = document.getElementById('prevStep');
            const nextBtn = document.getElementById('nextStep');
            const saveBtn = document.getElementById('saveDraftBtn');
            prevBtn.style.display = step > 1 ? '' : 'none';
            nextBtn.style.display = step < 3 ? '' : 'none';
            saveBtn.style.display = step === 3 ? '' : 'none';

            recalcDraftLineTotals();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        document.querySelectorAll('.draft-qty-input, .draft-price-input').forEach((input) => {
            input.addEventListener('input', recalcDraftLineTotals);
            input.addEventListener('change', recalcDraftLineTotals);
        });
        recalcDraftLineTotals();

        window.toggleCandidateCheckboxes = (checked) => { document.querySelectorAll('.candidate-item-checkbox').forEach((cb) => cb.checked = checked); };
        window.toggleDraftCheckboxes = (checked) => { document.querySelectorAll('.draft-item-checkbox').forEach((cb) => cb.checked = checked); };
        window.clearCandidateChecks = () => { document.querySelectorAll('.candidate-item-checkbox').forEach((cb) => cb.checked = false); };

        window.addCheckedCandidateItems = () => {
            const checkedItems = Array.from(document.querySelectorAll('.candidate-item-checkbox:checked')).map((cb) => cb.value);
            if (checkedItems.length === 0) return;
            const form = document.querySelector('.shipment-selection-form');
            const existingIds = new Set(Array.from(form.querySelectorAll('input[name="selected_items[]"]')).map((i) => i.value));
            checkedItems.forEach((id) => {
                if (existingIds.has(id)) return;
                const input = document.createElement('input');
                input.type = 'hidden'; input.name = 'selected_items[]'; input.value = id; form.appendChild(input);
            });
            form.submit();
        };

        window.removeDraftItem = (itemId) => {
            const nid = String(itemId);
            const form = document.querySelector('.shipment-selection-form');
            if (!form) return;
            form.querySelectorAll(`input[name="selected_items[]"][value="${nid}"]`).forEach((n) => n.remove());
            form.querySelectorAll(`input[name="shipped_qty[${nid}]"]`).forEach((n) => n.remove());
            form.querySelectorAll(`input[name="invoice_unit_price[${nid}]"]`).forEach((n) => n.remove());
            form.submit();
        };

        window.removeCheckedDraftItems = () => {
            const checkedItems = Array.from(document.querySelectorAll('.draft-item-checkbox:checked')).map((cb) => cb.value);
            if (checkedItems.length === 0) return;
            const form = document.querySelector('.shipment-selection-form');
            if (!form) return;
            [...new Set(checkedItems)].forEach((itemId) => {
                form.querySelectorAll(`input[name="selected_items[]"][value="${itemId}"]`).forEach((n) => n.remove());
                form.querySelectorAll(`input[name="shipped_qty[${itemId}]"]`).forEach((n) => n.remove());
                form.querySelectorAll(`input[name="invoice_unit_price[${itemId}]"]`).forEach((n) => n.remove());
            });
            form.submit();
        };

        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'n') { e.preventDefault(); window.location.href = '{{ route('shipments.create') }}'; }
            if (e.ctrlKey && e.key === 'f') { e.preventDefault(); const kw = document.querySelector('input[name="keyword"]'); if (kw) kw.focus(); }
        });

        goToStep(currentStep);
    </script>
@endsection