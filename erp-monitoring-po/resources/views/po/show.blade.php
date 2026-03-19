@extends('layouts.erp')
@php($title = 'Detail PO')
@php($header = 'Detail Purchase Order')

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-md-8">
            {{-- Header PO --}}
            <div class="card card-outline card-primary mb-3">
                <div class="card-header">
                    <h3 class="card-title">Header PO</h3>
                </div>
                <div class="card-body row g-2">
                    <div class="col-md-6"><strong>Nomor PO:</strong> {{ $po->po_number }}</div>
                    <div class="col-md-6"><strong>Tanggal PO:</strong>
                        {{ \Carbon\Carbon::parse($po->po_date)->format('d-m-Y') }}</div>
                    <div class="col-md-6"><strong>Supplier:</strong> {{ $po->supplier_name }}</div>
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        <span
                            class="badge {{ match ($po->status) {
                                'Closed' => 'bg-success',
                                'Partial', 'Confirmed', 'PO Issued', 'Waiting' => 'bg-warning text-dark',
                                'Late', 'Cancelled' => 'bg-danger',
                                default => 'bg-secondary',
                            } }}">
                            {{ $po->status ?? '-' }}
                        </span>
                    </div>
                    <div class="col-md-12"><strong>Catatan:</strong> {{ $po->notes ?: '-' }}</div>
                </div>
            </div>

            {{-- Ringkasan Item --}}
            <div class="card card-outline card-info mb-3">
                <div class="card-header">
                    <h3 class="card-title">Ringkasan Monitoring Item</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-light border mb-3">
                        <strong>Interpretasi cepat:</strong> {{ $itemSummary['progress_label'] }}
                    </div>
                    <div class="row g-2 text-center">
                        @foreach (['total', 'waiting', 'confirmed', 'late', 'partial', 'closed'] as $key)
                            <div class="col-6 col-lg-2">
                                <div class="border rounded p-2">
                                    <div class="small text-muted">{{ ucfirst($key) }}</div>
                                    <div
                                        class="fs-5 fw-bold {{ match ($key) {
                                            'waiting' => 'text-secondary',
                                            'confirmed' => 'text-warning',
                                            'late' => 'text-danger',
                                            'partial' => 'text-primary',
                                            'closed' => 'text-success',
                                            default => '',
                                        } }}">
                                        {{ $itemSummary[$key] }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-md-4">
            {{-- Cancel PO --}}
            <div class="card card-outline card-danger mb-3">
                <div class="card-header">
                    <h3 class="card-title">Cancel PO</h3>
                </div>
                <div class="card-body">
                    <button class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#cancelPoModal">Batalkan
                        PO</button>
                    @if ($po->cancel_reason)
                        <div class="alert alert-danger mt-2 mb-0"><strong>Alasan:</strong> {{ $po->cancel_reason }}</div>
                    @endif
                </div>
            </div>

            {{-- History Status --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Riwayat Status</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($histories as $history)
                            <li class="list-group-item">
                                <div class="fw-semibold">{{ $history->from_status ?: 'N/A' }} ->
                                    {{ $history->to_status }}</div>
                                <small class="text-muted">{{ $history->changed_by_name ?: 'System' }} |
                                    {{ \Carbon\Carbon::parse($history->changed_at)->format('d-m-Y H:i') }}</small>
                                @if ($history->note)
                                    <div>{{ $history->note }}</div>
                                @endif
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Belum ada histori status.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            {{-- Tabel Item --}}
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h3 class="card-title mb-0">Item PO & Monitoring ETD</h3>
                    <span class="text-muted small">Status item otomatis: Waiting / Confirmed / Late / Partial / Closed / Cancelled</span>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover mb-0 data-table" style="min-width: 1200px;">
                        <thead>
                            <tr>
                                <th style="min-width: 120px;">Kode</th>
                                <th style="min-width: 220px;">Nama Item</th>
                                <th style="min-width: 130px;">Ordered</th>
                                <th style="min-width: 130px;">Received</th>
                                <th style="min-width: 140px;">Outstanding</th>
                                <th style="min-width: 240px;">Status</th>
                                <th style="min-width: 380px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr class="{{ $item->monitoring_status === 'Late' ? 'table-danger' : '' }}">
                                    <td class="align-top">{{ $item->item_code }}</td>
                                    <td class="align-top">{{ $item->item_name }}</td>
                                    <td class="align-top">{{ number_format($item->ordered_qty, 2, ',', '.') }} {{ $item->unit_name }}</td>
                                    <td class="align-top">{{ number_format($item->received_qty, 2, ',', '.') }} {{ $item->unit_name }}</td>
                                    <td class="align-top">{{ number_format($item->outstanding_qty, 2, ',', '.') }} {{ $item->unit_name }}</td>
                                    <td class="align-top">
                                        <span
                                            class="badge {{ match ($item->monitoring_status) {
                                                'Closed' => 'bg-success',
                                                'Partial', 'Confirmed', 'PO Issued', 'Waiting' => 'bg-warning text-dark',
                                                'Late', 'Cancelled' => 'bg-danger',
                                                default => 'bg-secondary',
                                            } }}">{{ $item->monitoring_status }}</span>
                                        @if ($item->cancel_reason)
                                            <div class="small text-danger mt-1">Alasan: {{ $item->cancel_reason }}</div>
                                        @elseif ($item->monitoring_status === 'Waiting')
                                            <div class="small text-muted mt-1">Belum ada konfirmasi ETD dari supplier.</div>
                                        @elseif ($item->monitoring_status === 'Confirmed')
                                            <div class="small text-muted mt-1">Sudah dikonfirmasi, menunggu pengiriman atau receiving.</div>
                                        @elseif ($item->monitoring_status === 'Partial')
                                            <div class="small text-muted mt-1">Sudah diterima sebagian, outstanding masih tersisa.</div>
                                        @elseif ($item->monitoring_status === 'Late')
                                            <div class="small text-danger mt-1">ETD lewat, item belum selesai diterima.</div>
                                        @endif
                                    </td>
                                    <td class="align-top">
                                        <form method="POST" action="{{ route('po.items.schedule', $item->id) }}" class="row g-1 mb-2">
                                            @csrf
                                            @method('PATCH')
                                            <div class="col-md-7">
                                                <input type="date" name="etd_date" value="{{ $item->etd_date }}"
                                                    class="form-control form-control-sm" @disabled($item->monitoring_status === 'Cancelled')>
                                            </div>
                                            <div class="col-md-5">
                                                <button class="btn btn-sm btn-primary w-100"
                                                    @disabled($item->monitoring_status === 'Cancelled')>Simpan ETD</button>
                                            </div>
                                        </form>

                                        <div class="d-flex gap-1 flex-wrap">
                                            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                                data-bs-target="#cancelItemModal{{ $item->id }}"
                                                @disabled($item->monitoring_status === 'Cancelled')>Cancel</button>
                                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                data-bs-target="#forceCloseModal{{ $item->id }}"
                                                @disabled(!in_array($item->monitoring_status, ['Confirmed', 'Partial']))>Force Close</button>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Modal Cancel --}}
                                <div class="modal fade" id="cancelItemModal{{ $item->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <form method="POST" action="{{ route('po.items.cancel', $item->id) }}"
                                            class="modal-content">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Cancel Item {{ $item->item_code }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <label class="form-label">Alasan Pembatalan *</label>
                                                <textarea name="cancel_reason" class="form-control" required rows="3"></textarea>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light"
                                                    data-bs-dismiss="modal">Tutup</button>
                                                <button class="btn btn-danger">Konfirmasi Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                {{-- Modal Force Close --}}
                                <div class="modal fade" id="forceCloseModal{{ $item->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <form method="POST" action="{{ route('po.items.force-close', $item->id) }}"
                                            class="modal-content">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Force Close Item {{ $item->item_code }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="alert alert-warning">Status akan jadi <strong>Cancelled</strong></div>
                                                <label class="form-label">Cancel Reason *</label>
                                                <textarea name="cancel_reason" class="form-control" required rows="3"></textarea>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light"
                                                    data-bs-dismiss="modal">Tutup</button>
                                                <button class="btn btn-danger">Force Close</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Cancel PO --}}
    <div class="modal fade" id="cancelPoModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('po.cancel', $po->id) }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Batalkan PO {{ $po->po_number }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Alasan Pembatalan *</label>
                    <textarea name="cancel_reason" class="form-control" required rows="3"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                    <button class="btn btn-danger">Konfirmasi Cancel PO</button>
                </div>
            </form>
        </div>
    </div>
@endsection
