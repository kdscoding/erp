<?php

namespace App\Http\Controllers;

use App\Support\ErpFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ShipmentController extends Controller
{
    private const SHIPPABLE_PO_STATUSES = ['PO Issued', 'Confirmed', 'Shipped', 'Partial'];

    public function index(Request $request): View
    {
        $rows = DB::table('shipments as sh')
            ->join('purchase_orders as po', 'po.id', '=', 'sh.purchase_order_id')
            ->leftJoin('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->select('sh.*', 'po.po_number', 's.supplier_name')
            ->when($request->filled('delivery_note_number'), fn ($q) => $q->where('sh.delivery_note_number', 'like', '%'.$request->string('delivery_note_number').'%'))
            ->orderByDesc('sh.id')
            ->paginate(20);

        $pos = DB::table('purchase_orders as po')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->leftJoin('purchase_order_items as poi', 'poi.purchase_order_id', '=', 'po.id')
            ->leftJoin('items as i', 'i.id', '=', 'poi.item_id')
            ->whereIn('po.status', self::SHIPPABLE_PO_STATUSES)
            ->when($request->filled('supplier_id'), fn ($q) => $q->where('po.supplier_id', $request->integer('supplier_id')))
            ->when($request->filled('keyword'), function ($q) use ($request) {
                $keyword = '%'.$request->string('keyword').'%';
                $q->where(function ($inner) use ($keyword) {
                    $inner->where('po.po_number', 'like', $keyword)
                        ->orWhere('s.supplier_name', 'like', $keyword)
                        ->orWhere('i.item_code', 'like', $keyword)
                        ->orWhere('i.item_name', 'like', $keyword);
                });
            })
            ->groupBy('po.id', 'po.po_number', 'po.status', 's.supplier_name')
            ->select(
                'po.id',
                'po.po_number',
                'po.status',
                's.supplier_name',
                DB::raw('COUNT(DISTINCT poi.id) as item_count')
            )
            ->orderByDesc('po.id')
            ->limit(200)
            ->get();

        $suppliers = DB::table('suppliers')->orderBy('supplier_name')->get(['id', 'supplier_name']);

        return view('shipments.index', compact('rows', 'pos', 'suppliers'));
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'purchase_order_id' => 'required|integer|exists:purchase_orders,id',
            'shipment_date' => 'required|date',
            'eta_date' => 'nullable|date|after_or_equal:shipment_date',
            'delivery_note_number' => 'required|string|max:100',
            'supplier_remark' => 'nullable|string|max:500',
            'po_reference_missing' => ['nullable', Rule::in(['1'])],
        ], ['required' => ':attribute wajib diisi.']);

        $po = DB::table('purchase_orders')->where('id', $v['purchase_order_id'])->firstOrFail();
        if (! in_array($po->status, self::SHIPPABLE_PO_STATUSES, true)) {
            return back()->with('error', 'Status PO tidak valid untuk shipment.');
        }

        $userId = optional($request->user())->id;
        $number = ErpFlow::generateNumber('SHP', 'shipments', 'shipment_number');

        $remark = trim(implode(' | ', array_filter([
            ! empty($v['po_reference_missing']) ? 'Dokumen supplier tidak mencantumkan nomor PO.' : null,
            $v['supplier_remark'] ?? null,
        ]))) ?: null;

        DB::table('shipments')->insert([
            'purchase_order_id' => $v['purchase_order_id'],
            'shipment_number' => $number,
            'shipment_date' => $v['shipment_date'],
            'eta_date' => $v['eta_date'] ?? null,
            'delivery_note_number' => $v['delivery_note_number'],
            'supplier_remark' => $remark,
            'status' => 'Shipped',
            'created_by' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('purchase_orders')->where('id', $v['purchase_order_id'])->update([
            'status' => 'Shipped',
            'eta_date' => $v['eta_date'] ?? $po->eta_date,
            'updated_by' => $userId,
            'updated_at' => now(),
        ]);

        ErpFlow::pushPoStatus((int) $po->id, $po->status, 'Shipped', $userId, 'Shipment '.$number.' dibuat.');
        ErpFlow::audit('shipments', (int) DB::getPdo()->lastInsertId(), 'create', null, $v, $userId, $request->ip());

        return back()->with('success', 'Shipment tersimpan.');
    }
}
