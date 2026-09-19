@extends('layouts.erp')

@php
    $title = 'Buat PO';
    $header = 'Create Purchase Order (Manual)';
@endphp

@push('styles')
    <style>
        .select2-container {
            width: 100% !important;
        }

        .select2-container .select2-selection--single {
            height: calc(2.25rem + 2px) !important;
            min-height: calc(2.25rem + 2px) !important;
            border: 1px solid #ced4da !important;
            border-radius: .375rem !important;
            padding: .375rem .75rem !important;
            display: flex !important;
            align-items: center !important;
            background-color: #fff !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #495057 !important;
            line-height: 1.5 !important;
            padding-left: 0 !important;
            padding-right: 24px !important;
            width: 100%;
        }

        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #6c757d !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100% !important;
            right: 8px !important;
        }

        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #80bdff !important;
            box-shadow: 0 0 0 .2rem rgba(0, 123, 255, .12) !important;
        }

        .select2-dropdown {
            border: 1px solid #ced4da !important;
            border-radius: .375rem !important;
            overflow: hidden;
            z-index: 9999 !important;
        }

        .select2-search--dropdown {
            padding: 8px;
            background: #fff;
        }

        .select2-search__field {
            width: 100% !important;
            box-sizing: border-box !important;
            border: 1px solid #ced4da !important;
            border-radius: .375rem !important;
            padding: .375rem .75rem !important;
        }

        .ceisa-card .card-header {
            background: #f4f6f9;
            border-bottom: 1px solid #dee2e6;
            padding-top: .65rem;
            padding-bottom: .65rem;
        }

        .ceisa-card .card-title {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 0;
        }

        .section-caption {
            font-size: 12px;
            color: #6c757d;
            margin-top: 4px;
        }

        .table-ceisa {
            margin-bottom: 0;
        }

        .table-ceisa thead th {
            background: #eef2f7;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .2px;
            border-bottom-width: 1px;
            vertical-align: middle;
            white-space: nowrap;
        }

        .table-ceisa td,
        .table-ceisa th {
            vertical-align: middle;
        }

        .field-readonly {
            background-color: #f8f9fa !important;
        }

        .doc-label {
            font-size: 12px;
            font-weight: 600;
            color: #495057;
            margin-bottom: 6px;
        }

        .summary-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: .375rem;
            padding: 12px 14px;
        }

        .summary-title {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 2px;
        }

        .summary-value {
            font-size: 20px;
            font-weight: 700;
            color: #212529;
        }

        .row-no {
            width: 48px;
            text-align: center;
            font-weight: 600;
        }

        .btn-action {
            white-space: nowrap;
        }

        .sticky-action-bar {
            position: sticky;
            bottom: 0;
            z-index: 10;
            background: #fff;
            border-top: 1px solid #dee2e6;
            padding-top: 12px;
            margin-top: 12px;
        }

        .item-code-input {
            text-transform: uppercase;
        }

        .code-status {
            font-size: 11px;
            margin-top: 4px;
        }

        .code-status.text-success {
            color: #198754 !important;
        }

        .code-status.text-danger {
            color: #dc3545 !important;
        }

        .remarks-cell {
            position: relative;
        }

        .remarks-cell .form-control {
            display: none;
        }

        .remarks-cell .remarks-toggle {
            position: absolute;
            right: 4px;
            top: 4px;
            cursor: pointer;
            color: #6c757d;
            font-size: 12px;
        }

        .remarks-cell .remarks-toggle:hover {
            color: #495057;
        }

        .remarks-cell .item-remarks-input.d-none + .remarks-toggle,
        .remarks-cell textarea.d-none + .remarks-toggle {
            display: none !important;
        }

        .remarks-cell textarea:not(.d-none) + .remarks-toggle {
            display: none !important;
        }
    </style>
@endpush

@section('content')
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('po.store') }}" id="po-form">
        @csrf

        <div class="card card-primary card-outline ceisa-card mb-3">
            <div class="card-header">
                <h3 class="card-title">Data Dokumen</h3>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="doc-label">Nomor PO (Opsional, auto jika kosong)</label>
                        <input type="text" class="form-control form-control-sm" name="po_number"
                            value="{{ old('po_number') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="doc-label">Tanggal PO</label>
                        <input type="date" class="form-control form-control-sm" name="po_date"
                            value="{{ old('po_date') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="doc-label">Jenis Dokumen</label>
                        <input type="text" class="form-control form-control-sm field-readonly"
                            value="Purchase Order Manual" readonly>
                    </div>

                    <div class="col-md-12">
                        <label class="doc-label">Catatan</label>
                        <input type="text" class="form-control form-control-sm" name="notes"
                            value="{{ old('notes') }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-primary card-outline ceisa-card mb-3">
            <div class="card-header">
                <h3 class="card-title">Data Supplier</h3>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="doc-label">Supplier</label>
                        <select name="supplier_id" class="form-control form-control-sm supplier-select" required>
                            <option value="">-- Pilih Supplier --</option>
                            @foreach ($suppliers as $s)
                                <option value="{{ $s->id }}" {{ old('supplier_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->supplier_name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="section-caption">Ketik nama supplier untuk mencari data.</div>
                    </div>

                    <div class="col-md-4">
                        <label class="doc-label">Status Awal</label>
                        <input type="text" class="form-control form-control-sm field-readonly"
                            value="{{ \App\Support\DocumentTermStatus::poStatusLabel(\App\Support\DocumentTermCodes::PO_ISSUED) }}"
                            readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-primary card-outline ceisa-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="card-title">Data Barang</h3>
                    <div class="section-caption">Ketik kode barang, gunakan Enter untuk menambah baris, Ctrl+S untuk menyimpan.</div>
                </div>
                <button type="button" class="btn btn-sm btn-primary" id="btn-add-item">+ Tambah Barang</button>
            </div>

            <datalist id="item-codes">
                @foreach ($items as $item)
                    <option value="{{ $item->item_code }} | {{ $item->item_name }} — {{ $item->unit_name }}"></option>
                @endforeach
            </datalist>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-ceisa" id="po-items-table">
                        <thead>
                            <tr>
                                <th style="width: 5%">No</th>
                                <th style="width: 14%">Kode</th>
                                <th style="width: 24%">Uraian</th>
                                <th style="width: 10%">Satuan</th>
                                <th style="width: 10%">Qty</th>
                                <th style="width: 11%">Harga</th>
                                <th style="width: 11%">Subtotal</th>
                                <th style="width: 10%">Status</th>
                                <th style="width: 5%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="row mt-3 g-3">
                    <div class="col-md-3 ms-auto">
                        <div class="summary-box">
                            <div class="summary-title">Grand Total</div>
                            <div class="summary-value" id="grand-total-text">0,00</div>
                            <input type="hidden" name="grand_total" id="grand-total-input" value="0">
                        </div>
                    </div>
                </div>

                <div class="sticky-action-bar d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-success btn-sm" id="btn-submit">Simpan PO</button>
                    <button type="submit" name="save_and_new" value="1" class="btn btn-success btn-sm" id="btn-save-and-new">Simpan & Baru</button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    @vite('resources/js/po-create.js')
    <script>
        window.PO_CREATE_CONFIG = {
            items: @json($items),
            oldItems: @json(old('items', [])),
            searchUrl: '{{ route('po.items.search') }}',
        };
    </script>
@endpush
