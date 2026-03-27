<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlantController extends Controller
{
    public function index(Request $request): View
    {
        $rows = DB::table('plants as p')
            ->leftJoin('purchase_orders as po', 'po.plant_id', '=', 'p.id')
            ->select('p.*', DB::raw('count(po.id) as po_count'))
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = trim((string) $request->input('q'));
                $query->where(function ($builder) use ($q) {
                    $builder->where('p.plant_code', 'like', "%{$q}%")
                        ->orWhere('p.plant_name', 'like', "%{$q}%");
                });
            })
            ->groupBy('p.id', 'p.plant_code', 'p.plant_name', 'p.created_at', 'p.updated_at')
            ->orderBy('p.plant_name')
            ->get();

        $stats = [
            'total' => DB::table('plants')->count(),
            'used_in_po' => DB::table('purchase_orders')->whereNotNull('plant_id')->distinct('plant_id')->count('plant_id'),
            'unused' => DB::table('plants')->whereNotIn('id', DB::table('purchase_orders')->select('plant_id')->whereNotNull('plant_id'))->count(),
        ];

        return view('masters.plants.index', compact('rows', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $normalizedCode = strtoupper(trim((string) $request->input('plant_code')));
        $request->merge(['plant_code' => $normalizedCode]);

        $v = $request->validate([
            'plant_code' => ['required', 'string', 'max:50', Rule::unique('plants', 'plant_code')],
            'plant_name' => 'required|string|max:150',
        ]);

        DB::table('plants')->insert([
            'plant_code' => $normalizedCode,
            'plant_name' => trim((string) $v['plant_name']),
            'updated_at' => now(),
            'created_at' => now(),
        ]);

        return back()->with('success', 'Plant tersimpan.');
    }

    public function edit(string $id): View
    {
        $plant = DB::table('plants')->where('id', $id)->firstOrFail();

        return view('masters.plants.edit', compact('plant'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $plant = DB::table('plants')->where('id', $id)->firstOrFail();
        $normalizedCode = strtoupper(trim((string) $request->input('plant_code')));
        $request->merge(['plant_code' => $normalizedCode]);

        $v = $request->validate([
            'plant_code' => ['required', 'string', 'max:50', Rule::unique('plants', 'plant_code')->ignore($plant->id)],
            'plant_name' => 'required|string|max:150',
        ]);

        DB::table('plants')->where('id', $id)->update([
            'plant_code' => $normalizedCode,
            'plant_name' => trim((string) $v['plant_name']),
            'updated_at' => now(),
        ]);

        return redirect()->route('plants.index')->with('success', 'Plant berhasil diperbarui.');
    }
}
