<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public function index()
    {
        $rows = DB::table('items as i')
            ->leftJoin('units as u', 'u.id', '=', 'i.unit_id')
            ->select('i.*', 'u.unit_name')
            ->orderByDesc('i.id')
            ->paginate(20);
        $units = DB::table('units')->orderBy('unit_name')->get();
        return view('masters.items.index', compact('rows', 'units'));
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'item_code' => 'required',
            'item_name' => 'required',
        ]);

        DB::table('items')->updateOrInsert(
            ['item_code' => $v['item_code']],
            [
                'item_name' => $v['item_name'],
                'unit_id' => $request->unit_id,
                'category' => $request->category,
                'active' => 1,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return back()->with('success', 'Item tersimpan.');
    }
}
