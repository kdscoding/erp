@extends('layouts.erp')

@php($title='Master Supplier')
@php($header='Master Data Supplier')

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header"><h3 class="card-title">Input Supplier</h3></div>
    <div class="card-body">
        <form method="POST" action="{{ route('suppliers.store') }}" class="row g-2">
            @csrf
            <div class="col-md-2"><input class="form-control" name="supplier_code" placeholder="Kode Supplier" required></div>
            <div class="col-md-4"><input class="form-control" name="supplier_name" placeholder="Nama Supplier" required></div>
            <div class="col-md-3"><input class="form-control" name="email" placeholder="Email"></div>
            <div class="col-md-3"><button class="btn btn-primary w-100">Simpan Supplier</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Daftar Supplier</h3></div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap mb-0">
            <thead><tr><th>Kode</th><th>Nama</th><th>Email</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($suppliers as $s)
                <tr>
                    <td>{{ $s->supplier_code }}</td>
                    <td>{{ $s->supplier_name }}</td>
                    <td>{{ $s->email }}</td>
                    <td><span class="badge bg-success">Aktif</span></td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted">Belum ada data</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
