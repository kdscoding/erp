@extends('layouts.erp')
@php($title = 'Edit Item')
@php($header = 'Edit Item')
@php($headerSubtitle = 'Rapikan identitas barang, satuan, kategori, dan spesifikasi.')

@section('content')
<form method="POST" action="{{ route('items.update', $item->id) }}">
    @csrf
    @method('PUT')
    <x-master-edit-layout
        title="Form Edit Item"
        subtitle="Rapikan identitas barang, satuan, kategori, dan spesifikasi agar pencarian serta proses PO tetap konsisten."
        :back-route="route('items.index')">
        <div class="col-md-3"><label class="form-label">Kode Item</label><input class="form-control form-control-sm" name="item_code" value="{{ old('item_code', $item->item_code) }}" required></div>
        <div class="col-md-5"><label class="form-label">Nama Item</label><input class="form-control form-control-sm" name="item_name" value="{{ old('item_name', $item->item_name) }}" required></div>
        <div class="col-md-4">
            <label class="form-label">Unit</label>
            <select class="form-control form-control-sm" name="unit_id">
                <option value="">Pilih Unit</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}" @selected(old('unit_id', $item->unit_id) == $unit->id)>{{ $unit->unit_name }}</option>
                @endforeach
            </select>
        </div>
        @if ($supportsCategoryMaster)
            <div class="col-md-6">
                <label class="form-label">Kategori</label>
                <select class="form-control form-control-sm" name="category_id">
                    <option value="">Pilih Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $item->category_id) == $category->id)>{{ $category->category_name }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <div class="col-md-6"><label class="form-label">Kategori</label><input class="form-control form-control-sm" name="category" value="{{ old('category', $item->category ?? null) }}"></div>
        @endif
        <div class="col-md-6"><label class="form-label">Spesifikasi</label><textarea class="form-control form-control-sm" name="specification" rows="3">{{ old('specification', $item->specification) }}</textarea></div>
    </x-master-edit-layout>
</form>
@endsection
