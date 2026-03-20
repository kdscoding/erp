@extends('layouts.erp')
@php($title='Tambah User')
@php($header='Tambah User')
@section('content')
<div class="card card-primary card-outline">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title">Form Tambah User</h3>
    <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">Kembali ke Daftar</a>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('users.store') }}">
      @csrf
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Nama</label>
          <input type="text" name="name" value="{{ old('name') }}" class="form-control form-control-sm" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">NIK</label>
          <input type="text" name="nik" value="{{ old('nik') }}" class="form-control form-control-sm" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" value="{{ old('email') }}" class="form-control form-control-sm" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Role</label>
          <select name="role_slug" class="form-select form-select-sm" required>
            <option value="">Pilih role</option>
            @foreach($roles as $role)
              <option value="{{ $role->slug }}" @selected(old('role_slug') === $role->slug)>{{ $role->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Password Awal</label>
          <input type="password" name="password" class="form-control form-control-sm" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Konfirmasi Password Awal</label>
          <input type="password" name="password_confirmation" class="form-control form-control-sm" required>
        </div>
      </div>
      <button class="btn btn-primary btn-sm">Simpan User</button>
    </form>
  </div>
</div>
@endsection
