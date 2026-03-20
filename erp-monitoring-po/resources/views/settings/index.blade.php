@extends('layouts.erp')
@php($title='Settings')
@php($header='System Settings')
@section('content')
<div class="card card-primary card-outline">
  <div class="card-body">
    <div class="alert alert-info">
      Pengaturan user sekarang dikelola terpisah melalui menu
      <a href="{{ route('users.index') }}">Daftar User</a>.
    </div>
    <form method="POST" action="{{ route('settings.update') }}">@csrf
      <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" value="1" name="allow_over_receipt" id="allow_over_receipt" {{ $allowOver == '1' ? 'checked' : '' }}>
        <label class="form-check-label" for="allow_over_receipt">Izinkan Over Receipt</label>
      </div>
      <button class="btn btn-primary">Simpan</button>
    </form>
  </div>
</div>
@endsection
