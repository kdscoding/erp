@extends('layouts.erp')
@php($title='Edit Supplier')
@php($header='Edit Supplier')
@php($headerSubtitle='Perbarui identitas supplier tanpa mengubah histori transaksi yang sudah ada.')

@section('content')
<form method="POST" action="{{ route('suppliers.update', $supplier->id) }}">
    @csrf
    @method('PUT')
    <x-master-edit-layout
        title="Form Edit Supplier"
        subtitle="Perbarui identitas supplier, PIC, dan kanal kontak tanpa mengubah histori transaksi yang sudah ada."
        :back-route="route('suppliers.index')">
        <div class="col-md-3"><label class="form-label">Kode Supplier</label><input class="form-control form-control-sm" name="supplier_code" value="{{ old('supplier_code', $supplier->supplier_code) }}" required></div>
        <div class="col-md-5"><label class="form-label">Nama Supplier</label><input class="form-control form-control-sm" name="supplier_name" value="{{ old('supplier_name', $supplier->supplier_name) }}" required></div>
        <div class="col-md-4"><label class="form-label">PIC</label><input class="form-control form-control-sm" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}"></div>
        <div class="col-md-4"><label class="form-label">Telepon</label><input class="form-control form-control-sm" name="phone" value="{{ old('phone', $supplier->phone) }}"></div>
        <div class="col-md-4"><label class="form-label">Email</label><input class="form-control form-control-sm" name="email" value="{{ old('email', $supplier->email) }}"></div>
        <div class="col-md-4"><label class="form-label">Alamat</label><textarea class="form-control form-control-sm" name="address" rows="3">{{ old('address', $supplier->address) }}</textarea></div>
    </x-master-edit-layout>
</form>
@endsection
