@extends('layouts.erp')
@php($title='Audit Trail')
@php($header='Audit Trail')
@php($headerSubtitle='Riwayat perubahan modul dan aktivitas user untuk kebutuhan audit internal.')

@section('content')
<div class="page-shell">
    <section class="page-head">
        <div class="page-head-main">
            <h2 class="page-section-title">Audit Log</h2>
            <p class="page-section-subtitle">Tampilkan log secara sederhana dalam satu tabel yang mudah discan.</p>
        </div>
    </section>

    <section class="ui-surface">
        <div class="ui-surface-head">
            <div>
                <h3 class="ui-surface-title">Riwayat Aktivitas</h3>
                <div class="ui-surface-subtitle">Waktu, modul, aksi, user, dan IP ditampilkan langsung di tabel.</div>
            </div>
        </div>
        <div class="table-wrap table-responsive">
            <table class="table table-hover ui-table">
                <thead><tr><th>Waktu</th><th>Module</th><th>Aksi</th><th>User</th><th>IP</th></tr></thead>
                <tbody>
                    @forelse($rows as $r)
                        <tr>
                            <td>{{ $r->created_at }}</td>
                            <td>{{ $r->module }}</td>
                            <td>{{ $r->action }}</td>
                            <td>{{ $r->user_id }}</td>
                            <td>{{ $r->ip_address }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Belum ada audit log.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
