<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnitController extends Controller
{
    public function index()
    {
        $rows = DB::table('units')->orderByDesc('id')->paginate(20);
        return view('masters.units.index', compact('rows'));
    }

    public function store(Request $request)
    {
        $v = $request->validate(['unit_code' => 'required', 'unit_name' => 'required'], ['required' => ':attribute wajib diisi.']);
        DB::table('units')->updateOrInsert(['unit_code' => $v['unit_code']], ['unit_name' => $v['unit_name'], 'updated_at' => now(), 'created_at' => now()]);
        return back()->with('success', 'Unit tersimpan.');
    }
}
