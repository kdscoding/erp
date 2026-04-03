@extends('layouts.erp')

@php($title = 'Audit Viewer')
@php($header = 'Audit Viewer')
@php($headerSubtitle = 'Review perubahan operasional tanpa buka database langsung, lengkap dengan actor, modul, dan ringkasan before/after.')

@section('content')
    <div class="page-shell">
        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Filter Audit Log</h3>
                    <div class="ui-surface-subtitle">Saring audit berdasarkan modul, actor, action, dan periode kejadian.</div>
                </div>
            </div>

            <form method="GET" class="filter-grid">
                <div class="span-3">
                    <label class="field-label">Module</label>
                    <select name="module" class="form-control form-control-sm">
                        <option value="">Semua Module</option>
                        @foreach ($modules as $moduleOption)
                            <option value="{{ $moduleOption }}" @selected($module === $moduleOption)>{{ $moduleOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="span-3">
                    <label class="field-label">Actor</label>
                    <select name="actor_id" class="form-control form-control-sm">
                        <option value="">Semua Actor</option>
                        @foreach ($actors as $actor)
                            <option value="{{ $actor->id }}" @selected($actorId === (int) $actor->id)>{{ $actor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="span-2">
                    <label class="field-label">Action</label>
                    <select name="action" class="form-control form-control-sm">
                        <option value="">Semua Action</option>
                        @foreach ($actions as $actionOption)
                            <option value="{{ $actionOption }}" @selected($action === $actionOption)>{{ $actionOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="span-2">
                    <label class="field-label">Date From</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control form-control-sm">
                </div>
                <div class="span-2">
                    <label class="field-label">Date To</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control form-control-sm">
                </div>
                <div class="span-1"><button class="btn btn-primary btn-sm w-100">Apply</button></div>
                <div class="span-1"><a href="{{ route('audit.index') }}" class="btn btn-light btn-sm w-100">Reset</a></div>
            </form>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Audit Log List</h3>
                    <div class="ui-surface-subtitle">Ringkasan perubahan per event untuk memudahkan review cepat.</div>
                </div>
            </div>

            <div class="table-wrap table-responsive">
                <table class="table table-hover ui-table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Actor</th>
                            <th>Module</th>
                            <th>Action</th>
                            <th>Record</th>
                            <th>Before</th>
                            <th>After</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($auditLogs as $log)
                            <tr>
                                <td>
                                    <div class="doc-number">{{ \Carbon\Carbon::parse($log->created_at)->format('d-m-Y H:i') }}</div>
                                </td>
                                <td>
                                    <div class="doc-number">{{ $log->actor_label }}</div>
                                    <div class="doc-meta">{{ $log->role_labels->isNotEmpty() ? $log->role_labels->implode(', ') : 'No role' }}</div>
                                </td>
                                <td>{{ $log->module }}</td>
                                <td><span class="qty">{{ $log->action }}</span></td>
                                <td>#{{ $log->record_id ?: '-' }}</td>
                                <td>{{ $log->old_summary }}</td>
                                <td>{{ $log->new_summary }}</td>
                                <td>{{ $log->ip_address ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">Belum ada audit log pada filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-3 pb-3">
                {{ $auditLogs->links() }}
            </div>
        </section>
    </div>
@endsection
