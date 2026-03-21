<?php

namespace App\Http\Controllers;

use App\Support\TermCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function index()
    {
        $allowOver = DB::table('settings')->where('key', 'allow_over_receipt')->value('value') ?? '0';
        $documentTermGroups = TermCatalog::groupedTerms();

        return view('settings.index', compact('allowOver', 'documentTermGroups'));
    }

    public function update(Request $request)
    {
        DB::table('settings')->updateOrInsert(
            ['key' => 'allow_over_receipt'],
            ['value' => $request->boolean('allow_over_receipt') ? '1' : '0', 'updated_at' => now(), 'created_at' => now()]
        );

        return back()->with('success', 'Settings berhasil disimpan.');
    }

    public function updateDocumentTerms(Request $request)
    {
        $validated = $request->validate([
            'document_terms' => 'required|array|min:1',
            'document_terms.*.label' => 'required|string|max:150',
            'document_terms.*.description' => 'nullable|string|max:1000',
            'document_terms.*.sort_order' => 'required|integer|min:0|max:9999',
        ]);

        $termRows = DB::table('document_terms')->get(['id'])->keyBy('id');

        foreach ($validated['document_terms'] as $termId => $termData) {
            $termId = (int) $termId;
            if (! $termRows->has($termId)) {
                continue;
            }

            DB::table('document_terms')->where('id', $termId)->update([
                'label' => $termData['label'],
                'description' => $termData['description'] ?? null,
                'sort_order' => (int) $termData['sort_order'],
                'is_active' => $request->has("document_terms.{$termId}.is_active"),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'Istilah document terms berhasil diperbarui.');
    }
}
