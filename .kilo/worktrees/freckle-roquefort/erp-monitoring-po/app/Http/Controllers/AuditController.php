<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuditController extends Controller
{
    public function index(Request $request): View
    {
        $module = $request->string('module')->toString();
        $actorId = $request->integer('actor_id');
        $action = $request->string('action')->toString();
        $recordId = $request->integer('record_id');
        $dateFrom = $request->date('date_from')?->format('Y-m-d');
        $dateTo = $request->date('date_to')?->format('Y-m-d');

        $roleAggregate = DB::table('user_roles as ur')
            ->join('roles as r', 'r.id', '=', 'ur.role_id')
            ->select('ur.user_id')
            ->selectRaw('GROUP_CONCAT(r.slug) as role_slugs')
            ->groupBy('ur.user_id');

        $auditLogs = DB::table('audit_logs as al')
            ->leftJoin('users as u', 'u.id', '=', 'al.user_id')
            ->leftJoinSub($roleAggregate, 'actor_roles', function ($join) {
                $join->on('actor_roles.user_id', '=', 'al.user_id');
            })
            ->when($module !== '', fn ($query) => $query->where('al.module', $module))
            ->when($actorId, fn ($query) => $query->where('al.user_id', $actorId))
            ->when($action !== '', fn ($query) => $query->where('al.action', $action))
            ->when($recordId, fn ($query) => $query->where('al.record_id', $recordId))
            ->when($dateFrom, fn ($query) => $query->whereDate('al.created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('al.created_at', '<=', $dateTo))
            ->select(
                'al.id',
                'al.module',
                'al.record_id',
                'al.action',
                'al.old_values',
                'al.new_values',
                'al.ip_address',
                'al.created_at',
                'u.name as actor_name',
                'actor_roles.role_slugs'
            )
            ->orderByDesc('al.created_at')
            ->orderByDesc('al.id')
            ->paginate(25)
            ->withQueryString()
            ->through(function ($log) {
                $log->actor_label = $log->actor_name ?: 'System';
                $log->role_labels = collect(explode(',', (string) $log->role_slugs))
                    ->filter()
                    ->map(fn ($role) => Str::headline($role))
                    ->values();
                $log->old_payload = $this->decodeAuditPayload($log->old_values);
                $log->new_payload = $this->decodeAuditPayload($log->new_values);
                $log->old_summary = $this->summarizeAuditPayload($log->old_payload);
                $log->new_summary = $this->summarizeAuditPayload($log->new_payload);
                $log->changed_fields = $this->diffAuditKeys($log->old_payload, $log->new_payload);
                $log->changed_field_count = $log->changed_fields->count();
                $log->old_pretty_json = $this->prettyAuditPayload($log->old_payload);
                $log->new_pretty_json = $this->prettyAuditPayload($log->new_payload);

                return $log;
            });

        $modules = DB::table('audit_logs')
            ->select('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module');

        $actions = DB::table('audit_logs')
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $actors = DB::table('audit_logs as al')
            ->join('users as u', 'u.id', '=', 'al.user_id')
            ->select('u.id', 'u.name')
            ->distinct()
            ->orderBy('u.name')
            ->get();

        return view('audit.index', compact(
            'auditLogs',
            'modules',
            'actions',
            'actors',
            'module',
            'actorId',
            'action',
            'recordId',
            'dateFrom',
            'dateTo'
        ));
    }

    private function decodeAuditPayload(?string $payload): array
    {
        if (! $payload) {
            return [];
        }

        $decoded = json_decode($payload, true);
        if (! is_array($decoded) || $decoded === []) {
            return ['value' => (string) $payload];
        }

        return $decoded;
    }

    private function summarizeAuditPayload(array $payload): string
    {
        if ($payload === []) {
            return '-';
        }

        $segments = collect($payload)
            ->map(function ($value, $key) {
                if (is_array($value)) {
                    $value = collect($value)
                        ->take(3)
                        ->map(fn ($item, $nestedKey) => is_scalar($item) ? "{$nestedKey}:{$item}" : $nestedKey)
                        ->implode(', ');
                    $value = '[' . $value . (count($value ? explode(', ', $value) : []) >= 3 ? '...' : '') . ']';
                } elseif (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                } elseif ($value === null || $value === '') {
                    $value = '-';
                }

                return "{$key}: {$value}";
            })
            ->take(3)
            ->implode(' | ');

        return Str::limit($segments, 140);
    }

    private function diffAuditKeys(array $oldPayload, array $newPayload)
    {
        $allKeys = collect(array_unique([
            ...array_keys($oldPayload),
            ...array_keys($newPayload),
        ]));

        return $allKeys
            ->filter(function ($key) use ($oldPayload, $newPayload) {
                return ($oldPayload[$key] ?? null) !== ($newPayload[$key] ?? null);
            })
            ->values();
    }

    private function prettyAuditPayload(array $payload): string
    {
        if ($payload === []) {
            return '-';
        }

        return (string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
