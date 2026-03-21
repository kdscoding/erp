@extends('layouts.erp')

@php($title = 'Master Supplier')
@php($header = 'Master Data Supplier')

@section('content')
    <div class="row">
        <div class="col-md-3">
            <div class="card card-outline card-primary">
                <div class="card-body">
                    <div class="text-muted text-uppercase small">Total Supplier</div>
                    <div class="h3 mb-1">{{ $stats['total'] }}</div>
                    <div class="small text-muted">Seluruh supplier yang terdaftar di sistem.</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-outline card-success">
                <div class="card-body">
                    <div class="text-muted text-uppercase small">Supplier Aktif</div>
                    <div class="h3 mb-1">{{ $stats['active'] }}</div>
                    <div class="small text-muted">Masih dapat dipakai saat membuat PO baru.</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-outline card-warning">
                <div class="card-body">
                    <div class="text-muted text-uppercase small">Pernah Dipakai</div>
                    <div class="h3 mb-1">{{ $stats['used_in_po'] }}</div>
                    <div class="small text-muted">Supplier yang sudah punya histori PO.</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-outline card-secondary">
                <div class="card-body">
                    <div class="text-muted text-uppercase small">Supplier Nonaktif</div>
                    <div class="h3 mb-1">{{ $stats['inactive'] }}</div>
                    <div class="small text-muted">Disimpan untuk arsip dan histori.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary mb-3">
        <div class="card-header">
            <h3 class="card-title">Filter Supplier</h3>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Cari</label>
                    <input class="form-control form-control-sm" name="q" value="{{ request('q') }}"
                        placeholder="Kode, nama, PIC, telepon, atau email">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-control form-control-sm" name="status">
                        <option value="">Semua</option>
                        <option value="1" @selected(request('status') === '1')>Aktif</option>
                        <option value="0" @selected(request('status') === '0')>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary btn-sm w-100">Terapkan</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('suppliers.index') }}" class="btn btn-light btn-sm w-100">Reset Filter</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-primary card-outline mb-3">
        <div class="card-header">
            <h3 class="card-title">Tambah Supplier</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('suppliers.store') }}" class="row g-2">
                @csrf
                <div class="col-md-2">
                    <label class="form-label">Kode Supplier</label>
                    <input class="form-control form-control-sm" name="supplier_code" placeholder="Kode unik"
                        value="{{ old('supplier_code') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nama Supplier</label>
                    <input class="form-control form-control-sm" name="supplier_name" placeholder="Nama perusahaan"
                        value="{{ old('supplier_name') }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">PIC</label>
                    <input class="form-control form-control-sm" name="contact_person" placeholder="Nama PIC"
                        value="{{ old('contact_person') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Telepon</label>
                    <input class="form-control form-control-sm" name="phone" placeholder="Nomor telepon"
                        value="{{ old('phone') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Email</label>
                    <input class="form-control form-control-sm" name="email" placeholder="Email supplier"
                        value="{{ old('email') }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Alamat</label>
                    <input class="form-control form-control-sm" name="address" placeholder="Alamat supplier"
                        value="{{ old('address') }}">
                </div>
                <div class="col-12 text-end">
                    <button class="btn btn-primary btn-sm px-4">Simpan Supplier</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Supplier</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap mb-0">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>PIC</th>
                        <th>Kontak</th>
                        <th>Alamat</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                        <tr>
                            <td class="font-weight-bold">{{ $supplier->supplier_code }}</td>
                            <td>{{ $supplier->supplier_name }}</td>
                            <td>{{ $supplier->contact_person ?: '-' }}</td>
                            <td>
                                {{ $supplier->phone ?: '-' }}<br>
                                <small class="text-muted">{{ $supplier->email ?: '-' }}</small>
                            </td>
                            <td>{{ $supplier->address ?: '-' }}</td>
                            <td>{!! $supplier->status ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>' !!}</td>
                            <td class="text-end">
                                <a href="{{ route('suppliers.edit', $supplier->id) }}"
                                    class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route('suppliers.toggle-status', $supplier->id) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        class="btn btn-sm btn-outline-warning">{{ $supplier->status ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Belum ada data supplier.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-2">{{ $suppliers->links() }}</div>
@endsection
