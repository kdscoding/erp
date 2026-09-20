@props([
    'title',
    'subtitle' => null,
    'backRoute',
    'backLabel' => 'Kembali',
    'submitLabel' => 'Simpan Perubahan',
])

<div class="page-shell">
    <section class="page-head">
        <div class="page-head-main">
            <h2 class="page-section-title">{{ $title }}</h2>
            @if ($subtitle)
                <p class="page-section-subtitle">{{ $subtitle }}</p>
            @endif
        </div>

        <div class="page-actions">
            <a href="{{ $backRoute }}" class="btn btn-sm btn-light">{{ $backLabel }}</a>
        </div>
    </section>

    <section class="ui-surface">
        <div class="ui-surface-body">
            <div class="row g-3">
                {{ $slot }}
            </div>

            <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="{{ $backRoute }}" class="btn btn-light btn-sm">{{ $backLabel }}</a>
                <button class="btn btn-primary btn-sm">{{ $submitLabel }}</button>
            </div>
        </div>
    </section>
</div>
