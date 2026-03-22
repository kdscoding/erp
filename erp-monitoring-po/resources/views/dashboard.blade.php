@extends('layouts.erp')

@php($title = 'Dashboard Lemon')
@php($header = 'Dashboard Monitoring PO')

@section('content')
    <style>
        .dash-shell {
            display: grid;
            gap: 1rem;
        }

        .dash-hero {
            border: 1px solid rgba(111, 150, 40, .14);
            border-radius: 18px;
            padding: 1.25rem;
            background:
                radial-gradient(circle at top right, rgba(241, 217, 59, .32), transparent 30%),
                linear-gradient(135deg, rgba(255, 255, 255, .94), rgba(244, 248, 219, .96));
            box-shadow: 0 18px 34px rgba(111, 150, 40, .08);
        }

        .dash-focus {
            display: flex;
            flex-wrap: wrap;
            gap: .65rem;
            justify-content: space-between;
        }

        .focus-pill {
            min-width: 132px;
            padding: .8rem .95rem;
            border-radius: 16px;
            background: rgba(255, 255, 255, .72);
            border: 1px solid rgba(111, 150, 40, .12);
        }

        .focus-pill-label {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #7f8c60;
            margin-bottom: .2rem;
        }

        .focus-pill-value {
            font-size: 1.4rem;
            font-weight: 800;
            color: #314216;
            line-height: 1;
        }

        .metric-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .9rem;
        }

        .metric-card {
            position: relative;
            overflow: hidden;
            padding: 1rem;
            border-radius: 18px;
            border: 1px solid rgba(111, 150, 40, .12);
            background: rgba(255, 255, 255, .92);
            box-shadow: 0 14px 28px rgba(111, 150, 40, .06);
        }

        .metric-card::after {
            content: "";
            position: absolute;
            inset: auto -18px -28px auto;
            width: 78px;
            height: 78px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .45);
        }

        .metric-tone-open {
            background: linear-gradient(135deg, #ffffff, #eef7d2);
        }

        .metric-tone-risk {
            background: linear-gradient(135deg, #fffef8, #fff0d5);
        }

        .metric-tone-ship {
            background: linear-gradient(135deg, #ffffff, #e6f4d8);
        }

        .metric-tone-gr {
            background: linear-gradient(135deg, #ffffff, #edf7cf);
        }

        .metric-label {
            font-size: .74rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #77875a;
            margin-bottom: .4rem;
        }

        .metric-value {
            font-size: 2rem;
            line-height: 1;
            font-weight: 800;
            color: #2e3e15;
            margin-bottom: .35rem;
        }

        .metric-foot {
            font-size: .82rem;
            color: #6f7d52;
        }

        .metric-icon {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.15rem;
            color: #6f9628;
            opacity: .65;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1.1fr .9fr;
            gap: 1rem;
        }

        .surface-card {
            border: 1px solid rgba(111, 150, 40, .12);
            border-radius: 18px;
            background: rgba(255, 255, 255, .94);
            box-shadow: 0 14px 32px rgba(111, 150, 40, .05);
        }

        .surface-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .75rem;
            padding: 1rem 1rem 0;
        }

        .surface-title {
            margin: 0;
            font-size: .96rem;
            font-weight: 800;
            color: #314216;
        }

        .surface-subtitle {
            font-size: .8rem;
            color: #7a8660;
        }

        .attention-list {
            display: grid;
            gap: .75rem;
            padding: 1rem;
        }

        .attention-item {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: .8rem;
            align-items: center;
            padding: .9rem 1rem;
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(255, 255, 255, .96), rgba(247, 248, 234, .96));
            border: 1px solid rgba(111, 150, 40, .1);
        }

        .attention-rank {
            width: 34px;
            height: 34px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            background: #f4f7d8;
            color: #5d7425;
        }

        .attention-name {
            font-weight: 700;
            color: #314216;
        }

        .attention-meta {
            font-size: .82rem;
            color: #7a8660;
            margin-top: .15rem;
        }

        .attention-badge {
            padding: .38rem .65rem;
            border-radius: 999px;
            background: #fff1eb;
            color: #b04835;
            font-size: .8rem;
            font-weight: 800;
        }

        .receipt-list {
            display: grid;
            gap: .75rem;
            padding: 1rem;
        }

        .receipt-item {
            display: block;
            padding: .9rem 1rem;
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(255, 255, 255, .96), rgba(243, 248, 214, .86));
            border: 1px solid rgba(111, 150, 40, .1);
            text-decoration: none;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }

        .receipt-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(111, 150, 40, .08);
            border-color: rgba(111, 150, 40, .22);
        }

        .receipt-top {
            display: flex;
            justify-content: space-between;
            gap: .75rem;
            margin-bottom: .35rem;
        }

        .receipt-number {
            font-weight: 800;
            color: #314216;
        }

        .receipt-date {
            font-size: .8rem;
            color: #76845d;
        }

        .receipt-meta {
            font-size: .83rem;
            color: #66764a;
        }

        .summary-table-wrap,
        .monitor-wrap {
            padding: 1rem;
        }

        .summary-table {
            margin-bottom: 0;
        }

        .summary-table thead th,
        .monitor-table thead th {
            font-size: .69rem;
            letter-spacing: .08em;
        }

        .summary-po {
            font-weight: 700;
            color: #314216;
        }

        .summary-muted {
            font-size: .8rem;
            color: #7a8660;
        }

        .qty-chip {
            display: inline-flex;
            min-width: 34px;
            justify-content: center;
            padding: .18rem .5rem;
            border-radius: 999px;
            font-size: .76rem;
            font-weight: 700;
            background: #f4f7d8;
            color: #5d7425;
        }

        .monitor-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .85rem;
            flex-wrap: wrap;
            padding: 1rem 1rem 0;
        }

        .monitor-filters {
            display: flex;
            gap: .6rem;
            flex-wrap: wrap;
            width: 100%;
        }

        .monitor-filters .form-control,
        .monitor-filters .form-select {
            max-width: 220px;
        }

        .monitor-table {
            margin-bottom: 0;
        }

        .item-main {
            font-weight: 700;
            color: #314216;
        }

        .item-sub {
            font-size: .8rem;
            color: #7a8660;
        }

        @media (max-width: 1199.98px) {
            .metric-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767.98px) {
            .dash-hero {
                padding: 1rem;
            }

            .dash-focus {
                justify-content: flex-start;
            }

            .metric-grid {
                grid-template-columns: 1fr;
            }

            .metric-value {
                font-size: 1.65rem;
            }

            .monitor-filters .form-control,
            .monitor-filters .form-select {
                max-width: 100%;
                width: 100%;
            }

            .receipt-top {
                flex-direction: column;
            }
        }
    </style>

    <div class="dash-shell">
        <section class="dash-hero">
            <div class="dash-focus">
                <div class="focus-pill">
                    <div class="focus-pill-label">Late PO</div>
                    <div class="focus-pill-value">{{ $metrics['late_po'] }}</div>
                </div>
                <div class="focus-pill">
                    <div class="focus-pill-label">At-Risk Item</div>
                    <div class="focus-pill-value">{{ $metrics['at_risk_items'] }}</div>
                </div>
                <div class="focus-pill">
                    <div class="focus-pill-label">Supplier Aktif</div>
                    <div class="focus-pill-value">{{ $metrics['suppliers'] }}</div>
                </div>
            </div>
        </section>

        <section class="metric-grid">
            <article class="metric-card metric-tone-open">
                <i class="fas fa-file-invoice metric-icon"></i>
                <div class="metric-label">Open PO</div>
                <div class="metric-value">{{ $metrics['open_po'] }}</div>
                <div class="metric-foot">Semua PO aktif yang masih berjalan.</div>
            </article>
            <article class="metric-card metric-tone-risk">
                <i class="fas fa-triangle-exclamation metric-icon"></i>
                <div class="metric-label">Overdue Item</div>
                <div class="metric-value">{{ $metrics['overdue_po'] }}</div>
                <div class="metric-foot">Item outstanding dengan ETD yang sudah lewat.</div>
            </article>
            <article class="metric-card metric-tone-ship">
                <i class="fas fa-shipping-fast metric-icon"></i>
                <div class="metric-label">Shipment Hari Ini</div>
                <div class="metric-value">{{ $metrics['shipped_today'] }}</div>
                <div class="metric-foot">Dokumen shipment yang diproses hari ini.</div>
            </article>
            <article class="metric-card metric-tone-gr">
                <i class="fas fa-box-open metric-icon"></i>
                <div class="metric-label">Receiving Hari Ini</div>
                <div class="metric-value">{{ $metrics['received_today'] }}</div>
                <div class="metric-foot">Goods receipt yang sudah diposting hari ini.</div>
            </article>
        </section>

        <section class="dashboard-grid">
            <div class="surface-card">
                <div class="surface-head">
                    <div>
                        <h3 class="surface-title">Supplier Paling Perlu Follow Up</h3>
                        <div class="surface-subtitle">Diurutkan dari item terlambat terbanyak.</div>
                    </div>
                </div>
                <div class="attention-list">
                    @forelse($supplierDelay as $index => $row)
                        <div class="attention-item">
                            <div class="attention-rank">{{ $index + 1 }}</div>
                            <div>
                                <div class="attention-name">{{ $row->supplier_name }}</div>
                                <div class="attention-meta">
                                    {{ $row->late_po_count }} PO terdampak
                                    @if ($row->oldest_late_etd)
                                        • ETD tertua {{ \Carbon\Carbon::parse($row->oldest_late_etd)->format('d-m-Y') }}
                                    @endif
                                </div>
                            </div>
                            <div class="attention-badge">{{ $row->late_item_count }} item</div>
                        </div>
                    @empty
                        <div class="text-muted px-3 pb-3">Belum ada supplier yang terlambat.</div>
                    @endforelse
                </div>
            </div>

            <div class="surface-card">
                <div class="surface-head">
                    <div>
                        <h3 class="surface-title">Receiving Terbaru</h3>
                        <div class="surface-subtitle">Dokumen GR yang baru diposting.</div>
                    </div>
                </div>
                <div class="receipt-list">
                    @forelse($recentReceivings as $row)
                        <a href="{{ route('receiving.show', $row->id) }}" class="receipt-item">
                            <div class="receipt-top">
                                <div class="receipt-number">{{ $row->gr_number }}</div>
                                <div class="receipt-date">{{ \Carbon\Carbon::parse($row->receipt_date)->format('d-m-Y') }}</div>
                            </div>
                            <div class="receipt-meta">PO {{ $row->po_number }}</div>
                            <div class="receipt-meta">{{ $row->supplier_name }}</div>
                        </a>
                    @empty
                        <div class="text-muted px-3 pb-3">Belum ada data receiving.</div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="surface-card">
            <div class="surface-head">
                <div>
                    <h3 class="surface-title">Komposisi Item per PO</h3>
                    <div class="surface-subtitle">Header PO dibaca dari komposisi status item yang aktif.</div>
                </div>
                <a href="{{ route('monitoring') }}" class="btn btn-sm btn-outline-primary">Lihat Monitoring</a>
            </div>
            <div class="summary-table-wrap table-responsive">
                <table class="table table-hover summary-table">
                    <thead>
                        <tr>
                            <th>PO</th>
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
                                <td>
                                    <a href="{{ route('po.show', $summary->po_id) }}" class="summary-po text-decoration-none">
                                        {{ $summary->po_number }}
                                    </a>
                                    <div class="summary-muted">{{ $summary->supplier_name }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ \App\Support\TermCatalog::label('po_status', $summary->po_status, $summary->po_status) }}
                                    </span>
                                </td>
                                <td><span class="qty-chip">{{ $summary->waiting_items }}</span></td>
                                <td><span class="qty-chip">{{ $summary->confirmed_items }}</span></td>
                                <td><span class="qty-chip">{{ $summary->late_items }}</span></td>
                                <td><span class="qty-chip">{{ $summary->partial_items }}</span></td>
                                <td><span class="qty-chip">{{ $summary->closed_items }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Belum ada ringkasan monitoring.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="surface-card">
            <div class="monitor-toolbar">
                <div>
                    <h3 class="surface-title mb-1">Item Yang Sedang Berjalan</h3>
                    <div class="surface-subtitle">Cari cepat item outstanding tanpa masuk ke halaman monitoring penuh.</div>
                </div>
                <a href="{{ route('monitoring') }}" class="btn btn-sm btn-primary">Halaman Monitoring Lengkap</a>
            </div>
            <div class="monitor-toolbar pt-0">
                <div class="monitor-filters">
                    <input type="text" id="searchInput" class="form-control form-control-sm"
                        placeholder="Cari PO, item, atau supplier">
                    <select id="statusItemFilter" class="form-select form-select-sm">
                        <option value="">Semua Status Item</option>
                        @foreach (\App\Support\TermCatalog::options('po_item_status', ['Closed', 'Partial', 'Waiting', 'Late', 'Confirmed']) as $status => $label)
                            <option value="{{ $status }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <select id="statusEtdFilter" class="form-select form-select-sm">
                        <option value="">Semua Status ETD</option>
                        <option value="On-Time">On-Time</option>
                        <option value="At-Risk">At-Risk</option>
                        <option value="N/A">N/A</option>
                    </select>
                </div>
            </div>
            <div class="monitor-wrap table-responsive">
                <table class="table table-hover monitor-table" id="itemMonitoringTable">
                    <thead>
                        <tr>
                            <th>Dokumen</th>
                            <th>Status Item</th>
                            <th>Status ETD</th>
                            <th>Keterangan</th>
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
                                    <a href="{{ route('po.show', $item->po_id) }}" class="item-main text-decoration-none">
                                        {{ $item->po_number }}
                                    </a>
                                    <div class="item-sub">{{ $item->item_code }} - {{ $item->item_name }}</div>
                                    <div class="item-sub">{{ $item->supplier_name }}</div>
                                </td>
                                <td>
                                    <span
                                        class="badge {{ match ($item->monitoring_status) {
                                            'Closed' => 'bg-success',
                                            'Partial', 'Confirmed', 'Waiting' => 'bg-warning text-dark',
                                            'Late', 'Cancelled' => 'bg-danger',
                                            default => 'bg-secondary',
                                        } }}">
                                        {{ \App\Support\TermCatalog::label('po_item_status', $item->monitoring_status, $item->monitoring_status) }}
                                    </span>
                                </td>
                                <td>
                                    @if ($etdStatus === 'At-Risk')
                                        <span class="badge bg-danger">At-Risk</span>
                                    @elseif ($etdStatus === 'On-Time')
                                        <span class="badge bg-success">On-Time</span>
                                    @else
                                        <span class="badge bg-secondary">N/A</span>
                                    @endif
                                    <div class="item-sub mt-1">
                                        {{ $item->etd_date ? \Carbon\Carbon::parse($item->etd_date)->format('d-m-Y') : '-' }}
                                    </div>
                                </td>
                                <td>{{ $item->monitoring_note }}</td>
                                <td>{{ \App\Support\NumberFormatter::trim($item->outstanding_qty) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Tidak ada item monitoring.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
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

                    row.style.display = matchesSearch && matchesStatusItem && matchesStatusEtd ? '' : 'none';
                });
            }

            searchInput.addEventListener('input', filterTable);
            statusItemFilter.addEventListener('change', filterTable);
            statusEtdFilter.addEventListener('change', filterTable);
        });
    </script>
@endsection
