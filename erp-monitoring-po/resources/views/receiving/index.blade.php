@extends('layouts.erp')
@php($title='Goods Receiving')
@php($header='Goods Receiving Per Item')
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
                    <option value="">Semua PO Open</option>
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
        <table class="table table-hover mb-0">
            <thead><tr><th>PO</th><th>Item</th><th>Ordered</th><th>Received</th><th>Outstanding</th><th>Input Kedatangan</th></tr></thead>
            <tbody>
            @forelse($poItems as $item)
                <tr>
                    <td>{{ $item->po_number }}<br><span class="badge bg-info">{{ $item->po_status }}</span></td>
                    <td><strong>{{ $item->item_code }}</strong><br>{{ $item->item_name }}</td>
                    <td>{{ number_format($item->ordered_qty, 2, ',', '.') }}</td>
                    <td>{{ number_format($item->received_qty, 2, ',', '.') }}</td>
                    <td><span class="badge bg-warning text-dark">{{ number_format($item->outstanding_qty, 2, ',', '.') }}</span></td>
                    <td>
                        <form method="POST" action="{{ route('receiving.store') }}" class="row g-1 align-items-center">
                            @csrf
                            <input type="hidden" name="purchase_order_item_id" value="{{ $item->id }}">
                            <div class="col-md-3"><input type="date" name="receipt_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required></div>
                            <div class="col-md-3"><input type="number" step="0.01" name="received_qty" class="form-control" placeholder="Qty" required></div>
                            <div class="col-md-3"><input type="text" name="document_number" class="form-control" placeholder="No Dok"></div>
                            <div class="col-md-3"><button class="btn btn-success w-100">Post Item</button></div>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">Tidak ada item outstanding untuk diproses.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card"><div class="card-header"><h3 class="card-title">Riwayat Goods Receipt</h3></div><div class="card-body table-responsive p-0"><table class="table table-hover text-nowrap mb-0"><thead><tr><th>GR</th><th>PO</th><th>Supplier</th><th>Tgl</th></tr></thead><tbody>@foreach($rows as $r)<tr><td>{{ $r->gr_number }}</td><td>{{ $r->po_number }}</td><td>{{ $r->supplier_name }}</td><td>{{ \Carbon\Carbon::parse($r->receipt_date)->format('d-m-Y') }}</td></tr>@endforeach</tbody></table></div></div>
<div class="mt-2">{{ $rows->links() }}</div>
@endsection
