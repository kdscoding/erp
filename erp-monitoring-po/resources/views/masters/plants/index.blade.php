@extends('layouts.erp')
@php($title='Plants')
@php($header='Plants')
{{-- @php($headerSubtitle='Master plant yang terhubung ke dokumen purchase order.') --}}
@php($headerSubtitle = '')

@section('content')
    <div class="page-shell">
        <section class="ui-surface">
            <div class="ui-surface-head"><div><h3 class="ui-surface-title">Filter Plant</h3><div class="ui-surface-subtitle">Cari berdasarkan kode atau nama plant.</div></div></div>
            <form method="GET" class="filter-grid">
                <div class="span-6"><label class="field-label">Cari</label><input class="form-control form-control-sm" name="q" value="{{ request('q') }}" placeholder="Kode atau nama plant"></div>
                <div class="span-2"><button class="btn btn-primary btn-sm w-100">Apply</button></div>
                <div class="span-2"><a href="{{ route('plants.index') }}" class="btn btn-light btn-sm w-100">Reset</a></div>
            </form>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head"><div><h3 class="ui-surface-title">Tambah Plant</h3><div class="ui-surface-subtitle">Masukkan kode dan nama plant yang akan dipakai pada operasional.</div></div></div>
            <div class="ui-surface-body">
                <form method="POST" action="{{ route('plants.store') }}" class="filter-grid px-0 pt-0 pb-0">
                    @csrf
                    <div class="span-3"><label class="field-label">Kode Plant</label><input class="form-control form-control-sm" name="plant_code" placeholder="Kode Plant" value="{{ old('plant_code') }}" required></div>
                    <div class="span-7"><label class="field-label">Nama Plant</label><input class="form-control form-control-sm" name="plant_name" placeholder="Nama Plant" value="{{ old('plant_name') }}" required></div>
                    <div class="span-2 d-flex align-items-end"><button class="btn btn-primary btn-sm w-100">Simpan Plant</button></div>
                </form>
            </div>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head"><div><h3 class="ui-surface-title">Daftar Plant</h3><div class="ui-surface-subtitle">Kolom jumlah PO membantu melihat pemakaian plant secara cepat.</div></div></div>
            <div class="table-wrap table-responsive">
                <table class="table table-hover ui-table data-table-advanced">
                    <thead><tr><th>Kode</th><th>Nama</th><th>Jumlah PO</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td><div class="doc-number">{{ $row->plant_code }}</div></td>
                                <td>{{ $row->plant_name }}</td>
                                <td>{{ $row->po_count }}</td>
                                <td class="text-end"><div class="action-stack"><a href="{{ route('plants.edit', $row->id) }}" class="btn btn-sm btn-outline-primary">Edit</a></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">Belum ada plant.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
