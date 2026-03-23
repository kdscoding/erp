@extends('layouts.erp')

@php($title = 'Purchase Order')
@php($header = 'Purchase Order Monitoring')

@section('content')
    <style>
        .po-shell {
            display: grid;
            gap: 1rem;
        }

        .po-hero,
        .po-surface {
            border: 1px solid rgba(111, 150, 40, .12);
            border-radius: 18px;
            background: rgba(255, 255, 255, .94);
            box-shadow: 0 14px 32px rgba(111, 150, 40, .05);
        }

        .po-hero {
            padding: 1rem 1.1rem;
            background:
                radial-gradient(circle at top right, rgba(241, 217, 59, .24), transparent 30%),
                linear-gradient(135deg, rgba(255, 255, 255, .96), rgba(245, 249, 221, .96));
        }

        .po-hero-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: #314216;
            margin-bottom: .25rem;
        }

        .po-hero-copy {
            color: #6f7d52;
            margin-bottom: 0;
            font-size: .88rem;
        }

        .po-stat-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .75rem;
        }

        .po-stat {
            padding: .85rem .95rem;
            border-radius: 16px;
            border: 1px solid rgba(111, 150, 40, .1);
            background: rgba(255, 255, 255, .8);
        }

        .po-stat-label {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #7a8660;
            margin-bottom: .25rem;
        }

        .po-stat-value {
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
            flex-wrap: wrap;
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

        .filter-grid {
            display: grid;
            grid-template-columns: 1.3fr 1fr auto auto;
            gap: .75rem;
            padding: 1rem;
            align-items: end;
        }

        .table-wrap {
            padding: 1rem;
        }

        .po-table {
            margin-bottom: 0;
        }

        .po-table thead th {
            font-size: .69rem;
            letter-spacing: .08em;
        }

        .po-number {
            font-weight: 700;
            color: #314216;
        }

        .po-meta {
            font-size: .8rem;
            color: #7a8660;
        }

        @media (max-width: 991.98px) {
            .po-stat-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .filter-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 767.98px) {

            .po-stat-grid,
            .filter-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    @php($openCount = $rows->getCollection()->where('status', 'Open')->count())
    @php($lateCount = $rows->getCollection()->where('status', 'Late')->count())
    @php($closedCount = $rows->getCollection()->where('status', 'Closed')->count())
    @php($cancelledCount = $rows->getCollection()->where('status', 'Cancelled')->count())

    <div class="po-shell">
        <section class="po-hero">
            <div class="row g-3 align-items-end">
                <div class="col-lg-5">
                    <div class="po-hero-title">Daftar PO yang lebih cepat dipindai dan lebih mudah difilter.</div>
                    <p class="po-hero-copy">Gunakan filter di bawah untuk fokus ke supplier atau status PO yang sedang
                        berjalan.</p>
                </div>
                <div class="col-lg-7">
                    <div class="po-stat-grid">
                        <div class="po-stat">
                            <div class="po-stat-label">Open</div>
                            <div class="po-stat-value">{{ $openCount }}</div>
                        </div>
                        <div class="po-stat">
                            <div class="po-stat-label">Late</div>
                            <div class="po-stat-value">{{ $lateCount }}</div>
                        </div>
                        <div class="po-stat">
                            <div class="po-stat-label">Closed</div>
                            <div class="po-stat-value">{{ $closedCount }}</div>
                        </div>
                        <div class="po-stat">
                            <div class="po-stat-label">Cancelled</div>
                            <div class="po-stat-value">{{ $cancelledCount }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="po-surface">
            <div class="surface-head">
                <div>
                    <h3 class="surface-title">Filter Dokumen PO</h3>
                    <div class="surface-subtitle">Saring list PO berdasarkan supplier dan status header.</div>
                </div>
                <a href="{{ route('po.create') }}" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Buat PO</a>
            </div>
            <form method="GET" class="filter-grid">
                <div>
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" class="form-control form-control-sm">
                        <option value="">Semua Supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>{{ $supplier->supplier_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">Semua Status</option>
                        @foreach (\App\Support\TermCatalog::options('po_status', ['Open', 'Late', 'Closed', 'Cancelled']) as $status => $label)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div><button class="btn btn-primary btn-sm w-100">Terapkan Filter</button></div>
                <div><a href="{{ route('po.index') }}" class="btn btn-light btn-sm w-100">Reset</a></div>
            </form>
        </section>

        <section class="po-surface">
            <div class="surface-head">
                <div>
                    <h3 class="surface-title">Daftar Purchase Order</h3>
                    <div class="surface-subtitle">Buka detail untuk melihat histori, item, dan aksi operasional.</div>
                </div>
            </div>
            <div class="table-wrap table-responsive">
                <table class="table table-hover po-table">
                    <thead>
                        <tr>
                            <th>PO</th>
                            <th>Tanggal</th>
                            <th>Supplier</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $r)
                            <tr>
                                <td>
                                    <div class="po-number">{{ $r->po_number }}</div>
                                    <div class="po-meta">Dokumen pembelian aktif</div>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($r->po_date)->format('d-m-Y') }}</td>
                                <td>{{ $r->supplier_name }}</td>
                                <td>
                                    <x-status-badge :status="$r->status" scope="po" />
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('po.show', $r->id) }}"
                                        class="btn btn-sm btn-outline-primary">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada PO.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="mt-2">{{ $rows->links() }}</div>
@endsection
