@extends('layouts.erp')
@php($title='Traceability')
@php($header='Traceability')
@php($headerSubtitle='Laporan jejak PO, ETD, dan receiving untuk membaca timeline dokumen secara ringkas.')

@php
    $statusBadge = static function ($status) {
        return match ($status) {
            'Closed' => 'success',
            'Open', 'Partial', 'Confirmed', 'Waiting', 'PO Issued' => 'warning text-dark',
            'Late', 'Cancelled' => 'danger',
            default => 'secondary',
        };
    };
@endphp

@section('content')
<div class="page-shell">
    <section class="page-head">
        <div class="page-head-main">
            <h2 class="page-section-title">Traceability Report</h2>
            <p class="page-section-subtitle">Cari per nomor PO lalu baca alur dokumen dari dibuat sampai diterima.</p>
        </div>
    </section>

    <section class="ui-surface">
        <div class="ui-surface-head">
            <div>
                <h3 class="ui-surface-title">Filter Traceability</h3>
                <div class="ui-surface-subtitle">Gunakan nomor PO untuk mempersempit hasil pelacakan.</div>
            </div>
        </div>
        <form method="GET" class="filter-grid">
            <div class="span-4">
                <label class="field-label">Nomor PO</label>
                <input name="po_number" class="form-control form-control-sm" placeholder="Cari Nomor PO" value="{{ request('po_number') }}">
            </div>
            <div class="span-2">
                <button class="btn btn-primary btn-sm w-100">Cari</button>
            </div>
            <div class="span-2">
                <a href="{{ route('traceability.index') }}" class="btn btn-light btn-sm w-100">Reset</a>
            </div>
        </form>
    </section>

    <section class="ui-surface">
        <div class="ui-surface-head">
            <div>
                <h3 class="ui-surface-title">Timeline PO & Receiving</h3>
                <div class="ui-surface-subtitle">Setiap row merangkum item, ETD, status, dan jejak penerimaan.</div>
            </div>
        </div>
        <div class="table-wrap table-responsive">
            <table class="table table-hover ui-table data-table-advanced">
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
                            <td>
                                <div class="doc-number">{{ $r->item_code }}</div>
                                <div class="doc-meta">{{ $r->item_name }}</div>
                            </td>
                            <td>{{ $r->etd_date ? \Carbon\Carbon::parse($r->etd_date)->format('d-m-Y') : '-' }}</td>
                            <td>{{ \App\Support\NumberFormatter::trim($r->received_qty) }} / {{ \App\Support\NumberFormatter::trim($r->ordered_qty) }}<br><span class="doc-meta">Parsial: {{ $r->receipt_count }}x</span></td>
                            <td><span class="badge bg-{{ $statusBadge($r->item_status) }}">{{ \App\Support\TermCatalog::label('po_item_status', $r->item_status, $r->item_status) }}</span></td>
                            <td>
                                Dibuat: {{ \Carbon\Carbon::parse($r->po_date)->format('d-m-Y') }}<br>
                                ETD: {{ $r->etd_date ? \Carbon\Carbon::parse($r->etd_date)->format('d-m-Y') : '-' }}<br>
                                Datang #1: {{ $r->first_receipt_date ? \Carbon\Carbon::parse($r->first_receipt_date)->format('d-m-Y') : '-' }}<br>
                                Datang Terakhir: {{ $r->last_receipt_date ? \Carbon\Carbon::parse($r->last_receipt_date)->format('d-m-Y') : '-' }}
                                @if($r->cancel_reason)
                                    <br><span class="text-danger">Cancel Reason: {{ $r->cancel_reason }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">Belum ada data traceability.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
