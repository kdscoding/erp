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
                                    </div>
                                    <div class="col-md-8">
                                        @php($percent = $item->ordered_qty > 0 ? min(100, round(($item->received_qty / $item->ordered_qty) * 100)) : 0)
                                        <div class="progress" style="height:8px;"><div class="progress-bar bg-success" style="width: {{ $percent }}%"></div></div>
                                        <small class="text-muted">Progress Item: {{ $percent }}%</small>
                                    </div>
                                    <div class="col-md-4"><button class="btn btn-sm btn-primary w-100">Simpan Jadwal</button></div>
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
                <form method="POST" action="{{ route('po.transition', $po->id) }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">Status Tujuan</label>
                        <select name="to_status" class="form-select" required>
                            @forelse($allowedTransitions as $status)
                                <option value="{{ $status }}">{{ $status }}</option>
                            @empty
                                <option value="">Tidak ada transisi tersedia</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Catatan</label>
                        <textarea name="note" class="form-control" rows="2"></textarea>
                    </div>
                    <button class="btn btn-warning w-100" @disabled(empty($allowedTransitions))>Update Status</button>
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
