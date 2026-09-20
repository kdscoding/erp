<?php

namespace App\Imports;

use App\Support\DocumentTermCodes;
use App\Support\DomainStatus;
use App\Support\ErpFlow;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ShipmentDraftBulkImport
{
    public const COLUMNS = [
        'shipment_date',
        'supplier_code',
        'DN',
        'invoice_number',
        'invoice_date',
        'supplier_remark',
        'po_number',
        'item_code',
        'invoice_unit_price',
        'qty_pengiriman',
    ];

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

        $header = array_map(fn ($value) => trim((string) $value), array_keys($rows[0]));

        $missingCols = array_filter(self::COLUMNS, fn ($col) => ! in_array($col, $header, true));

        if (! empty($missingCols)) {
            throw ValidationException::withMessages([
                'file' => 'Kolom berikut wajib ada di header: '.implode(', ', $missingCols),
            ]);
        }

        $this->validateAndInsert($rows);

        if (! empty($this->errors)) {
            throw ValidationException::withMessages([
                'file' => $this->formatErrors(),
            ]);
        }
    }

    private function readRows(UploadedFile $file): array
    {
        $pathname = $file->getPathname();
        $spreadsheet = IOFactory::createReaderForFile($pathname)->load($pathname);
        $sheet = $spreadsheet->getSheet(0);

        $rawRows = $sheet->toArray(null, true, true, false);

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
        $userId = auth()->id();
        $ip = request()->ip();

        $validatedRows = [];
        $allocatedQtyByPoi = [];

        foreach ($rows as $idx => $row) {
            $rowNum = $idx + 2;

            $shipmentDate = trim((string) ($row['shipment_date'] ?? ''));
            $supplierCode = strtoupper(trim((string) ($row['supplier_code'] ?? '')));
            $deliveryNote = trim((string) ($row['DN'] ?? ''));
            $invoiceNumber = trim((string) ($row['invoice_number'] ?? ''));
            $invoiceDate = trim((string) ($row['invoice_date'] ?? ''));
            $supplierRemark = trim((string) ($row['supplier_remark'] ?? ''));
            $poNumber = trim((string) ($row['po_number'] ?? ''));
            $itemCode = strtoupper(trim((string) ($row['item_code'] ?? '')));
            $qty = trim((string) ($row['qty_pengiriman'] ?? ''));
            $invoiceUnitPrice = trim((string) ($row['invoice_unit_price'] ?? ''));

            if ($shipmentDate === '') {
                $this->addError($rowNum, 'shipment_date', 'shipment_date wajib diisi.');
            } elseif (! $this->isValidDate($shipmentDate)) {
                $this->addError($rowNum, 'shipment_date', 'shipment_date tidak valid.');
            }

            if ($invoiceDate !== '' && ! $this->isValidDate($invoiceDate)) {
                $this->addError($rowNum, 'invoice_date', 'invoice_date tidak valid.');
            }

            if ($supplierCode === '') {
                $this->addError($rowNum, 'supplier_code', 'supplier_code wajib diisi.');
            }

            if ($deliveryNote === '') {
                $this->addError($rowNum, 'DN', 'DN (delivery_note_number) wajib diisi.');
            }

            if ($poNumber === '') {
                $this->addError($rowNum, 'po_number', 'po_number wajib diisi.');
            }

            if ($itemCode === '') {
                $this->addError($rowNum, 'item_code', 'item_code wajib diisi.');
            }

            $qtyValid = ($qty !== '' && is_numeric($qty) && (float) $qty > 0);
            if (! $qtyValid) {
                $this->addError($rowNum, 'qty_pengiriman', 'qty_pengiriman harus numerik dan lebih besar dari 0.');
            }

            if ($invoiceUnitPrice !== '' && (is_numeric($invoiceUnitPrice) === false || (float) $invoiceUnitPrice < 0)) {
                $this->addError($rowNum, 'invoice_unit_price', 'invoice_unit_price tidak boleh negatif.');
            }

            if (! $qtyValid || ! $this->isValidDate($shipmentDate) || $supplierCode === '' || $poNumber === '' || $itemCode === '') {
                continue;
            }

            $supplier = DB::table('suppliers')
                ->whereRaw('LOWER(TRIM(supplier_code)) = ?', [mb_strtolower($supplierCode)])
                ->where('status', 1)
                ->first();

            if (! $supplier) {
                $this->addError($rowNum, 'supplier_code', "supplier_code '{$supplierCode}' tidak ditemukan atau tidak aktif.");

                continue;
            }

            $item = DB::table('items')
                ->whereRaw('LOWER(TRIM(item_code)) = ?', [mb_strtolower($itemCode)])
                ->where('active', 1)
                ->first();

            if (! $item) {
                $this->addError($rowNum, 'item_code', "item_code '{$itemCode}' tidak ditemukan atau tidak aktif.");

                continue;
            }

            $purchaseOrderItem = DB::table('purchase_order_items as poi')
                ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
                ->select(
                    'poi.id as poi_id',
                    'poi.purchase_order_id',
                    'poi.item_id',
                    'poi.outstanding_qty',
                    'poi.item_status',
                    'poi.unit_price',
                    'poi.ordered_qty',
                    'poi.received_qty'
                )
                ->whereRaw('LOWER(TRIM(po.po_number)) = ?', [mb_strtolower($poNumber)])
                ->where('poi.item_id', $item->id)
                ->where('po.supplier_id', $supplier->id)
                ->whereIn('po.status', [
                    DocumentTermCodes::PO_ISSUED,
                    DocumentTermCodes::PO_OPEN,
                    DocumentTermCodes::PO_LATE,
                ])
                ->where('poi.outstanding_qty', '>', 0)
                ->where('poi.item_status', '!=', DocumentTermCodes::ITEM_CANCELLED)
                ->first();

            if (! $purchaseOrderItem) {
                $this->addError($rowNum, 'po_number', "PO '{$poNumber}' dengan item '{$itemCode}' untuk supplier '{$supplierCode}' tidak ditemukan, tidak active, atau tidak dapat dikirim.");

                continue;
            }

            $poItemId = (int) $purchaseOrderItem->poi_id;
            $qtyValue = (float) $qty;
            $availableToShipQty = $this->computeAvailableToShipQty($poItemId);
            $allocatedInFile = $allocatedQtyByPoi[$poItemId] ?? 0.0;
            $effectiveAvailable = $availableToShipQty - $allocatedInFile;

            if ($qtyValue > $effectiveAvailable) {
                $this->addError($rowNum, 'qty_pengiriman', "qty_pengiriman {$qty} untuk item {$itemCode} melebihi sisa qty yang tersedia ({$effectiveAvailable}).");

                continue;
            }

            $allocatedQtyByPoi[$poItemId] = $allocatedInFile + $qtyValue;

            $validatedRows[] = [
                'row_number' => $rowNum,
                'shipment_date' => Carbon::parse($shipmentDate)->format('Y-m-d'),
                'supplier_code' => $supplierCode,
                'supplier_id' => (int) $supplier->id,
                'delivery_note_number' => $deliveryNote,
                'invoice_number' => $invoiceNumber !== '' ? $invoiceNumber : null,
                'invoice_date' => ($invoiceDate !== '' && $this->isValidDate($invoiceDate)) ? Carbon::parse($invoiceDate)->format('Y-m-d') : null,
                'supplier_remark' => $supplierRemark !== '' ? $supplierRemark : null,
                'po_number' => $poNumber,
                'item_code' => $itemCode,
                'item_id' => (int) $item->id,
                'purchase_order_item_id' => $poItemId,
                'purchase_order_id' => (int) $purchaseOrderItem->purchase_order_id,
                'invoice_unit_price' => $invoiceUnitPrice !== '' ? (float) $invoiceUnitPrice : null,
                'shipped_qty' => $qtyValue,
            ];
        }

        if (empty($validatedRows) && ! empty($this->errors)) {
            throw ValidationException::withMessages([
                'file' => $this->formatErrors(),
            ]);
        }

        if (empty($validatedRows)) {
            throw ValidationException::withMessages([
                'file' => 'Tidak ada baris data yang valid untuk diimport.',
            ]);
        }

        $groups = [];
        $groupKeys = [];
        $groupRowNumbers = [];

        foreach ($validatedRows as $row) {
            $groupKey = $this->buildGroupKey($row);

            if (! isset($groups[$groupKey])) {
                $groups[$groupKey] = [];
                $groupKeys[$groupKey] = [
                    'supplier_code' => $row['supplier_code'],
                    'supplier_id' => $row['supplier_id'],
                    'shipment_date' => $row['shipment_date'],
                    'delivery_note_number' => $row['delivery_note_number'],
                    'invoice_number' => $row['invoice_number'],
                    'invoice_date' => $row['invoice_date'],
                    'supplier_remark' => $row['supplier_remark'],
                ];
                $groupRowNumbers[$groupKey] = [];
            }

            $groups[$groupKey][] = $row;
            $groupRowNumbers[$groupKey][] = $row['row_number'];
        }

        foreach ($groups as $groupKey => $groupRows) {
            $poiIds = collect($groupRows)->pluck('purchase_order_item_id')->toArray();

            if (count($poiIds) !== count(array_unique($poiIds))) {
                $this->addError(
                    min($groupRowNumbers[$groupKey]),
                    'DN',
                    "Delivery note {$groupKeys[$groupKey]['delivery_note_number']}: ada PO item yang sama muncul lebih dari sekali dalam satu shipment."
                );
            }
        }

        $seenDnInvoice = [];
        foreach ($groupKeys as $groupKey => $groupHeader) {
            $lockedSupplierId = $groupHeader['supplier_id'];

            $duplicateShipment = DB::table('shipments')
                ->where('supplier_id', $lockedSupplierId)
                ->whereRaw('LOWER(TRIM(delivery_note_number)) = ?', [mb_strtolower($groupHeader['delivery_note_number'])])
                ->where('status', '!=', DocumentTermCodes::SHIPMENT_CANCELLED)
                ->exists();

            if ($duplicateShipment) {
                $this->addError(
                    min($groupRowNumbers[$groupKey]),
                    'DN',
                    "Delivery note {$groupHeader['delivery_note_number']} sudah dipakai oleh shipment lain untuk supplier ini."
                );
            }

            if ($groupHeader['invoice_number'] !== null) {
                $duplicateInvoice = DB::table('shipments')
                    ->where('supplier_id', $lockedSupplierId)
                    ->whereRaw('LOWER(TRIM(invoice_number)) = ?', [mb_strtolower($groupHeader['invoice_number'])])
                    ->where('status', '!=', DocumentTermCodes::SHIPMENT_CANCELLED)
                    ->exists();

                if ($duplicateInvoice) {
                    $this->addError(
                        min($groupRowNumbers[$groupKey]),
                        'invoice_number',
                        "Invoice {$groupHeader['invoice_number']} sudah dipakai oleh shipment lain untuk supplier ini."
                    );
                }
            }

            $dupInFileKey = $groupHeader['supplier_id'].'|'.mb_strtolower($groupHeader['delivery_note_number']);
            if (isset($seenDnInvoice[$dupInFileKey])) {
                $this->addError(
                    min($groupRowNumbers[$groupKey]),
                    'DN',
                    "Delivery note {$groupHeader['delivery_note_number']} muncul lebih dari sekali untuk supplier yang sama dalam file yang sama."
                );
            } else {
                $seenDnInvoice[$dupInFileKey] = true;
            }
        }

        if (! empty($this->errors)) {
            throw ValidationException::withMessages([
                'file' => $this->formatErrors(),
            ]);
        }

        DB::transaction(function () use ($groups, $groupKeys, $userId, $ip) {
            foreach ($groupKeys as $groupKey => $groupHeader) {
                $groupRows = $groups[$groupKey];

                do {
                    $shipmentNumber = (string) mt_rand(1000000000, 9999999999);
                } while (DB::table('shipments')->where('shipment_number', $shipmentNumber)->exists());

                $shipmentId = DB::table('shipments')->insertGetId([
                    'purchase_order_id' => $groupRows[0]['purchase_order_id'],
                    'supplier_id' => $groupHeader['supplier_id'],
                    'shipment_number' => $shipmentNumber,
                    'shipment_date' => $groupHeader['shipment_date'],
                    'delivery_note_number' => $groupHeader['delivery_note_number'],
                    'invoice_number' => $groupHeader['invoice_number'],
                    'invoice_date' => $groupHeader['invoice_date'],
                    'invoice_currency' => 'IDR',
                    'supplier_remark' => $groupHeader['supplier_remark'],
                    'created_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ] + DomainStatus::payload(DomainStatus::GROUP_SHIPMENT_STATUS, 'status', DocumentTermCodes::SHIPMENT_DRAFT));

                $lineRows = [];
                foreach ($groupRows as $line) {
                    $invoiceUnitPrice = $line['invoice_unit_price'];
                    $invoiceLineTotal = $invoiceUnitPrice !== null
                        ? round($line['shipped_qty'] * $invoiceUnitPrice, 2)
                        : null;

                    $lineRows[] = [
                        'shipment_id' => $shipmentId,
                        'purchase_order_item_id' => $line['purchase_order_item_id'],
                        'shipped_qty' => $line['shipped_qty'],
                        'received_qty' => 0,
                        'invoice_unit_price' => $invoiceUnitPrice,
                        'invoice_line_total' => $invoiceLineTotal,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                DB::table('shipment_items')->insert($lineRows);

                $poIds = collect($groupRows)
                    ->pluck('purchase_order_id')
                    ->map(fn ($poId) => (int) $poId)
                    ->unique()
                    ->values();

                foreach ($poIds as $poId) {
                    ErpFlow::refreshPoStatusByOutstanding((int) $poId, $userId);
                }

                ErpFlow::audit(
                    'shipments',
                    (int) $shipmentId,
                    'import_bulk_excel',
                    null,
                    [
                        'shipment' => [
                            'shipment_number' => $shipmentNumber,
                            'shipment_date' => $groupHeader['shipment_date'],
                            'supplier_id' => $groupHeader['supplier_id'],
                            'delivery_note_number' => $groupHeader['delivery_note_number'],
                            'invoice_number' => $groupHeader['invoice_number'],
                            'invoice_date' => $groupHeader['invoice_date'],
                            'supplier_remark' => $groupHeader['supplier_remark'],
                            'status' => DocumentTermCodes::SHIPMENT_DRAFT,
                            'lines' => collect($groupRows)->map(fn ($r) => [
                                'po_number' => $r['po_number'],
                                'item_code' => $r['item_code'],
                                'shipped_qty' => $r['shipped_qty'],
                                'invoice_unit_price' => $r['invoice_unit_price'],
                            ])->all(),
                        ],
                    ],
                    $userId,
                    $ip
                );

                $this->inserted++;
            }
        });
    }

    private function computeAvailableToShipQty(int $purchaseOrderItemId): float
    {
        $outstandingQty = (float) DB::table('purchase_order_items')
            ->where('id', $purchaseOrderItemId)
            ->value('outstanding_qty');

        $openShipmentQty = (float) DB::table('shipment_items as si')
            ->join('shipments as sh', 'sh.id', '=', 'si.shipment_id')
            ->where('si.purchase_order_item_id', $purchaseOrderItemId)
            ->where('sh.status', '!=', DocumentTermCodes::SHIPMENT_CANCELLED)
            ->sum(DB::raw('COALESCE(si.shipped_qty - si.received_qty, 0)'));

        return $outstandingQty - $openShipmentQty;
    }

    private function buildGroupKey(array $row): string
    {
        return $row['supplier_code'].'|'
            .$row['shipment_date'].'|'
            .$row['delivery_note_number'].'|'
            .($row['invoice_number'] ?? '');
    }

    private function addError(int $rowNum, string $field, string $message): void
    {
        $this->errors[] = [
            'row' => $rowNum,
            'field' => $field,
            'message' => $message,
        ];
    }

    private function formatErrors(): string
    {
        if (empty($this->errors)) {
            return 'Terjadi kesalahan tidak diketahui.';
        }

        $errorCount = count($this->errors);

        return "Import dibatalkan — ditemukan {$errorCount} error. Perbaiki error pada tabel di bawah dan coba lagi.";
    }

    private function normalizeForCompare(?string $value): string
    {
        return mb_strtolower(trim((string) $value));
    }

    private function isValidDate(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        if ($value instanceof \DateTimeInterface) {
            return true;
        }

        try {
            Carbon::parse((string) $value);

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
