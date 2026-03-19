@extends('layouts.erp')
@php($title='Detail PO')
@php($header='Detail Purchase Order')

@php
    $statusBadge = static function ($status) {
        return match ($status) {
            'Closed' => 'success',
            'Partial', 'Confirmed', 'PO Issued', 'Waiting' => 'warning text-dark',
            'Late', 'Cancelled' => 'danger',
            default => 'secondary',
        };
    };
@endphp

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

<div class="row">
    <div class="col-md-8">
        <div class="card card-outline card-primary mb-3">
            <div class="card-header"><h3 class="card-title">Header PO</h3></div>
            <div class="card-body row g-2">
                <div class="col-md-6"><strong>Nomor PO:</strong> {{ $po->po_number }}</div>
                <div class="col-md-6"><strong>Tanggal PO:</strong> {{ \Carbon\Carbon::parse($po->po_date)->format('d-m-Y') }}</div>
                <div class="col-md-6"><strong>Supplier:</strong> {{ $po->supplier_name }}</div>
                <div class="col-md-6"><strong>Status:</strong> <span class="badge bg-{{ $statusBadge($po->status) }}">{{ $po->status }}</span></div>
                <div class="col-md-12"><strong>Catatan:</strong> {{ $po->notes ?: '-' }}</div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Item PO & Monitoring ETD</h3>
                <span class="text-muted small">Status item otomatis: Waiting / Confirmed / Late / Partial / Closed / Cancelled</span>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover mb-0 data-table">
                    <thead><tr><th>Kode</th><th>Nama Item</th><th>Ordered</th><th>Received</th><th>Outstanding</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                    @foreach($items as $item)
                        <tr>
                            <td>{{ $item->item_code }}</td>
                            <td>{{ $item->item_name }}</td>
                            <td>{{ number_format($item->ordered_qty, 2, ',', '.') }} {{ $item->unit_name }}</td>
                            <td>{{ number_format($item->received_qty, 2, ',', '.') }} {{ $item->unit_name }}</td>
                            <td>{{ number_format($item->outstanding_qty, 2, ',', '.') }} {{ $item->unit_name }}</td>
                            <td>
                                <span class="badge bg-{{ $statusBadge($item->monitoring_status) }}">{{ $item->monitoring_status }}</span>
                                @if($item->cancel_reason)<div class="small text-danger mt-1">Alasan: {{ $item->cancel_reason }}</div>@endif
                            </td>
                            <td style="min-width: 380px;">
                                <form method="POST" action="{{ route('po.items.schedule', $item->id) }}" class="row g-1 mb-1">
                                    @csrf @method('PATCH')
                                    <div class="col-md-7"><input type="date" name="etd_date" value="{{ $item->etd_date }}" class="form-control form-control-sm" @disabled($item->item_status === 'Cancelled')></div>
                                    <div class="col-md-5"><button class="btn btn-sm btn-primary w-100" @disabled($item->item_status === 'Cancelled')>Simpan ETD</button></div>
                                </form>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelItemModal{{ $item->id }}">Cancel</button>
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#forceCloseModal{{ $item->id }}" @disabled(!in_array($item->monitoring_status, ['Confirmed','Partial']))>Force Close</button>
                                </div>
                            </td>
                        </tr>

                        <div class="modal fade" id="cancelItemModal{{ $item->id }}" tabindex="-1"><div class="modal-dialog"><form method="POST" action="{{ route('po.items.cancel', $item->id) }}" class="modal-content">@csrf
                            <div class="modal-header"><h5 class="modal-title">Cancel Item {{ $item->item_code }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body"><label class="form-label">Alasan Pembatalan *</label><textarea name="cancel_reason" class="form-control" required rows="3"></textarea></div>
                            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button><button class="btn btn-danger">Konfirmasi Cancel</button></div>
                        </form></div></div>

                        <div class="modal fade" id="forceCloseModal{{ $item->id }}" tabindex="-1"><div class="modal-dialog"><form method="POST" action="{{ route('po.items.force-close', $item->id) }}" class="modal-content">@csrf
                            <div class="modal-header"><h5 class="modal-title">Force Close Item {{ $item->item_code }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body"><div class="alert alert-warning">Status item akan dipindah ke <strong>Cancelled</strong>.</div><label class="form-label">Cancel Reason *</label><textarea name="cancel_reason" class="form-control" required rows="3"></textarea></div>
                            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button><button class="btn btn-danger">Force Close</button></div>
                        </form></div></div>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-outline card-danger mb-3">
            <div class="card-header"><h3 class="card-title">Cancel PO</h3></div>
            <div class="card-body">
                <button class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#cancelPoModal">Batalkan PO</button>
                @if($po->cancel_reason)
                    <div class="alert alert-danger mt-2 mb-0"><strong>Alasan:</strong> {{ $po->cancel_reason }}</div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">Riwayat Status</h3></div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($histories as $history)
                        <li class="list-group-item">
                            <div class="fw-semibold">{{ $history->from_status ?: 'N/A' }} → {{ $history->to_status }}</div>
                            <small class="text-muted">{{ $history->changed_by_name ?: 'System' }} | {{ \Carbon\Carbon::parse($history->changed_at)->format('d-m-Y H:i') }}</small>
                            @if($history->note)<div>{{ $history->note }}</div>@endif
                        </li>
                    @empty
                        <li class="list-group-item text-muted">Belum ada histori status.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cancelPoModal" tabindex="-1"><div class="modal-dialog"><form method="POST" action="{{ route('po.cancel', $po->id) }}" class="modal-content">@csrf
    <div class="modal-header"><h5 class="modal-title">Batalkan PO {{ $po->po_number }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><label class="form-label">Alasan Pembatalan *</label><textarea name="cancel_reason" class="form-control" required rows="3"></textarea></div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button><button class="btn btn-danger">Konfirmasi Cancel PO</button></div>
</form></div></div>
@endsection
