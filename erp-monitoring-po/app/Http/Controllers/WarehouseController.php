<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(Request $request): View
    {
        $rows = DB::table('warehouses as w')
            ->leftJoin('purchase_orders as po', 'po.warehouse_id', '=', 'w.id')
            ->leftJoin('goods_receipts as gr', 'gr.warehouse_id', '=', 'w.id')
            ->select(
                'w.*',
                DB::raw('count(distinct po.id) as po_count'),
                DB::raw('count(distinct gr.id) as gr_count')
            )
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = trim((string) $request->input('q'));
                $query->where(function ($builder) use ($q) {
                    $builder->where('w.warehouse_code', 'like', "%{$q}%")
                        ->orWhere('w.warehouse_name', 'like', "%{$q}%")
                        ->orWhere('w.location', 'like', "%{$q}%");
                });
            })
            ->orderBy('w.warehouse_name')
            ->groupBy('w.id', 'w.warehouse_code', 'w.warehouse_name', 'w.location', 'w.created_at', 'w.updated_at')
            ->get();

        $stats = [
            'total' => DB::table('warehouses')->count(),
            'used_in_po' => DB::table('purchase_orders')->whereNotNull('warehouse_id')->distinct('warehouse_id')->count('warehouse_id'),
            'used_in_gr' => DB::table('goods_receipts')->whereNotNull('warehouse_id')->distinct('warehouse_id')->count('warehouse_id'),
        ];

        return view('masters.warehouses.index', compact('rows', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $normalizedCode = strtoupper(trim((string) $request->input('warehouse_code')));
        $request->merge(['warehouse_code' => $normalizedCode]);

        $v = $request->validate([
            'warehouse_code' => ['required', 'string', 'max:50', Rule::unique('warehouses', 'warehouse_code')],
            'warehouse_name' => 'required|string|max:150',
            'location' => 'nullable|string|max:255',
        ]);

        DB::table('warehouses')->insert([
            'warehouse_code' => $normalizedCode,
            'warehouse_name' => trim((string) $v['warehouse_name']),
            'location' => $v['location'] ?? null,
            'updated_at' => now(),
            'created_at' => now(),
        ]);

        return back()->with('success', 'Warehouse tersimpan.');
    }

    public function edit(string $id): View
    {
        $warehouse = DB::table('warehouses')->where('id', $id)->firstOrFail();

        return view('masters.warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $warehouse = DB::table('warehouses')->where('id', $id)->firstOrFail();
        $normalizedCode = strtoupper(trim((string) $request->input('warehouse_code')));
        $request->merge(['warehouse_code' => $normalizedCode]);

        $v = $request->validate([
            'warehouse_code' => ['required', 'string', 'max:50', Rule::unique('warehouses', 'warehouse_code')->ignore($warehouse->id)],
            'warehouse_name' => 'required|string|max:150',
            'location' => 'nullable|string|max:255',
        ]);

        DB::table('warehouses')->where('id', $id)->update([
            'warehouse_code' => $normalizedCode,
            'warehouse_name' => trim((string) $v['warehouse_name']),
            'location' => $v['location'] ?? null,
            'updated_at' => now(),
        ]);

        return redirect()->route('warehouses.index')->with('success', 'Warehouse berhasil diperbarui.');
    }
}
