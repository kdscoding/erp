@extends('layouts.erp')
@php($title='Edit Supplier')
@php($header='Ubah Data Supplier')
@section('content')
@if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title">Form Edit Supplier</h3></div>
    <div class="card-body">
        <form method="POST" action="{{ route('suppliers.update', $supplier->id) }}" class="row g-3">
            @csrf @method('PUT')
            <div class="col-md-3"><label class="form-label">Kode Supplier</label><input class="form-control" name="supplier_code" value="{{ old('supplier_code', $supplier->supplier_code) }}" required></div>
            <div class="col-md-5"><label class="form-label">Nama Supplier</label><input class="form-control" name="supplier_name" value="{{ old('supplier_name', $supplier->supplier_name) }}" required></div>
            <div class="col-md-4"><label class="form-label">PIC</label><input class="form-control" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}"></div>
            <div class="col-md-4"><label class="form-label">Telepon</label><input class="form-control" name="phone" value="{{ old('phone', $supplier->phone) }}"></div>
            <div class="col-md-4"><label class="form-label">Email</label><input class="form-control" name="email" value="{{ old('email', $supplier->email) }}"></div>
            <div class="col-md-4"><label class="form-label">Alamat</label><input class="form-control" name="address" value="{{ old('address', $supplier->address) }}"></div>
            <div class="col-12 d-flex justify-content-end gap-2"><a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Kembali</a><button class="btn btn-primary">Simpan Perubahan</button></div>
        </form>
    </div>
</div>
@endsection
