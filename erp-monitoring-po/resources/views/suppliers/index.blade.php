@extends('layouts.erp')

@php
    $entity = 'supplier';
    $title = entity_label($entity, 'plural');
    $header = $title;
    $headerSubtitle = label($entity, 'entity.description');
@endphp

@section('content')
    <div class="page-shell">
        <x-ui.page-header
            :entity="$entity"
            :actions="[
                ['label' => action_label($entity, 'export'), 'url' => route('suppliers.export'), 'icon' => 'fas fa-file-excel', 'class' => 'btn btn-light btn-sm'],
                ['label' => action_label($entity, 'create'), 'url' => route('suppliers.create'), 'icon' => 'fas fa-plus', 'class' => 'btn btn-primary btn-sm'],
            ]"
        />

        <x-ui.filter-bar
            :module="$entity"
            :context="'default'"
            :inline="true"
        />

        <x-ui.data-table
            :entity="$entity"
            :view="'index'"
            :rows="$suppliers"
            :emptyState="[
                'icon' => '📦',
                'title' => label($entity, 'empty.title', 'Belum ada data supplier'),
                'subtitle' => label($entity, 'empty.subtitle', 'Mulai tambah supplier baru untuk melihat data di sini.'),
                'action' => ['label' => action_label($entity, 'create'), 'url' => route('suppliers.create'), 'class' => 'btn btn-primary btn-sm px-4 mt-2'],
            ]"
            :rowClass="function ($row) { return !$row->status ? 'row-inactive' : ''; }"
            :rowId="'id'"
            :columns="[
                'supplier_code' => ['label' => column_label($entity, 'supplier_code'), 'render' => function ($row) { return '<div class=\"doc-number\">' . $row->supplier_code . '</div>'; }],
                'supplier_name' => ['label' => column_label($entity, 'supplier_name'), 'render' => function ($row) { return '<div class=\"supplier-name\">' . $row->supplier_name . '</div>'; }],
                'updated_at' => ['label' => column_label($entity, 'updated_at'), 'render' => function ($row) { return $row->updated_at ? '<small class=\"text-muted\">' . \Carbon\Carbon::parse($row->updated_at)->diffForHumans() . '</small>' : '<span class=\"text-muted\">-</span>'; }],
                'status' => ['label' => column_label($entity, 'status'), 'render' => function ($row) {
                    return '<form action=\"' . route('suppliers.toggle-status', $row->id) . '\" method=\"POST\" class=\"d-inline status-toggle-form\">'
                        . csrf_field()
                        . method_field('PATCH')
                        . '<label class=\"status-toggle\">
                            <input type=\"checkbox\" name=\"status\" ' . ($row->status ? 'checked' : '') . ' onchange=\"this.form.submit()\">
                            <span class=\"toggle-slider\"></span>
                        </label>
                        <span class=\"status-text ' . ($row->status ? 'text-success' : 'text-muted') . '\">
                            ' . ($row->status ? 'Aktif' : 'Nonaktif') . '
                        </span>
                    </form>';
                }],
                'actions' => ['label' => column_label($entity, 'actions'), 'tdAttributes' => ['class' => 'text-end'], 'render' => function ($row) {
                    return '<div class=\"action-stack\">
                        <a href=\"' . route('suppliers.edit', $row->id) . '\" class=\"btn btn-sm btn-outline-primary\" title=\"' . action_label($entity, 'edit') . '\">✏️</a>
                    </div>';
                }],
            ]"
        />
    </div>

    <x-ui.fab :entity="$entity" />
@endsection