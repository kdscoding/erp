@extends('layouts.erp')

@php($title = 'Comprehensive Item Monitoring')
@php($header = 'Comprehensive Item Monitoring')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Comprehensive Item Monitoring</h3>
            <div class="d-flex gap-2">
                <input type="text" id="searchInput" class="form-control form-control-sm"
                    placeholder="Cari PO, Item, atau Supplier..." style="width: 250px;">
                <select id="statusItemFilter" class="form-select form-select-sm" style="width: 150px;">
                    <option value="">Semua Status Item</option>
                    <option value="Closed">Closed</option>
                    <option value="Partial">Partial</option>
                    <option value="Waiting">Waiting</option>
                    <option value="Late">Late</option>
                    <option value="Confirmed">Confirmed</option>
                </select>
                <select id="statusEtdFilter" class="form-select form-select-sm" style="width: 150px;">
                    <option value="">Semua Status ETD</option>
                    <option value="On-Time">On-Time</option>
                    <option value="At-Risk">At-Risk</option>
                    <option value="N/A">N/A</option>
                </select>
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
                        <tr data-status-item="{{ $item->monitoring_status }}" data-status-etd="{{ $etdStatus }}"
                            data-search="{{ strtolower($item->po_number . ' ' . $item->item_code . ' ' . $item->item_name . ' ' . $item->supplier_name) }}">
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
