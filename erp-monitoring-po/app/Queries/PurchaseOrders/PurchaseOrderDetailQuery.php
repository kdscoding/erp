<?php

namespace App\Queries\PurchaseOrders;

use App\Support\DocumentTermCodes;
use App\Support\ErpFlow;
use Illuminate\Support\Facades\DB;

class PurchaseOrderDetailQuery
{
    public function get(string $id): array
    {
        $currentDateSql = ErpFlow::currentDateExpression();

        $po = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->leftJoin('plants as p', 'p.id', '=', 'po.plant_id')
            ->leftJoin('warehouses as w', 'w.id', '=', 'po.warehouse_id')
            ->select('po.*', 's.supplier_name', 'p.plant_name', 'w.warehouse_name')
            ->where('po.id', $id)
            ->firstOrFail();

        $items = DB::table('purchase_order_items as poi')
            ->join('items as i', 'i.id', '=', 'poi.item_id')
            ->leftJoin('units as u', 'u.id', '=', 'i.unit_id')
            ->select('poi.*', 'i.item_code', 'i.item_name', 'u.unit_name')
            ->selectRaw("CASE
                WHEN poi.item_status = '" . DocumentTermCodes::ITEM_CANCELLED . "' THEN '" . DocumentTermCodes::ITEM_CANCELLED . "'
                WHEN poi.item_status = '" . DocumentTermCodes::ITEM_FORCE_CLOSED . "' THEN '" . DocumentTermCodes::ITEM_FORCE_CLOSED . "'
                WHEN poi.outstanding_qty <= 0 THEN '" . DocumentTermCodes::ITEM_CLOSED . "'
                WHEN poi.received_qty > 0 THEN '" . DocumentTermCodes::ITEM_PARTIAL . "'
                WHEN poi.etd_date IS NULL THEN '" . DocumentTermCodes::ITEM_WAITING . "'
                WHEN DATE(poi.etd_date) < {$currentDateSql} THEN '" . DocumentTermCodes::ITEM_LATE . "'
                ELSE '" . DocumentTermCodes::ITEM_CONFIRMED . "'
            END as monitoring_status")
            ->where('poi.purchase_order_id', $id)
            ->orderBy('poi.id')
            ->get();

        $poIsFinal = in_array($po->status, [
            DocumentTermCodes::PO_CLOSED,
            DocumentTermCodes::PO_CANCELLED,
        ], true);

        $items = $items->map(function ($item) use ($poIsFinal) {
            $itemIsFinal = in_array($item->monitoring_status, [
                DocumentTermCodes::ITEM_CLOSED,
                DocumentTermCodes::ITEM_FORCE_CLOSED,
                DocumentTermCodes::ITEM_CANCELLED,
            ], true);

            $item->can_update_etd = ! $poIsFinal && ! $itemIsFinal;
            $item->can_cancel = ! $poIsFinal && ! $itemIsFinal && (float) $item->received_qty <= 0;
            $item->can_force_close = ! $poIsFinal && ! $itemIsFinal && (float) $item->outstanding_qty > 0;

            return $item;
        });

        $trackingRows = DB::table('purchase_order_items as poi')
            ->leftJoin('shipment_items as si', 'si.purchase_order_item_id', '=', 'poi.id')
            ->leftJoin('shipments as sh', 'sh.id', '=', 'si.shipment_id')
            ->leftJoin('goods_receipt_items as gri', function ($join) {
                $join->on('gri.purchase_order_item_id', '=', 'poi.id')
                    ->on('gri.shipment_item_id', '=', 'si.id');
            })
            ->leftJoin('goods_receipts as gr', 'gr.id', '=', 'gri.goods_receipt_id')
            ->where('poi.purchase_order_id', $id)
            ->select(
                'poi.id as purchase_order_item_id',
                'si.id as shipment_item_id',
                'si.shipped_qty',
                'si.received_qty as shipment_received_qty',
                'sh.id as shipment_id',
                'sh.shipment_number',
                'sh.shipment_date',
                'sh.delivery_note_number',
                'sh.status as shipment_status',
                'gr.id as goods_receipt_id',
                'gr.gr_number',
                'gr.receipt_date',
                'gr.document_number as gr_document_number',
                'gr.status as gr_status',
                'gri.received_qty as gr_received_qty',
                'gri.accepted_qty'
            )
            ->orderBy('sh.shipment_date')
            ->orderBy('sh.id')
            ->orderBy('gr.receipt_date')
            ->orderBy('gr.id')
            ->get()
            ->groupBy('purchase_order_item_id');

        $items = $items->map(function ($item) use ($trackingRows) {
            $rows = collect($trackingRows->get($item->id, []))
                ->filter(fn ($row) => $row->shipment_id || $row->goods_receipt_id)
                ->values();

            $item->tracking_rows = $rows
                ->groupBy(fn ($row) => $row->shipment_item_id ?: 'no-shipment')
                ->map(function ($shipmentRows) {
                    $base = $shipmentRows->first();

                    return (object) [
                        'shipment_id' => $base->shipment_id,
                        'shipment_item_id' => $base->shipment_item_id,
                        'shipment_number' => $base->shipment_number,
                        'shipment_date' => $base->shipment_date,
                        'delivery_note_number' => $base->delivery_note_number,
                        'shipment_status' => $base->shipment_status,
                        'shipped_qty' => $base->shipped_qty,
                        'shipment_received_qty' => $base->shipment_received_qty,
                        'gr_rows' => $shipmentRows
                            ->filter(fn ($row) => $row->goods_receipt_id)
                            ->map(fn ($row) => (object) [
                                'goods_receipt_id' => $row->goods_receipt_id,
                                'gr_number' => $row->gr_number,
                                'receipt_date' => $row->receipt_date,
                                'gr_document_number' => $row->gr_document_number,
                                'gr_status' => $row->gr_status,
                                'gr_received_qty' => $row->gr_received_qty,
                                'accepted_qty' => $row->accepted_qty,
                            ])
                            ->values(),
                    ];
                })
                ->values();

            return $item;
        });

        $itemSummary = [
            'total' => $items->count(),
            'waiting' => $items->where('monitoring_status', DocumentTermCodes::ITEM_WAITING)->count(),
            'confirmed' => $items->where('monitoring_status', DocumentTermCodes::ITEM_CONFIRMED)->count(),
            'late' => $items->where('monitoring_status', DocumentTermCodes::ITEM_LATE)->count(),
            'partial' => $items->where('monitoring_status', DocumentTermCodes::ITEM_PARTIAL)->count(),
            'closed' => $items->where('monitoring_status', DocumentTermCodes::ITEM_CLOSED)->count(),
            'force_closed' => $items->where('monitoring_status', DocumentTermCodes::ITEM_FORCE_CLOSED)->count(),
            'cancelled' => $items->where('monitoring_status', DocumentTermCodes::ITEM_CANCELLED)->count(),
        ];

        $itemSummary['active'] = $itemSummary['total'] - $itemSummary['cancelled'];
        $itemSummary['progress_label'] = match (true) {
            $itemSummary['active'] === 0 => 'Semua item dibatalkan',
            $itemSummary['partial'] > 0 && ($itemSummary['waiting'] > 0 || $itemSummary['confirmed'] > 0 || $itemSummary['late'] > 0) => 'Receiving berjalan, masih ada item belum selesai',
            $itemSummary['partial'] > 0 => 'Receiving parsial',
            $itemSummary['confirmed'] > 0 && ($itemSummary['waiting'] > 0 || $itemSummary['late'] > 0) => 'Konfirmasi supplier masih campuran',
            $itemSummary['late'] > 0 => 'Ada item overdue ETD',
            $itemSummary['confirmed'] > 0 => 'Seluruh item aktif sudah terkonfirmasi',
            $itemSummary['waiting'] === $itemSummary['active'] => 'Semua item aktif masih waiting',
            ($itemSummary['closed'] + $itemSummary['force_closed']) === $itemSummary['active'] => 'Semua item sudah final',
            default => 'Perlu review manual',
        };

        $histories = DB::table('po_status_histories as h')
            ->leftJoin('users as u', 'u.id', '=', 'h.changed_by')
            ->where('h.purchase_order_id', $id)
            ->orderByDesc('h.id')
            ->select('h.*', 'u.name as changed_by_name')
            ->get();

        $poCanCancel = ! $poIsFinal;

        return compact('po', 'items', 'itemSummary', 'histories', 'poCanCancel', 'poIsFinal');
    }
}
