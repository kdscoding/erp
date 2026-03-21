@extends('layouts.erp')
@php($title = 'Edit Kategori Item')
@php($header = 'Ubah Kategori Barang')
@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Form Edit Kategori</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('item-categories.update', $category->id) }}" class="row g-3">
                @csrf
                @method('PUT')
                <div class="col-md-4">
                    <label class="form-label">Kode Kategori</label>
                    <input class="form-control form-control-sm" name="category_code"
                        value="{{ old('category_code', $category->category_code) }}" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Nama Kategori</label>
                    <input class="form-control form-control-sm" name="category_name"
                        value="{{ old('category_name', $category->category_name) }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea class="form-control form-control-sm" name="description" rows="3"
                        placeholder="Catatan kategori atau panduan klasifikasi">{{ old('description', $category->description) }}</textarea>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="{{ route('item-categories.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
                    <button class="btn btn-primary btn-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
