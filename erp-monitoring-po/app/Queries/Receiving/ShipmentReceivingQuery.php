<?php

namespace App\Queries\Receiving;

use App\Support\DocumentTermCodes;
use Illuminate\Http\Request;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ShipmentReceivingQuery
{
    public function documents(Request $request): Collection
    {
        return DB::table('shipments as sh')
            ->join('suppliers as s', 's.id', '=', 'sh.supplier_id')
            ->join('shipment_items as si', 'si.shipment_id', '=', 'sh.id')
            ->join('purchase_order_items as poi', 'poi.id', '=', 'si.purchase_order_item_id')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->whereIn('sh.status', [
                DocumentTermCodes::SHIPMENT_SHIPPED,
                DocumentTermCodes::SHIPMENT_PARTIAL_RECEIVED,
            ])
            ->whereRaw('(si.shipped_qty - si.received_qty) > 0')
            ->when($request->filled('supplier_id'), fn ($query) => $query->where('sh.supplier_id', $request->integer('supplier_id')))
            ->when(
                $request->filled('document_number'),
                fn ($query) => $query->where('sh.delivery_note_number', 'like', '%' . $request->string('document_number') . '%')
            )
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $keyword = '%' . $request->string('keyword') . '%';

                $query->where(function ($inner) use ($keyword) {
                    $inner->where('sh.shipment_number', 'like', $keyword)
                        ->orWhere('sh.delivery_note_number', 'like', $keyword)
                        ->orWhere('sh.invoice_number', 'like', $keyword)
                        ->orWhere('po.po_number', 'like', $keyword)
                        ->orWhere('s.supplier_name', 'like', $keyword);
                });
            })
            ->select(
                'sh.id',
                'sh.shipment_number',
                'sh.delivery_note_number',
                'sh.invoice_number',
                'sh.shipment_date',
                'sh.status',
                'sh.supplier_id',
                's.supplier_name'
            )
            ->selectRaw('COUNT(DISTINCT si.id) as line_count')
            ->selectRaw('COUNT(DISTINCT po.id) as po_count')
            ->selectRaw('SUM(si.shipped_qty - si.received_qty) as outstanding_qty')
            ->groupBy(
                'sh.id',
                'sh.shipment_number',
                'sh.delivery_note_number',
                'sh.invoice_number',
                'sh.shipment_date',
                'sh.status',
                'sh.supplier_id',
                's.supplier_name'
            )
            ->orderByDesc('sh.id')
            ->get();
    }

    public function itemsForShipment(int $shipmentId): Collection
    {
        return $this->baseItems()
            ->where('sh.id', $shipmentId)
            ->orderBy('po.po_number')
            ->orderBy('i.item_code')
            ->get();
    }

    public function itemsForShipmentBuilder(int $shipmentId): Builder
    {
        return $this->baseItems()->where('sh.id', $shipmentId);
    }

    private function baseItems(): Builder
    {
        $currentDateSql = DB::connection()->getDriverName() === 'sqlite'
            ? "date('now')"
            : 'CURDATE()';

        return DB::table('shipment_items as si')
            ->join('shipments as sh', 'sh.id', '=', 'si.shipment_id')
            ->join('purchase_order_items as poi', 'poi.id', '=', 'si.purchase_order_item_id')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->join('items as i', 'i.id', '=', 'poi.item_id')
            ->leftJoin('goods_receipt_items as gri', 'gri.shipment_item_id', '=', 'si.id')
            ->select(
                'si.id as shipment_item_id',
                'si.shipment_id',
                'si.shipped_qty',
                'si.received_qty as shipment_received_qty',
                'si.invoice_unit_price',
                'si.invoice_line_total',
                'poi.id as purchase_order_item_id',
                'poi.purchase_order_id',
                'poi.ordered_qty',
                'poi.unit_price',
                'poi.received_qty',
                'poi.outstanding_qty',
                'poi.etd_date',
                'po.po_number',
                'po.status as po_status',
                's.supplier_name',
                'i.item_code',
                'i.item_name',
                'sh.shipment_number',
                'sh.delivery_note_number',
                'sh.invoice_number',
                'sh.invoice_date',
                'sh.invoice_currency',
                'sh.status as shipment_status',
                DB::raw('COALESCE(MAX(gri.created_at), NULL) as last_receipt_at')
            )
            ->selectRaw('(si.shipped_qty - si.received_qty) as shipment_outstanding_qty')
            ->selectRaw("CASE
                WHEN (si.shipped_qty - si.received_qty) <= 0 THEN '" . DocumentTermCodes::SHIPMENT_RECEIVED . "'
                WHEN si.received_qty > 0 THEN '" . DocumentTermCodes::SHIPMENT_PARTIAL_RECEIVED . "'
                WHEN poi.etd_date IS NULL THEN '" . DocumentTermCodes::ITEM_WAITING . "'
                WHEN DATE(poi.etd_date) < {$currentDateSql} THEN '" . DocumentTermCodes::ITEM_LATE . "'
                ELSE '" . DocumentTermCodes::SHIPMENT_SHIPPED . "'
            END as monitoring_status")
            ->whereIn('sh.status', [
                DocumentTermCodes::SHIPMENT_SHIPPED,
                DocumentTermCodes::SHIPMENT_PARTIAL_RECEIVED,
            ])
            ->whereRaw('(si.shipped_qty - si.received_qty) > 0')
            ->where('poi.item_status', '!=', DocumentTermCodes::ITEM_CANCELLED)
            ->groupBy(
                'si.id',
                'si.shipment_id',
                'si.shipped_qty',
                'si.received_qty',
                'si.invoice_unit_price',
                'si.invoice_line_total',
                'poi.id',
                'poi.purchase_order_id',
                'poi.ordered_qty',
                'poi.unit_price',
                'poi.received_qty',
                'poi.outstanding_qty',
                'poi.etd_date',
                'po.po_number',
                'po.status',
                's.supplier_name',
                'i.item_code',
                'i.item_name',
                'sh.shipment_number',
                'sh.delivery_note_number',
                'sh.invoice_number',
                'sh.invoice_date',
                'sh.invoice_currency',
                'sh.status'
            );
    }
}
