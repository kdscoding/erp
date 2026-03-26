@extends('layouts.erp')
@php($title='Outstanding Report')
@php($header='Outstanding Purchase Order')
@php($headerSubtitle='Laporan PO yang masih outstanding berdasarkan supplier, status, dan rentang tanggal.')

@section('content')
<div class="page-shell">
    <section class="page-head">
        <div class="page-head-main">
            <h2 class="page-section-title">Outstanding PO Report</h2>
            <p class="page-section-subtitle">Fokus ke filter laporan dan daftar dokumen tanpa panel tambahan yang tidak perlu.</p>
        </div>
    </section>

    <section class="ui-surface">
        <div class="ui-surface-head">
            <div>
                <h3 class="ui-surface-title">Filter Laporan Outstanding</h3>
                <div class="ui-surface-subtitle">Saring berdasarkan supplier, status, dan periode dokumen PO.</div>
            </div>
        </div>
        <form method="GET" class="filter-grid">
            <div class="span-3"><label class="field-label">Supplier</label><select name="supplier_id" class="form-control form-control-sm"><option value="">Semua Supplier</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>{{ $supplier->supplier_name }}</option>@endforeach</select></div>
            <div class="span-2"><label class="field-label">Status</label><select name="status" class="form-control form-control-sm"><option value="">Semua</option>@foreach(\App\Support\TermCatalog::options('po_status', ['PO Issued','Open','Late']) as $status => $label)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>@endforeach</select></div>
            <div class="span-2"><label class="field-label">Mulai</label><input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control form-control-sm"></div>
            <div class="span-2"><label class="field-label">Sampai</label><input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control form-control-sm"></div>
            <div class="span-1"><button class="btn btn-primary btn-sm w-100">Filter</button></div>
            <div class="span-1"><a href="{{ route('reports.outstanding') }}" class="btn btn-light btn-sm w-100">Reset</a></div>
        </form>
    </section>

    <section class="ui-surface">
        <div class="ui-surface-head">
            <div>
                <h3 class="ui-surface-title">Daftar Outstanding PO</h3>
                <div class="ui-surface-subtitle">Dokumen yang masih aktif ditampilkan dalam format list laporan yang ringkas.</div>
            </div>
        </div>
        <div class="table-wrap table-responsive">
            <table class="table table-hover ui-table">
                <thead><tr><th>PO Number</th><th>Tanggal</th><th>Supplier</th><th>Status</th><th>ETA</th></tr></thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td><div class="doc-number">{{ $row->po_number }}</div></td>
                            <td>{{ \Carbon\Carbon::parse($row->po_date)->format('d-m-Y') }}</td>
                            <td>{{ $row->supplier_name }}</td>
                            <td>{{ \App\Support\TermCatalog::label('po_status', $row->status, $row->status) }}</td>
                            <td>{{ $row->eta_date ? \Carbon\Carbon::parse($row->eta_date)->format('d-m-Y') : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Tidak ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-2">{{ $rows->links() }}</div>
</div>
@endsection
