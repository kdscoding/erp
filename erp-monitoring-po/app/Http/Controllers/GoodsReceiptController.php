<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoodsReceiptController extends Controller
{
    public function index()
    {
        $rows = DB::table('goods_receipts as gr')
            ->join('purchase_orders as po', 'po.id', '=', 'gr.purchase_order_id')
            ->select('gr.*', 'po.po_number')
            ->orderByDesc('gr.id')
            ->paginate(20);
        $poItems = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->select('poi.*', 'po.po_number')
            ->where('poi.outstanding_qty', '>', 0)
            ->limit(300)
            ->get();
        return view('receiving.index', compact('rows', 'poItems'));
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'purchase_order_item_id' => 'required|integer',
            'receipt_date' => 'required|date',
            'received_qty' => 'required|numeric|min:0.01',
        ]);

        $poItem = DB::table('purchase_order_items')->where('id', $v['purchase_order_item_id'])->first();
        if (!$poItem) abort(422, 'PO item tidak ditemukan');
        if ($v['received_qty'] > $poItem->outstanding_qty) abort(422, 'Qty melebihi outstanding');

        $grNumber = 'GR-' . now()->format('Ymd') . '-' . str_pad((string)(DB::table('goods_receipts')->count()+1), 4, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $grId = DB::table('goods_receipts')->insertGetId([
                'gr_number' => $grNumber,
                'receipt_date' => $v['receipt_date'],
                'purchase_order_id' => $poItem->purchase_order_id,
                'document_number' => $request->document_number,
                'remark' => $request->remark,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('goods_receipt_items')->insert([
                'goods_receipt_id' => $grId,
                'purchase_order_item_id' => $poItem->id,
                'received_qty' => $v['received_qty'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $newReceived = $poItem->received_qty + $v['received_qty'];
            $newOutstanding = $poItem->ordered_qty - $newReceived;
            DB::table('purchase_order_items')->where('id', $poItem->id)->update([
                'received_qty' => $newReceived,
                'outstanding_qty' => $newOutstanding,
                'updated_at' => now(),
            ]);

            $hasOutstanding = DB::table('purchase_order_items')
                ->where('purchase_order_id', $poItem->purchase_order_id)
                ->where('outstanding_qty', '>', 0)
                ->exists();
            DB::table('purchase_orders')->where('id', $poItem->purchase_order_id)->update([
                'status' => $hasOutstanding ? 'Partial Received' : 'Closed',
                'updated_at' => now(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return back()->with('success', 'Goods Receipt tersimpan.');
    }
}
