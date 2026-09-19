@extends('layouts.erp')

@php
    $entity = 'supplier';
    $title = 'Tambah ' . entity_label($entity, 'singular');
    $header = $title;
    $headerSubtitle = 'Input data supplier baru.';
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
            <form method="POST" action="{{ route('suppliers.store') }}">
                @csrf
                <x-ui.form-field
                    :module="$entity"
                    name="supplier_code"
                    :required="true"
                />

                <x-ui.form-field
                    :module="$entity"
                    name="supplier_name"
                    :required="true"
                />

                <x-ui.form-field
                    :module="$entity"
                    name="status"
                    type="select"
                    :required="true"
                />

                <div class="form-actions">
                    <a href="{{ route('suppliers.index') }}" class="btn btn-light btn-sm">Batal</a>
                    <button type="submit" class="btn btn-primary btn-sm px-5">{{ action_label($entity, 'create') }}</button>
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