@extends('layouts.erp')
@php($title='Detail PO')
@php($header='Detail Purchase Order')
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

<div class="row">
    <div class="col-md-8">
        <div class="card card-outline card-primary mb-3">
            <div class="card-header"><h3 class="card-title">Header PO</h3></div>
            <div class="card-body row">
                <div class="col-md-6"><strong>Nomor PO:</strong> {{ $po->po_number }}</div>
                <div class="col-md-6"><strong>Tanggal PO:</strong> {{ \Carbon\Carbon::parse($po->po_date)->format('d-m-Y') }}</div>
                <div class="col-md-6"><strong>Supplier:</strong> {{ $po->supplier_name }}</div>
                <div class="col-md-6"><strong>Status:</strong> <span class="badge bg-secondary">{{ $po->status }}</span></div>
                <div class="col-md-12 mt-2"><strong>Catatan:</strong> {{ $po->notes ?: '-' }}</div>
            </div>
        </div>

<<<<<<< ours
=======


        <div class="card mb-3">
            <div class="card-header"><h3 class="card-title">Progress Konfirmasi Supplier</h3></div>
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <div class="fw-semibold">{{ $confirmedItems }}/{{ $totalItems }} Items Confirmed</div>
                    <small class="text-muted">PO akan berstatus <strong>Partial Confirmed</strong> jika belum semua item memiliki ETD.</small>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge" style="background:#fde68a;color:#7c4a03;">Partial Confirmed</span>
                    @if($splitShipment)<span class="badge" style="background:#fecdd3;color:#7f1d1d;">Split Shipment</span>@endif
                </div>
            </div>
        </div>

>>>>>>> theirs
        <div class="card mb-3">
            <div class="card-header"><h3 class="card-title">Item PO & Monitoring ETD/ETA</h3></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Kode</th><th>Nama Item</th><th>Ordered</th><th>Received</th><th>Outstanding</th><th>Jadwal Item</th></tr></thead>
                    <tbody>
                    @foreach($items as $item)
                        <tr>
                            <td>{{ $item->item_code }}</td>
                            <td>{{ $item->item_name }}</td>
                            <td>{{ number_format($item->ordered_qty, 2, ',', '.') }} {{ $item->unit_name }}</td>
                            <td>{{ number_format($item->received_qty, 2, ',', '.') }} {{ $item->unit_name }}</td>
                            <td>{{ number_format($item->outstanding_qty, 2, ',', '.') }} {{ $item->unit_name }}</td>
                            <td style="min-width:360px;">
<<<<<<< ours
                                <form method="POST" action="{{ route('po.items.schedule', $item->id) }}" class="row g-1 align-items-center">
                                    @csrf @method('PATCH')
                                    <div class="col-md-4"><input type="date" name="etd_date" value="{{ $item->etd_date }}" class="form-control" title="ETD"></div>
                                    <div class="col-md-4"><input type="date" name="eta_date" value="{{ $item->eta_date }}" class="form-control" title="ETA"></div>
                                    <div class="col-md-4">
                                        <select name="item_status" class="form-select">
                                            @foreach(['Waiting','Confirmed','Shipped','Partial Received','Received','On Hold'] as $status)
                                                <option value="{{ $status }}" @selected(($item->item_status ?? 'Waiting') === $status)>{{ $status }}</option>
                                            @endforeach
                                        </select>
=======
                                <form method="POST" action="{{ route('po.items.schedule', $item->id) }}" class="row g-1 align-items-center" title="Form update jadwal dan status item untuk supplier confirmation">
                                    @csrf @method('PATCH')
                                    <div class="col-md-4"><input type="date" name="etd_date" value="{{ $item->etd_date }}" class="form-control" title="ETD - Estimasi tanggal kirim dari supplier" aria-label="ETD item"><small class="text-muted">Masukkan ETD item ini.</small></div>
                                    <div class="col-md-4"><input type="date" name="eta_date" value="{{ $item->eta_date }}" class="form-control" title="ETA - Estimasi tanggal tiba di gudang" aria-label="ETA item"><small class="text-muted">ETA untuk prediksi kedatangan.</small></div>
                                    <div class="col-md-4">
                                        <select name="status_item" class="form-select" title="Status item saat ini di alur pengadaan" aria-label="Status item">
                                            @foreach(['Waiting','Confirmed','Shipped','Partial Received','Received','On Hold'] as $status)
                                                <option value="{{ $status }}" @selected((($item->status_item ?? $item->item_status) ?? 'Waiting') === $status)>{{ $status }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Status ini dipakai untuk monitoring item-level.</small>
>>>>>>> theirs
                                    </div>
                                    <div class="col-md-8">
                                        @php($percent = $item->ordered_qty > 0 ? min(100, round(($item->received_qty / $item->ordered_qty) * 100)) : 0)
                                        <div class="progress" style="height:8px;"><div class="progress-bar bg-success" style="width: {{ $percent }}%"></div></div>
                                        <small class="text-muted">Progress Item: {{ $percent }}%</small>
                                    </div>
<<<<<<< ours
                                    <div class="col-md-4"><button class="btn btn-sm btn-primary w-100">Simpan Jadwal</button></div>
=======
                                    <div class="col-md-4"><button class="btn btn-sm btn-primary w-100" title="Simpan perubahan ETD/ETA/status item">Simpan Jadwal</button></div>
>>>>>>> theirs
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-outline card-warning mb-3">
            <div class="card-header"><h3 class="card-title">Transisi Status PO</h3></div>
            <div class="card-body">
<<<<<<< ours
                <form method="POST" action="{{ route('po.transition', $po->id) }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">Status Tujuan</label>
                        <select name="to_status" class="form-select" required>
=======
                <form method="POST" action="{{ route('po.transition', $po->id) }}" title="Form perpindahan status dokumen PO sesuai aturan transisi">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">Status Tujuan</label><small class="text-muted d-block">Pilih transisi yang tersedia saja agar status valid.</small>
                        <select name="to_status" class="form-select" title="Pilih status tujuan PO" aria-label="Status tujuan PO" required>
>>>>>>> theirs
                            @forelse($allowedTransitions as $status)
                                <option value="{{ $status }}">{{ $status }}</option>
                            @empty
                                <option value="">Tidak ada transisi tersedia</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Catatan</label>
<<<<<<< ours
                        <textarea name="note" class="form-control" rows="2"></textarea>
                    </div>
                    <button class="btn btn-warning w-100" @disabled(empty($allowedTransitions))>Update Status</button>
=======
                        <textarea name="note" class="form-control" rows="2" title="Catatan alasan perubahan status PO" aria-label="Catatan transisi status"></textarea>
                    </div>
                    <button class="btn btn-warning w-100" title="Jalankan perubahan status PO" @disabled(empty($allowedTransitions))>Update Status</button>
>>>>>>> theirs
                    <small class="text-muted d-block mt-2">Transisi valid dari status saat ini: <strong>{{ empty($allowedTransitions) ? '-' : implode(', ', $allowedTransitions) }}</strong></small>
                </form>
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
@endsection
