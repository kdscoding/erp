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
                        <x-status-badge :status="$po->status" scope="po" />
                    </div>
                    <div class="col-md-12"><strong>Catatan:</strong> {{ $po->notes ?: '-' }}</div>
                    @if ($poIsFinal)
                        <div class="col-md-12">
                            <div class="alert alert-light border mb-0 mt-2">
                                Dokumen ini sudah final. Aksi operasional seperti cancel PO, cancel item, force close, dan
                                update ETD dinonaktifkan.
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card card-outline card-info mb-3">
                <div class="card-header">
                    <h3 class="card-title">Ringkasan Monitoring Item</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-light border mb-3">
                        <strong>Interpretasi cepat:</strong> {{ $itemSummary['progress_label'] }}
                    </div>
                    <div class="row g-2 text-center">
                        @foreach (['total', 'waiting', 'confirmed', 'late', 'partial', 'closed', 'cancelled'] as $key)
                            <div class="col-6 col-lg-2">
                                <div class="border rounded p-2 h-100">
                                    <div class="small text-muted">{{ ucfirst($key) }}</div>
                                    <div
                                        class="fs-5 fw-bold {{ match ($key) {
                                            'waiting' => 'text-secondary',
                                            'confirmed' => 'text-warning',
                                            'late' => 'text-danger',
                                            'partial' => 'text-primary',
                                            'closed' => 'text-success',
                                            'cancelled' => 'text-danger',
                                            default => '',
                                        } }}">
                                        {{ $itemSummary[$key] ?? 0 }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-outline card-danger mb-3">
                <div class="card-header">
                    <h3 class="card-title">Cancel PO</h3>
                </div>
                <div class="card-body">
                    <button class="btn btn-danger btn-sm w-100" data-toggle="modal" data-target="#cancelPoModal"
                        @disabled(!$poCanCancel)>
                        Batalkan PO
                    </button>
                    @if (!$poCanCancel)
                        <div class="small text-muted mt-2">PO dengan status final tidak bisa dibatalkan lagi.</div>
                    @endif
                    @if ($po->cancel_reason)
                        <div class="alert alert-danger mt-2 mb-0"><strong>Alasan:</strong> {{ $po->cancel_reason }}</div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Riwayat Status</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($histories as $history)
                            <li class="list-group-item">
                                <div class="fw-semibold">
                                    {{ $history->from_status ? \App\Support\TermCatalog::label('po_status', $history->from_status, $history->from_status) : 'N/A' }}
                                    ->
                                    {{ \App\Support\TermCatalog::label('po_status', $history->to_status, $history->to_status) }}
                                </div>
                                <small class="text-muted">
                                    {{ $history->changed_by_name ?: 'System' }} |
                                    {{ \Carbon\Carbon::parse($history->changed_at)->format('d-m-Y H:i') }}
                                </small>
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
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h3 class="card-title mb-0">Item PO & Monitoring ETD</h3>
                    <span class="text-muted small">Status item otomatis: Waiting / Confirmed / Late / Partial / Closed /
                        Cancelled. Tracking shipment dan GR tersedia per item.</span>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover mb-0 data-table" style="min-width: 1380px;">
                        <thead>
                            <tr>
                                <th style="min-width: 120px;">Kode</th>
                                <th style="min-width: 220px;">Nama Item</th>
                                <th style="min-width: 130px;">Ordered</th>
                                <th style="min-width: 130px;">Received</th>
                                <th style="min-width: 140px;">Outstanding</th>
                                <th style="min-width: 340px;">Tracking Shipment / GR</th>
                                <th style="min-width: 240px;">Status</th>
                                <th style="min-width: 380px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr class="{{ $item->monitoring_status === 'Late' ? 'table-danger' : '' }}">
                                    <td class="align-top">{{ $item->item_code }}</td>
                                    <td class="align-top">{{ $item->item_name }}</td>
                                    <td class="align-top">{{ \App\Support\NumberFormatter::trim($item->ordered_qty) }}
                                        {{ $item->unit_name }}</td>
                                    <td class="align-top">{{ \App\Support\NumberFormatter::trim($item->received_qty) }}
                                        {{ $item->unit_name }}</td>
                                    <td class="align-top">{{ \App\Support\NumberFormatter::trim($item->outstanding_qty) }}
                                        {{ $item->unit_name }}</td>
                                    <td class="align-top">
                                        @if ($item->tracking_rows->isEmpty())
                                            <div class="small text-muted">Belum ada shipment / GR untuk item ini.</div>
                                        @else
                                            <details>
                                                <summary class="small fw-semibold text-primary" style="cursor: pointer;">
                                                    Buka histori pengiriman & GR
                                                </summary>
                                                <div class="mt-2 d-flex flex-column gap-2">
                                                    @foreach ($item->tracking_rows as $tracking)
                                                        <div class="border rounded p-2 bg-light">
                                                            <div class="fw-semibold small">
                                                                Shipment:
                                                                {{ $tracking->shipment_number ?: 'Belum ada nomor shipment' }}
                                                            </div>
                                                            <div class="small text-muted">
                                                                Tgl Shipment:
                                                                {{ $tracking->shipment_date ? \Carbon\Carbon::parse($tracking->shipment_date)->format('d-m-Y') : '-' }}
                                                                | DN:
                                                                {{ $tracking->delivery_note_number ?: '-' }}
                                                            </div>
                                                            <div class="small text-muted">
                                                                Qty Kirim:
                                                                {{ \App\Support\NumberFormatter::trim($tracking->shipped_qty ?? 0) }}
                                                                {{ $item->unit_name }}
                                                                | Sudah di-GR:
                                                                {{ \App\Support\NumberFormatter::trim($tracking->shipment_received_qty ?? 0) }}
                                                                {{ $item->unit_name }}
                                                            </div>
                                                            <div class="mt-1">
                                                                <x-status-badge :status="$tracking->shipment_status ?: 'Draft'" scope="shipment" />
                                                            </div>

                                                            @if ($tracking->gr_rows->isEmpty())
                                                                <div class="small text-muted mt-2">Belum ada transaksi GR untuk shipment ini.</div>
                                                            @else
                                                                <div class="table-responsive mt-2">
                                                                    <table class="table table-sm mb-0">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Tgl GR</th>
                                                                                <th>No GR</th>
                                                                                <th>No Dokumen</th>
                                                                                <th>Qty GR</th>
                                                                                <th>Accepted</th>
                                                                                <th>Rejected</th>
                                                                                <th>Status</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach ($tracking->gr_rows as $gr)
                                                                                <tr>
                                                                                    <td>{{ $gr->receipt_date ? \Carbon\Carbon::parse($gr->receipt_date)->format('d-m-Y') : '-' }}</td>
                                                                                    <td>
                                                                                        @if ($gr->goods_receipt_id)
                                                                                            <a href="{{ route('receiving.show', $gr->goods_receipt_id) }}">
                                                                                                {{ $gr->gr_number ?: '-' }}
                                                                                            </a>
                                                                                        @else
                                                                                            -
                                                                                        @endif
                                                                                    </td>
                                                                                    <td>{{ $gr->gr_document_number ?: '-' }}</td>
                                                                                    <td>{{ \App\Support\NumberFormatter::trim($gr->gr_received_qty ?? 0) }} {{ $item->unit_name }}</td>
                                                                                    <td>{{ \App\Support\NumberFormatter::trim($gr->accepted_qty ?? 0) }} {{ $item->unit_name }}</td>
                                                                                    <td>{{ \App\Support\NumberFormatter::trim($gr->rejected_qty ?? 0) }} {{ $item->unit_name }}</td>
                                                                                    <td><x-status-badge :status="$gr->gr_status ?: 'Posted'" scope="gr" /></td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </details>
                                        @endif
                                    </td>
                                    <td class="align-top">
                                        <x-status-badge :status="$item->monitoring_status" scope="item" />

                                        @if ($item->cancel_reason)
                                            <div class="small text-danger mt-1">Alasan: {{ $item->cancel_reason }}</div>
                                        @elseif ($item->monitoring_status === 'Waiting')
                                            <div class="small text-muted mt-1">Belum ada konfirmasi ETD dari supplier.</div>
                                        @elseif ($item->monitoring_status === 'Confirmed')
                                            <div class="small text-muted mt-1">Sudah dikonfirmasi, menunggu pengiriman atau
                                                receiving.</div>
                                        @elseif ($item->monitoring_status === 'Partial')
                                            <div class="small text-muted mt-1">Sudah diterima sebagian, outstanding masih
                                                tersisa.</div>
                                        @elseif ($item->monitoring_status === 'Closed')
                                            <div class="small text-success mt-1">Item complete. Seluruh qty PO sudah
                                                diterima.</div>
                                        @elseif ($item->monitoring_status === 'Late')
                                            <div class="small text-danger mt-1">ETD lewat, item belum selesai diterima.
                                            </div>
                                        @endif
                                    </td>
                                    <td class="align-top">
                                        <form method="POST" action="{{ route('po.items.schedule', $item->id) }}"
                                            class="row g-1 mb-2">
                                            @csrf
                                            @method('PATCH')
                                            <div class="col-md-7">
                                                <input type="date" name="etd_date" value="{{ $item->etd_date }}"
                                                    class="form-control form-control-sm" @disabled(!$item->can_update_etd)>
                                            </div>
                                            <div class="col-md-5">
                                                <button class="btn btn-sm btn-primary w-100"
                                                    @disabled(!$item->can_update_etd)>Simpan ETD</button>
                                            </div>
                                        </form>

                                        <div class="d-flex gap-1 flex-wrap">
                                            <button class="btn btn-sm btn-outline-danger" data-toggle="modal"
                                                data-target="#cancelItemModal{{ $item->id }}"
                                                @disabled(!$item->can_cancel)>Cancel</button>
                                            <button class="btn btn-sm btn-danger" data-toggle="modal"
                                                data-target="#forceCloseModal{{ $item->id }}"
                                                @disabled(!$item->can_force_close)>Force Close</button>
                                        </div>

                                        @if (!$item->can_update_etd || !$item->can_cancel || !$item->can_force_close)
                                            <div class="small text-muted mt-2">
                                                @if (!$item->can_update_etd)
                                                    ETD terkunci karena item atau PO sudah final.
                                                @elseif (!$item->can_cancel)
                                                    Cancel item hanya tersedia untuk item aktif yang belum pernah diterima.
                                                @elseif (!$item->can_force_close)
                                                    Force close hanya tersedia saat item masih outstanding dan belum final.
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                </tr>

                                <div class="modal fade" id="cancelItemModal{{ $item->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <form method="POST" action="{{ route('po.items.cancel', $item->id) }}"
                                            class="modal-content">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Cancel Item {{ $item->item_code }}</h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            </div>
                                            <div class="modal-body">
                                                <label class="form-label">Alasan Pembatalan *</label>
                                                <textarea name="cancel_reason" class="form-control form-control-sm" required rows="3"></textarea>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light"
                                                    data-dismiss="modal">Tutup</button>
                                                <button class="btn btn-danger btn-sm">Konfirmasi Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="modal fade" id="forceCloseModal{{ $item->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <form method="POST" action="{{ route('po.items.force-close', $item->id) }}"
                                            class="modal-content">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Force Close Item {{ $item->item_code }}</h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="alert alert-warning">Status akan jadi
                                                    <strong>Cancelled</strong></div>
                                                <label class="form-label">Cancel Reason *</label>
                                                <textarea name="cancel_reason" class="form-control form-control-sm" required rows="3"></textarea>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light"
                                                    data-dismiss="modal">Tutup</button>
                                                <button class="btn btn-danger btn-sm">Force Close</button>
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

    <div class="modal fade" id="cancelPoModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('po.cancel', $po->id) }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Batalkan PO {{ $po->po_number }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Alasan Pembatalan *</label>
                    <textarea name="cancel_reason" class="form-control form-control-sm" required rows="3"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
                    <button class="btn btn-danger btn-sm">Konfirmasi Cancel PO</button>
                </div>
            </form>
        </div>
    </div>
@endsection
