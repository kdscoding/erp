@props([
    'title',
    'subtitle' => null,
    'backRoute',
    'backLabel' => 'Kembali',
    'submitLabel' => 'Simpan Perubahan',
])

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">{{ $title }}</h3>
    </div>
    <div class="card-body">
        @if ($subtitle)
            <p class="text-muted mb-3">{{ $subtitle }}</p>
        @endif

        {{ $slot }}

        <div class="col-12 d-flex justify-content-end gap-2 mt-3">
            <a href="{{ $backRoute }}" class="btn btn-secondary btn-sm">{{ $backLabel }}</a>
            <button class="btn btn-primary btn-sm">{{ $submitLabel }}</button>
        </div>
    </div>
</div>
