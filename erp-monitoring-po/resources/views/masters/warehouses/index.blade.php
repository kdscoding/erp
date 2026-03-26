@extends('layouts.erp')
@php($title='Warehouses')
@php($header='Warehouses')
@php($headerSubtitle='Master gudang untuk transaksi purchase order dan goods receipt.')

@section('content')
    <div class="page-shell">
        <section class="page-head">
            <div class="page-head-main">
                <h2 class="page-section-title">Warehouse List</h2>
                <p class="page-section-subtitle">Master gudang dengan filter cepat, form tambah, dan tabel utama yang bersih.</p>
            </div>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head"><div><h3 class="ui-surface-title">Filter Gudang</h3><div class="ui-surface-subtitle">Cari berdasarkan kode, nama, atau lokasi gudang.</div></div></div>
            <form method="GET" class="filter-grid">
                <div class="span-6"><label class="field-label">Cari</label><input class="form-control form-control-sm" name="q" value="{{ request('q') }}" placeholder="Kode, nama, atau lokasi gudang"></div>
                <div class="span-2"><button class="btn btn-primary btn-sm w-100">Apply</button></div>
                <div class="span-2"><a href="{{ route('warehouses.index') }}" class="btn btn-light btn-sm w-100">Reset</a></div>
            </form>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head"><div><h3 class="ui-surface-title">Tambah Gudang</h3><div class="ui-surface-subtitle">Masukkan kode, nama, dan lokasi gudang.</div></div></div>
            <div class="ui-surface-body">
                <form method="POST" action="{{ route('warehouses.store') }}" class="filter-grid px-0 pt-0 pb-0">
                    @csrf
                    <div class="span-2"><label class="field-label">Kode Gudang</label><input class="form-control form-control-sm" name="warehouse_code" placeholder="Kode" value="{{ old('warehouse_code') }}" required></div>
                    <div class="span-4"><label class="field-label">Nama Gudang</label><input class="form-control form-control-sm" name="warehouse_name" placeholder="Nama" value="{{ old('warehouse_name') }}" required></div>
                    <div class="span-4"><label class="field-label">Lokasi</label><input class="form-control form-control-sm" name="location" placeholder="Lokasi fisik gudang" value="{{ old('location') }}"></div>
                    <div class="span-2 d-flex align-items-end"><button class="btn btn-primary btn-sm w-100">Simpan Gudang</button></div>
                </form>
            </div>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head"><div><h3 class="ui-surface-title">Daftar Gudang</h3><div class="ui-surface-subtitle">Jumlah pemakaian pada PO dan GR diringkas di tabel utama.</div></div></div>
            <div class="table-wrap table-responsive">
                <table class="table table-hover ui-table">
                    <thead><tr><th>Kode</th><th>Nama</th><th>Lokasi</th><th>PO</th><th>GR</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td><div class="doc-number">{{ $row->warehouse_code }}</div></td>
                                <td>{{ $row->warehouse_name }}</td>
                                <td>{{ $row->location ?: '-' }}</td>
                                <td>{{ $row->po_count }}</td>
                                <td>{{ $row->gr_count }}</td>
                                <td class="text-end"><div class="action-stack"><a href="{{ route('warehouses.edit', $row->id) }}" class="btn btn-sm btn-outline-primary">Edit</a></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">Belum ada gudang.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="mt-2">{{ $rows->links() }}</div>
@endsection
