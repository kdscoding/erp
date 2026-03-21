@extends('layouts.erp')
@php($title = 'Master Item')
@php($header = 'Master Barang dan Material')
@section('content')
    <div class="row">
        <div class="col-md-3">
            <div class="card card-outline card-primary">
                <div class="card-body">
                    <div class="text-muted text-uppercase small">Total Item</div>
                    <div class="h3 mb-1">{{ $stats['total'] }}</div>
                    <div class="small text-muted">Seluruh master barang yang terdaftar.</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-outline card-success">
                <div class="card-body">
                    <div class="text-muted text-uppercase small">Item Aktif</div>
                    <div class="h3 mb-1">{{ $stats['active'] }}</div>
                    <div class="small text-muted">Siap dipakai di proses PO dan shipment.</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-outline card-warning">
                <div class="card-body">
                    <div class="text-muted text-uppercase small">Sudah Berkategori</div>
                    <div class="h3 mb-1">{{ $stats['categorized'] }}</div>
                    <div class="small text-muted">Sudah masuk klasifikasi kategori barang.</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-outline card-secondary">
                <div class="card-body">
                    <div class="text-muted text-uppercase small">Item Nonaktif</div>
                    <div class="h3 mb-1">{{ $stats['inactive'] }}</div>
                    <div class="small text-muted">Disimpan sebagai arsip atau tidak dipakai.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary mb-3">
        <div class="card-header">
            <h3 class="card-title">Filter dan Pencarian</h3>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Cari</label>
                    <input class="form-control form-control-sm" name="q" value="{{ request('q') }}"
                        placeholder="Kode, nama, spesifikasi, atau kategori">
                </div>
                @if ($supportsCategoryMaster)
                    <div class="col-md-3">
                        <label class="form-label">Kategori</label>
                        <select name="category_id" class="form-control form-control-sm">
                            <option value="">Semua kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                                    {{ $category->category_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-2">
                    <label class="form-label">Unit</label>
                    <select name="unit_id" class="form-control form-control-sm">
                        <option value="">Semua unit</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" @selected(request('unit_id') == $unit->id)>{{ $unit->unit_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">Semua</option>
                        <option value="1" @selected(request('status') === '1')>Aktif</option>
                        <option value="0" @selected(request('status') === '0')>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-primary btn-sm w-100">Cari</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('items.index') }}" class="btn btn-light btn-sm w-100">Reset Filter</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-primary card-outline mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Tambah Item Baru</h3>
            @if ($supportsCategoryMaster)
                <a href="{{ route('item-categories.index') }}" class="btn btn-sm btn-outline-primary">Kelola Kategori</a>
            @else
                <span class="text-muted small">Kategori master aktif setelah `php artisan migrate` dijalankan.</span>
            @endif
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('items.store') }}" class="row g-2">
                @csrf
                <div class="col-md-2">
                    <label class="form-label">Kode Item</label>
                    <input class="form-control form-control-sm" name="item_code" placeholder="Kode unik"
                        value="{{ old('item_code') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nama Item</label>
                    <input class="form-control form-control-sm" name="item_name" placeholder="Nama barang/material"
                        value="{{ old('item_name') }}" required>
                </div>
                @if ($supportsCategoryMaster)
                    <div class="col-md-2">
                        <label class="form-label">Kategori</label>
                        <select class="form-control form-control-sm" name="category_id">
                            <option value="">Pilih kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->category_name }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div class="col-md-2">
                        <label class="form-label">Kategori</label>
                        <input class="form-control form-control-sm" name="category" placeholder="Kategori"
                            value="{{ old('category') }}">
                    </div>
                @endif
                <div class="col-md-2">
                    <label class="form-label">Unit</label>
                    <select class="form-control form-control-sm" name="unit_id">
                        <option value="">Pilih unit</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" @selected(old('unit_id') == $unit->id)>{{ $unit->unit_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Spesifikasi</label>
                    <input class="form-control form-control-sm" name="specification"
                        placeholder="Ukuran, material, warna, printer match, dll"
                        value="{{ old('specification') }}">
                </div>
                <div class="col-12 text-end">
                    <button class="btn btn-primary btn-sm px-4">Simpan Item</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Item</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Spesifikasi</th>
                        <th>Unit</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td class="font-weight-bold">{{ $row->item_code }}</td>
                            <td>{{ $row->item_name }}</td>
                            <td>
                                @if (($row->category_name ?? null) || ($row->category ?? null))
                                    <span class="badge bg-primary">{{ $row->category_name ?? $row->category }}</span>
                                @else
                                    <span class="text-muted">Belum dikategorikan</span>
                                @endif
                            </td>
                            <td>{{ $row->specification ?: '-' }}</td>
                            <td>{{ $row->unit_name ?: '-' }}</td>
                            <td>{!! $row->active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>' !!}</td>
                            <td class="text-end">
                                <a href="{{ route('items.edit', $row->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('items.toggle-status', $row->id) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        class="btn btn-sm btn-outline-warning">{{ $row->active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Belum ada data item.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-2">{{ $rows->links() }}</div>
@endsection
