@extends('layouts.erp')
@php($title='Edit Unit')
@php($header='Ubah Data Unit')
@section('content')
@if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title">Form Edit Unit</h3></div>
    <div class="card-body">
        <form method="POST" action="{{ route('units.update', $unit->id) }}" class="row g-3">
            @csrf @method('PUT')
            <div class="col-md-4"><label class="form-label">Kode Unit</label><input class="form-control form-control-sm" name="unit_code" value="{{ old('unit_code', $unit->unit_code) }}" required></div>
            <div class="col-md-6"><label class="form-label">Nama Unit</label><input class="form-control form-control-sm" name="unit_name" value="{{ old('unit_name', $unit->unit_name) }}" required></div>
            <div class="col-12 d-flex justify-content-end gap-2"><a href="{{ route('units.index') }}" class="btn btn-secondary btn-sm">Kembali</a><button class="btn btn-primary btn-sm">Simpan Perubahan</button></div>
        </form>
    </div>
</div>
@endsection
