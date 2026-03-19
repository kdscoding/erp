@extends('layouts.erp')
@php($title='Edit Item')
@php($header='Ubah Data Item')
@section('content')
@if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title">Form Edit Item</h3></div>
    <div class="card-body">
        <form method="POST" action="{{ route('items.update', $item->id) }}" class="row g-3">
            @csrf @method('PUT')
            <div class="col-md-3"><label class="form-label">Kode Item</label><input class="form-control" name="item_code" value="{{ old('item_code', $item->item_code) }}" required></div>
            <div class="col-md-5"><label class="form-label">Nama Item</label><input class="form-control" name="item_name" value="{{ old('item_name', $item->item_name) }}" required></div>
            <div class="col-md-4"><label class="form-label">Unit</label><select class="form-select" name="unit_id"><option value="">Pilih Unit</option>@foreach($units as $unit)<option value="{{ $unit->id }}" @selected(old('unit_id', $item->unit_id)==$unit->id)>{{ $unit->unit_name }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label">Kategori</label><input class="form-control" name="category" value="{{ old('category', $item->category) }}"></div>
            <div class="col-md-6"><label class="form-label">Spesifikasi</label><input class="form-control" name="specification" value="{{ old('specification', $item->specification) }}"></div>
            <div class="col-12 d-flex justify-content-end gap-2"><a href="{{ route('items.index') }}" class="btn btn-secondary">Kembali</a><button class="btn btn-primary">Simpan Perubahan</button></div>
        </form>
    </div>
</div>
@endsection
