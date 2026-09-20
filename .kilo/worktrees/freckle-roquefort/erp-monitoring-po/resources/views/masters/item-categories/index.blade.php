@extends('layouts.erp')
@php($title = 'Item Categories')
@php($header = 'Item Categories')
{{-- @php($headerSubtitle = 'Master kategori barang untuk klasifikasi item dan monitoring yang lebih rapi.') --}}
@php($headerSubtitle = '')
@section('content')
    <div class="page-shell">
        <section class="ui-surface">
            <div class="ui-surface-head"><div><h3 class="ui-surface-title">Filter Kategori</h3><div class="ui-surface-subtitle">Cari berdasarkan kode, nama, deskripsi, atau status kategori.</div></div></div>
            <form method="GET" class="filter-grid">
                <div class="span-5"><label class="field-label">Cari</label><input class="form-control form-control-sm" name="q" value="{{ request('q') }}" placeholder="Kode, nama, atau deskripsi kategori"></div>
                <div class="span-3"><label class="field-label">Status</label><select name="status" class="form-control form-control-sm"><option value="">Semua</option><option value="1" @selected(request('status') === '1')>Aktif</option><option value="0" @selected(request('status') === '0')>Nonaktif</option></select></div>
                <div class="span-2"><button class="btn btn-primary btn-sm w-100">Apply</button></div>
                <div class="span-2"><a href="{{ route('item-categories.index') }}" class="btn btn-light btn-sm w-100">Reset</a></div>
            </form>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head"><div><h3 class="ui-surface-title">Tambah Kategori</h3><div class="ui-surface-subtitle">Masukkan kode, nama, dan deskripsi singkat kategori baru.</div></div></div>
            <div class="ui-surface-body">
                <form method="POST" action="{{ route('item-categories.store') }}" class="filter-grid px-0 pt-0 pb-0">
                    @csrf
                    <div class="span-3"><label class="field-label">Kode Kategori</label><input class="form-control form-control-sm" name="category_code" placeholder="Kode Kategori" value="{{ old('category_code') }}" required></div>
                    <div class="span-4"><label class="field-label">Nama Kategori</label><input class="form-control form-control-sm" name="category_name" placeholder="Nama Kategori" value="{{ old('category_name') }}" required></div>
                    <div class="span-3"><label class="field-label">Deskripsi</label><input class="form-control form-control-sm" name="description" placeholder="Deskripsi singkat" value="{{ old('description') }}"></div>
                    <div class="span-2 d-flex align-items-end"><button class="btn btn-primary btn-sm w-100">Simpan</button></div>
                </form>
            </div>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head"><div><h3 class="ui-surface-title">Daftar Kategori</h3><div class="ui-surface-subtitle">Jumlah item dan status kategori tampil ringkas di tabel utama.</div></div></div>
            <div class="table-wrap table-responsive">
                <table class="table table-hover ui-table data-table-advanced">
                    <thead><tr><th>Kode</th><th>Nama</th><th>Deskripsi</th><th>Jumlah Item</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td><div class="doc-number">{{ $row->category_code }}</div></td>
                                <td>{{ $row->category_name }}</td>
                                <td>{{ $row->description ?: '-' }}</td>
                                <td>{{ $row->item_count }}</td>
                                <td>{!! $row->is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>' !!}</td>
                                <td class="text-end"><div class="action-stack"><a href="{{ route('item-categories.edit', $row->id) }}" class="btn btn-sm btn-outline-primary">Edit</a><form method="POST" action="{{ route('item-categories.toggle-status', $row->id) }}" class="d-inline">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-warning">{{ $row->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">Belum ada kategori item.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
