<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Support\DocumentTermCodes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ShipmentDraftBulkImportTest extends TestCase
{
    use RefreshDatabase;

    private const COLUMNS = [
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

    private function adminUser(): User
    {
        $role = Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator']);
        $user = User::factory()->create();
        DB::table('user_roles')->insert(['user_id' => $user->id, 'role_id' => $role->id]);

        return $user;
    }

    private function seedBasic(): void
    {
        DB::table('settings')->insert([
            'key' => 'allow_over_receipt',
            'value' => '0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('suppliers')->insert(['supplier_code' => 'SUP001', 'supplier_name' => 'Supplier A', 'status' => 1, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('suppliers')->insert(['supplier_code' => 'SUP002', 'supplier_name' => 'Supplier B', 'status' => 1, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('suppliers')->insert(['supplier_code' => 'SUP003', 'supplier_name' => 'Supplier Inactive', 'status' => 0, 'created_at' => now(), 'updated_at' => now()]);

        $unitId = DB::table('units')->insertGetId(['unit_code' => 'PCS', 'unit_name' => 'Pieces', 'created_at' => now(), 'updated_at' => now()]);

        DB::table('items')->insert(['item_code' => 'ITM001', 'item_name' => 'Item Alpha', 'unit_id' => $unitId, 'active' => 1, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('items')->insert(['item_code' => 'ITM002', 'item_name' => 'Item Beta', 'unit_id' => $unitId, 'active' => 1, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('items')->insert(['item_code' => 'ITM003', 'item_name' => 'Item Gamma', 'unit_id' => $unitId, 'active' => 1, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('items')->insert(['item_code' => 'ITM004', 'item_name' => 'Item Inactive', 'unit_id' => $unitId, 'active' => 0, 'created_at' => now(), 'updated_at' => now()]);
    }

    private function createPo(string $poNumber, string $supplierCode, array $items): int
    {
        $supplierId = DB::table('suppliers')->where('supplier_code', $supplierCode)->value('id');

        $poId = DB::table('purchase_orders')->insertGetId([
            'po_number' => $poNumber,
            'po_date' => now()->toDateString(),
            'supplier_id' => $supplierId,
            'currency' => 'IDR',
            'status' => DocumentTermCodes::PO_ISSUED,
            'eta_date' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($items as $itemCode => $orderedQty) {
            $itemId = DB::table('items')->where('item_code', $itemCode)->value('id');
            DB::table('purchase_order_items')->insert([
                'purchase_order_id' => $poId,
                'item_id' => $itemId,
                'ordered_qty' => $orderedQty,
                'received_qty' => 0,
                'outstanding_qty' => $orderedQty,
                'item_status' => DocumentTermCodes::ITEM_WAITING,
                'unit_price' => 50000,
                'etd_date' => null,
                'eta_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $poId;
    }

    private function createFlatFile(array $dataRows, string $extension = 'xlsx'): string
    {
        if ($extension === 'xlsx') {
            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();

            foreach (self::COLUMNS as $index => $col) {
                $sheet->setCellValueByColumnAndRow($index + 1, 1, $col);
            }

            $rowNum = 2;
            foreach ($dataRows as $data) {
                foreach (self::COLUMNS as $colIndex => $colName) {
                    $sheet->setCellValueByColumnAndRow($colIndex + 1, $rowNum, $data[$colName] ?? '');
                }
                $rowNum++;
            }

            $path = storage_path('app/temp/test_bulk_import_'.uniqid('', true).'.xlsx');
            $writer = new Xlsx($spreadsheet);
            $writer->save($path);

            return $path;
        }

        $path = storage_path('app/temp/test_bulk_import_'.uniqid('', true).'.csv');
        $fp = fopen($path, 'w');
        fputcsv($fp, self::COLUMNS);
        foreach ($dataRows as $data) {
            $rowValues = array_map(fn ($col) => (string) ($data[$col] ?? ''), self::COLUMNS);
            fputcsv($fp, $rowValues);
        }
        fclose($fp);

        return $path;
    }

    private function uploadFile(string $path, string $originalName, string $mimeType): UploadedFile
    {
        return new UploadedFile($path, $originalName, $mimeType, 0, true);
    }

    public function test_bulk_template_download(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->get('/shipments/template/bulk-draft-excel')
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_import_xlsx_single_shipment_multiple_items(): void
    {
        $this->seedBasic();
        $user = $this->adminUser();

        $this->createPo('PO-0001', 'SUP001', ['ITM001' => 100, 'ITM002' => 50]);

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile([
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'SUP001',
                'DN' => 'DN001',
                'invoice_number' => 'INV001',
                'invoice_date' => $dateStr,
                'supplier_remark' => 'Remark test',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM001',
                'invoice_unit_price' => '50000',
                'qty_pengiriman' => 60,
            ],
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'SUP001',
                'DN' => 'DN001',
                'invoice_number' => 'INV001',
                'invoice_date' => $dateStr,
                'supplier_remark' => 'Remark test',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM002',
                'invoice_unit_price' => '',
                'qty_pengiriman' => 30,
            ],
        ]);

        $file = $this->uploadFile($path, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($user)
            ->post('/shipments/import-bulk-draft-excel', ['file' => $file])
            ->assertSessionHas('import_success');

        $this->assertDatabaseCount('shipments', 1);
        $this->assertDatabaseCount('shipment_items', 2);
        $this->assertDatabaseHas('shipments', [
            'delivery_note_number' => 'DN001',
            'invoice_number' => 'INV001',
            'invoice_date' => date('Y-m-d'),
            'supplier_id' => DB::table('suppliers')->where('supplier_code', 'SUP001')->value('id'),
            'status' => DocumentTermCodes::SHIPMENT_DRAFT,
        ]);

        $shipmentNumber = DB::table('shipments')->value('shipment_number');
        $this->assertIsNumeric($shipmentNumber);
        $this->assertGreaterThan(0, (int) $shipmentNumber);

        $shipmentId = DB::table('shipments')->value('id');
        $itm001ItemId = DB::table('items')->where('item_code', 'ITM001')->value('id');
        $itm002ItemId = DB::table('items')->where('item_code', 'ITM002')->value('id');
        $itm001PoiId = DB::table('purchase_order_items')->where('item_id', $itm001ItemId)->value('id');
        $itm002PoiId = DB::table('purchase_order_items')->where('item_id', $itm002ItemId)->value('id');

        $this->assertDatabaseHas('shipment_items', [
            'shipment_id' => $shipmentId,
            'purchase_order_item_id' => $itm001PoiId,
            'shipped_qty' => 60,
            'invoice_unit_price' => 50000,
        ]);

        $this->assertDatabaseHas('shipment_items', [
            'shipment_id' => $shipmentId,
            'purchase_order_item_id' => $itm002PoiId,
            'shipped_qty' => 30,
            'invoice_unit_price' => null,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'module' => 'shipments',
            'action' => 'import_bulk_excel',
        ]);
    }

    public function test_import_xlsx_multiple_shipments(): void
    {
        $this->seedBasic();
        $user = $this->adminUser();

        $this->createPo('PO-0001', 'SUP001', ['ITM001' => 100]);
        $this->createPo('PO-0002', 'SUP002', ['ITM003' => 50]);

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile([
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'SUP001',
                'DN' => 'DN001',
                'invoice_number' => 'INV001',
                'supplier_remark' => '',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM001',
                'invoice_unit_price' => '50000',
                'qty_pengiriman' => 80,
            ],
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'SUP002',
                'DN' => 'DN002',
                'invoice_number' => 'INV002',
                'supplier_remark' => '',
                'po_number' => 'PO-0002',
                'item_code' => 'ITM003',
                'invoice_unit_price' => '100000',
                'qty_pengiriman' => 25,
            ],
        ]);

        $file = $this->uploadFile($path, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($user)
            ->post('/shipments/import-bulk-draft-excel', ['file' => $file])
            ->assertSessionHas('import_success');

        $this->assertDatabaseCount('shipments', 2);
        $this->assertDatabaseCount('shipment_items', 2);

        $this->assertDatabaseHas('shipments', ['delivery_note_number' => 'DN001', 'invoice_number' => 'INV001', 'supplier_id' => DB::table('suppliers')->where('supplier_code', 'SUP001')->value('id')]);
        $this->assertDatabaseHas('shipments', ['delivery_note_number' => 'DN002', 'invoice_number' => 'INV002', 'supplier_id' => DB::table('suppliers')->where('supplier_code', 'SUP002')->value('id')]);
    }

    public function test_import_csv_success(): void
    {
        $this->seedBasic();
        $user = $this->adminUser();

        $this->createPo('PO-0001', 'SUP001', ['ITM001' => 100]);

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile([
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'SUP001',
                'DN' => 'DN001',
                'invoice_number' => '',
                'supplier_remark' => '',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM001',
                'invoice_unit_price' => '50000',
                'qty_pengiriman' => 75,
            ],
        ], 'csv');

        $file = $this->uploadFile($path, 'test.csv', 'text/csv');

        $this->actingAs($user)
            ->post('/shipments/import-bulk-draft-excel', ['file' => $file])
            ->assertSessionHas('import_success');

        $this->assertDatabaseCount('shipments', 1);
        $this->assertDatabaseCount('shipment_items', 1);
        $this->assertDatabaseHas('shipments', [
            'delivery_note_number' => 'DN001',
            'invoice_number' => null,
            'status' => DocumentTermCodes::SHIPMENT_DRAFT,
        ]);
    }

    public function test_import_invalid_supplier(): void
    {
        $this->seedBasic();
        $user = $this->adminUser();

        $this->createPo('PO-0001', 'SUP001', ['ITM001' => 100]);

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile([
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'INVALID',
                'DN' => 'DN001',
                'invoice_number' => '',
                'supplier_remark' => '',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM001',
                'invoice_unit_price' => '',
                'qty_pengiriman' => 50,
            ],
        ]);

        $file = $this->uploadFile($path, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($user)
            ->post('/shipments/import-bulk-draft-excel', ['file' => $file])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('shipments', 0);
    }

    public function test_import_inactive_supplier(): void
    {
        $this->seedBasic();
        $user = $this->adminUser();

        $this->createPo('PO-0001', 'SUP001', ['ITM001' => 100]);

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile([
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'SUP003',
                'DN' => 'DN001',
                'invoice_number' => '',
                'supplier_remark' => '',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM001',
                'invoice_unit_price' => '',
                'qty_pengiriman' => 50,
            ],
        ]);

        $file = $this->uploadFile($path, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($user)
            ->post('/shipments/import-bulk-draft-excel', ['file' => $file])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('shipments', 0);
    }

    public function test_import_invalid_item(): void
    {
        $this->seedBasic();
        $user = $this->adminUser();

        $this->createPo('PO-0001', 'SUP001', ['ITM001' => 100]);

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile([
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'SUP001',
                'DN' => 'DN001',
                'invoice_number' => '',
                'supplier_remark' => '',
                'po_number' => 'PO-0001',
                'item_code' => 'INVALID',
                'invoice_unit_price' => '',
                'qty_pengiriman' => 50,
            ],
        ]);

        $file = $this->uploadFile($path, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($user)
            ->post('/shipments/import-bulk-draft-excel', ['file' => $file])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('shipments', 0);
    }

    public function test_import_inactive_item(): void
    {
        $this->seedBasic();
        $user = $this->adminUser();

        $this->createPo('PO-0001', 'SUP001', ['ITM004' => 100]);

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile([
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'SUP001',
                'DN' => 'DN001',
                'invoice_number' => '',
                'supplier_remark' => '',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM004',
                'invoice_unit_price' => '',
                'qty_pengiriman' => 50,
            ],
        ]);

        $file = $this->uploadFile($path, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($user)
            ->post('/shipments/import-bulk-draft-excel', ['file' => $file])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('shipments', 0);
    }

    public function test_import_qty_zero(): void
    {
        $this->seedBasic();
        $user = $this->adminUser();

        $this->createPo('PO-0001', 'SUP001', ['ITM001' => 100]);

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile([
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'SUP001',
                'DN' => 'DN001',
                'invoice_number' => '',
                'supplier_remark' => '',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM001',
                'invoice_unit_price' => '',
                'qty_pengiriman' => 0,
            ],
        ]);

        $file = $this->uploadFile($path, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($user)
            ->post('/shipments/import-bulk-draft-excel', ['file' => $file])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('shipments', 0);
    }

    public function test_import_negative_qty(): void
    {
        $this->seedBasic();
        $user = $this->adminUser();

        $this->createPo('PO-0001', 'SUP001', ['ITM001' => 100]);

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile([
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'SUP001',
                'DN' => 'DN001',
                'invoice_number' => '',
                'supplier_remark' => '',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM001',
                'invoice_unit_price' => '',
                'qty_pengiriman' => -5,
            ],
        ]);

        $file = $this->uploadFile($path, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($user)
            ->post('/shipments/import-bulk-draft-excel', ['file' => $file])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('shipments', 0);
    }

    public function test_import_qty_exceeds_available(): void
    {
        $this->seedBasic();
        $user = $this->adminUser();

        $this->createPo('PO-0001', 'SUP001', ['ITM001' => 100]);

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile([
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'SUP001',
                'DN' => 'DN001',
                'invoice_number' => '',
                'supplier_remark' => '',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM001',
                'invoice_unit_price' => '',
                'qty_pengiriman' => 200,
            ],
        ]);

        $file = $this->uploadFile($path, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($user)
            ->post('/shipments/import-bulk-draft-excel', ['file' => $file])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('shipments', 0);
    }

    public function test_import_qty_exceeds_available_after_partial_allocation_in_same_file(): void
    {
        $this->seedBasic();
        $user = $this->adminUser();

        $this->createPo('PO-0001', 'SUP001', ['ITM001' => 100]);

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile([
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'SUP001',
                'DN' => 'DN001',
                'invoice_number' => '',
                'supplier_remark' => '',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM001',
                'invoice_unit_price' => '',
                'qty_pengiriman' => 80,
            ],
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'SUP001',
                'DN' => 'DN002',
                'invoice_number' => '',
                'supplier_remark' => '',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM001',
                'invoice_unit_price' => '',
                'qty_pengiriman' => 50,
            ],
        ]);

        $file = $this->uploadFile($path, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($user)
            ->post('/shipments/import-bulk-draft-excel', ['file' => $file])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('shipments', 0);
    }

    public function test_import_duplicate_delivery_note_in_file(): void
    {
        $this->seedBasic();
        $user = $this->adminUser();

        $this->createPo('PO-0001', 'SUP001', ['ITM001' => 100, 'ITM002' => 50]);

        $dateStr1 = now()->toDateString();
        $dateStr2 = now()->subDay()->toDateString();
        $path = $this->createFlatFile([
            [
                'shipment_date' => $dateStr1,
                'supplier_code' => 'SUP001',
                'DN' => 'DN001',
                'invoice_number' => '',
                'supplier_remark' => '',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM001',
                'invoice_unit_price' => '',
                'qty_pengiriman' => 50,
            ],
            [
                'shipment_date' => $dateStr2,
                'supplier_code' => 'SUP001',
                'DN' => 'DN001',
                'invoice_number' => '',
                'supplier_remark' => '',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM002',
                'invoice_unit_price' => '',
                'qty_pengiriman' => 30,
            ],
        ]);

        $file = $this->uploadFile($path, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($user)
            ->post('/shipments/import-bulk-draft-excel', ['file' => $file])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('shipments', 0);
    }

    public function test_import_duplicate_delivery_note_in_db(): void
    {
        $this->seedBasic();
        $user = $this->adminUser();

        $this->createPo('PO-0001', 'SUP001', ['ITM001' => 100]);

        $existingSupplierId = DB::table('suppliers')->where('supplier_code', 'SUP001')->value('id');
        $existingPoItemId = DB::table('purchase_order_items')->where('purchase_order_id', DB::table('purchase_orders')->where('po_number', 'PO-0001')->value('id'))->value('id');

        $shipmentId = DB::table('shipments')->insertGetId([
            'purchase_order_id' => DB::table('purchase_orders')->where('po_number', 'PO-0001')->value('id'),
            'supplier_id' => $existingSupplierId,
            'shipment_number' => 'SHP-'.date('Ymd').'-0001',
            'shipment_date' => now()->toDateString(),
            'delivery_note_number' => 'DN001',
            'invoice_number' => null,
            'supplier_remark' => null,
            'status' => DocumentTermCodes::SHIPMENT_DRAFT,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('shipment_items')->insert([
            'shipment_id' => $shipmentId,
            'purchase_order_item_id' => $existingPoItemId,
            'shipped_qty' => 0,
            'received_qty' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile([
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'SUP001',
                'DN' => 'DN001',
                'invoice_number' => '',
                'supplier_remark' => '',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM001',
                'invoice_unit_price' => '',
                'qty_pengiriman' => 50,
            ],
        ]);

        $file = $this->uploadFile($path, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($user)
            ->post('/shipments/import-bulk-draft-excel', ['file' => $file])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('shipments', 1);
    }

    public function test_import_invalid_date(): void
    {
        $this->seedBasic();
        $user = $this->adminUser();

        $this->createPo('PO-0001', 'SUP001', ['ITM001' => 100]);

        $path = $this->createFlatFile([
            [
                'shipment_date' => 'invalid-date',
                'supplier_code' => 'SUP001',
                'DN' => 'DN001',
                'invoice_number' => '',
                'supplier_remark' => '',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM001',
                'invoice_unit_price' => '',
                'qty_pengiriman' => 50,
            ],
        ]);

        $file = $this->uploadFile($path, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($user)
            ->post('/shipments/import-bulk-draft-excel', ['file' => $file])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('shipments', 0);
    }

    public function test_import_missing_columns(): void
    {
        $this->seedBasic();
        $user = $this->adminUser();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValueByColumnAndRow(1, 1, 'supplier_code');
        $sheet->setCellValueByColumnAndRow(1, 2, 'SUP001');

        $path = storage_path('app/temp/test_missing_cols_'.uniqid('', true).'.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        $file = $this->uploadFile($path, 'test_missing.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($user)
            ->post('/shipments/import-bulk-draft-excel', ['file' => $file])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('shipments', 0);
    }

    public function test_import_invalid_extension(): void
    {
        $this->seedBasic();
        $user = $this->adminUser();

        $path = storage_path('app/temp/test.txt');
        file_put_contents($path, 'test');

        $file = $this->uploadFile($path, 'test.txt', 'text/plain');

        $this->actingAs($user)
            ->post('/shipments/import-bulk-draft-excel', ['file' => $file])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('shipments', 0);
    }

    public function test_import_empty_file(): void
    {
        $this->seedBasic();
        $user = $this->adminUser();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        foreach (self::COLUMNS as $index => $col) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $col);
        }

        $path = storage_path('app/temp/test_empty_'.uniqid('', true).'.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        $file = $this->uploadFile($path, 'test_empty.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($user)
            ->post('/shipments/import-bulk-draft-excel', ['file' => $file])
            ->assertSessionHas('error');
    }

    public function test_import_rollback_on_mixed_valid_invalid(): void
    {
        $this->seedBasic();
        $user = $this->adminUser();

        $this->createPo('PO-0001', 'SUP001', ['ITM001' => 100]);

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile([
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'SUP001',
                'DN' => 'DN001',
                'invoice_number' => '',
                'supplier_remark' => '',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM001',
                'invoice_unit_price' => '50000',
                'qty_pengiriman' => 50,
            ],
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'INVALID',
                'DN' => 'DN002',
                'invoice_number' => '',
                'supplier_remark' => '',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM001',
                'invoice_unit_price' => '',
                'qty_pengiriman' => 30,
            ],
        ]);

        $file = $this->uploadFile($path, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($user)
            ->post('/shipments/import-bulk-draft-excel', ['file' => $file])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('shipments', 0);
        $this->assertDatabaseCount('shipment_items', 0);
    }

    public function test_import_same_po_item_in_same_shipment_blocked(): void
    {
        $this->seedBasic();
        $user = $this->adminUser();

        $this->createPo('PO-0001', 'SUP001', ['ITM001' => 100]);

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile([
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'SUP001',
                'DN' => 'DN001',
                'invoice_number' => '',
                'supplier_remark' => '',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM001',
                'invoice_unit_price' => '',
                'qty_pengiriman' => 30,
            ],
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'SUP001',
                'DN' => 'DN001',
                'invoice_number' => '',
                'supplier_remark' => '',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM001',
                'invoice_unit_price' => '',
                'qty_pengiriman' => 40,
            ],
        ]);

        $file = $this->uploadFile($path, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($user)
            ->post('/shipments/import-bulk-draft-excel', ['file' => $file])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('shipments', 0);
    }

    public function test_import_auto_generates_shipment_number(): void
    {
        $this->seedBasic();
        $user = $this->adminUser();

        $this->createPo('PO-0001', 'SUP001', ['ITM001' => 100]);

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile([
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'SUP001',
                'DN' => 'DN001',
                'invoice_number' => '',
                'supplier_remark' => '',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM001',
                'invoice_unit_price' => '',
                'qty_pengiriman' => 100,
            ],
        ]);

        $file = $this->uploadFile($path, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($user)
            ->post('/shipments/import-bulk-draft-excel', ['file' => $file])
            ->assertSessionHas('import_success');

        $shipmentNumber = DB::table('shipments')->value('shipment_number');
        $this->assertIsNumeric($shipmentNumber);
        $this->assertGreaterThanOrEqual(1000000000, (int) $shipmentNumber);
        $this->assertLessThanOrEqual(9999999999, (int) $shipmentNumber);
    }

    public function test_import_requires_authentication(): void
    {
        $this->seedBasic();

        $this->createPo('PO-0001', 'SUP001', ['ITM001' => 100]);

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile([
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'SUP001',
                'DN' => 'DN001',
                'invoice_number' => '',
                'supplier_remark' => '',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM001',
                'invoice_unit_price' => '',
                'qty_pengiriman' => 50,
            ],
        ]);

        $file = $this->uploadFile($path, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->post('/shipments/import-bulk-draft-excel', ['file' => $file])
            ->assertRedirect('/login');
    }

    public function test_import_collects_all_errors_before_rollback(): void
    {
        $this->seedBasic();
        $user = $this->adminUser();

        $this->createPo('PO-0001', 'SUP001', ['ITM001' => 100]);

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile([
            [
                'shipment_date' => 'invalid',
                'supplier_code' => 'INVALID',
                'DN' => '',
                'invoice_number' => '',
                'invoice_date' => '',
                'supplier_remark' => '',
                'po_number' => 'NOTFOUND',
                'item_code' => 'NOPE',
                'invoice_unit_price' => '',
                'qty_pengiriman' => -1,
            ],
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'SUP001',
                'DN' => 'DN002',
                'invoice_number' => '',
                'invoice_date' => 'bad-date',
                'supplier_remark' => '',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM001',
                'invoice_unit_price' => '',
                'qty_pengiriman' => 200,
            ],
        ]);

        $file = $this->uploadFile($path, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $response = $this->actingAs($user)
            ->post('/shipments/import-bulk-draft-excel', ['file' => $file]);

        $response->assertSessionHas('error');

        $this->assertDatabaseCount('shipments', 0);
        $this->assertDatabaseCount('shipment_items', 0);
    }

    public function test_import_structured_error_table_format(): void
    {
        $this->seedBasic();
        $user = $this->adminUser();

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile([
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'INVALID',
                'DN' => 'DN001',
                'invoice_number' => '',
                'invoice_date' => '',
                'supplier_remark' => '',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM001',
                'invoice_unit_price' => '',
                'qty_pengiriman' => 50,
            ],
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'SUP001',
                'DN' => 'DN002',
                'invoice_number' => '',
                'invoice_date' => '',
                'supplier_remark' => '',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM001',
                'invoice_unit_price' => '',
                'qty_pengiriman' => 0,
            ],
        ]);

        $file = $this->uploadFile($path, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $response = $this->actingAs($user)
            ->post('/shipments/import-bulk-draft-excel', ['file' => $file]);

        $response->assertSessionHas('error');
        $response->assertSessionHas('import_errors');
        $errors = $response->getSession()->get('import_errors');

        $this->assertCount(2, $errors);
        $this->assertEquals(2, $errors[0]['row']);
        $this->assertEquals('supplier_code', $errors[0]['field']);
        $this->assertEquals(3, $errors[1]['row']);
        $this->assertEquals('qty_pengiriman', $errors[1]['field']);
    }

    public function test_import_invalid_invoice_date(): void
    {
        $this->seedBasic();
        $user = $this->adminUser();

        $this->createPo('PO-0001', 'SUP001', ['ITM001' => 100]);

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile([
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'SUP001',
                'DN' => 'DN001',
                'invoice_number' => 'INV001',
                'invoice_date' => 'not-a-date',
                'supplier_remark' => '',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM001',
                'invoice_unit_price' => '',
                'qty_pengiriman' => 50,
            ],
        ]);

        $file = $this->uploadFile($path, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($user)
            ->post('/shipments/import-bulk-draft-excel', ['file' => $file])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('shipments', 0);
    }

    public function test_import_all_or_nothing_with_duplicate_dn_and_invalid_row(): void
    {
        $this->seedBasic();
        $user = $this->adminUser();

        $this->createPo('PO-0001', 'SUP001', ['ITM001' => 100, 'ITM002' => 50]);

        $existingSupplierId = DB::table('suppliers')->where('supplier_code', 'SUP001')->value('id');
        $poId = DB::table('purchase_orders')->where('po_number', 'PO-0001')->value('id');
        $poiId1 = DB::table('purchase_order_items')->where('purchase_order_id', $poId)->where('item_id', DB::table('items')->where('item_code', 'ITM001')->value('id'))->value('id');

        $shipmentId = DB::table('shipments')->insertGetId([
            'purchase_order_id' => $poId,
            'supplier_id' => $existingSupplierId,
            'shipment_number' => 'SHP-'.date('Ymd').'-0001',
            'shipment_date' => now()->toDateString(),
            'delivery_note_number' => 'DN001',
            'invoice_number' => null,
            'supplier_remark' => null,
            'status' => DocumentTermCodes::SHIPMENT_DRAFT,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('shipment_items')->insert([
            'shipment_id' => $shipmentId,
            'purchase_order_item_id' => $poiId1,
            'shipped_qty' => 0,
            'received_qty' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile([
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'INVALID',
                'DN' => 'DN002',
                'invoice_number' => '',
                'invoice_date' => '',
                'supplier_remark' => '',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM001',
                'invoice_unit_price' => '',
                'qty_pengiriman' => 50,
            ],
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'SUP001',
                'DN' => 'DN001',
                'invoice_number' => '',
                'invoice_date' => '',
                'supplier_remark' => '',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM002',
                'invoice_unit_price' => '',
                'qty_pengiriman' => 30,
            ],
        ]);

        $file = $this->uploadFile($path, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $response = $this->actingAs($user)
            ->post('/shipments/import-bulk-draft-excel', ['file' => $file]);

        $response->assertSessionHas('error');
        $response->assertSessionHas('import_errors');
        $errors = $response->getSession()->get('import_errors');

        $hasSupplierError = collect($errors)->contains(fn ($e) => $e['field'] === 'supplier_code');
        $hasDnError = collect($errors)->contains(fn ($e) => str_contains($e['message'], 'DN001 sudah dipakai'));

        $this->assertTrue($hasSupplierError, 'Structured errors should contain supplier_code error');
        $this->assertTrue($hasDnError, 'Structured errors should contain duplicate DN error');

        $this->assertDatabaseCount('shipments', 1);
    }

    public function test_import_same_invoice_different_dn_succeeds(): void
    {
        $this->seedBasic();
        $user = $this->adminUser();

        $this->createPo('PO-0001', 'SUP001', ['ITM001' => 100]);
        $this->createPo('PO-0002', 'SUP001', ['ITM002' => 50]);

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile([
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'SUP001',
                'DN' => 'DN001',
                'invoice_number' => 'DMT 01.02.04.26',
                'invoice_date' => $dateStr,
                'supplier_remark' => '',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM001',
                'invoice_unit_price' => '50000',
                'qty_pengiriman' => 60,
            ],
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'SUP001',
                'DN' => 'DN002',
                'invoice_number' => 'DMT 01.02.04.26',
                'invoice_date' => $dateStr,
                'supplier_remark' => '',
                'po_number' => 'PO-0002',
                'item_code' => 'ITM002',
                'invoice_unit_price' => '75000',
                'qty_pengiriman' => 30,
            ],
        ]);

        $file = $this->uploadFile($path, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($user)
            ->post('/shipments/import-bulk-draft-excel', ['file' => $file])
            ->assertSessionHas('import_success');

        $this->assertDatabaseCount('shipments', 2);
        $this->assertDatabaseCount('shipment_items', 2);

        $this->assertDatabaseHas('shipments', [
            'delivery_note_number' => 'DN001',
            'invoice_number' => 'DMT 01.02.04.26',
        ]);
        $this->assertDatabaseHas('shipments', [
            'delivery_note_number' => 'DN002',
            'invoice_number' => 'DMT 01.02.04.26',
        ]);
    }

    public function test_import_same_invoice_multiple_pos_items_succeeds(): void
    {
        $this->seedBasic();
        $user = $this->adminUser();

        $this->createPo('PO-0001', 'SUP001', ['ITM001' => 100, 'ITM002' => 50]);

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile([
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'SUP001',
                'DN' => 'DN001',
                'invoice_number' => 'DMT 01.02.04.26',
                'invoice_date' => $dateStr,
                'supplier_remark' => 'Remark 1',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM001',
                'invoice_unit_price' => '50000',
                'qty_pengiriman' => 60,
            ],
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'SUP001',
                'DN' => 'DN001',
                'invoice_number' => 'DMT 01.02.04.26',
                'invoice_date' => $dateStr,
                'supplier_remark' => 'Remark 2',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM002',
                'invoice_unit_price' => '75000',
                'qty_pengiriman' => 30,
            ],
        ]);

        $file = $this->uploadFile($path, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($user)
            ->post('/shipments/import-bulk-draft-excel', ['file' => $file])
            ->assertSessionHas('import_success');

        $this->assertDatabaseCount('shipments', 1);
        $this->assertDatabaseCount('shipment_items', 2);

        $this->assertDatabaseHas('shipments', [
            'delivery_note_number' => 'DN001',
            'invoice_number' => 'DMT 01.02.04.26',
            'status' => DocumentTermCodes::SHIPMENT_DRAFT,
        ]);

        $shipmentId = DB::table('shipments')->where('delivery_note_number', 'DN001')->value('id');
        $itm001PoiId = DB::table('purchase_order_items')->where('item_id', DB::table('items')->where('item_code', 'ITM001')->value('id'))->where('purchase_order_id', DB::table('purchase_orders')->where('po_number', 'PO-0001')->value('id'))->value('id');
        $itm002PoiId = DB::table('purchase_order_items')->where('item_id', DB::table('items')->where('item_code', 'ITM002')->value('id'))->where('purchase_order_id', DB::table('purchase_orders')->where('po_number', 'PO-0001')->value('id'))->value('id');

        $this->assertDatabaseHas('shipment_items', [
            'shipment_id' => $shipmentId,
            'purchase_order_item_id' => $itm001PoiId,
            'shipped_qty' => 60,
        ]);
        $this->assertDatabaseHas('shipment_items', [
            'shipment_id' => $shipmentId,
            'purchase_order_item_id' => $itm002PoiId,
            'shipped_qty' => 30,
        ]);
    }

    public function test_import_same_invoice_same_dn_different_invoice_date_succeeds(): void
    {
        $this->seedBasic();
        $user = $this->adminUser();

        $this->createPo('PO-0001', 'SUP001', ['ITM001' => 100, 'ITM002' => 50]);

        $dateStr = now()->toDateString();
        $invoiceDate1 = now()->subDays(2)->toDateString();
        $invoiceDate2 = now()->subDay()->toDateString();

        $path = $this->createFlatFile([
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'SUP001',
                'DN' => 'DN001',
                'invoice_number' => 'DMT 01.02.04.26',
                'invoice_date' => $invoiceDate1,
                'supplier_remark' => '',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM001',
                'invoice_unit_price' => '50000',
                'qty_pengiriman' => 60,
            ],
            [
                'shipment_date' => $dateStr,
                'supplier_code' => 'SUP001',
                'DN' => 'DN001',
                'invoice_number' => 'DMT 01.02.04.26',
                'invoice_date' => $invoiceDate2,
                'supplier_remark' => 'Remark',
                'po_number' => 'PO-0001',
                'item_code' => 'ITM002',
                'invoice_unit_price' => '75000',
                'qty_pengiriman' => 30,
            ],
        ]);

        $file = $this->uploadFile($path, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($user)
            ->post('/shipments/import-bulk-draft-excel', ['file' => $file])
            ->assertSessionHas('import_success');

        $this->assertDatabaseCount('shipments', 1);
        $this->assertDatabaseCount('shipment_items', 2);
    }
}
