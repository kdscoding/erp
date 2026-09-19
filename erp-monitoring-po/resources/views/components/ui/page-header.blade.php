@props([
    'title' => null,
    'subtitle' => null,
    'entity' => null,
    'actions' => [],
    'breadcrumb' => [],
    'class' => '',
    'icon' => null,
])

<?php
$entity = $entity ?? (request()->route()->getPrefix() ?? 'default');
$resolvedTitle = $title ?? \App\Support\LabelRegistry::entity($entity, 'plural');
$resolvedSubtitle = $subtitle ?? \App\Support\LabelRegistry::get($entity, 'entity.description', '');
$entitySingular = \App\Support\LabelRegistry::entity($entity, 'singular');
?>

<div class="page-header {{ $class }}">
    @if ($breadcrumb)
        <nav class="breadcrumb-nav mb-3" aria-label="Breadcrumb">
            @foreach ($breadcrumb as $index => $item)
                @if ($index > 0)
                    <span class="breadcrumb-separator">/</span>
                @endif
                @if (isset($item['url']) && $index < count($breadcrumb) - 1)
                    <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                @else
                    <span class="breadcrumb-current">{{ $item['label'] }}</span>
                @endif
            @endforeach
        </nav>
    @endif

    <div class="page-header-content">
        <div class="page-header-main">
            @if ($icon)
                <span class="page-header-icon">{{ $icon }}</span>
            @endif
            <div class="page-header-text">
                <h1 class="page-header-title">{{ $resolvedTitle }}</h1>
                @if ($resolvedSubtitle)
                    <p class="page-header-subtitle">{{ $resolvedSubtitle }}</p>
                @endif
            </div>
        </div>

        @if ($actions)
            <div class="page-header-actions">
                @foreach ($actions as $action)
                    @php
                        $actionLabel = $action['label'] ?? $action['text'] ?? '';
                        $actionUrl = $action['url'] ?? '#';
                        $actionClass = $action['class'] ?? 'btn btn-primary btn-sm';
                        $actionIcon = $action['icon'] ?? '';
                        $actionAttrs = $action['attributes'] ?? [];
                    @endphp
                    <a href="{{ $actionUrl }}" class="{{ $actionClass }}" {{ $actionAttrs }}>
                        @if ($actionIcon)
                            <i class="{{ $actionIcon }}"></i>
                        @endif
                        {{ $actionLabel }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>