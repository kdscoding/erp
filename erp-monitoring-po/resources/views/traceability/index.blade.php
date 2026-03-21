@extends('layouts.erp')
@php($title='Traceability')
@php($header='Laporan Traceability PO & Receiving')

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
<div class="card card-outline card-primary mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-4"><input name="po_number" class="form-control form-control-sm" placeholder="Cari Nomor PO" value="{{ request('po_number') }}"></div>
            <div class="col-md-2"><button class="btn btn-primary btn-sm w-100">Cari</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap mb-0 data-table">
            <thead>
                <tr>
                    <th>PO</th>
                    <th>Tgl PO</th>
                    <th>Supplier</th>
                    <th>Item</th>
                    <th>ETD</th>
                    <th>Penerimaan</th>
                    <th>Status</th>
                    <th>Timeline Ringkas</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $r)
                    <tr>
                        <td>{{ $r->po_number }}</td>
                        <td>{{ \Carbon\Carbon::parse($r->po_date)->format('d-m-Y') }}</td>
                        <td>{{ $r->supplier_name }}</td>
                        <td>{{ $r->item_code }} - {{ $r->item_name }}</td>
                        <td>{{ $r->etd_date ? \Carbon\Carbon::parse($r->etd_date)->format('d-m-Y') : '-' }}</td>
                        <td>{{ \App\Support\NumberFormatter::trim($r->received_qty) }} / {{ \App\Support\NumberFormatter::trim($r->ordered_qty) }}<br><small class="text-muted">Parsial: {{ $r->receipt_count }}x</small></td>
                        <td><span class="badge bg-{{ $statusBadge($r->item_status) }}">{{ \App\Support\TermCatalog::label('po_item_status', $r->item_status, $r->item_status) }}</span></td>
                        <td>
                            Dibuat: {{ \Carbon\Carbon::parse($r->po_date)->format('d-m-Y') }}<br>
                            ETD: {{ $r->etd_date ? \Carbon\Carbon::parse($r->etd_date)->format('d-m-Y') : '-' }}<br>
                            Datang #1: {{ $r->first_receipt_date ? \Carbon\Carbon::parse($r->first_receipt_date)->format('d-m-Y') : '-' }}<br>
                            Datang Terakhir: {{ $r->last_receipt_date ? \Carbon\Carbon::parse($r->last_receipt_date)->format('d-m-Y') : '-' }}
                            @if($r->cancel_reason)<br><span class="text-danger">Cancel Reason: {{ $r->cancel_reason }}</span>@endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted">Belum ada data traceability.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-2">{{ $rows->links() }}</div>
@endsection
