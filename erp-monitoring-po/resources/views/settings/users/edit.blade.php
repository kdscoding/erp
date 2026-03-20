@extends('layouts.erp')
@php($title='Kelola User')
@php($header='Kelola User')
@section('content')
<div class="row">
  <div class="col-lg-6">
    <div class="card card-primary card-outline">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Data User</h3>
        <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">Kembali ke Daftar</a>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('users.update', $user) }}">
          @csrf
          @method('PUT')
          <div class="mb-3">
            <label class="form-label">Nama</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">NIK</label>
            <input type="text" name="nik" value="{{ old('nik', $user->nik) }}" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role_slug" class="form-select" required>
              @foreach($roles as $role)
                <option value="{{ $role->slug }}" @selected(old('role_slug', $user->primaryRoleSlug()) === $role->slug)>{{ $role->name }}</option>
              @endforeach
            </select>
          </div>
          <button class="btn btn-primary">Update Data User</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card card-warning card-outline">
      <div class="card-header">
        <h3 class="card-title">Reset Password</h3>
      </div>
      <div class="card-body">
        @if($pendingResetRequest)
          <div class="alert alert-warning">
            <div><strong>Request pending:</strong> {{ \Carbon\Carbon::parse($pendingResetRequest->requested_at)->format('d-m-Y H:i') }}</div>
            <div class="mt-2"><strong>Keterangan user:</strong></div>
            <div>{{ $pendingResetRequest->request_note }}</div>
          </div>
          <form method="POST" action="{{ route('users.reset-password', $user) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
              <label class="form-label">Password Baru</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Konfirmasi Password Baru</label>
              <input type="password" name="password_confirmation" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Catatan Tindak Lanjut Admin</label>
              <textarea name="admin_note" class="form-control" rows="3" placeholder="Contoh: reset diproses setelah verifikasi identitas user." required>{{ old('admin_note') }}</textarea>
            </div>
            <button class="btn btn-warning">Proses Reset Password</button>
          </form>
        @else
          <div class="alert alert-secondary mb-0">
            Belum ada request reset password yang pending dari user ini.
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
