@extends('layouts.erp')
@php($title='Master Warehouse')
@php($header='Master Warehouse')
@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card card-outline card-primary">
            <div class="card-body">
                <div class="text-muted text-uppercase small">Total Gudang</div>
                <div class="h3 mb-1">{{ $stats['total'] }}</div>
                <div class="small text-muted">Master gudang yang tersedia di sistem.</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-outline card-warning">
            <div class="card-body">
                <div class="text-muted text-uppercase small">Dipakai di PO</div>
                <div class="h3 mb-1">{{ $stats['used_in_po'] }}</div>
                <div class="small text-muted">Gudang yang sudah dipakai pada dokumen PO.</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-outline card-success">
            <div class="card-body">
                <div class="text-muted text-uppercase small">Dipakai di GR</div>
                <div class="h3 mb-1">{{ $stats['used_in_gr'] }}</div>
                <div class="small text-muted">Gudang yang sudah menerima transaksi GR.</div>
            </div>
        </div>
    </div>
</div>

<div class="card card-outline card-primary mb-3">
    <div class="card-header"><h3 class="card-title">Filter Gudang</h3></div>
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Cari</label>
                <input class="form-control form-control-sm" name="q" value="{{ request('q') }}" placeholder="Kode, nama, atau lokasi gudang">
            </div>
            <div class="col-md-2"><button class="btn btn-primary btn-sm w-100">Terapkan</button></div>
            <div class="col-md-2"><a href="{{ route('warehouses.index') }}" class="btn btn-light btn-sm w-100">Reset Filter</a></div>
        </form>
    </div>
</div>

<div class="card card-primary card-outline mb-3">
    <div class="card-header"><h3 class="card-title">Tambah Gudang</h3></div>
    <div class="card-body">
        <form method="POST" action="{{ route('warehouses.store') }}" class="row g-2">@csrf
            <div class="col-md-2">
                <label class="form-label">Kode Gudang</label>
                <input class="form-control form-control-sm" name="warehouse_code" placeholder="Kode" value="{{ old('warehouse_code') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Nama Gudang</label>
                <input class="form-control form-control-sm" name="warehouse_name" placeholder="Nama" value="{{ old('warehouse_name') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Lokasi</label>
                <input class="form-control form-control-sm" name="location" placeholder="Lokasi fisik gudang" value="{{ old('location') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary btn-sm w-100">Simpan Gudang</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Daftar Gudang</h3></div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Kode</th><th>Nama</th><th>Lokasi</th><th>PO</th><th>GR</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td class="font-weight-bold">{{ $row->warehouse_code }}</td>
                        <td>{{ $row->warehouse_name }}</td>
                        <td>{{ $row->location ?: '-' }}</td>
                        <td>{{ $row->po_count }}</td>
                        <td>{{ $row->gr_count }}</td>
                        <td class="text-end"><a href="{{ route('warehouses.edit', $row->id) }}" class="btn btn-sm btn-outline-primary">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">Belum ada gudang.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-2">{{ $rows->links() }}</div>
@endsection
