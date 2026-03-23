@extends('layouts.erp')

@php
    $title = 'Settings';
    $header = 'System Settings';
@endphp

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <div class="fw-bold mb-1">Ada data yang belum valid:</div>
                    <ul class="mb-0 pl-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="alert alert-info">
                Pengaturan user sekarang dikelola terpisah melalui menu
                <a href="{{ route('users.index') }}">Daftar User</a>.
            </div>

            <form method="POST" action="{{ route('settings.update') }}">
                @csrf
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" value="1" name="allow_over_receipt"
                        id="allow_over_receipt" {{ $allowOver == '1' ? 'checked' : '' }}>
                    <label class="form-check-label" for="allow_over_receipt">
                        Izinkan Over Receipt
                    </label>
                </div>
                <button class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>

    <div class="card card-outline card-secondary mt-3">
        <div class="card-header">
            <h3 class="card-title">Document Terms</h3>
        </div>
        <div class="card-body">
            <style>
                .term-preview-box {
                    min-width: 180px;
                    padding: .65rem;
                    border: 1px dashed #d6d9de;
                    border-radius: .5rem;
                    background: #f8f9fa;
                    text-align: center;
                }

                .term-preview-box .badge {
                    display: inline-block !important;
                    font-size: .78rem;
                    padding: .45em .75em;
                    white-space: normal;
                    line-height: 1.2;
                    max-width: 100%;
                }

                .term-help {
                    font-size: .75rem;
                    color: #6c757d;
                    margin-top: .35rem;
                }
            </style>

            <div class="alert alert-light border">
                Ubah istilah tampilan status dari UI. Kode internal tetap sama, jadi flow sistem tidak berubah walaupun
                label display diganti.
                @if (!empty($hasBadgeColumns) && $hasBadgeColumns)
                    <hr class="my-2">
                    <div class="mb-0">
                        Badge juga sekarang dikendalikan dari database:
                        <code>badge_class</code> untuk warna badge dan
                        <code>badge_text</code> untuk warna teks badge.
                    </div>
                @endif
            </div>

            <form method="POST" action="{{ route('settings.document-terms.update') }}">
                @csrf

                @if (!empty($documentTermGroups) && count($documentTermGroups) > 0)
                    @foreach ($documentTermGroups as $groupKey => $terms)
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                <div class="font-weight-bold text-uppercase">
                                    {{ str_replace('_', ' ', $groupKey) }}
                                </div>
                                <div class="small text-muted">
                                    {{ count($terms) }} term
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 180px;">Code</th>
                                            <th style="min-width: 220px;">Label</th>
                                            <th style="min-width: 220px;">Description</th>
                                            <th style="min-width: 80px;">Sort</th>
                                            <th style="width: 100px;">Aktif</th>
                                            @if (!empty($hasBadgeColumns) && $hasBadgeColumns)
                                                <th style="min-width: 200px;">Badge Class</th>
                                                <th style="min-width: 180px;">Badge Text</th>
                                                <th style="min-width: 150px;">Preview</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($terms as $term)
                                            @php
                                                $labelValue = old(
                                                    'document_terms.' . $term->id . '.label',
                                                    $term->label,
                                                );
                                                $descValue = old(
                                                    'document_terms.' . $term->id . '.description',
                                                    $term->description,
                                                );
                                                $sortValue = old(
                                                    'document_terms.' . $term->id . '.sort_order',
                                                    $term->sort_order,
                                                );
                                                $activeValue = old(
                                                    'document_terms.' . $term->id . '.is_active',
                                                    $term->is_active,
                                                );
                                                $badgeClassValue = old(
                                                    'document_terms.' . $term->id . '.badge_class',
                                                    isset($term->badge_class) ? $term->badge_class : 'bg-secondary',
                                                );
                                                $badgeTextValue = old(
                                                    'document_terms.' . $term->id . '.badge_text',
                                                    isset($term->badge_text) ? $term->badge_text : 'text-white',
                                                );
                                            @endphp

                                            <tr>
                                                <td>
                                                    <div class="fw-semibold">{{ $term->code }}</div>
                                                    <div class="small text-muted">ID: {{ $term->id }}</div>
                                                    <div class="small text-muted">Group: {{ $term->group_key }}</div>
                                                </td>

                                                <td>
                                                    <input type="text" name="document_terms[{{ $term->id }}][label]"
                                                        value="{{ $labelValue }}" class="form-control form-control-sm"
                                                        required>
                                                </td>

                                                <td>
                                                    <input type="text"
                                                        name="document_terms[{{ $term->id }}][description]"
                                                        value="{{ $descValue }}" class="form-control form-control-sm">
                                                </td>

                                                <td>
                                                    <input type="number" min="0" max="9999"
                                                        name="document_terms[{{ $term->id }}][sort_order]"
                                                        value="{{ $sortValue }}" class="form-control form-control-sm"
                                                        required>
                                                </td>

                                                <td class="text-center">
                                                    <input type="checkbox"
                                                        name="document_terms[{{ $term->id }}][is_active]"
                                                        value="1" {{ $activeValue ? 'checked' : '' }}>
                                                </td>

                                                @if (!empty($hasBadgeColumns) && $hasBadgeColumns)
                                                    <td>
                                                        <input type="text"
                                                            name="document_terms[{{ $term->id }}][badge_class]"
                                                            value="{{ $badgeClassValue }}"
                                                            class="form-control form-control-sm"
                                                            placeholder="bg-success / bg-danger / bg-warning">
                                                        <div class="term-help">
                                                            Contoh: <code>bg-secondary</code>, <code>bg-warning</code>,
                                                            <code>bg-danger</code>, <code>bg-success</code>,
                                                            <code>bg-primary</code>, <code>bg-info</code>
                                                        </div>
                                                    </td>

                                                    <td>
                                                        <input type="text"
                                                            name="document_terms[{{ $term->id }}][badge_text]"
                                                            value="{{ $badgeTextValue }}"
                                                            class="form-control form-control-sm"
                                                            placeholder="text-white / text-dark">
                                                        <div class="term-help">
                                                            Contoh: <code>text-white</code> atau <code>text-dark</code>
                                                        </div>
                                                    </td>

                                                    <td>
                                                        <div class="term-preview-box">
                                                            <span
                                                                class="badge {{ $badgeClassValue }} {{ $badgeTextValue }}">
                                                                {{ $labelValue }}
                                                            </span>
                                                        </div>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-muted">Belum ada document terms.</div>
                @endif

                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary">Simpan Document Terms</button>
                </div>
            </form>
        </div>
    </div>
@endsection
