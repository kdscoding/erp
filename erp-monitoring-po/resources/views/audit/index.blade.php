@extends('layouts.erp')
@php($title='Audit Trail')
@php($header='Audit Trail')
@section('content')
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-hover text-nowrap mb-0"><thead><tr><th>Waktu</th><th>Module</th><th>Aksi</th><th>User</th><th>IP</th></tr></thead><tbody>@forelse($rows as $r)<tr><td>{{ $r->created_at }}</td><td>{{ $r->module }}</td><td>{{ $r->action }}</td><td>{{ $r->user_id }}</td><td>{{ $r->ip_address }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted">Belum ada audit log</td></tr>@endforelse</tbody></table></div></div>
@endsection
