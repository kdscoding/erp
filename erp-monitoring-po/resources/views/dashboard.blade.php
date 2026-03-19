@extends('layouts.erp')

@php($title = 'Dashboard ERP')
@php($header = 'Dashboard Monitoring PO')

@section('content')
    <style>
        .kpi-card {
            border-radius: 10px;
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
            background: linear-gradient(135deg, #00a6c0, #0072a5)
        }

        .bg-kpi-2 {
            background: linear-gradient(135deg, #ea5455, #b51d2f)
        }

        .bg-kpi-3 {
            background: linear-gradient(135deg, #2d9cdb, #1565d8)
        }

        .bg-kpi-4 {
            background: linear-gradient(135deg, #2ecc71, #1f9c53)
        }

        .bg-kpi-5 {
            background: linear-gradient(135deg, #f9ca24, #e39d12)
        }

        .bg-kpi-6 {
            background: linear-gradient(135deg, #7b8794, #4b5563)
        }

        .mini-chip {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600
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
                    <div class="kpi-label">Overdue PO</div>
                    <div class="kpi-value">{{ $metrics['overdue_po'] }}</div><i class="fas fa-clock kpi-icon"></i>
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
                    <div class="kpi-label">At-Risk Items</div>
                    <div class="kpi-value">{{ $metrics['at_risk_items'] }}</div><i
                        class="fas fa-triangle-exclamation kpi-icon"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Ranking Keterlambatan Supplier -->
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

        <!-- Receiving Terbaru -->
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

        <!-- Comprehensive Item Monitoring -->
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Comprehensive Item Monitoring</h3>
                    <span class="text-muted small">Melihat semua item dari PO open dengan status monitoring dan ETD.</span>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>PO</th>
                                <th>Item</th>
                                <th>Supplier</th>
                                <th>Status Item</th>
                                <th>Status ETD</th>
                                <th>Keterangan</th>
                                <th>ETD</th>
                                <th>Outstanding</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($itemMonitoringList as $item)
                                <tr class="{{ $item->monitoring_status === 'Late' ? 'table-warning' : '' }}">
                                    <td>
                                        <a href="{{ route('po.show', $item->po_id) }}"
                                            class="fw-semibold text-decoration-none">{{ $item->po_number }}</a>
                                        <div class="small text-muted">PO: {{ $item->po_status }}</div>
                                    </td>
                                    <td>{{ $item->item_code }} - {{ $item->item_name }}</td>
                                    <td>{{ $item->supplier_name }}</td>
                                    <td>
                                        <span
                                            class="badge {{ match ($item->monitoring_status) {
                                                'Closed' => 'bg-success',
                                                'Partial', 'Confirmed', 'PO Issued', 'Waiting' => 'bg-warning text-dark',
                                                'Late', 'Cancelled' => 'bg-danger',
                                                default => 'bg-secondary',
                                            } }}">{{ $item->monitoring_status }}</span>
                                    </td>
                                    <td>
                                        @if ($item->etd_date)
                                            @if (\Carbon\Carbon::parse($item->etd_date)->isBefore(now()))
                                                <span class="badge bg-danger">At-Risk</span>
                                            @else
                                                <span class="badge bg-success">On-Time</span>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->monitoring_note }}</td>
                                    <td>{{ $item->etd_date ? \Carbon\Carbon::parse($item->etd_date)->format('d-m-Y') : '-' }}
                                    </td>
                                    <td>{{ number_format($item->outstanding_qty, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Tidak ada item monitoring.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
