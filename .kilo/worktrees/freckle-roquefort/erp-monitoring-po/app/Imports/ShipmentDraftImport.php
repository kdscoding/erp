<?php

namespace App\Imports;

use App\Support\DocumentTermCodes;
use App\Support\ErpFlow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ShipmentDraftImport implements WithMultipleSheets
{
    public array $headerRows = [];
    public array $lineRows = [];

    public function __construct(
        private readonly int $shipmentId
    ) {}

    public function sheets(): array
    {
        return [
            'HEADER' => new class($this) implements ToCollection {
                public function __construct(
                    private readonly ShipmentDraftImport $parent
                ) {}

                public function collection(Collection $rows): void
                {
                    $this->parent->headerRows = $rows->map(fn($row) => $row->toArray())->all();
                }
            },
            'LINES' => new class($this) implements ToCollection {
                public function __construct(
                    private readonly ShipmentDraftImport $parent
                ) {}

                public function collection(Collection $rows): void
                {
                    $this->parent->lineRows = $rows->map(fn($row) => $row->toArray())->all();
                }
            },
        ];
    }

    public function apply(): void
    {
        DB::transaction(function () {
            $shipment = DB::table('shipments')->where('id', $this->shipmentId)->lockForUpdate()->firstOrFail();

            if ($shipment->status !== DocumentTermCodes::SHIPMENT_DRAFT) {
                throw ValidationException::withMessages([
                    'file' => 'Import hanya diperbolehkan untuk shipment Draft.',
                ]);
            }

            $header = $this->extractAssocRow($this->headerRows);
            $lines = $this->extractAssocRows($this->lineRows);

            if (!empty($header['shipment_number']) && trim((string) $header['shipment_number']) !== trim((string) $shipment->shipment_number)) {
                throw ValidationException::withMessages([
                    'file' => 'Shipment number pada file tidak sesuai dengan draft target.',
                ]);
            }

            $existingSupplierName = (string) (
                DB::table('shipments as sh')
                ->leftJoin('suppliers as s', 's.id', '=', 'sh.supplier_id')
                ->where('sh.id', $shipment->id)
                ->value('s.supplier_name') ?? ''
            );

            if (!empty($header['supplier_name']) && trim((string) $header['supplier_name']) !== trim($existingSupplierName)) {
                throw ValidationException::withMessages([
                    'file' => 'Supplier pada file tidak sesuai dengan shipment draft.',
                ]);
            }

            $deliveryNote = $this->nullableString($header['delivery_note_number'] ?? null);
            $invoiceNumber = $this->nullableString($header['invoice_number'] ?? null);
            $invoiceCurrency = $this->nullableString($header['invoice_currency'] ?? null);
            $supplierRemark = $this->nullableString($header['supplier_remark'] ?? null);
            $shipmentDate = $this->nullableString($header['shipment_date'] ?? null);
            $invoiceDate = $this->nullableString($header['invoice_date'] ?? null);

            if (!$shipmentDate) {
                $shipmentDate = (string) $shipment->shipment_date;
            }

            if (!$deliveryNote) {
                $deliveryNote = (string) $shipment->delivery_note_number;
            }

            if (!$deliveryNote) {
                throw ValidationException::withMessages([
                    'file' => 'delivery_note_number wajib ada pada sheet HEADER.',
                ]);
            }

            $duplicateShipment = DB::table('shipments')
                ->where('supplier_id', $shipment->supplier_id)
                ->where('id', '!=', $shipment->id)
                ->whereRaw('LOWER(TRIM(delivery_note_number)) = ?', [mb_strtolower($deliveryNote)])
                ->where('status', '!=', DocumentTermCodes::SHIPMENT_CANCELLED)
                ->lockForUpdate()
                ->first();

            if ($duplicateShipment) {
                throw ValidationException::withMessages([
                    'file' => "Delivery note {$deliveryNote} sudah dipakai oleh shipment {$duplicateShipment->shipment_number}.",
                ]);
            }

            if ($invoiceNumber) {
                $duplicateInvoice = DB::table('shipments')
                    ->where('supplier_id', $shipment->supplier_id)
                    ->where('id', '!=', $shipment->id)
                    ->whereRaw('LOWER(TRIM(invoice_number)) = ?', [mb_strtolower($invoiceNumber)])
                    ->where('status', '!=', DocumentTermCodes::SHIPMENT_CANCELLED)
                    ->lockForUpdate()
                    ->first();

                if ($duplicateInvoice) {
                    throw ValidationException::withMessages([
                        'file' => "Invoice {$invoiceNumber} sudah dipakai oleh shipment {$duplicateInvoice->shipment_number}.",
                    ]);
                }
            }

            DB::table('shipments')->where('id', $shipment->id)->update([
                'shipment_date' => $shipmentDate,
                'delivery_note_number' => $deliveryNote,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $invoiceDate,
                'invoice_currency' => $invoiceCurrency,
                'supplier_remark' => $supplierRemark,
                'updated_at' => now(),
            ]);

            if (empty($lines)) {
                throw ValidationException::withMessages([
                    'file' => 'Sheet LINES tidak boleh kosong.',
                ]);
            }

            $validLines = collect($lines)
                ->filter(fn($row) => (string) ($row['keep'] ?? '1') === '1')
                ->values();

            if ($validLines->isEmpty()) {
                throw ValidationException::withMessages([
                    'file' => 'Minimal satu line harus keep=1.',
                ]);
            }

            $currentLines = DB::table('shipment_items as si')
                ->join('purchase_order_items as poi', 'poi.id', '=', 'si.purchase_order_item_id')
                ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
                ->join('items as i', 'i.id', '=', 'poi.item_id')
                ->leftJoin('shipment_items as other_si', function ($join) use ($shipment) {
                    $join->on('other_si.purchase_order_item_id', '=', 'poi.id')
                        ->where('other_si.shipment_id', '!=', $shipment->id);
                })
                ->leftJoin('shipments as other_sh', function ($join) {
                    $join->on('other_sh.id', '=', 'other_si.shipment_id')
                        ->where('other_sh.status', '!=', DocumentTermCodes::SHIPMENT_CANCELLED);
                })
                ->where('si.shipment_id', $shipment->id)
                ->select(
                    'si.id as shipment_item_id',
                    'si.shipment_id',
                    'si.purchase_order_item_id',
                    'si.shipped_qty as current_shipped_qty',
                    'si.received_qty',
                    'poi.purchase_order_id',
                    'poi.outstanding_qty',
                    'poi.unit_price as po_unit_price',
                    'i.item_code'
                )
                ->selectRaw('COALESCE(SUM(CASE WHEN other_sh.id IS NOT NULL THEN other_si.shipped_qty - other_si.received_qty ELSE 0 END), 0) as other_open_shipment_qty')
                ->groupBy(
                    'si.id',
                    'si.shipment_id',
                    'si.purchase_order_item_id',
                    'si.shipped_qty',
                    'si.received_qty',
                    'poi.purchase_order_id',
                    'poi.outstanding_qty',
                    'poi.unit_price',
                    'i.item_code'
                )
                ->get()
                ->keyBy('shipment_item_id');

            $keptIds = [];

            foreach ($validLines as $row) {
                $shipmentItemId = (int) ($row['shipment_item_id'] ?? 0);

                if ($shipmentItemId <= 0) {
                    throw ValidationException::withMessages([
                        'file' => 'Ada shipment_item_id yang kosong atau tidak valid di sheet LINES.',
                    ]);
                }

                $currentLine = $currentLines->get($shipmentItemId);

                if (!$currentLine) {
                    throw ValidationException::withMessages([
                        'file' => 'Ada shipment_item_id yang tidak cocok dengan draft target: ' . $shipmentItemId,
                    ]);
                }

                $qty = (float) ($row['shipped_qty'] ?? 0);
                $invoiceUnitPrice = $this->nullableNumber($row['invoice_unit_price'] ?? null);

                if ($qty <= 0) {
                    throw ValidationException::withMessages([
                        'file' => "Qty kirim untuk shipment_item_id {$shipmentItemId} harus lebih besar dari 0.",
                    ]);
                }

                $availableToShipQty = (float) $currentLine->outstanding_qty - (float) $currentLine->other_open_shipment_qty;

                if ($qty > $availableToShipQty) {
                    throw ValidationException::withMessages([
                        'file' => "Qty kirim untuk item {$currentLine->item_code} melebihi qty tersedia.",
                    ]);
                }

                if ($invoiceUnitPrice !== null && $invoiceUnitPrice < 0) {
                    throw ValidationException::withMessages([
                        'file' => "Harga invoice untuk item {$currentLine->item_code} tidak boleh negatif.",
                    ]);
                }

                $invoiceLineTotal = $invoiceUnitPrice !== null
                    ? round($qty * $invoiceUnitPrice, 2)
                    : null;

                DB::table('shipment_items')
                    ->where('id', $shipmentItemId)
                    ->update([
                        'shipped_qty' => $qty,
                        'invoice_unit_price' => $invoiceUnitPrice,
                        'invoice_line_total' => $invoiceLineTotal,
                        'updated_at' => now(),
                    ]);

                $keptIds[] = $shipmentItemId;
            }

            DB::table('shipment_items')
                ->where('shipment_id', $shipment->id)
                ->whereNotIn('id', $keptIds)
                ->delete();

            $poIds = DB::table('shipment_items as si')
                ->join('purchase_order_items as poi', 'poi.id', '=', 'si.purchase_order_item_id')
                ->where('si.shipment_id', $shipment->id)
                ->pluck('poi.purchase_order_id')
                ->map(fn($poId) => (int) $poId)
                ->unique()
                ->values();

            foreach ($poIds as $poId) {
                ErpFlow::refreshPoStatusByOutstanding((int) $poId, null);
            }

            ErpFlow::audit(
                'shipments',
                (int) $shipment->id,
                'import_excel',
                $shipment,
                [
                    'header' => $header,
                    'lines' => $validLines->values()->all(),
                ],
                null,
                null
            );
        });
    }

    private function extractAssocRow(array $rows): array
    {
        $rows = array_values(array_filter($rows, fn($row) => is_array($row) && count($row) > 0));

        if (count($rows) < 2) {
            throw ValidationException::withMessages([
                'file' => 'Sheet HEADER tidak valid. Minimal harus ada 2 baris: header dan value.',
            ]);
        }

        $header = array_map(fn($value) => trim((string) $value), $rows[0]);
        $values = array_pad($rows[1], count($header), null);

        return array_combine($header, $values);
    }

    private function extractAssocRows(array $rows): array
    {
        $rows = array_values(array_filter($rows, fn($row) => is_array($row) && count($row) > 0));

        if (count($rows) < 2) {
            throw ValidationException::withMessages([
                'file' => 'Sheet LINES tidak valid. Minimal harus ada 2 baris: header dan data.',
            ]);
        }

        $header = array_map(fn($value) => trim((string) $value), $rows[0]);
        $dataRows = [];

        foreach (array_slice($rows, 1) as $row) {
            $padded = array_pad($row, count($header), null);

            if (!array_filter($padded, fn($value) => $value !== null && $value !== '')) {
                continue;
            }

            $dataRows[] = array_combine($header, $padded);
        }

        return $dataRows;
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private function nullableNumber(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }
}
