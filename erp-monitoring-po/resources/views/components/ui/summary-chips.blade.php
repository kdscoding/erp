@props([
    'stats' => [],
    'module' => null,
    'context' => 'default',
    'class' => '',
])

<?php
$module = $module ?? (request()->route()->getPrefix() ?? 'default');
$resolvedStats = $stats ?: \App\Support\LabelRegistry::get($module, 'summary_stats', []);
$contextStats = $resolvedStats[$context] ?? $resolvedStats;
?>

<div class="summary-chips {{ $class }}">
    @foreach ($contextStats as $key => $stat)
        @php
            $label = $stat['label'] ?? $key;
            $value = $stat['value'] ?? 0;
            $color = $stat['color'] ?? 'primary';
            $url = $stat['url'] ?? null;
        @endphp

        @if ($url)
            <a href="{{ $url }}" class="summary-chip summary-chip-{{ $color }}">
        @else
            <div class="summary-chip summary-chip-{{ $color }}">
        @endif
                <div class="summary-chip-label">{{ $label }}</div>
                <div class="summary-chip-value">{{ $value }}</div>
            @if ($url)
            </a>
        @else
            </div>
        @endif
    @endforeach
</div>

<style>
    .summary-chips {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }

    .summary-chip {
        min-width: 120px;
        padding: 12px 16px;
        border-radius: 10px;
        border: 1px solid var(--po-border, #e2e8f0);
        background: #f8fafc;
        text-decoration: none;
        color: #334155;
        transition: all 0.15s ease;
        flex: 1 0 auto;
    }

    .summary-chip:hover {
        border-color: #93c5fd;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.08);
        transform: translateY(-1px);
    }

    .summary-chip-label {
        display: block;
        font-size: 11px;
        line-height: 1.25;
        color: var(--po-muted, #64748b);
        margin-bottom: 4px;
    }

    .summary-chip-value {
        display: block;
        font-size: 20px;
        line-height: 1.1;
        font-weight: 700;
        color: #0f172a;
    }

    .summary-chip-primary { border-left: 3px solid #2563eb; }
    .summary-chip-success { border-left: 3px solid #16a34a; }
    .summary-chip-warning { border-left: 3px solid #d97706; }
    .summary-chip-danger { border-left: 3px solid #dc2626; }
    .summary-chip-secondary { border-left: 3px solid #64748b; }
</style>