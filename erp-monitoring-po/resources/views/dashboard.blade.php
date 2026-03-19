@extends('layouts.erp')

@php($title = 'Dashboard ERP')
@php($header = 'Dashboard Monitoring PO')

@section('content')
    @php
        $isSupervisor = auth()->check()
            ? \Illuminate\Support\Facades\DB::table('roles as r')
                ->join('user_roles as ur', 'ur.role_id', '=', 'r.id')
                ->where('ur.user_id', auth()->id())
                ->where('r.slug', 'supervisor')
                ->exists()
            : false;
    @endphp
    @if ($isSupervisor)
        <div class="alert alert-info border-0" style="border-radius:12px;background:#e0f2fe;color:#0c4a6e;">
            Mode Supervisor aktif: tampilan ini read-only dan berfokus pada KPI item-level.
        </div>
    @endif
    <style>
        .kpi-card {
            border-radius: 12px;
            color: #fff;
            position: relative;
            overflow: hidden;
            min-height: 108px
        }

        .kpi-card .kpi-icon {
            position: absolute;
            right: 12px;
            top: 12px;
            font-size: 34px;
            opacity: .25
        }

        .kpi-label {
            font-size: 12px;
            opacity: .95
        }

        .kpi-value {
            font-size: 36px;
            font-weight: 700;
            line-height: 1
        }

        .bg-kpi-1 {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            color: #1e3a8a
        }

        .bg-kpi-2 {
            background: linear-gradient(135deg, #ffe4e6, #fecdd3);
            color: #9f1239
        }

        .bg-kpi-3 {
            background: linear-gradient(135deg, #e0f2fe, #bae6fd);
            color: #0c4a6e
        }

        .bg-kpi-4 {
            background: linear-gradient(135deg, #dcfce7, #bbf7d0);
            color: #166534
        }

        .bg-kpi-5 {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            color: #92400e
        }

        .bg-kpi-6 {
            background: linear-gradient(135deg, #ede9fe, #ddd6fe);
            color: #5b21b6
        }
    </style>

    <div class="row g-3 mb-3">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card kpi-card bg-kpi-1">
                <div class="card-body">
                    <div class="kpi-label">Open PO</div>
                    <div class="kpi-value">{{ $metrics['open_po'] }}</div><i class="fas fa-file-invoice kpi-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card kpi-card bg-kpi-2">
                <div class="card-body">
                    <div class="kpi-label">Items Pending ETD</div>
                    <div class="kpi-value">{{ $metrics['items_pending_etd'] }}</div><i
                        class="fas fa-hourglass-half kpi-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card kpi-card bg-kpi-3">
                <div class="card-body">
                    <div class="kpi-label">Shipped Hari Ini</div>
                    <div class="kpi-value">{{ $metrics['shipped_today'] }}</div><i
                        class="fas fa-shipping-fast kpi-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card kpi-card bg-kpi-4">
                <div class="card-body">
                    <div class="kpi-label">GR Hari Ini</div>
                    <div class="kpi-value">{{ $metrics['received_today'] }}</div><i class="fas fa-box-open kpi-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card kpi-card bg-kpi-5">
                <div class="card-body">
                    <div class="kpi-label">Partial PO</div>
                    <div class="kpi-value">{{ $metrics['partial_po'] }}</div><i class="fas fa-balance-scale kpi-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card kpi-card bg-kpi-6">
                <div class="card-body">
                    <div class="kpi-label">Late Items</div>
                    <div class="kpi-value">{{ $metrics['late_items'] }}</div><i
                        class="fas fa-triangle-exclamation kpi-icon"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ranking Keterlambatan Supplier</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Supplier</th>
                                <th class="text-end">Jumlah PO Terlambat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($supplierDelay as $row)
                                <tr>
                                    <td>{{ $row->supplier_name }}</td>
                                    <td class="text-end"><span class="badge bg-danger">{{ $row->late_count }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">Belum ada keterlambatan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Receiving Terbaru</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>GR Number</th>
                                <th>PO</th>
                                <th>Supplier</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentReceivings as $row)
                                <tr>
                                    <td>{{ $row->gr_number }}</td>
                                    <td>{{ $row->po_number }}</td>
                                    <td>{{ $row->supplier_name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($row->receipt_date)->format('d-m-Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Belum ada data receiving.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">At-Risk Items</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>PO Number</th>
                                <th>Item</th>
                                <th>Supplier</th>
                                <th>ETD</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($atRiskItems as $item)
                                <tr class="table-warning">
                                    <td>{{ $item->po_number }}</td>
                                    <td>{{ $item->item_name }}</td>
                                    <td>{{ $item->supplier_name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->etd)->format('d-m-Y') }}</td>
                                    <td><span class="badge bg-warning">{{ $item->status }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Tidak ada item berisiko.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Open PO</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>PO Number</th>
                                <th>Supplier</th>
                                <th>Items</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($openPOs as $po)
                                <tr>
                                    <td>{{ $po->po_number }}</td>
                                    <td>{{ $po->supplier_name }}</td>
                                    <td>{{ $po->total_items }}/{{ $po->confirmed_items }}</td>
                                    <td><span class="badge bg-primary">{{ $po->status }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Tidak ada PO terbuka.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
