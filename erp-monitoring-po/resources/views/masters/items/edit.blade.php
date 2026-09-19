@extends('layouts.erp')

@php($title = 'Edit Item')
@php($header = 'Edit Item')
@php($headerSubtitle = 'Perbarui identitas item.')

@section('content')
    <div class="page-shell">
        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Form Edit Item</h3>
                    <div class="ui-surface-subtitle">Kode item tidak dapat diubah.</div>
                </div>
            </div>

            <div class="ui-surface-body">
                <div class="form-wrapper">
                    <form method="POST" action="{{ route('items.update', $item->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label class="field-label">Kode Item</label>
                            <input class="form-control form-control-sm" name="item_code"
                                value="{{ old('item_code', $item->item_code) }}" required readonly>
                        </div>

                        <div class="form-group">
                            <label class="field-label">Nama Item</label>
                            <input class="form-control form-control-sm" name="item_name"
                                placeholder="Nama barang/material" value="{{ old('item_name', $item->item_name) }}" required>
                        </div>

                        <div class="form-group">
                            <label class="field-label">Kategori</label>
                            <select class="form-control form-control-sm" name="category_id">
                                <option value="">Pilih kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $item->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->category_code }} - {{ $category->category_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="field-label">Unit</label>
                            <select class="form-control form-control-sm" name="unit_id">
                                <option value="">Pilih unit</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}" {{ old('unit_id', $item->unit_id) == $unit->id ? 'selected' : '' }}>{{ $unit->unit_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="field-label">Spesifikasi</label>
                            <input class="form-control form-control-sm" name="specification"
                                placeholder="Ukuran, material, warna, printer match, dll"
                                value="{{ old('specification', $item->specification) }}">
                        </div>

                        <div class="form-actions">
                            <a href="{{ route('items.index') }}" class="btn btn-light btn-sm">Batal</a>
                            <button type="submit" class="btn btn-primary btn-sm px-5">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
    <style>
        .form-wrapper {
            max-width: 540px;
            margin: 24px auto;
            padding: 24px;
        }
        .form-wrapper .form-group {
            margin-bottom: 20px;
        }
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid var(--lemon-line, #dfe6b8);
        }
    </style>
@endsection
