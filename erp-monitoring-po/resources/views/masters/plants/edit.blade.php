@extends('layouts.erp')
@php($title='Edit Plant')
@php($header='Ubah Data Plant')
@section('content')
@if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title">Form Edit Plant</h3></div>
    <div class="card-body">
        <form method="POST" action="{{ route('plants.update', $plant->id) }}" class="row g-3">
            @csrf @method('PUT')
            <div class="col-md-4"><label class="form-label">Kode Plant</label><input class="form-control form-control-sm" name="plant_code" value="{{ old('plant_code', $plant->plant_code) }}" required></div>
            <div class="col-md-6"><label class="form-label">Nama Plant</label><input class="form-control form-control-sm" name="plant_name" value="{{ old('plant_name', $plant->plant_name) }}" required></div>
            <div class="col-12 d-flex justify-content-end gap-2"><a href="{{ route('plants.index') }}" class="btn btn-secondary btn-sm">Kembali</a><button class="btn btn-primary btn-sm">Simpan Perubahan</button></div>
        </form>
    </div>
</div>
@endsection
