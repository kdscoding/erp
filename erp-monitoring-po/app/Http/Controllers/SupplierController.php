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
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $normalizedCode = strtoupper(trim((string) $request->input('supplier_code')));
        $request->merge(['supplier_code' => $normalizedCode]);

        $validated = $request->validate([
            'supplier_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('suppliers', 'supplier_code'),
            ],
            'supplier_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
        ], [
            'supplier_code.required' => 'Kode supplier wajib diisi.',
            'supplier_code.unique' => 'Kode supplier sudah digunakan',
            'supplier_name.required' => 'Nama supplier wajib diisi',
        ]);

        DB::table('suppliers')->insert([
            'supplier_code' => $normalizedCode,
            'supplier_name' => trim((string) $validated['supplier_name']),
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'contact_person' => $validated['contact_person'] ?? null,
            'address' => $validated['address'] ?? null,
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil ditambahkan.');
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

        $validated = $request->validate([
            'supplier_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('suppliers', 'supplier_code')->ignore($supplier->id),
            ],
            'supplier_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
        ], [
            'supplier_code.required' => 'Kode supplier wajib diisi.',
            'supplier_code.unique' => 'Kode supplier sudah digunakan',
            'supplier_name.required' => 'Nama supplier wajib diisi',
        ]);

        DB::table('suppliers')->where('id', $id)->update([
            'supplier_code' => $normalizedCode,
            'supplier_name' => trim((string) $validated['supplier_name']),
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'contact_person' => $validated['contact_person'] ?? null,
            'address' => $validated['address'] ?? null,
            'updated_at' => now(),
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil diperbarui.');
    }

    public function toggleStatus(string $id): RedirectResponse
    {
        $supplier = DB::table('suppliers')->where('id', $id)->firstOrFail();

        DB::table('suppliers')->where('id', $id)->update([
            'status' => ! (bool) $supplier->status,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Status supplier berhasil diperbarui.');
    }
}
