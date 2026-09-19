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
                <button class="po-filter-toggle" type="button" data-toggle="collapse" data-target="#poFilterBody" aria-expanded="true" aria-controls="poFilterBody">
                    <span><i class="fas fa-sliders-h"></i> Filter cepat</span>
                    <i class="fas fa-chevron-up po-filter-chevron"></i>
                </button>
                <div id="poFilterBody" class="collapse show">
                    <form method="GET" class="po-filter-grid">
                        <div class="po-field">
                            <label class="field-label" for="poNumberFilter">Nomor PO</label>
                            <input type="text" id="poNumberFilter" name="po_number" value="{{ request('po_number') }}" class="form-control form-control-sm" placeholder="Cari nomor PO">
                        </div>

                        <div class="po-field">
                            <label class="field-label" for="supplierFilter">Supplier</label>
                            <select id="supplierFilter" name="supplier_code" class="form-control form-control-sm supplier-select">
                                <option value="">Semua supplier</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->supplier_code }}" @selected(request('supplier_code') === $supplier->supplier_code)>
                                        {{ $supplier->supplier_code }} - {{ $supplier->supplier_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="po-field">
                            <label class="field-label" for="dateFromFilter">Dari Tanggal</label>
                            <input type="date" id="dateFromFilter" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm">
                        </div>

                        <div class="po-field">
                            <label class="field-label" for="dateToFilter">Sampai Tanggal</label>
                            <input type="date" id="dateToFilter" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm">
                        </div>

                        <div class="po-field">
                            <label class="field-label" for="statusFilter">Status</label>
                            <select id="statusFilter" name="status" class="form-control form-control-sm">
                                <option value="">Semua status</option>
                                @foreach (\App\Support\TermCatalog::options('po_status', \App\Support\DomainStatus::legacyOptions(\App\Support\DomainStatus::GROUP_PO_STATUS)) as $value => $label)
                                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="po-filter-actions">
                            <button class="btn btn-primary btn-sm w-100" type="submit"><i class="fas fa-search"></i> Terapkan</button>
                            <a href="{{ route('po.index') }}" class="btn btn-light btn-sm w-100"><i class="fas fa-redo"></i> Reset</a>
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
 @endsection

@push('scripts')
    @vite('resources/js/po-index.js')
    <script>
        window.PO_INDEX_CONFIG = {};
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

        .po-panel-actions,
        .po-filter-actions {
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

        .po-filter-toggle {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border: 0;
            background: transparent;
            color: #334155;
            font-weight: 700;
            cursor: pointer;
        }

        .po-filter-toggle span {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .po-filter-chevron {
            font-size: 11px;
            transition: transform 0.15s ease;
        }

        .po-filter-toggle[aria-expanded="false"] .po-filter-chevron {
            transform: rotate(-90deg);
        }

        .po-filter-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr)) minmax(150px, auto);
            gap: 10px;
            padding-top: 4px;
        }

        .po-field label {
            display: block;
            margin-bottom: 5px;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
        }

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
            .po-filter-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
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

            .po-panel-actions,
            .po-filter-actions {
                width: 100%;
            }

            .po-panel-actions .btn,
            .po-filter-actions .btn {
                flex: 1;
            }

            .po-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .po-filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .po-search-wrap {
                width: 100%;
            }
        }

        @media (max-width: 575.98px) {
            .po-summary-grid,
            .po-filter-grid {
                grid-template-columns: 1fr;
            }

            .po-summary-card {
                min-height: 66px;
            }
        }
    </style>
@endpush
