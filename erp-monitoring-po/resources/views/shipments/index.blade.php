@extends('layouts.erp')
@php($title = 'Shipment Tracking')
@php($header = 'Shipment Tracking')
@section('content')
    @php($focusedShipmentId = (int) request('focus'))
    <style>
        .history-shell {
            display: grid;
            gap: 1rem;
        }

        .history-hero,
        .history-surface {
            border: 1px solid rgba(111, 150, 40, .12);
            border-radius: 18px;
            background: rgba(255, 255, 255, .94);
            box-shadow: 0 14px 32px rgba(111, 150, 40, .05);
        }

        .history-hero {
            padding: 1rem 1.1rem;
            background:
                radial-gradient(circle at top right, rgba(241, 217, 59, .24), transparent 30%),
                linear-gradient(135deg, rgba(255, 255, 255, .96), rgba(245, 249, 221, .96));
        }

        .history-hero-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: #314216;
            margin-bottom: .25rem;
        }

        .history-hero-copy {
            color: #6f7d52;
            margin-bottom: 0;
            font-size: .88rem;
        }

        .history-stat-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .75rem;
        }

        .history-stat {
            padding: .85rem .95rem;
            border-radius: 16px;
            border: 1px solid rgba(111, 150, 40, .1);
            background: rgba(255, 255, 255, .8);
        }

        .history-stat-label {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #7a8660;
            margin-bottom: .25rem;
        }

        .history-stat-value {
            font-size: 1.45rem;
            font-weight: 800;
            color: #314216;
            line-height: 1;
        }

        .surface-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .75rem;
            flex-wrap: wrap;
            padding: 1rem 1rem 0;
        }

        .surface-title {
            margin: 0;
            font-size: .96rem;
            font-weight: 800;
            color: #314216;
        }

        .surface-subtitle {
            font-size: .8rem;
            color: #7a8660;
        }

        .history-filter {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: .75rem;
            padding: 1rem;
            align-items: end;
        }

        .history-table-wrap {
            padding: 1rem;
        }

        .history-table {
            margin-bottom: 0;
        }

        .history-table thead th {
            font-size: .69rem;
            letter-spacing: .08em;
        }

        .shipment-number {
            font-weight: 700;
            color: #314216;
        }

        .shipment-meta {
            font-size: .8rem;
            color: #7a8660;
        }

        .action-stack {
            display: flex;
            gap: .35rem;
            flex-wrap: wrap;
        }

        @media (max-width: 991.98px) {
            .history-stat-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            .history-stat-grid,
            .history-filter {
                grid-template-columns: 1fr;
            }
        }
    </style>
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}</div>
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

    @php($selectedItemIds = collect(request('selected_items', []))->map(fn($id) => (int) $id)->filter()->values()->all())
    @php($isHistoryView = request()->routeIs('shipments.history') || request('view') === 'history')

    @if (! $isHistoryView)
            <div class="card card-outline card-primary mb-3">
                <div class="card-header">
                    <h3 class="card-title">Pembuatan Draft Shipment</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        Mulai dari dokumen supplier, lalu pilih item yang benar-benar akan dikirim. Satu delivery note bisa
                        berisi item dari beberapa PO selama supplier-nya sama.
                    </div>
                    @if ($selectedItems->isNotEmpty())
                        <div class="alert alert-warning mb-3">
                            Supplier sudah terkunci ke <strong>{{ $selectedItems->first()->supplier_name }}</strong>. Item
                            dari supplier lain tidak akan ditampilkan selama builder draft ini masih aktif.
                        </div>
                    @endif
                    <form method="GET" class="row g-2 align-items-end shipment-selection-form">
                        <div class="col-md-3"><label class="form-label">Supplier</label><select name="supplier_id"
                                class="form-control form-control-sm" {{ $selectedItems->isNotEmpty() ? 'disabled' : '' }}>
                                <option value="">Semua Supplier</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" @selected((int) request('supplier_id', $selectedSupplierId) == (int) $supplier->id)>
                                        {{ $supplier->supplier_name }}</option>
                                @endforeach
                            </select>
                            @if ($selectedItems->isNotEmpty())
                                <input type="hidden" name="supplier_id" value="{{ $selectedSupplierId }}">
                            @endif
                        </div>
                        <div class="col-md-8"><label class="form-label">Cari Item / PO / Supplier</label><input
                                type="text" name="keyword" value="{{ request('keyword') }}"
                                class="form-control form-control-sm"
                                placeholder="Contoh: item code, nama item, PO, supplier"></div>
                        <input type="hidden" name="view" value="draft">
                        @foreach ($selectedItemIds as $selectedItemId)
                            <input type="hidden" name="selected_items[]" value="{{ $selectedItemId }}">
                        @endforeach
                        @foreach ($draftQuantities as $itemId => $qty)
                            <input type="hidden" name="shipped_qty[{{ $itemId }}]" value="{{ $qty }}">
                        @endforeach
                        <div class="col-md-1"><button class="btn btn-outline-primary btn-sm w-100">Cari</button></div>
                    </form>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">1. Pilih Item yang Akan Dikirim</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    @if (!$hasSearch)
                        <div class="p-3 text-muted">
                            Kandidat item akan muncul setelah Anda mencari supplier atau keyword item/PO.
                        </div>
                    @else
                        <div class="p-3 border-bottom bg-light">
                            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                                <div>
                                    <div class="font-weight-bold">Kandidat Item</div>
                                    <small class="text-muted">Centang item yang ingin dimasukkan ke draft shipment, lalu
                                        lanjutkan dengan aksi di kanan.</small>
                                </div>
                                <button type="button" class="btn btn-primary btn-sm"
                                    onclick="addCheckedCandidateItems()">Tambahkan ke Draft</button>
                            </div>
                        </div>
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" onchange="toggleCandidateCheckboxes(this.checked)"></th>
                                    <th>Supplier</th>
                                    <th>PO</th>
                                    <th>Item</th>
                                    <th>Outstanding PO</th>
                                    <th>Sudah Dialokasikan</th>
                                    <th>Sisa Bisa Dikirim</th>
                                    <th>ETD</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($candidateItems as $candidate)
                                    @php($isAllocatable = (float) $candidate->available_to_ship_qty > 0)
                                    <tr
                                        class="{{ in_array((int) $candidate->purchase_order_item_id, $selectedItemIds, true) ? 'table-primary' : (! $isAllocatable ? 'table-light' : '') }}">
                                        <td>
                                            <input type="checkbox" class="candidate-item-checkbox"
                                                value="{{ $candidate->purchase_order_item_id }}" {{ $isAllocatable ? '' : 'disabled' }}>
                                        </td>
                                        <td>{{ $candidate->supplier_name }}</td>
                                        <td>{{ $candidate->po_number }}<br><span
                                                class="badge bg-light text-dark">{{ \App\Support\TermCatalog::label('po_status', $candidate->po_status, $candidate->po_status) }}</span></td>
                                        <td><strong>{{ $candidate->item_code }}</strong><br>{{ $candidate->item_name }}
                                        </td>
                                        <td>{{ \App\Support\NumberFormatter::trim($candidate->outstanding_qty) }}</td>
                                        <td>{{ \App\Support\NumberFormatter::trim($candidate->open_shipment_qty) }}</td>
                                        <td>
                                            <span
                                                class="badge {{ $isAllocatable ? 'bg-warning text-dark' : 'bg-secondary' }}">{{ \App\Support\NumberFormatter::trim(max(0, $candidate->available_to_ship_qty)) }}</span>
                                            @if (! $isAllocatable)
                                                <br><small class="text-muted">Sudah teralokasi di shipment aktif</small>
                                            @endif
                                        </td>
                                        <td>{{ $candidate->etd_date ? \Carbon\Carbon::parse($candidate->etd_date)->format('d-m-Y') : '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">Belum ada kandidat. Coba filter
                                            supplier atau cari berdasarkan item yang akan datang.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

            <div class="card card-primary card-outline mb-3">
                <div class="card-header">
                    <h3 class="card-title">2. Simpan Draft Shipment</h3>
                </div>
                <div class="card-body">
                    @if ($selectedItems->isNotEmpty())
                        <div class="alert alert-success">
                            {{ $selectedItems->count() }} item terpilih dari
                            {{ $selectedItems->pluck('purchase_order_id')->unique()->count() }} PO.
                            Supplier: <strong>{{ $selectedItems->first()->supplier_name }}</strong>
                        </div>
                        <div class="mb-3 p-3 border rounded bg-light">
                            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                                <div>
                                    <div class="font-weight-bold">Item Dalam Draft</div>
                                    <small class="text-muted">Tinjau kembali item yang sudah dipilih. Gunakan aksi berikut
                                        jika ada item yang perlu dikeluarkan dari draft.</small>
                                </div>
                                <button type="button" class="btn btn-outline-danger btn-sm"
                                    onclick="removeCheckedDraftItems()">Keluarkan Item Terpilih</button>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            Pilih minimal satu item di tabel di atas. Draft shipment akan dibuat per dokumen supplier, bukan
                            per PO.
                        </div>
                    @endif
                    <form method="POST" action="{{ route('shipments.store') }}" class="row g-2">@csrf
                        <div class="col-md-4">
                            <label class="form-label">Supplier</label>
                            <input type="text" class="form-control form-control-sm"
                                value="{{ optional($selectedItems->first())->supplier_name ?: '-' }}" disabled>
                        </div>
                        <div class="col-md-3"><label class="form-label">No Delivery Note</label><input type="text"
                                name="delivery_note_number" value="{{ old('delivery_note_number') }}"
                                class="form-control form-control-sm" placeholder="No surat jalan supplier" required></div>
                        <div class="col-md-4"><label class="form-label">Tanggal Dokumen</label><input type="date"
                                name="shipment_date" value="{{ old('shipment_date', now()->format('Y-m-d')) }}"
                                class="form-control form-control-sm" required></div>
                        <div class="col-md-4">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" value="1"
                                    name="po_reference_missing" id="po_reference_missing" @checked(old('po_reference_missing') === '1')>
                                <label class="form-check-label" for="po_reference_missing">Nomor PO tidak ada di dokumen
                                    supplier</label>
                            </div>
                        </div>
                        <div class="col-md-8"><input type="text" name="supplier_remark"
                                value="{{ old('supplier_remark') }}" class="form-control form-control-sm"
                                placeholder="Catatan internal, misalnya info supplier atau alasan pencocokan"></div>

                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" onchange="toggleDraftCheckboxes(this.checked)">
                                            </th>
                                            <th>PO</th>
                                            <th>Item</th>
                                            <th>Sisa Bisa Dikirim</th>
                                            <th>Qty di Draft Ini</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($selectedItems as $item)
                                            <tr>
                                                <td><input type="checkbox" class="draft-item-checkbox"
                                                        value="{{ $item->purchase_order_item_id }}"></td>
                                                <td>{{ $item->po_number }}</td>
                                                <td><strong>{{ $item->item_code }}</strong><br>{{ $item->item_name }}</td>
                                                <td>{{ \App\Support\NumberFormatter::trim($item->available_to_ship_qty) }}</td>
                                                <td>
                                                    <input type="hidden" name="selected_items[]"
                                                        value="{{ $item->purchase_order_item_id }}">
                                                    <input type="number" step="0.01" min="0.01"
                                                        max="{{ \App\Support\NumberFormatter::input($item->available_to_ship_qty) }}"
                                                        name="shipped_qty[{{ $item->purchase_order_item_id }}]"
                                                        value="{{ \App\Support\NumberFormatter::input(old('shipped_qty.' . $item->purchase_order_item_id, $draftQuantities[$item->purchase_order_item_id] ?? $item->available_to_ship_qty)) }}"
                                                        class="form-control form-control-sm" required>
                                                </td>
                                                <td class="text-nowrap">
                                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                                        onclick="removeDraftItem('{{ $item->purchase_order_item_id }}')">Batal
                                                        Pilih</button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">Belum ada item terpilih.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button class="btn btn-primary btn-sm"
                                {{ $selectedItems->isEmpty() ? 'disabled' : '' }}>Simpan Draft Shipment</button>
                        </div>
                    </form>
                </div>
            </div>
    @else
            @php($historyRows = $rows->getCollection())
            <div class="history-shell">
                <section class="history-hero">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-5">
                            <div class="history-hero-title">Riwayat shipment dibuat lebih ringkas dan cepat dipindai.</div>
                            <p class="history-hero-copy">Lihat status dokumen supplier, jumlah PO terkait, dan lanjut ke aksi yang relevan.</p>
                        </div>
                        <div class="col-lg-7">
                            <div class="history-stat-grid">
                                <div class="history-stat">
                                    <div class="history-stat-label">Draft</div>
                                    <div class="history-stat-value">{{ $historyRows->where('status', 'Draft')->count() }}</div>
                                </div>
                                <div class="history-stat">
                                    <div class="history-stat-label">Shipped</div>
                                    <div class="history-stat-value">{{ $historyRows->where('status', 'Shipped')->count() }}</div>
                                </div>
                                <div class="history-stat">
                                    <div class="history-stat-label">Partial</div>
                                    <div class="history-stat-value">{{ $historyRows->where('status', 'Partial Received')->count() }}</div>
                                </div>
                                <div class="history-stat">
                                    <div class="history-stat-label">Received</div>
                                    <div class="history-stat-value">{{ $historyRows->where('status', 'Received')->count() }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="history-surface">
                    <div class="surface-head">
                        <div>
                            <h3 class="surface-title">Filter Riwayat Shipment</h3>
                            <div class="surface-subtitle">Cari berdasarkan delivery note supplier.</div>
                        </div>
                    </div>
                    <form method="GET" class="history-filter">
                        <div>
                            <label class="form-label">Cari Delivery Note</label>
                            <input type="text" name="delivery_note_number" value="{{ request('delivery_note_number') }}"
                                class="form-control form-control-sm" placeholder="No surat jalan supplier">
                        </div>
                        <input type="hidden" name="view" value="history">
                        <div><button class="btn btn-primary btn-sm w-100">Cari Riwayat</button></div>
                        <div><a href="{{ route('shipments.history') }}" class="btn btn-light btn-sm w-100">Reset</a></div>
                    </form>
                </section>

                <section class="history-surface">
                    <div class="surface-head">
                        <div>
                            <h3 class="surface-title">Daftar Riwayat Shipment</h3>
                            <div class="surface-subtitle">Dokumen shipment dari draft sampai selesai.</div>
                        </div>
                    </div>
                    <div class="history-table-wrap table-responsive">
                        <table class="table table-hover history-table">
                        <thead>
                            <tr>
                                <th>No Shipment</th>
                                <th>Supplier</th>
                                <th>PO Terkait</th>
                                <th>Line Item</th>
                                <th>Status</th>
                                <th>Delivery Note</th>
                                <th>Tanggal Dokumen</th>
                                <th>Catatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $r)
                                <tr class="{{ $focusedShipmentId === (int) $r->id ? 'table-success' : '' }}">
                                    <td>
                                        <div class="shipment-number">{{ $r->shipment_number }}</div>
                                        @if ($focusedShipmentId === (int) $r->id)
                                            <div class="shipment-meta text-success font-weight-bold">Draft terbaru</div>
                                        @endif
                                    </td>
                                    <td>{{ $r->supplier_name }}</td>
                                    <td>{{ $r->po_numbers ?: '-' }}<br><span class="shipment-meta">{{ $r->po_count }} PO</span></td>
                                    <td>{{ $r->line_count }}</td>
                                    <td><span
                                            class="badge {{ $r->status === 'Draft' ? 'bg-secondary' : ($r->status === 'Shipped' ? 'bg-primary' : ($r->status === 'Partial Received' ? 'bg-warning text-dark' : ($r->status === 'Cancelled' ? 'bg-danger' : 'bg-success'))) }}">{{ \App\Support\TermCatalog::label('shipment_status', $r->status, $r->status) }}</span>
                                    </td>
                                    <td>{{ $r->delivery_note_number ?: '-' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($r->shipment_date)->format('d-m-Y') }}</td>
                                    <td>{{ $r->supplier_remark ?: '-' }}</td>
                                    <td>
                                        @if ($r->status === 'Draft')
                                            <div class="action-stack">
                                                <a href="{{ route('shipments.show', $r->id) }}" class="btn btn-sm btn-light">Lihat</a>
                                                <a href="{{ route('shipments.edit', $r->id) }}" class="btn btn-sm btn-outline-secondary">Edit Draft</a>
                                                <form method="POST"
                                                    action="{{ route('shipments.mark-shipped', $r->id) }}">@csrf
                                                    @method('PATCH')<button class="btn btn-sm btn-outline-primary">Tandai
                                                        Sudah Berangkat</button></form>
                                                <form method="POST"
                                                    action="{{ route('shipments.cancel-draft', $r->id) }}">@csrf
                                                    @method('PATCH')<button class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Batalkan draft shipment ini?')">Batalkan
                                                        Draft</button></form>
                                            </div>
                                        @elseif (in_array($r->status, ['Shipped', 'Partial Received'], true))
                                            <div class="action-stack">
                                                <a href="{{ route('shipments.show', $r->id) }}" class="btn btn-sm btn-light">Lihat</a>
                                                <a href="{{ route('receiving.process', ['supplier_id' => $r->supplier_id, 'shipment_id' => $r->id, 'document_number' => $r->delivery_note_number]) }}"
                                                    class="btn btn-sm btn-outline-primary">Lanjut ke Receiving</a>
                                            </div>
                                        @else
                                            <a href="{{ route('shipments.show', $r->id) }}" class="btn btn-sm btn-light">Lihat</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        </table>
                    </div>
                </section>
            </div>
            <div class="mt-2">{{ $rows->links() }}</div>
    @endif
    <script>
        const draftStorageKey = 'shipment-draft-state-{{ auth()->id() ?? 'guest' }}';

        @if (session('shipment_builder_reset'))
            localStorage.removeItem(draftStorageKey);
            sessionStorage.removeItem('shipment-draft-rehydrated');
        @endif

        const getDraftQuantities = () => {
            const quantities = {};
            document.querySelectorAll('form[method="POST"] input[name^="shipped_qty["]').forEach((input) => {
                quantities[input.name] = input.value;
            });

            return quantities;
        };

        const getSelectedItems = () => {
            const selectedItems = [];
            document.querySelectorAll('form[method="POST"] input[name="selected_items[]"]').forEach((input) => {
                selectedItems.push(input.value);
            });

            return selectedItems;
        };

        const persistDraftState = () => {
            const draftState = {
                supplier_id: document.querySelector('input[name="supplier_id"][type="hidden"]')?.value ||
                    document.querySelector('select[name="supplier_id"]')?.value ||
                    '',
                keyword: document.querySelector('input[name="keyword"]')?.value || '',
                selected_items: getSelectedItems(),
                quantities: getDraftQuantities(),
            };

            localStorage.setItem(draftStorageKey, JSON.stringify(draftState));
        };

        const restoreDraftState = () => {
            const rawState = localStorage.getItem(draftStorageKey);
            if (!rawState) {
                return;
            }

            try {
                const state = JSON.parse(rawState);

                Object.entries(state.quantities || {}).forEach(([name, value]) => {
                    document.querySelectorAll(`input[name="${name.replace(/"/g, '\\"')}"]`).forEach((input) => {
                        input.value = value;
                    });
                });

                const keywordInput = document.querySelector('input[name="keyword"]');
                if (keywordInput && !keywordInput.value && state.keyword) {
                    keywordInput.value = state.keyword;
                }
            } catch (error) {
                localStorage.removeItem(draftStorageKey);
            }
        };

        const submitDraftSelectionState = (selectedItemsOverride = null, quantitiesOverride = null) => {
            let savedState = {};
            try {
                const rawState = localStorage.getItem(draftStorageKey);
                savedState = rawState ? JSON.parse(rawState) : {};
            } catch (error) {
                localStorage.removeItem(draftStorageKey);
            }
            const selectedItems = selectedItemsOverride ?? getSelectedItems();
            const quantities = quantitiesOverride ?? getDraftQuantities();

            const form = document.createElement('form');
            form.method = 'GET';
            form.action = '{{ route('shipments.process') }}';

            const appendHidden = (name, value) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                form.appendChild(input);
            };

            appendHidden('view', 'draft');

            const supplierId = document.querySelector('input[name="supplier_id"][type="hidden"]')?.value ||
                document.querySelector('select[name="supplier_id"]')?.value ||
                savedState.supplier_id ||
                '';
            const keyword = document.querySelector('input[name="keyword"]')?.value || savedState.keyword || '';

            if (supplierId) {
                appendHidden('supplier_id', supplierId);
            }

            if (keyword) {
                appendHidden('keyword', keyword);
            }

            if (selectedItems.length === 0) {
                appendHidden('clear_selection', '1');
            }

            selectedItems.forEach((itemId) => appendHidden('selected_items[]', itemId));
            Object.entries(quantities).forEach(([name, value]) => appendHidden(name, value));

            document.body.appendChild(form);
            form.submit();
        };

        const rehydrateDraftSelection = () => {
            const rawState = localStorage.getItem(draftStorageKey);
            if (!rawState) {
                return;
            }

            const hasSelectedDraftRows = getSelectedItems().length > 0;
            if (hasSelectedDraftRows) {
                sessionStorage.removeItem('shipment-draft-rehydrated');
                return;
            }

            if (sessionStorage.getItem('shipment-draft-rehydrated') === '1') {
                sessionStorage.removeItem('shipment-draft-rehydrated');
                return;
            }

            try {
                const state = JSON.parse(rawState);
                if (!Array.isArray(state.selected_items) || state.selected_items.length === 0) {
                    return;
                }

                sessionStorage.setItem('shipment-draft-rehydrated', '1');
                submitDraftSelectionState(state.selected_items, state.quantities || {});
            } catch (error) {
                sessionStorage.removeItem('shipment-draft-rehydrated');
                localStorage.removeItem(draftStorageKey);
            }
        };

        const syncDraftQuantities = (form) => {
            form.querySelectorAll('input[name^="shipped_qty["]').forEach((input) => input.remove());

            Object.entries(getDraftQuantities()).forEach(([name, value]) => {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = name;
                hidden.value = value;
                form.appendChild(hidden);
            });

            persistDraftState();
        };

        restoreDraftState();
        rehydrateDraftSelection();

        document.querySelectorAll('form[method="POST"] input[name^="shipped_qty["], input[name="keyword"]').forEach((
            input) => {
                input.addEventListener('input', persistDraftState);
                input.addEventListener('change', persistDraftState);
            });

        document.querySelectorAll('.shipment-selection-form').forEach((form) => {
            form.addEventListener('submit', () => syncDraftQuantities(form));
        });

        window.removeDraftItem = (itemId) => {
            persistDraftState();

            const normalizedItemId = String(itemId);
            const nextSelectedItems = getSelectedItems().filter((selectedItemId) => String(selectedItemId) !==
                normalizedItemId);
            const nextQuantities = {
                ...getDraftQuantities()
            };
            delete nextQuantities[`shipped_qty[${normalizedItemId}]`];

            let draftState = {};
            try {
                draftState = JSON.parse(localStorage.getItem(draftStorageKey) || '{}');
            } catch (error) {
                draftState = {};
            }

            draftState.selected_items = nextSelectedItems;
            draftState.quantities = nextQuantities;
            localStorage.setItem(draftStorageKey, JSON.stringify(draftState));

            submitDraftSelectionState(nextSelectedItems, nextQuantities);
        };

        window.toggleCandidateCheckboxes = (checked) => {
            document.querySelectorAll('.candidate-item-checkbox').forEach((checkbox) => {
                checkbox.checked = checked;
            });
        };

        window.toggleDraftCheckboxes = (checked) => {
            document.querySelectorAll('.draft-item-checkbox').forEach((checkbox) => {
                checkbox.checked = checked;
            });
        };

        window.addCheckedCandidateItems = () => {
            persistDraftState();

            const checkedItems = Array.from(document.querySelectorAll('.candidate-item-checkbox:checked')).map((
                checkbox) => checkbox.value);
            if (checkedItems.length === 0) {
                return;
            }

            const nextSelectedItems = Array.from(new Set([...getSelectedItems(), ...checkedItems]));
            const nextQuantities = {
                ...getDraftQuantities()
            };

            let draftState = {};
            try {
                draftState = JSON.parse(localStorage.getItem(draftStorageKey) || '{}');
            } catch (error) {
                draftState = {};
            }

            draftState.selected_items = nextSelectedItems;
            draftState.quantities = nextQuantities;
            localStorage.setItem(draftStorageKey, JSON.stringify(draftState));

            submitDraftSelectionState(nextSelectedItems, nextQuantities);
        };

        window.removeCheckedDraftItems = () => {
            persistDraftState();

            const checkedItems = Array.from(document.querySelectorAll('.draft-item-checkbox:checked')).map((checkbox) =>
                checkbox.value);
            if (checkedItems.length === 0) {
                return;
            }

            const checkedSet = new Set(checkedItems.map(String));
            const nextSelectedItems = getSelectedItems().filter((selectedItemId) => !checkedSet.has(String(
                selectedItemId)));
            const nextQuantities = {
                ...getDraftQuantities()
            };

            checkedItems.forEach((itemId) => {
                delete nextQuantities[`shipped_qty[${itemId}]`];
            });

            let draftState = {};
            try {
                draftState = JSON.parse(localStorage.getItem(draftStorageKey) || '{}');
            } catch (error) {
                draftState = {};
            }

            draftState.selected_items = nextSelectedItems;
            draftState.quantities = nextQuantities;
            localStorage.setItem(draftStorageKey, JSON.stringify(draftState));

            submitDraftSelectionState(nextSelectedItems, nextQuantities);
        };

        document.querySelectorAll(
            'form[action="{{ route('shipments.store') }}"], form[action="{{ route('shipments.process') }}"]').forEach((
            form) => {
            form.addEventListener('submit', persistDraftState);
        });
    </script>
@endsection
