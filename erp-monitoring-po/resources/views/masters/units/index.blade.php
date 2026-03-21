@extends('layouts.erp')
@php($title='Master Unit')
@php($header='Master Unit of Measure')
@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card card-outline card-primary">
            <div class="card-body">
                <div class="text-muted text-uppercase small">Total Unit</div>
                <div class="h3 mb-1">{{ $stats['total'] }}</div>
                <div class="small text-muted">Seluruh satuan yang tersedia di sistem.</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-outline card-success">
            <div class="card-body">
                <div class="text-muted text-uppercase small">Dipakai di Item</div>
                <div class="h3 mb-1">{{ $stats['used'] }}</div>
                <div class="small text-muted">Unit yang sudah terhubung ke master barang.</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-outline card-secondary">
            <div class="card-body">
                <div class="text-muted text-uppercase small">Belum Dipakai</div>
                <div class="h3 mb-1">{{ $stats['unused'] }}</div>
                <div class="small text-muted">Masih tersedia dan belum dipakai item.</div>
            </div>
        </div>
    </div>
</div>

<div class="card card-outline card-primary mb-3">
    <div class="card-header"><h3 class="card-title">Filter Unit</h3></div>
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Cari</label>
                <input class="form-control form-control-sm" name="q" value="{{ request('q') }}" placeholder="Kode atau nama unit">
            </div>
            <div class="col-md-2"><button class="btn btn-primary btn-sm w-100">Terapkan</button></div>
            <div class="col-md-2"><a href="{{ route('units.index') }}" class="btn btn-light btn-sm w-100">Reset Filter</a></div>
        </form>
    </div>
</div>

<div class="card card-primary card-outline mb-3">
    <div class="card-header"><h3 class="card-title">Tambah Unit</h3></div>
    <div class="card-body">
        <form method="POST" action="{{ route('units.store') }}" class="row g-2">@csrf
            <div class="col-md-3">
                <label class="form-label">Kode Unit</label>
                <input class="form-control form-control-sm" name="unit_code" placeholder="Mis. PCS" value="{{ old('unit_code') }}" required>
            </div>
            <div class="col-md-7">
                <label class="form-label">Nama Unit</label>
                <input class="form-control form-control-sm" name="unit_name" placeholder="Nama lengkap unit" value="{{ old('unit_name') }}" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary btn-sm w-100">Simpan Unit</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Daftar Unit</h3></div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Kode</th><th>Nama</th><th>Jumlah Item</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td class="font-weight-bold">{{ $row->unit_code }}</td>
                        <td>{{ $row->unit_name }}</td>
                        <td>{{ $row->item_count }}</td>
                        <td class="text-end"><a href="{{ route('units.edit', $row->id) }}" class="btn btn-sm btn-outline-primary">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted">Belum ada unit.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-2">{{ $rows->links() }}</div>
@endsection
