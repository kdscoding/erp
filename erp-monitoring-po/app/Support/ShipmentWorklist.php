<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShipmentWorklist
{
    public static function baseQuery(): Builder
    {
        return DB::table('shipments as sh')
            ->leftJoin('suppliers as s', 's.id', '=', 'sh.supplier_id')
            ->leftJoin('purchase_orders as anchor_po', 'anchor_po.id', '=', 'sh.purchase_order_id')
            ->leftJoin('suppliers as anchor_s', 'anchor_s.id', '=', 'anchor_po.supplier_id')
            ->leftJoin('shipment_items as si', 'si.shipment_id', '=', 'sh.id')
            ->leftJoin('purchase_order_items as poi', 'poi.id', '=', 'si.purchase_order_item_id')
            ->leftJoin('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->select(
                'sh.*',
                DB::raw('COALESCE(s.supplier_name, anchor_s.supplier_name) as supplier_name'),
                DB::raw('COUNT(DISTINCT si.id) as line_count'),
                DB::raw('COUNT(DISTINCT po.id) as po_count'),
                DB::raw("GROUP_CONCAT(DISTINCT po.po_number ORDER BY po.po_number SEPARATOR ', ') as po_numbers"),
                DB::raw('COALESCE(SUM(si.shipped_qty),0) as total_shipped_qty'),
                DB::raw('COALESCE(SUM(si.received_qty),0) as total_received_qty'),
                DB::raw('COALESCE(SUM(si.shipped_qty - si.received_qty),0) as total_open_qty')
            )
            ->groupBy(
                'sh.id',
                'sh.purchase_order_id',
                'sh.supplier_id',
                'sh.shipment_number',
                'sh.shipment_date',
                'sh.delivery_note_number',
                'sh.invoice_number',
                'sh.invoice_date',
                'sh.invoice_currency',
                'sh.supplier_remark',
                'sh.status',
                'sh.created_by',
                'sh.created_at',
                'sh.updated_at',
                's.supplier_name',
                'anchor_s.supplier_name'
            );
    }

    public static function applyFilters(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->filled('supplier_id'), fn ($q) => $q->where('sh.supplier_id', $request->integer('supplier_id')))
            ->when($request->filled('delivery_note_number'), fn ($q) => $q->where('sh.delivery_note_number', 'like', '%'.$request->string('delivery_note_number').'%'))
            ->when($request->filled('invoice_number'), fn ($q) => $q->where('sh.invoice_number', 'like', '%'.$request->string('invoice_number').'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('sh.status', $request->string('status')))
            ->when($request->filled('keyword'), function ($q) use ($request) {
                $keyword = '%'.$request->string('keyword').'%';
                $q->where(function ($inner) use ($keyword) {
                    $inner->where('sh.shipment_number', 'like', $keyword)
                        ->orWhere('sh.delivery_note_number', 'like', $keyword)
                        ->orWhere('sh.invoice_number', 'like', $keyword)
                        ->orWhere('s.supplier_name', 'like', $keyword)
                        ->orWhere('anchor_s.supplier_name', 'like', $keyword)
                        ->orWhere('po.po_number', 'like', $keyword);
                });
            });
    }

    public static function activeStatuses(): array
    {
        return [
            DocumentTermCodes::SHIPMENT_DRAFT,
            DocumentTermCodes::SHIPMENT_SHIPPED,
            DocumentTermCodes::SHIPMENT_PARTIAL_RECEIVED,
        ];
    }

    public static function archiveStatuses(): array
    {
        return [
            DocumentTermCodes::SHIPMENT_RECEIVED,
            DocumentTermCodes::SHIPMENT_CANCELLED,
        ];
    }
}
