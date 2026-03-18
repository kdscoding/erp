@extends('layouts.erp')
@php($title='Master Warehouse')
@php($header='Master Warehouse')
@section('content')
<div class="card card-primary card-outline mb-3"><div class="card-body"><form method="POST" action="{{ route('warehouses.store') }}" class="row g-2">@csrf
<div class="col-md-2"><input class="form-control" name="warehouse_code" placeholder="Kode" required></div>
<div class="col-md-4"><input class="form-control" name="warehouse_name" placeholder="Nama" required></div>
<div class="col-md-4"><input class="form-control" name="location" placeholder="Lokasi"></div>
<div class="col-md-2"><button class="btn btn-primary w-100">Simpan</button></div>
</form></div></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-hover mb-0"><thead><tr><th>Kode</th><th>Nama</th><th>Lokasi</th></tr></thead><tbody>@foreach($rows as $r)<tr><td>{{ $r->warehouse_code }}</td><td>{{ $r->warehouse_name }}</td><td>{{ $r->location }}</td></tr>@endforeach</tbody></table></div></div>
@endsection
