@props([
    'columns' => [],
    'rows' => [],
    'entity' => null,
    'view' => 'index',
    'emptyState' => null,
    'sortable' => false,
    'exportable' => false,
    'striped' => true,
    'hoverable' => true,
    'rowClass' => null,
    'rowId' => null,
    'tableClass' => 'table table-hover ui-table',
    'theadClass' => '',
    'tbodyClass' => '',
    'attributes' => [],
])

<?php
$entity = $entity ?? (request()->route()->getPrefix() ?? 'default');
$resolvedColumns = $columns ?: (function () use ($entity, $view) {
    return \App\Support\LabelRegistry::tableColumns($entity, $view);
})();

$emptyStateContent = $emptyState ?? [
    'icon' => '📦',
    'title' => 'Belum ada data',
    'subtitle' => 'Mulai tambah data baru untuk melihat data di sini.',
    'action' => null,
];
?>

<div class="table-wrap table-responsive">
    <table class="{{ $tableClass }}" {{ $attributes->merge(['data-export-title' => $entity]) }}>
        <thead class="{{ $theadClass }}">
            <tr>
                @foreach ($resolvedColumns as $key => $column)
                    @php
                        $label = is_array($column) ? ($column['label'] ?? $column['title'] ?? \App\Support\LabelRegistry::columnLabel($entity, $key, $view)) : $column;
                        $colAttributes = is_array($column) && is_array($column['attributes'] ?? null) ? $column['attributes'] : [];
                        $colAttributesBag = (new \Illuminate\View\ComponentAttributeBag())->merge($colAttributes);
                        $isSortable = $sortable && (is_array($column) ? ($column['sortable'] ?? true) : true);
                    @endphp
                    <th {{ $colAttributesBag }} {{ $isSortable ? 'data-sortable="true"' : '' }}>
                        {{ $label }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="{{ $tbodyClass }}">
            @forelse ($rows as $row)
                @php
                    $rowClassName = $rowClass ? (is_callable($rowClass) ? $rowClass($row) : $rowClass) : '';
                    $rowIdValue = $rowId ? (is_callable($rowId) ? $rowId($row) : $row->{$rowId} ?? null) : null;
                @endphp
                <tr class="{{ $rowClassName }}" {{ $rowIdValue ? 'data-row-id="' . $rowIdValue . '"' : '' }}>
                    @foreach ($resolvedColumns as $key => $column)
                        @php
                            $render = is_array($column) ? ($column['render'] ?? null) : null;
                            $value = $render ? $render($row) : ($row->{$key} ?? ($row[$key] ?? '-'));
                            $tdAttributes = is_array($column) && is_array($column['tdAttributes'] ?? null) ? $column['tdAttributes'] : [];
                            $tdAttributesBag = (new \Illuminate\View\ComponentAttributeBag())->merge($tdAttributes);
                        @endphp
                        <td {{ $tdAttributesBag }}>
                            {!! $value !!}
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($resolvedColumns) }}">
                        <x-ui.empty-state
                            :icon="$emptyStateContent['icon']"
                            :title="$emptyStateContent['title']"
                            :subtitle="$emptyStateContent['subtitle']"
                            :action="$emptyStateContent['action']"
                        />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($exportable)
    <div class="table-export-actions mt-2">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-export-table="{{ $entity }}">
            <i class="fas fa-file-excel"></i> Export Excel
        </button>
    </div>
@endif