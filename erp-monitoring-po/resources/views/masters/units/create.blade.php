@extends('layouts.erp')

@php($title = 'Tambah Unit')
@php($header = 'Tambah Unit')
@php($headerSubtitle = 'Input data unit baru.')

@section('content')
    <div class="page-shell">
        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Form Unit</h3>
                </div>
            </div>

            <div class="ui-surface-body">
                <div class="form-wrapper">
                    <form method="POST" action="{{ route('units.store') }}">
                        @csrf
                        <div class="form-group">
                            <label class="field-label">Kode Unit</label>
                            <input class="form-control form-control-sm" name="unit_code"
                                placeholder="Mis. PCS" value="{{ old('unit_code') }}" required>
                        </div>

                        <div class="form-group">
                            <label class="field-label">Nama Unit</label>
                            <input class="form-control form-control-sm" name="unit_name"
                                placeholder="Nama lengkap unit" value="{{ old('unit_name') }}" required>
                        </div>

                        <div class="form-actions">
                            <a href="{{ route('units.index') }}" class="btn btn-light btn-sm">Batal</a>
                            <button type="submit" class="btn btn-primary btn-sm px-5">Simpan Unit</button>
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