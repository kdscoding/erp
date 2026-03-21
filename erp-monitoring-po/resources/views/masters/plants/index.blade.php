@extends('layouts.erp')
@php($title='Master Plant')
@php($header='Master Plant')
@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card card-outline card-primary">
            <div class="card-body">
                <div class="text-muted text-uppercase small">Total Plant</div>
                <div class="h3 mb-1">{{ $stats['total'] }}</div>
                <div class="small text-muted">Seluruh plant yang tersedia di sistem.</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-outline card-success">
            <div class="card-body">
                <div class="text-muted text-uppercase small">Dipakai di PO</div>
                <div class="h3 mb-1">{{ $stats['used_in_po'] }}</div>
                <div class="small text-muted">Plant yang sudah terhubung ke dokumen PO.</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-outline card-secondary">
            <div class="card-body">
                <div class="text-muted text-uppercase small">Belum Dipakai</div>
                <div class="h3 mb-1">{{ $stats['unused'] }}</div>
                <div class="small text-muted">Masih siap dipakai untuk plant baru.</div>
            </div>
        </div>
    </div>
</div>

<div class="card card-outline card-primary mb-3">
    <div class="card-header"><h3 class="card-title">Filter Plant</h3></div>
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Cari</label>
                <input class="form-control form-control-sm" name="q" value="{{ request('q') }}" placeholder="Kode atau nama plant">
            </div>
            <div class="col-md-2"><button class="btn btn-primary btn-sm w-100">Terapkan</button></div>
            <div class="col-md-2"><a href="{{ route('plants.index') }}" class="btn btn-light btn-sm w-100">Reset Filter</a></div>
        </form>
    </div>
</div>

<div class="card card-primary card-outline mb-3">
    <div class="card-header"><h3 class="card-title">Tambah Plant</h3></div>
    <div class="card-body">
        <form method="POST" action="{{ route('plants.store') }}" class="row g-2">@csrf
            <div class="col-md-3">
                <label class="form-label">Kode Plant</label>
                <input class="form-control form-control-sm" name="plant_code" placeholder="Kode Plant" value="{{ old('plant_code') }}" required>
            </div>
            <div class="col-md-7">
                <label class="form-label">Nama Plant</label>
                <input class="form-control form-control-sm" name="plant_name" placeholder="Nama Plant" value="{{ old('plant_name') }}" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary btn-sm w-100">Simpan Plant</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Daftar Plant</h3></div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Kode</th><th>Nama</th><th>Jumlah PO</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td class="font-weight-bold">{{ $row->plant_code }}</td>
                        <td>{{ $row->plant_name }}</td>
                        <td>{{ $row->po_count }}</td>
                        <td class="text-end"><a href="{{ route('plants.edit', $row->id) }}" class="btn btn-sm btn-outline-primary">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted">Belum ada plant.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-2">{{ $rows->links() }}</div>
@endsection
