@extends('layouts.erp')

@php($title='Dashboard ERP')
@php($header='Dashboard Monitoring PO')

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ \Illuminate\Support\Facades\DB::table('purchase_orders')->whereNotIn('status', ['Closed','Cancelled'])->count() }}</h3>
                <p>Total Open PO</p>
            </div>
            <div class="icon"><i class="fas fa-file-invoice"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ \Illuminate\Support\Facades\DB::table('purchase_orders')->where('status','Draft')->count() }}</h3>
                <p>Draft PO</p>
            </div>
            <div class="icon"><i class="fas fa-pen"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ \Illuminate\Support\Facades\DB::table('suppliers')->count() }}</h3>
                <p>Total Supplier</p>
            </div>
            <div class="icon"><i class="fas fa-truck"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ \Illuminate\Support\Facades\DB::table('items')->count() }}</h3>
                <p>Total Item</p>
            </div>
            <div class="icon"><i class="fas fa-tags"></i></div>
        </div>
    </div>
</div>

<div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title">Ringkasan Implementasi</h3></div>
    <div class="card-body">
        <ul class="mb-0">
            <li>Foundation + Auth + Role</li>
            <li>Master Data</li>
            <li>Purchase Order Basic</li>
            <li>UI Template AdminLTE (adapted)</li>
        </ul>
    </div>
</div>
@endsection
