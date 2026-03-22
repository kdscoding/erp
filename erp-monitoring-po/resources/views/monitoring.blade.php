@extends('layouts.erp')

@php($title = 'Monitoring Item')
@php($header = 'Monitoring Item')

@section('content')
    <style>
        .monitor-shell {
            display: grid;
            gap: 1rem;
        }

        .monitor-hero,
        .monitor-surface {
            border: 1px solid rgba(111, 150, 40, .12);
            border-radius: 18px;
            background: rgba(255, 255, 255, .94);
            box-shadow: 0 14px 32px rgba(111, 150, 40, .05);
        }

        .monitor-hero {
            padding: 1rem 1.1rem;
            background:
                radial-gradient(circle at top right, rgba(241, 217, 59, .24), transparent 30%),
                linear-gradient(135deg, rgba(255, 255, 255, .96), rgba(245, 249, 221, .96));
        }

        .monitor-hero-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: #314216;
            margin-bottom: .25rem;
        }

        .monitor-hero-copy {
            color: #6f7d52;
            margin-bottom: 0;
            font-size: .88rem;
        }

        .mini-stat-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .75rem;
        }

        .mini-stat {
            padding: .85rem .95rem;
            border-radius: 16px;
            border: 1px solid rgba(111, 150, 40, .1);
            background: rgba(255, 255, 255, .8);
        }

        .mini-stat-label {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #7a8660;
            margin-bottom: .25rem;
        }

        .mini-stat-value {
            font-size: 1.45rem;
            font-weight: 800;
            color: #314216;
            line-height: 1;
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

        .summary-wrap,
        .table-wrap {
            padding: 1rem;
        }

        .summary-table,
        .monitor-table {
            margin-bottom: 0;
        }

        .summary-table thead th,
        .monitor-table thead th {
            font-size: .69rem;
            letter-spacing: .08em;
        }

        .summary-po,
        .item-main {
            font-weight: 700;
            color: #314216;
        }

        .summary-muted,
        .item-sub {
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

        .table-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .85rem;
            flex-wrap: wrap;
            padding: 1rem 1rem 0;
        }

        .table-filters {
            display: flex;
            gap: .6rem;
            flex-wrap: wrap;
            width: 100%;
        }

        .table-filters .form-control,
        .table-filters .form-select {
            max-width: 220px;
        }

        @media (max-width: 991.98px) {
            .mini-stat-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            .mini-stat-grid {
                grid-template-columns: 1fr;
            }

            .table-filters .form-control,
            .table-filters .form-select {
                max-width: 100%;
                width: 100%;
            }
        }
    </style>

    @php($waitingTotal = collect($poMonitoringSummary)->sum('waiting_items'))
    @php($confirmedTotal = collect($poMonitoringSummary)->sum('confirmed_items'))
    @php($lateTotal = collect($poMonitoringSummary)->sum('late_items'))
    @php($partialTotal = collect($poMonitoringSummary)->sum('partial_items'))

    <div class="monitor-shell">
        <section class="monitor-hero">
            <div class="row g-3 align-items-end">
                <div class="col-lg-5">
                    <div class="monitor-hero-title">Satu layar untuk baca komposisi item dan prioritas follow up.</div>
                    <p class="monitor-hero-copy">Gunakan ringkasan per PO di atas, lalu telusuri item aktif di tabel bawah.</p>
                </div>
                <div class="col-lg-7">
                    <div class="mini-stat-grid">
                        <div class="mini-stat">
                            <div class="mini-stat-label">Waiting</div>
                            <div class="mini-stat-value">{{ $waitingTotal }}</div>
                        </div>
                        <div class="mini-stat">
                            <div class="mini-stat-label">Confirmed</div>
                            <div class="mini-stat-value">{{ $confirmedTotal }}</div>
                        </div>
                        <div class="mini-stat">
                            <div class="mini-stat-label">Late</div>
                            <div class="mini-stat-value">{{ $lateTotal }}</div>
                        </div>
                        <div class="mini-stat">
                            <div class="mini-stat-label">Partial</div>
                            <div class="mini-stat-value">{{ $partialTotal }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="monitor-surface">
            <div class="surface-head">
                <div>
                    <h3 class="surface-title">Komposisi Item per PO</h3>
                    <div class="surface-subtitle">Status header PO dibaca dari komposisi item aktif.</div>
                </div>
            </div>
            <div class="summary-wrap table-responsive">
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
                                    <a href="{{ route('po.show', $summary->po_id) }}" class="summary-po text-decoration-none">{{ $summary->po_number }}</a>
                                    <div class="summary-muted">{{ $summary->supplier_name }}</div>
                                </td>
                                <td><span class="badge bg-light text-dark">{{ \App\Support\TermCatalog::label('po_status', $summary->po_status, $summary->po_status) }}</span></td>
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

        <section class="monitor-surface">
            <div class="table-toolbar">
                <div>
                    <h3 class="surface-title mb-1">Item Aktif</h3>
                    <div class="surface-subtitle">Cari cepat item yang masih outstanding atau perlu tindak lanjut.</div>
                </div>
                <div class="table-filters">
                    <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Cari PO, item, atau supplier...">
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
            <div class="table-wrap table-responsive">
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
                            @php($etdStatus = $item->etd_date ? (\Carbon\Carbon::parse($item->etd_date)->isBefore(now()->timezone('Asia/Jakarta')) ? 'At-Risk' : 'On-Time') : 'N/A')
                            <tr data-status-item="{{ $item->monitoring_status }}" data-status-etd="{{ $etdStatus }}"
                                data-search="{{ strtolower($item->po_number . ' ' . $item->item_code . ' ' . $item->item_name . ' ' . $item->supplier_name) }}">
                                <td>
                                    <a href="{{ route('po.show', $item->po_id) }}" class="item-main text-decoration-none">{{ $item->po_number }}</a>
                                    <div class="item-sub">{{ $item->item_code }} - {{ $item->item_name }}</div>
                                    <div class="item-sub">{{ $item->supplier_name }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ match ($item->monitoring_status) {
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
                                    <div class="item-sub mt-1">{{ $item->etd_date ? \Carbon\Carbon::parse($item->etd_date)->format('d-m-Y') : '-' }}</div>
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
