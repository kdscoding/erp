@extends('layouts.erp')

@php($title = 'Dashboard Outstanding PO')
@php($header = 'Dashboard Summary Outstanding')
@php($headerSubtitle = 'Ringkasan operasional untuk membaca outstanding, risiko ETD, dan prioritas tindak lanjut.')

@section('content')
    <style>
        .dash{display:grid;gap:1rem}.box{background:rgba(255,255,255,.96);border:1px solid rgba(111,150,40,.12);border-radius:20px;box-shadow:0 14px 32px rgba(111,150,40,.05)}.head{display:flex;justify-content:space-between;align-items:center;gap:.75rem;padding:1rem 1rem 0}.head h3{margin:0;font-size:1rem;color:#314216}.sub{font-size:.8rem;color:#7a8660}.body{padding:1rem}
        .hero{padding:1.2rem;background:radial-gradient(circle at top right,rgba(241,217,59,.34),transparent 24%),radial-gradient(circle at bottom left,rgba(158,203,60,.18),transparent 24%),linear-gradient(135deg,rgba(255,255,255,.98),rgba(244,248,219,.98))}.eyebrow{display:inline-flex;gap:.45rem;padding:.35rem .7rem;border-radius:999px;font-size:.72rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#5f7331;border:1px solid rgba(111,150,40,.16);background:rgba(255,255,255,.7)}.hero h2{margin:.75rem 0 .45rem;font-size:1.55rem;line-height:1.1;color:#2d3d15}.muted,.meta,.note{font-size:.82rem;color:#728058}
        .filters{display:grid;grid-template-columns:2fr 1fr 1fr auto auto;gap:.75rem;align-items:end;margin-top:1rem}.chips{display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.85rem}.chip{padding:.32rem .65rem;border-radius:999px;background:#f7f9e7;border:1px solid #dbe5b0;font-size:.76rem;color:#62743a}.actions{display:flex;gap:.5rem;flex-wrap:wrap}
        .kpis,.main,.secondary{display:grid;gap:1rem}.kpis{grid-template-columns:repeat(4,minmax(0,1fr))}.main{grid-template-columns:1.15fr 1fr .85fr}.secondary{grid-template-columns:1fr 1fr}.kpi{position:relative;overflow:hidden;padding:1rem;border-radius:18px;border:1px solid rgba(111,150,40,.12);box-shadow:0 12px 24px rgba(111,150,40,.04)}.kpi:after{content:"";position:absolute;right:-18px;bottom:-24px;width:80px;height:80px;border-radius:999px;background:rgba(255,255,255,.35)}.k1{background:linear-gradient(135deg,#fff,#eef7d2)}.k2{background:linear-gradient(135deg,#fffef8,#fff0d5)}.k3{background:linear-gradient(135deg,#fff,#e6f4d8)}.k4{background:linear-gradient(135deg,#fff,#edf7cf)}
        .label{font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;color:#7a8660}.value{font-size:1.8rem;font-weight:800;color:#314216;line-height:1;margin-top:.25rem}.chart{position:relative;min-height:280px}.legend{display:grid;gap:.55rem;margin-top:.8rem}.legend-row{display:grid;grid-template-columns:auto 1fr auto;gap:.6rem;align-items:center;font-size:.82rem;color:#647248}.sw{width:12px;height:12px;border-radius:999px}.lv{font-weight:800;color:#314216}
        .meter{height:16px;display:flex;overflow:hidden;border-radius:999px;background:#edf2d5;margin-bottom:1rem}.risk{background:linear-gradient(90deg,#efaa8d,#d66848)}.safe{background:linear-gradient(90deg,#bfd86d,#86b83d)}.stack,.list{display:grid;gap:.75rem}.item,.list-card,.receipt{padding:.9rem;border-radius:16px;border:1px solid rgba(111,150,40,.1);background:linear-gradient(135deg,rgba(255,255,255,.98),rgba(247,248,234,.96))}.receipt{text-decoration:none;display:block}.top{display:flex;justify-content:space-between;gap:.75rem;align-items:flex-start}.rank{width:30px;height:30px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;font-size:.82rem;font-weight:800;background:#f4f7d8;color:#5d7425;flex:0 0 auto}.pill{padding:.38rem .65rem;border-radius:999px;background:#fff1eb;color:#b04835;font-size:.8rem;font-weight:800;white-space:nowrap}.ttl{font-weight:700;color:#314216}.table-wrap{padding:1rem}.qty{display:inline-flex;min-width:34px;justify-content:center;padding:.18rem .5rem;border-radius:999px;font-size:.76rem;font-weight:700;background:#f4f7d8;color:#5d7425}.health-table td,.health-table th{font-size:.8rem}.health-badge{display:inline-flex;padding:.2rem .5rem;border-radius:999px;font-size:.74rem;font-weight:700}.health-badge.red{background:#fff1eb;color:#b04835}.health-badge.yellow{background:#fff7d9;color:#8b6b12}.health-badge.green{background:#eef7d2;color:#5b7c24}
        .modal-list{display:grid;gap:.75rem}.modal-item{padding:.8rem .9rem;border-radius:14px;border:1px solid #e4eabc;background:#fbfcf3}.modal-kpi{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem}.modal-stat{padding:.8rem .9rem;border-radius:14px;background:#f7f9e7;border:1px solid #dbe5b0}.modal-stat-label{font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;color:#6b7b42}.modal-stat-value{font-size:1.2rem;font-weight:800;color:#314216}.modal-subtable{margin-top:.65rem}.modal-subtable th{font-size:.68rem;text-transform:uppercase;color:#6d7c44}.kpi-actions{position:relative;z-index:1;display:flex;justify-content:flex-end;margin-top:.65rem}
        .modal-summary th,.modal-summary td{font-size:.84rem}.modal-subtable th,.modal-subtable td{font-size:.8rem}.kpi-actions{position:relative;z-index:1;display:flex;justify-content:flex-end;margin-top:.65rem}
        .modal-filters{display:grid;grid-template-columns:2fr 1.2fr 1.2fr 1fr auto;gap:.75rem;align-items:end;margin-bottom:1rem}.filter-result{font-size:.8rem;color:#728058}
        @media (max-width:1199.98px){.filters,.main,.secondary{grid-template-columns:1fr}.kpis{grid-template-columns:repeat(2,minmax(0,1fr))}}@media (max-width:767.98px){.kpis{grid-template-columns:1fr}.top{flex-direction:column}.hero h2{font-size:1.3rem}}
    </style>

    <div class="dash">
        <section class="box hero">
            <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:.75rem;">
                <div>
                    <div class="eyebrow"><i class="fas fa-chart-line"></i> Procurement Control Room</div>
                    <h2>Dashboard dibuat ulang untuk satu tujuan: tahu apa yang perlu ditindak lebih dulu tanpa membaca terlalu banyak layar.</h2>
                    <div class="muted">Gunakan filter untuk mempersempit konteks, lalu baca KPI, chart, dan daftar prioritas dari atas ke bawah.</div>
                </div>
                <div class="actions">
                    <button type="button" class="btn btn-sm btn-light" data-toggle="modal" data-target="#heroDetailModal">Detail Ringkasan</button>
                </div>
            </div>

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
            <article class="kpi k1"><div class="label">Open PO</div><div class="value">{{ $metrics['open_po'] }}</div><div class="note">Dokumen aktif yang masih berjalan.</div><div class="kpi-actions"><button type="button" class="btn btn-sm btn-light" data-toggle="modal" data-target="#openPoKpiModal">Detail</button></div></article>
            <article class="kpi k2"><div class="label">Late PO</div><div class="value">{{ $metrics['late_po'] }}</div><div class="note">Header PO yang sudah masuk kondisi terlambat.</div><div class="kpi-actions"><button type="button" class="btn btn-sm btn-light" data-toggle="modal" data-target="#latePoKpiModal">Detail</button></div></article>
            <article class="kpi k3"><div class="label">Shipment Hari Ini</div><div class="value">{{ $metrics['shipped_today'] }}</div><div class="note">Dokumen shipment yang diproses hari ini.</div><div class="kpi-actions"><button type="button" class="btn btn-sm btn-light" data-toggle="modal" data-target="#shipmentTodayKpiModal">Detail</button></div></article>
            <article class="kpi k4"><div class="label">Receiving Hari Ini</div><div class="value">{{ $metrics['received_today'] }}</div><div class="note">GR yang sudah diposting. At-risk item: {{ $metrics['at_risk_items'] }}</div><div class="kpi-actions"><button type="button" class="btn btn-sm btn-light" data-toggle="modal" data-target="#receivingTodayKpiModal">Detail</button></div></article>
        </section>

        <section class="main">
            <article class="box">
                <div class="head">
                    <div><h3>Komposisi Status Item</h3><div class="sub">Distribusi status outstanding agar beban kerja langsung terlihat.</div></div>
                    <div class="actions"><button type="button" class="btn btn-sm btn-light" data-toggle="modal" data-target="#statusDetailModal">Detail</button></div>
                </div>
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
                <div class="head">
                    <div><h3>Supplier Risk Compare</h3><div class="sub">Bandingkan jumlah item terlambat dengan jumlah PO terdampak.</div></div>
                    <div class="actions"><button type="button" class="btn btn-sm btn-light" data-toggle="modal" data-target="#supplierRiskDetailModal">Detail</button></div>
                </div>
                <div class="body"><div class="chart"><canvas id="supplierRiskChart"></canvas></div></div>
            </article>

            <article class="box">
                <div class="head">
                    <div><h3>Supplier ETD Health</h3><div class="sub">Lihat supplier mana yang sehat dan mana yang paling perlu dikejar.</div></div>
                    <div class="actions"><button type="button" class="btn btn-sm btn-light" data-toggle="modal" data-target="#etdHealthDetailModal">Detail</button></div>
                </div>
                <div class="body">
                    <div class="stack">
                        @forelse($supplierEtdHealth->take(5) as $supplier)
                            <div class="item">
                                <div class="top">
                                    <div>
                                        <div class="ttl">{{ $supplier->supplier_name }}</div>
                                        <div class="meta">At-Risk {{ $supplier->at_risk_items }} | On-Time {{ $supplier->on_time_items }} | Waiting ETD {{ $supplier->waiting_etd_items }}</div>
                                        <div class="meta">PO terdampak {{ $supplier->impacted_po }} | Outstanding {{ \App\Support\NumberFormatter::trim($supplier->outstanding_qty) }}</div>
                                    </div>
                                    <div class="health-badge {{ $supplier->at_risk_percent >= 50 ? 'red' : ($supplier->at_risk_percent >= 20 ? 'yellow' : 'green') }}">
                                        {{ $supplier->at_risk_percent }}% at-risk
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-muted">Belum ada data ETD supplier.</div>
                        @endforelse
                    </div>
                </div>
            </article>
        </section>

        <section class="secondary">
            <article class="box">
                <div class="head">
                    <div><h3>Supplier Paling Perlu Follow Up</h3><div class="sub">Urut dari item terlambat terbanyak.</div></div>
                    <div class="actions"><button type="button" class="btn btn-sm btn-light" data-toggle="modal" data-target="#supplierFollowupModal">Detail</button></div>
                </div>
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
                <div class="head">
                    <div><h3>PO Terdekat Ke ETA</h3><div class="sub">Prioritas follow up supplier dan kesiapan shipment.</div></div>
                    <div class="actions"><button type="button" class="btn btn-sm btn-light" data-toggle="modal" data-target="#etaDetailModal">Detail</button></div>
                </div>
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
                <div class="head">
                    <div><h3>Receiving Terbaru</h3><div class="sub">Dokumen GR yang baru diposting untuk membaca pergerakan akhir proses.</div></div>
                    <div class="actions"><button type="button" class="btn btn-sm btn-light" data-toggle="modal" data-target="#receivingDetailModal">Detail</button></div>
                </div>
                <div class="body"><div class="list">
                    @forelse($recentReceivings as $row)
                        <button type="button" class="receipt text-left w-100 border-0" data-toggle="modal" data-target="#receivingDetailModal">
                            <div class="top"><div class="ttl">{{ $row->gr_number }}</div><div class="meta">{{ \Carbon\Carbon::parse($row->receipt_date)->format('d-m-Y') }}</div></div>
                            <div class="meta">PO {{ $row->po_number }}</div>
                            <div class="meta">{{ $row->supplier_name }}</div>
                        </button>
                    @empty
                        <div class="text-muted">Belum ada data receiving.</div>
                    @endforelse
                </div></div>
            </article>

            <article class="box">
                <div class="head">
                    <div><h3>Item Prioritas</h3><div class="sub">Snapshot cepat item outstanding yang paling perlu dilihat dulu.</div></div>
                    <div class="actions">
                        <button type="button" class="btn btn-sm btn-light" data-toggle="modal" data-target="#itemPriorityDetailModal">Detail</button>
                        <a href="{{ route('summary.po') }}" class="btn btn-sm btn-primary">Buka Summary PO</a>
                    </div>
                </div>
                <div class="table-wrap table-responsive">
                    <table class="table table-hover">
                        <thead><tr><th>Dokumen</th><th>Status</th><th>ETD</th><th>Catatan</th><th>Outstanding</th></tr></thead>
                        <tbody>
                            @forelse($itemMonitoringList as $item)
                                @php($etdStatus = $item->etd_date ? (\Carbon\Carbon::parse($item->etd_date)->isBefore(now()) ? 'At-Risk' : 'On-Time') : 'N/A')
                                <tr>
                                    <td><a href="{{ route('po.show', $item->po_id) }}" class="ttl text-decoration-none">{{ $item->po_number }}</a><div class="meta">{{ $item->item_code }} - {{ $item->item_name }}</div><div class="meta">{{ $item->supplier_name }}</div></td>
                                    <td><span class="badge {{ match ($item->item_status_label) {'Closed' => 'bg-success','Partial', 'Confirmed', 'Waiting' => 'bg-warning text-dark','Late', 'Cancelled' => 'bg-danger',default => 'bg-secondary',} }}">{{ \App\Support\TermCatalog::label('po_item_status', $item->item_status_label, $item->item_status_label) }}</span></td>
                                    <td>@if ($etdStatus === 'At-Risk')<span class="badge bg-danger">At-Risk</span>@elseif ($etdStatus === 'On-Time')<span class="badge bg-success">On-Time</span>@else<span class="badge bg-secondary">N/A</span>@endif<div class="meta mt-1">{{ $item->etd_date ? \Carbon\Carbon::parse($item->etd_date)->format('d-m-Y') : '-' }}</div></td>
                                    <td>{{ $item->item_status_note }}</td>
                                    <td>{{ \App\Support\NumberFormatter::trim($item->outstanding_qty) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">Tidak ada item outstanding.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </section>

    </div>

    <div class="modal fade" id="heroDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Ringkasan Dashboard</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover modal-summary mb-0">
                            <thead><tr><th>Ringkasan</th><th>Nilai</th><th>Keterangan</th></tr></thead>
                            <tbody>
                                <tr><td>Open PO</td><td>{{ $metrics['open_po'] }}</td><td>Dokumen aktif yang masih berjalan</td></tr>
                                <tr><td>Late PO</td><td>{{ $metrics['late_po'] }}</td><td>Header PO yang sudah masuk kondisi terlambat</td></tr>
                                <tr><td>Shipment Hari Ini</td><td>{{ $metrics['shipped_today'] }}</td><td>Dokumen shipment yang diproses hari ini</td></tr>
                                <tr><td>Receiving Hari Ini</td><td>{{ $metrics['received_today'] }}</td><td>GR yang sudah diposting hari ini</td></tr>
                                <tr><td>At-Risk Item</td><td>{{ $metrics['at_risk_items'] }}</td><td>Item outstanding dengan ETD yang sudah lewat</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="openPoKpiModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Detail KPI Open PO</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead><tr><th>PO</th><th>Supplier</th><th>ETA</th><th>Waiting</th><th>Confirmed</th><th>Late</th></tr></thead>
                            <tbody>
                                @forelse($openPoList as $row)
                                    <tr>
                                        <td>{{ $row->po_number }}</td>
                                        <td>{{ $row->supplier_name }}</td>
                                        <td>{{ $row->po_eta_date ? \Carbon\Carbon::parse($row->po_eta_date)->format('d-m-Y') : '-' }}</td>
                                        <td>{{ $row->waiting_items }}</td>
                                        <td>{{ $row->confirmed_items }}</td>
                                        <td>{{ $row->late_items }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">Tidak ada open PO.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="latePoKpiModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Detail KPI Late PO</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead><tr><th>PO</th><th>Supplier</th><th>Late</th><th>Waiting</th><th>Confirmed</th></tr></thead>
                            <tbody>
                                @forelse($latePoRows as $row)
                                    <tr>
                                        <td>{{ $row->po_number }}</td>
                                        <td>{{ $row->supplier_name }}</td>
                                        <td>{{ $row->late_items }}</td>
                                        <td>{{ $row->waiting_items }}</td>
                                        <td>{{ $row->confirmed_items }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">Tidak ada late PO pada filter ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="shipmentTodayKpiModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Detail KPI Shipment Hari Ini</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead><tr><th>Shipment</th><th>Tanggal</th><th>Supplier</th><th>Status</th><th>Delivery Note</th></tr></thead>
                            <tbody>
                                @forelse($shipmentTodayRows as $row)
                                    <tr>
                                        <td>{{ $row->shipment_number }}</td>
                                        <td>{{ \Carbon\Carbon::parse($row->shipment_date)->format('d-m-Y') }}</td>
                                        <td>{{ $row->supplier_name ?: '-' }}</td>
                                        <td>{{ $row->status }}</td>
                                        <td>{{ $row->delivery_note_number ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">Tidak ada shipment hari ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="receivingTodayKpiModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Detail KPI Receiving Hari Ini</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                <div class="modal-body">
                    <div class="actions mb-3"><a href="{{ route('receiving.history') }}" class="btn btn-sm btn-outline-primary">Buka Halaman Receiving History</a></div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead><tr><th>GR</th><th>PO</th><th>Supplier</th><th>Tanggal</th><th>Shipment</th></tr></thead>
                            <tbody>
                                @forelse($receivingTodayRows as $row)
                                    <tr>
                                        <td>{{ $row->gr_number }}</td>
                                        <td>{{ $row->po_number }}</td>
                                        <td>{{ $row->supplier_name }}</td>
                                        <td>{{ \Carbon\Carbon::parse($row->receipt_date)->format('d-m-Y') }}</td>
                                        <td>{{ $row->shipment_number ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">Tidak ada receiving hari ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="statusDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Detail Komposisi Status Item</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                <div class="modal-body">
                    <div class="modal-filters">
                        <div>
                            <label class="label">Cari PO / Item / Supplier</label>
                            <input type="text" id="statusDetailKeyword" class="form-control form-control-sm" placeholder="Ketik PO, item code, nama item, atau supplier">
                        </div>
                        <div>
                            <label class="label">Status</label>
                            <select id="statusDetailStatus" class="form-control form-control-sm">
                                <option value="">Semua Status</option>
                                @foreach (array_keys($statusBreakdown) as $label)
                                    <option value="{{ $label }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label">ETD</label>
                            <select id="statusDetailEtd" class="form-control form-control-sm">
                                <option value="">Semua ETD</option>
                                <option value="at-risk">At-Risk</option>
                                <option value="on-time">On-Time</option>
                                <option value="waiting-date">Belum Ada ETD</option>
                            </select>
                        </div>
                        <div>
                            <label class="label">Supplier</label>
                            <select id="statusDetailSupplier" class="form-control form-control-sm">
                                <option value="">Semua Supplier</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ strtolower($supplier->supplier_name) }}">{{ $supplier->supplier_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <button type="button" id="statusDetailReset" class="btn btn-light btn-sm w-100">Reset</button>
                        </div>
                    </div>
                    <div class="filter-result mb-2" id="statusDetailCount"></div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0" id="statusDetailTable">
                            <thead><tr><th>PO</th><th>Item Code</th><th>Nama Item</th><th>Supplier</th><th>Status</th><th>ETD</th><th>Total Order</th><th>Sudah Dikirim</th><th>Outstanding</th></tr></thead>
                            <tbody>
                                @forelse($statusDetailItems as $row)
                                    @php($etdBucket = !$row->etd_date ? 'waiting-date' : (\Carbon\Carbon::parse($row->etd_date)->isBefore(now()) ? 'at-risk' : 'on-time'))
                                    <tr
                                        data-status="{{ strtolower($row->item_status_label) }}"
                                        data-supplier="{{ strtolower($row->supplier_name) }}"
                                        data-etd="{{ $etdBucket }}"
                                        data-search="{{ strtolower($row->po_number . ' ' . $row->item_code . ' ' . $row->item_name . ' ' . $row->supplier_name) }}">
                                        <td>{{ $row->po_number }}</td>
                                        <td>{{ $row->item_code }}</td>
                                        <td>{{ $row->item_name }}</td>
                                        <td>{{ $row->supplier_name }}</td>
                                        <td>{{ $row->item_status_label }}</td>
                                        <td>{{ $row->etd_date ? \Carbon\Carbon::parse($row->etd_date)->format('d-m-Y') : '-' }}</td>
                                        <td>{{ \App\Support\NumberFormatter::trim($row->ordered_qty) }}</td>
                                        <td>{{ \App\Support\NumberFormatter::trim($row->received_qty) }}</td>
                                        <td>{{ \App\Support\NumberFormatter::trim($row->outstanding_qty) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="text-center text-muted">Tidak ada item.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="supplierRiskDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Detail Supplier Risk</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead><tr><th>Supplier</th><th>Late Item</th><th>PO Terdampak</th><th>ETD Tertua</th></tr></thead>
                            <tbody>
                                @forelse($supplierDelay as $row)
                                    <tr>
                                        <td>{{ $row->supplier_name }}</td>
                                        <td>{{ $row->late_item_count }}</td>
                                        <td>{{ $row->late_po_count }}</td>
                                        <td>{{ $row->oldest_late_etd ? \Carbon\Carbon::parse($row->oldest_late_etd)->format('d-m-Y') : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="p-0">
                                            <div class="table-responsive modal-subtable px-2 pb-2">
                                                <table class="table table-sm table-hover mb-0">
                                                    <thead><tr><th>PO</th><th>Item</th><th>ETD</th><th>Outstanding</th></tr></thead>
                                                    <tbody>
                                                        @forelse(($supplierFollowupDetails->get($row->supplier_name) ?? collect())->take(8) as $detail)
                                                            <tr>
                                                                <td>{{ $detail->po_number }}</td>
                                                                <td>{{ $detail->item_code }} - {{ $detail->item_name }}</td>
                                                                <td>{{ $detail->etd_date ? \Carbon\Carbon::parse($detail->etd_date)->format('d-m-Y') : '-' }}</td>
                                                                <td>{{ \App\Support\NumberFormatter::trim($detail->outstanding_qty) }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr><td colspan="4" class="text-center text-muted">Tidak ada item detail.</td></tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">Belum ada supplier berisiko.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="etdHealthDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Detail ETD Health</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 health-table">
                            <thead><tr><th>Supplier</th><th>At-Risk</th><th>On-Time</th><th>Waiting ETD</th><th>% At-Risk</th><th>PO Terdampak</th><th>Outstanding</th><th>ETD Terdekat</th></tr></thead>
                            <tbody>
                                @forelse($supplierEtdHealth as $supplier)
                                    <tr>
                                        <td>{{ $supplier->supplier_name }}</td>
                                        <td>{{ $supplier->at_risk_items }}</td>
                                        <td>{{ $supplier->on_time_items }}</td>
                                        <td>{{ $supplier->waiting_etd_items }}</td>
                                        <td>{{ $supplier->at_risk_percent }}%</td>
                                        <td>{{ $supplier->impacted_po }}</td>
                                        <td>{{ \App\Support\NumberFormatter::trim($supplier->outstanding_qty) }}</td>
                                        <td>{{ $supplier->nearest_etd ? \Carbon\Carbon::parse($supplier->nearest_etd)->format('d-m-Y') : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center text-muted">Belum ada data ETD supplier.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="supplierFollowupModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Detail Supplier Follow Up</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead><tr><th>Rank</th><th>Supplier</th><th>Late Item</th><th>PO Terdampak</th></tr></thead>
                            <tbody>
                                @forelse($supplierDelay as $index => $row)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $row->supplier_name }}</td>
                                        <td>{{ $row->late_item_count }}</td>
                                        <td>{{ $row->late_po_count }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="p-0">
                                            <div class="table-responsive modal-subtable px-2 pb-2">
                                                <table class="table table-sm table-hover mb-0">
                                                    <thead><tr><th>PO</th><th>Item</th><th>ETD</th><th>Outstanding</th></tr></thead>
                                                    <tbody>
                                                        @forelse(($supplierFollowupDetails->get($row->supplier_name) ?? collect())->take(8) as $detail)
                                                            <tr>
                                                                <td>{{ $detail->po_number }}</td>
                                                                <td>{{ $detail->item_code }} - {{ $detail->item_name }}</td>
                                                                <td>{{ $detail->etd_date ? \Carbon\Carbon::parse($detail->etd_date)->format('d-m-Y') : '-' }}</td>
                                                                <td>{{ \App\Support\NumberFormatter::trim($detail->outstanding_qty) }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr><td colspan="4" class="text-center text-muted">Tidak ada item detail.</td></tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">Belum ada data follow up.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="etaDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Detail PO Terdekat ETA</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead><tr><th>PO</th><th>Supplier</th><th>ETA</th><th>Waiting</th><th>Confirmed</th><th>Late</th><th>Partial</th></tr></thead>
                            <tbody>
                                @forelse($openPoList as $row)
                                    <tr>
                                        <td>{{ $row->po_number }}</td>
                                        <td>{{ $row->supplier_name }}</td>
                                        <td>{{ $row->po_eta_date ? \Carbon\Carbon::parse($row->po_eta_date)->format('d-m-Y') : '-' }}</td>
                                        <td>{{ $row->waiting_items }}</td>
                                        <td>{{ $row->confirmed_items }}</td>
                                        <td>{{ $row->late_items }}</td>
                                        <td>{{ $row->partial_items }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="p-0">
                                            <div class="table-responsive modal-subtable px-2 pb-2">
                                                <table class="table table-sm table-hover mb-0">
                                                    <thead><tr><th>Item</th><th>Promise Date</th><th>Outstanding</th></tr></thead>
                                                    <tbody>
                                                        @forelse(($etaDetailRows->get($row->po_number) ?? collect())->take(8) as $detail)
                                                            <tr>
                                                                <td>{{ $detail->item_code }} - {{ $detail->item_name }}</td>
                                                                <td>{{ $detail->promise_date ? \Carbon\Carbon::parse($detail->promise_date)->format('d-m-Y') : '-' }}</td>
                                                                <td>{{ \App\Support\NumberFormatter::trim($detail->outstanding_qty) }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr><td colspan="3" class="text-center text-muted">Tidak ada item detail.</td></tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted">Belum ada PO aktif.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="receivingDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Detail Receiving Terbaru</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                <div class="modal-body">
                    <div class="actions mb-3"><a href="{{ route('receiving.history') }}" class="btn btn-sm btn-outline-primary">Buka Halaman Receiving History</a></div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead><tr><th>GR</th><th>Tanggal</th><th>PO</th><th>Supplier</th><th>Shipment</th><th>Delivery Note</th></tr></thead>
                            <tbody>
                                @forelse($receivingDetailRows as $row)
                                    <tr>
                                        <td>{{ $row->gr_number }}</td>
                                        <td>{{ \Carbon\Carbon::parse($row->receipt_date)->format('d-m-Y') }}</td>
                                        <td>{{ $row->po_number }}</td>
                                        <td>{{ $row->supplier_name }}</td>
                                        <td>{{ $row->shipment_number ?: '-' }}</td>
                                        <td>{{ $row->delivery_note_number ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">Belum ada receiving.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="itemPriorityDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Detail Item Prioritas Outstanding</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead><tr><th>PO</th><th>Item</th><th>Supplier</th><th>Status</th><th>ETD</th><th>Outstanding</th></tr></thead>
                            <tbody>
                                @forelse($itemMonitoringList as $item)
                                    <tr>
                                        <td>{{ $item->po_number }}</td>
                                        <td>{{ $item->item_code }} - {{ $item->item_name }}</td>
                                        <td>{{ $item->supplier_name }}</td>
                                        <td>{{ $item->item_status_label }}</td>
                                        <td>{{ $item->etd_date ? \Carbon\Carbon::parse($item->etd_date)->format('d-m-Y') : '-' }}</td>
                                        <td>{{ \App\Support\NumberFormatter::trim($item->outstanding_qty) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">Tidak ada item prioritas outstanding.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusCanvas = document.getElementById('statusBreakdownChart');
            const supplierCanvas = document.getElementById('supplierRiskChart');
            const statusDetailKeyword = document.getElementById('statusDetailKeyword');
            const statusDetailStatus = document.getElementById('statusDetailStatus');
            const statusDetailSupplier = document.getElementById('statusDetailSupplier');
            const statusDetailEtd = document.getElementById('statusDetailEtd');
            const statusDetailReset = document.getElementById('statusDetailReset');
            const statusDetailTable = document.getElementById('statusDetailTable');
            const statusDetailCount = document.getElementById('statusDetailCount');

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

            if (statusDetailTable) {
                const rows = Array.from(statusDetailTable.querySelectorAll('tbody tr'));

                const applyStatusDetailFilters = function() {
                    const keyword = (statusDetailKeyword?.value || '').trim().toLowerCase();
                    const status = (statusDetailStatus?.value || '').trim().toLowerCase();
                    const supplier = (statusDetailSupplier?.value || '').trim().toLowerCase();
                    const etd = (statusDetailEtd?.value || '').trim().toLowerCase();
                    let visibleCount = 0;

                    rows.forEach(function(row) {
                        const matchesKeyword = !keyword || row.dataset.search.includes(keyword);
                        const matchesStatus = !status || row.dataset.status === status;
                        const matchesSupplier = !supplier || row.dataset.supplier === supplier;
                        const matchesEtd = !etd || row.dataset.etd === etd;
                        const visible = matchesKeyword && matchesStatus && matchesSupplier && matchesEtd;

                        row.style.display = visible ? '' : 'none';
                        if (visible) {
                            visibleCount += 1;
                        }
                    });

                    if (statusDetailCount) {
                        statusDetailCount.textContent = visibleCount + ' item ditampilkan';
                    }
                };

                [statusDetailKeyword, statusDetailStatus, statusDetailSupplier, statusDetailEtd].forEach(function(el) {
                    if (el) {
                        el.addEventListener('input', applyStatusDetailFilters);
                        el.addEventListener('change', applyStatusDetailFilters);
                    }
                });

                if (statusDetailReset) {
                    statusDetailReset.addEventListener('click', function() {
                        if (statusDetailKeyword) statusDetailKeyword.value = '';
                        if (statusDetailStatus) statusDetailStatus.value = '';
                        if (statusDetailSupplier) statusDetailSupplier.value = '';
                        if (statusDetailEtd) statusDetailEtd.value = '';
                        applyStatusDetailFilters();
                    });
                }

                applyStatusDetailFilters();
            }
        });
    </script>
@endsection
