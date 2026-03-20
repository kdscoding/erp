@extends('layouts.erp')

@php($title = 'Master Supplier')
@php($header = 'Master Data Supplier')

@section('content')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card card-outline card-primary mb-3">
        <div class="card-header">
            <h3 class="card-title">Filter Supplier</h3>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4"><label class="form-label">Cari</label><input class="form-control form-control-sm"
                        name="q" value="{{ request('q') }}" placeholder="Kode / Nama Supplier"></div>
                <div class="col-md-2"><label class="form-label">Status</label><select class="form-control form-control-sm"
                        name="status">
                        <option value="">Semua</option>
                        <option value="1" @selected(request('status') === '1')>Aktif</option>
                        <option value="0" @selected(request('status') === '0')>Nonaktif</option>
                    </select></div>
                <div class="col-md-2"><button class="btn btn-primary btn-sm w-100">Terapkan</button></div>
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
                <div class="col-md-2"><input class="form-control form-control-sm" name="supplier_code"
                        placeholder="Kode Supplier" value="{{ old('supplier_code') }}" required></div>
                <div class="col-md-3"><input class="form-control form-control-sm" name="supplier_name"
                        placeholder="Nama Supplier" value="{{ old('supplier_name') }}" required></div>
                <div class="col-md-2"><input class="form-control form-control-sm" name="contact_person" placeholder="PIC"
                        value="{{ old('contact_person') }}"></div>
                <div class="col-md-2"><input class="form-control form-control-sm" name="phone" placeholder="Telepon"
                        value="{{ old('phone') }}"></div>
                <div class="col-md-2"><input class="form-control form-control-sm" name="email" placeholder="Email"
                        value="{{ old('email') }}"></div>
                <div class="col-md-1"><button class="btn btn-primary btn-sm w-100">Simpan</button></div>
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
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $s)
                        <tr>
                            <td>{{ $s->supplier_code }}</td>
                            <td>{{ $s->supplier_name }}</td>
                            <td>{{ $s->contact_person ?: '-' }}</td>
                            <td>{{ $s->phone ?: '-' }}<br><small class="text-muted">{{ $s->email ?: '-' }}</small></td>
                            <td>{!! $s->status
                                ? '<span class="badge bg-success">Aktif</span>'
                                : '<span class="badge bg-secondary">Nonaktif</span>' !!}</td>
                            <td class="text-end">
                                <a href="{{ route('suppliers.edit', $s->id) }}"
                                    class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route('suppliers.toggle-status', $s->id) }}" method="POST"
                                    class="d-inline">@csrf @method('PATCH')<button
                                        class="btn btn-sm btn-outline-warning">{{ $s->status ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Belum ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-2">{{ $suppliers->links() }}</div>
@endsection
