@extends('layouts.erp')

@php($title = 'Monitoring Item')
@php($header = 'Monitoring Item')
@php($headerSubtitle = 'Baca komposisi item per PO dan prioritas follow up dalam satu layar kerja.')

@section('content')
    @php($waitingTotal = collect($poMonitoringSummary)->sum('waiting_items'))
    @php($confirmedTotal = collect($poMonitoringSummary)->sum('confirmed_items'))
    @php($lateTotal = collect($poMonitoringSummary)->sum('late_items'))
    @php($partialTotal = collect($poMonitoringSummary)->sum('partial_items'))
    @php($closedTotal = collect($poMonitoringSummary)->sum('closed_items'))
    @php($forceClosedTotal = collect($poMonitoringSummary)->sum('force_closed_items'))

    <div class="page-shell">
        <section class="page-head">
            <div class="page-head-main">
                <h2 class="page-section-title">Item Monitoring</h2>
                <p class="page-section-subtitle">Gunakan halaman ini sebagai ringkasan monitoring. Detail lengkap tetap dibuka dari PO atau lewat export Excel.</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('monitoring.export-excel') }}" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-file-excel"></i> Export Monitoring
                </a>
            </div>
        </section>

        <section class="summary-chips">
            <div class="summary-chip"><div class="summary-chip-label">Waiting</div><div class="summary-chip-value">{{ $waitingTotal }}</div></div>
            <div class="summary-chip"><div class="summary-chip-label">Confirmed</div><div class="summary-chip-value">{{ $confirmedTotal }}</div></div>
            <div class="summary-chip"><div class="summary-chip-label">Late</div><div class="summary-chip-value">{{ $lateTotal }}</div></div>
            <div class="summary-chip"><div class="summary-chip-label">Partial</div><div class="summary-chip-value">{{ $partialTotal }}</div></div>
            <div class="summary-chip"><div class="summary-chip-label">Closed</div><div class="summary-chip-value">{{ $closedTotal }}</div></div>
            <div class="summary-chip"><div class="summary-chip-label">Force Closed</div><div class="summary-chip-value">{{ $forceClosedTotal }}</div></div>
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
                            <th>Force Closed</th>
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
                                <td>{{ $summary->force_closed_items }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted">Belum ada ringkasan monitoring.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">15 Item Prioritas</h3>
                    <div class="ui-surface-subtitle">Ringkasan cepat item yang paling perlu dilihat. Detail lengkap gunakan export monitoring atau buka PO terkait.</div>
                </div>
            </div>
            <div class="ui-surface-body pt-0">
                <div class="soft-alert">
                    Monitoring difokuskan sebagai summary. Saat jumlah PO bertambah, tabel detail penuh lebih aman dibaca lewat export Excel daripada ditumpuk semua di layar.
                </div>
            </div>
            <div class="table-wrap table-responsive">
                <table class="table table-hover ui-table">
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
                            <tr>
                                <td>
                                    <a href="{{ route('po.show', $item->po_id) }}" class="doc-number text-decoration-none">{{ $item->po_number }}</a>
                                    <div class="doc-meta">{{ $item->item_code }} - {{ $item->item_name }}</div>
                                    <div class="doc-meta">{{ $item->supplier_name }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ match ($item->monitoring_status) {
                                        'Closed' => 'bg-success',
                                        'Force Closed' => 'bg-dark text-white',
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
@endsection
