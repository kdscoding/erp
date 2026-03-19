@extends('layouts.erp')
@php($title='Master Item')
@php($header='Master Item Label/Material')
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="card card-outline card-primary mb-3">
    <div class="card-header"><h3 class="card-title">Filter Item</h3></div>
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4"><label class="form-label">Cari</label><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Kode / Nama Item"></div>
            <div class="col-md-2"><label class="form-label">Status</label><select name="status" class="form-select"><option value="">Semua</option><option value="1" @selected(request('status')==='1')>Aktif</option><option value="0" @selected(request('status')==='0')>Nonaktif</option></select></div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Terapkan</button></div>
        </form>
    </div>
</div>

<div class="card card-primary card-outline mb-3"><div class="card-body"><form method="POST" action="{{ route('items.store') }}" class="row g-2">@csrf
<div class="col-md-2"><input class="form-control" name="item_code" placeholder="Kode Item" value="{{ old('item_code') }}" required></div>
<div class="col-md-3"><input class="form-control" name="item_name" placeholder="Nama Item" value="{{ old('item_name') }}" required></div>
<div class="col-md-2"><select class="form-select" name="unit_id"><option value="">Pilih Unit</option>@foreach($units as $u)<option value="{{ $u->id }}">{{ $u->unit_name }}</option>@endforeach</select></div>
<div class="col-md-2"><input class="form-control" name="category" placeholder="Kategori" value="{{ old('category') }}"></div>
<div class="col-md-2"><input class="form-control" name="specification" placeholder="Spesifikasi" value="{{ old('specification') }}"></div>
<div class="col-md-1"><button class="btn btn-primary w-100">Simpan</button></div>
</form></div></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-hover mb-0"><thead><tr><th>Kode</th><th>Nama</th><th>Unit</th><th>Kategori</th><th>Status</th><th class="text-end">Aksi</th></tr></thead><tbody>@forelse($rows as $r)<tr><td>{{ $r->item_code }}</td><td>{{ $r->item_name }}</td><td>{{ $r->unit_name ?: '-' }}</td><td>{{ $r->category ?: '-' }}</td><td>{!! $r->active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>' !!}</td><td class="text-end"><a href="{{ route('items.edit', $r->id) }}" class="btn btn-sm btn-outline-primary">Edit</a> <form method="POST" action="{{ route('items.toggle-status', $r->id) }}" class="d-inline">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-warning">{{ $r->active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form></td></tr>@empty<tr><td colspan="6" class="text-center text-muted">Belum ada data</td></tr>@endforelse</tbody></table></div></div>
<div class="mt-2">{{ $rows->links() }}</div>
@endsection
