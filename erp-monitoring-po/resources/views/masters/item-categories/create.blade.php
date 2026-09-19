@extends('layouts.erp')

@php($title = 'Tambah Kategori Item')
@php($header = 'Tambah Kategori Item')
@php($headerSubtitle = 'Input data kategori item baru.')

@section('content')
    <div class="page-shell">
        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Form Kategori</h3>
                </div>
            </div>

            <div class="ui-surface-body">
                <div class="form-wrapper">
                    <form method="POST" action="{{ route('item-categories.store') }}">
                        @csrf
                        <div class="form-group">
                            <label class="field-label">Kode Kategori</label>
                            <input class="form-control form-control-sm" name="category_code"
                                placeholder="Kode unik" value="{{ old('category_code') }}" required>
                        </div>

                        <div class="form-group">
                            <label class="field-label">Nama Kategori</label>
                            <input class="form-control form-control-sm" name="category_name"
                                placeholder="Nama kategori" value="{{ old('category_name') }}" required>
                        </div>

                        <div class="form-group">
                            <label class="field-label">Deskripsi</label>
                            <textarea class="form-control form-control-sm" name="description"
                                placeholder="Deskripsi singkat" rows="3">{{ old('description') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label class="field-label">Status</label>
                            <select class="form-control form-control-sm" name="is_active">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>

                        <div class="form-actions">
                            <a href="{{ route('item-categories.index') }}" class="btn btn-light btn-sm">Batal</a>
                            <button type="submit" class="btn btn-primary btn-sm px-5">Simpan Kategori</button>
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