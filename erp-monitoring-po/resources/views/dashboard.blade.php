@extends('layouts.erp')

@php($title = 'Dashboard Lemon')
@php($header = 'Dashboard Monitoring PO')
@php($headerSubtitle = 'Ringkasan operasional untuk membaca risiko, aliran dokumen, dan prioritas tindak lanjut.')

@section('content')
    @php($etdTotal = max(array_sum($etdHealth), 1))
    @php($atRiskPercent = round(($etdHealth['At-Risk'] / $etdTotal) * 100, 1))
    @php($onTimePercent = round(($etdHealth['On-Time'] / $etdTotal) * 100, 1))

    <style>
        .dash{display:grid;gap:1rem}.box{background:rgba(255,255,255,.96);border:1px solid rgba(111,150,40,.12);border-radius:20px;box-shadow:0 14px 32px rgba(111,150,40,.05)}.head{display:flex;justify-content:space-between;align-items:center;gap:.75rem;padding:1rem 1rem 0}.head h3{margin:0;font-size:1rem;color:#314216}.sub{font-size:.8rem;color:#7a8660}.body{padding:1rem}
        .hero{padding:1.2rem;background:radial-gradient(circle at top right,rgba(241,217,59,.34),transparent 24%),radial-gradient(circle at bottom left,rgba(158,203,60,.18),transparent 24%),linear-gradient(135deg,rgba(255,255,255,.98),rgba(244,248,219,.98))}.eyebrow{display:inline-flex;gap:.45rem;padding:.35rem .7rem;border-radius:999px;font-size:.72rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#5f7331;border:1px solid rgba(111,150,40,.16);background:rgba(255,255,255,.7)}.hero h2{margin:.75rem 0 .45rem;font-size:1.55rem;line-height:1.1;color:#2d3d15}.muted,.meta,.note{font-size:.82rem;color:#728058}
        .filters{display:grid;grid-template-columns:2fr 1fr 1fr auto auto;gap:.75rem;align-items:end;margin-top:1rem}.chips{display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.85rem}.chip{padding:.32rem .65rem;border-radius:999px;background:#f7f9e7;border:1px solid #dbe5b0;font-size:.76rem;color:#62743a}
        .kpis,.main,.secondary{display:grid;gap:1rem}.kpis{grid-template-columns:repeat(4,minmax(0,1fr))}.main{grid-template-columns:1.15fr 1fr .85fr}.secondary{grid-template-columns:1fr 1fr}.kpi{position:relative;overflow:hidden;padding:1rem;border-radius:18px;border:1px solid rgba(111,150,40,.12);box-shadow:0 12px 24px rgba(111,150,40,.04)}.kpi:after{content:"";position:absolute;right:-18px;bottom:-24px;width:80px;height:80px;border-radius:999px;background:rgba(255,255,255,.35)}.k1{background:linear-gradient(135deg,#fff,#eef7d2)}.k2{background:linear-gradient(135deg,#fffef8,#fff0d5)}.k3{background:linear-gradient(135deg,#fff,#e6f4d8)}.k4{background:linear-gradient(135deg,#fff,#edf7cf)}
        .label{font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;color:#7a8660}.value{font-size:1.8rem;font-weight:800;color:#314216;line-height:1;margin-top:.25rem}.chart{position:relative;min-height:280px}.legend{display:grid;gap:.55rem;margin-top:.8rem}.legend-row{display:grid;grid-template-columns:auto 1fr auto;gap:.6rem;align-items:center;font-size:.82rem;color:#647248}.sw{width:12px;height:12px;border-radius:999px}.lv{font-weight:800;color:#314216}
        .meter{height:16px;display:flex;overflow:hidden;border-radius:999px;background:#edf2d5;margin-bottom:1rem}.risk{background:linear-gradient(90deg,#efaa8d,#d66848)}.safe{background:linear-gradient(90deg,#bfd86d,#86b83d)}.stack,.list{display:grid;gap:.75rem}.item,.list-card,.receipt{padding:.9rem;border-radius:16px;border:1px solid rgba(111,150,40,.1);background:linear-gradient(135deg,rgba(255,255,255,.98),rgba(247,248,234,.96))}.receipt{text-decoration:none;display:block}.top{display:flex;justify-content:space-between;gap:.75rem;align-items:flex-start}.rank{width:30px;height:30px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;font-size:.82rem;font-weight:800;background:#f4f7d8;color:#5d7425;flex:0 0 auto}.pill{padding:.38rem .65rem;border-radius:999px;background:#fff1eb;color:#b04835;font-size:.8rem;font-weight:800;white-space:nowrap}.ttl{font-weight:700;color:#314216}.table-wrap{padding:1rem}.qty{display:inline-flex;min-width:34px;justify-content:center;padding:.18rem .5rem;border-radius:999px;font-size:.76rem;font-weight:700;background:#f4f7d8;color:#5d7425}
        @media (max-width:1199.98px){.filters,.main,.secondary{grid-template-columns:1fr}.kpis{grid-template-columns:repeat(2,minmax(0,1fr))}}@media (max-width:767.98px){.kpis{grid-template-columns:1fr}.top{flex-direction:column}.hero h2{font-size:1.3rem}}
    </style>

    <div class="dash">
        <section class="box hero">
            <div class="eyebrow"><i class="fas fa-chart-line"></i> Procurement Control Room</div>
            <h2>Dashboard dibuat ulang untuk satu tujuan: tahu apa yang perlu ditindak lebih dulu tanpa membaca terlalu banyak layar.</h2>
            <div class="muted">Gunakan filter untuk mempersempit konteks, lalu baca KPI, chart, dan daftar prioritas dari atas ke bawah.</div>

            <form method="GET" class="filters">
                <div>
                    <label class="label">Supplier</label>
                    <select name="supplier_id" class="form-control form-control-sm">
                        <option value="">Semua Supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected($supplierId === (int) $supplier->id)>{{ $supplier->supplier_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">PO Dari</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control form-control-sm">
                </div>
                <div>
                    <label class="label">PO Sampai</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control form-control-sm">
                </div>
                <div><button class="btn btn-primary btn-sm w-100">Apply</button></div>
                <div><a href="{{ route('dashboard') }}" class="btn btn-light btn-sm w-100">Reset</a></div>
            </form>

            <div class="chips">
                <span class="chip">Supplier: {{ $supplierId ? $suppliers->firstWhere('id', $supplierId)?->supplier_name : 'Semua' }}</span>
                <span class="chip">PO Dari: {{ $dateFrom ?: 'Awal data' }}</span>
                <span class="chip">PO Sampai: {{ $dateTo ?: 'Semua' }}</span>
            </div>
        </section>

        <section class="kpis">
            <article class="kpi k1"><div class="label">Open PO</div><div class="value">{{ $metrics['open_po'] }}</div><div class="note">Dokumen aktif yang masih berjalan.</div></article>
            <article class="kpi k2"><div class="label">Late PO</div><div class="value">{{ $metrics['late_po'] }}</div><div class="note">Header PO yang sudah masuk kondisi terlambat.</div></article>
            <article class="kpi k3"><div class="label">Shipment Hari Ini</div><div class="value">{{ $metrics['shipped_today'] }}</div><div class="note">Dokumen shipment yang diproses hari ini.</div></article>
            <article class="kpi k4"><div class="label">Receiving Hari Ini</div><div class="value">{{ $metrics['received_today'] }}</div><div class="note">GR yang sudah diposting. At-risk item: {{ $metrics['at_risk_items'] }}</div></article>
        </section>

        <section class="main">
            <article class="box">
                <div class="head"><div><h3>Komposisi Status Item</h3><div class="sub">Distribusi monitoring agar beban kerja langsung terlihat.</div></div></div>
                <div class="body">
                    <div class="chart"><canvas id="statusBreakdownChart"></canvas></div>
                    <div class="legend">
                        @foreach ($statusBreakdown as $label => $value)
                            <div class="legend-row">
                                <span class="sw" style="background: {{ match ($label) {
                                    'Waiting' => '#f1d93b',
                                    'Confirmed' => '#9ecb3c',
                                    'Late' => '#d66848',
                                    'Partial' => '#7aa4d8',
                                    default => '#7f8b62',
                                } }}"></span>
                                <span>{{ $label }}</span>
                                <span class="lv">{{ $value }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </article>

            <article class="box">
                <div class="head"><div><h3>Supplier Risk Compare</h3><div class="sub">Bandingkan jumlah item terlambat dengan jumlah PO terdampak.</div></div></div>
                <div class="body"><div class="chart"><canvas id="supplierRiskChart"></canvas></div></div>
            </article>

            <article class="box">
                <div class="head"><div><h3>ETD Health</h3><div class="sub">Ringkas, langsung, dan fokus ke kondisi pengiriman.</div></div></div>
                <div class="body">
                    <div class="meter"><div class="risk" style="width: {{ $atRiskPercent }}%"></div><div class="safe" style="width: {{ $onTimePercent }}%"></div></div>
                    <div class="stack">
                        <div class="item"><div class="ttl">At-Risk</div><div class="meta">{{ $etdHealth['At-Risk'] }} item | {{ $atRiskPercent }}%</div></div>
                        <div class="item"><div class="ttl">On-Time</div><div class="meta">{{ $etdHealth['On-Time'] }} item | {{ $onTimePercent }}%</div></div>
                        @forelse($onTimeItems->take(2) as $item)
                            <div class="item">
                                <div class="ttl">{{ $item->po_number }} - {{ $item->item_code }}</div>
                                <div class="meta">{{ $item->supplier_name }}</div>
                                <div class="meta">ETD {{ \Carbon\Carbon::parse($item->etd_date)->format('d-m-Y') }} | Outstanding {{ \App\Support\NumberFormatter::trim($item->outstanding_qty) }}</div>
                            </div>
                        @empty
                            <div class="text-muted">Belum ada item on-time yang terjadwal.</div>
                        @endforelse
                    </div>
                </div>
            </article>
        </section>

        <section class="secondary">
            <article class="box">
                <div class="head"><div><h3>Supplier Paling Perlu Follow Up</h3><div class="sub">Urut dari item terlambat terbanyak.</div></div></div>
                <div class="body"><div class="list">
                    @forelse($supplierDelay as $index => $row)
                        <div class="list-card">
                            <div class="top">
                                <div class="d-flex align-items-start" style="gap:.7rem;">
                                    <div class="rank">{{ $index + 1 }}</div>
                                    <div>
                                        <div class="ttl">{{ $row->supplier_name }}</div>
                                        <div class="meta">{{ $row->late_po_count }} PO terdampak</div>
                                        <div class="meta">@if ($row->oldest_late_etd) ETD tertua {{ \Carbon\Carbon::parse($row->oldest_late_etd)->format('d-m-Y') }} @else Belum ada ETD referensi @endif</div>
                                    </div>
                                </div>
                                <div class="pill">{{ $row->late_item_count }} item</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted">Belum ada supplier yang terlambat.</div>
                    @endforelse
                </div></div>
            </article>

            <article class="box">
                <div class="head"><div><h3>PO Terdekat Ke ETA</h3><div class="sub">Prioritas follow up supplier dan kesiapan shipment.</div></div></div>
                <div class="body"><div class="list">
                    @forelse($openPoList->take(5) as $row)
                        <div class="list-card">
                            <div class="top">
                                <div>
                                    <div class="ttl">{{ $row->po_number }}</div>
                                    <div class="meta">{{ $row->supplier_name }}</div>
                                    <div class="meta">ETA {{ $row->po_eta_date ? \Carbon\Carbon::parse($row->po_eta_date)->format('d-m-Y') : '-' }}</div>
                                    <div class="meta">Waiting {{ $row->waiting_items }} | Confirmed {{ $row->confirmed_items }} | Late {{ $row->late_items }}</div>
                                </div>
                                <div class="pill">{{ $row->partial_items }} partial</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted">Belum ada PO aktif.</div>
                    @endforelse
                </div></div>
            </article>
        </section>

        <section class="secondary">
            <article class="box">
                <div class="head"><div><h3>Receiving Terbaru</h3><div class="sub">Dokumen GR yang baru diposting untuk membaca pergerakan akhir proses.</div></div></div>
                <div class="body"><div class="list">
                    @forelse($recentReceivings as $row)
                        <a href="{{ route('receiving.show', $row->id) }}" class="receipt">
                            <div class="top"><div class="ttl">{{ $row->gr_number }}</div><div class="meta">{{ \Carbon\Carbon::parse($row->receipt_date)->format('d-m-Y') }}</div></div>
                            <div class="meta">PO {{ $row->po_number }}</div>
                            <div class="meta">{{ $row->supplier_name }}</div>
                        </a>
                    @empty
                        <div class="text-muted">Belum ada data receiving.</div>
                    @endforelse
                </div></div>
            </article>

            <article class="box">
                <div class="head"><div><h3>Item Prioritas</h3><div class="sub">Snapshot cepat item yang paling perlu dilihat dulu.</div></div><a href="{{ route('monitoring') }}" class="btn btn-sm btn-primary">Monitoring Lengkap</a></div>
                <div class="table-wrap table-responsive">
                    <table class="table table-hover">
                        <thead><tr><th>Dokumen</th><th>Status</th><th>ETD</th><th>Catatan</th><th>Outstanding</th></tr></thead>
                        <tbody>
                            @forelse($itemMonitoringList as $item)
                                @php($etdStatus = $item->etd_date ? (\Carbon\Carbon::parse($item->etd_date)->isBefore(now()) ? 'At-Risk' : 'On-Time') : 'N/A')
                                <tr>
                                    <td><a href="{{ route('po.show', $item->po_id) }}" class="ttl text-decoration-none">{{ $item->po_number }}</a><div class="meta">{{ $item->item_code }} - {{ $item->item_name }}</div><div class="meta">{{ $item->supplier_name }}</div></td>
                                    <td><span class="badge {{ match ($item->monitoring_status) {'Closed' => 'bg-success','Partial', 'Confirmed', 'Waiting' => 'bg-warning text-dark','Late', 'Cancelled' => 'bg-danger',default => 'bg-secondary',} }}">{{ \App\Support\TermCatalog::label('po_item_status', $item->monitoring_status, $item->monitoring_status) }}</span></td>
                                    <td>@if ($etdStatus === 'At-Risk')<span class="badge bg-danger">At-Risk</span>@elseif ($etdStatus === 'On-Time')<span class="badge bg-success">On-Time</span>@else<span class="badge bg-secondary">N/A</span>@endif<div class="meta mt-1">{{ $item->etd_date ? \Carbon\Carbon::parse($item->etd_date)->format('d-m-Y') : '-' }}</div></td>
                                    <td>{{ $item->monitoring_note }}</td>
                                    <td>{{ \App\Support\NumberFormatter::trim($item->outstanding_qty) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">Tidak ada item monitoring.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </section>

        <section class="box">
            <div class="head"><div><h3>Komposisi Item per PO</h3><div class="sub">Ringkasan yang tetap informatif untuk membaca struktur item aktif per dokumen.</div></div><a href="{{ route('monitoring') }}" class="btn btn-sm btn-outline-primary">Lihat Monitoring</a></div>
            <div class="table-wrap table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>PO</th><th>Header</th><th>Waiting</th><th>Confirmed</th><th>Late</th><th>Partial</th><th>Closed</th></tr></thead>
                    <tbody>
                        @forelse($poMonitoringSummary as $summary)
                            <tr>
                                <td><a href="{{ route('po.show', $summary->po_id) }}" class="ttl text-decoration-none">{{ $summary->po_number }}</a><div class="meta">{{ $summary->supplier_name }}</div></td>
                                <td><span class="badge bg-light text-dark">{{ \App\Support\TermCatalog::label('po_status', $summary->po_status, $summary->po_status) }}</span></td>
                                <td><span class="qty">{{ $summary->waiting_items }}</span></td>
                                <td><span class="qty">{{ $summary->confirmed_items }}</span></td>
                                <td><span class="qty">{{ $summary->late_items }}</span></td>
                                <td><span class="qty">{{ $summary->partial_items }}</span></td>
                                <td><span class="qty">{{ $summary->closed_items }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">Belum ada ringkasan monitoring.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusCanvas = document.getElementById('statusBreakdownChart');
            const supplierCanvas = document.getElementById('supplierRiskChart');
            if (statusCanvas) {
                new Chart(statusCanvas, {
                    type: 'doughnut',
                    data: { labels: @json(array_keys($statusBreakdown)), datasets: [{ data: @json(array_values($statusBreakdown)), backgroundColor: ['#f1d93b', '#9ecb3c', '#d66848', '#7aa4d8', '#7f8b62'], borderColor: '#fff', borderWidth: 4 }] },
                    options: { maintainAspectRatio: false, cutout: '64%', plugins: { legend: { display: false } } }
                });
            }
            if (supplierCanvas) {
                new Chart(supplierCanvas, {
                    type: 'bar',
                    data: { labels: @json($supplierRiskChart['labels']), datasets: [{ label: 'Late Item', data: @json($supplierRiskChart['late_items']), backgroundColor: '#d66848', borderRadius: 8, barThickness: 18 }, { label: 'Late PO', data: @json($supplierRiskChart['late_pos']), backgroundColor: '#9ecb3c', borderRadius: 8, barThickness: 18 }] },
                    options: { indexAxis: 'y', maintainAspectRatio: false, scales: { x: { beginAtZero: true, ticks: { precision: 0 } }, y: { grid: { display: false } } }, plugins: { legend: { position: 'top', align: 'start' } } }
                });
            }
        });
    </script>
@endsection
