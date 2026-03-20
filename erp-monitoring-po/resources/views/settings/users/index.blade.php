@extends('layouts.erp')
@php($title='Daftar User')
@php($header='Daftar User')
@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title">Daftar User</h3>
    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">Tambah User</a>
  </div>
  <div class="card-body table-responsive">
    <table class="table table-striped data-table">
      <thead>
        <tr>
          <th>Nama</th>
          <th>NIK</th>
          <th>Email</th>
          <th>Role</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($users as $user)
        <tr>
          <td>{{ $user->name }}</td>
          <td>{{ $user->nik }}</td>
          <td>{{ $user->email }}</td>
          <td>{{ $user->roles->pluck('name')->join(', ') ?: '-' }}</td>
          <td>
            <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-secondary' }}">
              {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
            </span>
          </td>
          <td class="text-nowrap">
            <form method="POST" action="{{ route('users.toggle-status', $user) }}" class="d-inline">
              @csrf
              @method('PATCH')
              <button class="btn btn-sm {{ $user->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
              </button>
            </form>
            <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Kelola</a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
