@extends('layouts.erp')

@php($title = 'Edit Supplier')
@php($header = 'Edit Supplier')
@php($headerSubtitle = 'Perbarui data supplier.')

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
                <div class="form-wrapper">
                    <form method="POST" action="{{ route('suppliers.update', $supplier->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label class="field-label">Kode Supplier</label>
                            <input class="form-control form-control-sm" name="supplier_code"
                                value="{{ old('supplier_code', $supplier->supplier_code) }}" required readonly>
                        </div>

                        <div class="form-group">
                            <label class="field-label">Nama Supplier</label>
                            <input class="form-control form-control-sm" name="supplier_name"
                                placeholder="Nama perusahaan supplier" value="{{ old('supplier_name', $supplier->supplier_name) }}" required>
                        </div>

                        <div class="form-group">
                            <label class="field-label">Alamat</label>
                            <input class="form-control form-control-sm" name="address"
                                placeholder="Alamat lengkap supplier"
                                value="{{ old('address', $supplier->address) }}">
                        </div>

                        <div class="form-group">
                            <label class="field-label">Telepon</label>
                            <input class="form-control form-control-sm" name="phone"
                                placeholder="No. telepon supplier"
                                value="{{ old('phone', $supplier->phone) }}">
                        </div>

                        <div class="form-group">
                            <label class="field-label">Email</label>
                            <input class="form-control form-control-sm" name="email"
                                type="email"
                                placeholder="Email supplier"
                                value="{{ old('email', $supplier->email) }}">
                        </div>

                        <div class="form-group">
                            <label class="field-label">Kontak</label>
                            <input class="form-control form-control-sm" name="contact_person"
                                placeholder="Nama PIC supplier"
                                value="{{ old('contact_person', $supplier->contact_person) }}">
                        </div>

                        <div class="form-actions">
                            <a href="{{ route('suppliers.index') }}" class="btn btn-light btn-sm">Batal</a>
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
