<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(Request $request): View
    {
        $rows = DB::table('units as u')
            ->leftJoin('items as i', 'i.unit_id', '=', 'u.id')
            ->select('u.*', DB::raw('count(i.id) as item_count'))
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = trim((string) $request->input('q'));
                $query->where(function ($builder) use ($q) {
                    $builder->where('u.unit_code', 'like', "%{$q}%")
                        ->orWhere('u.unit_name', 'like', "%{$q}%");
                });
            })
            ->groupBy('u.id', 'u.unit_code', 'u.unit_name', 'u.created_at', 'u.updated_at')
            ->orderBy('u.unit_name')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => DB::table('units')->count(),
            'used' => DB::table('items')->whereNotNull('unit_id')->distinct('unit_id')->count('unit_id'),
            'unused' => DB::table('units')->whereNotIn('id', DB::table('items')->select('unit_id')->whereNotNull('unit_id'))->count(),
        ];

        return view('masters.units.index', compact('rows', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $normalizedCode = strtoupper(trim((string) $request->input('unit_code')));
        $request->merge(['unit_code' => $normalizedCode]);

        $v = $request->validate([
            'unit_code' => ['required', 'string', 'max:50', Rule::unique('units', 'unit_code')],
            'unit_name' => 'required|string|max:100',
        ], ['required' => ':attribute wajib diisi.']);

        DB::table('units')->insert([
            'unit_code' => $normalizedCode,
            'unit_name' => trim((string) $v['unit_name']),
            'updated_at' => now(),
            'created_at' => now(),
        ]);

        return back()->with('success', 'Unit tersimpan.');
    }

    public function edit(string $id): View
    {
        $unit = DB::table('units')->where('id', $id)->firstOrFail();

        return view('masters.units.edit', compact('unit'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $unit = DB::table('units')->where('id', $id)->firstOrFail();
        $normalizedCode = strtoupper(trim((string) $request->input('unit_code')));
        $request->merge(['unit_code' => $normalizedCode]);

        $v = $request->validate([
            'unit_code' => ['required', 'string', 'max:50', Rule::unique('units', 'unit_code')->ignore($unit->id)],
            'unit_name' => 'required|string|max:100',
        ], ['required' => ':attribute wajib diisi.']);

        DB::table('units')->where('id', $id)->update([
            'unit_code' => $normalizedCode,
            'unit_name' => trim((string) $v['unit_name']),
            'updated_at' => now(),
        ]);

        return redirect()->route('units.index')->with('success', 'Unit berhasil diperbarui.');
    }
}
