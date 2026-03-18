<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    public function index()
    {
        $rows = DB::table('warehouses')->orderByDesc('id')->paginate(20);
        return view('masters.warehouses.index', compact('rows'));
    }

    public function store(Request $request)
    {
        $v = $request->validate(['warehouse_code' => 'required', 'warehouse_name' => 'required']);
        DB::table('warehouses')->updateOrInsert(
            ['warehouse_code' => $v['warehouse_code']],
            ['warehouse_name' => $v['warehouse_name'], 'location' => $request->location, 'updated_at' => now(), 'created_at' => now()]
        );
        return back()->with('success', 'Warehouse tersimpan.');
    }
}
