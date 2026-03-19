<?php

namespace App\Http\Controllers;

use App\Support\ErpFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GoodsReceiptController extends Controller
{
    public function index(Request $request): View
    {
        $rows = DB::table('goods_receipts as gr')
            ->join('purchase_orders as po', 'po.id', '=', 'gr.purchase_order_id')
            ->leftJoin('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->select('gr.*', 'po.po_number', 's.supplier_name')
            ->orderByDesc('gr.id')
            ->paginate(20);

        $openPoList = DB::table('purchase_orders as po')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->select('po.id', 'po.po_number', 'po.status', 's.supplier_name')
            ->whereIn('po.status', ['Shipped', 'Partial Received', 'Supplier Confirmed', 'Sent to Supplier'])
            ->orderByDesc('po.id')
            ->limit(200)
            ->get();

        $poItems = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->join('items as i', 'i.id', '=', 'poi.item_id')
            ->leftJoin('goods_receipt_items as gri', 'gri.purchase_order_item_id', '=', 'poi.id')
            ->select(
                'poi.id',
                'poi.purchase_order_id',
                'poi.ordered_qty',
                'poi.received_qty',
                'poi.outstanding_qty',
                'po.po_number',
                'po.status as po_status',
                'i.item_code',
                'i.item_name',
                DB::raw('COALESCE(MAX(gri.created_at), NULL) as last_receipt_at')
            )
            ->where('poi.outstanding_qty', '>', 0)
            ->whereIn('po.status', ['Shipped', 'Partial Received', 'Supplier Confirmed', 'Sent to Supplier'])
            ->when($request->filled('po_id'), fn ($q) => $q->where('po.id', $request->integer('po_id')))
            ->groupBy('poi.id', 'poi.purchase_order_id', 'poi.ordered_qty', 'poi.received_qty', 'poi.outstanding_qty', 'po.po_number', 'po.status', 'i.item_code', 'i.item_name')
            ->orderBy('po.po_number')
            ->orderBy('i.item_code')
            ->get();

        return view('receiving.index', compact('rows', 'poItems', 'openPoList'));
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'purchase_order_item_id' => 'required|integer|exists:purchase_order_items,id',
            'receipt_date' => 'required|date',
            'received_qty' => 'required|numeric|min:0.01',
            'accepted_qty' => 'nullable|numeric|min:0',
            'rejected_qty' => 'nullable|numeric|min:0',
            'remark' => 'nullable|string|max:500',
            'document_number' => 'nullable|string|max:100',
        ], [
            'required' => ':attribute wajib diisi.',
            'received_qty.min' => 'Qty terima minimal 0.01.',
        ]);

        $allowOverReceipt = (bool) DB::table('settings')->where('key', 'allow_over_receipt')->value('value');

        DB::beginTransaction();
        try {
            $poItem = DB::table('purchase_order_items')->where('id', $v['purchase_order_item_id'])->lockForUpdate()->firstOrFail();
            $po = DB::table('purchase_orders')->where('id', $poItem->purchase_order_id)->lockForUpdate()->firstOrFail();

            if (in_array($po->status, ['Draft', 'Cancelled', 'Closed'], true)) {
                throw new \RuntimeException('PO dengan status ini tidak dapat diproses receiving.');
            }

            if ($v['received_qty'] > $poItem->outstanding_qty && ! $allowOverReceipt) {
                throw new \RuntimeException('Qty melebihi outstanding dan konfigurasi over-receipt tidak diizinkan.');
            }

            $grId = DB::table('goods_receipts')->insertGetId([
                'gr_number' => ErpFlow::generateNumber('GR', 'goods_receipts', 'gr_number'),
                'receipt_date' => $v['receipt_date'],
                'purchase_order_id' => $poItem->purchase_order_id,
                'warehouse_id' => $po->warehouse_id,
                'received_by' => optional($request->user())->id,
                'document_number' => $v['document_number'] ?? null,
                'remark' => $v['remark'] ?? null,
                'status' => 'Posted',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $acceptedQty = $v['accepted_qty'] ?? $v['received_qty'];
            $rejectedQty = $v['rejected_qty'] ?? max(0, $v['received_qty'] - $acceptedQty);

            DB::table('goods_receipt_items')->insert([
                'goods_receipt_id' => $grId,
                'purchase_order_item_id' => $poItem->id,
                'item_id' => $poItem->item_id,
                'received_qty' => $v['received_qty'],
                'accepted_qty' => $acceptedQty,
                'rejected_qty' => $rejectedQty,
                'remark' => $v['remark'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $newReceived = (float) $poItem->received_qty + (float) $v['received_qty'];
            $newOutstanding = max(0, (float) $poItem->ordered_qty - $newReceived);

            DB::table('purchase_order_items')->where('id', $poItem->id)->update([
                'received_qty' => $newReceived,
                'outstanding_qty' => $newOutstanding,
                'item_status' => $newOutstanding > 0 ? 'Partial Received' : 'Received',
                'updated_at' => now(),
            ]);

            ErpFlow::refreshPoStatusByOutstanding((int) $poItem->purchase_order_id, optional($request->user())->id);
            ErpFlow::audit('goods_receipts', $grId, 'create', null, $v, optional($request->user())->id, $request->ip());

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Goods Receipt item berhasil diposting.');
    }
}
