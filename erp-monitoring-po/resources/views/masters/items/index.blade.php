@extends('layouts.erp')

@php($title = 'Items')
@php($header = 'Items')
@php($headerSubtitle = 'Master barang dan material yang dipakai di purchase order dan shipment.')

@section('content')
    <div class="page-shell">
        <section class="page-head">
            <div class="page-head-main">
                <h2 class="page-section-title">Item List</h2>
                <p class="page-section-subtitle">Master data barang dengan filter, input cepat, dan tabel utama yang konsisten.</p>
            </div>

            <div class="page-actions">
                @if ($supportsCategoryMaster)
                    <a href="{{ route('item-categories.index') }}" class="btn btn-outline-primary btn-sm">Kelola Kategori</a>
                @endif
            </div>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Filter Item</h3>
                    <div class="ui-surface-subtitle">Cari item berdasarkan kode, nama, spesifikasi, kategori, unit, dan status.</div>
                </div>
            </div>

            <form method="GET" class="filter-grid">
                <div class="span-4">
                    <label class="field-label">Cari</label>
                    <input class="form-control form-control-sm" name="q" value="{{ request('q') }}"
                        placeholder="Kode, nama, spesifikasi, atau kategori">
                </div>

                @if ($supportsCategoryMaster)
                    <div class="span-3">
                        <label class="field-label">Kategori</label>
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

                <div class="span-2">
                    <label class="field-label">Unit</label>
                    <select name="unit_id" class="form-control form-control-sm">
                        <option value="">Semua unit</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" @selected(request('unit_id') == $unit->id)>{{ $unit->unit_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="span-2">
                    <label class="field-label">Status</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">Semua</option>
                        <option value="1" @selected(request('status') === '1')>Aktif</option>
                        <option value="0" @selected(request('status') === '0')>Nonaktif</option>
                    </select>
                </div>

                <div class="span-1">
                    <button class="btn btn-primary btn-sm w-100">Apply</button>
                </div>

                <div class="span-2">
                    <a href="{{ route('items.index') }}" class="btn btn-light btn-sm w-100">Reset</a>
                </div>
            </form>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Tambah Item</h3>
                    <div class="ui-surface-subtitle">Form input tetap singkat agar tidak terasa seperti halaman dashboard.</div>
                </div>
            </div>

            <div class="ui-surface-body">
                <form method="POST" action="{{ route('items.store') }}" class="filter-grid px-0 pt-0 pb-0">
                    @csrf
                    <div class="span-2">
                        <label class="field-label">Kode Item</label>
                        <input class="form-control form-control-sm" name="item_code" placeholder="Kode unik"
                            value="{{ old('item_code') }}" required>
                    </div>

                    <div class="span-3">
                        <label class="field-label">Nama Item</label>
                        <input class="form-control form-control-sm" name="item_name" placeholder="Nama barang/material"
                            value="{{ old('item_name') }}" required>
                    </div>

                    @if ($supportsCategoryMaster)
                        <div class="span-2">
                            <label class="field-label">Kategori</label>
                            <select class="form-control form-control-sm" name="category_id">
                                <option value="">Pilih kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div class="span-2">
                            <label class="field-label">Kategori</label>
                            <input class="form-control form-control-sm" name="category" placeholder="Kategori"
                                value="{{ old('category') }}">
                        </div>
                    @endif

                    <div class="span-2">
                        <label class="field-label">Unit</label>
                        <select class="form-control form-control-sm" name="unit_id">
                            <option value="">Pilih unit</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}" @selected(old('unit_id') == $unit->id)>{{ $unit->unit_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="span-3">
                        <label class="field-label">Spesifikasi</label>
                        <input class="form-control form-control-sm" name="specification"
                            placeholder="Ukuran, material, warna, printer match, dll"
                            value="{{ old('specification') }}">
                    </div>

                    <div class="span-12 d-flex justify-content-end">
                        <button class="btn btn-primary btn-sm px-4">Simpan Item</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Daftar Item</h3>
                    <div class="ui-surface-subtitle">Kolom identitas utama, status, dan aksi mengikuti baseline global yang sama.</div>
                </div>
            </div>

            <div class="table-wrap table-responsive">
                <table class="table table-hover ui-table">
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
                                <td><div class="doc-number">{{ $row->item_code }}</div></td>
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
                                    <div class="action-stack">
                                        <a href="{{ route('items.edit', $row->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                        <form method="POST" action="{{ route('items.toggle-status', $row->id) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-outline-warning">{{ $row->active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                        </form>
                                    </div>
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
        </section>
    </div>

    <div class="mt-2">{{ $rows->links() }}</div>
@endsection
