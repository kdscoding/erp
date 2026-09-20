@extends('layouts.erp')

@php($title = 'Profil Pengguna')
@php($header = 'Profil Pengguna')

@section('content')
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Informasi Profil</h3></div>
            <div class="card-body">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Ubah Password</h3></div>
            <div class="card-body">
                @include('profile.partials.update-password-form')
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card card-outline card-danger">
            <div class="card-header"><h3 class="card-title">Nonaktifkan Akun</h3></div>
            <div class="card-body">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</div>
@endsection
