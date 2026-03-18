<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlantController extends Controller
{
    public function index()
    {
        $rows = DB::table('plants')->orderByDesc('id')->paginate(20);
        return view('masters.plants.index', compact('rows'));
    }

    public function store(Request $request)
    {
        $v = $request->validate(['plant_code' => 'required', 'plant_name' => 'required']);
        DB::table('plants')->updateOrInsert(['plant_code' => $v['plant_code']], ['plant_name' => $v['plant_name'], 'updated_at' => now(), 'created_at' => now()]);
        return back()->with('success', 'Plant tersimpan.');
    }
}
