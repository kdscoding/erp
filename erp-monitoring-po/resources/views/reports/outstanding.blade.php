@extends('layouts.erp')
@php($title='Laporan Outstanding PO')
@php($header='Outstanding Purchase Order')
@section('content')
<div class="card card-outline card-primary mb-3"><div class="card-body">
<form method="GET" class="row g-2 align-items-end">
    <div class="col-md-3"><label class="form-label">Supplier</label><select name="supplier_id" class="form-select"><option value="">Semua Supplier</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>{{ $supplier->supplier_name }}</option>@endforeach</select></div>
    <div class="col-md-2"><label class="form-label">Status</label><select name="status" class="form-select"><option value="">Semua</option>@foreach(['PO Issued','Confirmed','Shipped','Partial'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select></div>
    <div class="col-md-2"><label class="form-label">Mulai</label><input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control"></div>
    <div class="col-md-2"><label class="form-label">Sampai</label><input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control"></div>
    <div class="col-md-1"><button class="btn btn-primary w-100">Filter</button></div>
</form>
</div></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-striped mb-0"><thead><tr><th>PO Number</th><th>Tanggal</th><th>Supplier</th><th>Status</th><th>ETA</th></tr></thead><tbody>@forelse($rows as $row)<tr><td>{{ $row->po_number }}</td><td>{{ \Carbon\Carbon::parse($row->po_date)->format('d-m-Y') }}</td><td>{{ $row->supplier_name }}</td><td>{{ $row->status }}</td><td>{{ $row->eta_date ? \Carbon\Carbon::parse($row->eta_date)->format('d-m-Y') : '-' }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted">Tidak ada data.</td></tr>@endforelse</tbody></table></div></div>
<div class="mt-2">{{ $rows->links() }}</div>
@endsection
