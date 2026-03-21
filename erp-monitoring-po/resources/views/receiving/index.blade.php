@extends('layouts.erp')
@php($title = $mode === 'history' ? 'Riwayat Goods Receipt' : 'Goods Receiving')
@php($header = $mode === 'history' ? 'Riwayat Goods Receipt' : 'Goods Receiving')

@section('content')
@if ($mode === 'process')
<div class="card card-outline card-primary mb-3">
    <div class="card-header"><h3 class="card-title">1. Cari Dokumen Shipment</h3></div>
    <div class="card-body">
        <form method="GET" action="{{ route('receiving.process') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Supplier</label>
                <select name="supplier_id" class="form-select form-select-sm">
                    <option value="">Semua Supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>{{ $supplier->supplier_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Delivery Note</label>
                <input type="text" name="document_number" value="{{ request('document_number') }}" class="form-control form-control-sm" placeholder="No surat jalan supplier">
            </div>
            <div class="col-md-4">
                <label class="form-label">Cari Shipment / PO / Supplier</label>
                <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control form-control-sm" placeholder="Shipment number, PO, supplier">
            </div>
            <div class="col-md-2"><button class="btn btn-primary btn-sm w-100">Tampilkan Dokumen</button></div>
        </form>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header"><h3 class="card-title">2. Pilih Dokumen Yang Akan Diterima</h3></div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Shipment</th>
                    <th>Supplier</th>
                    <th>Delivery Note</th>
                    <th>Tanggal Dokumen</th>
                    <th>PO</th>
                    <th>Line Item</th>
                    <th>Sisa Kiriman</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shipmentDocuments as $document)
                    <tr>
                        <td>{{ $document->shipment_number }}<br><span class="badge {{ $document->status === 'Shipped' ? 'bg-primary' : 'bg-warning text-dark' }}">{{ $document->status }}</span></td>
                        <td>{{ $document->supplier_name }}</td>
                        <td>{{ $document->delivery_note_number }}</td>
                        <td>{{ \Carbon\Carbon::parse($document->shipment_date)->format('d-m-Y') }}</td>
                        <td>{{ $document->po_count }}</td>
                        <td>{{ $document->line_count }}</td>
                        <td>{{ number_format($document->outstanding_qty, 2, ',', '.') }}</td>
                        <td><a href="{{ route('receiving.process', ['shipment_id' => $document->id, 'supplier_id' => request('supplier_id'), 'document_number' => request('document_number'), 'keyword' => request('keyword')]) }}" class="btn btn-sm btn-outline-primary">Pilih Dokumen</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted">Belum ada dokumen shipment yang siap diterima.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card card-outline card-primary mb-3">
    <div class="card-header"><h3 class="card-title">3. Konfirmasi Receiving</h3></div>
    <div class="card-body">
        @if ($selectedShipment)
            <div class="alert alert-info">
                Warehouse akan memproses dokumen <strong>{{ $selectedShipment->shipment_number }}</strong> dari supplier <strong>{{ $selectedShipment->supplier_name }}</strong> dengan delivery note <strong>{{ $selectedShipment->delivery_note_number }}</strong>.
            </div>

            <div class="d-flex justify-content-end mb-3">
                <a href="{{ route('receiving.process', ['clear_selection' => 1, 'supplier_id' => request('supplier_id'), 'document_number' => request('document_number'), 'keyword' => request('keyword')]) }}" class="btn btn-sm btn-outline-secondary">Batalkan Pilihan Dokumen</a>
            </div>

            <form method="POST" action="{{ route('receiving.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="shipment_id" value="{{ $selectedShipment->id }}">
                <input type="hidden" name="supplier_id" value="{{ request('supplier_id') }}">
                <input type="hidden" name="search_document_number" value="{{ request('document_number') }}">

                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Terima</label>
                        <input type="date" name="receipt_date" class="form-control form-control-sm" value="{{ old('receipt_date', now()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">No Dokumen Receiving</label>
                        <input type="text" name="document_number" class="form-control form-control-sm" value="{{ old('document_number', $selectedShipment->delivery_note_number) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Lampiran</label>
                        <input type="file" name="attachment" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.pdf">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Catatan</label>
                        <input type="text" name="note" class="form-control form-control-sm" value="{{ old('note') }}" placeholder="Opsional">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-3">
                        <thead>
                            <tr>
                                <th>PO</th>
                                <th>Item</th>
                                <th>Qty Dikirim</th>
                                <th>Sudah Diterima</th>
                                <th>Sisa Bisa Diterima</th>
                                <th>Qty Diterima Sekarang</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shipmentItems as $item)
                                <tr>
                                    <td>{{ $item->po_number }}</td>
                                    <td><strong>{{ $item->item_code }}</strong><br>{{ $item->item_name }}</td>
                                    <td>{{ number_format($item->shipped_qty, 2, ',', '.') }}</td>
                                    <td>{{ number_format($item->shipment_received_qty, 2, ',', '.') }}</td>
                                    <td><span class="badge bg-warning text-dark">{{ number_format($item->shipment_outstanding_qty, 2, ',', '.') }}</span></td>
                                    <td><input type="number" step="0.01" min="0" max="{{ $item->shipment_outstanding_qty }}" name="received_qty[{{ $item->shipment_item_id }}]" value="{{ old('received_qty.'.$item->shipment_item_id) }}" class="form-control form-control-sm" placeholder="Isi jika datang"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end">
                    <button class="btn btn-success btn-sm">Posting Receiving Dokumen Ini</button>
                </div>
            </form>
        @else
            <div class="text-muted">Pilih dulu satu dokumen shipment di tabel atas. Setelah itu item akan muncul otomatis dan warehouse tinggal mengisi qty yang benar-benar diterima.</div>
        @endif
    </div>
</div>
@endif

@if ($mode === 'history')
<div class="card card-outline card-secondary mb-3">
    <div class="card-header"><h3 class="card-title">Filter Riwayat Goods Receipt</h3></div>
    <div class="card-body">
        <form method="GET" action="{{ route('receiving.history') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">No Dokumen Receiving</label>
                <input type="text" name="document_number" value="{{ request('document_number') }}" class="form-control form-control-sm" placeholder="Nomor dokumen GR">
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-secondary btn-sm w-100">Cari Riwayat</button>
            </div>
            <div class="col-md-6">
                <div class="alert alert-light border mb-0">Riwayat GR dipisah dari halaman proses supaya user warehouse fokus ke dokumen aktif saat posting receiving.</div>
            </div>
        </form>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header"><h3 class="card-title">{{ $mode === 'history' ? 'Daftar Riwayat Goods Receipt' : 'Riwayat Goods Receipt' }}</h3></div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap mb-0 {{ $mode === 'history' ? '' : 'data-table' }}">
            <thead>
                <tr>
                    <th>GR</th>
                    <th>Shipment</th>
                    <th>PO</th>
                    <th>Supplier</th>
                    <th>No Dok</th>
                    <th>Tgl</th>
                    @if ($mode === 'history')
                        <th>Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $r)
                    <tr>
                        <td>{{ $r->gr_number }}</td>
                        <td>{{ $r->shipment_number ?: '-' }}</td>
                        <td>{{ $r->po_number }}</td>
                        <td>{{ $r->supplier_name }}</td>
                        <td>{{ $r->document_number ?: $r->delivery_note_number ?: '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($r->receipt_date)->format('d-m-Y') }}</td>
                        @if ($mode === 'history')
                            <td><a href="{{ route('receiving.show', $r->id) }}" class="btn btn-sm btn-light">Detail</a></td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-2">{{ $rows->links() }}</div>
@endsection
