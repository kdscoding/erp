@extends('layouts.erp')
@php($title='Edit Warehouse')
@php($header='Ubah Data Warehouse')
@section('content')
@if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title">Form Edit Warehouse</h3></div>
    <div class="card-body">
        <form method="POST" action="{{ route('warehouses.update', $warehouse->id) }}" class="row g-3">
            @csrf @method('PUT')
            <div class="col-md-3"><label class="form-label">Kode</label><input class="form-control" name="warehouse_code" value="{{ old('warehouse_code', $warehouse->warehouse_code) }}" required></div>
            <div class="col-md-5"><label class="form-label">Nama</label><input class="form-control" name="warehouse_name" value="{{ old('warehouse_name', $warehouse->warehouse_name) }}" required></div>
            <div class="col-md-4"><label class="form-label">Lokasi</label><input class="form-control" name="location" value="{{ old('location', $warehouse->location) }}"></div>
            <div class="col-12 d-flex justify-content-end gap-2"><a href="{{ route('warehouses.index') }}" class="btn btn-secondary">Kembali</a><button class="btn btn-primary">Simpan Perubahan</button></div>
        </form>
    </div>
</div>
@endsection
