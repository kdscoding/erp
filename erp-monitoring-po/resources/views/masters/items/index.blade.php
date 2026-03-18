@extends('layouts.erp')
@php($title='Master Item')
@php($header='Master Item Label/Material')
@section('content')
<div class="card card-primary card-outline mb-3"><div class="card-body"><form method="POST" action="{{ route('items.store') }}" class="row g-2">@csrf
<div class="col-md-2"><input class="form-control" name="item_code" placeholder="Kode Item" required></div>
<div class="col-md-4"><input class="form-control" name="item_name" placeholder="Nama Item" required></div>
<div class="col-md-3"><select class="form-select" name="unit_id"><option value="">Pilih Unit</option>@foreach($units as $u)<option value="{{ $u->id }}">{{ $u->unit_name }}</option>@endforeach</select></div>
<div class="col-md-2"><input class="form-control" name="category" placeholder="Kategori"></div>
<div class="col-md-1"><button class="btn btn-primary w-100">OK</button></div>
</form></div></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-hover mb-0"><thead><tr><th>Kode</th><th>Nama</th><th>Unit</th><th>Kategori</th></tr></thead><tbody>@foreach($rows as $r)<tr><td>{{ $r->item_code }}</td><td>{{ $r->item_name }}</td><td>{{ $r->unit_name }}</td><td>{{ $r->category }}</td></tr>@endforeach</tbody></table></div></div>
@endsection
