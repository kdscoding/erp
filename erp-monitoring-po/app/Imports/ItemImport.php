<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ItemImport implements ToCollection, WithHeadingRow
{
    public int $inserted = 0;

    public array $duplicates = [];

    public array $invalidRows = [];

    public function collection(Collection $rows): void
    {
        $hasCategoryTable = Schema::hasTable('item_categories') && Schema::hasColumn('items', 'category_id');
        $hasUnitTable = Schema::hasTable('units');

        $existingCodes = DB::table('items')
            ->pluck('item_code')
            ->map(fn ($c) => strtoupper(trim((string) $c)))
            ->toArray();

        $categoryCache = $hasCategoryTable
            ? DB::table('item_categories')->pluck('id', 'category_code')->toArray()
            : [];

        $unitCache = $hasUnitTable
            ? DB::table('units')->pluck('id', 'unit_code')->toArray()
            : [];

        DB::transaction(function () use ($rows, $existingCodes, $categoryCache, $unitCache, $hasCategoryTable, $hasUnitTable) {
            $seenInThisFile = [];

            foreach ($rows as $index => $row) {
                $rowNum = $index + 2;

                $itemCode = strtoupper(trim((string) ($row['item_code'] ?? '')));
                $itemName = trim((string) ($row['item_name'] ?? ''));

                if ($itemCode === '') {
                    $this->invalidRows[] = "Baris {$rowNum}: item_code wajib diisi.";

                    continue;
                }

                if (in_array($itemCode, $existingCodes, true) || in_array($itemCode, $seenInThisFile, true)) {
                    $this->duplicates[] = $itemCode;

                    continue;
                }

                $seenInThisFile[] = $itemCode;
                $existingCodes[] = $itemCode;

                $categoryCode = trim((string) ($row['category_code'] ?? ''));
                $unitCode = trim((string) ($row['unit_code'] ?? ''));
                $specification = trim((string) ($row['specification'] ?? '')) ?: null;

                $categoryId = null;
                if ($categoryCode !== '' && $hasCategoryTable) {
                    $categoryId = $categoryCache[$categoryCode] ?? null;
                    if ($categoryId === null) {
                        $this->invalidRows[] = "Baris {$rowNum}: category_code '{$categoryCode}' tidak ditemukan.";

                        continue;
                    }
                }

                $unitId = null;
                if ($unitCode !== '' && $hasUnitTable) {
                    $unitId = $unitCache[$unitCode] ?? null;
                    if ($unitId === null) {
                        $this->invalidRows[] = "Baris {$rowNum}: unit_code '{$unitCode}' tidak ditemukan.";

                        continue;
                    }
                }

                $payload = [
                    'item_code' => $itemCode,
                    'item_name' => $itemName,
                    'unit_id' => $unitId,
                    'specification' => $specification,
                    'active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if ($hasCategoryTable) {
                    $payload['category_id'] = $categoryId;
                }

                DB::table('items')->insert($payload);
                $this->inserted++;
            }
        });

        if (! empty($this->invalidRows) && $this->inserted === 0) {
            throw ValidationException::withMessages([
                'file' => implode(' ', $this->invalidRows),
            ]);
        }
    }

    public function headingRow(): int
    {
        return 1;
    }
}
