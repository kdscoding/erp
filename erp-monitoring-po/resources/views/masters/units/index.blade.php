@extends('layouts.erp')
@php($title='Master Unit')
@php($header='Master Unit of Measure')
@section('content')
<div class="card card-primary card-outline mb-3"><div class="card-body"><form method="POST" action="{{ route('units.store') }}" class="row g-2">@csrf
<div class="col-md-3"><input class="form-control form-control-sm" name="unit_code" placeholder="Kode Unit" required></div>
<div class="col-md-5"><input class="form-control form-control-sm" name="unit_name" placeholder="Nama Unit" required></div>
<div class="col-md-2"><button class="btn btn-primary btn-sm w-100">Simpan</button></div>
</form></div></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-hover mb-0"><thead><tr><th>Kode</th><th>Nama</th></tr></thead><tbody>@foreach($rows as $r)<tr><td>{{ $r->unit_code }}</td><td>{{ $r->unit_name }}</td></tr>@endforeach</tbody></table></div></div>
@endsection
