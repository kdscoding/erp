@extends('layouts.erp')
@php($title = 'Edit Draft Shipment')
@php($header = 'Edit Draft Shipment')

@section('content')
@if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<form method="POST" action="{{ route('shipments.update', $shipment->id) }}">
    @csrf
    @method('PUT')

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Ubah Draft {{ $shipment->shipment_number }}</h3>
            <div class="d-flex gap-2">
                <a href="{{ route('shipments.show', $shipment->id) }}" class="btn btn-sm btn-light">Lihat Detail</a>
                <a href="{{ route('shipments.history', ['focus' => $shipment->id]) }}" class="btn btn-sm btn-light">Kembali ke Riwayat</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label">Supplier</label>
                    <input type="text" class="form-control form-control-sm" value="{{ $shipment->supplier_name }}" disabled>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Delivery Note</label>
                    <input type="text" name="delivery_note_number" class="form-control form-control-sm" value="{{ old('delivery_note_number', $shipment->delivery_note_number) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Dokumen</label>
                    <input type="date" name="shipment_date" class="form-control form-control-sm" value="{{ old('shipment_date', \Carbon\Carbon::parse($shipment->shipment_date)->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Catatan</label>
                    <input type="text" name="supplier_remark" class="form-control form-control-sm" value="{{ old('supplier_remark', $shipment->supplier_remark) }}">
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Item Draft Shipment</h3></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Pakai</th>
                        <th>PO</th>
                        <th>Item</th>
                        <th>Qty Draft</th>
                        <th>Maks Bisa Dipakai</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lines as $line)
                        @php($maxQty = $line->available_to_ship_qty + $line->shipped_qty)
                        <tr>
                            <td>
                                <input type="hidden" name="shipment_items[{{ $loop->index }}][id]" value="{{ $line->shipment_item_id }}">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="shipment_items[{{ $loop->index }}][keep]" value="1" id="keep_{{ $line->shipment_item_id }}" {{ old("shipment_items.{$loop->index}.keep", '1') === '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="keep_{{ $line->shipment_item_id }}">Pertahankan</label>
                                </div>
                            </td>
                            <td>{{ $line->po_number }}</td>
                            <td><strong>{{ $line->item_code }}</strong><br>{{ $line->item_name }}</td>
                            <td><input type="number" step="0.01" min="0.01" max="{{ $maxQty }}" name="shipment_items[{{ $loop->index }}][shipped_qty]" class="form-control form-control-sm" value="{{ old("shipment_items.{$loop->index}.shipped_qty", $line->shipped_qty) }}" required></td>
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
@endsection
