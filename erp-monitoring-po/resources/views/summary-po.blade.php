@extends('layouts.erp')

@php($title = 'Summary PO')
@php($header = 'Summary PO')
@php($headerSubtitle = 'Halaman ini sedang dipensiunkan dan digabung ke Monitoring Hub.')

@section('content')
    <div class="page-shell">
        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Summary PO Dipindahkan</h3>
                    <div class="ui-surface-subtitle">Untuk mengurangi layar yang mirip, summary per PO sekarang dibaca dari Monitoring Hub pada mode `PO View`.</div>
                </div>
            </div>
            <div class="ui-surface-body">
                <div class="soft-alert mb-3">
                    `Summary PO` dan `Summary Item` tidak lagi diposisikan sebagai laporan utama yang terpisah. Gunakan satu layar `Monitoring Hub` agar filter dan konteks tetap konsisten.
                </div>
                <div class="page-actions">
                    <a href="{{ route('monitoring.index', array_filter(['supplier_id' => $supplierId, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'mode' => 'po'])) }}" class="btn btn-sm btn-primary">Buka Monitoring Hub · PO View</a>
                    <a href="{{ route('monitoring.index', array_filter(['supplier_id' => $supplierId, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'mode' => 'item'])) }}" class="btn btn-sm btn-light">Buka Item View</a>
                </div>
            </div>
        </section>
    </div>
@endsection
