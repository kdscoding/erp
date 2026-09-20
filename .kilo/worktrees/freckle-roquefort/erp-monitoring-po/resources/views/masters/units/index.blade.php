@extends('layouts.erp')

@php($title='Units')
@php($header='Units')
{{-- @php($headerSubtitle='Master unit of measure yang dipakai pada item dan transaksi operasional.') --}}
@php($headerSubtitle = '')

@section('content')
    <div class="page-shell">
        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Filter Unit</h3>
                    <div class="ui-surface-subtitle">Cari berdasarkan kode atau nama unit.</div>
                </div>
            </div>

            <form method="GET" class="filter-grid">
                <div class="span-6">
                    <label class="field-label">Cari</label>
                    <input class="form-control form-control-sm" name="q" value="{{ request('q') }}" placeholder="Kode atau nama unit">
                </div>
                <div class="span-2">
                    <button class="btn btn-primary btn-sm w-100">Apply</button>
                </div>
                <div class="span-2">
                    <a href="{{ route('units.index') }}" class="btn btn-light btn-sm w-100">Reset</a>
                </div>
            </form>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Tambah Unit</h3>
                    <div class="ui-surface-subtitle">Masukkan kode dan nama unit yang akan dipakai item master.</div>
                </div>
            </div>

            <div class="ui-surface-body">
                <form method="POST" action="{{ route('units.store') }}" class="filter-grid px-0 pt-0 pb-0">
                    @csrf
                    <div class="span-3">
                        <label class="field-label">Kode Unit</label>
                        <input class="form-control form-control-sm" name="unit_code" placeholder="Mis. PCS" value="{{ old('unit_code') }}" required>
                    </div>
                    <div class="span-7">
                        <label class="field-label">Nama Unit</label>
                        <input class="form-control form-control-sm" name="unit_name" placeholder="Nama lengkap unit" value="{{ old('unit_name') }}" required>
                    </div>
                    <div class="span-2 d-flex align-items-end">
                        <button class="btn btn-primary btn-sm w-100">Simpan Unit</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Daftar Unit</h3>
                    <div class="ui-surface-subtitle">Jumlah item yang memakai unit ditampilkan ringkas di tabel.</div>
                </div>
            </div>

            <div class="table-wrap table-responsive">
                <table class="table table-hover ui-table data-table-advanced">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Jumlah Item</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td><div class="doc-number">{{ $row->unit_code }}</div></td>
                                <td>{{ $row->unit_name }}</td>
                                <td>{{ $row->item_count }}</td>
                                <td class="text-end">
                                    <div class="action-stack">
                                        <a href="{{ route('units.edit', $row->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Belum ada unit.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
