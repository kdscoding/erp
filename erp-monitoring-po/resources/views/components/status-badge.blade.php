@props([
    'status' => '',
    'scope' => 'po', // po | item
])

@php
    $status = trim((string) $status);

    $class = match ($status) {
        'Closed' => 'bg-success',
        'Open' => 'bg-warning text-dark',
        'Confirmed' => 'bg-warning text-dark',
        'Waiting' => 'bg-secondary',
        'Partial' => 'bg-primary',
        'Late' => 'bg-danger',
        'Cancelled' => 'bg-danger',
        default => 'bg-secondary',
    };

    $group = $scope === 'item' ? 'po_item_status' : 'po_status';
    $label = \App\Support\TermCatalog::label($group, $status, $status !== '' ? $status : '-');
@endphp

<span {{ $attributes->merge(['class' => 'badge ' . $class]) }}>
    {{ $label }}
</span>
