@extends('layouts.erp')
@php
    $title = 'Detail PO';
    $header = 'Detail Purchase Order';
    $unit = $itemUnit ?? ($items->isNotEmpty() ? ($items->first()->unit_name ?? '') : '');
@endphp

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="quick-actions-toolbar d-flex gap-2 flex-wrap mb-3">
        <a href="{{ route('po.index') }}" class="btn btn-sm btn-light">
            <i class="fas fa-arrow-left"></i> Kembali ke List
        </a>
        <a href="{{ route('po.export-detail-excel', $po->po_number) }}" class="btn btn-sm btn-outline-success">
            <i class="fas fa-file-excel"></i> Export Excel
        </a>
        @if ($poCanCancel)
            <button class="btn btn-sm btn-outline-danger" data-toggle="modal" data-target="#cancelPoModal">
                <i class="fas fa-times"></i> Batalkan PO
            </button>
        @endif
        <button class="btn btn-sm btn-outline-primary" id="refreshStatusBtn">
            <i class="fas fa-sync-alt"></i> Refresh Status
        </button>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card card-outline card-primary mb-3">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h3 class="card-title mb-0">Header PO</h3>
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <a href="{{ route('po.export-detail-excel', $po->po_number) }}" class="btn btn-sm btn-outline-success">
                                Export Excel
                            </a>
                        </div>
                    </div>
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
                    <div class="col-md-6">
                        <strong>ETA PO:</strong>
                        {{ $po->eta_date ? \Carbon\Carbon::parse($po->eta_date)->format('d-m-Y') : '-' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Plant:</strong> {{ $po->plant_name ?: '-' }}
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

            <x-item-summary :counts="$itemSummary" :received="$totalReceived" :ordered="$totalOrdered" :unit="$unit" />

            <div class="card card-outline card-info mb-3">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h3 class="card-title mb-0">Item PO & Monitoring ETD</h3>
                    <span class="text-muted small">Status item otomatis: Waiting / Confirmed / Late / Partial / Closed /
                        Cancelled. Tracking shipment dan GR tersedia per item.</span>
                </div>

                @if (!$poIsFinal)
                    <div class="card-body border-bottom">
                        <form id="bulkEtdForm" method="POST" action="{{ route('po.items.bulk-schedule', $po->id) }}" class="row g-2 align-items-end">
                            @csrf
                            @method('PATCH')
                            <div class="col-md-4">
                                <label class="form-label">ETD Dasar Bulk Update</label>
                                <input type="date" name="etd_date" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Offset Hari</label>
                                <input type="number" name="day_offset" class="form-control form-control-sm" value="0" min="-30" max="30">
                            </div>
                            <div class="col-md-4">
                                <div class="small text-muted mb-2">Pilih item aktif dari checklist di tabel lalu apply satu ETD untuk semua item terpilih.</div>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-sm btn-primary w-100">Bulk Update ETD</button>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="card-body border-bottom">
                        <div class="alert alert-light border mb-0">
                            PO sudah final. Bulk update ETD dinonaktifkan.
                        </div>
                    </div>
                @endif

                <div class="card-body table-responsive p-0">
                    <table class="table table-hover mb-0" style="min-width: 1380px;">
                        <thead>
                            <tr>
                                <th style="min-width: 70px;">
                                    <input type="checkbox" id="bulkSelectAll" @disabled($poIsFinal)>
                                </th>
                                @php
                                    $sortableHeaders = [
                                        'item_code' => 'Kode',
                                        'item_name' => 'Nama Item',
                                        'ordered_qty' => 'Ordered',
                                        'received_qty' => 'Received',
                                        'outstanding_qty' => 'Outstanding',
                                        'etd_date' => 'ETD',
                                        'monitoring_status' => 'Status',
                                    ];
                                    $currentSort = $sort ?? 'item_code';
                                    $currentDirection = $direction ?? 'asc';
                                @endphp
                                @foreach ($sortableHeaders as $field => $label)
                                    @php($nextDirection = $currentSort === $field && $currentDirection === 'asc' ? 'desc' : 'asc')
                                    <th style="min-width: 120px;">
                                        <a href="?sort={{ $field }}&direction={{ $nextDirection }}"
                                            class="text-decoration-none d-flex align-items-center gap-1">
                                            {{ $label }}
                                            @if ($currentSort === $field)
                                                <i class="fas fa-sort-{{ $currentDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                @endforeach
                                <th style="min-width: 300px;">Tracking Shipment / GR</th>
                                <th style="min-width: 160px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                                @forelse ($items as $item)
                                    @php($rowClass = match(true) {
                                        in_array($item->monitoring_status, [\App\Support\DocumentTermCodes::ITEM_LATE]) => 'table-danger',
                                        in_array($item->monitoring_status, [\App\Support\DocumentTermCodes::ITEM_WAITING]) => 'table-warning',
                                        default => '',
                                    })
                                    <tr class="{{ $rowClass }}">
                                        <td class="align-top">
                                            <input type="checkbox"
                                                name="item_ids[]"
                                                value="{{ $item->id }}"
                                                class="bulk-item-checkbox"
                                                form="bulkEtdForm"
                                                @disabled(!$item->can_update_etd || $poIsFinal)>
                                        </td>
                                        <td class="align-top">{{ $item->item_code }}</td>
                                        <td class="align-top">
                                            {{ $item->item_name }}
                                            @if ($item->cancel_reason)
                                                <div class="small text-danger mt-1">Alasan: {{ $item->cancel_reason }}</div>
                                            @endif
                                        </td>
                                        <td class="align-top">{{ \App\Support\NumberFormatter::trim($item->ordered_qty) }} {{ $item->unit_name }}</td>
                                        <td class="align-top">{{ \App\Support\NumberFormatter::trim($item->received_qty) }} {{ $item->unit_name }}</td>
                                        <td class="align-top">{{ \App\Support\NumberFormatter::trim($item->outstanding_qty) }} {{ $item->unit_name }}</td>
                                        <td class="align-top">
                                            @if ($item->etd_date)
                                                {{ \Carbon\Carbon::parse($item->etd_date)->format('d-m-Y') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="align-top">
                                            @if ($item->tracking_rows->isEmpty())
                                                <div class="small text-muted">Belum ada shipment / GR untuk item ini.</div>
                                            @else
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-view-tracking"
                                                    data-action="view-tracking"
                                                    data-item-id="{{ $item->id }}"
                                                    data-item-code="{{ $item->item_code }}"
                                                    data-copy-url="{{ route('po.item.tracking.copy-text', [$po->id, $item->id]) }}"
                                                    data-excel-url="{{ route('po.item.tracking.export-excel', [$po->id, $item->id]) }}">
                                                    Lihat Tracking
                                                </button>
                                                <div class="small text-muted mt-1">
                                                    {{ $item->tracking_rows->count() }} shipment trace
                                                </div>
                                            @endif
                                        </td>
                                        <td class="align-top">
                                            <x-status-badge :status="$item->monitoring_status" scope="item" />
                                            <x-status-help :status="$item->monitoring_status" scope="item" />
                                        </td>
                                    @php($etdUrl = $item->can_update_etd && !$poIsFinal ? route('po.items.schedule', [$item->id]) : '')
                                    @php($cancelUrl = $item->can_cancel && !$poIsFinal ? route('po.items.cancel', [$item->id]) : '')
                                    @php($forceCloseUrl = $item->can_force_close && !$poIsFinal ? route('po.items.force-close', [$item->id]) : '')
                                    <td class="align-top">
                                        @if (in_array($item->monitoring_status, [\App\Support\DocumentTermCodes::ITEM_CLOSED]))
                                            <span class="badge bg-light text-muted">Final — Diterima Penuh</span>
                                        @elseif ($item->can_update_etd || $item->can_cancel || $item->can_force_close)
                                            <div class="d-flex gap-1 flex-wrap">
                                                @if ($item->can_update_etd)
                                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                                        data-action="edit-etd"
                                                        data-item-id="{{ $item->id }}"
                                                        data-item-code="{{ $item->item_code }}"
                                                        data-etd-url="{{ $etdUrl }}"
                                                        data-etd-date="{{ $item->etd_date }}"
                                                        data-cancel-reason="{{ $item->cancel_reason }}">
                                                        Edit ETD
                                                    </button>
                                                @endif
                                                @if ($item->can_cancel)
                                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                                        data-action="cancel-item"
                                                        data-item-id="{{ $item->id }}"
                                                        data-item-code="{{ $item->item_code }}"
                                                        data-cancel-url="{{ $cancelUrl }}">
                                                        Cancel
                                                    </button>
                                                @endif
                                                @if ($item->can_force_close)
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        data-action="force-close"
                                                        data-item-id="{{ $item->id }}"
                                                        data-item-code="{{ $item->item_code }}"
                                                        data-force-close-url="{{ $forceCloseUrl }}">
                                                        Force Close
                                                    </button>
                                                @endif
                                            </div>
                                        @else
                                            <span class="badge bg-light text-muted">Tidak dapat diubah</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Belum ada item.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Hidden tracking content per item (populated into the shared modal by JS) --}}
                @foreach ($items as $item)
                    @if (!$item->tracking_rows->isEmpty())
                        <div class="tracking-content d-none" data-item-id="{{ $item->id }}">
                            <div class="small text-muted mb-3">
                                {{ $item->item_name }} | Qty Order PO {{ \App\Support\NumberFormatter::trim($item->ordered_qty) }} {{ $item->unit_name }} |
                                Qty Sudah Masuk PO {{ \App\Support\NumberFormatter::trim($item->received_qty) }} {{ $item->unit_name }} |
                                Qty Outstanding PO {{ \App\Support\NumberFormatter::trim($item->outstanding_qty) }} {{ $item->unit_name }}
                            </div>

                            @php($initialTimelineStatus = match (true) {
                                $item->monitoring_status === \App\Support\DocumentTermCodes::ITEM_CANCELLED => \App\Support\DocumentTermCodes::ITEM_CANCELLED,
                                $item->monitoring_status === \App\Support\DocumentTermCodes::ITEM_FORCE_CLOSED => \App\Support\DocumentTermCodes::ITEM_FORCE_CLOSED,
                                $item->etd_date && \Carbon\Carbon::parse($item->etd_date)->isPast() && (float) $item->received_qty <= 0 => \App\Support\DocumentTermCodes::ITEM_LATE,
                                $item->etd_date => \App\Support\DocumentTermCodes::ITEM_CONFIRMED,
                                default => \App\Support\DocumentTermCodes::ITEM_WAITING,
                            })
                            @php($runningReceivedQty = 0)

                            <div class="timeline-row d-flex align-items-start gap-2 mb-3">
                                <div class="timeline-dot rounded-circle bg-{{ $initialTimelineStatus === \App\Support\DocumentTermCodes::ITEM_CONFIRMED ? 'info' : 'secondary' }}"></div>
                                <div class="timeline-content">
                                    <div class="fw-bold small">{{ \Carbon\Carbon::parse($po->po_date)->format('d/m/Y') }} | PO Created</div>
                                    <div class="small">Qty Order: {{ \App\Support\NumberFormatter::trim($item->ordered_qty) }} {{ $item->unit_name }} |
                                        Qty Masuk: 0 {{ $item->unit_name }} |
                                        Qty Outstanding: {{ \App\Support\NumberFormatter::trim($item->ordered_qty) }} {{ $item->unit_name }}
                                    </div>
                                    <div class="text-{{ $initialTimelineStatus === \App\Support\DocumentTermCodes::ITEM_CONFIRMED ? 'warning' : 'muted' }} small">{{ $initialTimelineStatus }}</div>
                                </div>
                            </div>

                            @foreach ($item->tracking_rows as $tracking)
                                @php($shipmentDate = $tracking->shipment_date ? \Carbon\Carbon::parse($tracking->shipment_date)->format('d/m/Y') : '-')
                                @php($shipmentNumber = $tracking->shipment_number ?: 'Belum ada nomor shipment')
                                @php($deliveryNoteNumber = $tracking->delivery_note_number ?: '-')
                                @php($shipmentLabel = 'Pengiriman ke-'.$loop->iteration.' | DN '.$deliveryNoteNumber)

                                @if ($tracking->gr_rows->isEmpty())
                                    @php($shipmentTimelineStatus = $runningReceivedQty > 0
                                        ? \App\Support\DocumentTermCodes::ITEM_PARTIAL
                                        : ($initialTimelineStatus === \App\Support\DocumentTermCodes::ITEM_WAITING
                                            ? \App\Support\DocumentTermCodes::ITEM_CONFIRMED
                                            : $initialTimelineStatus))

                                    <div class="timeline-row d-flex align-items-start gap-2 mb-3">
                                        <div class="timeline-dot rounded-circle bg-{{ $shipmentTimelineStatus === \App\Support\DocumentTermCodes::ITEM_PARTIAL ? 'primary' : 'info' }}"></div>
                                        <div class="timeline-content">
                                            <div class="fw-bold small">{{ $shipmentDate }} | {{ $shipmentLabel }} (Belum GR)</div>
                                            <div class="small">
                                                {{ $shipmentNumber }}
                                            </div>
                                            <div class="text-{{ $shipmentTimelineStatus === \App\Support\DocumentTermCodes::ITEM_PARTIAL ? 'primary' : 'muted' }} small">{{ $shipmentTimelineStatus }}</div>
                                        </div>
                                    </div>
                                @else
                                    @foreach ($tracking->gr_rows as $gr)
                                        @php($runningReceivedQty += (float) ($gr->gr_received_qty ?? 0))
                                        @php($grDate = $gr->receipt_date ? \Carbon\Carbon::parse($gr->receipt_date)->format('d/m/Y') : '-')
                                        @php($remainingQty = max(0, (float) $item->ordered_qty - $runningReceivedQty))

                                        <div class="timeline-row d-flex align-items-start gap-2 mb-3">
                                            <div class="timeline-dot rounded-circle {{ $remainingQty <= 0 ? 'bg-success' : ($runningReceivedQty > 0 ? 'bg-primary' : 'bg-secondary') }}"></div>
                                            <div class="timeline-content">
                                                <div class="fw-bold small">{{ $grDate }} | {{ $shipmentLabel }}</div>
                                                <div class="small">
                                                    No Shipment: {{ $shipmentNumber }} | No GR: {{ $gr->gr_number ?: '-' }}
                                                </div>
                                                <div class="small">
                                                    Qty Order: - | Qty Masuk: {{ \App\Support\NumberFormatter::trim($gr->gr_received_qty ?? 0) }} {{ $item->unit_name }} |
                                                    Qty Outstanding: {{ \App\Support\NumberFormatter::trim($remainingQty) }} {{ $item->unit_name }}
                                                </div>
                                                <div class="text-{{ $remainingQty <= 0 ? 'success' : ($runningReceivedQty > 0 ? 'primary' : 'muted') }} small">
                                                    {{ $remainingQty <= 0 ? \App\Support\DocumentTermCodes::ITEM_CLOSED : ($runningReceivedQty > 0 ? \App\Support\DocumentTermCodes::ITEM_PARTIAL : $initialTimelineStatus) }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            @endforeach
                        </div>
                    @endif
                @endforeach
                
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-outline card-danger mb-3">
                <div class="card-header">
                    <h3 class="card-title">Batalkan PO</h3>
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

    {{-- Single Modal: Cancel/Force Close Item --}}
    <div class="modal fade" id="itemActionModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Item Action</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="item_id" value="">
                    <label class="form-label">Alasan *</label>
                    <textarea name="cancel_reason" class="form-control form-control-sm" required rows="3"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
                    <button class="btn btn-danger btn-sm">Konfirmasi</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Single Modal: Edit ETD --}}
    <div class="modal fade" id="etdModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                @csrf
                @method('PATCH')
                <input type="hidden" name="item_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title">Edit ETD Item</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">ETD Date</label>
                    <input type="date" name="etd_date" class="form-control form-control-sm">
                    <label class="form-label mt-2">Remarks (opsional)</label>
                    <textarea name="remarks" class="form-control form-control-sm" rows="2"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
                    <button class="btn btn-primary btn-sm">Simpan ETD</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Single Modal: Tracking --}}
    <div class="modal fade" id="trackingModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1">Tracking Shipment / GR</h5>
                        <div class="small text-muted">Detail per shipment dan histori GR tersedia dalam satu popup ringkas.</div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-sm btn-outline-secondary js-copy-tracking">
                            Copy
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success js-export-tracking">
                            Export Excel
                        </button>
                        <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
                <div class="modal-body">
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Cancel PO --}}
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

@push('scripts')
    @vite('resources/js/po-show.js')
    <script>
        window.PO_SHOW_CONFIG = {
            refreshUrl: "{{ route('po.refresh-status', $po->id) }}",
            csrfToken: document.querySelector('meta[name="csrf-token"]').content,
        };
    </script>
@endpush

@push('styles')
    <style>
        .timeline-dot {
            min-width: 12px;
        }
        .timeline-container {
            position: relative;
            padding-left: 14px;
        }
        .timeline-node {
            position: relative;
        }
        .timeline-node:before {
            content: '';
            position: absolute;
            left: 5px;
            top: 24px;
            bottom: -16px;
            width: 2px;
            background: #d0d7bb;
        }
        .timeline-node:last-child:before {
            display: none;
        }
    </style>
@endpush
