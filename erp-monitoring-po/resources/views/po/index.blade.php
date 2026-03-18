@extends('layouts.erp')
@php($title='Purchase Order')
@php($header='Purchase Order Monitoring')
@section('content')
<div class="mb-3"><a href="{{ route('po.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Buat PO</a></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-hover text-nowrap mb-0">
<thead><tr><th>PO Number</th><th>PO Date</th><th>Supplier</th><th>Status</th></tr></thead>
<tbody>
@forelse($rows as $r)
<tr><td>{{ $r->po_number }}</td><td>{{ \Carbon\Carbon::parse($r->po_date)->format('d-m-Y') }}</td><td>{{ $r->supplier_name }}</td><td><span class="badge bg-secondary">{{ $r->status }}</span></td></tr>
@empty
<tr><td colspan="4" class="text-center text-muted">Belum ada PO</td></tr>
@endforelse
</tbody>
</table></div></div>
@endsection
