<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = DB::table('suppliers')->orderByDesc('id')->paginate(20);
        return view('suppliers.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_code' => 'required',
            'supplier_name' => 'required',
        ], ['required' => ':attribute wajib diisi.']);

        DB::table('suppliers')->updateOrInsert([
            'supplier_code' => $validated['supplier_code'],
        ], [
            'supplier_name' => $validated['supplier_name'],
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'status' => true,
            'updated_at' => now(),
            'created_at' => now(),
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Supplier tersimpan.');
    }
}
