<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $suppliers = DB::table('suppliers')
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = trim((string) $request->input('q'));
                $query->where(function ($qBuilder) use ($q) {
                    $qBuilder->where('supplier_code', 'like', "%{$q}%")
                        ->orWhere('supplier_name', 'like', "%{$q}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', (int) $request->input('status')))
            ->orderBy('supplier_name')
            ->get();

        $stats = [
            'total' => DB::table('suppliers')->count(),
            'active' => DB::table('suppliers')->where('status', true)->count(),
            'inactive' => DB::table('suppliers')->where('status', false)->count(),
        ];

        return view('suppliers.index', compact('suppliers', 'stats'));
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $normalizedCode = strtoupper(trim((string) $request->input('supplier_code')));
        $request->merge(['supplier_code' => $normalizedCode]);

        $v = $request->validate([
            'supplier_code' => ['required', 'string', 'max:50', Rule::unique('suppliers', 'supplier_code')],
            'supplier_name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
        ], [
            'supplier_code.required' => 'Kode supplier wajib diisi.',
            'supplier_code.unique' => 'Kode supplier sudah digunakan',
            'supplier_name.required' => 'Nama supplier wajib diisi',
        ]);

        DB::table('suppliers')->insert([
            'supplier_code' => $normalizedCode,
            'supplier_name' => trim((string) $v['supplier_name']),
            'address' => $v['address'] ?? null,
            'phone' => $v['phone'] ?? null,
            'email' => $v['email'] ?? null,
            'contact_person' => $v['contact_person'] ?? null,
            'status' => 1,
            'updated_at' => now(),
            'created_at' => now(),
        ]);

        return back()->with('success', 'Supplier Successfully added.');
    }

    public function edit(string $id): View
    {
        $supplier = DB::table('suppliers')->where('id', $id)->firstOrFail();
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $supplier = DB::table('suppliers')->where('id', $id)->firstOrFail();

        $normalizedCode = strtoupper(trim((string) $request->input('supplier_code')));
        $request->merge(['supplier_code' => $normalizedCode]);

        $v = $request->validate([
            'supplier_code' => ['required', 'string', 'max:50', Rule::unique('suppliers', 'supplier_code')->ignore($supplier->id)],
            'supplier_name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
        ], [
            'supplier_code.required' => 'Kode supplier wajib diisi.',
            'supplier_code.unique' => 'Kode supplier sudah digunakan',
            'supplier_name.required' => 'Nama supplier wajib diisi',
        ]);

        DB::table('suppliers')->where('id', $id)->update([
            'supplier_code' => $normalizedCode,
            'supplier_name' => trim((string) $v['supplier_name']),
            'address' => $v['address'] ?? null,
            'phone' => $v['phone'] ?? null,
            'email' => $v['email'] ?? null,
            'contact_person' => $v['contact_person'] ?? null,
            'updated_at' => now(),
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Supplier Successfully updated.');
    }

    public function toggleStatus(string $id): RedirectResponse
    {
        $supplier = DB::table('suppliers')->where('id', $id)->firstOrFail();

        DB::table('suppliers')->where('id', $id)->update([
            'status' => ! (bool) $supplier->status,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Status supplier Successfully updated.');
    }
}