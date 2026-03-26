<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class ShipmentDraftExport implements WithMultipleSheets
{
    public function __construct(
        private readonly object $shipment,
        private readonly Collection $lines,
    ) {}

    public function sheets(): array
    {
        return [
            new ShipmentDraftHeaderSheet($this->shipment),
            new ShipmentDraftLinesSheet($this->lines),
        ];
    }
}

class ShipmentDraftHeaderSheet implements FromArray, WithTitle
{
    public function __construct(
        private readonly object $shipment
    ) {}

    public function title(): string
    {
        return 'HEADER';
    }

    public function array(): array
    {
        return [
            [
                'shipment_number',
                'shipment_date',
                'supplier_name',
                'delivery_note_number',
                'invoice_number',
                'invoice_date',
                'invoice_currency',
                'supplier_remark',
                'status',
            ],
            [
                $this->shipment->shipment_number ?? '',
                $this->shipment->shipment_date ?? '',
                $this->shipment->supplier_name ?? '',
                $this->shipment->delivery_note_number ?? '',
                $this->shipment->invoice_number ?? '',
                $this->shipment->invoice_date ?? '',
                $this->shipment->invoice_currency ?? '',
                $this->shipment->supplier_remark ?? '',
                $this->shipment->status ?? '',
            ],
        ];
    }
}

class ShipmentDraftLinesSheet implements FromArray, WithTitle
{
    public function __construct(
        private readonly Collection $lines
    ) {}

    public function title(): string
    {
        return 'LINES';
    }

    public function array(): array
    {
        $rows = [[
            'shipment_item_id',
            'purchase_order_item_id',
            'po_number',
            'item_code',
            'item_name',
            'po_unit_price',
            'shipped_qty',
            'invoice_unit_price',
            'invoice_line_total',
            'keep',
        ]];

        foreach ($this->lines as $line) {
            $rows[] = [
                $line->shipment_item_id ?? '',
                $line->purchase_order_item_id ?? '',
                $line->po_number ?? '',
                $line->item_code ?? '',
                $line->item_name ?? '',
                $line->po_unit_price ?? '',
                $line->shipped_qty ?? '',
                $line->invoice_unit_price ?? '',
                $line->invoice_line_total ?? '',
                1,
            ];
        }

        return $rows;
    }
}
