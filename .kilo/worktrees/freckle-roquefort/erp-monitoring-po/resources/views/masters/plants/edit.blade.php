@extends('layouts.erp')
@php($title='Edit Plant')
@php($header='Edit Plant')
@php($headerSubtitle='Jaga konsistensi master plant karena muncul di header PO dan laporan.')

@section('content')
<form method="POST" action="{{ route('plants.update', $plant->id) }}">
    @csrf
    @method('PUT')
    <x-master-edit-layout
        title="Form Edit Plant"
        subtitle="Jaga konsistensi master plant karena akan muncul di header PO dan laporan monitoring."
        :back-route="route('plants.index')">
        <div class="col-md-4"><label class="form-label">Kode Plant</label><input class="form-control form-control-sm" name="plant_code" value="{{ old('plant_code', $plant->plant_code) }}" required></div>
        <div class="col-md-8"><label class="form-label">Nama Plant</label><input class="form-control form-control-sm" name="plant_name" value="{{ old('plant_name', $plant->plant_name) }}" required></div>
    </x-master-edit-layout>
</form>
@endsection
