@props([
    'route' => null,
    'label' => null,
    'entity' => null,
    'icon' => '+',
    'class' => '',
    'title' => null,
    'position' => 'bottom-right',
])

<?php
$entity = $entity ?? (request()->route()->getPrefix() ?? 'default');
$resolvedRoute = $route ?? route("{$entity}.create");
$resolvedLabel = $label ?? \App\Support\LabelRegistry::action($entity, 'create');
$resolvedTitle = $title ?? $resolvedLabel;
?>

<a href="{{ $resolvedRoute }}" class="fab-add {{ $class }}" title="{{ $resolvedTitle }}" style="{{ $position === 'bottom-right' ? 'bottom: 24px; right: 24px;' : ($position === 'bottom-left' ? 'bottom: 24px; left: 24px;' : '') }}">
    <span class="fab-icon">{{ $icon }}</span>
    @if ($label || $entity)
        <span class="fab-label">{{ $resolvedLabel }}</span>
    @endif
</a>

<style>
    .fab-add {
        position: fixed;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: var(--lemon-green, #9ecb3c);
        color: var(--lemon-ink, #304218);
        border: none;
        box-shadow: 0 4px 12px rgba(158, 203, 60, 0.35);
        cursor: pointer;
        z-index: 100;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .fab-add:hover {
        transform: scale(1.08) translateY(-2px);
        box-shadow: 0 6px 20px rgba(158, 203, 60, 0.5);
        color: var(--lemon-ink, #304218);
        text-decoration: none;
    }

    .fab-icon {
        font-size: 22px;
        font-weight: 700;
        line-height: 1;
    }

    .fab-label {
        font-size: 9px;
        font-weight: 600;
        margin-top: 1px;
        white-space: nowrap;
    }

    @media (max-width: 768px) {
        .fab-add {
            width: 48px;
            height: 48px;
            bottom: 16px;
            right: 16px;
        }
    }
</style>