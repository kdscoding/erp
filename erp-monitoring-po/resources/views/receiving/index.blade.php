@extends('layouts.erp')
@php($title='Goods Receiving')
@php($header='Goods Receiving Per Item')

@php
    $statusBadge = static function ($status) {
        return match ($status) {
            'Closed' => 'success',
            'Partial', 'Confirmed', 'Waiting', 'PO Issued' => 'warning text-dark',
            'Late', 'Cancelled' => 'danger',
            default => 'secondary',
        };
    };
@endphp

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="card card-outline card-primary mb-3">
    <div class="card-header"><h3 class="card-title">Filter Item Outstanding</h3></div>
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Pilih PO</label>
                <select name="po_id" class="form-select">
                    <option value="">Semua PO Aktif</option>
                    @foreach($openPoList as $po)
                        <option value="{{ $po->id }}" @selected(request('po_id') == $po->id)>{{ $po->po_number }} - {{ $po->supplier_name }} ({{ $po->status }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Tampilkan</button></div>
        </form>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header"><h3 class="card-title">Posting Receiving (Item per Item)</h3></div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover mb-0 data-table">
            <thead><tr><th>PO</th><th>Item</th><th>Ordered</th><th>Received</th><th>Outstanding</th><th>Status</th><th>Input Kedatangan</th></tr></thead>
            <tbody>
            @forelse($poItems as $item)
                <tr>
                    <td>{{ $item->po_number }}<br><span class="badge bg-{{ $statusBadge($item->po_status) }}">{{ $item->po_status }}</span></td>
                    <td><strong>{{ $item->item_code }}</strong><br>{{ $item->item_name }}</td>
                    <td>{{ number_format($item->ordered_qty, 2, ',', '.') }}</td>
                    <td>{{ number_format($item->received_qty, 2, ',', '.') }}</td>
                    <td><span class="badge bg-warning text-dark">{{ number_format($item->outstanding_qty, 2, ',', '.') }}</span></td>
                    <td><span class="badge bg-{{ $statusBadge($item->monitoring_status) }}">{{ $item->monitoring_status }}</span></td>
                    <td>
                        <form method="POST" action="{{ route('receiving.store') }}" class="row g-1 align-items-center receiving-form" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="purchase_order_item_id" value="{{ $item->id }}">
                            <input type="hidden" class="ordered-val" value="{{ $item->ordered_qty }}">
                            <input type="hidden" class="received-val" value="{{ $item->received_qty }}">
                            <div class="col-md-3"><input type="date" name="receipt_date" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}" required></div>
                            <div class="col-md-2"><input type="number" step="0.01" name="received_qty" class="form-control form-control-sm input-qty" placeholder="Qty" required></div>
                            <div class="col-md-3"><input type="file" name="attachment" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.pdf"></div>
                            <div class="col-md-2"><input type="text" name="document_number" class="form-control form-control-sm" placeholder="No Dok"></div>
                            <div class="col-md-2"><button class="btn btn-success btn-sm w-100">Post</button></div>
                            <div class="col-12"><small class="text-muted">Outstanding realtime: <span class="fw-bold outstanding-preview">{{ number_format($item->outstanding_qty, 2, ',', '.') }}</span></small></div>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted">Tidak ada item outstanding untuk diproses.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card"><div class="card-header"><h3 class="card-title">Riwayat Goods Receipt</h3></div><div class="card-body table-responsive p-0"><table class="table table-hover text-nowrap mb-0 data-table"><thead><tr><th>GR</th><th>PO</th><th>Supplier</th><th>Tgl</th></tr></thead><tbody>@foreach($rows as $r)<tr><td>{{ $r->gr_number }}</td><td>{{ $r->po_number }}</td><td>{{ $r->supplier_name }}</td><td>{{ \Carbon\Carbon::parse($r->receipt_date)->format('d-m-Y') }}</td></tr>@endforeach</tbody></table></div></div>
<div class="mt-2">{{ $rows->links() }}</div>

<script>
    document.querySelectorAll('.receiving-form').forEach((form) => {
        const qtyInput = form.querySelector('.input-qty');
        const ordered = parseFloat(form.querySelector('.ordered-val').value || '0');
        const received = parseFloat(form.querySelector('.received-val').value || '0');
        const preview = form.querySelector('.outstanding-preview');

        const refreshPreview = () => {
            const incoming = parseFloat(qtyInput.value || '0');
            const nextOutstanding = Math.max(0, ordered - (received + incoming));
            preview.textContent = new Intl.NumberFormat('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}).format(nextOutstanding);
        };

        qtyInput.addEventListener('input', refreshPreview);
    });
</script>
@endsection
