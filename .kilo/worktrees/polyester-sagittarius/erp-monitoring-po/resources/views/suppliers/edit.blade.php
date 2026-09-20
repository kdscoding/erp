@extends('layouts.erp')

@php($title = 'Edit Supplier')
@php($header = 'Edit Supplier')
@php($headerSubtitle = 'Perbarui identitas supplier.')

@section('content')
    <div class="page-shell">
        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Form Edit Supplier</h3>
                    <div class="ui-surface-subtitle">Kode supplier tidak dapat diubah.</div>
                </div>
            </div>

            <div class="ui-surface-body">
                <form method="POST" action="{{ route('suppliers.update', $supplier->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="span-4">
                        <label class="field-label">Kode Supplier</label>
                        <input class="form-control form-control-sm" name="supplier_code"
                            value="{{ old('supplier_code', $supplier->supplier_code) }}" required readonly>
                    </div>

                    <div class="span-6">
                        <label class="field-label">Nama Supplier</label>
                        <input class="form-control form-control-sm" name="supplier_name"
                            placeholder="Nama perusahaan" value="{{ old('supplier_name', $supplier->supplier_name) }}" required>
                    </div>

                    <div class="span-2">
                        <label class="field-label">Status</label>
                        <select class="form-control form-control-sm" name="status">
                            <option value="1" {{ ($supplier->status ?? true) ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ !($supplier->status ?? true) ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>

                    <div class="span-12 d-flex justify-content-end gap-2">
                        <a href="{{ route('suppliers.index') }}" class="btn btn-light btn-sm">Batal</a>
                        <button type="submit" class="btn btn-primary btn-sm px-4">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection