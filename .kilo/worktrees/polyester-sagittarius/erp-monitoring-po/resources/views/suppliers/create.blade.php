@extends('layouts.erp')

@php($title = 'Tambah Supplier')
@php($header = 'Tambah Supplier')
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
                <form method="POST" action="{{ route('suppliers.store') }}" class="filter-grid">
                    @csrf
                    <div class="span-4">
                        <label class="field-label">Kode Supplier</label>
                        <input class="form-control form-control-sm" name="supplier_code"
                            placeholder="Kode unik" value="{{ old('supplier_code') }}" required>
                    </div>

                    <div class="span-6">
                        <label class="field-label">Nama Supplier</label>
                        <input class="form-control form-control-sm" name="supplier_name"
                            placeholder="Nama perusahaan" value="{{ old('supplier_name') }}" required>
                    </div>

                    <div class="span-2">
                        <label class="field-label">Status</label>
                        <select class="form-control form-control-sm" name="status">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>

                    <div class="span-12 d-flex justify-content-end gap-2">
                        <a href="{{ route('suppliers.index') }}" class="btn btn-light btn-sm">Batal</a>
                        <button type="submit" class="btn btn-primary btn-sm px-4">Simpan Supplier</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection