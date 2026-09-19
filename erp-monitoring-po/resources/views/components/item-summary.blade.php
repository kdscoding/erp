@props([
    'counts' => [],
    'received' => 0,
    'ordered' => 0,
    'unit' => '',
])

@php
    $percent = (float) $ordered > 0 ? round((float) $received / (float) $ordered * 100, 1) : 0;
    $progressLabel = \App\Support\NumberFormatter::trim($received) . ' / ' . \App\Support\NumberFormatter::trim($ordered);

    $statusConfig = [
        'waiting' => ['label' => 'Waiting', 'class' => 'text-secondary'],
        'confirmed' => ['label' => 'Confirmed', 'class' => 'text-warning'],
        'late' => ['label' => 'Late', 'class' => 'text-danger'],
        'partial' => ['label' => 'Partial', 'class' => 'text-primary'],
        'closed' => ['label' => 'Closed', 'class' => 'text-success'],
        'force_closed' => ['label' => 'Force Closed', 'class' => 'text-dark'],
        'cancelled' => ['label' => 'Cancelled', 'class' => 'text-danger'],
    ];
@endphp

<div class="item-summary">
    <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-2">
        @foreach (['total', 'waiting', 'confirmed', 'late', 'partial', 'closed', 'force_closed', 'cancelled'] as $key)
            @php($count = $counts[$key] ?? 0)
            @if ($count > 0)
                @php($label = $key === 'total' ? 'Total' : ($statusConfig[$key]['label'] ?? ucfirst(str_replace('_', ' ', $key))))
                @php($textClass = $key === 'total' ? 'text-dark' : ($statusConfig[$key]['class'] ?? ''))
                <span class="badge bg-light {{ $textClass }} border flex-nowrap">
                    <i class="fas fa-circle" style="font-size: 6px; margin-right: 4px;"></i>
                    {{ $label }}: {{ $count }}
                </span>
            @endif
        @endforeach
    </div>

    @if ((float) $ordered > 0)
        <div class="progress" style="height: 10px">
            <div class="progress-bar bg-success" role="progressbar"
                style="width: {{ $percent }}%"
                aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-1">
            <span class="small text-muted">{{ $progressLabel }} {{ $unit }}</span>
            <span class="small fw-bold">{{ $percent }}%</span>
        </div>
    @endif
</div>
