@extends('layouts.erp')
@php($title='Shipment Tracking')
@php($header='Shipment Tracking')
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

@php($selectedItemIds = collect(request('selected_items', []))->map(fn($id) => (int) $id)->filter()->values()->all())

<div class="card card-outline card-primary mb-3">
<div class="card-header"><h3 class="card-title">1. Identifikasi Dokumen Supplier</h3></div>
<div class="card-body">
<div class="alert alert-info mb-3">
    Mulai dari dokumen supplier, lalu pilih item yang benar-benar akan dikirim. Satu delivery note bisa berisi item dari beberapa PO selama supplier-nya sama.
</div>
<form method="GET" class="row g-2 align-items-end">
<div class="col-md-3"><label class="form-label">Supplier</label><select name="supplier_id" class="form-select"><option value="">Semua Supplier</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>{{ $supplier->supplier_name }}</option>@endforeach</select></div>
<div class="col-md-8"><label class="form-label">Cari Item / PO / Supplier</label><input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control" placeholder="Contoh: item code, nama item, PO, supplier"></div>
@foreach($selectedItemIds as $selectedItemId)<input type="hidden" name="selected_items[]" value="{{ $selectedItemId }}">@endforeach
<div class="col-md-1"><button class="btn btn-outline-primary w-100">Cari</button></div>
</form></div></div>

<div class="card card-outline card-secondary mb-3">
<div class="card-header"><h3 class="card-title">Cari Riwayat Shipment</h3></div>
<div class="card-body">
<form method="GET" class="row g-2 align-items-end">
<div class="col-md-4"><label class="form-label">Cari Delivery Note</label><input type="text" name="delivery_note_number" value="{{ request('delivery_note_number') }}" class="form-control" placeholder="No surat jalan supplier"></div>
@foreach($selectedItemIds as $selectedItemId)<input type="hidden" name="selected_items[]" value="{{ $selectedItemId }}">@endforeach
@if(request('supplier_id'))<input type="hidden" name="supplier_id" value="{{ request('supplier_id') }}">@endif
@if(request('keyword'))<input type="hidden" name="keyword" value="{{ request('keyword') }}">@endif
<div class="col-md-2"><button class="btn btn-outline-secondary w-100">Cari Riwayat</button></div>
</form>
</div></div>

<div class="card mb-3">
<div class="card-header"><h3 class="card-title">2. Pilih Item yang Akan Dikirim</h3></div>
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
<div class="card-header"><h3 class="card-title">3. Buat Draft Shipment Dokumen Supplier</h3></div>
<div class="card-body">
@if($selectedItems->isNotEmpty())
<div class="alert alert-success">
    {{ $selectedItems->count() }} item terpilih dari {{ $selectedItems->pluck('purchase_order_id')->unique()->count() }} PO.
    Supplier: <strong>{{ $selectedItems->first()->supplier_name }}</strong>
</div>
@else
<div class="alert alert-warning">
    Pilih minimal satu item di tabel di atas. Draft shipment akan dibuat per dokumen supplier, bukan per PO.
</div>
@endif
<form method="POST" action="{{ route('shipments.store') }}" class="row g-2">@csrf
<div class="col-md-4"><label class="form-label">Supplier</label><select name="supplier_id" class="form-select" required><option value="">Pilih supplier</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected((int) old('supplier_id', optional($selectedItems->first())->supplier_id) === (int) $supplier->id)>{{ $supplier->supplier_name }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">No Delivery Note</label><input type="text" name="delivery_note_number" value="{{ old('delivery_note_number') }}" class="form-control" placeholder="No surat jalan supplier" required></div>
<div class="col-md-2"><label class="form-label">Tgl Shipment</label><input type="date" name="shipment_date" value="{{ old('shipment_date', now()->format('Y-m-d')) }}" class="form-control" required></div>
<div class="col-md-2"><label class="form-label">ETA</label><input type="date" name="eta_date" value="{{ old('eta_date') }}" class="form-control"></div>
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
            <thead><tr><th>PO</th><th>Item</th><th>Sisa Bisa Dikirim</th><th>Qty di Draft Ini</th></tr></thead>
            <tbody>
            @forelse($selectedItems as $item)
                <tr>
                    <td>{{ $item->po_number }}</td>
                    <td><strong>{{ $item->item_code }}</strong><br>{{ $item->item_name }}</td>
                    <td>{{ number_format($item->available_to_ship_qty, 2, ',', '.') }}</td>
                    <td>
                        <input type="hidden" name="selected_items[]" value="{{ $item->purchase_order_item_id }}">
                        <input type="number" step="0.01" min="0.01" max="{{ $item->available_to_ship_qty }}" name="shipped_qty[{{ $item->purchase_order_item_id }}]" value="{{ old('shipped_qty.'.$item->purchase_order_item_id, $item->available_to_ship_qty) }}" class="form-control" required>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted">Belum ada item terpilih.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
</form></div></div>

<div class="card">
<div class="card-header"><h3 class="card-title">Riwayat Shipment</h3></div>
<div class="card-body table-responsive p-0"><table class="table table-hover text-nowrap mb-0"><thead><tr><th>No Shipment</th><th>Supplier</th><th>PO Terkait</th><th>Line Item</th><th>Status</th><th>Delivery Note</th><th>Tgl</th><th>ETA</th><th>Aksi</th></tr></thead><tbody>@foreach($rows as $r)<tr><td>{{ $r->shipment_number }}</td><td>{{ $r->supplier_name }}</td><td>{{ $r->po_numbers ?: '-' }}<br><small class="text-muted">{{ $r->po_count }} PO</small></td><td>{{ $r->line_count }}</td><td><span class="badge {{ $r->status === 'Draft' ? 'bg-secondary' : ($r->status === 'Shipped' ? 'bg-primary' : ($r->status === 'Partial Received' ? 'bg-warning text-dark' : 'bg-success')) }}">{{ $r->status }}</span></td><td>{{ $r->delivery_note_number ?: '-' }}</td><td>{{ \Carbon\Carbon::parse($r->shipment_date)->format('d-m-Y') }}</td><td>{{ $r->eta_date ? \Carbon\Carbon::parse($r->eta_date)->format('d-m-Y') : '-' }}</td><td>@if($r->status === 'Draft')<form method="POST" action="{{ route('shipments.mark-shipped', $r->id) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-primary">Tandai Sudah Berangkat</button></form>@else - @endif</td></tr>@endforeach</tbody></table></div></div>
<div class="mt-2">{{ $rows->links() }}</div>
@endsection
