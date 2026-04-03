@extends('layouts.erp')

@php($title = 'Supplier Performance')
@php($header = 'Supplier Performance')
@php($headerSubtitle = 'Scorecard supplier untuk membaca OTIF, delay item, dan kecepatan receiving dari flow aktif.')

@section('content')
    <div class="page-shell">
        <section class="summary-chips">
            <div class="summary-chip">
                <div class="summary-chip-label">Supplier</div>
                <div class="summary-chip-value">{{ $performanceMetrics['supplier_count'] }}</div>
            </div>
            <div class="summary-chip">
                <div class="summary-chip-label">Received Items</div>
                <div class="summary-chip-value">{{ $performanceMetrics['received_items'] }}</div>
            </div>
            <div class="summary-chip">
                <div class="summary-chip-label">OTIF%</div>
                <div class="summary-chip-value">{{ $performanceMetrics['overall_otif_percent'] }}</div>
            </div>
            <div class="summary-chip">
                <div class="summary-chip-label">Avg Ship to GR</div>
                <div class="summary-chip-value">{{ $performanceMetrics['avg_shipment_to_receiving_days'] }}d</div>
            </div>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Filter Supplier Performance</h3>
                    <div class="ui-surface-subtitle">Gunakan supplier dan periode PO untuk membaca performa dari shipment sampai receiving.</div>
                </div>
            </div>

            <form method="GET" class="filter-grid">
                <div class="span-4">
                    <label class="field-label">Supplier</label>
                    <select name="supplier_id" class="form-control form-control-sm">
                        <option value="">Semua Supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected($supplierId === (int) $supplier->id)>{{ $supplier->supplier_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="span-3">
                    <label class="field-label">PO Dari</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control form-control-sm">
                </div>
                <div class="span-3">
                    <label class="field-label">PO Sampai</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control form-control-sm">
                </div>
                <div class="span-1"><button class="btn btn-primary btn-sm w-100">Apply</button></div>
                <div class="span-1"><a href="{{ route('supplier-performance.index') }}" class="btn btn-light btn-sm w-100">Reset</a></div>
            </form>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Supplier Performance Scorecard</h3>
                    <div class="ui-surface-subtitle">OTIF dihitung dari item yang sudah diterima terhadap item yang selesai tepat waktu dan in full.</div>
                </div>
            </div>

            <div class="table-wrap table-responsive">
                <table class="table table-hover ui-table">
                    <thead>
                        <tr>
                            <th>Supplier</th>
                            <th>Total PO</th>
                            <th>Total Item</th>
                            <th>Received</th>
                            <th>Closed</th>
                            <th>OTIF%</th>
                            <th>Delayed Open</th>
                            <th>Delay Rate</th>
                            <th>Avg Ship to GR</th>
                            <th>Nearest Open ETD</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($supplierScorecard as $row)
                            <tr>
                                <td><div class="doc-number">{{ $row->supplier_name }}</div></td>
                                <td>{{ $row->total_po }}</td>
                                <td>{{ $row->total_items }}</td>
                                <td>{{ $row->received_items }}</td>
                                <td>{{ $row->closed_items }}</td>
                                <td>
                                    <span class="health-badge {{ $row->otif_percent >= 85 ? 'green' : ($row->otif_percent >= 60 ? 'yellow' : 'red') }}">
                                        {{ $row->otif_percent }}%
                                    </span>
                                </td>
                                <td>{{ $row->delayed_open_items }}</td>
                                <td>{{ $row->delay_rate }}%</td>
                                <td>{{ $row->avg_shipment_to_receiving_days }}</td>
                                <td>{{ $row->nearest_open_etd ? \Carbon\Carbon::parse($row->nearest_open_etd)->format('d-m-Y') : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">Belum ada data supplier performance pada filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="secondary-grid">
            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Top Delayed Suppliers</h3>
                        <div class="ui-surface-subtitle">Prioritas follow up supplier dengan item open terlambat terbanyak.</div>
                    </div>
                </div>

                <div class="ui-surface-body">
                    <div class="stack-list">
                        @forelse ($topDelayedSuppliers as $row)
                            <div class="soft-alert">
                                <div class="doc-number">{{ $row->supplier_name }}</div>
                                <div class="doc-meta">Delayed open {{ $row->delayed_open_items }} item | Delay rate {{ $row->delay_rate }}%</div>
                                <div class="doc-meta">Nearest open ETD {{ $row->nearest_open_etd ? \Carbon\Carbon::parse($row->nearest_open_etd)->format('d-m-Y') : '-' }}</div>
                            </div>
                        @empty
                            <div class="text-muted">Tidak ada supplier yang sedang terlambat.</div>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Best OTIF Suppliers</h3>
                        <div class="ui-surface-subtitle">Supplier dengan performa penerimaan tepat waktu paling baik pada periode ini.</div>
                    </div>
                </div>

                <div class="ui-surface-body">
                    <div class="stack-list">
                        @forelse ($bestOtiFSuppliers as $row)
                            <div class="soft-alert">
                                <div class="doc-number">{{ $row->supplier_name }}</div>
                                <div class="doc-meta">OTIF {{ $row->otif_percent }}% | Received {{ $row->received_items }} item | Closed {{ $row->closed_items }} item</div>
                                <div class="doc-meta">Avg ship to GR {{ $row->avg_shipment_to_receiving_days }} hari</div>
                            </div>
                        @empty
                            <div class="text-muted">Belum ada data item received untuk menghitung OTIF.</div>
                        @endforelse
                    </div>
                </div>
            </section>
        </section>
    </div>

    <style>
        .secondary-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .stack-list {
            display: grid;
            gap: .75rem;
        }

        @media (max-width: 991.98px) {
            .secondary-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection
