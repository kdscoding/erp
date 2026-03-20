<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function index(Request $request): View
    {
        $rows = DB::table('items as i')
            ->leftJoin('units as u', 'u.id', '=', 'i.unit_id')
            ->select('i.*', 'u.unit_name')
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = trim((string) $request->input('q'));
                $query->where(function ($qBuilder) use ($q) {
                    $qBuilder->where('i.item_code', 'like', "%{$q}%")
                        ->orWhere('i.item_name', 'like', "%{$q}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('i.active', (int) $request->input('status')))
            ->orderByDesc('i.id')
            ->paginate(20)
            ->withQueryString();

        $units = DB::table('units')->orderBy('unit_name')->get();

        return view('masters.items.index', compact('rows', 'units'));
    }

    public function store(Request $request): RedirectResponse
    {
        $normalizedCode = strtoupper(trim((string) $request->input('item_code')));
        $request->merge(['item_code' => $normalizedCode]);

        $v = $request->validate([
            'item_code' => ['required', 'string', 'max:50', Rule::unique('items', 'item_code')],
            'item_name' => 'required|string|max:255',
            'unit_id' => 'nullable|integer|exists:units,id',
            'category' => 'nullable|string|max:100',
            'specification' => 'nullable|string|max:500',
        ], [
            'item_code.required' => 'Kode item wajib diisi.',
            'item_code.unique' => 'Kode item sudah digunakan',
            'item_name.required' => 'Nama item wajib diisi',
        ]);

        DB::table('items')->insert([
            'item_code' => $normalizedCode,
            'item_name' => trim((string) $v['item_name']),
            'unit_id' => $v['unit_id'] ?? null,
            'category' => $v['category'] ?? null,
            'specification' => $v['specification'] ?? null,
            'active' => 1,
            'updated_at' => now(),
            'created_at' => now(),
        ]);

        return back()->with('success', 'Item berhasil ditambahkan.');
    }

    public function edit(string $id): View
    {
        $item = DB::table('items')->where('id', $id)->firstOrFail();
        $units = DB::table('units')->orderBy('unit_name')->get();

        return view('masters.items.edit', compact('item', 'units'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $item = DB::table('items')->where('id', $id)->firstOrFail();

        $normalizedCode = strtoupper(trim((string) $request->input('item_code')));
        $request->merge(['item_code' => $normalizedCode]);

        $v = $request->validate([
            'item_code' => ['required', 'string', 'max:50', Rule::unique('items', 'item_code')->ignore($item->id)],
            'item_name' => 'required|string|max:255',
            'unit_id' => 'nullable|integer|exists:units,id',
            'category' => 'nullable|string|max:100',
            'specification' => 'nullable|string|max:500',
        ], [
            'item_code.required' => 'Kode item wajib diisi.',
            'item_code.unique' => 'Kode item sudah digunakan',
            'item_name.required' => 'Nama item wajib diisi',
        ]);

        DB::table('items')->where('id', $id)->update([
            'item_code' => $normalizedCode,
            'item_name' => trim((string) $v['item_name']),
            'unit_id' => $v['unit_id'] ?? null,
            'category' => $v['category'] ?? null,
            'specification' => $v['specification'] ?? null,
            'updated_at' => now(),
        ]);

        return redirect()->route('items.index')->with('success', 'Item berhasil diperbarui.');
    }

    public function toggleStatus(string $id): RedirectResponse
    {
        $item = DB::table('items')->where('id', $id)->firstOrFail();

        DB::table('items')->where('id', $id)->update([
            'active' => ! (bool) $item->active,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Status item berhasil diperbarui.');
    }
}
