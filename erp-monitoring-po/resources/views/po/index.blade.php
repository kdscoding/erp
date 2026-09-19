@extends('layouts.erp')

@php($title = 'Purchase Orders')
@php($header = 'Purchase Orders')
@php($headerSubtitle = 'Kelola dan pantau dokumen purchase order yang masih aktif maupun yang sudah selesai.')

@section('content')
    @php($statusCounts = $summaryChips ?? [])

    <div class="page-shell">
        <section class="summary-chips">
            @php($canonicalOrder = ['Full', 'Partial', 'Delayed', 'Open', 'Late', 'Closed', 'Cancelled'])
            @foreach ($canonicalOrder as $label)
                <div class="summary-chip">
                    <div class="summary-chip-label">{{ $label }}</div>
                    <div class="summary-chip-value">{{ $statusCounts[$label] ?? 0 }}</div>
                </div>
            @endforeach
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Filter Purchase Orders</h3>
                    <div class="ui-surface-subtitle">Cari purchase orders berdasarkan supplier, status, dan periode.</div>
                </div>
            </div>

            <form method="GET" class="filter-grid">
                <div class="span-3">
                    <label class="field-label">Nomor PO</label>
                    <input type="text" name="po_number" value="{{ request('po_number') }}" class="form-control form-control-sm" placeholder="Contoh: PO-2026-0001">
                </div>

                <div class="span-4">
                    <label class="field-label">Supplier</label>
                    <select name="supplier_code" class="form-control form-control-sm supplier-select">
                        <option value="">Semua Supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->supplier_code }}" @selected(request('supplier_code') === $supplier->supplier_code)>
                                {{ $supplier->supplier_code }} - {{ $supplier->supplier_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="span-2">
                    <label class="field-label">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm">
                </div>

                <div class="span-2">
                    <label class="field-label">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm">
                </div>

                <div class="span-3">
                    <label class="field-label">Status</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">Semua Status</option>
                        @foreach (\App\Support\TermCatalog::options('po_status', \App\Support\DomainStatus::legacyOptions(\App\Support\DomainStatus::GROUP_PO_STATUS)) as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="span-2">
                    <button class="btn btn-primary btn-sm w-100">Filter</button>
                </div>

                <div class="span-1">
                    <a href="{{ route('po.index') }}" class="btn btn-light btn-sm w-100">Reset</a>
                </div>
            </form>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Daftar Purchase Order</h3>
                    <div class="ui-surface-subtitle">Fokus ke dokumen aktif tanpa hero besar atau panel dashboard tambahan.</div>
                </div>

                <div class="page-actions">
                    <input type="text" id="po-table-search" class="form-control form-control-sm"
                        placeholder="Cari PO..." style="max-width: 240px;">
                    <a href="{{ route('po.export-excel', request()->query()) }}" class="btn btn-light btn-sm">
                        <i class="fas fa-file-excel"></i> Export Monitoring
                    </a>
                    <a href="{{ route('po.create') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-plus"></i> Create PO
                    </a>
                </div>
            </div>

            <div class="table-wrap table-responsive">
                <table class="table table-hover ui-table">
                    <thead>
                        <tr>
                            <th>PO</th>
                            <th>Tanggal</th>
                            <th>Supplier</th>
                            <th>ETA</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
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
                                    <a href="{{ route('po.show', $r->po_number) }}" class="doc-number text-decoration-none">{{ $r->po_number }}</a>
                                    <div class="doc-meta">Dokumen pembelian</div>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($r->po_date)->format('d-m-Y') }}</td>
                                <td><div class="doc-number">{{ $r->supplier_code }}</div><div class="doc-meta">{{ $r->supplier_name }}</div></td>
                                <td>
                                    @if ($r->eta_date)
                                        {{ \Carbon\Carbon::parse($r->eta_date)->format('d-m-Y') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td><x-status-badge :status="$r->status" scope="po" /></td>
                                <td class="text-end">
                                    <div class="action-stack">
                                        <a href="{{ route('po.export-detail-excel', $r->po_number) }}" class="btn btn-sm btn-outline-success">Export Excel</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada PO.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="table-wrap table-responsive">
                <div class="mt-2">{{ $rows->links() }}</div>
            </div>
        </section>

        <a href="{{ route('po.create') }}" class="fab-create btn btn-success" title="Create PO">
            <i class="fas fa-plus"></i>
        </a>
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
        .fab-create {
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
    </style>
@endpush
