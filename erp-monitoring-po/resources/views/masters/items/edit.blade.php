@extends('layouts.erp')
@php($title = 'Edit Item')
@php($header = 'Ubah Data Item')
@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Form Edit Item</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('items.update', $item->id) }}" class="row g-3">
                @csrf @method('PUT')
                <div class="col-md-3"><label class="form-label">Kode Item</label><input class="form-control form-control-sm"
                        name="item_code" value="{{ old('item_code', $item->item_code) }}" required></div>
                <div class="col-md-5"><label class="form-label">Nama Item</label><input class="form-control form-control-sm"
                        name="item_name" value="{{ old('item_name', $item->item_name) }}" required></div>
                <div class="col-md-4"><label class="form-label">Unit</label><select class="form-control form-control-sm"
                        name="unit_id">
                        <option value="">Pilih Unit</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" @selected(old('unit_id', $item->unit_id) == $unit->id)>{{ $unit->unit_name }}</option>
                        @endforeach
                    </select></div>
                @if ($supportsCategoryMaster)
                    <div class="col-md-6"><label class="form-label">Kategori</label><select class="form-control form-control-sm"
                            name="category_id">
                            <option value="">Pilih Kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $item->category_id) == $category->id)>{{ $category->category_name }}</option>
                            @endforeach
                        </select></div>
                @else
                    <div class="col-md-6"><label class="form-label">Kategori</label><input class="form-control form-control-sm"
                            name="category" value="{{ old('category', $item->category ?? null) }}"></div>
                @endif
                <div class="col-md-6"><label class="form-label">Spesifikasi</label><textarea
                        class="form-control form-control-sm" name="specification" rows="3"
                        placeholder="Ukuran, bahan, warna, finishing, atau catatan teknis lain">{{ old('specification', $item->specification) }}</textarea></div>
                <div class="col-12 d-flex justify-content-end gap-2"><a href="{{ route('items.index') }}"
                        class="btn btn-secondary btn-sm">Kembali</a><button class="btn btn-primary btn-sm">Simpan
                        Perubahan</button></div>
            </form>
        </div>
    </div>
@endsection
