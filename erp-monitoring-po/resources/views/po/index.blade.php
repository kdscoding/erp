@extends('layouts.erp')
@php($title='Purchase Order')
@php($header='Purchase Order Monitoring')
@section('content')
<div class="card card-outline card-primary mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Supplier</label>
                <select name="supplier_id" class="form-select">
                    <option value="">Semua Supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>{{ $supplier->supplier_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    @foreach(['PO Issued','Confirmed','Partial','Closed','Cancelled'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
            <div class="col-md-3 text-end"><a href="{{ route('po.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Buat PO</a></div>
        </form>
    </div>
</div>

<div class="card"><div class="card-body table-responsive p-0"><table class="table table-hover text-nowrap mb-0 data-table">
<thead><tr><th>PO Number</th><th>PO Date</th><th>Supplier</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
<tbody>
@forelse($rows as $r)
<tr>
    <td>{{ $r->po_number }}</td>
    <td>{{ \Carbon\Carbon::parse($r->po_date)->format('d-m-Y') }}</td>
    <td>{{ $r->supplier_name }}</td>
    <td><span class="badge {{ in_array($r->status,['Closed']) ? 'bg-success' : (in_array($r->status,['Cancelled','Late']) ? 'bg-danger' : 'bg-warning text-dark') }}">{{ $r->status }}</span></td>
    <td class="text-end"><a href="{{ route('po.show', $r->id) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
</tr>
@empty
<tr><td colspan="5" class="text-center text-muted">Belum ada PO</td></tr>
@endforelse
</tbody>
</table></div></div>
<div class="mt-2">{{ $rows->links() }}</div>
@endsection
