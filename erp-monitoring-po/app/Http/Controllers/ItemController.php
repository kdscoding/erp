<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function index(Request $request): View
    {
        $hasCategoryTable = Schema::hasTable('item_categories');
        $hasCategoryId = Schema::hasColumn('items', 'category_id');
        $hasLegacyCategory = Schema::hasColumn('items', 'category');

        $rows = DB::table('items as i')
            ->leftJoin('units as u', 'u.id', '=', 'i.unit_id')
            ->when($hasCategoryTable && $hasCategoryId, fn ($query) => $query->leftJoin('item_categories as c', 'c.id', '=', 'i.category_id'))
            ->select('i.*', 'u.unit_name')
            ->when($hasCategoryTable && $hasCategoryId, fn ($query) => $query->addSelect('c.category_name', 'c.category_code'))
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = trim((string) $request->input('q'));
                $hasCategoryTable = Schema::hasTable('item_categories');
                $hasCategoryId = Schema::hasColumn('items', 'category_id');
                $hasLegacyCategory = Schema::hasColumn('items', 'category');

                $query->where(function ($qBuilder) use ($q, $hasCategoryTable, $hasCategoryId, $hasLegacyCategory) {
                    $qBuilder->where('i.item_code', 'like', "%{$q}%")
                        ->orWhere('i.item_name', 'like', "%{$q}%")
                        ->orWhere('i.specification', 'like', "%{$q}%");

                    if ($hasCategoryTable && $hasCategoryId) {
                        $qBuilder->orWhere('c.category_name', 'like', "%{$q}%");
                    } elseif ($hasLegacyCategory) {
                        $qBuilder->orWhere('i.category', 'like', "%{$q}%");
                    }
                });
            })
            ->when($hasCategoryTable && $hasCategoryId && $request->filled('category_id'), fn ($query) => $query->where('i.category_id', (int) $request->input('category_id')))
            ->when($request->filled('unit_id'), fn ($query) => $query->where('i.unit_id', (int) $request->input('unit_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('i.active', (int) $request->input('status')))
            ->orderBy('i.item_name')
            ->paginate(20)
            ->withQueryString();

        $units = DB::table('units')->orderBy('unit_name')->get();
        $categories = $hasCategoryTable ? DB::table('item_categories')->orderBy('category_name')->get() : collect();

        $stats = [
            'total' => DB::table('items')->count(),
            'active' => DB::table('items')->where('active', true)->count(),
            'inactive' => DB::table('items')->where('active', false)->count(),
            'categorized' => $hasCategoryId
                ? DB::table('items')->whereNotNull('category_id')->count()
                : ($hasLegacyCategory ? DB::table('items')->whereNotNull('category')->where('category', '!=', '')->count() : 0),
        ];

        $supportsCategoryMaster = $hasCategoryTable && $hasCategoryId;

        return view('masters.items.index', compact('rows', 'units', 'categories', 'stats', 'supportsCategoryMaster'));
    }

    public function store(Request $request): RedirectResponse
    {
        $normalizedCode = strtoupper(trim((string) $request->input('item_code')));
        $request->merge(['item_code' => $normalizedCode]);

        $rules = [
            'item_code' => ['required', 'string', 'max:50', Rule::unique('items', 'item_code')],
            'item_name' => 'required|string|max:255',
            'unit_id' => 'nullable|integer|exists:units,id',
            'specification' => 'nullable|string|max:500',
        ];
        if (Schema::hasTable('item_categories') && Schema::hasColumn('items', 'category_id')) {
            $rules['category_id'] = 'nullable|integer|exists:item_categories,id';
        } elseif (Schema::hasColumn('items', 'category')) {
            $rules['category'] = 'nullable|string|max:100';
        }

        $v = $request->validate($rules, [
            'item_code.required' => 'Kode item wajib diisi.',
            'item_code.unique' => 'Kode item sudah digunakan',
            'item_name.required' => 'Nama item wajib diisi',
        ]);

        $payload = [
            'item_code' => $normalizedCode,
            'item_name' => trim((string) $v['item_name']),
            'unit_id' => $v['unit_id'] ?? null,
            'specification' => $v['specification'] ?? null,
            'active' => 1,
            'updated_at' => now(),
            'created_at' => now(),
        ];

        if (Schema::hasTable('item_categories') && Schema::hasColumn('items', 'category_id')) {
            $payload['category_id'] = $v['category_id'] ?? null;
            if (Schema::hasColumn('items', 'category')) {
                $payload['category'] = ! empty($v['category_id'])
                    ? DB::table('item_categories')->where('id', $v['category_id'])->value('category_name')
                    : null;
            }
        } elseif (Schema::hasColumn('items', 'category')) {
            $payload['category'] = $v['category'] ?? null;
        }

        DB::table('items')->insert($payload);

        return back()->with('success', 'Item berhasil ditambahkan.');
    }

    public function edit(string $id): View
    {
        $item = DB::table('items')->where('id', $id)->firstOrFail();
        $units = DB::table('units')->orderBy('unit_name')->get();
        $categories = Schema::hasTable('item_categories') ? DB::table('item_categories')->orderBy('category_name')->get() : collect();
        $supportsCategoryMaster = Schema::hasTable('item_categories') && Schema::hasColumn('items', 'category_id');

        return view('masters.items.edit', compact('item', 'units', 'categories', 'supportsCategoryMaster'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $item = DB::table('items')->where('id', $id)->firstOrFail();

        $normalizedCode = strtoupper(trim((string) $request->input('item_code')));
        $request->merge(['item_code' => $normalizedCode]);

        $rules = [
            'item_code' => ['required', 'string', 'max:50', Rule::unique('items', 'item_code')->ignore($item->id)],
            'item_name' => 'required|string|max:255',
            'unit_id' => 'nullable|integer|exists:units,id',
            'specification' => 'nullable|string|max:500',
        ];
        if (Schema::hasTable('item_categories') && Schema::hasColumn('items', 'category_id')) {
            $rules['category_id'] = 'nullable|integer|exists:item_categories,id';
        } elseif (Schema::hasColumn('items', 'category')) {
            $rules['category'] = 'nullable|string|max:100';
        }

        $v = $request->validate($rules, [
            'item_code.required' => 'Kode item wajib diisi.',
            'item_code.unique' => 'Kode item sudah digunakan',
            'item_name.required' => 'Nama item wajib diisi',
        ]);

        $payload = [
            'item_code' => $normalizedCode,
            'item_name' => trim((string) $v['item_name']),
            'unit_id' => $v['unit_id'] ?? null,
            'specification' => $v['specification'] ?? null,
            'updated_at' => now(),
        ];

        if (Schema::hasTable('item_categories') && Schema::hasColumn('items', 'category_id')) {
            $payload['category_id'] = $v['category_id'] ?? null;
            if (Schema::hasColumn('items', 'category')) {
                $payload['category'] = ! empty($v['category_id'])
                    ? DB::table('item_categories')->where('id', $v['category_id'])->value('category_name')
                    : null;
            }
        } elseif (Schema::hasColumn('items', 'category')) {
            $payload['category'] = $v['category'] ?? null;
        }

        DB::table('items')->where('id', $id)->update($payload);

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
