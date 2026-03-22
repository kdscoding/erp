@extends('layouts.erp')

@php($title = 'Dashboard Lemon')
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
            background: linear-gradient(135deg, #bfd730, #8fc63f)
        }

        .bg-kpi-2 {
            background: linear-gradient(135deg, #f1d93b, #d8b529)
        }

        .bg-kpi-3 {
            background: linear-gradient(135deg, #9ecb3c, #6f9628)
        }

        .bg-kpi-4 {
            background: linear-gradient(135deg, #cfe56a, #94bf36)
        }

        .bg-kpi-5 {
            background: linear-gradient(135deg, #f6e97e, #e2c93e)
        }

        .bg-kpi-6 {
            background: linear-gradient(135deg, #7b8f35, #556c22)
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
                                <th class="text-end">Item Terlambat</th>
                                <th class="text-end">PO Terdampak</th>
                                <th>ETD Terlama</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($supplierDelay as $row)
                                <tr>
                                    <td>{{ $row->supplier_name }}</td>
                                    <td class="text-end"><span class="badge bg-danger">{{ $row->late_item_count }}</span></td>
                                    <td class="text-end">{{ $row->late_po_count }}</td>
                                    <td>{{ $row->oldest_late_etd ? \Carbon\Carbon::parse($row->oldest_late_etd)->format('d-m-Y') : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Belum ada keterlambatan.</td>
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
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Komposisi Item per PO</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>PO</th>
                                <th>Supplier</th>
                                <th>Header</th>
                                <th>Waiting</th>
                                <th>Confirmed</th>
                                <th>Late</th>
                                <th>Partial</th>
                                <th>Closed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($poMonitoringSummary as $summary)
                                <tr>
                                    <td><a href="{{ route('po.show', $summary->po_id) }}">{{ $summary->po_number }}</a></td>
                                    <td>{{ $summary->supplier_name }}</td>
                                    <td><span class="badge bg-light text-dark">{{ \App\Support\TermCatalog::label('po_status', $summary->po_status, $summary->po_status) }}</span></td>
                                    <td>{{ $summary->waiting_items }}</td>
                                    <td>{{ $summary->confirmed_items }}</td>
                                    <td>{{ $summary->late_items }}</td>
                                    <td>{{ $summary->partial_items }}</td>
                                    <td>{{ $summary->closed_items }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Belum ada ringkasan monitoring.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Comprehensive Item Monitoring</h3>
                    <div class="d-flex gap-2 align-items-center">
                        <a href="{{ route('monitoring') }}" class="btn btn-sm btn-primary">Lihat Semua Monitoring</a>
                        <div class="d-flex gap-2">
                            <input type="text" id="searchInput" class="form-control form-control-sm"
                                placeholder="Cari PO, Item, atau Supplier..." style="width: 250px;">
                            <select id="statusItemFilter" class="form-control form-control-sm" style="width: 150px;">
                                <option value="">Semua Status Item</option>
                                @foreach (\App\Support\TermCatalog::options('po_item_status', ['Closed', 'Partial', 'Waiting', 'Late', 'Confirmed']) as $status => $label)
                                    <option value="{{ $status }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <select id="statusEtdFilter" class="form-control form-control-sm" style="width: 150px;">
                                <option value="">Semua Status ETD</option>
                                <option value="On-Time">On-Time</option>
                                <option value="At-Risk">At-Risk</option>
                                <option value="N/A">N/A</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover mb-0" id="itemMonitoringTable">
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
                                @php($etdStatus = $item->etd_date ? (\Carbon\Carbon::parse($item->etd_date)->isBefore(now()) ? 'At-Risk' : 'On-Time') : 'N/A')
                                <tr data-status-item="{{ $item->monitoring_status }}"
                                    data-status-etd="{{ $etdStatus }}"
                                    data-search="{{ strtolower($item->po_number . ' ' . $item->item_code . ' ' . $item->item_name . ' ' . $item->supplier_name) }}">
                                    <td>
                                        <a href="{{ route('po.show', $item->po_id) }}"
                                            class="fw-semibold text-decoration-none">{{ $item->po_number }}</a>
                                        <div class="small text-muted">PO: {{ \App\Support\TermCatalog::label('po_status', $item->po_status, $item->po_status) }}</div>
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
                                            } }}">{{ \App\Support\TermCatalog::label('po_item_status', $item->monitoring_status, $item->monitoring_status) }}</span>
                                    </td>
                                    <td>
                                        @if ($etdStatus === 'At-Risk')
                                            <span class="badge bg-danger">At-Risk</span>
                                        @elseif ($etdStatus === 'On-Time')
                                            <span class="badge bg-success">On-Time</span>
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->monitoring_note }}</td>
                                    <td>{{ $item->etd_date ? \Carbon\Carbon::parse($item->etd_date)->format('d-m-Y') : '-' }}
                                    </td>
                                    <td>{{ \App\Support\NumberFormatter::trim($item->outstanding_qty) }}</td>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const statusItemFilter = document.getElementById('statusItemFilter');
            const statusEtdFilter = document.getElementById('statusEtdFilter');
            const table = document.getElementById('itemMonitoringTable');
            const rows = table.querySelectorAll('tbody tr');

            function filterTable() {
                const searchTerm = searchInput.value.toLowerCase();
                const statusItemValue = statusItemFilter.value;
                const statusEtdValue = statusEtdFilter.value;

                rows.forEach(row => {
                    const searchData = row.getAttribute('data-search') || '';
                    const statusItemData = row.getAttribute('data-status-item') || '';
                    const statusEtdData = row.getAttribute('data-status-etd') || '';

                    const matchesSearch = searchData.includes(searchTerm);
                    const matchesStatusItem = !statusItemValue || statusItemData === statusItemValue;
                    const matchesStatusEtd = !statusEtdValue || statusEtdData === statusEtdValue;

                    if (matchesSearch && matchesStatusItem && matchesStatusEtd) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            searchInput.addEventListener('input', filterTable);
            statusItemFilter.addEventListener('change', filterTable);
            statusEtdFilter.addEventListener('change', filterTable);
        });
    </script>
@endsection
