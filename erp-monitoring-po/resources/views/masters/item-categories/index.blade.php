@extends('layouts.erp')
@php($title = 'Kategori Item')
@php($header = 'Master Kategori Barang')
@section('content')
    <div class="card card-outline card-primary mb-3">
        <div class="card-header">
            <h3 class="card-title">Filter Kategori</h3>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Cari</label>
                    <input class="form-control form-control-sm" name="q" value="{{ request('q') }}"
                        placeholder="Kode, nama, atau deskripsi kategori">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">Semua</option>
                        <option value="1" @selected(request('status') === '1')>Aktif</option>
                        <option value="0" @selected(request('status') === '0')>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary btn-sm w-100">Terapkan</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('item-categories.index') }}" class="btn btn-light btn-sm w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-primary card-outline mb-3">
        <div class="card-header">
            <h3 class="card-title">Tambah Kategori Baru</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('item-categories.store') }}" class="row g-2">
                @csrf
                <div class="col-md-3">
                    <input class="form-control form-control-sm" name="category_code" placeholder="Kode Kategori"
                        value="{{ old('category_code') }}" required>
                </div>
                <div class="col-md-4">
                    <input class="form-control form-control-sm" name="category_name" placeholder="Nama Kategori"
                        value="{{ old('category_name') }}" required>
                </div>
                <div class="col-md-3">
                    <input class="form-control form-control-sm" name="description" placeholder="Deskripsi singkat"
                        value="{{ old('description') }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary btn-sm w-100">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Deskripsi</th>
                        <th>Jumlah Item</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $row->category_code }}</td>
                            <td>{{ $row->category_name }}</td>
                            <td>{{ $row->description ?: '-' }}</td>
                            <td>{{ $row->item_count }}</td>
                            <td>{!! $row->is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>' !!}</td>
                            <td class="text-end">
                                <a href="{{ route('item-categories.edit', $row->id) }}"
                                    class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('item-categories.toggle-status', $row->id) }}"
                                    class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        class="btn btn-sm btn-outline-warning">{{ $row->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Belum ada kategori item.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-2">{{ $rows->links() }}</div>
@endsection
