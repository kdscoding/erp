@extends('layouts.erp')

@php($title = 'Summary Item')
@php($header = 'Summary Item')
@php($headerSubtitle = 'Halaman ini sedang dipensiunkan dan digabung ke Monitoring Hub.')

@section('content')
    <div class="page-shell">
        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Summary Item Dipindahkan</h3>
                    <div class="ui-surface-subtitle">Untuk mengurangi duplikasi layar, summary item sekarang dibaca dari Monitoring Hub pada mode `Item View`.</div>
                </div>
            </div>
            <div class="ui-surface-body">
                <div class="soft-alert mb-3">
                    Halaman item outstanding sekarang menjadi bagian dari Monitoring Hub agar filter supplier/periode, summary chip, dan konteks operasional tetap satu.
                </div>
                <div class="page-actions">
                    <a href="{{ route('monitoring.index', array_filter(['supplier_id' => $supplierId, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'mode' => 'item'])) }}" class="btn btn-sm btn-primary">Buka Monitoring Hub · Item View</a>
                    <a href="{{ route('monitoring.index', array_filter(['supplier_id' => $supplierId, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'mode' => 'po'])) }}" class="btn btn-sm btn-light">Buka PO View</a>
                </div>
            </div>
        </section>
    </div>
@endsection
