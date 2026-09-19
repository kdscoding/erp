<?php

namespace App\Imports;

use App\Support\DocumentTermCodes;
use App\Support\DomainStatus;
use App\Support\ErpFlow;
use App\Support\TermCatalog;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PurchaseOrderImport
{
    public int $inserted = 0;

    public array $errors = [];

    public function handle(UploadedFile $file): void
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, ['xlsx', 'xls', 'csv'])) {
            throw ValidationException::withMessages([
                'file' => 'Format file harus xlsx, xls, atau csv.',
            ]);
        }

        $rows = $this->readRows($file);

        if (empty($rows)) {
            throw ValidationException::withMessages([
                'file' => 'File tidak memiliki data.',
            ]);
        }

        $this->validateAndInsert($rows);

        if (! empty($this->errors)) {
            throw ValidationException::withMessages([
                'file' => implode(' ', $this->errors),
            ]);
        }
    }

    private function readRows(UploadedFile $file): array
    {
        $pathname = $file->getPathname();
        $spreadsheet = IOFactory::createReaderForFile($pathname)->load($pathname);
        $sheet = $spreadsheet->getSheet(0);

        $rawRows = $sheet->toArray();

        if (empty($rawRows)) {
            return [];
        }

        $header = array_map(fn ($value) => trim((string) $value), $rawRows[0]);
        $dataRows = [];

        foreach (array_slice($rawRows, 1) as $row) {
            $padded = array_pad($row, count($header), null);
            $combined = array_combine($header, $padded);

            if (! is_array($combined)) {
                continue;
            }

            $filtered = array_filter($combined, fn ($cell) => $cell !== null && $cell !== '');
            if (empty($filtered)) {
                continue;
            }

            $dataRows[] = $combined;
        }

        return $dataRows;
    }

    private function validateAndInsert(array $rows): void
    {
        $requiredCols = ['po_number', 'po_date', 'supplier_code', 'item_code', 'ordered_qty'];
        $allCols = ['po_number', 'po_date', 'supplier_code', 'currency', 'notes', 'item_code', 'ordered_qty', 'unit_price', 'etd_date', 'remarks'];

        foreach ($rows as $idx => $row) {
            $rowNum = $idx + 2;
            foreach ($requiredCols as $col) {
                if (! array_key_exists($col, $row)) {
                    $this->errors[] = "Baris {$rowNum}: kolom {$col} tidak ada.";
                }
            }
        }

        if (! empty($this->errors)) {
            throw ValidationException::withMessages([
                'file' => implode(' ', $this->errors),
            ]);
        }

        $itemByPo = [];
        foreach ($rows as $idx => $row) {
            $rowNum = $idx + 2;
            $poNumber = trim((string) ($row['po_number'] ?? ''));
            $itemCode = strtoupper(trim((string) ($row['item_code'] ?? '')));
            $orderedQty = trim((string) ($row['ordered_qty'] ?? ''));

            if ($itemCode === '') {
                $this->errors[] = "Baris {$rowNum}: item_code wajib diisi.";

                continue;
            }

            if ($poNumber !== '') {
                $itemKey = "{$itemCode}|{$orderedQty}";
                if (isset($itemByPo[$poNumber]) && in_array($itemKey, $itemByPo[$poNumber], true)) {
                    $this->errors[] = "Duplikasi item_code '{$itemCode}' dengan qty '{$orderedQty}' pada PO '{$poNumber}'.";

                    continue;
                }

                $itemByPo[$poNumber][] = $itemKey;
            }
        }

        if (! empty($this->errors)) {
            throw ValidationException::withMessages([
                'file' => implode(' ', $this->errors),
            ]);
        }

        $validatedRows = [];
        foreach ($rows as $idx => $row) {
            $rowNum = $idx + 2;
            $poNumber = trim((string) ($row['po_number'] ?? ''));
            $poDateVal = trim((string) ($row['po_date'] ?? ''));
            $supplierCode = strtoupper(trim((string) ($row['supplier_code'] ?? '')));
            $itemCode = strtoupper(trim((string) ($row['item_code'] ?? '')));
            $orderedQty = trim((string) ($row['ordered_qty'] ?? ''));
            $unitPrice = trim((string) ($row['unit_price'] ?? ''));
            $etdDateVal = trim((string) ($row['etd_date'] ?? ''));
            $currencyVal = trim((string) ($row['currency'] ?? '') ?: 'IDR');
            $notes = trim((string) ($row['notes'] ?? '')) ?: null;
            $remarks = trim((string) ($row['remarks'] ?? '')) ?: null;

            if ($poNumber === '') {
                $poNumber = ErpFlow::generateNumber('PO', 'purchase_orders', 'po_number');
            }

            if ($supplierCode === '') {
                $this->errors[] = "Baris {$rowNum}: supplier_code wajib diisi.";

                continue;
            }

            $supplier = DB::table('suppliers')
                ->whereRaw('LOWER(TRIM(supplier_code)) = ?', [mb_strtolower($supplierCode)])
                ->where('status', 1)
                ->first();

            if (! $supplier) {
                $this->errors[] = "Baris {$rowNum}: supplier_code '{$supplierCode}' tidak ditemukan atau tidak aktif.";

                continue;
            }

            if ($poDateVal === '') {
                $this->errors[] = "Baris {$rowNum}: po_date wajib diisi.";
            } elseif (! $this->isValidDate($poDateVal)) {
                $this->errors[] = "Baris {$rowNum}: po_date tidak valid.";
            } else {
                $poDateVal = Carbon::parse($poDateVal)->format('Y-m-d');
            }

            if ($currencyVal !== 'IDR') {
                $this->errors[] = "Baris {$rowNum}: currency hanya mendukung IDR.";
            }

            if ($orderedQty === '' || ! is_numeric($orderedQty) || (float) $orderedQty <= 0) {
                $this->errors[] = "Baris {$rowNum}: ordered_qty harus numerik dan lebih besar dari 0.";
            }

            if ($unitPrice !== '' && (is_numeric($unitPrice) === false || (float) $unitPrice < 0)) {
                $this->errors[] = "Baris {$rowNum}: unit_price tidak boleh negatif.";
            }

            if ($etdDateVal !== '' && ! $this->isValidDate($etdDateVal)) {
                $this->errors[] = "Baris {$rowNum}: etd_date tidak valid.";
            } elseif ($etdDateVal !== '') {
                $etdDateVal = Carbon::parse($etdDateVal)->format('Y-m-d');
            }

            $item = DB::table('items')
                ->whereRaw('LOWER(TRIM(item_code)) = ?', [mb_strtolower($itemCode)])
                ->where('active', 1)
                ->first();

            if (! $item) {
                $this->errors[] = "Baris {$rowNum}: item_code '{$itemCode}' tidak ditemukan atau tidak aktif.";

                continue;
            }

            $validatedRows[] = [
                'po_number' => $poNumber,
                'po_date' => $poDateVal,
                'supplier_code' => $supplierCode,
                'supplier_id' => (int) $supplier->id,
                'currency' => 'IDR',
                'notes' => $notes,
                'item_id' => (int) $item->id,
                'item_code' => $itemCode,
                'ordered_qty' => $orderedQty,
                'unit_price' => $unitPrice,
                'etd_date' => $etdDateVal,
                'remarks' => $remarks,
            ];
        }

        if (! empty($this->errors)) {
            throw ValidationException::withMessages([
                'file' => implode(' ', $this->errors),
            ]);
        }

        DB::transaction(function () use ($validatedRows) {
            $userId = auth()->id();
            $ip = request()->ip();
            $poCache = [];

            foreach ($validatedRows as $row) {
                $poNumber = $row['po_number'];

                if (! isset($poCache[$poNumber])) {
                    $poId = DB::table('purchase_orders')->insertGetId([
                        'po_number' => $poNumber,
                        'po_date' => $row['po_date'],
                        'supplier_id' => $row['supplier_id'],
                        'currency' => 'IDR',
                        'notes' => $row['notes'],
                        'created_by' => $userId,
                        'updated_by' => $userId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ] + DomainStatus::payload(DomainStatus::GROUP_PO_STATUS, 'status', DocumentTermCodes::PO_ISSUED));

                    $newEta = ErpFlow::resolvePoEtaDate((int) $poId);
                    DB::table('purchase_orders')
                        ->where('id', $poId)
                        ->update([
                            'eta_date' => $newEta,
                            'updated_at' => now(),
                            'updated_by' => $userId,
                        ]);

                    ErpFlow::pushPoStatus(
                        (int) $poId,
                        null,
                        DocumentTermCodes::PO_ISSUED,
                        $userId,
                        TermCatalog::label(
                            DocumentTermCodes::GROUP_PO_HISTORY_NOTE,
                            DocumentTermCodes::NOTE_RELEASED_NEW_PO,
                            'Released new PO'
                        )
                    );

                    ErpFlow::audit(
                        'purchase_orders',
                        (int) $poId,
                        'import',
                        null,
                        [
                            'status' => DocumentTermCodes::PO_ISSUED,
                            'po_number' => $poNumber,
                            'po_date' => $row['po_date'],
                            'supplier_id' => $row['supplier_id'],
                        ],
                        $userId,
                        $ip
                    );

                    $poCache[$poNumber] = $poId;
                }

                $poId = $poCache[$poNumber];
                $orderedQty = (float) $row['ordered_qty'];
                $unitPrice = $row['unit_price'] !== '' ? (float) $row['unit_price'] : null;
                $etdDate = $row['etd_date'] !== '' ? $row['etd_date'] : null;

                DB::table('purchase_order_items')->insert([
                    'purchase_order_id' => $poId,
                    'item_id' => (int) $row['item_id'],
                    'ordered_qty' => $orderedQty,
                    'received_qty' => 0,
                    'outstanding_qty' => $orderedQty,
                    'unit_price' => $unitPrice,
                    'etd_date' => $etdDate,
                    'remarks' => $row['remarks'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ] + DomainStatus::payload(DomainStatus::GROUP_PO_ITEM_STATUS, 'item_status', DocumentTermCodes::ITEM_WAITING));

                $this->inserted++;
            }
        });
    }

    private function isValidDate(string $value): bool
    {
        try {
            Carbon::parse($value);

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
