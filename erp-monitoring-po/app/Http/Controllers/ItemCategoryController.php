<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ItemCategoryController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(Schema::hasTable('item_categories'), 503, 'Tabel item_categories belum tersedia. Jalankan php artisan migrate terlebih dahulu.');

        $rows = DB::table('item_categories as c')
            ->leftJoin('items as i', 'i.category_id', '=', 'c.id')
            ->select('c.*', DB::raw('count(i.id) as item_count'))
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = trim((string) $request->input('q'));
                $query->where(function ($builder) use ($q) {
                    $builder->where('c.category_code', 'like', "%{$q}%")
                        ->orWhere('c.category_name', 'like', "%{$q}%")
                        ->orWhere('c.description', 'like', "%{$q}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('c.is_active', (int) $request->input('status')))
            ->groupBy('c.id', 'c.category_code', 'c.category_name', 'c.description', 'c.is_active', 'c.created_at', 'c.updated_at')
            ->orderBy('c.category_name')
            ->paginate(15)
            ->withQueryString();

        return view('masters.item-categories.index', compact('rows'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(Schema::hasTable('item_categories'), 503, 'Tabel item_categories belum tersedia. Jalankan php artisan migrate terlebih dahulu.');

        $normalizedCode = strtoupper(trim((string) $request->input('category_code')));
        $request->merge(['category_code' => $normalizedCode]);

        $data = $request->validate([
            'category_code' => ['required', 'string', 'max:50', Rule::unique('item_categories', 'category_code')],
            'category_name' => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
        ]);

        DB::table('item_categories')->insert([
            'category_code' => $normalizedCode,
            'category_name' => trim((string) $data['category_name']),
            'description' => $data['description'] ?? null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Kategori item berhasil ditambahkan.');
    }

    public function edit(string $id): View
    {
        abort_unless(Schema::hasTable('item_categories'), 503, 'Tabel item_categories belum tersedia. Jalankan php artisan migrate terlebih dahulu.');

        $category = DB::table('item_categories')->where('id', $id)->firstOrFail();

        return view('masters.item-categories.edit', compact('category'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        abort_unless(Schema::hasTable('item_categories'), 503, 'Tabel item_categories belum tersedia. Jalankan php artisan migrate terlebih dahulu.');

        $category = DB::table('item_categories')->where('id', $id)->firstOrFail();
        $normalizedCode = strtoupper(trim((string) $request->input('category_code')));
        $request->merge(['category_code' => $normalizedCode]);

        $data = $request->validate([
            'category_code' => ['required', 'string', 'max:50', Rule::unique('item_categories', 'category_code')->ignore($category->id)],
            'category_name' => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
        ]);

        DB::table('item_categories')->where('id', $id)->update([
            'category_code' => $normalizedCode,
            'category_name' => trim((string) $data['category_name']),
            'description' => $data['description'] ?? null,
            'updated_at' => now(),
        ]);

        return redirect()->route('item-categories.index')->with('success', 'Kategori item berhasil diperbarui.');
    }

    public function toggleStatus(string $id): RedirectResponse
    {
        abort_unless(Schema::hasTable('item_categories'), 503, 'Tabel item_categories belum tersedia. Jalankan php artisan migrate terlebih dahulu.');

        $category = DB::table('item_categories')->where('id', $id)->firstOrFail();

        DB::table('item_categories')->where('id', $id)->update([
            'is_active' => ! (bool) $category->is_active,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Status kategori item berhasil diperbarui.');
    }
}
