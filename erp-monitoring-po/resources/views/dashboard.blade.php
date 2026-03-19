@extends('layouts.erp')

@php($title='Dashboard ERP')
@php($header='Dashboard Monitoring PO')

@section('content')
<<<<<<< ours
<style>
    .kpi-card{border-radius:10px;color:#fff;position:relative;overflow:hidden;min-height:108px}
    .kpi-card .kpi-icon{position:absolute;right:12px;top:12px;font-size:34px;opacity:.25}
    .kpi-label{font-size:12px;opacity:.95}
    .kpi-value{font-size:36px;font-weight:700;line-height:1}
<<<<<<< ours
    .bg-kpi-1{background:linear-gradient(135deg,#00a6c0,#0072a5)}
    .bg-kpi-2{background:linear-gradient(135deg,#ea5455,#b51d2f)}
    .bg-kpi-3{background:linear-gradient(135deg,#2d9cdb,#1565d8)}
    .bg-kpi-4{background:linear-gradient(135deg,#2ecc71,#1f9c53)}
    .bg-kpi-5{background:linear-gradient(135deg,#f9ca24,#e39d12)}
    .bg-kpi-6{background:linear-gradient(135deg,#7b8794,#4b5563)}
=======
    .bg-kpi-1{background:linear-gradient(135deg,#dbeafe,#bfdbfe);color:#1e3a8a}
    .bg-kpi-2{background:linear-gradient(135deg,#ffe4e6,#fecdd3);color:#9f1239}
    .bg-kpi-3{background:linear-gradient(135deg,#e0f2fe,#bae6fd);color:#0c4a6e}
    .bg-kpi-4{background:linear-gradient(135deg,#dcfce7,#bbf7d0);color:#166534}
    .bg-kpi-5{background:linear-gradient(135deg,#fef3c7,#fde68a);color:#92400e}
    .bg-kpi-6{background:linear-gradient(135deg,#ede9fe,#ddd6fe);color:#5b21b6}
>>>>>>> theirs
</style>

<div class="row g-3 mb-3">
    <div class="col-xl-2 col-md-4 col-6"><div class="card kpi-card bg-kpi-1"><div class="card-body"><div class="kpi-label">Open PO</div><div class="kpi-value">{{ $metrics['open_po'] }}</div><i class="fas fa-file-invoice kpi-icon"></i></div></div></div>
    <div class="col-xl-2 col-md-4 col-6"><div class="card kpi-card bg-kpi-2"><div class="card-body"><div class="kpi-label">Overdue PO</div><div class="kpi-value">{{ $metrics['overdue_po'] }}</div><i class="fas fa-clock kpi-icon"></i></div></div></div>
    <div class="col-xl-2 col-md-4 col-6"><div class="card kpi-card bg-kpi-3"><div class="card-body"><div class="kpi-label">Shipped Hari Ini</div><div class="kpi-value">{{ $metrics['shipped_today'] }}</div><i class="fas fa-shipping-fast kpi-icon"></i></div></div></div>
    <div class="col-xl-2 col-md-4 col-6"><div class="card kpi-card bg-kpi-4"><div class="card-body"><div class="kpi-label">GR Hari Ini</div><div class="kpi-value">{{ $metrics['received_today'] }}</div><i class="fas fa-box-open kpi-icon"></i></div></div></div>
    <div class="col-xl-2 col-md-4 col-6"><div class="card kpi-card bg-kpi-5"><div class="card-body"><div class="kpi-label">Partial PO</div><div class="kpi-value">{{ $metrics['partial_po'] }}</div><i class="fas fa-balance-scale kpi-icon"></i></div></div></div>
    <div class="col-xl-2 col-md-4 col-6"><div class="card kpi-card bg-kpi-6"><div class="card-body"><div class="kpi-label">At-Risk Items</div><div class="kpi-value">{{ $metrics['at_risk_items'] }}</div><i class="fas fa-triangle-exclamation kpi-icon"></i></div></div></div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Ranking Keterlambatan Supplier</h3></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Supplier</th><th class="text-end">Jumlah PO Terlambat</th></tr></thead>
                    <tbody>
                    @forelse($supplierDelay as $row)
                        <tr><td>{{ $row->supplier_name }}</td><td class="text-end"><span class="badge bg-danger">{{ $row->late_count }}</span></td></tr>
                    @empty
                        <tr><td colspan="2" class="text-center text-muted">Belum ada keterlambatan.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Receiving Terbaru</h3></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>GR Number</th><th>PO</th><th>Supplier</th><th>Tanggal</th></tr></thead>
                    <tbody>
                    @forelse($recentReceivings as $row)
                        <tr><td>{{ $row->gr_number }}</td><td>{{ $row->po_number }}</td><td>{{ $row->supplier_name }}</td><td>{{ \Carbon\Carbon::parse($row->receipt_date)->format('d-m-Y') }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">Belum ada data receiving.</td></tr>
=======
@php
    $isSupervisor = auth()->check()
        ? \Illuminate\Support\Facades\DB::table('roles as r')
            ->join('user_roles as ur', 'ur.role_id', '=', 'r.id')
            ->where('ur.user_id', auth()->id())
            ->where('r.slug', 'supervisor')
            ->exists()
        : false;
@endphp
@if($isSupervisor)
    <div class="alert alert-info border-0" style="border-radius:12px;background:#e0f2fe;color:#0c4a6e;">
        Mode Supervisor aktif: tampilan ini read-only dan berfokus pada KPI item-level.
    </div>
@endif
<style>
    .kpi-card{border-radius:12px;color:#fff;position:relative;overflow:hidden;min-height:108px}
    .kpi-card .kpi-icon{position:absolute;right:12px;top:12px;font-size:34px;opacity:.25}
    .kpi-label{font-size:12px;opacity:.95}
    .kpi-value{font-size:36px;font-weight:700;line-height:1}
    .bg-kpi-1{background:linear-gradient(135deg,#dbeafe,#bfdbfe);color:#1e3a8a}
    .bg-kpi-2{background:linear-gradient(135deg,#ffe4e6,#fecdd3);color:#9f1239}
    .bg-kpi-3{background:linear-gradient(135deg,#e0f2fe,#bae6fd);color:#0c4a6e}
    .bg-kpi-4{background:linear-gradient(135deg,#dcfce7,#bbf7d0);color:#166534}
    .bg-kpi-5{background:linear-gradient(135deg,#fef3c7,#fde68a);color:#92400e}
    .bg-kpi-6{background:linear-gradient(135deg,#ede9fe,#ddd6fe);color:#5b21b6}
</style>

<div class="row g-3 mb-3">
    <div class="col-xl-2 col-md-4 col-6"><div class="card kpi-card bg-kpi-1"><div class="card-body"><div class="kpi-label">Open PO</div><div class="kpi-value">{{ $metrics['open_po'] }}</div><i class="fas fa-file-invoice kpi-icon"></i></div></div></div>
    <div class="col-xl-2 col-md-4 col-6"><div class="card kpi-card bg-kpi-2"><div class="card-body"><div class="kpi-label">Items Pending ETD</div><div class="kpi-value">{{ $metrics['items_pending_etd'] }}</div><i class="fas fa-hourglass-half kpi-icon"></i></div></div></div>
    <div class="col-xl-2 col-md-4 col-6"><div class="card kpi-card bg-kpi-3"><div class="card-body"><div class="kpi-label">Shipped Hari Ini</div><div class="kpi-value">{{ $metrics['shipped_today'] }}</div><i class="fas fa-shipping-fast kpi-icon"></i></div></div></div>
    <div class="col-xl-2 col-md-4 col-6"><div class="card kpi-card bg-kpi-4"><div class="card-body"><div class="kpi-label">GR Hari Ini</div><div class="kpi-value">{{ $metrics['received_today'] }}</div><i class="fas fa-box-open kpi-icon"></i></div></div></div>
    <div class="col-xl-2 col-md-4 col-6"><div class="card kpi-card bg-kpi-5"><div class="card-body"><div class="kpi-label">Partial PO</div><div class="kpi-value">{{ $metrics['partial_po'] }}</div><i class="fas fa-balance-scale kpi-icon"></i></div></div></div>
    <div class="col-xl-2 col-md-4 col-6"><div class="card kpi-card bg-kpi-6"><div class="card-body"><div class="kpi-label">Late Items</div><div class="kpi-value">{{ $metrics['late_items'] }}</div><i class="fas fa-triangle-exclamation kpi-icon"></i></div></div></div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Ranking Keterlambatan Supplier</h3></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Supplier</th><th class="text-end">Jumlah PO Terlambat</th></tr></thead>
                    <tbody>
                    @forelse($supplierDelay as $row)
                        <tr><td>{{ $row->supplier_name }}</td><td class="text-end"><span class="badge bg-danger">{{ $row->late_count }}</span></td></tr>
                    @empty
                        <tr><td colspan="2" class="text-center text-muted">Belum ada keterlambatan.</td></tr>
>>>>>>> theirs
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
<<<<<<< ours
<<<<<<< ours
        <div class="card border-warning">
=======
        <div class="card">
>>>>>>> theirs
            <div class="card-header"><h3 class="card-title">At-Risk Items (ETD Lewat)</h3></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>PO</th><th>Item</th><th>Supplier</th><th>ETD</th><th>OS Qty</th></tr></thead>
                    <tbody>
                    @forelse($atRiskItems as $risk)
<<<<<<< ours
                        <tr class="table-warning">
=======
                        <tr style="background:#fffbeb;">
>>>>>>> theirs
                            <td>{{ $risk->po_number }}</td>
                            <td>{{ $risk->item_code }} - {{ $risk->item_name }}</td>
                            <td>{{ $risk->supplier_name }}</td>
                            <td>{{ \Carbon\Carbon::parse($risk->etd_date)->format('d-m-Y') }}</td>
                            <td>{{ number_format($risk->outstanding_qty, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Tidak ada item berisiko.</td></tr>
=======
        <div class="card">
            <div class="card-header"><h3 class="card-title">Receiving Terbaru</h3></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>GR Number</th><th>PO</th><th>Supplier</th><th>Tanggal</th></tr></thead>
                    <tbody>
                    @forelse($recentReceivings as $row)
                        <tr><td>{{ $row->gr_number }}</td><td>{{ $row->po_number }}</td><td>{{ $row->supplier_name }}</td><td>{{ \Carbon\Carbon::parse($row->receipt_date)->format('d-m-Y') }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">Belum ada data receiving.</td></tr>
>>>>>>> theirs
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<<<<<<< ours
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Open PO Terdekat ETA</h3>
                <a href="{{ route('po.index') }}" class="btn btn-sm btn-primary">Lihat Semua PO</a>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-striped mb-0">
                    <thead><tr><th>PO Number</th><th>Tanggal PO</th><th>Supplier</th><th>Status</th><th>ETA</th></tr></thead>
                    <tbody>
                    @forelse($openPoList as $row)
                        <tr>
                            <td>{{ $row->po_number }}</td>
                            <td>{{ \Carbon\Carbon::parse($row->po_date)->format('d-m-Y') }}</td>
                            <td>{{ $row->supplier_name }}</td>
                            <td><span class="badge bg-info">{{ $row->status }}</span></td>
                            <td>{{ $row->eta_date ? \Carbon\Carbon::parse($row->eta_date)->format('d-m-Y') : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Tidak ada open PO.</td></tr>
=======
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">At-Risk Items (ETD Lewat)</h3></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>PO</th><th>Item</th><th>Supplier</th><th>ETD</th><th>OS Qty</th></tr></thead>
                    <tbody>
                    @forelse($atRiskItems as $risk)
                        <tr style="background:#fffbeb;">
                            <td>{{ $risk->po_number }}</td>
                            <td>{{ $risk->item_code }} - {{ $risk->item_name }}</td>
                            <td>{{ $risk->supplier_name }}</td>
                            <td>{{ \Carbon\Carbon::parse($risk->etd_date)->format('d-m-Y') }}</td>
                            <td>{{ number_format($risk->outstanding_qty, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Tidak ada item berisiko.</td></tr>
>>>>>>> theirs
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<<<<<<< ours
=======

    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Open PO Terdekat ETA</h3>
                <a href="{{ route('po.index') }}" class="btn btn-sm btn-primary">Lihat Semua PO</a>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-striped mb-0">
                    <thead><tr><th>PO Number</th><th>Tanggal PO</th><th>Supplier</th><th>Status</th><th>ETA</th></tr></thead>
                    <tbody>
                    @forelse($openPoList as $row)
                        <tr>
                            <td>{{ $row->po_number }}</td>
                            <td>{{ \Carbon\Carbon::parse($row->po_date)->format('d-m-Y') }}</td>
                            <td>{{ $row->supplier_name }}</td>
                            <td><span class="badge bg-info">{{ $row->status }}</span></td>
                            <td>{{ $row->eta_date ? \Carbon\Carbon::parse($row->eta_date)->format('d-m-Y') : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Tidak ada open PO.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
>>>>>>> theirs
</div>
@endsection
