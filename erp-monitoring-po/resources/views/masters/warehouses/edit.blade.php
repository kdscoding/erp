@extends('layouts.erp')
@php($title='Edit Warehouse')
@php($header='Ubah Data Warehouse')
@section('content')
<form method="POST" action="{{ route('warehouses.update', $warehouse->id) }}" class="row g-3">
    @csrf @method('PUT')
    <x-master-edit-layout
        title="Form Edit Warehouse"
        subtitle="Pastikan kode gudang, nama, dan lokasi tetap jelas karena master ini dipakai pada PO dan GR."
        :back-route="route('warehouses.index')">
        <div class="col-md-3"><label class="form-label">Kode Gudang</label><input class="form-control form-control-sm" name="warehouse_code" value="{{ old('warehouse_code', $warehouse->warehouse_code) }}" required></div>
        <div class="col-md-5"><label class="form-label">Nama Gudang</label><input class="form-control form-control-sm" name="warehouse_name" value="{{ old('warehouse_name', $warehouse->warehouse_name) }}" required></div>
        <div class="col-md-4"><label class="form-label">Lokasi</label><textarea class="form-control form-control-sm" name="location" rows="3" placeholder="Lokasi fisik gudang">{{ old('location', $warehouse->location) }}</textarea></div>
    </x-master-edit-layout>
</form>
@endsection
