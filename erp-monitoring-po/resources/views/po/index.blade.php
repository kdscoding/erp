@extends('layouts.erp')

@php
    $title = 'Purchase Orders';
    $header = 'Purchase Orders';
    $headerSubtitle = 'Kontrol dokumen purchase order dari satu tampilan.';
    $poSummary = $summaryChips ?? [];
@endphp

@section('content')
    <div class="page-shell po-page">
        <section class="ui-surface po-control-panel">
            <div class="po-panel-head">
                <div>
                    <div class="po-eyebrow">PROCUREMENT / PURCHASE ORDER</div>
                    <h2 class="po-panel-title">Kontrol Purchase Order</h2>
                    <p class="po-panel-subtitle">Pantau status, percepat pencarian, dan kelola dokumen PO tanpa berpindah halaman.</p>
                </div>
                <div class="po-panel-actions">
                    <a href="{{ route('po.export-excel', request()->query()) }}" class="btn btn-light btn-sm">
                        <i class="fas fa-file-excel"></i> Export Monitoring
                    </a>
                    <a href="{{ route('po.import-template') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-file-download"></i> Template Import
                    </a>
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#importPoModal">
                        <i class="fas fa-file-import"></i> Import Excel
                    </button>
                    <a href="{{ route('po.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Buat PO
                    </a>
                </div>
            </div>

            <div class="po-summary-grid">
                @foreach ($poSummary as $chip)
                    @php
                        $filterQuery = request()->query();
                        if ($chip['value'] !== '') {
                            $filterQuery['status'] = $chip['value'];
                        } else {
                            unset($filterQuery['status']);
                        }

                        $summaryTone = match ($chip['value']) {
                            'Late', 'Delayed' => 'danger',
                            'Partial' => 'primary',
                            'Closed' => 'success',
                            'Cancelled' => 'secondary',
                            default => 'warning',
                        };
                    @endphp
                    <a href="{{ route('po.index', $filterQuery) }}" class="po-summary-card po-summary-{{ $summaryTone }} @if (request('status') === (string) $chip['value']) is-active @endif">
                        <span class="po-summary-icon">
                            <i class="fas {{ $chip['value'] === '' ? 'fas fa-boxes' : 'fas fa-filter' }}"></i>
                        </span>
                        <span class="po-summary-copy">
                            <small>{{ $chip['label'] }}</small>
                            <strong>{{ (int) ($chip['count'] ?? 0) }}</strong>
                        </span>
                    </a>
                @endforeach
            </div>

            <div class="po-filter-panel">
                <button type="button" class="filter-toggle-btn" id="filterToggle">
                    <i class="fas fa-sliders-h"></i> <span id="filterToggleText">Tampilkan Filter</span>
                </button>
                <div id="filterSection" style="display:none;">
                    <form method="GET" class="filter-bar" id="poFilterForm">
                        <div class="filter-bar-field">
                            <label for="filterPoNumber">Nomor PO</label>
                            <input type="text" id="filterPoNumber" name="po_number" value="{{ $filterPoNumber ?? '' }}" class="form-control form-control-sm" placeholder="Cari nomor PO">
                        </div>
                        <div class="filter-bar-field">
                            <label for="filterSupplier">Supplier</label>
                            <select id="filterSupplier" name="supplier_code" class="form-control form-control-sm supplier-select">
                                <option value="">Semua supplier</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->supplier_code }}" {{ ($filterSupplierCode ?? '') === $supplier->supplier_code ? 'selected' : '' }}>
                                        {{ $supplier->supplier_code }} - {{ $supplier->supplier_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-bar-field">
                            <label for="filterDateFrom">Dari Tanggal</label>
                            <input type="date" id="filterDateFrom" name="date_from" value="{{ $filterDateFrom ?? '' }}" class="form-control form-control-sm">
                        </div>
                        <div class="filter-bar-field">
                            <label for="filterDateTo">Sampai Tanggal</label>
                            <input type="date" id="filterDateTo" name="date_to" value="{{ $filterDateTo ?? '' }}" class="form-control form-control-sm">
                        </div>
                        <div class="filter-bar-field">
                            <label for="filterStatus">Status</label>
                            <select id="filterStatus" name="status" class="form-control form-control-sm">
                                <option value="">Semua status</option>
                                @foreach (\App\Support\TermCatalog::options('po_status', \App\Support\DomainStatus::legacyOptions(\App\Support\DomainStatus::GROUP_PO_STATUS)) as $value => $label)
                                    <option value="{{ $value }}" {{ ($filterStatus ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-bar-actions">
                            <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-search"></i> Terapkan</button>
                            <a href="{{ route('po.index') }}" class="btn btn-light btn-sm"><i class="fas fa-redo"></i> Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section class="ui-surface po-table-panel">
            <div class="po-table-head">
                <div>
                    <h3 class="po-section-title">Daftar PO</h3>
                    <p class="po-section-subtitle">{{ $rows->total() }} dokumen ditemukan pada halaman ini.</p>
                </div>
                <div class="po-search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" id="po-table-search" class="form-control form-control-sm" placeholder="Cari PO, supplier..." aria-label="Cari PO">
                </div>
            </div>

            <div class="table-wrap table-responsive">
                <table class="table table-hover po-table" id="po-table">
                    <thead>
                        <tr>
                            <th>PO</th>
                            <th>Supplier</th>
                            <th>Tanggal</th>
                            <th>ETA</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $r)
                            @php($rowClass = match(true) {
                                in_array($r->status, [\App\Support\DocumentTermCodes::PO_LATE, 'Delayed']) => 'table-danger',
                                in_array($r->status, [\App\Support\DocumentTermCodes::PO_ISSUED, 'Open']) => 'table-warning',
                                default => '',
                            })
                            <tr class="{{ $rowClass }}">
                                <td>
                                    <a href="{{ route('po.show', $r->po_number) }}" class="po-doc-link">{{ $r->po_number }}</a>
                                    <div class="po-doc-meta">{{ \Carbon\Carbon::parse($r->po_date)->format('d-m-Y') }}</div>
                                </td>
                                <td>
                                    <div class="po-doc-link">{{ $r->supplier_code }}</div>
                                    <div class="po-doc-meta">{{ $r->supplier_name }}</div>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($r->po_date)->format('d-m-Y') }}</td>
                                <td>
                                    @if ($r->eta_date)
                                        <strong>{{ \Carbon\Carbon::parse($r->eta_date)->format('d-m-Y') }}</strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td><x-status-badge :status="$r->status" scope="po" /></td>
                                <td class="text-right">
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
                                            Aksi
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item" href="{{ route('po.show', $r->po_number) }}">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                            <a class="dropdown-item" href="{{ route('po.export-detail-excel', $r->po_number) }}">
                                                <i class="fas fa-file-excel"></i> Export Excel
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="po-empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <strong>Belum ada PO yang sesuai.</strong>
                                        <span>Ubah filter atau buat purchase order baru.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="po-pagination">{{ $rows->links() }}</div>
        </section>
    </div>

    <div class="modal fade" id="importPoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('po.import') }}" enctype="multipart/form-data" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Purchase Order</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="field-label" for="poImportFile">File Excel/CSV</label>
                        <input type="file" id="poImportFile" name="file" class="form-control form-control-sm" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <div class="alert alert-info mb-0" style="font-size: 12px;">
                        Gunakan template yang tersedia. Format mendukung <strong>.xlsx</strong>, <strong>.xls</strong>, dan <strong>.csv</strong>.
                        Setiap baris barang wajib memiliki kode barang, qty, dan harga yang valid.
                        <a href="{{ route('po.import-template') }}">Unduh template</a>.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Import PO</button>
                </div>
            </form>
        </div>
    </div>
 @endsection

@push('scripts')
    @vite('resources/js/po-index.js')
    <script>
        window.PO_INDEX_CONFIG = {};
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggle = document.getElementById('filterToggle');
            const filterSection = document.getElementById('filterSection');
            const toggleText = document.getElementById('filterToggleText');
            if (toggle && filterSection) {
                toggle.addEventListener('click', function () {
                    const isHidden = filterSection.style.display === 'none';
                    filterSection.style.display = isHidden ? 'block' : 'none';
                    if (toggleText) {
                        toggleText.textContent = isHidden ? 'Sembunyikan Filter' : 'Tampilkan Filter';
                    }
                });
            }
        });
    </script>
@endpush

@push('styles')
    <style>
        .po-page {
            --po-primary: #2563eb;
            --po-primary-soft: #eff6ff;
            --po-border: #e2e8f0;
            --po-muted: #64748b;
            --po-surface: #ffffff;
        }

        .po-control-panel,
        .po-table-panel {
            border: 1px solid var(--po-border);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        }

        .po-control-panel {
            padding: 18px;
            margin-bottom: 16px;
        }

        .po-panel-head,
        .po-table-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            flex-wrap: wrap;
        }

        .po-eyebrow,
        .po-section-subtitle,
        .po-panel-subtitle {
            color: var(--po-muted);
        }

        .po-eyebrow {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            margin-bottom: 4px;
        }

        .po-panel-title,
        .po-section-title {
            margin-bottom: 4px;
            color: #0f172a;
            font-weight: 700;
        }

        .po-panel-subtitle,
        .po-section-subtitle {
            font-size: 13px;
            margin-bottom: 0;
        }

        .po-panel-actions {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            flex-wrap: wrap;
        }

        .po-summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(128px, 1fr));
            gap: 10px;
            margin: 18px 0;
        }

        .po-summary-card {
            min-height: 74px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            border: 1px solid var(--po-border);
            border-radius: 10px;
            background: #f8fafc;
            color: #334155;
            text-decoration: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, transform 0.15s ease;
        }

        .po-summary-card:hover,
        .po-summary-card.is-active {
            border-color: #93c5fd;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.08);
            transform: translateY(-1px);
        }

        .po-summary-icon {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            flex: 0 0 auto;
        }

        .po-summary-warning .po-summary-icon { background: #fef3c7; color: #b45309; }
        .po-summary-danger .po-summary-icon { background: #fee2e2; color: #b91c1c; }
        .po-summary-primary .po-summary-icon { background: #dbeafe; color: #1d4ed8; }
        .po-summary-success .po-summary-icon { background: #dcfce7; color: #15803d; }
        .po-summary-secondary .po-summary-icon { background: #e2e8f0; color: #475569; }

        .po-summary-copy small {
            display: block;
            font-size: 11px;
            line-height: 1.25;
            color: var(--po-muted);
        }

        .po-summary-copy strong {
            display: block;
            margin-top: 2px;
            font-size: 20px;
            line-height: 1.1;
            color: #0f172a;
        }

        .po-filter-panel {
            border-top: 1px solid var(--po-border);
            padding-top: 14px;
        }

        .filter-toggle-btn { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; border: 1px solid var(--lemon-line); background: #f8f9fa; color: #666; cursor: pointer; margin-bottom: .5rem; }
        .filter-toggle-btn:hover { background: #e9ecef; color: #333; border-color: #dee2e6; }
        .filter-toggle-btn i { font-size: 12px; }
        .filter-bar { display: flex; flex-direction: row; align-items: flex-end; gap: .75rem; flex-wrap: wrap; padding: .5rem 0; }
        .filter-bar-field { display: flex; flex-direction: column; min-width: 140px; flex: 1; }
        .filter-bar-field label { font-size: 11px; font-weight: 600; color: #666; margin-bottom: 3px; }
        .filter-bar-field select, .filter-bar-field input { font-size: 12px; padding: 4px 8px; height: 32px; }
        .filter-bar-actions { display: flex; gap: .4rem; align-items: flex-end; margin-left: auto; }
        .filter-bar-actions .btn { height: 32px; font-size: 12px; }

        .po-table-panel {
            padding: 18px;
        }

        .po-table-head {
            align-items: center;
            margin-bottom: 14px;
        }

        .po-section-title {
            font-size: 16px;
        }

        .po-search-wrap {
            position: relative;
            width: min(100%, 260px);
        }

        .po-search-wrap > i {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
        }

        .po-search-wrap .form-control {
            padding-left: 32px;
        }

        .po-table {
            margin-bottom: 0;
            font-size: 13px;
        }

        .po-table thead th {
            padding: 10px 12px;
            border-top: 0;
            border-bottom-width: 1px;
            background: #f8fafc;
            color: #475569;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .po-table tbody td {
            padding: 12px;
            vertical-align: middle;
        }

        .po-doc-link {
            color: #1d4ed8;
            font-weight: 700;
            text-decoration: none;
        }

        .po-doc-link:hover {
            color: #1e40af;
            text-decoration: underline;
        }

        .po-doc-meta {
            margin-top: 3px;
            color: var(--po-muted);
            font-size: 11px;
        }

        .po-empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            padding: 32px 10px;
            color: var(--po-muted);
        }

        .po-empty-state i {
            font-size: 28px;
            color: #94a3b8;
        }

        .po-empty-state strong {
            color: #334155;
        }

        .po-empty-state span {
            font-size: 12px;
        }

        .po-pagination {
            margin-top: 14px;
        }

        @media (max-width: 1199.98px) {
            .filter-bar {
                flex-wrap: wrap;
            }
            .filter-bar-field {
                flex: 1 1 calc(33.333% - .5rem);
                min-width: 140px;
            }
        }

        @media (max-width: 767.98px) {
            .po-control-panel,
            .po-table-panel {
                padding: 14px;
            }

            .po-panel-head,
            .po-table-head {
                align-items: stretch;
                flex-direction: column;
            }

            .po-panel-actions {
                width: 100%;
            }

            .po-panel-actions .btn {
                flex: 1;
            }

            .filter-bar-actions {
                width: 100%;
            }

            .filter-bar-actions .btn {
                flex: 1;
            }

            .po-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .filter-bar-field {
                flex: 1 1 calc(50% - .375rem);
            }

            .po-search-wrap {
                width: 100%;
            }
        }

        @media (max-width: 575.98px) {
            .po-summary-grid {
                grid-template-columns: 1fr;
            }

            .filter-bar,
            .filter-bar-field,
            .filter-bar-actions {
                flex-direction: column;
                width: 100%;
            }

            .filter-bar-field {
                flex: 1 1 auto;
            }

            .po-summary-card {
                min-height: 66px;
            }
        }
    </style>
@endpush
