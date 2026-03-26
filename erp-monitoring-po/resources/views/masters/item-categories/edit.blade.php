@extends('layouts.erp')
@php($title = 'Edit Kategori Item')
@php($header = 'Edit Kategori Item')
@php($headerSubtitle = 'Kategori dipakai untuk pengelompokan item, filter, dan pelaporan.')

@section('content')
<form method="POST" action="{{ route('item-categories.update', $category->id) }}">
    @csrf
    @method('PUT')
    <x-master-edit-layout
        title="Form Edit Kategori"
        subtitle="Kategori dipakai untuk pengelompokan item, filter, dan pelaporan. Jaga istilahnya tetap ringkas dan konsisten."
        :back-route="route('item-categories.index')">
        <div class="col-md-4">
            <label class="form-label">Kode Kategori</label>
            <input class="form-control form-control-sm" name="category_code" value="{{ old('category_code', $category->category_code) }}" required>
        </div>
        <div class="col-md-8">
            <label class="form-label">Nama Kategori</label>
            <input class="form-control form-control-sm" name="category_name" value="{{ old('category_name', $category->category_name) }}" required>
        </div>
        <div class="col-12">
            <label class="form-label">Deskripsi</label>
            <textarea class="form-control form-control-sm" name="description" rows="3">{{ old('description', $category->description) }}</textarea>
        </div>
    </x-master-edit-layout>
</form>
@endsection
