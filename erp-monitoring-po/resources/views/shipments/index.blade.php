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
        <button class="nav-link {{ request('view', 'draft') !== 'history' ? 'active' : '' }}" id="draft-tab" data-bs-toggle="tab" data-bs-target="#draft-pane" type="button" role="tab" aria-controls="draft-pane" aria-selected="{{ request('view', 'draft') !== 'history' ? 'true' : 'false' }}">Pembuatan Draft</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ request('view') === 'history' ? 'active' : '' }}" id="history-tab" data-bs-toggle="tab" data-bs-target="#history-pane" type="button" role="tab" aria-controls="history-pane" aria-selected="{{ request('view') === 'history' ? 'true' : 'false' }}">Riwayat Shipment</button>
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
<form method="GET" class="row g-2 align-items-end">
<div class="col-md-3"><label class="form-label">Supplier</label><select name="supplier_id" class="form-select" {{ $selectedItems->isNotEmpty() ? 'disabled' : '' }}><option value="">Semua Supplier</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected((int) request('supplier_id', $selectedSupplierId) == (int) $supplier->id)>{{ $supplier->supplier_name }}</option>@endforeach</select>@if($selectedItems->isNotEmpty())<input type="hidden" name="supplier_id" value="{{ $selectedSupplierId }}">@endif</div>
<div class="col-md-8"><label class="form-label">Cari Item / PO / Supplier</label><input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control" placeholder="Contoh: item code, nama item, PO, supplier"></div>
<input type="hidden" name="view" value="draft">
@foreach($selectedItemIds as $selectedItemId)<input type="hidden" name="selected_items[]" value="{{ $selectedItemId }}">@endforeach
@foreach($draftQuantities as $itemId => $qty)<input type="hidden" name="shipped_qty[{{ $itemId }}]" value="{{ $qty }}">@endforeach
<div class="col-md-1"><button class="btn btn-outline-primary w-100">Cari</button></div>
</form></div></div>

<div class="card mb-3">
<div class="card-header"><h3 class="card-title">1. Pilih Item yang Akan Dikirim</h3></div>
<div class="card-body table-responsive p-0">
@if(! $hasSearch)
<div class="p-3 text-muted">
    Kandidat item akan muncul setelah Anda mencari supplier atau keyword item/PO.
</div>
@else
<table class="table table-hover mb-0">
<thead><tr><th>Pilih</th><th>Supplier</th><th>PO</th><th>Item</th><th>Outstanding PO</th><th>Sudah Dialokasikan</th><th>Sisa Bisa Dikirim</th><th>ETD</th></tr></thead>
<tbody>
@forelse($candidateItems as $candidate)
<tr class="{{ in_array((int) $candidate->purchase_order_item_id, $selectedItemIds, true) ? 'table-primary' : '' }}">
    <td>
        <form method="GET">
            @foreach(request()->except('selected_items') as $key => $value)
                @if(is_array($value))
                    @foreach($value as $nestedValue)
                        <input type="hidden" name="{{ $key }}[]" value="{{ $nestedValue }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            @php($updatedSelections = collect($selectedItemIds))
            @if(in_array((int) $candidate->purchase_order_item_id, $selectedItemIds, true))
                @php($updatedSelections = $updatedSelections->reject(fn($id) => (int) $id === (int) $candidate->purchase_order_item_id)->values())
            @else
                @php($updatedSelections = $updatedSelections->push((int) $candidate->purchase_order_item_id)->unique()->values())
            @endif
            @foreach($updatedSelections as $selectedItemId)
                <input type="hidden" name="selected_items[]" value="{{ $selectedItemId }}">
            @endforeach
            @foreach($draftQuantities as $itemId => $qty)
                <input type="hidden" name="shipped_qty[{{ $itemId }}]" value="{{ $qty }}">
            @endforeach
            <button class="btn btn-sm {{ in_array((int) $candidate->purchase_order_item_id, $selectedItemIds, true) ? 'btn-outline-danger' : 'btn-outline-primary' }}">
                {{ in_array((int) $candidate->purchase_order_item_id, $selectedItemIds, true) ? 'Batalkan' : 'Pilih' }}
            </button>
        </form>
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
<div class="mb-3">
    <form method="GET" action="{{ route('shipments.index') }}">
        <input type="hidden" name="view" value="draft">
        <input type="hidden" name="clear_selection" value="1">
        <button class="btn btn-sm btn-outline-danger">Bersihkan Pilihan Item</button>
    </form>
</div>
@else
<div class="alert alert-warning">
    Pilih minimal satu item di tabel di atas. Draft shipment akan dibuat per dokumen supplier, bukan per PO.
</div>
@endif
<form method="POST" action="{{ route('shipments.store') }}" class="row g-2">@csrf
<div class="col-md-4">
    <label class="form-label">Supplier</label>
    <input type="text" class="form-control" value="{{ optional($selectedItems->first())->supplier_name ?: '-' }}" disabled>
</div>
<div class="col-md-3"><label class="form-label">No Delivery Note</label><input type="text" name="delivery_note_number" value="{{ old('delivery_note_number') }}" class="form-control" placeholder="No surat jalan supplier" required></div>
<div class="col-md-4"><label class="form-label">Tanggal Dokumen</label><input type="date" name="shipment_date" value="{{ old('shipment_date', now()->format('Y-m-d')) }}" class="form-control" required></div>
<div class="col-md-1 d-flex align-items-end"><button class="btn btn-primary w-100" {{ $selectedItems->isEmpty() ? 'disabled' : '' }}>Simpan</button></div>
<div class="col-md-4">
    <div class="form-check mt-2">
        <input class="form-check-input" type="checkbox" value="1" name="po_reference_missing" id="po_reference_missing" @checked(old('po_reference_missing') === '1')>
        <label class="form-check-label" for="po_reference_missing">Nomor PO tidak ada di dokumen supplier</label>
    </div>
</div>
<div class="col-md-8"><input type="text" name="supplier_remark" value="{{ old('supplier_remark') }}" class="form-control" placeholder="Catatan internal, misalnya info supplier atau alasan pencocokan"></div>

<div class="col-12">
    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-0">
            <thead><tr><th>PO</th><th>Item</th><th>Sisa Bisa Dikirim</th><th>Qty di Draft Ini</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse($selectedItems as $item)
                <tr>
                    <td>{{ $item->po_number }}</td>
                    <td><strong>{{ $item->item_code }}</strong><br>{{ $item->item_name }}</td>
                    <td>{{ number_format($item->available_to_ship_qty, 2, ',', '.') }}</td>
                    <td>
                        <input type="hidden" name="selected_items[]" value="{{ $item->purchase_order_item_id }}">
                        <input type="number" step="0.01" min="0.01" max="{{ $item->available_to_ship_qty }}" name="shipped_qty[{{ $item->purchase_order_item_id }}]" value="{{ old('shipped_qty.'.$item->purchase_order_item_id, $draftQuantities[$item->purchase_order_item_id] ?? $item->available_to_ship_qty) }}" class="form-control" required>
                    </td>
                    <td>
                        <form method="GET">
                            @foreach(request()->except('selected_items', 'shipped_qty') as $key => $value)
                                @if(is_array($value))
                                    @foreach($value as $nestedValue)
                                        <input type="hidden" name="{{ $key }}[]" value="{{ $nestedValue }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            @foreach(collect($selectedItemIds)->reject(fn($id) => (int) $id === (int) $item->purchase_order_item_id) as $remainingId)
                                <input type="hidden" name="selected_items[]" value="{{ $remainingId }}">
                            @endforeach
                            @foreach($draftQuantities as $qtyItemId => $qty)
                                @if((int) $qtyItemId !== (int) $item->purchase_order_item_id)
                                    <input type="hidden" name="shipped_qty[{{ $qtyItemId }}]" value="{{ $qty }}">
                                @endif
                            @endforeach
                            <button class="btn btn-sm btn-outline-danger">Batal Pilih</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">Belum ada item terpilih.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
</form></div></div>
</div>

<div class="tab-pane fade {{ request('view') === 'history' ? 'show active' : '' }}" id="history-pane" role="tabpanel" aria-labelledby="history-tab">
<div class="card card-outline card-secondary mb-3">
<div class="card-header"><h3 class="card-title">Riwayat Shipment</h3></div>
<div class="card-body">
<form method="GET" class="row g-2 align-items-end">
<div class="col-md-4"><label class="form-label">Cari Delivery Note</label><input type="text" name="delivery_note_number" value="{{ request('delivery_note_number') }}" class="form-control" placeholder="No surat jalan supplier"></div>
<input type="hidden" name="view" value="history">
<div class="col-md-2"><button class="btn btn-outline-secondary w-100">Cari Riwayat</button></div>
</form>
</div></div>

<div class="card">
<div class="card-header"><h3 class="card-title">Daftar Riwayat Shipment</h3></div>
<div class="card-body table-responsive p-0"><table class="table table-hover text-nowrap mb-0"><thead><tr><th>No Shipment</th><th>Supplier</th><th>PO Terkait</th><th>Line Item</th><th>Status</th><th>Delivery Note</th><th>Tanggal Dokumen</th><th>Catatan</th><th>Aksi</th></tr></thead><tbody>@foreach($rows as $r)<tr><td>{{ $r->shipment_number }}</td><td>{{ $r->supplier_name }}</td><td>{{ $r->po_numbers ?: '-' }}<br><small class="text-muted">{{ $r->po_count }} PO</small></td><td>{{ $r->line_count }}</td><td><span class="badge {{ $r->status === 'Draft' ? 'bg-secondary' : ($r->status === 'Shipped' ? 'bg-primary' : ($r->status === 'Partial Received' ? 'bg-warning text-dark' : ($r->status === 'Cancelled' ? 'bg-danger' : 'bg-success'))) }}">{{ $r->status }}</span></td><td>{{ $r->delivery_note_number ?: '-' }}</td><td>{{ \Carbon\Carbon::parse($r->shipment_date)->format('d-m-Y') }}</td><td>{{ $r->supplier_remark ?: '-' }}</td><td>@if($r->status === 'Draft')<div class="d-flex gap-1"><form method="POST" action="{{ route('shipments.mark-shipped', $r->id) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-primary">Tandai Sudah Berangkat</button></form><form method="POST" action="{{ route('shipments.cancel-draft', $r->id) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-danger" onclick="return confirm('Batalkan draft shipment ini?')">Batalkan Draft</button></form></div>@else - @endif</td></tr>@endforeach</tbody></table></div></div>
<div class="mt-2">{{ $rows->links() }}</div>
</div>
</div>
@endsection
