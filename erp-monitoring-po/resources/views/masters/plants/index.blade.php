@extends('layouts.erp')
@php($title='Master Plant')
@php($header='Master Plant')
@section('content')
<div class="card card-primary card-outline mb-3"><div class="card-body"><form method="POST" action="{{ route('plants.store') }}" class="row g-2">@csrf
<div class="col-md-3"><input class="form-control form-control-sm" name="plant_code" placeholder="Kode Plant" required></div>
<div class="col-md-7"><input class="form-control form-control-sm" name="plant_name" placeholder="Nama Plant" required></div>
<div class="col-md-2"><button class="btn btn-primary btn-sm w-100">Simpan</button></div>
</form></div></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-hover mb-0"><thead><tr><th>Kode</th><th>Nama</th></tr></thead><tbody>@foreach($rows as $r)<tr><td>{{ $r->plant_code }}</td><td>{{ $r->plant_name }}</td></tr>@endforeach</tbody></table></div></div>
@endsection
