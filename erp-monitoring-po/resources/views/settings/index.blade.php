@extends('layouts.erp')
@php($title='Settings')
@php($header='System Settings')
@section('content')
<div class="card card-primary card-outline">
  <div class="card-body">
    <div class="alert alert-info">
      Pengaturan user sekarang dikelola terpisah melalui menu
      <a href="{{ route('users.index') }}">Daftar User</a>.
    </div>
    <form method="POST" action="{{ route('settings.update') }}">@csrf
      <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" value="1" name="allow_over_receipt" id="allow_over_receipt" {{ $allowOver == '1' ? 'checked' : '' }}>
        <label class="form-check-label" for="allow_over_receipt">Izinkan Over Receipt</label>
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
    <div class="alert alert-light border">
      Ubah istilah tampilan status dari UI. Kode internal tetap sama, jadi flow sistem tidak berubah walaupun label display diganti.
    </div>
    <form method="POST" action="{{ route('settings.document-terms.update') }}">
      @csrf
      @forelse($documentTermGroups as $groupKey => $terms)
        <div class="mb-4">
          <div class="font-weight-bold text-uppercase mb-2">{{ str_replace('_', ' ', $groupKey) }}</div>
          <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0">
              <thead>
                <tr>
                  <th style="width: 180px;">Code</th>
                  <th>Label</th>
                  <th>Description</th>
                  <th style="width: 100px;">Sort</th>
                  <th style="width: 100px;">Aktif</th>
                </tr>
              </thead>
              <tbody>
                @foreach($terms as $term)
                  <tr>
                    <td>
                      <div class="fw-semibold">{{ $term->code }}</div>
                      <div class="small text-muted">ID: {{ $term->id }}</div>
                    </td>
                    <td>
                      <input type="text" name="document_terms[{{ $term->id }}][label]" value="{{ old("document_terms.{$term->id}.label", $term->label) }}" class="form-control form-control-sm" required>
                    </td>
                    <td>
                      <input type="text" name="document_terms[{{ $term->id }}][description]" value="{{ old("document_terms.{$term->id}.description", $term->description) }}" class="form-control form-control-sm">
                    </td>
                    <td>
                      <input type="number" min="0" max="9999" name="document_terms[{{ $term->id }}][sort_order]" value="{{ old("document_terms.{$term->id}.sort_order", $term->sort_order) }}" class="form-control form-control-sm" required>
                    </td>
                    <td class="text-center">
                      <input type="checkbox" name="document_terms[{{ $term->id }}][is_active]" value="1" @checked(old("document_terms.{$term->id}.is_active", $term->is_active))>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      @empty
        <div class="text-muted">Belum ada document terms.</div>
      @endforelse
      <button class="btn btn-primary">Simpan Document Terms</button>
    </form>
  </div>
</div>
@endsection
