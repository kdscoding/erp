@extends('layouts.erp')

@php
    $entity = 'supplier';
    $title = 'Edit ' . entity_label($entity, 'singular');
    $header = $title;
    $headerSubtitle = 'Perbarui identitas supplier.';
@endphp

@section('content')
    <div class="page-shell">
        <x-ui.page-header
            :entity="$entity"
            :title="$title"
            :subtitle="$headerSubtitle"
            :actions="[
                ['label' => 'Kembali', 'url' => route('suppliers.index'), 'class' => 'btn btn-light btn-sm'],
            ]"
        />

        <div class="form-wrapper">
            <form method="POST" action="{{ route('suppliers.update', $supplier->id) }}">
                @csrf
                @method('PUT')

                <x-ui.form-field
                    :module="$entity"
                    name="supplier_code"
                    :value="$supplier->supplier_code"
                    :attributes="['readonly' => true]"
                    :required="true"
                />

                <x-ui.form-field
                    :module="$entity"
                    name="supplier_name"
                    :value="$supplier->supplier_name"
                    :required="true"
                />

                <x-ui.form-field
                    :module="$entity"
                    name="status"
                    type="select"
                    :value="$supplier->status ?? true"
                    :required="true"
                />

                <div class="form-actions">
                    <a href="{{ route('suppliers.index') }}" class="btn btn-light btn-sm">Batal</a>
                    <button type="submit" class="btn btn-primary btn-sm px-5">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .form-wrapper {
        max-width: 540px;
        margin: 24px auto;
        padding: 24px;
    }
    .form-wrapper .form-group {
        margin-bottom: 20px;
    }
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 28px;
        padding-top: 20px;
        border-top: 1px solid var(--lemon-line, #dfe6b8);
    }
</style>
@endpush