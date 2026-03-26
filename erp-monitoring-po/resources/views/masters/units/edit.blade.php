@extends('layouts.erp')
@php($title='Edit Unit')
@php($header='Edit Unit')
@php($headerSubtitle='Gunakan kode singkat dan nama unit yang konsisten.')

@section('content')
<form method="POST" action="{{ route('units.update', $unit->id) }}">
    @csrf
    @method('PUT')
    <x-master-edit-layout
        title="Form Edit Unit"
        subtitle="Gunakan kode singkat dan nama unit yang konsisten agar tidak terjadi variasi satuan di item atau transaksi."
        :back-route="route('units.index')">
        <div class="col-md-4"><label class="form-label">Kode Unit</label><input class="form-control form-control-sm" name="unit_code" value="{{ old('unit_code', $unit->unit_code) }}" required></div>
        <div class="col-md-8"><label class="form-label">Nama Unit</label><input class="form-control form-control-sm" name="unit_name" value="{{ old('unit_name', $unit->unit_name) }}" required></div>
    </x-master-edit-layout>
</form>
@endsection
