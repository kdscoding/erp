<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class AuditTrailController extends Controller
{
    public function index()
    {
        $rows = DB::table('audit_logs')->orderByDesc('id')->paginate(50);
        return view('audit.index', compact('rows'));
    }
}
