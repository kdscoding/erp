@extends('layouts.erp')

@php($title = 'Purchase Orders')
@php($header = 'Purchase Orders')
@php($headerSubtitle = 'Kelola dan pantau dokumen purchase order yang masih aktif maupun yang sudah selesai.')

@section('content')
    @php($openCount = $rows->getCollection()->where('status', 'Open')->count())
    @php($lateCount = $rows->getCollection()->where('status', 'Late')->count())
    @php($closedCount = $rows->getCollection()->where('status', 'Closed')->count())
    @php($cancelledCount = $rows->getCollection()->where('status', 'Cancelled')->count())

    <div class="page-shell">
        <section class="summary-chips">
            <div class="summary-chip">
                <div class="summary-chip-label">Open</div>
                <div class="summary-chip-value">{{ $openCount }}</div>
            </div>
            <div class="summary-chip">
                <div class="summary-chip-label">Late</div>
                <div class="summary-chip-value">{{ $lateCount }}</div>
            </div>
            <div class="summary-chip">
                <div class="summary-chip-label">Closed</div>
                <div class="summary-chip-value">{{ $closedCount }}</div>
            </div>
            <div class="summary-chip">
                <div class="summary-chip-label">Cancelled</div>
                <div class="summary-chip-value">{{ $cancelledCount }}</div>
            </div>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Filter Purchase Orders</h3>
                    <div class="ui-surface-subtitle">Cari purchase orders berdasarkan supplier dan status.</div>
                </div>
            </div>

            <form method="GET" class="filter-grid">
                <div class="span-3">
                    <label class="field-label">Nomor PO</label>
                    <input type="text" name="po_number" value="{{ request('po_number') }}" class="form-control form-control-sm" placeholder="Contoh: PO-2026-0001">
                </div>

                <div class="span-4">
                    <label class="field-label">Supplier Code</label>
                    <select name="supplier_code" class="form-control form-control-sm">
                        <option value="">Semua Supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->supplier_code }}" @selected(request('supplier_code') === $supplier->supplier_code)>
                                {{ $supplier->supplier_code }} - {{ $supplier->supplier_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="span-3">
                    <label class="field-label">Status</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">Semua Status</option>
                        @foreach (\App\Support\TermCatalog::options('po_status', ['Open', 'Late', 'Closed', 'Cancelled']) as $status => $label)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="span-2">
                    <button class="btn btn-primary btn-sm w-100">Apply</button>
                </div>

                <div class="span-2">
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
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $r)
                            <tr>
                                <td>
                                    <div class="doc-number">{{ $r->po_number }}</div>
                                    <div class="doc-meta">Dokumen pembelian</div>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($r->po_date)->format('d-m-Y') }}</td>
                                <td><div class="doc-number">{{ $r->supplier_code }}</div><div class="doc-meta">{{ $r->supplier_name }}</div></td>
                                <td><x-status-badge :status="$r->status" scope="po" /></td>
                                <td class="text-end">
                                    <div class="action-stack">
                                        <a href="{{ route('po.show', $r->po_number) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                                        <a href="{{ route('po.export-detail-excel', $r->po_number) }}" class="btn btn-sm btn-outline-success">Export Excel</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada PO.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="mt-2">{{ $rows->links() }}</div>
@endsection
