@extends('layouts.erp')

@php($title = 'Suppliers')
@php($header = 'Suppliers')
@php($headerSubtitle = 'Master data supplier yang dipakai pada purchase order, shipment, dan receiving.')

@section('content')
    <div class="page-shell">
        <section class="page-head">
            <div class="page-head-main">
                <h2 class="page-section-title">Supplier List</h2>
                <p class="page-section-subtitle">Halaman master data yang ringan: filter, form input, dan tabel utama.</p>
            </div>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Filter Supplier</h3>
                    <div class="ui-surface-subtitle">Cari berdasarkan kode, nama, PIC, telepon, email, atau status.</div>
                </div>
            </div>

            <form method="GET" class="filter-grid">
                <div class="span-5">
                    <label class="field-label">Cari</label>
                    <input class="form-control form-control-sm" name="q" value="{{ request('q') }}"
                        placeholder="Kode, nama, PIC, telepon, atau email">
                </div>

                <div class="span-3">
                    <label class="field-label">Status</label>
                    <select class="form-control form-control-sm" name="status">
                        <option value="">Semua</option>
                        <option value="1" @selected(request('status') === '1')>Aktif</option>
                        <option value="0" @selected(request('status') === '0')>Nonaktif</option>
                    </select>
                </div>

                <div class="span-2">
                    <button class="btn btn-primary btn-sm w-100">Apply</button>
                </div>

                <div class="span-2">
                    <a href="{{ route('suppliers.index') }}" class="btn btn-light btn-sm w-100">Reset</a>
                </div>
            </form>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Tambah Supplier</h3>
                    <div class="ui-surface-subtitle">Gunakan form singkat agar input master data tetap cepat.</div>
                </div>
            </div>

            <div class="ui-surface-body">
                <form method="POST" action="{{ route('suppliers.store') }}" class="filter-grid px-0 pt-0 pb-0">
                    @csrf
                    <div class="span-2">
                        <label class="field-label">Kode Supplier</label>
                        <input class="form-control form-control-sm" name="supplier_code" placeholder="Kode unik"
                            value="{{ old('supplier_code') }}" required>
                    </div>

                    <div class="span-3">
                        <label class="field-label">Nama Supplier</label>
                        <input class="form-control form-control-sm" name="supplier_name" placeholder="Nama perusahaan"
                            value="{{ old('supplier_name') }}" required>
                    </div>

                    <div class="span-2">
                        <label class="field-label">PIC</label>
                        <input class="form-control form-control-sm" name="contact_person" placeholder="Nama PIC"
                            value="{{ old('contact_person') }}">
                    </div>

                    <div class="span-2">
                        <label class="field-label">Telepon</label>
                        <input class="form-control form-control-sm" name="phone" placeholder="Nomor telepon"
                            value="{{ old('phone') }}">
                    </div>

                    <div class="span-3">
                        <label class="field-label">Email</label>
                        <input class="form-control form-control-sm" name="email" placeholder="Email supplier"
                            value="{{ old('email') }}">
                    </div>

                    <div class="span-12">
                        <label class="field-label">Alamat</label>
                        <input class="form-control form-control-sm" name="address" placeholder="Alamat supplier"
                            value="{{ old('address') }}">
                    </div>

                    <div class="span-12 d-flex justify-content-end">
                        <button class="btn btn-primary btn-sm px-4">Simpan Supplier</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Daftar Supplier</h3>
                    <div class="ui-surface-subtitle">Status dan aksi diletakkan konsisten di sisi kanan tabel.</div>
                </div>
            </div>

            <div class="table-wrap table-responsive">
                <table class="table table-hover ui-table">
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
                                <td><div class="doc-number">{{ $supplier->supplier_code }}</div></td>
                                <td>{{ $supplier->supplier_name }}</td>
                                <td>{{ $supplier->contact_person ?: '-' }}</td>
                                <td>
                                    {{ $supplier->phone ?: '-' }}<br>
                                    <span class="doc-meta">{{ $supplier->email ?: '-' }}</span>
                                </td>
                                <td>{{ $supplier->address ?: '-' }}</td>
                                <td>
                                    {!! $supplier->status ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>' !!}
                                </td>
                                <td class="text-end">
                                    <div class="action-stack">
                                        <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                        <form action="{{ route('suppliers.toggle-status', $supplier->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-outline-warning">{{ $supplier->status ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                        </form>
                                    </div>
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
        </section>
    </div>

    <div class="mt-2">{{ $suppliers->links() }}</div>
@endsection
