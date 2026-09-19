@extends('layouts.erp')

@php($title = 'Purchase Orders')
@php($header = 'Purchase Orders')
@php($headerSubtitle = 'Kelola dan pantau dokumen purchase order.')

@section('content')
    @php($statusCounts = $summaryChips ?? [])

    <div class="page-shell">
        <section class="ui-surface" style="margin-bottom: 1rem;">
            <div class="ui-surface-head">
                <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
                    <div>
                        <h3 class="ui-surface-title mb-1">Filter</h3>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('po.export-excel', request()->query()) }}" class="btn btn-light btn-sm">
                            <i class="fas fa-file-excel"></i> Export
                        </a>
                        <a href="{{ route('po.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Buat PO
                        </a>
                    </div>
                </div>
            </div>

            <form method="GET" class="filter-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
                <div>
                    <label class="field-label">Nomor PO</label>
                    <input type="text" name="po_number" value="{{ request('po_number') }}" class="form-control form-control-sm" placeholder="PO-2026-0001">
                </div>

                <div>
                    <label class="field-label">Supplier</label>
                    <select name="supplier_code" class="form-control form-control-sm supplier-select">
                        <option value="">Semua</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->supplier_code }}" @selected(request('supplier_code') === $supplier->supplier_code)>
                                {{ $supplier->supplier_code }} - {{ $supplier->supplier_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="field-label">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm">
                </div>

                <div>
                    <label class="field-label">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm">
                </div>

                <div>
                    <label class="field-label">Status</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">Semua</option>
                        @foreach (\App\Support\TermCatalog::options('po_status', \App\Support\DomainStatus::legacyOptions(\App\Support\DomainStatus::GROUP_PO_STATUS)) as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex align-items-end gap-2">
                    <button class="btn btn-primary btn-sm" type="submit">Filter</button>
                    <a href="{{ route('po.index') }}" class="btn btn-light btn-sm">Reset</a>
                </div>
            </form>
        </section>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
            <div class="summary-chips" style="gap: 0.5rem;">
                @php($canonicalOrder = ['Full', 'Partial', 'Delayed', 'Open', 'Late', 'Closed', 'Cancelled'])
                @foreach ($canonicalOrder as $label)
                    <span class="badge bg-light text-dark border" style="font-size: 0.7rem; padding: 0.35rem 0.6rem;">
                        {{ $label }}: {{ $statusCounts[$label] ?? 0 }}
                    </span>
                @endforeach
            </div>
            <input type="text" id="po-table-search" class="form-control form-control-sm" placeholder="Cari PO..." style="max-width: 240px;">
        </div>

        <section class="ui-surface">
            <div class="table-wrap table-responsive">
                <table class="table table-hover ui-table" id="po-table">
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
                                </td>
                                <td>{{ \Carbon\Carbon::parse($r->po_date)->format('d-m-Y') }}</td>
                                <td>
                                    <div class="doc-number">{{ $r->supplier_code }}</div>
                                    <div class="doc-meta">{{ $r->supplier_name }}</div>
                                </td>
                                <td>
                                    @if ($r->eta_date)
                                        {{ \Carbon\Carbon::parse($r->eta_date)->format('d-m-Y') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td><x-status-badge :status="$r->status" scope="po" /></td>
                                <td class="text-end">
                                    <a href="{{ route('po.export-detail-excel', $r->po_number) }}" class="btn btn-sm btn-outline-success">Export</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Belum ada PO.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-2">{{ $rows->links() }}</div>
        </section>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/po-index.js')
    <script>
        window.PO_INDEX_CONFIG = {};
    </script>
@endpush
