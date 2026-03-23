<?php

namespace App\Http\Controllers;

use App\Support\TermCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SettingsController extends Controller
{
    public function index()
    {
        $allowOver = DB::table('settings')->where('key', 'allow_over_receipt')->value('value') ?? '0';
        $documentTermGroups = TermCatalog::groupedTerms();

        $hasBadgeColumns = Schema::hasTable('document_terms')
            && Schema::hasColumn('document_terms', 'badge_class')
            && Schema::hasColumn('document_terms', 'badge_text');

        return view('settings.index', compact('allowOver', 'documentTermGroups', 'hasBadgeColumns'));
    }

    public function update(Request $request)
    {
        DB::table('settings')->updateOrInsert(
            ['key' => 'allow_over_receipt'],
            [
                'value' => $request->boolean('allow_over_receipt') ? '1' : '0',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return back()->with('success', 'Settings berhasil disimpan.');
    }

    public function updateDocumentTerms(Request $request)
    {
        $hasBadgeColumns = Schema::hasTable('document_terms')
            && Schema::hasColumn('document_terms', 'badge_class')
            && Schema::hasColumn('document_terms', 'badge_text');

        $rules = [
            'document_terms' => 'required|array|min:1',
            'document_terms.*.label' => 'required|string|max:150',
            'document_terms.*.description' => 'nullable|string|max:1000',
            'document_terms.*.sort_order' => 'required|integer|min:0|max:9999',
        ];

        if ($hasBadgeColumns) {
            $rules['document_terms.*.badge_class'] = 'nullable|string|max:120';
            $rules['document_terms.*.badge_text'] = 'nullable|string|max:120';
        }

        $validated = $request->validate($rules);

        $termRows = DB::table('document_terms')->get(['id'])->keyBy('id');

        foreach ($validated['document_terms'] as $termId => $termData) {
            $termId = (int) $termId;

            if (! $termRows->has($termId)) {
                continue;
            }

            $payload = [
                'label' => $termData['label'],
                'description' => $termData['description'] ?? null,
                'sort_order' => (int) $termData['sort_order'],
                'is_active' => $request->has("document_terms.{$termId}.is_active"),
                'updated_at' => now(),
            ];

            if ($hasBadgeColumns) {
                $payload['badge_class'] = $termData['badge_class'] ?? null;
                $payload['badge_text'] = $termData['badge_text'] ?? null;
            }

            DB::table('document_terms')->where('id', $termId)->update($payload);
        }

        return back()->with('success', 'Document terms berhasil diperbarui.');
    }
}
