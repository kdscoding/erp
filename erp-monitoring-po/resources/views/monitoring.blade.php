@extends('layouts.erp')

@php($title = 'Monitoring Item')
@php($header = 'Monitoring Item')
@php($headerSubtitle = 'Baca komposisi item per PO dan prioritas follow up dalam satu layar kerja.')

@section('content')
    @php($waitingTotal = collect($poMonitoringSummary)->sum('waiting_items'))
    @php($confirmedTotal = collect($poMonitoringSummary)->sum('confirmed_items'))
    @php($lateTotal = collect($poMonitoringSummary)->sum('late_items'))
    @php($partialTotal = collect($poMonitoringSummary)->sum('partial_items'))

    <div class="page-shell">
        <section class="page-head">
            <div class="page-head-main">
                <h2 class="page-section-title">Item Monitoring</h2>
                <p class="page-section-subtitle">Gunakan ringkasan per PO di atas, lalu telusuri item aktif di tabel bawah tanpa hero atau CSS lokal.</p>
            </div>
        </section>

        <section class="summary-chips">
            <div class="summary-chip"><div class="summary-chip-label">Waiting</div><div class="summary-chip-value">{{ $waitingTotal }}</div></div>
            <div class="summary-chip"><div class="summary-chip-label">Confirmed</div><div class="summary-chip-value">{{ $confirmedTotal }}</div></div>
            <div class="summary-chip"><div class="summary-chip-label">Late</div><div class="summary-chip-value">{{ $lateTotal }}</div></div>
            <div class="summary-chip"><div class="summary-chip-label">Partial</div><div class="summary-chip-value">{{ $partialTotal }}</div></div>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Komposisi Item per PO</h3>
                    <div class="ui-surface-subtitle">Status header PO dibaca dari komposisi item aktif.</div>
                </div>
            </div>
            <div class="table-wrap table-responsive">
                <table class="table table-hover ui-table">
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
                                    <a href="{{ route('po.show', $summary->po_id) }}" class="doc-number text-decoration-none">{{ $summary->po_number }}</a>
                                    <div class="doc-meta">{{ $summary->supplier_name }}</div>
                                </td>
                                <td><span class="badge bg-light text-dark">{{ \App\Support\TermCatalog::label('po_status', $summary->po_status, $summary->po_status) }}</span></td>
                                <td>{{ $summary->waiting_items }}</td>
                                <td>{{ $summary->confirmed_items }}</td>
                                <td>{{ $summary->late_items }}</td>
                                <td>{{ $summary->partial_items }}</td>
                                <td>{{ $summary->closed_items }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">Belum ada ringkasan monitoring.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Item Aktif</h3>
                    <div class="ui-surface-subtitle">Cari cepat item yang masih outstanding atau perlu tindak lanjut.</div>
                </div>
            </div>
            <div class="filter-grid">
                <div class="span-4">
                    <label class="field-label">Cari</label>
                    <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Cari PO, item, atau supplier...">
                </div>
                <div class="span-3">
                    <label class="field-label">Status Item</label>
                    <select id="statusItemFilter" class="form-control form-control-sm">
                        <option value="">Semua Status Item</option>
                        @foreach (\App\Support\TermCatalog::options('po_item_status', ['Closed', 'Partial', 'Waiting', 'Late', 'Confirmed']) as $status => $label)
                            <option value="{{ $status }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="span-3">
                    <label class="field-label">Status ETD</label>
                    <select id="statusEtdFilter" class="form-control form-control-sm">
                        <option value="">Semua Status ETD</option>
                        <option value="On-Time">On-Time</option>
                        <option value="At-Risk">At-Risk</option>
                        <option value="N/A">N/A</option>
                    </select>
                </div>
            </div>
            <div class="table-wrap table-responsive">
                <table class="table table-hover ui-table" id="itemMonitoringTable">
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
                            <tr data-status-item="{{ $item->monitoring_status }}" data-status-etd="{{ $etdStatus }}" data-search="{{ strtolower($item->po_number . ' ' . $item->item_code . ' ' . $item->item_name . ' ' . $item->supplier_name) }}">
                                <td>
                                    <a href="{{ route('po.show', $item->po_id) }}" class="doc-number text-decoration-none">{{ $item->po_number }}</a>
                                    <div class="doc-meta">{{ $item->item_code }} - {{ $item->item_name }}</div>
                                    <div class="doc-meta">{{ $item->supplier_name }}</div>
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
                                    <div class="doc-meta mt-1">{{ $item->etd_date ? \Carbon\Carbon::parse($item->etd_date)->format('d-m-Y') : '-' }}</div>
                                </td>
                                <td>{{ $item->monitoring_note }}</td>
                                <td>{{ \App\Support\NumberFormatter::trim($item->outstanding_qty) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">Tidak ada item monitoring.</td></tr>
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
