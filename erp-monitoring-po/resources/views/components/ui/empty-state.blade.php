@props([
    'icon' => '📦',
    'title' => 'Belum ada data',
    'subtitle' => 'Mulai tambah data baru untuk melihat data di sini.',
    'action' => null,
    'class' => '',
    'size' => 'md',
])

<div class="empty-state {{ $class }} empty-state-{{ $size }}" style="text-align: center; padding: {{ $size === 'sm' ? '20px' : ($size === 'lg' ? '60px' : '40px') }} 20px;">
    <div class="empty-icon" style="font-size: {{ $size === 'sm' ? '32px' : ($size === 'lg' ? '64px' : '48px') }}; margin-bottom: 12px;">{{ $icon }}</div>
    <div class="empty-title" style="font-size: {{ $size === 'sm' ? '14px' : ($size === 'lg' ? '20px' : '16px') }}; font-weight: 600; color: var(--text-primary, #1e293b); margin-bottom: 4px;">{{ $title }}</div>
    <div class="empty-subtitle" style="font-size: {{ $size === 'sm' ? '12px' : ($size === 'lg' ? '16px' : '13px') }}; color: var(--text-secondary, #64748b); margin-bottom: 16px;">{{ $subtitle }}</div>
    @if ($action)
        @php
            $actionUrl = $action['url'] ?? '#';
            $actionLabel = $action['label'] ?? 'Tambah';
            $actionClass = $action['class'] ?? 'btn btn-primary btn-sm px-4';
            $actionIcon = $action['icon'] ?? '';
        @endphp
        <a href="{{ $actionUrl }}" class="{{ $actionClass }}">
            @if ($actionIcon)
                <i class="{{ $actionIcon }}"></i>
            @endif
            {{ $actionLabel }}
        </a>
    @endif
</div>