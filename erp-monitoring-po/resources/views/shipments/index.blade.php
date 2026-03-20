@extends('layouts.erp')
@php($title='Shipment Tracking')
@php($header='Shipment Tracking')
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

@php($selectedItemIds = collect(request('selected_items', []))->map(fn($id) => (int) $id)->filter()->values()->all())

<ul class="nav nav-tabs mb-3" id="shipmentTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ request('view', 'draft') !== 'history' ? 'active' : '' }}" id="draft-tab" data-toggle="tab" href="#draft-pane" role="tab" aria-controls="draft-pane" aria-selected="{{ request('view', 'draft') !== 'history' ? 'true' : 'false' }}">Pembuatan Draft</a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ request('view') === 'history' ? 'active' : '' }}" id="history-tab" data-toggle="tab" href="#history-pane" role="tab" aria-controls="history-pane" aria-selected="{{ request('view') === 'history' ? 'true' : 'false' }}">Riwayat Shipment</a>
    </li>
</ul>

<div class="tab-content" id="shipmentTabsContent">
<div class="tab-pane fade {{ request('view', 'draft') !== 'history' ? 'show active' : '' }}" id="draft-pane" role="tabpanel" aria-labelledby="draft-tab">
<div class="card card-outline card-primary mb-3">
<div class="card-header"><h3 class="card-title">Pembuatan Draft Shipment</h3></div>
<div class="card-body">
<div class="alert alert-info mb-3">
    Mulai dari dokumen supplier, lalu pilih item yang benar-benar akan dikirim. Satu delivery note bisa berisi item dari beberapa PO selama supplier-nya sama.
</div>
@if($selectedItems->isNotEmpty())
<div class="alert alert-warning mb-3">
    Supplier sudah terkunci ke <strong>{{ $selectedItems->first()->supplier_name }}</strong>. Item dari supplier lain tidak akan ditampilkan sampai pilihan item ini dibersihkan.
</div>
@endif
<form method="GET" class="row g-2 align-items-end shipment-selection-form">
<div class="col-md-3"><label class="form-label">Supplier</label><select name="supplier_id" class="form-select form-select-sm" {{ $selectedItems->isNotEmpty() ? 'disabled' : '' }}><option value="">Semua Supplier</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected((int) request('supplier_id', $selectedSupplierId) == (int) $supplier->id)>{{ $supplier->supplier_name }}</option>@endforeach</select>@if($selectedItems->isNotEmpty())<input type="hidden" name="supplier_id" value="{{ $selectedSupplierId }}">@endif</div>
<div class="col-md-8"><label class="form-label">Cari Item / PO / Supplier</label><input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control form-control-sm" placeholder="Contoh: item code, nama item, PO, supplier"></div>
<input type="hidden" name="view" value="draft">
@foreach($selectedItemIds as $selectedItemId)<input type="hidden" name="selected_items[]" value="{{ $selectedItemId }}">@endforeach
@foreach($draftQuantities as $itemId => $qty)<input type="hidden" name="shipped_qty[{{ $itemId }}]" value="{{ $qty }}">@endforeach
<div class="col-md-1"><button class="btn btn-outline-primary btn-sm w-100">Cari</button></div>
</form></div></div>

<div class="card mb-3">
<div class="card-header"><h3 class="card-title">1. Pilih Item yang Akan Dikirim</h3></div>
<div class="card-body table-responsive p-0">
@if(! $hasSearch)
<div class="p-3 text-muted">
    Kandidat item akan muncul setelah Anda mencari supplier atau keyword item/PO.
</div>
@else
<div class="p-3 border-bottom bg-light">
    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
        <div>
            <div class="font-weight-bold">Kandidat Item</div>
            <small class="text-muted">Centang item yang ingin dimasukkan ke draft shipment, lalu lanjutkan dengan aksi di kanan.</small>
        </div>
        <button type="button" class="btn btn-primary btn-sm" onclick="addCheckedCandidateItems()">Tambahkan ke Draft</button>
    </div>
</div>
<table class="table table-hover mb-0">
<thead><tr><th><input type="checkbox" onchange="toggleCandidateCheckboxes(this.checked)"></th><th>Supplier</th><th>PO</th><th>Item</th><th>Outstanding PO</th><th>Sudah Dialokasikan</th><th>Sisa Bisa Dikirim</th><th>ETD</th></tr></thead>
<tbody>
@forelse($candidateItems as $candidate)
<tr class="{{ in_array((int) $candidate->purchase_order_item_id, $selectedItemIds, true) ? 'table-primary' : '' }}">
    <td>
        <input type="checkbox" class="candidate-item-checkbox" value="{{ $candidate->purchase_order_item_id }}">
    </td>
    <td>{{ $candidate->supplier_name }}</td>
    <td>{{ $candidate->po_number }}<br><span class="badge bg-light text-dark">{{ $candidate->po_status }}</span></td>
    <td><strong>{{ $candidate->item_code }}</strong><br>{{ $candidate->item_name }}</td>
    <td>{{ number_format($candidate->outstanding_qty, 2, ',', '.') }}</td>
    <td>{{ number_format($candidate->open_shipment_qty, 2, ',', '.') }}</td>
    <td><span class="badge bg-warning text-dark">{{ number_format($candidate->available_to_ship_qty, 2, ',', '.') }}</span></td>
    <td>{{ $candidate->etd_date ? \Carbon\Carbon::parse($candidate->etd_date)->format('d-m-Y') : '-' }}</td>
</tr>
@empty
<tr><td colspan="8" class="text-center text-muted">Belum ada kandidat. Coba filter supplier atau cari berdasarkan item yang akan datang.</td></tr>
@endforelse
</tbody>
</table>
@endif
</div></div>

<div class="card card-primary card-outline mb-3">
<div class="card-header"><h3 class="card-title">2. Simpan Draft Shipment</h3></div>
<div class="card-body">
@if($selectedItems->isNotEmpty())
<div class="alert alert-success">
    {{ $selectedItems->count() }} item terpilih dari {{ $selectedItems->pluck('purchase_order_id')->unique()->count() }} PO.
    Supplier: <strong>{{ $selectedItems->first()->supplier_name }}</strong>
</div>
<div class="mb-3 p-3 border rounded bg-light">
    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
        <div>
            <div class="font-weight-bold">Item Dalam Draft</div>
            <small class="text-muted">Tinjau kembali item yang sudah dipilih. Gunakan aksi berikut jika ada item yang perlu dikeluarkan dari draft.</small>
        </div>
        <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeCheckedDraftItems()">Keluarkan Item Terpilih</button>
    </div>
</div>
@else
<div class="alert alert-warning">
    Pilih minimal satu item di tabel di atas. Draft shipment akan dibuat per dokumen supplier, bukan per PO.
</div>
@endif
<form method="POST" action="{{ route('shipments.store') }}" class="row g-2">@csrf
<div class="col-md-4">
    <label class="form-label">Supplier</label>
    <input type="text" class="form-control form-control-sm" value="{{ optional($selectedItems->first())->supplier_name ?: '-' }}" disabled>
</div>
<div class="col-md-3"><label class="form-label">No Delivery Note</label><input type="text" name="delivery_note_number" value="{{ old('delivery_note_number') }}" class="form-control form-control-sm" placeholder="No surat jalan supplier" required></div>
<div class="col-md-4"><label class="form-label">Tanggal Dokumen</label><input type="date" name="shipment_date" value="{{ old('shipment_date', now()->format('Y-m-d')) }}" class="form-control form-control-sm" required></div>
<div class="col-md-4">
    <div class="form-check mt-2">
        <input class="form-check-input" type="checkbox" value="1" name="po_reference_missing" id="po_reference_missing" @checked(old('po_reference_missing') === '1')>
        <label class="form-check-label" for="po_reference_missing">Nomor PO tidak ada di dokumen supplier</label>
    </div>
</div>
<div class="col-md-8"><input type="text" name="supplier_remark" value="{{ old('supplier_remark') }}" class="form-control form-control-sm" placeholder="Catatan internal, misalnya info supplier atau alasan pencocokan"></div>

<div class="col-12">
    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-0">
            <thead><tr><th><input type="checkbox" onchange="toggleDraftCheckboxes(this.checked)"></th><th>PO</th><th>Item</th><th>Sisa Bisa Dikirim</th><th>Qty di Draft Ini</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse($selectedItems as $item)
                <tr>
                    <td><input type="checkbox" class="draft-item-checkbox" value="{{ $item->purchase_order_item_id }}"></td>
                    <td>{{ $item->po_number }}</td>
                    <td><strong>{{ $item->item_code }}</strong><br>{{ $item->item_name }}</td>
                    <td>{{ number_format($item->available_to_ship_qty, 2, ',', '.') }}</td>
                    <td>
                        <input type="hidden" name="selected_items[]" value="{{ $item->purchase_order_item_id }}">
                        <input type="number" step="0.01" min="0.01" max="{{ $item->available_to_ship_qty }}" name="shipped_qty[{{ $item->purchase_order_item_id }}]" value="{{ old('shipped_qty.'.$item->purchase_order_item_id, $draftQuantities[$item->purchase_order_item_id] ?? $item->available_to_ship_qty) }}" class="form-control form-control-sm" required>
                    </td>
                    <td class="text-nowrap">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeDraftItem('{{ $item->purchase_order_item_id }}')">Batal Pilih</button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">Belum ada item terpilih.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="col-12 d-flex justify-content-end">
    <button class="btn btn-primary btn-sm" {{ $selectedItems->isEmpty() ? 'disabled' : '' }}>Simpan Draft Shipment</button>
</div>
</form></div></div>
</div>

<div class="tab-pane fade {{ request('view') === 'history' ? 'show active' : '' }}" id="history-pane" role="tabpanel" aria-labelledby="history-tab">
<div class="card card-outline card-secondary mb-3">
<div class="card-header"><h3 class="card-title">Riwayat Shipment</h3></div>
<div class="card-body">
<form method="GET" class="row g-2 align-items-end">
<div class="col-md-4"><label class="form-label">Cari Delivery Note</label><input type="text" name="delivery_note_number" value="{{ request('delivery_note_number') }}" class="form-control form-control-sm" placeholder="No surat jalan supplier"></div>
<input type="hidden" name="view" value="history">
<div class="col-md-2"><button class="btn btn-outline-secondary btn-sm w-100">Cari Riwayat</button></div>
</form>
</div></div>

<div class="card">
<div class="card-header"><h3 class="card-title">Daftar Riwayat Shipment</h3></div>
<div class="card-body table-responsive p-0"><table class="table table-hover text-nowrap mb-0"><thead><tr><th>No Shipment</th><th>Supplier</th><th>PO Terkait</th><th>Line Item</th><th>Status</th><th>Delivery Note</th><th>Tanggal Dokumen</th><th>Catatan</th><th>Aksi</th></tr></thead><tbody>@foreach($rows as $r)<tr><td>{{ $r->shipment_number }}</td><td>{{ $r->supplier_name }}</td><td>{{ $r->po_numbers ?: '-' }}<br><small class="text-muted">{{ $r->po_count }} PO</small></td><td>{{ $r->line_count }}</td><td><span class="badge {{ $r->status === 'Draft' ? 'bg-secondary' : ($r->status === 'Shipped' ? 'bg-primary' : ($r->status === 'Partial Received' ? 'bg-warning text-dark' : ($r->status === 'Cancelled' ? 'bg-danger' : 'bg-success'))) }}">{{ $r->status }}</span></td><td>{{ $r->delivery_note_number ?: '-' }}</td><td>{{ \Carbon\Carbon::parse($r->shipment_date)->format('d-m-Y') }}</td><td>{{ $r->supplier_remark ?: '-' }}</td><td>@if($r->status === 'Draft')<div class="d-flex gap-1"><form method="POST" action="{{ route('shipments.mark-shipped', $r->id) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-primary">Tandai Sudah Berangkat</button></form><form method="POST" action="{{ route('shipments.cancel-draft', $r->id) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-danger" onclick="return confirm('Batalkan draft shipment ini?')">Batalkan Draft</button></form></div>@else - @endif</td></tr>@endforeach</tbody></table></div></div>
<div class="mt-2">{{ $rows->links() }}</div>
</div>
</div>
<script>
    const draftStorageKey = 'shipment-draft-state-{{ auth()->id() ?? "guest" }}';

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
            supplier_id: document.querySelector('input[name="supplier_id"][type="hidden"]')?.value
                || document.querySelector('select[name="supplier_id"]')?.value
                || '',
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
        form.action = '{{ route('shipments.index') }}';

        const appendHidden = (name, value) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            form.appendChild(input);
        };

        appendHidden('view', 'draft');

        const supplierId = document.querySelector('input[name="supplier_id"][type="hidden"]')?.value
            || document.querySelector('select[name="supplier_id"]')?.value
            || savedState.supplier_id
            || '';
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

    document.querySelectorAll('form[method="POST"] input[name^="shipped_qty["], input[name="keyword"]').forEach((input) => {
        input.addEventListener('input', persistDraftState);
        input.addEventListener('change', persistDraftState);
    });

    document.querySelectorAll('.shipment-selection-form').forEach((form) => {
        form.addEventListener('submit', () => syncDraftQuantities(form));
    });

    window.removeDraftItem = (itemId) => {
        persistDraftState();

        const normalizedItemId = String(itemId);
        const nextSelectedItems = getSelectedItems().filter((selectedItemId) => String(selectedItemId) !== normalizedItemId);
        const nextQuantities = {...getDraftQuantities()};
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

        const checkedItems = Array.from(document.querySelectorAll('.candidate-item-checkbox:checked')).map((checkbox) => checkbox.value);
        if (checkedItems.length === 0) {
            return;
        }

        const nextSelectedItems = Array.from(new Set([...getSelectedItems(), ...checkedItems]));
        const nextQuantities = {...getDraftQuantities()};

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

        const checkedItems = Array.from(document.querySelectorAll('.draft-item-checkbox:checked')).map((checkbox) => checkbox.value);
        if (checkedItems.length === 0) {
            return;
        }

        const checkedSet = new Set(checkedItems.map(String));
        const nextSelectedItems = getSelectedItems().filter((selectedItemId) => !checkedSet.has(String(selectedItemId)));
        const nextQuantities = {...getDraftQuantities()};

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

    document.querySelectorAll('form[action="{{ route('shipments.store') }}"], form[action="{{ route('shipments.index') }}"]').forEach((form) => {
        form.addEventListener('submit', persistDraftState);
    });
</script>
@endsection
