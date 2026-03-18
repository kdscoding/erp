<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function index()
    {
        $allowOver = DB::table('settings')->where('key', 'allow_over_receipt')->value('value') ?? '0';
        return view('settings.index', compact('allowOver'));
    }

    public function update(Request $request)
    {
        DB::table('settings')->updateOrInsert(
            ['key' => 'allow_over_receipt'],
            ['value' => $request->boolean('allow_over_receipt') ? '1' : '0', 'updated_at' => now(), 'created_at' => now()]
        );

        return back()->with('success', 'Settings berhasil disimpan.');
    }
}
