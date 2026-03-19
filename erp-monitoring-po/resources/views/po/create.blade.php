@extends('layouts.erp')

@php($title = 'Buat PO')
@php($header = 'Create Purchase Order (Manual)')

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

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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
    </style>

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
                        <input type="text" class="form-control" name="po_number" value="{{ old('po_number') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="doc-label">Tanggal PO</label>
                        <input type="date" class="form-control" name="po_date" value="{{ old('po_date') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="doc-label">Nomor Referensi</label>
                        <input type="text" class="form-control" name="reference_number"
                            value="{{ old('reference_number') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="doc-label">Jenis Dokumen</label>
                        <input type="text" class="form-control field-readonly" value="Purchase Order Manual" readonly>
                    </div>

                    <div class="col-md-12">
                        <label class="doc-label">Catatan</label>
                        <input type="text" class="form-control" name="notes" value="{{ old('notes') }}">
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
                        <select name="supplier_id" class="form-select supplier-select" required>
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
                        <label class="doc-label">Status</label>
                        <input type="text" class="form-control field-readonly" value="PO Issued (Direct Entry)" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-primary card-outline ceisa-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="card-title">Data Barang</h3>
                    <div class="section-caption">Isi kode barang manual, data barang akan terisi otomatis.</div>
                </div>
                <button type="button" class="btn btn-sm btn-primary" id="btn-add-item">+ Tambah Barang</button>
            </div>

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
                    <button type="submit" class="btn btn-success">Simpan PO</button>
                </div>
            </div>
        </div>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        (function() {
            const items = @json($items);
            const oldItems = @json(old('items', []));
            const tbody = document.querySelector('#po-items-table tbody');
            const addBtn = document.getElementById('btn-add-item');
            const grandTotalText = document.getElementById('grand-total-text');
            const grandTotalInput = document.getElementById('grand-total-input');

            const itemMap = {};
            items.forEach(item => {
                itemMap[String(item.item_code || '').trim().toUpperCase()] = item;
            });

            function escapeHtml(text) {
                return String(text ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function parseNumber(value) {
                if (value === null || value === undefined || value === '') return 0;
                return parseFloat(String(value).replace(/,/g, '')) || 0;
            }

            function formatNumber(value) {
                return new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(parseNumber(value));
            }

            function initSupplierSelect() {
                $('.supplier-select').select2({
                    width: '100%',
                    placeholder: '-- Pilih Supplier --',
                    allowClear: true,
                    dropdownParent: $(document.body)
                });
            }

            function rowTemplate(idx, rowData = {}) {
                const code = rowData.item_code ?? '';
                const itemId = rowData.item_id ?? '';
                const itemName = rowData.item_name ?? '';
                const unitName = rowData.unit_name ?? '';
                const qty = rowData.ordered_qty ?? 1;
                const price = rowData.price ?? '';
                const subtotal = parseNumber(qty) * parseNumber(price);

                return `
                    <tr>
                        <td class="row-no row-number">${idx + 1}</td>
                        <td>
                            <input type="hidden" class="item-id-input" name="items[${idx}][item_id]" value="${escapeHtml(itemId)}">
                            <input type="text" class="form-control item-code-input" name="items[${idx}][item_code]" value="${escapeHtml(code)}" autocomplete="off" required>
                        </td>
                        <td>
                            <input type="text" class="form-control item-name-display field-readonly" value="${escapeHtml(itemName)}" readonly>
                        </td>
                        <td>
                            <input type="text" class="form-control item-unit field-readonly" value="${escapeHtml(unitName)}" readonly>
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0.01" class="form-control qty-input"
                                name="items[${idx}][ordered_qty]" value="${escapeHtml(qty)}" required>
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" class="form-control price-input"
                                name="items[${idx}][price]" value="${escapeHtml(price)}">
                        </td>
                        <td>
                            <input type="text" class="form-control subtotal-display field-readonly"
                                value="${formatNumber(subtotal)}" readonly>
                        </td>
                        <td>
                            <div class="code-status"></div>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove btn-action">x</button>
                        </td>
                    </tr>
                `;
            }

            function reindex() {
                [...tbody.querySelectorAll('tr')].forEach((tr, idx) => {
                    tr.querySelector('.row-number').textContent = idx + 1;
                    tr.querySelector('.item-id-input').setAttribute('name', `items[${idx}][item_id]`);
                    tr.querySelector('.item-code-input').setAttribute('name', `items[${idx}][item_code]`);
                    tr.querySelector('.qty-input').setAttribute('name', `items[${idx}][ordered_qty]`);
                    tr.querySelector('.price-input').setAttribute('name', `items[${idx}][price]`);
                });
            }

            function getEnteredCodes(excludeInput = null) {
                return [...tbody.querySelectorAll('.item-code-input')]
                    .filter(input => input !== excludeInput)
                    .map(input => String(input.value || '').trim().toUpperCase())
                    .filter(Boolean);
            }

            function isDuplicateCode(code, currentInput) {
                if (!code) return false;
                return getEnteredCodes(currentInput).includes(code);
            }

            function clearItemInfo(tr, statusText = '', statusClass = '') {
                tr.querySelector('.item-id-input').value = '';
                tr.querySelector('.item-name-display').value = '';
                tr.querySelector('.item-unit').value = '';

                const statusEl = tr.querySelector('.code-status');
                statusEl.className = 'code-status';
                statusEl.textContent = statusText;

                if (statusClass) {
                    statusEl.classList.add(statusClass);
                }
            }

            function fillItemInfo(tr, item) {
                tr.querySelector('.item-id-input').value = item.id || '';
                tr.querySelector('.item-name-display').value = item.item_name || '';
                tr.querySelector('.item-unit').value = item.unit_name || '';

                const statusEl = tr.querySelector('.code-status');
                statusEl.className = 'code-status text-success';
                statusEl.textContent = 'Kode valid';
            }

            function updateRowSubtotal(tr) {
                const qty = parseNumber(tr.querySelector('.qty-input').value);
                const price = parseNumber(tr.querySelector('.price-input').value);
                const subtotal = qty * price;

                tr.querySelector('.subtotal-display').value = formatNumber(subtotal);
                updateGrandTotal();
            }

            function updateGrandTotal() {
                let total = 0;

                tbody.querySelectorAll('tr').forEach(tr => {
                    const qty = parseNumber(tr.querySelector('.qty-input').value);
                    const price = parseNumber(tr.querySelector('.price-input').value);
                    total += qty * price;
                });

                grandTotalText.textContent = formatNumber(total);
                grandTotalInput.value = total;
            }

            function resolveItemByCode(tr) {
                const codeInput = tr.querySelector('.item-code-input');
                const code = String(codeInput.value || '').trim().toUpperCase();
                codeInput.value = code;

                if (!code) {
                    clearItemInfo(tr, '');
                    updateRowSubtotal(tr);
                    return;
                }

                if (isDuplicateCode(code, codeInput)) {
                    clearItemInfo(tr, 'Kode duplikat', 'text-danger');
                    updateRowSubtotal(tr);
                    return;
                }

                const item = itemMap[code];

                if (!item) {
                    clearItemInfo(tr, 'Kode tidak ditemukan', 'text-danger');
                    updateRowSubtotal(tr);
                    return;
                }

                fillItemInfo(tr, item);
                updateRowSubtotal(tr);
            }

            function bindRow(tr) {
                const codeInput = tr.querySelector('.item-code-input');
                const qtyInput = tr.querySelector('.qty-input');
                const priceInput = tr.querySelector('.price-input');

                codeInput.addEventListener('input', function() {
                    this.value = this.value.toUpperCase();
                });

                codeInput.addEventListener('change', function() {
                    resolveItemByCode(tr);
                });

                codeInput.addEventListener('blur', function() {
                    resolveItemByCode(tr);
                });

                qtyInput.addEventListener('input', function() {
                    updateRowSubtotal(tr);
                });

                priceInput.addEventListener('input', function() {
                    updateRowSubtotal(tr);
                });

                resolveItemByCode(tr);
                updateRowSubtotal(tr);
            }

            function addRow(rowData = {}) {
                const idx = tbody.querySelectorAll('tr').length;
                tbody.insertAdjacentHTML('beforeend', rowTemplate(idx, rowData));
                const newRow = tbody.querySelector('tr:last-child');
                bindRow(newRow);
                reindex();
                updateGrandTotal();
            }

            addBtn.addEventListener('click', function() {
                addRow({
                    item_code: '',
                    item_id: '',
                    item_name: '',
                    unit_name: '',
                    ordered_qty: 1,
                    price: ''
                });
            });

            tbody.addEventListener('click', function(e) {
                if (e.target.classList.contains('btn-remove')) {
                    const rows = tbody.querySelectorAll('tr');
                    if (rows.length === 1) return;

                    e.target.closest('tr').remove();
                    reindex();
                    updateGrandTotal();
                }
            });

            initSupplierSelect();

            if (oldItems.length > 0) {
                oldItems.forEach(item => addRow(item));
            } else {
                addRow();
            }
        })();
    </script>
@endsection
