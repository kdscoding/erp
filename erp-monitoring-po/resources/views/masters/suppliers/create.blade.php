@extends('layouts.erp')

@php($title = ' TAMBAH SUPPLIER')
@php($header = ' TAMBAH SUPPLIER')
@php($headerSubtitle = 'Input data supplier baru.')

@section('content')
    <div class="page-shell">
        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Form Supplier</h3>
                </div>
            </div>

            <div class="ui-surface-body">
                <div class="form-wrapper">
                    <form method="POST" action="{{ route('suppliers.store') }}">
                        @csrf
                        <div class="form-group">
                            <label class="field-label">Kode Supplier</label>
                            <input class="form-control form-control-sm" name="supplier_code"
                                placeholder="Kode unik" value="{{ old('supplier_code') }}" required>
                        </div>

                        <div class="form-group">
                            <label class="field-label">Nama Supplier</label>
                            <input class="form-control form-control-sm" name="supplier_name"
                                placeholder="Nama perusahaan supplier" value="{{ old('supplier_name') }}" required>
                        </div>

                        <div class="form-group">
                            <label class="field-label">Status</label>
                            <select class="form-control form-control-sm" name="status">
                                <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('status', 1) == 0 ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>

                        <div class="form-actions">
                            <a href="{{ route('suppliers.index') }}" class="btn btn-light btn-sm">Batal</a>
                            <button type="submit" class="btn btn-primary btn-sm px-5">Simpan Supplier</button>
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