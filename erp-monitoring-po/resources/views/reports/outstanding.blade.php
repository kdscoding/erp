@extends('layouts.erp')
@php($title='Outstanding PO')
@php($header='Laporan Outstanding PO')
@section('content')
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-hover text-nowrap mb-0"><thead><tr><th>PO</th><th>Tgl</th><th>Supplier</th><th>Status</th></tr></thead><tbody>@foreach($rows as $r)<tr><td>{{ $r->po_number }}</td><td>{{ $r->po_date }}</td><td>{{ $r->supplier_name }}</td><td>{{ $r->status }}</td></tr>@endforeach</tbody></table></div></div>
@endsection
